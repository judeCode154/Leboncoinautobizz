<?php
include '../config/database.php';
include '../config/init.php';

if (!isset($_SESSION['id_users'])) {
    header('Location: index.php');
    exit;
}

$idUtilisateur = $_SESSION['id_users'];
$listeMessages = [];
$nbMessagesNonLus = 0;

$resultatNonLus = mysqli_query($connexion, "SELECT COUNT(*) AS total FROM messages WHERE id_desti = $idUtilisateur AND lu = 0");
$ligneNonLus = mysqli_fetch_assoc($resultatNonLus);
$nbMessagesNonLus = (int) ($ligneNonLus['total'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['unfavorite_ad_id'])) {
    $idAnnonce = (int) $_POST['unfavorite_ad_id'];
    $requete = "DELETE FROM favorites WHERE id_users = $idUtilisateur AND id_ads = $idAnnonce";
    if (mysqli_query($connexion, $requete)) {
        $listeMessages[] = 'Annonce retirée des favoris.';
    }
}

$listeFavoris = [];
$requete = "SELECT a.*
        FROM annonce a
        INNER JOIN favorites f ON f.id_ads = a.id_ads
        WHERE f.id_users = $idUtilisateur
        ORDER BY a.created_at DESC";
$resultat = mysqli_query($connexion, $requete);
while ($ligne = mysqli_fetch_assoc($resultat)) {
    $listeFavoris[] = $ligne;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $seoTitle = 'Mes favoris | AutoBizz';
    $seoDescription = 'Retrouvez les annonces automobiles que vous avez enregistrées sur AutoBizz.';
    $seoRobots = 'noindex, nofollow';
    include __DIR__ . '/partials/seo-head.php';
    ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/site-header.php'; ?>

    <main class="container">
        <?php if (!empty($listeMessages)): ?>
            <div class="messages">
                <?php foreach ($listeMessages as $message): ?>
                    <div><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="page-intro favorites-intro" aria-labelledby="favorites-title">
            <p class="page-eyebrow">À retrouver facilement</p>
            <h1 id="favorites-title">Mes favoris</h1>
            <p>Les annonces que vous souhaitez garder sous la main.</p>
        </section>

        <?php if (empty($listeFavoris)): ?>
            <div class="box empty-state">
                <span class="empty-state-icon" aria-hidden="true">♡</span>
                <h2>Votre liste est encore vide</h2>
                <p>Ajoutez des annonces à vos favoris pour les retrouver ici.</p>
                <a class="btn btn-primary no-decoration empty-state-action" href="index.php">Découvrir les annonces</a>
            </div>
        <?php else: ?>
            <div class="listing-heading favorites-heading">
                <h2>Vos annonces enregistrées</h2>
                <span><?= count($listeFavoris); ?> annonce<?= count($listeFavoris) > 1 ? 's' : ''; ?></span>
            </div>
            <div class="favorites-grid">
                <?php foreach ($listeFavoris as $annonce): ?>
                    <article class="box favorite-card">
                        <?php if (!empty($annonce['image']) && file_exists(__DIR__ . '/' . $annonce['image'])): ?>
                            <img class="favorite-image" src="<?= htmlspecialchars($annonce['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($annonce['title'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <div class="favorite-image ad-image-placeholder">Aucune image</div>
                        <?php endif; ?>
                        <div class="favorite-content">
                            <h3><a href="ad.php?id=<?= (int) $annonce['id_ads']; ?>"><?= htmlspecialchars($annonce['title'], ENT_QUOTES, 'UTF-8'); ?></a></h3>
                            <p class="favorite-price"><?= number_format((float) $annonce['price'], 2, ',', ' '); ?> €</p>
                            <p class="favorite-description"><?= nl2br(htmlspecialchars($annonce['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <div class="favorite-actions">
                                <a class="btn btn-secondary no-decoration" href="ad.php?id=<?= (int) $annonce['id_ads']; ?>">Voir l’annonce</a>
                                <form method="post">
                                    <input type="hidden" name="unfavorite_ad_id" value="<?= (int) $annonce['id_ads']; ?>">
                                    <button type="submit" class="btn btn-danger">Retirer</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
