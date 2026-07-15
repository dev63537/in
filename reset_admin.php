<?php
// ============================================================
// reset_admin.php — One-Time Admin Password Reset Tool
// INSTRUCTIONS:
//   1. Upload this file to your /htdocs/ folder
//   2. Visit: http://yourdomain.com/reset_admin.php
//   3. Fill in the form and submit
//   4. DELETE this file immediately after use!
// ============================================================
require_once __DIR__ . '/includes/functions.php';

$done  = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email    = trim($_POST['new_email'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $secret_key   = trim($_POST['secret_key'] ?? '');

    // Simple protection — you must know this key to use the script
    if ($secret_key !== '27_01_2007') {
        $error = 'Wrong secret key. Access denied.';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Check if admin exists
        $admin = dbFetchOne("SELECT id FROM users WHERE role = 'admin' LIMIT 1");

        if ($admin) {
            dbExecute(
                "UPDATE users SET email = ?, password = ? WHERE role = 'admin'",
                [$new_email, $hashed]
            );
        } else {
            // Create admin if not exists
            dbExecute(
                "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'admin', 'active')",
                ['Admin', $new_email, $hashed]
            );
        }
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Reset — Gujju Clothing</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Outfit',sans-serif;background:#0f0f0f;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .card{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:420px;box-shadow:0 8px 48px rgba(0,0,0,.3)}
    h1{font-size:1.4rem;margin-bottom:6px;color:#0f0f0f}
    .subtitle{color:#888;font-size:.88rem;margin-bottom:28px}
    label{display:block;font-size:.85rem;font-weight:600;margin-bottom:6px;color:#333}
    input{width:100%;padding:12px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:inherit;font-size:.95rem;outline:none;margin-bottom:16px;transition:.3s}
    input:focus{border-color:#c9a96e;box-shadow:0 0 0 3px rgba(201,169,110,.12)}
    button{width:100%;padding:14px;background:#c9a96e;color:#0f0f0f;border:none;border-radius:8px;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;transition:.3s}
    button:hover{background:#a8844d}
    .error{background:#fdedec;color:#e74c3c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.88rem}
    .success{background:#e8f8f0;color:#27ae60;padding:20px;border-radius:8px;text-align:center}
    .success h2{margin-bottom:8px}
    .warning{background:#fef9e7;border:1px solid #f39c12;color:#856404;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.82rem}
    .hint{font-size:.78rem;color:#aaa;margin-top:-10px;margin-bottom:16px}
  </style>
</head>
<body>
<div class="card">

  <?php if ($done): ?>
    <div class="success">
      <h2>&#10003; Admin Updated!</h2>
      <p>Your admin email and password have been changed successfully.</p>
      <br>
      <p><strong>Next step:</strong> Go to your hosting File Manager and <strong>DELETE this file</strong> (reset_admin.php) immediately!</p>
      <br>
      <a href="/admin/index.php" style="color:#c9a96e;font-weight:700">Go to Admin Panel &rarr;</a>
    </div>

  <?php else: ?>
    <h1>&#128274; Admin Reset</h1>
    <p class="subtitle">Gujju Clothing — One-Time Admin Credentials Reset</p>

    <div class="warning">
      &#9888; <strong>Security Notice:</strong> Delete this file from your server immediately after use!
    </div>

    <?php if ($error): ?>
      <div class="error">&#10007; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label>Secret Key *</label>
      <input type="password" name="secret_key" placeholder="Enter the secret key" required />
      <p class="hint">Default key: Gujju2025reset</p>

      <label>New Admin Email *</label>
      <input type="email" name="new_email" placeholder="admin@yourdomain.com" required
             value="<?= htmlspecialchars($_POST['new_email'] ?? '') ?>"/>

      <label>New Password *</label>
      <input type="password" name="new_password" placeholder="Minimum 6 characters" required />

      <button type="submit">&#128274; Update Admin Credentials</button>
    </form>
  <?php endif; ?>

</div>
</body>
</html>
