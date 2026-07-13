<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $user = dbFetchOne("SELECT id, name, email FROM users WHERE email = ? AND status = 'active'", [$email]);
            // Always show success to prevent email enumeration
            $success = 'If an account exists with that email, you will receive password reset instructions shortly.';
            // In production: generate token, store in DB, send email
            // For now: show the success message + optionally a dev note
            if ($user) {
                // In a real implementation you'd send an email here
                // For demo: store reset token in session
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_email']   = $email;
            }
        }
    }
}

$pageTitle = "Forgot Password — Devendra's Shop";
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
      <span><i class="fa fa-gem" style="color:#c9a96e"></i> DEVENDRA'S <span class="accent">SHOP</span></span>
    </div>

    <?php if (!$success): ?>
    <h1 class="auth-title">Forgot Password?</h1>
    <p class="auth-subtitle">Enter your email and we'll send you reset instructions</p>

    <?php if ($error): ?>
    <div class="flash-message flash-error" style="position:static;margin-bottom:18px">
      <i class="fa fa-exclamation-circle"></i> <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-icon">
          <i class="fa fa-envelope"></i>
          <input type="email" id="email" name="email" required placeholder="you@example.com"
                 value="<?= e($_POST['email'] ?? '') ?>"/>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
        <i class="fa fa-paper-plane"></i> Send Reset Link
      </button>
    </form>

    <?php else: ?>
    <div style="text-align:center;padding:20px 0">
      <div style="width:70px;height:70px;border-radius:50%;background:#e8f8f0;margin:0 auto 16px;display:flex;align-items:center;justify-content:center">
        <i class="fa fa-envelope-open-text" style="font-size:1.8rem;color:#27ae60"></i>
      </div>
      <h1 class="auth-title" style="font-size:1.4rem">Check Your Email</h1>
      <p class="auth-subtitle" style="font-size:.92rem;line-height:1.7;margin-bottom:24px">
        <?= e($success) ?><br>
        <small style="color:#aaa">Remember to check your spam folder.</small>
      </p>

      <div style="background:#f9f7f4;border-radius:10px;padding:16px;text-align:left;margin-bottom:20px;font-size:.85rem;color:#555">
        <strong style="display:block;margin-bottom:8px"><i class="fa fa-info-circle" style="color:#c9a96e"></i> For Demo Purposes</strong>
        Since this is a demo, password resets are handled by the admin. Please contact support at <a href="mailto:info@devendras.com" style="color:#c9a96e">info@devendras.com</a> to reset your password.
      </div>
    </div>
    <?php endif; ?>

    <div class="divider">OR</div>
    <p class="auth-footer"><a href="<?= SITE_URL ?>/auth/login.php"><i class="fa fa-arrow-left"></i> Back to Login</a></p>
    <p class="auth-footer" style="margin-top:8px">
      Don't have an account? <a href="<?= SITE_URL ?>/auth/register.php">Create one free</a>
    </p>
  </div>
</main>
</body>
</html>
