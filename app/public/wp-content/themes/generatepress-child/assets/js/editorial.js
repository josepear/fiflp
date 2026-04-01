/**
 * EDITORIAL JS
 * - Scroll suave
 * - Capítulo activo
 * - Lightbox
 * - Animaciones
 */

document.addEventListener("DOMContentLoaded", function () {
    const getScrollOffset = () => {
        const adminBar = document.getElementById('wpadminbar');

        return (adminBar ? adminBar.offsetHeight : 0) + 40;
    };

    // =========================
    // SCROLL SUAVE
    // =========================
    document.querySelectorAll('.menu-lateral a').forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            if (!href || href.charAt(0) !== '#') {
                return;
            }

            e.preventDefault();

            const id = href.replace('#', '');
            const target = document.getElementById(id);

            if (target) {
                window.scrollTo({
                    top: target.getBoundingClientRect().top + window.scrollY - getScrollOffset(),
                    behavior: 'smooth'
                });
            }
        });
    });

    // =========================
    // CAPÍTULO ACTIVO
    // =========================
    const sections = document.querySelectorAll('.bloque.capitulo');
    const menuLinks = document.querySelectorAll('.menu-lateral a');

    const updateActiveChapter = () => {

        let current = sections[0] ? sections[0].getAttribute('id') : '';
        const scrollPosition = window.scrollY + getScrollOffset() + 1;

        sections.forEach(section => {
            const sectionTop = section.offsetTop;

            if (scrollPosition >= sectionTop) {
                current = section.getAttribute('id');
            }
        });

        menuLinks.forEach(link => {
            link.classList.remove('activo');

            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('activo');
            }
        });

    };

    window.addEventListener('scroll', updateActiveChapter, { passive: true });
    updateActiveChapter();

    // =========================
    // LIGHTBOX
    // =========================
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.querySelector('.lightbox-img');
    const lightboxClose = document.querySelector('.lightbox-close');

    if (lightbox && lightboxImg && lightboxClose) {
        const closeLightbox = () => {
            lightbox.style.display = 'none';
            lightbox.setAttribute('aria-hidden', 'true');
            lightboxImg.setAttribute('src', '');
        };

        document.querySelectorAll('.lightbox-trigger').forEach(link => {

            link.addEventListener('click', function(e) {
                const src = this.getAttribute('href');

                if (!src) {
                    return;
                }

                e.preventDefault();
                lightboxImg.src = src;
                lightbox.style.display = 'flex';
                lightbox.setAttribute('aria-hidden', 'false');
            });

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

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1
            });

            bloques.forEach((bloque, index) => {

                observer.observe(bloque);
                applyDelay(bloque, index);
            });
        } else {
            bloques.forEach((bloque, index) => {
                bloque.classList.add('visible');
                applyDelay(bloque, index);
            });
        }

    }

});
