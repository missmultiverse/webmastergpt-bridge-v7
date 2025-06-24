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

//To use it paste this at the cutomGPT: https://missosology.com/wp-json/wgpt/v1/schema and use: x-api-key: 0xteEF2YXTpNnnLOZP8SZDyo


add_action('rest_api_init', function () {
    register_rest_route('wgpt/v1', '/schema', [
        'methods'  => 'GET',
        'callback' => 'wgpt_openapi_schema_handler',
        'permission_callback' => '__return_true', // Publicly readable!
    ]);
});

/**
 * Returns the OpenAPI schema for all available actions.
 *
 * @return array
 */
function wgpt_openapi_schema_handler()
{
    $site_url = get_site_url();

    return [
        "openapi" => "3.1.0",
        "info" => [
            "title" => "WebMasterGPT Bridge5",
            "version" => "1.1.0",
            "description" => "OpenAPI schema for GPT function-calling on this WordPress site (Editor & Publisher actions supported)."
        ],
        "servers" => [
            ["url" => $site_url . "/wp-json/wgpt/v1"]
        ],
        "components" => [
            "schemas" => [
                "edit_post_input" => [
                    "type" => "object",
                    "properties" => [
                        "post_id" => ["type" => "integer", "description" => "ID of the post to edit"],
                        "title" => ["type" => "string", "description" => "New post title (optional)"],
                        "content" => ["type" => "string", "description" => "New post content (optional)"],
                        "status" => [
                            "type" => "string",
                            "description" => "New post status (optional)",
                            "enum" => ["publish", "draft", "pending", "private"]
                        ]
                    ],
                    "required" => ["post_id"]
                ],
                "delete_post_input" => [
                    "type" => "object",
                    "properties" => [
                        "post_id" => ["type" => "integer", "description" => "ID of the post to delete"]
                    ],
                    "required" => ["post_id"]
                ],
                "get_post_input" => [
                    "type" => "object",
                    "properties" => [
                        "post_id" => ["type" => "integer", "description" => "ID of the post to retrieve"]
                    ],
                    "required" => ["post_id"]
                ],
                "list_posts_input" => [
                    "type" => "object",
                    "properties" => [
                        "post_type" => ["type" => "string", "description" => "Post type to query", "default" => "post"],
                        "category" => ["type" => "integer", "description" => "Category ID (optional)"],
                        "author" => ["type" => "integer", "description" => "Author user ID (optional)"],
                        "status" => ["type" => "string", "description" => "Post status", "default" => "publish"],
                        "limit" => ["type" => "integer", "description" => "Max results (default 10, max 50)", "default" => 10],
                        "offset" => ["type" => "integer", "description" => "Pagination offset", "default" => 0]
                    ]
                ],
                "upload_media_input" => [
                    "type" => "object",
                    "properties" => [
                        "file_url" => ["type" => "string", "description" => "URL of the media file to upload"],
                        "file_name" => ["type" => "string", "description" => "Desired filename (with extension)"]
                    ],
                    "required" => ["file_url", "file_name"]
                ],
                "set_post_status_input" => [
                    "type" => "object",
                    "properties" => [
                        "post_id" => ["type" => "integer", "description" => "ID of the post to update"],
                        "status" => [
                            "type" => "string",
                            "description" => "New post status",
                            "enum" => ["publish", "draft", "pending", "private"]
                        ]
                    ],
                    "required" => ["post_id", "status"]
                ],
                "gpt_create_post_input" => [
                    "type" => "object",
                    "properties" => [
                        "post_title" => ["type" => "string", "description" => "Title of the new post"],
                        "post_content" => ["type" => "string", "description" => "Content of the new post"],
                        "post_status" => [
                            "type" => "string",
                            "description" => "Status of the new post",
                            "enum" => ["publish", "draft", "pending", "private"]
                        ]
                    ],
                    "required" => ["post_title", "post_content"]
                ],
                "gpt_publish_input" => [
                    "type" => "object",
                    "properties" => [
                        "post_id" => ["type" => "integer", "description" => "ID of the post to publish"]
                    ],
                    "required" => ["post_id"]
                ],
                "gpt_manage_dashboard_input" => [
                    "type" => "object",
                    "properties" => [
                        "dashboard_id" => [
                            "type" => "integer",
                            "description" => "ID of the dashboard to manage"
                        ]
                    ],
                    "required" => ["dashboard_id"]
                ],
                "gpt_execute_action_input" => [
                    "type" => "object",
                    "properties" => [
                        "action" => [
                            "type" => "string",
                            "description" => "Action to be executed"
                        ]
                    ],
                    "required" => ["action"]
                ],
                "gpt_read_logs_input" => [
                    "type" => "object",
                    "properties" => [
                        "log_id" => [
                            "type" => "integer",
                            "description" => "ID of the log to read"
                        ]
                    ],
                    "required" => ["log_id"]
                ],
                "gpt_sync_identity_input" => [
                    "type" => "object",
                    "properties" => [
                        "uuid" => [
                            "type" => "string",
                            "description" => "UUID for identity sync"
                        ]
                    ],
                    "required" => ["uuid"]
                ],
                "gpt_export_data_input" => [
                    "type" => "object",
                    "properties" => [
                        "data_type" => [
                            "type" => "string",
                            "description" => "Type of data to export"
                        ]
                    ],
                    "required" => ["data_type"]
                ],
                "gpt_list_universal_actions_output" => [
                    "type" => "object",
                    "properties" => [
                        "actions" => [
                            "type" => "array",
                            "items" => [
                                "type" => "string",
                                "enum" => [
                                    "gpt_create_post",
                                    "gpt_publish",
                                    "gpt_upload_media",
                                    "gpt_set_post_status",
                                    "gpt_edit_post",
                                    "gpt_delete_post",
                                    "gpt_list_posts",
                                    "gpt_get_post",
                                    "gpt_manage_dashboard",
                                    "gpt_execute_action",
                                    "gpt_read_logs",
                                    "gpt_sync_identity",
                                    "gpt_export_data"
                                ],
                                "description" => "List of available actions"
                            ]
                        ]
                    ]
                ],
                "generic_action_response" => [
                    "type" => "object",
                    "properties" => [
                        "success" => ["type" => "boolean", "description" => "True if action succeeded"],
                        "post_id" => ["type" => "integer", "description" => "Affected post ID (if relevant)"],
                        "post" => [
                            "type" => "object",
                            "description" => "Full post object (get_post)",
                            "properties" => [
                                "ID" => ["type" => "integer"],
                                "title" => ["type" => "string"],
                                "content" => ["type" => "string"],
                                "status" => ["type" => "string"],
                                "author" => ["type" => "integer"],
                                "date" => ["type" => "string"],
                                "link" => ["type" => "string"]
                            ]
                        ],
                        "posts" => [
                            "type" => "array",
                            "items" => [
                                "type" => "object",
                                "properties" => [
                                    "ID" => ["type" => "integer"],
                                    "title" => ["type" => "string"],
                                    "status" => ["type" => "string"],
                                    "date" => ["type" => "string"],
                                    "author" => ["type" => "integer"]
                                ]
                            ],
                            "description" => "List of posts (list_posts)"
                        ],
                        "attachment_id" => ["type" => "integer", "description" => "ID of uploaded media"],
                        "url" => ["type" => "string", "description" => "URL to the new resource"],
                        "error" => [
                            "type" => "object",
                            "description" => "Error details",
                            "properties" => [
                                "code" => ["type" => "string"],
                                "message" => ["type" => "string"]
                            ]
                        ]
                    ]
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
            "/action" => [
                "post" => [
                    "operationId" => "wgpt_action",
                    "summary" => "Perform an Editor/Publisher action",
                    "description" => "Submit an action for GPT editorial workflow. The 'action' field selects the operation. Payload must match schema for that action.",
                    "requestBody" => [
                        "required" => true,
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "action" => [
                                            "type" => "string",
                                            "enum" => [
                                                "edit_post",
                                                "delete_post",
                                                "get_post",
                                                "list_posts",
                                                "upload_media",
                                                "set_post_status",
                                                "gpt_manage_dashboard",
                                                "gpt_execute_action",
                                                "gpt_read_logs",
                                                "gpt_sync_identity",
                                                "gpt_export_data",
                                                "gpt_create_post",
                                                "gpt_publish",
                                                "gpt_list_universal_actions"
                                            ],
                                            "description" => "Name of the action to perform"
                                        ],
                                        "payload" => [
                                            "oneOf" => [
                                                ["$ref" => "#/components/schemas/edit_post_input"],
                                                ["$ref" => "#/components/schemas/delete_post_input"],
                                                ["$ref" => "#/components/schemas/get_post_input"],
                                                ["$ref" => "#/components/schemas/list_posts_input"],
                                                ["$ref" => "#/components/schemas/upload_media_input"],
                                                ["$ref" => "#/components/schemas/set_post_status_input"],
                                                ["$ref" => "#/components/schemas/gpt_create_post_input"],
                                                ["$ref" => "#/components/schemas/gpt_publish_input"],
                                                ["$ref" => "#/components/schemas/gpt_list_universal_actions_output"],
                                                ["$ref" => "#/components/schemas/gpt_manage_dashboard_input"],
                                                ["$ref" => "#/components/schemas/gpt_execute_action_input"],
                                                ["$ref" => "#/components/schemas/gpt_read_logs_input"],
                                                ["$ref" => "#/components/schemas/gpt_sync_identity_input"],
                                                ["$ref" => "#/components/schemas/gpt_export_data_input"]
                                            ]
                                        ]
                                    ],
                                    "required" => ["action", "payload"]
                                ]
                            ]
                        ]
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Action performed successfully",
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "$ref" => "#/components/schemas/generic_action_response"
                                    ]
                                ]
                            ]
                        ],
                        "400" => ["description" => "Bad request"],
                        "401" => ["description" => "Unauthorized"],
                        "403" => ["description" => "Forbidden"],
                        "404" => ["description" => "Not found"],
                        "500" => ["description" => "Internal server error"]
                    ]
                ]
            ]
        ]
    ];
}

// --- END --- SCHEMA ENDPOINT -----------------------------------


