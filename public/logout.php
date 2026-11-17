<?php
include '../config/database.php';

// Détruire la session et rediriger vers la page d'accueil
session_unset();
session_destroy();
header('Location: index.php');
exit;
