<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
if (isLoggedIn()) { redirect(SITE_URL . '/index.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Invalid request.'; }
    else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) {
            $error = 'Please fill in all fields.';
        } else {
            $user = dbFetchOne("SELECT id, name, email, password, role FROM users WHERE email = ? AND status = 'active'", [$email]);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                session_regenerate_id(true);
                $redirect = $_GET['redirect'] ?? ($user['role'] === 'admin' ? '/admin/index.php' : '/index.php');
                redirect(SITE_URL . $redirect);
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
$pageTitle = "Login — Gujju Clothing";
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
    <h1 class="auth-title">Welcome Back</h1>
    <p class="auth-subtitle">Sign in to your account</p>

    <?php if ($error): ?>
      <div class="flash-message flash-error" style="position:static;margin-bottom:18px;">
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
      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-icon">
          <i class="fa fa-lock"></i>
          <input type="password" id="password" name="password" required placeholder="Your password"/>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
        <i class="fa fa-sign-in-alt"></i> Sign In
      </button>
    </form>

    <div class="divider">OR</div>
    <p class="auth-footer">
      Don't have an account? <a href="<?= SITE_URL ?>/auth/register.php">Create one free</a>
    </p>
    <p class="auth-footer" style="margin-top:8px">
      <a href="<?= SITE_URL ?>/auth/forgot-password.php"><i class="fa fa-key"></i> Forgot Password?</a>
    </p>
    <p class="auth-footer" style="margin-top:8px">
      <a href="<?= SITE_URL ?>/index.php"><i class="fa fa-home"></i> Back to Home</a>
    </p>
  </div>
</main>
</body>
</html>
