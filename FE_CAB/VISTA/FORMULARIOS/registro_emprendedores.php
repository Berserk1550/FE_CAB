<?php
/* ======= registro_ruta_emprendedora.php ======= */

declare(strict_types=1);

require_once '../servicios/conexion.php';

$cn = ConectarDB();
if ($cn instanceof mysqli) {
  @$cn->set_charset('utf8mb4');
  @$cn->query("SET NAMES utf8mb4 COLLATE utf8mb4_spanish_ci");
  @$cn->query("SET time_zone='-05:00'");
}

/* ====== AJAX: validar documento existente (con DEBUG detallado) ====== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'check_doc') {
  header('Content-Type: application/json; charset=utf-8');

  // ---- Activa/desactiva LOG detallado aquí:
  $DEBUG = true;

  $tipo = trim((string)($_POST['tipo_id'] ?? ''));
  $num  = trim((string)($_POST['numero_id'] ?? ''));

  $out = function ($arr) {
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
  };

  if ($tipo === '' || $num === '') {
    $out(['ok' => false, 'exists' => null, 'msg' => 'Faltan parámetros (tipo_id / numero_id)']);
  }

  // 0) Verifica conexión
  if (!($cn instanceof mysqli) || $cn->connect_errno) {
    $out([
      'ok' => false,
      'exists' => null,
      'msg' => 'Error de conexión a BD',
      'debug' => $DEBUG ? [
        'mysqli_connect_errno' => $cn->connect_errno ?? null,
        'mysqli_connect_error' => $cn->connect_error ?? null
      ] : null
    ]);
  }

  try {
    // ====== A) INTENTO #1: tu esquema original ======
    $sqlA = "SELECT id, nombres, apellidos FROM orientacion_rcde2025_valle WHERE tipo_id = ? AND numero_id = ? LIMIT 1";

    $stA = @$cn->prepare($sqlA);

    // Si falla preparar A), probamos un esquema alterno común
    if (!$stA) {
      // ====== B) INTENTO #2: nombres alternos de columnas ======
      $sqlB = "SELECT id, nombres, apellidos FROM emprendedores WHERE tipo_documento = ? AND documento = ? LIMIT 1";
      $stB = @$cn->prepare($sqlB);

      if (!$stB) {
        // Nada funcionó: devolvemos el motivo exacto
        $out([
          'ok' => false,
          'exists' => null,
          'msg' => 'Error al preparar consulta SQL',
          'debug' => $DEBUG ? [
            'error_sql_A' => $cn->error,
            'sql_A' => $sqlA,
            'error_sql_B' => $cn->error,
            'sql_B' => $sqlB
          ] : null
        ]);
      } else {
        // Usamos B)
        $stB->bind_param('ss', $tipo, $num);
        if (!$stB->execute()) {
          $out([
            'ok' => false,
            'exists' => null,
            'msg' => 'Error al ejecutar SQL (B)',
            'debug' => $DEBUG ? ['mysqli_error' => $cn->error, 'sql' => $sqlB] : null
          ]);
        }
        $res = $stB->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stB->close();
      }
    } else {
      // Usamos A)
      $stA->bind_param('ss', $tipo, $num);
      if (!$stA->execute()) {
        $out([
          'ok' => false,
          'exists' => null,
          'msg' => 'Error al ejecutar SQL (A)',
          'debug' => $DEBUG ? ['mysqli_error' => $cn->error, 'sql' => $sqlA] : null
        ]);
      }
      $res = $stA->get_result();
      $row = $res ? $res->fetch_assoc() : null;
      $stA->close();
    }

    if ($row) {
      $out(['ok' => true, 'exists' => true, 'data' => [
        'id' => (int)$row['id'],
        'nombres' => (string)($row['nombres'] ?? ''),
        'apellidos' => (string)($row['apellidos'] ?? '')
      ]]);
    } else {
      $out(['ok' => true, 'exists' => false]);
    }
  } catch (Throwable $e) {
    @error_log('[check_doc EX] ' . $e->getMessage());
    $out([
      'ok' => false,
      'exists' => null,
      'msg' => 'Error interno',
      'debug' => $DEBUG ? ['exception' => $e->getMessage()] : null
    ]);
  }
}
/* ---------------------------------------------------------
   PREFILL PARA PRODUCCIÓN
   - Soporta tokens cifrados (?t=) generados por qr_enlaces.php
   - Soporta parámetros legacy firmados (o,r,n,sig o center,region,name,sig)
   Esta lógica garantiza que los campos de Centro y Orientador se rellenen
   automáticamente y que los nombres con tildes se manejen correctamente.
--------------------------------------------------------- */

// Aseguramos salida en UTF-8 para que tildes y caracteres especiales
// se visualicen correctamente en cualquier navegador/dispositivo.

