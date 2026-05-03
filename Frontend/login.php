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
    <title>Mini-Banque - Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Mini-Banque</h1>
                <p>Bienvenue</p>
            </div>
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                </div>
                <div class="form-group">
                    <label for="motDePasse">Mot de passe</label>
                    <input type="password" id="motDePasse" name="motDePasse" placeholder="••••••••" required>
                </div>
                <div id="successMessage" class="success-message" style="display: none;"></div>
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                <button type="submit" class="btn btn-primary btn-block">Connexion</button>
                <div class="login-footer">
                    <p>Vous n'avez pas de compte ? <a href="signup.php">Inscrivez-vous</a></p>
                </div>
            </form>
            <div class="loading-spinner" id="loadingSpinner" style="display: none;">
                <div class="spinner"></div>
                <p>Connexion en cours...</p>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
    <script>
        // Notification inscription réussie
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('inscrit') === '1') {
            const successMsg = document.getElementById('successMessage');
            successMsg.textContent = '✅ Inscription réussie ! Vous pouvez vous connecter.';
            successMsg.style.display = 'block';
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const motDePasse = document.getElementById('motDePasse').value;
            const errorMessage = document.getElementById('errorMessage');
            const loadingSpinner = document.getElementById('loadingSpinner');

            errorMessage.textContent = '';
            errorMessage.style.display = 'none';

            try {
                loadingSpinner.style.display = 'flex';

                const response = await fetch('../api/connexion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, motDePasse })
                });

                const data = await response.json();
                loadingSpinner.style.display = 'none';

                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    errorMessage.textContent = data.message || 'Email ou mot de passe incorrect.';
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                loadingSpinner.style.display = 'none';
                errorMessage.textContent = 'Erreur de connexion. Veuillez réessayer.';
                errorMessage.style.display = 'block';
            }
        });
    </script>
</body>
</html>