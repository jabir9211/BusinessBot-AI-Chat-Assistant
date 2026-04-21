<?php
// Prevent direct access
defined('ABSPATH') || exit;

// Register the admin menu
add_action('admin_menu', 'businessbot_chat_assist_add_admin_menu');

function businessbot_chat_assist_add_admin_menu() {
    add_menu_page(
        __('BusinessBot', 'businessbot-ai-chat'),
        __('BusinessBot ', 'businessbot-ai-chat'),
        'manage_options',
        'businessbot',
        'businessbot_chat_assist_settings_page',
        'dashicons-smiley',
        100
    );

    add_submenu_page(
        'businessbot',
        __('Business Info', 'businessbot-ai-chat'),
        __('Details', 'businessbot-ai-chat'),
        'manage_options',
        'businessbot-details',
        'businessbot_chat_assist_settings_page' // Reuses main page callback — OK
    );

    add_submenu_page(
        'businessbot',
        __('Settings', 'businessbot-ai-chat'),
        __('Settings', 'businessbot-ai-chat'),
        'manage_options',
        'businessbot-settings',
        'businessbot_chat_assist_front_settings_page'
    );

    add_submenu_page(
        'businessbot',
        __('Integration Settings', 'businessbot-ai-chat'),
        __('Integration', 'businessbot-ai-chat'),
        'manage_options',
        'businessbot-integration',
        'businessbot_chat_assist_integration_page'
    );

    add_submenu_page(
        'businessbot',
        __('How to Use', 'businessbot-ai-chat'),
        __('How to Use', 'businessbot-ai-chat'),
        'manage_options',
        'businessbot-how-to-use',
        'businessbot_chat_assist_how_to_use_page'
    );

    add_submenu_page(
        'businessbot',
        __('Logs', 'businessbot-ai-chat'),
        __('Logs', 'businessbot-ai-chat'),
        'manage_options',
        'businessbot-logs',
        'businessbot_chat_assist_logs_page'
    );
}
