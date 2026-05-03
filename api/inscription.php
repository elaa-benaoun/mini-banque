<?php
session_start();
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['prenom']) || empty($data['nom']) || empty($data['email']) || empty($data['motDePasse'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
    exit;
}

// Vérifier si email existe déjà
$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
$stmt->execute([$data['email']]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
    exit;
}

$hash = password_hash($data['motDePasse'], PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, solde) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$data['nom'], $data['prenom'], $data['email'], $hash]);
    echo json_encode(['success' => true, 'message' => 'Inscription réussie.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription.']);
}
?>