<!-- LA EXTENSION PHP DE ESTE ARCHIVO SE ENCUENTRA UBICADA EN LA CARPETA php DENTRO DE LA CARPETA servicios DEL PROYECTO -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $es_continuar ? 'Decisión Confirmada' : 'Decisión Registrada' ?> - SENA CAB</title>
    <link rel="icon" href="../../componentes/img/favicon.ico">
    <style>
        
    </style>
</head>
<body>
    
    <div class="contenedor">
        <div class="icono-grande">
            <?= $es_continuar ? '🎉' : '👋' ?>
        </div>
        
        <h1><?= $es_continuar ? '¡Excelente decisión!' : 'Decisión registrada' ?></h1>
        <p class="subtitulo">SENA CAB - Fondo Emprender</p>
        
        <p class="mensaje-principal">
            <?= htmlspecialchars($mensaje) ?>
        </p>
        
        <?php if ($es_continuar): ?>
            <div class="caja-info">
                <h3>📋 Próximos pasos</h3>
                <ul>
                    <li>Tu orientador del SENA CAB recibirá una notificación inmediata</li>
                    <li>Se pondrá en contacto contigo en los próximos días hábiles</li>
                    <li>Recibirás acceso completo a los recursos del programa</li>
                    <li>Podrás acceder a tu panel de emprendedor</li>
                </ul>
            </div>
            
            <div class="nota-pequena">
                <p>💡 <strong>Tip:</strong> Mientras tanto, te recomendamos comenzar a definir tu idea de negocio y reunir información sobre tu mercado objetivo.</p>
            </div>
        <?php else: ?>
            <div class="caja-info">
                <h3>📌 Ten en cuenta</h3>
                <ul>
                    <li>Tu decisión ha sido registrada en el sistema</li>
                    <li>Puedes cambiar de opinión en cualquier momento</li>
                    <li>Siempre serás bienvenido/a en el programa</li>
                    <li>Conservarás tu cuenta para uso futuro</li>
                </ul>
            </div>
            
            <div class="nota-pequena">
                <p>💡 Si cambias de opinión, puedes volver a acceder al correo de confirmación o contactar directamente al SENA CAB.</p>
            </div>
        <?php endif; ?>
        
        <div class="botones">
            <?php if ($es_continuar): ?>
                <?php if ($viene_desde_correo): ?>
                    <a href="index" class="boton boton-principal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        <span>Iniciar sesión</span>
                    </a>
                <?php else: ?>
                    <a href="dashboard" class="boton boton-principal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        <span>Ir a mi Dashboard</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <a href="../../index" class="boton boton-secundario">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>Volver al inicio</span>
            </a>
        </div>
        
        <div class="logo-footer">
            SENA CAB - Centro de Desarrollo Empresarial 
        </div>
    </div>
</body>
</html>