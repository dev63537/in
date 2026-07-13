<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Sitemap — Devendra's Shop";
include __DIR__ . '/../includes/header.php';
$categories = getCategories();
?>
<section class="page-hero">
  <div class="container">
    <h1>Sitemap</h1>
    <p>All pages on Devendra's Shop</p>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Sitemap</nav>
  </div>
</section>

<div class="container" style="max-width:960px;padding:60px 20px">
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:32px">

    <div>
      <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #c9a96e"><i class="fa fa-home" style="color:#c9a96e"></i> Main Pages</h2>
      <ul style="list-style:none;padding:0">
        <?php foreach ([
          ['/', 'Home'],
          ['/pages/shop.php', 'Shop All Products'],
          ['/pages/collections.php', 'Collections'],
          ['/pages/about.php', 'About Us'],
          ['/pages/contact.php', 'Contact'],
          ['/pages/blog.php', 'Style Blog'],
        ] as $l): ?>
        <li style="margin-bottom:8px"><a href="<?= SITE_URL . $l[0] ?>" style="color:#555;font-size:.9rem;transition:.3s" onmouseover="this.style.color='#c9a96e'" onmouseout="this.style.color='#555'"><i class="fa fa-chevron-right" style="color:#c9a96e;font-size:.65rem;margin-right:6px"></i><?= $l[1] ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #c9a96e"><i class="fa fa-tags" style="color:#c9a96e"></i> Categories</h2>
      <ul style="list-style:none;padding:0">
        <?php foreach ($categories as $cat): ?>
        <li style="margin-bottom:8px"><a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['id'] ?>" style="color:#555;font-size:.9rem;transition:.3s" onmouseover="this.style.color='#c9a96e'" onmouseout="this.style.color='#555'"><i class="fa fa-chevron-right" style="color:#c9a96e;font-size:.65rem;margin-right:6px"></i><?= e($cat['name']) ?></a></li>
        <?php endforeach; ?>
        <li style="margin-bottom:8px"><a href="<?= SITE_URL ?>/pages/shop.php?tag=new" style="color:#555;font-size:.9rem;transition:.3s" onmouseover="this.style.color='#c9a96e'" onmouseout="this.style.color='#555'"><i class="fa fa-chevron-right" style="color:#c9a96e;font-size:.65rem;margin-right:6px"></i>New Arrivals</a></li>
        <li style="margin-bottom:8px"><a href="<?= SITE_URL ?>/pages/shop.php?tag=sale" style="color:#555;font-size:.9rem;transition:.3s" onmouseover="this.style.color='#c9a96e'" onmouseout="this.style.color='#555'"><i class="fa fa-chevron-right" style="color:#c9a96e;font-size:.65rem;margin-right:6px"></i>Sale Items</a></li>
      </ul>
    </div>

    <div>
      <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #c9a96e"><i class="fa fa-user" style="color:#c9a96e"></i> Account</h2>
      <ul style="list-style:none;padding:0">
        <?php foreach ([
          ['/auth/login.php','Login'],
          ['/auth/register.php','Create Account'],
          ['/auth/forgot-password.php','Forgot Password'],
          ['/pages/account.php','My Profile'],
          ['/pages/orders.php','My Orders'],
          ['/pages/wishlist.php','My Wishlist'],
          ['/pages/track-order.php','Track Order'],
        ] as $l): ?>
        <li style="margin-bottom:8px"><a href="<?= SITE_URL . $l[0] ?>" style="color:#555;font-size:.9rem;transition:.3s" onmouseover="this.style.color='#c9a96e'" onmouseout="this.style.color='#555'"><i class="fa fa-chevron-right" style="color:#c9a96e;font-size:.65rem;margin-right:6px"></i><?= $l[1] ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #c9a96e"><i class="fa fa-headset" style="color:#c9a96e"></i> Support</h2>
      <ul style="list-style:none;padding:0">
        <?php foreach ([
          ['/pages/faq.php','FAQ'],
          ['/pages/shipping.php','Shipping Policy'],
          ['/pages/returns.php','Returns & Exchanges'],
          ['/pages/size-guide.php','Size Guide'],
          ['/pages/contact.php','Contact Us'],
        ] as $l): ?>
        <li style="margin-bottom:8px"><a href="<?= SITE_URL . $l[0] ?>" style="color:#555;font-size:.9rem;transition:.3s" onmouseover="this.style.color='#c9a96e'" onmouseout="this.style.color='#555'"><i class="fa fa-chevron-right" style="color:#c9a96e;font-size:.65rem;margin-right:6px"></i><?= $l[1] ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #c9a96e"><i class="fa fa-building" style="color:#c9a96e"></i> Company</h2>
      <ul style="list-style:none;padding:0">
        <?php foreach ([
          ['/pages/about.php','About Us'],
          ['/pages/careers.php','Careers'],
          ['/pages/affiliate.php','Affiliate Program'],
          ['/pages/blog.php','Style Blog'],
          ['/pages/privacy.php','Privacy Policy'],
          ['/pages/terms.php','Terms of Service'],
        ] as $l): ?>
        <li style="margin-bottom:8px"><a href="<?= SITE_URL . $l[0] ?>" style="color:#555;font-size:.9rem;transition:.3s" onmouseover="this.style.color='#c9a96e'" onmouseout="this.style.color='#555'"><i class="fa fa-chevron-right" style="color:#c9a96e;font-size:.65rem;margin-right:6px"></i><?= $l[1] ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
