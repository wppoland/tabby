/**
 * Tabby - repeater for the settings screen.
 *
 * Clones a <template> row on "Add", renumbers field name indexes, and removes
 * rows. Works without any framework or jQuery. Fully keyboard usable. Enqueued
 * deferred / in the footer. No dependencies.
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    function reindex(rowsContainer) {
        var rows = rowsContainer.querySelectorAll('[data-tabby-row]');
        Array.prototype.forEach.call(rows, function (row, index) {
            var fields = row.querySelectorAll('[name]');
            Array.prototype.forEach.call(fields, function (field) {
                var name = field.getAttribute('name');
                if (!name) {
                    return;
                }
                field.setAttribute(
                    'name',
                    name.replace(/\[(?:__index__|\d+)\]/, '[' + index + ']')
                );
            });
        });
    }

    function initRepeater(repeater) {
        var rowsContainer = repeater.querySelector('[data-tabby-rows]');
        var template = repeater.querySelector('[data-tabby-template]');
        var addButton = repeater.querySelector('[data-tabby-add]');

        if (!rowsContainer || !template || !addButton) {
            return;
        }

        addButton.addEventListener('click', function () {
            var clone = template.content
                ? template.content.firstElementChild.cloneNode(true)
                : null;

            // Fallback for browsers without <template>.content (very old).
            if (!clone) {
                var wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML;
                clone = wrapper.firstElementChild;
            }

            if (!clone) {
                return;
            }

            rowsContainer.appendChild(clone);
            reindex(rowsContainer);

            var firstInput = clone.querySelector('input[type="text"], textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });

        rowsContainer.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-tabby-remove]');
            if (!trigger) {
                return;
            }
            event.preventDefault();
            var row = trigger.closest('[data-tabby-row]');
            if (!row) {
                return;
            }
            row.parentNode.removeChild(row);
            reindex(rowsContainer);
        });

        reindex(rowsContainer);
    }

    ready(function () {
        var repeaters = document.querySelectorAll('[data-tabby-repeater]');
        Array.prototype.forEach.call(repeaters, initRepeater);
    });
})();
