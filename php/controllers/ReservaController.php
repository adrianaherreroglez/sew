<?php
session_start();

require_once '../models/Reserva.php';
require_once '../models/Pago.php';
require_once '../models/Recurso.php';

class ReservaController
{
    private $reservaModel;
    private $pagoModel;
    private $recursoModel;

    public function __construct()
    {
        $this->reservaModel = new Reserva();
        $this->pagoModel = new Pago();
        $this->recursoModel = new Recurso();
    }

    public function procesarSolicitud()
    {
        if (!isset($_SESSION['usuario'])) {
            $this->redirigir('../login.php');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir('../reservas.php?error=Solicitud inválida');
        }

        $usuario_id = $_SESSION['usuario']['id'] ?? null;
        if (!$usuario_id) {
            $this->redirigir('../reservas.php?error=Usuario no autenticado');
        }

        if (isset($_POST['reservar'])) {
            $this->crearReserva($usuario_id);
        } elseif (isset($_POST['anular'])) {
            $this->anularReserva($usuario_id);
        } else {
            $this->redirigir('../reservas.php?error=Acción no reconocida');
        }
    }

    public function crearReserva($usuario_id)
{
    // Validar que haya datos para confirmar reserva
    if (!isset($_SESSION['reserva_confirmacion'])) {
        $this->redirigir('../../reservas.php?error=No hay datos para confirmar reserva');
    }

    $datos = $_SESSION['reserva_confirmacion'];

    $recurso_id = intval($datos['recurso_id'] ?? 0);
    $fecha_inicio = $datos['fecha_inicio'] ?? null;
    $fecha_fin = $datos['fecha_fin'] ?? null;

    if (!$recurso_id || !$fecha_inicio || !$fecha_fin) {
        $this->redirigir('../../reservas.php?error=Datos incompletos en sesión');
    }

    $recurso = $this->recursoModel->obtenerPorId($recurso_id);
    if (!$recurso) {
        $this->redirigir('../../reservas.php?error=Recurso no encontrado');
    }

    // Comprobar plazas disponibles para el recurso en el rango de fechas
    $plazasDisponibles = $this->recursoModel->obtenerPlazasDisponibles($recurso_id, $fecha_inicio, $fecha_fin);
    if ($plazasDisponibles <= 0) {
        $this->redirigir('../../reservas.php?error=No hay plazas disponibles');
    }

    // Crear la reserva
    $reserva_id = $this->reservaModel->crearReserva($usuario_id, $recurso_id, $fecha_inicio, $fecha_fin);
    if (!$reserva_id) {
        $this->redirigir('../../reservas.php?error=No se pudo crear la reserva');
    }

    // Calcular el monto a pagar
    try {
        $inicio = new DateTime($fecha_inicio);
        $fin = new DateTime($fecha_fin);
        $interval = $inicio->diff($fin);
        $numDias = max(1, (int)$interval->format('%a'));
    } catch (Exception $e) {
        // Si la fecha es inválida, anulamos la reserva creada y redirigimos con error
        $this->reservaModel->anularReserva($reserva_id, $usuario_id);
        $this->redirigir('../../reservas.php?error=Fecha inválida');
    }

    $monto = $numDias * $recurso['precio'];

    // Registrar el pago
    $pago = $this->pagoModel->registrarPago($reserva_id, $monto);

    if (!$pago) {
        // Si el pago falla, anulamos la reserva y redirigimos con error
        $this->reservaModel->anularReserva($reserva_id, $usuario_id);
        $this->redirigir('../../reservas.php?error=Error en el pago');
    }

    // Limpiar datos de confirmación de reserva de la sesión
    unset($_SESSION['reserva_confirmacion']);

    // Redirigir con éxito
    $this->redirigir('../../reservas.php?success=Reserva realizada con éxito');
}

   public function anularReserva($usuario_id)
{
    $reserva_id = intval($_POST['reserva_id'] ?? 0);
    if (!$reserva_id) {
        $this->redirigir('../../reservas.php?error=ID de reserva inválido');
    }

    $reserva = $this->reservaModel->obtenerPorId($reserva_id);
    if (!$reserva || $reserva['usuario_id'] != $usuario_id) {
        $this->redirigir('../../reservas.php?error=Reserva no encontrada o no autorizada');
    }

    $ok = $this->reservaModel->anularReserva($reserva_id, $usuario_id);
    if ($ok) {
        $this->redirigir('../../reservas.php?confirmacion=Reserva anulada');
    } else {
        $this->redirigir('../../reservas.php?error=No se pudo anular la reserva');
    }
}


    private function redirigir($url)
    {
        header('Location: ' . $url);
        exit();
    }
}

$controller = new ReservaController();
$controller->procesarSolicitud();
