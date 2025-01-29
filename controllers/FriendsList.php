<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/FriendsListStyle.css">
    <script src="../script.js"></script>
    <title>InstaChat</title>
</head>

<body>
    <?php
    session_start();
    include('./ConnexionBD.php');

    if (!isset($_SESSION['utilisateur_id'])) {
        header("Location: Session.php");
        exit();
    }

    $utilisateur_id = $_SESSION['utilisateur_id'];

    // récupération de la liste des amis acceptés avec leur nom et prénom
    $requete_amis = "
        SELECT Users.ID, Users.Nom, Users.Prenom 
        FROM Users 
        INNER JOIN DemandesAmis ON Users.ID = DemandesAmis.ID_Envoyeur OR Users.ID = DemandesAmis.ID_Receveur
        WHERE (DemandesAmis.ID_Envoyeur = :utilisateur_id OR DemandesAmis.ID_Receveur = :utilisateur_id)
        AND DemandesAmis.Statut = 'Acceptée'
        AND Users.ID != :utilisateur_id";
    $stmt_amis = $connexion->prepare($requete_amis);
    $stmt_amis->execute([':utilisateur_id' => $utilisateur_id]);

    echo "<h2>Liste de vos amis :</h2>";
    echo "<ul>";
    while ($ami = $stmt_amis->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>";
        echo htmlspecialchars($ami['Prenom']) . " " . htmlspecialchars($ami['Nom']); // nom et prénom
        echo "<a href='FriendsProfile.php?id=" . $ami['ID'] . "'>Voir Profil</a>"; // lien vers le profil de l'ami
        echo " - <form method='post' action='' style='display: inline;'>";
        echo "<input type='hidden' name='id_ami' value='" . $ami['ID'] . "'>";
        echo "<input type='submit' name='supprimer_ami' value='Supprimer'>"; // supprimer l'ami
        echo "</form>";
        echo "</li>";
    }
    echo "</ul>";

    // traitement de la suppression d'un ami
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_ami'])) {
        $id_ami = $_POST['id_ami'];

        // suppression des deux côtés de la relation d'amitié
        $requete_suppression = "
            DELETE FROM DemandesAmis 
            WHERE (ID_Envoyeur = :utilisateur_id AND ID_Receveur = :id_ami AND Statut = 'Acceptée') 
            OR (ID_Envoyeur = :id_ami AND ID_Receveur = :utilisateur_id AND Statut = 'Acceptée')";
        $stmt_suppression = $connexion->prepare($requete_suppression);
        $stmt_suppression->execute([':utilisateur_id' => $utilisateur_id, ':id_ami' => $id_ami]);

        echo "<p>Ami supprimé avec succès.</p>";
        // refresh la page pour mettre à jour la liste des amis
        header("Refresh:0");
    }
    ?>

    <a href="../index.php">Retour</a>
</body>
</html>