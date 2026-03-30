<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if ($user && $_POST['password'] === $user['password']) {
        $_SESSION['user'] = $user;
        header("Location: ../users/index.php");
        exit;
    }
    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AICS 2025 – Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-blue-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-sm">
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-blue-800">AICS 2025</h1>
      <p class="text-sm text-gray-500 mt-1">Assistance to Individuals in Crisis Situation</p>
    </div>
    <?php if (!empty($error)): ?>
      <div class="bg-red-50 text-red-700 text-sm rounded-lg px-4 py-2 mb-4"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="username" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>
      <button type="submit"
        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg transition">
        Sign In
      </button>
    </form>
  </div>
</body>
</html>