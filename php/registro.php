<?php
require_once __DIR__ . '/controllers/UsuarioController.php';

$controller = new UsuarioController();

if ($controller->estaLogueado()) {
    $controller->redirigir('/sew/reservas.php');
}

$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        if ($controller->registrar($nombre, $email, $password)) {
            $controller->redirigir('/sew/reservas.php');
        } else {
            $resultado = "Error en el registro";
        }
    } catch (Exception $e) {
        $resultado = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mieres - Reservas</title>
    <meta name="author" content="Adriana Herrero González">
    <meta name="description" content="Página sobre Mieres, Asturias">
    <meta name="keywords" content="Mieres, Asturias, registro">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
    <link rel="icon" href="../multimedia/imagenes/favicon.ico">

</head>
<body>
<header>
    <h1><a href="index.html">Turismo de Mieres</a></h1>
    <button>☰</button>
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
<!-- Migas del documento -->
<p>Estás en <a href="php/registro.php" title="Reservas">Inicio</a> &gt;&gt; Reservas &gt;&gt; Registro</p>

<main>
<section>
<form method="POST">
    <h2>Registro</h2>
    <label>Nombre:</label>
    <input type="text" name="nombre" placeholder="Nombre completo" required>
    <label>Correo:</label>
    <input type="email" name="email" placeholder="Correo electrónico" required>
    <label>Contraseña:</label>
    <input type="password" name="password" placeholder="Contraseña" required>
    <nav>
        <button>Registrarse</button>
        <a href="login.php">¿Ya tienes cuenta?</a>
    </nav>
</form>
</section>
<?php if ($resultado) echo "<p>" . htmlspecialchars($resultado) . "</p>"; ?>
</main>
</body>
</html>
