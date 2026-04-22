jQuery(document).ready(function ($) {
    const chat_open_once = BusinessBotData.chat_open_once;
    let isMinimized = false;

    function scrollToMessage($el) {
        const $container = $('#ai-chat-messages');
        $container.stop().animate({
            scrollTop: $el.offset().top - $container.offset().top + $container.scrollTop() - 30
        }, 400);
    }

    function getTimestamp() {
        return new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }

    function setWidgetBodyState(minimized) {
        isMinimized = minimized;
        $('#ai-chat-widget').toggleClass('is-minimized', minimized);
        $('.ai-minimize-icon').attr('data-state', minimized ? 'minimized' : 'open');
    }

    function ensureInitialMessage() {
        if ($('#ai-chat-messages').children().length === 0) {
            appendMessage('assistant', BusinessBotData.start_message);
        }
    }

    function appendMessage(sender, message) {
        const content = typeof marked !== 'undefined' ? marked.parse(message) : $('<div>').text(message).html();
        const $message = $(`
            <div class="ai-message-row ${sender}" style="opacity: 0; transform: translateY(10px);">
                <div class="bubble">${content}</div>
                <div class="ai-message-time">${getTimestamp()}${sender === 'user' ? ' \u2713\u2713' : ''}</div>
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

    function showTypingIndicator() {
        const $typing = $(`
            <div class="ai-message-row assistant typing-indicator">
                <div class="bubble">
                    <span class="typing-label">Typing</span>
                    <span class="typing-dots" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </span>
                </div>
            </div>
        `);
        $('#ai-chat-messages').append($typing);
        scrollToMessage($typing);
    }

    function removeTypingIndicator() {
        $('.typing-indicator').remove();
    }

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
                : 'Sorry, something went wrong. Please try again later.';

            appendMessage('assistant', reply);
        }).fail(() => {
            removeTypingIndicator();
            appendMessage('assistant', 'Unable to connect. Please check your internet connection.');
        });
    }

    $('#ai-chat-minimize').on('click', function () {
        setWidgetBodyState(!isMinimized);
    });

    $('#ai-chat-close').on('click', function () {
        setWidgetBodyState(false);
        $('#ai-chat-widget').fadeOut(150, function () {
            $('#ai-chat-launcher').css('display', 'inline-flex').hide().fadeIn(120);
        });
    });

    $('#ai-chat-launcher').on('click', function () {
        $('#ai-chat-launcher').hide();
        $('#ai-chat-widget').fadeIn(160, function () {
            ensureInitialMessage();
        });
        setWidgetBodyState(false);
    });

    $('#ai-chat-send').on('click', sendUserMessage);

    $('#ai-chat-input').on('keypress', function (e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendUserMessage();
        }
    });

    $(window).on('load', function () {
        if (chat_open_once === 'yes') {
            if (!sessionStorage.getItem('chatOpenedOnce')) {
                setTimeout(() => {
                    setWidgetBodyState(false);
                    $('#ai-chat-widget').fadeIn(180, function () {
                        ensureInitialMessage();
                    });

                    sessionStorage.setItem('chatOpenedOnce', 'true');
                }, 800);
            } else {
                $('#ai-chat-launcher').css('display', 'inline-flex').hide().fadeIn(120);
            }
        } else {
            setTimeout(() => {
                setWidgetBodyState(false);
                $('#ai-chat-widget').fadeIn(180, function () {
                    ensureInitialMessage();
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
