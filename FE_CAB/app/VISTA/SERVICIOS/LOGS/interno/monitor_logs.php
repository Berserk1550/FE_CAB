<?php
/**
 * MONITOR LOGS v2
 * Explorador y visor de logs del sistema/aplicación
 *
 * - Protegido por autenticación (igual que dashboard)
 * - Escanea recursivamente rutas configuradas en $monitor_log_paths
 * - Si no se configura, usa rutas por defecto típicas de logs
 * - Permite ver el contenido (últimas N líneas) de un log
 */

define('MONITOR_ACCESS', true);

require_once 'config.inc.php';
require_once 'auth.inc.php';
require_once 'security.inc.php';

process_logout();
$login_result = process_login();

$autenticado = isset($_SESSION['monitor_auth']) && $_SESSION['monitor_auth'] === true;

// Si no está autenticado, mostrar login básico (igual que en dashboard)
if (!$autenticado) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Monitor Logs</title>
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
            <h1>MONITOR LOGS</h1>
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
 * CONFIG LOG PATHS
 * ============================ */

/**
 * Puedes definir en config.inc.php:
 *   $monitor_log_paths = ['/var/log', '/home/usuario/proyecto/logs', ...];
 * Si no existe, usamos rutas típicas por defecto.
 */
$logPaths = [];

// 1) Siempre intenta usar LOG_DIR si está definido y existe
if (defined('LOG_DIR')) {
    $logDirReal = realpath(LOG_DIR);
    if ($logDirReal !== false && is_dir($logDirReal)) {
        $logPaths[] = $logDirReal;
    }
}

// 2) Si config.inc.php define rutas extra, las agregamos
if (isset($GLOBALS['monitor_log_paths']) && is_array($GLOBALS['monitor_log_paths'])) {
    foreach ($GLOBALS['monitor_log_paths'] as $p) {
        $rp = realpath($p);
        if ($rp !== false && is_dir($rp) && !in_array($rp, $logPaths, true)) {
            $logPaths[] = $rp;
        }
    }
}

// 3) Si todavía no hay nada, usamos los defaults
if (empty($logPaths)) {
    $candidates = [
        '/var/log',
        __DIR__ . '/../logs',
        __DIR__ . '/logs',
        dirname(__DIR__) . '/logs',
    ];
    foreach ($candidates as $p) {
        $rp = realpath($p);
        if ($rp !== false && is_dir($rp) && !in_array($rp, $logPaths, true)) {
            $logPaths[] = $rp;
        }
    }
}


/* ============================
 * FUNCIONES AUXILIARES
 * ============================ */

/**
 * Devuelve TRUE si un archivo "huele" a log.
 * - Extensiones .log, .out, .err
 * - O nombre conteniendo "log" (error_log, access.log, etc.)
 */
function is_log_file(string $path): bool {
    if (!is_file($path)) return false;

    $basename = basename($path);
    $lower = strtolower($basename);

    // Extensiones típicas
    $ext = pathinfo($basename, PATHINFO_EXTENSION);
    $logExts = ['log', 'out', 'err', 'txt'];

    if (in_array($ext, $logExts, true)) {
        return true;
    }

    // Nombres como "error_log", "access_log"
    if (strpos($lower, 'log') !== false) {
        return true;
    }

    return false;
}

/**
 * Escanea recursivamente las rutas dadas y devuelve un array de logs:
 * [
 *   id => [
 *     'id' => string,
 *     'path' => string,
 *     'name' => string,
 *     'size' => int,
 *     'size_f' => string,
 *     'mtime' => int,
 *   ]
 * ]
 */
function scan_logs(array $roots): array {
    $result = [];
    $seen = [];

    foreach ($roots as $root) {
        $root = realpath($root);
        if ($root === false || !is_dir($root) || !is_readable($root)) {
            continue;
        }

        try {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $root,
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
                ),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($it as $file) {
                /** @var SplFileInfo $file */
                if (!$file->isFile()) continue;

                $path = $file->getPathname();
                if (!is_log_file($path)) continue;

                $real = realpath($path);
                if ($real === false) continue;

                // Evitar duplicados por symlinks
                if (isset($seen[$real])) continue;
                $seen[$real] = true;

                $size = $file->getSize();
                $mtime = $file->getMTime();

                $id = sha1($real);

                $result[$id] = [
                    'id'     => $id,
                    'path'   => $real,
                    'name'   => basename($real),
                    'size'   => $size,
                    'size_f' => format_bytes($size),
                    'mtime'  => $mtime,
                ];
            }
        } catch (Throwable $e) {
            // Ignoramos errores de permisos/directorios inaccesibles
            continue;
        }
    }

    // Ordenar por fecha de modificación descendente (más recientes arriba)
    uasort($result, function ($a, $b) {
        return $b['mtime'] <=> $a['mtime'];
    });

    return $result;
}

