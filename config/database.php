<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "foodscope";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e){

    die("Connection Failed : ".$e->getMessage());

}