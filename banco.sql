CREATE DATABASE IF NOT EXISTS petshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE petshop;

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    estoque INT NOT NULL
);

INSERT INTO produtos (id, nome, preco, estoque) VALUES
(1, 'Ração 10 kg', 89.90, 12),
(2, 'Coleira', 25.00, 20),
(3, 'Areia sanitária', 19.90, 15),
(4, 'Petisco de frango', 12.50, 30),
(5, 'Shampoo pet', 22.00, 10),
(6, 'Brinquedo de corda', 15.00, 18),
(7, 'Comedouro inox', 18.00, 14),
(8, 'Bebedouro', 24.90, 9),
(9, 'Arranhador', 59.90, 5),
(10, 'Antipulgas', 49.90, 16),
(11, 'Escova de pelos', 16.50, 11),
(12, 'Sachê para gatos', 3.50, 40),
(13, 'Tapete higiênico', 34.90, 22);