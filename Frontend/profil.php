<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
$userFirstName = htmlspecialchars($_SESSION['user_prenom'] ?? 'Utilisateur');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini-Banque – Mon Profil</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profil-main { max-width: 760px; margin: 0 auto; padding: 30px 20px 60px; display: flex; flex-direction: column; gap: 24px; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: #0056b3; font-size: 14px; font-weight: 500; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }

        /* Hero */
        .profil-hero { background: linear-gradient(135deg, #0056b3 0%, #007bff 100%); border-radius: 12px; padding: 28px; color: #fff; display: flex; align-items: center; gap: 20px; }
        .avatar { width: 68px; height: 68px; border-radius: 50%; background: rgba(255,255,255,0.2); border: 3px solid rgba(255,255,255,0.4); display: flex; align-items: center; justify-content: center; font-size: 30px; flex-shrink: 0; }
        .hero-info h2 { color: #fff; font-size: 20px; margin-bottom: 4px; }
        .hero-info p  { opacity: .85; font-size: 13px; }
        .hero-badges  { margin-left: auto; text-align: right; }
        .badge-solde  { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); border-radius: 20px; padding: 6px 16px; font-size: 14px; font-weight: 600; display: inline-block; margin-bottom: 6px; }
        .badge-membre { font-size: 12px; opacity: .75; }

        /* Card */
        .profil-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); overflow: hidden; }
        .card-header  { padding: 16px 22px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
        .card-icon    { font-size: 18px; }
        .card-header h3 { font-size: 15px; font-weight: 600; }
        .card-body    { padding: 22px; }

        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; }
        .stat-item  { background: #f8f9fa; border-radius: 8px; padding: 16px; text-align: center; }
        .stat-value { font-size: 24px; font-weight: 700; color: #0056b3; margin-bottom: 4px; }
        .stat-label { font-size: 12px; color: #6c757d; }

        /* Form */
        .profil-form  { display: flex; flex-direction: column; gap: 14px; }
        .form-row     { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-field   { display: flex; flex-direction: column; gap: 5px; }
        .form-field label { font-size: 13px; font-weight: 600; color: #495057; }
        .form-field input { padding: 10px 14px; border: 1.5px solid #dee2e6; border-radius: 7px; font-size: 14px; font-family: inherit; outline: none; transition: border-color .2s; }
        .form-field input:focus { border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.1); }
        .form-field input[readonly] { background: #f8f9fa; color: #6c757d; cursor: not-allowed; }
        .field-hint   { font-size: 11px; color: #6c757d; }

        /* Boutons */
        .btn-save   { padding: 10px 22px; background: #0056b3; color: #fff; border: none; border-radius: 7px; font-size: 14px; font-weight: 600; font-family: inherit; cursor: pointer; align-self: flex-start; }
        .btn-save:hover   { background: #004085; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #b02a37; }

        /* Alertes */
        .alert { padding: 10px 14px; border-radius: 7px; font-size: 13px; display: none; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Force MDP */
        .strength-bg  { height: 4px; background: #dee2e6; border-radius: 2px; margin-top: 5px; overflow: hidden; }
        .strength-bar { height: 100%; width: 0%; border-radius: 2px; transition: width .3s, background .3s; }
        .strength-txt { font-size: 11px; margin-top: 3px; }

        @media (max-width: 520px) { .form-row, .stats-grid { grid-template-columns: 1fr; } .hero-badges { display: none; } }
    </style>
</head>
<body class="dashboard-page">
<div class="dashboard-container">

    <header class="dashboard-header">
        <div class="header-content">
            <h1>Mini-Banque</h1>
            <div class="user-info">
                <span class="user-greeting">Bienvenue, <?php echo $userFirstName; ?> 👋</span>
                <a href="index.php" class="btn btn-logout" style="background:#6c757d;">Tableau de bord</a>
                <a href="logout.php" class="btn btn-logout">Déconnexion</a>
            </div>
        </div>
    </header>

    <main class="profil-main">

        <a href="index.php" class="back-link">← Retour au tableau de bord</a>

        <!-- Hero -->
        <div class="profil-hero">
            <div class="avatar">👤</div>
            <div class="hero-info">
                <h2 id="heroNom">Chargement...</h2>
                <p  id="heroEmail">—</p>
            </div>
            <div class="hero-badges">
                <div class="badge-solde"  id="heroSolde">— DT</div>
                <div class="badge-membre" id="heroMembre">Membre depuis —</div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="profil-card">
            <div class="card-header"><span class="card-icon">📊</span><h3>Statistiques du compte</h3></div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-item"><div class="stat-value" id="statDepots">—</div><div class="stat-label">Dépôts effectués</div></div>
                    <div class="stat-item"><div class="stat-value" id="statRetraits">—</div><div class="stat-label">Retraits effectués</div></div>
                    <div class="stat-item"><div class="stat-value" id="statTotal">—</div><div class="stat-label">Transactions totales</div></div>
                </div>
            </div>
        </div>

        <!-- Modifier nom -->
        <div class="profil-card">
            <div class="card-header"><span class="card-icon">✏️</span><h3>Modifier mes informations</h3></div>
            <div class="card-body">
                <div class="profil-form">
                    <div class="form-row">
                        <div class="form-field"><label>Prénom</label><input type="text" id="champPrenom" maxlength="100"></div>
                        <div class="form-field"><label>Nom</label><input type="text" id="champNom" maxlength="100"></div>
                    </div>
                    <div class="form-field">
                        <label>Adresse e-mail</label>
                        <input type="email" id="champEmail" readonly>
                        <span class="field-hint">⚠️ L'adresse e-mail ne peut pas être modifiée.</span>
                    </div>
                    <div class="alert alert-success" id="nomSuccess"></div>
                    <div class="alert alert-error"   id="nomError"></div>
                    <button class="btn-save" id="btnNom">💾 Sauvegarder les modifications</button>
                </div>
            </div>
        </div>

        <!-- Changer MDP -->
        <div class="profil-card">
            <div class="card-header"><span class="card-icon">🔐</span><h3>Changer le mot de passe</h3></div>
            <div class="card-body">
                <div class="profil-form">
                    <div class="form-field">
                        <label>Mot de passe actuel</label>
                        <input type="password" id="ancienMdp" placeholder="••••••••" autocomplete="current-password">
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label>Nouveau mot de passe</label>
                            <input type="password" id="nouveauMdp" placeholder="••••••••" autocomplete="new-password">
                            <div class="strength-bg"><div class="strength-bar" id="strengthBar"></div></div>
                            <div class="strength-txt" id="strengthTxt"></div>
                        </div>
                        <div class="form-field">
                            <label>Confirmer le nouveau mot de passe</label>
                            <input type="password" id="confirmerMdp" placeholder="••••••••" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="alert alert-success" id="mdpSuccess"></div>
                    <div class="alert alert-error"   id="mdpError"></div>
                    <button class="btn-save btn-danger" id="btnMdp">🔐 Changer le mot de passe</button>
                </div>
            </div>
        </div>

    </main>

    <footer class="dashboard-footer">
        <p>&copy; 2024 Mini-Banque. Tous droits réservés.</p>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    await chargerProfil();
    await chargerStats();

    // ── Modifier nom ──
    document.getElementById('btnNom').addEventListener('click', async () => {
        const prenom = document.getElementById('champPrenom').value.trim();
        const nom    = document.getElementById('champNom').value.trim();
        cacherAlertes('nom');
        if (!prenom || !nom) { showAlert('nomError', 'Veuillez remplir le prénom et le nom.'); return; }
        try {
            const res  = await fetch('../api/profil.php', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ action: 'modifier_nom', nom, prenom })
            });
            const data = await res.json();
            if (data.success) {
                showAlert('nomSuccess', '✅ ' + data.message);
                document.getElementById('heroNom').textContent = data.prenom + ' ' + data.nom;
                document.querySelector('.user-greeting').textContent = 'Bienvenue, ' + data.prenom + ' 👋';
            } else {
                showAlert('nomError', '❌ ' + data.message);
            }
        } catch { showAlert('nomError', '❌ Erreur de connexion.'); }
    });

    // ── Changer MDP ──
    document.getElementById('nouveauMdp').addEventListener('input', function () { evaluerForce(this.value); });

    document.getElementById('btnMdp').addEventListener('click', async () => {
        const ancien    = document.getElementById('ancienMdp').value;
        const nouveau   = document.getElementById('nouveauMdp').value;
        const confirmer = document.getElementById('confirmerMdp').value;
        cacherAlertes('mdp');
        if (!ancien || !nouveau || !confirmer) { showAlert('mdpError', 'Veuillez remplir tous les champs.'); return; }
        if (nouveau !== confirmer)             { showAlert('mdpError', '❌ Les mots de passe ne correspondent pas.'); return; }
        if (nouveau.length < 6)                { showAlert('mdpError', '❌ Minimum 6 caractères requis.'); return; }
        try {
            const res  = await fetch('../api/profil.php', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ action: 'changer_mdp', ancien_mot_de_passe: ancien, nouveau_mot_de_passe: nouveau, confirmer_mot_de_passe: confirmer })
            });
            const data = await res.json();
            if (data.success) {
                showAlert('mdpSuccess', '✅ ' + data.message);
                document.getElementById('ancienMdp').value    = '';
                document.getElementById('nouveauMdp').value   = '';
                document.getElementById('confirmerMdp').value = '';
                document.getElementById('strengthBar').style.width = '0%';
                document.getElementById('strengthTxt').textContent = '';
            } else {
                showAlert('mdpError', '❌ ' + data.message);
            }
        } catch { showAlert('mdpError', '❌ Erreur de connexion.'); }
    });
});

async function chargerProfil() {
    try {
        const data = await (await fetch('../api/profil.php')).json();
        if (!data.success) return;
        const u = data.user;
        document.getElementById('heroNom').textContent    = u.prenom + ' ' + u.nom;
        document.getElementById('heroEmail').textContent  = u.email;
        document.getElementById('heroSolde').textContent  = parseFloat(u.solde).toFixed(2) + ' DT';
        const d = new Date(u.date_creation);
        document.getElementById('heroMembre').textContent = 'Membre depuis ' + d.toLocaleDateString('fr-FR', {year:'numeric', month:'long'});
        document.getElementById('champPrenom').value = u.prenom;
        document.getElementById('champNom').value    = u.nom;
        document.getElementById('champEmail').value  = u.email;
    } catch (e) { console.error(e); }
}

async function chargerStats() {
    try {
        const data = await (await fetch('../api/historique.php')).json();
        if (data.success && Array.isArray(data.transactions)) {
            const tx = data.transactions;
            document.getElementById('statDepots').textContent   = tx.filter(t => t.type === 'depot').length;
            document.getElementById('statRetraits').textContent = tx.filter(t => t.type === 'retrait').length;
            document.getElementById('statTotal').textContent    = tx.length;
        } else {
            ['statDepots','statRetraits','statTotal'].forEach(id => document.getElementById(id).textContent = '0');
        }
    } catch { ['statDepots','statRetraits','statTotal'].forEach(id => document.getElementById(id).textContent = '—'); }
}

function evaluerForce(mdp) {
    const bar = document.getElementById('strengthBar');
    const txt = document.getElementById('strengthTxt');
    if (!mdp) { bar.style.width = '0%'; txt.textContent = ''; return; }
    let score = 0;
    if (mdp.length >= 6)              score++;
    if (mdp.length >= 10)             score++;
    if (/[A-Z]/.test(mdp))           score++;
    if (/[0-9]/.test(mdp))           score++;
    if (/[^A-Za-z0-9]/.test(mdp))   score++;
    const niveaux = [
        {pct:'20%', color:'#dc3545', text:'Très faible'},
        {pct:'40%', color:'#fd7e14', text:'Faible'},
        {pct:'60%', color:'#ffc107', text:'Moyen'},
        {pct:'80%', color:'#20c997', text:'Fort'},
        {pct:'100%',color:'#28a745', text:'Très fort'}
    ];
    const n = niveaux[Math.min(score - 1, 4)] || niveaux[0];
    bar.style.width      = n.pct;
    bar.style.background = n.color;
    txt.textContent      = n.text;
    txt.style.color      = n.color;
}

function showAlert(id, msg) {
    const el = document.getElementById(id);
    el.textContent   = msg;
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 5000);
}
function cacherAlertes(prefix) {
    ['Success','Error'].forEach(s => { const el = document.getElementById(prefix+s); if(el) el.style.display='none'; });
}
</script>
</body>
</html>