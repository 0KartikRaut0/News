(function () {
    'use strict';

    var cfg = window.neonewsMembership || {};

    function initPopup() {
        if (!cfg.popupEnabled || cfg.isPremium) {
            return;
        }

        var popup = document.getElementById('nn-membership-popup');
        if (!popup) {
            return;
        }

        var storageKey = 'nn_membership_popup_dismissed';
        var last = parseInt(localStorage.getItem(storageKey) || '0', 10);
        var interval = (cfg.popupInterval || 86400) * 1000;

        if (last && Date.now() - last < interval) {
            return;
        }

        function closePopup() {
            popup.hidden = true;
            popup.setAttribute('aria-hidden', 'true');
            localStorage.setItem(storageKey, String(Date.now()));
        }

        popup.querySelectorAll('[data-close-popup]').forEach(function (el) {
            el.addEventListener('click', closePopup);
        });

        window.setTimeout(function () {
            popup.hidden = false;
            popup.setAttribute('aria-hidden', 'false');
        }, cfg.popupDelay || 45000);
    }

    function initExpiryCountdown() {
        document.querySelectorAll('.nn-expiry-countdown').forEach(function (el) {
            var days = parseInt(el.getAttribute('data-days') || '0', 10);
            if (days > 0) {
                el.textContent = days + (days === 1 ? ' day remaining' : ' days remaining');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initPopup();
            initExpiryCountdown();
        });
    } else {
        initPopup();
        initExpiryCountdown();
    }
})();
