<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Contact Us — Devendra's Shop";
$sent = false; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $msg     = trim($_POST['message'] ?? '');
    if ($name && $email && $msg) { $sent = true; }
    else { $error = 'Please fill in all required fields.'; }
}
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero"><div class="container"><h1>Contact Us</h1><p>We are here to help — get in touch!</p></div></section>
<div class="container">
  <div class="contact-grid">
    <div>
      <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:24px">Get In Touch</h2>
      <div class="contact-info-item"><div class="contact-icon"><i class="fa fa-map-marker-alt"></i></div><div><h4>Our Store</h4><p style="color:#888">42 Fashion Street, Mumbai, Maharashtra 400001, India</p></div></div>
      <div class="contact-info-item"><div class="contact-icon"><i class="fa fa-phone"></i></div><div><h4>Phone</h4><p style="color:#888">+91 98765 43210</p></div></div>
      <div class="contact-info-item"><div class="contact-icon"><i class="fa fa-envelope"></i></div><div><h4>Email</h4><p style="color:#888">info@devendras.com</p></div></div>
      <div class="contact-info-item"><div class="contact-icon"><i class="fa fa-clock"></i></div><div><h4>Hours</h4><p style="color:#888">Mon-Sat: 10:00 AM – 7:00 PM IST</p></div></div>
    </div>
    <div class="contact-form-wrap">
      <?php if ($sent): ?>
        <div style="text-align:center;padding:40px 0"><i class="fa fa-check-circle" style="font-size:3rem;color:#27ae60;display:block;margin-bottom:12px"></i><h3>Message Sent!</h3><p style="color:#888;margin-top:8px">We will get back to you within 24 hours.</p></div>
      <?php else: ?>
        <?php if ($error): ?><div class="flash-message flash-error" style="position:static;margin-bottom:16px"><?= e($error) ?></div><?php endif; ?>
        <h3 style="margin-bottom:20px;font-family:'Playfair Display',serif">Send a Message</h3>
        <form method="POST">
          <div class="form-group"><label>Name *</label><input type="text" name="name" required value="<?= e($_POST['name']??'') ?>"/></div>
          <div class="form-group"><label>Email *</label><input type="email" name="email" required value="<?= e($_POST['email']??'') ?>"/></div>
          <div class="form-group"><label>Subject</label><input type="text" name="subject" value="<?= e($_POST['subject']??'') ?>"/></div>
          <div class="form-group"><label>Message *</label><textarea name="message" rows="5" required><?= e($_POST['message']??'') ?></textarea></div>
          <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Send Message</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
