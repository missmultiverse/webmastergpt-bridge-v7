<?php
/**
 * File: admin-ui.php
 * Purpose: Provides the main admin menu and tab navigation for the WebmasterGPT Bridge plugin.
 * Each tab loads content from a separate section via hook-based rendering (e.g., tools.php, agents.php).
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
// --- START --- SECURITY GUARD -----------------------------------
// ----------------------------------------------------------------
if (!defined('ABSPATH')) {
    exit;
}
// --- END --- SECURITY GUARD -------------------------------------


// ----------------------------------------------------------------
// --- START --- REGISTER ADMIN MENU ------------------------------
// ----------------------------------------------------------------
add_action('admin_menu', function () {
    add_menu_page(
        'WebmasterGPT',
        'WebmasterGPT',
        'manage_options',
        'webmastergpt',
        'wgpt_render_admin_ui',
        'dashicons-admin-users',
        70
    );
});
// --- END --- REGISTER ADMIN MENU --------------------------------


// ----------------------------------------------------------------
// --- START --- REGISTER TAB RENDERERS ---------------------------
// ----------------------------------------------------------------

add_action('wgpt_render_tab_tools', function () {
    include __DIR__ . '/tabs/tab-tools.php';
});

add_action('wgpt_render_tab_access', function () {
    include __DIR__ . '/tabs/tab-access.php';
});

add_action('wgpt_render_tab_agents', function () {
    include __DIR__ . '/tabs/tab-agents.php';
});

add_action('wgpt_render_tab_logs', function () {
    include __DIR__ . '/tabs/tab-logs.php';
});

// --- END --- REGISTER TAB RENDERERS -----------------------------



// ----------------------------------------------------------------
// --- START --- RENDER ADMIN INTERFACE ---------------------------
// ----------------------------------------------------------------
function wgpt_render_admin_ui()
{
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'tools';

    echo '<div class="wrap">';
    echo '<h1>🤖 WebmasterGPT Bridge</h1>';

    // --- Navigation Tabs ---
    echo '<h2 class="nav-tab-wrapper">';
    foreach ([
        'tools'   => '🛠️ Tools',
        'access'  => '🔐 Access',
        'agents'  => '👥 Agents',
        'logs'    => '📜 Logs',
    ] as $tab_key => $tab_label) {
        $class = ($tab_key === $active_tab) ? 'nav-tab nav-tab-active' : 'nav-tab';
        echo '<a href="?page=webmastergpt&tab=' . esc_attr($tab_key) . '" class="' . esc_attr($class) . '">' . esc_html($tab_label) . '</a>';
    }
    echo '</h2>';

    // --- Load Tab Content via Hook ---
    do_action('wgpt_render_tab_' . sanitize_key($active_tab));

    echo '</div>';
}
// --- END --- RENDER ADMIN INTERFACE -----------------------------
