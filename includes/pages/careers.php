<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Careers — Gujju Clothing";
$metaDesc  = "Join the Gujju Clothing team. Explore open positions in fashion, tech, marketing and operations.";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Careers at Gujju Clothing</h1>
    <p>Join our passionate team and help shape the future of fashion</p>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Careers</nav>
  </div>
</section>

<div class="container" style="padding:60px 20px">
  <!-- Why Join Us -->
  <div style="text-align:center;margin-bottom:64px">
    <div class="section-header"><h2>Why Join Us?</h2><p>More than a job — it's a community</p></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;margin-top:40px">
      <?php foreach ([
        ['fa-heart','Passionate Culture','We love what we do and it shows — from how we treat customers to how we support each other.'],
        ['fa-chart-line','Growth Opportunities','Fast-growing company means rapid career growth and new challenges every day.'],
        ['fa-gift','Great Benefits','Competitive salary, health benefits, employee discounts, and flexible working hours.'],
        ['fa-lightbulb','Innovation First','We encourage bold ideas and give you the freedom to make a real impact.'],
      ] as $b): ?>
      <div style="background:#fff;border-radius:14px;padding:28px;box-shadow:0 2px 14px rgba(0,0,0,.07);text-align:center">
        <div style="width:56px;height:56px;border-radius:50%;background:rgba(201,169,110,.12);border:1px solid rgba(201,169,110,.3);margin:0 auto 14px;display:flex;align-items:center;justify-content:center">
          <i class="fa <?= $b[0] ?>" style="color:#c9a96e;font-size:1.2rem"></i>
        </div>
        <div style="font-weight:700;margin-bottom:8px"><?= $b[1] ?></div>
        <div style="font-size:.87rem;color:#888;line-height:1.6"><?= $b[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Open Positions -->
  <div class="section-header"><h2>Open Positions</h2><p>Find your perfect role</p></div>
  <div style="margin-top:36px">
    <?php $jobs = [
      ['Fashion Buyer','Mumbai (Hybrid)','Full-time','Operations','You will source and curate the next season\'s collection, working closely with manufacturers and designers to ensure quality, pricing, and trend alignment.','3+ years in fashion buying or merchandising'],
      ['Frontend Developer','Remote','Full-time','Technology','Build and optimize our e-commerce platform. You will work with PHP, JavaScript, and modern CSS to create fast, beautiful user experiences.','Proficient in HTML, CSS, JS, PHP'],
      ['Digital Marketing Manager','Mumbai','Full-time','Marketing','Own our digital marketing strategy across SEO, social media, email, and paid ads. Drive growth and build the Gujju\'s Shop brand online.','3+ years digital marketing experience'],
      ['Customer Support Executive','Mumbai (WFH)','Full-time','Support','Provide exceptional support to our customers via email, chat, and phone. Resolve queries, process returns, and ensure every customer leaves happy.','Excellent communication, empathy'],
      ['Graphic Designer (Fashion)','Remote','Freelance','Creative','Create stunning visual content for our social media, website, and campaigns. Passion for fashion and a strong portfolio are must-haves.','Portfolio of fashion/lifestyle design work'],
    ]; foreach ($jobs as $i => $job): ?>
    <div style="background:#fff;border-radius:14px;padding:28px;box-shadow:0 2px 14px rgba(0,0,0,.07);margin-bottom:18px;transition:.3s" onmouseover="this.style.boxShadow='0 6px 28px rgba(0,0,0,.12)'" onmouseout="this.style.boxShadow='0 2px 14px rgba(0,0,0,.07)'">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:12px">
        <div>
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:6px"><?= $job[0] ?></h3>
          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <span style="background:#f9f7f4;color:#c9a96e;padding:3px 10px;border-radius:50px;font-size:.75rem;font-weight:600"><i class="fa fa-map-marker-alt"></i> <?= $job[1] ?></span>
            <span style="background:#f9f7f4;color:#555;padding:3px 10px;border-radius:50px;font-size:.75rem"><i class="fa fa-clock"></i> <?= $job[2] ?></span>
            <span style="background:#f9f7f4;color:#555;padding:3px 10px;border-radius:50px;font-size:.75rem"><i class="fa fa-briefcase"></i> <?= $job[3] ?></span>
          </div>
        </div>
        <a href="mailto:careers@gujjuclothing.com?subject=Application: <?= urlencode($job[0]) ?>" class="btn btn-primary" style="padding:9px 20px;font-size:.87rem"><i class="fa fa-paper-plane"></i> Apply Now</a>
      </div>
      <p style="color:#555;font-size:.9rem;line-height:1.7;margin-bottom:10px"><?= $job[4] ?></p>
      <div style="font-size:.82rem;color:#888"><i class="fa fa-check-circle" style="color:#c9a96e"></i> Required: <?= $job[5] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- No Position Found -->
  <div style="background:linear-gradient(135deg,#0f0f0f,#1a1a1a);border-radius:16px;padding:44px;text-align:center;color:#fff;margin-top:48px">
    <h3 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:8px">Don't see the right role?</h3>
    <p style="color:rgba(255,255,255,.7);margin-bottom:24px">We're always looking for talented people. Send us your CV and tell us how you'd like to contribute.</p>
    <a href="mailto:careers@gujjuclothing.com" class="btn btn-primary"><i class="fa fa-envelope"></i> Email Your CV</a>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
