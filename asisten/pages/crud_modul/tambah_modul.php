<?php
session_start();
require dirname(__DIR__, 3) . '/config.php';

// Ambil daftar praktikum untuk dropdown
$praktikumList = $conn->query("SELECT id, nama_praktikum FROM praktikum");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $praktikum_id = intval($_POST['praktikum_id']);
    $nama = trim($_POST['nama_modul']);
    $deskripsi = trim($_POST['deskripsi']);

    $file_materi = null;
    if (!empty($_FILES['file_materi']['name'])) {
        $target_dir = dirname(__DIR__, 3) . "/uploads/modul_praktikum/";
        $file_materi = time() . "_" . basename($_FILES["file_materi"]["name"]);
        $target_path = $target_dir . $file_materi;

        if (!move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_path)) {
            die("<h1 style='color: red; text-align: center;'>Gagal upload file materi!</h1>");
        }
    }

    $stmt = $conn->prepare("
            INSERT INTO modul_praktikum 
            (praktikum_id, nama_modul, deskripsi, file_materi) 
            VALUES (?, ?, ?, ?)
            ");
    $stmt->bind_param(
        "isss",
        $praktikum_id,
        $nama,
        $deskripsi,
        $file_materi
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Modul berhasil ditambahkan.";
        header("Location: /asisten/pages/crud_modul/kelola_modul.php?praktikum_id=$praktikum_id");
        exit;
    } else {
        die("<h1 style='color: red; text-align: center;'>Gagal tambah modul!</h1>");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tambah Modul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6">
    <div class="mb-10">
        <a href="/asisten/pages/crud_modul/kelola_modul.php"
            class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Daftar Modul
        </a>
    </div>
    <h1 class="text-xl font-bold mb-4">Tambah Modul</h1>
    <form method="POST" enctype="multipart/form-data" class="space-y-3">
        <div>
            <label>Pilih Praktikum:</label>
            <select name="praktikum_id" required class="border px-2 py-1 rounded w-full">
                <option value="">-- Pilih Praktikum --</option>
                <?php while ($row = $praktikumList->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= (isset($_GET['praktikum_id']) && $_GET['praktikum_id'] == $row['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nama_praktikum']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label>Nama Modul:</label>
            <input type="text" name="nama_modul" required class="border px-2 py-1 rounded w-full">
        </div>
        <div>
            <label>Deskripsi:</label>
            <textarea name="deskripsi" class="border px-2 py-1 rounded w-full"></textarea>
        </div>
        <div>
            <label>Upload File Materi:</label>
            <input type="file" name="file_materi" class="block">
        </div>
        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Simpan</button>
    </form>
</body>

</html>