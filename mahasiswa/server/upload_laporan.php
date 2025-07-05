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
$modul_id = intval($_POST['modul_id'] ?? 0);

if (!isset($_FILES['laporan']) || $_FILES['laporan']['error'] !== UPLOAD_ERR_OK) {
    die("<h1 style='color: red; 
             text-align: center;'>
             Upload gagal, file error!
         </h1>");
}

$target_dir = dirname(__DIR__, 2) . "/uploads/laporan_praktikum/";
$filename = time() . "_" . basename($_FILES["laporan"]["name"]);
$target_file = $target_dir . $filename;

if (!move_uploaded_file($_FILES["laporan"]["tmp_name"], $target_file)) {
    die("<h1 style='color: red; 
             text-align: center;'>
             Gagal menyimpan file laporan!
         </h1>");
}

$stmt = $conn->prepare("
        INSERT INTO laporan_mahasiswa 
        (user_id, modul_id, file_laporan) 
        VALUES (?, ?, ?)");
$stmt->bind_param(
    "iis",
    $user_id,
    $modul_id,
    $filename
);

if ($stmt->execute()) {
    $stmt = $conn->prepare("
    SELECT praktikum_id 
    FROM modul_praktikum WHERE id = ?
    ");
    $stmt->bind_param(
        "i",
        $modul_id
    );
    $stmt->execute();
    $praktikumResult = $stmt->get_result()->fetch_assoc();

    $praktikum_id = $praktikumResult['praktikum_id'] ?? 0;

    $_SESSION['success_' . $modul_id] = "Laporan berhasil diupload!";

    header("Location: /mahasiswa/pages/detail_praktikum.php?id=$praktikum_id#modul$modul_id");
    exit;
} else {
    $_SESSION['error_' . $modul_id] = "Gagal upload laporan: " . $conn->error;
    header("Location: /mahasiswa/pages/detail_praktikum.php?id=$praktikum_id#modul$modul_id");
    exit;
}
