<?php
/**
 * enlace_orientador.php — UI + API + include()
 * - Genera enlace cifrado y QR (usa config_qr.php + qr_orientadores.php)
 * - Verifica token
 * - Integra con el formulario via enlace_include()
 * - Carga orientadores desde BD para seleccionar por nombres y usar su ID real
 */
declare(strict_types=1);
@date_default_timezone_set('America/Bogota');
@session_start();

/* ================== CONFIG (AJUSTA AQUÍ) ================== */
const BASE_FORM_URL   = 'https://arcano.digital/emprender/formulario_emprendedores/registro_emprendedores'; // URL del formulario real que recibirá ?t=
const SECRET_KEY_HEX  = '4d3f2e7a9b1c0d4e5f60718293a4b5c6d7e8f90123456789abcdeffedcba9876';               // 32 bytes HEX (NO cambiar entre generar y verificar)
const QR_OUT_DIR      = __DIR__ . '/storage/qr';                                                            // Carpeta para PNG
const QR_HTTP_PATH    = '/servicios/php/qr_orientadores.php';                                              // Fallback HTTP (ruta web de tu script QR)
const CONEXION_PHP    = __DIR__ . '/../servicios/conexion.php';                                            // Ruta a tu conexion.php

/* === RUTAS CANDIDATAS DONDE BUSCAR config_qr.php y qr_orientadores.php === */
function get_qr_config_paths(): array {
  return [
    __DIR__.'/config_qr.php',
    __DIR__.'/../servicios/php/config_qr.php',
    __DIR__.'/../../servicios/php/config_qr.php',
  ];
}
function get_qr_lib_paths(): array {
  return [
    __DIR__.'/qr_orientadores.php',
    __DIR__.'/../servicios/php/qr_orientadores.php',
    __DIR__.'/../../servicios/php/qr_orientadores.php',
  ];
}

/* ================== HELPERS ================== */
if (!function_exists('e')) { function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); } }
function k(): string { return hex2bin(SECRET_KEY_HEX); }
function b64u_enc(string $bin): string { return rtrim(strtr(base64_encode($bin), '+/', '-_'), '='); }
function b64u_dec(string $b64u): string { $b64 = strtr($b64u, '-_', '+/'); return base64_decode($b64 . str_repeat('=', (4 - strlen($b64) % 4) % 4)); }
function tok_encrypt(array $payload): string {
  $json = json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  $iv = random_bytes(12); $tag = '';
  $ct = openssl_encrypt($json, 'aes-256-gcm', k(), OPENSSL_RAW_DATA, $iv, $tag, '');
  if ($ct === false) throw new RuntimeException('Error cifrando');
  return b64u_enc($iv.$tag.$ct);
}
function tok_decrypt(string $token): array {
  $bin = b64u_dec($token);
  if (strlen($bin) < 28) throw new RuntimeException('Token corrupto');
  $iv  = substr($bin, 0, 12); $tag = substr($bin, 12, 16); $ct  = substr($bin, 28);
  $plain = openssl_decrypt($ct, 'aes-256-gcm', k(), OPENSSL_RAW_DATA, $iv, $tag, '');
  if ($plain === false) throw new RuntimeException('Token inválido');
  $arr = json_decode($plain, true); if (!is_array($arr)) throw new RuntimeException('Payload inválido');
  return $arr;
}

