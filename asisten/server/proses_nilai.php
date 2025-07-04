<?php
session_start();
require dirname(__DIR__, 2) . '/config.php';

if ($_SESSION['role'] !== 'asisten') {
    die("<h1 style='color: red; 
            text-align: center;'>
            Akses hanya untuk asisten!
         </h1>");
}

$laporan_id = intval($_POST['laporan_id'] ?? 0);
$nilai = intval($_POST['nilai'] ?? 0);
$feedback = trim($_POST['feedback'] ?? '');

$stmt = $conn->prepare("
        UPDATE laporan_mahasiswa 
        SET nilai = ?, feedback = ? 
        WHERE id = ?
        ");
$stmt->bind_param(
    "isi", 
    $nilai, 
    $feedback, 
    $laporan_id
);

if ($stmt->execute()) {
    $_SESSION['success'] = "Nilai berhasil disimpan.";
    header("Location: /asisten/pages/laporan.php");
    exit;
} else {
    $_SESSION['error'] = "Gagal menyimpan nilai.";
    header("Location: /asisten/pages/nilai.php?id=" . $laporan_id);
    exit;
}