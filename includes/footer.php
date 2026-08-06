<?php
// ============================================================
// includes/footer.php — Global Site Footer
// ============================================================
?>

<!-- ===== NEWSLETTER ===== -->
<section class="newsletter-section">
  <div class="container">
    <div class="newsletter-inner">
      <div class="newsletter-text">
        <h3>Join the Gujju Clothing Circle</h3>
        <p>Get exclusive deals, early access to new arrivals & style inspiration.</p>
      </div>
      <form class="newsletter-form" id="newsletter-form" action="<?= SITE_URL ?>/pages/newsletter.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>" />
        <input type="email" name="email" placeholder="Enter your email address" required />
        <button type="submit">Subscribe <i class="fa fa-arrow-right"></i></button>
      </form>
    </div>
  </div>
</section>

<!-- ===== MAIN FOOTER ===== -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand Column -->
      <div class="footer-col footer-brand">
        <a href="<?= SITE_URL ?>/index.php" class="footer-logo">
          <img src="<?= SITE_URL ?>/assets/images/logo.jpg" alt="<?= e(SITE_NAME) ?>" style="max-height:45px;width:auto;object-fit:contain;margin-bottom:14px;" onError="if(this.src.indexOf('assets')!=-1){this.src='<?= SITE_URL ?>/logo.jpg';}else{this.style.display='none';this.nextElementSibling.style.display='inline-block';}" />
          <span style="display:none;"><i class="fa fa-gem"></i> Gujju Clothing</span>
        </a>
        <p>Premium fashion for every occasion. Curated styles that define your identity.</p>
        <div class="social-links">
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
          <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="footer-col">
        <h4 class="footer-heading">Quick Links</h4>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/index.php"><i class="fa fa-chevron-right"></i> Home</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shop.php"><i class="fa fa-chevron-right"></i> Shop All</a></li>
          <li><a href="<?= SITE_URL ?>/pages/collections.php"><i class="fa fa-chevron-right"></i> Collections</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shop.php?tag=new"><i class="fa fa-chevron-right"></i> New Arrivals</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shop.php?tag=sale"><i class="fa fa-chevron-right"></i> Sale</a></li>
          <li><a href="<?= SITE_URL ?>/pages/about.php"><i class="fa fa-chevron-right"></i> About Us</a></li>
        </ul>
      </div>

      <!-- Customer Service -->
      <div class="footer-col">
        <h4 class="footer-heading">Customer Service</h4>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/pages/faq.php"><i class="fa fa-chevron-right"></i> FAQ</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shipping.php"><i class="fa fa-chevron-right"></i> Shipping Policy</a></li>
          <li><a href="<?= SITE_URL ?>/pages/returns.php"><i class="fa fa-chevron-right"></i> Returns & Exchanges</a></li>
          <li><a href="<?= SITE_URL ?>/pages/size-guide.php"><i class="fa fa-chevron-right"></i> Size Guide</a></li>
          <li><a href="<?= SITE_URL ?>/pages/contact.php"><i class="fa fa-chevron-right"></i> Contact Us</a></li>
          <li><a href="<?= SITE_URL ?>/pages/track-order.php"><i class="fa fa-chevron-right"></i> Track Order</a></li>
        </ul>
      </div>

      <!-- Company -->
      <div class="footer-col">
        <h4 class="footer-heading">Company</h4>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/pages/privacy.php"><i class="fa fa-chevron-right"></i> Privacy Policy</a></li>
          <li><a href="<?= SITE_URL ?>/pages/terms.php"><i class="fa fa-chevron-right"></i> Terms of Service</a></li>
          <li><a href="<?= SITE_URL ?>/pages/careers.php"><i class="fa fa-chevron-right"></i> Careers</a></li>
          <li><a href="<?= SITE_URL ?>/pages/affiliate.php"><i class="fa fa-chevron-right"></i> Affiliate Program</a></li>
          <li><a href="<?= SITE_URL ?>/pages/blog.php"><i class="fa fa-chevron-right"></i> Style Blog</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="footer-col">
        <h4 class="footer-heading">Get in Touch</h4>
        <ul class="footer-contact">
          <li><i class="fa fa-map-marker-alt"></i> 42 Fashion Street, Mumbai, India</li>
          <li><i class="fa fa-phone"></i> +91 98765 43210</li>
          <li><i class="fa fa-envelope"></i> <?= SITE_EMAIL ?></li>
          <li><i class="fa fa-clock"></i> Mon–Sat: 10:00 AM – 7:00 PM</li>
        </ul>
      </div><!-- /footer-col get-in-touch -->

    </div><!-- /footer-grid -->
  </div><!-- /container -->

  <!-- Footer Bottom -->
  <div class="footer-bottom">
    <div class="container">
      <p>&copy; <?= date('Y') ?> <strong>Gujju Clothing</strong>. All Rights Reserved. Made with <i class="fa fa-heart" style="color:#e74c3c"></i> in India.</p>
      <p class="footer-bottom-links">
        <a href="<?= SITE_URL ?>/pages/privacy.php">Privacy</a> &bull;
        <a href="<?= SITE_URL ?>/pages/terms.php">Terms</a> &bull;
        <a href="<?= SITE_URL ?>/pages/sitemap.php">Sitemap</a>
      </p>
    </div>
  </div>
