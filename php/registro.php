<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once __DIR__ . '/controllers/UsuarioController.php';
    $controller = new UsuarioController();

    // registrar() ahora devuelve un array con id, nombre, email
    $usuario = $controller->registrar($_POST['nombre'], $_POST['email'], $_POST['password']);

    if ($usuario) {
        // Guarda el usuario completo en sesión
        $_SESSION['usuario'] = $usuario;
        header('Location: /sew/reservas.php');
        exit();
    } else {
        $resultado = "Error en el registro";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Mieres - Reservas</title>
    <meta name="author" content="Adriana Herrero González" />
    <meta name="description" content="Página sobre Mieres, Asturias" />
    <meta name="keywords" content="Mieres, Asturias, reservas, recursos, presupuestar" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
    <link rel="icon" href="multimedia/imagenes/favicon.ico" />
</head>
<body>
<header>
    <h1><a href="index.html">Turismo de Mieres</a></h1>
    <nav>
        <a href="../index.html">Inicio</a>
        <a href="../gastronomia.html">Gastronomía</a>
        <a href="../rutas.html">Rutas</a>
        <a href="../meteorologia.html">Meteorología</a>
        <a href="../juego.html">Juego</a>
        <a href="reservas.php" class="active">Reservas</a>
        <a href="../ayuda.html">Ayuda</a>
    </nav>
</header>

<h2>Registro</h2>
<form method="POST">
    <input type="text" name="nombre" placeholder="Nombre completo" required><br>
    <input type="email" name="email" placeholder="Correo electrónico" required><br>
    <input type="password" name="password" placeholder="Contraseña" required><br>
    <button type="submit">Registrarse</button>
</form>
<a href="login.php">¿Ya tienes cuenta?</a>

<?php if (isset($resultado)) echo "<p style='color:red;'>$resultado</p>"; ?>

</body>
</html>
