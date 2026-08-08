<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Privacy Policy — Gujju Clothing";
$metaDesc  = "Read Gujju Clothing's privacy policy to understand how we collect, use and protect your personal information.";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Privacy Policy</h1>
    <p>Last updated: January 1, 2025</p>
</div>
</section>

<div class="container" style="max-width:860px;padding:60px 20px">
  <div style="background:#fff8ee;border:1px solid #c9a96e30;border-radius:10px;padding:18px 22px;margin-bottom:36px;color:#555;font-size:.92rem;line-height:1.7">
    <strong>Summary:</strong> We collect only what we need to process your orders and improve your experience. We never sell your personal data. You can request deletion of your data at any time.
  </div>

  <?php $sections = [
    ['Information We Collect', 'fa-database', '#3498db', [
      '<strong>Account Information:</strong> Name, email address, phone number, and password (hashed) when you create an account.',
      '<strong>Order Information:</strong> Shipping address, payment method (we do not store card details), and purchase history.',
      '<strong>Device & Usage Data:</strong> IP address, browser type, pages visited, and time spent — collected via cookies for analytics.',
      '<strong>Communications:</strong> Any messages you send us via our contact form or email.',
    ]],
    ['How We Use Your Information', 'fa-cog', '#27ae60', [
      'To process and fulfil your orders and send you order confirmations and shipping updates.',
      'To create and manage your account.',
      'To respond to your customer service inquiries.',
      'To send you promotional emails and offers — only if you have opted in (you can unsubscribe any time).',
      'To improve our website, products, and user experience through analytics.',
      'To prevent fraud and maintain the security of our platform.',
    ]],
    ['Cookies', 'fa-cookie-bite', '#e67e22', [
      'We use essential cookies to keep you logged in and maintain your shopping cart.',
      'Analytics cookies (via Google Analytics) help us understand how visitors use our site.',
      'You can control cookies through your browser settings. Disabling cookies may affect site functionality.',
    ]],
    ['Data Sharing', 'fa-share-alt', '#8e44ad', [
      'We do <strong>not</strong> sell, rent, or trade your personal information to any third parties.',
      '<strong>Courier Partners:</strong> Your name, address, and phone number are shared with our delivery partners (Delhivery, BlueDart, India Post) solely for order delivery.',
      '<strong>Payment Gateways:</strong> Payment processing is handled by secure third-party processors. We do not store your card details.',
      '<strong>Legal Compliance:</strong> We may disclose your information if required by law or to protect our legal rights.',
    ]],
    ['Data Security', 'fa-shield-alt', '#e74c3c', [
      'All data is transmitted over HTTPS (256-bit SSL encryption).',
      'Passwords are stored using bcrypt hashing — they cannot be reversed.',
      'Our servers are protected by firewalls and regular security audits.',
      'Access to personal data is restricted to authorized personnel only.',
    ]],
    ['Your Rights', 'fa-user-shield', '#c9a96e', [
      '<strong>Access:</strong> You can view all personal data we hold about you by logging into your account or contacting us.',
      '<strong>Correction:</strong> Update your account information at any time from your profile page.',
      '<strong>Deletion:</strong> Request deletion of your account and data by emailing privacy@gujjuclothing.com.',
      '<strong>Opt-Out:</strong> Unsubscribe from marketing emails via the link in any email we send.',
      '<strong>Data Portability:</strong> Request a copy of your data in CSV format.',
    ]],
    ['Data Retention', 'fa-calendar', '#555', [
      'Order records are retained for 7 years for legal and tax compliance.',
      'Account data is retained until you request deletion.',
      'Analytics data is retained for 26 months.',
    ]],
    ['Children\'s Privacy', 'fa-child', '#27ae60', [
      'Our services are not directed to children under 13. We do not knowingly collect personal information from children. If we become aware that we have collected data from a child under 13, we will delete it immediately.',
    ]],
    ['Contact Us', 'fa-envelope', '#c9a96e', [
      'For any privacy-related queries or requests, contact our Data Protection Officer:',
      'Email: <a href="mailto:privacy@gujjuclothing.com" style="color:#c9a96e">privacy@gujjuclothing.com</a>',
      'Phone: +91 98765 43210',
      'Address: 42 Fashion Street, Mumbai, Maharashtra 400001, India',
    ]],
  ]; foreach ($sections as $i => $s): ?>
  <div style="margin-bottom:36px" id="section-<?= $i ?>">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.25rem;margin-bottom:16px;display:flex;align-items:center;gap:12px">
      <span style="width:38px;height:38px;border-radius:50%;background:<?= $s[2] ?>18;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fa <?= $s[1] ?>" style="color:<?= $s[2] ?>;font-size:.85rem"></i>
      </span>
      <?= $s[0] ?>
    </h2>
    <div style="background:#fff;border-radius:10px;padding:22px 26px;box-shadow:0 1px 8px rgba(0,0,0,.05)">
      <ul style="list-style:none;padding:0;color:#555">
        <?php foreach ($s[3] as $p): ?>
        <li style="padding:9px 0;border-bottom:1px solid #f5f5f5;line-height:1.75;display:flex;gap:10px;align-items:flex-start">
          <i class="fa fa-chevron-right" style="color:#c9a96e;margin-top:5px;font-size:.7rem;flex-shrink:0"></i><?= $p ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endforeach; ?>

  <p style="text-align:center;color:#888;font-size:.85rem;margin-top:20px">
    We may update this policy from time to time. Any changes will be posted on this page with the updated date.
  </p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
