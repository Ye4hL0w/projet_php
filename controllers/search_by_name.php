<?php
session_start();
require './ConnexionBD.php';

if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['name'])) {
    $searchName = $_GET['name'];

    // Effectuez une requête SQL pour récupérer les membres par nom
    $membersQuery = "SELECT * FROM Users WHERE Nom LIKE :search_name";
    $membersStmt = $connexion->prepare($membersQuery);
    $searchName = '%' . $searchName . '%'; // Ajoutez des caractères génériques pour une correspondance partielle
    $membersStmt->bindParam(':search_name', $searchName);
    $membersStmt->execute();
    $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Générez le code HTML pour la liste de membres mise à jour
    if (!empty($members)) {
        echo '<table>';
        echo '<tr><th>Nom</th><th>Prénom</th><th>Email</th><th>Date d\'inscription</th><th>Actions</th></tr>';
        foreach ($members as $member) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($member['Nom']) . '</td>';
            echo '<td>' . htmlspecialchars($member['Prenom']) . '</td>';
            echo '<td>' . htmlspecialchars($member['Email']) . '</td>';
            echo '<td>' . htmlspecialchars($member['DateInscription']) . '</td>';
            echo '<td>';
            echo '<form method="post" action="">';
            echo '<input type="hidden" name="memberId" value="' . $member['ID'] . '">';
            echo '<input type="submit" name="delete_member" value="Supprimer" class="delete-button" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce membre ?\');">';
            echo '<a href="adminSendAlert.php?memberId=' . $member['ID'] . '" class="alert-button">Alerter</a>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Aucun membre trouvé avec ce nom.</p>';
    }
}
?>
