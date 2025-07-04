<?php
session_start();
require dirname(__DIR__, 2) . '/config.php';

if (!isset($_SESSION['user_id'])) {
    die("<h1 style='color: red; 
             text-align: center;'>
             Harus login dulu!
         </h1>");
}

$user_id = $_SESSION['user_id'];
$praktikum_id = intval($_GET['id'] ?? 0);

$cek = $conn->prepare("
        SELECT * FROM praktikum_mahasiswa 
        WHERE user_id = ? 
        AND praktikum_id = ?
        ");
$cek->bind_param(
    "ii",
    $user_id,
    $praktikum_id
);
$cek->execute();
$result = $cek->get_result();

if ($result->num_rows === 0) {
    die("<h1 style='color: red; 
             text-align: center;'>
         Anda tidak terdaftar di praktikum ini!
         </h1>");
}

$stmt = $conn->prepare("
        SELECT * FROM praktikum 
        WHERE id = ?
        ");
$stmt->bind_param(
    "i",
    $praktikum_id
);
$stmt->execute();
$praktikum = $stmt->get_result()->fetch_assoc();

$stmtModul = $conn->prepare("
        SELECT * FROM modul_praktikum 
        WHERE praktikum_id = ?
");
$stmtModul->bind_param(
    "i",
    $praktikum_id
);
$stmtModul->execute();
$modulList = $stmtModul->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($praktikum['nama_praktikum']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6">
    <div class="mb-10">
        <a href="/mahasiswa/pages/my_courses.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Daftar Praktikum
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($praktikum['nama_praktikum']) ?></h1>
    <p class="mb-4"><?= htmlspecialchars($praktikum['deskripsi']) ?></p>

    <h2 class="text-xl font-semibold mb-2">Daftar Modul</h2>

    <ul class="space-y-3">
        <?php while ($modul = $modulList->fetch_assoc()): ?>
            <li class="border p-3 rounded" id="modul<?= $modul['id'] ?>">
                <div class="font-semibold"><?= htmlspecialchars($modul['nama_modul']) ?></div>
                <div class="text-sm text-gray-600"><?= htmlspecialchars($modul['deskripsi']) ?></div>

                <!-- Tombol Download Materi -->
                <?php if (!empty($modul['file_materi'])): ?>
                    <a href="/uploads/modul_praktikum/<?= htmlspecialchars($modul['file_materi']) ?>" download
                        class="inline-block mt-2 bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                        Unduh Materi
                    </a>
                <?php else: ?>
                    <p class="text-red-500 text-sm mt-2">Materi belum tersedia</p>
                <?php endif; ?>

                <!-- Cek laporan mahasiswa -->
                <?php
                $cekLaporan = $conn->prepare("
                SELECT * FROM laporan_mahasiswa 
                WHERE user_id = ? 
                AND modul_id = ?
                ORDER BY tanggal_upload DESC
                ");
                $cekLaporan->bind_param("ii", $user_id, $modul['id']);
                $cekLaporan->execute();
                $laporanResult = $cekLaporan->get_result();

                $laporanList = [];
                $sudahDinilai = false;

                while ($laporan = $laporanResult->fetch_assoc()) {
                    $laporanList[] = $laporan;
                    if (!is_null($laporan['nilai'])) {
                        $sudahDinilai = true;
                    }
                }
                ?>

                <!-- Status Laporan -->
                <?php if (!empty($laporanList)): ?>
                    <div class="mt-2 text-sm">
                        <?php foreach ($laporanList as $laporan): ?>
                            ✅ Laporan dikumpulkan:
                            <a href="/uploads/laporan/<?= htmlspecialchars($laporan['file_laporan']) ?>" target="_blank"
                                class="text-blue-600 underline">
                                <?= htmlspecialchars($laporan['file_laporan']) ?>
                            </a><br>
                            <?php if (!is_null($laporan['nilai'])): ?>
                                🎯 Nilai: <?= htmlspecialchars($laporan['nilai']) ?><br>
                                💬 Feedback: <?= htmlspecialchars($laporan['feedback']) ?><br><br>
                            <?php else: ?>
                                ⏳ Menunggu penilaian<br><br>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-sm mt-2">Belum ada laporan dikumpulkan.</p>
                <?php endif; ?>



                <!-- Pesan Sukses/Error -->
                <?php
                $successKey = 'success_' . $modul['id'];
                $errorKey = 'error_' . $modul['id'];
                ?>

                <?php if (!empty($_SESSION[$successKey])): ?>
                    <div class="bg-green-100 text-green-800 p-3 mt-2 mb-4 rounded">
                        <?= htmlspecialchars($_SESSION[$successKey]);
                        unset($_SESSION[$successKey]); ?>
                    </div>
                <?php elseif (!empty($_SESSION[$errorKey])): ?>
                    <div class="bg-red-100 text-red-800 p-3 mb-4 rounded">
                        <?= htmlspecialchars($_SESSION[$errorKey]);
                        unset($_SESSION[$errorKey]); ?>
                    </div>
                <?php endif; ?>

                <!-- Form Upload -->
                <?php if ($sudahDinilai): ?>
                    <p class="text-green-600 mt-3">✅ Laporan sudah dinilai, tidak bisa upload.</p>
                <?php else: ?>
                    <form action="/mahasiswa/server/upload_laporan.php" method="POST" enctype="multipart/form-data"
                        class="mt-3">
                        <input type="hidden" name="modul_id" value="<?= $modul['id'] ?>">
                        <input type="file" name="laporan" required class="block mb-2">
                        <button type="submit" class="bg-purple-500 text-white px-3 py-1 rounded hover:bg-purple-600">
                            Upload Laporan
                        </button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
    </ul>
</body>

</html>