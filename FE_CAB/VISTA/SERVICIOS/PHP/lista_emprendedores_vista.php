 /**DENTRO DE IF LINEA 32 */
 
 <!doctype html>
  <html lang="es">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Acceso No Autorizado</title>
    <link rel="stylesheet" href="../../componentes/acceso_noautorizado.css">
  </head>

  <body>
    <div class="auth-modal" aria-hidden="false">
      <div class="auth-card" role="dialog" aria-modal="true" aria-labelledby="authTitle">
        <div class="auth-hdr">
          <div class="auth-ico">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
              <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              <line x1="12" y1="15" x2="12" y2="18" />
            </svg>
          </div>
          <h3 id="authTitle">Acceso No Autorizado</h3>
        </div>
        <div class="auth-body">
          <p>No tienes acceso a esta funcionalidad.</p>
          <p>Debes iniciar sesión con el <strong>rol autorizado</strong> para ver esta página.</p>
          <p class="muted">Te redirigiremos al inicio en <strong id="count">5</strong> segundos.</p>
        </div>
        <div class="auth-actions">
          <a class="btn btn-secondary" href="<?php echo htmlspecialchars($homeUrl, ENT_QUOTES); ?>">Ir al inicio</a>
          <button type="button" class="btn btn-primary" id="goNow">Ir ahora</button>
        </div>
      </div>
    </div>
    <script>
      
    </script>
  </body>

  </html>


/**SEGUNDO HTML */
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lista de Emprendedores</title>
  <link rel="icon" href="../../componentes/img/favicon.ico">
  <link rel="stylesheet" href="../../componentes/tabla_emprendedores.css">
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <style>

  </style>
</head>

