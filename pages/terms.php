<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Terms of Service — Gujju Clothing";
$metaDesc  = "Read the terms and conditions governing your use of Gujju Clothing website and services.";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Terms of Service</h1>
    <p>Last updated: January 1, 2025</p>
</div>
</section>

<div class="container" style="max-width:860px;padding:60px 20px">
  <div style="background:#f9f7f4;border-radius:10px;padding:18px 22px;margin-bottom:36px;color:#555;font-size:.92rem;line-height:1.7">
    By accessing or using Gujju Clothing ("the Site"), you agree to these Terms of Service. Please read them carefully. If you do not agree with these terms, please do not use our website.
  </div>

  <?php $sections = [
    ['1. Acceptance of Terms', [
      'By creating an account or placing an order, you confirm that you are at least 18 years of age and have the legal capacity to enter into a binding agreement.',
      'These terms apply to all visitors, users, and customers of the Site.',
    ]],
    ['2. Account Registration', [
      'You are responsible for maintaining the confidentiality of your account credentials.',
      'You must provide accurate, complete, and current information during registration.',
      'You are responsible for all activities that occur under your account.',
      'Notify us immediately of any unauthorized use of your account.',
      'We reserve the right to terminate accounts that violate these terms.',
    ]],
    ['3. Products & Pricing', [
      'All product descriptions and prices are subject to change without notice.',
      'We strive to display product images and colors accurately, but slight variations may occur due to screen settings.',
      'Prices are listed in Indian Rupees (₹) and are inclusive of applicable taxes.',
      'We reserve the right to cancel orders if a product is found to be mispriced due to a technical error.',
    ]],
    ['4. Orders & Payment', [
      'Placing an order constitutes an offer to purchase the selected item at the listed price.',
      'We reserve the right to refuse or cancel any order for any reason, including stock unavailability or suspected fraud.',
      'Payment must be completed before your order is dispatched (except for Cash on Delivery orders).',
      'All financial transactions are secured using industry-standard SSL encryption.',
    ]],
    ['5. Shipping & Delivery', [
      'Delivery timelines are estimates and are not guaranteed. We are not liable for delays caused by courier partners, natural disasters, or circumstances beyond our control.',
      'Risk of loss and title pass to you upon delivery to the specified address.',
      'Please refer to our full <a href="'.SITE_URL.'/pages/shipping.php" style="color:#c9a96e">Shipping Policy</a> for details.',
    ]],
    ['6. Returns & Refunds', [
      'Returns are accepted within 15 days of delivery for eligible items.',
      'Please refer to our <a href="'.SITE_URL.'/pages/returns.php" style="color:#c9a96e">Returns & Exchanges Policy</a> for the complete process and eligibility criteria.',
      'Refunds are processed within 5–7 business days of receiving and inspecting the returned item.',
    ]],
    ['7. Intellectual Property', [
      'All content on the Site — including logos, images, text, and design — is owned by Gujju\'s Shop and protected by copyright laws.',
      'You may not reproduce, distribute, or create derivative works from our content without prior written permission.',
    ]],
    ['8. Prohibited Conduct', [
      'You must not use the Site for any fraudulent or illegal purpose.',
      'You must not interfere with the security or functionality of the Site.',
      'You must not post false reviews or misleading information.',
      'You must not attempt to gain unauthorized access to any part of the Site.',
    ]],
    ['9. Limitation of Liability', [
      'Gujju\'s Shop is not liable for any indirect, incidental, or consequential damages arising from your use of the Site.',
      'Our total liability for any claim arising from these terms shall not exceed the amount you paid for the order in question.',
    ]],
    ['10. Governing Law', [
      'These terms are governed by the laws of India. Any disputes shall be subject to the exclusive jurisdiction of the courts in Mumbai, Maharashtra.',
    ]],
    ['11. Changes to Terms', [
      'We reserve the right to modify these terms at any time. The updated version will be posted on this page with the revised date.',
      'Continued use of the Site after changes constitutes your acceptance of the new terms.',
    ]],
    ['12. Contact', [
      'For any questions about these terms, contact us at: <a href="mailto:legal@gujjuclothing.com" style="color:#c9a96e">legal@gujjuclothing.com</a>',
    ]],
  ]; foreach ($sections as $s): ?>
  <div style="margin-bottom:28px">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:12px;color:#0f0f0f;padding-bottom:8px;border-bottom:1px solid #f0ece6"><?= $s[0] ?></h2>
    <ul style="list-style:none;padding:0;color:#555">
      <?php foreach ($s[1] as $p): ?>
      <li style="padding:7px 0;display:flex;gap:10px;align-items:flex-start;line-height:1.75;border-bottom:1px dotted #f5f5f5">
        <i class="fa fa-circle" style="font-size:.4rem;color:#c9a96e;margin-top:9px;flex-shrink:0"></i><?= $p ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endforeach; ?>

  <div style="text-align:center;margin-top:36px;padding:24px;background:#f9f7f4;border-radius:12px">
    <p style="color:#888;font-size:.85rem">Questions about our terms? <a href="<?= SITE_URL ?>/pages/contact.php" style="color:#c9a96e">Contact us</a>.</p>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
