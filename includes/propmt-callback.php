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
    $prompt .= "- Concise and to the point (usually 1-4 short lines)\n";
    $prompt .= "- Use bullet points only when listing services, steps, or options\n";
    $prompt .= "- Avoid long stories, filler text, and repetitive greetings\n\n";

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
    $prompt .= "1. Start directly with the answer. Keep intro text minimal.\n";
    $prompt .= "2. For direct factual questions (contact, email, address, hours, services), answer in 1-2 lines.\n";
    $prompt .= "3. If unsure, never guess. Say you are not certain and direct user to {$faq_link} or {$phone}.\n";
    $prompt .= "4. Stay on-topic, helpful, and never break character as an AI support assistant.\n";
    $prompt .= "5. If the user asks to be concise, respond in max 2 lines.\n\n";

    $prompt .= "### Example:\n\n";
    $prompt .= "**User:** Do you offer home installation?\n";
    $prompt .= "**Assistant:**\n";
    $prompt .= "Great question! I’m not totally sure, but the team would love to help. You can contact them directly [here]({$faq_link}) or give us a call at {$phone}.\n\n";

    $prompt .= "Let's begin. Prioritize concise, accurate, professional support answers.";

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

    $validated_contents = businessbot_chat_assist_get_sanitized_chat_session();

    $payload = [
        'contents' => array_values($validated_contents)
    ];

    $model_chain = businessbot_chat_assist_build_runtime_model_chain($api_key);
    $last_error = businessbot_chat_assist_get_service_unavailable_message();
    $max_attempts = 3;
    $attempts = 0;

    foreach ($model_chain as $model) {
        $attempts++;
        if ($attempts > $max_attempts) {
            break;
        }

        $result = businessbot_chat_assist_call_gemini_model($api_key, $model, $payload);
        if ($result['success']) {
            $reply = businessbot_chat_assist_normalize_assistant_reply($result['reply']);
            $_SESSION['businessbot_chat'][] = [
                'role'  => 'model',
                'parts' => [['text' => $reply]]
            ];
            return wp_kses_post($reply);
        }

        $last_error = $result['message'];
        if (!$result['retry_next']) {
            break;
        }
    }

    $fallback_reply = businessbot_chat_assist_local_business_fallback_answer($user_input);
    if ('' !== $fallback_reply) {
        $_SESSION['businessbot_chat'][] = [
            'role'  => 'model',
            'parts' => [['text' => $fallback_reply]]
        ];
        return wp_kses_post($fallback_reply);
    }

    return businessbot_chat_assist_normalize_assistant_reply($last_error);
}

function businessbot_chat_assist_get_sanitized_chat_session() {
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

            if (!empty($sanitized_parts)) {
                $output[] = [
                    'role'  => sanitize_text_field($entry['role']),
                    'parts' => $sanitized_parts
                ];
            }
        }
    }

    return $output;
}

function businessbot_chat_assist_get_model_fallback_chain() {
    // Safe defaults only. Newer models (including 3.x) are auto-added from ListModels when available.
    $default_models = [
        'gemini-2.5-flash',
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
    ];

    $models = apply_filters('businessbot_gemini_model_chain', $default_models);
    if (!is_array($models) || empty($models)) {
        return $default_models;
    }

    $models = array_map('sanitize_text_field', $models);
    return array_values(array_filter(array_unique($models)));
}

function businessbot_chat_assist_call_gemini_model($api_key, $model, $payload) {
    $url = sprintf(
        'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
        rawurlencode($model)
    );

    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type'   => 'application/json',
            'x-goog-api-key' => $api_key
        ],
        'body'    => wp_json_encode($payload),
        'method'  => 'POST',
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        return [
            'success' => false,
            'retry_next' => true,
            'message' => __('AI is temporarily unavailable. Please try again.', 'businessbot-ai-chat'),
            'model' => $model,
            'status_code' => 0,
            'error_message' => sanitize_text_field($response->get_error_message()),
        ];
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    $reply = businessbot_chat_assist_extract_gemini_reply($body);

    if ('' !== $reply) {
        return [
            'success' => true,
            'retry_next' => false,
            'reply' => $reply,
            'message' => '',
            'model' => $model,
            'status_code' => $status_code,
            'error_message' => '',
        ];
    }

    $api_error_message = '';
    if (isset($body['error']['message']) && is_string($body['error']['message'])) {
        $api_error_message = sanitize_text_field($body['error']['message']);
    }

    $retryable = businessbot_chat_assist_is_retryable_model_error($status_code, $api_error_message, $body);
    $fallback_message = __('AI is temporarily unavailable. Please try again.', 'businessbot-ai-chat');

    return [
        'success' => false,
        'retry_next' => $retryable,
        'message' => $fallback_message,
        'model' => $model,
        'status_code' => $status_code,
        'error_message' => $api_error_message,
    ];
}

