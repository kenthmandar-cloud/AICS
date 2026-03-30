<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: /aics/auth/login.php");
    exit;
}
$cp  = basename($_SERVER['PHP_SELF']);
$dir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($d, $p = null) {
    global $cp, $dir;
    if ($p) return ($dir === $d && $cp === $p) ? 'active' : '';
    return ($dir === $d) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AICS 2025</title>
  <link rel="stylesheet" href="/aics/assets/css/style.css">
</head>
<body>
<div class="layout">
  <div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon">A</div>
      <div class="sidebar-logo-text">
        <h1>AICS 2025</h1>
        <p>Crisis Assistance</p>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a href="/aics/index.php" class="nav-item <?= isActive('aics','index.php') ?>">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
      </a>

      <div class="nav-section-label">Records</div>
      <a href="/aics/records/index.php" class="nav-item <?= isActive('records','index.php') ?>">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        All Records
      </a>
      <a href="/aics/records/add.php" class="nav-item <?= isActive('records','add.php') ?>">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Record
      </a>
      <a href="/aics/reports/index.php" class="nav-item <?= isActive('reports') ?>">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Reports
      </a>

      <?php if ($_SESSION['user']['role'] === 'admin'): ?>
      <div class="nav-section-label">Admin</div>
      <a href="/aics/users/index.php" class="nav-item <?= isActive('users') ?>">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        Users
      </a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['user']['full_name'],0,1)) ?></div>
      <div class="sidebar-user">
        <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user']['full_name']) ?></div>
        <div class="sidebar-user-role"><?= $_SESSION['user']['role'] ?></div>
      </div>
      <a href="/aics/auth/logout.php" class="sidebar-logout" title="Logout">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      </a>
    </div>
  </aside>

  <div class="main-content">
    <div class="mobile-topbar">
      <span class="mobile-topbar-title">AICS 2025</span>
      <button class="hamburger" onclick="openSidebar()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
    <div class="page-content">