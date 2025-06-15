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

public function obtenerPlazasDisponibles($recurso_id, $fecha_inicio = null, $fecha_fin = null) {
    // Obtener capacidad total del recurso
    $stmt = $this->db->prepare("SELECT capacidad FROM recursos WHERE id = ?");
    $stmt->execute([$recurso_id]);
    $capacidad = $stmt->fetchColumn();

    if ($capacidad === false) {
        return 0;
    }

    if (!$fecha_inicio || !$fecha_fin) {
        $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM reservas WHERE recurso_id = ?");
        $stmt2->execute([$recurso_id]);
        $ocupadas = $stmt2->fetchColumn();
    } else {
        $stmt2 = $this->db->prepare("
            SELECT COUNT(*) FROM reservas 
            WHERE recurso_id = ? 
              AND fecha_inicio < ? 
              AND fecha_fin > ?
        ");
        $stmt2->execute([$recurso_id, $fecha_fin, $fecha_inicio]);
        $ocupadas = $stmt2->fetchColumn();
    }

    $disponibles = $capacidad - $ocupadas;
    return max(0, $disponibles);
}



}
?>