/* ================== CARGA QR (robusto) ================== */
function make_qr_png(string $text, string $outfile): void {
  @is_dir(QR_OUT_DIR) || @mkdir(QR_OUT_DIR, 0775, true);

  // Carga archivos si existen (con manejo de errores y supresión de output)
  $loaded_cfg = $loaded_lib = null;
  $ok = false; 
  $why = [];
  
  ob_start(); // Capturar cualquier output no deseado
  foreach (get_qr_config_paths() as $path) { 
    if (is_file($path)) { 
      try { 
        require_once $path; 
        $loaded_cfg = $path; 
        break; 
      } catch (Throwable $e) { 
        $why[] = "Error cargando config: ".$e->getMessage(); 
      }
    } 
  }
  foreach (get_qr_lib_paths() as $path) { 
    if (is_file($path)) { 
      try { 
        require_once $path; 
        $loaded_lib = $path; 
        break; 
      } catch (Throwable $e) { 
        $why[] = "Error cargando lib: ".$e->getMessage(); 
      }
    } 
  }
  ob_end_clean(); // Descartar el output capturado

  // Defaults de constantes si tu config no las define
  if (!defined('QR_ECLEVEL_L')) define('QR_ECLEVEL_L', 'L');
  if (!defined('QR_ECLEVEL_M')) define('QR_ECLEVEL_M', 'M');
  if (!defined('QR_ECLEVEL_Q')) define('QR_ECLEVEL_Q', 'Q');
  if (!defined('QR_ECLEVEL_H')) define('QR_ECLEVEL_H', 'H');
  if (!defined('QR_IMAGE_SIZE'))   define('QR_IMAGE_SIZE', 5);
  if (!defined('QR_IMAGE_MARGIN')) define('QR_IMAGE_MARGIN', 1);

  // 1) Clase QRcode (phpqrcode)
  if (class_exists('QRcode')) {
    try {
      $level  = QR_ECLEVEL_M; $size = QR_IMAGE_SIZE; $margin = QR_IMAGE_MARGIN;
      $ref = new ReflectionMethod('QRcode','png');
      if ($ref->getNumberOfParameters() >= 5) { QRcode::png($text, $outfile, $level, $size, $margin); }
      else { QRcode::png($text, $outfile, $level, $size); }
      $ok = true;
    } catch (Throwable $e) { $why[] = 'QRcode::png falló: '.$e->getMessage(); }
  } else { $why[] = 'Clase QRcode no encontrada'; }

  // 2) Funciones personalizadas
  if (!$ok) {
    if (function_exists('generar_qr')) { 
      try { generar_qr($text, $outfile); $ok = true; }
      catch (Throwable $e) { $why[] = 'generar_qr() falló: '.$e->getMessage(); }
    }
    elseif (function_exists('crear_qr')) { 
      try { crear_qr($text, $outfile); $ok = true; }
      catch (Throwable $e) { $why[] = 'crear_qr() falló: '.$e->getMessage(); }
    }
    elseif (function_exists('qr_orientadores')) { 
      try { qr_orientadores($text, $outfile); $ok = true; }
      catch (Throwable $e) { $why[] = 'qr_orientadores() falló: '.$e->getMessage(); }
    }
    else { $why[] = 'No existen generar_qr()/crear_qr()/qr_orientadores()'; }
  }

  // 3) Fallback APIs externas de QR
  if (!$ok) {
    $q = urlencode($text);
    $apis = [
      "https://quickchart.io/qr?size=480&margin=2&text=$q",
      "https://api.qrserver.com/v1/create-qr-code/?size=480x480&data=$q",
      "https://chart.googleapis.com/chart?chs=480x480&cht=qr&chl=$q&choe=UTF-8"
    ];
    
    foreach ($apis as $api) {
      // Intentar con file_get_contents primero
      $png = false;
      if (ini_get('allow_url_fopen')) {
        $png = @file_get_contents($api, false, stream_context_create([
          'http' => ['timeout' => 10, 'ignore_errors' => true]
        ]));
      }
      
      // Si falla, intentar con cURL
      if ($png === false && function_exists('curl_init')) {
        $ch = curl_init($api);
        curl_setopt_array($ch, [
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_TIMEOUT => 10,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_FOLLOWLOCATION => true
        ]);
        $png = @curl_exec($ch);
        curl_close($ch);
      }
      
      if ($png !== false && strlen($png) > 100) { 
        file_put_contents($outfile, $png); 
        $ok = true;
        break;
      }
    }
    
    if (!$ok) {
      $why[] = 'Todas las APIs de QR fallaron (verifica conexión a Internet)'; 
    }
  }

  // 4) Fallback HTTP local (si existe)
  if (!$ok && QR_HTTP_PATH && php_sapi_name() !== 'cli') {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url   = $proto.'://'.$host.QR_HTTP_PATH.'?text='.urlencode($text);
    $png   = @file_get_contents($url);
    if ($png !== false && strlen($png) > 0) { file_put_contents($outfile, $png); $ok = true; }
    else { $why[] = 'Fallback HTTP local falló en '.$url; }
  }

  if (!$ok) {
    throw new RuntimeException(
      "No se pudo generar QR.\n".
      "config: ".($loaded_cfg ?: 'no cargada')."\n".
      "lib: ".($loaded_lib ?: 'no cargada')."\n".
      implode("\n", $why)
    );
  }
}

