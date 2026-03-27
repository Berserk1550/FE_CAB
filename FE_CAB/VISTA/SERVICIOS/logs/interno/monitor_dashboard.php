<?php
/**
 * MONITOR DASHBOARD v3 - Punto de entrada principal
 * PROTECCIÓN: Solo se puede acceder mediante autenticación
 */

define('MONITOR_ACCESS', true);

require_once 'config.inc.php';
require_once 'auth.inc.php';
require_once 'security.inc.php';

process_logout();
$login_result = process_login();

$autenticado = isset($_SESSION['monitor_auth']) && $_SESSION['monitor_auth'] === true;

// Si no está autenticado, mostrar login
if (!$autenticado) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Monitor Sistema</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: #0a0a0a;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-box {
                background: #1a1a1a;
                border: 2px solid #333;
                padding: 40px;
                width: 100%;
                max-width: 400px;
            }
            h1 {
                color: #fff;
                font-size: 24px;
                margin-bottom: 5px;
                text-align: center;
            }
            .version {
                color: #666;
                font-size: 11px;
                text-align: center;
                margin-bottom: 30px;
            }
            .error {
                background: #2a1a1a;
                border: 1px solid #ff0000;
                color: #ff0000;
                padding: 10px;
                margin-bottom: 20px;
                font-size: 13px;
                text-align: center;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                color: #999;
                font-size: 13px;
                margin-bottom: 8px;
            }
            input {
                width: 100%;
                padding: 12px;
                background: #0a0a0a;
                border: 1px solid #333;
                color: #fff;
                font-size: 14px;
            }
            input:focus {
                outline: none;
                border-color: #666;
            }
            button {
                width: 100%;
                padding: 12px;
                background: #fff;
                border: none;
                color: #000;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
            }
            button:hover {
                background: #e0e0e0;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>MONITOR SISTEMA</h1>
            <div class="version">v<?php echo SYSTEM_VERSION; ?></div>

            <?php if ($login_result && isset($login_result['error'])): ?>
                <div class="error"><?php echo e($login_result['error']); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>USUARIO</label>
                    <input type="text" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label>CONTRASEÑA</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login">INICIAR SESIÓN</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/* ============================
 * FUNCIONES DE MONITOREO
 * ============================ */

function get_cpu() {
    if (!file_exists('/proc/stat')) return null;

    try {
        $s1 = @fopen('/proc/stat', 'r');
        if (!$s1) return null;
        $l1 = fgets($s1);
        fclose($s1);

        usleep(100000); // 100ms

        $s2 = @fopen('/proc/stat', 'r');
        if (!$s2) return null;
        $l2 = fgets($s2);
        fclose($s2);

        if (!$l1 || !$l2) return null;

        $i1 = explode(" ", preg_replace("!cpu +!", "", trim($l1)));
        $i2 = explode(" ", preg_replace("!cpu +!", "", trim($l2)));

        $d = [];
        for ($i = 0; $i < 4; $i++) {
            $d[$i] = (int)$i2[$i] - (int)$i1[$i];
        }

        $t = array_sum($d);
        if ($t === 0) return null;

        return round(100 - ($d[3] / $t * 100), 1);
    } catch (Exception $e) {
        return null;
    }
}

function get_mem() {
    if (!file_exists('/proc/meminfo')) return null;

    try {
        $m = @file_get_contents('/proc/meminfo');
        if (!$m) return null;

        preg_match_all('/(\w+):\s+(\d+)/', $m, $ma);
        $i = array_combine($ma[1], $ma[2]);

        $t = ($i['MemTotal'] ?? 0) * 1024;
        $f = ($i['MemFree'] ?? 0) * 1024;

        if ($t == 0) return null;

        $u = $t - $f;
        $p = round(($u / $t) * 100, 2);

        return [
            'total'   => $t,
            'used'    => $u,
            'free'    => $f,
            'percent' => $p,
            'total_f' => format_bytes($t),
            'used_f'  => format_bytes($u),
            'free_f'  => format_bytes($f),
        ];
    } catch (Exception $e) {
        return null;
    }
}

function get_disk() {
    try {
        $t = @disk_total_space('/');
        $f = @disk_free_space('/');

        if (!$t || !$f) return null;

        $u = $t - $f;
        $p = round(($u / $t) * 100, 2);

        return [
            'total'   => $t,
            'used'    => $u,
            'free'    => $f,
            'percent' => $p,
            'total_f' => format_bytes($t),
            'used_f'  => format_bytes($u),
            'free_f'  => format_bytes($f),
        ];
    } catch (Exception $e) {
        return null;
    }
}

function get_mysql() {
    try {
        $c = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($c->connect_error) {
            return ['status' => 'ERROR', 'error' => $c->connect_error];
        }

        $h = [
            'status'  => 'OK',
            'version' => $c->server_info,
            'threads' => 0,
        ];

        $r = @$c->query("SHOW GLOBAL STATUS WHERE Variable_name='Threads_connected'");
        if ($r && $row = $r->fetch_assoc()) {
            $h['threads'] = (int)$row['Value'];
        }

        $c->close();
        return $h;
    } catch (Exception $e) {
        return ['status' => 'ERROR', 'error' => $e->getMessage()];
    }
}

function check_url($url) {
    try {
        if (!function_exists('curl_init')) {
            return ['status' => 'ERROR', 'code' => 0, 'time' => 0];
        }

        $start = microtime(true);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        @curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $time = round((microtime(true) - $start) * 1000, 2);
        curl_close($ch);

        return [
            'status' => ($code >= 200 && $code < 400) ? 'OK' : 'ERROR',
            'code'   => $code,
            'time'   => $time,
        ];
    } catch (Exception $e) {
        return ['status' => 'ERROR', 'code' => 0, 'time' => 0];
    }
}

function parse_ini_bytes($val) {
    if ($val === '' || $val === false || $val === null) {
        return -1;
    }

    $val = trim((string)$val);
    if ($val === '-1') {
        return -1;
    }

    $last = strtolower(substr($val, -1));
    $num  = (float)$val;

    switch ($last) {
        case 'g':
            $num *= 1024;
        case 'm':
            $num *= 1024;
        case 'k':
            $num *= 1024;
    }

    return (int)$num;
}

function get_php_memory() {
    $usage = memory_get_usage(true);
    $peak  = memory_get_peak_usage(true);
    $limitRaw = ini_get('memory_limit');
    $limit = parse_ini_bytes($limitRaw);

    $percent = null;
    if ($limit > 0) {
        $percent = round(($usage / $limit) * 100, 2);
    }

    return [
        'usage'     => $usage,
        'usage_f'   => format_bytes($usage),
        'peak'      => $peak,
        'peak_f'    => format_bytes($peak),
        'limit'     => $limit,
        'limit_f'   => $limit > 0 ? format_bytes($limit) : 'Sin límite',
        'percent'   => $percent,
        'limit_raw' => $limitRaw,
    ];
}

function get_uptime() {
    if (PHP_OS_FAMILY !== 'Linux' || !is_readable('/proc/uptime')) {
        return null;
    }

    $data = @file_get_contents('/proc/uptime');
    if (!$data) return null;

    $parts = explode(' ', trim($data));
    $secs  = (int)$parts[0];

    $days  = intdiv($secs, 86400);
    $secs %= 86400;
    $hours = intdiv($secs, 3600);
    $secs %= 3600;
    $mins  = intdiv($secs, 60);

    return [
        'raw_seconds' => (int)$parts[0],
        'formatted'   => sprintf('%dd %dh %dm', $days, $hours, $mins),
    ];
}

function get_loadavg() {
    if (!function_exists('sys_getloadavg')) return null;

    $load = sys_getloadavg();
    if (!is_array($load) || count($load) < 3) {
        return null;
    }

    return [
        '1m'  => round($load[0], 2),
        '5m'  => round($load[1], 2),
        '15m' => round($load[2], 2),
    ];
}

function calc_health($cpu, $mem, $disk, $mysql, $phpMem = null) {
    $s = 100;

    if ($cpu && $cpu > 90)      $s -= 20;
    elseif ($cpu && $cpu > 70) $s -= 10;

    if ($mem && $mem['percent'] > 95)      $s -= 20;
    elseif ($mem && $mem['percent'] > 80) $s -= 10;

    if ($disk && $disk['percent'] > 90)      $s -= 20;
    elseif ($disk && $disk['percent'] > 75) $s -= 10;

    if ($mysql['status'] !== 'OK') $s -= 30;

    if ($phpMem && $phpMem['percent'] !== null) {
        if ($phpMem['percent'] > 90)      $s -= 15;
        elseif ($phpMem['percent'] > 75) $s -= 8;
    }

    $s = max(0, $s);

    return [
        'score' => $s,
        'level' => $s >= 90 ? 'excellent' :
                  ($s >= 75 ? 'good' :
                  ($s >= 50 ? 'fair' : 'poor')),
        'label' => $s >= 90 ? 'Excelente' :
                  ($s >= 75 ? 'Bueno' :
                  ($s >= 50 ? 'Regular' : 'Crítico')),
    ];
}

/* ============================
 * RECOLECCIÓN DE MÉTRICAS
 * ============================ */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

$cpu    = get_cpu();
$mem    = get_mem();
$disk   = get_disk();
$mysql  = get_mysql();
$phpMem = get_php_memory();
$uptime = get_uptime();
$load   = get_loadavg();

$health = calc_health($cpu, $mem, $disk, $mysql, $phpMem);

$crit = [];
if ($cpu && $cpu > 90) {
    $crit[] = ['title' => 'CPU Crítica', 'msg' => 'Uso: ' . $cpu . '%'];
}
if ($mem && $mem['percent'] > 95) {
    $crit[] = ['title' => 'RAM Crítica (Sistema)', 'msg' => 'Uso: ' . $mem['percent'] . '%'];
}
if ($disk && $disk['percent'] > 90) {
    $crit[] = ['title' => 'Disco Casi Lleno', 'msg' => 'Uso: ' . $disk['percent'] . '%'];
}
if ($mysql['status'] !== 'OK') {
    $crit[] = ['title' => 'MySQL Error', 'msg' => $mysql['error'] ?? 'Sin conexión'];
}
if ($phpMem && $phpMem['percent'] !== null && $phpMem['percent'] > 90) {
    $crit[] = [
        'title' => 'Memoria PHP Crítica',
        'msg'   => 'Uso del script: ' . $phpMem['percent'] . '% de ' . $phpMem['limit_f'],
    ];
}

// Respuesta AJAX ligera (JSON) para auto-actualización
if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'time'   => date('Y-m-d H:i:s'),
        'cpu'    => $cpu,
        'mem'    => $mem,
        'disk'   => $disk,
        'mysql'  => $mysql,
        'phpMem' => $phpMem,
        'uptime' => $uptime,
        'load'   => $load,
        'health' => $health,
        'crit'   => $crit,
    ]);
    exit;
}

