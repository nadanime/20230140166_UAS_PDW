<?php
session_start();

require dirname(__DIR__, 2) . '/config.php';

$sql = "SELECT * FROM praktikum";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Katalog Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6">
    <div class="mb-10">
        <a href="/mahasiswa/dashboard.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Dashboard
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-4">Daftar Mata Praktikum</h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="text-green-600 mb-4"><?= $_SESSION['success'];
        unset($_SESSION['success']); ?></div>
    <?php elseif (!empty($_SESSION['sudah_daftar'])): ?>
        <div class="text-red-600 mb-4"><?= $_SESSION['sudah_daftar'];
        unset($_SESSION['sudah_daftar']); ?></div>
    <?php endif; ?>

    <div class="grid gap-4 grid-cols-1 md:grid-cols-3">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="border p-4 rounded shadow">
                <h2 class="text-xl font-semibold"><?= htmlspecialchars($row['nama_praktikum']) ?></h2>
                <p class="text-gray-600"><?= htmlspecialchars($row['deskripsi']) ?></p>
                <span class="text-sm text-gray-500">Semester: <?= htmlspecialchars($row['semester']) ?></span>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="/mahasiswa/server/daftar_praktikum.php" method="POST" class="mt-2">
                        <input type="hidden" name="praktikum_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600">Daftar</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>