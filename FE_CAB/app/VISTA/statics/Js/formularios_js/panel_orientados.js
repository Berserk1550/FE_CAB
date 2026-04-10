   // Función de medalla
    (function() {
      const medallaBtn = document.querySelector(".btn-medalla");
      const tooltip = document.querySelector(".tooltip-medalla");
      const textoGuardado = document.querySelector(".texto-guardado");

      if (!medallaBtn || !tooltip || !textoGuardado) return;

      // Inicializamos el contenido del tooltip con el texto guardado
      tooltip.textContent = textoGuardado.textContent.trim();

      medallaBtn.addEventListener("click", function(e) {
        e.stopPropagation();
        tooltip.classList.toggle("visible");
        tooltip.setAttribute("aria-hidden", tooltip.classList.contains("visible") ? "false" : "true");
      });

      // Se cierra si se da click por fuera
      document.addEventListener("click", function(e) {
        if (!medallaBtn.contains(e.target) && !tooltip.contains(e.target)) {
          tooltip.classList.remove("visible");
          tooltip.setAttribute("aria-hidden", "true");
        }
      });
    })();