<?php
class DB {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO('mysql:host=localhost;dbname=central_reservas', 'DBUSER2025', 'DBPWD2025');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getTipoId($nombre_tipo) {
        $stmt = $this->pdo->prepare("SELECT id FROM tipos_recurso WHERE nombre = ?");
        $stmt->execute([$nombre_tipo]);
        $row = $stmt->fetch();

        if ($row) {
            return $row['id'];
        } else {
            $insert = $this->pdo->prepare("INSERT INTO tipos_recurso (nombre) VALUES (?)");
            $insert->execute([$nombre_tipo]);
            return $this->pdo->lastInsertId();
        }
    }

    public function insertarRecursosDesdeCSV($archivo) {
    if (($handle = fopen($archivo, "r")) !== FALSE) {
        fgetcsv($handle); // Saltar encabezado

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) < 5) {
                continue;
            }

            list($nombre, $descripcion, $capacidad, $precio, $tipo_nombre) = $data;

            $tipo_id = $this->getTipoId(trim($tipo_nombre));

            $stmt = $this->pdo->prepare("
                INSERT INTO recursos (nombre, descripcion, capacidad, precio, tipo_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nombre, $descripcion, $capacidad, $precio, $tipo_id
            ]);
        }
        fclose($handle);
        echo "Recursos importados correctamente.";
    } else {
        echo "Error al abrir el archivo.";
    }
}

}

$db = new DB();
$db->insertarRecursosDesdeCSV("recursos.csv");
?>
