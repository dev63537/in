<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Affiliate Program — Devendra's Shop";
$metaDesc  = "Earn up to 15% commission by promoting Devendra's Shop products. Join our affiliate program today.";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Affiliate Program</h1>
    <p>Earn money by promoting fashion you love</p>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Affiliate Program</nav>
  </div>
</section>

<div class="container" style="padding:60px 20px">
  <!-- Hero Stats -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-bottom:64px">
    <?php foreach ([
      ['fa-percentage','Up to 15%','Commission Rate','#c9a96e'],
      ['fa-calendar','30 Days','Cookie Duration','#3498db'],
      ['fa-rupee-sign','₹500+','Min Payout','#27ae60'],
      ['fa-users','5,000+','Active Affiliates','#e67e22'],
    ] as $s): ?>
    <div style="background:#fff;border-radius:14px;padding:28px;text-align:center;box-shadow:0 2px 14px rgba(0,0,0,.07)">
      <div style="width:54px;height:54px;border-radius:50%;background:<?= $s[3] ?>18;border:1px solid <?= $s[3] ?>30;margin:0 auto 12px;display:flex;align-items:center;justify-content:center">
        <i class="fa <?= $s[0] ?>" style="color:<?= $s[3] ?>;font-size:1.1rem"></i>
      </div>
      <div style="font-size:1.5rem;font-weight:800;color:#0f0f0f"><?= $s[1] ?></div>
      <div style="font-size:.82rem;color:#888;margin-top:4px"><?= $s[2] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- How it Works -->
  <div class="section-header"><h2>How It Works</h2><p>Three simple steps to start earning</p></div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;margin:40px 0 64px">
    <?php foreach ([
      ['1','fa-user-plus','Sign Up','Create your free affiliate account. It takes less than 2 minutes.','#c9a96e'],
      ['2','fa-link','Share Links','Get your unique tracking links and share them on your blog, social media, or YouTube.','#3498db'],
      ['3','fa-rupee-sign','Earn','Earn up to 15% commission on every sale made through your links.','#27ae60'],
    ] as $s): ?>
    <div style="background:#fff;border-radius:14px;padding:30px;box-shadow:0 2px 14px rgba(0,0,0,.07);text-align:center">
      <div style="width:56px;height:56px;border-radius:50%;background:<?= $s[4] ?>;color:#0f0f0f;font-size:1.3rem;font-weight:800;margin:0 auto 14px;display:flex;align-items:center;justify-content:center"><?= $s[0] ?></div>
      <i class="fa <?= $s[1] ?>" style="font-size:1.4rem;color:<?= $s[4] ?>;margin-bottom:10px;display:block"></i>
      <div style="font-weight:700;font-size:1rem;margin-bottom:8px"><?= $s[2] ?></div>
      <div style="font-size:.87rem;color:#888;line-height:1.6"><?= $s[3] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Commission Tiers -->
  <div class="section-header"><h2>Commission Tiers</h2><p>Earn more as you grow</p></div>
  <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);margin:36px 0 64px">
    <table style="width:100%;border-collapse:collapse">
      <thead><tr style="background:#c9a96e;color:#0f0f0f">
        <th style="padding:16px 20px;text-align:left;font-size:.88rem">Tier</th>
        <th style="padding:16px 20px;text-align:center;font-size:.88rem">Monthly Sales</th>
        <th style="padding:16px 20px;text-align:center;font-size:.88rem">Commission</th>
        <th style="padding:16px 20px;text-align:center;font-size:.88rem">Cookie Duration</th>
        <th style="padding:16px 20px;text-align:center;font-size:.88rem">Bonuses</th>
      </tr></thead>
      <tbody>
        <?php foreach ([
          ['Starter','₹0 – ₹10,000','8%','30 days','Basic support'],
          ['Silver','₹10,001 – ₹50,000','10%','30 days','+ Early access to sales'],
          ['Gold','₹50,001 – ₹1,00,000','12%','45 days','+ Co-branded content'],
          ['Platinum','₹1,00,001+','15%','60 days','+ Dedicated manager + Gift hamper'],
        ] as $i => $t): ?>
        <tr style="background:<?= $i%2?'#fafafa':'#fff' ?>;border-bottom:1px solid #f0ece6">
          <td style="padding:16px 20px;font-weight:700;color:<?= ['#888','#888','#c9a96e','#0f0f0f'][$i] ?>"><?= $t[0] ?></td>
          <td style="padding:16px 20px;text-align:center;font-size:.9rem"><?= $t[1] ?></td>
          <td style="padding:16px 20px;text-align:center;font-weight:800;font-size:1rem;color:#c9a96e"><?= $t[2] ?></td>
          <td style="padding:16px 20px;text-align:center;font-size:.9rem"><?= $t[3] ?></td>
          <td style="padding:16px 20px;text-align:center;font-size:.85rem;color:#555"><?= $t[4] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Apply Form -->
  <div style="background:linear-gradient(135deg,#0f0f0f,#252525);border-radius:20px;padding:48px;color:#fff">
    <div style="max-width:600px;margin:0 auto;text-align:center">
      <h2 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:8px">Ready to Start Earning?</h2>
      <p style="color:rgba(255,255,255,.7);margin-bottom:32px">Fill in your details and our team will get back to you within 48 hours.</p>
      <form onsubmit="this.querySelector('button').textContent='✓ Application Sent!';return false" style="text-align:left;display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="form-group">
          <label style="color:rgba(255,255,255,.8)">Full Name *</label>
          <input type="text" required placeholder="Your name" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);color:#fff"/>
        </div>
        <div class="form-group">
          <label style="color:rgba(255,255,255,.8)">Email *</label>
          <input type="email" required placeholder="you@example.com" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);color:#fff"/>
        </div>
        <div class="form-group">
          <label style="color:rgba(255,255,255,.8)">Website / Social Media *</label>
          <input type="url" required placeholder="https://yourwebsite.com" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);color:#fff"/>
        </div>
        <div class="form-group">
          <label style="color:rgba(255,255,255,.8)">Monthly Audience Size</label>
          <select style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);color:#fff;width:100%;padding:12px 16px;border-radius:8px;border:1.5px solid rgba(255,255,255,.2);font-family:inherit;outline:none">
            <option value="">Select range</option>
            <option>0 – 1,000</option><option>1,000 – 10,000</option>
            <option>10,000 – 1,00,000</option><option>1,00,000+</option>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label style="color:rgba(255,255,255,.8)">Tell us about your content / audience</label>
          <textarea rows="3" placeholder="e.g. I run a fashion blog focused on…" style="width:100%;padding:12px 16px;background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.2);border-radius:8px;color:#fff;font-family:inherit;resize:vertical;outline:none"></textarea>
        </div>
        <div style="grid-column:1/-1;text-align:center">
          <button type="submit" class="btn btn-primary" style="padding:14px 32px;font-size:1rem"><i class="fa fa-paper-plane"></i> Apply Now</button>
        </div>
      </form>
    </div>
  </div>
</div>
<style>.form-group input::placeholder,.form-group textarea::placeholder{color:rgba(255,255,255,.4)}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
