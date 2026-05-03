<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non connecté.']);
    exit;
}

$userId  = $_SESSION['user_id'];
$data    = json_decode(file_get_contents('php://input'), true);
$montant = floatval($data['montant'] ?? 0);

if ($montant <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Montant invalide.']);
    exit;
}

// ── Limite journalière (changez ce chiffre pour modifier la limite) ──
define('LIMITE_JOURNALIERE', 1000.00);

// Calculer le total des retraits d'aujourd'hui
$stmtJour = $pdo->prepare("
    SELECT COALESCE(SUM(montant), 0)
    FROM mouvements
    WHERE utilisateur_id = :uid
      AND type = 'retrait'
      AND DATE(date_mouvement) = CURDATE()
");
$stmtJour->execute(['uid' => $userId]);
$totalJour = floatval($stmtJour->fetchColumn());
$resteJour = LIMITE_JOURNALIERE - $totalJour;

if ($montant > $resteJour) {
    http_response_code(400);
    echo json_encode([
        'success'           => false,
        'message'           => sprintf(
            'Limite journalière dépassée. Vous pouvez encore retirer %.2f DT aujourd\'hui (limite : %.2f DT/jour).',
            max(0, $resteJour),
            LIMITE_JOURNALIERE
        ),
        'limite'            => LIMITE_JOURNALIERE,
        'deja_retire'       => $totalJour,
        'reste_aujourd_hui' => max(0, $resteJour)
    ]);
    exit;
}

// Vérifier le solde
$stmtUser = $pdo->prepare("SELECT solde FROM utilisateurs WHERE id = :uid LIMIT 1");
$stmtUser->execute(['uid' => $userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
    exit;
}

if (floatval($user['solde']) < $montant) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Solde insuffisant.']);
    exit;
}

$nouveauSolde = floatval($user['solde']) - $montant;

$pdo->beginTransaction();
try {
    $pdo->prepare("UPDATE utilisateurs SET solde = :solde WHERE id = :uid")
        ->execute(['solde' => $nouveauSolde, 'uid' => $userId]);

    $pdo->prepare("
        INSERT INTO mouvements (utilisateur_id, type, montant, solde_apres, description)
        VALUES (:uid, 'retrait', :montant, :solde_apres, 'Retrait guichet')
    ")->execute(['uid' => $userId, 'montant' => $montant, 'solde_apres' => $nouveauSolde]);

    $pdo->commit();

    $nouveauTotal = $totalJour + $montant;

    echo json_encode([
        'success'           => true,
        'nouveauSolde'      => $nouveauSolde,
        'limite'            => LIMITE_JOURNALIERE,
        'deja_retire'       => $nouveauTotal,
        'reste_aujourd_hui' => max(0, LIMITE_JOURNALIERE - $nouveauTotal),
        'transaction'       => [
            'type'       => 'retrait',
            'montant'    => $montant,
            'date'       => date('c'),
            'soldeApres' => $nouveauSolde
        ]
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du retrait.']);
}
?>