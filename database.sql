-- Base de datos y tabla del laboratorio CRUD
CREATE DATABASE IF NOT EXISTS productosdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE productosdb;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    producto VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL
);
