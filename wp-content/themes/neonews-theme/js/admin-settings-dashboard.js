/**
 * NewsPulse settings dashboard — search & sticky save.
 */
(function () {
    'use strict';

    function initSettingsSearch() {
        var input = document.getElementById('nn-settings-search');
        var panel = document.querySelector('.nn-settings-panel');
        if (!input || !panel) {
            return;
        }

        var noResults = document.createElement('p');
        noResults.className = 'nn-settings-no-results';
        noResults.textContent = (window.nnSettingsDash && nnSettingsDash.i18n && nnSettingsDash.i18n.noResults)
            ? nnSettingsDash.i18n.noResults
            : 'No matching settings.';
        panel.insertBefore(noResults, panel.firstChild);

        var rows = panel.querySelectorAll('table.form-table tr');
        var headings = panel.querySelectorAll('h2, h3');

        function filter() {
            var q = input.value.trim().toLowerCase();
            if (!q) {
                rows.forEach(function (row) {
                    row.classList.remove('nn-settings-search-hidden');
                });
                headings.forEach(function (h) {
                    h.style.display = '';
                });
                noResults.classList.remove('is-visible');
                return;
            }

            var visible = 0;
            rows.forEach(function (row) {
                var text = row.textContent.toLowerCase();
                var show = text.indexOf(q) !== -1;
                row.classList.toggle('nn-settings-search-hidden', !show);
                if (show) {
                    visible++;
                }
            });

            headings.forEach(function (h) {
                var sectionText = h.textContent.toLowerCase();
                var next = h.nextElementSibling;
                var sectionMatch = sectionText.indexOf(q) !== -1;
                var childVisible = false;
                if (next && next.matches && next.matches('table.form-table')) {
                    var trs = next.querySelectorAll('tr');
                    trs.forEach(function (tr) {
                        if (!tr.classList.contains('nn-settings-search-hidden')) {
                            childVisible = true;
                        }
                    });
                }
                h.style.display = sectionMatch || childVisible ? '' : 'none';
            });

            noResults.classList.toggle('is-visible', visible === 0);
        }

        input.addEventListener('input', filter);
        input.addEventListener('search', filter);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSettingsSearch();
    });
})();
