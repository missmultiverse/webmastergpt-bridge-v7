<?php
/**
 * File: rest-endpoints.php
 * Purpose: Registers the GPT REST endpoints for handling action requests, ping checks, and permission control.
 *
 * 📡 Routes:
 *   - POST /wp-json/wgpt/v1/action
 *   - GET|HEAD /wp-json/wgpt/v1/ping
 *   - GET /wp-json/wgpt/v1/universal-actions
 *
 * 🔐 Supports API key and role-based permission checks
 * 🧩 Compatible with OpenAI function calling & action schema
 */

/**
 * 🛠️ Developer Note:
 * This file is part of the WebmasterGPT Bridge plugin and MUST adhere to the following
 * hyper-descriptive structural and formatting conventions at all times:
 *
 * ✅ SECTION HEADERS
 *   — Every logical section of the code must begin with a **3-line banner** formatted as:
 *         ┌──────────────────────────────────────────────────────────────┐
 *         │ --- START --- {SECTION NAME IN CAPS}                         │
 *         └──────────────────────────────────────────────────────────────┘
 *   — Every section must end with a **1-line compact footer** formatted as:
 *         // --- END --- {SECTION NAME IN CAPS} ---------------------------
 *
 * ✅ FILE ORGANIZATION
 *   — All code must be grouped **logically by purpose**, such as:
 *         → Identity & authentication logic
 *         → Permissions & role systems
 *         → Admin UI or settings logic
 *         → REST API registration and handlers
 *         → Utility or helper functions
 *
 * ✅ CODE STYLE & RELIABILITY
 *   — All logic must be explicit and fail-safe:
 *         → Do not allow silent failures
 *         → Always log or return errors with meaningful context
 *         → Avoid ambiguous variable names or logic branches
 *
 * 🚨 DO NOT REMOVE THIS DEVELOPER NOTE FROM ANY FILE.
 * It serves as a shared convention contract across the entire GPT plugin codebase.
 */

// ----------------------------------------------------------------
// --- START --- GPT IDENTITY BOOTSTRAP ---------------------------
// ----------------------------------------------------------------

/**
 * Resolves the active GPT agent (as WP_User), sets global for later use.
 */
add_action('wgpt_init_identity', function () {
    global $wgpt_identity_user;
    $wgpt_identity_user = function_exists('wgpt_get_gpt_user_identity')
        ? wgpt_get_gpt_user_identity()
        : false;
}, 1);

// Invoke identity detection early for REST/API usage
do_action('wgpt_init_identity');

// --- END --- GPT IDENTITY BOOTSTRAP -----------------------------


// ----------------------------------------------------------------
// --- START --- REST API REGISTRATION ----------------------------
// ----------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('wgpt/v1', '/action', [
        'methods'             => 'POST',
        'callback'            => 'wgpt_handle_gpt_action',
        'permission_callback' => 'wgpt_rest_permission_check',
        'args' => [
            'action' => [
                'required'    => true,
                'type'        => 'string',
                'description' => 'The GPT action to perform',
            ],
            'payload' => [
                'required'    => false,
                'type'        => 'object',
                'description' => 'Optional data object containing action inputs',
            ],
        ],
    ]);

    register_rest_route('wgpt/v1', '/ping', [
        'methods'             => ['GET', 'HEAD'],
        'callback'            => 'wgpt_rest_ping_handler',
        'permission_callback' => 'wgpt_rest_permission_check',
    ]);

    register_rest_route('wgpt/v1', '/universal-actions', [
        'methods'             => 'GET',
        'callback'            => 'wgpt_rest_list_universal_actions',
        'permission_callback' => 'wgpt_rest_permission_check',
    ]);
});

// --- END --- REST API REGISTRATION ------------------------------


// ----------------------------------------------------------------
// --- START --- PERMISSION CHECK ---------------------------------
// ----------------------------------------------------------------

/**
 * Verifies access using either API Key header or current WP user roles.
 *
 * @return bool|WP_Error
 */
function wgpt_rest_permission_check() {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $headers = array_change_key_case($headers, CASE_LOWER);

    // ✅ API Key check
    if (isset($headers['x-api-key'])) {
        $api_key = trim($headers['x-api-key']);
        $valid_keys = [
            '0xteEF2YXTpNnnLOZP8SZDyo' => 'WebMaster.GPT',
        ];
        if (isset($valid_keys[$api_key])) return true;
    }

    // ✅ Logged-in user with correct role/capability
    $user = wp_get_current_user();
    if (
        function_exists('gpt_user_can') &&
        gpt_user_can('use_rest_endpoint') &&
        in_array('gpt_agent', (array) $user->roles)
    ) {
        return true;
    }

    // ❌ Denied
    return new WP_Error('unauthorized', __('Sorry, you are not allowed to do that.', 'webmastergpt'), ['status' => 401]);
}

