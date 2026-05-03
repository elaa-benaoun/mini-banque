<?php
session_start();

// Destruction de la session
session_destroy();

// Suppression des cookies de session si nécessaire
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirection vers la page de connexion
header('Location: login.php');
exit();
?>
