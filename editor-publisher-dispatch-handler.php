<?php
/**
 * File: editor-publisher-dispatch-handler.php
 * Purpose: Adds REST API action handlers for Editor & Publisher GPT agents.
 *
 * 🛠️ Developer Note:
 * All handlers are modular, capability-checked, and return REST-compatible arrays.
 * Register these via your wgpt_handle_action filter in the plugin dispatcher.
 * Add more handlers as your editorial workflow expands!
 */

// ┌──────────────────────────────────────────────────────────────┐
// │ --- START --- EDITOR & PUBLISHER REST HANDLERS --------------│
// └──────────────────────────────────────────────────────────────┘

/**
 * Handle editing a post by ID
 * 
 * @param array $payload [post_id, title, content, status, categories, tags]
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_edit_post($payload, $user) {
    $post_id = intval($payload['post_id'] ?? 0);
    $title   = sanitize_text_field($payload['post_title'] ?? '');
    $content = wp_kses_post($payload['post_content'] ?? '');
    $status  = sanitize_text_field($payload['status'] ?? 'publish');
    $categories = $payload['categories'] ?? [];
    $tags = $payload['tags'] ?? '';

    // Check if post exists
    if (!$post_id || !get_post($post_id)) {
        return new WP_Error('invalid_post', 'Post not found.', ['status' => 404]);
    }

    // Ensure the user has permissions to edit the post
    if (!user_can($user, 'edit_post', $post_id)) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }

    // Prepare the post data for update
    $update = [
        'ID'           => $post_id,
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => $status,
    ];

    // Update the post content and status
    $result = wp_update_post($update, true);

    // Handle errors from wp_update_post
    if (is_wp_error($result)) {
        return new WP_Error('update_failed', 'Could not update post.', ['status' => 500]);
    }

    // Update categories if provided
    if (!empty($categories)) {
        // Ensure categories are valid by converting them to term IDs
        $category_ids = [];
        foreach ($categories as $category) {
            $term = get_term_by('name', $category, 'category');
            if ($term) {
                $category_ids[] = $term->term_id;
            }
        }

        if (!empty($category_ids)) {
            wp_set_post_categories($post_id, $category_ids); // Set categories using valid term IDs
        } else {
            return new WP_Error('invalid_category', 'One or more categories are invalid.', ['status' => 400]);
        }
    }

    // Update tags if provided
    if (!empty($tags)) {
        wp_set_post_tags($post_id, $tags); // Set tags
    }

    // Return success response with post details
    return [
        'success'  => true,
        'post_id'  => $post_id,
        'post_url' => get_permalink($post_id),
        'status'   => $status,
        'categories' => $categories,
        'tags' => $tags,
    ];
}






/**
 * Handle deleting a post by ID
 * 
 * @param array $payload [post_id]
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_delete_post($payload, $user) {
    $post_id = intval($payload['post_id'] ?? 0);
    if (!$post_id || !get_post($post_id)) {
        return new WP_Error('invalid_post', 'Post not found.', ['status' => 404]);
    }
    if (!user_can($user, 'delete_post', $post_id)) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    $result = wp_trash_post($post_id);
    if (!$result) return new WP_Error('delete_failed', 'Could not delete post.', ['status' => 500]);
    return ['success' => true, 'post_id' => $post_id];
}

/**
 * Handle retrieving a post by ID
 * 
 * @param array $payload [post_id]
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_get_post($payload, $user) {
    $post_id = intval($payload['post_id'] ?? 0);
    $post = get_post($post_id);
    if (!$post) return new WP_Error('invalid_post', 'Post not found.', ['status' => 404]);
    if (!user_can($user, 'edit_post', $post_id)) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    return ['post' => $post];
}

/**
 * Handle retrieving a list of posts
 * 
 * @param array $payload [status, page, per_page]
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_list_posts($payload, $user) {
    $status = sanitize_text_field($payload['status'] ?? 'publish');
    $page = intval($payload['page'] ?? 1);
    $per_page = intval($payload['per_page'] ?? 10);

    $args = [
        'post_status' => $status,
        'paged' => $page,
        'posts_per_page' => $per_page
    ];

    $posts = get_posts($args);
    return ['posts' => $posts, 'count' => count($posts)];
}

/**
 * Handle uploading media via URL
 * 
 * @param array $payload [file_url, file_name]
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_upload_media($payload, $user) {
    if (!user_can($user, 'upload_files')) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    $file_url  = esc_url_raw($payload['file_url'] ?? '');
    $file_name = sanitize_file_name($payload['file_name'] ?? '');
    if (!$file_url || !$file_name) {
        return new WP_Error('invalid_args', 'Missing file_url or file_name.', ['status' => 400]);
    }
    $tmp = download_url($file_url);
    if (is_wp_error($tmp)) return $tmp;

    $file_array = [
        'name'     => $file_name,
        'tmp_name' => $tmp,
    ];
    $id = media_handle_sideload($file_array, 0);
    @unlink($tmp);
    if (is_wp_error($id)) return $id;

    $url = wp_get_attachment_url($id);
    return ['attachment_id' => $id, 'url' => $url];
}

/**
 * Handle setting post status (publish, draft, etc.)
 * 
 * @param array $payload [post_id, status]
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_set_post_status($payload, $user) {
    $post_id = intval($payload['post_id'] ?? 0);
    $status  = $payload['status'] ?? '';
    if (!$post_id || !get_post($post_id)) {
        return new WP_Error('invalid_post', 'Post not found.', ['status' => 404]);
    }
    if (!in_array($status, ['publish', 'draft', 'pending', 'private'])) {
        return new WP_Error('invalid_status', 'Invalid status.', ['status' => 400]);
    }
    if (!user_can($user, 'edit_post', $post_id)) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    $result = wp_update_post(['ID' => $post_id, 'post_status' => $status], true);
    if (is_wp_error($result)) return $result;
    return ['success' => true, 'post_id' => $post_id, 'status' => $status];
}

/**
 * Handle managing the dashboard
 * 
 * @param array $payload
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_manage_dashboard($payload, $user) {
    if (!user_can($user, 'gpt_manage_dashboard')) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    // Dashboard logic here
    return ['success' => true];
}

/**
 * Handle executing a REST action
 * 
 * @param array $payload
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_execute_action($payload, $user) {
    if (!user_can($user, 'gpt_execute_action')) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    // Action execution logic here
    return ['success' => true];
}

/**
 * Handle reading logs
 * 
 * @param array $payload
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_read_logs($payload, $user) {
    if (!user_can($user, 'gpt_read_logs')) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    // Log reading logic here
    return ['success' => true];
}

/**
 * Handle syncing identities
 * 
 * @param array $payload
 * @param WP_User $user
 * @return array|WP_Error
 */
