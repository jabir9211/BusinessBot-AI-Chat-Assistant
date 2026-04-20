<?php
defined('ABSPATH') || exit;

// Start session securely (not ideal long-term; consider transients or user meta)
if (!session_id()) {
    session_start();
}

// Generate system prompt with business data
function businessbot_chat_assist_generate_prompt() {
    $options = get_option('businessbot_data');

    if (!$options) return '';

    $site_url = site_url();
    $business_name     = $options['business_name'] ?? 'our business';
    $description       = $options['description'] ?? '';
    $products_services = $options['products_services'] ?? '';
    $customers         = $options['customers'] ?? '';
    $tone              = $options['tone'] ?? 'Professional';
    $hours             = $options['hours'] ?? '';
    $questions         = $options['common_questions'] ?? '';
    $promotions        = $options['promotions'] ?? '';
    $avoid             = $options['avoid'] ?? '';
    $faq_link          = $options['faq_link'] ?? $site_url;
    $address           = $options['address'] ?? '';
    $phone             = $options['contact_number'] ?? '';
    $email             = $options['businessEmails'] ?? '';
    $website           = $site_url;

    $prompt = "You are a smart, friendly AI Support Assistant working for \"{$business_name}\". Think of yourself as a helpful member of our customer service team.\n\n";

    $prompt .= "Your responses should be:\n";
    $prompt .= "- Helpful, easy to understand, and supportive\n";
    $prompt .= "- Friendly but still professional (tone: {$tone})\n";
    $prompt .= "- Occasionally use light conversational touches to feel human and approachable\n";
    $prompt .= "- Use **brief** formatting (bolding or bullet points) only when useful\n\n";

    $prompt .= "### Business Info\n\n";
    $prompt .= "- Website: {$website}\n";
    $prompt .= "- Description: {$description}\n";
    $prompt .= "- Products/Services: {$products_services}\n";
    $prompt .= "- Customers: {$customers}\n";
    $prompt .= "- Hours: {$hours}\n";
    $prompt .= "- Address: {$address}\n";
    $prompt .= "- Phone: {$phone}\n";
    $prompt .= "- Email: {$email}\n";
    $prompt .= "- Help Page: {$faq_link}\n";
    $prompt .= "- Promotions: {$promotions}\n";
    $prompt .= "- FAQs: {$questions}\n";
    $prompt .= "- Avoid Saying: {$avoid}\n\n";

    $prompt .= "### How to Respond:\n";
    $prompt .= "1. Begin replies in a warm, conversational way when possible. For example:\n";
    $prompt .= "- \"Hey there! Great question.\"\n";
    $prompt .= "- \"Sure, I’d be happy to help with that!\"\n";
    $prompt .= "- \"Absolutely! Let me walk you through it.\"\n";
    $prompt .= "2. If unsure of an answer, never guess. Instead, gently guide the user to our support:\n";
    $prompt .= "- \"I’m not 100% sure, but our team can help you out at [{$faq_link}] or via {$phone}.\"\n";
    $prompt .= "3. Stay on-topic, helpful, and never break character as an AI assistant.\n";
    $prompt .= "4. Be proactive — if the user might need something extra, suggest it helpfully.\n\n";

    $prompt .= "### Example:\n\n";
    $prompt .= "**User:** Do you offer home installation?\n";
    $prompt .= "**Assistant:**\n";
    $prompt .= "Great question! I’m not totally sure, but the team would love to help. You can contact them directly [here]({$faq_link}) or give us a call at {$phone}.\n\n";

    $prompt .= "Let's begin. Keep the tone human-like but always aligned with helpful, branded support.";

    return $prompt;
}

// Call Gemini API with chat history
function businessbot_chat_assist_query_gemini($user_input) {
    $api_key = get_option('businessbot_api_key');

    if (!$api_key) {
        return "API key is not set. Please go to the Integration page to add your Gemini API key.";
    }

    // Initialize chat history
    if (!isset($_SESSION['businessbot_chat'])) {
        $_SESSION['businessbot_chat'] = [];

        // Inject system prompt
        $_SESSION['businessbot_chat'][] = [
            'role'  => 'user',
            'parts' => [[
                'text' => businessbot_chat_assist_generate_prompt() . "\n\nUser: " . $user_input
            ]]
        ];
    } else {
        $_SESSION['businessbot_chat'][] = [
            'role'  => 'user',
            'parts' => [['text' => $user_input]]
        ];
    }

    $chat_history = [];
	function get_sanitized_chat_session() {
    $output = [];

    if (!isset($_SESSION['businessbot_chat']) || !is_array($_SESSION['businessbot_chat'])) {
        return $output;
    }

    foreach ($_SESSION['businessbot_chat'] as $entry) {
		if (
				is_array($entry) &&
				isset($entry['role'], $entry['parts']) &&
				in_array($entry['role'], ['user', 'model'], true) &&
				is_array($entry['parts'])
			) {
				$sanitized_parts = [];

				foreach ($entry['parts'] as $part) {
					if (is_array($part) && isset($part['text'])) {
						$sanitized_parts[] = [
							'text' => sanitize_textarea_field($part['text'])
						];
					}
				}

				$output[] = [
					'role'  => sanitize_text_field($entry['role']),
					'parts' => $sanitized_parts
				];
			}
		}

		return $output;
	}

	$validated_contents = get_sanitized_chat_session();

    $payload = [
        'contents' => array_values($validated_contents)
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    $args = [
        'headers' => [
            'Content-Type'   => 'application/json',
            'x-goog-api-key' => $api_key
        ],
        'body'    => json_encode($payload),
        'method'  => 'POST',
        'timeout' => 20,
    ];

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        return "Request error: " . esc_html($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
        $reply = $body['candidates'][0]['content']['parts'][0]['text'];

        $_SESSION['businessbot_chat'][] = [
            'role'  => 'model',
            'parts' => [['text' => $reply]]
        ];

        return wp_kses_post($reply);
    }

    return "Gemini returned no valid response.";
}

// AJAX Hooks
add_action('wp_ajax_nopriv_businessbot_send', 'businessbot_chat_assist_ajax_handler');
add_action('wp_ajax_businessbot_send', 'businessbot_chat_assist_ajax_handler');

function businessbot_chat_assist_ajax_handler() {
    check_ajax_referer('businessbot_nonce', '_ajax_nonce');

    if (!isset($_POST['message'])) {
        wp_send_json_error('Missing message');
        exit;
    }

    $user_input = sanitize_text_field(wp_unslash($_POST['message']));
    $assistant_response = businessbot_chat_assist_query_gemini($user_input);

    if ($assistant_response) {
        wp_send_json_success($assistant_response);
    } else {
        wp_send_json_error('AI failed to respond');
    }

    exit;  // Make sure nothing else is output after JSON
}

