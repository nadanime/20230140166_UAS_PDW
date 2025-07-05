<?php
session_start();
require dirname(__DIR__, 3) . '/config.php';

if ($_SESSION['role'] !== 'asisten') {
    die("<h1 style='color: red; 
            text-align: center;'>
            Akses hanya untuk asisten!
            </h1>");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Akses tidak valid!";
    header("Location: /asisten/pages/crud_modul/kelola_modul.php?praktikum_id=" . intval($_POST['praktikum_id'] ?? 0));
    exit;
}

$id = intval($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';

$user_id = $_SESSION['user_id'] ?? 0;
$stmt = $conn->prepare("
        SELECT password 
        FROM users 
        WHERE id = ?"
        );
$stmt->bind_param(
    "i", 
    $user_id
);
$stmt->execute();
$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();

if (!password_verify($password, $hash)) {
    $_SESSION['error'] = "Password salah. Modul tidak dihapus.";
    header("Location: /asisten/pages/crud_modul/kelola_modul.php?praktikum_id=" . intval($_POST['praktikum_id'] ?? 0));
    exit;
}

$stmt = $conn->prepare("
        DELETE FROM modul_praktikum 
        WHERE id = ?
        ");
$stmt->bind_param(
    "i", 
    $id
);

if ($stmt->execute()) {
    $_SESSION['success'] = "Modul berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus modul!";
}

header("Location: /asisten/pages/crud_modul/kelola_modul.php?praktikum_id=" . intval($_POST['praktikum_id'] ?? 0));
exit;
