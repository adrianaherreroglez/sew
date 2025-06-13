<?php
session_start();
require_once __DIR__ . '/../models/Reserva.php';
require_once __DIR__ . '/../models/Recurso.php';

$rCtrl = new Reserva();
$rc = new Recurso();

if (isset($_POST['reservar'])) {
    $usuario_id = $_SESSION['usuario']['id'];
    $recurso_id = $_POST['recurso_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    $result = $rCtrl->crear($usuario_id, $recurso_id, $fecha_inicio, $fecha_fin);

    if ($result) {
        header('Location: /sew/reservas.php?confirmacion=1');
    } else {
        header('Location: ../reservas.php?error=1');
    }
    exit;
}


if (isset($_POST['anular'])) {
    if (!isset($_SESSION['usuario'])) {
        // Sesión no iniciada, redirigir a login
        header('Location: /sew/php/login.php');
        exit();
    }
    $rCtrl->anular($_POST['reserva_id'], $_SESSION['usuario']['id']);
    header('Location: /sew/reservas.php');
    exit();
}

?>
