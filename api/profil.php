<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// ── GET : récupérer les infos du profil ──
if ($method === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT id, nom, prenom, email, solde, date_creation FROM utilisateurs WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
    exit;
}

// ── POST : modifier profil ──
if ($method === 'POST') {
    try {
        $data   = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';

        if ($action === 'modifier_nom') {
            $nom    = trim($data['nom']    ?? '');
            $prenom = trim($data['prenom'] ?? '');
            if (empty($nom) || empty($prenom)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nom et prénom requis.']);
                exit;
            }
            $pdo->prepare("UPDATE utilisateurs SET nom=:nom, prenom=:prenom WHERE id=:id")
                ->execute(['nom' => $nom, 'prenom' => $prenom, 'id' => $userId]);
            $_SESSION['user_nom']    = $nom;
            $_SESSION['user_prenom'] = $prenom;
            echo json_encode(['success' => true, 'message' => 'Nom mis à jour.', 'nom' => $nom, 'prenom' => $prenom]);
            exit;
        }

        if ($action === 'changer_mdp') {
            $ancien  = $data['ancien_mot_de_passe']    ?? '';
            $nouveau = $data['nouveau_mot_de_passe']   ?? '';
            $confirm = $data['confirmer_mot_de_passe'] ?? '';
            if (!$ancien || !$nouveau || !$confirm) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
                exit;
            }
            if ($nouveau !== $confirm) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
                exit;
            }
            if (strlen($nouveau) < 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Minimum 6 caractères requis.']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id=:id LIMIT 1");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($ancien, $user['mot_de_passe'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Ancien mot de passe incorrect.']);
                exit;
            }
            $pdo->prepare("UPDATE utilisateurs SET mot_de_passe=:mdp WHERE id=:id")
                ->execute(['mdp' => password_hash($nouveau, PASSWORD_BCRYPT), 'id' => $userId]);
            echo json_encode(['success' => true, 'message' => 'Mot de passe modifié avec succès.']);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
    exit;
}
?>