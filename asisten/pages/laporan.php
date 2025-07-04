<?php
session_start();
require dirname(__DIR__, 2) . '/config.php';

// Cek role, pastikan asisten
if ($_SESSION['role'] !== 'asisten') {
    die("Akses hanya untuk asisten! Hmph.");
}

$modulList = $conn->query("SELECT id, nama_modul FROM modul_praktikum");

$mahasiswaList = $conn->query("SELECT id, nama FROM users WHERE role = 'mahasiswa'");


// Ambil filter kalau ada
$filter_modul = intval($_GET['modul'] ?? 0);
$filter_user = intval($_GET['mahasiswa'] ?? 0);
$filter_status = $_GET['status'] ?? '';

$sql = "SELECT 
            l.id, 
            l.file_laporan, 
            l.nilai, 
            l.feedback, 
            l.tanggal_upload,
            u.nama AS nama_mahasiswa,
            m.nama_modul
        FROM laporan_mahasiswa l
        JOIN users u ON l.user_id = u.id
        JOIN modul_praktikum m ON l.modul_id = m.id
        WHERE 1=1";

$params = [];
$types = '';

if ($filter_modul) {
    $sql .= " AND l.modul_id = ?";
    $params[] = $filter_modul;
    $types .= 'i';
}
if ($filter_user) {
    $sql .= " AND l.user_id = ?";
    $params[] = $filter_user;
    $types .= 'i';
}
if ($filter_status === 'belum') {
    $sql .= " AND l.nilai IS NULL";
} elseif ($filter_status === 'sudah') {
    $sql .= " AND l.nilai IS NOT NULL";
}

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param(
        $types, 
        ...$params
    );
}

$stmt->execute();
$laporan = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Masuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6">
    <div class="mb-10">
        <a href="/asisten/dashboard.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Dashboard
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-4">Daftar Laporan Masuk</h1>

    <form method="GET" class="mb-4 space-y-2 md:space-y-0 md:flex md:space-x-4">
        <div>
            <label class="block text-sm font-medium">Filter Modul:</label>
            <select name="modul" class="border rounded px-2 py-1">
                <option value="">Semua</option>
                <?php while ($modul = $modulList->fetch_assoc()): ?>
                <option value="<?= $modul['id'] ?>" <?= ($filter_modul == $modul['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($modul['nama_modul']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">Filter Mahasiswa:</label>
            <select name="mahasiswa" class="border rounded px-2 py-1">
                <option value="">Semua</option>
                <?php while ($mhs = $mahasiswaList->fetch_assoc()): ?>
                <option value="<?= $mhs['id'] ?>" <?= ($filter_user == $mhs['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mhs['nama']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">Status Nilai:</label>
            <select name="status" class="border rounded px-2 py-1">
                <option value="">Semua</option>
                <option value="belum" <?= ($filter_status == 'belum') ? 'selected' : '' ?>>Belum Dinilai</option>
                <option value="sudah" <?= ($filter_status == 'sudah') ? 'selected' : '' ?>>Sudah Dinilai</option>
            </select>
        </div>

        <div class="self-end">
            <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Terapkan</button>
        </div>
    </form>

    <table class="table-auto w-full border border-gray-400 border-collapse divide-y divide-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 border border-gray-400">Mahasiswa</th>
                <th class="p-2 border border-gray-400">Modul</th>
                <th class="p-2 border border-gray-400">File</th>
                <th class="p-2 border border-gray-400">Nilai</th>
                <th class="p-2 border border-gray-400">Feedback</th>
                <th class="p-2 border border-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $laporan->fetch_assoc()): ?>
            <tr>
                <td class="p-2 border border-gray-400"><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                <td class="p-2 border border-gray-400"><?= htmlspecialchars($row['nama_modul']) ?></td>
                <td class="p-2 border border-gray-400">
                    <a href="/uploads/laporan/<?= htmlspecialchars($row['file_laporan']) ?>" download
                        class="text-blue-600 hover:underline">Unduh</a>
                </td>
                <td class="p-2 border border-gray-400">
                    <?= is_null($row['nilai']) ? 'Belum Dinilai' : htmlspecialchars($row['nilai']) ?></td>
                <td class="p-2 border border-gray-400">
                    <?= !empty($row['feedback']) ? htmlspecialchars($row['feedback']) : '<span class="text-gray-400">-</span>' ?>
                </td>
                <td class="p-2 border border-gray-400">
                    <a href="/asisten/pages/beri_nilai.php?id=<?= $row['id'] ?>"
                        class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500">Nilai</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>