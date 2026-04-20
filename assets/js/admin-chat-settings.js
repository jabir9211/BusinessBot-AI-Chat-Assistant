document.addEventListener('DOMContentLoaded', function () {
    var data = window.businessbotSettingsData || {};
    var form = document.getElementById('businessbot-settings-form');
    var enabledToggle = document.getElementById('chatbot_enabled');
    var autoOpenToggle = document.getElementById('chatbot_auto_open');
    var behaviorBlock = document.getElementById('businessbot-behavior-block');
    var statusIndicator = document.getElementById('businessbot-status-indicator');
    var statusHelp = document.getElementById('businessbot-status-help');
    var autoOpenHint = document.getElementById('businessbot-auto-open-hint');
    var firstVisitPreview = document.getElementById('businessbot-first-visit-preview');
    var toast = document.getElementById('businessbot-settings-toast');
    var saveTop = document.getElementById('businessbot-settings-save-top');
    var saveBottom = document.getElementById('businessbot-settings-save-bottom');

    if (!form || !enabledToggle || !autoOpenToggle) {
        return;
    }

    function setSaveDisabled(disabled) {
        if (saveTop) {
            saveTop.disabled = disabled;
        }
        if (saveBottom) {
            saveBottom.disabled = disabled;
        }
    }

    function showToast(message) {
        if (!toast || !message) {
            return;
        }
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 1700);
    }

    function syncStatus() {
        var isActive = enabledToggle.checked;

        if (statusIndicator) {
            statusIndicator.classList.toggle('is-active', isActive);
            statusIndicator.classList.toggle('is-disabled', !isActive);
            var stateText = statusIndicator.querySelector('.state-text');
            if (stateText) {
                stateText.textContent = isActive ? data.activeLabel : data.disabledLabel;
            }
        }

        if (statusHelp) {
            statusHelp.textContent = isActive ? data.activeHelp : data.disabledHelp;
        }

        autoOpenToggle.disabled = !isActive;
        if (behaviorBlock) {
            behaviorBlock.classList.toggle('is-disabled', !isActive);
        }
    }

    function syncBehaviorText() {
        if (autoOpenHint) {
            autoOpenHint.textContent = autoOpenToggle.checked ? data.autoOpenOnHint : data.autoOpenOffHint;
        }
        if (firstVisitPreview) {
            firstVisitPreview.textContent = autoOpenToggle.checked ? data.firstVisitOpen : data.firstVisitMin;
        }
    }

    function markDirty() {
        setSaveDisabled(false);
    }

    enabledToggle.addEventListener('change', function () {
        syncStatus();
        markDirty();
        showToast(enabledToggle.checked ? data.toastEnabled : data.toastDisabled);
    });

    autoOpenToggle.addEventListener('change', function () {
        syncBehaviorText();
        markDirty();
    });

    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);

    form.addEventListener('submit', function () {
        form.classList.add('is-saving');
        var textEls = form.querySelectorAll('.businessbot-btn-text');
        textEls.forEach(function (el) {
            el.textContent = data.savingText || 'Saving...';
        });
    });

    setSaveDisabled(true);
    syncStatus();
    syncBehaviorText();
});
