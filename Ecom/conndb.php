<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "ecom";

// Création de la connexion
$conn = new mysqli($host, $user, $password, $db);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données: " . $conn->connect_error);
}

// Retourner l'objet de connexion (si ce code est dans une fonction)
return $conn;
?>