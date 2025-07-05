<?php
session_start();
require dirname(__DIR__, 3) . '/config.php';

if ($_SESSION['role'] !== 'asisten') {
    die("<h1 style='color: red; 
        text-align: center;'>
        Akses hanya untuk asisten!
        </h1>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("
            INSERT INTO users 
            (nama, email, password, role) 
            VALUES (?, ?, ?, ?)
            ");
    $stmt->bind_param(
        "ssss", 
        $nama, 
        $email, 
        $password, 
        $role
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Pengguna berhasil ditambahkan.";
        header("Location: /asisten/pages/crud_user/kelola_user.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan pengguna: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
    <div class="mb-10">
        <a href="/asisten/pages/crud_user/kelola_user.php"
            class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Daftar Pengguna
        </a>
    </div>
    <h1 class="text-xl font-bold mb-4">Tambah Pengguna</h1>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="text-red-600 mb-4"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-3 max-w-md">
        <div>
            <label>Nama:</label>
            <input type="text" name="nama" required class="border px-2 py-1 rounded w-full">
        </div>
        <div>
            <label>Email:</label>
            <input type="email" name="email" required class="border px-2 py-1 rounded w-full">
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password" required class="border px-2 py-1 rounded w-full">
        </div>
        <div>
            <label>Role:</label>
            <select name="role" class="border px-2 py-1 rounded w-full">
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
        </div>
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Simpan</button>
    </form>
</body>
</html>