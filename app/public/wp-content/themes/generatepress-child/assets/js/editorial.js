/**
 * EDITORIAL JS
 * - Lightbox
 * - Animaciones
 */

document.addEventListener("DOMContentLoaded", function () {
    const fixPrologoStructure = () => {
        const prologos = document.querySelectorAll('article.prologo');

        prologos.forEach((prologo) => {
            const hasImgContainer = prologo.querySelector(':scope > .prologo-img');
            const content = prologo.querySelector(':scope > .prologo-content');
            const textRoot = prologo.querySelector('.prologo-texto');
            const internalImg = textRoot ? textRoot.querySelector('img') : null;

            if (hasImgContainer || !content || !internalImg) {
                return;
            }

            const imgContainer = document.createElement('div');
            imgContainer.className = 'prologo-img';
            imgContainer.appendChild(internalImg);
            prologo.insertBefore(imgContainer, content);

            // Limpieza de saltos de línea sobrantes tras mover la imagen.
            if (textRoot) {
                textRoot.querySelectorAll('br').forEach((br) => br.remove());

                // Elimina párrafos vacíos que a veces quedan tras quitar la imagen.
                textRoot.querySelectorAll('p').forEach((p) => {
                    if (p.querySelector('img')) {
                        return;
                    }
                    if (p.textContent.trim() === '') {
                        p.remove();
                    }
                });
            }
        });
    };

    fixPrologoStructure();

    // Móvil: al recargar, volver al inicio de la página.
    (function forceTopOnMobileReload() {
        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        if (!isMobile) {
            return;
        }

        const navEntries = (typeof performance !== 'undefined' && performance.getEntriesByType)
            ? performance.getEntriesByType('navigation')
            : [];
        const navType = navEntries.length ? navEntries[0].type : '';
        const isReload = navType === 'reload';

        if (!isReload) {
            return;
        }

        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        window.scrollTo(0, 0);
        requestAnimationFrame(() => window.scrollTo(0, 0));
        window.setTimeout(() => window.scrollTo(0, 0), 120);
    })();

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

    const getRotuloMaxTrackWidth = function (bloque, rotulo) {
        return Math.max(
            0,
            (bloque ? bloque.clientWidth : 0) ||
            (rotulo && rotulo.parentElement ? rotulo.parentElement.clientWidth : 0) ||
            Math.floor(window.innerWidth * 0.92)
        );
    };

    const fitRotuloText = () => {
        const isMobile = window.matchMedia('(max-width: 767px)').matches;

        document.querySelectorAll('.rotulo-editorial__texto').forEach(function (text) {
            const franja = text.closest('.rotulo-editorial__franja');
            const rotulo = text.closest('.rotulo-editorial');
            const bloque = text.closest('.rotulo-editorial-bloque');
            const isContextPage = !!(rotulo && rotulo.classList.contains('rotulo-editorial--context-page'));

            if (!franja) {
                return;
            }

            // Unificación Página + Portada Hero:
            // en móvil, el rótulo context-page no se autoajusta por JS; manda CSS.
            if (isMobile && isContextPage) {
                text.style.fontSize = '';
                franja.style.width = '';
                return;
            }

            const computedFranja = window.getComputedStyle(franja);
            const paddingLeft = parseFloat(computedFranja.paddingLeft) || 0;
            const paddingRight = parseFloat(computedFranja.paddingRight) || 0;
            const slantFactor = franja.classList.contains('rotulo-editorial__franja--superior') ? 1.22 : 1.08;
            const slantAllowance = Math.ceil(franja.offsetHeight * slantFactor);
            const maxTrackWidth = getRotuloMaxTrackWidth(bloque, rotulo);

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
            const maxTrackWidth = getRotuloMaxTrackWidth(bloque, rotulo);
            const isContextPage = rotulo.classList.contains('rotulo-editorial--context-page');

            if (isMobile && isContextPage) {
                if (cabecera) {
                    cabecera.style.transform = '';
                }
                if (subtitulo) {
                    subtitulo.style.width = '';
                    subtitulo.style.maxWidth = '';
                }
                return;
            }

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

    const initPortadaHeroCinematicIntro = () => {
        const hero = document.querySelector('.portada-hero');
        const rotulo = hero ? hero.querySelector('.portada-hero__rotulo') : null;

        if (!hero || hero.dataset.introDone === '1') {
            return;
        }

        hero.dataset.introDone = '1';
        hero.classList.add('portada-hero--cinematic');

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const markItems = (selector, slot) => {
            hero.querySelectorAll(selector).forEach((el) => {
                el.classList.add('hero-intro-item');
                el.classList.add('hero-intro-slot-' + slot);
            });
        };

        // Secuencia pedida:
        // 0s: rótulo entra grande en su sitio (sin recolocación).
        // 2s: logo
        // 3s: subtítulo
        // 4s: botón principal
        // 5s: botones descarga + logos institucionales juntos
        markItems('.portada-hero__logo', 'logo');
        markItems('.portada-hero__subtitulo', 'subtitulo');
        markItems('.portada-hero__boton--central', 'btn-main');
        markItems('.portada-hero__acciones-sec .portada-hero__boton', 'btn-sec');
        markItems('.portada-hero .portada-hero-retaila', 'logos');

        // El rótulo parte centrado en pantalla y vuelve a su posición natural.
        if (rotulo) {
            const rect = rotulo.getBoundingClientRect();
            const targetX = rect.left + (rect.width / 2);
            const targetY = rect.top + (rect.height / 2);
            const centerX = window.innerWidth / 2;
            const centerY = window.innerHeight / 2;
            const fromX = centerX - targetX;
            const fromY = centerY - targetY;

            rotulo.style.setProperty('--ph-rotulo-from-x', fromX.toFixed(2) + 'px');
            rotulo.style.setProperty('--ph-rotulo-from-y', fromY.toFixed(2) + 'px');
        }

        // Móvil: sin intro cinemática para evitar pérdidas de render del rótulo/línea.
        if (window.matchMedia('(max-width: 767px)').matches) {
            hero.classList.add('is-ready');
            scheduleRotuloFit();
            return;
        }

        if (reduceMotion) {
            hero.classList.add('is-ready');
            return;
        }

        requestAnimationFrame(() => {
            hero.classList.add('is-animating');
            requestAnimationFrame(() => {
                hero.classList.add('is-ready');
            });
        });

        // Reajusta el rótulo tras su transición principal.
        window.setTimeout(scheduleRotuloFit, 3000);
    };

    const initPortadaHeroGalleryFade = () => {
        const hero = document.querySelector('.portada-hero');
        if (!hero) {
            return;
        }

        const sanitizeUrl = (url) => String(url || '').replace(/"/g, '\\"');
        const crossfadeMs = 1200;
        const FALLBACK_INTERVAL = 3000;
        const bgNodes = Array.from(hero.querySelectorAll('.portada-hero__bg'));

        const parseUrls = (bg) => {
            try {
                const parsed = JSON.parse(bg.dataset.gallery || '[]');
                if (!Array.isArray(parsed)) {
                    return [];
                }
                return parsed.filter((u) => typeof u === 'string' && u.trim() !== '');
            } catch (e) {
                return [];
            }
        };

        const isVisibleBg = (bg) => {
            if (bg.hasAttribute('hidden')) {
                return false;
            }
            const style = window.getComputedStyle(bg);
            return style.display !== 'none' && style.visibility !== 'hidden';
        };

        const preloadImage = (url) => new Promise((resolve) => {
            const img = new Image();
            img.onload = () => resolve(true);
            img.onerror = () => resolve(false);
            img.src = url;
        });

        let timerId = null;
        let activeBg = null;
        let activeUrls = [];
        let activeIndex = 0;
        let activeInterval = FALLBACK_INTERVAL;
        let transitioning = false;

        const clearTimer = () => {
            if (timerId) {
                window.clearTimeout(timerId);
                timerId = null;
            }
        };

        const selectActiveBg = () => {
            const visible = bgNodes.find((node) => isVisibleBg(node));
            if (!visible) {
                return null;
            }

            const autoplay = visible.dataset.galleryAutoplay === '1';
            const urls = parseUrls(visible);
            if (!autoplay || urls.length < 2) {
                return null;
            }

            const interval = Math.max(
                FALLBACK_INTERVAL,
                parseInt(visible.dataset.galleryInterval || String(FALLBACK_INTERVAL), 10) || FALLBACK_INTERVAL
            );

            let currentIndex = 0;
            const inlineImage = visible.style.backgroundImage || '';
            urls.forEach((url, i) => {
                if (inlineImage.indexOf(url) !== -1) {
                    currentIndex = i;
                }
            });

            return { bg: visible, urls, interval, currentIndex };
        };

        const scheduleNext = () => {
            clearTimer();
            const waitMs = Math.max(80, activeInterval - crossfadeMs);
            timerId = window.setTimeout(runTick, waitMs);
        };

        const syncActiveContext = () => {
            const selected = selectActiveBg();
            if (!selected) {
                clearTimer();
                activeBg = null;
                activeUrls = [];
                transitioning = false;
                return;
            }

            if (activeBg !== selected.bg) {
                clearTimer();
                activeBg = selected.bg;
                activeUrls = selected.urls;
                activeInterval = selected.interval;
                activeIndex = selected.currentIndex;
                transitioning = false;
                scheduleNext();
                return;
            }

            activeUrls = selected.urls;
            activeInterval = selected.interval;
        };

        const runTick = async () => {
            if (!activeBg || document.hidden || transitioning) {
                scheduleNext();
                return;
            }

            const nextIndex = (activeIndex + 1) % activeUrls.length;
            const nextUrl = activeUrls[nextIndex];

            transitioning = true;
            const loaded = await preloadImage(nextUrl);
            if (!loaded || !activeBg) {
                transitioning = false;
                scheduleNext();
                return;
            }

            const layer = document.createElement('div');
            layer.className = 'portada-hero__bg-fade-layer';
            layer.style.backgroundImage = 'url("' + sanitizeUrl(nextUrl) + '")';
            layer.style.transitionDuration = crossfadeMs + 'ms';

            const parent = activeBg.parentElement || hero;
            parent.appendChild(layer);

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    layer.style.opacity = '1';
                });
            });

            window.setTimeout(() => {
                if (activeBg) {
                    activeBg.style.backgroundImage = 'url("' + sanitizeUrl(nextUrl) + '")';
                }
                layer.remove();
                activeIndex = nextIndex;
                transitioning = false;
                scheduleNext();
            }, crossfadeMs + 60);
        };

        syncActiveContext();
        window.addEventListener('resize', syncActiveContext, { passive: true });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                syncActiveContext();
            }
        });
    };

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
            const titleEl = shell.querySelector('.seccion-onepage__indice .seccion-onepage__titulo');

            // Devuelve el texto "base" del titular (sin puntos añadidos por la animación).
            const getBaseTitle = () => {
                if (!titleEl) {
                    return '';
                }
                if (!titleEl.dataset.fullTitle) {
                    titleEl.dataset.fullTitle = (titleEl.textContent || '').trim();
                }
                return titleEl.dataset.fullTitle;
            };

            // Encuentra cuántos caracteres del título caben en una línea con "..."
            const resolveDockedTitleChars = () => {
                if (!titleEl) {
                    return 0;
                }
                const full = getBaseTitle();
                if (!full) {
                    return 0;
                }
                const fullLen = full.length;
                const prevWhiteSpace = titleEl.style.whiteSpace;
                const prevOverflow = titleEl.style.overflow;
                const prevDisplay = titleEl.style.display;
                const prevWidth = titleEl.style.width;
                const prevTextOverflow = titleEl.style.textOverflow;

                // Medición en "modo una línea real" para calcular dónde cortar.
                titleEl.style.whiteSpace = 'nowrap';
                titleEl.style.overflow = 'hidden';
                titleEl.style.display = 'block';
                titleEl.style.width = '100%';
                titleEl.style.textOverflow = 'clip';

                let chars = fullLen;
                while (chars > 1) {
                    titleEl.textContent = `${full.slice(0, chars).trimEnd()}...`;
                    if (titleEl.scrollWidth <= titleEl.clientWidth + 1) {
                        break;
                    }
                    chars -= 1;
                }
                titleEl.textContent = full;
                titleEl.style.whiteSpace = prevWhiteSpace;
                titleEl.style.overflow = prevOverflow;
                titleEl.style.display = prevDisplay;
                titleEl.style.width = prevWidth;
                titleEl.style.textOverflow = prevTextOverflow;
                return Math.max(1, chars);
            };

            // "Máquina de escribir borrando" ligada al scroll extra tras anclarse arriba:
            // 0 = título completo, 1 = título truncado con "..."
            const applyTitleTypingByPhase = (phase) => {
                if (!titleEl) {
                    return;
                }
                const full = getBaseTitle();
                if (!full) {
                    return;
                }

                const clamped = Math.max(0, Math.min(1, phase));
                if (clamped <= 0.001) {
                    titleEl.textContent = full;
                    return;
                }

                if (typeof shell._fiflpDockedTitleChars !== 'number' || shell._fiflpDockedTitleChars <= 0) {
                    shell._fiflpDockedTitleChars = resolveDockedTitleChars();
                }

                const fullLen = full.length;
                const minLen = Math.max(1, shell._fiflpDockedTitleChars);
                const dynamicLen = Math.round(fullLen - (fullLen - minLen) * clamped);
                const visibleLen = Math.max(minLen, Math.min(fullLen, dynamicLen));

                if (visibleLen >= fullLen) {
                    titleEl.textContent = full;
                } else {
                    titleEl.textContent = `${full.slice(0, visibleLen).trimEnd()}...`;
                }
            };

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
                    delete shell._fiflpWasBelow;
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
                    let morphProgressMobile = 0;
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

                        /*
                         * Flag: el número estuvo por debajo del centro del viewport en algún momento.
                         * Si nunca estuvo abajo (p.ej. prólogo: sección arriba desde la carga de página)
                         * el fixed no se activa para evitar el salto número-natural → centro.
                         * En ese caso el morph progresa por posición de la sección (scroll-based).
                         */
                        if (!isFixed && nc > centerY + 0.75) {
                            shell._fiflpWasBelow = true;
                        }
                        const wasBelow = shell._fiflpWasBelow === true;

                        if (isFixed && (nc > centerY + 2 || shellBottom < numBottomFixed)) {
                            /* Desactivar: sección volvió atrás O el fondo pasó por debajo del número */
                            morphProgressMobile = 0;
                            shell.classList.remove('is-onepage-numero-sticky');
                            delete shell._fiflpOnepageMorphScrollY0;
                            delete shell._fiflpShellPadTop;
                            delete shell._fiflpWasBelow;
                            shell.style.removeProperty('--onepage-numero-sticky-top');
                        } else if (!isFixed && nc > centerY + 0.75) {
                            /* Número aún no ha llegado al centro desde abajo */
                            morphProgressMobile = 0;
                        } else if (!isFixed && !wasBelow) {
                            /*
                             * Móvil: antes de centrar el número se mantiene sólido.
                             * El barrido empieza únicamente cuando el número entra en sticky.
                             */
                            morphProgressMobile = 0;
                        } else {
                            /* Activar fixed o continuar morph */
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
                        delete shell._fiflpWasBelow;
                        shell.style.removeProperty('--onepage-numero-sticky-top');
                        morphProgressMobile = 0;
                    }

                    shell.style.setProperty('--onepage-morph-progress', morphProgressMobile.toFixed(3));
                    shell.style.setProperty('--onepage-morph-progress-inverse', (1 - morphProgressMobile).toFixed(3));

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
                    let titleDeltaToHeader = Number.POSITIVE_INFINITY;
                    if (indiceEl) {
                        const topPx = indiceEl.getBoundingClientRect().top;
                        const headerEl = document.querySelector('.site-header');
                        const headerH = headerEl ? headerEl.offsetHeight : 0;
                        const band = Math.max(110, window.innerHeight * 0.2);
                        // Fase basada en distancia real al borde inferior del header:
                        // delta >= band -> 0, delta <= 0 -> 1.
                        const delta = topPx - headerH;
                        titleDeltaToHeader = delta;
                        titlePhase = 1 - Math.max(0, Math.min(1, delta / band));
                    }
                    /*
                     * Suavizado móvil: evita tirón en los primeros px de scroll del titular sticky.
                     * Mezcla fase previa y nueva (lerp) para transición más fluida.
                     */
                    const prevTitlePhase = typeof shell._fiflpTitlePhaseSmooth === 'number'
                        ? shell._fiflpTitlePhaseSmooth
                        : titlePhase;
                    const smoothFactor = 0.22;
                    const smoothTitlePhase = prevTitlePhase + (titlePhase - prevTitlePhase) * smoothFactor;
                    shell._fiflpTitlePhaseSmooth = smoothTitlePhase;
                    const wasDocked = shell.classList.contains('is-onepage-title-docked');
                    // Docked en píxeles (más fiable que umbral por fase):
                    // entra cuando el título está casi pegado al header,
                    // sale cuando vuelve a separarse claramente.
                    const enterDockPx = 4;
                    const exitDockPx = 18;
                    const isDocked = wasDocked
                        ? titleDeltaToHeader <= exitDockPx
                        : titleDeltaToHeader <= enterDockPx;
                    shell.classList.toggle('is-onepage-title-docked', isDocked);

                    if (isDocked && !wasDocked) {
                        shell._fiflpTitleDockScrollY = window.scrollY || window.pageYOffset || 0;
                        shell.classList.remove('is-onepage-title-erasing');
                        applyTitleTypingByPhase(0); // llega arriba completo
                    } else if (isDocked) {
                        const sy = window.scrollY || window.pageYOffset || 0;
                        const start = typeof shell._fiflpTitleDockScrollY === 'number'
                            ? shell._fiflpTitleDockScrollY
                            : sy;
                        const eraseSpan = 140; // px de scroll para completar el borrado
                        const erasePhase = Math.max(0, Math.min(1, (sy - start) / eraseSpan));
                        shell.classList.toggle('is-onepage-title-erasing', erasePhase > 0.02);
                        applyTitleTypingByPhase(erasePhase);
                    } else {
                        delete shell._fiflpTitleDockScrollY;
                        shell.classList.remove('is-onepage-title-erasing');
                        applyTitleTypingByPhase(0);
                    }
                    shell.style.setProperty('--onepage-mobile-title-phase', smoothTitlePhase.toFixed(3));
                    shell.style.setProperty('--onepage-title-opacity', '1');

                    shell.style.setProperty('--onepage-reveal-progress', revealProgress.toFixed(3));
                    shell.classList.add('is-title-visible');
                    shell.classList.toggle('is-content-visible', revealProgress > 0.05);
                    return;
                }

                shell.classList.remove('is-onepage-numero-sticky');
                shell.classList.remove('is-onepage-title-docked');
                delete shell._fiflpShellPadTop;
                delete shell._fiflpWasBelow;
                delete shell._fiflpTitlePhaseSmooth;
                delete shell._fiflpDockedTitleChars;
                delete shell._fiflpTitleDockScrollY;
                shell.classList.remove('is-onepage-title-erasing');
                if (titleEl) {
                    titleEl.textContent = getBaseTitle();
                }
                shell.style.removeProperty('--onepage-numero-sticky-top');
                shell.style.removeProperty('--onepage-title-opacity');
                shell.style.removeProperty('--onepage-mobile-title-phase');

                shell.style.setProperty('--onepage-morph-progress', morphProgress.toFixed(3));
                shell.style.setProperty('--onepage-reveal-progress', revealProgressDesktop.toFixed(3));
                shell.classList.add('is-title-visible');
                shell.classList.toggle('is-content-visible', revealProgressDesktop > 0.01);
            };
            let syncFrame = null;
            const scheduleSyncNumberState = () => {
                if (syncFrame !== null) {
                    return;
                }
                syncFrame = window.requestAnimationFrame(() => {
                    syncFrame = null;
                    syncNumberState();
                });
            };

            shell._fiflpSyncNumberState = scheduleSyncNumberState;

            const firstItem = narrativeItems[0];
            if (firstItem) {
                setActiveItem(firstItem);
            }

            if (reduceMotion) {
                shell.classList.remove('seccion-onepage--js');
                shell.classList.remove('is-onepage-numero-sticky');
                delete shell._fiflpOnepageMorphScrollY0;
                delete shell._fiflpShellPadTop;
                delete shell._fiflpWasBelow;
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
            delete shell._fiflpWasBelow;
            shell.style.removeProperty('--onepage-numero-sticky-top');
            if (mqMobile.matches) {
                shell.style.setProperty('--onepage-title-opacity', '1');
                shell.style.setProperty('--onepage-mobile-title-phase', '0');
            } else {
                shell.style.removeProperty('--onepage-title-opacity');
                shell.style.removeProperty('--onepage-mobile-title-phase');
            }

            scheduleSyncNumberState();
            window.addEventListener('scroll', scheduleSyncNumberState, { passive: true });
            window.addEventListener('resize', scheduleSyncNumberState);

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
    initPortadaHeroGalleryFade();
    initPortadaHeroCinematicIntro();

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
        const LIGHTBOX_ZOOM_FACTOR = 1.25;
        const LIGHTBOX_CLOSE_MS = 340;
        let closeTimer = null;
        let suppressImageClickToggle = false;

        const centerLightboxViewport = () => {
            if (!lightboxViewport) {
                return;
            }
            const dx = lightboxViewport.scrollWidth - lightboxViewport.clientWidth;
            const dy = lightboxViewport.scrollHeight - lightboxViewport.clientHeight;
            lightboxViewport.scrollLeft = dx > 0 ? dx / 2 : 0;
            lightboxViewport.scrollTop = dy > 0 ? dy / 2 : 0;
        };

        const getLightboxFitSize = () => {
            const viewportW = lightboxViewport ? lightboxViewport.clientWidth : window.innerWidth;
            const viewportH = lightboxViewport ? lightboxViewport.clientHeight : window.innerHeight;
            const naturalW = lightboxImg.naturalWidth || 0;
            const naturalH = lightboxImg.naturalHeight || 0;
            if (!(naturalW > 0 && naturalH > 0)) {
                return null;
            }
            const fitScale = Math.min(viewportW / naturalW, viewportH / naturalH, 1);
            return {
                w: Math.round(naturalW * fitScale),
                h: Math.round(naturalH * fitScale),
            };
        };

        const resetLightboxZoom = () => {
            const wasZoomed = lightbox.classList.contains('lightbox--zoomed');
            lightbox.classList.remove('lightbox--zoomed');
            if (lightboxZoom) {
                lightboxZoom.setAttribute('aria-pressed', 'false');
                lightboxZoom.setAttribute('aria-label', 'Ampliar imagen');
                lightboxZoom.setAttribute('title', 'Ampliar');
            }
            const fit = getLightboxFitSize();
            if (fit) {
                lightboxImg.style.maxWidth = 'none';
                lightboxImg.style.maxHeight = 'none';
                lightboxImg.style.width = `${fit.w}px`;
                lightboxImg.style.height = `${fit.h}px`;
            } else {
                lightboxImg.style.width = '';
                lightboxImg.style.height = '';
                lightboxImg.style.maxWidth = '';
                lightboxImg.style.maxHeight = '';
            }
            if (lightboxViewport) {
                if (wasZoomed) {
                    const onEnd = () => {
                        centerLightboxViewport();
                        lightboxImg.removeEventListener('transitionend', onEnd);
                    };
                    lightboxImg.addEventListener('transitionend', onEnd, { once: true });
                } else {
                    requestAnimationFrame(centerLightboxViewport);
                }
            }
        };

        const applyLightboxZoomSize = () => {
            if (!lightbox.classList.contains('lightbox--zoomed')) {
                return;
            }
            if (!(lightboxImg.naturalWidth > 0)) {
                return;
            }
            const fit = getLightboxFitSize();
            if (!fit) {
                return;
            }

            lightboxImg.style.maxWidth = 'none';
            lightboxImg.style.maxHeight = 'none';
            lightboxImg.style.width = Math.round(fit.w * LIGHTBOX_ZOOM_FACTOR) + 'px';
            lightboxImg.style.height = Math.round(fit.h * LIGHTBOX_ZOOM_FACTOR) + 'px';
            requestAnimationFrame(centerLightboxViewport);
        };

        const openLightbox = (src, caption = '', alt = '') => {
            if (!src) {
                return;
            }

            if (closeTimer) {
                window.clearTimeout(closeTimer);
                closeTimer = null;
            }
            resetLightboxZoom();
            lightboxImg.src = src;
            lightboxImg.alt = alt || caption;
            const fitOnOpen = getLightboxFitSize();
            if (fitOnOpen) {
                lightboxImg.style.width = `${fitOnOpen.w}px`;
                lightboxImg.style.height = `${fitOnOpen.h}px`;
            }
            requestAnimationFrame(centerLightboxViewport);
            if (lightboxCaption) {
                lightboxCaption.textContent = caption;
            }
            lightbox.classList.toggle('lightbox--has-caption', caption !== '');
            document.body.classList.add('fiflp-lightbox-open');
            lightbox.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                lightbox.classList.add('lightbox--open');
            });
        };

        const closeLightbox = () => {
            lightbox.classList.remove('lightbox--open');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('fiflp-lightbox-open');
            resetLightboxZoom();
            if (closeTimer) {
                window.clearTimeout(closeTimer);
            }
            closeTimer = window.setTimeout(() => {
                lightboxImg.setAttribute('src', '');
                lightboxImg.setAttribute('alt', '');
                if (lightboxCaption) {
                    lightboxCaption.textContent = '';
                }
                lightbox.classList.remove('lightbox--has-caption');
                closeTimer = null;
            }, LIGHTBOX_CLOSE_MS);
        };

        document.addEventListener('click', function(e) {
            const link = e.target.closest('.lightbox-trigger');

            if (!link) {
                return;
            }

            const src = link.getAttribute('href');
            const triggerImg = link.querySelector('img');
            const figure = link.closest('figure');
            const figureCaption = figure ? figure.querySelector('figcaption') : null;
            const caption = (
                link.getAttribute('data-caption')
                || (figureCaption ? figureCaption.textContent : '')
                || (triggerImg ? triggerImg.getAttribute('alt') : '')
                || ''
            ).trim();
            const alt = ((triggerImg ? triggerImg.getAttribute('alt') : '') || caption).trim();
            e.preventDefault();
            openLightbox(src, caption, alt);
        });

        lightboxClose.addEventListener('click', function() {
            closeLightbox();
        });

        const toggleLightboxZoom = () => {
            const next = !lightbox.classList.contains('lightbox--zoomed');
            lightbox.classList.toggle('lightbox--zoomed', next);
            if (lightboxZoom) {
                lightboxZoom.setAttribute('aria-pressed', next ? 'true' : 'false');
                lightboxZoom.setAttribute('aria-label', next ? 'Reducir imagen' : 'Ampliar imagen');
                lightboxZoom.setAttribute('title', next ? 'Reducir' : 'Ampliar');
            }
            if (!next) {
                resetLightboxZoom();
            } else {
                applyLightboxZoomSize();
                if (!lightboxImg.complete || !(lightboxImg.naturalWidth > 0)) {
                    lightboxImg.addEventListener('load', applyLightboxZoomSize, { once: true });
                }
            }
        };

        if (lightboxZoom) {
            lightboxZoom.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleLightboxZoom();
            });
        }

        lightboxImg.addEventListener('click', function(e) {
            if (suppressImageClickToggle) {
                suppressImageClickToggle = false;
                return;
            }
            e.stopPropagation();
            toggleLightboxZoom();
        });

        // Clic en la transparencia del visor: cerrar sin exigir la cruz.
        if (lightboxViewport) {
            lightboxViewport.addEventListener('click', function(e) {
                if (e.target !== lightboxViewport) {
                    return;
                }
                if (suppressImageClickToggle) {
                    suppressImageClickToggle = false;
                    return;
                }
                closeLightbox();
            });
        }

        lightboxImg.addEventListener('load', function() {
            const fitOnLoad = getLightboxFitSize();
            if (fitOnLoad) {
                lightboxImg.style.width = `${fitOnLoad.w}px`;
                lightboxImg.style.height = `${fitOnLoad.h}px`;
            }
            requestAnimationFrame(centerLightboxViewport);
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
            if (e.key === 'Escape' && lightbox.classList.contains('lightbox--open')) {
                closeLightbox();
            }
        });

        // Drag para mover imagen ampliada (desktop/trackpad).
        let dragState = null;
        const endDrag = () => {
            if (dragState && dragState.moved) {
                suppressImageClickToggle = true;
                window.setTimeout(() => {
                    suppressImageClickToggle = false;
                }, 120);
            }
            dragState = null;
            lightbox.classList.remove('lightbox--dragging');
        };

        if (lightboxViewport) {
            lightboxViewport.addEventListener('pointerdown', function(e) {
                if (!lightbox.classList.contains('lightbox--zoomed')) {
                    return;
                }
                if (e.button !== 0) {
                    return;
                }
                dragState = {
                    x: e.clientX,
                    y: e.clientY,
                    scrollLeft: lightboxViewport.scrollLeft,
                    scrollTop: lightboxViewport.scrollTop,
                    moved: false,
                };
                lightbox.classList.add('lightbox--dragging');
                if (typeof lightboxViewport.setPointerCapture === 'function') {
                    lightboxViewport.setPointerCapture(e.pointerId);
                }
                e.preventDefault();
            });

            lightboxViewport.addEventListener('pointermove', function(e) {
                if (!dragState || !lightbox.classList.contains('lightbox--zoomed')) {
                    return;
                }
                const dx = e.clientX - dragState.x;
                const dy = e.clientY - dragState.y;
                if (Math.abs(dx) > 3 || Math.abs(dy) > 3) {
                    dragState.moved = true;
                }
                lightboxViewport.scrollLeft = dragState.scrollLeft - dx;
                lightboxViewport.scrollTop = dragState.scrollTop - dy;
                e.preventDefault();
            });

            lightboxViewport.addEventListener('pointerup', endDrag);
            lightboxViewport.addEventListener('pointercancel', endDrag);
            lightboxViewport.addEventListener('pointerleave', endDrag);
        }

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

    /* ── Header: color de sección + logo compacto (móvil) ─────────────────
     * Lee --onepage-bg del shell de la sección más visible y lo propaga
     * al .site-header via --fiflp-header-section-bg.
     * is-compact → logo pequeño tras 40 px de scroll.
     * is-dark-section → logo en blanco (fondos #1e1e1e / #072728).
     * Solo activo en móvil (≤768 px); en escritorio limpia y no interfiere.
     * ─────────────────────────────────────────────────────────────────── */
    (function initOnepageHeaderSync() {
        const header = document.querySelector('.site-header');
        const mqMobile = window.matchMedia('(max-width: 768px)');
        const onepageSections = Array.from(
            document.querySelectorAll('.bloque.seccion-onepage')
        );

        if (!header || !onepageSections.length) return;

        const clamp01 = (v) => Math.max(0, Math.min(1, v));

        const cssColorToRgb = (input) => {
            if (!input || typeof input !== 'string') {
                return null;
            }
            const probe = document.createElement('span');
            probe.style.color = input.trim();
            probe.style.display = 'none';
            document.body.appendChild(probe);
            const resolved = window.getComputedStyle(probe).color;
            probe.remove();
            const m = resolved.match(/rgba?\(([^)]+)\)/i);
            if (!m) {
                return null;
            }
            const parts = m[1].split(',').map((p) => parseFloat(p.trim()));
            if (parts.length < 3 || parts.some(Number.isNaN)) {
                return null;
            }
            return {
                r: Math.max(0, Math.min(255, parts[0])),
                g: Math.max(0, Math.min(255, parts[1])),
                b: Math.max(0, Math.min(255, parts[2])),
            };
        };

        const mixRgb = (a, b, t) => {
            const k = clamp01(t);
            return {
                r: Math.round(a.r + (b.r - a.r) * k),
                g: Math.round(a.g + (b.g - a.g) * k),
                b: Math.round(a.b + (b.b - a.b) * k),
            };
        };

        const rgbToCss = (c) => `rgb(${c.r}, ${c.g}, ${c.b})`;
        const rgbLuma = (c) => (0.2126 * c.r) + (0.7152 * c.g) + (0.0722 * c.b);

        const getShellForSection = (section) => section ? section.querySelector('[data-onepage-shell]') : null;
        const getSectionBgRgb = (section) => {
            const shell = getShellForSection(section);
            if (!shell) {
                return null;
            }
            const bg = getComputedStyle(shell).getPropertyValue('--onepage-bg').trim();
            return cssColorToRgb(bg || '');
        };

        function syncHeaderSection() {
            if (!mqMobile.matches) {
                header.style.removeProperty('--fiflp-header-section-bg');
                header.classList.remove('is-dark-section', 'is-compact');
                return;
            }

            const vh = window.innerHeight;
            const scrollY = window.scrollY || 0;
            const headerH = header.offsetHeight || 0;

            header.classList.toggle('is-compact', scrollY > 40);

            // 1) Buscamos una zona de transición entre sección i e i+1:
            //    next.top = vh  -> progreso 0 (color actual al 100)
            //    next.top = headerH -> progreso 1 (color siguiente al 100)
            let blended = false;
            for (let i = 0; i < onepageSections.length - 1; i += 1) {
                const current = onepageSections[i];
                const next = onepageSections[i + 1];
                const nextRect = next.getBoundingClientRect();
                if (nextRect.top <= vh && nextRect.top >= headerH) {
                    const a = getSectionBgRgb(current);
                    const b = getSectionBgRgb(next);
                    if (a && b) {
                        const t = clamp01((vh - nextRect.top) / Math.max(1, (vh - headerH)));
                        const mixed = mixRgb(a, b, t);
                        header.style.setProperty('--fiflp-header-section-bg', rgbToCss(mixed));
                        header.classList.toggle('is-dark-section', rgbLuma(mixed) < 145);
                        blended = true;
                        break;
                    }
                }
            }
            if (blended) {
                return;
            }

            // 2) Fuera de transición, usamos la sección "anclada" al header.
            let anchorSection = null;
            onepageSections.forEach((section) => {
                const r = section.getBoundingClientRect();
                if (r.top <= headerH && r.bottom > headerH) {
                    anchorSection = section;
                }
            });

            if (!anchorSection) {
                anchorSection = onepageSections.find((section) => {
                    const r = section.getBoundingClientRect();
                    return r.top < vh && r.bottom > 0;
                }) || onepageSections[0];
            }

            const anchorShell = getShellForSection(anchorSection);
            if (anchorShell) {
                const bg = getComputedStyle(anchorShell).getPropertyValue('--onepage-bg').trim();
                header.style.setProperty('--fiflp-header-section-bg', bg || 'transparent');
                const rgb = cssColorToRgb(bg || '');
                if (rgb) {
                    header.classList.toggle('is-dark-section', rgbLuma(rgb) < 145);
                } else {
                    header.classList.toggle(
                        'is-dark-section',
                        anchorShell.classList.contains('seccion-onepage__shell--dark-bg')
                    );
                }
            } else {
                header.style.removeProperty('--fiflp-header-section-bg');
                header.classList.remove('is-dark-section');
            }
        }

        function updateHeaderHeight() {
            if (mqMobile.matches) {
                document.documentElement.style.setProperty(
                    '--fiflp-header-height',
                    header.offsetHeight + 'px'
                );
            } else {
                document.documentElement.style.removeProperty('--fiflp-header-height');
            }
        }

        window.addEventListener('scroll', syncHeaderSection, { passive: true });
        window.addEventListener('resize', updateHeaderHeight);
        mqMobile.addEventListener('change', function () {
            syncHeaderSection();
            updateHeaderHeight();
        });
        syncHeaderSection();
        updateHeaderHeight();
    }());

});
