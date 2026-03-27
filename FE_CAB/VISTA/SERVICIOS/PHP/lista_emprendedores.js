//DENTRO DEL 1ER HTML

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


//DENTRO DEL 2DO HTML
    (function() {
      const qs = (s, r = document) => r.querySelector(s);
      const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

      const ordenFiltro = qs('#ordenFiltro');
      const estadoFiltro = qs('#estadoFiltro');
      const tbody = qs('#tbodyEmprendedores');

      const qInput = qs('#q');
      const btnBuscar = qs('#btnBuscar');

      const kpiFechaVal = qs('#kpiFechaVal');
      const fechaConteo = qs('#fechaConteo');
      const btnBorrar = qs('#btnBorrarFecha');

      /* —— Botón "Volver" flotante & pegado al contenedor —— */
      const cont = qs('.contenedor');
      const volver = qs('.volver');

      function positionBackBtn() {
        if (!cont || !volver) return;
        const contRect = cont.getBoundingClientRect();
        const shouldStick = window.scrollY > (cont.offsetTop - 8);
        if (shouldStick) {
          volver.classList.add('is-stuck');
          volver.style.top = '12px';
          volver.style.left = '12px';
        } else {
          volver.classList.remove('is-stuck');
          volver.style.top = (contRect.top + 12) + 'px';
          volver.style.left = (contRect.left + 12) + 'px';
        }
      }
      positionBackBtn();
      window.addEventListener('scroll', positionBackBtn, {
        passive: true
      });
      window.addEventListener('resize', positionBackBtn);

      /* —— Habilitar acceso (modal existente) —— */
      const modal = qs('#modalConfirmar');
      const msgEl = qs('#textoConfirmacion');
      const btnConf = qs('#confirmarBtn');
      const btnCanc = qs('#cancelarBtn');
      const formH = qs('#formHabilitar');
      const inputNum = qs('#numeroHidden');
      let currentNum = null;

      function openModal(nombre, numero) {
        currentNum = numero || '';
        msgEl.textContent = `¿Estás seguro de habilitar el acceso al panel para ${nombre}?`;
        modal.classList.add('modal--show');
        document.body.style.overflow = 'hidden';
        setTimeout(() => btnConf.focus(), 10);
      }

      function closeModal() {
        modal.classList.remove('modal--show');
        document.body.style.overflow = '';
        currentNum = null;
      }

      document.addEventListener('click', e => {
        const b = e.target.closest('.btn-habilitar');
        if (!b) return;
        e.preventDefault();
        openModal(b.dataset.nombre || 'este emprendedor', b.dataset.numero || '');
      });

      if (btnConf) {
        btnConf.addEventListener('click', () => {
          if (!currentNum) return closeModal();
          inputNum.value = currentNum;
          if (typeof formH.requestSubmit === 'function') formH.requestSubmit();
          else formH.submit();
        });
      }

      if (btnCanc) {
        btnCanc.addEventListener('click', closeModal);
      }

      if (modal) {
        modal.addEventListener('click', e => {
          if (e.target === modal) closeModal();
        });
      }

      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal && modal.classList.contains('modal--show')) {
          closeModal();
        }
      });

      /* —— Paginador: mantener orden, búsqueda y fecha en los enlaces —— */
      function syncPaginatorExtras() {
        const fecha = new URL(location.href).searchParams.get('fecha') || '';
        const orden = ordenFiltro ? ordenFiltro.value : 'recientes';
        const q = qInput ? qInput.value : '';
        qsa('.paginacion a.page-btn').forEach(a => {
          try {
            const u = new URL(a.getAttribute('href'), location.href);
            if (fecha) u.searchParams.set('fecha', fecha);
            else u.searchParams.delete('fecha');
            if (orden) u.searchParams.set('orden', orden);
            if (q) u.searchParams.set('q', q);
            else u.searchParams.delete('q');
            a.setAttribute('href', u.toString());
          } catch (_) {}
        });
      }
      syncPaginatorExtras();

      /* —— Orden —— */
      if (ordenFiltro) {
        ordenFiltro.addEventListener('change', () => {
          const u = new URL(location.href);
          u.searchParams.set('orden', ordenFiltro.value || 'recientes');
          u.searchParams.set('page', '1');
          location.assign(u.toString());
        });
      }

      /* —— Buscador —— */
      function doSearch() {
        const u = new URL(location.href);
        const val = qInput ? qInput.value.trim() : '';
        if (val) u.searchParams.set('q', val);
        else u.searchParams.delete('q');
        u.searchParams.set('page', '1');
        location.assign(u.toString());
      }

      if (btnBuscar) {
        btnBuscar.addEventListener('click', doSearch);
      }

      if (qInput) {
        qInput.addEventListener('keydown', e => {
          if (e.key === 'Enter') doSearch();
        });
      }

      /* —— KPI por fecha + marcado de filas —— */
      let selectedDate = '';

      function markRowsByDate(dateStr) {
        selectedDate = (dateStr || '').trim();
        const trs = tbody ? [...tbody.querySelectorAll('tr')] : [];
        let matched = 0;
        trs.forEach(tr => {
          tr.classList.remove('tr-mark', 'tr-nonmatch');
          const f = tr.dataset.fecha || '';
          if (!selectedDate) return;
          if (f === selectedDate) {
            tr.classList.add('tr-mark');
            matched++;
          } else tr.classList.add('tr-nonmatch');
        });
        const card = kpiFechaVal ? kpiFechaVal.closest('.kpi') : null;
        if (card) {
          card.classList.remove('kpi--ok', 'kpi--zero');
          if (selectedDate) card.classList.add(matched > 0 ? 'kpi--ok' : 'kpi--zero');
        }
        syncPaginatorExtras();
      }

      async function contarPorFecha(fecha) {
        const f = (fecha || (fechaConteo ? fechaConteo.value : '') || '').trim();
        if (!f) {
          if (kpiFechaVal) kpiFechaVal.textContent = '—';
          if (btnBorrar) btnBorrar.style.display = 'none';
          markRowsByDate('');
          const u = new URL(location.href);
          u.searchParams.delete('fecha');
          history.replaceState(null, '', u);
          syncPaginatorExtras();
          return;
        }
        const u = new URL(location.href);
        u.searchParams.set('ajax', '1');
        u.searchParams.set('action', 'countByDate');
        u.searchParams.set('fecha', f);
        const res = await fetch(u.toString(), {
          headers: {
            'Accept': 'application/json'
          }
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.ok) {
          if (kpiFechaVal) kpiFechaVal.textContent = String(data.count);
          if (btnBorrar) btnBorrar.style.display = '';
          markRowsByDate(f);
          const u2 = new URL(location.href);
          u2.searchParams.set('fecha', f);
          history.replaceState(null, '', u2);
          syncPaginatorExtras();
        }
      }

      if (fechaConteo) {
        fechaConteo.addEventListener('change', () => contarPorFecha(fechaConteo.value));
      }

      if (btnBorrar) {
        btnBorrar.addEventListener('click', () => {
          if (fechaConteo) fechaConteo.value = '';
          contarPorFecha('');
        });
      }

      (function initFecha() {
        const f = new URL(location.href).searchParams.get('fecha');
        if (f && /^\d{4}-\d{2}-\d{2}$/.test(f)) {
          if (fechaConteo) fechaConteo.value = f;
          contarPorFecha(f);
        }
      })();

      /* —— Filtro Estado (visual) —— */
      if (estadoFiltro) {
        estadoFiltro.addEventListener('change', () => {
          const val = estadoFiltro.value;
          qsa('#tbodyEmprendedores tr').forEach(tr => {
            const faseTxt = tr.querySelector('[data-fase]')?.dataset.fase?.toLowerCase() || '';
            let show = true;
            if (val === 'completados') show = (faseTxt !== 'sin avance');
            else if (val === 'no_completados') show = (faseTxt === 'sin avance');
            tr.style.display = show ? '' : 'none';
          });
        });
      }
    })();

    /* —— Reasignación (SEPARADA Y CORREGIDA) —— */
    (function() {
      const modalRe = document.getElementById('modalReasignar');
      const selO = document.getElementById('selOrientador');
      const btnReGo = document.getElementById('reasignarGo');
      const btnReCa = document.getElementById('reasignarCancel');
      const btnReCl = document.getElementById('reasignarClose');
      const txtRe = document.getElementById('reasignarTxt');

      // Validar que los elementos existan
      if (!modalRe || !selO || !btnReGo || !btnReCa || !btnReCl || !txtRe) {
        console.warn('Modal de reasignación: elementos no encontrados');
        return;
      }

      let reasignCache = null;
      let currentNumeroForReassign = null;

      function openReModal(nombre, numero) {
        currentNumeroForReassign = numero || '';
        txtRe.innerHTML = `Selecciona el nuevo orientador para <strong>${nombre}</strong>.`;
        modalRe.classList.add('modal--show');
        modalRe.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        loadOrientadores();
        setTimeout(() => selO.focus(), 300);
      }

      function closeReModal() {
        modalRe.classList.remove('modal--show');
        modalRe.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        currentNumeroForReassign = null;
        selO.value = '';
      }

      async function loadOrientadores() {
        if (reasignCache && reasignCache.length > 0) {
          fillSelect();
          return;
        }

        selO.innerHTML = '<option value="">Cargando orientadores...</option>';
        selO.disabled = true;

        try {
          const u = new URL(location.href);
          u.searchParams.set('ajax', '1');
          u.searchParams.set('action', 'orientadores');

          const res = await fetch(u.toString(), {
            headers: {
              'Accept': 'application/json'
            }
          });

          if (!res.ok) throw new Error(`HTTP ${res.status}`);

          const data = await res.json();

          if (data.ok && Array.isArray(data.orientadores)) {
            reasignCache = data.orientadores;
            fillSelect();
          } else {
            throw new Error('Formato de respuesta inválido');
          }
        } catch (err) {
          console.error('Error cargando orientadores:', err);
          selO.innerHTML = '<option value="">Error al cargar. Intenta de nuevo.</option>';
          setTimeout(() => closeReModal(), 2000);
        }
      }

      function fillSelect() {
        selO.innerHTML = '';
        selO.disabled = false;

        const op0 = document.createElement('option');
        op0.value = '';
        op0.textContent = 'Selecciona un orientador…';
        op0.disabled = true;
        op0.selected = true;
        selO.appendChild(op0);

        if (reasignCache && reasignCache.length > 0) {
          reasignCache.forEach(o => {
            const op = document.createElement('option');
            op.value = String(o.id);
            op.textContent = o.label || `${o.nombre} - ${o.centro}`;
            selO.appendChild(op);
          });
        } else {
          const opEmpty = document.createElement('option');
          opEmpty.value = '';
          opEmpty.textContent = 'No hay orientadores disponibles';
          opEmpty.disabled = true;
          selO.appendChild(opEmpty);
        }
      }

      async function doReassign() {
        const oid = selO.value.trim();

        if (!currentNumeroForReassign || !oid) {
          alert('Por favor selecciona un orientador');
          return;
        }

        btnReGo.disabled = true;
        btnReGo.textContent = 'Reasignando...';

        try {
          const u = new URL(location.href);
          u.searchParams.set('ajax', '1');
          u.searchParams.set('action', 'reasignar');

          const fd = new FormData();
          fd.append('numero_id', currentNumeroForReassign);
          fd.append('orientador_id', oid);

          const res = await fetch(u.toString(), {
            method: 'POST',
            body: fd,
            headers: {
              'Accept': 'application/json'
            }
          });

          const data = await res.json();

          if (data.ok) {
            if (typeof window.showNotice === 'function') {
              window.showNotice('success', 'Reasignado',
                `Emprendedor reasignado a: ${data.orientador || 'nuevo orientador'}`);
            }
            setTimeout(() => location.reload(), 800);
          } else {
            throw new Error(data.error || 'Error desconocido');
          }
        } catch (err) {
          console.error('Error en reasignación:', err);

          if (typeof window.showNotice === 'function') {
            window.showNotice('error', 'Error', 'No se pudo reasignar: ' + err.message);
          } else {
            alert('No se pudo reasignar: ' + err.message);
          }

          btnReGo.disabled = false;
          btnReGo.textContent = 'Reasignar';
        }
      }

      // Abrir modal al hacer clic en botones "Reasignar"
      document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-reasignar');
        if (!btn) return;
        e.preventDefault();
        openReModal(
          btn.dataset.nombre || 'este emprendedor',
          btn.dataset.numero || ''
        );
      });

      btnReGo.addEventListener('click', doReassign);
      btnReCa.addEventListener('click', closeReModal);
      btnReCl.addEventListener('click', closeReModal);

      modalRe.addEventListener('click', e => {
        if (e.target === modalRe) closeReModal();
      });

      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modalRe.classList.contains('modal--show')) {
          closeReModal();
        }
      });

      selO.addEventListener('keydown', e => {
        if (e.key === 'Enter' && selO.value) {
          e.preventDefault();
          doReassign();
        }
      });
    })();

