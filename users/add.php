<?php
require '../includes/header.php';
require '../config/db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate
    $claimant_name = trim($_POST['claimant_name'] ?? '');
    $claimant_age  = (int)($_POST['claimant_age'] ?? 0);
    $claimant_sex  = $_POST['claimant_sex'] ?? '';
    $barangay_id   = (int)($_POST['barangay_id'] ?? 0) ?: null;
    $contact       = trim($_POST['contact_number'] ?? '');

    $bene_name     = trim($_POST['bene_name'] ?? '');
    $bene_age      = (int)($_POST['bene_age'] ?? 0);
    $bene_sex      = $_POST['bene_sex'] ?? '';
    $relationship  = trim($_POST['relationship'] ?? '');

    $control_number     = (int)($_POST['control_number'] ?? 0);
    $transaction_date   = $_POST['transaction_date'] ?? '';
    $assistance_type_id = (int)($_POST['assistance_type_id'] ?? 0) ?: null;
    $amount             = (float)($_POST['amount'] ?? 0);
    $diagnoses          = trim($_POST['diagnoses'] ?? '');
    $category           = trim($_POST['category'] ?? '');
    $office_control     = trim($_POST['office_control'] ?? '');
    $date_released      = $_POST['date_released'] ?? '';
    $status             = $_POST['status'] ?? 'pending';
    $remarks            = trim($_POST['remarks'] ?? '');

    if (empty($claimant_name)) $errors[] = "Claimant name is required.";
    if ($claimant_age <= 0)    $errors[] = "Claimant age must be greater than 0.";
    if (empty($bene_name))     $errors[] = "Beneficiary name is required.";
    if ($bene_age <= 0)        $errors[] = "Beneficiary age must be greater than 0.";
    if ($control_number <= 0)  $errors[] = "Control number is required.";
    if (empty($transaction_date)) $errors[] = "Transaction date is required.";
    if ($amount <= 0)          $errors[] = "Amount must be greater than 0.";

    if (empty($errors)) {
        // Check duplicate control number
        $chk = $pdo->prepare("SELECT id FROM aics_record WHERE control_number=?");
        $chk->execute([$control_number]);
        if ($chk->fetch()) {
            $errors[] = "Control number $control_number already exists.";
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert claimant
            $s = $pdo->prepare("INSERT INTO claimant (full_name,age,sex,barangay_id,contact_number) VALUES (?,?,?,?,?)");
            $s->execute([$claimant_name,$claimant_age,$claimant_sex,$barangay_id,$contact]);
            $claimant_id = $pdo->lastInsertId();

            // Insert beneficiary
            $s = $pdo->prepare("INSERT INTO beneficiary (full_name,age,sex,relationship,claimant_id) VALUES (?,?,?,?,?)");
            $s->execute([$bene_name,$bene_age,$bene_sex,$relationship,$claimant_id]);
            $bene_id = $pdo->lastInsertId();

            // Insert record
            $s = $pdo->prepare("INSERT INTO aics_record
                (control_number,transaction_date,claimant_id,beneficiary_id,assistance_type_id,
                 amount,diagnoses,category,office_control,date_released,status,encoder_id,remarks)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $s->execute([
                $control_number, $transaction_date, $claimant_id, $bene_id, $assistance_type_id,
                $amount, $diagnoses, $category, $office_control,
                $date_released ?: null, $status, $_SESSION['user']['id'], $remarks
            ]);

            $pdo->commit();
            header("Location: index.php?success=1"); exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

$barangays  = $pdo->query("SELECT * FROM barangay ORDER BY name")->fetchAll();
$asst_types = $pdo->query("SELECT * FROM assistance_type ORDER BY name")->fetchAll();
$last_ctrl  = $pdo->query("SELECT MAX(control_number) AS mx FROM aics_record")->fetch();
$next_ctrl  = ($last_ctrl['mx'] ?? 0) + 1;
?>

<div class="breadcrumb">
  <a href="index.php">Records</a>
  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  <span>Add New Record</span>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <div>
    <?php foreach ($errors as $e): ?>
    <div><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<form method="POST">
  <!-- Transaction Info -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header">
      <div class="card-title">Transaction Information</div>
    </div>
    <div class="card-body">
      <div class="form-row cols-4">
        <div class="form-group">
          <label class="form-label">Control No. <span class="required">*</span></label>
          <input type="number" name="control_number" class="form-control"
                 value="<?= htmlspecialchars($_POST['control_number'] ?? $next_ctrl) ?>" required min="1">
        </div>
        <div class="form-group">
          <label class="form-label">Date <span class="required">*</span></label>
          <input type="date" name="transaction_date" class="form-control"
                 value="<?= htmlspecialchars($_POST['transaction_date'] ?? date('Y-m-d')) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Barangay</label>
          <select name="barangay_id" class="form-control">
            <option value="">-- Select Barangay --</option>
            <?php foreach ($barangays as $b): ?>
            <option value="<?= $b['id'] ?>" <?= (($_POST['barangay_id']??'')==$b['id'])?'selected':'' ?>>
              <?= htmlspecialchars($b['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="pending"   <?= (($_POST['status']??'pending')==='pending')?'selected':'' ?>>Pending</option>
            <option value="released"  <?= (($_POST['status']??'')==='released')?'selected':'' ?>>Released</option>
            <option value="cancelled" <?= (($_POST['status']??'')==='cancelled')?'selected':'' ?>>Cancelled</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Claimant -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header">
      <div class="card-title">Claimant Information</div>
    </div>
    <div class="card-body">
      <div class="form-row cols-3">
        <div class="form-group" style="grid-column:span 1">
          <label class="form-label">Full Name <span class="required">*</span></label>
          <input type="text" name="claimant_name" class="form-control"
                 placeholder="Last, First MI" required
                 value="<?= htmlspecialchars($_POST['claimant_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Age <span class="required">*</span></label>
          <input type="number" name="claimant_age" class="form-control"
                 min="1" max="120" required
                 value="<?= htmlspecialchars($_POST['claimant_age'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Sex <span class="required">*</span></label>
          <select name="claimant_sex" class="form-control" required>
            <option value="M" <?= (($_POST['claimant_sex']??'M')==='M')?'selected':'' ?>>Male</option>
            <option value="F" <?= (($_POST['claimant_sex']??'')==='F')?'selected':'' ?>>Female</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control"
               placeholder="e.g. 09XXXXXXXXX"
               value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
      </div>
    </div>
  </div>

  <!-- Beneficiary -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header">
      <div class="card-title">Beneficiary / Patient Information</div>
    </div>
    <div class="card-body">
      <div class="form-row cols-4">
        <div class="form-group" style="grid-column:span 2">
          <label class="form-label">Full Name <span class="required">*</span></label>
          <input type="text" name="bene_name" class="form-control"
                 placeholder="Last, First MI" required
                 value="<?= htmlspecialchars($_POST['bene_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Age <span class="required">*</span></label>
          <input type="number" name="bene_age" class="form-control"
                 min="1" max="120" required
                 value="<?= htmlspecialchars($_POST['bene_age'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Sex <span class="required">*</span></label>
          <select name="bene_sex" class="form-control" required>
            <option value="M" <?= (($_POST['bene_sex']??'M')==='M')?'selected':'' ?>>Male</option>
            <option value="F" <?= (($_POST['bene_sex']??'')==='F')?'selected':'' ?>>Female</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Relationship to Claimant</label>
        <input type="text" name="relationship" class="form-control"
               placeholder="e.g. Self, Son, Daughter, Spouse"
               value="<?= htmlspecialchars($_POST['relationship'] ?? '') ?>">
      </div>
    </div>
  </div>

  <!-- Assistance Details -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header">
      <div class="card-title">Assistance Details</div>
    </div>
    <div class="card-body">
      <div class="form-row cols-3">
        <div class="form-group">
          <label class="form-label">Assistance Type</label>
          <select name="assistance_type_id" class="form-control">
            <option value="">-- Select Type --</option>
            <?php foreach ($asst_types as $a): ?>
            <option value="<?= $a['id'] ?>" <?= (($_POST['assistance_type_id']??'')==$a['id'])?'selected':'' ?>>
              <?= htmlspecialchars($a['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Amount (&#8369;) <span class="required">*</span></label>
          <input type="number" name="amount" class="form-control"
                 step="0.01" min="0.01" required
                 value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Category</label>
          <input type="text" name="category" class="form-control"
                 placeholder="e.g. AICS, DSWD"
                 value="<?= htmlspecialchars($_POST['category'] ?? '') ?>">
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Diagnoses / Condition</label>
          <textarea name="diagnoses" class="form-control" rows="3"
                    placeholder="Medical diagnosis or reason for assistance"><?= htmlspecialchars($_POST['diagnoses'] ?? '') ?></textarea>
        </div>
        <div>
          <div class="form-group">
            <label class="form-label">Office Control No.</label>
            <input type="text" name="office_control" class="form-control"
                   value="<?= htmlspecialchars($_POST['office_control'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Date Released</label>
            <input type="date" name="date_released" class="form-control"
                   value="<?= htmlspecialchars($_POST['date_released'] ?? '') ?>">
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2"
                  placeholder="Additional notes..."><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div style="display:flex;gap:12px;justify-content:flex-end">
    <a href="index.php" class="btn btn-secondary btn-lg">Cancel</a>
    <button type="submit" class="btn btn-primary btn-lg">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      Save Record
    </button>
  </div>
</form>

<?php require '../includes/footer.php'; ?>