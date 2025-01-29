<?php
session_start();
require './ConnexionBD.php';

if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header("Location: index.php");
    exit();
}

// suppression d'un membre :
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_member'])) {
    $memberId = $_POST['memberId'];

    // transaction pour s'assurer que toutes les opérations sont effectuées avec succès
    $connexion->beginTransaction();

    try {
        // suppression des commentaires de l'utilisateur
        $deleteCommentsQuery = "DELETE FROM Commentaires WHERE ID_Utilisateur = :memberId";
        $deleteCommentsStmt = $connexion->prepare($deleteCommentsQuery);
        $deleteCommentsStmt->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        $deleteCommentsStmt->execute();

        // suppression des commentaires associés à la publication
        $deleteCommentsQuery = "DELETE FROM Commentaires WHERE ID_Publication = :publicationId";
        $deleteCommentsStmt = $connexion->prepare($deleteCommentsQuery);
        $deleteCommentsStmt->bindParam(':publicationId', $publicationId, PDO::PARAM_INT);
        $deleteCommentsStmt->execute();

        // suppression des publications associées à l'utilisateur
        $deletePublicationsQuery = "DELETE FROM Publications WHERE ID_Utilisateur = :memberId";
        $deletePublicationsStmt = $connexion->prepare($deletePublicationsQuery);
        $deletePublicationsStmt->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        $deletePublicationsStmt->execute();

        // suppression des messages associés à l'utilisateur
        $deleteMessagesQuery = "DELETE FROM Messages WHERE memberId = :memberId";
        $deleteMessagesStmt = $connexion->prepare($deleteMessagesQuery);
        $deleteMessagesStmt->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        $deleteMessagesStmt->execute();

        // suppression des demandes d'amis associées à l'utilisateur
        $deleteDemandesAmisQuery = "DELETE FROM DemandesAmis WHERE ID_Envoyeur = :memberId OR ID_Receveur = :memberId";
        $deleteDemandesAmisStmt = $connexion->prepare($deleteDemandesAmisQuery);
        $deleteDemandesAmisStmt->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        $deleteDemandesAmisStmt->execute();

        // suppression de l'utilisateur de la table Users
        $deleteUsersQuery = "DELETE FROM Users WHERE ID = :memberId";
        $deleteUsersStmt = $connexion->prepare($deleteUsersQuery);
        $deleteUsersStmt->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        $deleteUsersStmt->execute();

        // validation de la transaction
        $connexion->commit();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        // si il y a une erreur, annulation de la transaction
        $connexion->rollBack();
        die("Erreur lors de la suppression du membre : " . $e->getMessage());
    }
}

// récupération de la liste complète des membres
$membersQuery = "SELECT * FROM Users";
$membersStmt = $connexion->prepare($membersQuery);
$membersStmt->execute();
$members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Session Admin</title>
    <link rel="stylesheet" href="../assets/css/admin/AdminManageMembersStyle.css">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Liste complète des membres</h1>
        
        <!-- Recherche par date -->
        <div class="search-by-date">
            <label for="start-date">Date de début :</label>
            <input type="date" id="start-date" name="start-date">
            <label for="end-date">Date de fin :</label>
            <input type="date" id="end-date" name="end-date">
            <button onclick="searchByDate()">Rechercher par date</button>
        </div>

        <!-- Recherche par nom -->
        <div class="search-by-name">
            <label for="search-name">Rechercher par nom :</label>
            <input type="text" id="search-name" onkeyup="searchByName()" placeholder="Nom de membre">
        </div>

        <?php if (!empty($members)): ?>
            <table id="members-list">
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['Nom']); ?></td>
                        <td><?php echo htmlspecialchars($member['Prenom']); ?></td>
                        <td><?php echo htmlspecialchars($member['Email']); ?></td>
                        <td><?php echo htmlspecialchars($member['DateInscription']); ?></td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="memberId" value="<?php echo $member['ID']; ?>">
                                <input type="submit" name="delete_member" value="Supprimer" class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ?');">
                                <a href="adminSendAlert.php?memberId=<?php echo $member['ID']; ?>" class="alert-button">Alerter</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Aucun membre enregistré.</p>
        <?php endif; ?>
        <a href="../adminDashboard.php">Retour</a>
    </div>

    <script>
        // fonction pour la recherche par date
        function searchByDate() {
            const startDate = document.getElementById("start-date").value;
            const endDate = document.getElementById("end-date").value;

            // envoie des dates au serveur via une requête AJAX
            const xhr = new XMLHttpRequest();
            xhr.open("GET", `search_by_date.php?start=${startDate}&end=${endDate}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Mise à jour de la liste des membres avec les résultats de la recherche
                    const response = xhr.responseText;
                    document.getElementById("members-list").innerHTML = response;
                }
            };
            xhr.send();
        }

        // fonction pour la recherche par nom en temps réel
        function searchByName() {
            const searchName = document.getElementById("search-name").value;

            // envoi du le nom au serveur via une requête AJAX
            const xhr = new XMLHttpRequest();
            xhr.open("GET", `search_by_name.php?name=${searchName}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // mise à jour la liste des membres avec les résultats de la recherche
                    const response = xhr.responseText;
                    document.getElementById("members-list").innerHTML = response;
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>
