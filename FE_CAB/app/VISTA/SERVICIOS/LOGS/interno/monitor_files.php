<?php
/**
 * MONITOR FILES v3 (optimizado RAM)
 *
 * Explorador de archivos seguro y liviano:
 * - Solo navega en LOG_DIR, CACHE_DIR, BACKUP_DIR
 * - Lista como máximo 500 entradas por carpeta
 * - Visor de archivo limitado a 256KB
 * - Elimina solo archivos dentro de esas rutas
 */

define('MONITOR_ACCESS', true);

require_once 'config.inc.php';
require_once 'auth.inc.php';
require_once 'security.inc.php';

session_start();

// 1) Autenticación general
process_logout();
$login_result = process_login();
$autenticado = isset($_SESSION['monitor_auth']) && $_SESSION['monitor_auth'] === true;

if (!$autenticado) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Monitor Archivos</title>
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
            <h1>MONITOR ARCHIVOS</h1>
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
 * CONFIG: rutas permitidas
 * ============================ */

// Directorios raíz permitidos (SOLO estos, sin BASE_PATH)
$roots = [];
foreach ([LOG_DIR, CACHE_DIR, BACKUP_DIR] as $dir) {
    $real = realpath($dir);
    if ($real !== false && is_dir($real)) {
        $roots[] = $real;
    }
}

// Si por alguna razón no hay ninguno definido, usamos la ruta actual solo lectura
if (empty($roots)) {
    $cwd = realpath(getcwd());
    if ($cwd !== false) {
        $roots[] = $cwd;
    }
}

// Etiquetas amigables
$rootLabels = [];
foreach ($roots as $r) {
    if ($r === realpath(LOG_DIR))        $rootLabels[$r] = 'LOG_DIR';
    elseif ($r === realpath(CACHE_DIR))  $rootLabels[$r] = 'CACHE_DIR';
    elseif ($r === realpath(BACKUP_DIR)) $rootLabels[$r] = 'BACKUP_DIR';
    else                                 $rootLabels[$r] = $r;
}

/* ============================
 * FUNCIONES AUXILIARES
 * ============================ */

function is_path_allowed(string $path, array $roots): ?string {
    $real = realpath($path);
    if ($real === false) return null;

    foreach ($roots as $root) {
        $r = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strpos($real, $r) === 0 || $real === rtrim($root, DIRECTORY_SEPARATOR)) {
            return $real;
        }
    }
    return null;
}

// Solo se pueden ELIMINAR archivos dentro de LOG_DIR, CACHE_DIR, BACKUP_DIR
function is_deletable_root(string $realPath): bool {
    $log   = realpath(LOG_DIR);
    $cache = realpath(CACHE_DIR);
    $back  = realpath(BACKUP_DIR);

    foreach ([$log, $cache, $back] as $root) {
        if ($root && (strpos($realPath, rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) === 0 || $realPath === $root)) {
            return true;
        }
    }
    return false;
}

/**
 * Lista archivos y carpetas en un directorio (máx. $maxEntries)
 */
