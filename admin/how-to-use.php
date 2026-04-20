<?php
// Block direct access
defined('ABSPATH') || exit;

function businessbot_chat_assist_how_to_use_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('📘 How to Use: BusinessBot AI Chat Assistant', 'businessbot-ai-chat'); ?></h1>
        <p style="font-size: 16px; max-width: 800px;">
            <?php esc_html_e('Welcome! This guide will help you set up and start using your AI Support Executive on your website. Just follow these simple steps.', 'businessbot-ai-chat'); ?>
        </p>

        <ol style="line-height: 1.9; font-size: 15px; max-width: 800px;">
            <li><strong><?php esc_html_e('Step 1: Install the Plugin', 'businessbot-ai-chat'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Download or upload the plugin to your WordPress website.', 'businessbot-ai-chat'); ?></li>
                    <li><?php esc_html_e('Go to your WordPress dashboard, then to', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('Plugins > Add New', 'businessbot-ai-chat'); ?></strong>.</li>
                    <li><?php esc_html_e('Click', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('Upload Plugin', 'businessbot-ai-chat'); ?></strong>, <?php esc_html_e('select the ZIP file, and install it.', 'businessbot-ai-chat'); ?></li>
                    <li><?php esc_html_e('After installation, click', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('Activate', 'businessbot-ai-chat'); ?></strong>.</li>
                </ul>
            </li>

            <li><strong><?php esc_html_e('Step 2: Add Your Business Information', 'businessbot-ai-chat'); ?></strong>
                <ul>
                    <li><?php esc_html_e('From your WordPress dashboard, go to', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('BusinessBot  > Details', 'businessbot-ai-chat'); ?></strong>.</li>
                    <li><?php esc_html_e('You will see a form asking for information like:', 'businessbot-ai-chat'); ?>
                        <ul>
                            <li><?php esc_html_e('Your Business Name', 'businessbot-ai-chat'); ?></li>
                            <li><?php esc_html_e('What your business does (services, products)', 'businessbot-ai-chat'); ?></li>
                            <li><?php esc_html_e('Your mission or goal', 'businessbot-ai-chat'); ?></li>
                            <li><?php esc_html_e('Contact details (email, phone, etc.)', 'businessbot-ai-chat'); ?></li>
                            <li><?php esc_html_e('Preferred tone (formal, friendly, professional, etc.)', 'businessbot-ai-chat'); ?></li>
                        </ul>
                    </li>
                    <li><?php esc_html_e('Fill out the form as completely and clearly as possible.', 'businessbot-ai-chat'); ?></li>
                    <li><?php esc_html_e('This information will help the AI give smart and relevant replies to your visitors.', 'businessbot-ai-chat'); ?></li>
                </ul>
            </li>

            <li><strong><?php esc_html_e('Step 3: Connect with Gemini AI', 'businessbot-ai-chat'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Now go to', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('BusinessBot > Integration', 'businessbot-ai-chat'); ?></strong>.</li>
                    <li><?php esc_html_e('You need a Gemini API Key. Follow the instructions shown there, or:', 'businessbot-ai-chat'); ?>
                        <ol>
                            <li><a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer">https://aistudio.google.com/app/apikey</a></li>
                            <li><?php esc_html_e('Sign in with your Google account.', 'businessbot-ai-chat'); ?></li>
                            <li><?php esc_html_e('Click on', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('"Create API Key"', 'businessbot-ai-chat'); ?></strong>.</li>
                            <li><?php esc_html_e('Copy the API Key shown.', 'businessbot-ai-chat'); ?></li>
                        </ol>
                    </li>
                    <li><?php esc_html_e('Paste the API Key into the field in the plugin’s Integration page.', 'businessbot-ai-chat'); ?></li>
                    <li><?php esc_html_e('Click the', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('Save API Key', 'businessbot-ai-chat'); ?></strong> <?php esc_html_e('button.', 'businessbot-ai-chat'); ?></li>
                    <li>✅ <?php esc_html_e('Done! Your AI Assistant is now powered by Gemini.', 'businessbot-ai-chat'); ?></li>
                </ul>
            </li>

            <li><strong><?php esc_html_e('Step 4: Customize Chatbot Display', 'businessbot-ai-chat'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Go to', 'businessbot-ai-chat'); ?> <strong><?php esc_html_e('BusinessBot > Settings', 'businessbot-ai-chat'); ?></strong>.</li>
                    <li><strong><?php esc_html_e('Enable Chatbot:', 'businessbot-ai-chat'); ?></strong> <?php esc_html_e('Check this box to show the chatbot icon on your front-end. If unchecked, the chatbot will not load anywhere on your site.', 'businessbot-ai-chat'); ?></li>
                    <li><strong><?php esc_html_e('Auto Open Chatbot (First Visit Only):', 'businessbot-ai-chat'); ?></strong> <?php esc_html_e('Enable this option if you want the chatbot to open automatically the first time a visitor lands on your site (per session). After the first time, only the icon will appear and visitors can open it manually.', 'businessbot-ai-chat'); ?></li>
                </ul>
            </li>

            <li><strong><?php esc_html_e('Step 5: See It in Action!', 'businessbot-ai-chat'); ?></strong>
                <ul>
                    <li><?php esc_html_e('Visit your website’s front end (the public view).', 'businessbot-ai-chat'); ?></li>
                    <li><?php esc_html_e('You’ll see a chat icon at the bottom-right corner of the screen.', 'businessbot-ai-chat'); ?></li>
                    <li><?php esc_html_e('This is your AI Support Executive — always ready to help visitors.', 'businessbot-ai-chat'); ?></li>
                    <li><?php esc_html_e('Click it to open the chat. The assistant will start with a friendly welcome message and begin chatting based on the business info you provided.', 'businessbot-ai-chat'); ?></li>
                </ul>
            </li>

            <li><strong><?php esc_html_e('What’s Next?', 'businessbot-ai-chat'); ?></strong>
                <ul>
                    <li><?php esc_html_e('In future updates, we will add more features like:', 'businessbot-ai-chat'); ?>
                        <ul>
                            <li><?php esc_html_e('Support for more AI models', 'businessbot-ai-chat'); ?></li>
                            <li><?php esc_html_e('Custom chat widget designs', 'businessbot-ai-chat'); ?></li>
                            <li><?php esc_html_e('Chat history and analytics', 'businessbot-ai-chat'); ?></li>
                        </ul>
                    </li>
                    <li><?php esc_html_e('Stay tuned! 🚀', 'businessbot-ai-chat'); ?></li>
                </ul>
            </li>
        </ol>

        <div style="margin-top: 30px; background: #e9f9ef; padding: 20px; border-left: 4px solid #28a745;">
            <p><strong>✨ <?php esc_html_e('Bonus Update:', 'businessbot-ai-chat'); ?></strong> <?php esc_html_e('We’ve also improved the overall chatbot UI for a cleaner and more engaging experience for your visitors.', 'businessbot-ai-chat'); ?></p>
        </div>

        <div style="margin-top: 20px; background: #fffbe5; padding: 20px; border-left: 4px solid #ffba00;">
            <p><strong>💡 <?php esc_html_e('Tip:', 'businessbot-ai-chat'); ?></strong> <?php esc_html_e('The more detailed and accurate your business information is, the smarter your AI assistant will respond to your customers.', 'businessbot-ai-chat'); ?></p>
        </div>
    </div>
    <?php
}
