<?php
require_once '../models/Reserva.php';
require_once '../models/Pago.php';
require_once '../models/Recurso.php';

class ReservaController 
{
    private $reservaModel;
    private $pagoModel;
    private $recursoModel;

    public function __construct() {
        $this->reservaModel = new Reserva();
        $this->pagoModel = new Pago();
        $this->recursoModel = new Recurso();
    }

    public function procesarSolicitud() 
    {
        session_start();

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

    private function crearReserva($usuario_id)
    {
        $recurso_id = intval($_POST['recurso_id'] ?? 0);
        $fecha_inicio = $_POST['fecha_inicio'] ?? null;
        $fecha_fin = $_POST['fecha_fin'] ?? null;

        if (!$recurso_id || !$fecha_inicio || !$fecha_fin) {
            $this->redirigir('../reservas.php?error=Datos incompletos');
        }

        $recurso = $this->recursoModel->obtenerPorId($recurso_id);
        if (!$recurso) {
            $this->redirigir('../reservas.php?error=Recurso no encontrado');
        }

        $reserva_id = $this->reservaModel->crear($usuario_id, $recurso_id, $fecha_inicio, $fecha_fin);
        if (!$reserva_id) {
            $this->redirigir('../../reservas.php?error=No se pudo crear la reserva');
        }

        try {
            $inicio = new DateTime($fecha_inicio);
            $fin = new DateTime($fecha_fin);
            $interval = $inicio->diff($fin);
            $numDias = max(1, (int)$interval->format('%a'));
        } catch (Exception $e) {
            $this->reservaModel->anular($reserva_id, $usuario_id);
            $this->redirigir('../reservas.php?error=Fecha inválida');
        }

        $monto = $numDias * $recurso['precio'];

        $exitoPago = $this->pagoModel->registrarPago($reserva_id, $monto);
        if (!$exitoPago) {
            $this->reservaModel->anular($reserva_id, $usuario_id);
            $this->redirigir('../reservas.php?error=Error en el pago');
        }

        $this->redirigir('../../reservas.php?confirmacion=Reserva realizada');

    }

    private function anularReserva($usuario_id)
    {
        $reserva_id = intval($_POST['reserva_id'] ?? 0);
        if (!$reserva_id) {
            $this->redirigir('../reservas.php?error=ID de reserva inválido');
        }

        $ok = $this->reservaModel->anular($reserva_id, $usuario_id);
        if ($ok) {
            $this->redirigir('../../reservas.php?confirmacion=Reserva anulada');

        } else {
            $this->redirigir('../reservas.php?error=No se pudo anular la reserva');
        }
    }

    private function redirigir($url)
    {
        header("Location: $url");
        exit();
    }
}

$controller = new ReservaController();
$controller->procesarSolicitud();

