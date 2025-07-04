<?php
session_start();
require dirname(__DIR__, 2) . '/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Harus login dulu!");
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
    <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($praktikum['nama_praktikum']) ?></h1>
    <p class="mb-4"><?= htmlspecialchars($praktikum['deskripsi']) ?></p>

    <h2 class="text-xl font-semibold mb-2">Daftar Modul</h2>

    <ul class="space-y-3">
        <?php while ($modul = $modulList->fetch_assoc()): ?>
            <li class="border p-3 rounded">
                <div class="font-semibold"><?= htmlspecialchars($modul['nama_modul']) ?></div>
                <div class="text-sm text-gray-600"><?= htmlspecialchars($modul['deskripsi']) ?></div>
                
                <?php if ($modul['file_materi']): ?>
                    <a href="/uploads/modul_praktikum/<?= htmlspecialchars($modul['file_materi']) ?>" download class="inline-block mt-2 bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                        Unduh Materi
                    </a>
                <?php else: ?>
                    <p class="text-red-500 text-sm mt-2">Materi belum tersedia</p>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