// --- END --- PERMISSION CHECK -----------------------------------


// ----------------------------------------------------------------
// --- START --- PING ENDPOINT ------------------------------------
// ----------------------------------------------------------------

/**
 * Returns diagnostic info to confirm GPT connectivity and environment health.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function wgpt_rest_ping_handler($request) {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $headers = array_change_key_case($headers, CASE_LOWER);
    $identity = $headers['x-api-key'] ?? 'unauthenticated';

    $response = [
        'status'       => 'alive',
        'time'         => current_time('mysql'),
        'site_url'     => get_site_url(),
        'home_url'     => home_url(),
        'plugin'       => defined('WGPT_PLUGIN_VERSION') ? WGPT_PLUGIN_VERSION : 'WebmasterGPT Bridge v7.0.0',
        'wordpress'    => get_bloginfo('version'),
        'theme'        => wp_get_theme()->get('Name'),
        'environment'  => defined('WP_ENV') ? WP_ENV : (defined('WP_DEBUG') && WP_DEBUG ? 'development' : 'production'),
        'identity'     => $identity,
        'verified'     => isset($headers['x-api-key']) ? '✅ Key detected' : '❌ No key',
    ];

    return rest_ensure_response(apply_filters('wgpt_ping_response', $response, $request));
}

// --- END --- PING ENDPOINT --------------------------------------


// ----------------------------------------------------------------
// --- START --- GPT ACTION ROUTER --------------------------------
// ----------------------------------------------------------------

/**
 * Handles incoming GPT REST calls and routes the requested action.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wgpt_handle_gpt_action(WP_REST_Request $request) {
    $action  = sanitize_text_field($request->get_param('action'));
    $payload = (array) $request->get_param('payload');

    $user = function_exists('wgpt_get_gpt_user_identity') ? wgpt_get_gpt_user_identity() : false;

    if (!$user) {
        return new WP_Error('unauthenticated', 'Unable to resolve GPT identity.', ['status' => 401]);
    }

    // ✅ Check capability enforcement
    if (!user_can($user, $action)) {
        if (function_exists('wgpt_log')) {
            wgpt_log('REST', "❌ User '{$user->user_login}' attempted unauthorized action '{$action}'");
        }
        return new WP_Error('forbidden', "User '{$user->user_login}' is not allowed to run '{$action}'", ['status' => 403]);
    }

    // 🧠 Route to action handlers via filter
    $result = apply_filters('wgpt_handle_action', null, $action, $payload, $user);

    if ($result === null) {
        if (function_exists('wgpt_log')) {
            wgpt_log('REST', "⚠️ Unknown action '{$action}' invoked by '{$user->user_login}'");
        }
        return new WP_Error('invalid_action', "Action '{$action}' not recognized", ['status' => 400]);
    }

    if (function_exists('wgpt_log')) {
        wgpt_log('REST', "✅ Action '{$action}' invoked by '{$user->user_login}'");
    }

    return rest_ensure_response([
        'status' => 'success',
        'action' => $action,
        'result' => $result,
    ]);
}

// --- END --- GPT ACTION ROUTER ----------------------------------



// ----------------------------------------------------------------
// --- START --- UNIVERSAL ACTION LIST ----------------------------
// ----------------------------------------------------------------

/**
 * Returns a list of available GPT actions based on current user capabilities.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function wgpt_rest_list_universal_actions(WP_REST_Request $request) {
    $user = function_exists('wgpt_get_gpt_user_identity') ? wgpt_get_gpt_user_identity() : false;
    if (!$user) {
        return rest_ensure_response([
            'status' => 'error',
            'message' => 'No GPT user context detected.',
            'actions' => [],
            'count' => 0,
        ]);
    }
    $capabilities = (array) $user->allcaps;
    $allowed_prefixes = ['gpt_', 'edit_', 'view_', 'manage_', 'upload_', 'delete_', 'read_', 'publish_'];
    $actions = [];
    foreach ($capabilities as $cap => $enabled) {
        if (!$enabled) continue;
        foreach ($allowed_prefixes as $prefix) {
            if (strpos($cap, $prefix) === 0) {
                $actions[] = [
                    'action'  => $cap,
                    'granted' => true,
                ];
                break;
            }
        }
    }
    return rest_ensure_response([
        'status'   => 'ok',
        'identity' => $user->user_login,
        'actions'  => $actions,
        'count'    => count($actions),
    ]);
}

// --- END --- UNIVERSAL ACTION LIST ------------------------------
