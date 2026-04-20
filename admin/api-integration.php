<?php
// Block direct access
defined('ABSPATH') || exit;

function businessbot_chat_assist_integration_page() {
    $api_key = get_option('businessbot_api_key');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('API Integration', 'businessbot-ai-chat'); ?></h1>
        <form method="post" action="options.php" style="max-width: 600px; margin-top: 20px;">
            <?php
            settings_fields('businessbot_api_options');
            do_settings_sections('businessbot_api_options');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Gemini API Key', 'businessbot-ai-chat'); ?></th>
                    <td>
                        <input type="text"
                            name="businessbot_api_key"
                            value="<?php echo esc_attr($api_key); ?>"
                            class="regular-text"
                            placeholder="<?php esc_attr_e('Enter your Gemini API Key', 'businessbot-ai-chat'); ?>">
                        <p class="description"><?php esc_html_e('Your API key is securely stored in the WordPress database.', 'businessbot-ai-chat'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(esc_html__('Save API Key', 'businessbot-ai-chat')); ?>
        </form>

        <div style="margin-top: 40px; background: #fff; padding: 20px; border-left: 4px solid #0073aa;">
            <h2>🧭 <?php esc_html_e('How to Get Your Gemini API Key', 'businessbot-ai-chat'); ?></h2>
            <ol style="line-height: 1.8; padding-left: 20px;">
                <li><a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer">https://aistudio.google.com/app/apikey</a></li>
                <li><?php esc_html_e('Log in with your Google account.', 'businessbot-ai-chat'); ?></li>
                <li><?php esc_html_e('Click', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('“Get API Key”', 'businessbot-ai-chat'); ?></strong></li>
                <li><?php esc_html_e('Accept the terms by checking both boxes and clicking', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('“I accept”', 'businessbot-ai-chat'); ?></strong></li>
                <li><?php esc_html_e('Click', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('“+ Create API Key”', 'businessbot-ai-chat'); ?></strong></li>
                <li><?php esc_html_e('Choose', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('“Create API Key in new project”', 'businessbot-ai-chat'); ?></strong></li>
                <li><?php esc_html_e('Once the key is generated, click', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('“Copy”', 'businessbot-ai-chat'); ?></strong></li>
                <li><?php esc_html_e('Paste it in the field above and click', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('Save API Key', 'businessbot-ai-chat'); ?></strong></li>
            </ol>
            <p style="color: #555;"><?php esc_html_e('Never share your API key publicly. Keep it confidential.', 'businessbot-ai-chat'); ?></p>
        </div>
    </div>
    <?php
}
