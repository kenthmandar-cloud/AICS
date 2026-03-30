<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: /aics/auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AICS 2025</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">
  <!-- Sidebar -->
  <aside class="w-60 bg-blue-900 text-white flex flex-col min-h-screen fixed">
    <div class="px-6 py-5 border-b border-blue-800">
      <h1 class="text-lg font-bold">AICS 2025</h1>
      <p class="text-xs text-blue-300 mt-0.5">Crisis Assistance System</p>
    </div>
    <nav class="flex-1 px-4 py-4 space-y-1 text-sm">
      <a href="/aics/index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-800 transition">
        🏠 Dashboard
      </a>
      <a href="/aics/records/index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-800 transition">
        📋 AICS Records
      </a>
      <a href="/aics/records/add.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-800 transition">
        ➕ Add Record
      </a>
      <a href="/aics/reports/index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-800 transition">
        📊 Reports
      </a>
    </nav>
    <div class="px-4 py-4 border-t border-blue-800 text-xs">
      <p class="text-blue-300">Logged in as</p>
      <p class="font-medium"><?= htmlspecialchars($_SESSION['user']['full_name']) ?></p>
      <a href="/aics/auth/logout.php" class="text-red-300 hover:text-red-100 mt-1 inline-block">Logout</a>
    </div>
  </aside>
  <!-- Main content -->
  <main class="ml-60 flex-1 p-6">