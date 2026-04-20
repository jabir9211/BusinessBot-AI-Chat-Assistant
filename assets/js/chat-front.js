jQuery(document).ready(function ($) {
    const aiAssistant = BusinessBotData.plugin_url + 'assets/Images/ai-assisstant-logo.avif';
    const userIcon = BusinessBotData.plugin_url + 'assets/Images/user-icon.avif';
    const chat_open_once = BusinessBotData.chat_open_once;
    
    // Update launcher icon based on chat visibility
    function updateLauncherIcon() {
        const icon = $('#ai-chat-launcher i');
        if ($('#ai-chat-widget').is(':visible')) {
            icon.removeClass('fa-comments').addClass('fa-times');
        } else {
            icon.removeClass('fa-times').addClass('fa-comments');
        }
    }
    window.updateLauncherIcon = updateLauncherIcon;

    // Scroll the chat container to newly added message
    function scrollToMessage($el) {
        const $container = $('#ai-chat-messages');
        $container.stop().animate({
            scrollTop: $el.offset().top - $container.offset().top + $container.scrollTop() - 30
        }, 400);
    }

    // Append a chat message
    function appendMessage(sender, message) {
        const content = typeof marked !== 'undefined' ? marked.parse(message) : $('<div>').text(message).html();
        const imgSrc = sender === 'user' ? userIcon : aiAssistant;
        const altText = sender === 'user' ? 'You' : 'AI Assistant';

        const $message = $(`
            <div class="ai-message-row ${sender}" style="opacity: 0; transform: translateY(10px);">
                <img src="${imgSrc}" alt="${altText}" loading="lazy">
                <div class="bubble">${content}</div>
            </div>
        `);

        $('#ai-chat-messages').append($message);

        // Animate message appearance
        setTimeout(() => {
            $message.css({
                opacity: 1,
                transform: 'translateY(0)',
                transition: 'all 0.3s ease'
            });
        }, 50);

        scrollToMessage($message);
    }

    window.appendMessage = appendMessage; // For use outside jQuery init

    // Typing indicator animation
    function showTypingIndicator() {
        const $typing = $(`
            <div class="assistant typing-indicator">
                <img src="${aiAssistant}" alt="Bot">
                <div class="bubble typing-dots">
                    <span>.</span><span>.</span><span>.</span>
                </div>
            </div>
        `);
        $('#ai-chat-messages').append($typing);
        scrollToMessage($typing);
    }

    function removeTypingIndicator() {
        $('.typing-indicator').remove();
    }

    // Handle sending a user message
    function sendUserMessage() {
        const message = $('#ai-chat-input').val().trim();
        if (!message) return;

        appendMessage('user', message);
        $('#ai-chat-input').val('');
        showTypingIndicator();

        $.post(BusinessBotData.ajax_url, {
            action: 'businessbot_send',
            message: message,
             _ajax_nonce: BusinessBotData.nonce
        }, function (response) {
            removeTypingIndicator();

            const reply = response.success
                ? response.data
                : '⚠️ Sorry, something went wrong. Please try again later.';

            appendMessage('assistant', reply);
        }).fail(() => {
            removeTypingIndicator();
            appendMessage('assistant', '⚠️ Unable to connect. Please check your internet connection.');
        });
    }

    // Toggle chat widget
    $('#ai-chat-launcher').on('click', function () {
        $('#ai-chat-widget').slideToggle(() => {
            updateLauncherIcon();

            if ($('#ai-chat-messages').children().length === 0) {
                appendMessage('assistant', BusinessBotData.start_message);
            }
        });
    });

    // Close button
    $('#ai-chat-close').on('click', function () {
        $('#ai-chat-widget').slideUp(updateLauncherIcon);
    });

    // Send button click
    $('#ai-chat-send').on('click', sendUserMessage);

    // Press Enter to send
    $('#ai-chat-input').on('keypress', function (e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendUserMessage();
        }
    });

    // Auto-open after load
    $(window).on('load', function () {
        // Check parent condition
        if (chat_open_once === 'yes') {
            // Only open if not opened once in this session
            if (!sessionStorage.getItem('chatOpenedOnce')) {
                setTimeout(() => {
                    $('#ai-chat-widget').slideDown(() => {
                        updateLauncherIcon();

                        if ($('#ai-chat-messages').children().length === 0) {
                            appendMessage('assistant', BusinessBotData.start_message);
                        }
                    });

                    // Mark as opened once
                    sessionStorage.setItem('chatOpenedOnce', 'true');
                }, 800);
            }
            // else → do nothing (show only launcher)
        } else {
            // Original behavior: open every page load
            setTimeout(() => {
                $('#ai-chat-widget').slideDown(() => {
                    updateLauncherIcon();

                    if ($('#ai-chat-messages').children().length === 0) {
                        appendMessage('assistant', BusinessBotData.start_message);
                    }
                });
            }, 800);
        }
    });

});

// Ensure scroll stays at bottom when window resizes
jQuery(window).on('resize', function () {
    const $container = jQuery('#ai-chat-messages');
    $container.scrollTop($container[0].scrollHeight);
});
