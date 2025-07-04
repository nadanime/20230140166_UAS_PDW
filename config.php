<?php

require_once __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Pengaturan Database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'nada'); 
define('DB_PASSWORD', $_ENV['DB_PASS']); 
define('DB_NAME', 'simprak');

// Membuat koneksi ke database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("<h1 style='color: red; 
             text-align: center;'>
             Koneksi ke database gagal: " . $conn->connect_error . "
         </h1>");
}
?>