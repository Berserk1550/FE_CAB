<?php
/**
 * MONITOR DATABASE v2
 * - Requiere autenticación general (monitor_auth)
 * - Requiere una contraseña adicional específica de este módulo: 4d5005
 * - Solo muestra las tablas de la base de datos (no muestra registros)
 * - Intenta dificultar copia / captura desde el navegador
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

// Si no está autenticado, mostrar login básico (igual que en dashboard)
if (!$autenticado) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Monitor Base de Datos</title>
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
            <h1>MONITOR BD</h1>
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

// 2) Segunda contraseña específica del módulo de BD
//    Contraseña: 4d5005
$DB_MODULE_PASS = '4d5005';
$dbAuthError = null;

if (isset($_POST['db_pass'])) {
    $input = trim((string)$_POST['db_pass']);
    if (hash_equals($DB_MODULE_PASS, $input)) {
        $_SESSION['monitor_db_auth'] = true;
    } else {
        $_SESSION['monitor_db_auth'] = false;
        $dbAuthError = 'Contraseña de módulo inválida';
    }
}

$moduloAutorizado = isset($_SESSION['monitor_db_auth']) && $_SESSION['monitor_db_auth'] === true;

if (!$moduloAutorizado) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso Base de Datos - Monitor</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: #0a0a0a;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                user-select: none;
                -webkit-user-select: none;
            }
            .login-box {
                background: #1a1a1a;
                border: 2px solid #333;
                padding: 30px;
                width: 100%;
                max-width: 380px;
            }
            h1 {
                color: #fff;
                font-size: 20px;
                margin-bottom: 5px;
                text-align: center;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .subtitle {
                color: #888;
                font-size: 11px;
                text-align: center;
                margin-bottom: 20px;
            }
            .error {
                background: #2a1a1a;
                border: 1px solid #ff0000;
                color: #ff0000;
                padding: 10px;
                margin-bottom: 15px;
                font-size: 12px;
                text-align: center;
            }
            .form-group { margin-bottom: 15px; }
            label {
                display: block;
                color: #ccc;
                font-size: 12px;
                margin-bottom: 6px;
            }
            input {
                width: 100%;
                padding: 10px;
                background: #0a0a0a;
                border: 1px solid #333;
                color: #fff;
                font-size: 13px;
            }
            input:focus {
                outline: none;
                border-color: #666;
            }
            button {
                width: 100%;
                padding: 10px;
                background: #fff;
                border: none;
                color: #000;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            button:hover { background: #e0e0e0; }
            .info {
                color: #777;
                font-size: 11px;
                margin-top: 10px;
                text-align: center;
            }
        </style>
    </head>
    <body oncontextmenu="return false;">
        <div class="login-box">
            <h1>ACCESO BD</h1>
            <div class="subtitle">Se requiere código adicional para ver las tablas.</div>

            <?php if ($dbAuthError): ?>
                <div class="error"><?php echo e($dbAuthError); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>CÓDIGO DE ACCESO AL MÓDULO</label>
                    <input type="password" name="db_pass" autocomplete="off" required>
                </div>
                <button type="submit">INGRESAR</button>
            </form>
            <div class="info">
                El contenido de este módulo está protegido.
            </div>
        </div>

        <script>
        // Bloqueo básico de teclas (intento de evitar copia/inspección)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12') { e.preventDefault(); return false; }
            if (e.key === 'PrintScreen') { e.preventDefault(); return false; }
            if (e.ctrlKey) {
                const k = e.key.toLowerCase();
                if (k === 'c' || k === 's' || k === 'p' || k === 'u') {
                    e.preventDefault();
                    return false;
                }
                if (e.shiftKey && (k === 'i' || k === 'j')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
        </script>
    </body>
    </html>
    <?php
    exit;
}

/* ============================
 * FUNCIÓN: tablas de la BD
 * ============================ */