/**
 * Lee las últimas N líneas de un archivo, de forma eficiente.
 * Limitamos también por bytes máximos para evitar matar la memoria con logs enormes.
 */
function tail_file(string $filePath, int $lines = 500, int $maxBytes = 1048576) {
    $fh = @fopen($filePath, 'rb');
    if (!$fh) {
        return "No se pudo abrir el archivo.";
    }

    // Si el archivo es pequeño, se puede leer completo
    $fsize = filesize($filePath);
    if ($fsize <= 0) {
        fclose($fh);
        return "";
    }

    $readBytes = min($fsize, $maxBytes);
    $pos = $fsize - $readBytes;

    if (fseek($fh, $pos) !== 0) {
        // Si falla el seek, intentamos desde el inicio
        fseek($fh, 0);
        $readBytes = $fsize;
    }

    $data = fread($fh, $readBytes);
    fclose($fh);

    if ($pos > 0) {
        // Puede que el primer fragmento esté cortado, limpiamos hasta primer salto de línea
        $firstNewline = strpos($data, "\n");
        if ($firstNewline !== false) {
            $data = substr($data, $firstNewline + 1);
        }
    }

    $linesArr = explode("\n", $data);
    $totalLines = count($linesArr);
    if ($totalLines <= $lines) {
        return $data;
    }

    // Tomamos solo las últimas N líneas
    $tail = array_slice($linesArr, -$lines);
    return implode("\n", $tail);
}

/* ============================
 * RECOLECCIÓN DE LOGS
 * ============================ */

$logs = scan_logs($logPaths);

// Mapa rápido id => path para validación
$idToPath = [];
foreach ($logs as $log) {
    $idToPath[$log['id']] = $log['path'];
}

// Parámetros GET para ver un log
$selectedId   = isset($_GET['id']) ? $_GET['id'] : null;
$selectedFile = null;
$selectedContent = null;
$linesToShow  = isset($_GET['lines']) ? max(50, (int)$_GET['lines']) : 500;

if ($selectedId && isset($idToPath[$selectedId])) {
    $selectedFile = $idToPath[$selectedId];

    // Seguridad: solo leemos archivos que vienen del escaneo (id válido)
    $selectedContent = tail_file($selectedFile, $linesToShow, 1024 * 1024 * 2); // 2MB máx
}

