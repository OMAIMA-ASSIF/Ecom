<?php
session_start();
include 'conndb.php'; // Connexion PDO ou MySQLi avec $conn

if (!isset($_SESSION['nom_utilisateur'])) {
    header("Location: connexion.php");
    exit;
}

$error = '';
$success = '';

$nom = $_SESSION['nom'];
$prenom = $_SESSION['nom_utilisateur'];
$emailSession = $_SESSION['email'];
$email = $emailSession;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motdepasse_actuel = $_POST['motdepasse_actuel'] ?? '';
    $nouveau_mdp = $_POST['password'] ?? '';
    $confirmation = $_POST['confirm_password'] ?? '';

    // Vérifier le mot de passe actuel
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $emailSession);
    $stmt->execute();
    $stmt->bind_result($motdepasse_en_base);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($motdepasse_actuel, $motdepasse_en_base)) {
        $error = "Mot de passe actuel incorrect.";
    } elseif ($email !== $emailSession) {
        // Vérifier si le nouvel email est déjà utilisé
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error = "Cette adresse email est déjà utilisée.";
        }
    }

    // Vérifier la correspondance des nouveaux mots de passe
    if (empty($error) && !empty($nouveau_mdp) && $nouveau_mdp !== $confirmation) {
        $error = "Les mots de passe ne correspondent pas.";
    }

    // Mise à jour des données
    if (empty($error)) {
        if (!empty($nouveau_mdp)) {
            $mdp_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, password = ? WHERE email = ?");
            $stmt->bind_param("sssss", $nom, $prenom, $email, $mdp_hash, $emailSession);
        } else {
            $stmt = $conn->prepare("UPDATE users SET nom = ?, prenom = ?, email = ? WHERE email = ?");
            $stmt->bind_param("ssss", $nom, $prenom, $email, $emailSession);
        }

        if ($stmt->execute()) {
            $_SESSION['nom_utilisateur'] = $prenom;
            $_SESSION['email'] = $email;
            $_SESSION['nom'] = $nom;
            $success = "Profil mis à jour avec succès.";
            $emailSession = $email;
        } else {
            $error = "Erreur lors de la mise à jour : " . $stmt->error;
        }

        $stmt->close();
    }
}

// Debug : pour vérifier les données envoyées
//var_dump($_POST); // Décommentez cette ligne si vous voulez afficher les données POST.
//print_r($_SESSION);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<div class="absolute top-4 left-4">
        <a href="index.php">
            <i class="fas fa-arrow-left text-2xl text-blue-600"></i>  <!-- Icône de flèche -->
        </a>
</div>
<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <h1 class="text-2xl font-bold text-center mb-6">Modifier le profil</h1>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form class="space-y-4" method="post">
        <div>
            <label class="block text-sm font-medium mb-1">Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" class="w-full px-4 py-2 border rounded" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>" class="w-full px-4 py-2 border rounded" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full px-4 py-2 border rounded" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Mot de passe actuel</label>
            <input type="password" name="motdepasse_actuel" class="w-full px-4 py-2 border rounded">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Nouveau mot de passe (laisser vide si inchangé)</label>
            <input type="password" name="password" class="w-full px-4 py-2 border rounded">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Confirmer le mot de passe</label>
            <input type="password" name="confirm_password" class="w-full px-4 py-2 border rounded">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">
            Enregistrer les modifications
        </button>
    </form>
</div>
</body>
</html>
