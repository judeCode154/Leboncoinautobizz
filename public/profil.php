<?php
include '../config/database.php';
include '../config/init.php';

if (!isset($_SESSION['id_users'])) {
    header('Location: index.php');
    exit;
}

$idUtilisateur = $_SESSION['id_users'];
$listeMessages = [];
$typeMessage = 'error';
$nbMessagesNonLus = 0;

$resultatNonLus = mysqli_query($connexion, "SELECT COUNT(*) AS total FROM messages WHERE id_desti = $idUtilisateur AND lu = 0");
$ligneNonLus = mysqli_fetch_assoc($resultatNonLus);
$nbMessagesNonLus = (int) ($ligneNonLus['total'] ?? 0);

$resultatUtilisateur = mysqli_query($connexion, "SELECT * FROM users WHERE id_users = $idUtilisateur LIMIT 1");
$utilisateur = mysqli_fetch_assoc($resultatUtilisateur);

if (!$utilisateur) {
    header('Location: logout.php');
    exit;
}

$email = $utilisateur['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $nouveauMdp = trim($_POST['nouveau_mdp'] ?? '');
    $confirmerMdp = trim($_POST['confirmer_mdp'] ?? '');

    if ($email === '') {
        $listeMessages[] = 'Email obligatoire.';
    }

    if ($nouveauMdp !== '' || $confirmerMdp !== '') {
        if ($nouveauMdp === '' || $confirmerMdp === '') {
            $listeMessages[] = 'Remplissez les deux champs mot de passe.';
        } elseif ($nouveauMdp !== $confirmerMdp) {
            $listeMessages[] = 'Les mots de passe ne correspondent pas.';
        }
    }

    if (empty($listeMessages)) {
        $emailSecurise = mysqli_real_escape_string($connexion, $email);

        $resultatEmail = mysqli_query($connexion, "SELECT id_users FROM users WHERE email = '$emailSecurise' AND id_users != $idUtilisateur LIMIT 1");
        if (mysqli_fetch_assoc($resultatEmail)) {
            $listeMessages[] = 'Cet email est déjà utilisé.';
        } else {
            $requete = "UPDATE users SET email = '$emailSecurise'";

            if ($nouveauMdp !== '') {
                $mdpHash = password_hash($nouveauMdp, PASSWORD_DEFAULT);
                $mdpSecurise = mysqli_real_escape_string($connexion, $mdpHash);
                $requete .= ", mdp = '$mdpSecurise'";
            }

            $requete .= " WHERE id_users = $idUtilisateur";

            if (mysqli_query($connexion, $requete)) {
                $_SESSION['email'] = $email;
                $listeMessages[] = 'Profil mis à jour avec succès.';
                $typeMessage = 'success';
            } else {
                $listeMessages[] = 'Erreur lors de la mise à jour du profil.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $seoTitle = 'Mon profil | AutoBizz';
    $seoDescription = 'Gérez les paramètres de votre compte AutoBizz.';
    $seoRobots = 'noindex, nofollow';
    include __DIR__ . '/partials/seo-head.php';
    ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/site-header.php'; ?>

    <main class="container container-narrow">
        <?php if (!empty($listeMessages)): ?>
            <div class="messages messages--<?= htmlspecialchars($typeMessage, ENT_QUOTES, 'UTF-8'); ?>" role="<?= $typeMessage === 'error' ? 'alert' : 'status'; ?>">
                <?php foreach ($listeMessages as $message): ?>
                    <div><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="box profile-card" aria-labelledby="profile-title">
            <div class="profile-heading">
                <div>
                    <p class="profile-eyebrow">Votre espace personnel</p>
                    <h1 id="profile-title">Paramètres du compte</h1>
                    <p class="profile-intro">Gérez votre adresse e-mail et la sécurité de votre compte.</p>
                </div>
                <span class="profile-avatar" aria-hidden="true">@</span>
            </div>

            <div class="profile-summary">
                <div>
                    <span class="profile-summary-label">Adresse actuelle</span>
                    <strong><?= htmlspecialchars($utilisateur['email'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <span class="profile-account-id">Compte n° <?= (int) $idUtilisateur; ?></span>
            </div>

            <form method="post" class="profile-form">
                <div class="profile-section-heading">
                    <h2>Informations personnelles</h2>
                    <p>Les champs marqués sont nécessaires pour enregistrer vos changements.</p>
                </div>
                <div class="profile-field">
                    <label for="email">Nouvelle adresse e-mail</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="email" required>
                </div>

                <div class="profile-section-heading profile-password-heading">
                    <h2>Sécurité</h2>
                    <p>Laissez les champs vides si vous ne souhaitez pas changer votre mot de passe.</p>
                </div>
                <div class="profile-field-grid">
                    <div class="profile-field">
                        <label for="nouveau_mdp">Nouveau mot de passe</label>
                        <input type="password" id="nouveau_mdp" name="nouveau_mdp" autocomplete="new-password">
                    </div>
                    <div class="profile-field">
                        <label for="confirmer_mdp">Confirmer le mot de passe</label>
                        <input type="password" id="confirmer_mdp" name="confirmer_mdp" autocomplete="new-password">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
