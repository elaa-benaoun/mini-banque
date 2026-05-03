<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userFirstName = isset($_SESSION['user_prenom']) ? htmlspecialchars($_SESSION['user_prenom']) : 'Utilisateur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini-Banque - Tableau de Bord</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .historique-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-select, .filter-date {
            padding: 7px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            background: #fff;
            color: #495057;
            cursor: pointer;
            outline: none;
        }
        .filter-select:focus, .filter-date:focus { border-color: #007bff; }
        .btn-reset-filter {
            padding: 7px 14px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            background: #f8f9fa;
            color: #6c757d;
            cursor: pointer;
        }
        .btn-reset-filter:hover { background: #e2e6ea; }
        .filter-count { font-size: 13px; color: #6c757d; margin-left: auto; }

        /* ── Limite journalière ── */
        .limite-box { margin-top: 14px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 14px 16px; }
        .limite-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .limite-label { font-size: 13px; font-weight: 600; color: #495057; }
        .limite-chiffres { font-size: 13px; color: #6c757d; }
        .limite-chiffres span { font-weight: 600; color: #212529; }
        .limite-barre-bg { height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; }
        .limite-barre { height: 100%; border-radius: 4px; transition: width .4s, background .4s; background: #28a745; }
        .limite-barre.warn   { background: #ffc107; }
        .limite-barre.danger { background: #dc3545; }
        .limite-reste { margin-top: 6px; font-size: 12px; color: #6c757d; text-align: right; }

        .virement-section { margin-top: 24px; }
        .virement-container {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .virement-container h2 { margin-bottom: 18px; font-size: 18px; }
        .virement-form { display: flex; flex-direction: column; gap: 14px; }
        .virement-form .input-group { display: flex; flex-direction: column; gap: 6px; }
        .virement-form label { font-size: 14px; font-weight: 500; color: #495057; }
        .virement-form input {
            padding: 10px 14px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
        }
        .virement-form input:focus { border-color: #007bff; }
        .btn-virement {
            padding: 11px 20px;
            background: #0056b3;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
        }
        .btn-virement:hover { background: #004085; }
        .virement-message { padding: 10px 14px; border-radius: 6px; font-size: 14px; display: none; }
        .virement-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .virement-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body class="dashboard-page">
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Mini-Banque</h1>
                <div class="user-info">
                    <span class="user-greeting">Bienvenue, <?php echo $userFirstName; ?> 👋</span>
                    <a href="profil.php" class="btn btn-logout" style="background:#17a2b8;">👤 Mon Profil</a>
                    <a href="logout.php" class="btn btn-logout">Déconnexion</a>
                </div>
            </div>
        </header>

        <main class="dashboard-main">

            <!-- Solde -->
            <section class="balance-section">
                <div class="balance-card">
                    <h2>Solde de votre compte</h2>
                    <div class="balance-amount" id="balanceAmount">
                        <span class="currency">DT</span>
                        <span class="amount" id="solde">0.00</span>
                    </div>
                    <p class="balance-label">Solde actuel</p>
                </div>
            </section>

            <!-- Dépôt / Retrait -->
            <section class="transaction-section">
                <div class="transaction-container">
                    <h2>Effectuer une transaction</h2>
                    <div class="transaction-form">
                        <div class="input-group">
                            <label for="montant">Montant (DT)</label>
                            <input type="number" id="montant" placeholder="Entrez le montant" min="0.01" step="0.01" required>
                        </div>
                        <div class="button-group">
                            <button type="button" class="btn btn-success" id="depotBtn">💰 Déposer</button>
                            <button type="button" class="btn btn-danger"  id="retraitBtn">🏦 Retirer</button>
                        </div>
                        <div id="transactionMessage" class="transaction-message" style="display: none;"></div>

                        <!-- Jauge limite journalière -->
                        <div class="limite-box">
                            <div class="limite-header">
                                <span class="limite-label">🏦 Limite de retrait journalière</span>
                                <span class="limite-chiffres">
                                    <span id="limiteDejaRetire">0.00</span> / <span id="limiteTotale">1 000.00</span> DT
                                </span>
                            </div>
                            <div class="limite-barre-bg">
                                <div class="limite-barre" id="limiteBarre" style="width:0%"></div>
                            </div>
                            <div class="limite-reste">Reste aujourd'hui : <strong id="limiteReste">1 000.00 DT</strong></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Virement -->
            <section class="virement-section">
                <div class="virement-container">
                    <h2>💸 Virement vers un autre compte</h2>
                    <div class="virement-form">
                        <div class="input-group">
                            <label for="virementEmail">Email du destinataire</label>
                            <input type="email" id="virementEmail" placeholder="destinataire@email.com">
                        </div>
                        <div class="input-group">
                            <label for="virementMontant">Montant (DT)</label>
                            <input type="number" id="virementMontant" placeholder="Entrez le montant" min="0.01" step="0.01">
                        </div>
                        <button type="button" class="btn-virement" id="virementBtn">💸 Envoyer le virement</button>
                        <div id="virementMessage" class="virement-message"></div>
                    </div>
                </div>
            </section>

            <!-- Historique -->
            <section class="historique-section">
                <div class="historique-container">
                    <h2>Historique des transactions</h2>
                    <div class="historique-filters">
                        <select id="filterType" class="filter-select">
                            <option value="tous">Tous les types</option>
                            <option value="depot">Dépôts uniquement</option>
                            <option value="retrait">Retraits uniquement</option>
                        </select>
                        <input type="date" id="filterDateDebut" class="filter-date" title="Date de début">
                        <input type="date" id="filterDateFin"   class="filter-date" title="Date de fin">
                        <button class="btn-reset-filter" id="resetFilter">🔄 Réinitialiser</button>
                        <span class="filter-count" id="filterCount"></span>
                    </div>
                    <div id="historiqueLoading" class="loading-state">
                        <div class="spinner"></div>
                        <p>Chargement de l'historique...</p>
                    </div>
                    <table id="historiqueTable" class="historique-table" style="display: none;">
                        <thead>
                            <tr>
                                <th>Date & Heure</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Solde après</th>
                            </tr>
                        </thead>
                        <tbody id="historiqueBody"></tbody>
                    </table>
                    <div id="historiqueEmpty" class="empty-state" style="display: none;">
                        <p>📋 Aucune transaction trouvée.</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="dashboard-footer">
            <p>&copy; 2024 Mini-Banque. Tous droits réservés.</p>
        </footer>
    </div>

    <script src="script.js"></script>
    <script>
        let toutesLesTransactions = [];

        document.addEventListener('DOMContentLoaded', async () => {
            await loadBalance();
            await loadHistorique();
            await loadLimite();
            document.getElementById('filterType').addEventListener('change', appliquerFiltres);
            document.getElementById('filterDateDebut').addEventListener('change', appliquerFiltres);
            document.getElementById('filterDateFin').addEventListener('change', appliquerFiltres);
            document.getElementById('resetFilter').addEventListener('click', reinitialiserFiltres);
        });

        async function loadBalance() {
            try {
                const res  = await fetch('../api/solde.php');
                const data = await res.json();
                document.getElementById('solde').textContent =
                    (data.success) ? parseFloat(data.solde).toFixed(2) : '0.00';
            } catch (err) {
                document.getElementById('solde').textContent = '---';
            }
        }

        async function loadHistorique() {
            try {
                const res  = await fetch('../api/historique.php');
                const data = await res.json();
                document.getElementById('historiqueLoading').style.display = 'none';
                if (data.success && data.transactions?.length > 0) {
                    toutesLesTransactions = data.transactions;
                    afficherTransactions(toutesLesTransactions);
                } else {
                    toutesLesTransactions = [];
                    document.getElementById('historiqueTable').style.display = 'none';
                    document.getElementById('historiqueEmpty').style.display = 'block';
                }
            } catch {
                document.getElementById('historiqueLoading').style.display = 'none';
                document.getElementById('historiqueEmpty').style.display = 'block';
            }
        }

        function appliquerFiltres() {
            const type      = document.getElementById('filterType').value;
            const dateDebut = document.getElementById('filterDateDebut').value;
            const dateFin   = document.getElementById('filterDateFin').value;
            let filtered = toutesLesTransactions;
            if (type !== 'tous') filtered = filtered.filter(t => t.type === type);
            if (dateDebut) filtered = filtered.filter(t => new Date(t.date) >= new Date(dateDebut + 'T00:00:00'));
            if (dateFin)   filtered = filtered.filter(t => new Date(t.date) <= new Date(dateFin + 'T23:59:59'));
            afficherTransactions(filtered);
        }

        function reinitialiserFiltres() {
            document.getElementById('filterType').value      = 'tous';
            document.getElementById('filterDateDebut').value = '';
            document.getElementById('filterDateFin').value   = '';
            afficherTransactions(toutesLesTransactions);
        }

        function afficherTransactions(transactions) {
            const body  = document.getElementById('historiqueBody');
            const table = document.getElementById('historiqueTable');
            const empty = document.getElementById('historiqueEmpty');
            body.innerHTML = '';
            if (transactions.length > 0) {
                transactions.forEach(t => {
                    const row = document.createElement('tr');
                    const d   = new Date(t.date);
                    row.innerHTML = `
                        <td>${d.toLocaleDateString('fr-FR')} ${d.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'})}</td>
                        <td><span class="type-badge type-${t.type}">${t.type==='depot'?'➕ Dépôt':'➖ Retrait'}</span></td>
                        <td class="amount-cell">${parseFloat(t.montant).toFixed(2)} DT</td>
                        <td class="balance-cell">${parseFloat(t.soldeApres).toFixed(2)} DT</td>
                    `;
                    body.appendChild(row);
                });
                table.style.display = 'table';
                empty.style.display = 'none';
                document.getElementById('filterCount').textContent = transactions.length + ' transaction(s) affichée(s)';
            } else {
                table.style.display = 'none';
                empty.style.display = 'block';
                document.getElementById('filterCount').textContent = '0 transaction trouvée';
            }
        }

        // ── Dépôt ──
        document.getElementById('depotBtn').addEventListener('click', async () => {
            let montantStr = document.getElementById('montant').value.replace(',', '.');
            const montant  = parseFloat(montantStr);
            if (!montant || montant <= 0) { showMessage('Veuillez entrer un montant valide.', 'error'); return; }
            if (!confirm(`Confirmer le dépôt de ${montant.toFixed(2)} DT ?`)) return;
            try {
                const data = await (await fetch('../api/depot.php', {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ montant })
                })).json();
                if (data.success) {
                    showMessage(`Dépôt de ${montant.toFixed(2)} DT effectué avec succès! ✅`, 'success');
                    document.getElementById('montant').value = '';
                    await loadBalance(); await loadHistorique();
                } else { showMessage(data.message || 'Erreur lors du dépôt.', 'error'); }
            } catch { showMessage('Erreur de connexion.', 'error'); }
        });

        // ── Retrait ──
        document.getElementById('retraitBtn').addEventListener('click', async () => {
            let montantStr = document.getElementById('montant').value.replace(',', '.');
            const montant  = parseFloat(montantStr);
            if (!montant || montant <= 0) { showMessage('Veuillez entrer un montant valide.', 'error'); return; }
            if (!confirm(`Confirmer le retrait de ${montant.toFixed(2)} DT ?`)) return;
            try {
                const data = await (await fetch('../api/retrait.php', {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ montant })
                })).json();
                if (data.success) {
                    showMessage(`Retrait de ${montant.toFixed(2)} DT effectué avec succès! ✅`, 'success');
                    document.getElementById('montant').value = '';
                    await loadBalance(); await loadHistorique();
                    mettreAJourJauge(data.limite, data.deja_retire, data.reste_aujourd_hui);
                } else { showMessage(data.message || 'Erreur lors du retrait.', 'error'); }
            } catch { showMessage('Erreur de connexion.', 'error'); }
        });

        // ── Virement ──
        document.getElementById('virementBtn').addEventListener('click', async () => {
            const email    = document.getElementById('virementEmail').value.trim();
            let montantStr = document.getElementById('virementMontant').value.replace(',', '.');
            const montant  = parseFloat(montantStr);
            if (!email) { showVirementMessage('Veuillez entrer l\'email du destinataire.', 'error'); return; }
            if (isNaN(montant) || montant <= 0) { showVirementMessage('Veuillez entrer un montant valide.', 'error'); return; }
            if (!confirm(`Confirmer le virement de ${montant.toFixed(2)} DT vers ${email} ?`)) return;
            try {
                const data = await (await fetch('../api/virement.php', {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ email_destinataire: email, montant })
                })).json();
                if (data.success) {
                    showVirementMessage(`✅ Virement de ${montant.toFixed(2)} DT envoyé à ${data.destinataire} !`, 'success');
                    document.getElementById('virementEmail').value   = '';
                    document.getElementById('virementMontant').value = '';
                    await loadBalance(); await loadHistorique();
                } else { showVirementMessage(data.message || 'Erreur lors du virement.', 'error'); }
            } catch { showVirementMessage('Erreur de connexion au serveur.', 'error'); }
        });

        // ── Limite journalière ──
        async function loadLimite() {
            try {
                const res  = await fetch('../api/limite_retrait.php');
                const data = await res.json();
                if (data.success) {
                    mettreAJourJauge(data.limite, data.deja_retire, data.reste_aujourd_hui);
                }
            } catch (e) { console.error('loadLimite:', e); }
        }

        function mettreAJourJauge(limite, dejaRetire, reste) {
            const pct   = Math.min((dejaRetire / limite) * 100, 100);
            const barre = document.getElementById('limiteBarre');
            document.getElementById('limiteDejaRetire').textContent = parseFloat(dejaRetire).toFixed(2);
            document.getElementById('limiteTotale').textContent     = parseFloat(limite).toFixed(2);
            document.getElementById('limiteReste').textContent      = parseFloat(reste).toFixed(2) + ' DT';
            barre.style.width = pct + '%';
            barre.className   = 'limite-barre' + (pct >= 90 ? ' danger' : pct >= 60 ? ' warn' : '');
        }

        function showMessage(message, type) {
            const div = document.getElementById('transactionMessage');
            div.textContent = message;
            div.className = `transaction-message transaction-${type}`;
            div.style.display = 'block';
            setTimeout(() => { div.style.display = 'none'; }, 4000);
        }

        function showVirementMessage(message, type) {
            const div = document.getElementById('virementMessage');
            div.textContent = message;
            div.className = `virement-message virement-${type}`;
            div.style.display = 'block';
            setTimeout(() => { div.style.display = 'none'; }, 5000);
        }
    </script>
</body>
</html>