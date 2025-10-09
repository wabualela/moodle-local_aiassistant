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

define(['jquery'], function($) {

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
            this.isOpen = false;
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
         * Send a message
         */
        sendMessage() {
            const message = this.input.value.trim();

            if (message === '') {
                return;
            }

            // Add user message to UI
            this.addMessage(message, 'user');

            // Clear input
            this.input.value = '';

            // Show typing indicator
            this.showTypingIndicator();

            // TODO: Send to backend via web service
            // For now, simulate a response
            setTimeout(() => {
                this.hideTypingIndicator();
                this.addMessage('This is a simulated response. Web service integration coming soon!', 'ai');
            }, 1000);
        }

        /**
         * Add a message to the chat
         *
         * @param {string} text - The message text
         * @param {string} sender - Either 'user' or 'ai'
         */
        addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `local-aiassistant-message local-aiassistant-message-${sender}`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'local-aiassistant-message-content';
            contentDiv.textContent = text;

            const timeDiv = document.createElement('div');
            timeDiv.className = 'local-aiassistant-message-time';
            timeDiv.textContent = this.getCurrentTime();

            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(timeDiv);

            this.messagesContainer.appendChild(messageDiv);

            // Scroll to bottom
            this.scrollToBottom();
        }

        /**
         * Show typing indicator
         */
        showTypingIndicator() {
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
            return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
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
