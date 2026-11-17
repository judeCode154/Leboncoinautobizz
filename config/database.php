<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$db = 'leboncoin_bts';
$port = 8889;

$conn = mysqli_connect($host, $user, $pass, $db, $port);
$connexion = $conn;

if (!$conn) {
    die('Erreur de connexion a la base de donnees : ' . mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
