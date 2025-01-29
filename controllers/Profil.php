<?php
    session_start();
    include('./ConnexionBD.php');
    
    $utilisateur_id = $_SESSION['utilisateur_id'] ?? null;
    
    if (!$utilisateur_id) {
        header("Location: Session.php");
        exit();
    }

    include('./PublicationController.php');

    // Traitement de la suppresion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_publication_id'])) {
        $delete_publication_id = $_POST['delete_publication_id'];

        // Supprimer les commentaires liés à la publication
        $stmt_delete_comments = $connexion->prepare("DELETE FROM Commentaires WHERE ID_Publication = :publication_id");
        if (!$stmt_delete_comments->execute([':publication_id' => $delete_publication_id])) {
            echo "Erreur lors de la suppression des commentaires: " . print_r($stmt_delete_comments->errorInfo(), true);
        }

        // Supprimer la publication
        $stmt_delete_publication = $connexion->prepare("DELETE FROM Publications WHERE ID = :publication_id AND ID_Utilisateur = :utilisateur_id");
        if (!$stmt_delete_publication->execute([':publication_id' => $delete_publication_id, ':utilisateur_id' => $utilisateur_id])) {
            echo "Erreur lors de la suppression de la publication: " . print_r($stmt_delete_publication->errorInfo(), true);
        }

        // rediriger l'utilisateur vers la page précédente pour éviter un rechargement de page POST
        header("Location: Profil.php"); // Rediriger vers la page de profil après la suppression
        exit();
    }

    // traitement de la modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_publication_id'])) {
        $edit_publication_id = $_POST['edit_publication_id'];
    
        // récupérer les détails de la publication pour affichage dans le formulaire de modification
        $stmt_get_publication_details = $connexion->prepare("SELECT * FROM Publications WHERE ID = :publication_id AND ID_Utilisateur = :utilisateur_id");
        $stmt_get_publication_details->execute([':publication_id' => $edit_publication_id, ':utilisateur_id' => $utilisateur_id]);
        $publication_details = $stmt_get_publication_details->fetch(PDO::FETCH_ASSOC);
    
        if ($publication_details) {
            // afficher le formulaire de modification
            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='edit_publication_id' value='" . $edit_publication_id . "'>";
            echo "<label for='edit_title'>Titre:</label>";
            echo "<input type='text' name='edit_title' id='edit_title' value='" . htmlspecialchars($publication_details['Titre']) . "'>";
            echo "<label for='edit_content'>Contenu:</label>";
            echo "<textarea name='edit_content' id='edit_content'>" . htmlspecialchars($publication_details['Contenu']) . "</textarea>";
            echo "<button type='submit' name='action' value='edit'>Modifier la publication</button>";
            echo "</form>";
        } else {
            echo "Erreur: Vous n'avez pas le droit de modifier cette publication.";
        }
    }

    // Traitement de la modification (suite)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_publication_id'], $_POST['edit_title'], $_POST['edit_content'])) {
        $edit_publication_id = $_POST['edit_publication_id'];
        $edit_title = $_POST['edit_title'];
        $edit_content = $_POST['edit_content'];

        // mettre à jour les détails de la publication
        $stmt_update_publication = $connexion->prepare("UPDATE Publications SET Titre = :edit_title, Contenu = :edit_content WHERE ID = :edit_publication_id AND ID_Utilisateur = :utilisateur_id");
        if (!$stmt_update_publication->execute([':edit_title' => $edit_title, ':edit_content' => $edit_content, ':edit_publication_id' => $edit_publication_id, ':utilisateur_id' => $utilisateur_id])) {
            echo "Erreur lors de la modification de la publication: " . print_r($stmt_update_publication->errorInfo(), true);
        }

        // rediriger l'utilisateur vers la page précédente pour éviter un rechargement de page POST
        header("Location: Profil.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>InstaChat</title>
    <link rel="stylesheet" href="../assets/css/ProfilStyle.css">
    <script src="../assets/js/modificationProfil.js"></script>
</head>
<body>
    <div class="container">
        <div class="profil-info">

            <?php
            // récupération des informations de l'utilisateur
            $stmt_profil = $connexion->prepare("SELECT Nom, Prenom, DateNaissance, Adresse, TelephonePortable, Photo FROM Users WHERE ID = :utilisateur_id");
            $stmt_profil->execute([':utilisateur_id' => $utilisateur_id]);
            $profil = $stmt_profil->fetch(PDO::FETCH_ASSOC);
            
            echo "<div class='profil'>";
            if (!empty($profil['Photo'])) {
                $photo_base64 = $profil['Photo'];
                $finfo = finfo_open();
                $mime_type = finfo_buffer($finfo, base64_decode($photo_base64), FILEINFO_MIME_TYPE);
                finfo_close($finfo);
                $image_src = 'data:' . $mime_type . ';base64,' . $photo_base64;
                echo "<img src='" . $image_src . "' alt='Photo de profil'>";
            }
            echo "<h2>" . htmlspecialchars($profil['Prenom']) . " " . htmlspecialchars($profil['Nom']) . "</h2>";
            echo "<p>Date de naissance : " . htmlspecialchars($profil['DateNaissance']) . "</p>";
            echo "<p>Adresse : " . htmlspecialchars($profil['Adresse']) . "</p>";
            echo "<p>Téléphone portable : " . htmlspecialchars($profil['TelephonePortable']) . "</p>";
            ?>
            <div class="profile-actions">
                <?php
                if (isset($_SESSION['utilisateur_id'])) {
                    // Bouton "Modifier le Profil"
                    echo '<button id="btnModifierProfil" class="btn">Modifier le Profil</button>';
                }
                ?>
            </div>            
        </div>
            

        </div>

        <div id="formModifierProfil" style="display: none;">
            <div class="update-profile-form">
                <h2>Modifier Profil</h2>
                <form method="post" action="" enctype="multipart/form-data">

                    <label for="profile_nom">Nom :</label>
                    <input type="text" name="profile_nom" id="profile_nom" value="<?php echo htmlspecialchars($profil['Nom']); ?>">

                    <label for="profile_prenom">Prénom :</label>
                    <input type="text" name="profile_prenom" id="profile_prenom" value="<?php echo htmlspecialchars($profil['Prenom']); ?>">

                    <button type="submit" class="btn">Mettre à jour</button>
                </form>
            </div>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Mettre à jour les informations du profil (nom et prénom)
            $stmt_update_profil = $connexion->prepare("UPDATE Users SET Nom = :nom, Prenom = :prenom WHERE ID = :utilisateur_id");
            $stmt_update_profil->execute([
                ':nom' => $_POST['profile_nom'],
                ':prenom' => $_POST['profile_prenom'],
                ':utilisateur_id' => $utilisateur_id
            ]);
            header("Location: Profil.php");
            exit();
        }

        if (isset($_SESSION['editing_publication'])) {
            unset($_SESSION['editing_publication']);
        }
        
        // affichage des publications avec les boutons like et dislike
        $stmt_publications = $connexion->prepare("SELECT * FROM Publications WHERE ID_Utilisateur = :utilisateur_id AND Masquer = 0 ORDER BY DatePublication DESC");
        $stmt_publications->execute([':utilisateur_id' => $utilisateur_id]);

        while ($publication = $stmt_publications->fetch(PDO::FETCH_ASSOC)) {
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


            echo "<p>Likes: " . htmlspecialchars($publication['Likes']) . 
                " | Dislikes: " . htmlspecialchars($publication['Dislikes']) . "</p>";
            echo "<form method='post' action='' class='like-dislike-form'>";
            echo "<input type='hidden' name='publication_id' value='" . $publication['ID'] . "'>";
            echo "<button type='submit' name='action' value='like' class='btn'>Like</button>";
            echo "<button type='submit' name='action' value='dislike' class='btn'>Dislike</button>";
            echo "</form>";

            // Formulaire pour ajouter un commentaire
            echo "<form method='post' action='' class='comment-form'>";
            echo "<input type='hidden' name='publication_id' value='" . $publication['ID'] . "'>";
            echo "<label for='comment_content'>Commentaire:</label>";
            echo "<textarea name='comment_content' id='comment_content' class='comment-textarea'></textarea>";
            echo "<button type='submit' class='btn'>Ajouter un commentaire</button>";
            echo "</form>";

            // Formulaire pour modifier la publication
            echo "<form method='post' action='' class='edit-form'>";
            echo "<input type='hidden' name='edit_publication_id' value='" . $publication['ID'] . "'>";
            echo "<button type='submit' name='action' value='edit' class='btn'>Modifier la publication</button>";
            echo "</form>";

            // Formulaire pour supprimer la publication
            echo "<form method='post' action='' class='delete-form'>";
            echo "<input type='hidden' name='delete_publication_id' value='" . $publication['ID'] . "'>";
            echo "<button type='submit' name='action' value='delete' class='btn'>Supprimer la publication</button>";
            echo "</form>";


            // Affichage des commentaires
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

        echo "<a href='../index.php'>Retour</a>";
        ?>

    </div>
</body>
</html>