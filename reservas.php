<?php
session_start();

// Si no hay usuario autenticado, redirigir a login
if (!isset($_SESSION['usuario'])) {
    header('Location: php/login.php');
    exit();
}

require_once 'php/models/Reserva.php';
require_once 'php/models/Recurso.php';

$usuario = $_SESSION['usuario'];
$usuario_id = $usuario['id'] ?? null;

$reservaObj = new Reserva();
$recursoObj = new Recurso();

$reservas = $reservaObj->obtenerPorUsuario($usuario_id);
$recursos = $recursoObj->obtenerTodos();

// Variables para mostrar presupuesto
$mostrarPresupuesto = false;
$precioPresupuesto = 0;
$recursoPresupuesto = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['presupuestar'])) {
    $mostrarPresupuesto = true;
    $precioPresupuesto = floatval($_POST['precio']);
    $recursoIdPresupuesto = intval($_POST['recurso_id']);
    foreach ($recursos as $r) {
        if ($r['id'] === $recursoIdPresupuesto) {
            $recursoPresupuesto = $r;
            break;
        }
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
    <meta name="keywords" content="Mieres, Asturias, turismo, rutas, gastronomía" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
    <link rel="icon" href="multimedia/imagenes/favicon.ico" />
</head>
<body>
<header>
    <h1><a href="index.html">Turismo de Mieres</a></h1>
    <nav>
        <a href="index.html">Inicio</a>
        <a href="gastronomia.html">Gastronomía</a>
        <a href="rutas.html">Rutas</a>
        <a href="meteorologia.html">Meteorología</a>
        <a href="juego.html">Juego</a>
        <a href="reservas.php" class="active">Reservas</a>
        <a href="ayuda.html">Ayuda</a>
    </nav>
</header>

<h2>Recursos turísticos disponibles</h2>
<table cellpadding="5" cellspacing="0">
<thead>
<tr>
  <th>Nombre</th><th>Tipo</th><th>Descripción</th><th>Capacidad</th><th>Inicio</th><th>Fin</th><th>Precio (€)</th><th>Acción</th>
</tr>
</thead>
<tbody>
<?php foreach ($recursos as $r): ?>
<tr>
  <td><?= htmlspecialchars($r['nombre']) ?></td>
  <td><?= htmlspecialchars($r['tipo']) ?></td>
  <td><?= htmlspecialchars($r['descripcion']) ?></td>
  <td><?= $r['capacidad'] ?></td>
  <td><?= date('d/m/Y H:i', strtotime($r['fecha_inicio'])) ?></td>
  <td><?= date('d/m/Y H:i', strtotime($r['fecha_fin'])) ?></td>
  <td><?= number_format($r['precio'], 2) ?></td>
  <td>
    <form method="POST" style="margin:0;">
      <input type="hidden" name="recurso_id" value="<?= $r['id'] ?>">
      <input type="hidden" name="precio" value="<?= $r['precio'] ?>">
      <button type="submit" name="presupuestar">Presupuestar</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php if ($mostrarPresupuesto && $recursoPresupuesto): ?>
  <hr>
  <h3>Presupuesto para: <?= htmlspecialchars($recursoPresupuesto['nombre']) ?></h3>
  <p>Precio: <?= number_format($precioPresupuesto, 2) ?> €</p>
  <form method="POST" action="php/controllers/ReservaController.php">
    <input type="hidden" name="recurso_id" value="<?= $recursoPresupuesto['id'] ?>">
    <button type="submit" name="reservar">Confirmar reserva</button>
  </form>
<?php endif; ?>

<?php
if (isset($_GET['confirmacion'])) {
    echo '<p style="color:green;">Reserva confirmada correctamente.</p>';
}
if (isset($_GET['error'])) {
    echo '<p style="color:red;">Error al realizar la reserva. Intenta de nuevo.</p>';
}
?>

<hr>
<h2>Mis reservas</h2>

<?php if (empty($reservas)): ?>
    <p>No tienes reservas activas.</p>
<?php else: ?>
<table cellpadding="5" cellspacing="0">
<thead>
<tr><th>Recurso turístico</th><th>Fecha de reserva</th><th>Acción</th></tr>
</thead>
<tbody>
<?php foreach ($reservas as $reserva): ?>
<tr>
  <td><?= htmlspecialchars($reserva['nombre']) ?></td>
  <td><?= htmlspecialchars($reserva['fecha_reserva']) ?></td>
  <td>
    <form method="POST" action="php/controllers/ReservaController.php" onsubmit="return confirm('¿Seguro que quieres anular esta reserva?');" style="margin:0;">
      <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
      <button type="submit" name="anular">Anular</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<form action="php/cerrar_sesion.php" method="POST" style="text-align: center; margin: 20px 0;">
    <button type="submit" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
        Cerrar Sesión
    </button>
</form>

</body>
</html>