function businessbot_chat_assist_extract_gemini_reply($body) {
    if (!is_array($body) || empty($body['candidates']) || !is_array($body['candidates'])) {
        return '';
    }

    foreach ($body['candidates'] as $candidate) {
        if (!isset($candidate['content']['parts']) || !is_array($candidate['content']['parts'])) {
            continue;
        }

        foreach ($candidate['content']['parts'] as $part) {
            if (isset($part['text']) && is_string($part['text']) && '' !== trim($part['text'])) {
                return $part['text'];
            }
        }
    }

    return '';
}

function businessbot_chat_assist_is_retryable_model_error($status_code, $error_message, $body) {
    if (in_array($status_code, [404, 429, 500, 503], true)) {
        return true;
    }

    $error_blob = strtolower($error_message);
    if (!empty($body['error']) && is_array($body['error'])) {
        $error_blob .= ' ' . strtolower(wp_json_encode($body['error']));
    }

    $retry_tokens = [
        'quota',
        'resource_exhausted',
        'rate limit',
        'temporarily unavailable',
        'model not found',
        'not found',
    ];

    foreach ($retry_tokens as $token) {
        if (false !== strpos($error_blob, $token)) {
            return true;
        }
    }

    return false;
}

function businessbot_chat_assist_get_available_models($api_key) {
    $cache_key = 'businessbot_gemini_models_' . md5((string) $api_key);
    $cached = get_transient($cache_key);

    if (is_array($cached) && !empty($cached)) {
        return $cached;
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models';
    $response = wp_remote_get($url, [
        'headers' => [
            'x-goog-api-key' => $api_key,
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        return [];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($body) || empty($body['models']) || !is_array($body['models'])) {
        return [];
    }

    $available = [];
    foreach ($body['models'] as $model_info) {
        if (
            !isset($model_info['name']) ||
            !is_string($model_info['name']) ||
            !isset($model_info['supportedGenerationMethods']) ||
            !is_array($model_info['supportedGenerationMethods'])
        ) {
            continue;
        }

        if (!in_array('generateContent', $model_info['supportedGenerationMethods'], true)) {
            continue;
        }

        $model_name = str_replace('models/', '', sanitize_text_field($model_info['name']));
        if ('' === $model_name) {
            continue;
        }

        $available[] = $model_name;
    }

    $available = array_values(array_unique($available));
    if (!empty($available)) {
        set_transient($cache_key, $available, 6 * HOUR_IN_SECONDS);
    }

    return $available;
}

function businessbot_chat_assist_build_runtime_model_chain($api_key) {
    $preferred = businessbot_chat_assist_get_model_fallback_chain();
    $available = businessbot_chat_assist_get_available_models($api_key);

    if (empty($available)) {
        return $preferred;
    }

    $available_lookup = array_fill_keys($available, true);
    $runtime_chain = [];

    foreach ($preferred as $model) {
        if (isset($available_lookup[$model])) {
            $runtime_chain[] = $model;
        }
    }

    // Add any additional available flash models not in preferred list, prioritized by latest major version.
    $extra_flash = [];
    foreach ($available as $model) {
        if (
            false !== strpos($model, 'flash') &&
            !in_array($model, $runtime_chain, true)
        ) {
            $extra_flash[] = $model;
        }
    }
    usort($extra_flash, 'businessbot_chat_assist_compare_model_priority');
    $runtime_chain = array_merge($runtime_chain, $extra_flash);

    // Fallback to any available model if no flash model is found.
    if (empty($runtime_chain)) {
        $runtime_chain = $available;
    }

    return $runtime_chain;
}

function businessbot_chat_assist_compare_model_priority($a, $b) {
    $a_major = businessbot_chat_assist_extract_model_major($a);
    $b_major = businessbot_chat_assist_extract_model_major($b);

    if ($a_major === $b_major) {
        return strcmp($a, $b);
    }

    return $b_major <=> $a_major;
}

function businessbot_chat_assist_extract_model_major($model_name) {
    if (preg_match('/gemini-(\d+)(?:\.\d+)?/i', $model_name, $matches)) {
        return (int) $matches[1];
    }
    return 0;
}

function businessbot_chat_assist_normalize_assistant_reply($reply) {
    $reply = is_string($reply) ? trim($reply) : '';
    if ('' === $reply) {
        return __('AI is temporarily unavailable. Please try again.', 'businessbot-ai-chat');
    }

    $blocked_patterns = [
        'models/',
        'not supported for generatecontent',
        'call listmodels',
        'model gemini-',
        'api version v1beta',
    ];

    $lower = strtolower($reply);
    foreach ($blocked_patterns as $pattern) {
        if (false !== strpos($lower, $pattern)) {
            return businessbot_chat_assist_get_service_unavailable_message();
        }
    }

    return $reply;
}

function businessbot_chat_assist_local_business_fallback_answer($user_input) {
    $text = strtolower(trim((string) $user_input));
    if ('' === $text) {
        return '';
    }

    $options = get_option('businessbot_data', []);
    if (!is_array($options) || empty($options)) {
        return '';
    }

    $phone = trim((string) ($options['contact_number'] ?? ''));
    $email = trim((string) ($options['businessEmails'] ?? ''));
    $address = trim((string) ($options['address'] ?? ''));
    $hours = trim((string) ($options['hours'] ?? ''));
    $services = trim((string) ($options['products_services'] ?? ''));
    $business_name = trim((string) ($options['business_name'] ?? 'our business'));
    $faq_link = trim((string) ($options['faq_link'] ?? site_url()));

    if (businessbot_chat_assist_contains_any($text, ['contact', 'phone', 'number', 'call', 'mobile', 'whatsapp'])) {
        if ('' !== $phone) {
            return sprintf(__('You can reach us at %s.', 'businessbot-ai-chat'), $phone);
        }
    }

    if (businessbot_chat_assist_contains_any($text, ['email', 'mail', 'e-mail'])) {
        if ('' !== $email) {
            return sprintf(__('You can email us at %s.', 'businessbot-ai-chat'), $email);
        }
    }

    if (businessbot_chat_assist_contains_any($text, ['address', 'location', 'where', 'shop'])) {
        if ('' !== $address) {
            return sprintf(__('Our address is: %s', 'businessbot-ai-chat'), $address);
        }
    }

    if (businessbot_chat_assist_contains_any($text, ['hours', 'timing', 'open', 'close'])) {
        if ('' !== $hours) {
            return sprintf(__('Our business hours are: %s', 'businessbot-ai-chat'), $hours);
        }
    }

    if (businessbot_chat_assist_contains_any($text, ['service', 'services', 'provide', 'offer', 'product', 'products'])) {
        if ('' !== $services) {
            return sprintf(
                __('At %1$s, we provide: %2$s', 'businessbot-ai-chat'),
                $business_name,
                $services
            );
        }
    }

    if (businessbot_chat_assist_contains_any($text, ['book', 'booking', 'slot', 'appointment', 'reserve', 'order'])) {
        $lines = [];
        $lines[] = __('Sure - we can help you with booking.', 'businessbot-ai-chat');

        if ('' !== $phone) {
            $lines[] = sprintf(__('Call us at %s', 'businessbot-ai-chat'), $phone);
        }

        if ('' !== $email) {
            $lines[] = sprintf(__('or email us at %s', 'businessbot-ai-chat'), $email);
        }

        if ('' !== $faq_link) {
            $lines[] = sprintf(__('You can also use our help page: %s', 'businessbot-ai-chat'), $faq_link);
        }

        if ('' !== $hours) {
            $lines[] = sprintf(__('Booking support hours: %s', 'businessbot-ai-chat'), $hours);
        }

        return implode("\n", $lines);
    }

    if (businessbot_chat_assist_contains_any($text, ['you there', 'are you there', 'hello?', 'what happen', 'what happened', 'why not working', 'issue'])) {
        return businessbot_chat_assist_get_service_unavailable_message();
    }

    if (businessbot_chat_assist_contains_any($text, ['help', 'support', 'details'])) {
        return sprintf(
            __('I can help with business details, services, contact info, and store hours. For more help, visit: %s', 'businessbot-ai-chat'),
            $faq_link
        );
    }

    return '';
}

function businessbot_chat_assist_get_service_unavailable_message() {
    $options = get_option('businessbot_data', []);
    $phone = is_array($options) ? trim((string) ($options['contact_number'] ?? '')) : '';
    $email = is_array($options) ? trim((string) ($options['businessEmails'] ?? '')) : '';
    $faq_link = is_array($options) ? trim((string) ($options['faq_link'] ?? site_url())) : site_url();

    $message = __('I am having a temporary issue replying right now.', 'businessbot-ai-chat');
    if ('' !== $phone || '' !== $email) {
        $message .= ' ' . __('Please contact our support team directly:', 'businessbot-ai-chat');
        if ('' !== $phone) {
            $message .= ' ' . sprintf(__('Phone: %s.', 'businessbot-ai-chat'), $phone);
        }
        if ('' !== $email) {
            $message .= ' ' . sprintf(__('Email: %s.', 'businessbot-ai-chat'), $email);
        }
    }

    if ('' !== $faq_link) {
        $message .= ' ' . sprintf(__('Help page: %s', 'businessbot-ai-chat'), $faq_link);
    }

    return trim($message);
}

function businessbot_chat_assist_contains_any($haystack, $needles) {
    foreach ($needles as $needle) {
        if (false !== strpos($haystack, $needle)) {
            return true;
        }
    }
    return false;
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

