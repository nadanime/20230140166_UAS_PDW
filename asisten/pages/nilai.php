<?php
session_start();
require dirname(__DIR__, 2) . '/config.php';

if ($_SESSION['role'] !== 'asisten') {
    die("<h1 style='color: red; 
            text-align: center;'>
            Akses hanya untuk asisten!
         </h1>");
}

$laporan_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("
        SELECT 
            l.*, 
            u.nama AS nama_mahasiswa, 
            m.nama_modul
        FROM laporan_mahasiswa l
        JOIN users u ON l.user_id = u.id
        JOIN modul_praktikum m ON l.modul_id = m.id
        WHERE l.id = ?
        ");
$stmt->bind_param(
    "i", 
    $laporan_id
);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_assoc();

if (!$laporan) {
    die("<h1 style='color: red; 
            text-align: center;'>
            Laporan tidak ditemukan!
         </h1>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Beri Nilai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
    <h1 class="text-xl font-bold mb-4">Nilai Laporan</h1>

    <p><strong>Mahasiswa:</strong> <?= htmlspecialchars($laporan['nama_mahasiswa']) ?></p>
    <p><strong>Modul:</strong> <?= htmlspecialchars($laporan['nama_modul']) ?></p>
    <p><strong>File:</strong> <a href="/uploads/laporan_praktikum/<?= htmlspecialchars($laporan['file_laporan']) ?>" download class="text-blue-600 hover:underline">Unduh Laporan</a></p>

    <form action="/asisten/server/proses_nilai.php" method="POST" class="mt-4 space-y-3">
        <input type="hidden" name="laporan_id" value="<?= $laporan_id ?>">

        <div>
            <label class="block text-sm">Nilai (0 - 100):</label>
            <input type="number" name="nilai" required min="0" max="100" class="border px-2 py-1 rounded" value="<?= htmlspecialchars($laporan['nilai']) ?>">
        </div>

        <div>
            <label class="block text-sm">Feedback:</label>
            <textarea name="feedback" class="border px-2 py-1 rounded w-full" rows="4"><?= htmlspecialchars($laporan['feedback']) ?></textarea>
        </div>

        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Simpan Nilai</button>
    </form>
</body>
</html>
