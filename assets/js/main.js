// main.js — Devendra's Shop
// SITE_URL is injected by PHP footer to fix relative URL issues on shared hosting
var CART_URL = (window.SITE_URL || '') + '/cart/cart_actions.php';

document.addEventListener('DOMContentLoaded', function () {

  // ── Navbar scroll effect
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    navbar && (navbar.classList.toggle('scrolled', window.scrollY > 50));
    const btn = document.getElementById('back-to-top');
    btn && (btn.classList.toggle('visible', window.scrollY > 400));
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
    timer = setInterval(nextSlide, 5000);
    dots.forEach((d, i) => d.addEventListener('click', () => { clearInterval(timer); showSlide(i); timer = setInterval(nextSlide, 5000); }));
    const prev = document.getElementById('hero-prev');
    const next = document.getElementById('hero-next');
    prev && prev.addEventListener('click', () => { clearInterval(timer); showSlide((current - 1 + slides.length) % slides.length); timer = setInterval(nextSlide, 5000); });
    next && next.addEventListener('click', () => { clearInterval(timer); nextSlide(); timer = setInterval(nextSlide, 5000); });
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

  // ── AJAX Add to Cart (uses window.SITE_URL for correct absolute path)
  document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = this.querySelector('[type=submit]');
      const orig = btn.innerHTML;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Adding…';
      btn.disabled = true;
      fetch(CART_URL, { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            btn.innerHTML = '<i class="fa fa-check"></i> Added!';
            const badge = document.querySelector('.cart-badge');
            if (badge) badge.textContent = data.cart_count;
            else {
              const cartBtn = document.querySelector('.cart-btn');
              if (cartBtn) cartBtn.insertAdjacentHTML('beforeend', `<span class="cart-badge">${data.cart_count}</span>`);
            }
            showToast('Item added to cart!', 'success');
          } else {
            btn.innerHTML = orig;
            btn.disabled = false;
            showToast(data.message || 'Error adding item.', 'error');
          }
        })
        .catch(() => showToast('Network error. Please try again.', 'error'))
        .finally(() => { setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 2000); });
    });
  });

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
