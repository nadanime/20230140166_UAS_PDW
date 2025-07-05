<?php
session_start();
require dirname(__DIR__, 3) . '/config.php';

if ($_SESSION['role'] !== 'asisten') {
    die("<h1 style='color: red; 
        text-align: center;'>
        Akses hanya untuk asisten!
        </h1>");
}

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("
        SELECT * FROM modul_praktikum 
        WHERE id = ?
        ");
$stmt->bind_param(
    "i", 
    $id
);
$stmt->execute();
$result = $stmt->get_result();
$modul = $result->fetch_assoc();

if (!$modul) {
    $_SESSION['error'] = "Modul tidak ditemukan!";
    header("Location: /asisten/pages/crud_modul/kelola_modul.php");
    exit;
}

$praktikumList = $conn->query("SELECT id, nama_praktikum FROM praktikum");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $praktikum_id = intval($_POST['praktikum_id']);
    $nama_modul = trim($_POST['nama_modul']);
    $deskripsi = trim($_POST['deskripsi']);

    $file_materi = $modul['file_materi'];

    if (!empty($_FILES['file_materi']['name'])) {
        $target_dir = dirname(__DIR__, 3) . "/uploads/modul_praktikum/";
        $file_materi = time() . "_" . basename($_FILES["file_materi"]["name"]);
        $target_path = $target_dir . $file_materi;

        if (!move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_path)) {
            $_SESSION['error'] = "Gagal upload file materi!";
            header("Location: /asisten/pages/crud_modul/edit_modul.php?id=$id&praktikum_id=$praktikum_id");
            exit;
        }
    }

    $stmt = $conn->prepare("
    UPDATE modul_praktikum 
    SET praktikum_id = ?, 
        nama_modul = ?, 
        deskripsi = ?, 
        file_materi = ? 
    WHERE id = ?
    ");
    $stmt->bind_param(
        "isssi", 
        $praktikum_id,
        $nama_modul, 
        $deskripsi, 
        $file_materi, 
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Modul berhasil diperbarui.";
        header("Location: /asisten/pages/crud_modul/kelola_modul.php?praktikum_id=$praktikum_id");
        exit;
    } else {
        $_SESSION['error'] = "Gagal update modul!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Modul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6">
    <div class="mb-6">
        <a href="/asisten/pages/crud_modul/kelola_modul.php?praktikum_id=<?= $modul['praktikum_id'] ?>" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Daftar Modul
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-4">Edit Modul</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="text-red-600 mb-4"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-3">
        <div>
            <label>Pilih Praktikum:</label>
            <select name="praktikum_id" required class="border px-2 py-1 rounded w-full">
                <option value="">-- Pilih Praktikum --</option>
                <?php foreach ($praktikumList as $row): ?>
                    <option value="<?= $row['id'] ?>" <?= ($modul['praktikum_id'] == $row['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nama_praktikum']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Nama Modul:</label>
            <input type="text" name="nama_modul" value="<?= htmlspecialchars($modul['nama_modul']) ?>" required class="border px-2 py-1 rounded w-full">
        </div>

        <div>
            <label>Deskripsi:</label>
            <textarea name="deskripsi" class="border px-2 py-1 rounded w-full"><?= htmlspecialchars($modul['deskripsi']) ?></textarea>
        </div>

        <div>
            <label>File Materi (Kosongkan jika tidak diubah):</label>
            <input type="file" name="file_materi" class="block">
            <?php if ($modul['file_materi']): ?>
                <p class="text-sm text-gray-500 mt-1">File saat ini: <a href="/uploads/modul_praktikum/<?= htmlspecialchars($modul['file_materi']) ?>" download class="text-blue-600 hover:underline">Download</a></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Simpan Perubahan</button>
    </form>
</body>

</html>
