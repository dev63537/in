// main.js — Gujju Clothing
// SITE_URL is injected by PHP footer to fix relative URL issues on shared hosting
var CART_URL = (window.SITE_URL || '') + '/cart/cart_actions.php';

document.addEventListener('DOMContentLoaded', function () {

  // ── Navbar scroll effect
  const navbar = document.getElementById('navbar');
  let lastScrollY = window.scrollY;
  window.addEventListener('scroll', () => {
    if (!navbar) return;
    const currentScrollY = window.scrollY;

    // Transparent at top, solid white when scrolled down
    if (currentScrollY <= 20) {
      navbar.classList.remove('scrolled');
    } else {
      navbar.classList.add('scrolled');
    }

    // Hide navbar on scroll down, show on scroll up
    if (currentScrollY > lastScrollY && currentScrollY > 150) {
      navbar.classList.add('navbar-hidden');
    } else {
      navbar.classList.remove('navbar-hidden');
    }
    lastScrollY = currentScrollY;

    const btn = document.getElementById('back-to-top');
    btn && (btn.classList.toggle('visible', currentScrollY > 400));
  });

  // ── Hamburger menu
  const ham = document.getElementById('hamburger');
  const navLinks = document.getElementById('nav-links');
  const overlay = document.getElementById('nav-overlay');
  if (ham) {
    ham.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      ham.classList.toggle('active', open);
      overlay && overlay.classList.toggle('active', open);
      ham.setAttribute('aria-expanded', open);
    });
  }
  overlay && overlay.addEventListener('click', closeMenu);
  function closeMenu() {
    navLinks && navLinks.classList.remove('open');
    ham && ham.classList.remove('active');
    overlay && overlay.classList.remove('active');
  }

  // ── Search bar toggle
  const searchToggle = document.getElementById('search-toggle');
  const searchBar    = document.getElementById('search-bar');
  const searchClose  = document.getElementById('search-close');
  if (searchToggle) {
    searchToggle.addEventListener('click', () => {
      searchBar.classList.toggle('open');
      if (searchBar.classList.contains('open')) document.getElementById('search-input').focus();
    });
  }
  searchClose && searchClose.addEventListener('click', () => searchBar.classList.remove('open'));

  // ── User dropdown
  const userBtn  = document.getElementById('user-menu-btn');
  const userDrop = document.getElementById('user-dropdown');
  if (userBtn) {
    userBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      userDrop.classList.toggle('open');
    });
    document.addEventListener('click', () => userDrop && userDrop.classList.remove('open'));
  }

  // ── Back to top
  const btt = document.getElementById('back-to-top');
  btt && btt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  // ── Flash message auto-dismiss
  const flash = document.getElementById('flash-msg');
  if (flash) setTimeout(() => flash.remove(), 4000);

  // ── Hero slider
  const slides = document.querySelectorAll('.hero-slide');
  const dots   = document.querySelectorAll('.hero-dot');
  let current  = 0, timer;
  function showSlide(n) {
    slides.forEach((s, i) => s.classList.toggle('active', i === n));
    dots.forEach((d, i) => d.classList.toggle('active', i === n));
    current = n;
  }
  function nextSlide() { showSlide((current + 1) % slides.length); }
  if (slides.length > 1) {
    const SLIDE_INTERVAL = 2500; // Reduced slider duration (2.5 sec)
    timer = setInterval(nextSlide, SLIDE_INTERVAL);
    dots.forEach((d, i) => d.addEventListener('click', () => { clearInterval(timer); showSlide(i); timer = setInterval(nextSlide, SLIDE_INTERVAL); }));
    const prev = document.getElementById('hero-prev');
    const next = document.getElementById('hero-next');
    prev && prev.addEventListener('click', () => { clearInterval(timer); showSlide((current - 1 + slides.length) % slides.length); timer = setInterval(nextSlide, SLIDE_INTERVAL); });
    next && next.addEventListener('click', () => { clearInterval(timer); nextSlide(); timer = setInterval(nextSlide, SLIDE_INTERVAL); });
  }

  // ── Product image gallery
  const mainImg  = document.getElementById('main-product-img');
  const thumbs   = document.querySelectorAll('.thumb-img');
  thumbs.forEach(t => t.addEventListener('click', function () {
    if (mainImg) mainImg.src = this.querySelector('img') ? this.querySelector('img').dataset.src : this.dataset.src;
    thumbs.forEach(x => x.classList.remove('active'));
    this.classList.add('active');
  }));

  // ── Size / Color selectors
  document.querySelectorAll('.size-btn, .color-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      this.closest('.btn-group').querySelectorAll('button').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // ── Quantity +/- buttons
  const qtyMinus = document.getElementById('qty-minus');
  const qtyPlus  = document.getElementById('qty-plus');
  const qtyInput = document.getElementById('qty-input');
  if (qtyMinus && qtyPlus && qtyInput) {
    qtyMinus.addEventListener('click', () => { if (parseInt(qtyInput.value) > 1) qtyInput.value--; });
    qtyPlus.addEventListener('click',  () => { if (parseInt(qtyInput.value) < 99) qtyInput.value++; });
  }

  // ── AJAX Add to Cart is handled exclusively by drawers.js to prevent duplicate requests

  // ── Cart quantity update
  document.querySelectorAll('.cart-qty-input').forEach(input => {
    input.addEventListener('change', function () {
      const key = this.dataset.key;
      const qty = this.value;
      fetch(CART_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&key=${encodeURIComponent(key)}&qty=${qty}`
      }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
    });
  });

  // ── Cart remove
  document.querySelectorAll('.cart-remove-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const key = this.dataset.key;
      fetch(CART_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&key=${encodeURIComponent(key)}`
      }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
    });
  });

  // ── Toast notification
  window.showToast = function (msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    t.innerHTML = `<i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${msg}`;
    document.body.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, 3000);
  };

  // ── Animate on scroll (IntersectionObserver)
  const animEls = document.querySelectorAll('.animate-on-scroll');
  if (animEls.length && 'IntersectionObserver' in window) {
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('animated'); obs.unobserve(entry.target); } });
    }, { threshold: 0.1 });
    animEls.forEach(el => obs.observe(el));
  } else {
    // Fallback: show all immediately
    animEls.forEach(el => el.classList.add('animated'));
  }

  // ── Newsletter form AJAX
  const nlForm = document.getElementById('newsletter-form');
  if (nlForm) {
    nlForm.addEventListener('submit', function (e) {
      e.preventDefault();
      fetch(this.action, { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => showToast(data.message, data.success ? 'success' : 'error'))
        .catch(() => showToast('Subscribed! Thank you.', 'success'));
    });
  }

  // ── Price range slider (shop filter)
  const priceRange = document.getElementById('price-range');
  const priceVal   = document.getElementById('price-val');
  if (priceRange && priceVal) {
    priceRange.addEventListener('input', () => { priceVal.textContent = '₹' + priceRange.value; });
  }

  // ── Tab switching (product details, size guide, account)
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const target = this.dataset.tab;
      // Only affect tabs in same container
      const container = this.closest('[class*="tab"]')?.parentElement || document;
      container.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      container.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.remove('active');
        p.style.display = 'none';
      });
      this.classList.add('active');
      const panel = document.getElementById(target);
      if (panel) {
        panel.classList.add('active');
        panel.style.display = 'block';
      }
    });
  });

  // ── Star rating (review form)
  document.querySelectorAll('.star-rate .star').forEach((star, idx, arr) => {
    star.addEventListener('click', function () {
      arr.forEach((s, i) => s.classList.toggle('selected', i <= idx));
      const input = document.getElementById('rating-input');
      if (input) input.value = idx + 1;
    });
    star.addEventListener('mouseover', function () {
      arr.forEach((s, i) => s.classList.toggle('hovered', i <= idx));
    });
    star.addEventListener('mouseout', function () {
      arr.forEach(s => s.classList.remove('hovered'));
    });
  });

  // ── Mobile shop filter toggle
  const filterToggle = document.getElementById('filter-toggle');
  const shopSidebar  = document.getElementById('shop-sidebar');
  if (filterToggle && shopSidebar) {
    filterToggle.addEventListener('click', () => {
      shopSidebar.classList.toggle('open');
      filterToggle.innerHTML = shopSidebar.classList.contains('open')
        ? '<i class="fa fa-times"></i> Hide Filters'
        : '<i class="fa fa-sliders-h"></i> Show Filters';
    });
  }

  // ── Shop filter: auto-submit on radio change
  document.querySelectorAll('#filter-form input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function () {
      this.closest('form').submit();
    });
  });

});
