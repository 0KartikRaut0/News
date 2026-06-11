/**
 * NeoNews Theme - Main JavaScript
 *
 * @package NeoNews
 */

(function() {
    'use strict';

    /**
     * DOM Ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initMobileMenu();
        initSearchOverlay();
        initThemeToggle();
        initStickyHeader();
        initCarousel();
        initLazyLoading();
        initBackToTop();
        initUserMenu();
        initLogoutConfirm();
        initAuthModal();
        showLogoutToast();
        initCookieConsent();
        initProfilePhotoPreview();

        var runHeavy = function() {
            initScrollReveal();
            initEngagement();
            initWeather();
            initLiveDateTime();
        };

        if (neonewsData && neonewsData.perf && neonewsData.perf.lite && 'requestIdleCallback' in window) {
            requestIdleCallback(runHeavy, { timeout: 2000 });
        } else {
            runHeavy();
        }
    });

    /**
     * Mobile Menu Toggle
     */
    function initMobileMenu() {
        var mobileToggle = document.querySelector('.nn-mobile-toggle');
        var mobileNav = document.querySelector('.nn-mobile-nav-pulse') || document.querySelector('.nn-mobile-nav');
        var backdrop = document.getElementById('nn-mobile-backdrop');
        var body = document.body;

        if (!mobileToggle || !mobileNav) {
            return;
        }

        function setMenuOpen(open) {
            mobileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            mobileNav.classList.toggle('active', open);
            mobileNav.setAttribute('aria-hidden', open ? 'false' : 'true');
            body.classList.toggle('nn-mobile-menu-open', open);
            if (backdrop) {
                backdrop.classList.toggle('active', open);
                backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
            }
            if (open) {
                body.style.overflow = 'hidden';
            } else {
                body.style.overflow = '';
            }
        }

        mobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            var isExpanded = this.getAttribute('aria-expanded') === 'true';
            setMenuOpen(!isExpanded);
        });

        var closeBtn = mobileNav.querySelector('.nn-mobile-nav-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                setMenuOpen(false);
                mobileToggle.focus();
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', function() {
                setMenuOpen(false);
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileNav.classList.contains('active')) {
                setMenuOpen(false);
                mobileToggle.focus();
            }
        });

        mobileNav.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                setMenuOpen(false);
            });
        });
    }

    /**
     * Search Overlay
     */
    function initSearchOverlay() {
        var searchToggle = document.querySelector('.nn-search-toggle');
        var searchOverlay = document.querySelector('.nn-search-overlay');
        var searchClose = document.querySelector('.nn-search-close');
        var searchInput = document.querySelector('.nn-search-input-overlay');

        if (!searchToggle || !searchOverlay) {
            return;
        }

        searchToggle.addEventListener('click', function() {
            searchOverlay.classList.add('active');
            document.body.classList.add('nn-search-open');
            
            if (searchInput) {
                setTimeout(function() {
                    searchInput.focus();
                }, 100);
            }
        });

        function closeSearch() {
            searchOverlay.classList.remove('active');
            document.body.classList.remove('nn-search-open');
            searchToggle.focus();
        }

        if (searchClose) {
            searchClose.addEventListener('click', closeSearch);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                closeSearch();
            }
        });

        searchOverlay.addEventListener('click', function(e) {
            if (e.target === searchOverlay) {
                closeSearch();
            }
        });
    }

    /**
     * Theme Toggle (Dark/Light Mode)
     */
    function initThemeToggle() {
        var themeToggle = document.querySelector('.nn-theme-toggle');
        
        if (!themeToggle) {
            return;
        }

        var savedTheme = localStorage.getItem('neonews_theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            themeToggle.setAttribute('data-current', savedTheme);
            updateThemeIcons(savedTheme);
        }

        themeToggle.addEventListener('click', function() {
            var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            var newTheme = currentTheme === 'light' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', newTheme);
            this.setAttribute('data-current', newTheme);
            updateThemeIcons(newTheme);

            localStorage.setItem('neonews_theme', newTheme);
            setCookie('neonews_theme', newTheme, 365);

            if (typeof neonewsData !== 'undefined' && neonewsData.ajaxUrl) {
                var formData = new FormData();
                formData.append('action', 'neonews_toggle_theme');
                formData.append('nonce', neonewsData.nonce);
                formData.append('mode', newTheme);

                fetch(neonewsData.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                }).catch(function() {
                });
            }
        });

        function updateThemeIcons(theme) {
            var sunIcon = themeToggle.querySelector('.nn-icon-sun');
            var moonIcon = themeToggle.querySelector('.nn-icon-moon');

            if (sunIcon && moonIcon) {
                if (theme === 'dark') {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }
            }
        }
    }

    /**
     * Sticky Header
     */
    function initStickyHeader() {
        var header = document.querySelector('.nn-header-pulse') || document.querySelector('.nn-header');
        
        if (!header) {
            return;
        }

        var scrollThreshold = 80;

        function handleScroll() {
            var scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > scrollThreshold) {
                header.classList.add('nn-scrolled');
                header.classList.add('scrolled');
            } else {
                header.classList.remove('nn-scrolled');
                header.classList.remove('scrolled');
            }
        }

        var ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        handleScroll();
    }

    /**
     * Featured Carousel
     */
    function initCarousel() {
        var carousel = document.querySelector('.nn-featured-carousel');
        
        if (!carousel) {
            return;
        }

        var track = carousel.querySelector('.nn-carousel-track');
        var slides = carousel.querySelectorAll('.nn-carousel-slide');
        var prevBtn = carousel.querySelector('.nn-carousel-prev');
        var nextBtn = carousel.querySelector('.nn-carousel-next');
        var dots = carousel.querySelectorAll('.nn-carousel-dot');

        if (slides.length <= 1) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            return;
        }

        var currentIndex = 0;
        var slideCount = slides.length;
        var autoplayInterval = null;
        var autoplayDelay = 5000;

        function goToSlide(index) {
            if (index < 0) {
                index = slideCount - 1;
            } else if (index >= slideCount) {
                index = 0;
            }

            currentIndex = index;
            track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';

            dots.forEach(function(dot, i) {
                dot.classList.toggle('active', i === currentIndex);
                dot.setAttribute('aria-selected', i === currentIndex);
            });
        }

        function nextSlide() {
            goToSlide(currentIndex + 1);
        }

        function prevSlide() {
            goToSlide(currentIndex - 1);
        }

        function startAutoplay() {
            stopAutoplay();
            autoplayInterval = setInterval(nextSlide, autoplayDelay);
        }

        function stopAutoplay() {
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
                autoplayInterval = null;
            }
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                prevSlide();
                stopAutoplay();
                startAutoplay();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                nextSlide();
                stopAutoplay();
                startAutoplay();
            });
        }

        dots.forEach(function(dot, index) {
            dot.addEventListener('click', function() {
                goToSlide(index);
                stopAutoplay();
                startAutoplay();
            });
        });

        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', startAutoplay);

        document.addEventListener('keydown', function(e) {
            if (!carousel.matches(':hover')) {
                return;
            }

            if (e.key === 'ArrowLeft') {
                prevSlide();
                stopAutoplay();
                startAutoplay();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                stopAutoplay();
                startAutoplay();
            }
        });

        var touchStartX = 0;
        var touchEndX = 0;

        track.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            stopAutoplay();
        }, { passive: true });

        track.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startAutoplay();
        }, { passive: true });

        function handleSwipe() {
            var swipeThreshold = 50;
            var diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
        }

        startAutoplay();
    }

    /**
     * Lazy Loading Images (fallback for browsers without native support)
     */
    function initLazyLoading() {
        if ('loading' in HTMLImageElement.prototype) {
            return;
        }

        var lazyImages = document.querySelectorAll('img[loading="lazy"]');
        
        if (!lazyImages.length) {
            return;
        }

        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.dataset.src || img.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px'
            });

            lazyImages.forEach(function(img) {
                imageObserver.observe(img);
            });
        }
    }

    /**
     * Cookie Helper Functions
     */
    function setCookie(name, value, days) {
        var expires = '';
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax';
    }

    function getCookie(name) {
        var nameEQ = name + '=';
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
        return null;
    }

    /**
     * Back to top button
     */
    function initBackToTop() {
        var btn = document.getElementById('nn-back-to-top');
        if (!btn) {
            return;
        }

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 400) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        }, { passive: true });

        btn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /**
     * Scroll reveal (glide-in effect)
     */
    function initScrollReveal() {
        var items = document.querySelectorAll('.nn-reveal');
        if (!items.length) {
            return;
        }

        if (document.body.classList.contains('nn-reduce-motion')) {
            items.forEach(function(el) { el.classList.add('nn-visible'); });
            return;
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('nn-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '0px 0px -40px 0px', threshold: 0.1 });

            items.forEach(function(el) {
                observer.observe(el);
            });
        } else {
            items.forEach(function(el) {
                el.classList.add('nn-visible');
            });
        }
    }

    /**
     * Likes, shares, copy link
     */
    function initEngagement() {
        if (typeof neonewsData === 'undefined') {
            return;
        }

        document.addEventListener('click', function(e) {
            var likeBtn = e.target.closest('.nn-like-btn');
            if (likeBtn) {
                e.preventDefault();
                var postId = likeBtn.getAttribute('data-post-id');
                if (!postId) {
                    return;
                }

                var formData = new FormData();
                formData.append('action', 'neonews_toggle_like');
                formData.append('nonce', neonewsData.nonce);
                formData.append('post_id', postId);

                fetch(neonewsData.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        likeBtn.classList.toggle('liked', data.data.liked);
                        likeBtn.setAttribute('aria-pressed', data.data.liked ? 'true' : 'false');
                        if (data.data.liked) {
                            likeBtn.classList.add('nn-like-pop');
                            spawnHeartBurst(likeBtn);
                            setTimeout(function() {
                                likeBtn.classList.remove('nn-like-pop');
                            }, 500);
                        }
                        var countEl = likeBtn.querySelector('.nn-like-count');
                        if (countEl) {
                            countEl.textContent = data.data.count;
                        }
                        var svg = likeBtn.querySelector('svg');
                        if (svg) {
                            svg.setAttribute('fill', data.data.liked ? 'currentColor' : 'none');
                        }
                    }
                })
                .catch(function() {});
                return;
            }

            var shareEl = e.target.closest('[data-share]');
            if (shareEl) {
                trackShare(shareEl.getAttribute('data-post'));
            }

            var copyBtn = e.target.closest('.nn-copy-link');
            if (copyBtn) {
                e.preventDefault();
                var url = copyBtn.getAttribute('data-url');
                if (navigator.clipboard && url) {
                    navigator.clipboard.writeText(url).then(function() {
                        copyBtn.textContent = '✓';
                        setTimeout(function() { copyBtn.textContent = '⎘'; }, 2000);
                    });
                }
                trackShare(copyBtn.getAttribute('data-post'));
            }
        });
    }

    function trackShare(postId) {
        if (!postId || typeof neonewsData === 'undefined') {
            return;
        }
        if (!neonewsStatisticsAllowed()) {
            return;
        }
        var formData = new FormData();
        formData.append('action', 'neonews_track_share');
        formData.append('nonce', neonewsData.nonce);
        formData.append('post_id', postId);

        fetch(neonewsData.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        }).catch(function() {});
    }

    /**
     * User dropdown menu
     */
    function initUserMenu() {
        var menu = document.querySelector('.nn-user-menu');
        if (!menu) {
            return;
        }

        var toggle = menu.querySelector('.nn-user-toggle');
        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            var open = menu.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function() {
            menu.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    }

    /**
     * Confirm before logging out.
     */
    function initLogoutConfirm() {
        document.addEventListener('click', function(e) {
            var link = e.target.closest('a.nn-logout-link, a.nn-mobile-logout');
            if (!link) {
                return;
            }

            e.preventDefault();

            var message = (typeof neonewsData !== 'undefined' && neonewsData.i18n && neonewsData.i18n.logoutConfirm)
                ? neonewsData.i18n.logoutConfirm
                : 'Are you sure you want to log out?';

            if (window.confirm(message)) {
                window.location.href = link.getAttribute('href');
            }
        });
    }

    function spawnHeartBurst(btn) {
        for (var i = 0; i < 6; i++) {
            var heart = document.createElement('span');
            heart.className = 'nn-heart-particle';
            heart.textContent = '♥';
            heart.style.setProperty('--nn-hx', ((Math.random() - 0.5) * 40) + 'px');
            heart.style.setProperty('--nn-hy', (-20 - Math.random() * 30) + 'px');
            btn.appendChild(heart);
            setTimeout(function(h) {
                return function() { h.remove(); };
            }(heart), 600);
        }
    }

    function initAuthModal() {
        var modal = document.getElementById('nn-auth-modal');
        if (!modal) {
            return;
        }

        var loginForm = document.getElementById('nn-login-form');
        var registerForm = document.getElementById('nn-register-form');
        var forgotForm = document.getElementById('nn-forgot-form');
        var loginPanel = document.getElementById('nn-auth-panel-login');
        var registerPanel = document.getElementById('nn-auth-panel-register');
        var forgotPanel = document.getElementById('nn-auth-panel-forgot');
        var authTabs = modal.querySelector('.nn-auth-tabs');
        var cfg = typeof neonewsData !== 'undefined' ? neonewsData.captcha : null;
        var captchaScriptsLoaded = false;

        function loadScript(url) {
            return new Promise(function(resolve, reject) {
                if (!url) {
                    resolve();
                    return;
                }
                var existing = document.querySelector('script[src="' + url + '"]');
                if (existing) {
                    resolve();
                    return;
                }
                var script = document.createElement('script');
                script.src = url;
                script.async = true;
                script.defer = true;
                script.onload = resolve;
                script.onerror = reject;
                document.body.appendChild(script);
            });
        }

        function ensureCaptchaAssets() {
            if (!cfg || !cfg.enabled || !cfg.lazyLoad || captchaScriptsLoaded) {
                return Promise.resolve();
            }
            var chain = Promise.resolve();
            if (cfg.helperUrl && typeof window.nnInitCaptcha === 'undefined') {
                chain = loadScript(cfg.helperUrl);
            }
            return chain.then(function() {
                return loadScript(cfg.scriptUrl);
            }).then(function() {
                captchaScriptsLoaded = true;
            });
        }

        function panelError(panel) {
            return panel ? panel.querySelector('.nn-auth-error') : null;
        }

        function panelSuccess(panel) {
            return panel ? panel.querySelector('.nn-auth-success') : null;
        }

        function clearPanelMessages(panel) {
            var errorEl = panelError(panel);
            var successEl = panelSuccess(panel);
            if (errorEl) {
                errorEl.hidden = true;
            }
            if (successEl) {
                successEl.hidden = true;
            }
        }

        function mountCaptcha(attempt) {
            attempt = attempt || 0;
            if (!window.nnInitCaptcha || !cfg || !cfg.enabled) {
                return;
            }
            window.nnInitCaptcha(cfg);
            if (!cfg.isInvisible && attempt < 12) {
                var pending = ['login', 'register'].some(function(ctx) {
                    var el = document.getElementById('nn-captcha-' + ctx);
                    return el && el.dataset.mounted !== '1';
                });
                if (pending) {
                    setTimeout(function() { mountCaptcha(attempt + 1); }, 250);
                }
            }
        }

        function switchTab(tab) {
            if (authTabs) {
                authTabs.hidden = tab === 'forgot';
            }
            modal.querySelectorAll('.nn-auth-tab').forEach(function(btn) {
                var active = btn.getAttribute('data-auth-tab') === tab;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            [loginPanel, registerPanel, forgotPanel].forEach(function(panel) {
                if (!panel) {
                    return;
                }
                var name = panel.getAttribute('data-auth-panel');
                var show = name === tab;
                panel.classList.toggle('is-active', show);
                panel.hidden = !show;
                if (show) {
                    clearPanelMessages(panel);
                }
            });

            mountCaptcha();
            var panelMap = { login: loginPanel, register: registerPanel, forgot: forgotPanel };
            var activePanel = panelMap[tab] || loginPanel;
            var input = activePanel ? activePanel.querySelector('input:not([type="hidden"])') : null;
            if (input) {
                setTimeout(function() { input.focus(); }, 100);
            }
        }

        function openModal(tab) {
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            ensureCaptchaAssets().finally(function() {
                switchTab(tab || 'login');
            });
        }

        function closeModal() {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function submitAuthForm(form, action, context) {
            var panel = form.closest('.nn-auth-panel');
            var errorEl = panelError(panel);

            if (errorEl) {
                errorEl.hidden = true;
            }

            var runSubmit = function() {
                var formData = new FormData(form);
                formData.append('action', action);
                formData.append('nonce', neonewsData.nonce);

                fetch(neonewsData.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        window.location.href = data.data.redirect || neonewsData.homeUrl;
                        return;
                    }
                    if (errorEl) {
                        errorEl.textContent = data.data && data.data.message ? data.data.message : neonewsData.i18n.error;
                        errorEl.hidden = false;
                    }
                    if (window.nnResetCaptcha && cfg) {
                        window.nnResetCaptcha(cfg, context);
                    }
                })
                .catch(function() {
                    if (errorEl) {
                        errorEl.textContent = neonewsData.i18n.error;
                        errorEl.hidden = false;
                    }
                });
            };

            if (window.nnGetCaptchaToken && cfg && cfg.enabled) {
                window.nnGetCaptchaToken(context, cfg).then(function(token) {
                    var tokenField = form.querySelector('.nn-captcha-token');
                    if (tokenField) {
                        tokenField.value = token;
                    }
                    runSubmit();
                }).catch(function() {
                    if (errorEl) {
                        errorEl.textContent = neonewsData.i18n.captcha || 'Please complete the CAPTCHA verification.';
                        errorEl.hidden = false;
                    }
                });
                return;
            }

            runSubmit();
        }

        document.querySelectorAll('.nn-open-login').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('login');
            });
        });

        document.querySelectorAll('.nn-open-signup').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('register');
            });
        });

        modal.querySelectorAll('.nn-auth-tab, [data-auth-tab]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                switchTab(btn.getAttribute('data-auth-tab'));
            });
        });

        modal.querySelectorAll('[data-close-modal]').forEach(function(el) {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });

        if (loginForm && typeof neonewsData !== 'undefined') {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitAuthForm(loginForm, 'neonews_ajax_login', 'login');
            });
        }

        if (registerForm && typeof neonewsData !== 'undefined') {
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitAuthForm(registerForm, 'neonews_ajax_register', 'register');
            });
        }

        if (forgotForm && typeof neonewsData !== 'undefined') {
            forgotForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var panel = forgotForm.closest('.nn-auth-panel');
                var errorEl = panelError(panel);
                var successEl = panelSuccess(panel);
                clearPanelMessages(panel);

                var formData = new FormData(forgotForm);
                formData.append('action', 'neonews_ajax_lost_password');
                formData.append('nonce', neonewsData.nonce);

                fetch(neonewsData.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        if (successEl) {
                            successEl.textContent = data.data && data.data.message ? data.data.message : '';
                            successEl.hidden = false;
                        }
                        forgotForm.reset();
                        return;
                    }
                    if (errorEl) {
                        errorEl.textContent = data.data && data.data.message ? data.data.message : neonewsData.i18n.error;
                        errorEl.hidden = false;
                        if (data.data && data.data.code === 'not_found' && registerPanel) {
                            errorEl.innerHTML = errorEl.textContent + ' <button type="button" class="nn-auth-link-btn" data-auth-tab="register">' + (neonewsData.i18n.signUp || 'Sign up') + '</button>';
                            errorEl.querySelector('[data-auth-tab="register"]').addEventListener('click', function(ev) {
                                ev.preventDefault();
                                switchTab('register');
                            });
                        }
                    }
                })
                .catch(function() {
                    if (errorEl) {
                        errorEl.textContent = neonewsData.i18n.error;
                        errorEl.hidden = false;
                    }
                });
            });
        }

        mountCaptcha();

        if (window.location.search.indexOf('logged_out=1') !== -1) {
            setTimeout(function() { openModal('login'); }, 800);
        }
    }

    function showLogoutToast() {
        var toast = document.getElementById('nn-toast');
        if (!toast || !toast.classList.contains('nn-toast-visible')) {
            return;
        }
        setTimeout(function() {
            toast.classList.remove('nn-toast-visible');
        }, 5000);
    }

    function initLiveDateTime() {
        var el = document.getElementById('nn-live-datetime');
        if (!el) {
            return;
        }
        function tick() {
            var now = new Date();
            var datePart = now.toLocaleDateString(undefined, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            var timeOpts = { hour: 'numeric', minute: '2-digit', hour12: true };
            if (!document.body.classList.contains('nn-reduce-motion')) {
                timeOpts.second = '2-digit';
            }
            var timePart = now.toLocaleTimeString(undefined, timeOpts);
            el.textContent = datePart + ' · ' + timePart;
            el.setAttribute('datetime', now.toISOString());
        }
        tick();
        setInterval(tick, document.body.classList.contains('nn-reduce-motion') ? 60000 : 1000);
    }

    function neonewsStatisticsAllowed() {
        if (document.body.classList.contains('nn-consent-no-statistics') ||
            document.body.classList.contains('nn-consent-pending')) {
            return false;
        }
        return true;
    }

    function neonewsMarketingAllowed() {
        if (document.body.classList.contains('nn-consent-no-marketing') ||
            document.body.classList.contains('nn-consent-pending')) {
            return false;
        }
        return true;
    }

    function pushAdSenseUnits() {
        document.querySelectorAll('ins.adsbygoogle:not([data-adsbygoogle-status])').forEach(function() {
            try {
                (window.adsbygoogle = window.adsbygoogle || []).push({});
            } catch (e) {}
        });
    }

    function loadAdSenseScript(callback) {
        if (typeof neonewsData === 'undefined' || !neonewsData.consent) {
            if (callback) {
                callback();
            }
            return;
        }

        var cfg = neonewsData.consent;
        if (!cfg.adsenseEnabled || !cfg.adsensePublisherId) {
            if (callback) {
                callback();
            }
            return;
        }

        if (window.nnAdSenseLoaded) {
            pushAdSenseUnits();
            if (callback) {
                callback();
            }
            return;
        }

        var script = document.createElement('script');
        script.async = true;
        script.crossOrigin = 'anonymous';
        script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + encodeURIComponent(cfg.adsensePublisherId);
        script.onload = function() {
            window.nnAdSenseLoaded = true;
            pushAdSenseUnits();
            if (callback) {
                callback();
            }
        };
        document.head.appendChild(script);
    }

    function loadConsentScripts() {
        if (typeof neonewsData === 'undefined' || !neonewsData.consent) {
            return;
        }

        var cfg = neonewsData.consent;
        if (cfg.analyticsEnabled && cfg.gaId && !window.nnGaLoaded) {
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg.gaId);
            script.onload = function() {
                window.dataLayer = window.dataLayer || [];
                function gtag() {
                    window.dataLayer.push(arguments);
                }
                window.gtag = gtag;
                gtag('js', new Date());
                gtag('config', cfg.gaId, { anonymize_ip: true });
                window.nnGaLoaded = true;
            };
            document.head.appendChild(script);
        }

        if (neonewsMarketingAllowed()) {
            loadAdSenseScript();
        }
    }

    function syncMarketingVisibility() {
        var show = neonewsMarketingAllowed();

        document.querySelectorAll('.nn-consent-marketing, .nn-consent-marketing-hidden').forEach(function(el) {
            if (show) {
                el.classList.remove('nn-consent-marketing-hidden');
            } else {
                el.classList.add('nn-consent-marketing-hidden');
            }
        });
    }

    function applyCookieConsent(level) {
        var consentClasses = [
            'nn-consent-all',
            'nn-consent-essential',
            'nn-consent-pending',
            'nn-consent-no-marketing',
            'nn-consent-no-analytics',
            'nn-consent-no-statistics'
        ];

        consentClasses.forEach(function(className) {
            document.body.classList.remove(className);
        });

        if (level === 'all' || level === 'essential') {
            document.body.classList.add('nn-consent-' + level);
        } else {
            document.body.classList.add('nn-consent-pending');
        }

        if (typeof neonewsData !== 'undefined' && neonewsData.consent) {
            neonewsData.consent.level = level;

            var cfg = neonewsData.consent;
            if (cfg.bannerEnabled && cfg.gateAds && level !== 'all') {
                document.body.classList.add('nn-consent-no-marketing');
            }
            if (cfg.bannerEnabled && cfg.analyticsEnabled && level !== 'all') {
                document.body.classList.add('nn-consent-no-analytics');
            }
            if (cfg.bannerEnabled && cfg.gateStatistics && level !== 'all') {
                document.body.classList.add('nn-consent-no-statistics');
            }
        }

        syncMarketingVisibility();

        if (level === 'all') {
            loadConsentScripts();
        }

        try {
            window.dispatchEvent(new Event('resize'));
        } catch (e) {}
    }

    function initCookieConsent() {
        var banner = document.getElementById('nn-cookie-banner');
        if (!banner || typeof neonewsData === 'undefined') {
            return;
        }

        function setConsentCookie(value) {
            var maxAge = 365 * 24 * 60 * 60;
            document.cookie = 'neonews_cookie_consent=' + value + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
            try {
                localStorage.setItem('neonews_cookie_consent', value);
            } catch (e) {}
        }

        function syncCookiePadding() {
            var extra = 0;
            var adBar = document.querySelector('.nn-ad-mobile-wrap');
            if (adBar && window.getComputedStyle(adBar).display !== 'none') {
                extra += adBar.offsetHeight || 0;
            }

            if (banner.classList.contains('nn-cookie-hidden')) {
                document.body.style.paddingBottom = extra ? extra + 'px' : '';
                return;
            }

            var bannerHeight = banner.offsetHeight || 0;
            document.body.style.paddingBottom = (bannerHeight + extra + 8) + 'px';
        }

        function hideBanner() {
            banner.classList.add('nn-cookie-hidden');
            banner.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('nn-cookie-visible');
            syncCookiePadding();
        }

        function showBanner() {
            banner.classList.remove('nn-cookie-hidden');
            banner.setAttribute('aria-hidden', 'false');
            document.body.classList.add('nn-cookie-visible');
            syncCookiePadding();
        }

        function saveConsent(value) {
            setConsentCookie(value);
            hideBanner();
            applyCookieConsent(value);

            var formData = new FormData();
            formData.append('action', 'neonews_cookie_consent');
            formData.append('nonce', neonewsData.nonce);
            formData.append('consent', value);

            fetch(neonewsData.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data && data.success && data.data && data.data.config && neonewsData.consent) {
                        neonewsData.consent = data.data.config;
                        applyCookieConsent(value);
                    }
                })
                .catch(function() {});
        }

        var stored = '';
        try {
            stored = localStorage.getItem('neonews_cookie_consent') || '';
        } catch (e) {}
        if (!stored && document.cookie.indexOf('neonews_cookie_consent=') !== -1) {
            var match = document.cookie.match(/neonews_cookie_consent=([^;]+)/);
            stored = match ? match[1] : '';
        }
        if (stored === 'all' || stored === 'essential') {
            hideBanner();
            applyCookieConsent(stored);
        } else {
            applyCookieConsent('');
            showBanner();
        }

        banner.querySelectorAll('[data-consent]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                saveConsent(this.getAttribute('data-consent'));
            });
        });

        document.querySelectorAll('.nn-cookie-reopen').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                showBanner();
            });
        });

        window.addEventListener('resize', syncCookiePadding);
        syncCookiePadding();
    }

    function initProfilePhotoPreview() {
        var input = document.getElementById('nn-profile-photo-input');
        var preview = document.getElementById('nn-profile-photo-preview');
        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function() {
            var file = input.files && input.files[0];
            if (!file) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function(event) {
                preview.innerHTML = '<img src="' + event.target.result + '" class="nn-avatar nn-avatar-custom nn-profile-photo" width="96" height="96" alt="" />';
            };
            reader.readAsDataURL(file);
        });
    }

    function loadWeatherTarget(el) {
        if (!el || typeof neonewsData === 'undefined') {
            return;
        }

        var isHeader = el.id === 'nn-weather-header';
        var body = isHeader ? el : el.querySelector('.nn-weather-body');
        if (!body) {
            return;
        }

        var formData = new FormData();
        formData.append('action', 'neonews_weather');
        formData.append('nonce', neonewsData.nonce);
        formData.append('city', el.getAttribute('data-city') || 'New York');
        formData.append('lat', el.getAttribute('data-lat') || '');
        formData.append('lon', el.getAttribute('data-lon') || '');

        fetch(neonewsData.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.success) {
                var err = (res.data && res.data.message) ? res.data.message : neonewsData.i18n.error;
                body.innerHTML = isHeader
                    ? '<span class="nn-header-weather-error">' + err + '</span>'
                    : '<p class="nn-weather-error">' + err + '</p>';
                return;
            }
            var d = res.data;
            if (isHeader) {
                body.innerHTML =
                    '<span class="nn-header-weather-icon" aria-hidden="true">' + d.icon + '</span>' +
                    '<span class="nn-header-weather-temp">' + d.temp + d.unit + '</span>' +
                    '<span class="nn-header-weather-city">' + d.city + '</span>' +
                    '<span class="nn-header-weather-label">' + d.label + '</span>';
                return;
            }
            body.innerHTML =
                '<div class="nn-weather-main">' +
                    '<span class="nn-weather-icon">' + d.icon + '</span>' +
                    '<div class="nn-weather-temp">' + d.temp + d.unit + '</div>' +
                '</div>' +
                '<div class="nn-weather-location">' + d.city + (d.country ? ', ' + d.country : '') + '</div>' +
                '<div class="nn-weather-label">' + d.label + '</div>' +
                '<div class="nn-weather-stats">' +
                    '<span>H ' + d.high + d.unit + '</span>' +
                    '<span>L ' + d.low + d.unit + '</span>' +
                    '<span>💧 ' + d.humidity + '%</span>' +
                    '<span>💨 ' + d.wind + ' km/h</span>' +
                '</div>';
        })
        .catch(function() {
            body.innerHTML = isHeader
                ? '<span class="nn-header-weather-error">' + neonewsData.i18n.error + '</span>'
                : '<p class="nn-weather-error">' + neonewsData.i18n.error + '</p>';
        });
    }

    function initWeather() {
        var header = document.getElementById('nn-weather-header');
        var widget = document.getElementById('nn-weather-widget');
        var targets = [header, widget].filter(Boolean);

        if (!targets.length) {
            return;
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        loadWeatherTarget(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '120px 0px' });
            targets.forEach(function(node) { observer.observe(node); });
            return;
        }

        targets.forEach(loadWeatherTarget);
    }

})();