// Check if the sync_identity function is already defined (i.e., from tools.php)
if (!function_exists('wgpt_handle_sync_identity')) {
    // Define the function here or register it for actions
    function wgpt_handle_sync_identity($payload, $user) {
        if (!user_can($user, 'gpt_sync_identity')) {
            return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
        }
        // Identity sync logic here
        return ['success' => true];
    }
}

// Add action hook for sync_identity
add_action('sync_identity_action', 'wgpt_handle_sync_identity', 10, 2);

/**
 * Handle exporting data
 * 
 * @param array $payload
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_export_data($payload, $user) {
    if (!user_can($user, 'gpt_export_data')) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    // Data export logic here
    return ['success' => true];
}

/**
 * Handle creating posts
 * 
 * @param array $payload
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_create_post($payload, $user) {
    if (!user_can($user, 'gpt_create_post')) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }

    // Sanitize and prepare the data for the new post
    $post_title   = sanitize_text_field($payload['post_title'] ?? '');
    $post_content = isset($payload['post_content']) ? wp_kses_post($payload['post_content']) : '';
    $post_status  = sanitize_text_field($payload['post_status'] ?? 'publish');

    // Validate required parameters
    if (empty($post_title) || empty($post_content)) {
        return new WP_Error('invalid_params', 'Post title and content are required.', ['status' => 400]);
    }

    // Prepare the data to insert into the database
    $post_data = [
        'post_title'   => $post_title,
        'post_content' => $post_content,
        'post_status'  => $post_status,
        'post_author'  => $user->ID,
        'post_type'    => 'post',  // Set post type (default to 'post')
    ];

    // Insert the post into the database
    $post_id = wp_insert_post($post_data);

    // Check for errors during the insert
    if (is_wp_error($post_id)) {
        return $post_id;  // Return the error if post creation fails
    }

    // Successfully created the post, return relevant details
    return [
        'success'   => true,
        'post_id'   => $post_id,
        'post_title'=> $post_title,
        'post_link' => get_permalink($post_id), // Return the link to the newly created post
    ];
}

/**
 * Handle publishing posts
 * 
 * @param array $payload
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_publish($payload, $user) {
    if (!user_can($user, 'gpt_publish')) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    // Publishing logic here
    return ['success' => true];
}

/**
 * Handle listing universal actions
 * 
 * @param array $payload
 * @param WP_User $user
 * @return array|WP_Error
 */
