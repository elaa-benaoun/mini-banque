<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    echo "Connexion MySQL OK !";
} catch (PDOException $e) {
    echo "ERREUR: " . $e->getMessage();
}