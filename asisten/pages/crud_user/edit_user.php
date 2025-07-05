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
        SELECT nama, email, role 
        FROM users 
        WHERE id = ?
        ");
$stmt->bind_param(
    "i", 
    $id
);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "Pengguna tidak ditemukan!";
    header("Location: /asisten/pages/crud_user/kelola_user.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
                UPDATE users 
                SET nama = ?, 
                    email = ?, 
                    role = ?, 
                    password = ? 
                    WHERE id = ?
                ");
        $stmt->bind_param(
            "ssssi", 
            $nama, 
            $email, 
            $role, 
            $password, 
            $id
        );
    } else {
        $stmt = $conn->prepare("
                UPDATE users 
                SET nama = ?, 
                email = ?, 
                role = ? 
                WHERE id = ?
                ");
        $stmt->bind_param(
            "sssi", 
            $nama, 
            $email, 
            $role, 
            $id
        );
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Pengguna berhasil diperbarui.";
        header("Location: /asisten/pages/crud_user/kelola_user.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal memperbarui pengguna: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
    <div class="mb-10">
        <a href="/asisten/pages/crud_user/kelola_user.php"
            class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Kembali ke Daftar Pengguna
        </a>
    </div>
    <h1 class="text-xl font-bold mb-4">Edit Pengguna</h1>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="text-red-600 mb-4"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-3 max-w-md">
        <div>
            <label>Nama:</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required class="border px-2 py-1 rounded w-full">
        </div>
        <div>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="border px-2 py-1 rounded w-full">
        </div>
        <div>
            <label>Password (Kosongkan jika tidak diubah):</label>
            <input type="password" name="password" class="border px-2 py-1 rounded w-full">
        </div>
        <div>
            <label>Role:</label>
            <select name="role" class="border px-2 py-1 rounded w-full">
                <option value="mahasiswa" <?= $user['role'] === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                <option value="asisten" <?= $user['role'] === 'asisten' ? 'selected' : '' ?>>Asisten</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Perbarui</button>
    </form>
</body>
</html>