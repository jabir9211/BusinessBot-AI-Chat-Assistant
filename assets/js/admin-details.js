document.addEventListener('DOMContentLoaded', function () {
    var toneField = document.getElementById('businessbot_tone');
    var toneHelp = document.getElementById('businessbot-tone-help');
    var greetingField = document.getElementById('businessbot_initial_greeting');
    var greetingPreview = document.getElementById('businessbot-greeting-preview');
    var toneDescriptions = window.businessbotToneDescriptions || {};

    function syncToneHelp() {
        if (!toneField || !toneHelp) {
            return;
        }

        var tone = toneField.value;
        toneHelp.textContent = toneDescriptions[tone] || toneDescriptions.Friendly || '';
    }

    function syncGreetingPreview() {
        if (!greetingField || !greetingPreview) {
            return;
        }

        var value = greetingField.value.trim();
        greetingPreview.textContent = value || 'Hi there! How can I help you today?';
    }

    if (toneField) {
        toneField.addEventListener('change', syncToneHelp);
        syncToneHelp();
    }

    if (greetingField) {
        greetingField.addEventListener('input', syncGreetingPreview);
        syncGreetingPreview();
    }
});
