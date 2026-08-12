<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$error = '';
$success = '';
$step = 1;
$email = '';
$key = '';
$userName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? 'email';
        $email = trim($_POST['email'] ?? '');
        
        if ($action === 'email') {
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                // Check if email exists
                $user = dbFetchOne("SELECT id, name FROM users WHERE email = ? AND status = 'active'", [$email]);
                if ($user) {
                    $step = 2;
                } else {
                    $error = 'No active account found with that email address.';
                }
            }
        } elseif ($action === 'key') {
            $key = trim($_POST['secret_key'] ?? '');
            if (!$key) {
                $error = 'Please enter the secret key.';
                $step = 2;
            } else {
                $user = dbFetchOne("SELECT id, name FROM users WHERE email = ? AND status = 'active'", [$email]);
                if ($user) {
                    $now = date('Y-m-d H:i:s');
                    $validKeyData = dbFetchOne("SELECT id FROM password_reset_keys WHERE secret_key = ? AND user_id = ? AND used = 0 AND expires_at > ?", [$key, $user['id'], $now]);
                    
                    if ($validKeyData) {
                        $step = 3;
                        $userName = $user['name'];
                    } else {
                        $error = 'Invalid or expired secret key.';
                        $step = 2;
                    }
                } else {
                    $error = 'Invalid user account.';
                }
            }
        } elseif ($action === 'password') {
            $key = trim($_POST['secret_key'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            
            $user = dbFetchOne("SELECT id, name FROM users WHERE email = ? AND status = 'active'", [$email]);
            $now = date('Y-m-d H:i:s');
            $validKeyData = dbFetchOne("SELECT id FROM password_reset_keys WHERE secret_key = ? AND user_id = ? AND used = 0 AND expires_at > ?", [$key, $user['id'], $now]);
            
            if (!$validKeyData) {
                $error = 'Session expired or invalid key.';
                $step = 1;
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
                $step = 3;
                $userName = $user['name'];
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
                $step = 3;
                $userName = $user['name'];
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                dbExecute("UPDATE users SET password = ? WHERE id = ?", [$hashed, $user['id']]);
                dbExecute("UPDATE password_reset_keys SET used = 1 WHERE id = ?", [$validKeyData['id']]);
                $step = 4;
            }
        }
    }
}

$pageTitle = "Forgot Password — Gujju Clothing";
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

    <?php if ($step === 1): ?>
        <h1 class="auth-title">Forgot Password?</h1>
        <p class="auth-subtitle">Enter your email address to begin</p>

        <?php if ($error): ?>
        <div class="flash-message flash-error" style="position:static;margin-bottom:18px">
          <i class="fa fa-exclamation-circle"></i> <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
          <input type="hidden" name="action" value="email"/>
          <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-icon">
              <i class="fa fa-envelope"></i>
              <input type="email" id="email" name="email" required placeholder="you@example.com" value="<?= e($email) ?>"/>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
            Continue <i class="fa fa-arrow-right"></i>
          </button>
        </form>

    <?php elseif ($step === 2): ?>
        <h1 class="auth-title">Recovery Key Required</h1>
        <p class="auth-subtitle" style="font-size: 0.95rem;">Please contact the Admin to get your 32-character secret key. Paste it below to continue.</p>

        <?php if ($error): ?>
        <div class="flash-message flash-error" style="position:static;margin-bottom:18px">
          <i class="fa fa-exclamation-circle"></i> <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
          <input type="hidden" name="action" value="key"/>
          <input type="hidden" name="email" value="<?= e($email) ?>"/>
          <div class="form-group">
            <label>Secret Key</label>
            <div class="input-icon">
              <i class="fa fa-key"></i>
              <input type="text" name="secret_key" required placeholder="Paste your key here" autocomplete="off"/>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
            Verify Key
          </button>
        </form>

    <?php elseif ($step === 3): ?>
        <h1 class="auth-title">Hello, <?= e($userName) ?>!</h1>
        <p class="auth-subtitle">Create a new password for your account.</p>

        <?php if ($error): ?>
        <div class="flash-message flash-error" style="position:static;margin-bottom:18px">
          <i class="fa fa-exclamation-circle"></i> <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
          <input type="hidden" name="action" value="password"/>
          <input type="hidden" name="email" value="<?= e($email) ?>"/>
          <input type="hidden" name="secret_key" value="<?= e($key) ?>"/>
          <div class="form-group">
            <label>New Password</label>
            <div class="input-icon">
              <i class="fa fa-lock"></i>
              <input type="password" name="password" required placeholder="At least 6 characters"/>
            </div>
          </div>
          <div class="form-group">
            <label>Confirm Password</label>
            <div class="input-icon">
              <i class="fa fa-lock"></i>
              <input type="password" name="confirm_password" required placeholder="Confirm new password"/>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
            Update Password
          </button>
        </form>

    <?php elseif ($step === 4): ?>
        <div style="text-align:center;padding:20px 0">
          <div style="width:70px;height:70px;border-radius:50%;background:#e8f8f0;margin:0 auto 16px;display:flex;align-items:center;justify-content:center">
            <i class="fa fa-check" style="font-size:1.8rem;color:#27ae60"></i>
          </div>
          <h1 class="auth-title" style="font-size:1.4rem">Password Updated!</h1>
          <p class="auth-subtitle" style="font-size:.92rem;line-height:1.7;margin-bottom:24px">
            Your password has been successfully reset.
          </p>
          <a href="<?= SITE_URL ?>/auth/login.php" class="btn btn-primary btn-full">Login Now</a>
        </div>
    <?php endif; ?>

    <?php if ($step !== 4): ?>
    <div class="divider">OR</div>
    <p class="auth-footer"><a href="<?= SITE_URL ?>/auth/login.php"><i class="fa fa-arrow-left"></i> Back to Login</a></p>
    <p class="auth-footer" style="margin-top:8px">
      Admin? <a href="<?= SITE_URL ?>/reset_admin.php">Reset your password here</a>
    </p>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
