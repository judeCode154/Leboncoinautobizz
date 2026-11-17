<?php
include '../config/database.php';
include '../config/init.php';

if (!isset($_SESSION['id_users'])) {
    header('Location: index.php');
    exit;
}

$idUtilisateur = $_SESSION['id_users'];
$listeMessages = [];
$typeMessage = 'success';
$nbMessagesNonLus = 0;

mysqli_query($connexion, "UPDATE messages SET lu = 1 WHERE id_desti = $idUtilisateur AND lu = 0");

$resultatNonLus = mysqli_query($connexion, "SELECT COUNT(*) AS total FROM messages WHERE id_desti = $idUtilisateur AND lu = 0");
$ligneNonLus = mysqli_fetch_assoc($resultatNonLus);
$nbMessagesNonLus = (int) ($ligneNonLus['total'] ?? 0);

// Supprimer un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['supprimer_message_id'])) {
    $idMessageSupprimer = (int) $_POST['supprimer_message_id'];
    $requete = "DELETE FROM messages
                WHERE id = $idMessageSupprimer
                AND (id_desti = $idUtilisateur OR id_expedi = $idUtilisateur)";

    if (mysqli_query($connexion, $requete) && mysqli_affected_rows($connexion) > 0) {
        $listeMessages[] = "Message supprimé";
    } else {
        $listeMessages[] = "Impossible de supprimer ce message";
        $typeMessage = 'error';
    }
}

// Envoyer une réponse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reply_to']) && empty($_POST['supprimer_message_id'])) {
    $messageReponse = trim($_POST['reply_message'] ?? '');
    $idExpediteur = (int) $_POST['reply_to'];
    
    if (empty($messageReponse)) {
        $listeMessages[] = "Écrivez un message";
        $typeMessage = 'error';
    } else {
        $idAnnonceMessage = isset($_POST['id_ads']) && (int) $_POST['id_ads'] > 0 ? (int) $_POST['id_ads'] : null;
        $messageReponse = mysqli_real_escape_string($connexion, $messageReponse);

        if ($idAnnonceMessage === null) {
            $requete = "INSERT INTO messages (id_expedi, id_desti, id_ads, content) VALUES ($idUtilisateur, $idExpediteur, NULL, '$messageReponse')";
        } else {
            $requete = "INSERT INTO messages (id_expedi, id_desti, id_ads, content) VALUES ($idUtilisateur, $idExpediteur, $idAnnonceMessage, '$messageReponse')";
        }

        if (mysqli_query($connexion, $requete)) {
            $listeMessages[] = "Message envoyé";
        } else {
            $listeMessages[] = "Erreur lors de l'envoi";
            $typeMessage = 'error';
        }
    }
}

// Messages reçus
$messagesRecus = [];
$requete = "SELECT m.*, a.title AS ad_title, u.email AS sender_email
        FROM messages m
        LEFT JOIN users u ON u.id_users = m.id_expedi
        LEFT JOIN annonce a ON a.id_ads = m.id_ads
        WHERE m.id_desti = $idUtilisateur
        ORDER BY m.created_at DESC";
$resultat = mysqli_query($connexion, $requete);
while ($ligne = mysqli_fetch_assoc($resultat)) {
    $messagesRecus[] = $ligne;
}

// Messages envoyés
$messagesEnvoyes = [];
$requete = "SELECT m.*, a.title AS ad_title, u.email AS recipient_email
        FROM messages m
        LEFT JOIN users u ON u.id_users = m.id_desti
        LEFT JOIN annonce a ON a.id_ads = m.id_ads
        WHERE m.id_expedi = $idUtilisateur
        ORDER BY m.created_at DESC";
