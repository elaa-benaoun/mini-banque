<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$log = __DIR__ . '/../debug_solde.log';
file_put_contents($log, "=== " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    file_put_contents($log, "ERREUR: Session non trouvée\n", FILE_APPEND);
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non connecté.']);
    exit;
}

file_put_contents($log, "USER_ID: " . $_SESSION['user_id'] . "\n", FILE_APPEND);

// Récupérer le solde réel depuis la table utilisateurs
try {
    $stmt = $pdo->prepare("SELECT solde FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    
    file_put_contents($log, "QUERY EXÉCUTÉE\n", FILE_APPEND);
    file_put_contents($log, "RÉSULTAT: " . print_r($result, true) . "\n", FILE_APPEND);

    if (!$result) {
        file_put_contents($log, "ERREUR: Utilisateur non trouvé\n", FILE_APPEND);
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
        exit;
    }

    $solde = floatval($result['solde']);
    file_put_contents($log, "SOLDE: " . $solde . "\n", FILE_APPEND);

    $response = [
        'success' => true,
        'solde' => $solde,
        'solde_formatted' => number_format($solde, 2, '.', '')
    ];
    
    file_put_contents($log, "RÉPONSE: " . json_encode($response) . "\n", FILE_APPEND);
    echo json_encode($response);
    
} catch (Exception $e) {
    file_put_contents($log, "EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>