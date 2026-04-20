<?php
function businessbot_chat_assist_front_settings_page() {
    // Process form data if submitted
    if (isset($_POST['_wpnonce']) && check_admin_referer('businessbot_settings_form')) {
        // Security check
        if (!current_user_can('manage_options')) return;

        // Sanitize input values
        $chatbot_enabled = isset($_POST['chatbot_enabled']) ? 'yes' : 'no';
        $chatbot_auto_open = isset($_POST['chatbot_auto_open']) ? 'yes' : 'no';

        // Update options in WP database
        update_option('businessbot_chatbot_enabled', $chatbot_enabled);
        update_option('businessbot_chatbot_auto_open', $chatbot_auto_open);

        echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__('Settings saved successfully.', 'businessbot-ai-chat') . '</strong></p></div>';
    }

    // Get saved values
    $chatbot_enabled = get_option('businessbot_chatbot_enabled');
    $chatbot_auto_open = get_option('businessbot_chatbot_auto_open');

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e('BusinessBot Chat Settings', 'businessbot-ai-chat'); ?></h1>
        <p class="description"><?php esc_html_e('Configure how the chatbot behaves on your website front-end.', 'businessbot-ai-chat'); ?></p>

        <form method="post" action="">
            <?php wp_nonce_field('businessbot_settings_form'); ?>

            <table class="form-table" role="presentation">
                <tbody>

                    <tr>
                        <th scope="row">
                            <label for="chatbot_enabled"><?php esc_html_e('Enable Chatbot', 'businessbot-ai-chat'); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <label for="chatbot_enabled">
                                    <input type="checkbox" name="chatbot_enabled" id="chatbot_enabled" value="yes" <?php checked('yes', $chatbot_enabled); ?> />
                                    <?php esc_html_e('Enable the AI chatbot to appear on the front-end.', 'businessbot-ai-chat'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('If disabled, the chatbot will not load anywhere on your website.', 'businessbot-ai-chat'); ?></p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="chatbot_auto_open"><?php esc_html_e('Auto Open Chatbot on First Visit Only(Per Session)', 'businessbot-ai-chat'); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <label for="chatbot_auto_open">
                                    <input type="checkbox" name="chatbot_auto_open" id="chatbot_auto_open" value="yes" <?php checked('yes', $chatbot_auto_open); ?> />
                                    <?php esc_html_e('Automatically open the chatbot once per user session (typically at the first page load, such as the homepage). Afterward, the chatbot icon will appear, and the user can manually open the chat.', 'businessbot-ai-chat'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('If unchecked, the chatbot will remain minimized until the user clicks the icon. Recommended for better user experience to avoid annoying repetitive pop-ups.', 'businessbot-ai-chat'); ?></p>
                            </fieldset>
                        </td>
                    </tr>

                </tbody>
            </table>

            <?php submit_button(__('Save Settings', 'businessbot-ai-chat')); ?>
        </form>
    </div>
    <?php
}


