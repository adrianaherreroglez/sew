<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: views/recursos.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once __DIR__ . '/controllers/UsuarioController.php';
    $controller = new UsuarioController();
    $user = $controller->autenticar($_POST['email'], $_POST['password']);
    if ($user) {
        $_SESSION['usuario'] = $user;
        header('Location: views/recursos.php');
        exit();
    } else {
        $error = "Credenciales incorrectas";
    }
}

?>
<!DOCTYPE html>
<html><body>
<h2>Login</h2>
<form method="POST">
  <input type="email" name="email" placeholder="Correo" required><br>
  <input type="password" name="password" placeholder="Contraseña" required><br>
  <button type="submit">Iniciar sesión</button>
</form>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
<a href="registro.php">¿No tienes cuenta?</a>
</body></html>
