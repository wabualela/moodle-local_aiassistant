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
 * Admin assistant ID field handler
 *
 * @module     local_aiassistant/admin_assistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    /**
     * Initialize the assistant ID field
     *
     * @param {string} fieldId The field ID
     */
    function init(fieldId) {
        const select = document.getElementById(fieldId);
        const container = document.getElementById('assistant-info-container');
        const loading = document.getElementById('assistant-loading');

        if (!select || !container || !loading) {
            return;
        }

        // Fetch assistants list on page load.
        fetchAssistantsList(select, container, loading);

        // When assistant is selected, fetch its info.
        select.addEventListener('change', function() {
            const assistantId = select.value;

            // Hide containers.
            container.style.display = 'none';

            if (!assistantId) {
                return;
            }

            fetchAssistantInfo(assistantId, container, loading);
        });
    }

    /**
     * Fetch list of assistants from OpenAI
     *
     * @param {HTMLSelectElement} select Select element
     * @param {HTMLElement} infoContainer Info container
     * @param {HTMLElement} loading Loading indicator
     */
    function fetchAssistantsList(select, infoContainer, loading) {
        // Get the saved value from data attribute
        const currentValue = select.getAttribute('data-current-value') || '';

        Ajax.call([{
            methodname: 'local_aiassistant_list_assistants',
            args: {},
        }])[0]
            .then(function(response) {
                window.console.log('Assistants list response:', response);
                loading.style.display = 'none';

                if (response.success && response.assistants.length > 0) {
                    // Clear existing options.
                    select.innerHTML = '';

                    // Add empty option.
                    const emptyOption = document.createElement('option');
                    emptyOption.value = '';
                    emptyOption.textContent = '-- Select an assistant --';
                    select.appendChild(emptyOption);

                    // Add assistant options.
                    response.assistants.forEach(function(assistant) {
                        const option = document.createElement('option');
                        option.value = assistant.id;
                        option.textContent = assistant.name + ' (' + assistant.model + ')';
                        if (assistant.id === currentValue) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });

                    // If there was a current value, fetch its info.
                    if (currentValue) {
                        fetchAssistantInfo(currentValue, infoContainer, loading);
                    }
                } else {
                    window.console.warn('Failed to fetch assistants:', response.error);
                    select.innerHTML = '';
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = response.error || 'No assistants found';
                    select.appendChild(option);

                    // Show error in info container.
                    if (response.error) {
                        infoContainer.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + escapeHtml(response.error);
                        infoContainer.className = 'alert alert-warning';
                        infoContainer.style.display = 'block';
                    }
                }
            })
            .catch(function(error) {
                window.console.error('Error fetching assistants:', error);
                loading.style.display = 'none';
                select.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Error loading assistants';
                select.appendChild(option);

                // Show error in info container.
                infoContainer.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Error: ' +
                    escapeHtml(error.message || 'Failed to load assistants');
                infoContainer.className = 'alert alert-danger';
                infoContainer.style.display = 'block';

                Notification.exception(error);
            });
    }

    /**
     * Fetch assistant info from OpenAI
     *
     * @param {string} assistantId Assistant ID
     * @param {HTMLElement} container Info container
     * @param {HTMLElement} loading Loading indicator
     */
    function fetchAssistantInfo(assistantId, container, loading) {
        // Show loading.
        loading.style.display = 'block';
        container.style.display = 'none';

        Ajax.call([{
            methodname: 'local_aiassistant_get_assistant_info',
            args: {
                assistantid: assistantId,
            },
        }])[0]
            .then(function(response) {
                loading.style.display = 'none';

                if (response.success) {
                    // Build info HTML.
                    let html = '<h5><i class="fa fa-robot"></i> ' + escapeHtml(response.name || 'Unnamed Assistant') + '</h5>';

                    if (response.description) {
                        html += '<p><strong>Description:</strong> ' + escapeHtml(response.description) + '</p>';
                    }

                    html += '<p><strong>Model:</strong> ' + escapeHtml(response.model || 'N/A') + '</p>';

                    if (response.tools > 0) {
                        html += '<p><strong>Tools:</strong> ' + response.tools + ' configured</p>';
                    }

                    if (response.instructions) {
                        const shortInstructions = response.instructions.length > 200
                            ? response.instructions.substring(0, 200) + '...'
                            : response.instructions;
                        html += '<p><strong>Instructions:</strong> ' + escapeHtml(shortInstructions) + '</p>';
                    }

                    container.innerHTML = html;
                    container.className = 'alert alert-success';
                    container.style.display = 'block';
                } else {
                    container.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' +
                        escapeHtml(response.error || 'Failed to fetch assistant info');
                    container.className = 'alert alert-danger';
                    container.style.display = 'block';
                }
            })
            .catch(function(error) {
                loading.style.display = 'none';
                container.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' +
                    escapeHtml(error.message || 'An error occurred');
                container.className = 'alert alert-danger';
                container.style.display = 'block';
                Notification.exception(error);
            });
    }

    /**
     * Escape HTML
     *
     * @param {string} text Text to escape
     * @return {string} Escaped text
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    return {
        init: init
    };
});
