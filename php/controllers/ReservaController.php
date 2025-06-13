<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit();
}

// Requiere los modelos necesarios
require_once '../models/Reserva.php';
require_once '../models/Pago.php';
require_once '../models/Recurso.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario']['id'] ?? null;

    // Validación de usuario
    if (!$usuario_id) {
        header('Location: ../../reservas.php?error=1');
        exit();
    }

    // Confirmar reserva
    if (isset($_POST['reservar'])) {
        $recurso_id = intval($_POST['recurso_id'] ?? 0);
        $fecha_inicio = $_POST['fecha_inicio'] ?? null;
        $fecha_fin = $_POST['fecha_fin'] ?? null;

        // Validación básica de campos
        if (!$recurso_id || !$fecha_inicio || !$fecha_fin) {
            header('Location: ../../reservas.php?error=1');
            exit();
        }

        $reservaObj = new Reserva();
        $pagoObj = new Pago();
        $recursoObj = new Recurso();

        // Obtener recurso y precio
        $recurso = $recursoObj->obtenerPorId($recurso_id);
        if (!$recurso) {
            header('Location: ../../reservas.php?error=1');
            exit();
        }

        // Crear la reserva
        $reserva_id = $reservaObj->crear($usuario_id, $recurso_id, $fecha_inicio, $fecha_fin);
        if (!$reserva_id) {
            header('Location: ../../reservas.php?error=1');
            exit();
        }

        // Calcular días de reserva
        try {
            $inicio = new DateTime($fecha_inicio);
            $fin = new DateTime($fecha_fin);
            $interval = $inicio->diff($fin);
            $numDias = max(1, (int)$interval->format('%a'));
        } catch (Exception $e) {
            // Si falla la fecha, eliminar reserva recién creada y salir
            $reservaObj->anular($reserva_id, $usuario_id);
            header('Location: ../../reservas.php?error=1');
            exit();
        }

        // Calcular el monto del pago
        $monto = $numDias * $recurso['precio'];

        // Registrar el pago
        $exitoPago = $pagoObj->registrarPago($reserva_id, $monto);

        if (!$exitoPago) {
            // Si falla el pago, eliminar la reserva para evitar inconsistencias
            $reservaObj->anular($reserva_id, $usuario_id);
            header('Location: ../../reservas.php?error=1');
            exit();
        }

        // Éxito total
        header('Location: ../../reservas.php?confirmacion=1');
        exit();
    }

    // Anular reserva
    if (isset($_POST['anular'])) {
    require_once '../models/Reserva.php';
    session_start();

    $reserva_id = intval($_POST['reserva_id']);
    $usuario_id = $_SESSION['usuario']['id'] ?? null;

    $reservaObj = new Reserva();
    $ok = $reservaObj->anular($reserva_id, $usuario_id);

    if ($ok) {
        header('Location: ../../reservas.php?anulada=1');
    } else {
        header('Location: ../../reservas.php?error=1');
    }
    exit();
}

}
?>
