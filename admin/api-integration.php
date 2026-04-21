<?php
// Block direct access
defined('ABSPATH') || exit;

add_action('admin_enqueue_scripts', 'businessbot_chat_assist_enqueue_admin_integration_assets');
add_action('wp_ajax_businessbot_test_connection', 'businessbot_chat_assist_test_connection_ajax');

function businessbot_chat_assist_enqueue_admin_integration_assets() {
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
        'test_connection_label' => __('Test Connection', 'businessbot-ai-chat'),
        'testing_label' => __('Testing...', 'businessbot-ai-chat'),
        'save_success' => __('API key saved', 'businessbot-ai-chat'),
        'copy_success' => __('API key copied', 'businessbot-ai-chat'),
    ]);
}

function businessbot_chat_assist_test_connection_ajax() {
    check_ajax_referer('businessbot_integration_nonce', '_ajax_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('You are not allowed to do this action.', 'businessbot-ai-chat'));
    }

    $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
    $api_key = trim($api_key);

    if ('' === $api_key) {
        update_option('businessbot_api_last_test', 'error');
        update_option('businessbot_api_last_test_message', __('API key missing. Please enter your key and test again.', 'businessbot-ai-chat'));
        update_option('businessbot_api_last_test_model', '');
        update_option('businessbot_api_last_test_http_code', 0);
        update_option('businessbot_api_last_test_error', __('API key missing.', 'businessbot-ai-chat'));
        update_option('businessbot_api_last_test_time', time());
        wp_send_json_error(__('API key missing. Please enter your key and test again.', 'businessbot-ai-chat'));
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
            update_option('businessbot_api_last_test_message', __('Connection successful. AI is working.', 'businessbot-ai-chat'));
            update_option('businessbot_api_last_test_model', sanitize_text_field($result['model'] ?? $model));
            update_option('businessbot_api_last_test_http_code', (int) ($result['status_code'] ?? 200));
            update_option('businessbot_api_last_test_error', '');
            update_option('businessbot_api_last_test_time', time());
            wp_send_json_success(__('Connection successful. AI is working.', 'businessbot-ai-chat'));
        }

        update_option('businessbot_api_last_test_model', sanitize_text_field($result['model'] ?? $model));
        update_option('businessbot_api_last_test_http_code', (int) ($result['status_code'] ?? 0));
        update_option('businessbot_api_last_test_error', sanitize_text_field($result['error_message'] ?? __('Unknown error.', 'businessbot-ai-chat')));
        update_option('businessbot_api_last_test_time', time());

        if (isset($result['retry_next']) && !$result['retry_next']) {
            break;
        }
    }

    update_option('businessbot_api_last_test', 'error');
    update_option('businessbot_api_last_test_message', __('Invalid API key or network issue.', 'businessbot-ai-chat'));
    wp_send_json_error(__('Invalid API key or network issue.', 'businessbot-ai-chat'));
}

