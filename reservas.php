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

// Añadir plazas disponibles para cada recurso
foreach ($recursos as &$r) {
    $r['plazas_disponibles'] = $recursoObj->obtenerPlazasDisponibles($r['id']);
}
unset($r);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mieres - Reservas</title>
    <meta name="author" content="Adriana Herrero González">
    <meta name="description" content="Página sobre Mieres, Asturias">
    <meta name="keywords" content="Mieres, Asturias, reservas, recursos, presupuestar">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="estilo/layout.css">
    <link rel="icon" href="multimedia/imagenes/favicon.ico">
</head>
<body>
<header>
        <h1><a href="index.html">Turismo de Mieres</a></h1>
        <button>☰</button>
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

  <!-- Migas del documento -->
  <p>Estás en <a href="reservas.php" title="Reservas">Inicio</a> >> Reservas</p>

<main>


<?php if ($mostrarPresupuesto && $recursoPresupuesto): ?>
  <h2>Presupuestado</h2>
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
<table>
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
    <form method="POST" action="php/controllers/ReservaController.php">
      <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
      <button type="submit" name="anular">Anular</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<h2>Recursos turísticos disponibles</h2>

<?php if ($errorFecha): ?>
    <p><?= htmlspecialchars($errorFecha) ?></p>
<?php endif; ?>


<section>
<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Capacidad</th>
            <th>Precio por día</th>
            <th>Plazas disponibles</th>
            <th>Fechas</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recursos as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['nombre']) ?></td>
            <td><?= htmlspecialchars($r['tipo']) ?></td>
            <td><?= htmlspecialchars($r['descripcion']) ?></td>
            <td><?= intval($r['capacidad']) ?></td>
            <td><?= number_format($r['precio'], 2) ?></td>
            <td><?= intval($r['plazas_disponibles']) ?></td>
            <td>
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
    </tbody>
</table>

</section>

<form action="php/cerrar_sesion.php" method="POST">
    <nav><button type="submit">
        Cerrar Sesión
    </button>
</nav>
</form>
</section>
</main>
</body>
</html>
