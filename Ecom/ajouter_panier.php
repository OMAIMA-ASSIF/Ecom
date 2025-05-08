<?php
include 'conndb.php';
session_start();
if(!isset($_SESSION['nom_utilisateur'])){
    header("Location: connexion.php");
    exit;
  
  }


if (!isset($_SESSION['panier'])) {
  $_SESSION['panier'] = [];
}

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  if (isset($_SESSION['panier'][$id])) {
    $_SESSION['panier'][$id]++;
  } else {
    $_SESSION['panier'][$id] = 1;
  }
}

$email = $_SESSION['email'];
$panierJson = json_encode($_SESSION['panier']);
$stmt = $conn->prepare("UPDATE users SET panier = ? WHERE email = ?");
$stmt->bind_param("ss", $panierJson, $email);
$stmt->execute();
$stmt->close();


header("Location: index.php");
exit;
