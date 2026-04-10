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

// Incluir la vista si no es AJAX
include 'registro_emprendedores_view.php';
?>