/* ============ DIAGNÓSTICO RÁPIDO (opcional) ============ */
if (isset($_GET['diag'])) {
  header('Content-Type: text/plain; charset=utf-8');
  echo "== DIAG ==\n\n";
  
  echo "1. Buscando archivos config_qr.php:\n";
  $loaded_cfg = null;
  foreach (get_qr_config_paths() as $p) {
    $exists = is_file($p);
    echo "   ".(($exists)?"[OK] ":"[NO] ").$p."\n";
    if ($exists && !$loaded_cfg) {
      try {
        require_once $p;
        $loaded_cfg = $p;
        echo "   --> Cargado exitosamente\n";
      } catch (Throwable $e) {
        echo "   --> Error al cargar: ".$e->getMessage()."\n";
      }
    }
  }
  
  echo "\n2. Buscando archivos qr_orientadores.php:\n";
  $loaded_lib = null;
  foreach (get_qr_lib_paths() as $p) {
    $exists = is_file($p);
    echo "   ".(($exists)?"[OK] ":"[NO] ").$p."\n";
    if ($exists && !$loaded_lib) {
      ob_start(); // Suprimir output del archivo
      try {
        require_once $p;
        $loaded_lib = $p;
        ob_end_clean();
        echo "   --> Cargado exitosamente\n";
      } catch (Throwable $e) {
        ob_end_clean();
        echo "   --> Error al cargar: ".$e->getMessage()."\n";
      }
    }
  }
  
  echo "\n3. Estado después de cargar:\n";
  echo "   Config cargado: ".($loaded_cfg ?: 'NINGUNO')."\n";
  echo "   Lib cargado: ".($loaded_lib ?: 'NINGUNO')."\n";
  echo "   QRcode class: ".(class_exists('QRcode')?'SI':'NO')."\n";
  echo "   generar_qr(): ".(function_exists('generar_qr')?'SI':'NO')."\n";
  echo "   crear_qr(): ".(function_exists('crear_qr')?'SI':'NO')."\n";
  echo "   qr_orientadores(): ".(function_exists('qr_orientadores')?'SI':'NO')."\n";
  
  echo "\n4. Constantes QR definidas:\n";
  echo "   QR_ECLEVEL_M: ".(defined('QR_ECLEVEL_M')?constant('QR_ECLEVEL_M'):'NO DEFINIDA')."\n";
  echo "   QR_IMAGE_SIZE: ".(defined('QR_IMAGE_SIZE')?constant('QR_IMAGE_SIZE'):'NO DEFINIDA')."\n";
  
  echo "\n5. Configuración PHP:\n";
  echo "   allow_url_fopen: ".(ini_get('allow_url_fopen')?'HABILITADO':'DESHABILITADO')."\n";
  echo "   cURL disponible: ".(function_exists('curl_init')?'SI':'NO')."\n";
  echo "   Internet: ";
  $internet = @file_get_contents('https://www.google.com', false, stream_context_create(['http'=>['timeout'=>3]]));
  echo ($internet !== false ? 'CONECTADO' : 'SIN CONEXIÓN')."\n";
  
  echo "\n6. Fallbacks disponibles:\n";
  echo "   HTTP fallback local: ".QR_HTTP_PATH."\n";
  echo "   QuickChart.io API: https://quickchart.io/qr\n";
  echo "   QRServer API: https://api.qrserver.com\n";
  echo "   Google Charts API: https://chart.googleapis.com/chart\n";
  
  echo "\n7. Prueba de generación:\n";
  try {
    $test_file = QR_OUT_DIR.'/test-diag.png';
    make_qr_png('https://ejemplo.com/test', $test_file);
    echo "   ✓ QR generado exitosamente en: $test_file\n";
    echo "   Tamaño: ".filesize($test_file)." bytes\n";
  } catch (Throwable $e) {
    echo "   ✗ Error: ".$e->getMessage()."\n";
  }
  
  exit;
}

