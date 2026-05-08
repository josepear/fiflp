/**
 * EDITORIAL JS
 * - Lightbox
 * - Animaciones
 */

document.addEventListener("DOMContentLoaded", function () {
    const centenarioLogo = document.querySelector('.fiflp-centenario-logo');

    if (centenarioLogo) {
        const updateCentenarioLogoState = () => {
            const progress = Math.min(window.scrollY, 180);
            const opacity = Math.max(0.06, 1 - (progress / 180));
            const translateY = -50 - Math.min(progress / 20, 8);

            centenarioLogo.style.opacity = opacity.toFixed(3);
            centenarioLogo.style.transform = 'translate(-50%, ' + translateY.toFixed(2) + '%)';
            centenarioLogo.classList.toggle('is-fading', progress > 96);
        };

        updateCentenarioLogoState();
        window.addEventListener('scroll', updateCentenarioLogoState, { passive: true });
        window.addEventListener('resize', updateCentenarioLogoState);
    }

    const fitRotuloText = () => {
        document.querySelectorAll('.rotulo-editorial__texto').forEach(function (text) {
            const franja = text.closest('.rotulo-editorial__franja');
            const rotulo = text.closest('.rotulo-editorial');
            const bloque = text.closest('.rotulo-editorial-bloque');

            if (!franja) {
                return;
            }

            const computedText = window.getComputedStyle(text);
            const computedFranja = window.getComputedStyle(franja);
            const baseFontSize = parseFloat(text.dataset.baseFontSize || computedText.fontSize);
            const paddingLeft = parseFloat(computedFranja.paddingLeft) || 0;
            const paddingRight = parseFloat(computedFranja.paddingRight) || 0;
            const slantFactor = franja.classList.contains('rotulo-editorial__franja--superior') ? 1.22 : 1.08;
            const slantAllowance = Math.ceil(franja.offsetHeight * slantFactor);
            const maxTrackWidth = Math.max(
                0,
                (bloque ? bloque.clientWidth : 0) ||
                (rotulo ? rotulo.parentElement.clientWidth : 0) ||
                Math.floor(window.innerWidth * 0.92)
            );

            if (!baseFontSize || !maxTrackWidth) {
                return;
            }

            text.dataset.baseFontSize = String(baseFontSize);
            text.style.fontSize = baseFontSize + 'px';
            franja.style.width = '';

            const desiredWidth = Math.min(
                maxTrackWidth,
                Math.ceil(text.scrollWidth + paddingLeft + paddingRight + slantAllowance)
            );

            if (desiredWidth > franja.clientWidth) {
                franja.style.width = desiredWidth + 'px';
            }

            const availableWidth = Math.max(0, franja.clientWidth - paddingLeft - paddingRight - slantAllowance);

            const currentWidth = text.scrollWidth;

            if (!currentWidth || currentWidth <= availableWidth) {
                return;
            }

            const ratio = availableWidth / currentWidth;
            const minFontSize = Math.max(18, baseFontSize * 0.42);
            const fittedFontSize = Math.max(minFontSize, Math.floor(baseFontSize * ratio * 100) / 100);

            text.style.fontSize = fittedFontSize + 'px';

            if (text.scrollWidth > availableWidth && fittedFontSize > minFontSize) {
                let trialSize = fittedFontSize;

                while (text.scrollWidth > availableWidth && trialSize > minFontSize) {
                    trialSize -= 0.5;
                    text.style.fontSize = trialSize + 'px';
                }
            }
        });

        document.querySelectorAll('.rotulo-editorial').forEach(function (rotulo) {
            const principal = rotulo.querySelector('.rotulo-editorial__franja--principal');
            const subtitulo = rotulo.querySelector('.rotulo-editorial__subtitulo');
            const bloque = rotulo.closest('.rotulo-editorial-bloque');

            if (!principal || !subtitulo) {
                return;
            }

            let subtituloWidth = principal.offsetWidth;

            if (rotulo.classList.contains('rotulo-editorial--subtitulo-estrecho')) {
                subtituloWidth = Math.round(principal.offsetWidth * 0.72);
            } else if (rotulo.classList.contains('rotulo-editorial--subtitulo-ancho')) {
                subtituloWidth = Math.max(
                    0,
                    (bloque ? bloque.clientWidth : 0) ||
                    (rotulo.parentElement ? rotulo.parentElement.clientWidth : 0) ||
                    Math.floor(window.innerWidth * 0.92)
                );
            }

            subtitulo.style.width = subtituloWidth + 'px';
        });
    };

    const scheduleRotuloFit = (() => {
        let frame = null;

        return function () {
            if (frame) {
                window.cancelAnimationFrame(frame);
            }

            frame = window.requestAnimationFrame(function () {
                fitRotuloText();
                frame = null;
            });
        };
    })();

    const getDisclosureBody = (group) => {
        return Array.from(group.children).find(function (child) {
            return child.classList.contains('children') || child.classList.contains('fiflp-global-index__children');
        });
    };

    const updateToggleLabel = (toggle, isOpen) => {
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

        const icon = toggle.querySelector('span');

        if (icon) {
            icon.textContent = isOpen ? '−' : '+';
        }
    };

    const setDisclosureState = (group, body, isOpen, immediate) => {
        const toggle = group.querySelector('[data-disclosure-toggle]');

        if (!body) {
            return;
        }

        if (toggle) {
            updateToggleLabel(toggle, isOpen);
        }

        if (immediate) {
            group.classList.toggle('is-open', isOpen);
            body.hidden = !isOpen;
            body.style.opacity = isOpen ? '1' : '0';
            body.style.height = isOpen ? 'auto' : '0px';
            body.style.transform = isOpen ? 'translateY(0)' : 'translateY(-6px)';
            return;
        }

        const finishOpen = function () {
            body.style.height = 'auto';
            body.removeEventListener('transitionend', finishOpen);
        };

        const finishClose = function (event) {
            if (event.propertyName !== 'height') {
                return;
            }

            body.hidden = true;
            body.removeEventListener('transitionend', finishClose);
        };

        if (isOpen) {
            body.hidden = false;
            body.style.height = '0px';
            body.style.opacity = '0';
            body.style.transform = 'translateY(-6px)';
            group.classList.add('is-open');
            body.offsetHeight;
            body.addEventListener('transitionend', finishOpen);

            requestAnimationFrame(function () {
                body.style.height = body.scrollHeight + 'px';
                body.style.opacity = '1';
                body.style.transform = 'translateY(0)';
            });

            return;
        }

        body.hidden = false;
        body.style.height = body.scrollHeight + 'px';
        body.style.opacity = '1';
        body.style.transform = 'translateY(0)';
        body.offsetHeight;
        group.classList.remove('is-open');
        body.addEventListener('transitionend', finishClose);

        requestAnimationFrame(function () {
            body.style.height = '0px';
            body.style.opacity = '0';
            body.style.transform = 'translateY(-6px)';
        });
    };

    document.querySelectorAll('.menu-lateral-grupo, .fiflp-global-index__group').forEach(function (group) {
        const body = getDisclosureBody(group);

        if (body) {
            setDisclosureState(group, body, group.classList.contains('is-open'), true);
        }
    });

    const disclosureToggles = document.querySelectorAll('[data-disclosure-toggle]');

    disclosureToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const group = toggle.closest('.menu-lateral-grupo, .fiflp-global-index__group');

            if (!group) {
                return;
            }

            const body = getDisclosureBody(group);
            const isOpen = !group.classList.contains('is-open');

            setDisclosureState(group, body, isOpen, false);
        });
    });

    document.querySelectorAll('.menu-lateral-summary > a, .fiflp-global-index__summary > a').forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
                return;
            }

            const group = link.closest('.menu-lateral-grupo, .fiflp-global-index__group');

            if (!group) {
                return;
            }

            const body = getDisclosureBody(group);

            if (!body) {
                return;
            }

            event.preventDefault();
            setDisclosureState(group, body, !group.classList.contains('is-open'), false);
        });
    });

    /* Solo menu-lateral: onepage usa data-onepage-sidebar-* y no debe compartir este comportamiento. */
    document.querySelectorAll('.menu-lateral[data-mobile-nav]').forEach(function (nav) {
        if (document.body.classList.contains('fiflp-onepage')) {
            return;
        }

        const toggle = nav.querySelector('[data-mobile-nav-toggle]');
        const panel = nav.querySelector('[data-mobile-nav-panel]');
        const icon = nav.querySelector('.menu-lateral-mobile-toggle__icon');
        const mobileQuery = window.matchMedia('(max-width: 1024px)');

        if (!toggle || !panel) {
            return;
        }

        const setMobileNavState = function (isOpen, immediate) {
            nav.classList.toggle('is-mobile-open', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            if (icon) {
                icon.textContent = isOpen ? '−' : '+';
            }

            if (!mobileQuery.matches) {
                panel.hidden = false;
                panel.style.height = 'auto';
                panel.style.opacity = '1';
                panel.style.transform = 'translateY(0)';
                return;
            }

            if (immediate) {
                panel.hidden = !isOpen;
                panel.style.height = isOpen ? 'auto' : '0px';
                panel.style.opacity = isOpen ? '1' : '0';
                panel.style.transform = isOpen ? 'translateY(0)' : 'translateY(-8px)';
                return;
            }

            const finishOpen = function (event) {
                if (event.propertyName !== 'height') {
                    return;
                }

                panel.style.height = 'auto';
                panel.removeEventListener('transitionend', finishOpen);
            };

            const finishClose = function (event) {
                if (event.propertyName !== 'height') {
                    return;
                }

                panel.hidden = true;
                panel.removeEventListener('transitionend', finishClose);
            };

            if (isOpen) {
                panel.hidden = false;
                panel.style.height = '0px';
                panel.style.opacity = '0';
                panel.style.transform = 'translateY(-8px)';
                panel.offsetHeight;
                panel.addEventListener('transitionend', finishOpen);

                requestAnimationFrame(function () {
                    panel.style.height = panel.scrollHeight + 'px';
                    panel.style.opacity = '1';
                    panel.style.transform = 'translateY(0)';
                });

                return;
            }

            panel.hidden = false;
            panel.style.height = panel.scrollHeight + 'px';
            panel.style.opacity = '1';
            panel.style.transform = 'translateY(0)';
            panel.offsetHeight;
            panel.addEventListener('transitionend', finishClose);

            requestAnimationFrame(function () {
                panel.style.height = '0px';
                panel.style.opacity = '0';
                panel.style.transform = 'translateY(-8px)';
            });
        };

        const syncMobileNav = function () {
            if (mobileQuery.matches) {
                setMobileNavState(nav.classList.contains('is-mobile-open'), true);
                return;
            }

            nav.classList.add('is-mobile-open');
            setMobileNavState(true, true);
        };

        if (mobileQuery.matches) {
            nav.classList.remove('is-mobile-open');
        } else {
            nav.classList.add('is-mobile-open');
        }

        syncMobileNav();

        toggle.addEventListener('click', function () {
            if (!mobileQuery.matches) {
                return;
            }

            setMobileNavState(!nav.classList.contains('is-mobile-open'), false);
        });

        if (typeof mobileQuery.addEventListener === 'function') {
            mobileQuery.addEventListener('change', syncMobileNav);
        } else if (typeof mobileQuery.addListener === 'function') {
            mobileQuery.addListener(syncMobileNav);
        }
    });

    const menuPanel = document.querySelector('[data-fiflp-menu-panel]');
    const menuToggle = document.querySelector('[data-fiflp-menu-toggle]');
    const menuCloseButtons = document.querySelectorAll('[data-fiflp-menu-close]');

    if (menuPanel && menuToggle) {
        const openMenu = () => {
            menuPanel.classList.add('is-open');
            menuPanel.setAttribute('aria-hidden', 'false');
            menuToggle.setAttribute('aria-expanded', 'true');
            document.body.classList.add('fiflp-menu-open');
        };

        const closeMenu = () => {
            menuPanel.classList.remove('is-open');
            menuPanel.setAttribute('aria-hidden', 'true');
            menuToggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('fiflp-menu-open');
        };

        menuToggle.addEventListener('click', function () {
            if (menuPanel.classList.contains('is-open')) {
                closeMenu();
                return;
            }

            openMenu();
        });

        menuCloseButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                closeMenu();
            });
        });

        menuPanel.querySelectorAll('.fiflp-global-index__link').forEach(function (link) {
            link.addEventListener('click', function (event) {
                if (event.defaultPrevented) {
                    return;
                }

                closeMenu();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && menuPanel.classList.contains('is-open')) {
                closeMenu();
            }
        });
    }

    const homeHero = document.querySelector('[data-editorial-hero]');
    const homeHeroContent = document.querySelector('[data-editorial-hero-content]');

    if (homeHero) {
        const activateHero = () => {
            homeHero.classList.add('is-ready');

            window.setTimeout(() => {
                homeHero.classList.add('is-content-ready');
            }, 900);
        };

        if (document.readyState === 'complete') {
            requestAnimationFrame(activateHero);
        } else {
            window.addEventListener('load', activateHero, { once: true });
            requestAnimationFrame(activateHero);
        }

        if (homeHeroContent && window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
            window.addEventListener('mousemove', function (event) {
                const x = (event.clientX / window.innerWidth) - 0.5;
                const y = (event.clientY / window.innerHeight) - 0.5;

                homeHero.style.setProperty('--hero-pan-x', `${x * 18}px`);
                homeHero.style.setProperty('--hero-pan-y', `${y * 12}px`);
                homeHeroContent.style.setProperty('--hero-content-x', `${x * -10}px`);
                homeHeroContent.style.setProperty('--hero-content-y', `${y * -8}px`);
            }, { passive: true });
        }
    }

    const initOnepageLayoutNav = () => {
        const layout = document.querySelector('[data-onepage-layout]');

        if (!layout) {
            return;
        }

        const toggle = document.querySelector('[data-onepage-sidebar-toggle]');
        const overlay = document.querySelector('[data-onepage-sidebar-overlay]');
        const navLinks = document.querySelectorAll('[data-onepage-nav-link]');
        const mqCompact = window.matchMedia('(max-width: 1024px)');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!toggle) {
            return;
        }

        const storageKey = 'fiflpOnepageMenu';

        const isCompact = () => mqCompact.matches;

        const readDesktopPreference = () => {
            try {
                return sessionStorage.getItem(storageKey);
            } catch (err) {
                return null;
            }
        };

        const persistDesktop = (open) => {
            if (isCompact()) {
                return;
            }

            try {
                sessionStorage.setItem(storageKey, open ? 'open' : 'closed');
            } catch (err) {
                /* ignore */
            }
        };

        const applyState = (open) => {
            document.body.classList.toggle('onepage-menu-open', open);
            document.body.classList.toggle('onepage-menu-closed', !open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            persistDesktop(open);
        };

        const initialOpen = () => {
            if (isCompact()) {
                return false;
            }

            const stored = readDesktopPreference();

            if (stored === 'closed') {
                return false;
            }

            if (stored === 'open') {
                return true;
            }

            return true;
        };

        let lastCompact = isCompact();

        applyState(initialOpen());

        toggle.addEventListener('click', () => {
            const next = !document.body.classList.contains('onepage-menu-open');
            applyState(next);
        });

        if (overlay) {
            overlay.addEventListener('click', () => {
                if (isCompact()) {
                    applyState(false);
                }
            });
        }

        navLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const href = link.getAttribute('href') || '';

                if (href.charAt(0) !== '#') {
                    return;
                }

                const id = href.slice(1);
                const target = document.getElementById(id);

                if (!target) {
                    return;
                }

                event.preventDefault();
                target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });

                if (window.history && window.history.pushState) {
                    window.history.pushState(null, '', href);
                }

                if (isCompact()) {
                    applyState(false);
                }
            });
        });

        const sections = document.querySelectorAll('section.seccion-onepage[id^="fiflp-onepage-row-"]');

        if (sections.length && navLinks.length && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                (entries) => {
                    const visible = entries
                        .filter((entry) => entry.isIntersecting)
                        .sort((a, b) => b.intersectionRatio - a.intersectionRatio);
                    const winner = visible[0];

                    if (!winner || !winner.target.id) {
                        return;
                    }

                    const hash = '#' + winner.target.id;

                    navLinks.forEach((a) => {
                        const on = a.getAttribute('href') === hash;
                        a.classList.toggle('is-active', on);

                        if (on) {
                            a.setAttribute('aria-current', 'true');
                        } else {
                            a.removeAttribute('aria-current');
                        }
                    });
                },
                {
                    rootMargin: '-38% 0px -42% 0px',
                    threshold: [0, 0.12, 0.25, 0.4, 0.55, 0.75, 1],
                }
            );

            sections.forEach((section) => observer.observe(section));
        }

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (!document.body.classList.contains('onepage-menu-open')) {
                return;
            }

            if (isCompact()) {
                applyState(false);
            }
        });

        window.addEventListener(
            'resize',
            () => {
                const compact = isCompact();

                if (compact === lastCompact) {
                    return;
                }

                lastCompact = compact;

                if (compact) {
                    applyState(false);
                } else {
                    const stored = readDesktopPreference();
                    applyState(stored !== 'closed');
                }
            },
            { passive: true }
        );
    };

    const initOnepageNarrative = () => {
        const shells = document.querySelectorAll('[data-onepage-shell]');

        if (!shells.length) {
            return;
        }

        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        shells.forEach((shell) => {
            if (shell.dataset.onepageNarrativeInit === '1') {
                if (typeof shell._fiflpSyncNumberState === 'function') {
                    shell._fiflpSyncNumberState();
                }
                return;
            }

            const items = Array.from(shell.querySelectorAll('[data-onepage-item]'));
            const moduleItems = Array.from(shell.querySelectorAll('.seccion-onepage__modulo'));
            const narrativeItems = items.length ? items : moduleItems;

            const photos = Array.from(shell.querySelectorAll('[data-onepage-photo]'));

            const setActiveItem = (target) => {
                narrativeItems.forEach((item) => {
                    item.classList.toggle('is-active', item === target);
                });

                const photoIndex = target ? target.getAttribute('data-onepage-photo-index') : '';
                if (!photoIndex) {
                    return;
                }

                photos.forEach((photo) => {
                    photo.classList.toggle('is-active', photo.getAttribute('data-onepage-photo') === photoIndex);
                });
            };

            const syncNumberState = () => {
                const rect = shell.getBoundingClientRect();
                const totalTrack = Math.max(window.innerHeight + rect.height, 1);
                const progress = Math.max(0, Math.min(1, (window.innerHeight - rect.top) / totalTrack));
                const morphStart = 0.00;
                const morphEndSetting = Number.parseFloat(shell.getAttribute('data-onepage-morph-end') || '0.10');
                const morphEnd = Math.max(0.02, Math.min(0.4, Number.isFinite(morphEndSetting) ? morphEndSetting : 0.10));
                const revealStart = 0.00;
                const revealEnd = 1.00;
                const revealTrackPx = Math.max(window.innerHeight * 0.42, 220);
                const revealRaw = (window.innerHeight - rect.top) / revealTrackPx;
                const morphProgress = Math.max(0, Math.min(1, (progress - morphStart) / Math.max(morphEnd - morphStart, 0.001)));
                const revealProgress = Math.max(0, Math.min(1, (revealRaw - revealStart) / Math.max(revealEnd - revealStart, 0.001)));

                shell.style.setProperty('--onepage-morph-progress', morphProgress.toFixed(3));
                shell.style.setProperty('--onepage-reveal-progress', revealProgress.toFixed(3));
                shell.classList.add('is-title-visible');
                shell.classList.toggle('is-content-visible', revealProgress > 0.01);
            };
            shell._fiflpSyncNumberState = syncNumberState;

            const firstItem = narrativeItems[0];
            if (firstItem) {
                setActiveItem(firstItem);
            }

            if (isMobile || reduceMotion) {
                shell.classList.remove('seccion-onepage--js');
                shell.classList.add('is-title-visible');
                shell.classList.add('is-content-visible');
                shell.style.setProperty('--onepage-morph-progress', '1');
                shell.style.setProperty('--onepage-reveal-progress', '1');
                return;
            }

            shell.classList.add('seccion-onepage--js');
            shell.dataset.onepageNarrativeInit = '1';
            shell.style.setProperty('--onepage-reveal-progress', '0');
            shell.style.setProperty('--onepage-morph-progress', '0');
            syncNumberState();
            window.addEventListener('scroll', syncNumberState, { passive: true });
            window.addEventListener('resize', syncNumberState);

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    let bestEntry = null;
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }
                        if (!bestEntry || entry.intersectionRatio > bestEntry.intersectionRatio) {
                            bestEntry = entry;
                        }
                    });

                    if (bestEntry && bestEntry.target) {
                        setActiveItem(bestEntry.target);
                    }
                }, {
                    threshold: [0.35, 0.55, 0.75],
                    rootMargin: '-18% 0px -18% 0px'
                });

                narrativeItems.forEach((item) => observer.observe(item));
                return;
            }

            const onScroll = () => {
                let candidate = null;
                let distance = Number.POSITIVE_INFINITY;

                narrativeItems.forEach((item) => {
                    const rect = item.getBoundingClientRect();
                    const d = Math.abs((rect.top + rect.height * 0.5) - window.innerHeight * 0.5);
                    if (d < distance) {
                        distance = d;
                        candidate = item;
                    }
                });

                if (candidate) {
                    setActiveItem(candidate);
                }
            };

            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        });
    };


    scheduleRotuloFit();

    if (document.fonts && typeof document.fonts.ready === 'object') {
        document.fonts.ready.then(scheduleRotuloFit).catch(function () {});
    }

    window.addEventListener('resize', scheduleRotuloFit, { passive: true });
    initOnepageLayoutNav();
    initOnepageNarrative();
    window.addEventListener('load', initOnepageNarrative, { once: true });
    window.addEventListener('pageshow', initOnepageNarrative);
    requestAnimationFrame(() => requestAnimationFrame(initOnepageNarrative));
    window.setTimeout(initOnepageNarrative, 180);

    // =========================
    // LIGHTBOX
    // =========================
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.querySelector('.lightbox-img');
    const lightboxCaption = document.querySelector('.lightbox-caption');
    const lightboxClose = document.querySelector('.lightbox-close');

    if (lightbox && lightboxImg && lightboxClose) {
        const openLightbox = (src, alt = '') => {
            if (!src) {
                return;
            }

            lightboxImg.src = src;
            lightboxImg.alt = alt;
            if (lightboxCaption) {
                lightboxCaption.textContent = alt;
            }
            lightbox.style.display = 'flex';
            lightbox.setAttribute('aria-hidden', 'false');
        };

        const closeLightbox = () => {
            lightbox.style.display = 'none';
            lightbox.setAttribute('aria-hidden', 'true');
            lightboxImg.setAttribute('src', '');
            lightboxImg.setAttribute('alt', '');
            if (lightboxCaption) {
                lightboxCaption.textContent = '';
            }
        };

        document.addEventListener('click', function(e) {
            const link = e.target.closest('.lightbox-trigger');

            if (!link) {
                return;
            }

            const src = link.getAttribute('href');
            const caption = link.getAttribute('data-caption') || '';

            e.preventDefault();
            openLightbox(src, caption);
        });

        lightboxClose.addEventListener('click', function() {
            closeLightbox();
        });

        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.style.display === 'flex') {
                closeLightbox();
            }
        });

    }

    // =========================
    // ANIMACIÓN BLOQUES
    // =========================
    const bloques = document.querySelectorAll('.fade-in');

    if (bloques.length) {
        const applyDelay = (bloque, index) => {
            bloque.style.transitionDelay = (index * 0.08) + 's';
        };

        const mostrarBloque = (bloque) => {
            bloque.classList.add('visible');
        };

        const bloqueEnViewport = (bloque) => {
            const rect = bloque.getBoundingClientRect();

            return rect.top < window.innerHeight && rect.bottom > 0;
        };

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        mostrarBloque(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });

            bloques.forEach((bloque, index) => {
                applyDelay(bloque, index);

                if (bloqueEnViewport(bloque)) {
                    mostrarBloque(bloque);
                    return;
                }

                observer.observe(bloque);
            });
        } else {
            bloques.forEach((bloque, index) => {
                mostrarBloque(bloque);
                applyDelay(bloque, index);
            });
        }

    }
});
