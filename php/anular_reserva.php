<?php
require_once 'php/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reserva_id = $_POST['reserva_id'] ?? null;
    if ($reserva_id) {
        $reserva = new Reserva($db);
        if ($reserva->anularReserva($reserva_id, $_SESSION['usuario_id'])) {
            header('Location: reservas_usuario.php?mensaje=Reserva anulada correctamente');
            exit;
        }
    }
}

header('Location: reservas_usuario.php?error=No se pudo anular la reserva');
exit;
