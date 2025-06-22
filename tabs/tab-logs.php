<?php
/**
 * File: tab-logs.php
 * Purpose: Displays logs grouped by type (identity, denial, system) with scoped buttons.
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
// --- START --- LOG TAB DISPLAY ----------------------------------
// ----------------------------------------------------------------

$log_types = ['identity', 'denial', 'system'];

echo '<h2>📜 GPT Event Log</h2>';

// Loop through each log type and build its block
foreach ($log_types as $type) {

    // ----------------------------------------------------------------
    // --- START --- FORM HANDLING FOR THIS LOG TYPE ------------------
    // ----------------------------------------------------------------
    if (
        current_user_can('manage_options') &&
        isset($_POST['log_type']) &&
        $_POST['log_type'] === $type &&
        check_admin_referer("wgpt_log_{$type}_nonce")
    ) {
        // --- Download Action ---
        if (isset($_POST["download_log_{$type}"])) {
            wgpt_download_log_file($type);
        }

        // --- Clear Action ---
        if (isset($_POST["clear_log_{$type}"])) {
            wgpt_clear_log($type);
            echo '<div class="notice notice-success is-dismissible"><p>🧹 ' . ucfirst($type) . ' log cleared.</p></div>';
        }

        // --- Refresh Action ---
        if (isset($_POST["refresh_log_{$type}"])) {
            echo '<div class="notice notice-info is-dismissible"><p>🔁 ' . ucfirst($type) . ' log refreshed.</p></div>';
        }
    }
    // --- END --- FORM HANDLING --------------------------------------
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    // --- START --- SECTION HEADER FOR THIS LOG TYPE -----------------
    // ----------------------------------------------------------------
    echo '<h3>📂 ' . ucfirst($type) . ' Events</h3>';
    // --- END --- SECTION HEADER -------------------------------------
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    // --- START --- ACTION BUTTONS (Download, Clear, Refresh) --------
    // ----------------------------------------------------------------
    echo '<form method="post" style="margin-bottom: 1em;">';
        wp_nonce_field("wgpt_log_{$type}_nonce");
        echo '<input type="hidden" name="log_type" value="' . esc_attr($type) . '" />';

        // --- START --- Download Button ---
        submit_button('📤 Download Log', 'secondary', "download_log_{$type}", false);
        // --- END --- Download Button ---

        // --- START --- Clear Button ---
        submit_button('🧹 Clear Log', 'delete', "clear_log_{$type}", false);
        // --- END --- Clear Button ---

        // --- START --- Refresh Button ---
        submit_button('🔁 Refresh Preview', 'primary', "refresh_log_{$type}", false);
        // --- END --- Refresh Button ---
    echo '</form>';
    // --- END --- ACTION BUTTONS -------------------------------------
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    // --- START --- LOG CONTENT PREVIEW BLOCK ------------------------
    // ----------------------------------------------------------------
    $entries = wgpt_get_logs($type);

    if (empty($entries)) {
        echo '<p><em>No entries logged for this type.</em></p>';
    } else {
        echo '<pre style="background:#f8f8f8; padding:10px; border:1px solid #ccc; max-height:300px; overflow:auto;">';
        foreach ($entries as $entry) {
            echo esc_html(json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) . "\n";
        }
        echo '</pre>';
    }
    // --- END --- LOG CONTENT PREVIEW --------------------------------
    // ----------------------------------------------------------------

    // --- Divider ---
    echo '<hr style="margin:2em 0;">';
}

// --- END --- LOG TAB DISPLAY ------------------------------------