/* ====== BD: LISTAR ORIENTADORES PARA EL SELECT (ajusta SQL si difiere) ====== */
function cargar_orientadores(string $conexionPhp): array {
  $lista = [];
  if (!is_file($conexionPhp)) return $lista;
  require_once $conexionPhp;                    // Debe exponer ConectarDB()
  if (!function_exists('ConectarDB')) return $lista;
  try {
    $cn = ConectarDB();
    if ($cn instanceof mysqli) {
      @$cn->set_charset('utf8mb4');
      // ⚠ Cambia "orientadores, nombres, apellidos, regional, centro" si en tu BD tienen otros nombres
      $sql = "SELECT id_orientador, nombres, apellidos, centro, regional FROM orientadores ORDER BY nombres";
      if ($res = $cn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
          $lista[] = [
            'id'        => (int)$row['id_orientador'],
            'nombres'   => (string)$row['nombres'],
            'apellidos' => (string)($row['apellidos'] ?? ''),
            'centro'    => (string)($row['centro'] ?? ''),
            'regional'  => (string)($row['regional'] ?? ''),
          ];
        }
        $res->free();
      }
    }
  } catch (Throwable $e) { /* error_log($e->getMessage()); */ }
  return $lista;
}

/* ================== LÓGICA DEL ENLACE ================== */
function enlace_gen(int $oid, string $regional='', string $centro='', string $nombre=''): array {
  if ($oid <= 0) throw new InvalidArgumentException('oid requerido (>0)');
  $payload = [ 'oid'=>$oid, 'exp'=> time()+90*24*3600, 'disp'=> ['regional'=>$regional,'centro'=>$centro,'nombres'=>$nombre] ];
  $t   = tok_encrypt($payload);
  $url = BASE_FORM_URL . '?t=' . urlencode($t);
  $slug = preg_replace('~[^a-z0-9]+~i', '-', $nombre !== '' ? $nombre : ('oid-'.$oid));
  $png  = rtrim(QR_OUT_DIR,'/').'/qr-orientador-'.trim($slug,'-').'.png';
  make_qr_png($url, $png);
  return ['url'=>$url, 'token'=>$t, 'qr'=>$png, 'payload'=>$payload];
}
function enlace_verify_from_token(string $t): array {
  $data = tok_decrypt($t);
  if (!empty($data['exp']) && time() > (int)$data['exp']) throw new RuntimeException('El enlace ha expirado.');
  $oid = (int)($data['oid'] ?? 0);
  if ($oid <= 0) throw new RuntimeException('ID inválido.');
  $_SESSION['token_orientador'] = ['oid'=>$oid]; // FUENTE de verdad para guardado
  $disp = is_array($data['disp'] ?? null) ? $data['disp'] : [];
  return ['ok'=>true,'oid'=>$oid,'regional'=>(string)($disp['regional'] ?? ''),'centro'=>(string)($disp['centro'] ?? ''),'nombres'=>(string)($disp['nombres'] ?? '')];
}

