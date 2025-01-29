<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>InstaChat</title>
    <link rel="stylesheet" href="../assets/css/RequestStyle.css">
    <script src="../script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Gestion des demandes d'amis</h1>
        <?php
        session_start();
        include('./ConnexionBD.php');

        $utilisateur_id = $_SESSION['utilisateur_id'];
        $demandesTrouvees = false;

        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_demande = isset($_POST['id_demande']) ? $_POST['id_demande'] : '';

            // Vérifier si l'utilisateur a cliqué sur "Accepter"
            if (isset($_POST['accepter'])) {
                // Mettre à jour le statut de la demande à "Acceptée"
                $requete_accepter = "UPDATE DemandesAmis SET Statut = 'Acceptée' WHERE ID_Demande = :id_demande";
                $stmt_accepter = $connexion->prepare($requete_accepter);
                $stmt_accepter->bindParam(':id_demande', $id_demande, PDO::PARAM_INT);
                $stmt_accepter->execute();

                // Autres actions à effectuer après avoir accepté (si nécessaire)
            } elseif (isset($_POST['refuser'])) {
                // Mettre à jour le statut de la demande à "Refusée"
                $requete_refuser = "UPDATE DemandesAmis SET Statut = 'Refusée' WHERE ID_Demande = :id_demande";
                $stmt_refuser = $connexion->prepare($requete_refuser);
                $stmt_refuser->bindParam(':id_demande', $id_demande, PDO::PARAM_INT);
                $stmt_refuser->execute();

                // Autres actions à effectuer après avoir refusé (si nécessaire)
            }
        }

        // Récupérer les demandes d'amis en attente pour l'utilisateur connecté avec l'ID de l'envoyeur
        $requete_demandes = "SELECT DemandesAmis.ID_Demande, Users.ID AS ID_Envoyeur, Users.Nom, Users.Prenom
                            FROM DemandesAmis
                            JOIN Users ON DemandesAmis.ID_Envoyeur = Users.ID
                            WHERE DemandesAmis.ID_Receveur = :utilisateur_id AND DemandesAmis.Statut = 'En attente'";
        $stmt_demandes = $connexion->prepare($requete_demandes);
        $stmt_demandes->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
        $stmt_demandes->execute();

        // Afficher les demandes d'amis en attente
        while ($demande = $stmt_demandes->fetch(PDO::FETCH_ASSOC)) {
            $demandesTrouvees = true;
            $id_envoyeur = $demande['ID_Envoyeur'];
            $nom_envoyeur = $demande['Nom'];
            $prenom_envoyeur = $demande['Prenom'];

            echo "<p>Demande d'amis de {$prenom_envoyeur} {$nom_envoyeur} (ID: {$id_envoyeur}).</p>";
            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='id_demande' value='{$demande['ID_Demande']}'>";
            echo "<input type='submit' name='accepter' value='Accepter'>";
            echo "<input type='submit' name='refuser' value='Refuser'>";
            echo "</form>";
        }
        if (!$demandesTrouvees) {
            echo "<p>Aucune demande d'amis en attente.</p>";
        }
        echo "<a href='../index.php'>Retour</a>";
        ?>
    </div>
</body>
</html>
