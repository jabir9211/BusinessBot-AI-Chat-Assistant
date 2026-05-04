<?php
// Block direct access
defined('ABSPATH') || exit;

// Register settings
add_action('admin_init', 'businessbot_chat_assist_settings_init');
add_action('admin_enqueue_scripts', 'businessbot_chat_assist_enqueue_admin_details_assets');

function businessbot_chat_assist_settings_init() {
    register_setting('businessbot_options', 'businessbot_data', [
        'sanitize_callback' => 'businessbot_chat_assist_sanitize_business_data',
    ]);

    register_setting('businessbot_api_options', 'businessbot_api_key', [
        'sanitize_callback' => 'businessbot_chat_assist_sanitize_api_key',
    ]);

    register_setting('businessbot_api_options', 'businessbot_debug_mode', [
        'sanitize_callback' => 'businessbot_chat_assist_sanitize_yes_no_option',
    ]);
}

function businessbot_chat_assist_sanitize_api_key($input) {
    $new_key = sanitize_text_field($input);
    $new_key = trim($new_key);
    $current_key = (string) get_option('businessbot_api_key', '');

    if ($new_key !== $current_key) {
        delete_option('businessbot_api_last_test');
        delete_option('businessbot_api_last_test_message');
        delete_option('businessbot_api_last_test_model');
        delete_option('businessbot_api_last_test_http_code');
        delete_option('businessbot_api_last_test_error');
        delete_option('businessbot_api_last_test_time');
    }

    return $new_key;
}

function businessbot_chat_assist_sanitize_business_data($input) {
    // Sanitize each field
    $output = [];

    $fields = [
        'business_name',
        'description',
        'products_services',
        'customers',
        'tone',
        'address',
        'contact_number',
        'businessEmails',
        'hours',
        'common_questions',
        'promotions',
        'avoid',
        'faq_link',
        'initial_greeting',
    ];

    foreach ($fields as $field) {
        if (isset($input[$field])) {
            $output[$field] = is_array($input[$field])
                ? array_map('sanitize_text_field', $input[$field])
                : sanitize_text_field($input[$field]);
        }
    }

    // Special case: URL
    if (!empty($input['faq_link'])) {
        $output['faq_link'] = esc_url_raw($input['faq_link']);
    }

    return $output;
}

function businessbot_chat_assist_sanitize_yes_no_option($input) {
    return ('yes' === sanitize_text_field((string) $input)) ? 'yes' : 'no';
}

function businessbot_chat_assist_enqueue_admin_details_assets($hook) {
    $allowed_pages = ['businessbot', 'businessbot-details'];
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page routing parameter.
    $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

    if (!in_array($current_page, $allowed_pages, true)) {
        return;
    }

    $css_file = BUSSINESSBOT_DIR . 'assets/css/admin-details.css';
    $js_file = BUSSINESSBOT_DIR . 'assets/js/admin-details.js';
    $css_version = file_exists($css_file) ? (string) filemtime($css_file) : BUSSINESSBOT_VERSION;
    $js_version = file_exists($js_file) ? (string) filemtime($js_file) : BUSSINESSBOT_VERSION;

    wp_enqueue_style(
        'businessbot-admin-details',
        BUSSINESSBOT_URL . 'assets/css/admin-details.css',
        [],
        $css_version
    );

    wp_enqueue_script(
        'businessbot-admin-details',
        BUSSINESSBOT_URL . 'assets/js/admin-details.js',
        [],
        $js_version,
        true
    );
}

