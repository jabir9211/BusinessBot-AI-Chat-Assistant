<?php
// Prevent direct access
defined('ABSPATH') || exit;

// Register the admin menu
add_action('admin_menu', 'businessbot_chat_assist_add_admin_menu');

function businessbot_chat_assist_add_admin_menu() {
    add_menu_page(
        __('BusinessBot', 'ai-chat-assistant-for-business'),
        __('BusinessBot ', 'ai-chat-assistant-for-business'),
        'manage_options',
        'businessbot',
        'businessbot_chat_assist_settings_page',
        'dashicons-smiley',
        100
    );

    add_submenu_page(
        'businessbot',
        __('Business Info', 'ai-chat-assistant-for-business'),
        __('Details', 'ai-chat-assistant-for-business'),
        'manage_options',
        'businessbot-details',
        'businessbot_chat_assist_settings_page' // Reuses main page callback — OK
    );

    add_submenu_page(
        'businessbot',
        __('Settings', 'ai-chat-assistant-for-business'),
        __('Settings', 'ai-chat-assistant-for-business'),
        'manage_options',
        'businessbot-settings',
        'businessbot_chat_assist_front_settings_page'
    );

    add_submenu_page(
        'businessbot',
        __('Integration Settings', 'ai-chat-assistant-for-business'),
        __('Integration', 'ai-chat-assistant-for-business'),
        'manage_options',
        'businessbot-integration',
        'businessbot_chat_assist_integration_page'
    );

    add_submenu_page(
        'businessbot',
        __('How to Use', 'ai-chat-assistant-for-business'),
        __('How to Use', 'ai-chat-assistant-for-business'),
        'manage_options',
        'businessbot-how-to-use',
        'businessbot_chat_assist_how_to_use_page'
    );

    add_submenu_page(
        'businessbot',
        __('Logs', 'ai-chat-assistant-for-business'),
        __('Logs', 'ai-chat-assistant-for-business'),
        'manage_options',
        'businessbot-logs',
        'businessbot_chat_assist_logs_page'
    );
}
