<?php
session_start();

// Comprueba si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirige al login si no está autenticado
    exit();
}

// Conexión a la base de datos (usa tu clase o PDO)
$host = 'localhost';
$dbname = 'tu_basededatos';
$user = 'DBUSER2025';
$pass = 'DBPWD2025';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

// Procesar anulación de reserva si viene por GET
if (isset($_GET['cancelar_reserva_id'])) {
    $reserva_id = intval($_GET['cancelar_reserva_id']);
    $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$reserva_id, $usuario_id]);
    $mensaje = "Reserva anulada correctamente.";
}

// Procesar nueva reserva si viene por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recurso_id = intval($_POST['recurso_id']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Validar datos (simplificado)
    if ($recurso_id && $fecha_inicio && $fecha_fin) {
        // Comprobar ocupación (ejemplo sencillo)
        $stmt = $pdo->prepare("SELECT plazas FROM recursos WHERE id = ?");
        $stmt->execute([$recurso_id]);
        $plazas = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE recurso_id = ? AND 
            ((fecha_inicio <= ? AND fecha_fin >= ?) OR (fecha_inicio <= ? AND fecha_fin >= ?))");
        $stmt->execute([$recurso_id, $fecha_fin, $fecha_fin, $fecha_inicio, $fecha_inicio]);
        $ocupadas = $stmt->fetchColumn();

        if ($ocupadas < $plazas) {
            // Insertar reserva
            $stmt = $pdo->prepare("INSERT INTO reservas (usuario_id, recurso_id, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $recurso_id, $fecha_inicio, $fecha_fin]);
            $mensaje = "Reserva realizada con éxito.";
        } else {
            $mensaje = "No hay plazas disponibles para esas fechas.";
        }
    } else {
        $mensaje = "Por favor, rellena todos los campos.";
    }
}

// Cargar recursos turísticos para mostrar en el formulario
$stmt = $pdo->query("SELECT id, nombre, descripcion, precio FROM recursos");
$recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar reservas del usuario
$stmt = $pdo->prepare("SELECT r.id, r.fecha_inicio, r.fecha_fin, rec.nombre FROM reservas r JOIN recursos rec ON r.recurso_id = rec.id WHERE r.usuario_id = ?");
$stmt->execute([$usuario_id]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Reservas</title>
</head>
<body>
    <nav>
        <!-- Aquí tu menú -->
        <a href="index.html">Inicio</a>
        <a href="reservas.php" class="active">Reservas</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <h1>Hacer una nueva reserva</h1>

    <?php if ($mensaje): ?>
        <p><strong><?= htmlspecialchars($mensaje) ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="reservas.php">
        <label for="recurso_id">Recurso turístico:</label>
        <select name="recurso_id" id="recurso_id" required>
            <option value="">-- Selecciona un recurso --</option>
            <?php foreach ($recursos as $recurso): ?>
                <option value="<?= $recurso['id'] ?>">
                    <?= htmlspecialchars($recurso['nombre']) ?> (<?= htmlspecialchars($recurso['precio']) ?> €)
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="fecha_inicio">Fecha inicio:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" required><br><br>

        <label for="fecha_fin">Fecha fin:</label>
        <input type="date" name="fecha_fin" id="fecha_fin" required><br><br>

        <button type="submit">Reservar</button>
    </form>

    <h2>Mis reservas</h2>
    <?php if ($reservas): ?>
        <ul>
            <?php foreach ($reservas as $reserva): ?>
                <li>
                    <?= htmlspecialchars($reserva['nombre']) ?> - 
                    Desde: <?= htmlspecialchars($reserva['fecha_inicio']) ?> Hasta: <?= htmlspecialchars($reserva['fecha_fin']) ?> 
                    <a href="reservas.php?cancelar_reserva_id=<?= $reserva['id'] ?>" onclick="return confirm('¿Seguro que quieres anular esta reserva?')">[Anular]</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No tienes reservas realizadas.</p>
    <?php endif; ?>
</body>
</html>
