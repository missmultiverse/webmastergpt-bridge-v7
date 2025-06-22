<?php
/**
 * File: tools.php
 * Purpose: Admin tools tab for syncing GPT identity, syncing capabilities, and exporting role matrix.
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
// --- START --- HELPER: CAPABILITY SYNC FUNCTION -----------------
// ----------------------------------------------------------------

/**
 * Syncs all capabilities from gpt_get_capability_map to administrator role.
 */
function wgpt_sync_capabilities(): void {
    $role = get_role('administrator');
    if (!$role || !function_exists('gpt_get_capability_map')) {
        return;
    }

    foreach (gpt_get_capability_map() as $cap) {
        $role->add_cap($cap);
    }
}
// --- END --- HELPER: CAPABILITY SYNC FUNCTION -------------------


// ----------------------------------------------------------------
// --- START --- EXPORT CAPABILITY MATRIX -------------------------
// ----------------------------------------------------------------

function wgpt_output_capability_matrix_json(): void {
    $map = function_exists('gpt_get_capability_map') ? gpt_get_capability_map() : [];
    $roles = wp_roles()->roles;
    $result = [];

    foreach ($map as $action => $cap) {
        $granted_roles = [];
        foreach ($roles as $slug => $details) {
            $role_obj = get_role($slug);
            if ($role_obj && $role_obj->has_cap($cap)) {
                $granted_roles[] = $details['name'];
            }
        }
        $result[$action] = [
            'capability' => $cap,
            'roles' => $granted_roles,
        ];
    }

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="gpt-capability-matrix.json"');
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}
// --- END --- EXPORT CAPABILITY MATRIX ---------------------------


// ----------------------------------------------------------------
// --- START --- ADMIN POST HANDLERS ------------------------------
// ----------------------------------------------------------------

add_action('admin_post_wgpt_sync_identity', function () {
    check_admin_referer('wgpt_tools_nonce');
    if (function_exists('wgpt_sync_identity')) {
        wgpt_sync_identity();
    }
    wp_safe_redirect(admin_url('admin.php?page=webmastergpt&tab=tools&status=identity_synced'));
    exit;
});

add_action('admin_post_wgpt_sync_capabilities', function () {
    check_admin_referer('wgpt_tools_nonce');
    wgpt_sync_capabilities();
    wp_safe_redirect(admin_url('admin.php?page=webmastergpt&tab=tools&status=capabilities_synced'));
    exit;
});

add_action('admin_post_wgpt_export_capabilities', function () {
    check_admin_referer('wgpt_tools_nonce');
    wgpt_output_capability_matrix_json();
    exit;
});

// --- END --- ADMIN POST HANDLERS -------------------------------

// ----------------------------------------------------------------
// --- START --- TOOLS TAB HOOK REGISTRATION ----------------------
// ----------------------------------------------------------------

add_action('wgpt_render_tab_tools', function () {
    $tab_file = __DIR__ . '/tabs/tab-tools.php';
    if (file_exists($tab_file)) {
        include $tab_file;
    } else {
        echo '<div class="notice notice-error"><p>тЪая╕П tools.php: Tab UI file not found at <code>' . esc_html($tab_file) . '</code></p></div>';
    }
});

// --- END --- TOOLS TAB HOOK REGISTRATION ------------------------

// ----------------------------------------------------------------
// --- START --- REST ACTION HANDLER: sync_identity --------------
// ----------------------------------------------------------------

/**
 * Handles REST action: sync_identity
 */
add_filter('wgpt_handle_action', 'wgpt_handle_sync_identity', 10, 3);

function wgpt_handle_sync_identity($null, $action, $payload) {
    if ($action !== 'sync_identity') return $null;

    $uuid = sanitize_text_field($payload['uuid'] ?? '');
    $site = esc_url_raw($payload['site'] ?? site_url());

    return [
        'status' => 'ok',
        'uuid'   => $uuid,
        'site'   => $site,
        'synced' => true,
    ];
}

// --- END --- REST ACTION HANDLER: sync_identity -----------------

