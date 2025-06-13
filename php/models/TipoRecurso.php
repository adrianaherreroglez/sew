<?php
require_once __DIR__ . '/../Database.php';
class TipoRecurso {
  private $db;

  public function __construct() {
    $this->db = Database::getInstance();
  }

  public function obtenerTodos() {
    return $this->db->query("SELECT * FROM tipos_recurso")->fetchAll(PDO::FETCH_ASSOC);
  }

  public function insertarDesdeCSV($csvFile) {
    $file = fopen($csvFile, 'r');
    fgetcsv($file); // Skip header
    while (($line = fgetcsv($file)) !== false) {
      $stmt = $this->db->prepare("INSERT INTO tipos_recurso(nombre) VALUES (?)");
      $stmt->execute([$line[0]]);
    }
    fclose($file);
  }
}
?>