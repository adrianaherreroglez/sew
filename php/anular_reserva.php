<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['reserva_id'])) {
    header('Location: ../reservas.php');
    exit();
}

require_once 'models/Reserva.php';

$usuario = $_SESSION['usuario'];
$usuario_id = $usuario['id'];

$reserva_id = intval($_POST['reserva_id']);

$reservaObj = new Reserva();

// Intentar anular la reserva solo si pertenece al usuario
$result = $reservaObj->anularReserva($reserva_id, $usuario_id);

if ($result) {
    header('Location: ../reservas.php?msg=Reserva anulada correctamente');
} else {
    header('Location: ../reservas.php?error=No se pudo anular la reserva');
}
exit();