</footer>

<!-- ===== CART DRAWER ===== -->
<div class="drawer-overlay" id="drawer-overlay" onclick="closeAllDrawers()"></div>

<aside class="side-drawer" id="cart-drawer" aria-label="Shopping Cart">
  <div class="drawer-header">
    <div class="drawer-title"><i class="fa fa-shopping-bag"></i> Your Cart <span class="drawer-count" id="drawer-cart-count">0</span></div>
    <button class="drawer-close" onclick="closeAllDrawers()" aria-label="Close cart"><i class="fa fa-times"></i></button>
  </div>
  <div class="drawer-body" id="cart-drawer-body">
    <div class="drawer-loading"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
  </div>
  <div class="drawer-footer" id="cart-drawer-footer" style="display:none">
    <div class="drawer-total-row">
      <span>Subtotal</span>
      <span class="drawer-total-val" id="drawer-cart-total">₹0.00</span>
    </div>
    <a href="<?= SITE_URL ?>/cart/cart.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px">
      <i class="fa fa-eye"></i> View Full Cart
    </a>
    <a href="<?= SITE_URL ?>/cart/checkout.php" class="btn btn-dark" style="width:100%;justify-content:center">
      <i class="fa fa-bolt"></i> Checkout Now
    </a>
  </div>
</aside>

<!-- ===== WISHLIST DRAWER ===== -->
<aside class="side-drawer" id="wishlist-drawer" aria-label="Wishlist">
  <div class="drawer-header">
    <div class="drawer-title"><i class="fas fa-heart" style="color:#e74c3c"></i> Wishlist <span class="drawer-count" id="drawer-wishlist-count">0</span></div>
    <button class="drawer-close" onclick="closeAllDrawers()" aria-label="Close wishlist"><i class="fa fa-times"></i></button>
  </div>
  <div class="drawer-body" id="wishlist-drawer-body">
    <div class="drawer-loading"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
  </div>
  <div class="drawer-footer" id="wishlist-drawer-footer" style="display:none">
    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary" style="width:100%;justify-content:center">
      <i class="fa fa-shopping-bag"></i> Continue Shopping
    </a>
  </div>
</aside>

<!-- ===== CART ADDED TOAST ===== -->
<div class="cart-toast" id="cart-toast">
  <div class="cart-toast-inner">
    <div class="cart-toast-icon"><i class="fa fa-check-circle"></i></div>
    <div>
      <div class="cart-toast-title">Added to Cart!</div>
      <div class="cart-toast-sub" id="cart-toast-name"></div>
    </div>
    <button class="cart-toast-view" onclick="openCartDrawer()">View Cart <i class="fa fa-arrow-right"></i></button>
  </div>
</div>

<!-- Back to Top -->
<button class="back-to-top" id="back-to-top" title="Back to Top" aria-label="Back to top">
  <i class="fa fa-chevron-up"></i>
</button>


<!-- Main JavaScript -->
<script>
  // Expose PHP SITE_URL to JavaScript so AJAX calls use correct absolute paths
  window.SITE_URL = '<?= SITE_URL ?>';
</script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<script src="<?= SITE_URL ?>/assets/js/drawers.js"></script>

<?php if (isset($extraJS)): ?>
  <script src="<?= SITE_URL ?>/assets/js/<?= e($extraJS) ?>"></script>
<?php endif; ?>

</body>
</html>
