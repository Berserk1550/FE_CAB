<?php
/**
 * MONITOR SECURITY - Auditoría de seguridad
 * PROTECCIÓN: Solo accesible con autenticación
 */

// Prevenir acceso directo
if(!defined('MONITOR_ACCESS')){
    if(!isset($_SESSION))session_start();
    if(!isset($_SESSION['monitor_auth'])||$_SESSION['monitor_auth']!==true){
        header('Location: monitor_dashboard.php');
        exit;
    }
    define('MONITOR_ACCESS',true);
}

require_once 'config.inc.php';
require_once 'auth.inc.php';
require_once 'security.inc.php';
require_auth();
process_logout();

// ═══════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DE RUTAS A ESCANEAR
// ═══════════════════════════════════════════════════════════════════
// Edita estas rutas según tu estructura de archivos

$ARCHIVOS_CRITICOS = [
    // Formato: 'Nombre descriptivo' => 'ruta/relativa/desde/BASE_PATH'
    'Conexión BD' => '/conexion.php',
    'Autenticador' => '/php_Login/autenticador.php',
    'Panel Orientador' => '/php/panel_orientador.php',
    'Dashboard' => '/../dashboard.php',
    'Verificar identidad' => '/php_Login/verificar_identidad.php',
    'Actualizar datos de usuario (formulario)' => '/php_Login/actualizar_datos.php',
    'Actualizar usuario (sistema)' => '/php_Login/actualizar_usuario.php',
    'Procesar actualización' => '/php_Login/procesar_actualizacion.php',
    'Sistema de correos automáticos' => '/php/sistema_correos_automaticos.php',
    'Archivo para la tarjeta de estadística (login)' => '/php/tarjeta_estadistica.php',
    'Guardar formulario' => '/php/guardar_formulario.php',
    'Formulario emprendedor' => '/../formulario_emprendedores/registro_emprendedores.php',
    'Guardar identificar problema' => '/php/guardar_necesidades.php',
    'Guardar tarjeta persona' => '/php/guardar_tarjeta_persona.php',
    'Guardar jobs to be done' => '/php/guardar_jobs.php',
    'Guardar lean canvas' => '/php/guarda_lean_canvas.php',
    'Config Monitor' => '/logs/interno/config.inc.php',
    'Auth Monitor' => '/logs/interno/auth.inc.php',
    'Security Monitor' => '/logs/interno/security.inc.php',
];

// ═══════════════════════════════════════════════════════════════════

function scan_critical_files(){
    global $ARCHIVOS_CRITICOS;
    $results=[];
    foreach($ARCHIVOS_CRITICOS as $name=>$relpath){
        $file=BASE_PATH.$relpath;
        if(file_exists($file)){
            $perms=substr(sprintf('%o',fileperms($file)),-4);
            $size=filesize($file);
            $modified=date('Y-m-d H:i:s',filemtime($file));
            $hash=md5_file($file);
            $results[]=[
                'name'=>$name,
                'file'=>$relpath,
                'exists'=>true,
                'perms'=>$perms,
                'size'=>$size,
                'modified'=>$modified,
                'hash'=>$hash
            ];
        }else{
            $results[]=[
                'name'=>$name,
                'file'=>$relpath,
                'exists'=>false
            ];
        }
    }
    return $results;
}

function check_php_config(){
    $checks=[
        ['name'=>'display_errors','value'=>ini_get('display_errors'),'safe'=>'0','status'=>ini_get('display_errors')=='0'],
        ['name'=>'expose_php','value'=>ini_get('expose_php'),'safe'=>'0','status'=>ini_get('expose_php')=='0'],
        ['name'=>'allow_url_fopen','value'=>ini_get('allow_url_fopen'),'safe'=>'0','status'=>true],
        ['name'=>'memory_limit','value'=>ini_get('memory_limit'),'safe'=>'128M+','status'=>true],
    ];
    return $checks;
}

function get_failed_logins(){
    $file=LOG_DIR.'security_'.date('Y-m-d').'.log';
    if(!file_exists($file))return [];
    $content=file($file);
    $failed=[];
    foreach($content as $line){
        if(stripos($line,'fallido')!==false||stripos($line,'bloqueado')!==false){
            $failed[]=$line;
        }
    }
    return array_slice(array_reverse($failed),0,20);
}

