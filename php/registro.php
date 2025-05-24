<?php
require_once 'php/config.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = new Usuario($db);
    $usuario->nombre = $_POST['nombre'] ?? '';
    $usuario->email = $_POST['email'] ?? '';
    $usuario->password = $_POST['password'] ?? '';

    // Validación sencilla
    if (!$usuario->nombre || !$usuario->email || !$usuario->password) {
        $mensaje = "Por favor, rellena todos los campos.";
    } elseif (!filter_var($usuario->email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Email no válido.";
    } elseif ($usuario->emailExiste()) {
        $mensaje = "El email ya está registrado.";
    } else {
        if ($usuario->registrar()) {
            $mensaje = "Registro correcto. Ya puedes <a href='login.php'>iniciar sesión</a>.";
        } else {
            $mensaje = "Error al registrar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Registro Usuario</title>
</head>
<body>
<h1>Registro de usuario</h1>
<p style="color:red;"><?php echo $mensaje; ?></p>
<form method="post" action="">
    <label>Nombre: <input type="text" name="nombre" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Contraseña: <input type="password" name="password" required></label><br>
    <button type="submit">Registrar</button>
</form>
<a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
</body>
</html>
