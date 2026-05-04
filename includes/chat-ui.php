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
    <button id="ai-chat-launcher" class="ai-launcher" type="button" style="display: none;" aria-label="<?php esc_attr_e('Open chat', 'ai-chat-assistant-for-business'); ?>" title="<?php esc_attr_e('Open chat', 'ai-chat-assistant-for-business'); ?>">
        <svg class="ai-svg-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M4 5.5a3.5 3.5 0 0 1 3.5-3.5h9A3.5 3.5 0 0 1 20 5.5v7A3.5 3.5 0 0 1 16.5 16H9l-4.4 4.4A1 1 0 0 1 3 19.7V5.5Z" fill="currentColor"></path>
        </svg>
    </button>

    <div id="ai-chat-widget" class="ai-widget" style="display: none;" aria-live="polite" aria-label="<?php esc_attr_e('AI Chat Widget', 'ai-chat-assistant-for-business'); ?>">
        <div class="ai-header">
            <div class="ai-header-left">
                <div class="ai-bot-mark" aria-hidden="true">
                    <svg class="ai-svg-icon ai-svg-lg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M4 5.5a3.5 3.5 0 0 1 3.5-3.5h9A3.5 3.5 0 0 1 20 5.5v7A3.5 3.5 0 0 1 16.5 16H9l-4.4 4.4A1 1 0 0 1 3 19.7V5.5Z" fill="currentColor"></path>
                    </svg>
                </div>
                <div class="ai-header-texts">
                    <h2 class="ai-header-title"><?php esc_html_e('AI Support Assistant', 'ai-chat-assistant-for-business'); ?></h2>
                    <p class="ai-status"><span class="ai-status-dot" aria-hidden="true"></span><?php esc_html_e('Online', 'ai-chat-assistant-for-business'); ?></p>
                </div>
            </div>
            <div class="ai-header-actions">
                <button id="ai-chat-menu" class="ai-header-btn" type="button" aria-label="<?php esc_attr_e('More options', 'ai-chat-assistant-for-business'); ?>" title="<?php esc_attr_e('More options', 'ai-chat-assistant-for-business'); ?>">
                    <svg class="ai-svg-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="5" r="2" fill="currentColor"></circle>
                        <circle cx="12" cy="12" r="2" fill="currentColor"></circle>
                        <circle cx="12" cy="19" r="2" fill="currentColor"></circle>
                    </svg>
                </button>
                <button id="ai-chat-minimize" class="ai-header-btn" type="button" aria-label="<?php esc_attr_e('Minimize chat', 'ai-chat-assistant-for-business'); ?>" title="<?php esc_attr_e('Minimize chat', 'ai-chat-assistant-for-business'); ?>">
                    <span class="ai-minimize-icon" data-state="open">
                        <svg class="ai-svg-icon ai-min-open" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" fill="none"></path>
                        </svg>
                        <svg class="ai-svg-icon ai-min-restore" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M7 7h10v10H7z" stroke="currentColor" stroke-width="2" fill="none"></path>
                        </svg>
                    </span>
                </button>
                <button id="ai-chat-close" class="ai-header-btn" type="button" aria-label="<?php esc_attr_e('Close chat', 'ai-chat-assistant-for-business'); ?>" title="<?php esc_attr_e('Close chat', 'ai-chat-assistant-for-business'); ?>">
                    <svg class="ai-svg-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" fill="none"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div id="ai-chat-messages" class="ai-messages" role="log" aria-live="polite" aria-relevant="additions"></div>
        <div class="ai-input-area">
            <label for="ai-chat-input" class="screen-reader-text"><?php esc_html_e('Message', 'ai-chat-assistant-for-business'); ?></label>
            <div class="ai-input-shell">
                <input type="text" id="ai-chat-input" placeholder="<?php esc_attr_e('Type your message...', 'ai-chat-assistant-for-business'); ?>" />
                <button id="ai-chat-send" type="button" aria-label="<?php esc_attr_e('Send message', 'ai-chat-assistant-for-business'); ?>" title="<?php esc_attr_e('Send message', 'ai-chat-assistant-for-business'); ?>">
                    <svg class="ai-svg-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M3 11.5 20 4l-5.6 16-3.4-5.2-8-3.3Z" fill="currentColor"></path>
                    </svg>
                </button>
            </div>
            <p class="ai-footer-note"><span aria-hidden="true">✦</span> <?php esc_html_e('Powered by BusinessBot', 'ai-chat-assistant-for-business'); ?></p>
        </div>
    </div>
    <?php
          
    }
}
