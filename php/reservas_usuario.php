<?php
require_once 'php/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$reserva = new Reserva($db);
$stmt = $reserva->obtenerReservasPorUsuario($_SESSION['usuario_id']);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Mis Reservas</title>
</head>
<body>
<h1>Mis reservas</h1>

<?php if (empty($reservas)): ?>
    <p>No tienes reservas realizadas.</p>
<?php else: ?>
<table border="1" cellpadding="5">
    <tr>
        <th>Recurso</th>
        <th>Descripción</th>
        <th>Fecha reserva</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($reservas as $res): ?>
    <tr>
        <td><?= htmlspecialchars($res['nombre']) ?></td>
        <td><?= htmlspecialchars($res['descripcion']) ?></td>
        <td><?= htmlspecialchars($res['fecha_reserva']) ?></td>
        <td><?= htmlspecialchars($res['estado']) ?></td>
        <td>
            <?php if ($res['estado'] === 'confirmada'): ?>
                <form method="post" action="anular_reserva.php" style="display:inline;">
                    <input type="hidden" name="reserva_id" value="<?= htmlspecialchars($res['id']) ?>">
                    <button type="submit" onclick="return confirm('¿Seguro que quieres anular esta reserva?');">Anular</button>
                </form>
            <?php else: ?>
                N/A
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<a href="reservar.php">Hacer nueva reserva</a>
<a href="logout.php">Cerrar sesión</a>
</body>
</html>
