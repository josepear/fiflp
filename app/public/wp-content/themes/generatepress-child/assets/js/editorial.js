/**
 * EDITORIAL JS
 * - Scroll suave
 * - Capítulo activo
 * - Lightbox
 * - Animaciones
 */

document.addEventListener("DOMContentLoaded", function () {

    // =========================
    // SCROLL SUAVE
    // =========================
    document.querySelectorAll('.menu-lateral a').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const id = this.getAttribute('href').replace('#', '');
            const target = document.getElementById(id);

            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
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

    window.addEventListener('scroll', function () {

        let current = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop - 120;

            if (window.scrollY >= sectionTop) {
                current = section.getAttribute('id');
            }
        });

        menuLinks.forEach(link => {
            link.classList.remove('activo');

            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('activo');
            }
        });

    });

    // =========================
    // LIGHTBOX
    // =========================
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.querySelector('.lightbox-img');
    const lightboxClose = document.querySelector('.lightbox-close');

    if (lightbox && lightboxImg && lightboxClose) {

        document.querySelectorAll('.lightbox-trigger').forEach(link => {

            link.addEventListener('click', function(e) {
                e.preventDefault();

                const src = this.getAttribute('href');

                lightboxImg.src = src;
                lightbox.style.display = 'flex';
            });

        });

        lightboxClose.addEventListener('click', function() {
            lightbox.style.display = 'none';
        });

        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                lightbox.style.display = 'none';
            }
        });

    }

    // =========================
    // ANIMACIÓN BLOQUES
    // =========================
    const bloques = document.querySelectorAll('.fade-in');

    if (bloques.length) {

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

            // Delay progresivo
            bloque.style.transitionDelay = (index * 0.08) + 's';
        });

    }

}); // ← ESTO TE FALTABA