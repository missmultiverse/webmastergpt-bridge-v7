<?php
/**
 * File: tab-access.php
 * Purpose: Displays the role-to-capability matrix with filtering and capability search.
 * Supports real-time filtering, role-specific viewing, and capability sync.
 * Fully functional with both legacy and WP-native capability systems.
 */

// Optional: Debug marker
// error_log('WGPT: Entered tab-access.php');
echo '<!-- Access tab loaded -->';

/**
 * 🛠️ Developer Note:
 * This file is part of the WebmasterGPT Bridge plugin and MUST adhere to the following
 * hyper-descriptive structural and formatting conventions at all times.
 *
 * [ ... keep your developer note here ... ]
 */

// ----------------------------------------------------------------
// --- START --- SECURITY GUARD -----------------------------------
// ----------------------------------------------------------------
if (!defined('ABSPATH'))
  exit;
// --- END --- SECURITY GUARD -------------------------------------

// ----------------------------------------------------------------
// --- START --- LEGACY FALLBACK: CAPABILITY MAP ------------------
// ----------------------------------------------------------------
if (!function_exists('gpt_get_capability_map')) {
  function gpt_get_capability_map(): array
  {
    return [
      'gpt_manage_dashboard' => 'Access GPT dashboard',
      'gpt_execute_action' => 'Execute GPT REST actions',
      'gpt_read_logs' => 'View GPT system logs',
      'gpt_sync_identity' => 'Sync identities and roles',
      'gpt_export_data' => 'Export plugin-related data',
    ];
  }
}
// --- END --- LEGACY FALLBACK: CAPABILITY MAP --------------------

// ----------------------------------------------------------------
// --- START --- ACCESS TAB CONTENT -------------------------------
// ----------------------------------------------------------------

$roles = wp_roles()->roles;
$map = gpt_get_capability_map();
$selected_role = isset($_GET['wgpt_role']) ? sanitize_key($_GET['wgpt_role']) : '';

?>
<div class="wrap">
  <h2>🧠 GPT Access Control Matrix</h2>

  <!-- Filter UI -->
  <form method="get" style="margin-bottom: 1em;">
    <input type="hidden" name="page" value="webmastergpt">
    <input type="hidden" name="tab" value="access">

    <label for="wgpt_role"><strong>Role:</strong></label>
    <select name="wgpt_role" id="wgpt_role" onchange="this.form.submit()">
      <option value="">All Roles</option>
      <?php foreach ($roles as $slug => $data): ?>
        <option value="<?php echo esc_attr($slug); ?>" <?php selected($slug, $selected_role); ?>>
          <?php echo esc_html($data['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    &nbsp;&nbsp;
    <label for="cap_search"><strong>Search:</strong></label>
    <input type="text" id="cap_search" placeholder="Type to filter capabilities...">
  </form>

  <!-- Re-Sync Capabilities Button -->
  <form method="post">
    <?php wp_nonce_field('wgpt_sync_caps', 'wgpt_sync_caps_nonce'); ?>
    <input type="hidden" name="wgpt_role_resync" value="<?php echo esc_attr($selected_role); ?>">
    <button type="submit" class="button">🔁 Re-Sync Capabilities to Role</button>
  </form>

  <hr>

  <!-- Capability Table -->
  <table class="widefat striped" id="cap_matrix">
    <thead>
      <tr>
        <th>Action</th>
        <th>Capability</th>
        <th>Granted Roles</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($map as $action => $cap):
        $granted_roles = [];
        foreach ($roles as $slug => $details) {
          if ($selected_role && $selected_role !== $slug)
            continue;
          $role_obj = get_role($slug);
          if ($role_obj && $role_obj->has_cap($cap)) {
            $granted_roles[] = $details['name'];
          }
        }
        if (!$selected_role || !empty($granted_roles)):
          ?>
          <tr>
            <td><code><?php echo esc_html($action); ?></code></td>
            <td><code><?php echo esc_html($cap); ?></code></td>
            <td><?php echo implode(', ', $granted_roles); ?></td>
          </tr>
        <?php endif; endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Live Search Script -->
<script>
  document.getElementById('cap_search').addEventListener('input', function () {
    const term = this.value.toLowerCase();
    const rows = document.querySelectorAll('#cap_matrix tbody tr');
    rows.forEach(row => {
      row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
  });
</script>
<?php
// --- END --- ACCESS TAB CONTENT ---------------------------------
?>