/* ============================
 * VISTA
 * ============================ */

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logs - Monitor</title>
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
.card{background:#1a1a1a;border:2px solid #333;padding:20px;margin-bottom:20px;}
.card h3{font-size:16px;margin-bottom:15px;color:#fff;text-transform:uppercase;letter-spacing:1px;}
.grid{display:grid;gap:20px;}
.grid-2{grid-template-columns:repeat(auto-fit,minmax(320px,1fr));}
.table{width:100%;border-collapse:collapse;font-size:13px;}
.table th,.table td{padding:8px 10px;text-align:left;border-bottom:1px solid #333;vertical-align:top;}
.table th{background:#0a0a0a;font-weight:700;font-size:11px;text-transform:uppercase;color:#999;}
.badge{display:inline-block;padding:3px 7px;font-size:11px;border-radius:3px;background:#222;color:#eee;}
.small-text{font-size:11px;color:#999;}
pre.log-viewer{background:#000;font-family:Menlo,Monaco,Consolas,monospace;font-size:11px;line-height:1.4;white-space:pre-wrap;word-wrap:break-word;padding:10px;border:1px solid #333;max-height:70vh;overflow:auto;}
.search-bar{margin-bottom:10px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.search-input{padding:7px 10px;background:#0a0a0a;border:1px solid #333;color:#fff;font-size:13px;min-width:200px;}
.search-input:focus{outline:none;border-color:#666;}
.log-row{cursor:pointer;}
.log-row:hover{background:#222;}
.logs-empty{padding:15px;color:#666;font-size:13px;}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>MONITOR LOGS</h1>
            <div class="header-info">
                <?php echo date('Y-m-d H:i:s'); ?> | Usuario: <?php echo ADMIN_USER; ?>
            </div>
            <div class="header-info small-text">
                Rutas monitorizadas:
                <?php echo e(implode(', ', array_filter(array_map('realpath', $logPaths)))); ?>
            </div>
        </div>
        <a href="?logout" class="btn">SALIR</a>
    </div>

    <div class="nav">
        <a href="monitor_dashboard">DASHBOARD</a>
        <a href="monitor_database">BASE DE DATOS</a>
        <a href="monitor_files">ARCHIVOS</a>
        <a href="monitor_logs" class="active">LOGS</a>
        <a href="monitor_security">SEGURIDAD</a>
        <a href="monitor_tools">HERRAMIENTAS</a>
    </div>

    <div class="grid grid-2">
        <!-- Lista de logs -->
        <div class="card">
            <h3>Archivos de log detectados (<?php echo count($logs); ?>)</h3>

            <div class="search-bar">
                <input type="text" id="logSearch" class="search-input" placeholder="Filtrar por nombre o ruta...">
                <span class="small-text">Escribe para filtrar la tabla de logs.</span>
            </div>

            <?php if (count($logs) === 0): ?>
                <div class="logs-empty">
                    No se encontraron archivos de log en las rutas configuradas.<br>
                    Revisa la configuración de <code>$monitor_log_paths</code> en <strong>config.inc.php</strong>.
                </div>
            <?php else: ?>
                <table class="table" id="logsTable">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Ruta completa</th>
                            <th>Tamaño</th>
                            <th>Modificado</th>
                            <th>Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr class="log-row" data-filter="<?php echo e(strtolower($log['name'] . ' ' . $log['path'])); ?>">
                                <td>
                                    <span class="badge"><?php echo e($log['name']); ?></span>
                                </td>
                                <td class="small-text">
                                    <?php echo e($log['path']); ?>
                                </td>
                                <td><?php echo $log['size_f']; ?></td>
                                <td class="small-text">
                                    <?php echo date('Y-m-d H:i:s', $log['mtime']); ?>
                                </td>
                                <td>
                                    <a class="btn" style="padding:4px 10px;font-size:11px;"
                                       href="?id=<?php echo urlencode($log['id']); ?>&lines=<?php echo $linesToShow; ?>">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Visor de contenido -->
        <div class="card">
            <h3>Contenido del log</h3>

            <?php if ($selectedFile === null): ?>
                <p class="small-text">
                    Selecciona un archivo en la tabla de la izquierda para ver su contenido.<br>
                    Por defecto se muestran las últimas <?php echo $linesToShow; ?> líneas (para evitar consumir demasiada memoria en logs muy grandes).
                </p>
            <?php else: ?>
                <p class="small-text">
                    Archivo: <strong><?php echo e($selectedFile); ?></strong><br>
                    Líneas mostradas: últimas <?php echo $linesToShow; ?> (puedes cambiar el valor en la URL con <code>&lines=1000</code>, por ejemplo).
                </p>
                <pre class="log-viewer"><?php echo htmlspecialchars($selectedContent ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></pre>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Filtro simple de logs por nombre/ruta
(function () {
    const input = document.getElementById('logSearch');
    const table = document.getElementById('logsTable');
    if (!input || !table) return;

    const rows = Array.from(table.querySelectorAll('tbody .log-row'));

    input.addEventListener('input', function () {
        const term = input.value.trim().toLowerCase();
        if (!term) {
            rows.forEach(r => r.style.display = '');
            return;
        }

        rows.forEach(r => {
            const data = r.getAttribute('data-filter') || '';
            r.style.display = data.indexOf(term) !== -1 ? '' : 'none';
        });
    });

    // Permitir click en toda la fila para abrir el log
    rows.forEach(row => {
        const link = row.querySelector('a.btn');
        if (!link) return;
        row.addEventListener('click', function (e) {
            // Si clic directamente en el botón, dejamos su comportamiento normal
            if (e.target === link || link.contains(e.target)) return;
            window.location.href = link.href;
        });
    });
})();
</script>
</body>
</html>
