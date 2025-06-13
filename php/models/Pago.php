<?php
require_once __DIR__ . '/../Database.php';
class Pago {
  private $db;

  public function __construct() {
    $this->db = Database::getInstance();
  }

  public function registrarPago($reserva_id, $monto) {
    $stmt = $this->db->prepare("INSERT INTO pagos(reserva_id, monto) VALUES (?, ?)");
    return $stmt->execute([$reserva_id, $monto]);
  }

  public function obtenerPorReserva($reserva_id) {
    $stmt = $this->db->prepare("SELECT * FROM pagos WHERE reserva_id = ?");
    $stmt->execute([$reserva_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
?>
