<?php
session_start();

require_once 'Database.php';
require_once 'Usuario.php';
require_once 'Reserva.php';

$db = (new Database())->getConnection();
?>
