<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/indexStyle.css">
    <script src="assets/js/script.js"></script>
    <title>InstaChat</title>
</head>
<body>
    <nav>
        <ul class= "nav-links">
            <?php
            session_start();
            include('controllers/ConnexionBD.php');

            if (isset($_SESSION['utilisateur_id'])) {
                // Utilisateur connecté, liens normaux
                echo '<li class="right"><a href="controllers/destroy_session.php">Déconnexion</a></li>';
                echo '<li class="left"><a href="controllers/Publication.php">Poster une publication</a></li>';
                echo '<li class="left"><a href="controllers/FriendsList.php">Gérer les amis</a></li>';
                echo '<li class="left"><a href="controllers/Request.php">Requetes</a></li>';
                echo '<li class="left"><a href="controllers/FriendRequest.php">Ajouter un ami</a></li>';

                $requete_nom_utilisateur = "SELECT Nom FROM Users WHERE ID = :utilisateur_id";
                $stmt_nom_utilisateur = $connexion->prepare($requete_nom_utilisateur);
                $stmt_nom_utilisateur->execute([':utilisateur_id' => $_SESSION['utilisateur_id']]);
                $nom_utilisateur = $stmt_nom_utilisateur->fetchColumn();

                echo '<li class="right"><a href="controllers/Profil.php">' . htmlspecialchars($nom_utilisateur) . '</a></li>';
            } else {
                // Utilisateur non connecté, liens désactivés
                echo '<li class="right"><a href="controllers/Register.php">Créer un compte</a></li>';
                echo '<li class="right"><a href="controllers/Session.php">Se connecter</a></li>';
                echo '<li class="left"><a class="disabled" href="#">Poster une publication</a></li>';
                echo '<li class="left"><a class="disabled" href="#">Requetes</a></li>';
                echo '<li class="left"><a class="disabled" href="#">Ajouter un ami</a></li>';
            }

            

            // fonction afficher et marquer les messages non lus
            function afficherMessagesNonLus($userID, $connexion) {
                $stmt = $connexion->prepare("SELECT id, message FROM Messages WHERE memberId = :userID AND `read` = 0");
                $stmt->execute([':userID' => $userID]);
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($messages) {
                    foreach ($messages as $message) {
                        echo "<script>alert(" . json_encode($message['message']) . ");</script>";
                        $stmtUpdate = $connexion->prepare("UPDATE Messages SET `read` = 1 WHERE id = :id");
                        $stmtUpdate->execute([':id' => $message['id']]);
                    }
                }
            }

            ?>
        </ul>
    </nav>
    <div class="calendar-selector">
        <form action="" method="get">
            <label for="monthYear">Filtrez par mois et année :</label>
            <input type="month" id="monthYear" name="monthYear" required>
            <input type="submit" value="Afficher">
        </form>
    </div>
            <?php
            // Traitement des likes et dislikes
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publication_id'], $_POST['action'])) {
                $publication_id = $_POST['publication_id'];
                $action = $_POST['action']; // 'like' ou 'dislike'
            
                try {
                    // Enregistrer l'interaction de l'utilisateur dans la table UserInteractions
                    $stmt_record_interaction = $connexion->prepare("INSERT INTO UserInteractions (UserID, PublicationID, Liked) VALUES (:utilisateur_id, :publication_id, :liked)");
                    $stmt_record_interaction->execute([':utilisateur_id' => $utilisateur_id, ':publication_id' => $publication_id, ':liked' => ($action == 'like' ? 1 : 0)]);
            
                    // Incrémenter le compteur
                    if ($action == 'like') {
                        $stmt_increment_likes = $connexion->prepare("UPDATE Publications SET Likes = Likes + 1 WHERE ID = :publication_id");
                        $stmt_increment_likes->execute([':publication_id' => $publication_id]);
                    } elseif ($action == 'dislike') {
                        $stmt_increment_dislikes = $connexion->prepare("UPDATE Publications SET Dislikes = Dislikes + 1 WHERE ID = :publication_id");
                        $stmt_increment_dislikes->execute([':publication_id' => $publication_id]);
                    }
            
                    // Rediriger l'utilisateur vers la page précédente pour éviter un rechargement de page POST
                    header("Location: index.php"); // Rediriger vers la page d'accueil après la modification
                    exit();
                } catch (PDOException $e) {
                    // La contrainte d'unicité a été violée, l'utilisateur a déjà effectué cette action
                    echo "Vous avez déjà " . ($action == 'like' ? "liké" : "disliké") . " cette publication.";
                }
            }
            
            
            // Traitement des commentaires
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publication_id'], $_POST['comment_content'])) {
                $publication_id = $_POST['publication_id'];
                $comment_content = trim($_POST['comment_content']); // Trim pour supprimer les espaces en début et fin de chaîne

                // Vérifier si le contenu du commentaire n'est pas vide
                if (!empty($comment_content)) {
                    // Enregistrer le commentaire dans la table Commentaires
                    $stmt_record_comment = $connexion->prepare("INSERT INTO Commentaires (ID_Utilisateur, ID_Publication, Contenu, DateCommentaire) VALUES (:utilisateur_id, :publication_id, :comment_content, NOW())");
                    $stmt_record_comment->execute([':utilisateur_id' => $utilisateur_id, ':publication_id' => $publication_id, ':comment_content' => $comment_content]);

                    // Rediriger l'utilisateur vers la page précédente pour éviter un rechargement de page POST
                    header("Location: index.php"); // Rediriger vers la page de profil après l'ajout du commentaire
                    exit();
                } else {
                    echo "Erreur: Le contenu du commentaire ne peut pas être vide.";
                }
            }

            // si l'utilisateur est connecté et affichez les messages non lus
                if (isset($_SESSION['utilisateur_id'])) {
                    afficherMessagesNonLus($_SESSION['utilisateur_id'], $connexion);
                }

                $utilisateur_id = $_SESSION['utilisateur_id'] ?? null;
                // Vérification et traitement de la sélection du calendrier
            $dateFilter = false;
            if (isset($_GET['monthYear'])) {
                $dateFilter = true;
                $monthYear = $_GET['monthYear'];
                $dateParts = explode('-', $monthYear);
                $year = $dateParts[0];
                $month = $dateParts[1];
            }

            // Préparation de la requête SQL avec ou sans filtre de date
            $sql = "
                SELECT 
                    Publications.*, 
                    Users.Nom AS NomUtilisateur, 
                    Users.Prenom AS PrenomUtilisateur 
                FROM 
                    Publications 
                JOIN 
                    Users ON Publications.ID_Utilisateur = Users.ID
                WHERE 
                    Publications.Masquer = 0 AND (
                        Publications.Visibilite = 'public' 
                        OR Publications.ID_Utilisateur = :utilisateur_id 
                        OR Publications.ID_Utilisateur IN (
                            SELECT ID_Envoyeur 
                            FROM DemandesAmis 
                            WHERE ID_Receveur = :utilisateur_id 
                            AND Statut = 'accepte'
                        ) 
                        OR Publications.ID_Utilisateur IN (
                            SELECT ID_Receveur 
                            FROM DemandesAmis 
                            WHERE ID_Envoyeur = :utilisateur_id 
                            AND Statut = 'accepte'
                        )
                    )
            ";

            // Ajout du filtre de date si nécessaire
            if ($dateFilter) {
                $sql .= " AND YEAR(Publications.DatePublication) = :year AND MONTH(Publications.DatePublication) = :month";
            }

            $sql .= " ORDER BY Publications.DatePublication DESC LIMIT :offset, :limit";

            // Préparation et exécution de la requête
            $stmt_publications = $connexion->prepare($sql);
            $stmt_publications->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
            if ($dateFilter) {
                $stmt_publications->bindParam(':year', $year, PDO::PARAM_INT);
                $stmt_publications->bindParam(':month', $month, PDO::PARAM_INT);
            }

            
            $stmt_publications->bindValue(':offset', 0, PDO::PARAM_INT);
            $stmt_publications->bindValue(':limit', 10, PDO::PARAM_INT);
            
            // Exécutez la requête préparée une fois
            $stmt_publications->execute();
            
            // Affichez les publications par lots de 10
            $publications_par_page = 10;
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $offset = ($page - 1) * $publications_par_page;
            
            $stmt_publications->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt_publications->bindParam(':limit', $publications_par_page, PDO::PARAM_INT);
            

            $stmt_publications->execute();

            // Affichage des publications
            while ($publication = $stmt_publications->fetch(PDO::FETCH_ASSOC)) {
                echo "<div class='publication'>";
                echo "<h3>" . htmlspecialchars($publication['NomUtilisateur']) . " " . htmlspecialchars($publication['PrenomUtilisateur']) . "</h3>";
                echo "<h3>" . htmlspecialchars($publication['Titre']) . "</h3>";
                echo "<p>" . htmlspecialchars($publication['Contenu']) . "</p>";
            
                // Affichage de l'image s'il y en a une
                if (!empty($publication['Photo'])) {
                    $photo_base64 = $publication['Photo'];
                    $finfo = finfo_open();
                    $mime_type = finfo_buffer($finfo, base64_decode($photo_base64), FILEINFO_MIME_TYPE);
                    finfo_close($finfo);
                    $image_src = 'data:' . $mime_type . ';base64,' . $photo_base64;
                    echo "<img src='" . htmlspecialchars($image_src) . "' alt='Publication Image'>";
                }
            
                echo "<p>Likes : " . htmlspecialchars($publication['Likes']) . " | Dislikes : " . htmlspecialchars($publication['Dislikes']) . "</p>";

            


                if (isset($_SESSION['utilisateur_id'])) {

                    // Formulaire pour ajouter un commentaire
                    echo "<form method='post' action=''>
                            <input type='hidden' name='publication_id' value='" . $publication['ID'] . "'>
                            <label for='comment_content'>Commentaire:</label>
                            <textarea name='comment_content' id='comment_content' class='comment-textarea'></textarea>
                            <button type='submit' class='btn'>Ajouter un commentaire</button>
                        </form>";

                    // Formulaire pour liker ou disliker
                    echo "<form method='post' action=''>
                            <input type='hidden' name='publication_id' value='" . $publication['ID'] . "'>
                            <button type='submit' name='action' value='like' class='btn'>Like</button>
                            <button type='submit' name='action' value='dislike' class='btn'>Dislike</button>
                        </form>";
                }

                // Affichage des commentaires
                $stmt_comments = $connexion->prepare("SELECT Commentaires.*, Users.Nom, Users.Prenom FROM Commentaires JOIN Users ON Commentaires.ID_Utilisateur = Users.ID WHERE Commentaires.ID_Publication = :publication_id ORDER BY Commentaires.DateCommentaire DESC");
                $stmt_comments->execute([':publication_id' => $publication['ID']]);

                while ($comment = $stmt_comments->fetch(PDO::FETCH_ASSOC)) {
                    echo "<div class='comment'>";
                    echo "<p><strong>" . htmlspecialchars($comment['Nom']) . " " . htmlspecialchars($comment['Prenom']) . ":</strong> " . htmlspecialchars($comment['Contenu']) . "</p>";
                    echo "<p class='comment-date'>Posté le: " . htmlspecialchars($comment['DateCommentaire']) . "</p>";
                    echo "</div>";                }
                echo "</div>";
            }



            // Affichage de la pagination
            $stmt_count_publications = $connexion->prepare("
                SELECT COUNT(*) 
                FROM Publications 
                WHERE Visibilite = 'public' 
                OR ID_Utilisateur = :utilisateur_id 
                OR ID_Utilisateur IN (
                    SELECT ID_Receveur 
                    FROM DemandesAmis 
                    WHERE ID_Envoyeur = :utilisateur_id AND Statut = 'Acceptee'
                ) 
                OR ID_Utilisateur IN (
                    SELECT ID_Envoyeur 
                    FROM DemandesAmis 
                    WHERE ID_Receveur = :utilisateur_id AND Statut = 'Acceptee'
                )
            ");
            $stmt_count_publications->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
            $stmt_count_publications->execute();
            $total_publications = $stmt_count_publications->fetchColumn();
            $total_pages = ceil($total_publications / $publications_par_page);

            // Affichage de la pagination
            echo "<div class='pagination'>";
            for ($i = 1; $i <= $total_pages; $i++) {
                // Assurez-vous que la classe active est appliquée à la page actuelle
                $class = ($page == $i) ? 'active' : '';
                echo "<a href='?page=$i' class='$class'>$i</a>";
            }
            echo "</div>";
            ?>
</body>
</html>