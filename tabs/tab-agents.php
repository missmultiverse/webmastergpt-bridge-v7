<?php
/**
 * File: tab-agents.php
 * Purpose: Displays GPT agents with capability and role filtering.
 */

/**
 * 🛠️ Developer Note:
 * This file is part of the WebmasterGPT Bridge plugin and MUST adhere to the following
 * hyper-descriptive structural and formatting conventions at all times:
 */

// ----------------------------------------------------------------
// --- START --- FETCH AGENTS -------------------------------------
// ----------------------------------------------------------------

$agents = apply_filters('wgpt_registered_agents', []);
$search = sanitize_text_field($_GET['cap_search'] ?? '');
$role_filter = sanitize_text_field($_GET['role'] ?? '');

echo '<h2>👥 Registered GPT Agents</h2>';

// --- END --- FETCH AGENTS ---------------------------------------


// ----------------------------------------------------------------
// --- START --- FILTER FORM --------------------------------------
// ----------------------------------------------------------------

echo '<form method="get" style="margin-bottom: 1em;">
    <input type="hidden" name="page" value="webmastergpt" />
    <input type="hidden" name="tab" value="agents" />
    <input type="text" name="cap_search" placeholder="🔍 Search Capability..." value="' . esc_attr($search) . '" style="margin-right: 1em;" />
    <select name="role">
        <option value="">🔘 Filter by Role</option>';

foreach (wp_roles()->roles as $slug => $role) {
    $selected = selected($slug, $role_filter, false);
    echo "<option value='" . esc_attr($slug) . "' $selected>" . esc_html($role['name']) . "</option>";
}

echo '</select>';
submit_button('Apply Filter', 'secondary', 'submit', false);
echo '</form>';

// --- END --- FILTER FORM ----------------------------------------


// ----------------------------------------------------------------
// --- START --- AGENTS TABLE -------------------------------------
// ----------------------------------------------------------------

if (empty($agents)) {
    echo '<p>No GPT agents registered yet.</p>';
} else {
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>Agent ID</th><th>Name / Email</th><th>Roles</th><th>Capabilities</th><th>Status</th></tr></thead>';
    echo '<tbody>';

    foreach ($agents as $agent_id => $agent_info) {
        // --- PATCH: Ensure scalar user ID and proper structure ---
        if (!is_scalar($agent_id)) {
            echo '<tr><td colspan="5">⚠️ Missing user with non-scalar ID (bad registration: type=' . gettype($agent_id) . ')</td></tr>';
            continue;
        }

        $user = get_user_by('id', $agent_id);
        if (!$user) {
            echo '<tr><td colspan="5">⚠️ Missing user with ID ' . esc_html((string)$agent_id) . '</td></tr>';
            continue;
        }

        // Use roles from agent info, fallback to WP user object if not present
        $roles = !empty($agent_info['roles']) && is_array($agent_info['roles']) ? $agent_info['roles'] : (is_array($user->roles) ? $user->roles : []);
        $caps  = !empty($agent_info['capabilities']) && is_array($agent_info['capabilities']) ? $agent_info['capabilities'] : (array_keys(array_filter($user->allcaps ?? [])));

        // Filtering logic
        $caps_matched = !$search || preg_grep('/' . preg_quote($search, '/') . '/i', $caps);
        $role_matched = !$role_filter || in_array($role_filter, $roles, true);

        if (!$caps_matched || !$role_matched) {
            continue;
        }

        // Capability list scrollable
        $cap_list = '<div style="max-height:160px; overflow:auto;"><ul style="margin:0; padding-left:1.25em;">';
        foreach ($caps as $cap) {
            $cap_list .= '<li><code>' . esc_html($cap) . '</code></li>';
        }
        $cap_list .= '</ul></div>';

        echo '<tr>';
        echo '<td>' . esc_html($user->ID) . '</td>';
        echo '<td>' . esc_html($user->display_name) . '<br><small>' . esc_html($user->user_email) . '</small></td>';
        echo '<td>' . esc_html(implode(', ', $roles)) . '</td>';
        echo '<td>' . $cap_list . '</td>';
        echo '<td>' . (user_can($user, 'read') ? '✅ Active' : '❌ Inactive') . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

// --- END --- AGENTS TABLE ---------------------------------------


// ----------------------------------------------------------------
// --- START --- RESYNC BUTTON ------------------------------------
// ----------------------------------------------------------------

echo '<form method="post" style="margin-top: 1.5em;">';
wp_nonce_field('wgpt_resync_agents', 'wgpt_resync_agents_nonce');
submit_button('🔁 Force Re-register GPT Agents', 'secondary', 'submit', false);
echo '</form>';

if (
    current_user_can('manage_options') &&
    isset($_POST['wgpt_resync_agents_nonce']) &&
    check_admin_referer('wgpt_resync_agents', 'wgpt_resync_agents_nonce')
) {
    if (function_exists('wgpt_register_default_agents')) {
        wgpt_register_default_agents();
        echo '<div class="notice notice-success is-dismissible"><p>✅ GPT agents re-registered successfully.</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>⚠️ Function wgpt_register_default_agents() not found.</p></div>';
    }
}

// --- END --- RESYNC BUTTON --------------------------------------
