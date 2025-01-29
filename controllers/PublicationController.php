<?php
    
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
        header("Location: Profil.php"); // Rediriger vers la page de profil après la modification
        exit();
    }

    // traitement des commentaires
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publication_id'], $_POST['comment_content'])) {
        $publication_id = $_POST['publication_id'];
        $comment_content = trim($_POST['comment_content']); // Trim pour supprimer les espaces en début et fin de chaîne

        // Vérifier si le contenu du commentaire n'est pas vide
        if (!empty($comment_content)) {
            // enregistrer le commentaire dans la table Commentaires
            $stmt_record_comment = $connexion->prepare("INSERT INTO Commentaires (ID_Utilisateur, ID_Publication, Contenu, DateCommentaire) VALUES (:utilisateur_id, :publication_id, :comment_content, NOW())");
            $stmt_record_comment->execute([':utilisateur_id' => $utilisateur_id, ':publication_id' => $publication_id, ':comment_content' => $comment_content]);

            // rediriger l'utilisateur vers la page précédente pour éviter un rechargement de page POST
            header("Location: Profil.php"); // Rediriger vers la page de profil après l'ajout du commentaire
            exit();
        } else {
            echo "Erreur: Le contenu du commentaire ne peut pas être vide.";
            // Vous pouvez également rediriger l'utilisateur vers la page précédente ici s'il y a une erreur
        }
    }
?>
