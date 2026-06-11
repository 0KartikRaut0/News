/**
 * NeoNews CAPTCHA helpers for Turnstile, reCAPTCHA, and hCaptcha.
 */
(function() {
    'use strict';

    var widgets = {};

    function setToken(context, token) {
        document.querySelectorAll('.nn-captcha-token[data-context="' + context + '"]').forEach(function(input) {
            input.value = token || '';
        });
    }

    window.nnCaptchaSetToken = function(context, token) {
        setToken(context, token);
    };

    window.nnGetCaptchaToken = function(context, cfg) {
        return new Promise(function(resolve, reject) {
            if (!cfg || !cfg.enabled) {
                resolve('');
                return;
            }

            var needLogin = context === 'login' && cfg.onLogin;
            var needRegister = context === 'register' && cfg.onRegister;
            if (!needLogin && !needRegister) {
                resolve('');
                return;
            }

            if (cfg.provider === 'recaptcha_v3') {
                if (typeof grecaptcha === 'undefined') {
                    reject(new Error('CAPTCHA not loaded'));
                    return;
                }
                grecaptcha.ready(function() {
                    grecaptcha.execute(cfg.siteKey, { action: context }).then(function(token) {
                        setToken(context, token);
                        resolve(token);
                    }).catch(reject);
                });
                return;
            }

            var tokenInput = document.querySelector('.nn-captcha-token[data-context="' + context + '"]');
            var token = tokenInput ? tokenInput.value : '';
            if (!token) {
                reject(new Error('captcha'));
                return;
            }
            resolve(token);
        });
    };

    window.nnInitCaptcha = function(cfg) {
        if (!cfg || !cfg.enabled || cfg.isInvisible) {
            return;
        }

        var contexts = [];
        if (cfg.onLogin) {
            contexts.push('login');
        }
        if (cfg.onRegister) {
            contexts.push('register');
        }

        contexts.forEach(function(context) {
            var el = document.getElementById('nn-captcha-' + context);
            if (!el || el.dataset.mounted === '1') {
                return;
            }
            el.dataset.mounted = '1';

            if (cfg.provider === 'turnstile' && window.turnstile) {
                widgets[context] = window.turnstile.render(el, {
                    sitekey: cfg.siteKey,
                    callback: function(token) { setToken(context, token); },
                    'expired-callback': function() { setToken(context, ''); }
                });
            } else if (cfg.provider === 'recaptcha_v2' && window.grecaptcha && window.grecaptcha.render) {
                widgets[context] = window.grecaptcha.render(el, {
                    sitekey: cfg.siteKey,
                    callback: function(token) { setToken(context, token); },
                    'expired-callback': function() { setToken(context, ''); }
                });
            } else if (cfg.provider === 'hcaptcha' && window.hcaptcha) {
                widgets[context] = window.hcaptcha.render(el, {
                    sitekey: cfg.siteKey,
                    callback: function(token) { setToken(context, token); },
                    'expired-callback': function() { setToken(context, ''); }
                });
            }
        });
    };

    window.nnResetCaptcha = function(cfg, context) {
        if (!cfg || !cfg.enabled) {
            return;
        }
        setToken(context, '');
        if (cfg.provider === 'turnstile' && widgets[context] && window.turnstile) {
            window.turnstile.reset(widgets[context]);
        } else if (cfg.provider === 'recaptcha_v2' && widgets[context] && window.grecaptcha) {
            window.grecaptcha.reset(widgets[context]);
        } else if (cfg.provider === 'hcaptcha' && widgets[context] && window.hcaptcha) {
            window.hcaptcha.reset(widgets[context]);
        }
    };
})();