function get_tables_info(): array {
    $info = [];

    $cn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($cn->connect_error) {
        return ['__ERROR__' => $cn->connect_error];
    }

    // SHOW TABLE STATUS da info de tamaño, engine, etc.
    $res = $cn->query("SHOW TABLE STATUS");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $dataLength  = (int)$row['Data_length'];
            $indexLength = (int)$row['Index_length'];
            $totalSize   = $dataLength + $indexLength;

            $info[] = [
                'name'       => $row['Name'],
                'engine'     => $row['Engine'],
                'rows'       => (int)$row['Rows'],
                'collation'  => $row['Collation'],
                'data_size'  => $dataLength,
                'idx_size'   => $indexLength,
                'total_size' => $totalSize,
                'created'    => $row['Create_time'],
                'updated'    => $row['Update_time'],
                'comment'    => $row['Comment'],
            ];
        }
        $res->free();
    }

    $cn->close();

    // Orden alfabético por nombre
    usort($info, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    return $info;
}

$tables = get_tables_info();
$dbError = null;
if (isset($tables['__ERROR__'])) {
    $dbError = $tables['__ERROR__'];
    $tables = [];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Base de Datos - Monitor</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:-apple-system,sans-serif;
    background:#0a0a0a;
    color:#e0e0e0;
    padding:20px;
    user-select:none;
    -webkit-user-select:none;
}
.container{max-width:1400px;margin:0 auto;}
.header{
    background:#1a1a1a;
    border:2px solid #333;
    padding:20px 25px;
    margin-bottom:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
}
.header h1{font-size:24px;color:#fff;}
.header-info{font-size:12px;color:#666;margin-top:5px;}
.nav{
    background:#1a1a1a;
    border:2px solid #333;
    padding:0;
    margin-bottom:20px;
    display:flex;
    flex-wrap:wrap;
}
.nav a{
    padding:15px 20px;
    color:#999;
    text-decoration:none;
    border-right:1px solid #333;
    font-weight:600;
    font-size:13px;
}
.nav a:hover{background:#222;color:#fff;}
.nav a.active{background:#fff;color:#000;}
.btn{
    padding:10px 20px;
    background:#fff;
    color:#000;
    border:none;
    cursor:pointer;
    font-size:13px;
    font-weight:600;
    text-decoration:none;
    display:inline-block;
}
.btn:hover{background:#e0e0e0;}
.card{
    background:#1a1a1a;
    border:2px solid #333;
    padding:20px;
    margin-bottom:20px;
}
.card h3{
    font-size:16px;
    margin-bottom:15px;
    color:#fff;
    text-transform:uppercase;
    letter-spacing:1px;
}
.small-text{font-size:11px;color:#999;}
.table{width:100%;border-collapse:collapse;font-size:13px;}
.table th,.table td{
    padding:8px 10px;
    text-align:left;
    border-bottom:1px solid #333;
    vertical-align:top;
}
.table th{
    background:#0a0a0a;
    font-weight:700;
    font-size:11px;
    text-transform:uppercase;
    color:#999;
}
.badge{
    display:inline-block;
    padding:3px 8px;
    font-size:11px;
    border-radius:3px;
    background:#222;
    color:#eee;
}
.badge-engine{
    background:#004d80;
    color:#fff;
}
.badge-size{
    background:#004000;
    color:#b3ffb3;
}
.error-box{
    padding:15px;
    background:#2a1a1a;
    border-left:4px solid #ff0000;
    color:#ff7777;
    font-size:13px;
    margin-bottom:15px;
}
.overlay-blur{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.85);
    backdrop-filter:blur(6px);
    z-index:9999;
    display:none;
    align-items:center;
    justify-content:center;
    text-align:center;
    color:#fff;
    padding:20px;
}
.overlay-blur-inner{
    max-width:400px;
}
.overlay-blur h2{
    margin-bottom:10px;
    font-size:18px;
}
.overlay-blur p{
    font-size:13px;
    color:#ccc;
}
</style>
</head>
<body oncontextmenu="return false;">
<div class="overlay-blur" id="focusOverlay">
    <div class="overlay-blur-inner">
        <h2>Contenido protegido</h2>
        <p>La visualización de información de la base de datos está restringida. Vuelve a esta pestaña para continuar.</p>
    </div>
</div>

<div class="container">
    <div class="header">
        <div>
            <h1>MONITOR BASE DE DATOS</h1>
            <div class="header-info">
                <?php echo date('Y-m-d H:i:s'); ?>
                | Usuario: <?php echo ADMIN_USER; ?>
                | BD: <?php echo e(DB_NAME); ?>
            </div>
            <div class="header-info small-text">
                Solo se muestran metadatos de tablas. No se visualizan datos de registros.
            </div>
        </div>
        <a href="?logout" class="btn">SALIR</a>
    </div>

    <div class="nav">
        <a href="monitor_dashboard">DASHBOARD</a>
        <a href="monitor_database" class="active">BASE DE DATOS</a>
        <a href="monitor_files">ARCHIVOS</a>
        <a href="monitor_logs">LOGS</a>
        <a href="monitor_security">SEGURIDAD</a>
        <a href="monitor_tools">HERRAMIENTAS</a>
    </div>

    <div class="card">
        <h3>Tablas de la base de datos</h3>

        <?php if ($dbError): ?>
            <div class="error-box">
                <strong>Error de conexión:</strong> <?php echo e($dbError); ?>
            </div>
        <?php else: ?>
            <p class="small-text" style="margin-bottom:10px;">
                Esta vista muestra únicamente información estructural de las tablas (nombre, motor, filas, tamaño, fechas).<br>
                El contenido de las filas no se puede consultar desde este módulo.
            </p>

            <?php if (empty($tables)): ?>
                <p class="small-text">No se encontraron tablas en la base de datos actual.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tabla</th>
                            <th>Motor / Filas</th>
                            <th>Tamaño total</th>
                            <th>Codificación</th>
                            <th>Creada / Actualizada</th>
                            <th>Comentario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $t): ?>
                            <tr>
                                <td>
                                    <span class="badge"><?php echo e($t['name']); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-engine"><?php echo e($t['engine'] ?: 'N/D'); ?></span><br>
                                    <span class="small-text"><?php echo number_format($t['rows']); ?> filas aprox.</span>
                                </td>
                                <td>
                                    <?php echo format_bytes($t['total_size']); ?><br>
                                    <span class="small-text">
                                        Datos: <?php echo format_bytes($t['data_size']); ?> |
                                        Índices: <?php echo format_bytes($t['idx_size']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small-text"><?php echo e($t['collation'] ?: 'N/D'); ?></span>
                                </td>
                                <td>
                                    <span class="small-text">
                                        Crea: <?php echo $t['created'] ? e($t['created']) : 'N/D'; ?><br>
                                        Act.: <?php echo $t['updated'] ? e($t['updated']) : 'N/D'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small-text"><?php echo e($t['comment'] ?: ''); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Intento de impedir copiar / inspeccionar / screenshot (NO es infalible)
document.addEventListener('keydown', function(e) {
    // F12
    if (e.key === 'F12') { e.preventDefault(); return false; }
    // PrintScreen
    if (e.key === 'PrintScreen') { e.preventDefault(); return false; }
    // Atajos Ctrl+...
    if (e.ctrlKey) {
        const k = e.key.toLowerCase();
        if (k === 'c' || k === 's' || k === 'p' || k === 'u') {
            e.preventDefault();
            return false;
        }
        if (e.shiftKey && (k === 'i' || k === 'j')) {
            e.preventDefault();
            return false;
        }
    }
});

// Overlay cuando la pestaña pierde foco (para dificultar captura)
(function() {
    const overlay = document.getElementById('focusOverlay');
    if (!overlay) return;

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            overlay.style.display = 'flex';
        } else {
            overlay.style.display = 'none';
        }
    });
})();
</script>
</body>
</html>
