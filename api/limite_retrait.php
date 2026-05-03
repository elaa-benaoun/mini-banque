<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non connecté.']);
    exit;
}

define('LIMITE_JOURNALIERE', 1000.00);

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(montant), 0)
    FROM mouvements
    WHERE utilisateur_id = :uid
      AND type = 'retrait'
      AND DATE(date_mouvement) = CURDATE()
");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$totalJour = floatval($stmt->fetchColumn());
$reste     = max(0, LIMITE_JOURNALIERE - $totalJour);

echo json_encode([
    'success'           => true,
    'limite'            => LIMITE_JOURNALIERE,
    'deja_retire'       => $totalJour,
    'reste_aujourd_hui' => $reste
]);
?>