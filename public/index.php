<?php
include '../config/database.php';
include '../config/init.php';

$connecte = isset($_SESSION['id_users']);
$idUtilisateur = $connecte ? $_SESSION['id_users'] : null;
$nbMessagesNonLus = 0;

$listeMessages = [];

if ($connecte) {
    $resultatNonLus = mysqli_query($connexion, "SELECT COUNT(*) AS total FROM messages WHERE id_desti = $idUtilisateur AND lu = 0");
    $ligneNonLus = mysqli_fetch_assoc($resultatNonLus);
    $nbMessagesNonLus = (int) ($ligneNonLus['total'] ?? 0);
}



// Gérer l'ajout / retrait de favoris
if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['favorite_ad_id'])) {
        $idAnnonce = (int) $_POST['favorite_ad_id'];
        $requete = "INSERT IGNORE INTO favorites (id_users, id_ads) VALUES ($idUtilisateur, $idAnnonce)";
        if (mysqli_query($connexion, $requete)) {
            $listeMessages[] = "Annonce ajoutée aux favoris.";
        }
    }

    if (!empty($_POST['unfavorite_ad_id'])) {
        $idAnnonce = (int) $_POST['unfavorite_ad_id'];
        $requete = "DELETE FROM favorites WHERE id_users = $idUtilisateur AND id_ads = $idAnnonce";
        if (mysqli_query($connexion, $requete)) {
            $listeMessages[] = "Annonce retirée des favoris.";
        }
    }
}

// Récupérer la liste des favoris de l'utilisateur connecté
$listeFavoris = [];
if ($connecte) {
    $resultat = mysqli_query($connexion, "SELECT id_ads FROM favorites WHERE id_users = $idUtilisateur");
    while ($ligne = mysqli_fetch_assoc($resultat)) {
        $listeFavoris[] = (int) $ligne['id_ads'];
    }
}

// Récupérer les annonces
$listeAnnonces = [];
$tri = isset($_GET['tri']) ? $_GET['tri'] : 'date_desc';

switch ($tri) {
    case 'prix_asc':
        $orderBy = 'a.price ASC';
        break;
    case 'prix_desc':
        $orderBy = 'a.price DESC';
        break;
    case 'date_asc':
        $orderBy = 'a.created_at ASC';
        break;
    case 'date_desc':
    default:
        $orderBy = 'a.created_at DESC';
        break;
}

$resultat = mysqli_query($connexion, "SELECT a.*, u.email AS author_email FROM annonce a LEFT JOIN users u ON u.id_users = a.id_users ORDER BY $orderBy");
if ($resultat) {
    while ($ligne = mysqli_fetch_assoc($resultat)) {
        $listeAnnonces[] = $ligne;
    }
}