//ESTE SCRIPT SE ENCUENTRE DENTRO DEL 2DO HTML PERO EN UN BLOQUE DIFERENTE AL PRIMERO
document.addEventListener('DOMContentLoaded', function() {
      const volver = document.querySelector('.volver');
      if (!volver) return;

      const onScroll = () => {
        if (window.scrollY > 80) {
          volver.classList.add('volver--compact');
        } else {
          volver.classList.remove('volver--compact');
        }
      };

      onScroll();
      window.addEventListener('scroll', onScroll);
    });
//TERCER SCRIPT
/* —— ELIMINACIÓN DE EMPRENDEDORES —— */
(function() {
    const confirmModal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const deleteReason = document.getElementById('deleteReason');
    const confirmDelete = document.getElementById('confirmDelete');
    const cancelDelete = document.getElementById('cancelDelete');
    const xClose = document.getElementById('xClose');
    
    // Validar que los elementos existan
    if (!confirmModal || !modalTitle || !modalBody || !deleteReason || !confirmDelete || !cancelDelete) {
        console.warn('Modal de eliminación: elementos no encontrados');
        return;
    }
    
    let currentDeleteId = null;
    let currentDeleteName = '';
    
    // Función para abrir modal de confirmación
    function openDeleteModal(id, nombre) {
        currentDeleteId = id;
        currentDeleteName = nombre;
        
        modalTitle.textContent = 'Confirmar eliminación';
        modalBody.textContent = `¿Estás seguro que quieres eliminar a ${nombre}?`;
        deleteReason.value = '';
        
        confirmModal.style.display = 'flex';
        confirmModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        
        // Enfocar el textarea para el motivo
        setTimeout(() => deleteReason.focus(), 100);
    }
    
    // Función para cerrar modal
    function closeDeleteModal() {
        confirmModal.style.display = 'none';
        confirmModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        currentDeleteId = null;
        currentDeleteName = '';
        deleteReason.value = '';
    }
    
    // Función para realizar la eliminación
    async function performDelete() {
        if (!currentDeleteId) return;
        
        const motivo = deleteReason.value.trim();
        
        // Deshabilitar botón mientras se procesa
        confirmDelete.disabled = true;
        confirmDelete.textContent = 'Eliminando...';
        
        try {
            //const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
            
            const response = await fetch(window.location.href + '?ajax=1&action=eliminar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id: currentDeleteId,
                    motivo: motivo,
                    csrf_token: csrfToken
                })
            });
            
            const data = await response.json();
            
            if (data.ok) {
                // Mostrar notificación de éxito
                showNotice('success', 'Eliminado', `${currentDeleteName} ha sido eliminado correctamente.`);
                
                // Cerrar modal
                closeDeleteModal();
                
                // Recargar la página después de un breve delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
                
            } else {
                throw new Error(data.message || 'Error desconocido al eliminar');
            }
            
        } catch (error) {
            console.error('Error eliminando emprendedor:', error);
            showNotice('error', 'Error', 'No se pudo eliminar: ' + error.message);
            
            // Rehabilitar botón
            confirmDelete.disabled = false;
            confirmDelete.textContent = 'Eliminar';
        }
    }
    
    // Event listeners para abrir modal al hacer clic en botones "Eliminar"
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-eliminar');
        if (!btn) return;
        
        e.preventDefault();
        
        const id = parseInt(btn.dataset.id);
        const nombre = btn.dataset.nombre || 'este emprendedor';
        
        if (id && id > 0) {
            openDeleteModal(id, nombre);
        }
    });
    
    // Event listeners para cerrar modal
    if (cancelDelete) {
        cancelDelete.addEventListener('click', closeDeleteModal);
    }
    
    if (xClose) {
        xClose.addEventListener('click', closeDeleteModal);
    }
    
    // Event listener para confirmar eliminación
    if (confirmDelete) {
        confirmDelete.addEventListener('click', performDelete);
    }
    
    // Cerrar modal al hacer clic fuera de ella
    confirmModal.addEventListener('click', function(e) {
        if (e.target === confirmModal) {
            closeDeleteModal();
        }
    });
    
    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && confirmModal.style.display === 'flex') {
            closeDeleteModal();
        }
    });
    
    // Permitir confirmar con Enter en el textarea
    deleteReason.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            performDelete();
        }
    });
})();

/* —— SISTEMA DE NOTIFICACIONES —— */
window.showNotice = function(type, title, message) {
    const notice = document.getElementById('notice');
    const noticeIco = document.getElementById('noticeIco');
    const noticeTitle = document.getElementById('noticeTitle');
    const noticeBody = document.getElementById('noticeBody');
    const noticeClose = document.getElementById('noticeClose');
    
    if (!notice || !noticeIco || !noticeTitle || !noticeBody) return;
    
    // Configurar contenido según el tipo
    if (type === 'success') {
        notice.className = 'notice show';
        noticeIco.textContent = '✅';
        noticeTitle.textContent = title || 'Éxito';
    } else if (type === 'error') {
        notice.className = 'notice show notice--error';
        noticeIco.textContent = '❌';
        noticeTitle.textContent = title || 'Error';
    }
    
    noticeBody.textContent = message || '';
    
    // Auto-cerrar después de 4 segundos
    setTimeout(() => {
        notice.classList.remove('show');
    }, 4000);
    
    // Event listener para cerrar manualmente
    if (noticeClose) {
        noticeClose.onclick = () => notice.classList.remove('show');
    }
};