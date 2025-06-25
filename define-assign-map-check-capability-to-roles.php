<?php
/**
 * File: define-assign-map-check-capability-to-roles.php
 * Formally named: PERMISSIONS.php
 * Purpose: Centralizes role creation, capability mapping, and checks for GPT users and their actions.
 *
 * 🛡️Manages WordPress role creation and assigns GPT-related capabilities to roles
 * 🎯 Provides compatibility for GPT action checks and role-based permissions
 * 🧩 Used for REST checks, admin tools, and system audits
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
// --- START --- GPT CAPABILITY MAP -------------------------------
// ----------------------------------------------------------------

/**
 * Defines all custom GPT capabilities and their descriptions.
 * This map can be used to:
 * - Register capabilities to roles
 * - Filter what agents can do
 * - Display readable names in admin UI
 */
function gpt_get_capability_map(): array
{
    return [
        'gpt_manage_dashboard' => 'Access GPT dashboard',
        'gpt_execute_action' => 'Execute GPT REST actions',
        'gpt_read_logs' => 'View GPT system logs',
        'gpt_sync_identity' => 'Sync identities and roles',
        'gpt_export_data' => 'Export plugin-related data',
        'gpt_create_post' => 'Create new posts',
        'gpt_publish' => 'Publish posts',
        'gpt_upload_media' => 'Upload media',
        'gpt_set_post_status' => 'Set post status (publish, draft)',
        'gpt_edit_post' => 'Edit posts',
        'gpt_delete_post' => 'Delete posts',
        'gpt_list_posts' => 'List all posts',
        'gpt_get_post' => 'Retrieve a specific post',
        'gpt_list_universal_actions' => 'List available universal actions',
        'use_rest_endpoint' => 'Access REST endpoints',
        // Add more custom GPT caps here as needed
    ];
}
// --- END --- GPT CAPABILITY MAP ---------------------------------


// ----------------------------------------------------------------
// --- START --- ROLES AND CAPABILITIES ASSIGNMENT ----------------
// ----------------------------------------------------------------

/**
 * Create custom roles if they don’t exist and assign GPT-related capabilities to them.
 */
function gpt_assign_capabilities()
{
    // Create roles if they don't exist
    if (!get_role('webmaster')) {
        add_role(
            'webmaster',
            'Webmaster',
            [
                'read'                 => true,
                'edit_posts'           => true,
                'manage_options'       => true,
                // Add more caps as needed...
            ]
        );
    }

    if (!get_role('publisher')) {
        add_role(
            'publisher',
            'Publisher',
            [
                'read'                 => true,
                'edit_posts'           => true,
                'edit_others_posts'    => true,
                'publish_posts'        => true,
                'delete_posts'         => true,
                'manage_categories'    => true,
                // Add more publisher caps...
            ]
        );
    }

    // Assign capabilities to `editor` role
    $editor = get_role('editor');
    if ($editor) {
        $editor->add_cap('gpt_manage_dashboard');
        $editor->add_cap('gpt_execute_action');
        $editor->add_cap('gpt_read_logs');
        $editor->add_cap('gpt_sync_identity');
        $editor->add_cap('gpt_export_data');
        $editor->add_cap('gpt_create_post');
        $editor->add_cap('gpt_publish');
        $editor->add_cap('gpt_upload_media');
        $editor->add_cap('gpt_set_post_status');
        $editor->add_cap('gpt_edit_post');
        $editor->add_cap('gpt_delete_post');
        $editor->add_cap('gpt_list_posts');
        $editor->add_cap('gpt_get_post');
        $editor->add_cap('gpt_list_universal_actions');
        $editor->add_cap('use_rest_endpoint');
    }

    // Assign capabilities to `publisher` role
    $publisher = get_role('publisher');
    if ($publisher) {
        $publisher->add_cap('gpt_manage_dashboard');
        $publisher->add_cap('gpt_execute_action');
        $publisher->add_cap('gpt_read_logs');
        $publisher->add_cap('gpt_sync_identity');
        $publisher->add_cap('gpt_export_data');
        $publisher->add_cap('gpt_create_post');
        $publisher->add_cap('gpt_publish');
        $publisher->add_cap('gpt_upload_media');
        $publisher->add_cap('gpt_set_post_status');
        $publisher->add_cap('gpt_edit_post');
        $publisher->add_cap('gpt_delete_post');
        $publisher->add_cap('gpt_list_posts');
        $publisher->add_cap('gpt_get_post');
        $publisher->add_cap('gpt_list_universal_actions');
        $publisher->add_cap('use_rest_endpoint');
    }

    // Log action for audit
    error_log('✅ [WebmasterGPT] Roles/capabilities bootstrap executed.');
}
// --- END --- ROLES AND CAPABILITIES ASSIGNMENT -------------------


// ----------------------------------------------------------------
// --- START --- HELPER: GPT USER CAN -----------------------------
// ----------------------------------------------------------------

/**
 * Wrapper to check if current user or specific user can perform GPT actions.
 */
function gpt_user_can(string $cap, $user_id = null): bool
{
    $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
    if (!$user || !($user instanceof WP_User)) {
        return false;
    }
    return user_can($user, $cap);
}
// --- END --- HELPER: GPT USER CAN -------------------------------


// ----------------------------------------------------------------
// --- START --- AGENT CAPABILITY CHECK ---------------------------
// ----------------------------------------------------------------

/**
 * Checks whether a GPT agent has the specified capability.
 * Supports fallback to plugin-defined caps in legacy mode..
 */
function gpt_agent_can($agent_id, $capability)
{
    $user_id = intval($agent_id);

    if (!get_userdata($user_id)) {
        return false; // Invalid user ID
    }

    $native_check = user_can($user_id, $capability);

    // Optional fallback: only used if agent is in legacy mode
    $agent = function_exists('wgpt_get_agent') ? wgpt_get_agent($agent_id) : null;
    if (!$native_check && is_array($agent) && ($agent['capability_source'] ?? '') === 'plugin') {
        return in_array($capability, $agent['capabilities'] ?? []);
    }

    /**
     * Allow developers to override agent capability resolution.
     */
    return apply_filters('wgpt_agent_can', $native_check, $agent_id, $capability);
}
// --- END --- AGENT CAPABILITY CHECK -----------------------------

// --- END --- FINAL CAPABILITY MAP AND ROLE ASSIGNMENT -----------------


//Explanation of Changes:
//Role Creation: I’ve merged the role creation part from roles-bootstrap.php into this file. Now, the webmaster and publisher roles are created if they don’t already exist.

//Capabilities Assignment: The gpt_assign_capabilities() function assigns the relevant GPT capabilities to the editor and publisher roles, which are now centralized in this file.

//Capability Map: The gpt_get_capability_map() function provides a list of GPT-specific capabilities that can be mapped to roles, and we’ve ensured that the necessary capabilities are assigned to the roles.

//Helper Functions: The helper functions (gpt_user_can() and gpt_agent_can()) are included to check if users or agents have the necessary permissions to perform certain GPT actions.
