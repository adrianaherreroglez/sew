<?php
require_once 'Database.php';

class Reserva {
    private $conn;
    private $table = "reservas";

    public $id;
    public $usuario_id;
    public $recurso_id;
    public $fecha_reserva;
    public $estado;

    public function __construct($db){
        $this->conn = $db;
    }

    public function crearReserva() {
        $query = "INSERT INTO " . $this->table . " SET usuario_id=:usuario_id, recurso_id=:recurso_id, fecha_reserva=NOW(), estado='confirmada'";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":recurso_id", $this->recurso_id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function obtenerReservasPorUsuario($usuario_id) {
        $query = "SELECT r.id, rt.nombre, rt.descripcion, r.fecha_reserva, r.estado 
                  FROM " . $this->table . " r 
                  JOIN recursos_turisticos rt ON r.recurso_id = rt.id 
                  WHERE r.usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        return $stmt;
    }

    public function anularReserva($reserva_id, $usuario_id) {
        $query = "UPDATE " . $this->table . " SET estado='anulada' WHERE id=:reserva_id AND usuario_id=:usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":reserva_id", $reserva_id);
        $stmt->bindParam(":usuario_id", $usuario_id);
        return $stmt->execute();
    }
}
?>
