-- ============================================
-- SCRIPT D'INITIALISATION BASE DE DONNÉES
-- Mini-Banque
-- ============================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS mini_banque;
USE mini_banque;

-- ============================================
-- Table: utilisateurs
-- ============================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    solde DECIMAL(12, 2) DEFAULT 0.00,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- ============================================
-- Table: mouvements (transactions)
-- ============================================
CREATE TABLE IF NOT EXISTS mouvements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    type VARCHAR(20) NOT NULL, -- 'depot' ou 'retrait'
    montant DECIMAL(12, 2) NOT NULL,
    solde_apres DECIMAL(12, 2) NOT NULL,
    description VARCHAR(255),
    date_mouvement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_user_mouvements (utilisateur_id),
    INDEX idx_date_mouvement (date_mouvement)
);

-- ============================================
-- Données de test (OPTIONNEL)
-- ============================================
-- Avant d'insérer, générez les hashs de mot de passe:
-- password_hash('password123', PASSWORD_BCRYPT)

-- Utilisateurs de test (décommentez pour insérer)

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, solde) VALUES
('Dupont', 'Jean', 'jean@example.com', '$2y$10$VvCfZBzBQa9RMPZQKjRr7.ZH5aIq3cFqIYhcqW8vxEQVJJb0ZYJuK', 1000.00),
('Martin', 'Marie', 'marie@example.com', '$2y$10$VvCfZBzBQa9RMPZQKjRr7.ZH5aIq3cFqIYhcqW8vxEQVJJb0ZYJuK', 500.00),
('Bernard', 'Pierre', 'pierre@example.com', '$2y$10$VvCfZBzBQa9RMPZQKjRr7.ZH5aIq3cFqIYhcqW8vxEQVJJb0ZYJuK', 750.00);

-- Mouvements de test
INSERT INTO mouvements (utilisateur_id, type, montant, solde_apres, description) VALUES
(1, 'depot', 100.00, 1100.00, 'Dépôt initial'),
(1, 'retrait', 50.00, 1050.00, 'Retrait guichet'),
(2, 'depot', 200.00, 700.00, 'Dépôt chèque'),
(3, 'retrait', 100.00, 650.00, 'Retrait ATM');
*/

-- ============================================
-- Vérification
-- ============================================
-- Afficher les tables
SHOW TABLES;

-- Afficher la structure des tables
DESCRIBE utilisateurs;
DESCRIBE mouvements;