/* ====== INCLUDE EN FORM ====== */
function enlace_include(): array {
  $t = $_GET['t'] ?? '';
  if (!$t) return ['ok'=>false, 'error'=>'Falta token'];
  try { return enlace_verify_from_token($t); }
  catch (Throwable $e) { return ['ok'=>false,'error'=>$e->getMessage()]; }
}

/* ====== API SIMPLE ====== */
$action = php_sapi_name()==='cli' ? ($argv[1] ?? '') : ($_REQUEST['action'] ?? '');
if ($action === 'gen' || $action === 'verify') {
  try {
    if ($action === 'gen') {
      $oid = (int)($_REQUEST['select_oid'] ?? 0);
      if ($oid <= 0) {
        throw new InvalidArgumentException('select_oid es requerido');
      }
      
      // Los datos se obtienen automáticamente desde la BD
      $regional = trim((string)($_REQUEST['regional'] ?? ''));
      $centro   = trim((string)($_REQUEST['centro']   ?? ''));
      $nombre   = trim((string)($_REQUEST['nombres']   ?? ''));
      
      $res = enlace_gen($oid, $regional, $centro, $nombre);
      header('Content-Type: application/json; charset=utf-8'); echo json_encode($res, JSON_UNESCAPED_UNICODE); exit;
    }
    if ($action === 'verify') {
      $t = (string)($_REQUEST['t'] ?? '');
      $out = $t ? enlace_verify_from_token($t) : ['ok'=>false,'error'=>'Falta token'];
      header('Content-Type: application/json; charset=utf-8'); echo json_encode($out, JSON_UNESCAPED_UNICODE); exit;
    }
  } catch (Throwable $e) {
    header('Content-Type: application/json; charset=utf-8'); http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE); exit;
  }
}

