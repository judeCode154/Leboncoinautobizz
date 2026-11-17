<?php
include '../config/database.php';
include '../config/init.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

$connecte = isset($_SESSION['id_users']);
$idUtilisateur = $connecte ? $_SESSION['id_users'] : null;

// Récupérer la term de recherche et le tri
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$tri = isset($_GET['tri']) ? $_GET['tri'] : 'date_desc';

if (strlen($searchTerm) < 2) {
    echo json_encode(['annonces' => []]);
    exit;
}

// Déterminer l'ordre de tri
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

// Échapper le terme de recherche pour la sécurité SQL
$searchTerm = mysqli_real_escape_string($connexion, $searchTerm);

// Rechercher dans les annonces (titre et description)
$requete = "SELECT a.*, u.email AS author_email 
            FROM annonce a 
            LEFT JOIN users u ON u.id_users = a.id_users 
            WHERE a.title LIKE '%$searchTerm%' OR a.description LIKE '%$searchTerm%'
            ORDER BY $orderBy
            LIMIT 10";

$resultat = mysqli_query($connexion, $requete);
$annonces = [];

while ($ligne = mysqli_fetch_assoc($resultat)) {
    $annonces[] = $ligne;
}

// Récupérer les favoris de l'utilisateur si connecté
$listeFavoris = [];
if ($connecte) {
    $resultat = mysqli_query($connexion, "SELECT id_ads FROM favorites WHERE id_users = $idUtilisateur");
    while ($ligne = mysqli_fetch_assoc($resultat)) {
        $listeFavoris[] = (int) $ligne['id_ads'];
    }
}

// Retourner les résultats en JSON
echo json_encode([
    'annonces' => $annonces,
    'favoris' => $listeFavoris,
    'connecte' => $connecte
]);
?>
