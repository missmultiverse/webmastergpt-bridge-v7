<?php
/**
 * File: define-user-identity-resolver.php
 * PReviously named IDENTITY.php
 * Purpose: Provides identity resolution logic for GPT agents using API keys or logged-in users.
 *
 * 🛠️ Developer Note:
 * This file must define wgpt_get_gpt_user_identity() only ONCE.
 * Do NOT duplicate this function in other plugin files (e.g., agents.php).
 */

// ----------------------------------------------------------------
// --- START --- GPT USER IDENTITY RESOLVER -----------------------
// ----------------------------------------------------------------

/**
 * Resolves the current GPT agent to a WP_User object.
 * Supports both API key header auth and logged-in fallback.
 *
 * @return WP_User|false
 */
if (!function_exists('wgpt_get_gpt_user_identity')) {
    function wgpt_get_gpt_user_identity()
    {
        // ✅ Ensure pluggable functions are loaded
        if (!function_exists('wp_get_current_user')) {
            require_once ABSPATH . 'wp-includes/pluggable.php';
        }

        // 1. Check for API key in headers
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $headers = array_change_key_case($headers, CASE_LOWER);

        if (isset($headers['x-api-key'])) {
            $api_key = trim($headers['x-api-key']);
            $map = [
                '0xteEF2YXTpNnnLOZP8SZDyo' => 2381, // <-- Use INT user ID!
            ];

            if (isset($map[$api_key])) {
                $user_id = (int) $map[$api_key];
                $user = get_user_by('id', $user_id);
                if ($user instanceof WP_User && $user->exists()) {
                    return $user;
                }
            }
        }

        // 2. Fallback to current WP user (if logged in)
        $current = wp_get_current_user();
        if ($current instanceof WP_User && $current->exists()) {
            return $current;
        }

        return false;
    }
}

// --- END --- GPT USER IDENTITY RESOLVER -------------------------



