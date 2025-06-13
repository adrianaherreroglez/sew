<?php
require_once __DIR__ . '/controllers/UsuarioController.php';

$controller = new UsuarioController();

if ($controller->estaLogueado()) {
    $controller->redirigir('/sew/reservas.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($controller->autenticar($email, $password)) {
        $controller->redirigir('/sew/reservas.php');
    } else {
        $error = "Credenciales incorrectas";
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
    <meta name="keywords" content="Mieres, Asturias, login" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
    <link rel="icon" href="multimedia/imagenes/favicon.ico" />
</head>
<body>
<header>
    <h1><a href="/index.html">Turismo de Mieres</a></h1>
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

<h2>Login</h2>
<form method="POST">
  <input type="email" name="email" placeholder="Correo" required><br>
  <input type="password" name="password" placeholder="Contraseña" required><br>
  <button type="submit">Iniciar sesión</button>
</form>
<?php if ($error) echo "<p>$error</p>"; ?>
<a href="registro.php">¿No tienes cuenta?</a>
</body>
</html>
