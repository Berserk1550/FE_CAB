<?php
declare(strict_types=1);
@ini_set('default_charset', 'UTF-8');
header('Content-Type: text/html; charset=UTF-8');

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function p($k){ return trim((string)($_POST[$k] ?? $_GET[$k] ?? '')); }

/* ==== Derivar remitente del dominio del sitio (no uses Gmail aquí) ==== */
function base_domain_from_host(string $host): string {
  $host = strtolower(trim($host));
  $host = preg_replace('~:\d+$~', '', $host);           // quita puerto
  if (filter_var($host, FILTER_VALIDATE_IP)) return 'localhost.localdomain';
  $host = preg_replace('~^www\.~i', '', $host);         // quita www.
  $host = preg_replace('~[^a-z0-9\.\-]~i', '', $host);  // limpia raro
  return $host ?: 'localhost.localdomain';
}

$host = base_domain_from_host((string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
$FROM_EMAIL  = 'fondoemprendersoporte@' . $host;    // <-- remitente del MISMO dominio
$SENDER_EMAIL= $FROM_EMAIL;            // Sender/Return-Path
$FROM_NAME   = 'Ruta Emprendedora';
$ALLOW_DEBUG = true;

/* ============ Captura destino ============ */
function get_destinatario(): string {
  $cands = [ p('to'), p('correo'), p('email'), p('t') ];
  foreach ($cands as $c) if ($c !== '' && filter_var($c, FILTER_VALIDATE_EMAIL)) return $c;

  $b64 = p('to_b64');
  if ($b64 !== '') {
    $dec = base64_decode($b64, true);
    if ($dec !== false && filter_var($dec, FILTER_VALIDATE_EMAIL)) return $dec;
  }

  $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
  if ($uri) {
    $parts = explode('?', $uri, 2);
    $path  = $parts[0];
    if (preg_match('~test_envio_correo\.php(?:/|%2F)([^/]+)$~i', $path, $m)) {
      $raw = urldecode($m[1]);
      if (filter_var($raw, FILTER_VALIDATE_EMAIL)) return $raw;
    }
  }

  foreach ($cands as $c) {
    $c = str_replace(' ', '+', (string)$c);
    if ($c !== '' && filter_var($c, FILTER_VALIDATE_EMAIL)) return $c;
  }
  return '';
}

/* ============ Plantilla rápida ============ */
function tpl(string $to, string $tipo, string $nombre=''): array {
  $dom = strtolower((string)substr(strrchr($to, '@') ?: '', 1));
  $isSena = (strpos($dom, 'sena.edu.co') !== false);
  $asuntos = [
    'registro' => $isSena ? 'Registro confirmado — Ruta Emprendedora (SENA)' : '¡Gracias por registrarte! — Ruta Emprendedora',
    'mensaje'  => $isSena ? 'Hemos recibido tu mensaje — CDE SENA'          : 'Recibimos tu mensaje — Ruta Emprendedora',
    'llamada'  => $isSena ? 'Solicitud de llamada recibida — CDE SENA'       : 'Solicitud de llamada recibida — Ruta Emprendedora',
  ];
  $asunto = $asuntos[$tipo] ?? $asuntos['registro'];

  if ($tipo === 'mensaje') {
    $html = $isSena
      ? "<div style='font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;line-height:1.55'><h2 style='margin:0 0 8px'>Hola ".h($nombre)."</h2><p>Tu mensaje fue recibido por el <strong>Centro de Desarrollo Empresarial</strong>.</p><p>Un orientador del SENA te responderá en breve.</p></div>"
      : "<div style='font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;line-height:1.55'><h2 style='margin:0 0 8px'>¡Hola ".h($nombre)."!</h2><p>Recibimos tu mensaje y pronto te contactaremos.</p></div>";
  } elseif ($tipo === 'llamada') {
    $html = $isSena
      ? "<div style='font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;line-height:1.55'><h2 style='margin:0 0 8px'>Hola ".h($nombre)."</h2><p>Registramos tu <strong>solicitud de llamada</strong>. Un orientador del SENA te marcará al número asociado a tu registro.</p></div>"
      : "<div style='font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;line-height:1.55'><h2 style='margin:0 0 8px'>¡Hola ".h($nombre)."!</h2><p>Recibimos tu <strong>solicitud de llamada</strong>. Te contactaremos pronto.</p></div>";
  } else {
    $html = $isSena
      ? "<div style='font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;line-height:1.55'><h2 style='margin:0 0 8px'>Hola ".h($nombre)."</h2><p>Tu registro en la <strong>Ruta Emprendedora</strong> quedó confirmado.</p><p>Puedes actualizar tus datos desde el portal cuando lo necesites.</p></div>"
      : "<div style='font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;line-height:1.55'><h2 style='margin:0 0 8px'>¡Hola ".h($nombre)."!</h2><p>¡Gracias por registrarte en la <strong>Ruta Emprendedora</strong>! Pronto te contactaremos.</p></div>";
  }
  return [$asunto, $html];
}

/* ============ Envío nativo + fallback .eml ============ */
function enviar_mail_nativo(string $to, string $asunto, string $html, string $fromEmail, string $fromName, string $senderEmail, array &$diag): bool {
  $headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: '.$fromName.' <'.$fromEmail.'>',
    'Sender: '.$senderEmail,
    'Return-Path: '.$senderEmail,
    'Reply-To: '.$fromName.' <'.$fromEmail.'>',
    'X-Mailer: PHP/'.phpversion(),
  ];
  $subject = '=?UTF-8?B?'.base64_encode($asunto).'?=';
  $params  = ' -f '.$senderEmail; // envelope sender

  $diag['headers'] = $headers;
  $diag['params']  = $params;

  // 1) intenta mail()
  if (function_exists('mail')) {
    $ok = @mail($to, $subject, $html, implode("\r\n", $headers), $params);
    if ($ok) return true;
  } else {
    $diag['note'] = 'La función mail() no existe en este hosting.';
  }

  // 2) Fallback: guarda .eml para no perder el mensaje
  $dir = __DIR__ . '/emails_outbox';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);

  $raw =
    "From: ".$fromName." <".$fromEmail.">\r\n".
    "Sender: ".$senderEmail."\r\n".
    "To: <".$to.">\r\n".
    "Subject: ".$subject."\r\n".
    "MIME-Version: 1.0\r\n".
    "Content-Type: text/html; charset=UTF-8\r\n".
    "Content-Transfer-Encoding: 8bit\r\n".
    "X-Mailer: PHP/".phpversion()."\r\n\r\n".
    $html;

  $file = $dir.'/'.date('Ymd_His').'_'.preg_replace('~[^a-z0-9_.-]~i','_', $to).'.eml';
  if (@file_put_contents($file, $raw) !== false) {
    $diag['saved_eml'] = $file;
  } else {
    $diag['saved_eml_error'] = 'No se pudo escribir el archivo .eml';
  }
  return false;
}

