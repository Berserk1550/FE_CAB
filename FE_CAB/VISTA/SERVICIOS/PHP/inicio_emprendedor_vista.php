<!DOCTYPE html>
<html>
<head>
    <title>Inicio del emprendedor</title>
</head>
<body>
    <h2>¡Bienvenido, <?php echo $usuario['nombres']; ?>!</h2>

    <?php
    if ($usuario['estado_proceso'] == 'pendiente') {
        echo '<form method="post" action="continuar_proceso.php">';
        echo '<input type="submit" name="continuar" value="Quiero continuar el proceso">';
        echo '</form>';
    }
    ?>
</body>
</html>