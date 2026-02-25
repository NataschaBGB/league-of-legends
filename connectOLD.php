<?php 

    try {
        $dbh = new PDO('mysql:host=localhost;dbname=league_of_legends', 'root', 'root');
    }
    catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }