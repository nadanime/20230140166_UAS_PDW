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

$stmt = $conn->prepare("
        SELECT 
            p.id, p.nama_praktikum, 
            p.deskripsi, p.semester
        FROM praktikum_mahasiswa pm
        JOIN praktikum p ON pm.praktikum_id = p.id
        WHERE pm.user_id = ?
        ");
$stmt->bind_param(
    "i",
    $user_id
);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Praktikum Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6">
    <div class="mb-10">
        <a href="/mahasiswa/dashboard.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Dashboard
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-4">Praktikum yang Kamu Ikuti</h1>

    <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="border p-4 rounded shadow">
                <h2 class="text-xl font-semibold"><?= htmlspecialchars($row['nama_praktikum']) ?></h2>
                <p class="text-gray-600"><?= htmlspecialchars($row['deskripsi']) ?></p>
                <span class="text-sm text-gray-500">Semester: <?= htmlspecialchars($row['semester']) ?></span>

                <div class="mt-2">
                    <a href="detail_praktikum.php?id=<?= $row['id'] ?>"
                        class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                        Lihat Detail
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>