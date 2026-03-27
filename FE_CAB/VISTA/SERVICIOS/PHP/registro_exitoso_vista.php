<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso - SENA CAB</title>
    <link rel="icon" href="componentes/img/favicon.ico">
    <style>

    </style>
</head>
<body>
    <div class="contenedor">
        <div class="icono-grande">
            ✅
        </div>
        
        <h1>¡Registro Exitoso!</h1>
        <p class="subtitulo">SENA CAB - Fondo Emprender</p>
        
        <p class="mensaje-principal">
            Hola <strong><?= htmlspecialchars($nombre) ?></strong>, tu registro ha sido completado exitosamente.
        </p>
        
        <div class="caja-correo">
            <h3>📧 Revisa tu correo electrónico</h3>
            
            <p class="instruccion">
                Hemos enviado un correo de confirmación a:
            </p>
            
            <div class="correo-destino">
                <?= htmlspecialchars($correo) ?>
            </div>
            
            <div class="pasos-lista">
                <div class="paso-item">
                    <div class="paso-numero">1</div>
                    <div class="paso-texto">
                        <strong>Abre tu correo</strong> y busca el mensaje del SENA Fondo Emprender
                    </div>
                </div>
                <div class="paso-item">
                    <div class="paso-numero">2</div>
                    <div class="paso-texto">
                        <strong>Confirma tu interés</strong> haciendo clic en "Sí, deseo continuar"
                    </div>
                </div>
                <div class="paso-item">
                    <div class="paso-numero">3</div>
                    <div class="paso-texto">
                        <strong>Espera la habilitación</strong> por parte de tu orientador
                    </div>
                </div>
            </div>
            
            <p class="instruccion">
                <strong>💡 Tip:</strong> Si no ves el correo en tu bandeja principal, revisa la carpeta de spam o correo no deseado.
            </p>
        </div>
        
        <div class="botones">
            <?php if (!empty($url_correo)): ?>
                <a href="<?= htmlspecialchars($url_correo) ?>" target="_blank" class="boton boton-principal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span>Abrir <?= htmlspecialchars($nombre_proveedor) ?></span>
                </a>
            <?php else: ?>
                <button type="button" class="boton boton-principal" onclick="alert('Por favor, abre tu aplicación de correo electrónico y busca el mensaje del SENA Fondo Emprender.')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span>Revisar mi correo</span>
                </button>
            <?php endif; ?>
            
            <a href="../../index" class="boton boton-secundario">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>Volver al inicio</span>
            </a>
        </div>
        
        <div class="nota-pequena">
            <p>
                <strong>¿No recibiste el correo?</strong><br>
                • Verifica que escribiste correctamente tu dirección de correo<br>
                • Revisa la carpeta de spam o correo no deseado<br>
                • El correo puede tardar algunos minutos en llegar<br>
                • Si después de 10 minutos no lo recibes, contacta al SENA CAB
            </p>
        </div>
        
        <div class="logo-footer">
            SENA CAB - Centro de Desarrollo Empresarial 
        </div>
    </div>
</body>
</html>