/**
 * NeoNews Role Manager — bulk toggles + unsaved-changes warning.
 */
(function () {
    'use strict';

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    onReady(function () {
        var form = document.getElementById('nn-roles-form');
        if (!form) {
            return;
        }

        var table = form.querySelector('.nn-roles-table');
        if (!table) {
            return;
        }

        function syncToggleVisual(input) {
            var wrap = input.closest('.nn-toggle');
            if (wrap) {
                wrap.classList.toggle('nn-toggle-on', input.checked);
            }
        }

        function setColumnChecked(colIndex, checked) {
            table.querySelectorAll('tr.nn-roles-data-row').forEach(function (row) {
                var cell = row.cells[colIndex];
                if (!cell) {
                    return;
                }
                var input = cell.querySelector('input[type="checkbox"]:not(:disabled)');
                if (!input) {
                    return;
                }
                input.checked = !!checked;
                syncToggleVisual(input);
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        table.querySelectorAll('.nn-bulk-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var col = parseInt(btn.getAttribute('data-col'), 10);
                if (isNaN(col)) {
                    return;
                }
                var action = btn.getAttribute('data-action');
                setColumnChecked(col, action === 'all');
            });
        });

        var changed = false;
        form.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(function (cb) {
            syncToggleVisual(cb);
            cb.addEventListener('change', function () {
                changed = true;
                syncToggleVisual(cb);
            });
        });

        window.addEventListener('beforeunload', function (e) {
            if (!changed) {
                return;
            }
            e.preventDefault();
            e.returnValue = '';
        });

        form.addEventListener('submit', function () {
            changed = false;
        });
    });
})();
