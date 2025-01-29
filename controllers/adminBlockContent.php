<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Session Admin</title>
    <link rel="stylesheet" href="../assets/css/admin/AdminBlockContentStyle.css">
    <script src="../assets/js/masquer.js"></script>
</head>
<body>
    <div class="container">
        
        <h1>Masquer une Publication</h1>

        <?php
        require 'ConnexionBD.php';

        // selection de toutes les publications
        $publicationsQuery = "SELECT * FROM Publications ORDER BY DatePublication DESC";
        $publicationsStmt = $connexion->prepare($publicationsQuery);
        $publicationsStmt->execute();
        $publications = $publicationsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($publications)): ?>
        <div class="publications">
            <?php foreach ($publications as $publication): ?>
                <div class="publication">
                    <h3><?php echo htmlspecialchars($publication['Titre']); ?></h3>
                    <p><?php echo htmlspecialchars($publication['Contenu']); ?></p>
                    <?php if (!empty($publication['Photo'])): 
                        $photo_base64 = $publication['Photo'];
                        $finfo = finfo_open();
                        $mime_type = finfo_buffer($finfo, base64_decode($photo_base64), FILEINFO_MIME_TYPE);
                        finfo_close($finfo);
                        $image_src = 'data:' . $mime_type . ';base64,' . $photo_base64;
                        $image_src_safe = htmlspecialchars($image_src);
                    ?>
                        <img src="<?php echo $image_src_safe; ?>" alt="Image de la publication" type="<?php echo $mime_type; ?>">
                    <?php endif; ?>
                    <p>Date de publication: <?php echo htmlspecialchars($publication['DatePublication']); ?></p>
                    <form action="" method="post">
                        <input type="hidden" name="publication_id" value="<?php echo $publication['ID']; ?>">
                        <input type="submit" name="toggle_visibility" value="<?php echo ($publication['Masquer'] == 0) ? 'Masquer' : 'Rendre Visible'; ?>">
                    </form>
                    <?php
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }       
                                 
                    require 'ConnexionBD.php';

                    if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
                        header("Location: index.php");
                        exit();
                    }

                    if (isset($_POST['toggle_visibility']) && isset($_POST['publication_id'])) {
                        $publicationId = $_POST['publication_id'];

                        // état actuel de la colonne 'Masquer' de la publication est récupérer
                        $getMasquerQuery = "SELECT Masquer FROM Publications WHERE ID = :publication_id";
                        $getMasquerStmt = $connexion->prepare($getMasquerQuery);
                        $getMasquerStmt->bindParam(':publication_id', $publicationId, PDO::PARAM_INT);
                        $getMasquerStmt->execute();
                        $result = $getMasquerStmt->fetch(PDO::FETCH_ASSOC);

                        if ($result) {
                            // invertion de la valeur de la colonne 'Masquer'
                            $newMasquerValue = $result['Masquer'] ? 0 : 1;

                            // Mettez à jour la colonne 'Masquer' dans la base de données
                            $updateMasquerQuery = "UPDATE Publications SET Masquer = :masquer WHERE ID = :publication_id";
                            $updateMasquerStmt = $connexion->prepare($updateMasquerQuery);
                            $updateMasquerStmt->bindParam(':masquer', $newMasquerValue, PDO::PARAM_INT);
                            $updateMasquerStmt->bindParam(':publication_id', $publicationId, PDO::PARAM_INT);

                            if ($updateMasquerStmt->execute()) {
                                // redirection de l'utilisateur vers la même page après la mise à jour
                                header("Location: adminBlockContent.php");
                                exit();
                            } else {
                                echo "Erreur lors de la mise à jour de la publication.";
                            }
                        } else {
                            echo "Publication non trouvée.";
                        }
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p>Aucune publication trouvée.</p>
        <?php endif; ?>
        <a href="../adminDashboard.php">Retour</a>
    </div>
</body>
</html>