require_once __DIR__ . '/../config/site.php';
$seoTitle = 'AutoBizz — Annonces de véhicules d’occasion près de chez vous';
$seoDescription = 'Parcourez AutoBizz pour acheter ou vendre des véhicules d’occasion près de chez vous. Découvrez les annonces et contactez directement les vendeurs.';
$seoCanonicalPath = '';
$seoStructuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'AutoBizz',
    'url' => autobizz_site_url(''),
    'description' => $seoDescription,
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/partials/seo-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/site-header.php'; ?>

    <main class="container">
        <section class="page-intro" aria-labelledby="page-title">
            <p class="page-eyebrow">AutoBizz, les bonnes affaires près de chez vous</p>
            <h1 id="page-title">Trouvez votre prochaine bonne affaire</h1>
            <p>Parcourez les annonces ou recherchez directement ce qu’il vous faut.</p>
        </section>

        <!-- Barre de recherche AJAX -->
        <div class="search-container">
            <label class="sr-only" for="searchInput">Rechercher une annonce</label>
            <input type="text" id="searchInput" placeholder="Rechercher une annonce..." autocomplete="off">
            <div id="searchResults" class="search-results" style="display: none;"></div>
        </div>

        <!-- Options de tri -->
        <div class="sort-options">
            <label for="sort-select" class="sort-label">Trier par :</label>
            <select id="sort-select" class="sort-select" onchange="changeSort(this.value)">
                <option value="date_desc" <?= $tri === 'date_desc' ? 'selected' : '' ?>>Plus récent</option>
                <option value="date_asc" <?= $tri === 'date_asc' ? 'selected' : '' ?>>Plus ancien</option>
                <option value="prix_asc" <?= $tri === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="prix_desc" <?= $tri === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
            </select>
        </div>

        <?php if (!empty($listeMessages)): ?>
            <div class="messages">
                <?php foreach ($listeMessages as $message): ?>
                    <div><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($connecte): ?>
            <div class="box info-box">
                <div>
                    <strong>Vous pouvez déposer une annonce.</strong>
                    <p>Publiez votre annonce en quelques étapes et trouvez rapidement un acheteur.</p>
                </div>
                <a class="btn btn-primary" href="annonces.php">Déposer mon annonce</a>
            </div>
        <?php else: ?>
            <div class="box info-box">
                <div>
                    <strong>Une annonce à vendre ?</strong>
                    <p>Connectez-vous ou créez un compte pour la publier gratuitement.</p>
                </div>
                <div class="info-actions">
                    <a href="../views/auth/connexion.php" class="btn btn-secondary no-decoration">Se connecter</a>
                    <a href="../views/auth/inscription.php" class="btn btn-primary no-decoration">Créer un compte</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($listeAnnonces)): ?>
            <div class="box empty-state">
                <span class="empty-state-icon" aria-hidden="true">⌕</span>
                <h2>Aucune annonce pour le moment</h2>
                <p>Il n'y a pas encore d'annonces. Soyez le premier à en ajouter !</p>
            </div>
        <?php else: ?>
            <div class="listing-heading">
                <h2>Annonces à découvrir</h2>
                <span><?= count($listeAnnonces); ?> annonce<?= count($listeAnnonces) > 1 ? 's' : ''; ?></span>
            </div>
            <div class="grid listing-grid">
                <?php foreach ($listeAnnonces as $annonce): ?>
                    <article class="box listing-card">
                        <?php if (!empty($annonce['image']) && file_exists(__DIR__ . '/' . $annonce['image'])): ?>
                            <img class="ad-image" src="<?= htmlspecialchars($annonce['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($annonce['title'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <div class="ad-image ad-image-placeholder">Aucune image</div>
                        <?php endif; ?>

                        <h3><a class="listing-title" href="ad.php?id=<?= (int)$annonce['id_ads']; ?>"><?= htmlspecialchars($annonce['title'], ENT_QUOTES, 'UTF-8'); ?></a></h3>
                        <div class="ad-price"><?= number_format($annonce['price'], 2, ',', ' '); ?> €</div>
                        <div class="ad-meta">
                            <small>Posté par <?= htmlspecialchars($annonce['author_email'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8'); ?></small>
                            <?php if ($connecte): ?>
                                <form method="post" class="form-inline-margin">
                                    <?php if (in_array((int)$annonce['id_ads'], $listeFavoris, true)): ?>
                                        <input type="hidden" name="unfavorite_ad_id" value="<?= (int)$annonce['id_ads']; ?>">
                                        <button type="submit" class="btn btn-secondary">Retirer des favoris</button>
                                    <?php else: ?>
                                        <input type="hidden" name="favorite_ad_id" value="<?= (int)$annonce['id_ads']; ?>">
                                        <button type="submit" class="btn btn-primary btn-avec-icone">
                                            <img src="assets/etoile.svg" alt="" class="icone-favori">
                                            Ajouter aux favoris
                                        </button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        // Récupérer le paramètre de tri depuis l'URL
        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('tri') || 'date_desc';

        function changeSort(newSort) {
            const url = new URL(window.location);
            url.searchParams.set('tri', newSort);
            window.location.href = url.toString();
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch('search.php?q=' + encodeURIComponent(query) + '&tri=' + encodeURIComponent(currentSort))
                    .then(response => response.json())
                    .then(data => {
                        if (data.annonces.length === 0) {
                            searchResults.innerHTML = '<p class="no-results">Aucune annonce trouvée</p>';
                        } else {
                            let html = '';
                            data.annonces.forEach(annonce => {
                                html += `
                                    <div class="search-result-item">
                                        <a href="ad.php?id=${annonce.id_ads}" class="result-title">${escapeHtml(annonce.title)}</a>
                                        <p class="result-price">${parseFloat(annonce.price).toFixed(2).replace('.', ',')} €</p>
                                        <small class="result-author">Par ${escapeHtml(annonce.author_email || 'Anonyme')}</small>
                                    </div>
                                `;
                            });
                            searchResults.innerHTML = html;
                        }
                        searchResults.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Erreur AJAX:', error);
                        searchResults.innerHTML = '<p class="error">Erreur lors de la recherche</p>';
                        searchResults.style.display = 'block';
                    });
            }, 300); // Délai de 300ms avant la recherche
        });

        // Fermer les résultats quand on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                searchResults.style.display = 'none';
            }
        });

        // Fonction utilitaire pour échapper le HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
