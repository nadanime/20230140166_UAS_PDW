<?php
session_start();
require dirname(__DIR__, 3) . '/config.php';

if ($_SESSION['role'] !== 'asisten') {
    die("<h1 style='color: red; 
            text-align: center;'>
            Akses hanya untuk asisten!
            </h1>");
}

$praktikumList = $conn->query("SELECT id, nama_praktikum FROM praktikum");

$praktikum_id = intval($_GET['praktikum_id'] ?? 0);

$praktikum = $conn->query("
        SELECT nama_praktikum 
        FROM praktikum 
        WHERE id = $praktikum_id
        ")->fetch_assoc();
$nama_praktikum = $praktikum['nama_praktikum'] ?? '';

if ($praktikum_id) {
    $stmt = $conn->prepare("
        SELECT mp.*, p.nama_praktikum 
        FROM modul_praktikum mp
        JOIN praktikum p ON mp.praktikum_id = p.id
        WHERE mp.praktikum_id = ?
        ORDER BY mp.id DESC
    ");
    $stmt->bind_param("i", $praktikum_id);
    $stmt->execute();
    $modul = $stmt->get_result();
} else {
    $modul = $conn->query("
        SELECT mp.*, p.nama_praktikum 
        FROM modul_praktikum mp
        JOIN praktikum p ON mp.praktikum_id = p.id
        ORDER BY mp.id DESC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kelola Modul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6">
    <div class="mb-10">
        <a href="/asisten/dashboard.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Dashboard
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-4">Kelola Modul Praktikum <?= htmlspecialchars($nama_praktikum) ?></h1>

    <form method="GET" class="mb-4 flex items-center space-x-2">
        <label for="praktikum_id" class="font-semibold">Filter Praktikum:</label>
        <select name="praktikum_id" id="praktikum_id" class="border px-2 py-1 rounded" onchange="this.form.submit()">
            <option value="0">Semua Praktikum</option>
            <?php foreach ($praktikumList as $row): ?>
                <option value="<?= $row['id'] ?>" <?= ($praktikum_id == $row['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['nama_praktikum']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($praktikum_id): ?>
            <a href="kelola_modul.php" class="ml-2 text-blue-600 hover:underline text-sm">Reset</a>
        <?php endif; ?>
    </form>

    <a href="/asisten/pages/crud_modul/tambah_modul.php?praktikum_id=<?= $praktikum_id ?>"
        class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Tambah Modul</a>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="text-green-600 mb-6 mt-6"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif (!empty($_SESSION['error'])): ?>
        <div class="text-red-600 mb-6 mt-6"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <table class="table-auto w-full mt-4 border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2">Nama Modul</th>
                <th class="p-2">Deskripsi</th>
                <th class="p-2">File Materi</th>
                <th class="p-2">Praktikum</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $modul->fetch_assoc()): ?>
                <tr>
                    <td class="p-2"><?= htmlspecialchars($row['nama_modul']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($row['deskripsi']) ?></td>
                    <td class="p-2">
                        <?php if ($row['file_materi']): ?>
                            <a href="/uploads/modul_praktikum/<?= htmlspecialchars($row['file_materi']) ?>" download
                                class="text-blue-600 hover:underline">Download</a>
                        <?php else: ?>
                            <span class="text-gray-500 italic">Tidak ada file</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-2"><?= htmlspecialchars($row['nama_praktikum']) ?></td> 
                    <td class="p-2 space-x-2">
                        <a href="/asisten/pages/crud_modul/edit_modul.php?id=<?= $row['id'] ?>&praktikum_id=<?= $praktikum_id ?>"
                            class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500">Edit</a>
                        <a href="#"
                            onclick="openDeleteModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_modul'])) ?>'); return false;"
                            class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
            <h2 class="text-xl font-bold mb-2 text-red-600">Konfirmasi Hapus Modul</h2>
            <p id="modalText" class="mb-4"></p>
            <form id="deleteForm" method="POST" action="/asisten/pages/crud_modul/hapus_modul.php">
                <input type="hidden" name="id" id="modulId">
                <input type="hidden" name="praktikum_id" value="<?= $praktikum_id ?>">
                <label class="block mb-2 text-sm">Masukkan password Anda untuk konfirmasi:</label>
                <input type="password" name="password" required class="border rounded px-2 py-1 w-full mb-4">
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeDeleteModal()"
                        class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400">Batal</button>
                    <button type="submit"
                        class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(id, nama) {
            document.getElementById('modulId').value = id;
            document.getElementById('modalText').innerText = `Anda yakin ingin menghapus modul "${nama}"? Tindakan ini tidak dapat dibatalkan.`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</body>

</html>