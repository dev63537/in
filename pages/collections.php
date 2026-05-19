<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Collections — Devendra's Shop";
$categories = getCategories();
$catImages = [
  1=>'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=800',
  2=>'https://images.unsplash.com/photo-1516257984-b1b4d707412e?w=800',
  3=>'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?w=800',
  4=>'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=800',
  5=>'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800',
  6=>'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800',
];
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container"><h1>Our Collections</h1><p>Explore every style, for every occasion</p></div>
</section>
<div class="container" style="padding:60px 20px">
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:28px">
    <?php foreach ($categories as $cat): $img = $catImages[$cat['id']] ?? 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800'; ?>
    <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['id'] ?>" style="position:relative;border-radius:16px;overflow:hidden;aspect-ratio:4/3;display:block;box-shadow:0 4px 24px rgba(0,0,0,.12)">
      <img src="<?= e($img) ?>" alt="<?= e($cat['name']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform .5s ease"/>
      <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.65) 0%,transparent 50%)"></div>
      <div style="position:absolute;bottom:0;left:0;right:0;padding:24px;color:#fff">
        <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:6px"><?= e($cat['name']) ?></h2>
        <p style="color:rgba(255,255,255,.7);font-size:.9rem"><?= e($cat['description']) ?></p>
        <span style="display:inline-block;margin-top:12px;background:#c9a96e;color:#000;padding:6px 18px;border-radius:50px;font-size:.85rem;font-weight:700">Shop Now</span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
