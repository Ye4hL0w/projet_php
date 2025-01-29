<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Session Admin</title>
    <link rel="stylesheet" href="assets/css/admin/AdminDashboardStyle.css">
    <script src="assets/js/script.js"></script>
</head>
<body>
    <nav>
        <ul>
            <?php
            session_start();

            // s'assurer que seul l'administrateur peut accéder à cette page
            if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
                header("Location: index.php");
                exit();
            }

            // liens de navigation de l'administrateur
            echo '<li class="left"><a href="controllers/adminViewPages.php">Visualiser les Pages</a></li>';
            echo '<li class="left"><a href="controllers/adminManageMembers.php">Gérer les membres</a></li>';
            echo '<li class="left"><a href="controllers/adminPublishAd.php">Publier une annonce</a></li>';
            echo '<li class="left"><a href="controllers/adminBlockContent.php">Bloquer un contenu</a></li>';

            // lien de déconnexion
            echo '<li class="right"><a href="controllers/destroy_session.php">Déconnexion</a></li>';

            include('controllers/ConnexionBD.php');
                $requete_nom_utilisateur = "SELECT Nom FROM Users WHERE ID = :utilisateur_id";
                $stmt_nom_utilisateur = $connexion->prepare($requete_nom_utilisateur);
                $stmt_nom_utilisateur->execute([':utilisateur_id' => $_SESSION['utilisateur_id']]);
                $nom_administrateur = $stmt_nom_utilisateur->fetchColumn();

                echo '<li class="right"><p style="color: white; margin: 0; padding-top: 14px;">' . $nom_administrateur . '</p></li>';
                ?>
        </ul>
    </nav>
    <div class="container">
        <h1>Tableau de bord de l'administrateur</h1>
    </div>
</body>
</html>