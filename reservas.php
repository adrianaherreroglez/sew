<?php
session_start();

require_once 'php/models/Reserva.php';
require_once 'php/models/Recurso.php';

// Comprobar usuario autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: php/login.php');
    exit();
}

$reservaObj = new Reserva();
$recursoObj = new Recurso();

$usuario = $_SESSION['usuario'];
$usuario_id = $usuario['id'] ?? null;

// Mensajes que vienen por GET tras redirección
$mensajeError = $_GET['error'] ?? '';
$mensajeExito = $_GET['success'] ?? '';
$mensajeConfirmacion = $_GET['confirmacion'] ?? '';

// Obtener reservas actualizadas para el usuario
$reservas = $reservaObj->obtenerPorUsuario($usuario_id);

// Obtener recursos y añadir plazas disponibles
$recursos = $recursoObj->obtenerTodos();
foreach ($recursos as &$r) {
    $r['plazas_disponibles'] = $recursoObj->obtenerPlazasDisponibles($r['id']);
}
unset($r);

$mostrarPresupuesto = false;
$precioPresupuesto = 0;
$recursoPresupuesto = null;
$errorFecha = '';

// Manejar presupuesto POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['presupuestar'])) {
    $fechaInicioPresupuesto = $_POST['fecha_inicio'] ?? '';
    $fechaFinPresupuesto = $_POST['fecha_fin'] ?? '';
    $recursoIdPresupuesto = intval($_POST['recurso_id'] ?? 0);

    // Validaciones básicas
    $fechaActual = date('Y-m-d\TH:i');

    if ($fechaInicioPresupuesto < $fechaActual) {
        $errorFecha = "La fecha de inicio debe ser hoy o una fecha futura.";
    } elseif ($fechaFinPresupuesto <= $fechaInicioPresupuesto) {
        $errorFecha = "La fecha de fin debe ser posterior a la fecha de inicio.";
    } else {
        // Buscar recurso seleccionado
        $precioUnitario = 0;
        foreach ($recursos as $r) {
            if ($r['id'] === $recursoIdPresupuesto) {
                $precioUnitario = floatval($r['precio']);
                $recursoPresupuesto = $r;
                break;
            }
        }

        if (!$recursoPresupuesto) {
            $errorFecha = "Recurso no encontrado.";
        } else {
            // Calcular días (mínimo 1)
            $inicio = new DateTime($fechaInicioPresupuesto);
            $fin = new DateTime($fechaFinPresupuesto);
            $interval = $inicio->diff($fin);
            $numDias = max(1, (int)$interval->format('%a'));

            $precioPresupuesto = $precioUnitario * $numDias;

            // Guardar en sesión para confirmar después
            $_SESSION['reserva_confirmacion'] = [
                'recurso_id' => $recursoPresupuesto['id'],
                'fecha_inicio' => $fechaInicioPresupuesto,
                'fecha_fin' => $fechaFinPresupuesto,
                'precio' => $precioPresupuesto,
                'nombre_recurso' => $recursoPresupuesto['nombre']
            ];

            $mostrarPresupuesto = true;
        }
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
    <meta name="keywords" content="Mieres, Asturias, reservas, recursos, presupuestar">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/estilo.css">
    <link rel="stylesheet" href="estilo/layout.css">
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

<p>Estás en <a href="index.html" title="Reservas">Inicio</a> >> Reservas</p>

<main>

<?php if ($mensajeError): ?>
    <p><?= htmlspecialchars($mensajeError) ?></p>
<?php endif; ?>
<?php if ($mensajeExito): ?>
    <p><?= htmlspecialchars($mensajeExito) ?></p>
<?php endif; ?>
<?php if ($mensajeConfirmacion): ?>
    <p><?= htmlspecialchars($mensajeConfirmacion) ?></p>
<?php endif; ?>

<?php if ($mostrarPresupuesto && isset($_SESSION['reserva_confirmacion'])):
    $datosReserva = $_SESSION['reserva_confirmacion'];
?>
<section>
    <h2>Presupuesto</h2>
    <h3>Para: <?= htmlspecialchars($datosReserva['nombre_recurso']) ?></h3>
    <p>Precio estimado: <?= number_format($datosReserva['precio'], 2) ?> €</p>
    <p>Fecha inicio: <?= date('d/m/Y H:i', strtotime($datosReserva['fecha_inicio'])) ?></p>
    <p>Fecha fin: <?= date('d/m/Y H:i', strtotime($datosReserva['fecha_fin'])) ?></p>

    <form method="POST" action="php/controllers/ReservaController.php">
      <nav>
        <button name="reservar">Confirmar reserva</button>
</nav>
    </form>
</section>
<?php endif; ?>

<section>
    <h2>Mis reservas</h2>
    <?php if (empty($reservas)): ?>
        <p>No tienes reservas activas.</p>
    <?php else: ?>
    <form method="POST" action="php/controllers/ReservaController.php">
        <label for="reserva_id">Selecciona una reserva para anular:</label>
        <select name="reserva_id" id="reserva_id" required>
            <?php foreach ($reservas as $i => $reserva): ?>
                <option value="<?= htmlspecialchars($reserva['id']) ?>" <?= $i === 0 ? 'selected' : '' ?>>
                    <?= htmlspecialchars($reserva['nombre']) ?> —
                    <?= date('d/m/Y H:i', strtotime($reserva['fecha_inicio'])) ?> a <?= date('d/m/Y H:i', strtotime($reserva['fecha_fin'])) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <nav>
        <button name="anular">Anular reserva</button>
            </nav>
    </form>
    <?php endif; ?>
</section>

<section>
    <h2>Recursos turísticos disponibles</h2>

    <?php if ($errorFecha): ?>
        <p><?= htmlspecialchars($errorFecha) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="recurso_id">Selecciona un recurso turístico:</label>
        <select name="recurso_id" id="recurso_id" required>
            <?php foreach ($recursos as $i => $r): ?>
                <option value="<?= htmlspecialchars($r['id']) ?>" <?= $i === 0 ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['nombre']) ?> — 
                    Tipo: <?= htmlspecialchars($r['tipo']) ?> — 
                    Capacidad: <?= intval($r['capacidad']) ?> — 
                    Precio/día: <?= number_format($r['precio'], 2) ?> € — 
                    Plazas disponibles: <?= intval($r['plazas_disponibles']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="fecha_inicio">Fecha inicio:</label>
        <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required>

        <label for="fecha_fin">Fecha fin:</label>
        <input type="datetime-local" id="fecha_fin" name="fecha_fin" required>

        <nav>
        <button name="presupuestar" type="submit">Presupuestar</button>
            </nav>
    </form>
</section>

<form action="php/cerrar_sesion.php" method="POST">
  <nav>
    <button type="submit">Cerrar Sesión</button>
            </nav>
</form>

</main>
</body>
</html>
