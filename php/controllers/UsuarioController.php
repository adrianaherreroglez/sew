<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    public function registrar($nombre, $email, $password) {
        $resultado = $this->usuario->registrar($nombre, $email, $password);
        if ($resultado) {
            return "Registro exitoso";
        } else {
            return "Error en el registro";
        }
    }

    public function autenticar($email, $password) {
        return $this->usuario->autenticar($email, $password);
    }
}
