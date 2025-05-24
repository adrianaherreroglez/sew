<?php
require_once 'Database.php';

class Usuario {
    private $conn;
    private $table = "usuarios";

    public $id;
    public $nombre;
    public $email;
    public $password;

    public function __construct($db){
        $this->conn = $db;
    }

    public function registrar() {
        $query = "INSERT INTO " . $this->table . " SET nombre=:nombre, email=:email, password=:password";

        $stmt = $this->conn->prepare($query);

        $this->nombre=htmlspecialchars(strip_tags($this->nombre));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->password=htmlspecialchars(strip_tags($this->password));

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", password_hash($this->password, PASSWORD_BCRYPT));

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function emailExiste() {
        $query = "SELECT id FROM " . $this->table . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->email]);
        return $stmt->rowCount() > 0;
    }
}
?>
