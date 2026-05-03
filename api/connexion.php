<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Vérifier que le JSON est valide
    if (!is_array($data)) {
        throw new Exception("Données JSON invalides.");
    }

    // Vérifier les champs requis
    if (empty($data['email']) || empty($data['motDePasse'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email et mot de passe requis.'
        ]);
        exit;
    }

    // Préparer la requête
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom, mot_de_passe 
        FROM utilisateurs 
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute(['email' => $data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification utilisateur + mot de passe
    if ($user && password_verify($data['motDePasse'], $user['mot_de_passe'])) {

        // Régénérer l'ID de session (sécurité)
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect.'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur.',
        'error' => $e->getMessage()
    ]);
}
?>