<?php
/**
 * INSTALADOR AUTOMÁTICO DE ENTORNOS - VERSION CORREGIDA
 * 
 * Este script crea automáticamente la estructura de carpetas
 * y copia los archivos necesarios para los entornos de prueba.
 * 
 * INSTRUCCIONES:
 * 1. Sube este archivo a la carpeta donde están tus archivos monitor_*.php
 * 2. Accede desde el navegador
 * 3. Sigue las instrucciones en pantalla
 * 4. ELIMINA este archivo después de la instalación
 */

// Configuración
$base_dir = dirname(__FILE__);
$environments = ['testing', 'dev'];
$subdirs = ['logs_app', 'cache', 'backups'];

$files_to_copy = [
    'config.inc.php',
    'auth.inc.php',
    'security.inc.php',
    'monitor_dashboard.php',
    'monitor_database.php',
    'monitor_files.php',
    'monitor_logs.php',
    'monitor_security.php',
    'monitor_tools.php'
];

$results = [];
$errors = [];
$warnings = [];

// Función para crear directorio de forma segura
function safe_mkdir($path){
    if(!is_dir($path)){
        return @mkdir($path, 0755, true);
    }
    return true;
}

// Verificar que los archivos necesarios existen
$missing_files = [];
foreach($files_to_copy as $file){
    if(!file_exists($base_dir . '/' . $file)){
        $missing_files[] = $file;
    }
}

