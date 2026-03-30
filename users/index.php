<?php
require '../includes/header.php';
require '../config/db.php';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND username != 'admin'");
    $stmt->execute([$_GET['delete']]);
    header("Location: index.php?deleted=1");
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="flex justify-between items-center mb-4">
  <h2 class="text-xl font-bold text-gray-800">User Management</h2>
  <a href="add.php" class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800 transition">
    + Add User
  </a>
</div>

<?php if (isset($_GET['success'])): ?>
  <div class="bg-green-50 text-green-700 text-sm rounded-lg px-4 py-2 mb-4">User saved successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
  <div class="bg-red-50 text-red-700 text-sm rounded-lg px-4 py-2 mb-4">User deleted.</div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-blue-800 text-white">
      <tr>
        <th class="px-4 py-3 text-left">#</th>
        <th class="px-4 py-3 text-left">Full Name</th>
        <th class="px-4 py-3 text-left">Username</th>
        <th class="px-4 py-3 text-left">Password</th>
        <th class="px-4 py-3 text-left">Role</th>
        <th class="px-4 py-3 text-left">Status</th>
        <th class="px-4 py-3 text-left">Created</th>
        <th class="px-4 py-3 text-left">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      <?php foreach ($users as $i => $u): ?>
      <tr class="hover:bg-blue-50 transition">
        <td class="px-4 py-3 text-gray-400"><?= $i + 1 ?></td>
        <td class="px-4 py-3 font-medium"><?= htmlspecialchars($u['full_name']) ?></td>
        <td class="px-4 py-3 font-mono"><?= htmlspecialchars($u['username']) ?></td>
        <td class="px-4 py-3 font-mono"><?= htmlspecialchars($u['password']) ?></td>
        <td class="px-4 py-3">
          <span class="px-2 py-0.5 rounded-full text-xs font-medium
            <?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700' :
               ($u['role'] === 'encoder' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') ?>">
            <?= ucfirst($u['role']) ?>
          </span>
        </td>
        <td class="px-4 py-3">
          <span class="px-2 py-0.5 rounded-full text-xs font-medium
            <?= $u['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
          </span>
        </td>
        <td class="px-4 py-3 text-gray-500"><?= date('m/d/Y', strtotime($u['created_at'])) ?></td>
        <td class="px-4 py-3 flex gap-3">
          <a href="edit.php?id=<?= $u['id'] ?>" class="text-yellow-600 hover:underline">Edit</a>
          <?php if ($u['username'] !== 'admin'): ?>
            <a href="index.php?delete=<?= $u['id'] ?>"
               onclick="return confirm('Delete this user?')"
               class="text-red-500 hover:underline">Delete</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($users)): ?>
        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No users found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require '../includes/footer.php'; ?>