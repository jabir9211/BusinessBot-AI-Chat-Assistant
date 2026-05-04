<?php
defined('ABSPATH') || exit;

add_action('admin_enqueue_scripts', 'businessbot_chat_assist_enqueue_admin_settings_assets');

function businessbot_chat_assist_enqueue_admin_settings_assets() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page routing parameter.
    $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    if ('businessbot-settings' !== $current_page) {
        return;
    }

    $css_file = BUSSINESSBOT_DIR . 'assets/css/admin-details.css';
    $js_file = BUSSINESSBOT_DIR . 'assets/js/admin-chat-settings.js';
    $css_version = file_exists($css_file) ? (string) filemtime($css_file) : BUSSINESSBOT_VERSION;
    $js_version = file_exists($js_file) ? (string) filemtime($js_file) : BUSSINESSBOT_VERSION;

    wp_enqueue_style(
        'businessbot-admin-details',
        BUSSINESSBOT_URL . 'assets/css/admin-details.css',
        [],
        $css_version
    );

    wp_enqueue_script(
        'businessbot-admin-chat-settings',
        BUSSINESSBOT_URL . 'assets/js/admin-chat-settings.js',
        [],
        $js_version,
        true
    );
}

function businessbot_chat_assist_front_settings_page() {
    if (isset($_POST['_wpnonce']) && check_admin_referer('businessbot_settings_form')) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $chatbot_enabled = isset($_POST['chatbot_enabled']) ? 'yes' : 'no';
        $chatbot_auto_open = isset($_POST['chatbot_auto_open']) ? 'yes' : 'no';
        $chatbot_debug_mode = isset($_POST['chatbot_debug_mode']) ? 'yes' : 'no';

        update_option('businessbot_chatbot_enabled', $chatbot_enabled);
        update_option('businessbot_chatbot_auto_open', $chatbot_auto_open);
        update_option('businessbot_debug_mode', $chatbot_debug_mode);
    }

    $chatbot_enabled = get_option('businessbot_chatbot_enabled', 'yes');
    $chatbot_auto_open = get_option('businessbot_chatbot_auto_open', 'yes');
    $chatbot_debug_mode = get_option('businessbot_debug_mode', 'no');
    $is_enabled = ('yes' === $chatbot_enabled);
    ?>
    <div class="wrap businessbot-admin-shell">
        <form id="businessbot-settings-form" method="post" action="">
            <?php wp_nonce_field('businessbot_settings_form'); ?>
            <div class="businessbot-profile-wrap businessbot-settings-wrap">
                <?php if (isset($_POST['_wpnonce'])) : ?>
                    <div class="notice notice-success is-dismissible businessbot-notice">
                        <p><strong><?php esc_html_e('Settings saved successfully.', 'ai-chat-assistant-for-business'); ?></strong></p>
                    </div>
                <?php endif; ?>

                <div class="businessbot-page-header">
                    <div>
                        <h1><?php esc_html_e('Chatbot Settings', 'ai-chat-assistant-for-business'); ?></h1>
                        <p><?php esc_html_e('Control how your AI chatbot appears and behaves on your website.', 'ai-chat-assistant-for-business'); ?></p>
                    </div>
                    <div class="businessbot-header-actions">
                        <button type="submit" class="button button-primary button-large" id="businessbot-settings-save-top" disabled>
                            <span class="businessbot-btn-text"><?php esc_html_e('Save Changes', 'ai-chat-assistant-for-business'); ?></span>
                            <span class="spinner"></span>
                        </button>
                    </div>
                </div>

                <div class="businessbot-settings-grid">
                    <div>
                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-format-chat"></span><?php esc_html_e('Chatbot Status', 'ai-chat-assistant-for-business'); ?></h2>
                            <div class="businessbot-status-row">
                                <div>
                                    <div class="businessbot-status-label">
                                        <?php esc_html_e('Chatbot is:', 'ai-chat-assistant-for-business'); ?>
                                        <span id="businessbot-status-indicator" class="businessbot-status-pill <?php echo $is_enabled ? 'is-active' : 'is-disabled'; ?>">
                                            <span class="dot" aria-hidden="true"></span>
                                            <span class="state-text"><?php echo $is_enabled ? esc_html__('Active', 'ai-chat-assistant-for-business') : esc_html__('Disabled', 'ai-chat-assistant-for-business'); ?></span>
                                        </span>
                                    </div>
                                    <p id="businessbot-status-help" class="businessbot-help">
                                        <?php echo $is_enabled ? esc_html__('Your chatbot is live and visible to visitors.', 'ai-chat-assistant-for-business') : esc_html__('Chatbot is turned off and will not appear on your website.', 'ai-chat-assistant-for-business'); ?>
                                    </p>
                                </div>
                                <label class="businessbot-toggle" for="chatbot_enabled">
                                    <input type="checkbox" name="chatbot_enabled" id="chatbot_enabled" value="yes" <?php checked('yes', $chatbot_enabled); ?> />
                                    <span class="businessbot-toggle-slider" aria-hidden="true"></span>
                                </label>
                            </div>
                        </div>

                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e('Behavior Settings', 'ai-chat-assistant-for-business'); ?></h2>
                            <div id="businessbot-behavior-block" class="businessbot-behavior-block <?php echo $is_enabled ? '' : 'is-disabled'; ?>">
                                <div class="businessbot-status-row">
                                    <div>
                                        <div class="businessbot-inline-heading">
                                            <span><?php esc_html_e('Auto open chat on first visit', 'ai-chat-assistant-for-business'); ?></span>
                                            <span class="businessbot-badge"><?php esc_html_e('Recommended', 'ai-chat-assistant-for-business'); ?></span>
                                        </div>
                                        <p class="businessbot-help"><?php esc_html_e('Automatically opens the chatbot once per user session (usually on first page visit).', 'ai-chat-assistant-for-business'); ?></p>
                                        <p id="businessbot-auto-open-hint" class="businessbot-help businessbot-help-strong">
                                            <?php echo ('yes' === $chatbot_auto_open) ? esc_html__('Enabled for higher engagement.', 'ai-chat-assistant-for-business') : esc_html__('Chatbot stays minimized until user clicks it.', 'ai-chat-assistant-for-business'); ?>
                                        </p>
                                    </div>
                                    <label class="businessbot-toggle" for="chatbot_auto_open">
                                        <input type="checkbox" name="chatbot_auto_open" id="chatbot_auto_open" value="yes" <?php checked('yes', $chatbot_auto_open); ?> <?php disabled(!$is_enabled); ?> />
                                        <span class="businessbot-toggle-slider" aria-hidden="true"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-admin-tools"></span><?php esc_html_e('Diagnostics', 'ai-chat-assistant-for-business'); ?></h2>
                            <div class="businessbot-status-row">
                                <div>
                                    <div class="businessbot-inline-heading">
                                        <span><?php esc_html_e('Enable debug mode', 'ai-chat-assistant-for-business'); ?></span>
                                        <span class="businessbot-badge"><?php esc_html_e('For troubleshooting', 'ai-chat-assistant-for-business'); ?></span>
                                    </div>
                                    <p class="businessbot-help"><?php esc_html_e('Writes model attempts and API status to your PHP error log. API keys are never logged.', 'ai-chat-assistant-for-business'); ?></p>
                                    <p class="businessbot-help businessbot-help-strong">
                                        <?php echo ('yes' === $chatbot_debug_mode) ? esc_html__('Debug mode is enabled.', 'ai-chat-assistant-for-business') : esc_html__('Debug mode is disabled.', 'ai-chat-assistant-for-business'); ?>
                                    </p>
                                </div>
                                <label class="businessbot-toggle" for="chatbot_debug_mode">
                                    <input type="checkbox" name="chatbot_debug_mode" id="chatbot_debug_mode" value="yes" <?php checked('yes', $chatbot_debug_mode); ?> />
                                    <span class="businessbot-toggle-slider" aria-hidden="true"></span>
                                </label>
                            </div>
                        </div>

                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-visibility"></span><?php esc_html_e('Chat Behavior Preview', 'ai-chat-assistant-for-business'); ?></h2>
                            <div class="businessbot-preview-list">
                                <div>
                                    <h3><?php esc_html_e('First Visit', 'ai-chat-assistant-for-business'); ?></h3>
                                    <p id="businessbot-first-visit-preview"><?php echo ('yes' === $chatbot_auto_open) ? esc_html__('Chat opens automatically.', 'ai-chat-assistant-for-business') : esc_html__('Chat remains minimized.', 'ai-chat-assistant-for-business'); ?></p>
                                </div>
                                <div>
                                    <h3><?php esc_html_e('Next Visits', 'ai-chat-assistant-for-business'); ?></h3>
                                    <p><?php echo ('yes' === $chatbot_auto_open) ? esc_html__('Chat stays minimized after first auto-open.', 'ai-chat-assistant-for-business') : esc_html__('Chat stays minimized until user manually opens it.', 'ai-chat-assistant-for-business'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="businessbot-card businessbot-tip-card">
                            <h2><span class="dashicons dashicons-lightbulb"></span><?php esc_html_e('Tips', 'ai-chat-assistant-for-business'); ?></h2>
                            <ul>
                                <li><?php esc_html_e('Auto-open can increase engagement on first visit.', 'ai-chat-assistant-for-business'); ?></li>
                                <li><?php esc_html_e('Avoid overuse to prevent visitor annoyance.', 'ai-chat-assistant-for-business'); ?></li>
                                <li><?php esc_html_e('Test behavior on mobile devices before publishing.', 'ai-chat-assistant-for-business'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="businessbot-footer-actions">
                    <button type="submit" class="button button-primary button-large" id="businessbot-settings-save-bottom" disabled>
                        <span class="businessbot-btn-text"><?php esc_html_e('Save Changes', 'ai-chat-assistant-for-business'); ?></span>
                        <span class="spinner"></span>
                    </button>
                </div>
            </div>
        </form>
        <div id="businessbot-settings-toast" class="businessbot-toast" role="status" aria-live="polite"></div>
    </div>
    <script>
        window.businessbotSettingsData = <?php echo wp_json_encode([
            'activeLabel' => __('Active', 'ai-chat-assistant-for-business'),
            'disabledLabel' => __('Disabled', 'ai-chat-assistant-for-business'),
            'activeHelp' => __('Your chatbot is live and visible to visitors.', 'ai-chat-assistant-for-business'),
            'disabledHelp' => __('Chatbot is turned off and will not appear on your website.', 'ai-chat-assistant-for-business'),
            'autoOpenOnHint' => __('Enabled for higher engagement.', 'ai-chat-assistant-for-business'),
            'autoOpenOffHint' => __('Chatbot stays minimized until user clicks it.', 'ai-chat-assistant-for-business'),
            'firstVisitOpen' => __('Chat opens automatically.', 'ai-chat-assistant-for-business'),
            'firstVisitMin' => __('Chat remains minimized.', 'ai-chat-assistant-for-business'),
            'toastEnabled' => __('Chatbot enabled', 'ai-chat-assistant-for-business'),
            'toastDisabled' => __('Chatbot disabled', 'ai-chat-assistant-for-business'),
            'savingText' => __('Saving...', 'ai-chat-assistant-for-business'),
        ]); ?>;
    </script>
    <?php
}


