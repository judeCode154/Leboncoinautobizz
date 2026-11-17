<?php
// Connexion à la base de données
$id = mysqli_connect('127.0.0.1', "root", "root", "leboncoin_bts" , 8889);

$message = '';
$messageType = '';

if(isset($_POST['inscription'])) {
    $email = mysqli_real_escape_string($id, $_POST['email']);
    $mdp = password_hash($_POST["mdp"], PASSWORD_DEFAULT);
    // Insertion des données dans la table users
   
    $requete = "SELECT * FROM users WHERE email='$email'";
    $resultat = mysqli_query($id, $requete);
    if(mysqli_num_rows($resultat) > 0) { //mysqli_num_rows compte le nombre de ligne renvoyée par la requete
        $message = "Cet email est déjà utilisé.";
        $messageType = 'error';
    } else {
    $requete = "INSERT INTO users (email, mdp) VALUES ('$email', '$mdp')";
    if (mysqli_query($id, $requete)) {
        $message = "Inscription réussie. Vous pouvez maintenant vous connecter.";
        $messageType = 'success';
        header("refresh:2;url=connexion.php");
    } else {
        $message = "Erreur pendant l'inscription.";
        $messageType = 'error';
    }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="design.css?v=3">
    <?php
    $seoTitle = 'Créer un compte | AutoBizz';
    $seoDescription = 'Créez un compte AutoBizz pour consulter et publier des annonces.';
    $seoRobots = 'noindex, nofollow';
    $seoFaviconPath = '../../public/assets/favicon.png?v=1';
    include __DIR__ . '/../../public/partials/seo-head.php';
    ?>
</head>
<body>
    <div class="hero">
        <nav>
            <a href="../../public/index.php" class="brand-link"><img src="../../public/assets/logo.png" alt="AutoBizz - Accueil" class="brand-logo-auth"></a>
            <a href="connexion.php" class="login-btn">Se connecter</a>
        </nav>

        <div class="container signup-container">
            <section class="signup-welcome" aria-labelledby="signup-welcome-title">
                <span class="signup-kicker">La bonne occasion est ici</span>
                <h1 id="signup-welcome-title">Faites de la place aux bonnes affaires.</h1>
                <p>Créez votre compte pour acheter et vendre simplement, près de chez vous.</p>
                <ul class="signup-benefits">
                    <li>Découvrez des annonces autour de vous</li>
                    <li>Échangez directement avec les vendeurs</li>
                    <li>Retrouvez vos annonces au même endroit</li>
                </ul>
            </section>

            <div class="card auth-card signup-card">
                <span class="auth-eyebrow">Bienvenue sur AutoBizz</span>
                <h2>Créer un compte</h2>
                <p class="auth-intro">Quelques informations suffisent pour commencer.</p>

                <?php if ($message !== ''): ?>
                    <p class="auth-message auth-message--<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>" role="<?= $messageType === 'error' ? 'alert' : 'status'; ?>">
                        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>

                <form action="" method="post" class="auth-form">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" placeholder="exemple@email.com" autocomplete="email" required>

                    <label for="mdp">Mot de passe</label>
                    <div class="password-field">
                        <input type="password" id="mdp" name="mdp" placeholder="Choisissez un mot de passe" autocomplete="new-password" aria-describedby="password-hint" required>
                        <button type="button" class="password-toggle" aria-label="Afficher le mot de passe" aria-controls="mdp" aria-pressed="false">Afficher</button>
                    </div>
                    <p id="password-hint" class="password-hint">Utilisez un mot de passe que vous n’employez pas ailleurs.</p>

                    <button type="submit" name="inscription" class="btn">Créer mon compte</button>
                </form>

                <p class="auth-switch">Déjà inscrit ? <a href="connexion.php" class="auth-link">Se connecter</a></p>
            </div>
        </div>
    </div>
    <script>
        const passwordInput = document.getElementById('mdp');
        const passwordToggle = document.querySelector('.password-toggle');

        passwordToggle.addEventListener('click', () => {
            const isVisible = passwordInput.type === 'text';
            passwordInput.type = isVisible ? 'password' : 'text';
            passwordToggle.textContent = isVisible ? 'Afficher' : 'Masquer';
            passwordToggle.setAttribute('aria-label', isVisible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
            passwordToggle.setAttribute('aria-pressed', String(!isVisible));
        });
    </script>
</body>
</html>