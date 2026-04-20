<?php
defined('ABSPATH') || exit;

add_action('wp_footer', 'businessbot_chat_assist_ui');

function businessbot_chat_assist_ui() {
    $options = get_option('ai_assistant_data');
    $options = get_option('ai_assistant_data');
    $chatbot_enabled = get_option('businessbot_chatbot_enabled');
    
    $start_message = $options['initial_greeting'] ?? 'Hi there! 👋 How can I help you today?';
    if (!empty($chatbot_enabled) && $chatbot_enabled == 'yes' ) {

    ?>
    <div id="ai-chat-launcher" class="ai-launcher" role="button" tabindex="0" aria-label="<?php esc_attr_e('Open chat', 'businessbot-ai-chat'); ?>">
        <!-- Chat Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="currentColor" d="M2.5 22.6C2.5 10.873 12.2 1.5 24 1.5s21.5 9.373 21.5 21.1v8.488q0 .275-.027.543q.027.18.027.369c0 1.97-.243 4.117-.48 5.757c-.372 2.567-1.891 5.113-4.672 6.214C37.355 45.157 32.158 46.5 24 46.5a2.5 2.5 0 0 1 0-5c7.603 0 12.165-1.25 14.507-2.177c.762-.302 1.39-1.077 1.564-2.282q.033-.229.065-.466c-1.406.313-3.03.615-4.645.784c-2.122.223-4.191-1.056-4.578-3.36c-.226-1.34-.413-3.273-.413-6c0-2.725.187-4.657.413-5.998c.387-2.304 2.456-3.583 4.578-3.36c.852.089 1.707.215 2.53.36C36.393 12.997 30.783 8.5 24 8.5S11.608 12.997 9.979 19.001a37 37 0 0 1 2.53-.36c2.122-.223 4.191 1.056 4.579 3.36c.225 1.34.412 3.273.412 5.999s-.187 4.658-.412 5.999c-.388 2.304-2.457 3.583-4.579 3.36c-2.198-.23-4.41-.705-6.07-1.12c-2.4-.601-3.939-2.777-3.939-5.15z"/>
        </svg>
    </div>

    <div id="ai-chat-widget" class="ai-widget" style="display: none;" aria-live="polite" aria-label="<?php esc_attr_e('AI Chat Widget', 'businessbot-ai-chat'); ?>">
        <div class="ai-header">
            <span><?php esc_html_e('AI Support Assistant', 'businessbot-ai-chat'); ?></span>
            <span id="ai-chat-close" role="button" tabindex="0" aria-label="<?php esc_attr_e('Close chat', 'businessbot-ai-chat'); ?>">&times;</span>
        </div>
        <div id="ai-chat-messages" class="ai-messages" role="log" aria-live="polite" aria-relevant="additions"></div>
        <div class="ai-input-area">
            <label for="ai-chat-input" class="screen-reader-text"><?php esc_html_e('Type your message', 'businessbot-ai-chat'); ?></label>
            <input type="text" id="ai-chat-input" placeholder="<?php esc_attr_e('Type your message...', 'businessbot-ai-chat'); ?>" />
            <button id="ai-chat-send" aria-label="<?php esc_attr_e('Send message', 'businessbot-ai-chat'); ?>">
                <i class="fa fa-paper-plane" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <?php
          
    }
}
