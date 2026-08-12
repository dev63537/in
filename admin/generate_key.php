<?php
// admin/generate_key.php
$pageTitle = 'Password Recovery Keys';
include __DIR__ . '/includes/admin_header.php';

// Handle key generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];
    $durationHours = (int)($_POST['duration'] ?? 24);
    
    // Validate user
    $user = dbFetchOne("SELECT id, name FROM users WHERE id = ?", [$userId]);
    if ($user) {
        $secretKey = bin2hex(random_bytes(16)); // 32 char hex string
        $expiresAt = date('Y-m-d H:i:s', strtotime("+$durationHours hours"));
        
        dbExecute("INSERT INTO password_reset_keys (user_id, secret_key, expires_at) VALUES (?, ?, ?)", [$userId, $secretKey, $expiresAt]);
        
        $_SESSION['flash_key_success'] = "Successfully generated recovery key for <strong>" . e($user['name']) . "</strong>.<br><br><strong>Key: </strong> <span style='font-family:monospace;background:rgba(0,0,0,0.05);padding:4px 8px;border-radius:4px;user-select:all;'>" . $secretKey . "</span><br><br>Share this key with the user. It will expire in $durationHours hours.";
        
        header("Location: generate_key.php");
        exit;
    }
}

// Fetch all users (admins and customers) for the dropdown
$users = dbFetchAll("SELECT id, name, email, role FROM users ORDER BY role ASC, name ASC");

// Fetch active keys
$activeKeys = dbFetchAll("
    SELECT k.*, u.name, u.email 
    FROM password_reset_keys k 
    JOIN users u ON k.user_id = u.id 
    ORDER BY k.created_at DESC 
    LIMIT 50
");

// Check for success message
$successMsg = '';
if (isset($_SESSION['flash_key_success'])) {
    $successMsg = $_SESSION['flash_key_success'];
    unset($_SESSION['flash_key_success']);
}
?>
<div class="admin-card" style="margin-bottom: 20px;">
  <div class="admin-card-header"><h3>Generate Recovery Key</h3></div>
  <div style="padding: 20px;">
    <?php if (!empty($successMsg)): ?>
        <div style="background: rgba(39, 174, 96, 0.1); border: 1px solid #27ae60; color: #27ae60; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $successMsg ?>
        </div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group" style="margin-bottom: 15px;">
            <label>Select User</label>
            <select name="user_id" required class="form-control">
                <option value="">-- Choose User --</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['email']) ?>) - <?= ucfirst($u['role']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label>Valid For (Hours)</label>
            <input type="number" name="duration" value="24" min="1" max="168" required class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Generate Key</button>
    </form>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header"><h3>Recent Keys</h3></div>
  <table class="admin-table">
    <thead>
        <tr><th>User</th><th>Key</th><th>Generated</th><th>Expires</th><th>Status</th></tr>
    </thead>
    <tbody>
        <?php foreach ($activeKeys as $k): ?>
        <?php 
            $isExpired = strtotime($k['expires_at']) < time();
            if ($k['used']) {
                $status = '<span class="status-badge status-inactive">Used</span>';
            } elseif ($isExpired) {
                $status = '<span class="status-badge status-inactive" style="background:rgba(231,76,60,0.1);color:#e74c3c;">Expired</span>';
            } else {
                $status = '<span class="status-badge status-active">Active</span>';
            }
        ?>
        <tr>
            <td><?= e($k['name']) ?><br><small style="color:#888"><?= e($k['email']) ?></small></td>
            <td style="font-family:monospace; white-space:nowrap;">
                <?= substr($k['secret_key'], 0, 8) ?>...
                <button type="button" onclick="copyKey('<?= $k['secret_key'] ?>', this)" style="border:none;background:none;color:var(--primary);cursor:pointer;margin-left:5px;" title="Copy Full Key">
                    <i class="fa fa-copy"></i>
                </button>
            </td>
            <td><?= date('d M Y H:i', strtotime($k['created_at'])) ?></td>
            <td><?= date('d M Y H:i', strtotime($k['expires_at'])) ?></td>
            <td><?= $status ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($activeKeys)): ?>
        <tr><td colspan="5" style="text-align:center;padding:20px;color:#888;">No keys generated yet.</td></tr>
        <?php endif; ?>
    </tbody>
  </table>
</div>
<script>
function copyKey(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
        let icon = btn.querySelector('i');
        icon.className = 'fa fa-check';
        icon.style.color = '#27ae60';
        setTimeout(function() {
            icon.className = 'fa fa-copy';
            icon.style.color = '';
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
