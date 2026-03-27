/**1ER HTML */
(function() {
            let seconds = 5;
            const countEl = document.getElementById('secs');
            const home = document.getElementById('goNow').href;
            
            const timer = setInterval(function() {
                seconds--;
                if (countEl) countEl.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(timer);
                    location.replace(home);
                }
            }, 1000);
            
            setTimeout(function() {
                location.replace(home);
            }, 5000);
        })();
    
/**2DO HTML */

    // ================= UTILIDADES UI =================
    const $ = (s, r = document) => r.querySelector(s);
    const fmt = n => new Intl.NumberFormat('es-CO').format(n);

    const chkAll = document.getElementById('chkAll');
    if (new URL(location.href).searchParams.get('all') === '1') {
      chkAll.checked = true;
      setDatesEnabled(false);
    }
    chkAll.addEventListener('change', () => {
      setDatesEnabled(!chkAll.checked);
      cargar();
    });


    // Corrige mojibake básico
    function fixMojibake(str) {
      if (typeof str !== 'string' || !str) return str ?? '';
      let s = str.replace(/\uFFFD/g, '').replace(/Â(?=[^a-zA-Z0-9])/g, '');
      const map = {
        'Ã¡': 'á',
        'Ã©': 'é',
        'Ã­': 'í',
        'Ã³': 'ó',
        'Ãº': 'ú',
        'Ã�': 'Á',
        'Ã‰': 'É',
        'Ã�': 'Í',
        'Ã“': 'Ó',
        'Ãš': 'Ú',
        'Ã±': 'ñ',
        'Ã‘': 'Ñ',
        'Ã¼': 'ü',
        'Ãœ': 'Ü',
        'â': '’',
        'â': '‘',
        'â': '“',
        'â': '”',
        'â¢': '•',
        'â': '–',
        'â': '—',
        'â¦': '…',
        'Â¿': '¿',
        'Â¡': '¡'
      };
      return s.replace(/(Ã¡|Ã©|Ã­|Ã³|Ãº|Ã�|Ã‰|Ã�|Ã“|Ãš|Ã±|Ã‘|Ã¼|Ãœ|â|â|â|â|â¢|â|â|â¦|Â¿|Â¡)/g, m => map[m] || m);
    }

    function setDatesEnabled(enabled) {
      fDesde.disabled = !enabled;
      fHasta.disabled = !enabled;
      ['btn7', 'btn30', 'btn90'].forEach(id => {
        const b = document.getElementById(id);
        if (b) b.disabled = !enabled;
      });
    }

    // const chkAll = document.getElementById('chkAll');
    // chkAll.addEventListener('change', () => {
    //   setDatesEnabled(!chkAll.checked);
    //   cargar();
    // });


    function sanitizeTop(arr) {
      if (!Array.isArray(arr)) return [];
      return arr.map(o => ({
        k: fixMojibake(String(o?.k ?? '')),
        c: Number(o?.c ?? 0)
      }));
    }

    // ================== PALETA / OPCIONES BASE ==================
    const PALETTE = ['#34d399', '#22d3ee', '#a78bfa', '#f59e0b', '#60a5fa', '#fb7185', '#fbbf24', '#38bdf8', '#10b981', '#f472b6', '#93c5fd', '#fca5a5', '#c084fc', '#4ade80', '#f97316', '#84cc16', '#14b8a6', '#8b5cf6', '#06b6d4', '#ef4444', '#7dd3fc', '#d946ef', '#f43f5e', '#eab308', '#3b82f6', '#0ea5e9', '#16a34a', '#22c55e', '#64748b', '#0ea5a6', '#d97706', '#36b37e', '#5e60ce', '#e85d75', '#b5179e'];
    const mkColors = n => Array.from({
      length: n
    }, (_, i) => PALETTE[i % PALETTE.length]);

    // Tooltip sin funciones anidadas (evita structuredClone)
    const safeTooltip = {
      backgroundColor: 'rgba(8,20,30,.9)',
      borderColor: '#0f2c41',
      borderWidth: 1,
      titleColor: '#eaf4ff',
      bodyColor: '#dbeafe',
      padding: 10,
      callbacks: {
        label: function(ctx) {
          const v = (ctx.parsed?.y ?? ctx.parsed ?? 0);
          const lab = ctx.dataset?.label ? ctx.dataset.label + ': ' : '';
          return lab + new Intl.NumberFormat('es-CO').format(isFinite(v) ? v : 0);
        }
      }
    };

    const baseOptions = {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 600,
        easing: 'easeOutQuart'
      },
      layout: {
        padding: {
          left: 8,
          right: 12,
          top: 8,
          bottom: 8
        }
      },
      plugins: {
        legend: {
          labels: {
            color: '#cfe6ff',
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: safeTooltip
      },
      scales: {
        x: {
          grid: {
            color: 'rgba(255,255,255,.06)'
          },
          ticks: {
            color: '#b7cde3',
            maxRotation: 0,
            autoSkipPadding: 8
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(255,255,255,.06)'
          },
          ticks: {
            color: '#b7cde3',
            autoSkip: false
          }
        }
      }
    };
    const lineOptions = Object.assign({}, baseOptions, {
      plugins: Object.assign({}, baseOptions.plugins, {
        legend: {
          display: false
        }
      }),
      elements: {
        line: {
          tension: .3,
          borderWidth: 2,
          fill: true
        },
        point: {
          radius: 3,
          hoverRadius: 4
        }
      }
    });
    const barXOptions = Object.assign({}, baseOptions, {
      plugins: Object.assign({}, baseOptions.plugins, {
        legend: {
          display: false
        }
      })
    });
    const barYBase = Object.assign({}, baseOptions, {
      indexAxis: 'y',
      plugins: Object.assign({}, baseOptions.plugins, {
        legend: {
          display: false
        }
      })
    });

    // =============== WRAP DE ETIQUETAS & AUTO-ALTURA ===============
    // Divide un texto en varias líneas (Chart.js acepta array) manteniendo palabras.
    function wrapLabel(text, maxChars = 26) {
      const t = String(text || '');
      const words = t.split(/\s+/);
      const lines = [];
      let line = '';
      for (const w of words) {
        const add = (line ? ' ' : '') + w;
        if ((line + add).length > maxChars) {
          if (line) lines.push(line);
          line = w;
        } else line += add;
      }
      if (line) lines.push(line);
      return lines;
    }
    // Ajusta la altura del contenedor según cantidad de etiquetas (evita cortes).
    function setSmartHeightForY(canvasEl, labels) {
      const box = canvasEl.closest('.chart-wrap');
      const L = labels?.length || 0;
      const per = L <= 6 ? 42 : L <= 12 ? 34 : 28; // más espacio por fila si hay pocas
      const px = Math.max(240, 70 + L * per); // mínimo 240px
      box && box.style.setProperty('--h', px + 'px');
    }
    // Plugin que ensancha el eje Y para que quepan etiquetas largas.
    const growYScale = {
      id: 'growYScale',
      afterFit(scale) {
        if (scale.isHorizontal()) return;
        try {
          const labels = scale.ticks?.map(t => (t.label ?? t.value ?? t));
          if (!labels?.length) return;
          const ctx = scale.ctx;
          const font = Chart.helpers.toFont(scale.options.ticks.font);
          ctx.save();
          ctx.font = font.string;
          let max = 0;
          for (const l of labels) {
            const t = Array.isArray(l) ? l.join(' ') : String(l);
            const w = ctx.measureText(t).width;
            if (w > max) max = w;
          }
          ctx.restore();
          const padding = 22; // espacio extra
          scale.width = Math.ceil(max) + padding;
        } catch (_) {}
      }
    };

    // Crea opciones de barras horizontales con wrap de etiquetas.
    function mkBarYOptions(labels, maxChars = 26) {
      const opts = JSON.parse(JSON.stringify(barYBase)); // clon simple (objetos planos)
      opts.scales.y.ticks.autoSkip = false;
      opts.scales.y.ticks.callback = function(value, index) {
        return wrapLabel(labels[index], maxChars);
      };
      return opts;
    }

    // =============== RENDER GENÉRICO ===============
    function up(ref, el, cfg) {
      if (!el) return ref || null;
      if (ref) ref.destroy();
      return new Chart(el, cfg);
    }

    // =============== ESTADO CHARTS ===============
    let lineChart, accessChart, sexoChart, formChart, carrChart, progChart,
      horasChart, dowChart, domChart, muniChart, deptoChart, paisChart, acumChart,
      poblChart, discChart, formalChart, actChart, oriChart, centroChart, sitnegChart;

    // =============== FILTROS ===============
    const fDesde = $('#fDesde'),
      fHasta = $('#fHasta'),
      err = $('#err');
    $('#btn7').onclick = () => {
      setDays(7);
      cargar();
    };
    $('#btn30').onclick = () => {
      setDays(30);
      cargar();
    };
    $('#btn90').onclick = () => {
      setDays(90);
      cargar();
    };
    $('#btnAplicar').onclick = cargar;
    $('#btnExport').onclick = exportar;

    function setDays(d) {
      const t = new Date(),
        f = new Date();
      f.setDate(t.getDate() - (d - 1));
      fDesde.value = f.toISOString().slice(0, 10);
      fHasta.value = t.toISOString().slice(0, 10);
    }

    function showErr(m) {
      err.textContent = m || '';
      err.style.display = m ? 'block' : 'none';
    }

    // ================== CARGA ==================
    async function cargar() {
      const u = new URL('api_resultados', location.href);
      u.searchParams.set('action', 'stats');
      if (chkAll.checked) {
        u.searchParams.set('all', '1');
      } else {
        u.searchParams.set('from', (fDesde.value || '').trim());
        u.searchParams.set('to', (fHasta.value || '').trim());
      }


      let res, data, text;
      try {
        res = await fetch(u, {
          cache: 'no-store',
          credentials: 'same-origin'
        });
      } catch {
        showErr('No se pudo contactar el servidor.');
        return;
      }

      try {
        data = await res.json();
      } catch {
        try {
          text = await res.text();
        } catch (_) {}
        showErr(`Respuesta no válida (${res.status}): ${(text||'').slice(0,200)}`);
        return;
      }

      const ALL = !!data.all_mode;

      // Sincronizar UI según el modo que reporta el backend
      chkAll.checked = ALL;
      setDatesEnabled(!ALL);

      if (!res.ok || !data?.ok) {
        showErr(data?.detail || data?.error || `Error ${res.status}`);
        return;
      }

      // Sanea arrays del backend
      ['sexo', 'formacion_top', 'carrera_top', 'programa_top', 'correo_dom_top', 'municipio_top', 'depto_top', 'pais_top',
        'poblacion', 'discapacidad', 'empresa_formal', 'actividad', 'orientadores', 'centro_top', 'situacion_negocio'
      ].forEach(k => {
        if (Array.isArray(data[k])) data[k] = sanitizeTop(data[k]);
      });

      // ========== KPIs ==========
      const badge = $('#badgeRango');
      if (ALL) {
        badge.textContent = `Todos los registros`;
        $('#rangoText').textContent = `Todo el histórico · ${data.rango.first_date || '—'} → ${data.rango.last_date || '—'}`;
      } else {
        badge.textContent = `Del ${data.rango.from} al ${data.rango.to} (${data.rango.days} días)`;
        $('#rangoText').textContent = `Del ${data.rango.from} al ${data.rango.to} · ${data.rango.days} días`;
      }
      $('#kTotal').textContent = fmt(data.totales.total_rango);
      $('#kProm').textContent = fmt(data.totales.promedio_diario);
      $('#kMax').textContent = fmt(data.totales.max_en_un_dia);
      $('#kAccOk').textContent = fmt(data.totales.acceso_habilitado);
      $('#kAccPct').textContent = (data.totales.acceso_pct ?? 0) + '%';
      const vAbs = data.totales.variacion_abs;
      const vPct = data.totales.variacion_pct;
      $('#kVar').textContent = (vAbs == null ? '—' : ((vAbs > 0 ? '+' : '') + fmt(vAbs)));
      $('#kVarPct').textContent = (vPct == null ? '—' : ((vPct > 0 ? '+' : '') + vPct + '% vs prev.'));
      $('#kFirstLast').textContent = (data.rango.first_date && data.rango.last_date) ? `Primera: ${data.rango.first_date} · Última: ${data.rango.last_date}` : '';
      $('#rangoText').textContent = `Del ${data.rango.from} al ${data.rango.to} · ${data.rango.days} días`;

      // ========== Evolución ==========
      const L = (data.serie_dias || []).map(x => x.d),
        V = (data.serie_dias || []).map(x => x.c);
      lineChart = up(lineChart, $('#chartLine'), {
        type: 'line',
        data: {
          labels: L,
          datasets: [{
            label: 'Registros',
            data: V,
            borderColor: '#22d3ee',
            backgroundColor: 'rgba(34,211,238,.25)'
          }]
        },
        options: lineOptions
      });

      // Donuts acceso y sexo
      accessChart = up(accessChart, $('#chartAccess'), {
        type: 'doughnut',
        data: {
          labels: ['Habilitado', 'No habilitado'],
          datasets: [{
            data: [data.acceso.habilitado, data.acceso.no_habilitado],
            backgroundColor: ['#22c55e', '#64748b']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '62%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: '#cfe6ff',
                usePointStyle: true,
                pointStyle: 'circle'
              }
            },
            tooltip: safeTooltip
          }
        }
      });
      const sL = (data.sexo || []).map(x => x.k),
        sV = (data.sexo || []).map(x => x.c);
      sexoChart = up(sexoChart, $('#chartSexo'), {
        type: 'doughnut',
        data: {
          labels: sL,
          datasets: [{
            data: sV,
            backgroundColor: mkColors(sL.length)
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '62%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: '#cfe6ff',
                usePointStyle: true,
                pointStyle: 'circle'
              }
            },
            tooltip: safeTooltip
          }
        }
      });

      // Horas y DOW
      const H = [...Array(24)].map((_, i) => i),
        Hmap = new Map((data.hour_counts || []).map(o => [o.h, o.c])),
        Hv = H.map(h => Hmap.get(h) || 0);
      horasChart = up(horasChart, $('#chartHoras'), {
        type: 'bar',
        data: {
          labels: H.map(h => String(h).padStart(2, '0') + ':00'),
          datasets: [{
            data: Hv,
            backgroundColor: mkColors(H.length)
          }]
        },
        options: barXOptions
      });
      const Dlab = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        Dmap = new Map((data.dow_counts || []).map(o => [o.d, o.c])),
        Dv = [1, 2, 3, 4, 5, 6, 7].map(d => Dmap.get(d) || 0);
      dowChart = up(dowChart, $('#chartDOW'), {
        type: 'bar',
        data: {
          labels: Dlab,
          datasets: [{
            data: Dv,
            backgroundColor: mkColors(7)
          }]
        },
        options: barXOptions
      });

      // Acumulado
      const acumVals = [];
      let run = 0;
      for (const v of V) {
        run += (v || 0);
        acumVals.push(run);
      }
      acumChart = up(acumChart, $('#chartAcum'), {
        type: 'line',
        data: {
          labels: L,
          datasets: [{
            label: 'Acumulado',
            data: acumVals,
            borderColor: '#34d399',
            backgroundColor: 'rgba(52,211,153,.22)',
            fill: true
          }]
        },
        options: lineOptions
      });

      // Helper TOP-N
      const mk = arr => ({
        L: (arr || []).map(x => x.k),
        V: (arr || []).map(x => x.c)
      });

      // ===== Barras horizontales con auto-alto y wrap =====
      function renderBarY(ref, canvasSel, labels, values, maxChars = 26) {
        const el = $(canvasSel);
        setSmartHeightForY(el, labels);
        const options = mkBarYOptions(labels, maxChars);
        return up(ref, el, {
          type: 'bar',
          data: {
            labels,
            datasets: [{
              data: values,
              backgroundColor: mkColors(labels.length),
              borderRadius: 8
            }]
          },
          options,
          plugins: [growYScale]
        });
      }

      // Programas — Formación / Carrera / Programa
      let t = mk(data.formacion_top || []);
      formChart = renderBarY(formChart, '#chartFormacion', t.L, t.V, 22);

      t = mk((data.carrera_top && data.carrera_top.length) ? data.carrera_top : (data.programa_top || []));
      carrChart = renderBarY(carrChart, '#chartCarrera', t.L, t.V, 30);

      t = mk(data.programa_top || []);
      progChart = renderBarY(progChart, '#chartPrograma', t.L, t.V, 26);

      // Dominios y ubicación
      t = mk(data.correo_dom_top || []);
      domChart = renderBarY(domChart, '#chartDominios', t.L, t.V, 28);

      t = mk(data.municipio_top || []);
      muniChart = renderBarY(muniChart, '#chartMuni', t.L, t.V, 28);

      t = mk(data.depto_top || []);
      deptoChart = renderBarY(deptoChart, '#chartDepto', t.L, t.V, 28);

      t = mk(data.pais_top || []);
      paisChart = renderBarY(paisChart, '#chartPais', t.L, t.V, 22);

      // Nuevos bloques
      t = mk(data.poblacion || []);
      poblChart = renderBarY(poblChart, '#chartPoblacion', t.L, t.V, 26);

      t = mk(data.discapacidad || []);
      discChart = renderBarY(discChart, '#chartDiscapacidad', t.L, t.V, 24);

      (() => { // Empresa formalizada
        const L = (data.empresa_formal || []).map(x => x.k),
          V = (data.empresa_formal || []).map(x => x.c);
        formalChart = up(formalChart, $('#chartFormalEmpresa'), {
          type: 'doughnut',
          data: {
            labels: L,
            datasets: [{
              data: V,
              backgroundColor: mkColors(L.length)
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  color: '#cfe6ff',
                  usePointStyle: true,
                  pointStyle: 'circle'
                }
              },
              tooltip: safeTooltip
            }
          }
        });
      })();

      (() => { // Actividad económica
        const L = (data.actividad || []).map(x => x.k),
          V = (data.actividad || []).map(x => x.c);
        actChart = up(actChart, $('#chartActividad'), {
          type: 'doughnut',
          data: {
            labels: L,
            datasets: [{
              data: V,
              backgroundColor: mkColors(L.length)
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  color: '#cfe6ff',
                  usePointStyle: true,
                  pointStyle: 'circle'
                }
              },
              tooltip: safeTooltip
            }
          }
        });
      })();

      t = mk(data.orientadores || []);
      oriChart = renderBarY(oriChart, '#chartOrientadores', t.L, t.V, 28);

      t = mk(data.centro_top || []);
      centroChart = renderBarY(centroChart, '#chartCentro', t.L, t.V, 16);

      (() => { // Situación negocio (doughnut)
        const L = (data.situacion_negocio || []).map(x => x.k),
          V = (data.situacion_negocio || []).map(x => x.c);
        sitnegChart = up(sitnegChart, $('#chartSitNeg'), {
          type: 'doughnut',
          data: {
            labels: L,
            datasets: [{
              data: V,
              backgroundColor: mkColors(L.length)
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  color: '#cfe6ff',
                  usePointStyle: true,
                  pointStyle: 'circle'
                }
              },
              tooltip: safeTooltip
            }
          }
        });
      })();

      if (ALL) {
        badge.textContent = `Todos los registros`;
        $('#rangoText').textContent = `Todo el histórico · ${data.rango.first_date || '—'} → ${data.rango.last_date || '—'}`;
      } else {
        badge.textContent = `Del ${data.rango.from} al ${data.rango.to} (${data.rango.days} días)`;
        $('#rangoText').textContent = `Del ${data.rango.from} al ${data.rango.to} · ${data.rango.days} días`;
      }

    }

    function exportar() {
      const u = new URL('api_resultados', location.href);
      u.searchParams.set('action', 'export_charts');
      if (chkAll.checked) {
        u.searchParams.set('all', '1');
      } else {
        u.searchParams.set('from', (fDesde.value || '').trim());
        u.searchParams.set('to', (fHasta.value || '').trim());
      }
      location.href = u.toString();

    }

    const ro = new ResizeObserver(() => [
      lineChart, accessChart, sexoChart, formChart, carrChart, progChart, horasChart, dowChart, domChart, muniChart, deptoChart, paisChart, acumChart,
      poblChart, discChart, formalChart, actChart, oriChart, centroChart, sitnegChart
    ].forEach(c => c && c.resize()));
    ro.observe(document.querySelector('.board'));

    cargar();