<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
if (isLoggedIn()) { redirect(SITE_URL . '/index.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid request.'; }
    else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$name)                             $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if (strlen($password) < 6)              $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm)             $errors[] = 'Passwords do not match.';

        if (!$errors) {
            $exists = dbFetchOne("SELECT id FROM users WHERE email = ?", [$email]);
            if ($exists) {
                $errors[] = 'This email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                dbExecute("INSERT INTO users (name,email,phone,password,role) VALUES (?,?,?,?,'customer')",
                          [$name, $email, $phone, $hash]);
                $id = dbLastId();
                $_SESSION['user_id']   = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'customer';
                session_regenerate_id(true);
                setFlash('success', "Welcome to Devendra's Shop, " . $name . '!');
                redirect(SITE_URL . '/index.php');
            }
        }
    }
}
$pageTitle = "Register — Devendra's Shop";
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
    <h1 class="auth-title">Create Account</h1>
    <p class="auth-subtitle">Join the Devendra's Shop family</p>

    <?php if ($errors): ?>
      <div class="flash-message flash-error" style="position:static;margin-bottom:18px;flex-direction:column;align-items:flex-start;gap:4px">
        <?php foreach ($errors as $e): ?><div><i class="fa fa-circle-dot"></i> <?= e($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <div class="form-group">
        <label>Full Name</label>
        <div class="input-icon"><i class="fa fa-user"></i>
          <input type="text" name="name" required placeholder="Your full name" value="<?= e($_POST['name'] ?? '') ?>"/>
        </div>
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <div class="input-icon"><i class="fa fa-envelope"></i>
          <input type="email" name="email" required placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>"/>
        </div>
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <div class="input-icon"><i class="fa fa-phone"></i>
          <input type="tel" name="phone" placeholder="+91 98765 43210" value="<?= e($_POST['phone'] ?? '') ?>"/>
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-icon"><i class="fa fa-lock"></i>
          <input type="password" name="password" required placeholder="Min. 6 characters"/>
        </div>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <div class="input-icon"><i class="fa fa-lock"></i>
          <input type="password" name="confirm_password" required placeholder="Repeat password"/>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
        <i class="fa fa-user-plus"></i> Create Account
      </button>
    </form>
    <p class="auth-footer" style="margin-top:16px">
      Already have an account? <a href="<?= SITE_URL ?>/auth/login.php">Sign In</a>
    </p>
  </div>
</main>
</body>
</html>
