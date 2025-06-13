<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    public function registrar($nombre, $email, $password) {
    $nuevoId = $this->usuario->registrar($nombre, $email, $password);
    if ($nuevoId) {
        return [
            'id' => $nuevoId,
            'nombre' => $nombre,
            'email' => $email
        ];
    }
    return false;
}


    public function autenticar($email, $password) {
        return $this->usuario->autenticar($email, $password);
    }
}
