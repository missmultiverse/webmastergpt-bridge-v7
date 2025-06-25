<?php
/**
 * Plugin Name: WebmasterGPT Bridge v7.0.0
 * Description: Core controller plugin for GPT agents in WordPress — handles identity sync, capabilities, access control, REST endpoints, logging, and admin tools.
 * Version: 7.0.0
 * Author: MissMultiverse.AI
 * Author URI: https://missmultiverse.com
 * Text Domain: webmastergpt
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

// ┌──────────────────────────────────────────────────────────────┐
// │ --- START --- REQUIRED MODULES: UNIVERSAL LOADER             │
// └──────────────────────────────────────────────────────────────┘

/**
 * Loads all plugin modules in logical order. 
 * Each file below provides a self-contained subsystem (API auth, identity, endpoints, etc).
 * 📂 Add/remove requires here to manage plugin features.
 */
 
 // Core API Key to User impersonation module
require_once plugin_dir_path(__FILE__) . 'api-key-impersonate.php';

// Common utility functions for the plugin
require_once plugin_dir_path(__FILE__) . 'helpers.php';

// Provides public OpenAI schema endpoint
require_once plugin_dir_path(__FILE__) . 'schema-endpoint.php';

//require_once __DIR__ . '/roles-bootstrap.php'; // Uncomment if needed

require_once plugin_dir_path(__FILE__) . 'tools.php';

// --- END --- REQUIRED MODULES: UNIVERSAL LOADER ----------------


// ----------------------------------------------------------------
// --- START --- SECURITY GUARD -----------------------------------
// ----------------------------------------------------------------
if (!defined('ABSPATH')) {
    exit; // Halt execution if accessed directly
}
// --- END --- SECURITY GUARD -------------------------------------


// ----------------------------------------------------------------
// --- START --- GLOBAL CONSTANTS (Optional Future Use) -----------
// ----------------------------------------------------------------
// Example: define('WGPT_PLUGIN_VERSION', '7.0.0');
// --- END --- GLOBAL CONSTANTS -----------------------------------


// ----------------------------------------------------------------
// --- START --- LOAD CORE MODULES -------------------------------- 
// ----------------------------------------------------------------

require_once __DIR__ . '/define-user-identity-resolver-API-key-auth.php';  // Identity management: Resolves the current GPT agent to a WP_User object using API key or logged-in fallback

require_once plugin_dir_path(__FILE__) . '/define-assign-map-check-capability-to-roles.php'; // Define and assign custom capabilities to roles, and handle capability checks.

require_once __DIR__ . '/editor-publisher-dispatch-handler.php';

require_once __DIR__ . '/rest-endpoints.php';   // GPT REST API handler

require_once __DIR__ . '/admin-ui.php';         // Admin panel and navigation

require_once __DIR__ . '/agents.php';           // GPT agent definitions + filters

require_once __DIR__ . '/logging.php';          // Logging: denials + identity audit

// --- END --- LOAD CORE MODULES ----------------------------------


// ----------------------------------------------------------------
// --- START --- OPTIONAL MODULES (Dynamic Load) ------------------
// ----------------------------------------------------------------
$module_dir = __DIR__ . '/modules';
if (is_dir($module_dir)) {
    foreach (glob($module_dir . '/*.php') as $module_file) {
        require_once $module_file;
    }
}
// --- END --- OPTIONAL MODULES -----------------------------------


// ----------------------------------------------------------------
// --- START --- PLUGIN ACTIVATION HOOK ---------------------------
// ----------------------------------------------------------------
register_activation_hook(__FILE__, function () {
    if (function_exists('gpt_assign_capabilities')) {
        gpt_assign_capabilities(); // Ensure GPT user + capabilities are created
        error_log('✅ [WebmasterGPT] Plugin activated and roles/capabilities assigned.');
    }
});
// --- END --- PLUGIN ACTIVATION HOOK -----------------------------




