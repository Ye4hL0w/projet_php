<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>InstaChat</title>
    <link rel="stylesheet" href="../assets/css/RegisterStyle.css">
    <script src="../script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Inscription</h1>
        <?php
        session_start();
        
        include('./ConnexionBD.php');

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $nom = isset($_POST['nom']) ? $_POST['nom'] : '';
            $motDePasse = isset($_POST['motDePasse']) ? $_POST['motDePasse'] : '';
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $prenom = isset($_POST['prenom']) ? $_POST['prenom'] : '';
            $dateNaissance = isset($_POST['dateNaissance']) ? $_POST['dateNaissance'] : '';
            $adresse = isset($_POST['adresse']) ? $_POST['adresse'] : '';
            $telephonePortable = isset($_POST['telephonePortable']) ? $_POST['telephonePortable'] : '';
            $photo = isset($_POST['photo']) ? $_POST['photo'] : '';
            $emailSecours = isset($_POST['emailSecours']) ? $_POST['emailSecours'] : '';

            // vérif si l'adresse e-mail est valide
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<p>Adresse e-mail non valide. Veuillez fournir une adresse e-mail correcte.</p>";
            } else {
                // Hasher le mot de passe avant de le stocker en base de données
                $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);

                // Stocker les données dans des variables de session
                $_SESSION['nom'] = $nom;
                $_SESSION['email'] = $email;

                // ititialiser la variable qui va contenir les données de l'image
                $photoData = null;

                // vérifier si un fichier a été téléchargé et traiter le fichier
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
                    // Lire le contenu binaire du fichier
                    $tmpName = $_FILES['photo']['tmp_name'];
                    $photoData = base64_encode(file_get_contents($tmpName));
                }
                // enregistrer les données dans la bd
                $requete = "INSERT INTO Users (Nom, `MotDePasse`, Email, Prenom, DateNaissance, Adresse, TelephonePortable, Photo, EmailSecours) 
                            VALUES (:nom, :motDePasse, :email, :prenom, :dateNaissance, :adresse, :telephonePortable, :photo, :emailSecours)";
                $stmt = $connexion->prepare($requete);

                // Liaison des valeurs avec les paramètres
                $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
                $stmt->bindParam(':motDePasse', $motDePasseHash, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
                $stmt->bindParam(':dateNaissance', $dateNaissance, PDO::PARAM_STR);
                $stmt->bindParam(':adresse', $adresse, PDO::PARAM_STR);
                $stmt->bindParam(':telephonePortable', $telephonePortable, PDO::PARAM_STR);
                $stmt->bindParam(':photo', $photoData, PDO::PARAM_LOB);
                $stmt->bindParam(':emailSecours', $emailSecours, PDO::PARAM_STR);

                try {
                    $stmt->execute();
                    $_SESSION['utilisateur_id'] = $connexion->lastInsertId();
                    echo "<p>Données sauvegardées dans les variables de session et dans la base de données.</p>";
                } catch (PDOException $e) {
                    echo "Erreur : " . $e->getMessage();
                }
            }
        }
        ?>

        <!-- Formulaire -->
        <form method="post" action="" enctype="multipart/form-data">

        <!-- Première partie -->
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required><br>

            <label for="motDePasse">Mot de passe :</label>
            <input type="password" id="motDePasse" name="motDePasse" required><br>
        </div>

        <!-- Deuxième partie  -->
        <div class="form-group">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required><br>

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required><br>

            <label for="dateNaissance">Date de naissance :</label>
            <input type="date" id="dateNaissance" name="dateNaissance" required><br>

            <label for="adresse">Adresse :</label>
            <input type="text" id="adresse" name="adresse"><br>

            <label for="telephonePortable">Téléphone portable :</label>
            <input type="text" id="telephonePortable" name="telephonePortable"><br>

            <label for="photo">Photo :</label>
            <input type="file" id="photo" name="photo"><br>

            <label for="emailSecours">Email de secours :</label>
            <input type="email" id="emailSecours" name="emailSecours" required><br>
        </div>

        <input type="submit" value="Enregistrer">
    </form>
    <p>Vous avez déjà un compte ? <a href="Session.php">Connectez-vous ici</a>.</p>
    <a href="../index.php">Retour</a>
    </div>
</body>
</html>
