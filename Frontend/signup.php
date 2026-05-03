<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini-Banque - Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="signup-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Mini-Banque</h1>
                <p>Créez votre compte</p>
            </div>
            <form id="signupForm" class="login-form">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required>
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Votre nom" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                </div>
                <div class="form-group">
                    <label for="motDePasse">Mot de passe</label>
                    <input type="password" id="motDePasse" name="motDePasse" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label for="confirmMotDePasse">Confirmer le mot de passe</label>
                    <input type="password" id="confirmMotDePasse" name="confirmMotDePasse" placeholder="••••••••" required>
                </div>
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
                <div class="login-footer">
                    <p>Déjà un compte ? <a href="login.php">Connectez-vous</a></p>
                </div>
            </form>
            <div class="loading-spinner" id="loadingSpinner" style="display: none;">
                <div class="spinner"></div>
                <p>Inscription en cours...</p>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('signupForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const prenom           = document.getElementById('prenom').value;
            const nom              = document.getElementById('nom').value;
            const email            = document.getElementById('email').value;
            const motDePasse       = document.getElementById('motDePasse').value;
            const confirmMotDePasse = document.getElementById('confirmMotDePasse').value;
            const errorMessage     = document.getElementById('errorMessage');
            const loadingSpinner   = document.getElementById('loadingSpinner');

            errorMessage.textContent = '';
            errorMessage.style.display = 'none';

            if (motDePasse !== confirmMotDePasse) {
                errorMessage.textContent = 'Les mots de passe ne correspondent pas.';
                errorMessage.style.display = 'block';
                return;
            }

            try {
                loadingSpinner.style.display = 'flex';

                const response = await fetch('../api/inscription.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prenom, nom, email, motDePasse })
                });

                const data = await response.json();
                loadingSpinner.style.display = 'none';

                if (data && data.success) {
                    window.location.href = 'login.php?inscrit=1';
                } else {
                    errorMessage.textContent = data.message || 'Erreur lors de l\'inscription.';
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                loadingSpinner.style.display = 'none';
                errorMessage.textContent = 'Erreur réseau. Veuillez réessayer.';
                errorMessage.style.display = 'block';
            }
        });
    </script>
</body>
</html>