function wgpt_handle_list_universal_actions($payload, $user) {
    if (!user_can($user, 'gpt_list_universal_actions')) {
        return new WP_Error('forbidden', 'Insufficient permissions.', ['status' => 403]);
    }
    // List universal actions logic here
    return ['success' => true];
}

// --- END --- ACTION DISPATCHER REGISTRATION ------------------

add_filter('wgpt_handle_action', function($result, $action, $payload, $user) {
    // Log the action being processed
    error_log("Processing action: {$action} for user: {$user->user_login} (ID: {$user->ID})");

    switch ($action) {
        case 'edit_post':
            // Log the payload to ensure the correct data is being passed
            error_log("Payload for edit_post: " . print_r($payload, true));
            
            // Ensure edit post functionality with wp_update_post logic, including categories and tags
            // Handle categories by ensuring they are passed correctly
            if (isset($payload['categories'])) {
                // Convert category names to term IDs
                $category_ids = [];
                foreach ($payload['categories'] as $category) {
                    $term = get_term_by('name', $category, 'category');
                    if ($term) {
                        $category_ids[] = $term->term_id;
                    }
                }
                $payload['categories'] = $category_ids;  // Update categories to term IDs
            }

            // Ensure the edit post functionality with wp_update_post and assign categories and tags
            return wgpt_handle_edit_post($payload, $user);
        
        case 'delete_post':
            error_log("Payload for delete_post: " . print_r($payload, true));
            return wgpt_handle_delete_post($payload, $user);
        
        case 'get_post':
            return wgpt_handle_get_post($payload, $user);
        
        case 'list_posts':
            return wgpt_handle_list_posts($payload, $user);
        
        case 'upload_media':
            return wgpt_handle_upload_media($payload, $user);
        
        case 'set_post_status':
            return wgpt_handle_set_post_status($payload, $user);
        
        case 'gpt_manage_dashboard':
            return wgpt_handle_manage_dashboard($payload, $user);
        
        case 'gpt_execute_action':
            return wgpt_handle_execute_action($payload, $user);
        
        case 'gpt_read_logs':
            return wgpt_handle_read_logs($payload, $user);
        
        case 'gpt_sync_identity':
            return wgpt_handle_sync_identity($payload, $user);
        
        case 'gpt_export_data':
            return wgpt_handle_export_data($payload, $user);
        
        case 'gpt_create_post':
            // Ensure to handle post creation via the updated function
            return wgpt_handle_create_post($payload, $user);
        
        case 'gpt_publish':
            return wgpt_handle_publish($payload, $user);
        
        case 'gpt_list_universal_actions':
            return wgpt_handle_list_universal_actions($payload, $user);
        
        default:
            return $result;
    }
}, 10, 4);

// --- END --- ACTION DISPATCHER REGISTRATION ------------------ 

