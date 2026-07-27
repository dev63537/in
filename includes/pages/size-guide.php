<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Size Guide — Gujju Clothing";
$metaDesc  = "Find your perfect fit with our comprehensive size guide for women, men and kids clothing at Gujju Clothing.";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Size Guide</h1>
    <p>Find your perfect fit every time</p>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Size Guide</nav>
  </div>
</section>

<div class="container" style="max-width:960px;padding:60px 20px">

  <!-- Tip Banner -->
  <div style="background:#fff8ee;border:1px solid #c9a96e40;border-radius:12px;padding:20px 24px;margin-bottom:40px;display:flex;gap:14px;align-items:flex-start">
    <i class="fa fa-lightbulb" style="color:#c9a96e;font-size:1.3rem;margin-top:2px;flex-shrink:0"></i>
    <div>
      <strong>Pro Tip:</strong> Take your measurements while wearing your regular innerwear. Use a soft measuring tape and keep it parallel to the floor for accuracy. When between sizes, we recommend sizing up for comfort.
    </div>
  </div>

  <!-- Category Tabs -->
  <div class="tabs-nav" style="margin-bottom:36px">
    <button class="tab-btn active" data-tab="tab-women">Women</button>
    <button class="tab-btn" data-tab="tab-men">Men</button>
    <button class="tab-btn" data-tab="tab-kids">Kids</button>
    <button class="tab-btn" data-tab="tab-measure">How to Measure</button>
  </div>

  <!-- WOMEN -->
  <div id="tab-women" class="tab-panel active">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:20px">Women's Size Chart</h2>
    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden">
        <thead><tr style="background:#c9a96e;color:#0f0f0f">
          <?php foreach (['Size','Chest (in)','Waist (in)','Hips (in)','Chest (cm)','Waist (cm)','Hips (cm)'] as $h): ?>
          <th style="padding:14px 16px;text-align:center;font-size:.85rem;font-weight:700"><?= $h ?></th>
          <?php endforeach; ?>
        </tr></thead>
        <tbody>
          <?php $women = [
            ['XS','30–31','24–25','33–34','76–79','61–64','84–86'],
            ['S', '32–33','26–27','35–36','81–84','66–69','89–91'],
            ['M', '34–35','28–29','37–38','86–89','71–74','94–97'],
            ['L', '36–37','30–31','39–40','91–94','76–79','99–102'],
            ['XL','38–40','32–34','41–43','97–102','81–86','104–109'],
            ['XXL','41–43','35–37','44–46','104–109','89–94','112–117'],
          ]; foreach ($women as $i => $r): ?>
          <tr style="background:<?= $i%2?'#fafafa':'#fff' ?>;border-bottom:1px solid #f0ece6">
            <?php foreach ($r as $cell): ?>
            <td style="padding:13px 16px;text-align:center;font-size:.9rem;<?= $cell===$r[0]?'font-weight:800;color:#c9a96e':'' ?>"><?= $cell ?></td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- MEN -->
  <div id="tab-men" class="tab-panel">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:20px">Men's Size Chart</h2>
    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden">
        <thead><tr style="background:#c9a96e;color:#0f0f0f">
          <?php foreach (['Size','Chest (in)','Waist (in)','Shoulder (in)','Chest (cm)','Waist (cm)'] as $h): ?>
          <th style="padding:14px 16px;text-align:center;font-size:.85rem;font-weight:700"><?= $h ?></th>
          <?php endforeach; ?>
        </tr></thead>
        <tbody>
          <?php $men = [
            ['S', '34–36','28–30','17','86–91','71–76'],
            ['M', '38–40','32–34','18','97–102','81–86'],
            ['L', '42–44','36–38','19','107–112','91–97'],
            ['XL','46–48','40–42','20','117–122','102–107'],
            ['XXL','50–52','44–46','21','127–132','112–117'],
          ]; foreach ($men as $i => $r): ?>
          <tr style="background:<?= $i%2?'#fafafa':'#fff' ?>;border-bottom:1px solid #f0ece6">
            <?php foreach ($r as $cell): ?>
            <td style="padding:13px 16px;text-align:center;font-size:.9rem;<?= $cell===$r[0]?'font-weight:800;color:#c9a96e':'' ?>"><?= $cell ?></td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <h3 style="margin:32px 0 16px;font-size:1.1rem">Trouser / Waist Sizes</h3>
    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden">
        <thead><tr style="background:#1a1a1a;color:#fff">
          <?php foreach (['Waist (in)','28','30','32','34','36','38','40'] as $h): ?>
          <th style="padding:12px 14px;text-align:center;font-size:.82rem"><?= $h ?></th>
          <?php endforeach; ?>
        </tr></thead>
        <tbody><tr style="background:#fff">
          <td style="padding:12px 14px;font-weight:700;text-align:center">Waist (cm)</td>
          <?php foreach (['71','76','81','86','91','97','102'] as $cm): ?>
          <td style="padding:12px 14px;text-align:center;font-size:.9rem;color:#555"><?= $cm ?></td>
          <?php endforeach; ?>
        </tr></tbody>
      </table>
    </div>
  </div>

  <!-- KIDS -->
  <div id="tab-kids" class="tab-panel">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:20px">Kids' Size Chart</h2>
    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden">
        <thead><tr style="background:#c9a96e;color:#0f0f0f">
          <?php foreach (['Age','Size Label','Height (cm)','Chest (cm)','Waist (cm)'] as $h): ?>
          <th style="padding:14px 16px;text-align:center;font-size:.85rem;font-weight:700"><?= $h ?></th>
          <?php endforeach; ?>
        </tr></thead>
        <tbody>
          <?php $kids = [
            ['2–3 Years','2–3Y','92–98','53','52'],
            ['4–5 Years','4–5Y','104–110','57','54'],
            ['6–7 Years','6–7Y','116–122','62','57'],
            ['8–9 Years','8–9Y','128–134','67','60'],
            ['10–11 Years','10–11Y','140–146','73','63'],
            ['12–13 Years','12–13Y','152–158','79','67'],
          ]; foreach ($kids as $i => $r): ?>
          <tr style="background:<?= $i%2?'#fafafa':'#fff' ?>;border-bottom:1px solid #f0ece6">
            <?php foreach ($r as $cell): ?>
            <td style="padding:13px 16px;text-align:center;font-size:.9rem;<?= $cell===$r[1]?'font-weight:800;color:#c9a96e':'' ?>"><?= $cell ?></td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- HOW TO MEASURE -->
  <div id="tab-measure" class="tab-panel">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:24px">How to Take Your Measurements</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px">
      <?php $tips = [
        ['fa-arrows-alt-h','Chest','Measure around the fullest part of your chest, keeping the tape parallel to the floor and your arms relaxed at your sides.'],
        ['fa-compress-alt','Waist','Measure around the narrowest part of your natural waist — usually about 1 inch above your belly button.'],
        ['fa-circle','Hips','Stand with feet together. Measure around the fullest part of your hips and buttocks.'],
        ['fa-ruler-vertical','Length','For dresses/kurtas: measure from the highest point of your shoulder to where you want the hem to fall.'],
      ]; foreach ($tips as $t): ?>
      <div style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.07)">
        <i class="fa <?= $t[0] ?>" style="font-size:1.5rem;color:#c9a96e;margin-bottom:12px;display:block"></i>
        <div style="font-weight:700;margin-bottom:8px"><?= $t[1] ?></div>
        <div style="font-size:.88rem;color:#555;line-height:1.7"><?= $t[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="background:#f9f7f4;border-radius:12px;padding:24px;margin-top:28px">
      <h3 style="font-size:1rem;margin-bottom:12px"><i class="fa fa-info-circle" style="color:#c9a96e"></i> General Tips</h3>
      <ul style="list-style:none;padding:0;color:#555">
        <?php foreach ([
          'Always use a soft, flexible measuring tape — never a rigid ruler.',
          'Measure over light fitting innerwear, not over bulky clothing.',
          'Keep the tape snug but not tight — you should be able to breathe comfortably.',
          'Ask someone to help you measure for more accurate results.',
          'If you are between sizes, we recommend choosing the larger size for comfort.',
        ] as $tip): ?>
        <li style="padding:7px 0;display:flex;gap:10px"><i class="fa fa-check" style="color:#c9a96e;margin-top:4px;flex-shrink:0"></i><?= $tip ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Still Unsure -->
  <div style="text-align:center;margin-top:50px;padding:36px;background:linear-gradient(135deg,#0f0f0f,#1a1a1a);border-radius:16px;color:#fff">
    <h3 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:8px">Still unsure about sizing?</h3>
    <p style="color:rgba(255,255,255,.7);margin-bottom:20px">Our style experts are happy to help you find the perfect fit.</p>
    <a href="<?= SITE_URL ?>/pages/contact.php" class="btn btn-primary"><i class="fa fa-comments"></i> Chat with Stylist</a>
  </div>
</div>

<script>
// Tab switching for size guide
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => { p.style.display='none'; p.classList.remove('active'); });
    btn.classList.add('active');
    const panel = document.getElementById(target);
    if (panel) { panel.style.display = 'block'; panel.classList.add('active'); }
  });
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
