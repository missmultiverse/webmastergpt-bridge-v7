<?php
/**
 * File: schema-endpoint.php
 * Purpose: Registers the OpenAI function-calling schema endpoint and handlers.
 *
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
 * 🚨 DO NOT REMOVE THIS DEVELOPER NOTE FROM ANY FILE.
 */

// ┌──────────────────────────────────────────────────────────────┐
// │ --- START --- SCHEMA ENDPOINT -------------------------------│
// └──────────────────────────────────────────────────────────────┘

add_action('rest_api_init', function () {
    register_rest_route('wgpt/v1', '/schema', [
        'methods'  => 'GET',
        'callback' => 'wgpt_openapi_schema_handler',
        'permission_callback' => '__return_true', // Publicly readable!
    ]);
});

function wgpt_openapi_schema_handler() {
    $site_url = get_site_url();

    return [
        "openapi" => "3.1.0",
        "info" => [
            "title" => "WebMasterGPT Bridge",
            "version" => "1.0.0",
            "description" => "OpenAPI schema for GPT function-calling on this WordPress site."
        ],
        "servers" => [
            [ "url" => $site_url . "/wp-json/wgpt/v1" ]
        ],
        "components" => [
            "schemas" => [
                "create_post_input" => [
                    "type" => "object",
                    "properties" => [
                        "title" => [
                            "type" => "string",
                            "description" => "Title of the post"
                        ],
                        "content" => [
                            "type" => "string",
                            "description" => "Content of the post"
                        ],
                        "status" => [
                            "type" => "string",
                            "enum" => ["publish", "draft"],
                            "default" => "publish",
                            "description" => "Post status"
                        ]
                    ],
                    "required" => ["title", "content"]
                ]
            ],
            "securitySchemes" => [
                "ApiKeyAuth" => [
                    "type" => "apiKey",
                    "in" => "header",
                    "name" => "x-api-key"
                ]
            ]
        ],
        "security" => [["ApiKeyAuth" => []]],
        "paths" => [
            "/create_post" => [
                "post" => [
                    "operationId" => "create_post",
                    "summary" => "Create a new WordPress post as the mapped WP user.",
                    "description" => "Creates and publishes a post with title and content. Author is the user mapped to the provided API key.",
                    "requestBody" => [
                        "required" => true,
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    '$ref' => '#/components/schemas/create_post_input'
                                ]
                            ]
                        ]
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Post created",
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object",
                                        "properties" => [
                                            "id" => ["type" => "integer"],
                                            "title" => ["type" => "string"],
                                            "content" => ["type" => "string"],
                                            "status" => ["type" => "string"],
                                            "author" => ["type" => "integer"],
                                            "link" => ["type" => "string"]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        "401" => [ "description" => "Unauthorized" ],
                        "403" => [ "description" => "Forbidden" ],
                        "500" => [ "description" => "Internal server error" ]
                    ]
                ]
            ]
        ]
    ];
}

// --- END --- SCHEMA ENDPOINT -----------------------------------


// ┌──────────────────────────────────────────────────────────────┐
// │ --- START --- CREATE POST ENDPOINT --------------------------│
// └──────────────────────────────────────────────────────────────┘

add_action('rest_api_init', function () {
    register_rest_route('wgpt/v1', '/create_post', [
        'methods'  => 'POST',
        'callback' => 'wgpt_create_post_handler',
        'permission_callback' => '__return_true',
    ]);
});

function wgpt_create_post_handler($request) {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $headers = array_change_key_case($headers, CASE_LOWER);

    if (!isset($headers['x-api-key'])) {
        return new WP_Error('rest_forbidden', 'Missing API key.', ['status' => 401]);
    }

    $api_key = trim($headers['x-api-key']);
    $map = [
        '0xteEF2YXTpNnnLOZP8SZDyo' => 'WebMaster.GPT',
    ];

    if (!isset($map[$api_key])) {
        return new WP_Error('rest_forbidden', 'Invalid API key.', ['status' => 403]);
    }

    $user = get_user_by('login', $map[$api_key]);
    if (!$user || !$user->exists()) {
        return new WP_Error('rest_forbidden', 'User not found.', ['status' => 403]);
    }

    wp_set_current_user($user->ID);

    $params = $request->get_json_params();
    $title = sanitize_text_field($params['title'] ?? '');
    $content = wp_kses_post($params['content'] ?? '');
    $status = sanitize_text_field($params['status'] ?? 'publish');

    if (empty($title) || empty($content)) {
        return new WP_Error('rest_invalid_param', 'Title and content are required.', ['status' => 400]);
    }

    $post_id = wp_insert_post([
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => $status,
        'post_author'  => $user->ID,
    ]);

    if (is_wp_error($post_id)) {
        return new WP_Error('rest_cannot_create', 'Failed to create post.', ['status' => 500]);
    }

    $post_url = get_permalink($post_id);

    return [
        'id'      => $post_id,
        'title'   => $title,
        'content' => $content,
        'status'  => $status,
        'author'  => $user->ID,
        'link'    => $post_url,
    ];
}

// --- END --- CREATE POST ENDPOINT ------------------------------
