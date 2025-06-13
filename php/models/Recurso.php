<?php
require_once __DIR__ . '/../Database.php';

class Recurso {
  private $db;

  public function __construct() {
    $this->db = Database::getInstance();
  }

  public function obtenerPorId($id) {
    $stmt = $this->db->prepare("SELECT * FROM recursos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function obtenerTodos() {
    $stmt = $this->db->query("
        SELECT r.id, r.nombre, t.nombre AS tipo, r.descripcion, r.capacidad, r.precio
        FROM recursos r
        LEFT JOIN tipos_recurso t ON r.tipo_id = t.id
        WHERE r.nombre IS NOT NULL
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}
?>