function list_dir(string $dir, int $maxEntries = 500): array {
    $items = [
        'dirs'      => [],
        'files'     => [],
        'truncated' => false,
    ];

    $dh = @opendir($dir);
    if (!$dh) {
        return $items;
    }

    $count = 0;
    while (($entry = readdir($dh)) !== false) {
        if ($entry === '.' || $entry === '..') continue;

        $full = $dir . DIRECTORY_SEPARATOR . $entry;

        $info = [
            'name' => $entry,
            'path' => $full,
            'size' => is_file($full) ? @filesize($full) : 0,
            'mtime'=> @filemtime($full) ?: 0,
        ];

        if (is_dir($full)) {
            $items['dirs'][] = $info;
        } elseif (is_file($full)) {
            $items['files'][] = $info;
        }

        $count++;
        if ($count >= $maxEntries) {
            $items['truncated'] = true;
            break;
        }
    }

    closedir($dh);

    usort($items['dirs'], function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    usort($items['files'], function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    return $items;
}

/**
 * Lee archivo de texto de forma segura (máx 256KB)
 */
function read_file_safe(string $file, int $maxBytes = 262144): string {
    if (!is_file($file) || !is_readable($file)) {
        return "No se pudo leer el archivo.";
    }
    $size = @filesize($file);
    if ($size === false || $size <= 0) {
        return "";
    }

    $fh = @fopen($file, 'rb');
    if (!$fh) {
        return "No se pudo abrir el archivo.";
    }

    $readBytes = min($size, $maxBytes);
    $data = @fread($fh, $readBytes);
    fclose($fh);

    if ($data === false) {
        return "No se pudo leer el archivo.";
    }

    if ($size > $readBytes) {
        $data = "... [contenido truncado: mostrando primeros " . format_bytes($readBytes) . " de " . format_bytes($size) . "]\n\n" . $data;
    }

    return $data;
}

/* ============================
 * CSRF simple para delete
 * ============================ */

if (!isset($_SESSION['monitor_files_token'])) {
    $_SESSION['monitor_files_token'] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['monitor_files_token'];

/* ============================
 * PROCESAR ACCIONES
 * ============================ */

$actionMessage = null;
$actionType    = null; // success / error

// Directorio actual
$defaultRoot = $roots[0] ?? realpath(getcwd());
$dirParam    = isset($_GET['dir']) ? $_GET['dir'] : $defaultRoot;
$currentDir  = is_path_allowed($dirParam, $roots) ?: $defaultRoot;

// Acción: descargar archivo
if (isset($_GET['download'])) {
    $fileParam = $_GET['download'];
    $filePath  = is_path_allowed($fileParam, $roots);

    if ($filePath && is_file($filePath)) {
        $basename = basename($filePath);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . rawurlencode($basename) . '"');
        header('Content-Length: ' . @filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        $actionMessage = 'No se puede descargar ese archivo.';
        $actionType    = 'error';
    }
}

// Acción: ver archivo
$viewFilePath    = null;
$viewFileContent = null;
if (isset($_GET['view'])) {
    $fileParam = $_GET['view'];
    $filePath  = is_path_allowed($fileParam, $roots);

    if ($filePath && is_file($filePath)) {
        $viewFilePath    = $filePath;
        $viewFileContent = read_file_safe($filePath); // máx 256KB
    } else {
        $actionMessage = 'No se puede ver ese archivo.';
        $actionType    = 'error';
    }
}

// Acción: eliminar archivo (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $fileParam = $_POST['file'] ?? '';
    $token     = $_POST['token'] ?? '';

    if (!hash_equals($csrfToken, $token)) {
        $actionMessage = 'Token inválido. Intenta de nuevo.';
        $actionType    = 'error';
    } else {
        $filePath = is_path_allowed($fileParam, $roots);
        if (!$filePath || !is_file($filePath)) {
            $actionMessage = 'El archivo no es válido.';
            $actionType    = 'error';
        } elseif (!is_deletable_root($filePath)) {
            $actionMessage = 'No está permitido eliminar archivos en esta ruta.';
            $actionType    = 'error';
        } else {
            if (@unlink($filePath)) {
                $actionMessage = 'Archivo eliminado correctamente.';
                $actionType    = 'success';
            } else {
                $actionMessage = 'No se pudo eliminar el archivo.';
                $actionType    = 'error';
            }
        }
    }

    // Mantener en la misma carpeta tras POST
    if (isset($_POST['dir'])) {
        $dirPost    = $_POST['dir'];
        $currentDir = is_path_allowed($dirPost, $roots) ?: $currentDir;
    }
}

// Listado del directorio actual (máx 500 entradas)
$list = list_dir($currentDir, 500);

// Directorio padre (limitado a roots)
$parentDir = null;
$parentReal = realpath($currentDir . DIRECTORY_SEPARATOR . '..');
if ($parentReal !== false && $parentReal !== $currentDir && is_path_allowed($parentReal, $roots)) {
    $parentDir = $parentReal;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Archivos - Monitor</title>
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
.btn{padding:7px 14px;background:#fff;color:#000;border:none;cursor:pointer;font-size:12px;font-weight:600;text-decoration:none;display:inline-block;border-radius:3px;}
.btn:hover{background:#e0e0e0;}
.btn-danger{background:#ff4444;color:#fff;}
.btn-danger:hover{background:#ff2222;}
.btn-secondary{background:#222;color:#fff;border:1px solid #555;}
.btn-secondary:hover{background:#333;}
.card{background:#1a1a1a;border:2px solid #333;padding:20px;margin-bottom:20px;}
.card h3{font-size:16px;margin-bottom:15px;color:#fff;text-transform:uppercase;letter-spacing:1px;}
.grid{display:grid;gap:20px;}
.grid-2{grid-template-columns:repeat(auto-fit,minmax(320px,1fr));}
.table{width:100%;border-collapse:collapse;font-size:13px;}
.table th,.table td{padding:8px 10px;text-align:left;border-bottom:1px solid #333;vertical-align:middle;}
.table th{background:#0a0a0a;font-weight:700;font-size:11px;text-transform:uppercase;color:#999;}
.small-text{font-size:11px;color:#999;}
.badge{display:inline-block;padding:3px 8px;font-size:11px;border-radius:3px;background:#222;color:#eee;}
.alert-box{padding:10px;margin-bottom:15px;border-left:4px solid #ffcc00;background:#2a2a1a;color:#ffeb99;font-size:12px;}
.alert-box.error{border-color:#ff0000;background:#2a1a1a;color:#ffaaaa;}
.path-bar{font-size:12px;margin-bottom:10px;}
.path-bar strong{color:#fff;}
pre.file-view{background:#000;font-family:Menlo,Monaco,Consolas,monospace;font-size:11px;line-height:1.4;white-space:pre-wrap;word-wrap:break-word;padding:10px;border:1px solid #333;max-height:65vh;overflow:auto;}
.root-selector{margin-bottom:10px;display:flex;flex-wrap:wrap;gap:5px;}
.root-selector a{font-size:11px;padding:5px 10px;border-radius:3px;text-decoration:none;border:1px solid #444;color:#ccc;}
.root-selector a.active{background:#fff;color:#000;border-color:#fff;}
.dir-link{color:#9ad0ff;text-decoration:none;}
.dir-link:hover{text-decoration:underline;}
.action-buttons{display:flex;gap:6px;flex-wrap:wrap;}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>MONITOR ARCHIVOS</h1>
            <div class="header-info">
                <?php echo date('Y-m-d H:i:s'); ?> | Usuario: <?php echo ADMIN_USER; ?>
            </div>
            <div class="header-info small-text">
                Rutas seguras: <?php echo e(implode(', ', array_values($rootLabels))); ?>
            </div>
        </div>
        <a href="?logout" class="btn">SALIR</a>
    </div>

    <div class="nav">
        <a href="monitor_dashboard">DASHBOARD</a>
        <a href="monitor_database">BASE DE DATOS</a>
        <a href="monitor_files" class="active">ARCHIVOS</a>
        <a href="monitor_logs">LOGS</a>
        <a href="monitor_security">SEGURIDAD</a>
        <a href="monitor_tools">HERRAMIENTAS</a>
    </div>

    <?php if ($actionMessage): ?>
        <div class="alert-box <?php echo $actionType === 'error' ? 'error' : ''; ?>">
            <?php echo e($actionMessage); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-2">
        <div class="card">
            <h3>Navegador de archivos</h3>

            <div class="root-selector">
                <?php foreach ($roots as $r): ?>
                    <a href="?dir=<?php echo urlencode($r); ?>"
                       class="<?php echo $currentDir === $r ? 'active' : ''; ?>">
                        <?php echo e($rootLabels[$r] ?? $r); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="path-bar">
                <strong>Directorio actual:</strong> <span><?php echo e($currentDir); ?></span><br>
                <?php if ($parentDir): ?>
                    <a class="dir-link" href="?dir=<?php echo urlencode($parentDir); ?>">⬆ Subir un nivel</a>
                <?php else: ?>
                    <span class="small-text">No se puede subir más (límite de rutas seguras).</span>
                <?php endif; ?>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Tamaño</th>
                        <th>Modificado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list['dirs']) && empty($list['files'])): ?>
                        <tr>
                            <td colspan="5" class="small-text">Directorio vacío.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list['dirs'] as $d): ?>
                            <tr>
                                <td>
                                    <a class="dir-link" href="?dir=<?php echo urlencode($d['path']); ?>">
                                        📁 <?php echo e($d['name']); ?>
                                    </a>
                                </td>
                                <td><span class="badge">Carpeta</span></td>
                                <td>-</td>
                                <td class="small-text"><?php echo date('Y-m-d H:i:s', $d['mtime']); ?></td>
                                <td class="small-text">-</td>
                            </tr>
                        <?php endforeach; ?>

                        <?php foreach ($list['files'] as $f): ?>
                            <?php $isDeletable = is_deletable_root($f['path']); ?>
                            <tr>
                                <td><?php echo e($f['name']); ?></td>
                                <td><span class="badge">Archivo</span></td>
                                <td><?php echo format_bytes($f['size']); ?></td>
                                <td class="small-text"><?php echo date('Y-m-d H:i:s', $f['mtime']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="btn btn-secondary"
                                           href="?dir=<?php echo urlencode($currentDir); ?>&view=<?php echo urlencode($f['path']); ?>">
                                            Ver
                                        </a>
                                        <a class="btn"
                                           href="?dir=<?php echo urlencode($currentDir); ?>&download=<?php echo urlencode($f['path']); ?>">
                                            Descargar
                                        </a>
                                        <?php if ($isDeletable): ?>
                                            <form method="POST" style="display:inline;"
                                                  onsubmit="return confirm('¿Seguro que quieres eliminar este archivo?\n<?php echo e($f['name']); ?>');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="file" value="<?php echo e($f['path']); ?>">
                                                <input type="hidden" name="dir" value="<?php echo e($currentDir); ?>">
                                                <input type="hidden" name="token" value="<?php echo e($csrfToken); ?>">
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="small-text">No eliminable</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($list['truncated']): ?>
                <p class="small-text" style="margin-top:8px;">
                    * Listado truncado a las primeras 500 entradas para reducir consumo de memoria.
                </p>
            <?php endif; ?>

            <p class="small-text" style="margin-top:10px;">
                * Solo se permite eliminar archivos dentro de LOG_DIR, CACHE_DIR y BACKUP_DIR.<br>
                * Archivos fuera de esas rutas quedan en solo lectura.
            </p>
        </div>

        <div class="card">
            <h3>Visor de archivo</h3>
            <?php if ($viewFilePath): ?>
                <p class="small-text" style="margin-bottom:10px;">
                    Archivo: <strong><?php echo e($viewFilePath); ?></strong>
                </p>
                <pre class="file-view"><?php echo htmlspecialchars($viewFileContent ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></pre>
            <?php else: ?>
                <p class="small-text">
                    Selecciona "Ver" sobre cualquier archivo del listado para mostrar su contenido aquí.<br>
                    Por seguridad, solo se muestran hasta 256KB del archivo. Para logs muy grandes es mejor descargarlos.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
