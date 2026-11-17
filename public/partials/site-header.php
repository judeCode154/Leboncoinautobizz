<?php
$headerPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$headerConnected = isset($_SESSION['id_users']);
$headerUnreadMessages = isset($nbMessagesNonLus) ? (int) $nbMessagesNonLus : 0;
$headerDepositActive = in_array($headerPage, ['annonces.php', 'edit-annonce.php'], true);
?>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="brand-link" aria-label="AutoBizz, accueil">
            <img src="assets/logo.png" alt="" class="brand-logo">
        </a>
        <nav class="primary-nav" aria-label="Navigation principale">
            <a href="index.php" class="nav-link<?= $headerPage === 'index.php' ? ' is-active' : ''; ?>"<?= $headerPage === 'index.php' ? ' aria-current="page"' : ''; ?>>Annonces</a>
            <a href="<?= $headerConnected ? 'annonces.php' : '../views/auth/connexion.php'; ?>" class="nav-link nav-cta<?= $headerDepositActive ? ' is-active' : ''; ?>"<?= $headerDepositActive ? ' aria-current="page"' : ''; ?>>Déposer une annonce</a>
            <?php if ($headerConnected): ?>
                <a href="favorites.php" class="nav-link<?= $headerPage === 'favorites.php' ? ' is-active' : ''; ?>"<?= $headerPage === 'favorites.php' ? ' aria-current="page"' : ''; ?>>Mes favoris</a>
                <a href="messages.php" class="nav-link<?= $headerPage === 'messages.php' ? ' is-active' : ''; ?>"<?= $headerPage === 'messages.php' ? ' aria-current="page"' : ''; ?>>
                    Messages<?php if ($headerUnreadMessages > 0): ?><span class="badge-non-lu" aria-label="<?= $headerUnreadMessages; ?> non lu<?= $headerUnreadMessages > 1 ? 's' : ''; ?>"><?= $headerUnreadMessages; ?></span><?php endif; ?>
                </a>
                <a href="profil.php" class="nav-link<?= $headerPage === 'profil.php' ? ' is-active' : ''; ?>"<?= $headerPage === 'profil.php' ? ' aria-current="page"' : ''; ?>>Mon profil</a>
                <a href="logout.php" class="nav-link nav-logout">Se déconnecter</a>
            <?php else: ?>
                <a href="../views/auth/connexion.php" class="nav-link<?= $headerPage === 'connexion.php' ? ' is-active' : ''; ?>">Se connecter</a>
                <a href="../views/auth/inscription.php" class="nav-link nav-signup">S'inscrire</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
