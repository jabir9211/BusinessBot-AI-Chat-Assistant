<?php
// Block direct access
defined('ABSPATH') || exit;

add_action('admin_enqueue_scripts', 'businessbot_chat_assist_enqueue_admin_onboarding_assets');

function businessbot_chat_assist_enqueue_admin_onboarding_assets() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page routing parameter.
    $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    if ('businessbot-how-to-use' !== $current_page) {
        return;
    }

    $css_file = BUSSINESSBOT_DIR . 'assets/css/admin-details.css';
    $css_version = file_exists($css_file) ? (string) filemtime($css_file) : BUSSINESSBOT_VERSION;

    wp_enqueue_style(
        'businessbot-admin-details',
        BUSSINESSBOT_URL . 'assets/css/admin-details.css',
        [],
        $css_version
    );
}

function businessbot_chat_assist_how_to_use_page() {
    $business_data = get_option('businessbot_data', []);
    $api_key = get_option('businessbot_api_key', '');
    $chatbot_enabled = get_option('businessbot_chatbot_enabled', 'no');

    $step_1_complete = !empty($business_data) && is_array($business_data);
    $step_2_complete = !empty($api_key);
    $step_3_complete = ('yes' === $chatbot_enabled);
    $step_4_complete = ($step_1_complete && $step_2_complete && $step_3_complete);

    $completed_count = ($step_1_complete ? 1 : 0) + ($step_2_complete ? 1 : 0) + ($step_3_complete ? 1 : 0) + ($step_4_complete ? 1 : 0);
    $progress_percent = (int) round(($completed_count / 4) * 100);

    $step_1_status = $step_1_complete ? __('Completed', 'ai-chat-assistant-for-business') : __('Needs attention', 'ai-chat-assistant-for-business');
    $step_2_status = $step_2_complete ? __('Completed', 'ai-chat-assistant-for-business') : __('Needs attention', 'ai-chat-assistant-for-business');
    $step_3_status = $step_3_complete ? __('Completed', 'ai-chat-assistant-for-business') : __('Needs attention', 'ai-chat-assistant-for-business');
    $step_4_status = $step_4_complete ? __('Completed', 'ai-chat-assistant-for-business') : __('Not started', 'ai-chat-assistant-for-business');

    $step_1_class = $step_1_complete ? 'is-complete' : 'is-warning';
    $step_2_class = $step_2_complete ? 'is-complete' : 'is-warning';
    $step_3_class = $step_3_complete ? 'is-complete' : 'is-warning';
    $step_4_class = $step_4_complete ? 'is-complete' : 'is-pending';
    ?>
    <div class="wrap businessbot-admin-shell">
        <div class="businessbot-profile-wrap businessbot-settings-wrap">
            <div class="businessbot-page-header businessbot-page-header-static">
                <div>
                    <h1><?php esc_html_e('How to Use: BusinessBot AI Chat Assistant', 'ai-chat-assistant-for-business'); ?></h1>
                    <p><?php esc_html_e('Set up your AI Support Assistant in a few easy steps.', 'ai-chat-assistant-for-business'); ?></p>
                </div>
            </div>

            <div class="businessbot-card businessbot-progress-card">
                <h2><span class="dashicons dashicons-chart-line"></span><?php esc_html_e('Setup Progress', 'ai-chat-assistant-for-business'); ?></h2>
                <div class="businessbot-progress-steps">
                    <a class="businessbot-progress-step <?php echo esc_attr($step_1_class); ?>" href="<?php echo esc_url(admin_url('admin.php?page=businessbot-details')); ?>">
                        <span class="step-title"><?php esc_html_e('1. Business Info', 'ai-chat-assistant-for-business'); ?></span>
                        <span class="step-status"><?php echo esc_html($step_1_status); ?></span>
                    </a>
                    <a class="businessbot-progress-step <?php echo esc_attr($step_2_class); ?>" href="<?php echo esc_url(admin_url('admin.php?page=businessbot-integration')); ?>">
                        <span class="step-title"><?php esc_html_e('2. API Setup', 'ai-chat-assistant-for-business'); ?></span>
                        <span class="step-status"><?php echo esc_html($step_2_status); ?></span>
                    </a>
                    <a class="businessbot-progress-step <?php echo esc_attr($step_3_class); ?>" href="<?php echo esc_url(admin_url('admin.php?page=businessbot-settings')); ?>">
                        <span class="step-title"><?php esc_html_e('3. Chat Settings', 'ai-chat-assistant-for-business'); ?></span>
                        <span class="step-status"><?php echo esc_html($step_3_status); ?></span>
                    </a>
                    <a class="businessbot-progress-step <?php echo esc_attr($step_4_class); ?>" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener noreferrer">
                        <span class="step-title"><?php esc_html_e('4. Go Live', 'ai-chat-assistant-for-business'); ?></span>
                        <span class="step-status"><?php echo esc_html($step_4_status); ?></span>
                    </a>
                </div>
                <div class="businessbot-progress-bar">
                    <span style="width: <?php echo esc_attr($progress_percent); ?>%;"></span>
                </div>
                <?php /* translators: %d: setup progress percentage. */ ?>
                <p class="businessbot-help"><?php echo esc_html(sprintf(__('Profile completion: %d%%', 'ai-chat-assistant-for-business'), $progress_percent)); ?></p>
            </div>

            <div class="businessbot-settings-grid businessbot-onboarding-grid">
                <div class="businessbot-card">
                    <div class="businessbot-card-head">
                        <h2><span class="dashicons dashicons-store"></span><?php esc_html_e('Business Information', 'ai-chat-assistant-for-business'); ?></h2>
                        <span class="businessbot-status-pill <?php echo esc_attr($step_1_class); ?>"><?php echo esc_html($step_1_status); ?></span>
                    </div>
                    <p class="businessbot-help businessbot-help-strong"><?php esc_html_e('Tell your AI about your business so it can respond accurately.', 'ai-chat-assistant-for-business'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=businessbot-details')); ?>" class="button button-primary"><?php esc_html_e('Open Details Page', 'ai-chat-assistant-for-business'); ?></a>
                    <ul class="businessbot-checklist">
                        <li><?php esc_html_e('Business name', 'ai-chat-assistant-for-business'); ?></li>
                        <li><?php esc_html_e('Services/products', 'ai-chat-assistant-for-business'); ?></li>
                        <li><?php esc_html_e('Customer type', 'ai-chat-assistant-for-business'); ?></li>
                        <li><?php esc_html_e('Contact info', 'ai-chat-assistant-for-business'); ?></li>
                    </ul>
                </div>

                <div class="businessbot-card">
                    <div class="businessbot-card-head">
                        <h2><span class="dashicons dashicons-admin-links"></span><?php esc_html_e('Connect AI (Gemini)', 'ai-chat-assistant-for-business'); ?></h2>
                        <span class="businessbot-status-pill <?php echo esc_attr($step_2_class); ?>"><?php echo esc_html($step_2_status); ?></span>
                    </div>
                    <p class="businessbot-help businessbot-help-strong"><?php esc_html_e('Enable smart AI responses by connecting your API key.', 'ai-chat-assistant-for-business'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=businessbot-integration')); ?>" class="button button-primary"><?php esc_html_e('Open Integration Page', 'ai-chat-assistant-for-business'); ?></a>
                    <ul class="businessbot-checklist">
                        <li><?php esc_html_e('Google account', 'ai-chat-assistant-for-business'); ?></li>
                        <li><?php esc_html_e('API key from AI Studio', 'ai-chat-assistant-for-business'); ?></li>
                    </ul>
                </div>

                <div class="businessbot-card">
                    <div class="businessbot-card-head">
                        <h2><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e('Chat Settings', 'ai-chat-assistant-for-business'); ?></h2>
                        <span class="businessbot-status-pill <?php echo esc_attr($step_3_class); ?>"><?php echo esc_html($step_3_status); ?></span>
                    </div>
                    <p class="businessbot-help businessbot-help-strong"><?php esc_html_e('Control how your chatbot appears to visitors.', 'ai-chat-assistant-for-business'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=businessbot-settings')); ?>" class="button button-primary"><?php esc_html_e('Open Settings Page', 'ai-chat-assistant-for-business'); ?></a>
                    <ul class="businessbot-checklist">
                        <li><?php esc_html_e('Enable chatbot', 'ai-chat-assistant-for-business'); ?></li>
                        <li><?php esc_html_e('Auto-open behavior', 'ai-chat-assistant-for-business'); ?></li>
                    </ul>
                </div>

                <div class="businessbot-card">
                    <div class="businessbot-card-head">
                        <h2><span class="dashicons dashicons-controls-play"></span><?php esc_html_e('Go Live', 'ai-chat-assistant-for-business'); ?></h2>
                        <span class="businessbot-status-pill <?php echo esc_attr($step_4_class); ?>"><?php echo esc_html($step_4_status); ?></span>
                    </div>
                    <p class="businessbot-help businessbot-help-strong"><?php esc_html_e('Visit your website and test the chatbot experience.', 'ai-chat-assistant-for-business'); ?></p>
                    <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary"><?php esc_html_e('Open Website', 'ai-chat-assistant-for-business'); ?></a>
                    <ul class="businessbot-checklist">
                        <li><?php esc_html_e('Chat icon appears', 'ai-chat-assistant-for-business'); ?></li>
                        <li><?php esc_html_e('AI greets users', 'ai-chat-assistant-for-business'); ?></li>
                        <li><?php esc_html_e('Responds based on your data', 'ai-chat-assistant-for-business'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="businessbot-settings-grid businessbot-onboarding-grid">
                <div class="businessbot-card businessbot-success-card">
                    <h2><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Success', 'ai-chat-assistant-for-business'); ?></h2>
                    <p><?php esc_html_e('Your AI assistant is ready to help visitors once setup is complete.', 'ai-chat-assistant-for-business'); ?></p>
                </div>
                <div class="businessbot-card businessbot-tip-card">
                    <h2><span class="dashicons dashicons-lightbulb"></span><?php esc_html_e('Tip', 'ai-chat-assistant-for-business'); ?></h2>
                    <p><?php esc_html_e('The more detailed your business information, the better your AI responses will be.', 'ai-chat-assistant-for-business'); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
}
