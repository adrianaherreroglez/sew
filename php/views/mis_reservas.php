<?php
session_start();
require_once __DIR__ . '/../models/Reserva.php';
$rCtrl = new Reserva();
$mis = $rCtrl->obtenerPorUsuario($_SESSION['usuario']['id']);
?>
<!DOCTYPE html><body>
<h2>Mis Reservas</h2>
<?php foreach ($mis as $r): ?>
  <div>
    <p><?= $r['nombre']; ?> - Reservado el <?= $r['fecha_reserva']; ?></p>
    <form method="POST" action="../controllers/ReservaController.php">
      <input type="hidden" name="reserva_id" value="<?= $r['id']; ?>">
      <button type="submit" name="anular">Anular</button>
    </form>
  </div>
<?php endforeach; ?>
<h2>Reservar nuevo recurso</h2>
<?php require 'recursos.php'; ?>
</body>
