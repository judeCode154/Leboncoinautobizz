<?php
include '../config/database.php';
include '../config/init.php';

if (!isset($_SESSION['id_users'])) {
    header('Location: index.php');
    exit;
}

$idUtilisateur = $_SESSION['id_users'];
$listeMessages = [];
$idAnnonce = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nbMessagesNonLus = 0;

$resultatNonLus = mysqli_query($connexion, "SELECT COUNT(*) AS total FROM messages WHERE id_desti = $idUtilisateur AND lu = 0");
$ligneNonLus = mysqli_fetch_assoc($resultatNonLus);
$nbMessagesNonLus = (int) ($ligneNonLus['total'] ?? 0);

// Vérifier que c'est bien son annonce
$requete = "SELECT * FROM annonce WHERE id_ads = $idAnnonce AND id_users = $idUtilisateur";
$resultat = mysqli_query($connexion, $requete);
$annonce = mysqli_fetch_assoc($resultat);

if (!$annonce) {
    header('Location: annonces.php');
    exit;
}

// Initialiser les variables avec les données actuelles
$titre = $annonce['title'];
$description = $annonce['description'];
$prix = $annonce['price'];
$ancienneImage = $annonce['image'];

// Traiter la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = (float) ($_POST['price'] ?? 0);
    $cheminImage = $ancienneImage; // Conserver l'ancienne image par défaut

    if ($titre === '' || $description === '') {
        $listeMessages[] = 'Le titre et la description sont obligatoires.';
    }

    if ($prix < 0) {
        $listeMessages[] = 'Le prix ne peut pas être négatif.';
    }

    // Traitement de la nouvelle image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $cheminTemporaire = $_FILES['image']['tmp_name'];

        // Vérifier le format
        if (function_exists('mime_content_type')) {
            $typeMime = mime_content_type($cheminTemporaire);
        } elseif (function_exists('finfo_open')) {
            $infoFichier = finfo_open(FILEINFO_MIME_TYPE);
            $typeMime = finfo_file($infoFichier, $cheminTemporaire);
            finfo_close($infoFichier);
        } else {
            [$width, $height, $type] = @getimagesize($cheminTemporaire) ?: [null, null, null];
            $typeMime = $type ? image_type_to_mime_type($type) : null;
        }

        $formatsAutorises = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
        ];

        if (!$typeMime || !array_key_exists($typeMime, $formatsAutorises)) {
            $listeMessages[] = 'Format d\'image non supporté.';
        } else {
            $extension = $formatsAutorises[$typeMime];
            $dossierUploads = __DIR__ . '/uploads';
            if (!is_dir($dossierUploads)) {
                mkdir($dossierUploads, 0755, true);
            }

            $nomFichier = bin2hex(random_bytes(8)) . '.' . $extension;
            $destination = $dossierUploads . '/' . $nomFichier;

            if (move_uploaded_file($cheminTemporaire, $destination)) {
                // Supprimer l'ancienne image
                if (!empty($ancienneImage) && file_exists(__DIR__ . '/' . $ancienneImage)) {
                    unlink(__DIR__ . '/' . $ancienneImage);
                }
                $cheminImage = 'uploads/' . $nomFichier;
            } else {
                $listeMessages[] = 'Erreur lors du téléchargement.';
            }
        }
    }

    // Mettre à jour l'annonce
    if (empty($listeMessages)) {
        $titreSecurise = mysqli_real_escape_string($connexion, $titre);
        $descriptionSecurisee = mysqli_real_escape_string($connexion, $description);
        if ($cheminImage === null || $cheminImage === '') {
            $requete = "UPDATE annonce SET title = '$titreSecurise', price = $prix, description = '$descriptionSecurisee', image = NULL WHERE id_ads = $idAnnonce AND id_users = $idUtilisateur";
        } else {
            $imageSecurisee = mysqli_real_escape_string($connexion, $cheminImage);
            $requete = "UPDATE annonce SET title = '$titreSecurise', price = $prix, description = '$descriptionSecurisee', image = '$imageSecurisee' WHERE id_ads = $idAnnonce AND id_users = $idUtilisateur";
        }

        if (mysqli_query($connexion, $requete)) {
            $listeMessages[] = 'Annonce modifiée avec succès.';
            $ancienneImage = $cheminImage;
        } else {
            $listeMessages[] = 'Erreur lors de la mise à jour.';
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
    $seoTitle = 'Modifier une annonce | AutoBizz';
    $seoDescription = 'Modifiez les informations de votre annonce AutoBizz.';
    $seoRobots = 'noindex, nofollow';
    include __DIR__ . '/partials/seo-head.php';
    ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/site-header.php'; ?>

    <main class="container container-narrow">
        <?php if (!empty($listeMessages)): ?>
            <div class="messages">
                <?php foreach ($listeMessages as $message): ?>
                    <div><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="box form-card" aria-labelledby="edit-ad-title">
            <div class="form-page-heading">
                <p class="page-eyebrow">Mise à jour de votre publication</p>
                <h1 id="edit-ad-title">Modifier l’annonce</h1>
                <p>Vérifiez les informations avant d’enregistrer vos changements.</p>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <label for="title">Titre</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'); ?>" required>

                <label for="price">Prix (€)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($prix, ENT_QUOTES, 'UTF-8'); ?>" required>

                <label for="description">Description</label>
                <textarea id="description" name="description" class="textarea-large" required><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>

                <?php if (!empty($ancienneImage) && file_exists(__DIR__ . '/' . $ancienneImage)): ?>
                    <div class="current-image">
                        <p><strong>Image actuelle :</strong></p>
                        <img src="<?= htmlspecialchars($ancienneImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                <?php endif; ?>

                <div class="form-field">
                    <label for="image">Nouvelle image <span class="optional-label">— facultative</span></label>
                    <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/gif">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="annonces.php" class="btn btn-dark no-decoration">Annuler</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
