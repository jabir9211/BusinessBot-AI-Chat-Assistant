<?php
// Block direct access
defined('ABSPATH') || exit;

add_action('admin_enqueue_scripts', 'businessbot_chat_assist_enqueue_admin_integration_assets');
add_action('wp_ajax_businessbot_test_connection', 'businessbot_chat_assist_test_connection_ajax');

function businessbot_chat_assist_enqueue_admin_integration_assets() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page routing parameter.
    $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    if ('businessbot-integration' !== $current_page) {
        return;
    }

    $css_file = BUSSINESSBOT_DIR . 'assets/css/admin-details.css';
    $js_file = BUSSINESSBOT_DIR . 'assets/js/admin-api-integration.js';
    $css_version = file_exists($css_file) ? (string) filemtime($css_file) : BUSSINESSBOT_VERSION;
    $js_version = file_exists($js_file) ? (string) filemtime($js_file) : BUSSINESSBOT_VERSION;

    wp_enqueue_style(
        'businessbot-admin-details',
        BUSSINESSBOT_URL . 'assets/css/admin-details.css',
        [],
        $css_version
    );

    wp_enqueue_script(
        'businessbot-admin-api-integration',
        BUSSINESSBOT_URL . 'assets/js/admin-api-integration.js',
        [],
        $js_version,
        true
    );

    wp_localize_script('businessbot-admin-api-integration', 'BusinessBotIntegrationData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('businessbot_integration_nonce'),
        'test_connection_label' => __('Test Connection', 'ai-chat-assistant-for-business'),
        'testing_label' => __('Testing...', 'ai-chat-assistant-for-business'),
        'save_success' => __('API key saved', 'ai-chat-assistant-for-business'),
        'copy_success' => __('API key copied', 'ai-chat-assistant-for-business'),
    ]);
}

function businessbot_chat_assist_test_connection_ajax() {
    check_ajax_referer('businessbot_integration_nonce', '_ajax_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('You are not allowed to do this action.', 'ai-chat-assistant-for-business'));
    }

    $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
    $api_key = trim($api_key);

    if ('' === $api_key) {
        update_option('businessbot_api_last_test', 'error');
        update_option('businessbot_api_last_test_message', __('API key missing. Please enter your key and test again.', 'ai-chat-assistant-for-business'));
        update_option('businessbot_api_last_test_model', '');
        update_option('businessbot_api_last_test_http_code', 0);
        update_option('businessbot_api_last_test_error', __('API key missing.', 'ai-chat-assistant-for-business'));
        update_option('businessbot_api_last_test_time', time());
        wp_send_json_error(__('API key missing. Please enter your key and test again.', 'ai-chat-assistant-for-business'));
    }

    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => 'Reply with exactly: OK'],
                ],
            ],
        ],
    ];

    $model_chain = function_exists('businessbot_chat_assist_build_runtime_model_chain')
        ? businessbot_chat_assist_build_runtime_model_chain($api_key)
        : ['gemini-2.5-flash', 'gemini-2.0-flash'];

    $max_attempts = max(1, count($model_chain));
    $attempts = 0;
    foreach ($model_chain as $model) {
        $attempts++;
        if ($attempts > $max_attempts) {
            break;
        }

        $result = function_exists('businessbot_chat_assist_call_gemini_model')
            ? businessbot_chat_assist_call_gemini_model($api_key, $model, $payload)
            : ['success' => false, 'retry_next' => true];

        if (function_exists('businessbot_chat_assist_debug_log')) {
            businessbot_chat_assist_debug_log('test_connection_attempt', [
                'attempt' => $attempts,
                'max_attempts' => $max_attempts,
                'model' => $model,
                'success' => !empty($result['success']) ? 'yes' : 'no',
                'retry_next' => !empty($result['retry_next']) ? 'yes' : 'no',
                'status_code' => (string) ($result['status_code'] ?? ''),
                'error_message' => (string) ($result['error_message'] ?? ''),
            ]);
        }

        if (!empty($result['success'])) {
            update_option('businessbot_api_last_test', 'success');
            update_option('businessbot_api_last_test_message', __('Connection successful. AI is working.', 'ai-chat-assistant-for-business'));
            update_option('businessbot_api_last_test_model', sanitize_text_field($result['model'] ?? $model));
            update_option('businessbot_api_last_test_http_code', (int) ($result['status_code'] ?? 200));
            update_option('businessbot_api_last_test_error', '');
            update_option('businessbot_api_last_test_time', time());
            wp_send_json_success(__('Connection successful. AI is working.', 'ai-chat-assistant-for-business'));
        }

        update_option('businessbot_api_last_test_model', sanitize_text_field($result['model'] ?? $model));
        update_option('businessbot_api_last_test_http_code', (int) ($result['status_code'] ?? 0));
        update_option('businessbot_api_last_test_error', sanitize_text_field($result['error_message'] ?? __('Unknown error.', 'ai-chat-assistant-for-business')));
        update_option('businessbot_api_last_test_time', time());

        if (isset($result['retry_next']) && !$result['retry_next']) {
            break;
        }
    }

    update_option('businessbot_api_last_test', 'error');
    update_option('businessbot_api_last_test_message', __('Invalid API key or network issue.', 'ai-chat-assistant-for-business'));
    wp_send_json_error(__('Invalid API key or network issue.', 'ai-chat-assistant-for-business'));
}

