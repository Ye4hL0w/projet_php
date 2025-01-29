<?php
session_start();
include('./ConnexionBD.php');

if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header("Location: index.php");
    exit();
}

$messageSent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    // enregistrement du message pour chaque membre dans la base de données
    $stmtSelectMembers = $connexion->prepare("SELECT ID FROM Users");
    $stmtSelectMembers->execute();
    $members = $stmtSelectMembers->fetchAll(PDO::FETCH_ASSOC);

    foreach ($members as $member) {
        $stmtInsertMessage = $connexion->prepare("INSERT INTO Messages (memberId, message, `read`) VALUES (:memberId, :message, 0)");
        $stmtInsertMessage->execute([':memberId' => $member['ID'], ':message' => $message]);
    }

    // le message a été envoyé
    $messageSent = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Envoyer une alerte globale</title>
    <link rel="stylesheet" href="../assets/css/admin/AdminPublishAdStyle.css">
    <script src="../assets/js/script.js"></script>
</head>
<body>
<div class="container">
    <h1>Envoyer une alerte à tous les membres</h1>
    <?php if ($messageSent): ?>
        <p>Alerte envoyée à tous les membres avec succès.</p>
    <?php endif; ?>
    <form method="post" action="adminPublishAd.php">
        <label for="message">Message :</label>
        <textarea id="message" name="message" required></textarea><br>
        <input type="submit" value="Envoyer le message">
    </form>
    <a href="../adminDashboard.php">Retour</a>
</div>
</body>
</html>
