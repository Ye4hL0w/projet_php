<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>InstaChat</title>
    <link rel="stylesheet" href="../assets/css/SessionStyle.css">
    <script src="../script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Se connecter</h1>
        <?php
            session_start();
            include('./ConnexionBD.php');

            // Traitement du formulaire de connexion
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = isset($_POST['email']) ? $_POST['email'] : '';
                $motDePasse = isset($_POST['motDePasse']) ? $_POST['motDePasse'] : '';

                // requête de sélection de l'utilisateur par email, y compris un check pour voir s'il est admin
                $requete = "SELECT * FROM Users WHERE Email = :email LIMIT 1";
                $stmt = $connexion->prepare($requete);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);

                try {
                    $stmt->execute();
                    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

                    // vérification du mot de passe
                    if ($utilisateur && password_verify($motDePasse, $utilisateur['MotDePasse'])) {
                        // l'utilisateur est connecté
                        $_SESSION['utilisateur_id'] = $utilisateur['ID'];
                        // verifier si l'utilisateur est un administrateur
                        if (!empty($utilisateur['isAdmin']) && $utilisateur['isAdmin']) {
                            $_SESSION['isAdmin'] = true;
                            header("Location: ../adminDashboard.php"); // redirection vers le tableau de bord admin
                        } else {
                            $_SESSION['isAdmin'] = false;
                            header("Location: ../index.php"); // redirection vers la page d'accueil utilisateur
                        }
                        exit();
                    } else {
                        echo "<p>Identifiants incorrects. Veuillez réessayer.</p>";
                    }
                } catch (PDOException $e) {
                    echo "Erreur : " . $e->getMessage();
                }
            }
            ?>

            <form method="post" action="">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required><br>

                <label for="motDePasse">Mot de passe :</label>
                <input type="password" id="motDePasse" name="motDePasse" required><br>

                <input type="submit" value="Se connecter">
            </form>
            <p>Vous n'avez pas de compte ? <a href="Register.php">Créez-en un ici</a>.</p>
            <a href="../index.php">Retour</a>
    </div>
</body>
</html>
