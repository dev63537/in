<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Style Blog — Gujju Clothing";
$metaDesc  = "Fashion tips, style guides, and trend updates from Gujju Clothing.";

$posts = [
  ['id'=>1,'slug'=>'summer-fashion-2025','title'=>'Summer Fashion 2025: 10 Must-Have Looks','category'=>'Trends','date'=>'May 20, 2025','read'=>'5 min','image'=>'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=600','excerpt'=>'Discover the hottest summer trends of 2025 — from breezy cotton kurtas to bold printed sets that will define the season.'],
  ['id'=>2,'slug'=>'ethnic-wear-guide','title'=>'Complete Guide to Indian Ethnic Wear for Weddings','category'=>'Guide','date'=>'May 15, 2025','read'=>'7 min','image'=>'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600','excerpt'=>'From Anarkali sets to Banarasi sarees — our ultimate guide to picking the perfect ethnic outfit for any wedding ceremony.'],
  ['id'=>3,'slug'=>'men-style-basics','title'=>'Men\'s Style Basics: Building a Capsule Wardrobe','category'=>'Style Guide','date'=>'May 10, 2025','read'=>'6 min','image'=>'https://images.unsplash.com/photo-1516257984-b1b4d707412e?w=600','excerpt'=>'Every man needs a solid wardrobe foundation. Here are the 10 essential pieces that will carry you from office to weekend effortlessly.'],
  ['id'=>4,'slug'=>'sustainable-fashion','title'=>'How We\'re Making Fashion More Sustainable','category'=>'Brand Story','date'=>'May 5, 2025','read'=>'4 min','image'=>'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600','excerpt'=>'Our commitment to ethical fashion — from sourcing sustainable fabrics to reducing packaging waste at Gujju\'s Shop.'],
  ['id'=>5,'slug'=>'kids-fashion-tips','title'=>'Dressing Your Kids in Style: Tips for Every Occasion','category'=>'Kids','date'=>'April 28, 2025','read'=>'5 min','image'=>'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?w=600','excerpt'=>'From school uniforms to birthday parties — tips on choosing comfortable, stylish, and practical clothing for your little ones.'],
  ['id'=>6,'slug'=>'accessory-styling','title'=>'How to Accessorize: The Art of Completing Your Look','category'=>'Accessories','date'=>'April 20, 2025','read'=>'4 min','image'=>'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=600','excerpt'=>'The right accessories can transform any outfit. Learn how to layer necklaces, choose the perfect bag, and pick shoes that elevate your look.'],
];

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Style Blog</h1>
    <p>Fashion tips, trends, and style inspiration</p>
</div>
</section>

<div class="container" style="padding:60px 20px">

  <!-- Featured Post -->
  <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.09);margin-bottom:48px;display:grid;grid-template-columns:1.2fr 1fr">
    <div style="overflow:hidden;aspect-ratio:16/9">
      <img src="<?= $posts[0]['image'] ?>" alt="<?= e($posts[0]['title']) ?>" style="width:100%;height:100%;object-fit:cover;transition:.5s" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform=''"/>
    </div>
    <div style="padding:36px;display:flex;flex-direction:column;justify-content:center">
      <div style="display:flex;gap:10px;align-items:center;margin-bottom:14px">
        <span style="background:#c9a96e;color:#0f0f0f;padding:3px 12px;border-radius:50px;font-size:.72rem;font-weight:700"><?= $posts[0]['category'] ?></span>
        <span style="color:#888;font-size:.82rem"><i class="fa fa-star" style="color:#c9a96e"></i> Featured</span>
      </div>
      <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:12px;line-height:1.3"><?= e($posts[0]['title']) ?></h2>
      <p style="color:#555;line-height:1.7;margin-bottom:20px"><?= e($posts[0]['excerpt']) ?></p>
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div style="font-size:.82rem;color:#888"><i class="fa fa-calendar" style="color:#c9a96e"></i> <?= $posts[0]['date'] ?> &nbsp;·&nbsp; <i class="fa fa-clock" style="color:#c9a96e"></i> <?= $posts[0]['read'] ?> read</div>
        <a href="<?= SITE_URL ?>/pages/blog-detail.php?slug=<?= $posts[0]['slug'] ?>" class="btn btn-primary" style="padding:10px 22px"><i class="fa fa-arrow-right"></i> Read More</a>
      </div>
    </div>
  </div>

  <!-- Post Grid -->
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:28px">
    <?php foreach (array_slice($posts, 1) as $post): ?>
    <article style="background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 14px rgba(0,0,0,.07);transition:.3s" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 8px 32px rgba(0,0,0,.13)'" onmouseout="this.style.transform='';this.style.boxShadow='0 2px 14px rgba(0,0,0,.07)'">
      <div style="overflow:hidden;aspect-ratio:16/9">
        <img src="<?= $post['image'] ?>" alt="<?= e($post['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;transition:.5s" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform=''"/>
      </div>
      <div style="padding:22px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
          <span style="background:#f9f7f4;color:#c9a96e;padding:3px 10px;border-radius:50px;font-size:.72rem;font-weight:700"><?= $post['category'] ?></span>
        </div>
        <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:10px;line-height:1.4"><a href="<?= SITE_URL ?>/pages/blog-detail.php?slug=<?= $post['slug'] ?>" style="color:#0f0f0f;transition:.3s" onmouseover="this.style.color='#c9a96e'" onmouseout="this.style.color='#0f0f0f'"><?= e($post['title']) ?></a></h3>
        <p style="color:#888;font-size:.87rem;line-height:1.6;margin-bottom:16px"><?= e(substr($post['excerpt'], 0, 100)) ?>…</p>
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div style="font-size:.78rem;color:#aaa"><i class="fa fa-calendar"></i> <?= $post['date'] ?> · <?= $post['read'] ?></div>
          <a href="<?= SITE_URL ?>/pages/blog-detail.php?slug=<?= $post['slug'] ?>" style="color:#c9a96e;font-size:.85rem;font-weight:600">Read <i class="fa fa-arrow-right"></i></a>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <!-- Newsletter CTA -->
  <div style="background:linear-gradient(135deg,#0f0f0f,#1a1a1a);border-radius:16px;padding:48px;text-align:center;color:#fff;margin-top:64px">
    <h3 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:8px">Never Miss a Style Update</h3>
    <p style="color:rgba(255,255,255,.7);margin-bottom:24px">Get the latest fashion tips and exclusive deals straight to your inbox.</p>
    <form onsubmit="alert('Thank you for subscribing!');return false" style="display:flex;gap:10px;max-width:440px;margin:0 auto">
      <input type="email" placeholder="Your email address" required style="flex:1;padding:13px 18px;border:none;border-radius:50px;font-family:inherit;font-size:.95rem;outline:none"/>
      <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Subscribe</button>
    </form>
  </div>
</div>
<style>@media(max-width:768px){.container > div[style*="grid-template-columns:1.2fr"]{grid-template-columns:1fr!important}}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
