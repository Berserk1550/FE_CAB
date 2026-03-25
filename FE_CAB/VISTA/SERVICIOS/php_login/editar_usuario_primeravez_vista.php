<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completa tu cuenta - SENA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Completa tu cuenta</h1>
                <p>Verifica y actualiza tus datos para continuar</p>
            </div>

            <div class="card-body">
                <?php if ($viene_de_confirmacion): ?>
                    <div class="welcome-banner">
                        <div class="icon-wrapper">
                            <div class="icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <h3>¡Participación confirmada!</h3>
                        </div>
                        <p><?= htmlspecialchars($mensaje_bienvenida) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($mensaje): ?>
                    <div class="alert error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <form id="mainForm" method="POST" novalidate>
                    <div class="form-grid">
                        <div class="form-field">
                            <label class="field-label">
                                <span class="field-number">1</span>
                                Nombres
                            </label>
                            <div class="input-wrapper">
                                <input type="text" name="nombres" id="nombres" class="input"
                                    value="<?= htmlspecialchars($usuario['nombres']) ?>"
                                    required autocomplete="given-name">
                            </div>
                        </div>

                        <div class="form-field">
                            <label class="field-label">
                                <span class="field-number">2</span>
                                Apellidos
                            </label>
                            <div class="input-wrapper">
                                <input type="text" name="apellidos" id="apellidos" class="input"
                                    value="<?= htmlspecialchars($usuario['apellidos']) ?>"
                                    required autocomplete="family-name">
                            </div>
                        </div>

                        <div class="form-field">
                            <label class="field-label">
                                <span class="field-number">3</span>
                                Celular
                            </label>
                            <div class="input-wrapper">
                                <input type="tel" name="celular" id="celular" class="input"
                                    value="<?= htmlspecialchars($usuario['celular']) ?>"
                                    pattern="[0-9]{7,15}" maxlength="15"
                                    required autocomplete="tel" inputmode="numeric">
                                <div class="help-text">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M12 16v-4M12 8h.01" />
                                    </svg>
                                    Solo números (7 a 15 dígitos)
                                </div>
                            </div>
                        </div>

                        <div class="form-field">
                            <label class="field-label">
                                <span class="field-number">4</span>
                                Correo electrónico
                            </label>
                            <div class="input-wrapper">
                                <input type="email" name="correo" id="correo" class="input"
                                    value="<?= htmlspecialchars($usuario['correo']) ?>"
                                    required autocomplete="email">
                            </div>
                        </div>

                        <div class="divider"></div>

                        <div class="form-field">
                            <label class="field-label">
                                <span class="field-number">5</span>
                                Crea tu contraseña
                            </label>
                            <div class="input-with-toggle">
                                <input type="password" name="contrasena" id="contrasena" class="input"
                                    minlength="6" required autocomplete="new-password">
                                <button type="button" class="toggle-btn" data-target="contrasena">
                                    Ver
                                </button>
                            </div>
                            <div class="help-text">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                Mínimo 6 caracteres (recomendado: mayúscula, número y símbolo)
                            </div>
                            <div class="strength-meter" id="strengthMeter" style="display:none;">
                                <div class="strength-bar-container">
                                    <div class="strength-bar" id="strengthBar"></div>
                                </div>
                                <div class="strength-label" id="strengthLabel">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    </svg>
                                    <span id="strengthText"></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-field">
                            <label class="field-label">
                                <span class="field-number">6</span>
                                Confirmar contraseña
                            </label>
                            <div class="input-with-toggle">
                                <input type="password" name="confirmar_contrasena" id="confirmar" class="input"
                                    minlength="6" required autocomplete="new-password">
                                <button type="button" class="toggle-btn" data-target="confirmar">
                                    Ver
                                </button>
                            </div>
                            <div class="match-indicator" id="matchIndicator">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span id="matchText"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="verificar_identidad" class="btn btn-secondary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="19" y1="12" x2="5" y2="12" />
                                <polyline points="12 19 5 12 12 5" />
                            </svg>
                            <span>Cancelar</span>
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span>Guardar cambios</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($mensajebueno === "success"): ?>
        <div class="modal-overlay show">
            <div class="modal-content">
                <div class="modal-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
                <h2>¡Cuenta configurada!</h2>
                <p>Tus datos se han guardado correctamente. Ya puedes iniciar sesión con tu nueva contraseña.</p>
                <a href="../../index" class="btn btn-primary" style="display: inline-flex;">
                    <span>Ir al inicio de sesión</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    <?php endif; ?>