// URLs críticas solo se calculan en carga "completa", no en cada AJAX
$urls = [];
foreach ($GLOBALS['urls_criticas'] as $n => $u) {
    $urls[$n] = check_url($u);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Monitor</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:-apple-system,sans-serif;background:#0a0a0a;color:#e0e0e0;padding:20px;}
.container{max-width:1400px;margin:0 auto;}
.header{background:#1a1a1a;border:2px solid #333;padding:20px 25px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;}
.header h1{font-size:24px;color:#fff;}
.header-info{font-size:12px;color:#666;margin-top:5px;}
.nav{background:#1a1a1a;border:2px solid #333;padding:0;margin-bottom:20px;display:flex;flex-wrap:wrap;}
.nav a{padding:15px 20px;color:#999;text-decoration:none;border-right:1px solid #333;font-weight:600;font-size:13px;}
.nav a:hover{background:#222;color:#fff;}
.nav a.active{background:#fff;color:#000;}
.btn{padding:10px 20px;background:#fff;color:#000;border:none;cursor:pointer;font-size:13px;font-weight:600;text-decoration:none;display:inline-block;}
.btn:hover{background:#e0e0e0;}
.btn-secondary{background:#222;color:#fff;border:1px solid #555;}
.btn-secondary:hover{background:#333;}
.header-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.auto-refresh-toggle{display:flex;align-items:center;font-size:11px;color:#aaa;gap:6px;}
.auto-refresh-toggle input{accent-color:#00ff00;}
.card{background:#1a1a1a;border:2px solid #333;padding:25px;margin-bottom:20px;}
.card h3{font-size:16px;margin-bottom:20px;color:#fff;text-transform:uppercase;letter-spacing:1px;}
.grid{display:grid;gap:20px;}
.grid-4{grid-template-columns:repeat(auto-fit,minmax(250px,1fr));}
.grid-2{grid-template-columns:repeat(auto-fit,minmax(300px,1fr));}
.metric-box{text-align:center;padding:20px;background:#0a0a0a;border:1px solid #333;}
.metric-value{font-size:36px;font-weight:700;margin:10px 0;color:#fff;}
.metric-label{font-size:11px;color:#666;text-transform:uppercase;letter-spacing:1px;}
.metric-box.success{border-color:#00ff00;}
.metric-box.warning{border-color:#ffaa00;}
.metric-box.danger{border-color:#ff0000;}
.metric-box.success .metric-value{color:#00ff00;}
.metric-box.warning .metric-value{color:#ffaa00;}
.metric-box.danger .metric-value{color:#ff0000;}
.health-score{text-align:center;padding:40px;}
.score-circle{width:200px;height:200px;border-radius:50%;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;font-size:60px;font-weight:700;border:10px solid;background:#0a0a0a;}
.score-circle.excellent{border-color:#00ff00;color:#00ff00;}
.score-circle.good{border-color:#00aaff;color:#00aaff;}
.score-circle.fair{border-color:#ffaa00;color:#ffaa00;}
.score-circle.poor{border-color:#ff0000;color:#ff0000;}
.progress-bar{width:100%;height:25px;background:#0a0a0a;border:1px solid #333;margin:15px 0;position:relative;}
.progress-fill{height:100%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#000;}
.progress-fill.success{background:#00ff00;}
.progress-fill.warning{background:#ffaa00;}
.progress-fill.danger{background:#ff0000;}
.alert{padding:15px;margin-bottom:15px;border-left:4px solid #ff0000;background:#2a1a1a;color:#ff6666;}
.alert strong{display:block;margin-bottom:5px;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{padding:12px;text-align:left;border-bottom:1px solid #333;}
.table th{background:#0a0a0a;font-weight:700;font-size:11px;text-transform:uppercase;color:#999;}
.badge{display:inline-block;padding:4px 8px;font-size:11px;font-weight:700;text-transform:uppercase;border-radius:3px;}
.badge-success{background:#00ff00;color:#000;}
.badge-danger{background:#ff0000;color:#fff;}
.small-text{font-size:11px;color:#999;}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>MONITOR SISTEMA</h1>
            <div class="header-info" id="headerInfoTime">
                <?php echo date('Y-m-d H:i:s'); ?>
                | Usuario: <?php echo ADMIN_USER; ?>
                <?php if ($uptime): ?>
                    | Uptime: <?php echo $uptime['formatted']; ?>
                <?php endif; ?>
            </div>
            <div class="header-info small-text">
                PHP <?php echo PHP_VERSION; ?> (<?php echo PHP_SAPI; ?>) | Límite memoria: <?php echo e($phpMem['limit_f']); ?> (<?php echo e($phpMem['limit_raw']); ?>)
            </div>
        </div>
        <div class="header-actions">
            <!-- Botón de refrescar manual (recarga completa) -->
            <button id="btnRefresh" class="btn btn-secondary" type="button">ACTUALIZAR</button>

            <!-- Toggle de auto-refresh (AJAX ligero) -->
            <label class="auto-refresh-toggle" for="autoRefresh">
                <input type="checkbox" id="autoRefresh">
                <span>Auto 5s</span>
            </label>

            <!-- Botón salir -->
            <a href="?logout" class="btn">SALIR</a>
        </div>
    </div>

    <div class="nav">
        <a href="monitor_dashboard" class="active">DASHBOARD</a>
        <a href="monitor_database">BASE DE DATOS</a>
        <a href="monitor_files">ARCHIVOS</a>
        <a href="monitor_logs">LOGS</a>
        <a href="monitor_security">SEGURIDAD</a>
        <a href="monitor_tools">HERRAMIENTAS</a>
    </div>

    <div class="card health-score">
        <h3>Health Score</h3>
        <div id="healthCircle" class="score-circle <?php echo $health['level']; ?>"><?php echo $health['score']; ?></div>
        <h2 id="healthLabel" style="color:#fff;font-size:20px;"><?php echo $health['label']; ?></h2>
        <?php if ($load): ?>
            <p class="small-text" id="loadInfo" style="margin-top:10px;">
                Load avg: 1m <?php echo $load['1m']; ?> | 5m <?php echo $load['5m']; ?> | 15m <?php echo $load['15m']; ?>
            </p>
        <?php else: ?>
            <p class="small-text" id="loadInfo" style="margin-top:10px;">
                Load avg: N/D
            </p>
        <?php endif; ?>
    </div>

    <div class="card" id="critCard" style="<?php echo count($crit) ? '' : 'display:none;'; ?>">
        <h3>⚠ Problemas Críticos</h3>
        <div id="critContainer">
            <?php foreach ($crit as $i): ?>
                <div class="alert">
                    <strong><?php echo e($i['title']); ?></strong><?php echo e($i['msg']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid grid-4">
        <div class="card">
            <h3>CPU</h3>
            <?php if ($cpu !== null): ?>
                <div id="cpuBox" class="metric-box <?php echo $cpu > 90 ? 'danger' : ($cpu > 70 ? 'warning' : 'success'); ?>">
                    <div class="metric-label">Uso</div>
                    <div class="metric-value" id="cpuValue"><?php echo $cpu; ?>%</div>
                </div>
                <div class="progress-bar">
                    <div id="cpuBar" class="progress-fill <?php echo $cpu > 90 ? 'danger' : ($cpu > 70 ? 'warning' : 'success'); ?>" style="width:<?php echo $cpu; ?>%">
                        <?php echo $cpu; ?>%
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align:center;color:#666;padding:20px;">NO DISPONIBLE</div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>MEMORIA SISTEMA</h3>
            <?php if ($mem): ?>
                <div id="memBox" class="metric-box <?php echo $mem['percent'] > 95 ? 'danger' : ($mem['percent'] > 80 ? 'warning' : 'success'); ?>">
                    <div class="metric-label">Uso</div>
                    <div class="metric-value" id="memValue"><?php echo $mem['percent']; ?>%</div>
                </div>
                <div class="progress-bar">
                    <div id="memBar" class="progress-fill <?php echo $mem['percent'] > 95 ? 'danger' : ($mem['percent'] > 80 ? 'warning' : 'success'); ?>" style="width:<?php echo $mem['percent']; ?>%">
                        <?php echo $mem['percent']; ?>%
                    </div>
                </div>
                <p id="memInfo" class="small-text" style="margin-top:10px;text-align:center;">
                    <?php echo $mem['used_f']; ?> / <?php echo $mem['total_f']; ?>
                </p>
            <?php else: ?>
                <div style="text-align:center;color:#666;padding:20px;">NO DISPONIBLE</div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>DISCO (RAÍZ)</h3>
            <?php if ($disk): ?>
                <div id="diskBox" class="metric-box <?php echo $disk['percent'] > 90 ? 'danger' : ($disk['percent'] > 75 ? 'warning' : 'success'); ?>">
                    <div class="metric-label">Uso</div>
                    <div class="metric-value" id="diskValue"><?php echo $disk['percent']; ?>%</div>
                </div>
                <div class="progress-bar">
                    <div id="diskBar" class="progress-fill <?php echo $disk['percent'] > 90 ? 'danger' : ($disk['percent'] > 75 ? 'warning' : 'success'); ?>" style="width:<?php echo $disk['percent']; ?>%">
                        <?php echo $disk['percent']; ?>%
                    </div>
                </div>
                <p id="diskInfo" class="small-text" style="margin-top:10px;text-align:center;">
                    <?php echo $disk['used_f']; ?> / <?php echo $disk['total_f']; ?>
                </p>
            <?php else: ?>
                <div style="text-align:center;color:#666;padding:20px;">NO DISPONIBLE</div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>MYSQL</h3>
            <?php if ($mysql['status'] === 'OK'): ?>
                <div id="mysqlBox" class="metric-box success">
                    <div class="metric-label">Estado</div>
                    <div class="metric-value" id="mysqlState" style="font-size:24px;">OK</div>
                </div>
                <table style="width:100%;font-size:11px;margin-top:15px;color:#999;">
                    <tr>
                        <td>Versión:</td>
                        <td id="mysqlVersion" style="text-align:right;color:#fff;"><?php echo e($mysql['version']); ?></td>
                    </tr>
                    <tr>
                        <td>Conexiones:</td>
                        <td id="mysqlThreads" style="text-align:right;color:#fff;"><?php echo $mysql['threads']; ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <div id="mysqlBox" class="metric-box danger">
                    <div class="metric-label">Estado</div>
                    <div class="metric-value" id="mysqlState" style="font-size:24px;">ERROR</div>
                </div>
                <p id="mysqlError" style="color:#ff0000;font-size:12px;margin-top:10px;text-align:center;">
                    <?php echo e($mysql['error'] ?? 'Error desconocido'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3>MEMORIA PHP / SERVIDOR</h3>
            <?php if ($phpMem): ?>
                <?php
                $phpPercent = $phpMem['percent'];
                $classPhp = 'success';
                if ($phpPercent !== null) {
                    if ($phpPercent > 90)      $classPhp = 'danger';
                    elseif ($phpPercent > 75) $classPhp = 'warning';
                }
                ?>
                <div id="phpBox" class="metric-box <?php echo $classPhp; ?>">
                    <div class="metric-label">Uso del script</div>
                    <div class="metric-value" id="phpUsageValue" style="font-size:28px;">
                        <?php echo $phpMem['usage_f']; ?>
                    </div>
                </div>
                <?php if ($phpPercent !== null): ?>
                    <div class="progress-bar">
                        <div id="phpBar" class="progress-fill <?php echo $classPhp; ?>" style="width:<?php echo min($phpPercent, 100); ?>%">
                            <?php echo $phpPercent; ?>% de <?php echo e($phpMem['limit_f']); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p id="phpBar" class="small-text" style="margin-top:10px;text-align:center;">
                        Sin límite de memoria configurado (memory_limit = -1)
                    </p>
                <?php endif; ?>
                <p id="phpInfo" class="small-text" style="margin-top:10px;text-align:center;">
                    Pico de memoria en esta petición: <?php echo $phpMem['peak_f']; ?>
                </p>
            <?php else: ?>
                <div style="text-align:center;color:#666;padding:20px;">NO DISPONIBLE</div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>UPTIME & CARGA</h3>
            <ul class="small-text" style="list-style:none;line-height:1.6;">
                <li><strong>Host:</strong> <?php echo php_uname('n'); ?></li>
                <li><strong>SO:</strong> <?php echo php_uname('s') . ' ' . php_uname('r'); ?></li>
                <li><strong>Uptime:</strong> <span id="uptimeText"><?php echo $uptime ? e($uptime['formatted']) : 'N/D'; ?></span></li>
                <li><strong>Load 1m:</strong> <span id="load1m"><?php echo $load ? $load['1m'] : 'N/D'; ?></span></li>
                <li><strong>Load 5m:</strong> <span id="load5m"><?php echo $load ? $load['5m'] : 'N/D'; ?></span></li>
                <li><strong>Load 15m:</strong> <span id="load15m"><?php echo $load ? $load['15m'] : 'N/D'; ?></span></li>
            </ul>
        </div>
    </div>

    <div class="card">
        <h3>URLs Críticas</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>URL</th>
                    <th>Estado</th>
                    <th>Tiempo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($urls as $n => $s): ?>
                    <tr>
                        <td><?php echo e($n); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $s['status'] === 'OK' ? 'success' : 'danger'; ?>">
                                <?php echo $s['code']; ?>
                            </span>
                        </td>
                        <td><?php echo $s['time']; ?>ms</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="small-text" style="margin-top:10px;">
            * El tiempo es de respuesta aproximado haciendo una petición HEAD. Estas métricas se refrescan al recargar completamente la página.
        </p>
    </div>
</div>

<script>
(function () {
    const REFRESH_INTERVAL = 5000; // 5 segundos
    const MONITOR_USER = <?php echo json_encode(ADMIN_USER); ?>;

    const btn = document.getElementById('btnRefresh');
    const chk = document.getElementById('autoRefresh');
    const headerInfoTime = document.getElementById('headerInfoTime');
    let timer = null;

    function setClassLevel(element, value, highWarn, highCrit) {
        if (!element) return;
        element.classList.remove('success', 'warning', 'danger');
        if (value == null) return;
        if (value > highCrit) {
            element.classList.add('danger');
        } else if (value > highWarn) {
            element.classList.add('warning');
        } else {
            element.classList.add('success');
        }
    }

    function applyMetrics(data) {
        if (!data) return;

        // Header time + uptime
        if (headerInfoTime && data.time) {
            let txt = data.time + ' | Usuario: ' + MONITOR_USER;
            if (data.uptime && data.uptime.formatted) {
                txt += ' | Uptime: ' + data.uptime.formatted;
            }
            headerInfoTime.textContent = txt;
        }

        // Health score
        const healthCircle = document.getElementById('healthCircle');
        const healthLabel = document.getElementById('healthLabel');
        if (data.health && healthCircle && healthLabel) {
            healthCircle.textContent = data.health.score;
            healthLabel.textContent = data.health.label;

            healthCircle.classList.remove('excellent', 'good', 'fair', 'poor');
            if (data.health.level) {
                healthCircle.classList.add(data.health.level);
            }
        }

        // Load info
        const loadInfo = document.getElementById('loadInfo');
        if (data.load && loadInfo) {
            loadInfo.textContent = 'Load avg: 1m ' + data.load['1m'] +
                                   ' | 5m ' + data.load['5m'] +
                                   ' | 15m ' + data.load['15m'];
        }

        // CPU
        if (data.cpu != null) {
            const v = parseFloat(data.cpu);
            const cpuValue = document.getElementById('cpuValue');
            const cpuBar = document.getElementById('cpuBar');
            const cpuBox = document.getElementById('cpuBox');
            if (cpuValue) cpuValue.textContent = v.toFixed(1) + '%';
            if (cpuBar) {
                cpuBar.style.width = Math.max(0, Math.min(100, v)) + '%';
                cpuBar.textContent = v.toFixed(1) + '%';
                setClassLevel(cpuBar, v, 70, 90);
            }
            if (cpuBox) {
                setClassLevel(cpuBox, v, 70, 90);
            }
        }

        // Memoria sistema
        if (data.mem) {
            const p = parseFloat(data.mem.percent);
            const memValue = document.getElementById('memValue');
            const memBar = document.getElementById('memBar');
            const memBox = document.getElementById('memBox');
            const memInfo = document.getElementById('memInfo');
            if (memValue && !isNaN(p)) memValue.textContent = p.toFixed(2) + '%';
            if (memBar && !isNaN(p)) {
                memBar.style.width = Math.max(0, Math.min(100, p)) + '%';
                memBar.textContent = p.toFixed(2) + '%';
                setClassLevel(memBar, p, 80, 95);
            }
            if (memBox && !isNaN(p)) {
                setClassLevel(memBox, p, 80, 95);
            }
            if (memInfo && data.mem.used_f && data.mem.total_f) {
                memInfo.textContent = data.mem.used_f + ' / ' + data.mem.total_f;
            }
        }

        // Disco
        if (data.disk) {
            const p = parseFloat(data.disk.percent);
            const diskValue = document.getElementById('diskValue');
            const diskBar = document.getElementById('diskBar');
            const diskBox = document.getElementById('diskBox');
            const diskInfo = document.getElementById('diskInfo');
            if (diskValue && !isNaN(p)) diskValue.textContent = p.toFixed(2) + '%';
            if (diskBar && !isNaN(p)) {
                diskBar.style.width = Math.max(0, Math.min(100, p)) + '%';
                diskBar.textContent = p.toFixed(2) + '%';
                setClassLevel(diskBar, p, 75, 90);
            }
            if (diskBox && !isNaN(p)) {
                setClassLevel(diskBox, p, 75, 90);
            }
            if (diskInfo && data.disk.used_f && data.disk.total_f) {
                diskInfo.textContent = data.disk.used_f + ' / ' + data.disk.total_f;
            }
        }

        // MySQL
        if (data.mysql) {
            const mysqlBox = document.getElementById('mysqlBox');
            const mysqlState = document.getElementById('mysqlState');
            const mysqlVersion = document.getElementById('mysqlVersion');
            const mysqlThreads = document.getElementById('mysqlThreads');
            const mysqlError = document.getElementById('mysqlError');

            if (data.mysql.status === 'OK') {
                if (mysqlBox) {
                    mysqlBox.classList.remove('danger');
                    mysqlBox.classList.add('success');
                }
                if (mysqlState) mysqlState.textContent = 'OK';
                if (mysqlVersion && data.mysql.version) {
                    mysqlVersion.textContent = data.mysql.version;
                }
                if (mysqlThreads && data.mysql.threads != null) {
                    mysqlThreads.textContent = data.mysql.threads;
                }
                if (mysqlError) mysqlError.textContent = '';
            } else {
                if (mysqlBox) {
                    mysqlBox.classList.remove('success');
                    mysqlBox.classList.add('danger');
                }
                if (mysqlState) mysqlState.textContent = 'ERROR';
                if (mysqlError) {
                    mysqlError.textContent = data.mysql.error || 'Error desconocido';
                }
            }
        }

        // Memoria PHP
        if (data.phpMem) {
            const usage = parseFloat(data.phpMem.usage || 0);
            const percent = data.phpMem.percent != null ? parseFloat(data.phpMem.percent) : null;
            const phpBox = document.getElementById('phpBox');
            const phpUsageValue = document.getElementById('phpUsageValue');
            const phpBar = document.getElementById('phpBar');
            const phpInfo = document.getElementById('phpInfo');

            if (phpUsageValue && data.phpMem.usage_f) {
                phpUsageValue.textContent = data.phpMem.usage_f;
            }

            if (percent != null && phpBar && phpBar.classList) {
                const p = Math.max(0, Math.min(100, percent));
                if (phpBar.classList.contains('progress-fill')) {
                    phpBar.style.width = p + '%';
                    phpBar.textContent = p.toFixed(2) + '% de ' + (data.phpMem.limit_f || '');
                }
                if (phpBox) {
                    setClassLevel(phpBox, p, 75, 90);
                }
                setClassLevel(phpBar, p, 75, 90);
            }

            if (phpInfo && data.phpMem.peak_f) {
                phpInfo.textContent = 'Pico de memoria en esta petición: ' + data.phpMem.peak_f;
            }
        }

        // Uptime & load detallados
        if (data.uptime && data.uptime.formatted) {
            const uptimeText = document.getElementById('uptimeText');
            if (uptimeText) uptimeText.textContent = data.uptime.formatted;
        }
        if (data.load) {
            const l1 = document.getElementById('load1m');
            const l5 = document.getElementById('load5m');
            const l15 = document.getElementById('load15m');
            if (l1) l1.textContent = data.load['1m'];
            if (l5) l5.textContent = data.load['5m'];
            if (l15) l15.textContent = data.load['15m'];
        }

        // Problemas críticos
        const critCard = document.getElementById('critCard');
        const critContainer = document.getElementById('critContainer');
        if (critCard && critContainer) {
            critContainer.innerHTML = '';
            if (Array.isArray(data.crit) && data.crit.length > 0) {
                critCard.style.display = '';
                data.crit.forEach(function (item) {
                    const div = document.createElement('div');
                    div.className = 'alert';
                    const strong = document.createElement('strong');
                    strong.textContent = item.title || 'Alerta';
                    div.appendChild(strong);
                    const text = document.createTextNode(item.msg || '');
                    div.appendChild(text);
                    critContainer.appendChild(div);
                });
            } else {
                critCard.style.display = 'none';
            }
        }
    }

// URL base del mismo script que estás viendo ahora (sin parámetros)
const AJAX_URL = window.location.href.split('?')[0];

function fetchMetrics() {
    fetch(AJAX_URL + '?ajax=1', { cache: 'no-store' })
        .then(function (r) { 
            return r.json(); 
        })
        .then(function (data) { 
            applyMetrics(data); 
        })
        .catch(function (err) { 
            console.warn('Error al actualizar métricas', err); 
        });
}


    function startAuto() {
        if (timer) return;
        // Llamada inmediata + luego cada 5s
        fetchMetrics();
        timer = setInterval(fetchMetrics, REFRESH_INTERVAL);
    }

    function stopAuto() {
        if (!timer) return;
        clearInterval(timer);
        timer = null;
    }

    if (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            // Recarga completa para refrescar también URLs críticas
            window.location.reload();
        });
    }

    if (chk) {
        const saved = localStorage.getItem('monitor_auto_refresh');
        if (saved === '1') {
            chk.checked = true;
            startAuto();
        }

        chk.addEventListener('change', function () {
            if (chk.checked) {
                localStorage.setItem('monitor_auto_refresh', '1');
                startAuto();
            } else {
                localStorage.setItem('monitor_auto_refresh', '0');
                stopAuto();
            }
        });
    }
})();
</script>
</body>
</html>
