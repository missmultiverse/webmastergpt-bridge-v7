<?php
/**
 * File: actions.php
 * Purpose: Contains GPT action handler functions like `ping`, `sync_identity`, etc.
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
// --- START --- GPT ACTION: PING ---------------------------------
// ----------------------------------------------------------------

/**
 * Responds to a basic GPT ping for health/status check.
 */
function wgpt_action_ping(array $payload = []): array
{
    return [
        'status' => 'ok',
        'site' => get_bloginfo('url'),
        'version' => '7.0.0',
        'message' => 'WebmasterGPT v7 is alive and responding.',
        'time' => current_time('mysql'),
    ];
}
// --- END --- GPT ACTION: PING -----------------------------------

// ----------------------------------------------------------------
// --- START --- GPT ACTION: CREATE_POST ---------------------------
// ----------------------------------------------------------------

/**
 * Handles the `create_post` action dispatched through the universal /action endpoint.
 * This mirrors the logic of the dedicated wgpt_create_post_handler but works with
 * the already authenticated GPT user context.
 *
 * @param mixed   $null    Placeholder from the filter.
 * @param string  $action  Action name being processed.
 * @param array   $payload Parameters supplied for post creation.
 * @param WP_User $user    GPT user object resolved from identity.
 *
 * @return array|null Array with post details on success or error information on failure.
 */
function wgpt_handle_create_post($null, $action, array $payload, WP_User $user)
{
    if ($action !== 'create_post') {
        return $null;
    }

    $title = sanitize_text_field($payload['title'] ?? '');
    $content = isset($payload['content']) ? wp_kses_post($payload['content']) : '';
    $status = sanitize_text_field($payload['status'] ?? 'publish');

    if ($title === '' || $content === '') {
        return [
            'error' => 'invalid_params',
            'message' => 'Title and content are required.',
        ];
    }

    $post_id = wp_insert_post([
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => $status,
        'post_author' => $user->ID,
    ], true);

    if (is_wp_error($post_id)) {
        return [
            'error' => 'post_creation_failed',
            'message' => $post_id->get_error_message(),
        ];
    }

    return [
        'id' => $post_id,
        'title' => $title,
        'content' => $content,
        'status' => $status,
        'author' => $user->ID,
        'link' => get_permalink($post_id),
    ];
}
add_filter('wgpt_handle_action', 'wgpt_handle_create_post', 10, 4);

// --- END --- GPT ACTION: CREATE_POST -----------------------------

// ----------------------------------------------------------------
// --- START --- GPT ACTION: SYNC_IDENTITY ------------------------
// ----------------------------------------------------------------

/**
 * Handles GPT identity sync action. (Stub implementation)
 *
 * @param array $payload
 * @return array
 */
function wgpt_sync_identity(array $payload = []): array
{
    // TODO: Implement actual identity sync logic
    return [
        'success' => true,
        'message' => 'Identity sync is not yet implemented.'
    ];
}
// --- END --- GPT ACTION: SYNC_IDENTITY --------------------------
