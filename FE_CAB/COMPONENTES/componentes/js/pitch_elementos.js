// pitch_handler.js - Sistema mejorado de gestión de pitches con notificaciones

// Sistema de notificaciones
const NotificationSystem = {
  show(type, title, message, duration = 5000) {
    // Eliminar notificación anterior si existe
    const existente = document.querySelector('.notification');
    if (existente) {
      existente.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? '✓' : '✕';
    
    notification.innerHTML = `
      <div class="notification-icon">${icon}</div>
      <div class="notification-content">
        <div class="notification-title">${title}</div>
        <div class="notification-message">${message}</div>
      </div>
      <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-cerrar después del tiempo especificado
    setTimeout(() => {
      if (notification.parentElement) {
        notification.style.animation = 'slideInRight 0.3s ease-out reverse';
        setTimeout(() => notification.remove(), 300);
      }
    }, duration);
  },

  success(title, message, duration) {
    this.show('success', title, message, duration);
  },

  error(title, message, duration) {
    this.show('error', title, message, duration);
  }
};

// Función para pintar el estado del pitch con calificación
function pintarEstadoPitch(datos) {
  const panelEstadoPitch = document.getElementById('estado_pitch');
  if (!panelEstadoPitch) return;
  
  panelEstadoPitch.classList.add('is-visible');

  const estado = datos.estado_eval || 'pendiente';
  const nombre = datos.nombre_archivo_original || 'Archivo enviado';
  const obs = datos.observaciones || '';
  const fecha = datos.fecha_evaluacion || 'Aún sin fecha de evaluación';
  const id = datos.id;
  const calificacion = datos.calificacion;

  const etiquetaEstado = estado
    .replace(/_/g, ' ')
    .replace(/\b\w/g, c => c.toUpperCase());

  // Construir el HTML con calificación si existe
  let htmlCalificacion = '';
  if (calificacion !== null && calificacion !== undefined) {
    htmlCalificacion = `
      <p class="status-calificacion">
        <span>Calificación:</span>
        <span class="status-calificacion-numero">${calificacion}/10</span>
      </p>
    `;
  }

  panelEstadoPitch.innerHTML = `
    <div class="status-top">
      <span class="status-badge status-${estado}">${etiquetaEstado}</span>
      <span class="status-label">Última evaluación</span>
    </div>
    <p class="status-file">${nombre}</p>
    ${htmlCalificacion}
    <p class="status-observaciones ${obs ? '' : 'muted'}">
      ${obs ? obs.replace(/\n/g, '<br>') : 'Tu orientador aún no ha dejado observaciones.'}
    </p>
    <p class="status-footer">Actualizado: ${fecha}</p>
    ${id ? `<button class="btn-delete-pitch btn-eliminar-pitch" data-id="${id}">🗑 Eliminar pitch</button>` : ''}
  `;
}

// Consultar estado del pitch
async function consultarEstadoPitch() {
  try {
    const respuesta = await fetch('../../servicios/php/pitch/estado_pitch', {cache: 'no-store'});
    if (!respuesta.ok) return;
    const datos = await respuesta.json();
    if (datos && datos.ok && datos.data) {
      pintarEstadoPitch(datos.data);
    }
  } catch (e) {
    console.error('Error al consultar estado:', e);
  }
}

// Configurar polling para actualizar estado
setInterval(consultarEstadoPitch, 5000);

// Gestión de eliminación de pitch
document.addEventListener('DOMContentLoaded', () => {
  const panelEstadoPitch = document.getElementById('estado_pitch');
  
  if (panelEstadoPitch) {
    panelEstadoPitch.addEventListener('click', async (evento) => {
      const botonEliminar = evento.target.closest('.btn-delete-pitch');
      if (!botonEliminar) return;

      const id = botonEliminar.dataset.id;
      if (!id) return;

      const confirmar = confirm('¿Seguro que deseas eliminar definitivamente tu pitch? Esta acción no se puede deshacer.');
      if (!confirmar) return;

      botonEliminar.disabled = true;
      botonEliminar.textContent = 'Eliminando…';

      try {
        const respuesta = await fetch('../../servicios/php/pitch/eliminar_pitch', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'id=' + encodeURIComponent(id)
        });

        if (!respuesta.ok) {
          NotificationSystem.error(
            'Error al eliminar',
            'No se pudo eliminar el pitch. Intenta de nuevo.'
          );
          botonEliminar.disabled = false;
          botonEliminar.textContent = '🗑 Eliminar pitch';
          return;
        }

        const datos = await respuesta.json();
        if (datos.ok) {
          panelEstadoPitch.classList.remove('is-visible');
          panelEstadoPitch.innerHTML = `
            <p class="status-observaciones muted">
              Aún no has subido ningún pitch.
            </p>
          `;
          
          // Mostrar notificación de éxito
          NotificationSystem.success(
            'Pitch eliminado',
            'Tu presentación ha sido eliminada correctamente.'
          );
        } else {
          NotificationSystem.error(
            'Error',
            'No se pudo eliminar el pitch. Código: ' + (datos.error || 'desconocido')
          );
          botonEliminar.disabled = false;
          botonEliminar.textContent = '🗑 Eliminar pitch';
        }
      } catch (err) {
        NotificationSystem.error(
          'Error de conexión',
          'No se pudo conectar con el servidor. Verifica tu conexión.'
        );
        botonEliminar.disabled = false;
        botonEliminar.textContent = '🗑 Eliminar pitch';
      }
    });
  }

  // Gestión del selector de archivos
  const inputArchivoPitch = document.getElementById('archivo_pitch');
  const textoNombreArchivo = document.querySelector('.nombre-archivo-seleccionado');
  const botonEnviarPitch = document.querySelector('.btn-enviar-pitch');

  if (inputArchivoPitch && textoNombreArchivo) {
    inputArchivoPitch.addEventListener('change', () => {
      if (inputArchivoPitch.files && inputArchivoPitch.files.length > 0) {
        textoNombreArchivo.textContent = inputArchivoPitch.files[0].name;
      } else {
        textoNombreArchivo.textContent = 'Ningún archivo seleccionado';
      }
    });
  }

  // Gestión del formulario de envío
  const formularioPitch = document.getElementById('formulario_pitch');
  if (formularioPitch && botonEnviarPitch) {
    formularioPitch.addEventListener('submit', () => {
      botonEnviarPitch.disabled = true;
      botonEnviarPitch.textContent = 'Subiendo…';
    });
  }

  // Consultar estado inicial
  consultarEstadoPitch();

  // Verificar si hay mensaje de éxito en la URL
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('upload') === 'success') {
    NotificationSystem.success(
      '¡Pitch subido exitosamente!',
      'Tu presentación ha sido enviada y está pendiente de evaluación.'
    );
    
    // Limpiar URL sin recargar la página
    window.history.replaceState({}, document.title, window.location.pathname);
  }
});