<?php

function getPDOConnection() {
    static $pdo = null; // Static variable to retain the PDO instance

    if ($pdo === null) {
        try {
            $dbConfig = parse_ini_file("config/db.ini");
            $dsn = $dbConfig['dsn'];
            $user = $dbConfig['user'];
            $password = $dbConfig['password'];
        
            $pdo = new PDO($dsn, $user, $password);
        
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}