/**ESTE HTML SE ENCONTRABA DENTRO DEL ARCHIVO EXPORT_EMPRENDEDORES_XLSX.PHP DENTRO DE UN IF EN LA LINEA 323 DE CODIGO */

<!doctype html>
    <html lang="es">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Acceso No Autorizado</title>

        <link rel="stylesheet" href="../../componentes/acceso_noautorizado.css">
    </head>

    <body>
        <div class="auth-modal" aria-hidden="false">
            <div class="auth-card" role="dialog" aria-modal="true" aria-labelledby="authTitle">
                <div class="auth-hdr">
                    <div class="auth-ico">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            <line x1="12" y1="15" x2="12" y2="18" />
                        </svg>
                    </div>
                    <h3 id="authTitle">Acceso No Autorizado</h3>
                </div>
                <div class="auth-body">
                    <p>No tienes acceso a esta funcionalidad.</p>
                    <p>Debes iniciar sesión con el <strong>rol autorizado</strong> para ver esta página.</p>
                    <p class="muted">Te redirigiremos al inicio en <strong id="count">5</strong> segundos.</p>
                </div>
                <div class="auth-actions">
                    <a class="btn btn-secondary" href="<?php echo htmlspecialchars($homeUrl, ENT_QUOTES); ?>">Ir al inicio</a>
                    <button type="button" class="btn btn-primary" id="goNow">Ir ahora</button>
                </div>
            </div>
        </div>
        <script>
    
        </script>
    </body>

    </html>