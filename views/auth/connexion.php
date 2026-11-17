<?php
session_start();
// Connexion à la base de données
$id = mysqli_connect('127.0.0.1', "root", "root", "leboncoin_bts", 8889);

$erreur = '';
$email = '';

if(isset($_POST['connexion'])) {
    $email = trim($_POST['email'] ?? '');
    $emailSecurise = mysqli_real_escape_string($id, $email);
    $mdp = $_POST['mdp'] ?? '';

    $requete = "SELECT * FROM users WHERE email='$emailSecurise'";
    $resultat = mysqli_query($id, $requete);
    $ligne = mysqli_fetch_assoc($resultat);

    if ($ligne && password_verify($mdp, $ligne["mdp"])) {
        $_SESSION['email'] = $ligne['email'];
        $_SESSION['id_users'] = $ligne['id_users'];
        header("Location: ../../public/index.php");
        exit();
    } else {
        $erreur = "Login ou mot de passe incorrect.";
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
    $seoTitle = 'Connexion | AutoBizz';
    $seoDescription = 'Connectez-vous à votre compte AutoBizz.';
    $seoRobots = 'noindex, nofollow';
    $seoFaviconPath = '../../public/assets/favicon.png?v=1';
    include __DIR__ . '/../../public/partials/seo-head.php';
    ?>
</head>
<body>
    <div class="hero">
        <nav>
            <a href="../../public/index.php" class="brand-link"><img src="../../public/assets/logo.png" alt="AutoBizz - Accueil" class="brand-logo-auth"></a>
            <a href="inscription.php" class="login-btn">S'inscrire</a>
        </nav>

        <div class="container signup-container login-container">
            <section class="signup-welcome login-welcome" aria-labelledby="login-welcome-title">
                <span class="signup-kicker">Votre espace AutoBizz</span>
                <h1 id="login-welcome-title">Heureux de vous retrouver.</h1>
                <p>Connectez-vous pour reprendre vos échanges et suivre vos annonces en toute simplicité.</p>
                <ul class="signup-benefits">
                    <li>Retrouvez vos annonces et favoris</li>
                    <li>Reprenez vos conversations avec les vendeurs</li>
                    <li>Gérez votre compte en un seul endroit</li>
                </ul>
            </section>

            <div class="card auth-card signup-card login-card">
                <span class="auth-eyebrow">Bon retour parmi nous</span>
                <h1>Connexion</h1>
                <p class="auth-intro">Accédez à votre compte AutoBizz.</p>
                <form action="" method="post" class="auth-form">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" placeholder="exemple@email.com" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="email" required>

                    <label for="mdp">Mot de passe</label>
                    <div class="password-field">
                        <input type="password" id="mdp" name="mdp" placeholder="Votre mot de passe" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" aria-label="Afficher le mot de passe" aria-controls="mdp" aria-pressed="false">Afficher</button>
                    </div>

                    <?php if ($erreur !== ''): ?>
                        <p class="auth-message auth-message--error" role="alert"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <button type="submit" name="connexion" class="btn">Se connecter</button>
                </form>

                <p class="auth-switch">Pas encore de compte ? <a href="inscription.php" class="auth-link">Créer un compte</a></p>
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