DROP DATABASE IF EXISTS central_reservas;
CREATE DATABASE central_reservas;
USE central_reservas;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE tipos_recurso (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL
);

CREATE TABLE recursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  capacidad INT NOT NULL,
  fecha_inicio DATETIME,
  fecha_fin DATETIME,
  precio DECIMAL(10,2),
  tipo_id INT,
  FOREIGN KEY (tipo_id) REFERENCES tipos_recurso(id) ON DELETE SET NULL
);

CREATE TABLE reservas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT,
  recurso_id INT,
  fecha_reserva DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (recurso_id) REFERENCES recursos(id) ON DELETE CASCADE
);

CREATE TABLE pagos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reserva_id INT UNIQUE,
  monto DECIMAL(10,2),
  fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE
);
