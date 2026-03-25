if (!window.__ACTUALIZAR_FORM_INIT__) {
      window.__ACTUALIZAR_FORM_INIT__ = true;
      (function() {
        const $ = (s, c = document) => c.querySelector(s);
        const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
        const on = (el, ev, fn) => el && el.addEventListener(ev, fn);
        const normalizeKey = window.__normalizeKey__ || (window.__normalizeKey__ = (s) => (s ?? '').normalize('NFD').replace(/\p{Diacritic}/gu, '').replace(/[\u200B-\u200D\uFEFF]/g, '').replace(/\s+/g, ' ').trim().toLowerCase());

        function sameGentilicio(a, b) {
          const norm = (s) => (s ?? '').normalize('NFD').replace(/\p{Diacritic}/gu, '').replace(/[\u200B-\u200D\uFEFF]/g, '').trim().toLowerCase();
          const stripVar = (s) => norm(s).replace(/\s+/g, ' ').replace(/(?:\/a|\(a\)|o\(a\))$/, '').replace(/([a-záéíóúüñ])(?:o|a)$/i, '$1').trim();
          return stripVar(a) === stripVar(b);
        }
        const cleanStr = (s = '') => String(s).replace(/[\u00A0\u202F\u2000-\u200B\u2060\uFEFF]/g, ' ').replace(/\s+/g, ' ').trim();
        const sameNoSpace = (a, b) => normalizeKey(cleanStr(a)).replace(/\s+/g, '') === normalizeKey(cleanStr(b)).replace(/\s+/g, '');

        const phases = $$('.phase'),
          steps = $$('.stepper .step'),
          bar = $('#bar'),
          btnPrev = $('#btnPrev'),
          btnNext = $('#btnNext'),
          btnSubmit = $('#btnSubmit'),
          btnSave = $('#btnSaveDraft'),
          stepper = $('.stepper');
        let current = Math.max(0, phases.findIndex(p => p.classList.contains('active')));
        if (current === -1) current = 0;

        function setProgress() {
          const pct = (current) / Math.max(1, phases.length - 1) * 100;
          if (bar) bar.style.width = pct + '%';
          steps.forEach((s, i) => {
            s.dataset.active = (i === current);
            s.dataset.done = (i < current);
          });
        }

        function centerActiveStep(immediate = false) {
          const sc = stepper,
            el = steps[current];
          if (!sc || !el) return;
          const r = el.getBoundingClientRect();
          const delta = (r.left + r.width / 2) - (window.innerWidth / 2);
          let target = sc.scrollLeft + delta;
          const max = sc.scrollWidth - sc.clientWidth;
          target = Math.max(0, Math.min(max, target));
          if (immediate) sc.scrollLeft = target;
          else sc.scrollTo ? sc.scrollTo({
            left: target,
            behavior: 'smooth'
          }) : (sc.scrollLeft = target);
        }

        function showPhase(i) {
          phases.forEach(p => p.classList.remove('active'));
          phases[i].classList.add('active');
          if (btnPrev) btnPrev.disabled = (i === 0);
          if (btnNext) btnNext.style.display = (i === phases.length - 1) ? 'none' : '';
          if (btnSubmit) btnSubmit.style.display = (i === phases.length - 1) ? '' : 'none';
          setProgress();
          requestAnimationFrame(() => centerActiveStep());
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        }

        function validateVisiblePhase() {
          const section = phases[current];
          const inputs = section.querySelectorAll('input, select, textarea');
          for (const el of inputs) {
            if (el.offsetParent === null) continue;
            el.oninput = () => el.setCustomValidity('');
            if (!el.checkValidity()) {
              el.reportValidity();
              el.focus();
              return false;
            }
          }
          return true;
        }
        on(btnPrev, 'click', () => {
          if (current > 0) {
            current--;
            showPhase(current);
          }
        });
        on(btnNext, 'click', () => {
          if (validateVisiblePhase() && current < phases.length - 1) {
            current++;
            showPhase(current);
          }
        });
        steps.forEach((s, i) => {
          s.style.cursor = 'pointer';
          on(s, 'click', () => {
            if (i === current) return;
            if (i > current && !validateVisiblePhase()) return;
            current = i;
            showPhase(current);
          });
        });

        const form = $('#form') || $('form');
        on(btnSave, 'click', () => {
          if (!form) return;
          const fd = new FormData(form);
          const obj = {};
          fd.forEach((v, k) => (obj[k] = v));
          const key = 'actualizacion_datos_draft_' + (form.numero_id?.value || 'tmp');
          localStorage.setItem(key, JSON.stringify(obj));
          btnSave.innerText = 'Borrador guardado ✓';
          setTimeout(() => (btnSave.innerText = 'Guardar borrador'), 1800);
        });
        (function restoreDraft() {
          if (!form) return;
          const key = 'actualizacion_datos_draft_' + (form.numero_id?.value || 'tmp');
          try {
            const raw = localStorage.getItem(key);
            if (!raw) return;
            const data = JSON.parse(raw);
            Object.keys(data).forEach((k) => {
              const el = form.elements[k];
              if (!el) return;
              const serverVal = el.getAttribute('data-value');
              if (serverVal && String(serverVal).trim() !== '') return;
              const incoming = data[k];
              if (incoming === '' || incoming == null) return;
              if (/select|input|textarea/i.test(el.tagName)) {
                if (el.value && el.value !== '') return;
                el.value = incoming;
              }
            });
          } catch {}
        })();

        const paisNacionalidad = {
          "Afganistán": "Afgano/a",
          "Albania": "Albanés/a",
          "Alemania": "Alemán/a",
          "Andorra": "Andorrano/a",
          "Angola": "Angoleño/a",
          "Antigua y Barbuda": "Antiguano/a",
          "Arabia Saudita": "Saudí/a",
          "Argelia": "Argelino/a",
          "Argentina": "Argentino/a",
          "Armenia": "Armenio/a",
          "Australia": "Australiano/a",
          "Austria": "Austriaco/a",
          "Azerbaiyán": "Azerí/a",
          "Bahamas": "Bahamés/a",
          "Bangladés": "Bangladesí/a",
          "Barbados": "Barbadense/a",
          "Baréin": "Bareiní/a",
          "Bélgica": "Belga",
          "Belice": "Beliceño/a",
          "Benín": "Beninés/a",
          "Bielorrusia": "Bielorruso/a",
          "Bolivia": "Boliviano/a",
          "Bosnia y Herzegovina": "Bosnio/a",
          "Botsuana": "Botsuano/a",
          "Brasil": "Brasileño/a",
          "Brunéi": "Bruneano/a",
          "Bulgaria": "Búlgaro/a",
          "Cabo Verde": "Caboverdiano/a",
          "Camboya": "Camboyano/a",
          "Camerún": "Camerunés/a",
          "Canadá": "Canadiense",
          "Catar": "Catarí",
          "Chile": "Chileno/a",
          "China": "Chino/a",
          "Chipre": "Chipriota",
          "Colombia": "Colombiano/a",
          "Corea del Norte": "Norcoreano/a",
          "Corea del Sur": "Surcoreano/a",
          "Costa Rica": "Costarricense",
          "Cuba": "Cubano/a",
          "Dinamarca": "Danés/a",
          "Ecuador": "Ecuatoriano/a",
          "Egipto": "Egipcio/a",
          "El Salvador": "Salvadoreño/a",
          "Emiratos Árabes Unidos": "Emiratí",
          "Eslovaquia": "Eslovaco/a",
          "Eslovenia": "Esloveno/a",
          "España": "Español/a",
          "Estados Unidos": "Estadounidense",
          "Estonia": "Estonio/a",
          "Etiopía": "Etíope",
          "Filipinas": "Filipino/a",
          "Finlandia": "Finlandés/a",
          "Francia": "Francés/a",
          "Grecia": "Griego/a",
          "Guatemala": "Guatemalteco/a",
          "Guyana": "Guyanés/a",
          "Haití": "Haitiano/a",
          "Honduras": "Hondureño/a",
          "Hungría": "Húngaro/a",
          "India": "Indio/a",
          "Indonesia": "Indonesio/a",
          "Irak": "Iraquí",
          "Irán": "Iraní",
          "Irlanda": "Irlandés/a",
          "Islandia": "Islandés/a",
          "Israel": "Israelí",
          "Italia": "Italiano/a",
          "Jamaica": "Jamaicano/a",
          "Japón": "Japonés/a",
          "Jordania": "Jordano/a",
          "Kazajistán": "Kazajo/a",
          "Kenia": "Keniano/a",
          "Kuwait": "Kuwaití",
          "Laos": "Laosiano/a",
          "Letonia": "Letón/a",
          "Líbano": "Libanés/a",
          "Lituania": "Lituano/a",
          "Luxemburgo": "Luxemburgués/a",
          "Madagascar": "Malgache",
          "Malasia": "Malasio/a",
          "Malta": "Maltés/a",
          "Marruecos": "Marroquí",
          "México": "Mexicano/a",
          "Moldavia": "Moldavo/a",
          "Mónaco": "Monegasco/a",
          "Mongolia": "Mongol/a",
          "Mozambique": "Mozambiqueño/a",
          "Namibia": "Namibio/a",
          "Nepal": "Nepalí",
          "Nicaragua": "Nicaragüense",
          "Nigeria": "Nigeriano/a",
          "Noruega": "Noruego/a",
          "Nueva Zelanda": "Neozelandés/a",
          "Omán": "Omaní",
          "Países Bajos": "Neerlandés/a",
          "Pakistán": "Pakistaní",
          "Panamá": "Panameño/a",
          "Paraguay": "Paraguayo/a",
          "Perú": "Peruano/a",
          "Polonia": "Polaco/a",
          "Portugal": "Portugués/a",
          "Reino Unido": "Británico/a",
          "República Checa": "Checo/a",
          "República Dominicana": "Dominicano/a",
          "Rumania": "Rumano/a",
          "Rusia": "Ruso/a",
          "Senegal": "Senegalés/a",
          "Serbia": "Serbio/a",
          "Singapur": "Singapurense",
          "Siria": "Sirio/a",
          "Somalia": "Somalí",
          "Sri Lanka": "Ceilanés/a",
          "Sudáfrica": "Sudafricano/a",
          "Suecia": "Sueco/a",
          "Suiza": "Suizo/a",
          "Tailandia": "Tailandés/a",
          "Tanzania": "Tanzano/a",
          "Túnez": "Tunecino/a",
          "Turquía": "Turco/a",
          "Ucrania": "Ucraniano/a",
          "Uganda": "Ugandés/a",
          "Uruguay": "Uruguayo/a",
          "Uzbekistán": "Uzbeko/a",
          "Venezuela": "Venezolano/a",
          "Vietnam": "Vietnamita",
          "Yemen": "Yemení",
          "Zambia": "Zambiano/a",
          "Zimbabue": "Zimbabuense"
        };
        const selectPais = $('#pais_origen'),
          inpNac = $('#nacionalidad');

        function fixMojibake(s) {
          if (!s) return '';
          if (/Ã.|Â./.test(s)) {
            try {
              return decodeURIComponent(escape(s));
            } catch {}
          }
          return s;
        }

        function llenarPaises() {
          if (!selectPais) return;
          const fromDB = cleanStr(fixMojibake(selectPais.getAttribute('data-value') || ''));
          const fromDBKey = normalizeKey(fromDB);
          const nacDB = cleanStr(fixMojibake(inpNac?.value || ''));
          const nacKey = normalizeKey(nacDB);
          selectPais.innerHTML = '';
          const ph = new Option('-- Selecciona un país --', '', true, true);
          ph.disabled = true;
          selectPais.add(ph);
          const paises = Object.keys(paisNacionalidad).sort((a, b) => a.localeCompare(b, 'es'));
          let matched = false;
          paises.forEach((p) => {
            const opt = new Option(p, p);
            if (fromDBKey && normalizeKey(p) === fromDBKey) {
              opt.selected = true;
              ph.selected = false;
              matched = true;
            }
            selectPais.add(opt);
          });
          if (!matched && nacKey) {
            if (sameGentilicio(nacDB, 'Colombiano/a')) {
              selectPais.value = 'Colombia';
              ph.selected = false;
              matched = true;
            } else {
              for (const [pais, gent] of Object.entries(paisNacionalidad)) {
                if (sameGentilicio(nacDB, gent)) {
                  selectPais.value = pais;
                  ph.selected = false;
                  matched = true;
                  break;
                }
              }
            }
          }
          if (!matched && fromDB) {
            const opt = new Option(fromDB, fromDB, true, true);
            selectPais.add(opt);
            ph.selected = false;
          }
          if (selectPais.value) actualizarNacionalidad();
        }

        function actualizarNacionalidad() {
          const pais = selectPais?.value || '';
          const nac = paisNacionalidad[pais] || '';
          if (pais && nac && inpNac) inpNac.value = nac;
        }
        on(selectPais, 'change', actualizarNacionalidad);

        function setupCampoOtro(selectId, inputId) {
          const s = $('#' + selectId),
            i = $('#' + inputId);
          if (!s || !i) return;
          const toggle = () => {
            if (s.value === 'Otro') {
              i.style.display = 'block';
              i.required = true;
            } else {
              i.style.display = 'none';
              i.required = false;
              i.value = '';
            }
          };
          on(s, 'change', toggle);
          toggle();
        }
        setupCampoOtro('departamento', 'dpto_otro');
        setupCampoOtro('situacion_negocio', 'negocio_otro');
        const selTipo = $('#tipo_emprendedor'),
          inpOtro = $('#tipo_emprendedor_otro');
        on(selTipo, 'change', () => {
          if (!inpOtro) return;
          if (selTipo.value === 'Otro') {
            inpOtro.style.display = 'block';
            inpOtro.required = true;
          } else {
            inpOtro.required = false;
            inpOtro.value = '';
            inpOtro.style.display = 'none';
          }
        });

        const nf = $('#nivel_formacion');
        const careerMap = {
          tecnico: 'carrera_tecnico',
          tecnicolaboral: 'carrera_tecnico',
          tecnologo: 'carrera_tecnologo',
          operario: 'carrera_operario',
          auxiliar: 'carrera_auxiliar',
          profesional: 'carrera_profesional',
          profesionalconposgrado: 'carrera_profesional',
          especializacion: 'carrera_profesional',
          maestria: 'carrera_profesional',
          doctorado: 'carrera_profesional'
        };

        function hideAllCarreras() {
          Object.values(careerMap).forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.display = 'none';
            el.required = false;
            el.disabled = true;
          });
        }

        function showCarreraFor(val) {
          hideAllCarreras();
          const key = normalizeKey(val).replace(/\s+/g, '');
          if (key === 'sintitulo') return;
          const id = careerMap[key];
          const target = id ? document.getElementById(id) : null;
          if (target) {
            target.style.display = 'block';
            target.required = true;
            target.disabled = false;
          }
        }

        function getVisibleCarreraSelect() {
          const key = normalizeKey(nf?.value || nf?.dataset.value || '').replace(/\s+/g, '');
          if (key === 'sintitulo') return null;
          const id = careerMap[key];
          if (!id) return null;
          const el = document.getElementById(id);
          if (!el || el.disabled || el.style.display === 'none') return null;
          return el;
        }

        function syncCarreraHidden() {
          const hidden = document.getElementById('carrera_hidden');
          if (!hidden) return;
          const sel = getVisibleCarreraSelect();
          if (!sel) {
            const key = normalizeKey(nf?.value || nf?.dataset.value || '').replace(/\s+/g, '');
            hidden.value = (key === 'sintitulo') ? 'No aplica' : (hidden.value || '');
            return;
          }
          hidden.value = sel.value || hidden.value || '';
        }

        function ensureOptionExists(select, value) {
          if (!select || !value) return;
          const exists = Array.from(select.options).some(o => sameNoSpace(o.value, value));
          if (!exists) {
            const opt = new Option(value, value, true, true);
            select.add(opt);
          }
        }

        function ensureNivelFallbackIfNeeded() {
          const selNivel = document.getElementById('nivel_formacion');
          if (!selNivel) return;
          const current = selNivel.value || selNivel.dataset.value || '';
          if (!current) return;
          const has = Array.from(selNivel.options).some(o => normalizeKey(o.value) === normalizeKey(current) || normalizeKey(o.text) === normalizeKey(current));
          if (!has) {
            selNivel.add(new Option(current, current, true, true));
          }
        }

        function ensureCarreraFallbackIfNeeded() {
          const hidden = document.getElementById('carrera_hidden');
          const sel = getVisibleCarreraSelect();
          if (!hidden || !sel) return;
          const val = hidden.value;
          if (!val) return;
          ensureOptionExists(sel, val);
        }

        function ensureCarreraSelectedFromDataset() {
          const sel = getVisibleCarreraSelect();
          if (!sel) return;
          const fromServer = sel.getAttribute('data-value') || '';
          if (!fromServer) return;
          const key = normalizeKey(fromServer);
          let matched = false;
          for (const opt of sel.options) {
            if (normalizeKey(opt.value) === key || normalizeKey(opt.text) === key) {
              opt.selected = true;
              matched = true;
              break;
            }
          }
          if (!matched) {
            sel.add(new Option(fromServer, fromServer, true, true));
          }
          syncCarreraHidden();
        }
        if (nf && nf.dataset.value) {
          const from = nf.dataset.value;
          const key = normalizeKey(from);
          let matched = false;
          for (const opt of nf.options) {
            if (normalizeKey(opt.value) === key || normalizeKey(opt.text) === key) {
              nf.value = opt.value;
              matched = true;
              break;
            }
          }
          if (!matched) {
            nf.add(new Option(from, from, true, true));
          }
          nf.dispatchEvent(new Event('change'));
        }
        nf && nf.addEventListener('change', (e) => showCarreraFor(e.target.value));
        Object.values(careerMap).forEach((id) => {
          const el = document.getElementById(id);
          if (el) el.addEventListener('change', syncCarreraHidden);
        });

        const orientadoresPorCentro = {
          CAB: ["Celiced Castaño Barco", "Jose Julian Angulo Hernandez", "Lina Maria Varela", "Harby Arce", "Carlos Andrés Matallana", "Albeth Martinez Valencia"],
          CBI: ["Hector James Serrano Ramírez", "Javier Duvan Cano León", "Sandra Patricia Reinel Piedrahita", "Julian Adolfo Manzano Gutierrez"],
          CDTI: ["Diana Lorena Bedoya Vásquez", "Jacqueline Mafla Vargas", "Juan Manuel Oyola", "Gloria Betancourth"],
          CEAI: ["Carolina Gálvez Noreña", "Cerbulo Andres Cifuentes Garcia", "Clara Ines Campo chaparro"],
          CGTS: ["Francia Velasquez", "Julio Andres Pabon Arboleda", "Andres Felipe Betancourt Hernandez"],
          ASTIN: ["Pablo Andres Cardona Echeverri", "Juan Carlos Bernal Bernal", "Pablo Diaz", "Marlen Erazo"],
          CTA: ["Angela Rendon Marin", "Juan Manuel Marmolejo Escobar", "Liliana Fernandez Angulo", "Luz Adriana Loaiza"],
          CLEM: ["Adalgisa Palacio Santa", "Eiider Cardona", "Manuela Jimenez", "William Bedoya Gomez"],
          CNP: ["LEIDDY DIANA MOLANO CAICEDO", "PEDRO ANDRÉS ARCE MONTAÑO", "DIANA MORENO FERRÍN"],
          CC: ["Franklin Ivan Marin Gomez", "Jorge Iván Valencia Vanegas", "Deider Arboleda Riascos"]
        };

        function poblarOrientadores(centroCodigo, preNombre) {
          const sel = $('#orientador');
          if (!sel) return;
          const key = (centroCodigo || '').toString().trim().toUpperCase();
          const lista = orientadoresPorCentro[key] || [];
          const preClean = cleanStr(preNombre);
          sel.innerHTML = '';
          const ph = new Option('-- Selecciona un orientador --', '', !preClean, true);
          ph.disabled = true;
          sel.add(ph);
          lista.forEach((n) => sel.add(new Option(n, n)));
          if (preClean) {
            const exists = Array.from(sel.options).some(o => sameNoSpace(o.value, preNombre));
            if (!exists) {
              sel.add(new Option(preNombre, preNombre, true, true));
              ph.selected = false;
            } else {
              sel.value = Array.from(sel.options).find(o => sameNoSpace(o.value, preNombre)).value;
              ph.selected = false;
            }
          }
        }
        const centroSel = $('#centro_orientacion'),
          inpPrevCentro = $('#prev_centro'),
          inpPrevOrie = $('#prev_orientador'),
          hiddenModo = $('#reasignacion_modo'),
          chkDuplicar = $('#chkDuplicar');

        function decideMode() {
          const prevCentro = inpPrevCentro?.value || '';
          const prevOrie = inpPrevOrie?.value || '';
          const nowCentro = centroSel?.value || '';
          const nowOrie = $('#orientador')?.value || '';
          const changed = !sameNoSpace(prevCentro, nowCentro) || (nowOrie && !sameNoSpace(prevOrie, nowOrie));
          if (hiddenModo) hiddenModo.value = changed ? 'copiar' : 'actualizar';
          if (chkDuplicar) chkDuplicar.checked = changed;
        }
        (function initOrientadores() {
          const initialCentro = centroSel?.value || inpPrevCentro?.value || '';
          const initialOrie = inpPrevOrie?.value || '';
          if (initialCentro) poblarOrientadores(initialCentro, initialOrie);
        })();
        on(centroSel, 'change', () => {
          poblarOrientadores(centroSel.value, null);
          decideMode();
        });
        on($('#orientador'), 'change', decideMode);
        on(chkDuplicar, 'change', () => hiddenModo && (hiddenModo.value = chkDuplicar.checked ? 'copiar' : 'actualizar'));

        function yyyymmdd(d) {
          return d.toISOString().split('T')[0];
        }
        document.addEventListener('DOMContentLoaded', () => {
          const hoy = new Date();
          const minFecha = '1900-01-01';
          const fecha14 = new Date(hoy.getFullYear() - 14, hoy.getMonth(), hoy.getDate());
          const nacimiento = document.getElementById('fecha_nacimiento');
          if (nacimiento) {
            nacimiento.setAttribute('min', minFecha);
            nacimiento.setAttribute('max', yyyymmdd(fecha14));
            nacimiento.addEventListener('input', () => {
              nacimiento.setCustomValidity(nacimiento.value > yyyymmdd(fecha14) ? 'Debes tener al menos 14 años.' : '');
            });
          }
          const foDate = document.getElementById('fecha_orientacion_display');
          const foHidden = document.getElementById('fecha_orientacion');
          if (foDate && foHidden) {
            if (!foDate.value) foDate.value = yyyymmdd(hoy);
            if (!foHidden.value) foHidden.value = foDate.value;
            foDate.addEventListener('change', () => {
              foHidden.value = foDate.value || '';
            });
          }
        });
        on(inpNac, 'change', () => {
          const v = cleanStr(inpNac.value);
          if (sameGentilicio(v, 'Colombiano/a')) {
            selectPais.value = 'Colombia';
            actualizarNacionalidad();
          }
        });

        on(form, 'submit', (e) => {
          if (selectPais && selectPais.value) actualizarNacionalidad();
          if (!validateVisiblePhase()) {
            e.preventDefault();
            return;
          }
          decideMode();
          syncCarreraHidden();
          const inpModo = $('#reasignacion_modo');
          if (inpModo?.value === 'copiar') {
            const today = new Date().toISOString().slice(0, 10);
            const hiddenF = $('#fecha_orientacion');
            if (hiddenF) hiddenF.value = today;
          }
          btnSubmit?.setAttribute('disabled', 'disabled');
          btnNext?.setAttribute('disabled', 'disabled');
          btnPrev?.setAttribute('disabled', 'disabled');
          try {
            const key = 'actualizacion_datos_draft_' + (form.numero_id?.value || 'tmp');
            localStorage.removeItem(key);
          } catch {}
          const foDate = document.getElementById('fecha_orientacion_display');
          const foHidden = document.getElementById('fecha_orientacion');
          if (foDate && foHidden) foHidden.value = foDate.value || '';
        });

        (function init() {
          setProgress();
          showPhase(current);
          centerActiveStep(true);
          window.addEventListener('resize', () => centerActiveStep(true));
          window.addEventListener('orientationchange', () => centerActiveStep(true));
          llenarPaises();
          const selectedNivel = nf?.value || nf?.dataset.value || '';
          if (selectedNivel) {
            nf.value = selectedNivel;
            nf.dispatchEvent(new Event('change'));
            showCarreraFor(selectedNivel);
          } else {
            hideAllCarreras();
          }
          ensureNivelFallbackIfNeeded();
          ensureCarreraSelectedFromDataset();
          ensureCarreraFallbackIfNeeded();
          syncCarreraHidden();
          if (normalizeKey((nf?.value || nf?.dataset.value || '')).replace(/\s+/g, '') === 'sintitulo') {
            const hidden = document.getElementById('carrera_hidden');
            if (hidden) hidden.value = 'No aplica';
          }
        })();
        console.debug('pais_origen (BD crudo)=', document.querySelector('#pais_origen')?.dataset.value);
      })();
    }
