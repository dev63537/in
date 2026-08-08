<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Shipping Policy — Gujju Clothing";
$metaDesc  = "Learn about our shipping rates, delivery timelines, and courier partners at Gujju Clothing.";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Shipping Policy</h1>
    <p>Fast, reliable delivery across India</p>
</div>
</section>

<div class="container" style="max-width:900px;padding:60px 20px">

  <!-- Key Info Cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:56px">
    <?php $cards = [
      ['fa-truck','Free Shipping','On orders above ₹999','#27ae60'],
      ['fa-clock','5–7 Days','Standard delivery time','#3498db'],
      ['fa-bolt','2–3 Days','Express delivery option','#e67e22'],
      ['fa-map-marker-alt','All India','We ship pan-India','#c9a96e'],
    ]; foreach ($cards as $c): ?>
    <div style="background:#fff;border-radius:12px;padding:24px;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.07)">
      <div style="width:54px;height:54px;border-radius:50%;background:<?= $c[3] ?>20;border:1px solid <?= $c[3] ?>40;margin:0 auto 12px;display:flex;align-items:center;justify-content:center">
        <i class="fa <?= $c[0] ?>" style="color:<?= $c[3] ?>;font-size:1.2rem"></i>
      </div>
      <div style="font-size:1.1rem;font-weight:800;color:#0f0f0f"><?= $c[1] ?></div>
      <div style="font-size:.82rem;color:#888;margin-top:4px"><?= $c[2] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Policy Content -->
  <?php $sections = [
    ['fa-rupee-sign','Shipping Rates','#27ae60',[
      'FREE shipping on all orders above <strong>₹999</strong>.',
      'A flat charge of <strong>₹99</strong> applies for orders below ₹999.',
      'Express delivery (2–3 days) is available at checkout for <strong>₹199</strong>.',
      'Remote area surcharges may apply for certain pin codes (you will be notified at checkout).',
    ]],
    ['fa-calendar','Delivery Timelines','#3498db',[
      '<strong>Standard Delivery:</strong> 5–7 business days after dispatch.',
      '<strong>Express Delivery:</strong> 2–3 business days after dispatch.',
      'Orders placed before 2:00 PM IST are dispatched the same day (business days only).',
      'Orders placed on weekends or public holidays are dispatched the next business day.',
      'Delivery to remote areas (Jammu & Kashmir, North-East states, Andaman, Lakshadweep) may take 8–12 business days.',
    ]],
    ['fa-box','Order Processing','#e67e22',[
      'All orders go through a quality check before dispatch.',
      'Processing time: 1–2 business days from order placement.',
      'You will receive an email confirmation with tracking details once your order is shipped.',
      'Tracking updates are provided by our courier partners every 24 hours.',
    ]],
    ['fa-shipping-fast','Courier Partners','#c9a96e',[
      '<strong>Delhivery</strong> — Pan-India coverage, real-time tracking.',
      '<strong>BlueDart</strong> — Express & priority shipments.',
      '<strong>India Post</strong> — Remote and rural areas.',
      '<strong>Ekart Logistics</strong> — Metro and Tier-1 cities.',
      'We select the best courier automatically based on your pin code.',
    ]],
    ['fa-exclamation-triangle','Important Notes','#e74c3c',[
      'Ensure your delivery address and pin code are correct before placing an order. We are not responsible for failed deliveries due to incorrect addresses.',
      'If you are unavailable at the time of delivery, the courier will make up to 3 delivery attempts.',
      'After 3 failed attempts, the order may be returned to us. Re-shipping charges will apply.',
      'For bulk or wholesale orders, please contact us separately for custom shipping arrangements.',
    ]],
  ]; foreach ($sections as $s): ?>
  <div style="margin-bottom:40px">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.3rem;margin-bottom:18px;display:flex;align-items:center;gap:12px">
      <span style="width:40px;height:40px;border-radius:50%;background:<?= $s[2] ?>18;border:1px solid <?= $s[2] ?>40;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fa <?= $s[0] ?>" style="color:<?= $s[2] ?>;font-size:.9rem"></i>
      </span>
      <?= $s[1] ?>
    </h2>
    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 1px 8px rgba(0,0,0,.06)">
      <ul style="list-style:none;padding:0">
        <?php foreach ($s[3] as $point): ?>
        <li style="display:flex;gap:12px;padding:10px 0;border-bottom:1px solid #f5f5f5;color:#555;line-height:1.7">
          <i class="fa fa-check-circle" style="color:#c9a96e;margin-top:3px;flex-shrink:0"></i>
          <?= $point ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Contact Box -->
  <div style="background:linear-gradient(135deg,#c9a96e,#a8844d);border-radius:16px;padding:36px;text-align:center;margin-top:16px">
    <h3 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:8px">Questions about your shipment?</h3>
    <p style="margin-bottom:20px;opacity:.9">We're here to help — Mon to Sat, 10AM–7PM IST</p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a href="<?= SITE_URL ?>/pages/track-order.php" class="btn btn-dark"><i class="fa fa-search"></i> Track Order</a>
      <a href="<?= SITE_URL ?>/pages/contact.php" class="btn btn-white"><i class="fa fa-envelope"></i> Contact Us</a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