// Proceso de instalación
if(isset($_POST['install'])){
    
    // 1. Crear estructura de carpetas
    foreach($environments as $env){
        $env_path = $base_dir . '/' . $env;
        
        if(safe_mkdir($env_path)){
            $results[] = "✅ Carpeta creada: {$env}/";
            
            // Crear subdirectorios
            foreach($subdirs as $subdir){
                $sub_path = $env_path . '/' . $subdir;
                if(safe_mkdir($sub_path)){
                    $results[] = "✅ Carpeta creada: {$env}/{$subdir}/";
                }else{
                    $errors[] = "❌ Error creando: {$env}/{$subdir}/";
                }
            }
        }else{
            $errors[] = "❌ Error creando carpeta: {$env}/";
            $errors[] = "⚠️ Verifica permisos de escritura en esta carpeta";
            continue;
        }
        
        // 2. Copiar archivos
        foreach($files_to_copy as $file){
            $source = $base_dir . '/' . $file;
            $dest = $env_path . '/' . $file;
            
            if(file_exists($source)){
                if(@copy($source, $dest)){
                    $results[] = "✅ Archivo copiado: {$env}/{$file}";
                }else{
                    $errors[] = "❌ Error copiando: {$env}/{$file}";
                }
            }else{
                $warnings[] = "⚠️ Archivo no encontrado: {$file} (se omitirá)";
            }
        }
    }
    
    // 3. Crear archivo .htaccess para proteger subdirectorios
    foreach($environments as $env){
        $htaccess_path = $base_dir . '/' . $env . '/.htaccess';
        $htaccess_content = <<<HTACCESS
# Proteger archivos .inc.php
<FilesMatch "\.inc\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
HTACCESS;
        
        if(@file_put_contents($htaccess_path, $htaccess_content)){
            $results[] = "✅ .htaccess creado en {$env}/";
        }else{
            $warnings[] = "⚠️ No se pudo crear .htaccess en {$env}/";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador de Entornos v2.0</title>
    
</head>
<body>
    <div class="container">
        <h1>🧪 INSTALADOR DE ENTORNOS v2.0</h1>
        <div class="subtitle">Configuración automática de Testing y Development</div>
        
        <?php if(!isset($_POST['install'])): ?>
        
        <div class="section">
            <h2>📍 Información del Sistema</h2>
            <div class="current-dir">
                <strong>Directorio actual:</strong><br>
                <?php echo htmlspecialchars($base_dir); ?>
            </div>
            
            <table>
                <tr>
                    <td>Permisos de escritura:</td>
                    <td><?php echo is_writable($base_dir) ? '<span class="success">✅ Sí</span>' : '<span class="error">❌ No</span>'; ?></td>
                </tr>
                <tr>
                    <td>Archivos encontrados:</td>
                    <td><?php echo (count($files_to_copy) - count($missing_files)) . ' / ' . count($files_to_copy); ?></td>
                </tr>
                <tr>
                    <td>PHP Version:</td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
            </table>
        </div>
        
        <?php if(count($missing_files) > 0): ?>
        <div class="warning">
            <strong>⚠️ ADVERTENCIA: Archivos faltantes</strong><br>
            Los siguientes archivos no se encontraron y se omitirán:
            <div class="file-list">
                <?php foreach($missing_files as $file): ?>
                    <div class="file-item file-missing">❌ <?php echo htmlspecialchars($file); ?></div>
                <?php endforeach; ?>
            </div>
            <br>
            Puedes continuar de todas formas, pero estos archivos no se copiarán a los nuevos entornos.
        </div>
        <?php endif; ?>
        
        <?php if(!is_writable($base_dir)): ?>
        <div class="error-box">
            <strong>❌ ERROR CRÍTICO: Sin permisos de escritura</strong><br>
            No tienes permisos para crear carpetas en este directorio.<br><br>
            <strong>Solución:</strong><br>
            1. En FileZilla, clic derecho en la carpeta actual<br>
            2. "Permisos de archivo..."<br>
            3. Cambiar a: 755<br>
            4. Marcar: "Recurrir en subdirectorios"<br>
            5. Aplicar y recargar esta página
        </div>
        <?php else: ?>
        
        <div class="section">
            <h2>📦 ¿Qué se va a instalar?</h2>
            <div class="info">
                <strong>Se crearán estas carpetas:</strong><br>
                • <?php echo $base_dir; ?>/testing/<br>
                • <?php echo $base_dir; ?>/dev/<br>
                • Subdirectorios: logs_app/, cache/, backups/<br><br>
                
                <strong>Se copiarán estos archivos encontrados:</strong><br>
                <div class="file-list">
                    <?php foreach($files_to_copy as $file): ?>
                        <?php if(file_exists($base_dir . '/' . $file)): ?>
                            <div class="file-item file-found">✅ <?php echo htmlspecialchars($file); ?></div>
                        <?php else: ?>
                            <div class="file-item file-missing">❌ <?php echo htmlspecialchars($file); ?> (no encontrado)</div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <br>
                <strong>Configuración automática:</strong><br>
                • Cada entorno tendrá su propia configuración<br>
                • Logs separados por entorno<br>
                • Protección .htaccess incluida
            </div>
        </div>
        
        <div class="section">
            <h2>📋 Pasos después de la instalación</h2>
            <div class="step">
                <span class="step-number">1</span>
                <strong>Reemplazar config.inc.php</strong><br>
                Sube el nuevo archivo config_multienv.inc.php y renómbralo a config.inc.php en TODAS las carpetas
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <strong>Crear bases de datos (opcional)</strong><br>
                En cPanel crea: arcanoposada_fondo_test y arcanoposada_fondo_dev
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <strong>Probar los entornos</strong><br>
                • Producción: ./monitor_dashboard.php<br>
                • Pruebas: ./testing/monitor_dashboard.php<br>
                • Desarrollo: ./dev/monitor_dashboard.php
            </div>
            <div class="step">
                <span class="step-number">4</span>
                <strong>ELIMINAR este archivo</strong><br>
                Por seguridad, elimina install_environments.php después de la instalación
            </div>
        </div>
        
        <form method="POST">
            <button type="submit" name="install" class="btn">
                INICIAR INSTALACIÓN
            </button>
        </form>
        
        <?php endif; ?>
        
        <?php else: ?>
        
        <div class="section">
            <h2>📊 Resultados de la Instalación</h2>
            <div class="result">
                <?php if(count($results) > 0): ?>
                    <?php foreach($results as $result): ?>
                        <div class="success"><?php echo htmlspecialchars($result); ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if(count($warnings) > 0): ?>
                    <br>
                    <?php foreach($warnings as $warning): ?>
                        <div class="warning-text"><?php echo htmlspecialchars($warning); ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if(count($errors) > 0): ?>
                    <br>
                    <?php foreach($errors as $error): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <br>
                <?php if(count($errors) === 0): ?>
                    <div class="success" style="margin-top: 20px; font-weight: 700;">
                        ✅ INSTALACIÓN COMPLETADA EXITOSAMENTE
                    </div>
                <?php else: ?>
                    <div class="error" style="margin-top: 20px; font-weight: 700;">
                        ⚠️ INSTALACIÓN COMPLETADA CON ERRORES
                    </div>
                    <div class="warning-text" style="margin-top: 10px;">
                        Revisa los errores arriba y verifica los permisos de las carpetas.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="section">
            <h2>🚀 Próximos Pasos</h2>
            <div class="step">
                <span class="step-number">1</span>
                Reemplaza config.inc.php en las 3 carpetas con el nuevo config_multienv.inc.php
            </div>
            <div class="step">
                <span class="step-number">2</span>
                Accede a:<br>
                • <a href="testing/monitor_dashboard.php" style="color:#ffaa00;">PRUEBAS (testing/)</a><br>
                • <a href="dev/monitor_dashboard.php" style="color:#00aaff;">DESARROLLO (dev/)</a>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <strong style="color:#ff0000;">ELIMINA este archivo (install_environments.php) por seguridad</strong>
            </div>
        </div>
        
        <div class="info">
            ℹ️ Si algo no funciona, revisa los permisos de las carpetas (deben ser 755) 
            y asegúrate de que config.inc.php esté correctamente configurado en cada carpeta.
        </div>
        
        <div class="current-dir" style="margin-top: 20px;">
            <strong>Estructura creada en:</strong><br>
            <?php echo htmlspecialchars($base_dir); ?>/testing/<br>
            <?php echo htmlspecialchars($base_dir); ?>/dev/
        </div>
        
        <?php endif; ?>
    </div>
</body>
</html>