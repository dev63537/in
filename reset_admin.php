<?php
require_once __DIR__ . '/includes/functions.php';
startSession();

// ==========================================
// HARDCODED ADMIN SECRET KEY
// Change this to whatever you want!
$ADMIN_SECRET_KEY = "GujjuAdmin2026";
// ==========================================

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $key = trim($_POST['key'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (!$email || !$key || !$password || !$confirm) {
            $error = 'Please fill in all fields.';
        } elseif ($key !== $ADMIN_SECRET_KEY) {
            $error = 'Invalid Secret Key!';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            // Check if admin exists
            $admin = dbFetchOne("SELECT id FROM users WHERE email = ? AND role = 'admin'", [$email]);
            
            if (!$admin) {
                $error = 'No admin account found with that email address.';
            } else {
                // Update password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                dbExecute("UPDATE users SET password = ? WHERE id = ?", [$hashed, $admin['id']]);
                
                $success = 'Admin Password has been successfully updated! You can now <a href="auth/login.php" style="color:var(--primary);text-decoration:underline;">login</a>.';
            }
        }
    }
}

$pageTitle = "Admin Password Recovery — Gujju Clothing";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css"/>
</head>
<body>
<main class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="Logo" style="max-height:100px;width:auto;object-fit:contain;margin:0 auto 10px;display:block;" onError="if(this.src.indexOf('png')!=-1){this.src='<?= SITE_URL ?>/assets/images/logo.jpg';}else{this.style.display='none';this.nextElementSibling.style.display='block';}" />
      <span style="display:none;"><i class="fa fa-gem" style="color:var(--copper)"></i> GUJJU <span class="accent">CLOTHING</span></span>
    </div>
    <h1 class="auth-title">Admin Recovery</h1>
    <p class="auth-subtitle">Use the Master Secret Key to reset your admin password</p>

    <?php if ($error): ?>
      <div class="alert alert-danger" style="margin-bottom:15px;text-align:center;"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success" style="margin-bottom:15px;text-align:center;"><?= $success ?></div>
    <?php else: ?>
        <form action="" method="post">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          
          <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="email" class="form-control" placeholder="admin@gujjuclothing.com" required>
          </div>
          <div class="form-group">
            <label>Master Secret Key</label>
            <input type="password" name="key" class="form-control" placeholder="Enter the secret key from the code" required autocomplete="off">
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your new password" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Force Reset Password</button>
        </form>
    <?php endif; ?>
    
    <div style="text-align:center; margin-top:20px; font-size:0.9rem;">
      <a href="<?= SITE_URL ?>/auth/login.php" style="color:#aaa;">Back to Login</a>
    </div>
  </div>
</main>
</body>
</html>
