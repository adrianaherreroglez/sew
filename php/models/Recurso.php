<?php
require_once __DIR__ . '/../Database.php';
class Recurso {
  private $db;

  public function __construct() {
    $this->db = Database::getInstance();
  }
  

  public function obtenerTodos() {
    $sql = "SELECT r.*, t.nombre AS tipo FROM recursos r JOIN tipos_recurso t ON r.tipo_id = t.id";
    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }

  public function insertarDesdeCSV($csvFile) {
    $file = fopen($csvFile, 'r');
    fgetcsv($file); // Skip header
    while (($line = fgetcsv($file)) !== false) {
      $stmt = $this->db->prepare(
        "INSERT INTO recursos(nombre, descripcion, capacidad, fecha_inicio, fecha_fin, precio, tipo_id) 
         VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute($line);
    }
    fclose($file);
  }
}
?>
