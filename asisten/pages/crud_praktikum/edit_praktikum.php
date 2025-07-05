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
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama_praktikum']);
    $deskripsi = trim($_POST['deskripsi']);
    $semester = trim($_POST['semester']);

    $stmt = $conn->prepare("
            UPDATE praktikum 
            SET nama_praktikum = ?, 
            deskripsi = ?, 
            semester = ? 
            WHERE id = ?
            ");
    $stmt->bind_param(
        "sssi",
        $nama,
        $deskripsi,
        $semester,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Praktikum berhasil diperbarui.";
        header("Location: /asisten/pages/crud_praktikum/kelola_praktikum.php");
        exit;
    } else {
        die("<h1 style='color: red; 
            text-align: center;'>
            Gagal update praktikum!
        </h1>");
    }
}


$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM praktikum WHERE id = ?");
$stmt->bind_param(
    "i",
    $id
);
$stmt->execute();
$result = $stmt->get_result();
$praktikum = $result->fetch_assoc();

if (!$praktikum) {
    die("<h1 style='color: red; 
            text-align: center;'>
            Data praktikum tidak ditemukan!
        </h1>");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6">
    <div class="mb-10">
        <a href="/asisten/pages/crud_praktikum/kelola_praktikum.php"
            class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Daftar Praktikum
        </a>
    </div>
    <h1 class="text-xl font-bold mb-4">Edit Praktikum</h1>

    <form method="POST" class="space-y-3">
        <input type="hidden" name="id" value="<?= htmlspecialchars($praktikum['id']) ?>">

        <div>
            <label>Nama Praktikum:</label>
            <input type="text" name="nama_praktikum" required class="border px-2 py-1 rounded w-full"
                value="<?= htmlspecialchars($praktikum['nama_praktikum']) ?>">
        </div>

        <div>
            <label>Deskripsi:</label>
            <textarea name="deskripsi"
                class="border px-2 py-1 rounded w-full"><?= htmlspecialchars($praktikum['deskripsi']) ?></textarea>
        </div>

        <div>
            <label>Semester:</label>
            <input type="text" name="semester" required class="border px-2 py-1 rounded"
                value="<?= htmlspecialchars($praktikum['semester']) ?>">
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Simpan
            Perubahan</button>
    </form>
</body>

</html>