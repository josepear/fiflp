/**
 * EDITORIAL JS
 * - Lightbox
 * - Animaciones
 */

document.addEventListener("DOMContentLoaded", function () {
    const centenarioLogo = document.querySelector('.fiflp-centenario-logo');

    if (centenarioLogo) {
        const mobileLogoQuery = window.matchMedia('(max-width: 782px)');

        const updateCentenarioLogoState = () => {
            if (mobileLogoQuery.matches) {
                centenarioLogo.style.opacity = '';
                centenarioLogo.style.transform = '';
                centenarioLogo.classList.remove('is-fading');
                return;
            }

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

        if (typeof mobileLogoQuery.addEventListener === 'function') {
            mobileLogoQuery.addEventListener('change', updateCentenarioLogoState);
        } else if (typeof mobileLogoQuery.addListener === 'function') {
            mobileLogoQuery.addListener(updateCentenarioLogoState);
        }
    }

    const fitRotuloText = () => {
        document.querySelectorAll('.rotulo-editorial__texto').forEach(function (text) {
            const franja = text.closest('.rotulo-editorial__franja');
            const rotulo = text.closest('.rotulo-editorial');
            const bloque = text.closest('.rotulo-editorial-bloque');

            if (!franja) {
                return;
            }

            const computedFranja = window.getComputedStyle(franja);
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

            if (!maxTrackWidth) {
                return;
            }

            text.style.fontSize = '';
            franja.style.width = '';

            const desiredWidth = Math.min(
                maxTrackWidth,
                Math.ceil(text.scrollWidth + paddingLeft + paddingRight + slantAllowance)
            );

            if (desiredWidth > franja.clientWidth) {
                franja.style.width = desiredWidth + 'px';
            }

        });

        document.querySelectorAll('.rotulo-editorial').forEach(function (rotulo) {
            const cabecera = rotulo.querySelector('.rotulo-editorial__cabecera');
            const subtitulo = rotulo.querySelector('.rotulo-editorial__subtitulo');
            const bloque = rotulo.closest('.rotulo-editorial-bloque');
            const maxTrackWidth = Math.max(
                0,
                (bloque ? bloque.clientWidth : 0) ||
                (rotulo.parentElement ? rotulo.parentElement.clientWidth : 0) ||
                Math.floor(window.innerWidth * 0.92)
            );

            if (cabecera && maxTrackWidth > 0) {
                cabecera.style.transform = '';

                const naturalWidth = cabecera.scrollWidth;
                if (naturalWidth > 0) {
                    const scale = Math.min(1, maxTrackWidth / naturalWidth);
                    cabecera.style.transform = 'scale(' + scale.toFixed(4) + ')';
                }
            }

            if (!subtitulo || maxTrackWidth <= 0) {
                return;
            }

            // Subtítulo/sumario independiente del tamaño del SVG.
            subtitulo.style.width = '';
            subtitulo.style.maxWidth = '';

            if (rotulo.classList.contains('rotulo-editorial--subtitulo-estrecho')) {
                subtitulo.style.width = Math.round(maxTrackWidth * 0.72) + 'px';
            } else if (rotulo.classList.contains('rotulo-editorial--subtitulo-ancho')) {
                subtitulo.style.width = Math.round(maxTrackWidth) + 'px';
            } else {
                subtitulo.style.maxWidth = Math.round(maxTrackWidth) + 'px';
            }
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

        const setMobileNavState = function (isOpen) {
            nav.classList.toggle('is-mobile-open', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            if (icon) {
                icon.textContent = isOpen ? '−' : '+';
            }

            if (!mobileQuery.matches) {
                document.body.classList.remove('fiflp-editorial-nav-open');
                panel.hidden = false;
                panel.style.height = '';
                panel.style.opacity = '';
                panel.style.transform = '';
                return;
            }

            /* Móvil: overlay a pantalla completa (CSS); sin animación de altura. */
            document.body.classList.toggle('fiflp-editorial-nav-open', isOpen);
            panel.style.height = '';
            panel.style.opacity = '';
            panel.style.transform = '';
            panel.hidden = !isOpen;
        };

        const syncMobileNav = function () {
            if (mobileQuery.matches) {
                setMobileNavState(nav.classList.contains('is-mobile-open'));
                return;
            }

            nav.classList.add('is-mobile-open');
            setMobileNavState(true);
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

            setMobileNavState(!nav.classList.contains('is-mobile-open'));
        });

        panel.addEventListener('click', function (event) {
            if (!mobileQuery.matches) {
                return;
            }

            if (event.defaultPrevented) {
                return;
            }

            const link = event.target.closest('a[href]');

            if (!link) {
                return;
            }

            setMobileNavState(false);
        });

        if (typeof mobileQuery.addEventListener === 'function') {
            mobileQuery.addEventListener('change', syncMobileNav);
        } else if (typeof mobileQuery.addListener === 'function') {
            mobileQuery.addListener(syncMobileNav);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape' || event.defaultPrevented) {
            return;
        }

        if (!window.matchMedia('(max-width: 1024px)').matches) {
            return;
        }

        document.querySelectorAll('.menu-lateral[data-mobile-nav].is-mobile-open').forEach(function (nav) {
            const toggleBtn = nav.querySelector('[data-mobile-nav-toggle]');

            if (toggleBtn) {
                toggleBtn.click();
            }
        });
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

        const panel = document.querySelector('[data-onepage-sidebar-panel]');
        let compactExitTimerId = null;
        let compactExitOnEnd = null;

        const clearCompactExitAnimation = () => {
            if (compactExitTimerId) {
                window.clearTimeout(compactExitTimerId);
                compactExitTimerId = null;
            }
            if (panel && compactExitOnEnd) {
                panel.removeEventListener('transitionend', compactExitOnEnd);
                compactExitOnEnd = null;
            }
        };

        const finishCompactMenuExitDown = () => {
            if (!document.body.classList.contains('onepage-menu-exit-down')) {
                return;
            }
            clearCompactExitAnimation();
            document.body.classList.add('onepage-menu-skip-panel-transition');
            document.body.classList.remove('onepage-menu-open', 'onepage-menu-exit-down');
            document.body.classList.add('onepage-menu-closed');
            toggle.setAttribute('aria-expanded', 'false');
            persistDesktop(false);
            window.requestAnimationFrame(() => {
                window.requestAnimationFrame(() => {
                    document.body.classList.remove('onepage-menu-skip-panel-transition');
                });
            });
        };

        const startCompactMenuExitDown = () => {
            if (document.body.classList.contains('onepage-menu-exit-down')) {
                return;
            }
            if (!panel) {
                clearCompactExitAnimation();
                document.body.classList.remove('onepage-menu-exit-down', 'onepage-menu-skip-panel-transition');
                document.body.classList.remove('onepage-menu-open');
                document.body.classList.add('onepage-menu-closed');
                toggle.setAttribute('aria-expanded', 'false');
                persistDesktop(false);
                return;
            }
            document.body.classList.add('onepage-menu-exit-down');
            compactExitOnEnd = (event) => {
                if (event.target !== panel || event.propertyName !== 'transform') {
                    return;
                }
                panel.removeEventListener('transitionend', compactExitOnEnd);
                compactExitOnEnd = null;
                if (compactExitTimerId) {
                    window.clearTimeout(compactExitTimerId);
                    compactExitTimerId = null;
                }
                finishCompactMenuExitDown();
            };
            panel.addEventListener('transitionend', compactExitOnEnd);
            compactExitTimerId = window.setTimeout(() => {
                compactExitTimerId = null;
                if (compactExitOnEnd) {
                    panel.removeEventListener('transitionend', compactExitOnEnd);
                    compactExitOnEnd = null;
                }
                finishCompactMenuExitDown();
            }, 650);
        };

        const applyState = (open) => {
            if (!open && document.body.classList.contains('onepage-menu-exit-down')) {
                return;
            }

            if (
                !open &&
                isCompact() &&
                !reduceMotion &&
                document.body.classList.contains('onepage-menu-open') &&
                !document.body.classList.contains('onepage-menu-exit-down')
            ) {
                startCompactMenuExitDown();
                return;
            }

            clearCompactExitAnimation();
            document.body.classList.remove('onepage-menu-exit-down', 'onepage-menu-skip-panel-transition');
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
            if (document.body.classList.contains('onepage-menu-exit-down')) {
                return;
            }
            const next = !document.body.classList.contains('onepage-menu-open');
            applyState(next);
        });

        if (overlay) {
            overlay.addEventListener('click', () => {
                if (document.body.classList.contains('onepage-menu-exit-down')) {
                    return;
                }
                if (isCompact()) {
                    applyState(false);
                }
            });
        }

        const scrollToOnepageAnchor = (targetEl, href) => {
            const run = () => {
                const scrollPaddingTop =
                    parseFloat(window.getComputedStyle(document.documentElement).scrollPaddingTop) || 0;
                const top =
                    targetEl.getBoundingClientRect().top + window.scrollY - scrollPaddingTop;

                window.scrollTo({
                    top: Math.max(0, top),
                    behavior: reduceMotion ? 'auto' : 'smooth',
                });

                if (window.history && window.history.pushState) {
                    window.history.pushState(null, '', href);
                }
            };

            if (!isCompact()) {
                run();
                return;
            }

            const menuBusy =
                document.body.classList.contains('onepage-menu-open') ||
                document.body.classList.contains('onepage-menu-exit-down');

            if (menuBusy && !document.body.classList.contains('onepage-menu-exit-down')) {
                applyState(false);
            }

            const settled = () =>
                !document.body.classList.contains('onepage-menu-open') &&
                !document.body.classList.contains('onepage-menu-exit-down');

            if (settled()) {
                run();
                return;
            }

            const intervalId = window.setInterval(() => {
                if (settled()) {
                    window.clearInterval(intervalId);
                    run();
                }
            }, 24);

            window.setTimeout(() => {
                window.clearInterval(intervalId);
                run();
            }, 720);
        };

        navLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const href = link.getAttribute('href') || '';

                if (href.charAt(0) !== '#') {
                    if (isCompact()) {
                        applyState(false);
                    }
                    return;
                }

                const id = href.slice(1);
                const target = document.getElementById(id);

                if (!target) {
                    if (isCompact()) {
                        applyState(false);
                    }
                    return;
                }

                event.preventDefault();
                scrollToOnepageAnchor(target, href);
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

            if (document.body.classList.contains('onepage-menu-exit-down')) {
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

                clearCompactExitAnimation();
                document.body.classList.remove('onepage-menu-exit-down', 'onepage-menu-skip-panel-transition');

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

        const mqMobile = window.matchMedia('(max-width: 768px)');
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
                const isMobile = mqMobile.matches;

                if (reduceMotion) {
                    shell.classList.remove('seccion-onepage--js');
                    shell.classList.remove('is-onepage-numero-sticky');
                    delete shell._fiflpOnepageMorphScrollY0;
                    delete shell._fiflpShellPadTop;
                    shell.style.removeProperty('--onepage-numero-sticky-top');
                    shell.classList.add('is-title-visible');
                    shell.classList.add('is-content-visible');
                    shell.style.setProperty('--onepage-morph-progress', '1');
                    shell.style.setProperty('--onepage-reveal-progress', '1');
                    shell.style.removeProperty('--onepage-title-opacity');
                    shell.style.setProperty('--onepage-mobile-title-phase', '1');
                    return;
                }

                /* Móvil onepage: tramo narrativo unificado (morph al centrar + scroll cuando hay número). */
                const minNarrativeTrack = window.innerHeight * 2.4;
                const totalTrack = Math.max(window.innerHeight + rect.height, minNarrativeTrack, 1);
                const progress = Math.max(0, Math.min(1, (window.innerHeight - rect.top) / totalTrack));
                const morphStart = 0.0;
                const morphEndDesktop = Number.parseFloat(shell.getAttribute('data-onepage-morph-end') || '0.10');
                const morphEndMobileRaw = shell.getAttribute('data-onepage-morph-end-mobile');
                const morphEndSetting = (() => {
                    if (
                        isMobile &&
                        morphEndMobileRaw !== null &&
                        morphEndMobileRaw !== '' &&
                        !Number.isNaN(Number.parseFloat(morphEndMobileRaw))
                    ) {
                        return Number.parseFloat(morphEndMobileRaw);
                    }
                    return morphEndDesktop;
                })();
                const morphEnd = Math.max(0.02, Math.min(1.2, Number.isFinite(morphEndSetting) ? morphEndSetting : 0.1));
                const revealStart = 0.0;
                const revealEnd = 1.0;
                const revealTrackPx = Math.max(window.innerHeight * 0.42, 220);
                const revealRaw = (window.innerHeight - rect.top) / revealTrackPx;
                const morphProgress = Math.max(
                    0,
                    Math.min(1, (progress - morphStart) / Math.max(morphEnd - morphStart, 0.001))
                );
                const revealProgressDesktop = Math.max(
                    0,
                    Math.min(1, (revealRaw - revealStart) / Math.max(revealEnd - revealStart, 0.001))
                );

                if (isMobile) {
                    shell.classList.add('seccion-onepage--js');

                    const firstContent =
                        shell.querySelector('.seccion-onepage__contenido [data-onepage-item]') ||
                        shell.querySelector('.seccion-onepage__contenido .seccion-onepage__modulo') ||
                        shell.querySelector('.seccion-onepage__contenido-wrap .seccion-onepage__contenido > *');

                    const numeroWrap = shell.querySelector('.seccion-onepage__numero-wrap');
                    let morphProgressMobile = morphProgress;
                    if (numeroWrap) {
                        const wrap = numeroWrap;
                        const vh = window.innerHeight;
                        const centerY = vh * 0.5;
                        const wr = wrap.getBoundingClientRect();
                        const size = wr.height || Math.min(window.innerWidth - 40, 400);
                        const stickyTopPx = centerY - size * 0.5;
                        const isFixed = shell.classList.contains('is-onepage-numero-sticky');

                        /*
                         * Móvil: position:fixed (más fiable que sticky, que overflow:clip en #page atrapa).
                         * Cuando fixed, getBoundingClientRect() devuelve la posición fijada, no la natural.
                         * Usamos el rect de la sección + padding almacenado para estimar la posición natural.
                         */
                        const shellPadTop = isFixed ? (shell._fiflpShellPadTop || 30) : 30;
                        const nc = isFixed
                            ? rect.top + shellPadTop + size * 0.5   // posición natural estimada
                            : wr.top + size * 0.5;                   // posición natural real

                        const shellBottom = rect.top + shell.offsetHeight;
                        const numBottomFixed = stickyTopPx + size;   // = 50vh + size/2

                        if (isFixed && (nc > centerY + 2 || shellBottom < numBottomFixed)) {
                            /* Desactivar: sección volvió atrás O el fondo de la sección pasó por debajo del número */
                            morphProgressMobile = 0;
                            shell.classList.remove('is-onepage-numero-sticky');
                            delete shell._fiflpOnepageMorphScrollY0;
                            delete shell._fiflpShellPadTop;
                            shell.style.removeProperty('--onepage-numero-sticky-top');
                        } else if (!isFixed && nc > centerY + 0.75) {
                            /* Número aún no ha llegado al centro */
                            morphProgressMobile = 0;
                        } else {
                            /* Activar o continuar morph */
                            if (!isFixed) {
                                shell._fiflpShellPadTop =
                                    parseFloat(window.getComputedStyle(shell).paddingTop) || 30;
                                shell.classList.add('is-onepage-numero-sticky');
                                shell.style.setProperty('--onepage-numero-sticky-top', `${stickyTopPx}px`);
                                shell._fiflpOnepageMorphScrollY0 = window.scrollY || window.pageYOffset || 0;
                                morphProgressMobile = 0;
                            } else {
                                if (typeof shell._fiflpOnepageMorphScrollY0 !== 'number') {
                                    shell._fiflpOnepageMorphScrollY0 = window.scrollY || window.pageYOffset || 0;
                                }
                                const sy = window.scrollY || window.pageYOffset || 0;
                                const morphSpan = Math.max(vh * 0.42, 280);
                                morphProgressMobile = Math.max(
                                    0,
                                    Math.min(1, (sy - shell._fiflpOnepageMorphScrollY0) / morphSpan)
                                );
                            }
                        }
                    } else {
                        shell.classList.remove('is-onepage-numero-sticky');
                        delete shell._fiflpOnepageMorphScrollY0;
                        delete shell._fiflpShellPadTop;
                        shell.style.removeProperty('--onepage-numero-sticky-top');
                    }

                    shell.style.setProperty('--onepage-morph-progress', morphProgressMobile.toFixed(3));

                    let revealProgress = revealProgressDesktop;

                    if (firstContent) {
                        const cr = firstContent.getBoundingClientRect();
                        const vh = window.innerHeight;
                        const rStart = vh * 0.8;
                        const rEnd = vh * 0.16;
                        const rSpan = Math.max(140, rStart - rEnd);
                        revealProgress = Math.max(0, Math.min(1, (rStart - cr.top) / rSpan));
                    } else {
                        const enter = Math.max(
                            0,
                            Math.min(1, (window.innerHeight * 0.62 - rect.top) / (window.innerHeight * 0.5))
                        );
                        revealProgress = enter > 0.52 ? 1 : enter;
                    }

                    const indiceEl = shell.querySelector('.seccion-onepage__indice');
                    let titlePhase = 0;
                    if (indiceEl) {
                        const topPx = indiceEl.getBoundingClientRect().top;
                        const band = Math.max(110, window.innerHeight * 0.2);
                        titlePhase = 1 - Math.max(0, Math.min(1, topPx / band));
                    }
                    shell.style.setProperty('--onepage-mobile-title-phase', titlePhase.toFixed(3));
                    shell.style.setProperty('--onepage-title-opacity', '1');

                    shell.style.setProperty('--onepage-reveal-progress', revealProgress.toFixed(3));
                    shell.classList.add('is-title-visible');
                    shell.classList.toggle('is-content-visible', revealProgress > 0.05);
                    return;
                }

                shell.classList.remove('is-onepage-numero-sticky');
                delete shell._fiflpShellPadTop;
                shell.style.removeProperty('--onepage-numero-sticky-top');
                shell.style.removeProperty('--onepage-title-opacity');
                shell.style.removeProperty('--onepage-mobile-title-phase');

                shell.style.setProperty('--onepage-morph-progress', morphProgress.toFixed(3));
                shell.style.setProperty('--onepage-reveal-progress', revealProgressDesktop.toFixed(3));
                shell.classList.add('is-title-visible');
                shell.classList.toggle('is-content-visible', revealProgressDesktop > 0.01);
            };
            shell._fiflpSyncNumberState = syncNumberState;

            const firstItem = narrativeItems[0];
            if (firstItem) {
                setActiveItem(firstItem);
            }

            if (reduceMotion) {
                shell.classList.remove('seccion-onepage--js');
                shell.classList.remove('is-onepage-numero-sticky');
                delete shell._fiflpOnepageMorphScrollY0;
                delete shell._fiflpShellPadTop;
                shell.style.removeProperty('--onepage-numero-sticky-top');
                shell.classList.add('is-title-visible');
                shell.classList.add('is-content-visible');
                shell.style.setProperty('--onepage-morph-progress', '1');
                shell.style.setProperty('--onepage-reveal-progress', '1');
                shell.style.removeProperty('--onepage-title-opacity');
                shell.style.setProperty('--onepage-mobile-title-phase', '1');
                shell.dataset.onepageNarrativeInit = '1';
                return;
            }

            shell.classList.add('seccion-onepage--js');
            shell.dataset.onepageNarrativeInit = '1';
            shell.style.setProperty('--onepage-reveal-progress', '0');
            shell.style.setProperty('--onepage-morph-progress', '0');
            shell.classList.remove('is-onepage-numero-sticky');
            delete shell._fiflpOnepageMorphScrollY0;
            delete shell._fiflpShellPadTop;
            shell.style.removeProperty('--onepage-numero-sticky-top');
            if (mqMobile.matches) {
                shell.style.setProperty('--onepage-title-opacity', '1');
                shell.style.setProperty('--onepage-mobile-title-phase', '0');
            } else {
                shell.style.removeProperty('--onepage-title-opacity');
                shell.style.removeProperty('--onepage-mobile-title-phase');
            }

            syncNumberState();
            window.addEventListener('scroll', syncNumberState, { passive: true });
            window.addEventListener('resize', syncNumberState);

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(
                    (entries) => {
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
                    },
                    {
                        threshold: [0.35, 0.55, 0.75],
                        rootMargin: '-18% 0px -18% 0px',
                    }
                );

                narrativeItems.forEach((item) => observer.observe(item));
                return;
            }

            const onScroll = () => {
                let candidate = null;
                let distance = Number.POSITIVE_INFINITY;

                narrativeItems.forEach((item) => {
                    const itemRect = item.getBoundingClientRect();
                    const d = Math.abs(itemRect.top + itemRect.height * 0.5 - window.innerHeight * 0.5);
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

    /**
     * Móvil ≤768px: cuadrado del número desde el ancho del viewport (no desde el padre estrecho del tema).
     * visualViewport cuando exista (iOS); inline !important gana sobre max-width heredados.
     */
    const initOnepageMobileNumeroSquare = () => {
        const mq = window.matchMedia('(max-width: 768px)');

        const viewportWidth = () => {
            if (window.visualViewport && typeof window.visualViewport.width === 'number') {
                return window.visualViewport.width;
            }
            return window.innerWidth;
        };

        const applySquareHeights = () => {
            document.querySelectorAll('.seccion-onepage__numero-wrap').forEach((wrap) => {
                if (!mq.matches) {
                    wrap.style.removeProperty('width');
                    wrap.style.removeProperty('height');
                    wrap.style.removeProperty('max-width');
                    return;
                }
                const vw = viewportWidth();
                const side = Math.round(Math.max(160, Math.min(400, vw - 40)));
                if (side > 0) {
                    wrap.style.setProperty('width', `${side}px`, 'important');
                    wrap.style.setProperty('height', `${side}px`, 'important');
                    wrap.style.setProperty('max-width', 'none', 'important');
                }
            });
        };

        const schedule = () => {
            window.requestAnimationFrame(applySquareHeights);
        };

        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', schedule);
        } else if (typeof mq.addListener === 'function') {
            mq.addListener(schedule);
        }

        window.addEventListener('resize', schedule, { passive: true });
        window.addEventListener('load', schedule, { once: true });
        window.addEventListener('pageshow', schedule);

        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', schedule, { passive: true });
        }

        if (typeof ResizeObserver !== 'undefined') {
            document.querySelectorAll('.seccion-onepage__numero-wrap').forEach((wrap) => {
                const ro = new ResizeObserver(schedule);
                ro.observe(wrap);
            });
        }

        if (document.fonts && typeof document.fonts.ready === 'object') {
            document.fonts.ready.then(schedule).catch(function () {});
        }

        schedule();
        window.setTimeout(schedule, 120);
        window.setTimeout(schedule, 400);
    };


    scheduleRotuloFit();

    if (document.fonts && typeof document.fonts.ready === 'object') {
        document.fonts.ready.then(scheduleRotuloFit).catch(function () {});
    }

    window.addEventListener('resize', scheduleRotuloFit, { passive: true });
    initOnepageMobileNumeroSquare();
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
    const lightboxViewport = lightbox ? lightbox.querySelector('.lightbox-viewport') : null;
    const lightboxImg = lightbox ? lightbox.querySelector('.lightbox-img') : null;
    const lightboxCaption = lightbox ? lightbox.querySelector('.lightbox-caption') : null;
    const lightboxClose = lightbox ? lightbox.querySelector('.lightbox-close') : null;
    const lightboxZoom = lightbox ? lightbox.querySelector('.lightbox-zoom') : null;

    if (lightbox && lightboxImg && lightboxClose) {
        const LIGHTBOX_ZOOM_FACTOR = 1.5;

        const centerLightboxViewport = () => {
            if (!lightboxViewport) {
                return;
            }
            const dx = lightboxViewport.scrollWidth - lightboxViewport.clientWidth;
            const dy = lightboxViewport.scrollHeight - lightboxViewport.clientHeight;
            lightboxViewport.scrollLeft = dx > 0 ? dx / 2 : 0;
            lightboxViewport.scrollTop = dy > 0 ? dy / 2 : 0;
        };

        const resetLightboxZoom = () => {
            lightbox.classList.remove('lightbox--zoomed');
            if (lightboxZoom) {
                lightboxZoom.setAttribute('aria-pressed', 'false');
                lightboxZoom.setAttribute('aria-label', 'Ampliar imagen');
                lightboxZoom.setAttribute('title', 'Ampliar');
            }
            lightboxImg.style.width = '';
            lightboxImg.style.height = '';
            lightboxImg.style.maxWidth = '';
            lightboxImg.style.maxHeight = '';
            if (lightboxViewport) {
                lightboxViewport.scrollLeft = 0;
                lightboxViewport.scrollTop = 0;
            }
        };

        const applyLightboxZoomSize = () => {
            if (!lightbox.classList.contains('lightbox--zoomed')) {
                return;
            }
            if (!(lightboxImg.naturalWidth > 0)) {
                return;
            }
            lightboxImg.style.maxWidth = 'none';
            lightboxImg.style.maxHeight = 'none';
            lightboxImg.style.width = Math.round(lightboxImg.naturalWidth * LIGHTBOX_ZOOM_FACTOR) + 'px';
            lightboxImg.style.height = Math.round(lightboxImg.naturalHeight * LIGHTBOX_ZOOM_FACTOR) + 'px';
            requestAnimationFrame(centerLightboxViewport);
        };

        const openLightbox = (src, alt = '') => {
            if (!src) {
                return;
            }

            resetLightboxZoom();
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
            resetLightboxZoom();
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

        if (lightboxZoom) {
            lightboxZoom.addEventListener('click', function(e) {
                e.stopPropagation();
                const next = !lightbox.classList.contains('lightbox--zoomed');
                lightbox.classList.toggle('lightbox--zoomed', next);
                lightboxZoom.setAttribute('aria-pressed', next ? 'true' : 'false');
                lightboxZoom.setAttribute('aria-label', next ? 'Reducir imagen' : 'Ampliar imagen');
                lightboxZoom.setAttribute('title', next ? 'Reducir' : 'Ampliar');
                if (!next) {
                    resetLightboxZoom();
                } else {
                    applyLightboxZoomSize();
                    if (!lightboxImg.complete || !(lightboxImg.naturalWidth > 0)) {
                        lightboxImg.addEventListener('load', applyLightboxZoomSize, { once: true });
                    }
                }
            });
        }

        lightboxImg.addEventListener('load', function() {
            if (lightbox.classList.contains('lightbox--zoomed')) {
                applyLightboxZoomSize();
            }
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
