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
    header("Location: /asisten/pages/crud_user/kelola_user.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("
        SELECT password 
        FROM users 
        WHERE id = ?
        ");
$stmt->bind_param(
    "i", 
    $id
);
$stmt->execute();
$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();

if (!password_verify($password, $hash)) {
    $_SESSION['error'] = "Password salah. Pengguna tidak dihapus.";
    header("Location: /asisten/pages/crud_user/kelola_user.php");
    exit;
}

$stmt = $conn->prepare("
        DELETE FROM users 
        WHERE id = ?
        ");
$stmt->bind_param(
    "i", 
    $id
);

if ($stmt->execute()) {
    $_SESSION['success'] = "Pengguna berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus pengguna!";
}

header("Location: /asisten/pages/crud_user/kelola_user.php");
exit;