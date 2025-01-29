<?php

session_start();
require './ConnexionBD.php';

if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header("Location: index.php");
    exit();
}

$memberId = $_GET['memberId'] ?? null;
$messageSent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    // enregistrement du message dans la bd
    $stmt = $connexion->prepare("INSERT INTO Messages (memberId, message, `read`) VALUES (:memberId, :message, 0)");
    $stmt->execute([':memberId' => $memberId, ':message' => $message]);

    // le message a été envoyé !
    $messageSent = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Envoyer une alerte</title>
    <link rel="stylesheet" href="../assets/css/admin/AdminSendAlertStyle.css">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Envoyer une alerte au membre</h1>
        <?php if ($messageSent): ?>
            <p>Alerte envoyée avec succès.</p>
        <?php endif; ?>
        <form method="post" action="adminSendAlert.php?memberId=<?php echo $memberId; ?>">
            <label for="message">Message :</label>
            <textarea id="message" name="message" required></textarea><br>
            <input type="submit" value="Envoyer le message">
        </form>
        <a href="adminManageMembers.php">Retour</a>
    </div>
</body>
</html>