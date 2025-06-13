<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function registrar($nombre, $email, $password) {
        $nuevoId = $this->usuario->registrar($nombre, $email, $password);
        if ($nuevoId) {
            $_SESSION['usuario'] = [
                'id' => $nuevoId,
                'nombre' => $nombre,
                'email' => $email
            ];
            return true;
        }
        return false;
    }

    public function autenticar($email, $password) {
        $user = $this->usuario->autenticar($email, $password);
        if ($user) {
            $_SESSION['usuario'] = $user;
            return true;
        }
        return false;
    }

    public function estaLogueado() {
        return isset($_SESSION['usuario']);
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function redirigir($url) {
        header("Location: $url");
        exit();
    }
}
