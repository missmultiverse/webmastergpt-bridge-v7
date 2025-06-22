<?php
/**
 * File: tab-tools.php
 * Purpose: Admin interface for GPT identity and capability tools.
 *
 * 🛠️ Developer Note:
 * - All buttons post to admin-post with nonce protection
 * - Output only UI; logic handled in tools.php
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


echo '<h2>🛠 Admin Tools</h2>';
echo '<p>This section lets you sync GPT identities, refresh capabilities, and export debug info.</p>';

// ----------------------------------------------------------------
// --- START --- BUTTON: Force GPT Identity & Role Sync -----------
// ----------------------------------------------------------------
echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-bottom: 1em;">';
    wp_nonce_field('wgpt_tools_nonce');
    echo '<input type="hidden" name="action" value="wgpt_sync_identity">';
    submit_button('🔄 Sync GPT Identity & Roles', 'primary');
echo '</form>';
// --- END --- BUTTON: Identity & Role Sync ------------------------


// ----------------------------------------------------------------
// --- START --- BUTTON: Sync Capabilities to Admin ---------------
// ----------------------------------------------------------------
echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-bottom: 1em;">';
    wp_nonce_field('wgpt_tools_nonce');
    echo '<input type="hidden" name="action" value="wgpt_sync_capabilities">';
    submit_button('🔐 Sync Capabilities to Admin Role', 'secondary');
echo '</form>';
// --- END --- BUTTON: Sync Capabilities ---------------------------


// ----------------------------------------------------------------
// --- START --- BUTTON: Export Capability Map ---------------------
// ----------------------------------------------------------------
echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('wgpt_tools_nonce');
    echo '<input type="hidden" name="action" value="wgpt_export_capabilities">';
    submit_button('📤 Export Capability Map', 'secondary');
echo '</form>';
// --- END --- BUTTON: Export --------------------------------------

