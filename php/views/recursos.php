<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit();
}
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../models/Recurso.php';
require_once __DIR__ . '/../models/Reserva.php';

$rc = new Recurso();
$recursos = $rc->obtenerTodos();

$rCtrl = new Reserva();
$mis = $rCtrl->obtenerPorUsuario($_SESSION['usuario']['id']);

// Variables para mostrar presupuesto y confirmación
$mostrarPresupuesto = false;
$precioPresupuesto = 0;
$recursoPresupuesto = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['presupuestar'])) {
        // Se pidió presupuesto para un recurso
        $mostrarPresupuesto = true;
        $precioPresupuesto = floatval($_POST['precio']);
        $recursoIdPresupuesto = intval($_POST['recurso_id']);

        // Buscar el recurso para mostrar nombre o info si quieres
        foreach ($recursos as $r) {
            if ($r['id'] === $recursoIdPresupuesto) {
                $recursoPresupuesto = $r;
                break;
            }
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
    <meta name="keywords" content="Mieres, Asturias, reservas, recursos, presupuestar" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="stylesheet" type="text/css" href="../../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../../estilo/layout.css" />
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

<h2>Recursos turísticos disponibles</h2>

<table>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Tipo</th>
        <th>Descripción</th>
        <th>Capacidad</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>Precio (€)</th>
        <th>Acción</th>
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
    <div class="presupuesto">
        <h3>Presupuesto para: <?= htmlspecialchars($recursoPresupuesto['nombre']) ?></h3>
        <p>Precio: <?= number_format($precioPresupuesto, 2) ?> €</p>
        <form method="POST" action="../controllers/ReservaController.php">
            <input type="hidden" name="recurso_id" value="<?= $recursoPresupuesto['id'] ?>">
            <button type="submit" name="reservar">Confirmar reserva</button>
        </form>
    </div>
<?php endif; ?>

<?php
// Mostrar mensajes de confirmación o error desde la URL (por ejemplo tras redirección)
if (isset($_GET['confirmacion'])) {
    echo '<div class="confirmacion">Reserva confirmada correctamente.</div>';
}
if (isset($_GET['error'])) {
    echo '<div class="error">Error al realizar la reserva. Intenta de nuevo.</div>';
}
?>

<h2>Mis reservas</h2>

<?php if (count($mis) === 0): ?>
    <p>No tienes reservas activas.</p>
<?php else: ?>
    <?php foreach ($mis as $r): ?>
        <div>
            <p><strong><?= htmlspecialchars($r['nombre']) ?></strong> – Reservado el <?= $r['fecha_reserva'] ?></p>
            <form method="POST" action="../controllers/ReservaController.php" style="margin:0;">
                <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                <button type="submit" name="anular">Anular</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<script>
window.addEventListener('beforeunload', function() {
  navigator.sendBeacon('/php/cerrar_sesion.php');
});
</script>
</body>
</html>
