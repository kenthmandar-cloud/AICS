<?php
require '../includes/header.php';
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: /aics/index.php"); exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { header("Location: index.php"); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = $user['username'] === 'admin' ? 'admin' : trim($_POST['username'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $role      = $_POST['role'] ?? 'encoder';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($username))  $errors[] = "Username is required.";
    if (empty($password))  $errors[] = "Password is required.";

    if (empty($errors) && $user['username'] !== 'admin') {
        $chk = $pdo->prepare("SELECT id FROM users WHERE username=? AND id!=?");
        $chk->execute([$username,$id]);
        if ($chk->fetch()) $errors[] = "Username '$username' is already taken.";
    }

    if (empty($errors)) {
        $pdo->prepare("UPDATE users SET full_name=?,username=?,password=?,role=?,is_active=? WHERE id=?")
            ->execute([$full_name,$username,$password,$role,$is_active,$id]);
        header("Location: index.php?success=1"); exit;
    }

    // Repopulate
    $user = array_merge($user, compact('full_name','username','password','role','is_active'));
}
?>

<div class="breadcrumb">
  <a href="index.php">Users</a>
  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  <span>Edit User</span>
</div>

<div style="max-width:600px">
  <?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div><?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <div style="display:flex;align-items:center;gap:12px">
        <div class="avatar" style="width:42px;height:42px;font-size:16px">
          <?= strtoupper(substr($user['full_name'],0,1)) ?>
        </div>
        <div>
          <div class="card-title"><?= htmlspecialchars($user['full_name']) ?></div>
          <div class="card-subtitle">@<?= htmlspecialchars($user['username']) ?> &bull; <?= ucfirst($user['role']) ?></div>
        </div>
      </div>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Full Name <span class="required">*</span></label>
          <input type="text" name="full_name" class="form-control" required
                 value="<?= htmlspecialchars($user['full_name']) ?>">
        </div>

        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Username <span class="required">*</span></label>
            <?php if ($user['username'] === 'admin'): ?>
              <input type="text" class="form-control" value="admin" readonly>
              <div class="form-hint">Admin username cannot be changed</div>
            <?php else: ?>
              <input type="text" name="username" class="form-control" required
                     value="<?= htmlspecialchars($user['username']) ?>">
            <?php endif; ?>
          </div>
          <div class="form-group">
            <label class="form-label">Password <span class="required">*</span></label>
            <input type="text" name="password" class="form-control" required
                   value="<?= htmlspecialchars($user['password']) ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Role</label>
          <select name="role" class="form-control">
            <option value="encoder" <?= $user['role']==='encoder'?'selected':'' ?>>Encoder — Can add and edit records</option>
            <option value="admin"   <?= $user['role']==='admin'?'selected':'' ?>>Admin — Full system access</option>
            <option value="viewer"  <?= $user['role']==='viewer'?'selected':'' ?>>Viewer — Read-only access</option>
          </select>
        </div>

        <div class="form-check">
          <input type="checkbox" name="is_active" id="is_active" value="1"
                 <?= $user['is_active'] ? 'checked' : '' ?>>
          <div>
            <label class="form-check-label" for="is_active">Active Account</label>
            <div class="form-check-hint">User can log in when active</div>
          </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
          <button type="submit" class="btn btn-primary btn-lg" style="flex:1">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Update User
          </button>
          <a href="index.php" class="btn btn-secondary btn-lg" style="flex:1;justify-content:center">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require '../includes/footer.php'; ?>