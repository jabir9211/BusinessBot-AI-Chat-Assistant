<?php
// Block direct access
defined('ABSPATH') || exit;

// Register settings
add_action('admin_init', 'businessbot_chat_assist_settings_init');

function businessbot_chat_assist_settings_init() {
    register_setting('businessbot_options', 'businessbot_data', [
        'sanitize_callback' => 'businessbot_chat_assist_sanitize_business_data',
    ]);

    register_setting('businessbot_api_options', 'businessbot_api_key', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);
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

function businessbot_chat_assist_settings_page() {
    $options = get_option('businessbot_data');
    ?>
    <div class="wrap">
       <h1><?php esc_html_e('Business Profile', 'businessbot-ai-chat'); ?></h1>
        <p class="description"><?php esc_html_e('Fill out these details to help your AI Assistant provide accurate, helpful, and on-brand responses to your customers.', 'businessbot-ai-chat'); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields('businessbot_options'); ?>
            <table class="form-table">
                <?php
                $fields = [
                    'business_name' => ['label' => 'Business Name', 'type' => 'text'],
                    'description' => ['label' => 'Business Overview', 'type' => 'textarea', 'rows' => 7],
                    'products_services' => ['label' => 'Products or Services', 'type' => 'textarea', 'rows' => 5],
                    'customers' => ['label' => 'Typical Customers', 'type' => 'textarea', 'rows' => 5],
                    'tone' => ['label' => 'Assistant Tone / Personality', 'type' => 'select'],
                    'address' => ['label' => 'Business Address', 'type' => 'textarea', 'rows' => 3],
                    'contact_number' => ['label' => 'Contact Number', 'type' => 'tel'],
                    'businessEmails' => ['label' => 'Support/Business Email', 'type' => 'email'],
                    'hours' => ['label' => 'Business Hours', 'type' => 'textarea', 'rows' => 5],
                    'common_questions' => ['label' => 'Common Customer Questions', 'type' => 'textarea', 'rows' => 3],
                    'promotions' => ['label' => 'Promotions or Highlights', 'type' => 'textarea', 'rows' => 5],
                    'avoid' => ['label' => 'Avoid Saying', 'type' => 'textarea', 'rows' => 5],
                    'faq_link' => ['label' => 'Help Center or Contact Page URL', 'type' => 'url'],
                    'initial_greeting' => ['label' => 'Initial Greeting Message', 'type' => 'textarea', 'rows' => 5],
                ];

                foreach ($fields as $key => $field) {
                    $value = $options[$key] ?? '';
                    echo '<tr>';
                    echo '<th scope="row">' . esc_html($field['label']) . '</th>';
                    echo '<td>';

                    switch ($field['type']) {
                        case 'text':
                        case 'email':
                        case 'tel':
                        case 'url':
                            echo '<input type="' . esc_attr($field['type']) . '" name="businessbot_data[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text">';
                            break;

                        case 'textarea':
                            $rows = $field['rows'] ?? 5;
                            echo '<textarea name="businessbot_data[' . esc_attr($key) . ']" rows="' . esc_attr($rows) . '" class="large-text">' . esc_textarea($value) . '</textarea>';
                            break;

                        case 'select':
                            $tone_options = [
                                'Friendly' => __('Friendly – Warm, welcoming, and informal', 'businessbot-ai-chat'),
                                'Professional' => __('Professional – Formal, clear, and respectful', 'businessbot-ai-chat'),
                                'Casual' => __('Casual – Relaxed and conversational', 'businessbot-ai-chat'),
                                'Witty' => __('Witty – Fun, clever, and humorous', 'businessbot-ai-chat'),
                                'Empathetic' => __('Empathetic – Understanding and supportive', 'businessbot-ai-chat'),
                                'Direct' => __('Direct – Straightforward and no-nonsense', 'businessbot-ai-chat'),
                            ];

                            echo '<select name="businessbot_data[tone]" class="regular-text">';
                            foreach ($tone_options as $val => $label) {
                                echo '<option value="' . esc_attr($val) . '" ' . selected($value, $val, false) . '>' . esc_html($label) . '</option>';
                            }
                            echo '</select>';
                            break;
                    }

                    // Description
                    echo '<p class="description">' . esc_html__('Provide relevant information for this field.', 'businessbot-ai-chat') . '</p>';

                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </table>

            <?php submit_button(__('Save Business Info', 'businessbot-ai-chat')); ?>
        </form>
    </div>
    <?php
}
