<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Perfil del Usuario</title>
    <link rel="stylesheet" href="../../componentes/editar_perfil.css"> 
</head>
<body>
    <div class="form-container">
        <h2>Editar Perfil</h2>
        <form action="actualizar_usuario.php" method="POST">
            <input type="hidden" name="id_usuarios" value="<?= $usuario['id_usuarios'] ?>">

            <label>Nombre:</label>
            <input type="text" name="nombres" value="<?= htmlspecialchars($usuario['nombres']) ?>" required>

            <label>Apellido:</label>
            <input type="text" name="apellidos" value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>

            <label>Número de Documento:</label>
            <input type="text" id="numero_id" name="numero_id" value="<?= htmlspecialchars($usuario['numero_id']) ?>" readonly>

            <label>Email:</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>

            <label>Teléfono:</label>
            <input type="text" name="celular" pattern="[0-9]{10}" title="Ingrese un número de teléfono válido de 10 digitos" minlength="10" maxlength="10" value="<?= htmlspecialchars($usuario['celular']) ?>">

            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
