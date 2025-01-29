<?php
// AdminController.php

session_start();
require_once('ConnexionBD.php');

class AdminController {
    public static function handleGlobalAlert() {
        global $connexion;


        if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
            header("Location: index.php");
            exit();
        }

        $messageSent = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
            $message = $_POST['message'];

            // enregistrement du message pour chaque membre dans la base de données
            $stmtSelectMembers = ConnexionBD::getConnection()->prepare("SELECT ID FROM Users");
            $stmtSelectMembers->execute();
            $members = $stmtSelectMembers->fetchAll(PDO::FETCH_ASSOC);

            foreach ($members as $member) {
                $stmtInsertMessage = ConnexionBD::getConnection()->prepare("INSERT INTO Messages (memberId, message, `read`) VALUES (:memberId, :message, 0)");
                $stmtInsertMessage->execute([':memberId' => $member['ID'], ':message' => $message]);
            }

            // le message a été envoyé
            $messageSent = true;
        }

        return $messageSent;
    }
}