function businessbot_chat_assist_integration_page() {
    $api_key = get_option('businessbot_api_key');
    $test_state = get_option('businessbot_api_last_test', '');
    $last_test_model = get_option('businessbot_api_last_test_model', '');
    $last_test_http_code = (int) get_option('businessbot_api_last_test_http_code', 0);
    $last_test_error = get_option('businessbot_api_last_test_error', '');
    $last_test_time = (int) get_option('businessbot_api_last_test_time', 0);
    $status = 'not_connected';
    $status_text = __('Not Connected', 'businessbot-ai-chat');
    $status_message = __('API key missing or invalid. Chatbot will not respond.', 'businessbot-ai-chat');

    if (!empty($api_key) && 'success' === $test_state) {
        $status = 'connected';
        $status_text = __('Connected', 'businessbot-ai-chat');
        $status_message = __('Your AI is successfully connected and ready.', 'businessbot-ai-chat');
    } elseif (!empty($api_key) && 'error' !== $test_state) {
        $status = 'not_set';
        $status_text = __('Not Tested', 'businessbot-ai-chat');
        $status_message = __('API key is saved. Run Test Connection to confirm it is working.', 'businessbot-ai-chat');
    } elseif (!empty($api_key) && 'error' === $test_state) {
        $status = 'not_connected';
        $status_text = __('Not Connected', 'businessbot-ai-chat');
        $status_message = __('API key missing or invalid. Chatbot will not respond.', 'businessbot-ai-chat');
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
                        <h1><?php esc_html_e('API Integration', 'businessbot-ai-chat'); ?></h1>
                        <p><?php esc_html_e('Connect your AI assistant to Google Gemini to enable smart responses.', 'businessbot-ai-chat'); ?></p>
                    </div>
                </div>

                <div class="businessbot-settings-grid businessbot-integration-grid">
                    <div>
                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-admin-links"></span><?php esc_html_e('Connection Status', 'businessbot-ai-chat'); ?></h2>
                            <div class="businessbot-field">
                                <label><?php esc_html_e('Gemini API Connection', 'businessbot-ai-chat'); ?></label>
                                <div id="businessbot-api-status" class="businessbot-status-pill <?php echo ('connected' === $status) ? 'is-active' : (('not_connected' === $status) ? 'is-error' : 'is-disabled'); ?>">
                                    <span class="dot" aria-hidden="true"></span>
                                    <span class="state-text"><?php echo esc_html($status_text); ?></span>
                                </div>
                                <p id="businessbot-api-status-message" class="businessbot-help businessbot-help-strong"><?php echo esc_html($status_message); ?></p>
                            </div>

                            <div class="businessbot-field">
                                <label for="businessbot_api_key_field"><?php esc_html_e('API Key', 'businessbot-ai-chat'); ?></label>
                                <div class="businessbot-input-group">
                                    <input
                                        id="businessbot_api_key_field"
                                        type="password"
                                        name="businessbot_api_key"
                                        value="<?php echo esc_attr($api_key); ?>"
                                        placeholder="<?php esc_attr_e('Enter your Gemini API key', 'businessbot-ai-chat'); ?>"
                                        data-masked="<?php echo esc_attr($masked_key); ?>"
                                    >
                                    <button class="button" type="button" id="businessbot-toggle-key"><?php esc_html_e('Show', 'businessbot-ai-chat'); ?></button>
                                    <button class="button" type="button" id="businessbot-copy-key"><?php esc_html_e('Copy', 'businessbot-ai-chat'); ?></button>
                                </div>
                                <p class="businessbot-help"><?php esc_html_e('Your API key is stored securely in your WordPress database and never exposed publicly.', 'businessbot-ai-chat'); ?></p>
                                <p id="businessbot-api-inline-feedback" class="businessbot-help"></p>
                            </div>

                            <div class="businessbot-actions-row">
                                <button type="submit" class="button button-primary" id="businessbot-save-key-btn"><?php esc_html_e('Save Key', 'businessbot-ai-chat'); ?></button>
                                <button type="button" class="button" id="businessbot-test-connection-btn"><?php esc_html_e('Test Connection', 'businessbot-ai-chat'); ?></button>
                                <span class="spinner" id="businessbot-test-spinner"></span>
                            </div>

                            <div class="businessbot-trust-list">
                                <div><span class="dashicons dashicons-lock"></span><?php esc_html_e('Secure Storage: Your key is stored safely in WordPress.', 'businessbot-ai-chat'); ?></div>
                                <div><span class="dashicons dashicons-lightning"></span><?php esc_html_e('Real-time AI Responses: Powered by Google Gemini.', 'businessbot-ai-chat'); ?></div>
                            </div>
                        </div>

                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-admin-tools"></span><?php esc_html_e('Diagnostics', 'businessbot-ai-chat'); ?></h2>
                            <div class="businessbot-diagnostics-grid">
                                <div><strong><?php esc_html_e('Last status:', 'businessbot-ai-chat'); ?></strong> <?php echo esc_html(ucfirst((string) $test_state ?: __('Not tested', 'businessbot-ai-chat'))); ?></div>
                                <div><strong><?php esc_html_e('Last model:', 'businessbot-ai-chat'); ?></strong> <?php echo esc_html($last_test_model ?: __('N/A', 'businessbot-ai-chat')); ?></div>
                                <div><strong><?php esc_html_e('HTTP code:', 'businessbot-ai-chat'); ?></strong> <?php echo esc_html($last_test_http_code > 0 ? (string) $last_test_http_code : __('N/A', 'businessbot-ai-chat')); ?></div>
                                <div><strong><?php esc_html_e('Last checked:', 'businessbot-ai-chat'); ?></strong> <?php echo esc_html($last_test_time ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $last_test_time) : __('N/A', 'businessbot-ai-chat')); ?></div>
                            </div>
                            <?php if (!empty($last_test_error)) : ?>
                                <p class="businessbot-help businessbot-inline-error"><strong><?php esc_html_e('Last error:', 'businessbot-ai-chat'); ?></strong> <?php echo esc_html($last_test_error); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="businessbot-card">
                            <h2><span class="dashicons dashicons-welcome-learn-more"></span><?php esc_html_e('Get Your Gemini API Key', 'businessbot-ai-chat'); ?></h2>
                            <div class="businessbot-step-list">
                                <div class="businessbot-step">
                                    <strong><?php esc_html_e('Step 1:', 'businessbot-ai-chat'); ?></strong>
                                    <span><?php esc_html_e('Open Google AI Studio', 'businessbot-ai-chat'); ?></span>
                                </div>
                                <a class="button button-secondary" href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open Google AI Studio', 'businessbot-ai-chat'); ?></a>
                                <div class="businessbot-step"><strong><?php esc_html_e('Step 2:', 'businessbot-ai-chat'); ?></strong><span><?php esc_html_e('Login with your Google account.', 'businessbot-ai-chat'); ?></span></div>
                                <div class="businessbot-step"><strong><?php esc_html_e('Step 3:', 'businessbot-ai-chat'); ?></strong><span><?php esc_html_e('Click "Get API Key".', 'businessbot-ai-chat'); ?></span></div>
                                <div class="businessbot-step"><strong><?php esc_html_e('Step 4:', 'businessbot-ai-chat'); ?></strong><span><?php esc_html_e('Create a new API key.', 'businessbot-ai-chat'); ?></span></div>
                                <div class="businessbot-step"><strong><?php esc_html_e('Step 5:', 'businessbot-ai-chat'); ?></strong><span><?php esc_html_e('Copy the key and paste it in the field.', 'businessbot-ai-chat'); ?></span></div>
                            </div>
                            <div class="businessbot-warning-box">
                                <?php esc_html_e('Never share your API key publicly. Keep it confidential.', 'businessbot-ai-chat'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div id="businessbot-settings-toast" class="businessbot-toast" role="status" aria-live="polite"></div>
        <?php if (isset($_GET['settings-updated'])) : ?>
            <script>
                window.businessbotApiSaved = true;
            </script>
        <?php endif; ?>
        <script>
            window.businessbotApiUiData = <?php echo wp_json_encode([
                'connected' => __('Connected', 'businessbot-ai-chat'),
                'not_connected' => __('Not Connected', 'businessbot-ai-chat'),
                'not_tested' => __('Not Tested', 'businessbot-ai-chat'),
                'connected_message' => __('Your AI is successfully connected and ready.', 'businessbot-ai-chat'),
                'disconnected_message' => __('API key missing or invalid. Chatbot will not respond.', 'businessbot-ai-chat'),
                'not_tested_message' => __('API key is saved. Run Test Connection to confirm it is working.', 'businessbot-ai-chat'),
                'show' => __('Show', 'businessbot-ai-chat'),
                'hide' => __('Hide', 'businessbot-ai-chat'),
            ]); ?>;
        </script>
    </div>
    <?php
}