$scan_files=scan_critical_files();
$php_config=check_php_config();
$failed_logins=get_failed_logins();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Auditoría de Seguridad - Monitor</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:-apple-system,sans-serif;background:#0a0a0a;color:#e0e0e0;padding:20px;}
.container{max-width:1400px;margin:0 auto;}
.header{background:#1a1a1a;border:2px solid #333;padding:20px 25px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;}
.header h1{font-size:24px;color:#fff;}
.nav{background:#1a1a1a;border:2px solid #333;padding:0;margin-bottom:20px;display:flex;flex-wrap:wrap;}
.nav a{padding:15px 20px;color:#999;text-decoration:none;border-right:1px solid #333;font-weight:600;font-size:13px;}
.nav a:hover{background:#222;color:#fff;}
.nav a.active{background:#fff;color:#000;}
.btn{padding:10px 20px;background:#fff;color:#000;border:none;cursor:pointer;font-size:13px;font-weight:600;text-decoration:none;display:inline-block;}
.btn:hover{background:#e0e0e0;}
.card{background:#1a1a1a;border:2px solid #333;padding:25px;margin-bottom:20px;}
.card h3{font-size:16px;margin-bottom:20px;color:#fff;text-transform:uppercase;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{padding:12px;text-align:left;border-bottom:1px solid #333;font-size:13px;}
.table th{background:#0a0a0a;font-weight:700;font-size:11px;text-transform:uppercase;color:#999;}
.badge{display:inline-block;padding:4px 8px;font-size:11px;font-weight:700;text-transform:uppercase;}
.badge-success{background:#00ff00;color:#000;}
.badge-danger{background:#ff0000;color:#fff;}
.badge-warning{background:#ffaa00;color:#000;}
.alert{padding:15px;margin-bottom:15px;border-left:4px solid;background:#2a1a1a;}
.alert.warning{border-color:#ffaa00;color:#ffaa00;}
.alert.info{border-color:#00aaff;color:#00aaff;}
.alert strong{display:block;margin-bottom:5px;}
.log-line{font-family:'Courier New',monospace;font-size:12px;padding:5px;background:#0a0a0a;border-bottom:1px solid #1a1a1a;color:#ff6666;}
.info{color:#666;font-size:12px;margin-top:10px;}
.config-note{background:#1a2a1a;border:1px solid #00ff00;color:#00ff00;padding:15px;margin-bottom:20px;font-size:13px;}
</style>
</head>
<body>
<div class="container">
<div class="header">
<h1>AUDITORÍA DE SEGURIDAD</h1>
<a href="?logout" class="btn">SALIR</a>
</div>

<div class="nav">
<a href="monitor_dashboard">DASHBOARD</a>
<a href="monitor_database">BASE DE DATOS</a>
<a href="monitor_files">ARCHIVOS</a>
<a href="monitor_logs">LOGS</a>
<a href="monitor_security" class="active">SEGURIDAD</a>
<a href="monitor_tools">HERRAMIENTAS</a>
</div>

<div class="card">
<h3>⚙️ Configuración de Rutas</h3>
<div class="config-note">
<strong>ℹ️ Para cambiar las rutas escaneadas:</strong><br>
1. Abre monitor_security.php en el editor<br>
2. Busca la sección "CONFIGURACIÓN DE RUTAS A ESCANEAR" (línea 25)<br>
3. Edita el array $ARCHIVOS_CRITICOS con tus rutas<br>
4. Formato: 'Nombre' => '/ruta/relativa/desde/BASE_PATH'
</div>
</div>

<div class="card">
<h3>🔍 Archivos Críticos Escaneados</h3>
<table class="table">
<thead>
<tr>
<th>Archivo</th>
<th>Ruta</th>
<th>Estado</th>
<th>Permisos</th>
<th>Tamaño</th>
<th>Modificado</th>
<th>Hash MD5</th>
</tr>
</thead>
<tbody>
<?php foreach($scan_files as $f): ?>
<tr>
<td><strong><?php echo e($f['name']); ?></strong></td>
<td style="font-size:11px;color:#666;"><?php echo e($f['file']); ?></td>
<td>
<?php if($f['exists']): ?>
<span class="badge badge-success">EXISTE</span>
<?php else: ?>
<span class="badge badge-danger">NO EXISTE</span>
<?php endif; ?>
</td>
<td><?php echo $f['exists']?$f['perms']:'-'; ?></td>
<td><?php echo $f['exists']?format_bytes($f['size']):'-'; ?></td>
<td style="font-size:11px;"><?php echo $f['exists']?$f['modified']:'-'; ?></td>
<td style="font-family:monospace;font-size:10px;"><?php echo $f['exists']?substr($f['hash'],0,12).'...':'-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="info">
⚠️ Guarda los hashes MD5 en un lugar seguro. Cambios inesperados pueden indicar modificaciones maliciosas.
</div>
</div>

<div class="card">
<h3>⚙ Configuración PHP</h3>
<table class="table">
<thead>
<tr>
<th>Configuración</th>
<th>Valor Actual</th>
<th>Valor Seguro</th>
<th>Estado</th>
</tr>
</thead>
<tbody>
<?php foreach($php_config as $c): ?>
<tr>
<td><?php echo $c['name']; ?></td>
<td><?php echo $c['value']; ?></td>
<td><?php echo $c['safe']; ?></td>
<td>
<?php if($c['status']): ?>
<span class="badge badge-success">OK</span>
<?php else: ?>
<span class="badge badge-warning">REVISAR</span>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="card">
<h3>🚨 Intentos de Login Fallidos (Hoy)</h3>
<?php if(count($failed_logins)>0): ?>
<?php foreach($failed_logins as $log): ?>
<div class="log-line"><?php echo e($log); ?></div>
<?php endforeach; ?>
<div class="info">Mostrando últimos 20 intentos fallidos</div>
<?php else: ?>
<div class="alert info">
<strong>✓ No hay intentos de login fallidos hoy</strong>
Sistema seguro - no se han detectado intentos de acceso no autorizado.
</div>
<?php endif; ?>
</div>

<div class="card">
<h3>💡 Recomendaciones de Seguridad</h3>
<div class="alert warning">
<strong>1. Cambiar credenciales por defecto</strong><br>
Edita auth.inc.php y cambia ADMIN_USER y la contraseña
</div>
<div class="alert warning">
<strong>2. Revisar permisos de archivos</strong><br>
Archivos PHP: 644 | Directorios: 755 | Archivos .inc.php: 600 (solo lectura por owner)
</div>
<div class="alert warning">
<strong>3. Monitorear logs regularmente</strong><br>
Revisa /servicios/logs/security_*.log diariamente
</div>
<div class="alert warning">
<strong>4. Proteger archivos .inc.php con .htaccess</strong><br>
Crea .htaccess en /servicios/logs/ para bloquear acceso directo a archivos .inc.php
</div>
<div class="alert warning">
<strong>5. Actualizar rutas de escaneo</strong><br>
Edita $ARCHIVOS_CRITICOS en este archivo para escanear tus archivos críticos específicos
</div>
</div>
</div>
</body>
</html>