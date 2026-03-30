<?php
require '../includes/header.php';
require '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $password  = trim($_POST['password']);
    $role      = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($full_name) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if username already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = "Username already exists. Please choose another.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role, is_active)
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $username, $password, $role, $is_active]);
            header("Location: index.php?success=1");
            exit;
        }
    }
}
?>

<div class="max-w-lg mx-auto">
  <div class="flex items-center gap-3 mb-6">
    <a href="index.php" class="text-blue-600 hover:underline text-sm">← Back to Users</a>
    <h2 class="text-xl font-bold text-gray-800">Add New User</h2>
  </div>

  <?php if ($error): ?>
    <div class="bg-red-50 text-red-700 text-sm rounded-lg px-4 py-2 mb-4"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" class="bg-white rounded-2xl shadow p-6 space-y-4">

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
      <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
        required placeholder="e.g. Juan Dela Cruz"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
        required placeholder="e.g. jdelacruz"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
      <input type="text" name="password"
        required placeholder="Enter password"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
      <p class="text-xs text-gray-400 mt-1">Password is stored as plain text.</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
      <select name="role"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="encoder" <?= (($_POST['role'] ?? '') === 'encoder') ? 'selected' : '' ?>>Encoder</option>
        <option value="admin"   <?= (($_POST['role'] ?? '') === 'admin')   ? 'selected' : '' ?>>Admin</option>
        <option value="viewer"  <?= (($_POST['role'] ?? '') === 'viewer')  ? 'selected' : '' ?>>Viewer</option>
      </select>
    </div>

    <div class="flex items-center gap-2">
      <input type="checkbox" name="is_active" id="is_active" value="1" checked
        class="w-4 h-4 accent-blue-600">
      <label for="is_active" class="text-sm text-gray-700">Active</label>
    </div>

    <div class="pt-2 flex gap-3">
      <button type="submit"
        class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2 rounded-lg text-sm font-semibold transition">
        Save User
      </button>
      <a href="index.php"
        class="px-6 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50 transition">
        Cancel
      </a>
    </div>
  </form>
</div>

<?php require '../includes/footer.php'; ?>