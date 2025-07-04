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
    header("Location: /asisten/pages/crud_praktikum/kelola_praktikum.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';

$user_id = $_SESSION['user_id'] ?? 0;
$stmt = $conn->prepare("
        SELECT password FROM users 
        WHERE id = ?
        ");
$stmt->bind_param(
    "i", 
    $user_id
);
$stmt->execute();
$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();

if (!password_verify($password, $hash)) {
    $_SESSION['error'] = "Password salah. Praktikum tidak dihapus.";
    header("Location: /asisten/pages/crud_praktikum/kelola_praktikum.php");
    exit;
}

$stmt = $conn->prepare("
        DELETE FROM praktikum 
        WHERE id = ?"
        );
$stmt->bind_param(
    "i", 
    $id
);

if ($stmt->execute()) {
    $_SESSION['success'] = "Praktikum berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus praktikum!";
}

header("Location: /asisten/pages/crud_praktikum/kelola_praktikum.php");
exit;
?>