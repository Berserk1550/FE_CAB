//ESTE JS SE ENCUENTRA DENTRO DEL 1ER HTML

//const HOME = <?php echo json_encode($homeUrl, JSON_UNESCAPED_SLASHES); ?>;
            const c   = document.getElementById('count');
            const go  = document.getElementById('goNow');
            let s = 5;
            (function tick() {
                s--;
                if (c) c.textContent = s;
                if (s <= 0) location.assign(HOME);
                else setTimeout(tick, 1000);
            })();
            go?.addEventListener('click', () => location.assign(HOME));

//SEGUNDO HTML

    /**
     * Componente: Botón flotante de navegación con modo compacto
     * Reutilizable para plantillas - Se adapta al scroll suavemente
     */
    (function setupBackButton() {
      'use strict';
      
      const volver = document.querySelector('.volver');
      if (!volver) return;
      
      const a = volver.querySelector('a');
      if (!a) return;

      // Asegurar que el texto esté envuelto en span.txt para animación suave
      let txtSpan = a.querySelector('.txt');
      if (!txtSpan) {
        const txtNodes = [];
        a.childNodes.forEach(n => {
          if (n.nodeType === Node.TEXT_NODE && n.nodeValue.trim()) {
            txtNodes.push(n);
          }
        });
        if (txtNodes.length) {
          txtSpan = document.createElement('span');
          txtSpan.className = 'txt';
          txtSpan.textContent = txtNodes.map(n => n.nodeValue).join(' ').replace(/\s+/g, ' ');
          txtNodes.forEach(n => n.remove());
          a.appendChild(txtSpan);
        }
      }

      let ticking = false;
      const SCROLL_THRESHOLD = 100; // Píxeles de scroll para activar modo compacto

      function handleScroll() {
        if (!ticking) {
          window.requestAnimationFrame(function() {
            const scrolled = window.scrollY > SCROLL_THRESHOLD;
            volver.classList.toggle('volver--compact', scrolled);
            ticking = false;
          });
          ticking = true;
        }
      }

      // Inicializar
      window.addEventListener('scroll', handleScroll, { passive: true });
      handleScroll(); // Verificar estado inicial
    })();