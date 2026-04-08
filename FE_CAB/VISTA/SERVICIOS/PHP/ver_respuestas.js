        // Script para mejorar la experiencia del botón flotante
        document.addEventListener('DOMContentLoaded', function() {
            const btnVolver = document.querySelector('.btn-volver');
            let lastScrollTop = 0;
            let scrollTimeout;

            // Agregar efecto de pulso sutil al botón cuando se hace scroll
            window.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                
                // Agregar clase temporal para efecto visual
                btnVolver.style.boxShadow = '0 6px 25px rgba(15, 143, 60, 0.3)';
                
                scrollTimeout = setTimeout(function() {
                    btnVolver.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
                }, 150);
            });

            // Mejorar el hover en móviles
            if ('ontouchstart' in window) {
                btnVolver.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                });
                
                btnVolver.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            }
        });
