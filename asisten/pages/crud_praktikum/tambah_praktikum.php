<?php
session_start();
require dirname(__DIR__, 3) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_praktikum']);
    $deskripsi = trim($_POST['deskripsi']);
    $semester = trim($_POST['semester']);

    $stmt = $conn->prepare("
            INSERT INTO praktikum 
            (nama_praktikum, deskripsi, semester) 
            VALUES (?, ?, ?)
            ");
    $stmt->bind_param(
        "sss", 
        $nama, 
        $deskripsi, 
        $semester
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Praktikum berhasil ditambahkan.";
        header("Location: /asisten/pages/crud_praktikum/kelola_praktikum.php");
        exit;
    } else {
        die("<h1 style='color: red; 
            text-align: center;'>
            Gagal tambah praktikum!
         </h1>");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
    <div class="mb-10">
        <a href="/asisten/pages/crud_praktikum/kelola_praktikum.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Daftar Praktikum
        </a>
    </div>
    <h1 class="text-xl font-bold mb-4">Tambah Praktikum</h1>
    
    <form method="POST" class="space-y-3">
        <div>
            <label>Nama Praktikum:</label>
            <input type="text" name="nama_praktikum" required class="border px-2 py-1 rounded w-full">
        </div>
        <div>
            <label>Deskripsi:</label>
            <textarea name="deskripsi" class="border px-2 py-1 rounded w-full"></textarea>
        </div>
        <div>
            <label>Semester:</label>
            <input type="text" name="semester" required class="border px-2 py-1 rounded">
        </div>
        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Simpan</button>
    </form>
</body>

</html>