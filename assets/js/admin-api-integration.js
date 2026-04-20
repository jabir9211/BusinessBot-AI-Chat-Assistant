document.addEventListener('DOMContentLoaded', function () {
    var uiData = window.businessbotApiUiData || {};
    var integrationData = window.BusinessBotIntegrationData || {};
    var apiInput = document.getElementById('businessbot_api_key_field');
    var showBtn = document.getElementById('businessbot-toggle-key');
    var copyBtn = document.getElementById('businessbot-copy-key');
    var testBtn = document.getElementById('businessbot-test-connection-btn');
    var spinner = document.getElementById('businessbot-test-spinner');
    var toast = document.getElementById('businessbot-settings-toast');
    var feedback = document.getElementById('businessbot-api-inline-feedback');
    var statusPill = document.getElementById('businessbot-api-status');
    var statusText = statusPill ? statusPill.querySelector('.state-text') : null;
    var statusMessage = document.getElementById('businessbot-api-status-message');

    if (!apiInput) {
        return;
    }

    function showToast(message) {
        if (!toast || !message) {
            return;
        }
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 1800);
    }

    function setStatus(type, text, message) {
        if (!statusPill || !statusText || !statusMessage) {
            return;
        }
        statusPill.classList.remove('is-active', 'is-error', 'is-disabled');
        if ('connected' === type) {
            statusPill.classList.add('is-active');
        } else if ('error' === type) {
            statusPill.classList.add('is-error');
        } else {
            statusPill.classList.add('is-disabled');
        }
        statusText.textContent = text;
        statusMessage.textContent = message;
    }

    function clearInlineFeedback() {
        if (!feedback) {
            return;
        }
        feedback.textContent = '';
        feedback.classList.remove('businessbot-inline-error');
        apiInput.classList.remove('has-error');
    }

    function showInlineError(message) {
        if (feedback) {
            feedback.textContent = message;
            feedback.classList.add('businessbot-inline-error');
        }
        apiInput.classList.add('has-error');
    }

    apiInput.addEventListener('blur', function () {
        apiInput.value = apiInput.value.trim();
    });

    if (showBtn) {
        showBtn.addEventListener('click', function () {
            var isPassword = apiInput.getAttribute('type') === 'password';
            apiInput.setAttribute('type', isPassword ? 'text' : 'password');
            showBtn.textContent = isPassword ? (uiData.hide || 'Hide') : (uiData.show || 'Show');
        });
    }

    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            if (!apiInput.value.trim()) {
                return;
            }
            if (!navigator.clipboard || !navigator.clipboard.writeText) {
                showInlineError('Copy is not supported in this browser context.');
                return;
            }
            navigator.clipboard.writeText(apiInput.value.trim())
                .then(function () {
                    clearInlineFeedback();
                    showToast(integrationData.copy_success || 'API key copied');
                })
                .catch(function () {
                    showInlineError('Unable to copy API key. Please copy manually.');
                });
        });
    }

    if (testBtn) {
        testBtn.addEventListener('click', function () {
            var apiKey = apiInput.value.trim();
            clearInlineFeedback();
            apiInput.value = apiKey;

            if (!apiKey) {
                showInlineError(uiData.disconnected_message || 'API key missing or invalid. Chatbot will not respond.');
                setStatus('error', uiData.not_connected || 'Not Connected', uiData.disconnected_message || 'API key missing or invalid. Chatbot will not respond.');
                return;
            }

            testBtn.disabled = true;
            testBtn.textContent = integrationData.testing_label || 'Testing...';
            if (spinner) {
                spinner.classList.add('is-visible');
            }

            var formData = new URLSearchParams();
            formData.append('action', 'businessbot_test_connection');
            formData.append('_ajax_nonce', integrationData.nonce || '');
            formData.append('api_key', apiKey);

            fetch(integrationData.ajax_url || '', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: formData.toString(),
            })
                .then(function (res) {
                    return res.json();
                })
                .then(function (res) {
                    if (res && res.success) {
                        setStatus('connected', uiData.connected || 'Connected', res.data || uiData.connected_message);
                        showToast(res.data || uiData.connected_message);
                    } else {
                        var msg = (res && res.data) ? res.data : (uiData.disconnected_message || 'Invalid API key or network issue.');
                        showInlineError(msg);
                        setStatus('error', uiData.not_connected || 'Not Connected', msg);
                    }
                })
                .catch(function () {
                    var msg = uiData.disconnected_message || 'Invalid API key or network issue.';
                    showInlineError(msg);
                    setStatus('error', uiData.not_connected || 'Not Connected', msg);
                })
                .finally(function () {
                    testBtn.disabled = false;
                    testBtn.textContent = integrationData.test_connection_label || 'Test Connection';
                    if (spinner) {
                        spinner.classList.remove('is-visible');
                    }
                });
        });
    }

    if (window.businessbotApiSaved) {
        showToast(integrationData.save_success || 'API key saved');
    }
});
