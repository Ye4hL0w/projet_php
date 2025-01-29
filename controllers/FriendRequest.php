<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>InstaChat</title>
    <link rel="stylesheet" href="../assets/css/FriendRequestStyle.css">
    <script src="../script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Gestion des demandes d'amis</h1>
    <?php
    session_start();

    include('./ConnexionBD.php');

    if (!isset($_SESSION['utilisateur_id'])) {
        // Rediriger l'utilisateur s'il n'est pas connecté
        header("Location: Session.php");
        exit();
    }

    $utilisateur_id = $_SESSION['utilisateur_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom_receveur = isset($_POST['nom_receveur']) ? $_POST['nom_receveur'] : '';

        // Recherche de l'ID de l'utilisateur par son nom
        $requete_id = "SELECT ID FROM Users WHERE Nom = :nom_receveur";
        $stmt_id = $connexion->prepare($requete_id);
        $stmt_id->execute(['nom_receveur' => $nom_receveur]);

        if ($stmt_id->rowCount() > 0) {
            // L'utilisateur existe, obtenir son ID
            $row = $stmt_id->fetch(PDO::FETCH_ASSOC);
            $id_receveur = $row['ID'];

            // Vérifier si la demande d'amis n'existe pas déjà
            $requete_existence = "SELECT * FROM DemandesAmis WHERE ID_Envoyeur = :utilisateur_id AND ID_Receveur = :id_receveur";
            $stmt_existence = $connexion->prepare($requete_existence);
            $stmt_existence->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
            $stmt_existence->bindParam(':id_receveur', $id_receveur, PDO::PARAM_INT);
            $stmt_existence->execute();

            if ($stmt_existence->rowCount() == 0) {
                // La demande n'existe pas encore, on peut l'ajouter
                $requete_insertion = "INSERT INTO DemandesAmis (ID_Envoyeur, ID_Receveur) VALUES (:utilisateur_id, :id_receveur)";
                $stmt_insertion = $connexion->prepare($requete_insertion);
                $stmt_insertion->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
                $stmt_insertion->bindParam(':id_receveur', $id_receveur, PDO::PARAM_INT);
                $stmt_insertion->execute();
                echo "<p>Demande d'amis envoyée avec succès.</p>";
            } else {
                echo "<p>Vous avez déjà envoyé une demande d'amis à cet utilisateur.</p>";
            }
        } else {
            echo "<p>L'utilisateur avec le nom spécifié n'existe pas.</p>";
        }
    }
    ?>

    <form method="post" action="">
        <label for="nom_receveur">Nom de l'utilisateur à ajouter en ami :</label>
        <input type="text" id="nom_receveur" name="nom_receveur" required>
        <input type="submit" value="Envoyer une demande d'amis">
    </form>
    <a href="../index.php">Retour</a>
    </div>
</body>
</html>