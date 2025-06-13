<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../php/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['recurso_id'])) {
    header('Location: ../reservas.php');
    exit();
}

require_once 'models/Reserva.php';

$usuario = $_SESSION['usuario'];
$usuario_id = $usuario['id'];

$recurso_id = intval($_POST['recurso_id']);

$reservaObj = new Reserva();

// Intentar crear la reserva
$result = $reservaObj->crearReserva($usuario_id, $recurso_id);

if ($result) {
    // Reserva creada con éxito
    header('Location: ../reservas.php?msg=Reserva realizada con éxito');
} else {
    // Error al crear reserva (p.ej. recurso lleno)
    header('Location: ../reservas.php?error=No se pudo realizar la reserva');
}
exit();
