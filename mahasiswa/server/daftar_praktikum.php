<?php
session_start();
require dirname(__DIR__, 2) . '/config.php';

if (!isset($_SESSION['user_id'])) {
    die("<h1 style='color: red; 
             text-align: center;'>
             Harus login dulu!
         </h1>");
}

$user_id = $_SESSION['user_id'];
$praktikum_id = intval($_POST['praktikum_id'] ?? 0);

$cek = $conn->prepare("
SELECT * FROM praktikum_mahasiswa 
WHERE user_id = ? AND praktikum_id = ?
");
$cek->bind_param(
    "ii", 
    $user_id, 
    $praktikum_id
);
$cek->execute();
$cek_result = $cek->get_result();

if ($cek_result->num_rows > 0) {
    $_SESSION['sudah_daftar'] = "Kamu sudah terdaftar di praktikum ini!";
    header("Location: /mahasiswa/pages/courses.php");
    exit;
}

$stmt = $conn->prepare("
INSERT INTO praktikum_mahasiswa 
(user_id, praktikum_id) VALUES (?, ?)
");
$stmt->bind_param(
    "ii", 
    $user_id, 
    $praktikum_id
);

if ($stmt->execute()) {
    $_SESSION['success'] = "Berhasil daftar!";
    header("Location: /mahasiswa/pages/courses.php");
    exit;
} else {
    die("<h1 style='color: red; 
             text-align: center;'>Gagal mendaftar: " . $conn->error . "
         </h1>");
}
