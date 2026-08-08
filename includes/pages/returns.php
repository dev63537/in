<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Returns & Exchanges — Gujju Clothing";
$metaDesc  = "Easy 15-day returns and exchanges at Gujju Clothing. Learn how to return or exchange your order.";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Returns &amp; Exchanges</h1>
    <p>Hassle-free 15-day returns — no questions asked</p>
</div>
</section>

<div class="container" style="max-width:900px;padding:60px 20px">

  <!-- Return Steps -->
  <div style="background:#fff;border-radius:16px;padding:40px;box-shadow:0 2px 16px rgba(0,0,0,.08);margin-bottom:50px">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;text-align:center;margin-bottom:36px">How to Return / Exchange</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:24px;text-align:center">
      <?php $steps = [
        ['1','fa-envelope','Contact Us','Email or call us within 15 days of delivery with your order number.'],
        ['2','fa-box','Pack Items','Pack the items securely in original packaging with all tags attached.'],
        ['3','fa-truck','Free Pickup','We arrange free pickup from your doorstep within 2–3 business days.'],
        ['4','fa-check-circle','Refund / Exchange','Refund processed in 5–7 days or exchange dispatched within 3 days.'],
      ]; foreach ($steps as $s): ?>
      <div>
        <div style="width:56px;height:56px;border-radius:50%;background:#c9a96e;color:#0f0f0f;font-size:1.3rem;font-weight:800;margin:0 auto 16px;display:flex;align-items:center;justify-content:center"><?= $s[0] ?></div>
        <i class="fa <?= $s[1] ?>" style="font-size:1.4rem;color:#c9a96e;margin-bottom:10px;display:block"></i>
        <div style="font-weight:700;margin-bottom:6px"><?= $s[2] ?></div>
        <div style="font-size:.85rem;color:#888;line-height:1.6"><?= $s[3] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Policy Details -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:40px">
    <div style="background:#e8f8f0;border-radius:12px;padding:28px">
      <h3 style="color:#27ae60;margin-bottom:16px;font-size:1.1rem"><i class="fa fa-check-circle"></i> Eligible for Return</h3>
      <ul style="list-style:none;padding:0;color:#333">
        <?php $yes = ['Unworn and unwashed items','Items with original tags intact','Items in original packaging','Wrong item received','Damaged / defective items','Size or colour mismatch']; foreach ($yes as $y): ?>
        <li style="padding:6px 0;display:flex;gap:8px;align-items:flex-start"><i class="fa fa-check" style="color:#27ae60;margin-top:3px;flex-shrink:0"></i><?= $y ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div style="background:#fdedec;border-radius:12px;padding:28px">
      <h3 style="color:#e74c3c;margin-bottom:16px;font-size:1.1rem"><i class="fa fa-times-circle"></i> Not Eligible for Return</h3>
      <ul style="list-style:none;padding:0;color:#333">
        <?php $no = ['Items returned after 15 days','Worn, washed, or altered items','Items without original tags','Innerwear / lingerie (hygiene reasons)','Customized or personalized items','Sale items marked as "Final Sale"']; foreach ($no as $n): ?>
        <li style="padding:6px 0;display:flex;gap:8px;align-items:flex-start"><i class="fa fa-times" style="color:#e74c3c;margin-top:3px;flex-shrink:0"></i><?= $n ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Refund Info -->
  <div style="background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.07);margin-bottom:36px">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.3rem;margin-bottom:20px"><i class="fa fa-rupee-sign" style="color:#c9a96e"></i> Refund Information</h2>
    <table style="width:100%;border-collapse:collapse">
      <thead><tr style="background:#f8f6f2">
        <th style="padding:12px 16px;text-align:left;font-size:.8rem;text-transform:uppercase;color:#888">Payment Method</th>
        <th style="padding:12px 16px;text-align:left;font-size:.8rem;text-transform:uppercase;color:#888">Refund Mode</th>
        <th style="padding:12px 16px;text-align:left;font-size:.8rem;text-transform:uppercase;color:#888">Timeline</th>
      </tr></thead>
      <tbody>
        <?php $rows = [
          ['UPI / Net Banking','Back to source account','2–5 business days'],
          ['Credit / Debit Card','Back to card','5–7 business days'],
          ['Cash on Delivery','Bank transfer / UPI','5–7 business days'],
          ['Store Credit (Coupon)','Coupon code to email','Instant'],
        ]; foreach ($rows as $r): ?>
        <tr style="border-bottom:1px solid #f5f5f5">
          <td style="padding:14px 16px;font-size:.9rem"><?= $r[0] ?></td>
          <td style="padding:14px 16px;font-size:.9rem;color:#555"><?= $r[1] ?></td>
          <td style="padding:14px 16px;font-size:.9rem"><span style="background:#e8f8f0;color:#27ae60;padding:3px 10px;border-radius:50px;font-size:.8rem;font-weight:600"><?= $r[2] ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Contact -->
  <div style="background:linear-gradient(135deg,#0f0f0f,#1a1a1a);border-radius:16px;padding:36px;text-align:center;color:#fff">
    <h3 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:8px">Need to start a return?</h3>
    <p style="color:rgba(255,255,255,.7);margin-bottom:8px">Email: <a href="mailto:returns@gujjuclothing.com" style="color:#c9a96e">returns@gujjuclothing.com</a></p>
    <p style="color:rgba(255,255,255,.7);margin-bottom:24px">Phone: <a href="tel:+919876543210" style="color:#c9a96e">+91 98765 43210</a> (Mon–Sat, 10AM–7PM)</p>
    <a href="<?= SITE_URL ?>/pages/contact.php" class="btn btn-primary"><i class="fa fa-envelope"></i> Contact Support</a>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
