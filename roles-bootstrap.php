<?php
// --- START --- TEMP ROLE/CAPABILITY BOOTSTRAP -------------------

// Add custom roles if they don’t exist
if (!get_role('webmaster')) {
    add_role(
        'webmaster',
        'WebMaster',
        [
            'read'                     => true,
            'edit_posts'               => true,
            'manage_options'           => true,
            'update_document_content'  => true, // Custom capability for document updates
            'edit_others_posts'        => true,
            'publish_posts'            => true,
            'delete_posts'             => true,
            'delete_others_posts'      => true,
            'edit_published_posts'     => true,
            'delete_published_posts'   => true,
            'manage_categories'        => true,
            'upload_files'             => true,
            'read_private_posts'       => true,
            'edit_private_posts'       => true,
            'publish_pages'            => true,
            'edit_pages'               => true,
            'edit_others_pages'        => true,
            'delete_pages'             => true,
            'delete_others_pages'      => true,
            'manage_options'           => true,
            'moderate_comments'        => true,
            'edit_theme_options'       => true,
            'manage_widgets'           => true,
            'install_plugins'          => true,
            'activate_plugins'         => true,
            'update_plugins'           => true,
            'delete_plugins'           => true,
            'manage_users'             => true,
            'edit_users'               => true,
            'create_users'             => true,
            'delete_users'             => true,
            'list_users'               => true,
            'manage_woocommerce'       => true,
            'manage_woocommerce_settings' => true,
            'import'                   => true,
            'export'                   => true,
            'view_site_health_checks'  => true,
            'update_core'              => true,
            'delete_themes'            => true,
            'install_themes'           => true,
            // Add more caps as needed...
        ]
    );
}

if (!get_role('publisher')) {
    add_role(
        'publisher',
        'Publisher',
        [
            'read'                   => true,
            'edit_posts'             => true,
            'edit_others_posts'      => true,
            'publish_posts'          => true,
            'delete_posts'           => true,
            'manage_categories'      => true,
            'edit_published_posts'   => true,
            'delete_published_posts' => true,
            'edit_pages'             => true,
            'edit_others_pages'      => true,
            'publish_pages'          => true,
            'delete_pages'           => true,
            'delete_others_pages'    => true,
            'upload_files'           => true,
            'read_private_posts'     => true,
            'edit_private_posts'     => true,
            'moderate_comments'      => true,
            'manage_woocommerce'     => true,
            'manage_woocommerce_settings' => true,
            'import'                 => true,
            'export'                 => true,
            'view_site_health_checks'=> true,
            'update_core'            => true,
            // Add more publisher caps as needed...
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
error_log('✅ [WebmasterGPT] Roles and capabilities have been successfully initialized.');

// --- END --- TEMP ROLE/CAPABILITY BOOTSTRAP ---------------------
?>