// log de prefill desde qr:
function log_prefill(array $data): void {
  $file = __DIR__.'/../logs/prefill_qr.log';
  $data['ts'] = date('c');
  $data['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
  $line = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  @file_put_contents($file, $line.PHP_EOL, FILE_APPEND);
}

header('Content-Type: text/html; charset=utf-8');

// Variables iniciales por defecto
$prefill_ok   = false;
$center       = '';
$region       = '';
$name         = '';
$oid_resuelto = 0;

// ----- 1) Intentar leer token cifrado (?t=...) -----
if (!empty($_GET['t'])) {
    // Ruta correcta al archivo qr_enlaces.php
    $qrPath = __DIR__ . '/../servicios/php/qr_enlaces.php';

    if (is_file($qrPath)) {
        require_once $qrPath;

        if (function_exists('enlace_include')) {
            try {
                // enlace_include() ya sabe leer $_GET['t']
                $info = enlace_include();
            } catch (Throwable $e) {
                $info = null;
            }

            if (is_array($info) && !empty($info['ok'])) {
                $prefill_ok   = true;
                $center       = (string)($info['centro']   ?? '');
                $region       = (string)($info['regional'] ?? '');
                $name         = (string)($info['nombres']  ?? '');
                $oid_resuelto = (int)($info['oid']        ?? 0);
            }
        } else {
            // DEBUG opcional
            error_log('qr_enlaces.php cargado, pero no existe la función enlace_include()');
        }
    } else {
        // DEBUG opcional
        error_log('No se encontró el archivo: ' . $qrPath);
    }
}



// ----- 2) Fallback: parámetros legacy firmados (o/r/n/sig o center/region/name/sig) -----
if (!$prefill_ok) {
    // Incluimos config_qr.php para disponer de sign_params()
    require_once __DIR__ . '/../servicios/php/config_qr.php';

    // Función interna para obtener valores de GET limpiando espacios
    $get = function ($key) {
        return isset($_GET[$key]) ? trim((string)$_GET[$key]) : '';
    };
    // Leemos los valores de las diferentes variantes
    $oValue  = $get('o') ?: $get('center');
    $rValue  = $get('r') ?: $get('region');
    $nValue  = $get('n') ?: $get('name');
    $sig     = $get('sig');
    // Saneamos centro y regional (solo alfanumérico, guión y guión bajo)
    $cTmp    = preg_replace('/[^A-Za-z0-9_\-]/', '', $oValue);
    $rTmp    = preg_replace('/[^A-Za-z0-9_\-]/', '', $rValue);
    $nTmp    = (string)$nValue; // dejamos tildes intactas

    // Verificamos firma en ambas variantes
    $firmaOk = false;
    if ($cTmp !== '' && $rTmp !== '' && $nTmp !== '' && $sig !== '') {
        // Abreviada: o,r,n
        $paramsA = ['o' => $cTmp, 'r' => $rTmp, 'n' => $nTmp];
        // Completa: center,region,name
        $paramsB = ['center' => $cTmp, 'region' => $rTmp, 'name' => $nTmp];
        if (function_exists('sign_params')) {
            if (hash_equals(sign_params($paramsA), $sig) || hash_equals(sign_params($paramsB), $sig)) {
                $firmaOk = true;
            }
        }
    }

    if ($firmaOk) {
        $prefill_ok = true;
        $center     = $cTmp;
        $region     = $rTmp;
        $name       = $nTmp;
        // Resolución del ID de orientador en la base de datos
        $sqlLegacy = "SELECT id_orientador FROM orientadores WHERE centro=? AND regional=? AND TRIM(CONCAT(nombres,' ',apellidos))=? LIMIT 1";
        if ($stmtLegacy = $cn->prepare($sqlLegacy)) {
            $stmtLegacy->bind_param('sss', $center, $region, $name);
            $stmtLegacy->execute();
            $resLegacy  = $stmtLegacy->get_result();
            $rowLegacy  = $resLegacy ? $resLegacy->fetch_assoc() : null;
            $stmtLegacy->close();
            if ($rowLegacy) {
                $oid_resuelto = (int)$rowLegacy['id_orientador'];
            }
        }
    }
}

// ----- 3) Normalizar cadenas UTF-8 (tildes, ñ, etc.) si ya tenemos valores -----
// Si la función fix_utf8 existe (definida en qr_enlaces.php) la usamos; de lo contrario
// aplicamos mb_convert_encoding para asegurar que las cadenas se corrijan en caso de
// estar codificadas doblemente. Esto ayuda a mostrar nombres con tildes correctamente.
if ($prefill_ok) {
    if (function_exists('fix_utf8')) {
        $center = fix_utf8($center);
        $region = fix_utf8($region);
        $name   = fix_utf8($name);
    } else {
        $center = mb_convert_encoding($center, 'UTF-8', 'UTF-8');
        $region = mb_convert_encoding($region, 'UTF-8', 'UTF-8');
        $name   = mb_convert_encoding($name, 'UTF-8', 'UTF-8');
    }
}

log_prefill([
    'ok'     => $prefill_ok,
    'center' => $center,
    'region' => $region,
    'name'   => $name,
    'oid'    => $oid_resuelto,
    'raw_t'  => $_GET['t'] ?? null,
]);

?>
<!-- HTML del formulario -->
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>SENA CAB | Registro - Ruta Emprendedora</title>
  <link rel="icon" type="image/png" href="../componentes/img/favicon.ico" />
  <!-- El echo lifetime rompe la caché para que rompa la cachpe del archivo, para que actualice el estilado -->
  <link rel="stylesheet" href="../../statics/css/css_formularios/formulario_emprendedores.css" />
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />

  <!-- Prefill data disponible para JS -->
<script>
window.PREFILL = <?= json_encode([
    'ok'     => $prefill_ok,
    'center' => $center,
    'name'   => $name,
    'oid'    => $oid_resuelto
], JSON_UNESCAPED_UNICODE) ?>;
</script>
  <script>
    window.SERVER_NOW = {
      ymd: "<?= date('Y-m-d'); ?>",
      ts: "<?= date('Y-m-d H:i:s'); ?>",
      tz: "America/Bogota"
    };
  </script>
  <!-- ============================================================================== -->
  <!-- Estilos mínimos para la modal local (si ya tienes otra, esta no estorba) -->
</head>

<body>
  <!-- Encabezado institucional -->
<!-- Encabezado institucional -->
<header class="site-header">
  <div class="site-header__inner">

    <!-- IZQUIERDA: logos tal como ya los maneja tu formulario -->
    
      <div class="brand-left">
        <img src="../componentes/img/logoFondoEmprender.svg" alt="Fondo Emprender" class="brand-fe__logo">
        <div class="brand-fe__caption">
          <!-- <span class="brand-fe__line">Fondo Emprender</span>
          <span class="brand-fe__code">SENA</span> -->
        </div>
      </div>

    <!-- DERECHA: medalla + botón Inicio -->
    <nav class="site-header__nav">

      <!-- Medalla -->
      <div class="dropdown-medalla">
        <button class="btn-medalla" type="button" aria-label="Ver información del programa">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
              viewBox="0 0 24 24" stroke-width="1.5" stroke="#39a900"
              fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M9 3h6l3 7l-6 2l-6 -2z" />
            <path d="M12 12l-3 -9" />
            <path d="M15 11l-3 -8" />
            <path d="M12 19.5l-3 1.5l.5 -3.5l-2 -2l3 -.5l1.5 -3l1.5 3l3 .5l-2 2l.5 3.5z" />
          </svg>
        </button>

        <!-- Texto oculto del programa -->
        <span class="texto-guardado" style="display:none;">
          ANÁLISIS Y DESARROLLO EN SOFTWARE | 2825817
        </span>

        <!-- Tooltip opcional -->
        <div class="tooltip-medalla" id="tooltip-medalla" aria-hidden="true">
        </div>
      </div>

      <!-- Botón Volver al inicio -->
      <a href="../index" class="btn-cta">Volver al inicio</a>
    </nav>
  </div>
</header>


  <div class="layout-registro">
    <aside class="col-info">
      <div class="dashboard-manual">
        <strong><b>ORIENTACIÓN A EMPRENDEDORES 2025</b></strong><br /><br />
        <strong>Centros de Desarrollo Empresarial - Regional Valle</strong>
        <p>
          ¡Bienvenido/a Emprendedor(a)! Por favor registre su asistencia a la orientación sobre los servicios
          de los Centros de Desarrollo Empresarial del SENA CAB Regional Valle. Este espacio permite acceder a la
          <b>Ruta Emprendedora</b> y a las herramientas necesarias para fortalecer sus habilidades blandas, desarrollar
          competencias emprendedoras y acceder a oportunidades como participar en convocatorias Fondo Emprender.
        </p>
        <br />
        <p>
          <b>CONSENTIMIENTO INFORMADO Y PROTECCIÓN DE DATOS:</b> Entiendo que mi
          participación consiste en el diligenciamiento del presente formulario. La información es confidencial
          (Ley 1581 de 2012).
        </p>
      </div>
    </aside>

    <main class="col-form">
      <div class="dashboard-contenedor">
        <div class="dashboard-header">
          <!-- <p><b>SBDC - Centro de Desarrollo Empresarial</b></p> -->
          <h2>Registro Ruta Emprendedora - 2025</h2>
        </div>
        <!-- Progreso -->
        <div class="progress-container">
          <div class="progress-bar" id="progress-bar"></div>
          <div class="progress-steps">
            <span class="step active" data-step="1">1</span>
            <span class="step" data-step="2">2</span>
            <span class="step" data-step="3">3</span>
            <span class="step" data-step="4">4</span>
            <span class="step" data-step="5">5</span>
            <span class="step" data-step="6">6</span>
            <span class="step" data-step="7">7</span>
          </div>
        </div>

        <form action="../servicios/php/guardar_formulario" method="post" id="MIformulario" accept-charset="UTF-8">

          <!-- ===== FASE 1 ===== -->
          <div class="fase">
            <div class="titulo-seccion"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.75 20.5V22H6.75C5.50736 22 4.5 20.9926 4.5 19.75V9.62105C4.5 9.02455 4.73686 8.45247 5.15851 8.03055L10.5262 2.65951C10.9482 2.23725 11.5207 2 12.1177 2H17.25C18.4926 2 19.5 3.00736 19.5 4.25V9.75H18V4.25C18 3.83579 17.6642 3.5 17.25 3.5H12.248L12.2509 7.4984C12.2518 8.74166 11.2442 9.75 10.0009 9.75H6V19.75C6 20.1642 6.33579 20.5 6.75 20.5H9.75ZM10.7488 4.55876L7.05986 8.25H10.0009C10.4153 8.25 10.7512 7.91389 10.7509 7.49947L10.7488 4.55876Z" fill="#323544" />
                <path fill-rule="evenodd" clip-rule="evenodd" d="M20.2988 12.3389C19.6153 11.6555 18.5073 11.6555 17.8239 12.3389L12.6657 17.4971C12.3028 17.86 12.0749 18.3359 12.0197 18.8461L11.8307 20.593C11.8063 20.8188 11.8854 21.0434 12.046 21.204C12.2066 21.3646 12.4312 21.4437 12.657 21.4193L14.4039 21.2303C14.9141 21.1751 15.39 20.9472 15.7529 20.5843L20.9111 15.4261C21.5945 14.7427 21.5945 13.6347 20.9111 12.9512L20.2988 12.3389ZM18.0219 14.2622L18.9878 15.2281L14.6922 19.5237C14.5713 19.6446 14.4126 19.7206 14.2426 19.739L13.4222 19.8278L13.511 19.0074C13.5294 18.8374 13.6054 18.6787 13.7263 18.5578L18.0219 14.2622Z" fill="#323544" />
              </svg>
              Información Personal</div>

            <div class="form-grupodos">
              <div class="form-grupo">
                <label for="nombres">1. Nombres <span style="color:red">*</span></label><br />
                <input type="text" id="nombres" name="nombres" class="form-control"
                  pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]{2,25}" minlength="2" maxlength="25" required />
              </div>

              <div class="form-grupo">
                <label for="apellidos">2. Apellidos <span style="color:red">*</span></label><br />
                <input type="text" id="apellidos" name="apellidos" class="form-control"
                  pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]{2,40}" minlength="2" maxlength="40" required />
              </div>
            </div>

            <div class="form-grupodos reducido doc-fila">
              <div class="form-grupo">
                <label for="tipo_id">3. Tipo de documento <span style="color:red">*</span></label><br />
                <select id="tipo_id" name="tipo_id" required class="form-control">
                  <option value="" disabled selected>-- Selecciona una opción --</option>
                  <option value="TI">TI</option>
                  <option value="CC">CC</option>
                  <option value="CE">CE</option>
                  <option value="PEP">PEP</option>
                  <option value="PAS">PAS</option>
                  <option value="PPT">PPT</option>
                </select>
              </div>

              <div class="form-grupo">
                <label for="numero_id">4. Número de Identificación <span style="color:red">*</span></label><br />
                <input type="text" id="numero_id" name="numero_id" class="form-control" pattern="[A-Za-z0-9]{6,20}" minlength="6" maxlength="20" required title="Ingrese solo letras y números, entre 6 y 20 caracteres" />
                <small id="numero_id_hint" style="display:block;color:#666;margin-top:4px;"></small>
              </div>
            </div>

            <div class="form-grupodos">
              <div class="form-grupo">
                <label for="celular">5. Número de Celular <span style="color:red">*</span></label><br />
                <input type="tel" id="celular" name="celular" class="form-control"
                  title="Solo números" pattern="[0-9]{10}" maxlength="10" minlength="10" required />
              </div>

              <div class="form-grupo">
                <label for="fecha_nacimiento">6. Fecha de nacimiento</label><br />
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" required />
              </div>
            </div>

            <div class="form-grupo">
              <label for="correo">7. Correo Electrónico <span style="color:red">*</span></label><br />
              <input type="email" id="correo" name="correo" required class="form-control" />
            </div>
            <!-- <div class="form-grupo">
          <label for="fecha_expedicion">8. Fecha de expedición del documento (opcional)</label><br />
          <input type="date" id="fecha_expedicion" name="fecha_expedicion" class="form-control" />
        </div>-->
          </div>

          <!-- ===== FASE 2 ===== -->
          <div class="fase">
            <div class="titulo-seccion"><svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.27344 3.41016C6.27344 2.99594 5.93765 2.66016 5.52344 2.66016C5.10922 2.66016 4.77344 2.99594 4.77344 3.41016V21.9102C4.77344 22.3244 5.10922 22.6602 5.52344 22.6602C5.93765 22.6602 6.27344 22.3244 6.27344 21.9102V15.9102H11.5234C11.5234 17.1528 12.5308 18.1602 13.7734 18.1602H19.0234C19.2779 18.1602 19.5151 18.0311 19.6533 17.8174C19.7914 17.6037 19.8118 17.3345 19.7074 17.1024L17.7344 12.7179C17.6463 12.5222 17.6463 12.2981 17.7344 12.1024L19.7074 7.71793C19.8118 7.48585 19.7914 7.21664 19.6533 7.00293C19.5151 6.78921 19.2779 6.66016 19.0234 6.66016H13.0234C13.0234 5.41752 12.0161 4.41016 10.7734 4.41016H6.27344V3.41016ZM6.27344 5.91016V14.4102H11.5234V6.66016C11.5234 6.24594 11.1877 5.91016 10.7734 5.91016H6.27344ZM13.0234 8.16016H17.8635L16.3665 11.4868C16.1023 12.074 16.1023 12.7463 16.3665 13.3335L17.8635 16.6602H13.7734C13.3592 16.6602 13.0234 16.3244 13.0234 15.9102V8.16016Z" fill="#323544" />
              </svg>
              Nacionalidad</div>

            <div class="form-grupo">
              <label for="pais">9. País <span style="color:red">*</span></label><br />
              <select id="pais" name="pais_origen" class="form-control" required>
                <option value="" disabled selected>-- Selecciona un país --</option>
              </select>
            </div>

            <div class="form-grupo">
              <label>10. Nacionalidad <span style="color:red">*</span></label><br />
              <span id="nacionalidad" class="form-control" name="nacionalidad"
                style="display:inline-block;min-height:38px;padding:8px 20px;margin-top:10px;"></span>
            </div>

            <div class="form-grupo">
              <label for="departamento">11. Departamento (si es de otro país, elija "Otro")
                <span style="color:red">*</span></label><br />
              <select id="departamento" name="departamento" class="form-control" required>
                <option value="" disabled selected>-- Selecciona un departamento --</option>
                <option value="Amazonas">Amazonas</option>
                <option value="Antioquia">Antioquia</option>
                <option value="Arauca">Arauca</option>
                <option value="Atlántico">Atlántico</option>
                <option value="Bogotá D.C.">Bogotá D.C.</option>
                <option value="Bolívar">Bolívar</option>
                <option value="Boyacá">Boyacá</option>
                <option value="Caldas">Caldas</option>
                <option value="Caquetá">Caquetá</option>
                <option value="Casanare">Casanare</option>
                <option value="Cauca">Cauca</option>
                <option value="Cesar">Cesar</option>
                <option value="Chocó">Chocó</option>
                <option value="Córdoba">Córdoba</option>
                <option value="Cundinamarca">Cundinamarca</option>
                <option value="Guainía">Guainía</option>
                <option value="Guaviare">Guaviare</option>
                <option value="Huila">Huila</option>
                <option value="La Guajira">La Guajira</option>
                <option value="Magdalena">Magdalena</option>
                <option value="Meta">Meta</option>
                <option value="Nariño">Nariño</option>
                <option value="Norte de Santander">Norte de Santander</option>
                <option value="Putumayo">Putumayo</option>
                <option value="Quindío">Quindío</option>
                <option value="Risaralda">Risaralda</option>
                <option value="San Andrés y Providencia">San Andrés y Providencia</option>
                <option value="Santander">Santander</option>
                <option value="Sucre">Sucre</option>
                <option value="Tolima">Tolima</option>
                <option value="Valle del Cauca" selected>Valle del Cauca</option>
                <option value="Vaupés">Vaupés</option>
                <option value="Vichada">Vichada</option>
                <option value="Otro">Otro</option>
              </select>
              <input type="text" id="dpto_otro" name="departamento_otro" placeholder="Especifique cuál"
                class="form-control" style="display:none;margin-top:8px" />
            </div>

            <div class="form-grupo">
              <label for="municipio">12. Municipio <span style="color:red">*</span></label><br />
              <input type="text" id="municipio" name="municipio" class="form-control"
                pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]{2,45}" title="Solo letras" required />
            </div>
          </div>

          <!-- ===== FASE 3 ===== -->
          <div class="fase">
            <div class="titulo-seccion"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.18535 12.75C7.18535 12.3082 7.54352 11.95 7.98535 11.95H7.99535C8.43718 11.95 8.79535 12.3082 8.79535 12.75C8.79535 13.1918 8.43718 13.55 7.99535 13.55H7.98535C7.54352 13.55 7.18535 13.1918 7.18535 12.75Z" fill="#323544" />
                <path d="M7.98535 15.95C7.54352 15.95 7.18535 16.3082 7.18535 16.75C7.18535 17.1918 7.54352 17.55 7.98535 17.55H7.99535C8.43718 17.55 8.79535 17.1918 8.79535 16.75C8.79535 16.3082 8.43718 15.95 7.99535 15.95H7.98535Z" fill="#323544" />
                <path d="M11.1951 12.75C11.1951 12.3082 11.5533 11.95 11.9951 11.95H12.0051C12.4469 11.95 12.8051 12.3082 12.8051 12.75C12.8051 13.1918 12.4469 13.55 12.0051 13.55H11.9951C11.5533 13.55 11.1951 13.1918 11.1951 12.75Z" fill="#323544" />
                <path d="M11.9951 15.95C11.5533 15.95 11.1951 16.3082 11.1951 16.75C11.1951 17.1918 11.5533 17.55 11.9951 17.55H12.0051C12.4469 17.55 12.8051 17.1918 12.8051 16.75C12.8051 16.3082 12.4469 15.95 12.0051 15.95H11.9951Z" fill="#323544" />
                <path d="M15.2049 12.75C15.2049 12.3082 15.5631 11.95 16.0049 11.95H16.0149C16.4567 11.95 16.8149 12.3082 16.8149 12.75C16.8149 13.1918 16.4567 13.55 16.0149 13.55H16.0049C15.5631 13.55 15.2049 13.1918 15.2049 12.75Z" fill="#323544" />
                <path d="M16.0049 15.95C15.5631 15.95 15.2049 16.3082 15.2049 16.75C15.2049 17.1918 15.5631 17.55 16.0049 17.55H16.0149C16.4567 17.55 16.8149 17.1918 16.8149 16.75C16.8149 16.3082 16.4567 15.95 16.0149 15.95H16.0049Z" fill="#323544" />
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.75 2.75C8.75 2.33579 8.41421 2 8 2C7.58579 2 7.25 2.33579 7.25 2.75V3.75H5.5C4.25736 3.75 3.25 4.75736 3.25 6V19C3.25 20.2426 4.25736 21.25 5.5 21.25H18.5C19.7426 21.25 20.75 20.2426 20.75 19V6C20.75 4.75736 19.7426 3.75 18.5 3.75H16.75V2.75C16.75 2.33579 16.4142 2 16 2C15.5858 2 15.25 2.33579 15.25 2.75V3.75H8.75V2.75ZM19.25 8.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25ZM4.75 9.75H19.25V19C19.25 19.4142 18.9142 19.75 18.5 19.75H5.5C5.08579 19.75 4.75 19.4142 4.75 19V9.75Z" fill="#323544" />
              </svg>
              Información Adicional</div>

            <div class="form-grupo">
              <!-- <label for="fecha_orientacion">13. Fecha de orientación</label><br /> -->
              <input type="text" id="fecha_orientacion_display" value="" readonly class="form-control" style="border:none;" hidden />
              <input type="hidden" name="fecha_orientacion" id="fecha_orientacion" />
              <input type="hidden" name="ts_inicio" id="ts_inicio" />
            </div>

            <div class="form-grupo">
              <label for="genero">13. Sexo <span style="color:red">*</span></label><br />
              <select id="genero" name="genero" class="form-control" required>
                <option value="">-- Selecciona --</option>
                <option value="Mujer">Mujer</option>
                <option value="Hombre">Hombre</option>
                <option value="No definido">No definido</option>
              </select>
            </div>
          </div>

          <!-- ===== FASE 4 ===== -->
          <div class="fase">
            <div class="titulo-seccion"><img width="32" height="32" src="https://img.icons8.com/windows/32/people-working-together.png" alt="people-working-together" /> Caracterización</div>

            <div class="form-grupo">
              <label for="clasificacion">14. Clasificación de población (si aplica)</label><br />
              <select id="clasificacion" name="clasificacion" class="form-control" required>
                <option value="" disabled selected>
                  -- Selecciona una opción --
                </option>
                <option value="Ninguno">Ninguno</option>
                <option value="Adolescente trabajador">
                  Adolescente trabajador
                </option>
                <option value="Adolescente en conflicto con la ley penal">
                  Adolescente en conflicto con la ley penal
                </option>
                <option value="Adolescentes y jóvenes vulnerables">
                  Adolescentes y jóvenes vulnerables
                </option>
                <option value="Afrocolombianos">Afrocolombianos</option>
                <option value="Campesinos">Campesinos</option>
                <option value="Desplazado por fenómenos naturales">
                  Desplazado por fenómenos naturales
                </option>
                <option value="Migrantes que retornan al país">
                  Migrantes que retornan al país
                </option>
                <option value="Mujer cabeza de hogar">
                  Mujer cabeza de hogar
                </option>
                <option value="Negritudes">Negritudes</option>
                <option value="Palenqueros">Palenqueros</option>
                <option value="Reintegrados (ARN)">
                  Participantes del programa de reintegración - Reintegrados (ARN)
                </option>
                <option value="Personas en reincorporación">
                  Personas en Proceso de Reincorporación
                </option>
                <option value="Población con discapacidad">
                  Población con discapacidad
                </option>
                <option value="Población indígena">Población indígena</option>
                <option value="Población LGBTI">Población LGBTI</option>
                <option value="Víctima de minas antipersona">
                  Población víctima de minas antipersona
                </option>
                <option value="Pueblo ROM">Pueblo ROM</option>
                <option value="Raizales">Raizales</option>
                <option value="Remitidos por PAL">
                  Remitidos por programa de adaptación laboral - PAL
                </option>
                <option value="Soldados campesinos">Soldados campesinos</option>
                <option value="Tercera edad">Tercera edad</option>
                <option value="Víctima de la violencia">
                  Víctima de la violencia
                </option>
                <option value="Víctima de otros hechos">
                  Víctima de otros hechos victimizantes
                </option>
                <option value="Sobrevivientes de agentes químicos">
                  Víctimas sobrevivientes con agentes químicos
                </option>
              </select>
            </div>

            <div class="form-grupo">
              <label for="discapacidad">15. Si es persona en condición de discapacidad, seleccionar el tipo</label><br />
              <select id="discapacidad" name="discapacidad" class="form-control" required>
                <option value="" disabled selected>-- Selecciona una opción --</option>
                <option value="Ninguna">Ninguna</option>
                <option value="Auditiva">Auditiva</option>
                <option value="Cognitiva">Cognitiva</option>
                <option value="Física">Física</option>
                <option value="Múltiple">Múltiple</option>
                <option value="Psicosocial">Psicosocial</option>
                <option value="Sordoceguera">Sordoceguera</option>
                <option value="Visual">Visual</option>
              </select>
            </div>
          </div>

          <!-- ===== FASE 5 ===== -->
          <div class="fase">
            <div class="titulo-seccion"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.2977 3.30965C12.1077 3.22751 11.8923 3.22751 11.7023 3.30965L2.45232 7.30965C2.17775 7.42838 2 7.69891 2 7.99805C2 8.29718 2.17775 8.56771 2.45232 8.68644L5.125 9.84219V15.9259C5.125 16.0717 5.16749 16.2143 5.24727 16.3363L5.875 15.9259C5.24727 16.3363 5.24706 16.336 5.24727 16.3363L5.24814 16.3376L5.2492 16.3392L5.25188 16.3433L5.25947 16.3546C5.26553 16.3636 5.27359 16.3753 5.28365 16.3897C5.30378 16.4184 5.33196 16.4577 5.36827 16.5059C5.44085 16.6023 5.54621 16.735 5.68494 16.8917C5.96196 17.2047 6.375 17.6169 6.9293 18.0282C8.04166 18.8535 9.72592 19.6759 12 19.6759C14.2741 19.6759 15.9583 18.8535 17.0707 18.0282C17.625 17.6169 18.038 17.2047 18.3151 16.8917C18.4538 16.735 18.5591 16.6023 18.6317 16.5059C18.668 16.4577 18.6962 16.4184 18.7163 16.3897C18.7264 16.3753 18.7345 16.3636 18.7405 16.3546L18.7481 16.3433L18.7508 16.3392L18.7519 16.3376C18.7521 16.3373 18.7527 16.3363 18.125 15.9259L18.7527 16.3363C18.8325 16.2143 18.875 16.0717 18.875 15.9259V9.84219L20.5 9.13949V14.7188C20.5 15.133 20.8358 15.4688 21.25 15.4688C21.6642 15.4688 22 15.133 22 14.7188V8C22 7.99967 22 8.00033 22 8C22 7.99968 22 7.99837 22 7.99805C22 7.69891 21.8222 7.42838 21.5477 7.30965L12.2977 3.30965ZM17.375 10.4908L12.2977 12.6864C12.1077 12.7686 11.8923 12.7686 11.7023 12.6864L6.625 10.4908V15.6793C6.67243 15.7392 6.73344 15.8131 6.80814 15.8975C7.02902 16.1471 7.36658 16.4848 7.8231 16.8236C8.73237 17.4982 10.1106 18.1759 12 18.1759C13.8894 18.1759 15.2676 17.4982 16.1769 16.8236C16.6334 16.4848 16.971 16.1471 17.1919 15.8975C17.2666 15.8131 17.3276 15.7392 17.375 15.6793V10.4908ZM12 11.1809L4.63959 7.99805L12 4.81517L19.3604 7.99805L12 11.1809Z" fill="#323544" />
              </svg>
              Caracterización Educativa</div>

            <div class="form-grupo">
              <label for="tipo_emprendedor">16. Tipo de Emprendedor <span style="color:red">*</span></label><br />
              <select id="tipo_emprendedor" name="tipo_emprendedor" required class="form-control">
                <option value="" disabled selected>-- Selecciona una opción --</option>
                <option value="Aprendiz">Aprendiz</option>
                <option value="Instructor">Instructor</option>
                <option value="Egresado de Otras Instituciones">Egresado de Otras Instituciones</option>
                <option value="Egresado SENA Complementaria">Egresado SENA Complementaria</option>
                <option value="Egresado SENA Titulada">Egresado SENA Titulada</option>
                <option value="No cuenta con formación">No cuenta con formación</option>
                <option value="Otro">Otro</option>
              </select>
              <input type="text" id="tipo_emprendedor_otro" name="tipo_emprendedor_otro"
                class="form-control" placeholder="Escribe tu tipo de emprendedor"
                style="display:none;margin-top:8px"
                pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]{3,60}"
                title="Solo letras, de 3 a 60 caracteres" />
            </div>

            <div class="form-grupo">
              <label for="nivel_formacion">17. Nivel de Formación en el momento actual <span style="color:red">*</span></label><br />
              <select id="nivel_formacion" name="nivel_formacion" class="form-control" required>
                <option value="" disabled selected>-- Selecciona --</option>
                <option value="Técnico">Técnico</option>
                <option value="Tecnólogo">Tecnólogo</option>
                <option value="Operario">Operario</option>
                <option value="Auxiliar">Auxiliar</option>
                <option value="Profesional">Profesional</option>
                <option value="Especialización">Especialización</option>
                <option value="Maestría">Maestría</option>
                <option value="Doctorado">Doctorado</option>
                <option value="Sin título">Sin título</option>
              </select>

              <select id="carrera_tecnologo" name="carrera_tecnologo" style="display:none;margin-top:8px" class="form-control">
                <option value="" disabled selected>
                  -- Elige tu Tecnólogo --
                </option>
                <option value="Análisis y desarrollo de software">Análisis y desarrollo de software</option>
                <option value="Gestión de talento humano">Gestión de talento humano</option>
                <option value="Gestión agroempresarial">Gestión agroempresarial</option>
                <option value="Gestión de recursos naturales">Gestión de recursos naturales</option>
                <option value="Prevención y control ambiental">Prevención y control ambiental</option>
                <option value="Desarrollo multimedia y web">Desarrollo multimedia y web</option>
                <option value="Gestión contable y de información financiera">Gestión contable y de información financiera</option>
                <option value="Desarrollo publicitario">Desarrollo publicitario</option>
                <option value="Gestión de la seguridad y salud en el trabajo">Gestión de la seguridad y salud en el trabajo</option>
                <option value="Gestión de redes de datos">Gestión de redes de datos</option>
                <option value="Mantenimiento electromecánico industrial">Mantenimiento electromecánico industrial</option>
                <option value="Producción de multimedia">Producción de multimedia</option>
                <option value="Animación digital">Animación digital</option>
                <option value="Gestión empresarial">Gestión empresarial</option>
                <option value="Gestión documental">Gestión documental</option>
                <option value="Actividad física y entrenamiento deportivo">Actividad física y entrenamiento deportivo</option>
                <option value="Regencia de farmacia">Regencia de farmacia</option>
                <option value="Producción ganadera">Producción ganadera</option>
                <option value="Gestión de empresas agropecuarias">Gestión de empresas agropecuarias</option>
                <option value="Supervisión de redes de distribución de energía eléctrica">Supervisión de redes de distribución de energía eléctrica</option>
                <option value="Procesamiento de alimentos">Procesamiento de alimentos</option>
                <option value="Control de calidad de alimentos">Control de calidad de alimentos</option>
                <option value="Gestión logística">Gestión logística</option>
                <option value="Mecanización agrícola y producción agrícola">Mecanización agrícola y producción agrícola</option>
                <option value="Otro">Otro</option>
              </select>

              <select id="carrera_tecnico" name="carrera_tecnico" style="display:none;margin-top:8px" class="form-control">
                <option value="" disabled selected>-- Elige tu Técnico --</option>
                <option value="Asistencia administrativa">Asistencia administrativa</option>
                <option value="Cocina">Cocina</option>
                <option value="Conservación de recursos naturales">Conservación de recursos naturales</option>
                <option value="Contabilización de operaciones comerciales y financieras">Contabilización de operaciones comerciales y financieras</option>
                <option value="Ejecución de programas deportivos">Ejecución de programas deportivos</option>
                <option value="Enfermería">Enfermería</option>
                <option value="Monitoreo ambiental">Monitoreo ambiental</option>
                <option value="Operación turística local">Operación turística local</option>
                <option value="Sistemas agropecuarios ecológicos">Sistemas agropecuarios ecológicos</option>
                <option value="Sistemas teleinformáticos">Sistemas teleinformáticos</option>
                <option value="Sistemas atención integral al cliente">Sistemas atención integral al cliente</option>
                <option value="Cultivo de agrícolas">Cultivo de agrícolas</option>
                <option value="Elaboración de productos alimenticios">Elaboración de productos alimenticios</option>
                <option value="Instalación de sistemas eléctricos residenciales y comerciales">Instalación de sistemas eléctricos residenciales y comerciales</option>
                <option value="Programación de software">Programación de software</option>
                <option value="Proyectos agropecuarios">Proyectos agropecuarios</option>
                <option value="Recursos humanos y comercialización de productos masivos">Recursos humanos y comercialización de productos masivos</option>
                <option value="Integración de operaciones logísticas">Integración de operaciones logísticas</option>
                <option value="Manejo de viveros">Manejo de viveros</option>
                <option value="Mecánica de maquinaria industrial">Mecánica de maquinaria industrial</option>
                <option value="Integración de contenidos digitales">Integración de contenidos digitales</option>
                <option value="Electricista industrial">Electricista industrial</option>
                <option value="Mantenimiento de motocicletas y motocarros">Mantenimiento de motocicletas y motocarros</option>
                <option value="Mantenimiento de vehículos livianos">Mantenimiento de vehículos livianos</option>
                <option value="Soldadura de productos metalócios en platina">Soldadura de productos metalócios en platina</option>
                <option value="Producción pecuario">Producción pecuario</option>
                <option value="Operaciones de comercio exterior">Operaciones de comercio exterior</option>
                <option value="Servicios comerciales y financieros">Servicios comerciales y financieros</option>
                <option value="Servicios farmacéuticos">Servicios farmacéuticos</option>
                <option value="Servicio de restaurante y bar">Servicio de restaurante y bar</option>
                <option value="Operaciones comerciales en retail">Operaciones comerciales en retail</option>
                <option value="Operaciones de maquinaria agrícola">Operaciones de maquinaria agrícola</option>
                <option value="Procesamiento de carnes">Procesamiento de carnes</option>
                <option value="Técnico en operaciones forestales y producción ovino-caprina">Técnico en operaciones forestales y producción ovino-caprina</option>
              </select>

              <select id="carrera_operario" name="carrera_operario" style="display:none;margin-top:8px" class="form-control">
                <option value="" disabled selected>
                  -- Elige tu Operario --
                </option>
                <option value="Procesos de panadería">Procesos de panadería</option>
                <option value="Cuidado básico de personas con dependencia funcional">Cuidado básico de personas con dependencia funcional</option>
                <option value="Instalaciones eléctricas para viviendas">Instalaciones eléctricas para viviendas</option>
                <option value="Otro">Otro</option>
              </select>

              <select id="carrera_auxiliar" name="carrera_auxiliar" style="display:none;margin-top:8px" class="form-control">
                <option value="" disabled selected>-- Elige tu Auxiliar --</option>
                <option value="Servicios de apoyo al cliente">Servicios de apoyo al cliente</option>
                <option value="Otro">Otro</option>
              </select>
            </div>

            <select id="carrera_profesional" name="carrera_profesional" required class="form-control" style="display:none; margin-top: 8px;">
              <option value="" disabled selected>-- Selecciona tu carrera profesional --</option>

              <optgroup label="Ingenierías y Tecnología">
                <option value="Ingeniería de Sistemas">Ingeniería de Sistemas</option>
                <option value="Ingeniería de Software">Ingeniería de Software</option>
                <option value="Ingeniería Informática">Ingeniería Informática</option>
                <option value="Ingeniería en Computación">Ingeniería en Computación</option>
                <option value="Ingeniería Electrónica">Ingeniería Electrónica</option>
                <option value="Ingeniería Eléctrica">Ingeniería Eléctrica</option>
                <option value="Ingeniería en Telecomunicaciones">Ingeniería en Telecomunicaciones</option>
                <option value="Ingeniería Mecánica">Ingeniería Mecánica</option>
                <option value="Ingeniería Mecatrónica">Ingeniería Mecatrónica</option>
                <option value="Ingeniería Industrial">Ingeniería Industrial</option>
                <option value="Ingeniería Civil">Ingeniería Civil</option>
                <option value="Ingeniería Ambiental">Ingeniería Ambiental</option>
                <option value="Ingeniería Química">Ingeniería Química</option>
                <option value="Ingeniería Biomédica">Ingeniería Biomédica</option>
                <option value="Ingeniería Aeroespacial">Ingeniería Aeroespacial</option>
                <option value="Ingeniería Naval">Ingeniería Naval</option>
                <option value="Ingeniería Geológica">Ingeniería Geológica</option>
                <option value="Ingeniería de Petróleos">Ingeniería de Petróleos</option>
                <option value="Ingeniería de Minas">Ingeniería de Minas</option>
                <option value="Ingeniería Agroindustrial">Ingeniería Agroindustrial</option>
                <option value="Ingeniería de Alimentos">Ingeniería de Alimentos</option>
                <option value="Ingeniería en Energías Renovables">Ingeniería en Energías Renovables</option>
                <option value="Ingeniería en Materiales">Ingeniería en Materiales</option>
                <option value="Ingeniería Topográfica">Ingeniería Topográfica</option>
                <option value="Ingeniería de Transporte">Ingeniería de Transporte</option>
                <option value="Ingeniería de Datos">Ingeniería de Datos</option>
                <option value="Ciencia de Datos">Ciencia de Datos</option>
                <option value="Analítica de Negocios">Analítica de Negocios</option>
                <option value="Inteligencia Artificial">Inteligencia Artificial</option>
                <option value="Ciberseguridad">Ciberseguridad</option>
                <option value="Robótica">Robótica</option>
                <option value="Geomática">Geomática</option>
                <option value="Logística e Ingeniería Logística">Logística e Ingeniería Logística</option>
              </optgroup>

              <optgroup label="Ciencias de la Salud">
                <option value="Medicina">Medicina</option>
                <option value="Enfermería">Enfermería</option>
                <option value="Odontología">Odontología</option>
                <option value="Fisioterapia">Fisioterapia</option>
                <option value="Terapia Ocupacional">Terapia Ocupacional</option>
                <option value="Fonoaudiología">Fonoaudiología</option>
                <option value="Nutrición y Dietética">Nutrición y Dietética</option>
                <option value="Instrumentación Quirúrgica">Instrumentación Quirúrgica</option>
                <option value="Bacteriología">Bacteriología</option>
                <option value="Microbiología">Microbiología</option>
                <option value="Química Farmacéutica (Farmacia)">Química Farmacéutica (Farmacia)</option>
                <option value="Optometría">Optometría</option>
                <option value="Terapia Respiratoria">Terapia Respiratoria</option>
                <option value="Salud Pública">Salud Pública</option>
                <option value="Radiología e Imágenes Diagnósticas">Radiología e Imágenes Diagnósticas</option>
              </optgroup>

              <optgroup label="Ciencias Sociales y Humanas">
                <option value="Psicología">Psicología</option>
                <option value="Sociología">Sociología</option>
                <option value="Antropología">Antropología</option>
                <option value="Trabajo Social">Trabajo Social</option>
                <option value="Filosofía">Filosofía</option>
                <option value="Historia">Historia</option>
                <option value="Geografía">Geografía</option>
                <option value="Ciencia Política">Ciencia Política</option>
                <option value="Relaciones Internacionales">Relaciones Internacionales</option>
                <option value="Arqueología">Arqueología</option>
                <option value="Lingüística">Lingüística</option>
                <option value="Literatura">Literatura</option>
                <option value="Estudios Culturales">Estudios Culturales</option>
                <option value="Teología">Teología</option>
                <option value="Desarrollo Territorial">Desarrollo Territorial</option>
              </optgroup>

              <optgroup label="Economía, Negocios y Gestión">
                <option value="Administración de Empresas">Administración de Empresas</option>
                <option value="Contaduría Pública">Contaduría Pública</option>
                <option value="Economía">Economía</option>
                <option value="Finanzas">Finanzas</option>
                <option value="Mercadeo">Mercadeo</option>
                <option value="Negocios Internacionales">Negocios Internacionales</option>
                <option value="Comercio Exterior">Comercio Exterior</option>
                <option value="Administración Pública">Administración Pública</option>
                <option value="Gestión Empresarial">Gestión Empresarial</option>
                <option value="Banca y Finanzas">Banca y Finanzas</option>
                <option value="Dirección de Empresas">Dirección de Empresas</option>
                <option value="Emprendimiento">Emprendimiento</option>
                <option value="Gerencia Logística">Gerencia Logística</option>
                <option value="Gestión de Proyectos">Gestión de Proyectos</option>
                <option value="Gestión del Talento Humano">Gestión del Talento Humano</option>
                <option value="Administración Turística y Hotelera">Administración Turística y Hotelera</option>
              </optgroup>

              <optgroup label="Educación (Licenciaturas)">
                <option value="Licenciatura en Educación Preescolar">Licenciatura en Educación Preescolar</option>
                <option value="Licenciatura en Educación Básica Primaria">Licenciatura en Educación Básica Primaria</option>
                <option value="Licenciatura en Lengua Castellana">Licenciatura en Lengua Castellana</option>
                <option value="Licenciatura en Matemáticas">Licenciatura en Matemáticas</option>
                <option value="Licenciatura en Ciencias Naturales">Licenciatura en Ciencias Naturales</option>
                <option value="Licenciatura en Educación Física">Licenciatura en Educación Física</option>
                <option value="Licenciatura en Idiomas (Inglés)">Licenciatura en Idiomas (Inglés)</option>
                <option value="Licenciatura en Educación Especial">Licenciatura en Educación Especial</option>
                <option value="Licenciatura en Artes">Licenciatura en Artes</option>
                <option value="Licenciatura en Música">Licenciatura en Música</option>
                <option value="Licenciatura en Tecnología e Informática">Licenciatura en Tecnología e Informática</option>
              </optgroup>

              <optgroup label="Artes, Arquitectura y Diseño">
                <option value="Arquitectura">Arquitectura</option>
                <option value="Diseño Gráfico">Diseño Gráfico</option>
                <option value="Diseño Industrial">Diseño Industrial</option>
                <option value="Diseño de Modas">Diseño de Modas</option>
                <option value="Diseño de Interiores">Diseño de Interiores</option>
                <option value="Artes Plásticas">Artes Plásticas</option>
                <option value="Artes Visuales">Artes Visuales</option>
                <option value="Fotografía">Fotografía</option>
                <option value="Cine y Televisión">Cine y Televisión</option>
                <option value="Animación Digital">Animación Digital</option>
                <option value="Música">Música</option>
                <option value="Danza">Danza</option>
                <option value="Teatro">Teatro</option>
                <option value="Producción Multimedia">Producción Multimedia</option>
              </optgroup>

              <optgroup label="Ciencias Básicas y Naturales">
                <option value="Matemáticas">Matemáticas</option>
                <option value="Estadística">Estadística</option>
                <option value="Física">Física</option>
                <option value="Química">Química</option>
                <option value="Biología">Biología</option>
                <option value="Bioquímica">Bioquímica</option>
                <option value="Geología">Geología</option>
                <option value="Ciencias de la Tierra">Ciencias de la Tierra</option>
                <option value="Astronomía">Astronomía</option>
                <option value="Nanociencia y Nanotecnología">Nanociencia y Nanotecnología</option>
                <option value="Ciencias del Mar">Ciencias del Mar</option>
              </optgroup>

              <optgroup label="Agropecuarias y Ambiente">
                <option value="Medicina Veterinaria">Medicina Veterinaria</option>
                <option value="Zootecnia">Zootecnia</option>
                <option value="Agronomía">Agronomía</option>
                <option value="Ingeniería Agronómica">Ingeniería Agronómica</option>
                <option value="Ingeniería Forestal">Ingeniería Forestal</option>
                <option value="Ingeniería Agroecológica">Ingeniería Agroecológica</option>
                <option value="Ingeniería Agrícola">Ingeniería Agrícola</option>
                <option value="Ingeniería Pesquera">Ingeniería Pesquera</option>
                <option value="Acuicultura">Acuicultura</option>
                <option value="Administración Ambiental">Administración Ambiental</option>
                <option value="Gestión Ambiental">Gestión Ambiental</option>
                <option value="Ciencias Ambientales">Ciencias Ambientales</option>
                <option value="Hidrología">Hidrología</option>
                <option value="Meteorología">Meteorología</option>
              </optgroup>

              <optgroup label="Comunicación y Medios">
                <option value="Comunicación Social">Comunicación Social</option>
                <option value="Periodismo">Periodismo</option>
                <option value="Publicidad">Publicidad</option>
                <option value="Relaciones Públicas">Relaciones Públicas</option>
                <option value="Comunicación Audiovisual">Comunicación Audiovisual</option>
                <option value="Comunicación Digital">Comunicación Digital</option>
                <option value="Producción de Radio y TV">Producción de Radio y TV</option>
                <option value="Comunicación Organizacional">Comunicación Organizacional</option>
              </optgroup>

              <optgroup label="Derecho, Gobierno y Seguridad">
                <option value="Derecho">Derecho</option>
                <option value="Criminología">Criminología</option>
                <option value="Criminalística">Criminalística</option>
                <option value="Gobierno y Asuntos Públicos">Gobierno y Asuntos Públicos</option>
                <option value="Gestión Pública">Gestión Pública</option>
                <option value="Seguridad y Salud en el Trabajo">Seguridad y Salud en el Trabajo</option>
                <option value="Gestión de la Seguridad">Gestión de la Seguridad</option>
                <option value="Investigación Criminal">Investigación Criminal</option>
              </optgroup>

              <optgroup label="Turismo, Gastronomía y Deporte">
                <option value="Turismo">Turismo</option>
                <option value="Administración Turística y Hotelera">Administración Turística y Hotelera</option>
                <option value="Gastronomía">Gastronomía</option>
                <option value="Guianza Turística">Guianza Turística</option>
                <option value="Gestión Deportiva">Gestión Deportiva</option>
                <option value="Recreación y Deporte">Recreación y Deporte</option>
              </optgroup>

              <option value="Otro">Otro</option>
            </select>

            <!-- ESPECIALIZACIÓN -->
            <select id="posgrado_especializacion" name="posgrado_especializacion"
              class="form-control" style="display:none;">
              <option value="" disabled selected>-- Selecciona tu especialización --</option>
              <option value="Especialización en Gerencia de Proyectos">Especialización en Gerencia de Proyectos</option>
              <option value="Especialización en Seguridad y Salud en el Trabajo">Especialización en Seguridad y Salud en el Trabajo</option>
              <option value="Especialización en Finanzas">Especialización en Finanzas</option>
              <option value="Especialización en Analítica de Datos">Especialización en Analítica de Datos</option>
              <option value="Especialización en Docencia Universitaria">Especialización en Docencia Universitaria</option>
              <option value="Otro">Otro</option>
            </select>

            <!-- MAESTRÍA -->
            <select id="posgrado_maestria" name="posgrado_maestria"
              class="form-control" style="display:none;">
              <option value="" disabled selected>-- Selecciona tu maestría --</option>
              <option value="Maestría en Ingeniería de Software">Maestría en Ingeniería de Software</option>
              <option value="Maestría en Administración (MBA)">Maestría en Administración (MBA)</option>
              <option value="Maestría en Dirección de Proyectos">Maestría en Dirección de Proyectos</option>
              <option value="Maestría en Ciencias de Datos">Maestría en Ciencias de Datos</option>
              <option value="Maestría en Educación">Maestría en Educación</option>
              <option value="Maestría en Salud Pública">Maestría en Salud Pública</option>
              <option value="Maestría en Ingeniería Industrial">Maestría en Ingeniería Industrial</option>
              <option value="Otro">Otro</option>
            </select>

            <!-- DOCTORADO -->
            <select id="posgrado_doctorado" name="posgrado_doctorado"
              class="form-control" style="display:none;">
              <option value="" disabled selected>-- Selecciona tu doctorado --</option>
              <option value="Doctorado en Ingeniería">Doctorado en Ingeniería</option>
              <option value="Doctorado en Ciencias">Doctorado en Ciencias</option>
              <option value="Doctorado en Educación">Doctorado en Educación</option>
              <option value="Doctorado en Salud Pública">Doctorado en Salud Pública</option>
              <option value="Doctorado en Economía">Doctorado en Economía</option>
              <option value="Otro">Otro</option>
            </select>


            <div class="form-grupo">
              <label for="ficha">18. Si eres aprendiz o egresado SENA, escribe tu <b>número de ficha</b>.
                De lo contrario, escribe "no aplica". <span style="color:red">*</span></label><br />
              <input type="text" id="ficha" name="ficha" class="form-control" placeholder="2825817" required />
            </div>
          </div>

          <!-- ===== FASE 6 ===== -->
          <div class="fase">
            <div class="titulo-seccion"><img width="48" height="48" src="https://img.icons8.com/fluency-systems-regular/48/1A1A1A/business-management.png" alt="business-management" /> Información Complementaria del emprendedor</div>

            <div class="form-grupo">
              <label>19. Eres un emprendedor que tiene… <span style="color:red">*</span></label><br />
              <select id="situacion_negocio" name="situacion_negocio" required class="form-control">
                <option value="" disabled selected>-- Selecciona --</option>
                <option value="Ninguno">Ninguno</option>
                <option value="Idea de negocio">Una idea de negocio</option>
                <option value="Unidad productiva">Una unidad productiva (informal)</option>
                <option value="Empresa persona natural">Una empresa como persona natural</option>
                <option value="Empresa persona jurídica">Una empresa como persona jurídica</option>
                <option value="Asociación">Una asociación</option>
              </select>
              <input type="text" id="negocio_otro" name="situacion_negocio_otro"
                placeholder="Especifique cuál" class="form-control"
                style="display:none;margin-top:8px"
                pattern="[a-zA-Z\s]+" title="Solo ingrese letras" />
            </div>

            <div class="form-grupo">
              <label>20. ¿Pertenece a alguno de los siguientes programas especiales?
                <span style="color:red">*</span></label><br />
              <select id="programa" name="programa" required class="form-control">
                <option value="" disabled selected>-- Selecciona --</option>
                <option value="Ninguno">Ninguno</option>
                <option value="Jóvenes en paz">Jóvenes en paz</option>
                <option value="Indígenas amazónicos">Indígenas amazónicos</option>
                <option value="Parques nacionales">Parques nacionales</option>
                <option value="ICBF">ICBF</option>
                <option value="Economía popular">Economía popular</option>
                <option value="Ninguno">Cuidadores</option>
              </select>

              <!-- Campo que solo se muestra si la opción es "Otro" -->
              <!-- <input type="text" id="programa_otro" name="programa_otro" placeholder="Especifique cuál" class="form-control" style="display:none; margin-top:8px;" required> -->
            </div>

            <div class="form-grupo">
              <label>21. ¿Usted ejerce la actividad relacionada con el proyecto que desea presentar?
                <span style="color:red">*</span></label><br />
              <select id="ejercer_actividad_proyecto" name="ejercer_actividad_proyecto" required class="form-control">
                <option value="" disabled selected hidden>-- Selecciona --</option>
                <option value="SI">Sí</option>
                <option value="NO">No</option>
              </select>
            </div>

            <div class="form-grupo">
              <label>22. ¿Usted tiene empresa formalizada ante Cámara de Comercio?
                <span style="color:red">*</span></label><br />
              <select id="empresa_formalizada" name="empresa_formalizada" required class="form-control">
                <option value="" disabled selected hidden>-- Selecciona --</option>
                <option value="SI">Sí</option>
                <option value="NO">No</option>
              </select>
            </div>
          </div>

          <!-- ===== FASE 7 ===== -->
          <div class="fase">
            <div class="titulo-seccion"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12.75 14.6667C12.75 14.2524 13.0858 13.9167 13.5 13.9167H16.5C16.9142 13.9167 17.25 14.2524 17.25 14.6667C17.25 15.0809 16.9142 15.4167 16.5 15.4167H13.5C13.0858 15.4167 12.75 15.0809 12.75 14.6667Z" fill="#323544" />
                <path d="M13.5 8.58334C13.0858 8.58334 12.75 8.91913 12.75 9.33334C12.75 9.74756 13.0858 10.0833 13.5 10.0833H16.5C16.9142 10.0833 17.25 9.74756 17.25 9.33334C17.25 8.91913 16.9142 8.58334 16.5 8.58334H13.5Z" fill="#323544" />
                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.5 3.25C10.2574 3.25 9.25 4.25736 9.25 5.5V7.75H5.5C4.25736 7.75 3.25 8.75736 3.25 10V20C3.25 20.4142 3.58579 20.75 4 20.75H20C20.4142 20.75 20.75 20.4142 20.75 20V5.5C20.75 4.25736 19.7426 3.25 18.5 3.25H11.5ZM9.25 19.25V17H7.75586C7.34165 17 7.00586 16.6642 7.00586 16.25C7.00586 15.8358 7.34165 15.5 7.75586 15.5H9.25V13H7.75586C7.34165 13 7.00586 12.6642 7.00586 12.25C7.00586 11.8358 7.34165 11.5 7.75586 11.5H9.25V9.25H5.5C5.08579 9.25 4.75 9.58579 4.75 10V19.25H9.25ZM10.75 12.2773C10.7503 12.2683 10.7505 12.2591 10.7505 12.25C10.7505 12.2409 10.7503 12.2317 10.75 12.2227V5.5C10.75 5.08579 11.0858 4.75 11.5 4.75H18.5C18.9142 4.75 19.25 5.08579 19.25 5.5V19.25H10.75V16.2773C10.7503 16.2683 10.7505 16.2591 10.7505 16.25C10.7505 16.2409 10.7503 16.2317 10.75 16.2227V12.2773Z" fill="#323544" />
              </svg>
              Centro y Orientador</div>

            <div class="form-grupo">
              <label for="centro_orientacion">23. ¿Cuál es el Centro de Desarrollo Empresarial que brinda la orientación?
                <span style="color:red">*</span></label><br />
              <select id="centro_orientacion" name="centro_orientacion" class="form-control" required
                onchange="actualizarOrientadores()"
                <?= $prefill_ok ? 'disabled title="Preseleccionado desde QR"' : '' ?>>
                
                <option value="" disabled <?= $prefill_ok ? '' : 'selected' ?>>-- Selecciona un centro --</option>

                <option value="CAB" <?= ($prefill_ok && $center === 'CAB')  ? 'selected' : '' ?>>Centro Agropecuario de Buga (CAB)</option>
                <option value="CBI" <?= ($prefill_ok && $center === 'CBI')  ? 'selected' : '' ?>>Centro de Biotecnología Industrial (CBI Palmira)</option>
                <option value="CDTI" <?= ($prefill_ok && $center === 'CDTI') ? 'selected' : '' ?>>Centro de Diseño Tecnológico Industrial (CDTI Cali)</option>
                <option value="CEAI" <?= ($prefill_ok && $center === 'CEAI') ? 'selected' : '' ?>>Centro de Electricidad y Automatización Industrial (CEAI Cali)</option>
                <option value="CGTS" <?= ($prefill_ok && $center === 'CGTS') ? 'selected' : '' ?>>Centro de Gestión Tecnológica de Servicios (CGTS Cali)</option>
                <option value="ASTIN" <?= ($prefill_ok && $center === 'ASTIN') ? 'selected' : '' ?>>Centro Nacional de Asistencia Técnica a la Industria (ASTIN - Cali)</option>
                <option value="CTA" <?= ($prefill_ok && $center === 'CTA')  ? 'selected' : '' ?>>Centro de Tecnologías Agroindustriales (CTA - Cartago)</option>
                <option value="CLEM" <?= ($prefill_ok && $center === 'CLEM') ? 'selected' : '' ?>>Centro Latinoamericano de Especies Menores (CLEM - Tuluá)</option>
                <option value="CNP" <?= ($prefill_ok && $center === 'CNP')  ? 'selected' : '' ?>>Centro Náutico y Pesquero (CNP - Buenaventura)</option>
                <option value="CC" <?= ($prefill_ok && $center === 'CC')   ? 'selected' : '' ?>>Centro de la Construcción (CC - Cali)</option>
              </select>
              <?php if ($prefill_ok): ?>
                <input type="hidden" name="centro_orientacion" value="<?= htmlspecialchars($center, ENT_QUOTES, 'UTF-8') ?>">
              <?php endif; ?>

            </div>

          <div class="form-grupo">
  <label for="orientador">24. ¿Cuál fue el orientador que brindó la orientación?
    <span style="color:red">*</span></label><br />
  <select id="orientador" name="orientador" class="form-control" required
    <?= $prefill_ok ? 'disabled title="Preseleccionado desde QR"' : '' ?>>
    <?php if ($prefill_ok): ?>
      <!-- Cuando el formulario se precarga desde QR, usamos el nombre completo como value para que
           coincida con las opciones generadas por actualizarOrientadores(). -->
      <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" selected>
        <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
      </option>
    <?php else: ?>
      <option value="" disabled selected>-- Selecciona primero un centro --</option>
    <?php endif; ?>
  </select>

  <?php if ($prefill_ok): ?>
    <!-- En modo QR el select está deshabilitado, así que mandamos el nombre en un hidden
         para que guardar_formulario.php no lo vea vacío. -->
    <input type="hidden"
           name="orientador"
           value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>

  <!-- IMPORTANTE: aquí cambiamos el name para que coincida con guardar_formulario.php -->
  <input type="hidden"
         name="orientador_id_prefill"
         id="orientador_id"
         value="<?= $prefill_ok ? (int)$oid_resuelto : '' ?>">

