<?php
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/Recurso.php';
require_once __DIR__ . '/Pago.php';

class Reserva {
  private $db;
  private $recursoModel;
  private $pagoModel;

  public function __construct() {
    $this->db = Database::getInstance();
    $this->recursoModel = new Recurso();
    $this->pagoModel = new Pago();
  }

  // Este método solo crea la reserva en la base y devuelve el ID o false
  public function crearReserva($usuario_id, $recurso_id, $fecha_inicio, $fecha_fin)
  {
    $stmt = $this->db->prepare("INSERT INTO reservas (usuario_id, recurso_id, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?)");
    $exito = $stmt->execute([$usuario_id, $recurso_id, $fecha_inicio, $fecha_fin]);
    if (!$exito) {
      return false;
    }
    return $this->db->lastInsertId();
  }

  public function anularReserva($reserva_id, $usuario_id) {
    $stmt = $this->db->prepare("DELETE FROM reservas WHERE id = ? AND usuario_id = ?");
    return $stmt->execute([$reserva_id, $usuario_id]);
  }

  public function obtenerPorUsuario($usuario_id) {
    $stmt = $this->db->prepare(
        "SELECT r.id, rc.nombre, r.fecha_inicio, r.fecha_fin 
         FROM reservas r
         JOIN recursos rc ON r.recurso_id = rc.id 
         WHERE r.usuario_id = ?"
    );
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
?>
