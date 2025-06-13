<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: php/login.php');
    exit();
}

require_once 'models/Recurso.php';


$recursoObj = new Recurso();
$recursos = $recursoObj->obtenerTodos();

$recursosSeleccionados = [];
$precioTotal = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['presupuestar'])) {
    foreach ($recursos as $r) {
        $campoPersonas = 'personas_' . $r['id'];
        if (isset($_POST[$campoPersonas]) && is_numeric($_POST[$campoPersonas])) {
            $numPersonas = intval($_POST[$campoPersonas]);
            if ($numPersonas > 0 && $numPersonas <= $r['capacidad']) {
                $precioRecurso = $r['precio'] * $numPersonas;
                $precioTotal += $precioRecurso;
                $recursosSeleccionados[] = [
                    'nombre' => $r['nombre'],
                    'personas' => $numPersonas,
                    'precio_unitario' => $r['precio'],
                    'precio_total' => $precioRecurso,
                ];
            }
        }
    }
}
?>
