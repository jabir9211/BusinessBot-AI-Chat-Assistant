=== BusinessBot AI Chat Assistant ===
Contributors: jabir20
Tags: ai assistant, support chat, gemini, customer support, chatbot, business assistant
Requires at least: 5.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: ai assistant, support chat, gemini, chatbot, business assistant


An AI-powered support assistant that uses Gemini GPT Api to interact with your site visitors using your business data.

== Description ==

**AI Assistant for Business** is a personalized BusinessBot AI Chat Assistant  plugin for WordPress that acts like a real-time support executive on your website. It uses Google Gemini AI to understand your business, based on the information you provide in the admin panel.

**Key Features:**

- Collect essential business details to personalize responses.
- Gemini API integration for AI capabilities.
- Sticky chat button in the front-end footer.
- Easy-to-follow guide to generate your API key.
- Seamless and intelligent communication with site visitors.

== Installation ==

1. Upload the plugin ZIP file to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to `AI Assistant > Details` to enter your business information.
4. Go to `AI Assistant > Integration` to add your Gemini API key.
5. That’s it! The AI chat icon will appear on your site automatically.

== Frequently Asked Questions ==

= Do I need a Gemini API key? =
Yes, the plugin requires a Gemini API key to function. You can generate one for free via [Google AI Studio](https://aistudio.google.com/app/apikey).

= Is the API key secure? =
Yes, the API key is stored in the WordPress database using `get_option` and is not publicly exposed.

= Can I customize the chat widget? =
Not yet, but customization features are planned in future updates.

== Screenshots ==

1. Admin Dashboard Menu
2. Business Details Form
3. API Integration Settings
4. Chat Widget on Frontend
5. For Settings

== Changelog ==

= 1.0 =
* Initial release with Gemini integration and AI assistant functionality.

= 1.1 =
* New option to Enable/Disable Chat Icon.
* Added control to stop annoying popups from showing every time.
* UI enhanced with better responsiveness and smoother experience.

= 2.0 =
* Complete admin UI redesign across Details, Integration, Settings, and How to Use pages.
* Added guided onboarding flow with dynamic setup progress tracking.
* Added API connection testing with status feedback and improved key management UX.

= 1.2 =
* Improved best practices.
== External Services ==

This plugin uses the **Gemini API** provided by Google to generate AI-powered responses based on your business details.

When a user interacts with the chat assistant, the plugin sends the current chat history (including business context and user questions) to the following endpoint:

`https://generativelanguage.googleapis.com/`

This request is made solely to retrieve relevant AI-generated replies. No personally identifiable information, login credentials, or sensitive user data is collected or transmitted.

For transparency and compliance, you may refer to Google’s policies:

- [Google Terms of Service](https://policies.google.com/terms)
- [Google Privacy Policy](https://policies.google.com/privacy)

== Privacy ==
	
This plugin does not:
- Track users
- Store personal data
- Send information to third parties (except the Gemini API prompt)
	
All data remains within your WordPress environment except when calling the Gemini API, which only transmits the AI prompt and business details as defined by the site administrator.

== Upgrade Notice ==

= 1.0 =
First stable release.

= 1.1 =
New option to Enable/Disable Chat Icon.
Added control to stop annoying popups from showing every time.
UI enhanced with better responsiveness and smoother experience.