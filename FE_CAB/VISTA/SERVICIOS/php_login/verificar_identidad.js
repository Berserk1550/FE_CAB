    /* ---------- Utilidades ---------- */
        const idInput = document.getElementById('numero_id');
        const form = document.getElementById('form-id');
        const btn = form.querySelector('.btn-sena');
        const alertZona = document.getElementById('alertZona');
        const KEY_DOC = 'verif_doc_num';

        /* Mantener valor entre visitas */
        if (idInput) {
            const saved = localStorage.getItem(KEY_DOC);
            if (saved && !idInput.value) idInput.value = saved;
        }

        /* Sanitiza y guarda */
        function sanitize(val) {
            return String(val || '').replace(/\D+/g, '').slice(0, 15);
        }

        // validador mínimo de dígitos
        const MIN_LEN = 5;

        function isValidDoc(doc) {
            return doc.length >= MIN_LEN;
        }


        function setLoading(v) {
            if (!btn) return;
            btn.disabled = !!v;
            btn.style.opacity = v ? .8 : 1;
            btn.textContent = v ? 'Revisando…' : 'Revisar';
        }

        function showAlert(msg) {
            alertZona.innerHTML = `<div class="alert" role="alert">${msg}</div>`;
        }

        function clearAlert() {
            alertZona.innerHTML = '';
        }

        function escapeHtml(s) {
            return String(s ?? '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        /* ---------- Lógica AJAX con cancelación ---------- */
        let controller = null;
        let lastQuery = '';

        async function lookup(doc) {
            // Reglas: mínimo 5 dígitos para consultar
            if (doc.length < 5) {
                clearAlert();
                return;
            }
            if (doc === lastQuery) return;
            lastQuery = doc;

            // Cancela petición anterior si sigue en vuelo
            if (controller) controller.abort();
            controller = new AbortController();

            setLoading(true);
            clearAlert();

            const fd = new FormData();
            fd.append('numero_id', doc);
            fd.append('ajax', '1');

            try {
                const res = await fetch(location.href, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: fd,
                    signal: controller.signal
                });
                const data = await res.json();

                if (data.ok) {
                    // Pintar modal
                    const body = document.getElementById('modalDesc');
                    const btnRedir = document.getElementById('btnRedir');
                    const u = data.usuario || {};
                    if (body) {
                        body.innerHTML = `
          <p>Verifica si tus datos son correctos:</p>
          <ul>
            <li><b>Nombres:</b> ${escapeHtml(u.nombres||'')}</li>
            <li><b>Apellidos:</b> ${escapeHtml(u.apellidos||'')}</li>
            <li><b>Documento:</b> ${escapeHtml(u.numero_id||'')}</li>
            <li><b>Celular:</b> ${escapeHtml(u.celular||'')}</li>
            <li><b>Correo:</b> ${escapeHtml(u.correo||'')}</li>
          </ul>`;
                    }
                    if (btnRedir && data.redir) {
                        btnRedir.href = data.redir;
                    }
                    openModal(); // abre modal
                    clearAlert(); // no dejes alerta roja visible
                } else {
                    showAlert(data.mensaje || 'No se encontró tu documento.');
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error(err);
                    showAlert('Ocurrió un error al consultar. Intenta de nuevo.');
                }
            } finally {
                setLoading(false);
            }
        }

        // Enviar con Enter o clic en el botón
        function manualLookup(e) {
            e.preventDefault();
            const clean = sanitize(idInput.value);
            if (!isValidDoc(clean)) {
                showAlert(`Ingresa al menos ${MIN_LEN} dígitos.`);
                btn.disabled = true;
                return;
            }
            clearAlert();
            lookup(clean);
        }

        form.addEventListener('submit', manualLookup);
        btn.addEventListener('click', manualLookup);


        /* ---------- Eventos ---------- */
        // Escribir => sanea, guarda, busca con debounce+cancelación
        let debTimer = null;
        idInput.addEventListener('input', () => {
            const clean = sanitize(idInput.value);
            if (idInput.value !== clean) idInput.value = clean;
            localStorage.setItem(KEY_DOC, clean);

            // habilita/deshabilita botón según validez
            btn.disabled = !isValidDoc(clean);

            clearAlert();
            clearTimeout(debTimer);
            // busca automáticamente (si es válido) sin tocar el botón
            debTimer = setTimeout(() => {
                if (isValidDoc(clean)) lookup(clean);
            }, 450);
        });

        // Enter en el form = también consulta (sin recargar)
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            lookup(sanitize(idInput.value));
        });

        /* ---------- Modal core ---------- */
        (function() {
            const modal = document.getElementById('usuarioModal');
            const backdrop = document.getElementById('usuarioBackdrop');
            if (!modal || !backdrop) return;

            const card = modal.querySelector('.modal-card');
            const closeBtns = modal.querySelectorAll('[data-close]');
            let lastActive = null;

            function getFocusable(root) {
                return [...root.querySelectorAll(
                    'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
                )].filter(el => el.offsetParent !== null || el === document.activeElement);
            }

            window.openModal = function() {
                lastActive = idInput || document.activeElement;
                modal.classList.add('is-open');
                backdrop.classList.add('is-open');
                const f = getFocusable(card)[0] || card;
                f.focus();
                document.addEventListener('keydown', onKey);
                document.addEventListener('focus', trapFocus, true);
            };

            function closeModal() {
                modal.classList.remove('is-open');
                backdrop.classList.remove('is-open');
                document.removeEventListener('keydown', onKey);
                document.removeEventListener('focus', trapFocus, true);
                if (idInput) idInput.focus();
            }

            function onKey(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeModal();
                }
                if (e.key === 'Tab') {
                    const f = getFocusable(card);
                    if (!f.length) return;
                    const first = f[0],
                        last = f[f.length - 1];
                    if (e.shiftKey && document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    } else if (!e.shiftKey && document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }

            function trapFocus(e) {
                if (!modal.classList.contains('is-open')) return;
                if (!modal.contains(e.target)) {
                    e.stopPropagation();
                    card.focus();
                }
            }

            closeBtns.forEach(b => b.addEventListener('click', closeModal));
            backdrop.addEventListener('click', closeModal);
        })();