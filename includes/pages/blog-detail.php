<?php
require_once __DIR__ . '/../includes/functions.php';
$slug = $_GET['slug'] ?? '';

$posts = [
  'summer-fashion-2025' => [
    'title'=>'Summer Fashion 2025: 10 Must-Have Looks','category'=>'Trends','date'=>'May 20, 2025','read'=>'5 min',
    'image'=>'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=900',
    'content'=>'<p>Summer 2025 is all about celebrating colour, breathability, and bold expression. Whether you\'re attending a beach wedding or navigating a busy work week, these are the looks you need in your wardrobe.</p>
    <h3>1. Linen Everything</h3><p>Linen shirts, linen trousers, linen dresses — the fabric of summer. Breathable, sustainable, and effortlessly stylish. Pair a white linen kurta with straight-cut trousers for an elevated everyday look.</p>
    <h3>2. Pastel Power</h3><p>Dusty rose, sage green, powder blue — soft pastels dominate summer 2025. Layer different pastel tones for a harmonious, serene aesthetic that photographs beautifully.</p>
    <h3>3. Bold Florals</h3><p>Gone are the days of subtle floral prints. This season is all about large-scale botanical prints in vivid colours. Try a floral midi dress with block heels for maximum impact.</p>
    <h3>4. Co-ord Sets</h3><p>Matching sets in cotton, satin, and linen are your best friend this summer. Shop our <a href="'.SITE_URL.'/pages/shop.php" style="color:#c9a96e">Western Wear collection</a> for curated co-ord options.</p>
    <h3>5. Breezy Kaftans</h3><p>The kaftan has made a stunning comeback. Worn as a beach cover-up or styled with a belt as a standalone dress, the kaftan is summer versatility at its finest.</p>',
  ],
  'ethnic-wear-guide' => [
    'title'=>'Complete Guide to Indian Ethnic Wear for Weddings','category'=>'Guide','date'=>'May 15, 2025','read'=>'7 min',
    'image'=>'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=900',
    'content'=>'<p>Weddings are among the most important occasions in Indian culture, and what you wear reflects both your respect for the event and your personal style. Here\'s your complete guide to navigating ethnic wear.</p>
    <h3>Mehendi & Sangeet</h3><p>Lean into bold yellows, greens, and pinks for the mehendi. A printed lehenga or Anarkali kurta works perfectly. For Sangeet, opt for embellished co-ords or a stylish Sharara set.</p>
    <h3>Wedding Ceremony</h3><p>The main wedding calls for your finest ethnic wear. Women can choose from: Banarasi silk sarees, heavy embroidered lehengas, or elegant Anarkali gowns. Men should opt for a sherwani, bandhgala, or a well-tailored kurta-pyjama.</p>
    <h3>Reception</h3><p>The reception offers more flexibility. A fusion look — Indo-western — works beautifully. Think draped sarees with crop blouses, or embroidered blazers with dhoti pants for men.</p>',
  ],
];

$post = $posts[$slug] ?? null;
if (!$post) redirect(SITE_URL . '/pages/blog.php');

$pageTitle = e($post['title']) . " — Gujju Clothing Blog";
$metaDesc  = substr(strip_tags($post['content']), 0, 155);
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <div style="max-width:700px;margin:0 auto;text-align:center">
      <span style="background:#c9a96e;color:#0f0f0f;padding:4px 14px;border-radius:50px;font-size:.75rem;font-weight:700;margin-bottom:16px;display:inline-block"><?= e($post['category']) ?></span>
      <h1 style="font-size:clamp(1.5rem,3vw,2.4rem);margin-bottom:12px"><?= e($post['title']) ?></h1>
      <div style="color:rgba(255,255,255,.6);font-size:.88rem">
        <i class="fa fa-calendar"></i> <?= $post['date'] ?> &nbsp;·&nbsp; <i class="fa fa-clock"></i> <?= $post['read'] ?> read
      </div>
    </div>
</div>
</section>

<div class="container" style="max-width:820px;padding:60px 20px">
  <!-- Featured Image -->
  <div style="border-radius:16px;overflow:hidden;margin-bottom:40px;box-shadow:0 8px 32px rgba(0,0,0,.12)">
    <img src="<?= $post['image'] ?>" alt="<?= e($post['title']) ?>" style="width:100%;max-height:460px;object-fit:cover"/>
  </div>

  <!-- Article Content -->
  <div style="font-size:1.02rem;line-height:1.85;color:#333">
    <style>
      .blog-content h3{font-family:'Playfair Display',serif;font-size:1.3rem;margin:28px 0 12px;color:#0f0f0f}
      .blog-content p{margin-bottom:18px}
      .blog-content a{color:#c9a96e;text-decoration:underline}
    </style>
    <div class="blog-content"><?= $post['content'] ?></div>
  </div>

  <!-- Share -->
  <div style="display:flex;align-items:center;gap:12px;margin-top:40px;padding-top:24px;border-top:1px solid #f0ece6;flex-wrap:wrap">
    <span style="font-weight:600;font-size:.9rem">Share:</span>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL.'/pages/blog-detail.php?slug='.$slug) ?>" target="_blank" class="btn btn-outline" style="padding:8px 16px;font-size:.85rem"><i class="fab fa-facebook"></i> Facebook</a>
    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode(SITE_URL.'/pages/blog-detail.php?slug='.$slug) ?>" target="_blank" class="btn btn-outline" style="padding:8px 16px;font-size:.85rem"><i class="fab fa-twitter"></i> Twitter</a>
    <a href="https://wa.me/?text=<?= urlencode($post['title'].' '.SITE_URL.'/pages/blog-detail.php?slug='.$slug) ?>" target="_blank" class="btn btn-outline" style="padding:8px 16px;font-size:.85rem"><i class="fab fa-whatsapp"></i> WhatsApp</a>
  </div>

  <!-- Back -->
  <div style="margin-top:28px">
    <a href="<?= SITE_URL ?>/pages/blog.php" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back to Blog</a>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
