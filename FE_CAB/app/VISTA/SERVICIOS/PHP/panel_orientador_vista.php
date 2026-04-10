<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel del Orientador</title>
  <link rel="icon" href="../../componentes/img/favicon.ico">
  <!-- Estilos -->
  <link rel="stylesheet" href="../../componentes/estilo_panel_orientador.css">
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
  <header class="encabezado-sena">
    <!-- Logo a la izquierda -->
    <div class="encabezado-logo-titulo">
      <a href="#" class="encabezado-logo-link">
        <img src="../../componentes/img/logoFondoEmprender.svg" alt="Logo SENA" class="encabezado-logo" />
      </a>
    </div>

    <!-- Navegación a la derecha -->
    <nav class="encabezado-nav">
      <div class="dropdown-medalla">
        <button class="btn-medalla" type="button" aria-label="Mostrar nombre del programa">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#39a900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-medal-2">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M9 3h6l3 7l-6 2l-6 -2z" />
            <path d="M12 12l-3 -9" />
            <path d="M15 11l-3 -8" />
            <path d="M12 19.5l-3 1.5l.5 -3.5l-2 -2l3 -.5l1.5 -3l1.5 3l3 .5l-2 2l.5 3.5z" />
          </svg>
        </button>
        <span class="texto-guardado" style="display: none;">ANÁLISIS Y DESARROLLO EN SOFTWARE | 2825817</span>
        <div class="tooltip-medalla" id="tooltip-medalla" aria-hidden="true"></div>
      </div>
      
      <div class="dropdown">
        <button class="dropdown-btn">Perfil</button>
        <div class="dropdown-content">
          <a href="../php_Login/editar_usuario">Editar usuario</a>
          <a href="cerrar_sesion">Cerrar sesión</a>
        </div>
      </div>
    </nav>
  </header>

  <main class="panel-orientador">
    <section class="panel-bienvenida">
      <div class="bienvenida">
        <!-- SOLO cambia esta línea para usar las variables ya normalizadas -->
        <span class="usuario-nombre">
          Hola <strong><?= htmlspecialchars($nombre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
          <?= htmlspecialchars($apellido, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>, en este espacio puedes hacer lo siguiente...
        </span>
      </div>
      <h2><img width="24" height="24" src="https://img.icons8.com/material-outlined/24/1A1A1A/view-file.png" alt="view-file" /> Seguimiento de Emprendedores</h2>
      <p>Desde este panel podrás visualizar y hacer seguimiento a todos los emprendedores registrados.</p>
    </section>

    <!-- 1) Ubicación en Acciones rápidas -->
    <section class="acciones-orientador">
      <ul class="lista-opciones">
        <li>
          <a class="btn-opcion" href="<?=$url_emprendedores?>">
            Ver lista de emprendedores
          </a>
        </li>
        
        <section class="grupo-seccion">
          <legend class="titulo-seccion">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M2 6C2 4.75736 3.00736 3.75 4.25 3.75H8.5C9.2082 3.75 9.87508 4.08344 10.3 4.65L11.65 6.45C11.7916 6.63885 12.0139 6.75 12.25 6.75H19.75C20.9926 6.75 22 7.75736 22 9V18C22 19.2426 20.9926 20.25 19.75 20.25H4.25C3.00736 20.25 2 19.2426 2 18V6ZM4.25 5.25C3.83579 5.25 3.5 5.58579 3.5 6V18C3.5 18.4142 3.83579 18.75 4.25 18.75H19.75C20.1642 18.75 20.5 18.4142 20.5 18V9C20.5 8.58579 20.1642 8.25 19.75 8.25H12.25C11.5418 8.25 10.8749 7.91656 10.45 7.35L9.1 5.55C8.95836 5.36115 8.73607 5.25 8.5 5.25H4.25Z" fill="#323544" />
            </svg>
            Reportes y Exportaciones
          </legend>
          <div class="dashboard-tarjetas">
            <a class="tarjeta-interactiva exportar" href="<?= $url_exportar ?>" target="_blank" alt="Exportar a excel">
              <div class="tarjeta-icono"><img width="40" height="40" src="https://img.icons8.com/puffy/40/1A1A1A/full-inbox.png" alt="full-inbox" /></div>
              <div class="tarjeta-titulo">Exportar a Excel</div>
              <div class="tarjeta-desc">
                Descarga la base completa de emprendedores (solo orientadores).
              </div>
            </a>
          </div>
        </section>
        
        <section class="grupo-seccion">
          <legend class="titulo-seccion">
            <img width="24" height="24" src="https://img.icons8.com/ios/24/1A1A1A/north-direction.png" alt="Modulo QR de enlaces de orientador" /> QR de Orientadores
          </legend>
          <div class="dashboard-tarjetas">
            <a class="tarjeta-interactiva exportar" href="<?=$url_qr ?>" target="_blank">
              <div class="tarjeta-icono">
                <img width="40" height="40" src="https://img.icons8.com/wired/64/1A1A1A/qr-code.png" alt="qr-code" />
              </div>
              <div class="tarjeta-titulo">Mostrar QR de los orientadores</div>
              <div class="tarjeta-desc">
                Visualizar los QR de los orientadores para el formulario de registro de emprendedores.
              </div>
            </a>
          </div>
        </section>
        
        <section class="grupo-seccion">
          <legend class="titulo-seccion">
            <img width="24" height="24" src="https://img.icons8.com/material-outlined/24/1A1A1A/document--v1.png" alt="Hoja de resultados de estadísticas" /> Hoja de resultados
          </legend>
          <div class="dashboard-tarjetas">
            <a class="tarjeta-interactiva hoja-resultados" href="<?= $url_resultados ?>" target="_blank" >
              <div class="tarjeta-icono">
                <img width="40" height="40" src="https://img.icons8.com/small/40/1A1A1A/bar-chart.png" alt="bar-chart" />
              </div>
              <div class="tarjeta-titulo">Hoja de resultados</div>
              <div class="tarjeta-desc">
                Visualizar la hoja de resultados de manera interactiva
              </div>
            </a>
          </div>
        </section>
        
        <section class="grupo-seccion">
          <legend class="titulo-seccion">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M14.7223 12.7585C14.7426 12.3448 14.4237 11.9929 14.01 11.9726C13.5963 11.9522 13.2444 12.2711 13.2241 12.6848L12.9999 17.2415C12.9796 17.6552 13.2985 18.0071 13.7122 18.0274C14.1259 18.0478 14.4778 17.7289 14.4981 17.3152L14.7223 12.7585Z" fill="#323544" />
              <path d="M9.98802 11.9726C9.5743 11.9929 9.25542 12.3448 9.27577 12.7585L9.49993 17.3152C9.52028 17.7289 9.87216 18.0478 10.2859 18.0274C10.6996 18.0071 11.0185 17.6552 10.9981 17.2415L10.774 12.6848C10.7536 12.2711 10.4017 11.9522 9.98802 11.9726Z" fill="#323544" />
              <path fill-rule="evenodd" clip-rule="evenodd" d="M10.249 2C9.00638 2 7.99902 3.00736 7.99902 4.25V5H5.5C4.25736 5 3.25 6.00736 3.25 7.25C3.25 8.28958 3.95503 9.16449 4.91303 9.42267L5.54076 19.8848C5.61205 21.0729 6.59642 22 7.78672 22H16.2113C17.4016 22 18.386 21.0729 18.4573 19.8848L19.085 9.42267C20.043 9.16449 20.748 8.28958 20.748 7.25C20.748 6.00736 19.7407 5 18.498 5H15.999V4.25C15.999 3.00736 14.9917 2 13.749 2H10.249ZM14.499 5V4.25C14.499 3.83579 14.1632 3.5 13.749 3.5H10.249C9.83481 3.5 9.49902 3.83579 9.49902 4.25V5H14.499ZM5.5 6.5C5.08579 6.5 4.75 6.83579 4.75 7.25C4.75 7.66421 5.08579 8 5.5 8H18.498C18.9123 8 19.248 7.66421 19.248 7.25C19.248 6.83579 18.9123 6.5 18.498 6.5H5.5ZM6.42037 9.5H17.5777L16.96 19.7949C16.9362 20.191 16.6081 20.5 16.2113 20.5H7.78672C7.38995 20.5 7.06183 20.191 7.03807 19.7949L6.42037 9.5Z" fill="#323544" />
            </svg>
            Eliminar Emprendedor
          </legend>
          <div class="dashboard-tarjetas">
            <a class="tarjeta-interactiva hoja-resultados" href="<?= $url_eliminar ?>" target="_blank">
              <div class="tarjeta-icono">
                <img width="40" height="40" src="https://img.icons8.com/fluency-systems-regular/40/1A1A1A/delete-user-male.png" alt="Eliminar emprendedor si es necesario" />
              </div>
              <div class="tarjeta-titulo">Eliminar Emprendedor</div>
              <div class="tarjeta-desc">
                Eliminar emprendedor
              </div>
            </a>
          </div>
        </section>

        <!-- Grupo: Correos Institucionales -->
        <section class="grupo-seccion">
          <legend class="titulo-seccion">
            <img width="24" height="24" src="https://img.icons8.com/material-outlined/24/1A1A1A/mailbox-plane.png" alt="Envío de correos" /> Envío de Correos Masivos y Personalizados  
          </legend>
          <div class="dashboard-tarjetas">
            <a class="tarjeta-interactiva" href="<?= $url_masivos ?>">
              <div class="tarjeta-icono"><img width="32" height="32" src="https://img.icons8.com/puffy/32/1A1A1A/send-mass-email.png" alt="send-mass-email" /></div>
              <div class="tarjeta-titulo">Correos Masivos</div>
              <div class="tarjeta-desc">
                Envía un mensaje igual a varios destinatarios.<br /><b>Sin personalización</b>
              </div>
            </a>
            <a class="tarjeta-interactiva" href="<?= $url_personalizados ?>">
              <div class="tarjeta-icono"><img width="32" height="32" src="https://img.icons8.com/forma-regular/32/1A1A1A/composing-mail.png" alt="composing-mail" /></div>
              <div class="tarjeta-titulo">Correos Personalizados</div>
              <div class="tarjeta-desc">
                Envía mensajes personalizados a partir de CSV o separado por comas.<br /><b>Para comunicaciones individualizadas</b>
              </div>
            </a>
          </div>
        </section>

        <!-- Grupo: PITCH -->
        <section class="grupo-seccion">
          <legend class="titulo-seccion">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-microphone">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M9 2m0 3a3 3 0 0 1 3 -3h0a3 3 0 0 1 3 3v5a3 3 0 0 1 -3 3h0a3 3 0 0 1 -3 -3z" />
              <path d="M5 10a7 7 0 0 0 14 0" />
              <path d="M8 21l8 0" />
              <path d="M12 17l0 4" />
            </svg> 
            Pitch de emprendedores
          </legend>
          <div class="dashboard-tarjetas">
            <a class="tarjeta-interactiva" href="<?= $url_pitch ?>">
              <div class="tarjeta-icono">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-ppt">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                  <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                  <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                  <path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                  <path d="M11 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                  <path d="M16.5 15h3" />
                  <path d="M18 15v6" />
                  <path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" />
                </svg>
              </div>
              <div class="tarjeta-titulo">Revisar pitch</div>
              <div class="tarjeta-desc">
                Revisa las presentaciones y califícalos
              </div>
            </a>
          </div>
        </section>
      </ul>
    </section>
  </main>
  
  <script>
 
  </script>
</body>

</html>
