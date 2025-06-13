<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once __DIR__ . '/controllers/UsuarioController.php';
    $controller = new UsuarioController();
    $registroExitoso = $controller->registrar($_POST['nombre'], $_POST['email'], $_POST['password']);
    if ($registroExitoso) {
        // Guarda usuario en sesión
        $_SESSION['usuario'] = [
            'nombre' => $_POST['nombre'],
            'email' => $_POST['email']
        ];
        header('Location: reservas.php');  
        exit();
    } else {
        $resultado = "Error en el registro";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
</head>
<body>
    <h2>Registro</h2>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre completo" required><br>
        <input type="email" name="email" placeholder="Correo electrónico" required><br>
        <input type="password" name="password" placeholder="Contraseña" required><br>
        <button type="submit">Registrarse</button>
    </form>
    <a href="login.php">¿Ya tienes cuenta?</a>
    <?php if (isset($resultado)) echo "<p>$resultado</p>"; ?>
</body>
</html>
