<?php
require_once __DIR__ . '/../Database.php';

class Usuario {
  private $db;

  public function __construct() {
    $this->db = Database::getInstance();
  }

  public function registrar($nombre, $email, $password) {
    $stmt = $this->db->prepare("INSERT INTO usuarios(nombre, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT)]);
  }


  public function autenticar($email, $password) {
    $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario && password_verify($password, $usuario['password'])) {
      return $usuario;
    }
    return false;
  }
}
?>
