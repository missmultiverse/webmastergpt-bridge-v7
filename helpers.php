<?php
/**
 * File: helpers.php
 * Purpose: Contains shared utility functions such as logging for GPT actions.
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
// --- START --- LOGGING UTILITY ----------------------------------
// ----------------------------------------------------------------

/**
 * Logs REST and system events for debugging GPT actions.
 *
 * @param string $channel  Log file name (e.g. REST, Sync)
 * @param string $message  Human-readable log message
 * @param mixed  $data     Optional array or string with structured payload
 */
function wgpt_log($channel, $message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $entry = [
        'timestamp' => $timestamp,
        'channel'   => strtoupper($channel),
        'message'   => $message,
        'data'      => $data
    ];

    $dir = plugin_dir_path(__FILE__) . 'logs/';
    $file = $dir . strtolower($channel) . '.log.json';

    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents($file, json_encode($entry, JSON_PRETTY_PRINT) . ",\n", FILE_APPEND);
}
// --- END --- LOGGING UTILITY ------------------------------------
