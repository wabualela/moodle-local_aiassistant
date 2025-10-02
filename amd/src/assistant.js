define(['jquery'], function($) {
    return {
        init: function() {
            const btn = $('<div id="ai-assistant-button">💬</div>');
            $('body').append(btn);

            $('#ai-assistant-button').on('click', function() {
                alert('AI Assistant coming soon!'); // Replace with real modal/chat UI
            });
        }
    };
});
