<?php
session_start();
if(!isset($_SESSION['nom_utilisateur'])){
  header("Location: connexion.php");
  exit;

}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Produits</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Importation de Font Awesome (CDN) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">


  <!-- Loader -->
  <div id="loading" class="fixed inset-0 flex items-center justify-center bg-white z-50">
    <div class="text-center">
      <div class="w-12 h-12 border-4 border-green-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
      <p class="text-lg text-gray-600">Chargement... cela prend un peu de temps</p>
    </div>
  </div>

  <!-- Contenu principal -->
  <div id="main-content" class="hidden container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-center mb-6">Bienvenue <?= htmlspecialchars($_SESSION['nom_utilisateur'])?> dans notre boutique !</h1>
    <div class="flex flex-col lg:flex-row gap-6">
      
      <!-- Filtres -->
      <aside class="w-full lg:w-1/4 bg-white p-3 rounded-xl shadow-md">
        <form id="filter-form" class="space-y-6">
          <div class="bg-white shadow p-4 flex justify-between items-center sticky top-0 z-40">
            <h2 class="text-xl font-semibold">Ma Boutique</h2>
            <div class="flex gap-4">
              <a href="profil.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Profil</a>
              <!-- Bouton Panier  -->
              <div class="fixed top-4 right-4 z-50 flex items-center">
                 <!-- Panier -->
                <button class="bg-blue-600 text-white px-4 py-2 rounded-full shadow hover:bg-blue-700" onclick="window.location.href='panier.php'">
                    <span id="panier-compteur">🛒 0</span>
                </button>

                <!-- Espace entre les deux éléments -->
                <div class="relative ml-4">
                    <!-- Logout -->
                    <a href="logout.php" class="text-xl text-red-500">
                        <i class="fas fa-sign-out-alt align-middle"></i>
                        <span class="sr-only">Quitter</span>
                    </a>
                </div>
              </div>

              <a href="panier.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">Panier</a>
              </div>
          </div>

          <div>
            <h1 class="font-semibold mb-2">Filtre</h1>
            <h2 class="font-semibold mb-2">Catégorie</h2>
            <select id="filter-category" class="w-full rounded-md border-gray-300">
              <option value="">Toutes les catégories</option>
            </select>
          </div>

          <div>
            <h2 class="font-semibold mb-2">Marque</h2>
            <select id="filter-brand" class="w-full rounded-md border-gray-300">
              <option value="">Toutes les marques</option>
            </select>
          </div>

          <div>
            <h2 class="font-semibold mb-2">Prix maximum</h2>
            <input type="range" id="filter-price" min="0" max="1000" value="1000" class="w-full">
            <label id="price-range-label" class="block text-sm text-gray-600 mt-1">Prix max : 1000</label>
          </div>
        </form>
      </aside>

      <!-- Produits -->
      <section class="flex-1">
        <div id="info-container" class="mb-4"></div>
        <div id="produits-container" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
        <div id="pagination-container" class="flex justify-center mt-6 flex-wrap gap-2"></div>
      </section>
    </div>
  </div>

  <!-- Modal Détails Produit -->
  <div id="produitModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl max-w-md w-full relative">
      <button onclick="fermerModal('produitModal')" class="absolute top-2 right-3 text-gray-500 hover:text-black text-xl">&times;</button>
      <img id="produit-image" src="" alt="Image produit" class="rounded-lg mb-4 w-full h-48 object-cover">
      <h3 id="produit-title" class="text-xl font-semibold mb-2"></h3>
      <p id="produit-rating" class="text-sm mb-1"></p>
      <p id="produit-sku" class="text-sm mb-1"></p>
      <p id="produit-category" class="text-sm mb-1"></p>
      <p id="produit-price" class="text-sm mb-1"></p>
      <p id="produit-discount" class="text-sm mb-1"></p>
      <p id="produit-brand" class="text-sm mb-1"></p>
      <p id="produit-availability" class="text-sm mb-1"></p>
      <p id="produit-returnPolicy" class="text-sm mb-1"></p>
    </div>
  </div>

  <!-- Modal Avis -->
  <div id="reviewsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl max-w-md w-full relative">
      <button onclick="fermerModal('reviewsModal')" class="absolute top-2 right-3 text-gray-500 hover:text-black text-xl">&times;</button>
      <h3 class="text-xl font-semibold mb-4">Avis</h3>
      <ul id="produit-reviews" class="space-y-2"></ul>
    </div>
  </div>

  <!-- Pied de page -->
  <footer class="mt-10 py-4 text-center bg-gray-800 text-white">
    Créé par Assif Omaima
  </footer>

  <script>
    // Loader
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(() => {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('main-content').classList.remove('hidden');
      }, 4000);
      fetch('panier_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: 0, action: 'none' }) // action fictive pour récupérer le panier
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          afficherPanier(data.panier);
        }
      });

    });

    const url = 'https://api.daaif.net/products?limit=200&delay=3000';
    let productsArray = [];
    let filteredProducts = [];
    let productsParPage = 20;
    let pageActuelle = 1;

    fetch(url)
      .then(res => res.json())
      .then(data => {
        productsArray = data.products;
        filteredProducts = productsArray;
        document.getElementById('info-container').innerHTML = `
          <div class="bg-green-100 text-green-800 p-4 rounded-xl shadow-md text-center">
            <strong>Total :</strong> ${filteredProducts.length} produits |
            <strong>Produits par page :</strong> ${productsParPage}
          </div>
        `;
        afficherPage(1);
        afficherPagination(Math.ceil(filteredProducts.length / productsParPage));
        remplirFiltres();
      });

    //affichage des produits
    function afficherPage(page) {
      const start = (page - 1) * productsParPage;
      const end = page * productsParPage;
      const produitsPage = filteredProducts.slice(start, end);
      const container = document.getElementById('produits-container');
      container.innerHTML = '';
      produitsPage.forEach(produit => {
        container.innerHTML += `
          <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-col hover:shadow-lg transition p-4">
            <img src="${produit.thumbnail}" alt="${produit.title}" class="h-40 object-cover rounded-md mb-4">
            <h3 class="font-semibold text-lg">${produit.title}</h3>
            <p class="text-sm text-gray-600">${produit.description.substring(0, 80)}...</p>
            <div class="grid gap-2 mt-auto">
              <p class="mt-2 font-medium">Prix: $${produit.price} | Stock: ${produit.stock}</p>
              <div class="flex justify-between items-center mt-2">
                <button onclick="modifierPanier(${produit.id}, 'add')" class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">+</button>
                <button onclick="modifierPanier(${produit.id}, 'remove')" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700">-</button>
              </div>
              <button onclick="voirDetails('${produit.title.replace(/'/g, "\\'")}', '${produit.images[0]}', '${produit.category}', '${produit.price}','${produit.discountPercentage}', '${produit.brand}', '${produit.rating}', '${produit.returnPolicy}', '${produit.availabilityStatus}', '${produit.sku}')" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-md">Détails</button>
              <button onclick="voirAvis('${produit.title}')" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md">Avis</button>
            </div>
          </div>
        `;
      });
    }

    function afficherPagination(totalPages) {
      const container = document.getElementById('pagination-container');
      container.innerHTML = '';
      for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = 'px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md';
        btn.textContent = i;
        btn.onclick = () => {
          pageActuelle = i;
          afficherPage(i);
        };
        container.appendChild(btn);
      }
    }

    function remplirFiltres() {
      const categories = [...new Set(productsArray.map(p => p.category))];
      const brands = [...new Set(productsArray.map(p => p.brand))];
      const selectCategory = document.getElementById('filter-category');
      const selectBrand = document.getElementById('filter-brand');

      categories.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat;
        opt.textContent = cat;
        selectCategory.appendChild(opt);
      });

      brands.forEach(br => {
        const opt = document.createElement('option');
        opt.value = br;
        opt.textContent = br;
        selectBrand.appendChild(opt);
      });

      document.getElementById('filter-price').addEventListener('input', (e) => {
        document.getElementById('price-range-label').textContent = `Prix max : ${e.target.value}`;
        appliquerFiltres();
      });

      selectCategory.addEventListener('change', appliquerFiltres);
      selectBrand.addEventListener('change', appliquerFiltres);
    }

    function appliquerFiltres() {
      const category = document.getElementById('filter-category').value;
      const brand = document.getElementById('filter-brand').value;
      const price = document.getElementById('filter-price').value;

      filteredProducts = productsArray.filter(p =>
        (category === '' || p.category === category) &&
        (brand === '' || p.brand === brand) &&
        p.price <= price
      );

      afficherPage(1);
      afficherPagination(Math.ceil(filteredProducts.length / productsParPage));
    }

    function voirDetails(title, image, category, price, discount, brand, rating, returnPolicy, availability, sku) {
      document.getElementById('produit-title').textContent = title;
      document.getElementById('produit-image').src = image;
      document.getElementById('produit-category').textContent = `Catégorie : ${category}`;
      document.getElementById('produit-price').textContent = `Prix : $${price}`;
      document.getElementById('produit-discount').textContent = `Réduction : ${discount}%`;
      document.getElementById('produit-brand').textContent = `Marque : ${brand}`;
      document.getElementById('produit-rating').textContent = `Note : ${rating}`;
      document.getElementById('produit-returnPolicy').textContent = `Retour : ${returnPolicy}`;
      document.getElementById('produit-availability').textContent = `Disponibilité : ${availability}`;
      document.getElementById('produit-sku').textContent = `SKU : ${sku}`;

      document.getElementById('produitModal').classList.remove('hidden');
    }

    function voirAvis(title) {
      const produit = filteredProducts.find(p => p.title === title);
      const container = document.getElementById('produit-reviews');
      container.innerHTML = '';
      if (produit && produit.reviews && produit.reviews.length > 0) {
        produit.reviews.forEach(r => {
          const li = document.createElement('li');
          li.textContent = `${r.reviewerName} : ${r.comment} (⭐ ${r.rating})`;
          container.appendChild(li);
        });
      } else {
        const li = document.createElement('li');
        li.textContent = 'Aucun avis disponible.';
        container.appendChild(li);
      }
      document.getElementById('reviewsModal').classList.remove('hidden');
    }

    function fermerModal(id) {
      document.getElementById(id).classList.add('hidden');
    }



    //gestion de panier 
    function modifierPanier(id, action) {
      fetch('panier_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, action: action })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          //console.log("Panier mis à jour :", data.panier);
          // Optionnel : afficher la quantité en direct
          afficherPanier(data.panier);
        } else {
          alert("Erreur lors de la mise à jour du panier");
        }
      });
    }

    //afficher le total des articles 
    function afficherPanier(panier) {
      // Exemple simple : afficher dans la console
      let total = Object.values(panier).reduce((a, b) => a + b, 0);
      document.getElementById('panier-compteur').textContent = `🛒 ${total}`;
    }

  </script>
</body>
</html>
