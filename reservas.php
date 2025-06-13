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

// Variables para mostrar presupuesto y errores
$mostrarPresupuesto = false;
$precioPresupuesto = 0;
$recursoPresupuesto = null;
$errorFecha = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['presupuestar'])) {
    $fechaInicioPresupuesto = $_POST['fecha_inicio'];
    $fechaFinPresupuesto = $_POST['fecha_fin'];
    $recursoIdPresupuesto = intval($_POST['recurso_id']);
    $precioUnitario = floatval($_POST['precio']);

    // Validar fechas
    $fechaActual = date('Y-m-d\TH:i'); // formato datetime-local

    if ($fechaInicioPresupuesto < $fechaActual) {
        $errorFecha = "La fecha de inicio debe ser hoy o una fecha futura.";
    } elseif ($fechaFinPresupuesto <= $fechaInicioPresupuesto) {
        $errorFecha = "La fecha de fin debe ser posterior a la fecha de inicio.";
    } else {
        // Calcular número de días (mínimo 1)
        $inicio = new DateTime($fechaInicioPresupuesto);
        $fin = new DateTime($fechaFinPresupuesto);
        $interval = $inicio->diff($fin);
        $numDias = (int)$interval->format('%a');
        if ($numDias < 1) $numDias = 1;

        $precioPresupuesto = $precioUnitario * $numDias;

        // Buscar recurso para mostrar presupuesto
        foreach ($recursos as $r) {
            if ($r['id'] === $recursoIdPresupuesto) {
                $recursoPresupuesto = $r;
                break;
            }
        }
        $mostrarPresupuesto = true;
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

<main>

<h2>Recursos turísticos disponibles</h2>

<?php if ($errorFecha): ?>
    <p style="color:red;"><?= htmlspecialchars($errorFecha) ?></p>
<?php endif; ?>

<?php
// Mostrar mensajes solo si NO estás presupuestando para evitar confusión
if (isset($_GET['confirmacion']) && !$mostrarPresupuesto) {
    if ($_GET['confirmacion'] === 'Reserva realizada') {
        echo '<p style="color:green;">Reserva confirmada correctamente.</p>';
    } elseif ($_GET['confirmacion'] === 'Reserva anulada') {
        echo '<p style="color:green;">Reserva anulada correctamente.</p>';
    }
}

if (isset($_GET['error']) && !$mostrarPresupuesto) {
    echo '<p style="color:red;">Error al realizar la reserva. Intenta de nuevo.</p>';
}
?>

<table id="tabla-recursos" border="1" cellspacing="0" cellpadding="5">
  <caption>Recursos turísticos disponibles</caption>
  <tr>
    <th scope="col" id="nombre">Nombre</th>
    <th scope="col" id="tipo">Tipo</th>
    <th scope="col" id="descripcion">Descripción</th>
    <th scope="col" id="capacidad">Capacidad</th>
    <th scope="col" id="precio">Precio (€)</th>
    <th scope="col" id="accion">Acción</th>
  </tr>
  <?php foreach ($recursos as $r): ?>
  <tr>
    <th scope="row" id="recurso<?= $r['id'] ?>" headers="nombre"><?= htmlspecialchars($r['nombre']) ?></th>
    <td headers="tipo recurso<?= $r['id'] ?>"><?= htmlspecialchars($r['tipo']) ?></td>
    <td headers="descripcion recurso<?= $r['id'] ?>"><?= htmlspecialchars($r['descripcion']) ?></td>
    <td headers="capacidad recurso<?= $r['id'] ?>"><?= $r['capacidad'] ?></td>
    <td headers="precio recurso<?= $r['id'] ?>"><?= number_format($r['precio'], 2) ?></td>
    <td headers="accion recurso<?= $r['id'] ?>">
      <form method="POST">
        <input type="hidden" name="recurso_id" value="<?= $r['id'] ?>">
        <input type="hidden" name="precio" value="<?= $r['precio'] ?>">
        <label for="fecha_inicio_<?= $r['id'] ?>">Inicio:</label>
        <input type="datetime-local" id="fecha_inicio_<?= $r['id'] ?>" name="fecha_inicio" required>
        <label for="fecha_fin_<?= $r['id'] ?>">Fin:</label>
        <input type="datetime-local" id="fecha_fin_<?= $r['id'] ?>" name="fecha_fin" required>
        <button type="submit" name="presupuestar">Presupuestar</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<?php if ($mostrarPresupuesto && $recursoPresupuesto): ?>
  <h3>Presupuesto para: <?= htmlspecialchars($recursoPresupuesto['nombre']) ?></h3>
  <p>Precio estimado: <?= number_format($precioPresupuesto, 2) ?> €</p>
  <p>Fecha de inicio: <?= date('d/m/Y H:i', strtotime($fechaInicioPresupuesto)) ?></p>
  <p>Fecha de fin: <?= date('d/m/Y H:i', strtotime($fechaFinPresupuesto)) ?></p>

  <form method="POST" action="php/controllers/ReservaController.php">
    <input type="hidden" name="recurso_id" value="<?= $recursoPresupuesto['id'] ?>">
    <input type="hidden" name="fecha_inicio" value="<?= $fechaInicioPresupuesto ?>">
    <input type="hidden" name="fecha_fin" value="<?= $fechaFinPresupuesto ?>">
    <button type="submit" name="reservar">Confirmar reserva</button>
  </form>
<?php endif; ?>

<h2>Mis reservas</h2>

<?php if (empty($reservas)): ?>
    <p>No tienes reservas activas.</p>
<?php else: ?>
<table border="1" cellspacing="0" cellpadding="5">
<thead>
<tr><th>Recurso turístico</th><th>Fecha de reserva</th><th>Acción</th></tr>
</thead>
<tbody>
<?php foreach ($reservas as $reserva): ?>
<tr>
  <td><?= htmlspecialchars($reserva['nombre']) ?></td>
  <td>
    <?= date('d/m/Y H:i', strtotime($reserva['fecha_inicio'])) ?> - <?= date('d/m/Y H:i', strtotime($reserva['fecha_fin'])) ?>
  </td>
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

<!-- Script para limpiar confirmacion/error de la URL tras mostrar mensaje -->
<script>
(() => {
    const url = new URL(window.location);
    if (url.searchParams.has('confirmacion')) {
        url.searchParams.delete('confirmacion');
        window.history.replaceState({}, document.title, url.toString());
    }
    if (url.searchParams.has('error')) {
        url.searchParams.delete('error');
        window.history.replaceState({}, document.title, url.toString());
    }
})();
</script>
</main>
</body>
</html>
