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
        initPageTransition();
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
        initInlineVideos();
        initMobileAdCollapse();

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
     * Page glide transition for primary navigation
     */
    function initPageTransition() {
        var mainContent = document.getElementById('main-content');
        if (!mainContent) {
            return;
        }

        document.body.classList.add('nn-page-enter');
        window.requestAnimationFrame(function() {
            document.body.classList.add('nn-page-enter-active');
        });

        mainContent.addEventListener('transitionend', function(e) {
            if (e.propertyName !== 'opacity') {
                return;
            }
            if (document.body.classList.contains('nn-page-enter-active')) {
                document.body.classList.remove('nn-page-enter', 'nn-page-enter-active');
            }
        });

        document.addEventListener('click', function(e) {
            var link = e.target.closest('a');
            if (!link || !isNavTransitionLink(link)) {
                return;
            }

            var href = link.getAttribute('href');
            if (!href || href.charAt(0) === '#') {
                return;
            }
            if (link.target === '_blank' || link.hasAttribute('download')) {
                return;
            }
            if (link.classList.contains('nn-auth-open') || link.closest('.nn-auth-modal')) {
                return;
            }

            var destination;
            try {
                destination = new URL(link.href, window.location.href);
            } catch (err) {
                return;
            }

            if (destination.origin !== window.location.origin) {
                return;
            }

            if (destination.pathname === window.location.pathname && destination.search === window.location.search) {
                return;
            }

            e.preventDefault();

            if (document.body.classList.contains('nn-page-leaving')) {
                return;
            }

            document.body.classList.add('nn-page-leaving');

            window.setTimeout(function() {
                window.location.href = link.href;
            }, 280);
        });
    }

    function isNavTransitionLink(link) {
        if (link.classList.contains('nn-topic-pill')) {
            return true;
        }

        return !!link.closest('.nn-nav-pulse, .nn-mobile-nav-pulse, .nn-mobile-nav, #site-navigation');
    }

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
     * Collapsible mobile sticky ad banner.
     */
    function initMobileAdCollapse() {
        if (typeof neonewsData === 'undefined' || !neonewsData.mobileAd || !neonewsData.mobileAd.collapsible) {
            return;
        }

        var wrap = document.querySelector('.nn-ad-mobile-wrap');
        if (!wrap) {
            return;
        }

        var STORE_KEY = 'nn_mobile_ad_collapsed';
        var STORE_TTL = 30 * 60 * 1000; // 30 minutes

        function isCollapsed() {
            try {
                var item = JSON.parse(sessionStorage.getItem(STORE_KEY) || 'null');
                return item && item.collapsed && (Date.now() - item.ts < STORE_TTL);
            } catch (e) {
                return false;
            }
        }

        function saveState(collapsed) {
            try {
                sessionStorage.setItem(STORE_KEY, JSON.stringify({ collapsed: collapsed, ts: Date.now() }));
            } catch (e) {}
        }

        function updateBodyPadding() {
            var h = wrap.classList.contains('nn-ad-collapsed') ? 0 : (wrap.offsetHeight || 0);
            document.body.style.paddingBottom = h ? (h + 'px') : '';
            // also nudge floating WA icon
            var waFloat = document.querySelector('.nn-wa-float-right');
            if (waFloat) {
                waFloat.style.bottom = h ? ((h + 16) + 'px') : '';
            }
        }

        // Build toggle button
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'nn-ad-mobile-collapse-btn';
        btn.setAttribute('aria-label', 'Collapse ad');

        var arrowSvg = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"></polyline></svg>';
        var label = document.createElement('span');
        label.className = 'nn-ad-mobile-collapse-label';
        label.textContent = neonewsData.mobileAd.collapsedText || 'Ad';

        btn.innerHTML = arrowSvg;
        btn.appendChild(label);
        wrap.insertBefore(btn, wrap.firstChild);

        function setCollapsed(collapsed, animate) {
            if (animate) {
                wrap.classList.add('nn-ad-collapsing');
            }
            wrap.classList.toggle('nn-ad-collapsed', collapsed);
            btn.setAttribute('aria-label', collapsed ? 'Expand ad' : 'Collapse ad');
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            label.textContent = collapsed
                ? (neonewsData.mobileAd.collapsedText || 'Ad')
                : '×';
            saveState(collapsed);
            setTimeout(function() {
                wrap.classList.remove('nn-ad-collapsing');
                updateBodyPadding();
            }, 260);
        }

        btn.addEventListener('click', function() {
            setCollapsed(!wrap.classList.contains('nn-ad-collapsed'), true);
        });

        // Restore previous state
        if (isCollapsed()) {
            setCollapsed(true, false);
        } else {
            label.textContent = '×';
            btn.setAttribute('aria-expanded', 'true');
        }

        updateBodyPadding();
        window.addEventListener('resize', updateBodyPadding);
    }

    /**
     * Play homepage video cards in place (one at a time).
     */
    function initInlineVideos() {
        var triggers = document.querySelectorAll('.nn-watch-trigger[data-yt-id]');
        if (!triggers.length) {
            return;
        }

        var activeCard = null;

        function stop(card) {
            if (!card) {
                return;
            }
            var player = card.querySelector('.nn-watch-player');
            if (player) {
                player.remove();
            }
            card.classList.remove('is-playing');
            var trigger = card.querySelector('.nn-watch-trigger');
            if (trigger) {
                trigger.focus({ preventScroll: true });
            }
            if (activeCard === card) {
                activeCard = null;
            }
        }

        function play(trigger) {
            var card = trigger.closest('.nn-watch-card');
            var id = trigger.getAttribute('data-yt-id');
            if (!card || !/^[A-Za-z0-9_-]{11}$/.test(id)) {
                return;
            }

            if (activeCard && activeCard !== card) {
                stop(activeCard);
            }

            var player = document.createElement('div');
            player.className = 'nn-watch-player';

            var iframe = document.createElement('iframe');
            iframe.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&rel=0&playsinline=1&modestbranding=1';
            iframe.title = trigger.getAttribute('aria-label') || 'Video';
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
            iframe.setAttribute('allowfullscreen', '');

            var bar = document.createElement('div');
            bar.className = 'nn-watch-player-bar';

            var close = document.createElement('button');
            close.type = 'button';
            close.className = 'nn-watch-player-close';
            close.setAttribute('aria-label', 'Close video');
            close.innerHTML = '&times;';
            close.addEventListener('click', function(e) {
                e.stopPropagation();
                stop(card);
            });

            bar.appendChild(close);
            player.appendChild(bar);
            player.appendChild(iframe);
            card.appendChild(player);
            card.classList.add('is-playing');
            activeCard = card;
        }

        triggers.forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                play(trigger);
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && activeCard) {
                stop(activeCard);
            }
        });
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
     * Fallback copy using a temporary textarea (works on http:// and older browsers).
     */
    function fallbackCopy(text, onSuccess) {
        try {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0;';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            var ok = document.execCommand('copy');
            document.body.removeChild(ta);
            if (ok && onSuccess) {
                onSuccess();
            }
        } catch (err) {}
    }

    /**
     * Likes, shares, copy link
     */
    function initEngagement() {
        if (typeof neonewsData === 'undefined') {
            return;
        }

        // ── Guest sign-in toast ──────────────────────────────────────────────
        var _guestToastTimer = null;
        function showGuestLikeToast(anchorEl) {
            // Remove any existing toast.
            var old = document.getElementById('nn-guest-like-toast');
            if (old) { old.remove(); }
            if (_guestToastTimer) { clearTimeout(_guestToastTimer); }

            var toast = document.createElement('div');
            toast.id = 'nn-guest-like-toast';
            toast.className = 'nn-guest-toast';
            toast.setAttribute('role', 'status');
            // Use .nn-open-login so the existing auth modal opens — no redirect to wp-login.php.
            toast.innerHTML =
                '<span class="nn-gt-icon">❤️</span>' +
                '<span class="nn-gt-msg">Sign in to like this post</span>' +
                '<button type="button" class="nn-gt-cta nn-open-login">Sign in</button>' +
                '<button type="button" class="nn-gt-close" aria-label="Close">×</button>';

            // Position near the button.
            document.body.appendChild(toast);
            var rect = anchorEl.getBoundingClientRect();
            var scrollY = window.scrollY || window.pageYOffset;
            toast.style.top  = (rect.bottom + scrollY + 8) + 'px';
            toast.style.left = Math.max(8, rect.left - 40) + 'px';

            // Animate in.
            requestAnimationFrame(function() { toast.classList.add('nn-gt-visible'); });

            toast.querySelector('.nn-gt-close').addEventListener('click', function() {
                dismissGuestToast(toast);
            });

            _guestToastTimer = setTimeout(function() {
                dismissGuestToast(toast);
            }, 4000);
        }

        function dismissGuestToast(toast) {
            if (!toast) { return; }
            toast.classList.remove('nn-gt-visible');
            toast.classList.add('nn-gt-hide');
            setTimeout(function() { if (toast.parentNode) { toast.remove(); } }, 350);
        }

        document.addEventListener('click', function(e) {
            var likeBtn = e.target.closest('.nn-like-btn');
            if (likeBtn) {
                e.preventDefault();
                var postId = likeBtn.getAttribute('data-post-id');
                if (!postId) { return; }

                // ── Guest user ───────────────────────────────────────────
                if (likeBtn.classList.contains('nn-like-btn-guest')) {
                    showGuestLikeToast(likeBtn);
                    // Track interest silently.
                    var gfd = new FormData();
                    gfd.append('action', 'neonews_guest_like');
                    gfd.append('nonce', neonewsData.nonce);
                    gfd.append('post_id', postId);
                    fetch(neonewsData.ajaxUrl, { method: 'POST', body: gfd, credentials: 'same-origin' }).catch(function(){});
                    return;
                }

                // ── Logged-in user ───────────────────────────────────────
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
                var url = copyBtn.getAttribute('data-url') || window.location.href;
                var originalText = copyBtn.textContent;

                function markCopied() {
                    copyBtn.textContent = '✓';
                    copyBtn.setAttribute('aria-label', 'Copied!');
                    setTimeout(function() {
                        copyBtn.textContent = originalText;
                        copyBtn.setAttribute('aria-label', 'Copy link');
                    }, 2000);
                }

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(markCopied).catch(function() {
                        fallbackCopy(url, markCopied);
                    });
                } else {
                    fallbackCopy(url, markCopied);
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
            // Dismiss guest like toast if open.
            var gt = document.getElementById('nn-guest-like-toast');
            if (gt) { gt.remove(); }
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

        // Use event delegation so dynamically-added buttons (e.g. guest like toast) also work.
        document.addEventListener('click', function(e) {
            if (e.target.closest('.nn-open-login')) {
                e.preventDefault();
                openModal('login');
            } else if (e.target.closest('.nn-open-signup')) {
                e.preventDefault();
                openModal('register');
            }
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

        /* ── Show / hide password toggle ── */
        modal.addEventListener('click', function(e) {
            var btn = e.target.closest('.nn-pass-toggle');
            if (!btn) return;
            var targetId = btn.getAttribute('data-target');
            var input = targetId ? document.getElementById(targetId) : btn.closest('.nn-pass-wrap').querySelector('input');
            if (!input) return;
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            var showIcon = btn.querySelector('.nn-pass-show');
            var hideIcon = btn.querySelector('.nn-pass-hide');
            if (showIcon) showIcon.hidden = isHidden;
            if (hideIcon) hideIcon.hidden = !isHidden;
            btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });

        /* ── Real-time username availability check ── */
        var usernameInput = modal.querySelector('#nn-register-username');
        var usernameFeedback = modal.querySelector('#nn-username-feedback');
        if (usernameInput && usernameFeedback && typeof neonewsData !== 'undefined') {
            var usernameTimer = null;
            usernameInput.addEventListener('input', function() {
                clearTimeout(usernameTimer);
                var val = usernameInput.value.trim();
                usernameFeedback.textContent = '';
                usernameFeedback.className = 'nn-username-feedback';
                if (val.length < 3) return;
                usernameFeedback.textContent = '…';
                usernameTimer = setTimeout(function() {
                    var fd = new FormData();
                    fd.append('action', 'neonews_ajax_check_username');
                    fd.append('nonce', neonewsData.nonce);
                    fd.append('username', val);
                    fetch(neonewsData.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (usernameInput.value.trim() !== val) return; // stale response
                            if (data.success) {
                                usernameFeedback.textContent = '✓ ' + (data.data.message || 'Available');
                                usernameFeedback.className = 'nn-username-feedback nn-uf-ok';
                            } else {
                                usernameFeedback.textContent = '✗ ' + (data.data && data.data.message ? data.data.message : 'Unavailable');
                                usernameFeedback.className = 'nn-username-feedback nn-uf-err';
                            }
                        })
                        .catch(function() {});
                }, 500);
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
            var w = window.innerWidth;
            var isXs = w <= 480;
            var isMobile = w <= 640;
            var datePart, timePart;

            if (isXs) {
                // Ultra-compact: "Thu, Nov 12"
                datePart = now.toLocaleDateString(undefined, {
                    weekday: 'short', month: 'short', day: 'numeric'
                });
                timePart = now.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit', hour12: true });
            } else if (isMobile) {
                // Compact: "Thu, Nov 12, 2026"
                datePart = now.toLocaleDateString(undefined, {
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
                });
                timePart = now.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit', hour12: true });
            } else {
                // Full: "Thursday, November 12, 2026"
                datePart = now.toLocaleDateString(undefined, {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                });
                var tOpts = { hour: 'numeric', minute: '2-digit', hour12: true };
                if (!document.body.classList.contains('nn-reduce-motion')) { tOpts.second = '2-digit'; }
                timePart = now.toLocaleTimeString(undefined, tOpts);
            }

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

    var WEATHER_STORE = 'nn_weather_loc';

    function weatherText(key, fallback) {
        var w = (typeof neonewsData !== 'undefined' && neonewsData.i18n && neonewsData.i18n.weather) || {};
        return w[key] || fallback;
    }

    function weatherEsc(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function getSavedWeatherLoc() {
        try {
            var loc = JSON.parse(window.localStorage.getItem(WEATHER_STORE) || 'null');
            return loc && (loc.city || loc.lat) ? loc : null;
        } catch (e) {
            return null;
        }
    }

    function saveWeatherLoc(loc) {
        try {
            if (loc) {
                window.localStorage.setItem(WEATHER_STORE, JSON.stringify(loc));
            } else {
                window.localStorage.removeItem(WEATHER_STORE);
            }
        } catch (e) {}
    }

    function guessCityFromTimezone() {
        try {
            var tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            var aliases = {
                'Asia/Calcutta': 'Kolkata',
                'Asia/Saigon': 'Ho Chi Minh City',
                'Asia/Katmandu': 'Kathmandu',
                'Asia/Rangoon': 'Yangon',
                'Europe/Kiev': 'Kyiv'
            };
            if (aliases[tz]) {
                return aliases[tz];
            }
            if (tz.indexOf('/') === -1 || /^(Etc|UTC|GMT)/.test(tz)) {
                return '';
            }
            return tz.split('/').pop().replace(/_/g, ' ');
        } catch (e) {
            return '';
        }
    }

    function getDefaultWeatherLoc(el) {
        return {
            city: el.getAttribute('data-city') || 'New York',
            lat: el.getAttribute('data-lat') || '',
            lon: el.getAttribute('data-lon') || '',
            mode: 'default'
        };
    }

    function resolveWeatherLoc(el) {
        if (el.getAttribute('data-personal') === '1') {
            var saved = getSavedWeatherLoc();
            if (saved) {
                return saved;
            }
            if (el.getAttribute('data-tz-guess') === '1') {
                var guess = guessCityFromTimezone();
                if (guess) {
                    return { city: guess, lat: '', lon: '', mode: 'tz' };
                }
            }
        }
        return getDefaultWeatherLoc(el);
    }

    function getWeatherTargets() {
        return [document.getElementById('nn-weather-header'), document.getElementById('nn-weather-widget')].filter(Boolean);
    }

    function refreshAllWeather() {
        getWeatherTargets().forEach(function(el) {
            loadWeatherTarget(el);
        });
    }

    function chooseWeatherLoc(loc) {
        saveWeatherLoc(loc);
        refreshAllWeather();
    }

    function buildWeatherPicker(el, isHeader) {
        var picker = document.createElement('div');
        picker.className = 'nn-weather-picker' + (isHeader ? ' nn-weather-picker-pop' : '');
        picker.hidden = true;
        picker.innerHTML =
            ('geolocation' in navigator
                ? '<button type="button" class="nn-weather-gps">📍 ' + weatherEsc(weatherText('useMine', 'Use my location')) + '</button>'
                : '') +
            '<form class="nn-weather-search" role="search">' +
                '<input type="search" maxlength="60" placeholder="' + weatherEsc(weatherText('searchCity', 'Search city…')) + '" aria-label="' + weatherEsc(weatherText('searchCity', 'Search city…')) + '" />' +
                '<button type="submit">' + weatherEsc(weatherText('go', 'Search')) + '</button>' +
            '</form>' +
            '<p class="nn-weather-picker-status" aria-live="polite"></p>' +
            '<ul class="nn-weather-results"></ul>' +
            (getSavedWeatherLoc()
                ? '<button type="button" class="nn-weather-reset">' + weatherEsc(weatherText('reset', 'Reset to default')) + '</button>'
                : '') +
            '<p class="nn-weather-privacy">🔒 ' + weatherEsc(weatherText('privacy', 'Saved only on this device. Never shared.')) + '</p>';

        var status = picker.querySelector('.nn-weather-picker-status');
        var list = picker.querySelector('.nn-weather-results');
        var form = picker.querySelector('.nn-weather-search');
        var gps = picker.querySelector('.nn-weather-gps');
        var reset = picker.querySelector('.nn-weather-reset');

        picker.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        if (gps) {
            gps.addEventListener('click', function() {
                status.textContent = weatherText('locating', 'Finding your area…');
                navigator.geolocation.getCurrentPosition(function(pos) {
                    chooseWeatherLoc({
                        city: weatherText('yourArea', 'Your area'),
                        lat: Math.round(pos.coords.latitude * 10) / 10,
                        lon: Math.round(pos.coords.longitude * 10) / 10,
                        mode: 'gps'
                    });
                }, function() {
                    status.textContent = weatherText('denied', 'Location permission denied — search your city instead.');
                }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 1800000 });
            });
        }

        if (reset) {
            reset.addEventListener('click', function() {
                chooseWeatherLoc(null);
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var q = form.querySelector('input').value.trim();
            if (q.length < 2) {
                return;
            }
            status.textContent = neonewsData.i18n.loading;
            list.innerHTML = '';

            var fd = new FormData();
            fd.append('action', 'neonews_weather_search');
            fd.append('nonce', neonewsData.nonce);
            fd.append('q', q);

            fetch(neonewsData.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success) {
                        status.textContent = (res.data && res.data.message) || neonewsData.i18n.error;
                        return;
                    }
                    if (!res.data.length) {
                        status.textContent = weatherText('noResults', 'No matching city found.');
                        return;
                    }
                    status.textContent = '';
                    res.data.forEach(function(row) {
                        var li = document.createElement('li');
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.textContent = [row.city, row.region, row.country].filter(Boolean).join(', ');
                        btn.addEventListener('click', function() {
                            chooseWeatherLoc({ city: row.city, country: row.country, lat: row.lat, lon: row.lon, mode: 'city' });
                        });
                        li.appendChild(btn);
                        list.appendChild(li);
                    });
                })
                .catch(function() {
                    status.textContent = neonewsData.i18n.error;
                });
        });

        return picker;
    }

    function attachWeatherPicker(el, body, isHeader) {
        if (el.getAttribute('data-personal') !== '1') {
            return;
        }

        var picker = buildWeatherPicker(el, isHeader);
        var toggle;

        if (isHeader) {
            toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'nn-header-weather-btn';
            toggle.setAttribute('aria-label', weatherText('change', 'Change location'));
            toggle.title = weatherText('change', 'Change location');
            while (body.firstChild) {
                toggle.appendChild(body.firstChild);
            }
            body.appendChild(toggle);
        } else {
            toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'nn-weather-change';
            toggle.textContent = '📍 ' + weatherText('change', 'Change location');
            body.appendChild(toggle);
        }

        toggle.setAttribute('aria-expanded', 'false');
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            var open = picker.hidden;
            document.querySelectorAll('.nn-weather-picker').forEach(function(p) { p.hidden = true; });
            picker.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                var input = picker.querySelector('input');
                if (input) {
                    input.focus();
                }
            }
        });

        body.appendChild(picker);
    }

    function renderWeatherError(body, isHeader, message) {
        body.innerHTML = isHeader
            ? '<span class="nn-header-weather-error">' + weatherEsc(message) + '</span>'
            : '<p class="nn-weather-error">' + weatherEsc(message) + '</p>';
    }

    function loadWeatherTarget(el, forcedLoc) {
        if (!el || typeof neonewsData === 'undefined') {
            return;
        }

        var isHeader = el.id === 'nn-weather-header';
        var body = isHeader ? el : el.querySelector('.nn-weather-body');
        if (!body) {
            return;
        }

        var loc = forcedLoc || resolveWeatherLoc(el);

        var formData = new FormData();
        formData.append('action', 'neonews_weather');
        formData.append('nonce', neonewsData.nonce);
        formData.append('city', loc.city || '');
        formData.append('lat', loc.lat || '');
        formData.append('lon', loc.lon || '');

        fetch(neonewsData.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.success) {
                // Nonce expired (e.g. cached page) — reload once to get fresh nonce.
                if (res.data && res.data.reload) {
                    window.location.reload();
                    return;
                }
                if (loc.mode === 'tz') {
                    loadWeatherTarget(el, getDefaultWeatherLoc(el));
                    return;
                }
                renderWeatherError(body, isHeader, (res.data && res.data.message) ? res.data.message : neonewsData.i18n.error);
                attachWeatherPicker(el, body, isHeader);
                return;
            }
            var d = res.data;
            var country = d.country || loc.country || '';
            if (isHeader) {
                body.innerHTML =
                    '<span class="nn-header-weather-icon" aria-hidden="true">' + weatherEsc(d.icon) + '</span>' +
                    '<span class="nn-header-weather-temp">' + weatherEsc(d.temp + d.unit) + '</span>' +
                    '<span class="nn-header-weather-city">' + weatherEsc(d.city) + '</span>' +
                    '<span class="nn-header-weather-label">' + weatherEsc(d.label) + '</span>';
            } else {
                body.innerHTML =
                    '<div class="nn-weather-main">' +
                        '<span class="nn-weather-icon">' + weatherEsc(d.icon) + '</span>' +
                        '<div class="nn-weather-temp">' + weatherEsc(d.temp + d.unit) + '</div>' +
                    '</div>' +
                    '<div class="nn-weather-location">' + weatherEsc(d.city + (country ? ', ' + country : '')) + '</div>' +
                    '<div class="nn-weather-label">' + weatherEsc(d.label) + '</div>' +
                    '<div class="nn-weather-stats">' +
                        '<span>H ' + weatherEsc(d.high + d.unit) + '</span>' +
                        '<span>L ' + weatherEsc(d.low + d.unit) + '</span>' +
                        '<span>💧 ' + weatherEsc(d.humidity) + '%</span>' +
                        '<span>💨 ' + weatherEsc(d.wind) + ' km/h</span>' +
                    '</div>';
            }
            attachWeatherPicker(el, body, isHeader);
        })
        .catch(function() {
            renderWeatherError(body, isHeader, neonewsData.i18n.error);
        });
    }

    function initWeather() {
        var targets = getWeatherTargets();

        if (!targets.length) {
            return;
        }

        document.addEventListener('click', function() {
            document.querySelectorAll('.nn-weather-picker').forEach(function(p) { p.hidden = true; });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.nn-weather-picker').forEach(function(p) { p.hidden = true; });
            }
        });

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
