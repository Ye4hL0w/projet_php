<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>InstaChat</title>
    <link rel="stylesheet" href="../assets/css/FriendsProfileStyle.css">
    <script src="../script.js"></script>
</head>
<body>
    <?php
        session_start();
        include('./ConnexionBD.php');

        $ami_id = $_GET['id'] ?? null;

        if (!$ami_id || !isset($_SESSION['utilisateur_id'])) {
            header("Location: Session.php");
            exit();
        }

        // traitement des likes et dislikes
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publication_id'], $_POST['action'])) {
            $publication_id = $_POST['publication_id'];
            $action = $_POST['action']; // 'like' ou 'dislike'

            // verification si l'utilisateur a déjà effectué cette action sur cette publication
            $stmt_check_interaction = $connexion->prepare("SELECT ID, Liked FROM UserInteractions WHERE UserID = :utilisateur_id AND PublicationID = :publication_id");
            $stmt_check_interaction->execute([':utilisateur_id' => $utilisateur_id, ':publication_id' => $publication_id]);
            $interaction = $stmt_check_interaction->fetch(PDO::FETCH_ASSOC);

            if ($interaction) {
                // l'utilisateur a déjà effectué cette action, supprimer l'interaction existante
                $stmt_delete_interaction = $connexion->prepare("DELETE FROM UserInteractions WHERE UserID = :utilisateur_id AND PublicationID = :publication_id");
                $stmt_delete_interaction->execute([':utilisateur_id' => $utilisateur_id, ':publication_id' => $publication_id]);

                // - 1 compteur si l'utilisateur annule son like ou dislike
                if ($action == 'like' && $interaction['Liked'] == 1) {
                    $stmt_decrement_likes = $connexion->prepare("UPDATE Publications SET Likes = Likes - 1 WHERE ID = :publication_id");
                    $stmt_decrement_likes->execute([':publication_id' => $publication_id]);
                } elseif ($action == 'dislike' && $interaction['Liked'] == 0) {
                    $stmt_decrement_dislikes = $connexion->prepare("UPDATE Publications SET Dislikes = Dislikes - 1 WHERE ID = :publication_id");
                    $stmt_decrement_dislikes->execute([':publication_id' => $publication_id]);
                }
            } else {
                // l'utilisateur n'a pas encore effectué cette action sur cette publication
                // enregistrer l'interaction de l'utilisateur dans la table UserInteractions
                $stmt_record_interaction = $connexion->prepare("INSERT INTO UserInteractions (UserID, PublicationID, Liked) VALUES (:utilisateur_id, :publication_id, :liked)");
                $stmt_record_interaction->execute([':utilisateur_id' => $utilisateur_id, ':publication_id' => $publication_id, ':liked' => ($action == 'like' ? 1 : 0)]);

                // + 1 compteur
                if ($action == 'like') {
                    $stmt_increment_likes = $connexion->prepare("UPDATE Publications SET Likes = Likes + 1 WHERE ID = :publication_id");
                    $stmt_increment_likes->execute([':publication_id' => $publication_id]);
                } elseif ($action == 'dislike') {
                    $stmt_increment_dislikes = $connexion->prepare("UPDATE Publications SET Dislikes = Dislikes + 1 WHERE ID = :publication_id");
                    $stmt_increment_dislikes->execute([':publication_id' => $publication_id]);
                }
            }

            // rediriger l'utilisateur vers la page précédente pour éviter un rechargement de page POST
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // traitement des commentaires
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publication_id'], $_POST['comment_content'])) {
            $publication_id = $_POST['publication_id'];
            $comment_content = trim($_POST['comment_content']); // Trim pour supprimer les espaces en début et fin de chaîne

            // verification si le contenu du commentaire n'est pas vide
            if (!empty($comment_content)) {
                // enregistrer le commentaire dans la table Commentaires
                $stmt_record_comment = $connexion->prepare("INSERT INTO Commentaires (ID_Utilisateur, ID_Publication, Contenu, DateCommentaire) VALUES (:utilisateur_id, :publication_id, :comment_content, NOW())");
                $stmt_record_comment->execute([':utilisateur_id' => $utilisateur_id, ':publication_id' => $publication_id, ':comment_content' => $comment_content]);

                // rediriger l'utilisateur vers la page précédente pour éviter un rechargement de page POST
                header("Location: Profil.php"); // Rediriger vers la page de profil après l'ajout du commentaire
                exit();
            } else {
                echo "Erreur: Le contenu du commentaire ne peut pas être vide.";
            }
        }


        // récupérer les informations de l'ami
        $requete_profil = "SELECT Nom, Prenom, DateNaissance, Adresse, TelephonePortable, Photo FROM Users WHERE ID = :ami_id";
        $stmt_profil = $connexion->prepare($requete_profil);
        $stmt_profil->bindParam(':ami_id', $ami_id, PDO::PARAM_INT);
        $stmt_profil->execute();
        $profil_ami = $stmt_profil->fetch(PDO::FETCH_ASSOC);

        // si l'ami a été trouvé dans la base de données
        if ($profil_ami) {
            echo "<div class='profil'>";
            if (!empty($profil_ami['Photo'])) {
                echo "<img src='data:image/jpeg;base64," . $profil_ami['Photo'] . "' alt='Photo de profil'>";
            }
            echo "<h2>" . htmlspecialchars($profil_ami['Prenom']) . " " . htmlspecialchars($profil_ami['Nom']) . "</h2>";
            echo "<p>Date de naissance: " . htmlspecialchars($profil_ami['DateNaissance']) . "</p>";
            echo "<p>Adresse: " . htmlspecialchars($profil_ami['Adresse']) . "</p>";
            echo "<p>Téléphone: " . htmlspecialchars($profil_ami['TelephonePortable']) . "</p>";
            echo "</div>";
        } else {
            echo "<p>Profil non trouvé.</p>";
        }

        // récupérer et afficher les publications de l'ami
        $requete_publications_ami = "SELECT * FROM Publications WHERE ID_Utilisateur = :ami_id AND Masquer = 0 ORDER BY DatePublication DESC";
        $stmt_publications_ami = $connexion->prepare($requete_publications_ami);
        $stmt_publications_ami->bindParam(':ami_id', $ami_id, PDO::PARAM_INT);
        $stmt_publications_ami->execute();

        if ($stmt_publications_ami->rowCount() > 0) {
            while ($publication = $stmt_publications_ami->fetch(PDO::FETCH_ASSOC)) {
                echo "<div class='dcc'>";
                echo "<h3>" . htmlspecialchars($publication['Titre']) . "</h3>";
                echo "<p>" . htmlspecialchars($publication['Contenu']) . "</p>";

                if (!empty($publication['Photo'])) {
                    $photo_base64 = $publication['Photo'];
            
                    // affichage de l'image en utilisant le type MIME approprié
                    $finfo = finfo_open();
                    $mime_type = finfo_buffer($finfo, base64_decode($photo_base64), FILEINFO_MIME_TYPE);
                    finfo_close($finfo);
            
                    $image_src = 'data:' . $mime_type . ';base64,' . $photo_base64;
                    $image_src_safe = htmlspecialchars($image_src);
                    echo "<img src='" . $image_src_safe . "' alt='Image' type='" . $mime_type . "'>";
                }

                echo "<p>Likes: " . htmlspecialchars($publication['Likes']) . " | Dislikes: " . htmlspecialchars($publication['Dislikes']) . "</p>";
        
                // ajout des boutons de like et de dislike
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='publication_id' value='" . $publication['ID'] . "'>";
                echo "<button type='submit' name='action' value='like' class='btn'>Like</button>";
                echo "<button type='submit' name='action' value='dislike' class='btn'>Dislike</button>";
                echo "</form>";

                // formulaire pour ajouter un commentaire
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='publication_id' value='" . $publication['ID'] . "'>";
                echo "<label for='comment_content'>Commentaire:</label>";
                echo "<textarea name='comment_content' id='comment_content'></textarea>";
                echo "<button type='submit' class='btn'>Ajouter un commentaire</button>";
                echo "</form>";

                // affichage des commentaires
                $stmt_comments = $connexion->prepare("SELECT Commentaires.*, Users.Nom, Users.Prenom FROM Commentaires JOIN Users ON Commentaires.ID_Utilisateur = Users.ID WHERE Commentaires.ID_Publication = :publication_id ORDER BY Commentaires.DateCommentaire DESC");
                $stmt_comments->execute([':publication_id' => $publication['ID']]);

                while ($comment = $stmt_comments->fetch(PDO::FETCH_ASSOC)) {
                    echo "<div class='comment'>";
                    echo "<p>" . htmlspecialchars($comment['Nom']) . " " . htmlspecialchars($comment['Prenom']) . ": " . htmlspecialchars($comment['Contenu']) . "</p>";
                    echo "<p class='comment-date'><strong>" . htmlspecialchars($comment['DateCommentaire']) . "</strong></p>";
                    echo "</div>";
                }

                echo "<p>Date de publication: " . htmlspecialchars($publication['DatePublication']) . "</p>";
                echo "</div>";

            }
        } else {
            echo "<p>Cet ami n'a posté aucune publication pour le moment.</p>";
        }

        echo "<a href='../index.php'>Retour</a>";
    ?>
</body>
</html>