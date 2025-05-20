<?php

class DBConnection {

    public static function connect(): mysqli {
        $host = "localhost";
        $username = "root";
        $password = "";
        $dbname = "news_system";
        $port = 3306; 

        $conn = new mysqli($host, $username, $password, $dbname, $port);

        if ($conn->connect_error) {
            die("Connection error: " . $conn->connect_error);
        }

        return $conn;
    }
}