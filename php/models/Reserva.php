<?php
require_once __DIR__ . '/../Database.php';

class Reserva {
  private $db;

  public function __construct() {
    $this->db = Database::getInstance();
  }

  /**
   * Crea reserva y devuelve el ID insertado, o false si falla.
   */
  public function crear($usuario_id, $recurso_id, $fecha_inicio, $fecha_fin) {
    // Calcular días (mínimo 1)
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $interval = $inicio->diff($fin);
    $numDias = (int)$interval->format('%a');
    if ($numDias < 1) {
      $numDias = 1;
    }

    $stmt = $this->db->prepare("INSERT INTO reservas(usuario_id, recurso_id, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?)");
    $exito = $stmt->execute([$usuario_id, $recurso_id, $fecha_inicio, $fecha_fin]);

    if ($exito) {
      return $this->db->lastInsertId();
    } else {
      return false;
    }
  }

  public function obtenerPorUsuario($usuario_id) {
    $stmt = $this->db->prepare(
      "SELECT r.id, rc.nombre, r.fecha_inicio, r.fecha_fin FROM reservas r
       JOIN recursos rc ON r.recurso_id = rc.id WHERE r.usuario_id = ?"
    );
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function anular($reserva_id, $usuario_id) {
    $stmt = $this->db->prepare("DELETE FROM reservas WHERE id = ? AND usuario_id = ?");
    return $stmt->execute([$reserva_id, $usuario_id]);
  }
}
?>
