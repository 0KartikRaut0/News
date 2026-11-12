/**
 * NeoNews Role Manager — bulk toggles (backup if inline script is stripped).
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    ready(function () {
        var form = document.getElementById('nn-roles-form');
        if (!form || form.dataset.nnRolesBound === '1') {
            return;
        }
        form.dataset.nnRolesBound = '1';

        var table = form.querySelector('.nn-roles-table');
        if (!table) {
            return;
        }

        function setColumn(col, on) {
            table.querySelectorAll('tr.nn-roles-data-row').forEach(function (row) {
                var cell = row.cells[col];
                if (!cell) {
                    return;
                }
                var input = cell.querySelector('input.nn-role-check:not(:disabled)');
                if (input) {
                    input.checked = !!on;
                }
            });
        }

        table.querySelectorAll('.nn-bulk-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var col = parseInt(btn.getAttribute('data-col'), 10);
                if (isNaN(col)) {
                    return;
                }
                setColumn(col, btn.getAttribute('data-action') === 'all');
            });
        });
    });
})();
