//HAY 2 SCRIPT DENTRO DE ESTE ARCHIVO, ESTE ES EL PRIMER SCRIPT
    (() => {
            // ✅ OPTIMIZADO: Lazy loading inteligente
            const BATCH = 100;        // 100 registros por batch (balance perfecto)
            const YIELD_MS = 0;

            const tb = document.getElementById('tb');
            const count = document.getElementById('count');
            const totalInfo = document.getElementById('total-info');
            const qInput = document.getElementById('q');
            const wrap = document.getElementById('table-wrap');
            const orden = document.getElementById('orden');
            const loadingIndicator = document.getElementById('loading-indicator');

            let loading = false;
            let ended = false;
            let cursor = null;
            let rendered = 0;
            let q = '';
            let sort = orden ? orden.value : 'fecha_desc';

            const fmt = s => String(s ?? '').trim();

            function rowHTML(r) {
                const hasOv = localStorage.getItem(`obs_override_${r.id}`) !== null;
                const dot = hasOv ? ' •' : '';
                return `
                <tr data-id="${r.id}">
                <td class="nowrap">${r.id}</td>
                <td>${fmt(r.nombre)}</td>
                <td class="nowrap">${fmt(r.doc)}</td>
                <td class="nowrap">${fmt(r.celular)}</td>
                <td>${fmt(r.correo)}</td>
                <td class="nowrap">${fmt(r.fecha)}</td>
                <td class="actions">
                    <button class="btn btn-danger btn-eliminar">Eliminar</button>
                    <button class="btn btn-ghost btn-detalle" title="Ver detalle">Ver${dot}</button>
                </td>
                </tr>`;
            }

            async function fetchPage(per = BATCH) {
                if (loading || ended) return 0;
                loading = true;
                loadingIndicator.style.display = 'flex';

                const u = new URL(location.href);
                u.searchParams.set('api', 'list');
                u.searchParams.set('per', String(per));
                u.searchParams.set('sort', sort);
                if (q) u.searchParams.set('q', q);

                if (sort.startsWith('fecha_')) {
                    if (cursor?.after_fecha) u.searchParams.set('after_fecha', cursor.after_fecha);
                    if (cursor?.after_id) u.searchParams.set('after_id', String(cursor.after_id));
                } else {
                    if (cursor?.after_id) u.searchParams.set('after_id', String(cursor.after_id));
                }

                try {
                    const res = await fetch(u.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();
                    if (!data.ok) throw new Error(data.message || 'Error API');

                    const rows = data.rows || [];
                    cursor = data.next || null;
                    ended = !cursor;

                    // ✅ Rendering optimizado
                    const fragment = document.createDocumentFragment();
                    const temp = document.createElement('tbody');
                    temp.innerHTML = rows.map(rowHTML).join('');
                    
                    while (temp.firstChild) {
                        fragment.appendChild(temp.firstChild);
                    }
                    
                    tb.appendChild(fragment);
                    rendered += rows.length;
                    count.textContent = rendered;
                    
                    // Mostrar info de "cargando más..."
                    if (!ended) {
                        totalInfo.textContent = '(scroll para cargar más)';
                    } else {
                        totalInfo.textContent = q ? '(búsqueda completa)' : '(todos cargados)';
                    }

                    return rows.length;
                } catch (err) {
                    console.error('fetchPage error:', err);
                    return 0;
                } finally {
                    loading = false;
                    loadingIndicator.style.display = 'none';
                }
            }

            function reset() {
                tb.innerHTML = '';
                rendered = 0;
                ended = false;
                cursor = null;
                count.textContent = '0';
                totalInfo.textContent = '';
            }

            async function initialFill() {
                await fetchPage(BATCH);
            }

            // ✅ Scroll infinito optimizado con throttle
            let scrolling = false;
            function onScroll() {
                if (scrolling || loading || ended) return;
                scrolling = true;
                
                requestAnimationFrame(() => {
                    const { scrollTop, scrollHeight, clientHeight } = wrap;
                    if (scrollTop + clientHeight >= scrollHeight - 300) {
                        fetchPage(BATCH);
                    }
                    scrolling = false;
                });
            }
            
            wrap.addEventListener('scroll', onScroll, { passive: true });

            // Modal de detalle
            const md = document.getElementById('md');
            const mdTitle = document.getElementById('md-title');
            const mdBody = document.getElementById('md-body');
            const mdX = document.getElementById('md-x');
            const mdCerrar = document.getElementById('md-cerrar');
            const mdGuardar = document.getElementById('md-guardar');
            const mdStatus = document.getElementById('md-status');

            function mdOpen() {
                md.style.display = 'flex';
            }

            function mdCloseFn() {
                md.style.display = 'none';
            }
            mdX.addEventListener('click', mdCloseFn);
            mdCerrar.addEventListener('click', mdCloseFn);
            md.addEventListener('click', e => {
                if (e.target === md) mdCloseFn();
            });

            function esc(s) {
                return String(s ?? '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function row(label, value) {
                return `<div class="detail-grid">
                    <strong class="detail-label">${label}:</strong>
                    <span class="detail-value">${esc(value)}</span>
                </div>`;
            }

            async function openDetail(id) {
                mdOpen();
                mdBody.innerHTML = '<div style="text-align:center; padding:50px"><span class="loading-spinner"></span> Cargando...</div>';

                const u = new URL(location.href);
                u.searchParams.set('api', 'detail');
                u.searchParams.set('id', id);

                try {
                    const res = await fetch(u.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (!data.ok) {
                        mdBody.innerHTML = `<div style="color:var(--danger); text-align:center; padding:50px">${esc(data.message || 'Error')}</div>`;
                        return;
                    }

                    const r = data.row;
                    mdTitle.textContent = `Detalle: ${r.nombres} ${r.apellidos}`;

                    let html = row('ID', r.id) +
                        row('Nombres', r.nombres) +
                        row('Apellidos', r.apellidos) +
                        row('Tipo ID', r.tipo_id || 'N/A') +
                        row('Número ID', r.numero_id) +
                        row('Correo', r.correo) +
                        row('Celular', r.celular) +
                        row('Fecha', r.fecha) +
                        row('Municipio', r.municipio) +
                        row('Departamento', r.departamento);

                    html += `<div style="margin-top:24px">
                        <label style="display:block; margin-bottom:10px; font-weight:700; color:var(--accent); font-size:13px; text-transform:uppercase; letter-spacing:0.5px">Observaciones:</label>
                        <textarea id="md-obs" rows="6" style="width:100%; background:rgba(15,23,42,0.6); border:1.5px solid rgba(255,255,255,0.12); border-radius:14px; padding:16px; color:var(--ink); font-size:14px; resize:vertical; font-weight:500">${esc(r.obs)}</textarea>
                    </div>`;

                    mdBody.innerHTML = html;

                    const ta = document.getElementById('md-obs');
                    const status = mdStatus;
                    const btnSave = mdGuardar;

                    function saveObsOverride(id, txt) {
                        localStorage.setItem(`obs_override_${id}`, txt);
                    }

                    function clearObsOverride(id) {
                        localStorage.removeItem(`obs_override_${id}`);
                    }

                    function showSaved(msg = 'Guardado') {
                        status.textContent = msg;
                        status.style.display = 'inline';
                        status.style.color = 'var(--success)';
                        setTimeout(() => {
                            status.style.display = 'none';
                        }, 1600);
                    }

                    function showCleared() {
                        status.textContent = 'Reestablecido';
                        status.style.display = 'inline';
                        status.style.color = 'var(--ink-muted)';
                        setTimeout(() => {
                            status.style.display = 'none';
                        }, 1200);
                    }

                    btnSave.onclick = () => {
                        const txt = ta.value.trim();
                        if (txt === '' || txt === (r.obs ?? '').trim()) {
                            clearObsOverride(id);
                            showCleared();
                        } else {
                            saveObsOverride(id, txt);
                            showSaved();
                        }
                    };

                    md.addEventListener('keydown', (ev) => {
                        if ((ev.ctrlKey || ev.metaKey) && ev.key.toLowerCase() === 's') {
                            ev.preventDefault();
                            btnSave.click();
                        }
                    }, { once: true });

                } catch (err) {
                    console.error(err);
                    mdBody.innerHTML = `<div style="color:var(--danger); text-align:center; padding:50px">Error de red</div>`;
                }
            }

            // Delegación de eventos
            tb.addEventListener('click', e => {
                const btnDet = e.target.closest('.btn-detalle');
                if (btnDet) {
                    const tr = btnDet.closest('tr');
                    const id = parseInt(tr?.dataset.id || '0', 10);
                    if (id > 0) openDetail(id);
                }
            });

            // ✅ Búsqueda ARREGLADA con debounce
            function debounce(fn, ms) {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), ms);
                };
            }

            const doSearch = debounce(async () => {
                const newQ = qInput.value.trim();
                // Solo buscar si cambió el término
                if (newQ === q) return;
                
                q = newQ;
                reset();
                wrap.scrollTop = 0; // Volver arriba
                await initialFill();
            }, 500); // 500ms debounce

            qInput.addEventListener('input', doSearch);

            // Cambio de orden
            orden?.addEventListener('change', async () => {
                sort = orden.value;
                reset();
                wrap.scrollTop = 0;
                await initialFill();
            });

            // ✅ Arranque optimizado
            initialFill();
        })();

