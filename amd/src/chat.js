// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AI Assistant Chat Interface
 *
 * @module     local_aiassistant/chat
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    const FALLBACK_ERROR_MESSAGE = 'An unexpected error occurred. Please try again later.';
    const STORAGE_KEY = 'local_aiassistant_chat_history';

    /**
     * Chat class to handle AI assistant interaction
     */
    class Chat {
        /**
         * Constructor
         */
        constructor() {
            this.fab = null;
            this.chatBox = null;
            this.messagesContainer = null;
            this.input = null;
            this.sendButton = null;
            this.closeButton = null;
            this.optionsButton = null;
            this.attachmentButton = null;
            this.isOpen = false;
            this.history = [];
            this.isSending = false;
            this.errorMessage = '';
        }

        /**
         * Initialize the chat interface
         */
        init() {
            // Get DOM elements
            this.fab = document.getElementById('local-aiassistant-fab');
            this.chatBox = document.getElementById('local-aiassistant-chat');

            if (!this.fab || !this.chatBox) {
                window.console.warn('AI Assistant: Required elements not found');
                return;
            }

            this.messagesContainer = this.chatBox.querySelector('#local-aiassistant-messages');
            this.input = this.chatBox.querySelector('#local-aiassistant-input');
            this.sendButton = this.chatBox.querySelector('#local-aiassistant-send');
            this.closeButton = this.chatBox.querySelector('.local-aiassistant-chat-close');
            this.optionsButton = this.chatBox.querySelector('.local-aiassistant-chat-options');
            this.clearButton = this.chatBox.querySelector('.local-aiassistant-chat-clear');
            this.attachmentButton = this.chatBox.querySelector('.local-aiassistant-attachment');

            if (this.chatBox && this.chatBox.dataset && this.chatBox.dataset.errorGeneric) {
                this.errorMessage = this.chatBox.dataset.errorGeneric;
            } else {
                this.errorMessage = FALLBACK_ERROR_MESSAGE;
            }

            // Load chat history from localStorage
            this.loadHistory();

            // Bind event listeners
            this.bindEvents();

            window.console.log('AI Assistant Chat initialized');
        }

        /**
         * Bind all event listeners
         */
        bindEvents() {
            // FAB click to open chat
            this.fab.addEventListener('click', () => this.toggleChat());

            // Also handle keyboard navigation for FAB
            this.fab.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleChat();
                }
            });

            // Close button
            if (this.closeButton) {
                this.closeButton.addEventListener('click', () => this.closeChat());
            }

            // Options button
            if (this.optionsButton) {
                this.optionsButton.addEventListener('click', () => this.openSettings());
            }

            // Clear history button
            if (this.clearButton) {
                this.clearButton.addEventListener('click', () => {
                    if (confirm('Are you sure you want to clear the chat history? This cannot be undone.')) {
                        this.clearHistory();
                    }
                });
            }

            // Send button
            if (this.sendButton) {
                this.sendButton.addEventListener('click', () => this.sendMessage());
            }

            // Enter key in input
            if (this.input) {
                this.input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });
            }

            // Close chat when clicking outside
            document.addEventListener('click', (e) => {
                if (this.isOpen &&
                    !this.chatBox.contains(e.target) &&
                    !this.fab.contains(e.target)) {
                    this.closeChat();
                }
            });

            // Prevent closing when clicking inside chat
            this.chatBox.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        /**
         * Enable or disable the chat controls while a request is in progress.
         *
         * @param {boolean} disabled
         */
        setInputDisabled(disabled) {
            if (this.input) {
                this.input.disabled = disabled;
            }
            if (this.sendButton) {
                this.sendButton.disabled = disabled;
            }
            if (this.attachmentButton) {
                this.attachmentButton.disabled = disabled;
            }
        }

        /**
         * Toggle chat visibility
         */
        toggleChat() {
            if (this.isOpen) {
                this.closeChat();
            } else {
                this.openChat();
            }
        }

        /**
         * Open the chat interface
         */
        openChat() {
            this.chatBox.classList.add('is-visible');
            this.fab.classList.add('is-hidden');
            this.isOpen = true;
            this.fab.setAttribute('aria-expanded', 'true');

            // Focus on input field
            if (this.input) {
                setTimeout(() => this.input.focus(), 100);
            }
        }

        /**
         * Close the chat interface
         */
        closeChat() {
            this.chatBox.classList.remove('is-visible');
            this.fab.classList.remove('is-hidden');
            this.isOpen = false;
            this.fab.setAttribute('aria-expanded', 'false');
        }

        /**
         * Open settings page
         */
        openSettings() {
            const settingsUrl = this.chatBox.getAttribute('data-settingsurl');
            if (settingsUrl) {
                window.location.href = settingsUrl;
            }
        }

        /**
         * Send a message
         */
        sendMessage() {
            if (this.isSending) {
                return;
            }

            const message = this.input.value.trim();

            if (message === '') {
                return;
            }

            const historyPayload = this.history.map((entry) => ({
                message: entry.message
            }));

            // Add user message to UI
            this.addMessage(message, 'user');

            // Clear input
            this.input.value = '';

            // Persist user message for future turns.
            this.history.push({sender: 'user', message: message});

            // Save history to localStorage
            this.saveHistory();

            // Show typing indicator
            this.showTypingIndicator();

            this.setInputDisabled(true);
            this.isSending = true;

            Ajax.call([{
                methodname: 'local_aiassistant_get_completion',
                args: {
                    message: message,
                    history: historyPayload,
                },
            }])[0]
                .then((response) => {
                    window.console.log('AI Response received:', response);
                    if (response.success) {
                        const content = response.formattedmessage || response.message;
                        window.console.log('Adding AI message, renderAsHtml:', Boolean(response.formattedmessage));
                        this.addMessage(content, 'ai', {
                            renderAsHtml: Boolean(response.formattedmessage),
                        });
                        this.history.push({sender: 'ai', message: response.message});
                        // Save history to localStorage
                        this.saveHistory();
                    } else {
                        window.console.log('AI Response failed:', response.message);
                        const errortext = response.message || this.errorMessage;
                        this.addMessage(errortext, 'ai', {
                            isError: true,
                        });
                    }

                    // Cleanup after successful response
                    window.console.log('Cleaning up after response');
                    this.hideTypingIndicator();
                    this.setInputDisabled(false);
                    this.isSending = false;
                    if (this.input) {
                        this.input.focus();
                    }
                })
                .catch((error) => {
                    window.console.error('Ajax call failed:', error);
                    Notification.exception(error);
                    this.addMessage(this.errorMessage, 'ai', {
                        isError: true,
                    });

                    // Cleanup after error
                    window.console.log('Cleaning up after error');
                    this.hideTypingIndicator();
                    this.setInputDisabled(false);
                    this.isSending = false;
                    if (this.input) {
                        this.input.focus();
                    }
                });
        }

        /**
         * Add a message to the chat
         *
         * @param {string} text - The message text
         * @param {string} sender - Either 'user' or 'ai'
         * @param {Object} options - Additional options for rendering the message
         */
        addMessage(text, sender, options = {}) {
            const renderAsHtml = options.renderAsHtml || false;
            const isError = options.isError || false;

            window.console.log('addMessage called:', {sender, renderAsHtml, isError, textLength: text.length});

            const messageDiv = document.createElement('div');
            messageDiv.className = `local-aiassistant-message local-aiassistant-message-${sender}`;
            if (isError) {
                messageDiv.classList.add('local-aiassistant-message-error');
            }

            const contentDiv = document.createElement('div');
            contentDiv.className = 'local-aiassistant-message-content';
            if (renderAsHtml) {
                // Sanitize and render HTML safely
                contentDiv.innerHTML = text;
                // Remove any script tags that might have been added
                const scripts = contentDiv.querySelectorAll('script');
                scripts.forEach(script => script.remove());
            } else {
                contentDiv.textContent = text;
            }

            const timeDiv = document.createElement('div');
            timeDiv.className = 'local-aiassistant-message-time';
            timeDiv.textContent = this.getCurrentTime();

            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(timeDiv);

            this.messagesContainer.appendChild(messageDiv);

            window.console.log('Message added to DOM');

            // Scroll to bottom
            this.scrollToBottom();
        }

        /**
         * Show typing indicator
         */
        showTypingIndicator() {
            this.hideTypingIndicator();
            const indicator = document.createElement('div');
            indicator.className = 'local-aiassistant-typing-indicator';
            indicator.id = 'local-aiassistant-typing';
            indicator.innerHTML = '<span></span><span></span><span></span>';

            this.messagesContainer.appendChild(indicator);
            this.scrollToBottom();
        }

        /**
         * Hide typing indicator
         */
        hideTypingIndicator() {
            const indicator = document.getElementById('local-aiassistant-typing');
            if (indicator) {
                indicator.remove();
            }
        }

        /**
         * Scroll messages to bottom
         */
        scrollToBottom() {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }

        /**
         * Get current time formatted
         *
         * @returns {string} Formatted time
         */
        getCurrentTime() {
            const now = new Date();
            return now.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
        }

        /**
         * Save chat history to localStorage
         */
        saveHistory() {
            try {
                const historyData = {
                    history: this.history,
                    timestamp: Date.now()
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(historyData));
                window.console.log('Chat history saved to localStorage');
            } catch (e) {
                window.console.error('Failed to save chat history:', e);
            }
        }

        /**
         * Load chat history from localStorage
         */
        loadHistory() {
            try {
                const stored = localStorage.getItem(STORAGE_KEY);
                if (!stored) {
                    window.console.log('No chat history found in localStorage');
                    return;
                }

                const historyData = JSON.parse(stored);

                // Check if history is older than 24 hours
                const now = Date.now();
                const twentyFourHours = 24 * 60 * 60 * 1000;
                if (historyData.timestamp && (now - historyData.timestamp) > twentyFourHours) {
                    window.console.log('Chat history expired, clearing');
                    this.clearHistory();
                    return;
                }

                if (historyData.history && Array.isArray(historyData.history)) {
                    this.history = historyData.history;
                    window.console.log('Chat history loaded:', this.history.length, 'messages');

                    // Restore messages to UI
                    this.restoreMessagesToUI();
                }
            } catch (e) {
                window.console.error('Failed to load chat history:', e);
                this.clearHistory();
            }
        }

        /**
         * Restore saved messages to the UI
         */
        restoreMessagesToUI() {
            // Remove the welcome message if we have history
            if (this.history.length > 0) {
                const welcomeMessage = this.messagesContainer.querySelector('.local-aiassistant-message-ai');
                if (welcomeMessage) {
                    welcomeMessage.remove();
                }
            }

            // Add each message from history
            this.history.forEach((entry) => {
                this.addMessage(entry.message, entry.sender, {
                    renderAsHtml: entry.sender === 'ai',
                    skipHistoryUpdate: true
                });
            });

            window.console.log('Restored', this.history.length, 'messages to UI');
        }

        /**
         * Clear chat history
         */
        clearHistory() {
            try {
                localStorage.removeItem(STORAGE_KEY);
                this.history = [];
                window.console.log('Chat history cleared');

                // Clear all UI messages
                const messages = this.messagesContainer.querySelectorAll('.local-aiassistant-message');
                messages.forEach((msg) => {
                    msg.remove();
                });

                // Reload the page to restore the welcome message from the template
                window.location.reload();
            } catch (e) {
                window.console.error('Failed to clear chat history:', e);
            }
        }
    }

    return {
        /**
         * Initialize the chat module
         */
        init: function() {
            // Wait for DOM to be ready
            $(document).ready(function() {
                const chat = new Chat();
                chat.init();
            });
        }
    };
});
