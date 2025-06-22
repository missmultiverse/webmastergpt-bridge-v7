<?php
/**
 * File: api-key-impersonate.php
 * Purpose: Enables universal REST API authentication for GPT agents by mapping x-api-key headers to WP users.
 *
 * 🛠️ Developer Note:
 * This module allows any REST API endpoint (including /wp/v2/*) to impersonate a mapped user
 * when a valid x-api-key is provided. Add, remove, or modify key-user mappings as needed.
 *
 * 🚨 DO NOT REMOVE THIS HEADER OR SECTION COMMENTS!
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
// │ --- START --- API KEY TO USER IMPERSONATION HOOK             │
// └──────────────────────────────────────────────────────────────┘

/**
 * Allows API key in header to impersonate a WP user for ALL WP REST requests.
 * Map your API key to the desired WP user login below.
 */
add_filter('rest_authentication_errors', function ($result) {
    if (!empty($result)) {
        return $result; // Another authentication already ran or failed
    }

    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $headers = array_change_key_case($headers, CASE_LOWER);

    if (isset($headers['x-api-key'])) {
        $api_key = trim($headers['x-api-key']);
        $map = [
            '0xteEF2YXTpNnnLOZP8SZDyo' => 'WebMaster.GPT', // API key maps to this user_login
        ];
        if (isset($map[$api_key])) {
            $user = get_user_by('login', $map[$api_key]);
            if ($user && $user->exists()) {
                wp_set_current_user($user->ID);
                return null; // Authenticated as this user
            }
        }
        // Return an error if API key is invalid
        return new WP_Error('rest_forbidden', 'Invalid API key.', ['status' => 401]);
    }
    return null; // No API key present, allow default auth handling
});

// --- END --- API KEY TO USER IMPERSONATION HOOK -----------------