/* ----------- Ejecuta ----------- */
$to     = get_destinatario();
$nombre = p('nombre') ?: p('nombres') ?: p('name');
$tipo   = strtolower(p('tipo') ?: 'registro');

if ($ALLOW_DEBUG && (isset($_GET['debug']) || isset($_POST['debug']))) {
  echo "<pre style='background:#f8fff9;border:1px solid #d1fae5;padding:8px'>";
  echo "HOST: ".h($host)."\n";
  echo "GET : ".h(json_encode($_GET, JSON_UNESCAPED_UNICODE))."\n";
  echo "POST: ".h(json_encode($_POST, JSON_UNESCAPED_UNICODE))."\n";
  echo "URI : ".h($_SERVER['REQUEST_URI'] ?? '')."\n";
  echo "TO detectado: ".h($to)."\n";
  echo "FROM: ".h($FROM_EMAIL)." | SENDER: ".h($SENDER_EMAIL)."\n";
  echo "</pre>";
}

if ($to === '') {
  http_response_code(400);
  echo '<h3>Error</h3><p>Debes pasar un correo válido en <code>?to=</code>, <code>?correo=</code>, <code>?email=</code>, <code>?t=</code>, <code>?to_b64=</code> o como segmento en la URL: <code>/test_envio_correo.php/tu@correo.com</code>.</p>';
  exit;
}

[$asunto, $html] = tpl($to, $tipo, $nombre ?: '');
$diag = [];
$ok = enviar_mail_nativo($to, $asunto, $html, $FROM_EMAIL, $FROM_NAME, $SENDER_EMAIL, $diag);

if ($ok) {
  echo '<h2>✅ Envío correcto</h2><p>Destino: <strong>'.h($to).'</strong></p><p>Asunto: '.h($asunto).'</p>';
} else {
  http_response_code(500);
  echo '<h2>❌ Falló el envío</h2><p>Destino: <strong>'.h($to).'</strong></p><p>Asunto: '.h($asunto).'</p>';
  echo '<p>El hosting rechazó <code>mail()</code> (o no está disponible) <strong>con el remitente actual</strong>.</p>';
  if (!empty($diag['saved_eml'])) {
    echo '<p>Guardé el correo en: <code>'.h($diag['saved_eml']).'</code> (puedes abrir ese .eml y reenviarlo desde tu cliente de correo).</p>';
  }
  if (!empty($_GET['debug']) || !empty($_POST['debug'])) {
    echo "<pre style='background:#fff;border:1px solid #eee;padding:8px;margin-top:8px'>".h(print_r($diag, true))."</pre>";
  }
}
