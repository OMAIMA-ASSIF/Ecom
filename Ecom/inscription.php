<?php
include 'conndb.php';

$error = '';
$error1 = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $emailCount = $stmt->num_rows;
    $stmt->close();

    if ($emailCount > 0) {
        $error1 = "L'email est déjà utilisé.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $panierVide = json_encode([]);
        $stmt = $conn->prepare("INSERT INTO users (nom, prenom, email, password,panier) VALUES (?, ?, ?, ?,?)");
        $stmt->bind_param("sssss", $nom, $prenom, $email, $hashedPassword, $panierVide);
        if ($stmt->execute()) {
            $_SESSION['panier'] = [];  // Initialisation panier pour ce nouvel utilisateur
            header('Location: inscription.php?success=1');
            exit;
        } else {
            $error = "Erreur lors de l'inscription.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Créer un compte</h1>

        <!--messages d'erreur -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error1)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($error1) ?>
            </div>
        <?php endif; ?>

        <!--msg de succees-->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                Inscription réussie ! Vous pouvez maintenant vous <a href="connexion.php" class="underline font-semibold">connecter</a>.
            </div>
        <?php endif; ?>


        <form class="space-y-4" method="post" action="inscription.php">
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <input type="password" id="password" name="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                S'inscrire
            </button>
        </form>

        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">Déjà un compte ?
                <a href="connexion.php" class="text-blue-600 hover:underline">Se connecter</a>
            </p>
        </div>
    </div>
</body>
</html>
