<?php
require_once __DIR__ . '/../Database.php';

class Usuario {
  private $db;

  public function __construct() {
    $this->db = Database::getInstance();
  }

  public function existeNombre($nombre) {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre = ?");
    $stmt->execute([$nombre]);
    return $stmt->fetchColumn() > 0;
}
public function existeEmail($email) {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}


public function registrar($nombre, $email, $password) {
    if ($this->existeEmail($email)) {
        throw new Exception("El email ya existe en la base de datos");
    }
    if ($this->existeNombre($nombre)) {
        throw new Exception("El nombre ya existe en la base de datos");
    }

    $stmt = $this->db->prepare("INSERT INTO usuarios(nombre, email, password) VALUES (?, ?, ?)");
    $success = $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT)]);
    if ($success) {
        return $this->db->lastInsertId(); 
    }
    return false;
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
