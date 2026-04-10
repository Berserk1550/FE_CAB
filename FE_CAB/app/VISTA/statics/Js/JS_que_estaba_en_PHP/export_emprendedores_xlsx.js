        //const HOME = <?php echo json_encode($homeUrl, JSON_UNESCAPED_SLASHES); ?>;
            const countEl = document.getElementById('count');
            const goBtn = document.getElementById('goNow');
            let seconds = 5;

            function tick() {
                seconds--;
                if (countEl) countEl.textContent = seconds;
                if (seconds <= 0) {
                    location.assign(HOME);
                } else {
                    setTimeout(tick, 1000);
                }
            }
            setTimeout(tick, 1000);
            goBtn?.addEventListener('click', () => location.assign(HOME));

// ESTE SCRIPT SE ENCUENTRA DENTRO DEL 2 HTML DENTRO DE UN IF DE LA LINEA 383

            const frm = document.getElementById('frmExport'),
                modeSel = document.getElementById('export_mode'),
                fsEvento = document.getElementById('fs-evento'),
                fsFechas = document.getElementById('fs-fechas'),
                chkExpo = document.getElementById('expo_na'),
                inpExpo = document.getElementById('expositor'),
                exclNA = document.getElementById('excl_na'),
                exclChoices = document.getElementById('choices'),
                selOrient = document.getElementById('orientador_sel'),
                inpOrientOtro = document.getElementById('orientador_otro'),
                inpDesde = frm?.elements['desde'],
                inpHasta = frm?.elements['hasta'],
                inpTitulo = frm?.elements['titulo'];

            function setDisabledScope(scope, disabled) {
                if (!scope) return;
                scope.querySelectorAll('input,select,textarea').forEach(el => {
                    if (el.name === 'export_mode') return;
                    el.disabled = disabled;
                });
            }

            function syncExpositor() {
                const na = !!(chkExpo && chkExpo.checked),
                    isDB = modeSel.value === 'db';
                if (!inpExpo) return;
                inpExpo.disabled = na || isDB;
                inpExpo.placeholder = isDB ? 'No requerido en base completa' : (na ? 'No aplica' : 'Nombre del expositor');
                if (na || isDB) inpExpo.value = '';
            }

            function syncExclusive() {
                const na = (exclNA && exclNA.checked) || modeSel.value === 'db';
                if (!exclChoices) return;
                exclChoices.querySelectorAll('input[type="checkbox"]').forEach(ch => {
                    ch.disabled = na;
                    if (na) ch.checked = false;
                });
                exclChoices.style.opacity = na ? .5 : 1;
                exclChoices.setAttribute('aria-disabled', na ? 'true' : 'false');
            }

            function syncMode() {
                const isDB = modeSel.value === 'db';
                fsEvento.style.opacity = isDB ? .45 : 1;
                setDisabledScope(fsEvento, isDB);
                fsFechas.style.opacity = isDB ? .45 : 1;
                setDisabledScope(fsFechas, isDB);
                syncExpositor();
                syncExclusive();
            }

            function syncOrient() {
                const isOtro = selOrient && selOrient.value === 'OTRO';
                if (!inpOrientOtro) return;
                inpOrientOtro.style.display = isOtro ? 'block' : 'none';
                if (!isOtro) inpOrientOtro.value = '';
            }

            function ensureModalRoot() {
                let root = document.getElementById('app-modal');
                if (!root) {
                    root = document.createElement('div');
                    root.id = 'app-modal';
                    root.innerHTML = `<div class="modal" aria-hidden="true"><div class="modal-backdrop" data-close></div><div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="app-modal-title"><div class="modal-header"><h3 id="app-modal-title">Faltan datos</h3></div><div class="modal-body"><p>Por favor completa los campos requeridos.</p><ul class="modal-list"></ul></div><div class="modal-actions"><button type="button" class="btn btn-primary" data-close>Aceptar</button></div></div></div>`;
                    document.body.appendChild(root);
                    root.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', closeModal));
                    document.addEventListener('keydown', (ev) => {
                        if (ev.key === 'Escape') closeModal();
                    });
                } else if (!root.querySelector('.modal')) {
                    root.remove();
                    return ensureModalRoot();
                }
                return root;
            }

            function openModal(title, messages) {
                const root = ensureModalRoot();
                const modal = root.querySelector('.modal');
                root.querySelector('#app-modal-title').textContent = title || 'Faltan datos';
                const list = root.querySelector('.modal-list');
                list.innerHTML = '';
                (messages || []).forEach(msg => {
                    const li = document.createElement('li');
                    li.textContent = msg;
                    list.appendChild(li);
                });
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                const root = document.getElementById('app-modal');
                if (!root) return;
                const modal = root.querySelector('.modal');
                if (!modal) return;
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            }

            function handleSubmitGee(ev) {
                if (!frm) return;
                if (modeSel.value !== 'gee') return;
                ev.preventDefault();
                ev.stopPropagation();
                ev.stopImmediatePropagation();
                const errors = [],
                    desdeVal = inpDesde?.value?.trim(),
                    hastaVal = inpHasta?.value?.trim();
                if (!desdeVal || !hastaVal) errors.push('Indica el rango de fechas (Desde y Hasta).');
                if (!inpTitulo?.value?.trim()) errors.push('Ingresa el título de la charla.');
                let orientOK = false;
                if (selOrient && selOrient.value) {
                    orientOK = selOrient.value !== 'OTRO' ? true : !!inpOrientOtro?.value?.trim();
                }
                if (!orientOK) errors.push('Selecciona el orientador responsable (o escribe el nombre si elegiste “Otro”).');
                if (errors.length) {
                    openModal('Faltan datos', errors);
                    return false;
                }
                if (typeof frm.checkValidity === 'function' && !frm.checkValidity()) {
                    if (typeof frm.reportValidity === 'function') frm.reportValidity();
                    return false;
                }
                frm.removeEventListener('submit', handleSubmitGee, true);
                frm.submit();
                return true;
            }
            modeSel?.addEventListener('change', syncMode);
            chkExpo?.addEventListener('change', syncExpositor);
            exclNA?.addEventListener('change', syncExclusive);
            selOrient?.addEventListener('change', syncOrient);
            frm?.addEventListener('submit', handleSubmitGee, true);
            syncMode();
            syncOrient();