<?php
/**
 * File: logging.php
 * Purpose: Logging system for GPT agents — stores events, denials, and identity actions in JSON format.
 *
 * 🛠️ Developer Note:
 * - Logs are grouped by type: 'identity', 'denial', 'system'
 * - JSON logs are stored in /logs/
 * - Use wgpt_log_event() for general use, or wgpt_log_identity / denial for targeted logs
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
// --- START --- LOG FILE UTILS -----------------------------------
// ----------------------------------------------------------------

/**
 * Get the full path to the log file for a given type.
 */
function wgpt_get_log_file_path(string $type): string {
    $dir = plugin_dir_path(__FILE__) . 'logs/';
    wp_mkdir_p($dir);
    return $dir . sanitize_file_name($type) . '.log.json';
}

/**
 * Append a new log entry.
 */
function wgpt_append_log(string $type, array $data): void {
    $entry = array_merge([
        'timestamp' => current_time('mysql'),
        'type' => $type
    ], $data);

    $file = wgpt_get_log_file_path($type);
    $logs = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    if (!is_array($logs)) {
        $logs = [];
    }

    $logs[] = $entry;
    file_put_contents($file, json_encode($logs, JSON_PRETTY_PRINT));
}

/**
 * Fetch all log entries of a given type.
 */
function wgpt_get_logs(string $type): array {
    $file = wgpt_get_log_file_path($type);
    if (!file_exists($file)) {
        return [];
    }
    $logs = json_decode(file_get_contents($file), true);
    return is_array($logs) ? $logs : [];
}

/**
 * Delete log file of a given type.
 */
function wgpt_clear_log(string $type): bool {
    $file = wgpt_get_log_file_path($type);
    return file_exists($file) ? unlink($file) : false;
}

/**
 * Force download of log file.
 */
function wgpt_download_log_file(string $type): void {
    $file = wgpt_get_log_file_path($type);
    if (!file_exists($file)) {
        wp_die('Log file not found.');
    }
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    readfile($file);
    exit;
}

// --- END --- LOG FILE UTILS -------------------------------------


// ----------------------------------------------------------------
// --- START --- LOGGING HELPERS ----------------------------------
// ----------------------------------------------------------------

function wgpt_log_identity(array $data): void {
    wgpt_append_log('identity', $data);
}

function wgpt_log_denial(array $data): void {
    wgpt_append_log('denial', $data);
}

function wgpt_log_event(string $type, string $message, array $context = []): void {
    wgpt_append_log($type, [
        'message' => $message,
        'context' => $context
    ]);
}
// --- END --- LOGGING HELPERS ------------------------------------





