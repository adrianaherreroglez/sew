<?php
class Database {
    private $host = "localhost";
    private $db_name = "central_reservas";
    private $username = "DBUSER2025";
    private $password = "DBPWD2025";
    public $conn;

    public function getConnection(){
        $this->conn = null;
        try{
            $this->conn = new PDO("mysql:host=".$this->host.";dbname=".$this->db_name.";charset=utf8mb4", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $exception){
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