function businessbot_chat_assist_integration_page() {
    $api_key = get_option('businessbot_api_key');
    $test_state = get_option('businessbot_api_last_test', '');
    $last_test_model = get_option('businessbot_api_last_test_model', '');
    $last_test_http_code = (int) get_option('businessbot_api_last_test_http_code', 0);
    $last_test_error = get_option('businessbot_api_last_test_error', '');
    $last_test_time = (int) get_option('businessbot_api_last_test_time', 0);
    $status = 'not_connected';
    $status_text = __('Not Connected', 'ai-chat-assistant-for-business');
    $status_message = __('API key missing or invalid. Chatbot will not respond.', 'ai-chat-assistant-for-business');

    if (!empty($api_key) && 'success' === $test_state) {
        $status = 'connected';
        $status_text = __('Connected', 'ai-chat-assistant-for-business');
        $status_message = __('Your AI is successfully connected and ready.', 'ai-chat-assistant-for-business');
    } elseif (!empty($api_key) && 'error' !== $test_state) {
        $status = 'not_set';
        $status_text = __('Not Tested', 'ai-chat-assistant-for-business');
        $status_message = __('API key is saved. Run Test Connection to confirm it is working.', 'ai-chat-assistant-for-business');
    } elseif (!empty($api_key) && 'error' === $test_state) {
        $status = 'not_connected';
        $status_text = __('Not Connected', 'ai-chat-assistant-for-business');
        $status_message = __('API key missing or invalid. Chatbot will not respond.', 'ai-chat-assistant-for-business');
    }

    $masked_key = '';
    if (!empty($api_key)) {
        $key_len = strlen($api_key);
        $masked_key = ($key_len > 8) ? substr($api_key, 0, 4) . str_repeat('*', max(6, $key_len - 8)) . substr($api_key, -4) : str_repeat('*', $key_len);
    }

    ?>
    <div class="wrap businessbot-admin-shell">
        <form id="businessbot-integration-form" method="post" action="options.php">
            <?php
            settings_fields('businessbot_api_options');
            do_settings_sections('businessbot_api_options');
            ?>
            <div class="businessbot-profile-wrap businessbot-settings-wrap">
                <div class="businessbot-page-header">
                    <div>
                        <h1><?php esc_html_e('API Integration', 'ai-chat-assistant-for-business'); ?></h1>
                        <p><?php esc_html_e('Connect your AI assistant to Google Gemini to enable smart responses.', 'ai-chat-assistant-for-business'); ?></p>
                    </div>
                </div>

                <div class="businessbot-settings-grid businessbot-integration-grid">
                    <div>
                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-admin-links"></span><?php esc_html_e('Connection Status', 'ai-chat-assistant-for-business'); ?></h2>
                            <div class="businessbot-field">
                                <label><?php esc_html_e('Gemini API Connection', 'ai-chat-assistant-for-business'); ?></label>
                                <div id="businessbot-api-status" class="businessbot-status-pill <?php echo ('connected' === $status) ? 'is-active' : (('not_connected' === $status) ? 'is-error' : 'is-disabled'); ?>">
                                    <span class="dot" aria-hidden="true"></span>
                                    <span class="state-text"><?php echo esc_html($status_text); ?></span>
                                </div>
                                <p id="businessbot-api-status-message" class="businessbot-help businessbot-help-strong"><?php echo esc_html($status_message); ?></p>
                            </div>

                            <div class="businessbot-field">
                                <label for="businessbot_api_key_field"><?php esc_html_e('API Key', 'ai-chat-assistant-for-business'); ?></label>
                                <div class="businessbot-input-group">
                                    <input
                                        id="businessbot_api_key_field"
                                        type="password"
                                        name="businessbot_api_key"
                                        value="<?php echo esc_attr($api_key); ?>"
                                        placeholder="<?php esc_attr_e('Enter your Gemini API key', 'ai-chat-assistant-for-business'); ?>"
                                        data-masked="<?php echo esc_attr($masked_key); ?>"
                                    >
                                    <button class="button" type="button" id="businessbot-toggle-key"><?php esc_html_e('Show', 'ai-chat-assistant-for-business'); ?></button>
                                    <button class="button" type="button" id="businessbot-copy-key"><?php esc_html_e('Copy', 'ai-chat-assistant-for-business'); ?></button>
                                </div>
                                <p class="businessbot-help"><?php esc_html_e('Your API key is stored securely in your WordPress database and never exposed publicly.', 'ai-chat-assistant-for-business'); ?></p>
                                <p id="businessbot-api-inline-feedback" class="businessbot-help"></p>
                            </div>

                            <div class="businessbot-actions-row">
                                <button type="submit" class="button button-primary" id="businessbot-save-key-btn"><?php esc_html_e('Save Key', 'ai-chat-assistant-for-business'); ?></button>
                                <button type="button" class="button" id="businessbot-test-connection-btn"><?php esc_html_e('Test Connection', 'ai-chat-assistant-for-business'); ?></button>
                                <span class="spinner" id="businessbot-test-spinner"></span>
                            </div>

                            <div class="businessbot-trust-list">
                                <div><span class="dashicons dashicons-lock"></span><?php esc_html_e('Secure Storage: Your key is stored safely in WordPress.', 'ai-chat-assistant-for-business'); ?></div>
                                <div><span class="dashicons dashicons-lightning"></span><?php esc_html_e('Real-time AI Responses: Powered by Google Gemini.', 'ai-chat-assistant-for-business'); ?></div>
                            </div>
                        </div>

                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-admin-tools"></span><?php esc_html_e('Diagnostics', 'ai-chat-assistant-for-business'); ?></h2>
                            <div class="businessbot-diagnostics-grid">
                                <div><strong><?php esc_html_e('Last status:', 'ai-chat-assistant-for-business'); ?></strong> <?php echo esc_html(ucfirst((string) $test_state ?: __('Not tested', 'ai-chat-assistant-for-business'))); ?></div>
                                <div><strong><?php esc_html_e('Last model:', 'ai-chat-assistant-for-business'); ?></strong> <?php echo esc_html($last_test_model ?: __('N/A', 'ai-chat-assistant-for-business')); ?></div>
                                <div><strong><?php esc_html_e('HTTP code:', 'ai-chat-assistant-for-business'); ?></strong> <?php echo esc_html($last_test_http_code > 0 ? (string) $last_test_http_code : __('N/A', 'ai-chat-assistant-for-business')); ?></div>
                                <div><strong><?php esc_html_e('Last checked:', 'ai-chat-assistant-for-business'); ?></strong> <?php echo esc_html($last_test_time ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $last_test_time) : __('N/A', 'ai-chat-assistant-for-business')); ?></div>
                            </div>
                            <?php if (!empty($last_test_error)) : ?>
                                <p class="businessbot-help businessbot-inline-error"><strong><?php esc_html_e('Last error:', 'ai-chat-assistant-for-business'); ?></strong> <?php echo esc_html($last_test_error); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-welcome-learn-more"></span><?php esc_html_e('Get Your Gemini API Key', 'ai-chat-assistant-for-business'); ?></h2>
                            <div class="businessbot-step-list">
                                <div class="businessbot-step">
                                    <strong><?php esc_html_e('Step 1:', 'ai-chat-assistant-for-business'); ?></strong>
                                    <span><?php esc_html_e('Open Google AI Studio', 'ai-chat-assistant-for-business'); ?></span>
                                </div>
                                <a class="button button-secondary" href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open Google AI Studio', 'ai-chat-assistant-for-business'); ?></a>
                                <div class="businessbot-step"><strong><?php esc_html_e('Step 2:', 'ai-chat-assistant-for-business'); ?></strong><span><?php esc_html_e('Login with your Google account.', 'ai-chat-assistant-for-business'); ?></span></div>
                                <div class="businessbot-step"><strong><?php esc_html_e('Step 3:', 'ai-chat-assistant-for-business'); ?></strong><span><?php esc_html_e('Click "Get API Key".', 'ai-chat-assistant-for-business'); ?></span></div>
                                <div class="businessbot-step"><strong><?php esc_html_e('Step 4:', 'ai-chat-assistant-for-business'); ?></strong><span><?php esc_html_e('Create a new API key.', 'ai-chat-assistant-for-business'); ?></span></div>
                                <div class="businessbot-step"><strong><?php esc_html_e('Step 5:', 'ai-chat-assistant-for-business'); ?></strong><span><?php esc_html_e('Copy the key and paste it in the field.', 'ai-chat-assistant-for-business'); ?></span></div>
                            </div>
                            <div class="businessbot-warning-box">
                                <?php esc_html_e('Never share your API key publicly. Keep it confidential.', 'ai-chat-assistant-for-business'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div id="businessbot-settings-toast" class="businessbot-toast" role="status" aria-live="polite"></div>
        <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WordPress core settings redirect flag.
        if (isset($_GET['settings-updated'])) : ?>
            <script>
                window.businessbotApiSaved = true;
            </script>
        <?php endif; ?>
        <script>
            window.businessbotApiUiData = <?php echo wp_json_encode([
                'connected' => __('Connected', 'ai-chat-assistant-for-business'),
                'not_connected' => __('Not Connected', 'ai-chat-assistant-for-business'),
                'not_tested' => __('Not Tested', 'ai-chat-assistant-for-business'),
                'connected_message' => __('Your AI is successfully connected and ready.', 'ai-chat-assistant-for-business'),
                'disconnected_message' => __('API key missing or invalid. Chatbot will not respond.', 'ai-chat-assistant-for-business'),
                'not_tested_message' => __('API key is saved. Run Test Connection to confirm it is working.', 'ai-chat-assistant-for-business'),
                'show' => __('Show', 'ai-chat-assistant-for-business'),
                'hide' => __('Hide', 'ai-chat-assistant-for-business'),
            ]); ?>;
        </script>
    </div>
    <?php
}