function businessbot_chat_assist_settings_page() {
    $options = get_option('businessbot_data');
    $tone_descriptions = [
        'Friendly' => __('Friendly tone responds in a warm, welcoming, and conversational style.', 'ai-chat-assistant-for-business'),
        'Professional' => __('Professional tone responds with clarity, respect, and business-focused language.', 'ai-chat-assistant-for-business'),
        'Casual' => __('Casual tone responds in a relaxed and easy-to-understand style.', 'ai-chat-assistant-for-business'),
        'Witty' => __('Witty tone responds with light humor while staying helpful and relevant.', 'ai-chat-assistant-for-business'),
        'Empathetic' => __('Empathetic tone responds with understanding and supportive language.', 'ai-chat-assistant-for-business'),
        'Direct' => __('Direct tone responds in a concise, no-nonsense, action-oriented way.', 'ai-chat-assistant-for-business'),
    ];
    $current_tone = $options['tone'] ?? 'Friendly';
    $current_greeting = $options['initial_greeting'] ?? __('Hi there! How can I help you today?', 'ai-chat-assistant-for-business');
    ?>
    <div class="wrap businessbot-admin-shell">
        <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WordPress core settings redirect flag.
        if (isset($_GET['settings-updated'])) : ?>
            <div class="notice notice-success is-dismissible businessbot-notice">
                <p><strong><?php esc_html_e('Settings saved successfully.', 'ai-chat-assistant-for-business'); ?></strong></p>
            </div>
        <?php endif; ?>
        <form method="post" action="options.php">
            <?php settings_fields('businessbot_options'); ?>
            <div class="businessbot-profile-wrap">
                <div class="businessbot-page-header">
                    <div>
                        <h1><?php esc_html_e('Business Profile', 'ai-chat-assistant-for-business'); ?></h1>
                        <p><?php esc_html_e('Configure how your AI assistant understands and represents your business.', 'ai-chat-assistant-for-business'); ?></p>
                    </div>
                    <div class="businessbot-header-actions">
                        <?php submit_button(__('Save Changes', 'ai-chat-assistant-for-business'), 'primary', 'submit', false, ['class' => 'button button-primary button-large']); ?>
                    </div>
                </div>

                <div class="businessbot-card">
                    <h2><span class="dashicons dashicons-store"></span><?php esc_html_e('Business Overview', 'ai-chat-assistant-for-business'); ?></h2>
                    <div class="businessbot-field">
                        <label for="businessbot_business_name"><?php esc_html_e('Business Name', 'ai-chat-assistant-for-business'); ?></label>
                        <input id="businessbot_business_name" type="text" name="businessbot_data[business_name]" value="<?php echo esc_attr($options['business_name'] ?? ''); ?>" placeholder="<?php esc_attr_e('UrbanNest Home & Lifestyle', 'ai-chat-assistant-for-business'); ?>">
                    </div>
                    <div class="businessbot-field">
                        <label for="businessbot_description"><?php esc_html_e('Business Overview', 'ai-chat-assistant-for-business'); ?></label>
                        <textarea id="businessbot_description" name="businessbot_data[description]" rows="4" placeholder="<?php esc_attr_e('Describe what your business does in simple terms. Recommended: 1-3 sentences.', 'ai-chat-assistant-for-business'); ?>"><?php echo esc_textarea($options['description'] ?? ''); ?></textarea>
                        <p class="businessbot-help"><?php esc_html_e('Describe what your business does in simple terms. Recommended: 1-3 sentences.', 'ai-chat-assistant-for-business'); ?></p>
                    </div>
                    <div class="businessbot-field">
                        <label for="businessbot_products_services"><?php esc_html_e('Products or Services', 'ai-chat-assistant-for-business'); ?></label>
                        <textarea id="businessbot_products_services" name="businessbot_data[products_services]" rows="4" placeholder="<?php esc_attr_e('Home decor, kitchen tools, furniture, gift items', 'ai-chat-assistant-for-business'); ?>"><?php echo esc_textarea($options['products_services'] ?? ''); ?></textarea>
                        <p class="businessbot-help"><?php esc_html_e('List key products or services separated by commas.', 'ai-chat-assistant-for-business'); ?></p>
                    </div>
                    <div class="businessbot-field">
                        <label for="businessbot_customers"><?php esc_html_e('Typical Customers', 'ai-chat-assistant-for-business'); ?></label>
                        <textarea id="businessbot_customers" name="businessbot_data[customers]" rows="3" placeholder="<?php esc_attr_e('Families, working professionals, first-time homeowners', 'ai-chat-assistant-for-business'); ?>"><?php echo esc_textarea($options['customers'] ?? ''); ?></textarea>
                        <p class="businessbot-help"><?php esc_html_e('Mention who you primarily serve so answers stay relevant.', 'ai-chat-assistant-for-business'); ?></p>
                    </div>
                </div>

                <div class="businessbot-card">
                    <h2><span class="dashicons dashicons-format-chat"></span><?php esc_html_e('Brand Personality', 'ai-chat-assistant-for-business'); ?></h2>
                    <div class="businessbot-field">
                        <label for="businessbot_tone"><?php esc_html_e('Assistant Tone / Personality', 'ai-chat-assistant-for-business'); ?></label>
                        <select id="businessbot_tone" name="businessbot_data[tone]">
                            <option value="Friendly" <?php selected($current_tone, 'Friendly'); ?>><?php esc_html_e('Friendly', 'ai-chat-assistant-for-business'); ?></option>
                            <option value="Professional" <?php selected($current_tone, 'Professional'); ?>><?php esc_html_e('Professional', 'ai-chat-assistant-for-business'); ?></option>
                            <option value="Casual" <?php selected($current_tone, 'Casual'); ?>><?php esc_html_e('Casual', 'ai-chat-assistant-for-business'); ?></option>
                            <option value="Witty" <?php selected($current_tone, 'Witty'); ?>><?php esc_html_e('Witty', 'ai-chat-assistant-for-business'); ?></option>
                            <option value="Empathetic" <?php selected($current_tone, 'Empathetic'); ?>><?php esc_html_e('Empathetic', 'ai-chat-assistant-for-business'); ?></option>
                            <option value="Direct" <?php selected($current_tone, 'Direct'); ?>><?php esc_html_e('Direct', 'ai-chat-assistant-for-business'); ?></option>
                        </select>
                        <p id="businessbot-tone-help" class="businessbot-help"><?php echo esc_html($tone_descriptions[$current_tone] ?? $tone_descriptions['Friendly']); ?></p>
                    </div>
                </div>

                <div class="businessbot-card">
                    <h2><span class="dashicons dashicons-phone"></span><?php esc_html_e('Contact Information', 'ai-chat-assistant-for-business'); ?></h2>
                    <div class="businessbot-grid businessbot-grid-two">
                        <div class="businessbot-field">
                            <label for="businessbot_contact_number"><?php esc_html_e('Contact Number', 'ai-chat-assistant-for-business'); ?></label>
                            <input id="businessbot_contact_number" type="tel" name="businessbot_data[contact_number]" value="<?php echo esc_attr($options['contact_number'] ?? ''); ?>" placeholder="<?php esc_attr_e('+91 00000 00000', 'ai-chat-assistant-for-business'); ?>">
                        </div>
                        <div class="businessbot-field">
                            <label for="businessbot_business_email"><?php esc_html_e('Support/Business Email', 'ai-chat-assistant-for-business'); ?></label>
                            <input id="businessbot_business_email" type="email" name="businessbot_data[businessEmails]" value="<?php echo esc_attr($options['businessEmails'] ?? ''); ?>" placeholder="<?php esc_attr_e('support@example.com', 'ai-chat-assistant-for-business'); ?>">
                        </div>
                    </div>
                    <div class="businessbot-field">
                        <label for="businessbot_address"><?php esc_html_e('Business Address', 'ai-chat-assistant-for-business'); ?></label>
                        <textarea id="businessbot_address" name="businessbot_data[address]" rows="3" placeholder="<?php esc_attr_e('Shop No. 12, Main Road, City, State, ZIP', 'ai-chat-assistant-for-business'); ?>"><?php echo esc_textarea($options['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="businessbot-field">
                        <label for="businessbot_hours"><?php esc_html_e('Business Hours', 'ai-chat-assistant-for-business'); ?></label>
                        <textarea id="businessbot_hours" name="businessbot_data[hours]" rows="3" placeholder="<?php esc_attr_e('Mon-Sat: 10:00 AM - 6:00 PM | Sun: Closed', 'ai-chat-assistant-for-business'); ?>"><?php echo esc_textarea($options['hours'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="businessbot-card">
                    <h2><span class="dashicons dashicons-editor-help"></span><?php esc_html_e('Customer Guidance', 'ai-chat-assistant-for-business'); ?></h2>
                    <div class="businessbot-field">
                        <label for="businessbot_common_questions"><?php esc_html_e('Common Customer Questions (FAQs)', 'ai-chat-assistant-for-business'); ?></label>
                        <textarea id="businessbot_common_questions" name="businessbot_data[common_questions]" rows="4" placeholder="<?php esc_attr_e('Do you offer delivery? What is your return policy?', 'ai-chat-assistant-for-business'); ?>"><?php echo esc_textarea($options['common_questions'] ?? ''); ?></textarea>
                        <p class="businessbot-help"><?php esc_html_e('These help AI answer common questions accurately.', 'ai-chat-assistant-for-business'); ?></p>
                    </div>
                    <div class="businessbot-field">
                        <label for="businessbot_promotions"><?php esc_html_e('Promotions or Highlights', 'ai-chat-assistant-for-business'); ?></label>
                        <textarea id="businessbot_promotions" name="businessbot_data[promotions]" rows="3" placeholder="<?php esc_attr_e('Free delivery above $50. 10% off first order.', 'ai-chat-assistant-for-business'); ?>"><?php echo esc_textarea($options['promotions'] ?? ''); ?></textarea>
                        <p class="businessbot-help"><?php esc_html_e('Mention current offers so the AI can surface them naturally.', 'ai-chat-assistant-for-business'); ?></p>
                    </div>
                    <div class="businessbot-field">
                        <label for="businessbot_avoid"><?php esc_html_e('Avoid Saying', 'ai-chat-assistant-for-business'); ?></label>
                        <textarea id="businessbot_avoid" name="businessbot_data[avoid]" rows="3" placeholder="<?php esc_attr_e('Do not promise same-day delivery in all areas.', 'ai-chat-assistant-for-business'); ?>"><?php echo esc_textarea($options['avoid'] ?? ''); ?></textarea>
                        <p class="businessbot-help"><?php esc_html_e('Avoid Saying sets negative constraints for AI responses.', 'ai-chat-assistant-for-business'); ?></p>
                    </div>
                </div>

                <div class="businessbot-card">
                    <h2><span class="dashicons dashicons-sos"></span><?php esc_html_e('Support & Greeting', 'ai-chat-assistant-for-business'); ?></h2>
                    <div class="businessbot-field">
                        <label for="businessbot_faq_link"><?php esc_html_e('Help Center or Contact Page URL', 'ai-chat-assistant-for-business'); ?></label>
                        <input id="businessbot_faq_link" type="url" name="businessbot_data[faq_link]" value="<?php echo esc_attr($options['faq_link'] ?? ''); ?>" placeholder="<?php esc_attr_e('https://example.com/help', 'ai-chat-assistant-for-business'); ?>">
                    </div>
                    <div class="businessbot-field">
                        <label for="businessbot_initial_greeting"><?php esc_html_e('Initial Greeting Message', 'ai-chat-assistant-for-business'); ?></label>
                        <textarea id="businessbot_initial_greeting" name="businessbot_data[initial_greeting]" rows="3" placeholder="<?php esc_attr_e('Hi there! Welcome to our store. How can I help you today?', 'ai-chat-assistant-for-business'); ?>"><?php echo esc_textarea($options['initial_greeting'] ?? ''); ?></textarea>
                        <p class="businessbot-help"><?php esc_html_e('This is the first message visitors see when chat opens.', 'ai-chat-assistant-for-business'); ?></p>
                        <div class="businessbot-preview">
                            <strong><?php esc_html_e('Preview:', 'ai-chat-assistant-for-business'); ?></strong>
                            <p id="businessbot-greeting-preview"><?php echo esc_html($current_greeting); ?></p>
                        </div>
                    </div>
                </div>

                <div class="businessbot-footer-actions">
                    <?php submit_button(__('Save Changes', 'ai-chat-assistant-for-business'), 'primary', 'submit', false, ['class' => 'button button-primary button-large']); ?>
                </div>
            </div>
        </form>
    </div>
    <script>
        window.businessbotToneDescriptions = <?php echo wp_json_encode($tone_descriptions); ?>;
    </script>
    <?php
}
