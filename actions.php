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
function wgpt_action_ping(array $payload = []): array {
    return [
        'status'    => 'ok',
        'site'      => get_bloginfo('url'),
        'version'   => '7.0.0',
        'message'   => 'WebmasterGPT v7 is alive and responding.',
        'time'      => current_time('mysql'),
    ];
}
// --- END --- GPT ACTION: PING -----------------------------------
