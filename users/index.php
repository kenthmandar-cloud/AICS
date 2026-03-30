<?php
require '../includes/header.php';
require '../config/db.php';

// Delete record
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM aics_record WHERE id=?")->execute([$id]);
    header("Location: index.php?deleted=1"); exit;
}

// Filters
$search  = trim($_GET['q'] ?? '');
$status  = $_GET['status'] ?? '';
$asst_id = $_GET['asst_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';

// Pagination
$per_page = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(c.full_name LIKE ? OR r.control_number LIKE ? OR b.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
if ($status !== '') {
    $where[] = "r.status = ?";
    $params[] = $status;
}
if ($asst_id !== '') {
    $where[] = "r.assistance_type_id = ?";
    $params[] = $asst_id;
}
if ($date_from !== '') {
    $where[] = "r.transaction_date >= ?";
    $params[] = $date_from;
}
if ($date_to !== '') {
    $where[] = "r.transaction_date <= ?";
    $params[] = $date_to;
}

$whereSQL = implode(' AND ', $where);

$count_stmt = $pdo->prepare("
    SELECT COUNT(*) FROM aics_record r
    JOIN claimant c ON r.claimant_id=c.id
    LEFT JOIN barangay b ON c.barangay_id=b.id
    WHERE $whereSQL
");
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $per_page);

$stmt = $pdo->prepare("
    SELECT r.*, c.full_name AS claimant_name, c.age AS claimant_age, c.sex AS claimant_sex,
           b.name AS barangay, a.name AS asst_type,
           bn.full_name AS bene_name
    FROM aics_record r
    JOIN claimant c ON r.claimant_id=c.id
    LEFT JOIN barangay b ON c.barangay_id=b.id
    LEFT JOIN assistance_type a ON r.assistance_type_id=a.id
    LEFT JOIN beneficiary bn ON r.beneficiary_id=bn.id
    WHERE $whereSQL
    ORDER BY r.control_number DESC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$records = $stmt->fetchAll();

$asst_types = $pdo->query("SELECT * FROM assistance_type ORDER BY name")->fetchAll();
?>

<div class="page-header">
  <div>
    <div class="page-title">AICS Records</div>
    <div class="page-subtitle">Total of <?= number_format($total_rows) ?> record(s) found</div>
  </div>
  <a href="add.php" class="btn btn-primary">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Add Record
  </a>
</div>

<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-danger">
  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
  Record deleted successfully.
</div>
<?php endif; ?>
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success">
  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
  Record saved successfully.
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:16px 20px">
    <form method="GET" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto auto;gap:10px;align-items:end;flex-wrap:wrap">
      <div>
        <label class="form-label" style="margin-bottom:4px">Search</label>
        <div class="search-box" style="min-width:unset">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Name, control no, barangay...">
        </div>
      </div>
      <div>
        <label class="form-label" style="margin-bottom:4px">Status</label>
        <select name="status" class="form-control">
          <option value="">All Status</option>
          <option value="pending"   <?= $status==='pending'   ?'selected':'' ?>>Pending</option>
          <option value="released"  <?= $status==='released'  ?'selected':'' ?>>Released</option>
          <option value="cancelled" <?= $status==='cancelled' ?'selected':'' ?>>Cancelled</option>
        </select>
      </div>
      <div>
        <label class="form-label" style="margin-bottom:4px">Assistance Type</label>
        <select name="asst_id" class="form-control">
          <option value="">All Types</option>
          <?php foreach ($asst_types as $a): ?>
          <option value="<?= $a['id'] ?>" <?= $asst_id==$a['id']?'selected':'' ?>><?= htmlspecialchars($a['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
        <div>
          <label class="form-label" style="margin-bottom:4px">Date From</label>
          <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
        </div>
        <div>
          <label class="form-label" style="margin-bottom:4px">Date To</label>
          <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
        </div>
      </div>
      <div style="grid-column:span 1">
        <label class="form-label" style="margin-bottom:4px">&nbsp;</label>
        <button type="submit" class="btn btn-primary btn-full">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Filter
        </button>
      </div>
      <div>
        <label class="form-label" style="margin-bottom:4px">&nbsp;</label>
        <a href="index.php" class="btn btn-secondary btn-full">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Table -->
<div class="table-wrapper">
  <div class="table-toolbar">
    <div class="card-title">
      Showing <?= number_format(min($offset+1,$total_rows)) ?>–<?= number_format(min($offset+$per_page,$total_rows)) ?> of <?= number_format($total_rows) ?> records
    </div>
    <a href="/aics/reports/index.php" class="btn btn-secondary btn-sm">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Reports
    </a>
  </div>
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Control No.</th>
          <th>Date</th>
          <th>Barangay</th>
          <th>Claimant</th>
          <th>Beneficiary</th>
          <th>Assistance</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Released</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($records as $i => $r): ?>
        <tr>
          <td class="td-muted"><?= $offset + $i + 1 ?></td>
          <td class="td-mono" style="font-weight:700"><?= $r['control_number'] ?></td>
          <td class="td-muted"><?= date('m/d/Y', strtotime($r['transaction_date'])) ?></td>
          <td class="td-muted"><?= htmlspecialchars($r['barangay'] ?? '—') ?></td>
          <td>
            <div style="font-weight:500"><?= htmlspecialchars($r['claimant_name']) ?></div>
            <div style="font-size:11px;color:var(--text-muted)"><?= $r['claimant_age'] ?>y/o &bull; <?= $r['claimant_sex']==='M'?'Male':'Female' ?></div>
          </td>
          <td><?= htmlspecialchars($r['bene_name'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['asst_type'] ?? '—') ?></td>
          <td style="font-weight:600;color:var(--success)">&#8369;<?= number_format($r['amount'],2) ?></td>
          <td>
            <span class="badge <?= $r['status']==='released'?'badge-green':($r['status']==='cancelled'?'badge-red':'badge-yellow') ?>">
              <?= ucfirst($r['status']) ?>
            </span>
          </td>
          <td class="td-muted"><?= $r['date_released'] ? date('m/d/Y',strtotime($r['date_released'])) : '—' ?></td>
          <td>
            <div class="table-actions">
              <a href="view.php?id=<?= $r['id'] ?>" class="action-btn view">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                View
              </a>
              <a href="edit.php?id=<?= $r['id'] ?>" class="action-btn edit">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
              </a>
              <a href="?delete=<?= $r['id'] ?><?= $search?"&q=$search":'' ?><?= $status?"&status=$status":'' ?>&page=<?= $page ?>"
                 onclick="return confirm('Delete this record?')" class="action-btn delete">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Del
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($records)): ?>
        <tr><td colspan="11">
          <div class="table-empty">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p>No records found</p>
            <span>Try adjusting your search or filters</span>
          </div>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
  <div class="pagination">
    <div class="pagination-info">
      Page <?= $page ?> of <?= $total_pages ?>
    </div>
    <div class="pagination-links">
      <?php
      $qs = http_build_query(array_filter(['q'=>$search,'status'=>$status,'asst_id'=>$asst_id,'date_from'=>$date_from,'date_to'=>$date_to]));
      ?>
      <a href="?<?= $qs ?>&page=1" class="page-link <?= $page<=1?'disabled':'' ?>">«</a>
      <a href="?<?= $qs ?>&page=<?= max(1,$page-1) ?>" class="page-link <?= $page<=1?'disabled':'' ?>">‹</a>
      <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
      <a href="?<?= $qs ?>&page=<?= $i ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a href="?<?= $qs ?>&page=<?= min($total_pages,$page+1) ?>" class="page-link <?= $page>=$total_pages?'disabled':'' ?>">›</a>
      <a href="?<?= $qs ?>&page=<?= $total_pages ?>" class="page-link <?= $page>=$total_pages?'disabled':'' ?>">»</a>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>