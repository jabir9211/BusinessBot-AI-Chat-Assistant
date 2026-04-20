<?php
/*
Plugin Name: BusinessBot AI Chat Assistant
Description: A personalized AI assistant plugin that gathers essential business details from the site admin and uses Gemini (Google) AI to act as a smart support executive for site visitors.
Version: 2.0
Author: Mohammed Jabir Shaikh
Author URI: https://mohammedjabir.com/
Contributors: jabir20
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: businessbot-ai-chat
Requires PHP: 7.4
Requires at least: 5.5
Tested up to: 6.9.4

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see https://www.gnu.org/licenses/gpl-2.0.html.

Tags: chatbot, AI assistant, support executive, Gemini, Google AI, customer support

This plugin uses third-party services (Google Gemini API) to power the AI responses. You are responsible for reviewing and complying with their terms of service.
*/

defined('ABSPATH') || exit;

// Constants
define('BUSSINESSBOT_DIR', plugin_dir_path(__FILE__));
define('BUSSINESSBOT_URL', plugin_dir_url(__FILE__));
define('BUSSINESSBOT_VERSION', '2.0');

// Include core files
require_once BUSSINESSBOT_DIR . 'admin/menus.php';
require_once BUSSINESSBOT_DIR . 'admin/admin-settings.php';
require_once BUSSINESSBOT_DIR . 'admin/settings.php';
require_once BUSSINESSBOT_DIR . 'admin/api-integration.php';
require_once BUSSINESSBOT_DIR . 'admin/how-to-use.php';
require_once BUSSINESSBOT_DIR . 'includes/propmt-callback.php';
require_once BUSSINESSBOT_DIR . 'includes/chat-ui.php';

// Start session if needed
add_action('plugins_loaded', function () {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
});

// Enqueue front-end scripts and styles
add_action('wp_enqueue_scripts', 'businessbot_chat_assist_enqueue_scripts');
function businessbot_chat_assist_enqueue_scripts() {
    wp_enqueue_script('jquery');

    wp_enqueue_style(
        'font-awesome',
        plugins_url('assets/css/fontawesome.min.css', __FILE__),
        [],
        '6.4.0'
    );

    wp_enqueue_style(
        'ai-chat-style',
        plugins_url('assets/css/ai-chat.css', __FILE__),
        [],
        time()
    );

    // Replaces external Marked.js
    wp_enqueue_script(
        'marked-js',
        plugins_url('assets/js/marked.min.js', __FILE__),
        ['jquery'],
        BUSSINESSBOT_VERSION,
        true
    );

    wp_enqueue_script(
        'chat-front-js',
        plugins_url('assets/js/chat-front.js', __FILE__),
        ['jquery'],
        BUSSINESSBOT_VERSION, 
        true
    );

    // Fetch greeting message
    $options = get_option('businessbot_data');
    $chatbot_open_once = get_option('businessbot_chatbot_auto_open', 'yes');
    $start_message = $options['initial_greeting'] ?? 'Hi there! 👋 How can I help you today?';

    // Localize PHP variables into JS object
    wp_localize_script('chat-front-js', 'BusinessBotData', [
        'ajax_url'       => admin_url('admin-ajax.php'),
        'start_message'  => $start_message,
        'plugin_url'     => plugins_url('/', __FILE__),
        'chat_open_once' => $chatbot_open_once,
        'nonce'         => wp_create_nonce('businessbot_nonce')
    ]);
}
