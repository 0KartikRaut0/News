/**
 * NeoNews Feature Control — instant toggles
 */
(function () {
    'use strict';

    var cfg = window.nnFeatureControl || {};

    function post(action, data) {
        var body = new URLSearchParams();
        body.append('action', action);
        body.append('nonce', cfg.nonce);
        Object.keys(data).forEach(function (key) {
            body.append(key, data[key]);
        });
        return fetch(cfg.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
        }).then(function (res) {
            return res.json();
        });
    }

    function setCardState(card, on) {
        if (!card) {
            return;
        }
        card.classList.toggle('is-on', !!on);
        var status = card.querySelector('.nn-fc-card-status');
        var sw = card.querySelector('.nn-fc-switch');
        if (status && cfg.i18n) {
            status.textContent = on ? cfg.i18n.enabled : cfg.i18n.disabled;
        }
        if (sw) {
            sw.setAttribute('aria-checked', on ? 'true' : 'false');
            sw.dataset.enabled = on ? '1' : '0';
        }
    }

    function bindSwitches(root) {
        root.querySelectorAll('.nn-fc-switch').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.disabled) {
                    return;
                }
                var id = btn.dataset.id;
                var next = btn.dataset.enabled !== '1';
                var card = btn.closest('.nn-fc-card');
                btn.disabled = true;
                if (cfg.i18n && card) {
                    var status = card.querySelector('.nn-fc-card-status');
                    if (status) {
                        status.textContent = cfg.i18n.saving;
                    }
                }
                post('nn_fc_toggle', { id: id, enabled: next ? '1' : '0' })
                    .then(function (json) {
                        if (!json || !json.success) {
                            throw new Error((json && json.data && json.data.message) || 'error');
                        }
                        setCardState(card, json.data.enabled);
                    })
                    .catch(function () {
                        if (card && cfg.i18n) {
                            var st = card.querySelector('.nn-fc-card-status');
                            if (st) {
                                st.textContent = cfg.i18n.error;
                            }
                        }
                    })
                    .finally(function () {
                        btn.disabled = false;
                    });
            });
        });
    }

    function bindBulk(root) {
        root.querySelectorAll('.nn-fc-bulk').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var group = btn.dataset.group;
                var on = btn.dataset.bulk === 'on';
                btn.disabled = true;
                post('nn_fc_bulk_group', { group: group, enabled: on ? '1' : '0' })
                    .then(function (json) {
                        if (!json || !json.success || !json.data.items) {
                            throw new Error('bulk failed');
                        }
                        json.data.items.forEach(function (item) {
                            var card = root.querySelector('.nn-fc-card[data-feature-id="' + item.id + '"]');
                            setCardState(card, item.enabled);
                        });
                    })
                    .finally(function () {
                        btn.disabled = false;
                    });
            });
        });
    }

    function bindFilters(root) {
        var search = document.getElementById('nn-fc-search');
        var pills = root.querySelectorAll('.nn-fc-pill');
        var sections = root.querySelectorAll('.nn-fc-section');
        var empty = document.getElementById('nn-fc-empty');
        var activeGroup = 'all';

        function applyFilter() {
            var q = search ? search.value.trim().toLowerCase() : '';
            var anyVisible = false;

            sections.forEach(function (section) {
                var group = section.getAttribute('data-group-section');
                var sectionVisible = activeGroup === 'all' || activeGroup === group;
                var cards = section.querySelectorAll('.nn-fc-card');
                var groupHasVisible = false;

                cards.forEach(function (card) {
                    var matchSearch = !q || (card.dataset.search || '').indexOf(q) !== -1;
                    var show = sectionVisible && matchSearch;
                    card.classList.toggle('is-hidden', !show);
                    if (show) {
                        groupHasVisible = true;
                        anyVisible = true;
                    }
                });

                section.classList.toggle('is-hidden', !sectionVisible || !groupHasVisible);
            });

            if (empty) {
                empty.hidden = anyVisible;
            }
        }

        if (search) {
            search.addEventListener('input', applyFilter);
        }

        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                pills.forEach(function (p) {
                    p.classList.remove('is-active');
                });
                pill.classList.add('is-active');
                activeGroup = pill.dataset.group || 'all';
                applyFilter();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.getElementById('nn-feature-control');
        if (!root) {
            return;
        }
        bindSwitches(root);
        bindBulk(root);
        bindFilters(root);
    });
})();
