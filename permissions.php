<?php
/**
 * File: permissions.php
 * Purpose: Centralized capability map + enforcement for GPT users.
 *
 * 🛡️ Enforces scoped access for GPT system users
 * 🎯 Enables compatibility with role editors and audit logs
 * 🧩 Used by: REST checks, admin tools, and system audits
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
function gpt_get_capability_map(): array {
    return [
        'gpt_manage_dashboard' => 'Access GPT dashboard',
        'gpt_execute_action'   => 'Execute GPT REST actions',
        'gpt_read_logs'        => 'View GPT system logs',
        'gpt_sync_identity'    => 'Sync identities and roles',
        'gpt_export_data'      => 'Export plugin-related data',
        // Add more custom GPT caps here as needed
    ];
}
// --- END --- GPT CAPABILITY MAP ---------------------------------


// ----------------------------------------------------------------
// --- START --- HELPER: GPT USER CAN -----------------------------
// ----------------------------------------------------------------

/**
 * Wrapper to check if current user or specific user can perform GPT actions.
 */
function gpt_user_can(string $cap, $user_id = null): bool {
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
 * Supports fallback to plugin-defined caps in legacy mode.
 */
function gpt_agent_can($agent_id, $capability) {
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



