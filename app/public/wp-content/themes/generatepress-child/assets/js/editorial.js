/**
 * EDITORIAL JS
 * - Lightbox
 * - Animaciones
 */

document.addEventListener("DOMContentLoaded", function () {
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
