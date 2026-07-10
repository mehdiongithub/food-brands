<?php

$host="localhost";
$user="root";
$pass="";

$conn=new mysqli($host,$user,$pass);

$conn->query("
CREATE DATABASE IF NOT EXISTS foodscope
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci
");

echo "Database Created Successfully";