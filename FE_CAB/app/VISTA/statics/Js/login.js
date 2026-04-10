document.addEventListener("DOMContentLoaded", () => {
  (() => {
    const btn = document.querySelector(".btn-medalla");
    const tooltip = document.querySelector(".tooltip-medalla");
    const txt = document.querySelector(".texto-guardado");

    if (!btn || !tooltip || !txt) return;
    tooltip.textContent = txt.textContent.trim();

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      tooltip.classList.toggle("visible");
    });
    document.addEventListener("click", (e) => {
      if (!tooltip.contains(e.target) && !btn.contains(e.target)) {
        tooltip.classList.remove("visible");
      }
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") tooltip.classList.remove("visible");
    });
  })();

  const ease = (t) => 1 - Math.pow(1 - t, 3);

  const animateValue = (el, start, end, ms = 1000) => {
    return new Promise((res) => {
      const ini = performance.now();
      const step = (now) => {
        const t = Math.min((now - ini) / ms, 1);
        const val = Math.round(start + (end - start) * ease(t));
        el.textContent = new Intl.NumberFormat("es-CO").format(val);
        if (t < 1) requestAnimationFrame(step);
        else {
          el.dataset.current = String(end);
          res();
        }
      };
      requestAnimationFrame(step);
    });
  };
  const renderList = (ul, data, type) => {
    if (!ul) return;
    ul.innerHTML = "";

    data.forEach((item) => {
      const li = document.createElement("li");
      const label = document.createElement("span");
      const value = document.createElement("span");

      label.className = "stat-label";
      value.className = "stat-value count";

      const newVal = item.total;

      const lastVal = parseInt(value.dataset?.current || "0", 10);

      // Asignar texto inicial ANTES de animar
      value.textContent = new Intl.NumberFormat("es-CO").format(lastVal);

      // Animar el número
      animateValue(value, lastVal, newVal, 1200);

      // Guardar el valor final
      value.dataset.current = newVal;

      if (type === "municipios") label.textContent = item.municipio;
      if (type === "estudios") label.textContent = item.nivel_formacion;
      if (type === "clasificaciones") label.textContent = item.clasificacion;

      li.appendChild(label);
      li.appendChild(value);
      ul.appendChild(li);
    });
  };

  const statCards = document.querySelectorAll(".card-stat");

  const animateInitialCounts = (card) => {
    const nums = card.querySelectorAll(".count");
    nums.forEach((el) => {
      if (el.dataset.init === "1") return;
      const raw = el.textContent.replace(/[^\d]/g, "") || "0";
      const target = parseInt(raw, 10);
      el.dataset.init = "1";
      el.dataset.current = "0";
      animateValue(el, 0, target, 1200);
    });
  };

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          animateInitialCounts(e.target);
          observer.unobserve(e.target);
        }
      });
    },
    {
      threshold: 0.4,
    }
  );

  statCards.forEach((card) => observer.observe(card));

  const ENDPOINT = "servicios/php/tarjeta_estadistica?ajax=1";

  const updateAsyncStats = async () => {
    try {
      const resp = await fetch(ENDPOINT, {
        cache: "no-store",
      });
      if (!resp.ok) return;
      const data = await resp.json();
      if (!data?.stats) return;

      /* --- 1. Total Registrados (ya funcionaba bien) --- */
      const totalEl = document.querySelector('.count[data-key="totalReg"]');
      if (totalEl) {
        const newVal = Number(data.stats.totalReg);
        const lastVal = parseInt(totalEl.dataset.current || "0", 10);
        if (newVal !== lastVal) animateValue(totalEl, lastVal, newVal);
      }

      const updateList = (ulSelector, newData, field) => {
        const ul = document.querySelector(ulSelector);
        if (!ul) return;

        const items = ul.querySelectorAll("li");
        newData.forEach((item, i) => {
          const li = items[i];
          if (!li) return;

          const valueEl = li.querySelector(".stat-value");
          if (!valueEl) return;

          const newVal = Number(item.total);
          const lastVal = parseInt(
            valueEl.dataset.current ||
              valueEl.textContent.replace(/[^\d]/g, ""),
            10
          );

          // si no cambió, no hacemos nada (NO animar)
          if (newVal === lastVal) return;

          // animar el cambio
          animateValue(valueEl, lastVal, newVal);
        });
      };

      updateList(
        'ul[data-key="municipios"]',
        data.stats.municipios,
        "municipios"
      );
      updateList('ul[data-key="estudios"]', data.stats.estudios, "estudios");
      updateList(
        'ul[data-key="clasificaciones"]',
        data.stats.clasificaciones,
        "clasificaciones"
      );
    } catch (e) {
      console.warn("Error al actualizar estadísticas:", e);
    }
  };

  updateAsyncStats();
  setInterval(updateAsyncStats, 1500);
});
// Ocultar el mensaje de error al empezar a escribir la contraseña
const inputContrasena = document.getElementById("contrasena");
const mensajeError = document.getElementById("errorMensaje");

if (inputContrasena && mensajeError) {
  inputContrasena.addEventListener("input", () => {
    mensajeError.style.display = "none";
  });
}

// Eliminar parámetros de error de la URL para evitar que se mantenga al refrescar
if (
  window.location.search.includes("error") ||
  window.location.search.includes("documento")
) {
  const url = new URL(window.location);
  url.searchParams.delete("error");
  url.searchParams.delete("documento");
  window.history.replaceState({}, document.title, url.pathname);
}

(function () {
  var b = document.body;
  function on() {
    b.style.overflow = "auto";
  }

  document.addEventListener("focusin", on);
})();

// Mostrar/Ocultar contraseña con íconos
const toggleBtn = document.getElementById("mostrarConstrasena");
const passInput = document.getElementById("contrasena");
const eyeOpen = document.getElementById("ojoAbierto");
const eyeClosed = document.getElementById("ojoCerrado");

// ✅ Verificar que TODOS los elementos existan antes de agregar el listener
if (toggleBtn && passInput && eyeOpen && eyeClosed) {
  toggleBtn.addEventListener("click", function () {
    const isPassword = passInput.type === "password";
    passInput.type = isPassword ? "text" : "password";
    eyeOpen.style.display = isPassword ? "none" : "";
    eyeClosed.style.display = isPassword ? "" : "none";
    toggleBtn.setAttribute(
      "aria-label",
      isPassword ? "Ocultar contraseña" : "Mostrar contraseña"
    );
  });
}
