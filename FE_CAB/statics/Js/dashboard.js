    // ===== Función de medalla (tooltip) =====
    (function() {
      const medallaBtn = document.querySelector(".btn-medalla");
      const tooltip = document.querySelector(".tooltip-medalla");
      const textoGuardado = document.querySelector(".texto-guardado");

      if (!medallaBtn || !tooltip || !textoGuardado) return;

      // Inicializar el contenido del tooltip
      tooltip.textContent = textoGuardado.textContent.trim();

      medallaBtn.addEventListener("click", function(e) {
        e.stopPropagation();
        tooltip.classList.toggle("visible");
        tooltip.setAttribute("aria-hidden", tooltip.classList.contains("visible") ? "false" : "true");
      });

      // Cerrar si se hace clic fuera
      document.addEventListener("click", function(e) {
        if (!medallaBtn.contains(e.target) && !tooltip.contains(e.target)) {
          tooltip.classList.remove("visible");
          tooltip.setAttribute("aria-hidden", "true");
        }
      });
    })();

    // ===== Datos PHP -> JS =====
    const ETAPAS = <?= json_encode($etapas, JSON_UNESCAPED_UNICODE) ?>;

    // ===== Constantes de posicionamiento por fase =====
    const POS_T = [0.11, 0.37, 0.66, 0.88];
    const META_T = 0.985;

    // ===== Referencias DOM =====
    const svg = document.getElementById('rutaCarreteraSVG');
    const guideP01 = document.getElementById('guide-p01');
    const guideP12 = document.getElementById('guide-p12');
    const guideP2E = document.getElementById('guide-p2e');
    const car = document.getElementById('car');
    const carBody = document.getElementById('car-body');
    const meta = document.getElementById('meta');
    const nodesCircles = document.getElementById('nodes-circles');
    const nodesLabels = document.getElementById('nodes-labels');
    const finishMsg = document.getElementById('finishMsg');

    const PATHS = {
      P01: guideP01,
      P12: guideP12,
      P2E: guideP2E
    };

    const SVG_SOURCE = 'ruta_emprendedora.svg';

    async function loadGuidesFromSVG() {
      try {
        const res = await fetch(SVG_SOURCE, {
          cache: 'no-store'
        });
        if (!res.ok) throw new Error("No pude cargar el SVG (" + res.status + ")");
        const text = await res.text();
        const doc = new DOMParser().parseFromString(text, "image/svg+xml");
        const a = doc.querySelector("#center_p0_1");
        const b = doc.querySelector("#center_p1_2");
        const c = doc.querySelector("#center_p2_2e");
        if (a && b && c) {
          PATHS.P01.setAttribute("d", a.getAttribute("d") || "");
          PATHS.P12.setAttribute("d", b.getAttribute("d") || "");
          PATHS.P2E.setAttribute("d", c.getAttribute("d") || "");
          return true;
        }
        throw new Error("IDs requeridos no encontrados en el SVG");
      } catch (err) {
        console.warn("[guías SVG]", err.message || err);
        PATHS.P01.setAttribute("d", "M -20 600 C 6 580, 15 560, 10 530 C 2 510, 2 475, 4 450 C 5 435, 4 420, 4 407 C 5 350, 10 343, 5 352 C 5 345, 9 299, 20 300 C 115 285, 130 355, 122 365 C 150 395, 253 390, 685 290");
        PATHS.P12.setAttribute("d", "M 690 300 C 700 295, 720 292, 740 291 C 760 290, 780 294, 840 273 C 865 270, 855 320, 890 335 C 930 340, 930 335, 940 340 C 980 340, 950 285, 950 255 C 950 255, 950 285, 925 220 C 900 215, 940 185, 945 150 C 955 130, 965 105, 995 95 C 1005 85, 1040 95, 1045 95 C 1045 95, 1070 140, 1075 145");
        PATHS.P2E.setAttribute("d", "M 1080 165 C 1100 165, 1090 175, 1095 185 C 1160 485, 1290 395, 1270 365 C 1280 325, 1285 305, 1275 255 C 1265 125, 1325 125, 1340 135");
        return false;
      }
    }

    function getLengths() {
      const L1 = PATHS.P01.getTotalLength();
      const L2 = PATHS.P12.getTotalLength();
      const L3 = PATHS.P2E.getTotalLength();
      return {
        L1,
        L2,
        L3,
        LT: L1 + L2 + L3
      };
    }

    function pointAtT(t) {
      const {
        L1,
        L2,
        L3,
        LT
      } = getLengths();
      const u = Math.max(0.005, Math.min(0.995, t));
      let s = u * LT;
      if (s <= L1) return PATHS.P01.getPointAtLength(s);
      s -= L1;
      if (s <= L2) return PATHS.P12.getPointAtLength(s);
      s -= L2;
      return PATHS.P2E.getPointAtLength(s);
    }

    function pointAtTDelta(t, dt = 0.002) {
      const u = Math.max(0.005, Math.min(0.995, t + dt));
      return pointAtT(u);
    }

    let idleRAF = null;

    function cancelIdleBounce() {
      if (idleRAF) {
        cancelAnimationFrame(idleRAF);
        idleRAF = null;
      }
    }

    function startIdleBounce(tBase, ampPx = 3, speedHz = 0.45) {
      cancelIdleBounce();

      function loop(ts) {
        const p = pointAtT(tBase);
        const p2 = pointAtTDelta(tBase);
        const angle = Math.atan2(p2.y - p.y, p2.x - p.x) * 180 / Math.PI;
        const phase = 2 * Math.PI * speedHz * (ts / 1000);
        const offsetY = Math.sin(phase) * ampPx;
        const wobble = Math.sin(phase * 0.5) * 0.4;
        car.setAttribute('transform', `translate(${p.x},${p.y+offsetY}) rotate(${angle})`);
        if (carBody) carBody.setAttribute('transform', `translate(0,${-2.6+wobble})`);
        idleRAF = requestAnimationFrame(loop);
      }
      idleRAF = requestAnimationFrame(loop);
    }

    function setCarAtT(t) {
      const p = pointAtT(t);
      const p2 = pointAtTDelta(t);
      const angle = Math.atan2(p2.y - p.y, p2.x - p.x) * 180 / Math.PI;
      car.setAttribute('transform', `translate(${p.x},${p.y}) rotate(${angle})`);
    }

    function animarHasta(fromT, toT, ms = 1400) {
      cancelIdleBounce();
      let start = null;
      const easeInOut = u => 0.5 - 0.5 * Math.cos(Math.PI * u);

      function step(ts) {
        if (!start) start = ts;
        const t = Math.min(1, (ts - start) / ms);
        const u = easeInOut(t);
        const curT = fromT + (toT - fromT) * u;

        const p = pointAtT(curT);
        const p2 = pointAtTDelta(curT);
        const angle = Math.atan2(p2.y - p.y, p2.x - p.x) * 180 / Math.PI;
        const wobble = Math.sin(ts * 0.006) * 1.0;

        car.setAttribute('transform', `translate(${p.x},${p.y}) rotate(${angle})`);
        if (carBody) carBody.setAttribute('transform', `translate(0,${-3+wobble})`);

        if (t < 1) requestAnimationFrame(step);
        else startIdleBounce(toT);
      }
      requestAnimationFrame(step);
    }

    (function() {
      let idxActiva = ETAPAS.findIndex(e => e.estado === 'active');
      if (idxActiva < 0) idxActiva = ETAPAS.length - 1;
      const subEl = document.getElementById('faseActivaTxt');
      if (subEl) {
        subEl.textContent = ETAPAS.every(e => e.estado === 'done') ?
          'Meta' :
          `#${ETAPAS[idxActiva].id} · ${ETAPAS[idxActiva].nombre}`;
      }
    })();

    function updateFinishMsg() {
      const allDone = ETAPAS.every(e => e.estado === 'done');
      if (!allDone) {
        finishMsg.classList.remove('show', 'bob');
        finishMsg.style.display = 'none';
        return;
      }
      const p = pointAtT(META_T);
      finishMsg.style.display = 'block';
      const tw = finishMsg.offsetWidth,
        th = finishMsg.offsetHeight;
      const svgW = svg.clientWidth,
        svgH = svg.clientHeight;
      const pad = 12,
        gap = 36;
      let left = p.x - (tw / 2);
      let top = p.y + gap;
      left = Math.max(pad, Math.min(left, svgW - pad - tw));
      top = Math.max(pad, Math.min(top, svgH - pad - th));
      finishMsg.style.left = `100px`;
      finishMsg.style.top = `20px`;
      finishMsg.classList.add('show', 'bob');
    }

    (async function boot() {
      await loadGuidesFromSVG();

      const mp = pointAtT(META_T);
      meta.setAttribute('transform', `translate(${mp.x},${mp.y})`);

      nodesCircles.innerHTML = '';
      nodesLabels.innerHTML = '';
      ETAPAS.forEach((et, i) => {
        const t = POS_T[i];
        const p = pointAtT(t);

        const gC = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        gC.setAttribute('transform', `translate(${p.x},${p.y})`);
        gC.style.cursor = (et.estado === 'locked') ? 'not-allowed' : 'pointer';
        gC.setAttribute('data-url', et.url);
        gC.setAttribute('data-estado', et.estado);

        const haloR = 30,
          nodeR = 25;
        const ring = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        ring.setAttribute('r', haloR);
        ring.setAttribute('fill', 'rgba(255,255,255,.95)');
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('r', nodeR);
        circle.setAttribute('stroke-width', 4);

        if (et.estado === 'done') {
          circle.setAttribute('fill', 'rgba(16,185,129,.95)');
          circle.setAttribute('stroke', '#065f46');
        } else if (et.estado === 'active') {
          circle.setAttribute('fill', 'rgba(251,191,36,.95)');
          circle.setAttribute('stroke', '#b45309');
        } else {
          circle.setAttribute('fill', 'rgba(156,163,175,.95)');
          circle.setAttribute('stroke', '#6b7280');
        }

        gC.appendChild(ring);
        gC.appendChild(circle);
        gC.addEventListener('click', () => {
          if (gC.getAttribute('data-estado') === 'locked') {
            alert('Fase bloqueada. Completa la anterior para continuar.');
            return;
          }
          const url = gC.getAttribute('data-url');
          if (url) window.location.href = url;
        });
        nodesCircles.appendChild(gC);

        const gL = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        gL.setAttribute('transform', `translate(${p.x},${p.y})`);
        const tx = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        tx.setAttribute('x', 0);
        tx.setAttribute('y', 6);
        tx.setAttribute('text-anchor', 'middle');
        tx.setAttribute('font-size', '14');
        tx.setAttribute('style', "font-family:'Work Sans';font-weight:700;fill:#0f172a;paint-order:stroke;stroke:#f5f5f590;stroke-width:3px;");
        tx.textContent = (et.estado === 'locked') ? '🔒' : (et.estado === 'done' ? '✓' : (i + 1));
        gL.appendChild(tx);
        nodesLabels.appendChild(gL);
      });

      const doneIdxs = ETAPAS.map((e, i) => e.estado === 'done' ? i : -1).filter(i => i >= 0);
      const allDone = ETAPAS.every(e => e.estado === 'done');

      let idxActiva = ETAPAS.findIndex(e => e.estado === 'active');
      if (idxActiva < 0) idxActiva = ETAPAS.length - 1;

      let fromT, toT;
      if (allDone) {
        fromT = POS_T[POS_T.length - 1];
        toT = META_T - 0.015;
      } else if (doneIdxs.length) {
        fromT = POS_T[doneIdxs[doneIdxs.length - 1]];
        toT = POS_T[idxActiva];
      } else {
        fromT = 0.06;
        toT = POS_T[idxActiva];
      }

      if (Math.abs(toT - fromT) < 0.0001) startIdleBounce(toT);
      else animarHasta(fromT, toT);

      updateFinishMsg();
      window.addEventListener('resize', updateFinishMsg);
    })();