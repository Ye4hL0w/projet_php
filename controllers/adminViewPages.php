<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Session Admin</title>
    <link rel="stylesheet" href="../assets/css/admin/AdminViewPagesStyle.css">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <div class="container">
        <?php
        session_start();
        require './ConnexionBD.php';

        if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
            header("Location: index.php");
            exit();
        }

        $publications = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $startDate = $_POST['startDate'] ?? '';
            $endDate = $_POST['endDate'] ?? '';

            if ($startDate && $endDate) {
                $publicationsQuery = "SELECT * FROM Publications WHERE DatePublication BETWEEN :startDate AND :endDate ORDER BY DatePublication DESC";
                $publicationsStmt = $connexion->prepare($publicationsQuery);
                $publicationsStmt->bindParam(':startDate', $startDate);
                $publicationsStmt->bindParam(':endDate', $endDate);
                $publicationsStmt->execute();
                $publications = $publicationsStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        ?>

        <h1>Visualisation des publications par tranche de date</h1>
        <form method="post" action="">
            <label for="startDate">Date de début :</label>
            <input type="date" id="startDate" name="startDate" required>

            <label for="endDate">Date de fin :</label>
            <input type="date" id="endDate" name="endDate" required>

            <input type="submit" value="Voir les publications">
        </form>

        <?php if (!empty($publications)): ?>
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
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Aucune publication trouvée pour la période sélectionnée.</p>
    <?php endif; ?>
        <a href="../adminDashboard.php">Retour</a>
    </div>
</body>
</html>