//ESTE ES EL SEGUNDO SCRIPT
    (function() {
            const CSRF = "<?= $CSRF_TOKEN ?>";
            const countEl = document.getElementById('count');

            const modal = document.getElementById('modal');
            const modalTitle = document.getElementById('modal-title');
            const modalText = document.getElementById('modal-text');
            const motivo = document.getElementById('motivo');
            const btnX = document.getElementById('modal-x');
            const btnCancel = document.getElementById('cancelar');
            const btnOK = document.getElementById('confirmar');
            let currentId = null;
            let currentName = '';

            const toast = document.getElementById('toast');

            function showToast(msg, ok = true) {
                toast.textContent = msg;
                toast.style.background = ok ? 'rgba(16, 185, 129, 0.95)' : 'rgba(239, 68, 68, 0.95)';
                toast.style.borderColor = ok ? 'rgba(16, 185, 129, 0.3)' : 'rgba(239, 68, 68, 0.3)';
                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 2800);
            }

            function openModal(id, name) {
                currentId = id;
                currentName = name || '';
                modalTitle.textContent = 'Eliminar: ' + (currentName || ('ID ' + id));
                modalText.textContent = '¿Seguro que deseas eliminar a "' + (currentName || ('ID ' + id)) + '"? Esta acción es permanente y no se puede deshacer.';
                motivo.value = '';
                modal.style.display = 'flex';
            }

            window.openDeleteModal = openModal;

            function closeModal() {
                modal.style.display = 'none';
                currentId = null;
                currentName = '';
            }

            btnX.addEventListener('click', closeModal);
            btnCancel.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });

            document.getElementById('tb').addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-eliminar');
                if (btn) {
                    const tr = btn.closest('tr');
                    const id = parseInt(tr?.dataset.id || '0', 10);
                    const name = tr ? tr.children[1].textContent.trim() : '';
                    if (id > 0) openModal(id, name);
                }
            });

            btnOK.addEventListener('click', async () => {
                if (!currentId) return;
                btnOK.disabled = true;
                btnOK.textContent = 'Eliminando...';
                try {
                    const resp = await fetch(location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'delete',
                            id: currentId,
                            motivo: motivo.value.trim(),
                            csrf_token: CSRF
                        })
                    });
                    const data = await resp.json();
                    if (data.ok) {
                        const tr = document.querySelector('tr[data-id="' + currentId + '"]');
                        if (tr) tr.remove();
                        countEl.textContent = Math.max(0, parseInt(countEl.textContent || '0', 10) - 1);
                        showToast('✓ Eliminado correctamente');
                    } else {
                        showToast(data.message || 'No se pudo eliminar', false);
                    }
                } catch (err) {
                    console.error(err);
                    showToast('Error de red/servidor', false);
                } finally {
                    btnOK.disabled = false;
                    btnOK.textContent = 'Eliminar definitivamente';
                    closeModal();
                }
            });
        })();