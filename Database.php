<?php

class Database {
    private static ?PDO $connection = null;

    public function __construct($config, $username = "root", $password = "") {

        if (self::$connection == null) {
            $dsn = "mysql:" . http_build_query($config, "", ";");

            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
    
            $this -> query("
                CREATE TABLE IF NOT EXISTS PHP_Users (
                    ID INT AUTO_INCREMENT PRIMARY KEY,
                    Username VARCHAR(20) UNIQUE NOT NULL,
                    Password VARCHAR(255)
                );

                CREATE TABLE IF NOT EXISTS Tokens (
                    Token VARCHAR(255) PRIMARY KEY,
                    User_ID INT NOT NULL,
                    Expires DATETIME NOT NULL,
                    FOREIGN KEY (User_ID) REFERENCES PHP_Users (ID) ON DELETE CASCADE
                );
            ");
        }
    }

    public function getLastInsertID() {
        return self::$connection -> lastInsertId();
    }

    public function query($query, $params = []) {
        $statement = self::$connection -> prepare($query);
        $statement -> execute($params);

        return $statement;
    }
}