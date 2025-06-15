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

        $plazasDisponibles = $this->recursoModel->obtenerPlazasDisponibles($recurso_id);
        if ($plazasDisponibles <= 0) {
            $this->redirigir('../../reservas.php?error=No hay plazas disponibles');
        }

        $reserva_id = $this->reservaModel->crearReserva($usuario_id, $recurso_id, $fecha_inicio, $fecha_fin);
        if (!$reserva_id) {
            $this->redirigir('../../reservas.php?error=No se pudo crear la reserva');
        }

        $this->recursoModel->reducirPlazas($recurso_id, 1);

        try {
            $inicio = new DateTime($fecha_inicio);
            $fin = new DateTime($fecha_fin);
            $interval = $inicio->diff($fin);
            $numDias = max(1, (int)$interval->format('%a'));
        } catch (Exception $e) {
            $this->reservaModel->anularReserva($reserva_id, $usuario_id);
            $this->redirigir('../../reservas.php?error=Fecha inválida');
        }

        $monto = $numDias * $recurso['precio'];
        $pago = $this->pagoModel->registrarPago($reserva_id, $monto);

        if (!$pago) {
            $this->reservaModel->anularReserva($reserva_id, $usuario_id);
            $this->redirigir('../../reservas.php?error=Error en el pago');
        }

        unset($_SESSION['reserva_confirmacion']);

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
            $this->recursoModel->aumentarPlazas($reserva['recurso_id'], 1);
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
