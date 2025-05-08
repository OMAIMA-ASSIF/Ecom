<?php
//On va utiliser AJAX dans ce fichier pour mettre a jour $_SESSION['panier'] ,gérer dynamiquement le panier (ajout/retrait d’articles)
//sans recharger la page.
include 'conndb.php';
session_start();

if(!isset($_SESSION['nom_utilisateur'])){
    header("Location: connexion.php");
    exit;
  
  }

  function getProductById($id) {
    $apiUrl = 'https://api.daaif.net/products?limit=200';
    $json = file_get_contents($apiUrl);
    $data = json_decode($json, true);

    foreach ($data['products'] as $product) {
        if ($product['id'] == $id) {
            return $product;
        }
    }
    return null;
  }


if (!isset($_SESSION['panier'])) {
  $_SESSION['panier'] = [];
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['action'])) {
  $id = intval($data['id']);
  $action = $data['action'];

  if ($action === 'add') {
    $product = getProductById($id);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produit introuvable']);
        exit;
    }

    $quantiteDansPanier = $_SESSION['panier'][$id] ?? 0;
    if ($quantiteDansPanier < $product['stock']) {
        $_SESSION['panier'][$id] = $quantiteDansPanier + 1;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Stock insuffisant pour ce produit',
            'panier' => $_SESSION['panier']
        ]);
        exit;
    }
}


    echo json_encode(['success' => true, 'panier' => $_SESSION['panier']]);
    $email = $_SESSION['email'];
    $panierJson = json_encode($_SESSION['panier']);
    $stmt = $conn->prepare("UPDATE users SET panier = ? WHERE email = ?");
    $stmt->bind_param("ss", $panierJson, $email);
    $stmt->execute();
    $stmt->close();

  exit;
}

echo json_encode(['success' => false]);
exit;

?>