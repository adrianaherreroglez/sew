<?php
require_once 'config.php';

class Database {
  private static $instance = null;
  private $pdo;

  private function __construct() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new Database();
    }
    return self::$instance->pdo;
  }
}
?>

