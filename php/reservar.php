<?php
require_once 'php/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensaje = '';

// Cargar recursos turísticos disponibles
$query = "SELECT * FROM recursos_turisticos WHERE fecha_fin > NOW()";
$stmt = $db->prepare($query);
$stmt->execute();
$recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recurso_id = $_POST['recurso_id'] ?? null;

    if (!$recurso_id) {
        $mensaje = "Selecciona un recurso turístico.";
    } else {
        $reserva = new Reserva($db);
        $reserva->usuario_id = $_SESSION['usuario_id'];
        $reserva->recurso_id = $recurso_id;
        if ($reserva->crearReserva()) {
            $mensaje = "Reserva realizada correctamente.";
        } else {
            $mensaje = "Error al realizar la reserva.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Realizar Reserva</title>
</head>
<body>
<h1>Reserva de recursos turísticos</h1>
<p><?php echo $mensaje; ?></p>

<form method="post" action="">
    <label>Selecciona recurso turístico:</label><br>
    <select name="recurso_id" required>
        <option value="">-- Seleccionar --</option>
        <?php foreach ($recursos as $recurso): ?>
            <option value="<?= htmlspecialchars($recurso['id']) ?>">
                <?= htmlspecialchars($recurso['nombre']) ?> (<?= htmlspecialchars($recurso['fecha_inicio']) ?> - <?= htmlspecialchars($recurso['fecha_fin']) ?>) - Precio: <?= htmlspecialchars($recurso['precio']) ?> €
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Reservar</button>
</form>

<a href="reservas_usuario.php">Ver mis reservas</a>
<a href="logout.php">Cerrar sesión</a>
</body>
</html>
