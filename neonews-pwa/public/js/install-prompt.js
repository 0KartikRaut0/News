(function() {
    'use strict';

    var cfg = window.neonewsPwaInstall || {};
    var STORAGE_KEY = 'neonews_pwa_install_dismissed';
    var deferredPrompt = null;
    var banner = null;
    var mode = '';

    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches ||
            window.matchMedia('(display-mode: fullscreen)').matches ||
            window.navigator.standalone === true;
    }

    function isDismissed() {
        try {
            var until = localStorage.getItem(STORAGE_KEY);
            if (until && Date.now() < parseInt(until, 10)) {
                return true;
            }
        } catch (e) {}
        return false;
    }

    function dismiss() {
        var days = parseInt(cfg.dismissDays, 10) || 7;
        try {
            localStorage.setItem(STORAGE_KEY, String(Date.now() + days * 86400000));
        } catch (e) {}
        hideBanner();
    }

    function isIOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    }

    function isAndroid() {
        return /Android/.test(navigator.userAgent);
    }

    function getBanner() {
        if (!banner) {
            banner = document.getElementById('nn-pwa-install-banner');
        }
        return banner;
    }

    function hideBanner() {
        var el = getBanner();
        if (!el) {
            return;
        }
        el.classList.add('nn-pwa-install-hidden');
        el.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('nn-pwa-install-visible', 'nn-pwa-install-bottom-active', 'nn-pwa-install-top-active');
        document.documentElement.style.removeProperty('--nn-pwa-install-height');
    }

    function syncBodyOffset() {
        var el = getBanner();
        if (!el || el.classList.contains('nn-pwa-install-hidden')) {
            return;
        }

        var height = el.offsetHeight || 0;
        document.documentElement.style.setProperty('--nn-pwa-install-height', height + 'px');

        if (cfg.position === 'top') {
            document.body.classList.add('nn-pwa-install-top-active');
            document.body.classList.remove('nn-pwa-install-bottom-active');
        } else {
            document.body.classList.add('nn-pwa-install-bottom-active');
            document.body.classList.remove('nn-pwa-install-top-active');
        }
    }

    function showBanner(nextMode) {
        if (isStandalone() || isDismissed()) {
            return;
        }

        var el = getBanner();
        if (!el) {
            return;
        }

        mode = nextMode || mode || 'native';
        var actionBtn = document.getElementById('nn-pwa-install-action');
        var iosBox = el.querySelector('.nn-pwa-install-ios-steps');
        var iosList = el.querySelector('.nn-pwa-install-steps');
        var desktopHint = el.querySelector('.nn-pwa-install-desktop-hint');

        if (iosBox) {
            iosBox.hidden = mode !== 'ios';
        }
        if (desktopHint) {
            desktopHint.hidden = mode !== 'desktop';
            if (mode === 'desktop' && cfg.desktopHint) {
                desktopHint.textContent = cfg.desktopHint;
            }
        }
        if (mode === 'ios' && iosList && cfg.iosSteps && cfg.iosSteps.length) {
            iosList.innerHTML = cfg.iosSteps.map(function(step) {
                return '<li>' + String(step).replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</li>';
            }).join('');
        }

        if (actionBtn) {
            if (mode === 'ios') {
                actionBtn.textContent = 'Got it';
            } else if (mode === 'desktop') {
                actionBtn.textContent = 'OK';
            } else {
                actionBtn.textContent = 'Install';
            }
        }

        el.classList.remove('nn-pwa-install-hidden');
        el.setAttribute('aria-hidden', 'false');
        document.body.classList.add('nn-pwa-install-visible');
        syncBodyOffset();
    }

    function onInstallClick() {
        if (mode === 'ios' || mode === 'desktop') {
            dismiss();
            return;
        }

        if (!deferredPrompt) {
            return;
        }

        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function(choice) {
            if (choice.outcome === 'accepted') {
                hideBanner();
            }
            deferredPrompt = null;
        }).catch(function() {
            deferredPrompt = null;
        });
    }

    function maybeShowFallbacks() {
        if (isStandalone() || isDismissed() || deferredPrompt) {
            return;
        }

        if (isIOS() && cfg.showIosHint) {
            showBanner('ios');
            return;
        }

        if (!isIOS() && !isAndroid() && cfg.showDesktopHint) {
            showBanner('desktop');
        }
    }

    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        showBanner('native');
    });

    window.addEventListener('appinstalled', function() {
        hideBanner();
        deferredPrompt = null;
    });

    document.addEventListener('DOMContentLoaded', function() {
        if (isStandalone() || isDismissed()) {
            hideBanner();
            return;
        }

        var dismissBtn = document.getElementById('nn-pwa-install-dismiss');
        var closeBtn = document.getElementById('nn-pwa-install-close');
        var actionBtn = document.getElementById('nn-pwa-install-action');

        if (dismissBtn) {
            dismissBtn.addEventListener('click', dismiss);
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', dismiss);
        }
        if (actionBtn) {
            actionBtn.addEventListener('click', onInstallClick);
        }

        window.setTimeout(maybeShowFallbacks, 2500);

        window.addEventListener('resize', syncBodyOffset);
    });
})();