$resultat = mysqli_query($connexion, $requete);
while ($ligne = mysqli_fetch_assoc($resultat)) {
    $messagesEnvoyes[] = $ligne;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $seoTitle = 'Messages | AutoBizz';
    $seoDescription = 'Consultez vos échanges liés aux annonces sur AutoBizz.';
    $seoRobots = 'noindex, nofollow';
    include __DIR__ . '/partials/seo-head.php';
    ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/site-header.php'; ?>

    <main class="container">
        <section class="page-intro messages-intro" aria-labelledby="messages-title">
            <p class="page-eyebrow">Votre espace d’échange</p>
            <h1 id="messages-title">Messages</h1>
            <p>Retrouvez les échanges liés à vos annonces.</p>
        </section>

        <?php if (!empty($listeMessages)): ?>
            <div class="messages-alert messages-alert--<?= htmlspecialchars($typeMessage, ENT_QUOTES, 'UTF-8'); ?>" role="<?= $typeMessage === 'error' ? 'alert' : 'status'; ?>">
                <?php foreach ($listeMessages as $messageInfo): ?>
                    <p><?= htmlspecialchars($messageInfo, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="box message-section">
            <div class="section-heading">
                <div>
                    <h2>Messages reçus</h2>
                    <p>Les réponses envoyées aux annonces que vous avez publiées.</p>
                </div>
                <span class="message-count"><?= count($messagesRecus); ?></span>
            </div>
            <?php if (empty($messagesRecus)): ?>
                <p>Vous n'avez aucun message reçu.</p>
            <?php else: ?>
                <?php foreach ($messagesRecus as $messageRecu): ?>
                    <article class="message">
                        <div class="message-topline">
                            <strong>Depuis <?= htmlspecialchars($messageRecu['sender_email'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <small><?= htmlspecialchars($messageRecu['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                        <p class="message-ad-title">Annonce : <?= htmlspecialchars($messageRecu['ad_title'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><?= nl2br(htmlspecialchars($messageRecu['content'], ENT_QUOTES, 'UTF-8')); ?></p>

                        <form method="post" class="btn-top" onsubmit="return confirm('Supprimer ce message ?');">
                            <input type="hidden" name="supprimer_message_id" value="<?= (int)($messageRecu['id'] ?? 0); ?>">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                        
                        <?php if ((int)($messageRecu['id_ads'] ?? 0) > 0): ?>
                        <div class="reply-form">
                            <form method="post">
                                <input type="hidden" name="reply_to" value="<?= (int)($messageRecu['id_expedi'] ?? 0); ?>">
                                <input type="hidden" name="id_ads" value="<?= (int)($messageRecu['id_ads'] ?? 0); ?>">
                                <label><strong>Repondre :</strong></label>
                                <textarea name="reply_message" required></textarea>
                                <button type="submit" class="btn btn-primary btn-top">Envoyer</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <p class="reply-note">Impossible de répondre - l'annonce n'existe plus.</p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="box message-section">
            <div class="section-heading">
                <div>
                    <h2>Messages envoyés</h2>
                    <p>Les messages que vous avez adressés aux vendeurs.</p>
                </div>
                <span class="message-count"><?= count($messagesEnvoyes); ?></span>
            </div>
            <?php if (empty($messagesEnvoyes)): ?>
                <p>Vous n'avez envoyé aucun message.</p>
            <?php else: ?>
                <?php foreach ($messagesEnvoyes as $messageEnvoye): ?>
                    <article class="message">
                        <div class="message-topline">
                            <strong>À <?= htmlspecialchars($messageEnvoye['recipient_email'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <small><?= htmlspecialchars($messageEnvoye['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                        <p class="message-ad-title">Annonce : <?= htmlspecialchars($messageEnvoye['ad_title'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><?= nl2br(htmlspecialchars($messageEnvoye['content'], ENT_QUOTES, 'UTF-8')); ?></p>

                        <form method="post" class="btn-top" onsubmit="return confirm('Supprimer ce message ?');">
                            <input type="hidden" name="supprimer_message_id" value="<?= (int)($messageEnvoye['id'] ?? 0); ?>">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
