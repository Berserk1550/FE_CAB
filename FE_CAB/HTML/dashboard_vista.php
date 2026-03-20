<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio de herramientas Fondo Emprender SENA</title>
  <link rel="stylesheet" href="statics/css/componentes_estilos_dashboard.css" />
  <link rel="icon" type="image/png" href="componentes/img/favicon.ico" />
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
</head>

<body>

  <header class="encabezado-sena">
    <!-- Izquierda: logo -->
    <div class="encabezado-logo-titulo">
      <a href="dashboard" class="encabezado-logo-link" title="Ir al panel de control">
        <img src="componentes/img/logoFondoEmprender.svg" alt="Fondo Emprender" class="encabezado-logo">
      </a>
    </div>

    <!-- Derecha: medalla + menú de perfil -->
    <nav class="nav-dashboard" aria-label="Acciones de usuario">
      <!-- Medalla -->
      <div class="dropdown-medalla">
        <button class="btn-medalla" type="button" aria-label="Ver información del programa">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#39a900" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M9 3h6l3 7l-6 2l-6 -2z" />
            <path d="M12 12l-3 -9" />
            <path d="M15 11l-3 -8" />
            <path d="M12 19.5l-3 1.5l.5 -3.5l-2 -2l3 -.5l1.5 -3l1.5 3l3 .5l-2 2l.5 3.5z" />
          </svg>
        </button>

        <span class="texto-guardado" style="display:none;">
          ANÁLISIS Y DESARROLLO EN SOFTWARE | 2825817
        </span>

        <div class="tooltip-medalla" id="tooltip-medalla" aria-hidden="true"></div>
      </div>

      <!-- Botón Perfil -->
      <div class="dropdown">
        <button class="dropdown-btn" type="button">
          Perfil
        </button>
        <div class="dropdown-content">
          <a href="servicios/php_Login/editar_usuario">Editar usuario</a>
          <a href="servicios/php/cerrar_sesion">Cerrar sesión</a>
        </div>
      </div>
    </nav>
  </header>

  <div class="dashboard-contenedor">
    <div class="dashboard-header">
      <h2><b>Herramientas ideacion - FONDO EMPRENDER SENA</b></h2>
      <span class="dashboard-titulo">
        Hola,
        <strong><?php echo htmlspecialchars(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? '')); ?></strong>
      </span>
    </div>

    <div class="dashboard-manual">
      <strong>Uso del aplicativo</strong>
      <ul>
        <li>Este panel te guía paso a paso a través de las <b>herramientas de ideación</b> del Fondo Emprender.</li>
        <li>Cada fase debe ser <b>completada en orden</b>. Al finalizar una, se habilitará la siguiente automáticamente.</li>
        <li>Las fases completadas se pueden realizar nuevamente. Si deseas añadir otra respuesta, el sistema te consultará antes de continuar.</li>
        <li>La plataforma guarda tu progreso y lo asocia con tu usuario registrado.</li>
        <li>Mantén un <b>lenguaje claro y profesional</b> en cada herramienta.</li>
        <li>Para dudas de redacción/visual, puedes consultar la guía de identidad SENA.</li>
      </ul>
    </div>

    <div class="ruta-header" style="text-align:center;margin:14px 0 8px;">
      <h3>Ruta de Herramientas de Ideación</h3>
      <p>Fase actual: <span id="faseActivaTxt">—</span></p>
    </div>

    <!-- ======== BLOQUE DE RUTA (PNG + GUÍAS SVG INVISIBLES) ======== -->
    <div class="ruta-standalone-card">
      <div class="ruta-standalone-wrap">
        <svg id="rutaCarreteraSVG" viewBox="0 0 1920 619" preserveAspectRatio="xMidYMid meet">
          <!-- PNG de fondo -->
          <image href="componentes/img/ruta_emprendedora_1920x968.png"
            x="0" y="0" width="1920" height="619" preserveAspectRatio="xMidYMid meet" />

          <!-- Guías invisibles -->
          <path id="guide-p01" d="" fill="none" stroke="none"></path>
          <path id="guide-p12" d="" fill="none" stroke="none"></path>
          <path id="guide-p2e" d="" fill="none" stroke="none"></path>

          <!-- Nodos opcionales -->
          <g id="nodes-circles"></g>
          <g id="nodes-labels" style="pointer-events:none"></g>

          <!-- Meta -->
          <g id="meta" transform="">
            <circle r="32" fill="#fbbf24"></circle>
            <text x="0" y="8" text-anchor="middle" font-size="22" style="font-family:'Work Sans';">🏁</text>
          </g>

          <!-- Coche VERDE -->
          <g id="car" transform="" style="pointer-events:none">
            <g transform="scale(1.25)">
              <ellipse cx="0" cy="20" rx="18" ry="5" fill="rgba(0,0,0,.18)"></ellipse>
              <g id="car-body" transform="translate(0,-4)">
                <rect x="-24" y="-16" width="48" height="20" rx="7" fill="#22c55e"></rect>
                <rect x="-16" y="-28" width="32" height="12" rx="3" fill="#15803d"></rect>
                <path d="M -12 -16 L -12 -24 L 12 -24 L 15 -16 Z" fill="#bbf7d0"></path>
              </g>
            </g>
          </g>
        </svg>

        <div id="finishMsg" class="finish-toast" role="status" aria-live="polite">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trophy">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M8 21l8 0" />
            <path d="M12 17l0 4" />
            <path d="M7 4l10 0" />
            <path d="M17 4v8a5 5 0 0 1 -10 0v-8" />
            <path d="M5 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
            <path d="M19 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
          </svg> ¡Listo! Completaste todas las herramientas de ideación.
        </div>

        <div class="ruta-legend">
          <span><i class="dot dot-green"></i>Completada</span>
          <span><i class="dot dot-yellow"></i>Activa</span>
          <span><i class="dot dot-gray"></i>Bloqueada</span>
        </div>
      </div>
    </div>
    <!-- ======== /BLOQUE DE RUTA ======== -->

    <fieldset class="grupo-seccion" id="grupo-ideacion">
      <legend class="titulo-seccion"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-brain">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M15.5 13a3.5 3.5 0 0 0 -3.5 3.5v1a3.5 3.5 0 0 0 7 0v-1.8" />
          <path d="M8.5 13a3.5 3.5 0 0 1 3.5 3.5v1a3.5 3.5 0 0 1 -7 0v-1.8" />
          <path d="M17.5 16a3.5 3.5 0 0 0 0 -7h-.5" />
          <path d="M19 9.3v-2.8a3.5 3.5 0 0 0 -7 0" />
          <path d="M6.5 16a3.5 3.5 0 0 1 0 -7h.5" />
          <path d="M5 9.3v-2.8a3.5 3.5 0 0 1 7 0v10" style="align-items:center;" />
        </svg> Herramientas de Ideación</legend>
      <div class="dashboard-tarjetas">
        <?php
        foreach ($fases as $num => $fase) {
          $icono = $fase['icono'];
          $descripcion = $fase['descripcion'];
          $completada = !empty($fases_completadas[$num]);
          $bloqueada  = ($num > 1 && empty($fases_completadas[$num - 1]));

          // Obtener el nombre base del archivo
          $clave_herramienta = pathinfo($fase['url'], PATHINFO_FILENAME);

          // Si el archivo es "main" o "index", usamos el nombre de la carpeta anterior
          if ($clave_herramienta === 'main' || $clave_herramienta === 'index') {
            $partes = explode('/', $fase['url']);
            $clave_herramienta = $partes[count($partes) - 2]; // carpeta anterior
          }

          // Normalizar formato
          $clave_herramienta = strtolower(trim($clave_herramienta));
          $clave_herramienta = str_replace('-', '_', $clave_herramienta);



          // preparar variable segura para el feedback
          $comentarioMostrar = null;
          $fechaMostrar = null;

          if (isset($feedbacks[$clave_herramienta])) {
            $fb = $feedbacks[$clave_herramienta];

            // Caso esperado: array asociativo con keys 'comentario' y 'fecha'
            if (is_array($fb) && array_key_exists('comentario', $fb)) {
              $comentarioMostrar = trim((string) $fb['comentario']);
              $fechaMostrar = $fb['fecha'] ?? null;
            }
            // Por si acaso se guardó como array indexado (varios rows)
            elseif (is_array($fb) && isset($fb[0]) && is_array($fb[0]) && isset($fb[0]['comentario'])) {
              $comentarioMostrar = trim((string) $fb[0]['comentario']);
              $fechaMostrar = $fb[0]['fecha'] ?? null;
            }
            // Por si acaso se guardó solo como string
            elseif (is_string($fb) && $fb !== '') {
              $comentarioMostrar = $fb;
            }
          }

          echo "<div class='tarjeta-wrapper'>";

          if ($completada) {
            echo "<a class='tarjeta-interactiva fase-completada' href='{$fase['url']}' id='fase-$num' data-fase='{$num}' data-url='{$fase['url']}'>
                    <div class='tarjeta-icono'>{$icono}</div>
                    <div class='tarjeta-titulo'>{$fase['nombre']}</div>
                    <div class='desc'>{$descripcion}</div>
                    <div class='tarjeta-desc'><svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon icon-tabler icons-tabler-outline icon-tabler-check'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M5 12l5 5l10 -10' /></svg> Completada </div>
                  </a>";
          } elseif ($bloqueada) {
            echo "<div class='tarjeta-bloqueada' id='fase-$num'>
                    <div class='tarjeta-icono'>{$icono}</div>
                    <div class='tarjeta-titulo'>{$fase['nombre']}</div>
                    <div class='desc'>{$descripcion}</div>
                    <div class='tarjeta-desc'>Fase bloqueada. Completa la anterior. 🔒</div>
                  </div>";
          } else {
            echo "<a class='tarjeta-interactiva fase-activa' href='{$fase['url']}' id='fase-$num'>
                    <div class='tarjeta-icono'>{$icono}</div>
                    <div class='tarjeta-titulo'>{$fase['nombre']}</div>
                    <div class='desc'>{$descripcion}</div>
                    <div class='tarjeta-desc'>Haz clic en la tarjeta para comenzar</div>
                  </a>";
          }

          // Mostrar el feedback solo si tenemos texto
          if (!empty($comentarioMostrar)) {
            $safeComentario = nl2br(htmlspecialchars($comentarioMostrar, ENT_QUOTES, 'UTF-8'));
            $safeFecha = $fechaMostrar ? htmlspecialchars($fechaMostrar, ENT_QUOTES, 'UTF-8') : '';

            echo '<div class="tarjeta-feedback">
          <strong>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" transform="rotate(0 0 0)">
              <path d="M6.90723 17.4416C7.2146 17.2178 7.64815 17.2597 7.90723 17.549C8.18325 17.8576 8.15703 18.3314 7.84863 18.6076L3.75 22.2775C3.52968 22.4747 3.21432 22.524 2.94434 22.4035C2.67426 22.2828 2.50003 22.0147 2.5 21.7189V18.049C2.5 17.6348 2.83579 17.299 3.25 17.299C3.66421 17.299 4 17.6348 4 18.049V20.0402L6.84766 17.4904L6.90723 17.4416Z" fill="#343C54"/>
              <path opacity="0.4" d="M19.25 3.75C20.4926 3.75 21.5 4.75736 21.5 6V16.5488C21.4998 17.7913 20.4925 18.7988 19.25 18.7988H7.63477L7.84863 18.6074C8.15695 18.3312 8.18322 17.8574 7.90723 17.5488C7.74873 17.3719 7.52492 17.2884 7.30469 17.3008C7.31924 17.2999 7.33387 17.2988 7.34863 17.2988H19.25C19.6641 17.2988 19.9998 16.9628 20 16.5488V6C20 5.58579 19.6642 5.25 19.25 5.25H4.75C4.33579 5.25 4 5.58579 4 6V18.0488C3.99991 17.6347 3.66416 17.2988 3.25 17.2988C2.83584 17.2988 2.50009 17.6347 2.5 18.0488V6C2.5 4.75736 3.50736 3.75 4.75 3.75H19.25Z" fill="#343C54"/>
            </svg>
            Feedback del orientador:
          </strong><br>
          <p>' . $safeComentario . '</p>';

            if ($safeFecha)
              echo '<small>' . $safeFecha . '</small>';

            echo '</div>';
          }


          echo "</div>"; // cerrar wrapper
        }

        $pitchDesbloqueado = !empty($fases_completadas[4]); // Solo si completó fase 4, osea lean_canvas

        if ($pitchDesbloqueado) {
          echo "
          
    <a class='tarjeta-interactiva fase-activa' href='herramientas_ideacion/herramientas_pitch/subir_pitch' id='card-pitch'>
        <div class='tarjeta-icono'>
            <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon icon-tabler icons-tabler-outline icon-tabler-microphone-2'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M15 12.9a5 5 0 1 0 -3.902 -3.9' /><path d='M15 12.9l-3.902 -3.899l-7.513 8.584a2 2 0 1 0 2.827 2.83l8.588 -7.515z' /></svg>
        </div>
        <div class='tarjeta-titulo'>Pitch</div>
        <div class='desc'>Presenta tu idea con claridad y seguridad.</div>
        <div class='tarjeta-desc'>Listo para iniciar</div>
    </a>
    ";
        } else {
          echo "
    <div class='tarjeta-bloqueada' id='card-pitch'>
        <div class='tarjeta-icono'>
            🎤
        </div>
        <div class='tarjeta-titulo'>Pitch</div>
        <div class='desc'>Debes completar la Fase 4 (Lean Canvas) para desbloquear este contenido.</div>
        <div class='tarjeta-desc'>🔒 Bloqueado</div>
    </div>
    ";
        }

        ?>
      </div>
    </fieldset>


  </div>

  <script>
  </script>
</body>

</html>