<!-- carta de debug -->
<!-- <?php
if (isset($_GET['t'])) {
    echo "<div style='background:#111;color:#0f0;padding:15px;margin:15px 0;border-radius:10px;font-family:monospace;'>";
    echo "<b>DEBUG TOKEN RECIBIDO DESDE EL QR:</b><br><br>";

    echo "<b>Token bruto:</b><br>";
    echo htmlspecialchars($_GET['t']) . "<br><br>";

        // Intentamos cargar qr_enlaces.php y usar enlace_include()
    $qrInc = __DIR__ . '/../servicios/php/qr_enlaces.php';
    if (is_file($qrInc)) {
        require_once $qrInc;
    } else {
        echo "<b>Error al decodificar:</b> No se encontró el archivo qr_enlaces.php en: "
           . htmlspecialchars($qrInc);
    }

    try {
        if (function_exists('enlace_include')) {
            $dump = enlace_include(); // ya lee $_GET['t']
            echo "<b>Contenido decodificado:</b><br>";
            echo "<pre style='color:#0f0;white-space:pre-wrap;'>";
            echo htmlspecialchars(json_encode($dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo "</pre>";
        } else {
$e = error_get_last();
echo "<b>Error al decodificar:</b> " . htmlspecialchars(($e['message'] ?? 'Error desconocido') . ' en ' . ($e['file'] ?? '') . ':' . ($e['line'] ?? ''), ENT_QUOTES, 'UTF-8');
        }
    } catch (Throwable $e) {
        echo "<b>Error al decodificar (excepción):</b> " . htmlspecialchars($e->getMessage());
    }


    echo "</div>";
}
?> -->

            </div>

            <button type="submit" class="btn-verde">Enviar Formulario</button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <!-- ========= MODAL ACTUALIZAR DATOS ========= -->
  <div id="modal-actualizar-datos" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="box">
      <h3>Documento ya registrado</h3>
      <p id="mad-texto">Este documento ya se encuentra registrado. Para continuar, por favor actualiza tus datos.</p>
      <div class="acciones">
        <button type="button" class="btn sec" id="mad-cerrar">X</button>
        <a id="mad-ir" href="#" class="btn pri">Actualizar mis datos</a>
      </div>
    </div>
  </div>

  <script src="../../statics/Js/formulario.js"></script>

  <footer class="site-footer" role="contentinfo">
    <div class="site-footer__inner">
      <img src="../componentes/img/logocolombiaporlavidatrabajo.png" alt="Colombia potencia de la vida" width="42" height="42" loading="lazy">
      <img src="../componentes/img/mintrabajo.png" alt="Ministerio del Trabajo" width="42" height="42" loading="lazy">
    </div>
  </footer>

</body>

</html>