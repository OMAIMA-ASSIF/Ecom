<?php
session_start();
if (!isset($_SESSION['nom_utilisateur'])) {
  header("Location: connexion.php");
  exit;
}

//pour reinitialiser le panier ^^
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_panier'])) {
  $_SESSION['panier'] = []; // Réinitialiser le panier
  header("Location: panier.php"); // Rediriger pour éviter la re-soumission du formulaire
  exit;
}



if (!isset($_SESSION['panier'])) {
  $_SESSION['panier'] = [];

  include 'conndb.php';
  $email_user = $_SESSION["email"];
  $panierVide = json_encode([]); // "{}"

  $stmt  = mysqli_prepare($conn, "UPDATE users SET panier = ? WHERE email = ?");
  mysqli_stmt_bind_param($stmt, "ss", $panierVide, $email_user );
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header("Location: panier.php");
  exit;

}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Panier</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold mb-6">🛒 Mon Panier</h1>
    <a href="index.php" class="text-blue-600 underline mb-4 inline-block">← Retour à la boutique</a>

    <?php if (empty($_SESSION['panier'])): ?>
      <p class="text-gray-600">Votre panier est vide.</p>
    <?php else: ?>
      <div class="bg-white rounded-lg shadow-md p-6 space-y-4">
        <?php
        // Appel API pour récupérer les infos produits
        $json = file_get_contents('https://api.daaif.net/products?limit=200');
        $data = json_decode($json, true);
        $produits = $data['products'];
        $total = 0;

        foreach ($_SESSION['panier'] as $id => $quantite):
          $produit = array_filter($produits, fn($p) => $p['id'] == $id);
          $produit = reset($produit);
          if (!$produit) continue;
          $prix = $produit['price'];
          $titre = $produit['title'];
          $sousTotal = $prix * $quantite;
          $total += $sousTotal;
        ?>
          <div class="flex justify-between border-b pb-2">
            <div>
              <h2 class="font-semibold"><?= htmlspecialchars($titre) ?></h2>
              <p>Quantité : <?= $quantite ?> × $<?= $prix ?></p>
            </div>
            <div class="text-right">
              <p class="font-semibold">$<?= number_format($sousTotal, 2) ?></p>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="text-right font-bold text-xl">
          Total : $<?= number_format($total, 2) ?>
        </div>
        <form method="post">
          <button type="submit" name="reset_panier" class="mt-4 bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded">
          <svg class="w-[33px] h-[33px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"/>
          </svg>
          </button>
        </form>

      </div>
    <?php endif; ?>
  </div>
  
</body>
</html>
<?php
  echo '<pre>';
  echo "Affichage de SESSION pour le champ panier (panier est un dictionnaire de cle: id de produit, valeur: la quantite de produit)  ";
  echo '<pre>';
  print_r($_SESSION['panier']);
  echo '</pre>';
  ?>