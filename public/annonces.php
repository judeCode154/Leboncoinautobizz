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

$resultatNonLus = mysqli_query($connexion, "SELECT COUNT(*) AS total FROM messages WHERE id_desti = $idUtilisateur AND lu = 0");
$ligneNonLus = mysqli_fetch_assoc($resultatNonLus);
$nbMessagesNonLus = (int) ($ligneNonLus['total'] ?? 0);

// Supprimer une annonce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['supprimer_id'])) {
    $idAnnonce = (int) $_POST['supprimer_id'];
    // Vérifier que c'est bien son annonce
    $requete = "SELECT image FROM annonce WHERE id_ads = $idAnnonce AND id_users = $idUtilisateur";
    $resultat = mysqli_query($connexion, $requete);
    $annonce = mysqli_fetch_assoc($resultat);
    
    if ($annonce) {
        // Supprimer l'image
        if (!empty($annonce['image']) && file_exists(__DIR__ . '/' . $annonce['image'])) {
            unlink(__DIR__ . '/' . $annonce['image']);
        }
        // Supprimer l'annonce
        $requete = "DELETE FROM annonce WHERE id_ads = $idAnnonce AND id_users = $idUtilisateur";
        if (mysqli_query($connexion, $requete)) {
            $listeMessages[] = 'Annonce supprimée';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['supprimer_id'])) {
    $titre = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = (float) ($_POST['price'] ?? 0);
    $cheminImage = null;

    if ($titre === '' || $description === '') {
        $listeMessages[] = 'Le titre et la description sont obligatoires.';
        $typeMessage = 'error';
    }

    if ($prix < 0) {
        $listeMessages[] = 'Le prix ne peut pas être négatif.';
        $typeMessage = 'error';
    }

    // Version simple: on vérifie l'extension du fichier
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $nomOriginal = $_FILES['image']['name'];
        $cheminTemporaire = $_FILES['image']['tmp_name'];
        $extension = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));

        $formatsAutorises = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extension, $formatsAutorises, true)) {
            $listeMessages[] = 'Format d\'image non supporté. Utilisez JPG, PNG ou GIF.';
            $typeMessage = 'error';
        } else {
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }

            $dossierUploads = __DIR__ . '/uploads';
            if (!is_dir($dossierUploads)) {
                mkdir($dossierUploads, 0755, true);
            }

            $nomFichier = time() . '_' . rand(1000, 9999) . '.' . $extension;
            $destination = $dossierUploads . '/' . $nomFichier;

            if (move_uploaded_file($cheminTemporaire, $destination)) {
                $cheminImage = 'uploads/' . $nomFichier;
            } else {
                $listeMessages[] = 'Impossible de déplacer l\'image envoyée.';
                $typeMessage = 'error';
            }
        }
    }

    if (empty($listeMessages)) {
        $titreSecurise = mysqli_real_escape_string($connexion, $titre);
        $descriptionSecurisee = mysqli_real_escape_string($connexion, $description);

        if ($cheminImage === null) {
            $valeurImage = 'NULL';
        } else {
            $imageSecurisee = mysqli_real_escape_string($connexion, $cheminImage);
            $valeurImage = "'$imageSecurisee'";
        }

        $requete = "INSERT INTO annonce (title, price, description, image, id_users) VALUES ('$titreSecurise', $prix, '$descriptionSecurisee', $valeurImage, $idUtilisateur)";

        if (mysqli_query($connexion, $requete)) {
            $listeMessages[] = 'Annonce déposée avec succès.';
            $titre = '';
            $description = '';
        } else {
            $listeMessages[] = 'Erreur lors de l\'enregistrement de l\'annonce.';
            $typeMessage = 'error';
        }
    }
}

// Récupérer les annonces publiées par l'utilisateur
$annoncesUtilisateur = [];
$resultat = mysqli_query($connexion, "SELECT * FROM annonce WHERE id_users = $idUtilisateur ORDER BY created_at DESC");
while ($ligne = mysqli_fetch_assoc($resultat)) {
    $annoncesUtilisateur[] = $ligne;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $seoTitle = 'Déposer une annonce | AutoBizz';
    $seoDescription = 'Publiez une annonce automobile sur AutoBizz.';
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

        <section class="box form-card" aria-labelledby="create-ad-title">
            <div class="form-page-heading">
                <p class="page-eyebrow">Vendez simplement sur AutoBizz</p>
                <h1 id="create-ad-title">Déposer une annonce</h1>
                <p>Présentez clairement votre article pour aider les acheteurs à se décider.</p>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-field">
                    <label for="title">Titre de l’annonce</label>
                    <input type="text" id="title" name="title" maxlength="120" placeholder="Ex. Vélo de ville en très bon état" value="<?= isset($titre) ? htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                </div>

                <div class="form-field">
                    <label for="price">Prix (€)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" placeholder="0,00" value="<?= isset($prix) ? $prix : ''; ?>" required>
                </div>

                <div class="form-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="textarea-large" placeholder="Décrivez l’état, les caractéristiques et les informations utiles..." required><?= isset($description) ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                </div>

                <div class="form-field">
                    <label for="image">Photo <span class="optional-label">— facultative</span></label>
                    <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/gif" aria-describedby="image-help">
                    <small id="image-help" class="field-help">Formats acceptés : JPG, PNG ou GIF.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Publier l’annonce</button>
                </div>
            </form>
        </section>

        <?php if (!empty($annoncesUtilisateur)): ?>
            <div class="box user-ads">
                <div class="listing-heading">
                    <h2>Mes annonces</h2>
                    <span><?= count($annoncesUtilisateur); ?> annonce<?= count($annoncesUtilisateur) > 1 ? 's' : ''; ?></span>
                </div>
                <?php foreach ($annoncesUtilisateur as $annonce): ?>
                    <div class="ad">
                        <strong><?= htmlspecialchars($annonce['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <p><?= nl2br(htmlspecialchars($annonce['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <?php if (!empty($annonce['image']) && file_exists(__DIR__ . '/' . $annonce['image'])): ?>
                            <img class="ad-image-user" src="<?= htmlspecialchars($annonce['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($annonce['title'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <small>Publié le <?= htmlspecialchars($annonce['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                        <div class="ad-actions">
                            <a href="edit-annonce.php?id=<?= $annonce['id_ads']; ?>" class="btn btn-success">Modifier</a>
                            <form method="post" class="inline-form" onsubmit="return confirm('Êtes-vous sûr ?');">
                                <input type="hidden" name="supprimer_id" value="<?= $annonce['id_ads']; ?>">
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
