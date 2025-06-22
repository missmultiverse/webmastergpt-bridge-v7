<?php
/**
 * File: agents.php
 * Purpose: Registers and displays known GPT agents with identity, roles, capabilities.
 */

// ----------------------------------------------------------------
// --- START --- AGENT REGISTRY HOOK ------------------------------
// ----------------------------------------------------------------

function wgpt_get_registered_agents(): array {
    return apply_filters('wgpt_registered_agents', []);
}

// --- END --- AGENT REGISTRY HOOK -------------------------------


// ----------------------------------------------------------------
// --- START --- REGISTER STATIC GPT AGENTS -----------------------
// ----------------------------------------------------------------

add_filter('wgpt_registered_agents', function ($agents) {
    $gpt_users = ['WebMaster.GPT']; // Add more GPT usernames here in the future

    foreach ($gpt_users as $login) {
        $user = get_user_by('login', $login);
        if ($user instanceof WP_User && $user->exists()) {
            $agents[$user->ID] = [
                'label'             => $user->display_name ?: $user->user_login,
                'email'             => $user->user_email,
                'roles'             => $user->roles,
                'capabilities'      => array_keys(array_filter($user->allcaps ?? [])),
                'capability_source' => 'wordpress',
                'notes'             => 'Auto-registered from GPT user account',
            ];
        }
    }

    return $agents;
});

// --- END --- REGISTER STATIC GPT AGENTS -------------------------


// ----------------------------------------------------------------
// --- START --- AGENT TAB RENDER HOOK ----------------------------
// ----------------------------------------------------------------

add_action('wgpt_render_tab_agents', function () {
    $agents = wgpt_get_registered_agents();
    $search = sanitize_text_field($_GET['cap_search'] ?? '');
    $role_filter = sanitize_text_field($_GET['role'] ?? '');

    echo '<p>Below is a list of all registered GPT agents and their properties. This table is useful for verifying identity scope, role bindings, and access logic.</p>';

    echo '<form method="get" style="margin-bottom:1em;">';
    echo '<input type="hidden" name="page" value="webmastergpt" />';
    echo '<input type="hidden" name="tab" value="agents" />';
    echo '<input type="text" name="cap_search" placeholder="🔍 Search Capability..." value="' . esc_attr($search) . '" style="margin-right:1em;" />';
    echo '<select name="role">';
    echo '<option value="">🔘 Filter by Role</option>';
    foreach (wp_roles()->roles as $slug => $role) {
        $selected = ($slug === $role_filter) ? 'selected' : '';
        echo "<option value='" . esc_attr($slug) . "' $selected>" . esc_html($role['name']) . "</option>";
    }
    echo '</select> ';
    submit_button('Apply Filter', 'secondary', 'submit', false);
    echo '</form>';

    if (empty($agents)) {
        echo '<div class="notice notice-warning"><p>No GPT agents registered. Add them using <code>add_filter(\"wgpt_registered_agents\", ...)</code>.</p></div>';
        return;
    }

    echo '<table class="widefat striped">';
    echo '<thead><tr><th>Agent ID</th><th>Label</th><th>Email</th><th>Roles</th><th>Capabilities</th><th>Capability Source</th><th>Notes</th></tr></thead>';
    echo '<tbody>';

    foreach ($agents as $id => $agent) {
        $label = esc_html($agent['label'] ?? '');
        $email = esc_html($agent['email'] ?? '');
        $roles = !empty($agent['roles']) ? $agent['roles'] : [];
        $caps  = !empty($agent['capabilities']) ? array_map('esc_html', $agent['capabilities']) : [];
        $source = esc_html($agent['capability_source'] ?? 'plugin');
        $notes = esc_html($agent['notes'] ?? '');

        $caps_scrollable = '<div style="max-height:200px; overflow:auto; background:#fff; padding:0.5em; border:1px solid #ccc;"><ul style="margin:0; padding-left:1.25em;">';
        foreach ($caps as $cap) {
            $caps_scrollable .= '<li><code>' . $cap . '</code></li>';
        }
        $caps_scrollable .= '</ul></div>';

        echo "<tr>
            <td><code>$id</code></td>
            <td>$label</td>
            <td><a href='mailto:$email'>$email</a></td>
            <td>";
        
        // --- Defensive Role Display ---
        if (is_array($roles)) {
            $role_flat = [];
            foreach ($roles as $role) {
                if (is_array($role)) {
                    $role_flat[] = implode('|', array_map('strval', $role));
                } else {
                    $role_flat[] = strval($role);
                }
            }
            echo esc_html(implode(', ', $role_flat));
        } else {
            echo esc_html(strval($roles));
        }

        echo "</td>
            <td>$caps_scrollable</td>
            <td>
                <select name='agent_config[$id][capability_source]'>
                    <option value='wordpress'" . selected($source, 'wordpress', false) . ">WordPress Roles</option>
                    <option value='plugin'" . selected($source, 'plugin', false) . ">Plugin Only</option>
                </select>
            </td>
            <td>$notes</td>
        </tr>";
    }

    echo '</tbody></table>';
});

// --- END --- AGENT TAB RENDER HOOK ------------------------------