<body>
  <!-- Botón volver al panel (reutiliza la clase .volver del CSS) -->
  <div class="volver">
    <a href="panel_orientador" title="Volver al panel">
      <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M14.1085 9.28033C14.4013 8.98744 14.4013 8.51256 14.1085 8.21967C13.8156 7.92678 13.3407 7.92678 13.0478 8.21967L9.79779 11.4697C9.5049 11.7626 9.5049 12.2374 9.79779 12.5303L13.0478 15.7803C13.3407 16.0732 13.8156 16.0732 14.1085 15.7803C14.4013 15.4874 14.4013 15.0126 14.1085 14.7197L11.3888 12L14.1085 9.28033Z" fill="currentColor" />
        <path fill-rule="evenodd" clip-rule="evenodd" d="M12.3281 2C6.80528 2 2.32812 6.47715 2.32812 12C2.32812 17.5228 6.80528 22 12.3281 22C17.851 22 22.3281 17.5228 22.3281 12C22.3281 6.47715 17.851 2 12.3281 2ZM3.82812 12C3.82812 7.30558 7.6337 3.5 12.3281 3.5C17.0225 3.5 20.8281 7.30558 20.8281 12C20.8281 16.6944 17.0225 20.5 12.3281 20.5C7.6337 20.5 3.82812 16.6944 3.82812 12Z" fill="currentColor" />
      </svg>
      <span class="txt">Volver al panel</span>
    </a>
  </div>

  <div class="contenedor">
    <h2 class="header-lista">
      <!-- Icono: decorativo, por eso aria-hidden -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
        width="28" height="28" aria-hidden="true" focusable="false">
        <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2"
          fill="none" stroke-linecap="round" stroke-linejoin="round" />
      </svg>

      <span class="header-lista__txt">Lista de emprendedores</span>
    </h2>


    <div class="kpi-grid" id="kpiGrid">
      <div class="kpi kpi--hoy">
        <div class="kpi-head">
          <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-down">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M12.5 21h-6.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5" />
              <path d="M19 16v6" />
              <path d="M22 19l-3 3l-3 -3" />
              <path d="M16 3v4" />
              <path d="M8 3v4" />
              <path d="M4 11h16" />
            </svg></div><span class="kpi-title">Hoy</span>
        </div>
        <div class="kpi-value" id="kpiHoy"><?= (int)$hoyCount ?></div>
        <span class="kpi-sub badge-time">Registrados <?= date('Y-m-d') ?></span>
      </div>
      <div class="kpi kpi--7d">
        <div class="kpi-head">
          <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-square-rounded-number-7">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M10 8h4l-2 8" />
              <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" />
            </svg></div><span class="kpi-title">Últimos 7 días</span>
        </div>
        <div class="kpi-value" id="kpi7"><?= (int)$ult7Count ?></div>
        <span class="kpi-sub">Incluye hoy</span>
      </div>
      <div class="kpi kpi--total">
        <div class="kpi-head">
          <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-users">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
              <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
            </svg></div><span class="kpi-title">Total asignados</span>
        </div>
        <div class="kpi-value" id="kpiTotal"><?= (int)$total ?></div>
        <span class="kpi-sub">A este orientador</span>
      </div>
      <div class="kpi kpi--fecha" id="kpiFechaCard">
        <div class="kpi-head">
          <div class="kpi-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-event">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
              <path d="M16 3l0 4" />
              <path d="M8 3l0 4" />
              <path d="M4 11l16 0" />
              <path d="M8 15h2v2h-2z" />
            </svg>
          </div><span class="kpi-title">Por fecha</span>
        </div>
        <div class="kpi-value" id="kpiFechaVal">—</div>
        <div class="filtros-inline">
          <div class="field">
            <label for="fechaConteo">Elegir fecha</label>
            <!-- // Al armar enlaces de paginación / inputs: -->
            <input type="date" id="fechaConteo" max="<?= date('Y-m-d') ?>"
              value="<?= isset($_GET['fecha']) ? htmlspecialchars((string)$_GET['fecha'], ENT_QUOTES, 'UTF-8') : '' ?>">

          </div>
          <button class="btn-ghost" id="btnBorrarFecha" type="button" style="display:none">Borrar</button>
        </div>
      </div>
    </div>

    <div class="filtros-emprendedores-barra">
      <span class="filtros-barra-titulo">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-zoom">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
          <path d="M21 21l-6 -6" />
        </svg> Filtrar y ordenar emprendedores</span>

      <div class="filtro-barra-campo">
        <label for="ordenFiltro">Ordenar por:</label>
        <select id="ordenFiltro">
          <option value="recientes" <?= $orden === 'recientes' ? 'selected' : '' ?>>Más recientes primero</option>
          <option value="antiguos" <?= $orden === 'antiguos' ? 'selected' : '' ?>>Más antiguos primero</option>
          <option value="alfabetico" <?= $orden === 'alfabetico' ? 'selected' : '' ?>>A-Z</option>
          <option value="alfabetico_desc" <?= $orden === 'alfabetico_desc' ? 'selected' : '' ?>>Z-A</option>
        </select>
      </div>

      <div class="filtro-barra-campo">
        <label for="estadoFiltro">Estado:</label>
        <select id="estadoFiltro">
          <option value="todos" selected>Todos</option>
          <option value="completados">Completados</option>
          <option value="no_completados">No completados</option>
        </select>
      </div>

      <div class="filtro-barra-campo grow">
        <label for="q">Buscar</label>
        <div style="display:flex;gap:8px;align-items:center">
          <input type="search" id="q" placeholder="Buscar por nombre, documento, correo o celular" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
          <button id="btnBuscar" class="btn btn--success" type="button" style="white-space:nowrap">Buscar</button>
        </div>
      </div>
    </div>

    <div class="tabla-scroll">
      <table class="tabla-emprendedores" id="tablaEmprendedores">
        <thead>
          <tr>
            <th>Nombres</th>
            <th>Apellidos</th>
            <th># de Documento</th>
            <th>Correo</th>
            <th># de Celular</th>
            <th>Fecha</th>
            <th>Última vez</th>
            <th>Her. Ideacion</th>
            <th>Opciones</th>
            <th>Acceso</th>
          </tr>
        </thead>
        <tbody id="tbodyEmprendedores">
          <?php foreach ($rows as $fila):
            $faseNum    = (int)$fila['ultima_fase'];
            $faseTxt    = $faseNum ? ($fases_totales[$faseNum] ?? 'Sin avance') : 'Sin avance';
            $nombresSan = fix_mojibake_human($fila['nombres']);
            $apellidosSan = fix_mojibake_human($fila['apellidos']);
            $correoSan  = fix_mojibake_human($fila['correo']);
            $nombreC    = trim($nombresSan . ' ' . $apellidosSan);
            $numeroAttr = htmlspecialchars($fila['numero_id'], ENT_QUOTES, 'UTF-8');
            $fechaSolo  = htmlspecialchars(substr((string)$fila['fecha_registro'], 0, 10), ENT_QUOTES, 'UTF-8');
          ?>
            <tr data-id="<?= (int)$fila['id'] ?>" data-fecha="<?= $fechaSolo ?>">
              <td data-label="Nombres"><?= htmlspecialchars($nombresSan, ENT_QUOTES, 'UTF-8') ?></td>
              <td data-label="Apellidos"><?= htmlspecialchars($apellidosSan, ENT_QUOTES, 'UTF-8') ?></td>
              <td data-label="Número de documento"><?= htmlspecialchars($fila['numero_id'], ENT_QUOTES, 'UTF-8') ?></td>
              <?php
              $to   = $correoSan; // destinatario
              $urlE = outlook_business_compose($to); // siempre office.com
              ?>
              <td data-label="Correo">
                <a class="cell-link" href="<?= htmlspecialchars($urlE, ENT_QUOTES) ?>" target="_blank" rel="noopener">
                  <?= htmlspecialchars($correoSan, ENT_QUOTES, 'UTF-8') ?>
                </a>
              </td>

              <td data-label="Celular">
                <a class="cell-link" href="tel:<?= preg_replace('/\D+/', '', (string)$fila['celular']) ?>">
                  <?= htmlspecialchars($fila['celular'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </td>
              <td data-label="Fecha"><?= $fechaSolo ?></td>
              <td data-label="Última vez">
                <?= $fila['ultima_vez'] ? htmlspecialchars($fila['ultima_vez'], ENT_QUOTES, 'UTF-8') : 'Nunca' ?>
              </td>
              <td data-label="Estado de avance">
                <span class="badge" data-fase="<?= htmlspecialchars($faseTxt, ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars($faseTxt, ENT_QUOTES, 'UTF-8') ?>
                </span>
              </td>
              <td data-label="Desarrollo">
                <div class="row-actions">
                  <a class="btn btn--link"
                    href="ver_progreso?numero_id=<?= htmlspecialchars($fila['numero_id'], ENT_QUOTES, 'UTF-8') ?>">
                    Ver progreso
                  </a>

                  <button type="button" class="btn btn--ghost btn-reasignar"
                    data-numero="<?= $numeroAttr ?>"
                    data-nombre="<?= htmlspecialchars($nombreC, ENT_QUOTES, 'UTF-8') ?>">
                    Reasignar
                  </button>

                  <!-- Botón Eliminar POR FILA -->
                  <button type="button" class="btn btn--ghost btn-eliminar"
                    data-id="<?= (int)$fila['id'] ?>"
                    data-nombre="<?= htmlspecialchars($nombreC, ENT_QUOTES, 'UTF-8') ?>">
                    Eliminar
                  </button>
                </div>
              </td>
              <td data-label="Acceso">
                <?php if ((int)$fila['acceso_panel'] === 1): ?>
                  <span class="badge badge--ok">Habilitado</span>
                <?php else: ?>
                  <button type="button" class="btn btn--success btn-habilitar"
                    data-numero="<?= $numeroAttr ?>"
                    data-nombre="<?= htmlspecialchars($nombreC, ENT_QUOTES, 'UTF-8') ?>">
                    Habilitar acceso
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginación COMPLETA -->
    <div class="paginacion" aria-label="Paginación" id="paginacion">
      <?php if ($page > 1): ?>
        <a class="page-btn" href="<?= page_link($page - 1) ?>" aria-label="Página anterior">‹</a>
      <?php else: ?><span class="page-btn is-disabled">‹</span><?php endif; ?>

      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <?php if ($p == $page): ?>
          <span class="page-btn is-active"><?= $p ?></span>
        <?php else: ?>
          <a class="page-btn" href="<?= page_link($p) ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <a class="page-btn" href="<?= page_link($page + 1) ?>" aria-label="Página siguiente">›</a>
      <?php else: ?><span class="page-btn is-disabled">›</span><?php endif; ?>

      <span class="page-info" id="pageInfo">Página <?= $page ?> de <?= $totalPages ?> · <?= $total ?> registros</span>
    </div>

    <!-- Volver flotante (sin mover de sitio en el DOM) -->
    <!-- <div class="volver"><a href="panel_orientador"><svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M14.1085 9.28033C14.4013 8.98744 14.4013 8.51256 14.1085 8.21967C13.8156 7.92678 13.3407 7.92678 13.0478 8.21967L9.79779 11.4697C9.5049 11.7626 9.5049 12.2374 9.79779 12.5303L13.0478 15.7803C13.3407 16.0732 13.8156 16.0732 14.1085 15.7803C14.4013 15.4874 14.4013 15.0126 14.1085 14.7197L11.3888 12L14.1085 9.28033Z" fill="#f3f3f3ff"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M12.3281 2C6.80528 2 2.32812 6.47715 2.32812 12C2.32812 17.5228 6.80528 22 12.3281 22C17.851 22 22.3281 17.5228 22.3281 12C22.3281 6.47715 17.851 2 12.3281 2ZM3.82812 12C3.82812 7.30558 7.6337 3.5 12.3281 3.5C17.0225 3.5 20.8281 7.30558 20.8281 12C20.8281 16.6944 17.0225 20.5 12.3281 20.5C7.6337 20.5 3.82812 16.6944 3.82812 12Z" fill="#f3f3f3ff"/>
</svg>
 Panel</a></div> -->
  </div>

  <!-- Form y modales existentes -->
  <form id="formHabilitar" method="POST" action="habilitar_dashboard" style="display:none" accept-charset="UTF-8">
    <input type="hidden" name="numero_id" id="numeroHidden">
  </form>

  <div id="modalConfirmar" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-card" role="document">
      <div class="modal-head">
        <div class="modal-title" id="confirmTitulo">Confirmar acción</div>
        <!-- <button class="modal-close" type="button" aria-label="Cerrar" id="modalCloseBtn">×</button> -->
      </div>
      <div class="modal-body">
        <p id="textoConfirmacion">¿Estás seguro?</p>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-pill btn-secondary" id="cancelarBtn">Cancelar</button>
        <button type="button" class="btn-pill btn-primary" id="confirmarBtn">Confirmar</button>
      </div>
    </div>
  </div>

  <div id="modalReasignar" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-card" role="document">
      <div class="modal-head">
        <h3 class="modal-title">Reasignar emprendedor</h3>
        <button class="modal-close" type="button" aria-label="Cerrar" id="reasignarClose">×</button>
      </div>

      <div class="modal-body">
        <p id="reasignarTxt">Selecciona el nuevo orientador.</p>

        <div class="field">
          <label for="selOrientador">Nuevo orientador</label>
          <div class="select-wrap">
            <select id="selOrientador" autocomplete="off" aria-required="true">
              <option value="" disabled selected>Selecciona un orientador...</option>
            </select>
            <span class="sel-caret" aria-hidden="true">▾</span>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-pill btn-secondary" id="reasignarCancel">Cancelar</button>
        <button type="button" class="btn-pill btn-primary" id="reasignarGo">Reasignar</button>
      </div>
    </div>
  </div>
  <!-- <button type="button" class="btn btn--ghost btn-eliminar"
    data-id="<?= (int)$fila['id'] ?>"
    data-nombre="<?= htmlspecialchars($nombreC, ENT_QUOTES, 'UTF-8') ?>">
    Eliminar
  </button>
  </div>
  </td> -->

  <!-- Modal de confirmación (eliminación) -->
  <div id="confirmModal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="confirm-card" role="document">
      <div class="confirm-hdr">
        <div class="confirm-ico"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 7h16" />
            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
            <path d="M10 12l4 4m0 -4l-4 4" />
          </svg></div>
        <div class="confirm-title" id="modalTitle">Confirmar eliminación</div>
        <button type="button" class="confirm-close" id="xClose" aria-label="Cerrar">×</button>
      </div>
      <div class="confirm-body">
        <p id="modalBody">¿Seguro que quieres eliminar este emprendedor?</p>
        <textarea id="deleteReason" class="confirm-textarea" placeholder="Motivo (opcional)"></textarea>
      </div>
      <div class="confirm-actions">
        <button id="cancelDelete" class="btn-pill btn-muted">Cancelar</button>
        <button id="confirmDelete" class="btn-pill btn-danger">Eliminar</button>
      </div>
    </div>
  </div>

  <!-- Modal de notificación -->
  <div id="notice" class="notice" role="dialog" aria-modal="true" aria-live="polite">
    <div class="notice-card">
      <div class="notice-hdr">
        <div class="notice-ico" id="noticeIco">✅</div>
        <div class="notice-title" id="noticeTitle">Listo</div>
        <button type="button" class="notice-close" id="noticeClose" aria-label="Cerrar">×</button>
      </div>
      <div class="notice-body" id="noticeBody">Operación realizada.</div>
    </div>
  </div>

  <script>
  </script>

  <script>
    
  </script>

  <script>

</script>

</body>
</body>

</html>