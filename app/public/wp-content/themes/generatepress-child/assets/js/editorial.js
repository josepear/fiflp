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

            if (!principal || !subtitulo) {
                return;
            }

            subtitulo.style.width = principal.offsetWidth + 'px';
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

    scheduleRotuloFit();

    if (document.fonts && typeof document.fonts.ready === 'object') {
        document.fonts.ready.then(scheduleRotuloFit).catch(function () {});
    }

    window.addEventListener('resize', scheduleRotuloFit, { passive: true });

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
