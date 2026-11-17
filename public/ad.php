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

// Récupérer l'ID de l'annonce
$idAnnonce = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($idAnnonce <= 0) {
    header('Location: index.php');
    exit;
}

// Gérer l'ajout / retrait de favoris ou l'envoi de message
if ($connecte && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['favorite_ad_id'])) {
        $idAnnonceAjouter = (int) $_POST['favorite_ad_id'];
        $requete = "INSERT IGNORE INTO favorites (id_users, id_ads) VALUES ($idUtilisateur, $idAnnonceAjouter)";
        if (mysqli_query($connexion, $requete)) {
            $listeMessages[] = "Annonce ajoutée aux favoris.";
        }
    }

    if (!empty($_POST['unfavorite_ad_id'])) {
        $idAnnonceRetirer = (int) $_POST['unfavorite_ad_id'];
        $requete = "DELETE FROM favorites WHERE id_users = $idUtilisateur AND id_ads = $idAnnonceRetirer";
        if (mysqli_query($connexion, $requete)) {
            $listeMessages[] = "Annonce retirée des favoris.";
        }
    }

    // Envoyer un message à l'annonceur
    if (!empty($_POST['message']) && isset($_POST['recipient_id'])) {
        $texteMessage = trim($_POST['message']);
        $idDestinataire = (int) $_POST['recipient_id'];

        if ($texteMessage === '') {
            $listeMessages[] = "Le message ne peut pas être vide.";
        } elseif ($idDestinataire === $idUtilisateur) {
            $listeMessages[] = "Vous ne pouvez pas vous envoyer un message à vous-même.";
        } else {
            $texteMessage = mysqli_real_escape_string($connexion, $texteMessage);
            $requete = "INSERT INTO messages (id_expedi, id_desti, id_ads, content) VALUES ($idUtilisateur, $idDestinataire, $idAnnonce, '$texteMessage')";
            if (mysqli_query($connexion, $requete)) {
                $listeMessages[] = "Message envoyé à l'annonceur.";
            } else {
                $listeMessages[] = "Erreur lors de l'envoi du message.";
            }
        }
    }
}

// Vérifier si l'annonce existe
$requete = "SELECT a.*, u.email AS author_email
        FROM annonce a
        LEFT JOIN users u ON u.id_users = a.id_users
        WHERE a.id_ads = $idAnnonce";
$resultat = mysqli_query($connexion, $requete);
$annonce = mysqli_fetch_assoc($resultat);

if (!$annonce) {
    header('Location: index.php');
    exit;
}

// Vérifier si l'annonce est dans les favoris
$estFavori = false;
if ($connecte) {
    $resultat = mysqli_query($connexion, "SELECT 1 FROM favorites WHERE id_users = $idUtilisateur AND id_ads = $idAnnonce LIMIT 1");
    $estFavori = (bool) mysqli_fetch_assoc($resultat);
}

require_once __DIR__ . '/../config/site.php';
$seoTitle = $annonce['title'] . ' | AutoBizz';
$seoDescription = autobizz_meta_excerpt($annonce['description']);
$seoCanonicalPath = 'ad.php?id=' . (int) $annonce['id_ads'];
$seoOgType = 'product';
$seoStructuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $annonce['title'],
    'description' => $seoDescription,
    'offers' => [
        '@type' => 'Offer',
        'url' => autobizz_site_url($seoCanonicalPath),
        'price' => number_format((float) $annonce['price'], 2, '.', ''),
        'priceCurrency' => 'EUR',
    ],
];

if (!empty($annonce['image']) && file_exists(__DIR__ . '/' . $annonce['image'])) {
    $seoImageUrl = autobizz_site_url($annonce['image']);
    $seoStructuredData['image'] = $seoImageUrl;
}
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
        <?php if (!empty($listeMessages)): ?>
            <div class="messages">
                <?php foreach ($listeMessages as $message): ?>
                    <div><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <article class="box ad-detail">
            <h1><?= htmlspecialchars($annonce['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <?php if (!empty($annonce['image']) && file_exists(__DIR__ . '/' . $annonce['image'])): ?>
                <img class="ad-image-large" src="<?= htmlspecialchars($annonce['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($annonce['title'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <p><?= nl2br(htmlspecialchars($annonce['description'], ENT_QUOTES, 'UTF-8')); ?></p>
            <p><strong>Publié par :</strong> <?= htmlspecialchars($annonce['author_email'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Date :</strong> <?= htmlspecialchars($annonce['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>

            <?php if ($connecte): ?>
                <form method="post" class="form-actions info-box">
                    <?php if ($estFavori): ?>
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

                <?php if ($idUtilisateur !== (int)$annonce['id_users']): ?>
                    <div class="box contact-box">
                        <h2>Contacter l'annonceur</h2>
                        <form method="post">
                            <input type="hidden" name="recipient_id" value="<?= (int)$annonce['id_users']; ?>">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="4" required></textarea>
                            <button type="submit" class="btn btn-primary btn-top">Envoyer</button>
                        </form>
                    </article>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
