<?php
require_once __DIR__ . '/../Database.php';
class Reserva {
  private $db;

  public function __construct() {
    $this->db = Database::getInstance();
  }

  public function crear($usuario_id, $recurso_id) {
    $stmt = $this->db->prepare("INSERT INTO reservas(usuario_id, recurso_id) VALUES (?, ?)");
    return $stmt->execute([$usuario_id, $recurso_id]);
  }

  public function obtenerPorUsuario($usuario_id) {
    $stmt = $this->db->prepare(
      "SELECT r.id, rc.nombre, r.fecha_reserva FROM reservas r
       JOIN recursos rc ON r.recurso_id = rc.id WHERE r.usuario_id = ?");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function anular($reserva_id, $usuario_id) {
    $stmt = $this->db->prepare("DELETE FROM reservas WHERE id = ? AND usuario_id = ?");
    return $stmt->execute([$reserva_id, $usuario_id]);
  }
}
?>
