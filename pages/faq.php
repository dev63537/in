<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "FAQ — Gujju Clothing";
$metaDesc  = "Frequently asked questions about shipping, returns, payments and more at Gujju Clothing.";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Frequently Asked Questions</h1>
    <p>Everything you need to know about shopping with us</p>
</div>
</section>

<div class="container" style="max-width:860px;padding:60px 20px">

  <!-- Search FAQ -->
  <div style="background:#fff;border-radius:12px;padding:28px;box-shadow:0 2px 16px rgba(0,0,0,.07);margin-bottom:48px;text-align:center">
    <h3 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:16px">How can we help you?</h3>
    <div style="display:flex;gap:10px;max-width:500px;margin:0 auto">
      <input type="text" id="faq-search" placeholder="Search questions…" oninput="filterFaq(this.value)"
             style="flex:1;padding:12px 18px;border:1.5px solid #e0e0e0;border-radius:50px;font-family:inherit;font-size:.95rem;outline:none">
      <button style="padding:12px 22px;background:#c9a96e;border:none;border-radius:50px;font-weight:600;cursor:pointer"><i class="fa fa-search"></i></button>
    </div>
  </div>

  <?php
  $sections = [
    'Shipping & Delivery' => [
      ['Q: How long does delivery take?',     'A: Standard delivery takes 5–7 business days. Express delivery (2–3 days) is available at checkout for an additional charge.'],
      ['Q: Do you ship across India?',        'A: Yes! We ship to all pin codes across India through our trusted courier partners — Delhivery, BlueDart, and India Post.'],
      ['Q: What is the shipping charge?',     'A: Shipping is FREE on all orders above ₹999. A flat charge of ₹99 applies to orders below ₹999.'],
      ['Q: Can I track my order?',            'A: Yes! Once your order is shipped, you will receive a tracking number via email. You can also track it on our <a href="'.SITE_URL.'/pages/track-order.php" style="color:#c9a96e">Track Order</a> page.'],
      ['Q: Do you offer international shipping?', 'A: Currently, we only ship within India. International shipping is coming soon!'],
    ],
    'Returns & Exchanges' => [
      ['Q: What is your return policy?',      'A: We offer hassle-free 15-day returns from the date of delivery. Items must be unworn, unwashed, and in original packaging with tags intact.'],
      ['Q: How do I initiate a return?',      'A: Simply contact us at info@gujjuclothing.com or call +91 98765 43210 with your order number. We will arrange a pickup within 2–3 business days.'],
      ['Q: When will I get my refund?',       'A: Refunds are processed within 5–7 business days after we receive and inspect the returned item. UPI/Bank transfers are instant once initiated.'],
      ['Q: Can I exchange for a different size?', 'A: Absolutely! Exchanges for different sizes (subject to availability) are processed free of charge within the 15-day window.'],
    ],
    'Payments' => [
      ['Q: What payment methods do you accept?', 'A: We accept Cash on Delivery (COD), UPI, Net Banking, Credit/Debit Cards, and all major wallets.'],
      ['Q: Is online payment safe?',          'A: Yes, all transactions are secured with 256-bit SSL encryption. We do not store your card details.'],
      ['Q: How do coupon codes work?',        'A: Enter your coupon code at checkout in the "Apply Coupon" field. Valid coupons will deduct the discount automatically from your total.'],
      ['Q: Can I pay in EMI?',               'A: EMI options are available for orders above ₹3,000 via select banks and credit cards at checkout.'],
    ],
    'Products & Sizing' => [
      ['Q: How do I find my size?',           'A: Visit our <a href="'.SITE_URL.'/pages/size-guide.php" style="color:#c9a96e">Size Guide</a> page for detailed measurements. When in doubt, we recommend sizing up.'],
      ['Q: Are the product colors accurate?', 'A: We strive to display colors as accurately as possible. However, slight variations may occur due to screen display settings.'],
      ['Q: Do you restock sold-out products?','A: Yes, popular items are restocked regularly. You can contact us to be notified when a specific item is back in stock.'],
      ['Q: How do I care for my garments?',  'A: Care instructions are on each product\'s label. Generally: cold wash, gentle cycle, dry in shade, iron on low heat.'],
    ],
    'Account & Orders' => [
      ['Q: Do I need an account to order?',   'A: Yes, an account is required to place orders so we can track your purchases and process returns efficiently.'],
      ['Q: How do I change or cancel my order?', 'A: Orders can be cancelled within 2 hours of placement by contacting us immediately. After dispatch, cancellation is not possible.'],
      ['Q: I forgot my password. What do I do?', 'A: Click <a href="'.SITE_URL.'/auth/forgot-password.php" style="color:#c9a96e">Forgot Password</a> on the login page and we\'ll send you a reset link.'],
      ['Q: Where can I view my order history?', 'A: Log in and go to <a href="'.SITE_URL.'/pages/orders.php" style="color:#c9a96e">My Orders</a> to see all past and current orders.'],
    ],
  ];
  foreach ($sections as $title => $faqs): ?>
  <div class="faq-section" style="margin-bottom:42px">
    <?php
      $iconMap = array(
        'Shipping & Delivery'  => 'truck',
        'Returns & Exchanges'  => 'undo',
        'Payments'             => 'credit-card',
        'Products & Sizing'    => 'ruler',
        'Account & Orders'     => 'user',
      );
      $icon = isset($iconMap[$title]) ? $iconMap[$title] : 'question-circle';
    ?>
    <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #c9a96e;color:#0f0f0f">
      <i class="fa fa-<?= $icon ?>"></i>
      <?= $title ?>
    </h2>
    <?php foreach ($faqs as $i => $faq): ?>
    <div class="faq-item" style="background:#fff;border-radius:10px;margin-bottom:12px;box-shadow:0 1px 8px rgba(0,0,0,.06);overflow:hidden">
      <button class="faq-q" onclick="toggleFaq(this)"
              style="width:100%;text-align:left;padding:18px 22px;background:none;border:none;font-family:inherit;font-size:.95rem;font-weight:600;cursor:pointer;display:flex;justify-content:space-between;align-items:center;color:#0f0f0f">
        <?= htmlspecialchars(substr($faq[0], 3)) ?>
        <i class="fa fa-plus" style="color:#c9a96e;flex-shrink:0;margin-left:12px;transition:.3s"></i>
      </button>
      <div class="faq-a" style="display:none;padding:0 22px 18px;color:#555;line-height:1.8;border-top:1px solid #f0ece6">
        <?= substr($faq[1], 3) ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <!-- Still need help? -->
  <div style="background:linear-gradient(135deg,#0f0f0f,#1a1a1a);border-radius:16px;padding:40px;text-align:center;color:#fff;margin-top:48px">
    <i class="fa fa-headset" style="font-size:2rem;color:#c9a96e;margin-bottom:16px;display:block"></i>
    <h3 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:8px">Still have questions?</h3>
    <p style="color:rgba(255,255,255,.7);margin-bottom:24px">Our support team is available Mon–Sat, 10AM–7PM IST</p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
      <a href="<?= SITE_URL ?>/pages/contact.php" class="btn btn-primary"><i class="fa fa-envelope"></i> Contact Us</a>
      <a href="tel:+919876543210" class="btn btn-white"><i class="fa fa-phone"></i> Call Us</a>
    </div>
  </div>
</div>

<script>
function toggleFaq(btn) {
  const answer = btn.nextElementSibling;
  const icon   = btn.querySelector('i');
  const open   = answer.style.display === 'block';
  // Close all
  document.querySelectorAll('.faq-a').forEach(a => a.style.display = 'none');
  document.querySelectorAll('.faq-q i').forEach(i => { i.className = 'fa fa-plus'; i.style.transform = ''; });
  if (!open) {
    answer.style.display = 'block';
    icon.className = 'fa fa-minus';
    icon.style.transform = 'rotate(180deg)';
  }
}
function filterFaq(q) {
  const term = q.toLowerCase();
  document.querySelectorAll('.faq-item').forEach(item => {
    item.style.display = item.textContent.toLowerCase().includes(term) ? '' : 'none';
  });
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
