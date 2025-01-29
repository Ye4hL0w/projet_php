<?php
    $servername = "localhost";
    $port = "3306";
    $username = "dg202433";
    $password = "dg202433";
    $dbname = "dg202433_phpprojet";

    //Connexion à la base de données
    try{
        $connexion= new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8", $username, $password);
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e){
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
        exit;
    }
?>