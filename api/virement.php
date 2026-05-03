<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$log = __DIR__ . '/../debug_full.log';

// ── Vérifier que l'utilisateur est connecté ──
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non connecté.']);
    exit;
}

// ── Récupérer et valider les données ──
$data = json_decode(file_get_contents('php://input'), true);
file_put_contents($log, "\n=== " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
file_put_contents($log, "DONNÉES REÇUES: " . json_encode($data) . "\n", FILE_APPEND);

if (!is_array($data)) {
    file_put_contents($log, "ERREUR: Data n'est pas un array\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$montant = floatval($data['montant'] ?? 0);
$emailDestinataire = trim($data['email_destinataire'] ?? '');

file_put_contents($log, "MONTANT: " . $montant . " | EMAIL_DEST: " . $emailDestinataire . "\n", FILE_APPEND);

// ── Validations ──
if (empty($emailDestinataire)) {
    file_put_contents($log, "ERREUR: Email vide\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email du destinataire requis.']);
    exit;
}

if ($montant <= 0) {
    file_put_contents($log, "ERREUR: Montant invalide\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Montant invalide.']);
    exit;
}

// ── Récupérer l'expéditeur ──
file_put_contents($log, "RECHERCHE EXPÉDITEUR (ID: " . $_SESSION['user_id'] . ")\n", FILE_APPEND);
$stmtExp = $pdo->prepare("SELECT id, nom, prenom, email, solde FROM utilisateurs WHERE id = ?");
$stmtExp->execute([$_SESSION['user_id']]);
$expediteur = $stmtExp->fetch();

if (!$expediteur) {
    file_put_contents($log, "ERREUR: Expéditeur non trouvé\n", FILE_APPEND);
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Compte expéditeur introuvable.']);
    exit;
}

$expediteur['solde'] = floatval($expediteur['solde']);
file_put_contents($log, "EXPÉDITEUR: " . $expediteur['email'] . " | SOLDE: " . $expediteur['solde'] . "\n", FILE_APPEND);

// ── Vérifier que ce n'est pas un virement vers soi-même ──
if (strtolower($expediteur['email']) === strtolower($emailDestinataire)) {
    file_put_contents($log, "ERREUR: Virement vers soi-même\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas vous virer de l\'argent à vous-même.']);
    exit;
}

// ── Récupérer le destinataire ──
file_put_contents($log, "RECHERCHE DESTINATAIRE (EMAIL: " . $emailDestinataire . ")\n", FILE_APPEND);
$stmtDest = $pdo->prepare("SELECT id, nom, prenom, email, solde FROM utilisateurs WHERE email = ?");
$stmtDest->execute([$emailDestinataire]);
$destinataire = $stmtDest->fetch();

if (!$destinataire) {
    file_put_contents($log, "ERREUR: Destinataire non trouvé\n", FILE_APPEND);
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Aucun compte trouvé avec cet email.']);
    exit;
}

$destinataire['solde'] = floatval($destinataire['solde']);
file_put_contents($log, "DESTINATAIRE: " . $destinataire['email'] . " | SOLDE: " . $destinataire['solde'] . "\n", FILE_APPEND);

// ── Vérifier le solde suffisant ──
if ($expediteur['solde'] < $montant) {
    file_put_contents($log, "ERREUR: Solde insuffisant (" . $expediteur['solde'] . " < " . $montant . ")\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Solde insuffisant pour effectuer ce virement.']);
    exit;
}

file_put_contents($log, "✅ Validation OK - Début de la transaction\n", FILE_APPEND);

// ── Transaction atomique ──
$pdo->beginTransaction();
file_put_contents($log, "TRANSACTION COMMENCÉE\n", FILE_APPEND);

try {
    $soldeExpNouv  = $expediteur['solde'] - $montant;
    $soldeDestNouv = $destinataire['solde'] + $montant;

    file_put_contents($log, "NOUVEAUX SOLDES: Exp=" . $soldeExpNouv . " | Dest=" . $soldeDestNouv . "\n", FILE_APPEND);

    // ✅ DÉBITER L'EXPÉDITEUR
    file_put_contents($log, "EXÉCUTION: UPDATE utilisateurs SET solde = " . $soldeExpNouv . " WHERE id = " . $expediteur['id'] . "\n", FILE_APPEND);
    $result1 = $pdo->prepare("UPDATE utilisateurs SET solde = ? WHERE id = ?")
        ->execute([$soldeExpNouv, $expediteur['id']]);
    file_put_contents($log, "RÉSULTAT UPDATE 1 (Expéditeur): " . ($result1 ? 'TRUE' : 'FALSE') . "\n", FILE_APPEND);

    // ✅ CRÉDITER LE DESTINATAIRE
    file_put_contents($log, "EXÉCUTION: UPDATE utilisateurs SET solde = " . $soldeDestNouv . " WHERE id = " . $destinataire['id'] . "\n", FILE_APPEND);
    $result2 = $pdo->prepare("UPDATE utilisateurs SET solde = ? WHERE id = ?")
        ->execute([$soldeDestNouv, $destinataire['id']]);
    file_put_contents($log, "RÉSULTAT UPDATE 2 (Destinataire): " . ($result2 ? 'TRUE' : 'FALSE') . "\n", FILE_APPEND);

    // ✅ ENREGISTRER LE MOUVEMENT EXPÉDITEUR (retrait)
    file_put_contents($log, "EXÉCUTION: INSERT mouvement expéditeur\n", FILE_APPEND);
    $result3 = $pdo->prepare("INSERT INTO mouvements (utilisateur_id, type, montant, solde_apres, description) VALUES (?, 'retrait', ?, ?, ?)")
        ->execute([
            $expediteur['id'],
            $montant,
            $soldeExpNouv,
            'Virement vers ' . $destinataire['prenom'] . ' ' . $destinataire['nom']
        ]);
    file_put_contents($log, "RÉSULTAT INSERT 1: " . ($result3 ? 'TRUE' : 'FALSE') . "\n", FILE_APPEND);

    // ✅ ENREGISTRER LE MOUVEMENT DESTINATAIRE (dépôt)
    file_put_contents($log, "EXÉCUTION: INSERT mouvement destinataire\n", FILE_APPEND);
    $result4 = $pdo->prepare("INSERT INTO mouvements (utilisateur_id, type, montant, solde_apres, description) VALUES (?, 'depot', ?, ?, ?)")
        ->execute([
            $destinataire['id'],
            $montant,
            $soldeDestNouv,
            'Virement de ' . $expediteur['prenom'] . ' ' . $expediteur['nom']
        ]);
    file_put_contents($log, "RÉSULTAT INSERT 2: " . ($result4 ? 'TRUE' : 'FALSE') . "\n", FILE_APPEND);

    // Vérifier après les UPDATE
    file_put_contents($log, "VÉRIFICATION APRÈS UPDATE...\n", FILE_APPEND);
    $verify = $pdo->prepare("SELECT solde FROM utilisateurs WHERE id = ?");
    $verify->execute([$expediteur['id']]);
    $newSoldeExp = $verify->fetch()['solde'];
    file_put_contents($log, "SOLDE EXPÉDITEUR EN BD: " . $newSoldeExp . "\n", FILE_APPEND);

    $verify->execute([$destinataire['id']]);
    $newSoldeDest = $verify->fetch()['solde'];
    file_put_contents($log, "SOLDE DESTINATAIRE EN BD: " . $newSoldeDest . "\n", FILE_APPEND);

    $pdo->commit();
    file_put_contents($log, "✅ TRANSACTION COMMITTÉE\n", FILE_APPEND);

    echo json_encode([
        'success'       => true,
        'message'       => 'Virement effectué avec succès.',
        'nouveau_solde' => $soldeExpNouv,
        'destinataire'  => $destinataire['prenom'] . ' ' . $destinataire['nom']
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    file_put_contents($log, "❌ ERREUR EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du virement: ' . $e->getMessage()]);
}

file_put_contents($log, "=== FIN ===\n", FILE_APPEND);
?>