/* ================== UI VISUAL ================== */
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])):
  $msg = $gen = $ver = null;
  $listaOrient = cargar_orientadores(CONEXION_PHP);

  if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['ui_action'])) {
    try {
      if ($_POST['ui_action']==='gen') {
        $oid = (int)($_POST['select_oid'] ?? 0);
        
        if ($oid <= 0) {
          throw new InvalidArgumentException('Debe seleccionar un orientador de la lista');
        }

        // Obtener datos del orientador desde la lista
        $regional = '';
        $centro   = '';
        $nombre   = '';
        
        foreach ($listaOrient as $o) {
          if ((int)$o['id'] === $oid) {
            $regional = (string)$o['regional'];
            $centro   = (string)$o['centro'];
            $nombre   = trim($o['nombres'].' '.$o['apellidos']);
            break;
          }
        }
        
        if ($nombre === '') {
          throw new InvalidArgumentException('Orientador no encontrado en la base de datos');
        }

        $gen = enlace_gen($oid, $regional, $centro, $nombre);
        $_SESSION['last_gen'] = $gen; // Guardar en sesión
      } else if ($_POST['ui_action']==='verify') {
        $ver = enlace_verify_from_token(trim((string)($_POST['token'] ?? '')));
        // Recuperar el último QR generado de la sesión
        if (isset($_SESSION['last_gen'])) {
          $gen = $_SESSION['last_gen'];
        }
      }
    } catch (Throwable $e) { $msg = $e->getMessage(); }
  }

  $qrDataUrl = null;
  if ($gen && is_file($gen['qr'])) { $qrDataUrl = 'data:image/png;base64,' . base64_encode(file_get_contents($gen['qr'])); }
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Generador de enlace y QR • Orientadores</title>
<link rel="stylesheet" href="../../statics/css/css_formularios/enlace_orientador.css" />
</head>
<body>
  <div class="wrap">
    <h1>Generador de enlace y QR para orientadores</h1>
    <?php if ($msg): ?><div class="err"><?= e($msg) ?></div><?php endif; ?>

    <div class="grid">
      <!-- Generar -->
      <form class="card" method="post">
        <input type="hidden" name="ui_action" value="gen">
        <h2>1) Generar enlace + QR</h2>

        <label>Seleccionar orientador <span style="color:red">*</span></label>
        <select name="select_oid" required>
          <option value="">— Selecciona un orientador —</option>
          <?php foreach ($listaOrient as $o): ?>
            <option value="<?= (int)$o['id'] ?>" <?= (isset($_POST['select_oid']) && (int)$_POST['select_oid']===(int)$o['id'])?'selected':'' ?>>
              <?= e($o['nombres'].' '.$o['apellidos']) ?><?= $o['centro'] ? ' — '.e($o['centro']) : '' ?><?= $o['regional'] ? ' ('.e($o['regional']).')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>

        <div class="row" style="margin-top:8px;display:none">
          <div style="flex:1 1 33%">
            <label>Regional (opcional)</label>
            <input name="regional" value="<?= e($_POST['regional'] ?? '') ?>">
          </div>
          <div style="flex:1 1 33%">
            <label>Centro (opcional)</label>
            <input name="centro" value="<?= e($_POST['centro'] ?? '') ?>">
          </div>
          <div style="flex:1 1 33%">
            <label>Nombre completo (opcional)</label>
            <input name="nombres" value="<?= e($_POST['nombres'] ?? '') ?>" placeholder="Nombres y apellidos">
          </div>
        </div>

        <p class="mut">El token expira en 90 días. Los datos se obtienen automáticamente de la base de datos.</p>
        <button class="btn">Generar</button>

        <?php if ($gen): ?>
          <hr>
          <p><strong>URL:</strong><br><a class="link" href="<?= e($gen['url']) ?>" target="_blank"><?= e($gen['url']) ?></a></p>
          <p><strong>TOKEN:</strong></p>
          <pre><?= e($gen['token']) ?></pre>
          <?php if ($qrDataUrl): ?>
            <p><strong>QR:</strong></p>
            <img class="qr" src="<?= $qrDataUrl ?>" alt="QR">
            <p class="mut" style="font-size:11px">Guardado: <code style="font-size:10px"><?= basename($gen['qr']) ?></code></p>
          <?php endif; ?>
        <?php endif; ?>
      </form>

      <!-- Verificar -->
      <form class="card" method="post">
        <input type="hidden" name="ui_action" value="verify">
        <h2>2) Verificar token</h2>
        <label>Pega el token</label>
        <textarea name="token" rows="4" style="font-size:11px"><?= e($_POST['token'] ?? '') ?></textarea>
        <div style="margin-top:6px"><button class="btn">Verificar</button></div>
        <?php if ($ver): ?>
          <?php if (!empty($ver['ok'])): ?>
            <hr>
            <p><strong>✓ Token Válido</strong></p>
            <!-- <p><strong>ID:</strong> <?= (int)$ver['oid'] ?></p> -->
            <p><strong>Regional:</strong> <?= e($ver['regional']) ?></p>
            <p><strong>Centro:</strong> <?= e($ver['centro']) ?></p>
            <p><strong>Nombre:</strong> <?= e($ver['nombres']) ?></p>
          <?php else: ?>
            <div class="err"><?= e($ver['error'] ?? 'Token inválido') ?></div>
          <?php endif; ?>
        <?php endif; ?>
      </form>
    </div>

    <!-- <div class="card" style="margin-top:12px">
      <h2>3) Integrar en tu formulario</h2>
      <pre>&lt;?php
include __DIR__.'/enlace_orientador.php';
$ORIENT = enlace_include(); // lee ?t=...
if (!$ORIENT['ok']) { echo '&lt;div class="err"&gt;'.htmlspecialchars($ORIENT['error'], ENT_QUOTES, 'UTF-8').'&lt;/div&gt;'; }
?&gt;

&lt;input type="hidden" name="orientador_id" value="&lt;?= (int)$ORIENT['oid'] ?&gt;"&gt;

&lt;!-- Al guardar: usar SIEMPRE $_SESSION['token_orientador']['oid'] --&gt;</pre>
    </div> -->
  </div>
</body>
</html>
<?php endif; ?>
            