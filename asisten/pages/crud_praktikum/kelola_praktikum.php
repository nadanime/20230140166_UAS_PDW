<?php
session_start();
require dirname(__DIR__, 3) . '/config.php';

if ($_SESSION['role'] !== 'asisten') {
    die("<h1 style='color: red; 
            text-align: center;'>
            Akses hanya untuk asisten!
         </h1>");
}

$praktikum = $conn->query("SELECT * FROM praktikum");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kelola Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6">
    <div class="mb-10">
        <a href="/asisten/dashboard.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Dashboard
        </a>
    </div>
    
    <h1 class="text-2xl font-bold mb-4">Kelola Mata Praktikum</h1>

    <a href="/asisten/pages/crud_praktikum/tambah_praktikum.php" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Tambah
        Praktikum</a>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="text-green-600 mb-6 mt-6"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif (!empty($_SESSION['error'])): ?>
        <div class="text-red-600 mb-6 mt-6"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <table class="table-auto w-full mt-4 border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2">Nama Praktikum</th>
                <th class="p-2">Deskripsi</th>
                <th class="p-2">Semester</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $praktikum->fetch_assoc()): ?>
                <tr>
                    <td class="p-2"><?= htmlspecialchars($row['nama_praktikum']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($row['deskripsi']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($row['semester']) ?></td>
                    <td class="p-2 space-x-2">
                        <a href="/asisten/pages/crud_praktikum/edit_praktikum.php?id=<?= $row['id'] ?>"
                            class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500">Edit</a>
                        <a href="#" 
                           onclick="openDeleteModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_praktikum'])) ?>'); return false;"
                           class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
            <h2 class="text-xl font-bold mb-2 text-red-600">Konfirmasi Hapus Praktikum</h2>
            <p id="modalText" class="mb-4"></p>
            <form id="deleteForm" method="POST" action="/asisten/pages/crud_praktikum/hapus_praktikum.php">
                <input type="hidden" name="id" id="praktikumId">
                <label class="block mb-2 text-sm">Masukkan password Anda untuk konfirmasi:</label>
                <input type="password" name="password" required class="border rounded px-2 py-1 w-full mb-4">
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeDeleteModal()" class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400">Batal</button>
                    <button type="submit" class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openDeleteModal(id, nama) {
        document.getElementById('praktikumId').value = id;
        document.getElementById('modalText').innerText = `Anda yakin ingin menghapus praktikum "${nama}"? Tindakan ini tidak dapat dibatalkan.`;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
    </script>
</body>

</html>