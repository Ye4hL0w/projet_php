<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/PublicationStyle.css">
    <script src="script.js"></script>
    <title>InstaChat</title>
</head>

<body>
    <div class="container">
        <?php
        // Démarrer la session
        session_start();

        // Traitement du formulaire d'ajout de publication
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier si l'ID de l'utilisateur est défini dans la session
            if (isset($_SESSION['utilisateur_id'])) {
                // Récupérer les données du formulaire
                $titre = isset($_POST['titre']) ? $_POST['titre'] : '';
                $contenu = isset($_POST['contenu']) ? $_POST['contenu'] : '';
                $visibilite = isset($_POST['visibilite']) ? $_POST['visibilite'] : 'amis'; // Par défaut, visibilité aux amis

            // Vérifier si un fichier a été téléchargé
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // Lire le contenu du fichier et le convertir en base64
                $photo_base64 = base64_encode(file_get_contents($_FILES['photo']['tmp_name']));
            } else {
                $photo_base64 = null;
            }

            // Enregistrer la publication dans la base de données
            include('./ConnexionBD.php');
            $utilisateur_id = $_SESSION['utilisateur_id'];

            $requete = "INSERT INTO Publications (ID_Utilisateur, Titre, Contenu, Photo, Visibilite) VALUES (:utilisateur_id, :titre, :contenu, :photo_base64, :visibilite)";
            $stmt = $connexion->prepare($requete);

            // Utiliser un tableau associatif pour les valeurs des paramètres
            $stmt->execute([
                ':utilisateur_id' => $utilisateur_id,
                ':titre' => $titre,
                ':contenu' => $contenu,
                ':photo_base64' => $photo_base64,
                ':visibilite' => $visibilite
            ]);

                // Rediriger vers la page de profil après l'ajout de la publication (ajustez l'URL en fonction de votre structure)
                header("Location: ../index.php");
                exit();
            } else {
                // L'ID de l'utilisateur n'est pas défini dans la session, gérer l'erreur ou rediriger vers la page appropriée
                echo "Erreur : ID de l'utilisateur non défini.";
            }
        }
        ?>

        <!-- Formulaire d'ajout de publication -->
        <form method="post" action="" enctype="multipart/form-data">
            <label for="titre">Titre :</label>
            <input type="text" id="titre" name="titre" required><br>

            <label for="contenu">Contenu :</label>
            <textarea id="contenu" name="contenu" required></textarea><br>

            <label for="photo">Photo :</label>
            <input type="file" id="photo" name="photo"><br>

            <label for="visibilite">Visibilité :</label>
            <select id="visibilite" name="visibilite">
                <option value="amis">Amis</option>
                <option value="public">Public</option>
            </select><br>

            <input type="submit" value="Ajouter">
        </form>
        <a href="../index.php">Retour</a>
    </div>
</body>
</html>
