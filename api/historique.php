<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, type, montant, date_mouvement AS date, solde_apres AS soldeApres
    FROM mouvements
    WHERE utilisateur_id = ?
    ORDER BY date_mouvement DESC
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

echo json_encode(['success' => true, 'transactions' => $transactions]);