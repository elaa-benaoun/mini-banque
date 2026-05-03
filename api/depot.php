<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$montant = floatval($data['montant'] ?? 0);

if ($montant <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Montant invalide.']);
    exit;
}

$stmtUser = $pdo->prepare("SELECT solde FROM utilisateurs WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$user = $stmtUser->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
    exit;
}

$nouveauSolde = $user['solde'] + $montant;

$pdo->beginTransaction();
try {
    $stmtUpd = $pdo->prepare("UPDATE utilisateurs SET solde = ? WHERE id = ?");
    $stmtUpd->execute([$nouveauSolde, $_SESSION['user_id']]);

    $stmtMvt = $pdo->prepare("INSERT INTO mouvements (utilisateur_id, type, montant, solde_apres, description) VALUES (?, 'depot', ?, ?, 'Dépôt')");
    $stmtMvt->execute([$_SESSION['user_id'], $montant, $nouveauSolde]);

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'nouveauSolde' => $nouveauSolde,
        'transaction' => [
            'id' => $pdo->lastInsertId(),
            'type' => 'depot',
            'montant' => $montant,
            'date' => date('c'),
            'soldeApres' => $nouveauSolde
        ]
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du dépôt.']);
}