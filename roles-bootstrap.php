<?php
// --- START --- TEMP ROLE/CAPABILITY BOOTSTRAP -------------------

// Add custom roles if they don’t exist
if (!get_role('webmaster')) {
    add_role(
        'webmaster',
        'Webmaster',
        [
            'read'                 => true,
            'edit_posts'           => true,
            'manage_options'       => true,
            // Add more caps as needed...
        ]
    );
}

if (!get_role('publisher')) {
    add_role(
        'publisher',
        'Publisher',
        [
            'read'                 => true,
            'edit_posts'           => true,
            'edit_others_posts'    => true,
            'publish_posts'        => true,
            'delete_posts'         => true,
            'manage_categories'    => true,
            // Add more publisher caps...
        ]
    );
}

// You can set up Editor and Administrator role tweaks similarly
// Example: Add new capability to an existing role:
$webmaster = get_role('webmaster');
if ($webmaster && !$webmaster->has_cap('install_plugins')) {
    $webmaster->add_cap('install_plugins');
}

// Log action for audit
error_log('✅ [WebmasterGPT] Roles/capabilities bootstrap executed.');

// --- END --- TEMP ROLE/CAPABILITY BOOTSTRAP ---------------------
