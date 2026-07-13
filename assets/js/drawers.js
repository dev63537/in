/**
 * drawers.js — Cart & Wishlist Side Drawer + Toast System
 * Devendra's Shop — Pure vanilla JS, no dependencies
 */

(function () {
  'use strict';

  const CART_URL = window.SITE_URL + '/cart/cart_actions.php';
  const CURRENCY = '₹';

  /* ─── Helpers ─────────────────────────────────────────────── */
  function post(url, data) {
    const body = new URLSearchParams(data);
    return fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
  }

  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  function fmt(n) {
    return CURRENCY + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function updateNavBadge(id, count) {
    const badge = document.getElementById(id);
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count;
      badge.style.display = '';
    } else {
      badge.style.display = 'none';
    }
  }

  /* ─── Overlay & close ─────────────────────────────────────── */
  window.closeAllDrawers = function () {
    document.querySelectorAll('.side-drawer').forEach(d => d.classList.remove('open'));
    const overlay = document.getElementById('drawer-overlay');
    if (overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
  };

  function openDrawer(id) {
    closeAllDrawers();
    const el = document.getElementById(id);
    const overlay = document.getElementById('drawer-overlay');
    if (el) el.classList.add('open');
    if (overlay) overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  /* ─── CART DRAWER ─────────────────────────────────────────── */
  window.openCartDrawer = function () {
    openDrawer('cart-drawer');
    loadCartDrawer();
  };

  function loadCartDrawer() {
    const body   = document.getElementById('cart-drawer-body');
    const footer = document.getElementById('cart-drawer-footer');
    if (!body) return;
    body.innerHTML = '<div class="drawer-loading"><i class="fa fa-spinner fa-spin"></i><span>Loading cart…</span></div>';
    if (footer) footer.style.display = 'none';

    post(CART_URL, { action: 'get_cart' })
      .then(r => r.json())
      .then(data => {
        if (!data.success) { body.innerHTML = '<div class="drawer-empty"><i class="fa fa-shopping-bag"></i><strong>Could not load cart.</strong></div>'; return; }

        updateNavBadge('cart-badge-nav', data.count);
        const countEl = document.getElementById('drawer-cart-count');
        if (countEl) countEl.textContent = data.count;

        if (!data.items || data.items.length === 0) {
          body.innerHTML = '<div class="drawer-empty"><i class="fa fa-shopping-bag"></i><strong>Your cart is empty</strong><span>Add some products to get started!</span></div>';
          return;
        }

        let html = '';
        data.items.forEach(function (item) {
          html += `
          <div class="drawer-item" id="ditem-${esc(item.key)}">
            <img class="drawer-item-img" src="${esc(item.image)}" alt="${esc(item.name)}"
                 onerror="this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='"/>
            <div>
              <div class="drawer-item-name">${esc(item.name)}</div>
              <div class="drawer-item-meta">${esc(item.size)}${item.color ? ' · ' + esc(item.color) : ''}</div>
              <div class="drawer-item-price">${fmt(item.price)}</div>
              <div class="drawer-item-qty">
                <button class="drawer-qty-btn" onclick="drawerQtyChange('${esc(item.key)}', ${item.qty - 1})">
                  <i class="fa fa-minus"></i>
                </button>
                <span class="drawer-qty-val">${item.qty}</span>
                <button class="drawer-qty-btn" onclick="drawerQtyChange('${esc(item.key)}', ${item.qty + 1})">
                  <i class="fa fa-plus"></i>
                </button>
              </div>
            </div>
            <button class="drawer-item-remove" title="Remove" onclick="drawerRemoveItem('${esc(item.key)}')">
              <i class="fa fa-times"></i>
            </button>
          </div>`;
        });

        body.innerHTML = html;

        if (footer) {
          footer.style.display = '';
          const totalEl = document.getElementById('drawer-cart-total');
          if (totalEl) totalEl.textContent = fmt(data.total_raw);
        }
      })
      .catch(function () {
        body.innerHTML = '<div class="drawer-empty"><i class="fa fa-exclamation-circle"></i><strong>Connection error.</strong></div>';
      });
  }

  window.drawerQtyChange = function (key, newQty) {
    post(CART_URL, { action: 'update', key: key, qty: newQty })
      .then(r => r.json())
      .then(function (data) {
        updateNavBadge('cart-badge-nav', data.cart_count);
        loadCartDrawer();
      });
  };

  window.drawerRemoveItem = function (key) {
    const row = document.getElementById('ditem-' + key);
    if (row) { row.style.opacity = '.4'; row.style.pointerEvents = 'none'; }
    post(CART_URL, { action: 'remove', key: key })
      .then(r => r.json())
      .then(function (data) {
        updateNavBadge('cart-badge-nav', data.cart_count);
        loadCartDrawer();
      });
  };

  /* ─── WISHLIST DRAWER ─────────────────────────────────────── */
  window.openWishlistDrawer = function () {
    openDrawer('wishlist-drawer');
    loadWishlistDrawer();
  };

  function loadWishlistDrawer() {
    const body   = document.getElementById('wishlist-drawer-body');
    const footer = document.getElementById('wishlist-drawer-footer');
    if (!body) return;
    body.innerHTML = '<div class="drawer-loading"><i class="fa fa-spinner fa-spin"></i><span>Loading wishlist…</span></div>';
    if (footer) footer.style.display = 'none';

    post(CART_URL, { action: 'get_wishlist' })
      .then(r => r.json())
      .then(function (data) {
        if (!data.success) { body.innerHTML = '<div class="drawer-empty"><i class="far fa-heart"></i><strong>Could not load wishlist.</strong></div>'; return; }

        const countEl = document.getElementById('drawer-wishlist-count');
        if (countEl) countEl.textContent = data.count;
        updateNavBadge('wishlist-badge', data.count);

        if (!data.products || data.products.length === 0) {
          body.innerHTML = '<div class="drawer-empty"><i class="far fa-heart"></i><strong>Your wishlist is empty</strong><span>Save items you love!</span></div>';
          return;
        }

        let html = '';
        data.products.forEach(function (p) {
          html += `
          <div class="drawer-wishlist-item" id="witem-${p.id}">
            <a href="${window.SITE_URL}/pages/product.php?id=${p.id}">
              <img class="drawer-wishlist-img" src="${esc(p.image)}" alt="${esc(p.name)}"
                   onerror="this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='"/>
            </a>
            <div>
              <a href="${window.SITE_URL}/pages/product.php?id=${p.id}" class="drawer-wishlist-name">${esc(p.name)}</a>
              <div class="drawer-wishlist-price${p.on_sale ? ' on-sale' : ''}">${fmt(p.price)}</div>
              <button class="drawer-wishlist-atc" onclick="wishlistAddToCart(${p.id}, '${esc(p.name)}')">
                <i class="fa fa-shopping-bag"></i> Add to Cart
              </button>
            </div>
            <button class="drawer-wishlist-remove" title="Remove from wishlist" onclick="removeFromWishlistDrawer(${p.id})">
              <i class="fa fa-times"></i>
            </button>
          </div>`;
        });

        body.innerHTML = html;
        if (footer) footer.style.display = '';
      })
      .catch(function () {
        body.innerHTML = '<div class="drawer-empty"><i class="fa fa-exclamation-circle"></i><strong>Connection error.</strong></div>';
      });
  }

  window.removeFromWishlistDrawer = function (pid) {
    const row = document.getElementById('witem-' + pid);
    if (row) { row.style.opacity = '.4'; row.style.pointerEvents = 'none'; }
    post(CART_URL, { action: 'wishlist_remove', product_id: pid })
      .then(r => r.json())
      .then(function (data) {
        updateNavBadge('wishlist-badge', data.wishlist_count);
        loadWishlistDrawer();
      });
  };

  window.wishlistAddToCart = function (pid, name) {
    post(CART_URL, { action: 'add', product_id: pid, size: 'M', color: '', quantity: 1 })
      .then(r => r.json())
      .then(function (data) {
        if (data.success) {
          updateNavBadge('cart-badge-nav', data.cart_count);
          closeAllDrawers();
          showCartToast(name);
          setTimeout(openCartDrawer, 450);
        }
      });
  };

  /* ─── TOAST ────────────────────────────────────────────────── */
  let toastTimer = null;

  window.showCartToast = function (productName) {
    const toast   = document.getElementById('cart-toast');
    const nameEl  = document.getElementById('cart-toast-name');
    if (!toast) return;
    if (nameEl) nameEl.textContent = productName || '';
    clearTimeout(toastTimer);
    toast.classList.add('show');
    toastTimer = setTimeout(function () { toast.classList.remove('show'); }, 4000);
  };

  window.showWishlistToast = function (productName) {
    // Reuse cart-toast with different styling — or create a separate one
    const toast  = document.getElementById('cart-toast');
    const nameEl = document.getElementById('cart-toast-name');
    const titleEl = toast ? toast.querySelector('.cart-toast-title') : null;
    const iconEl  = toast ? toast.querySelector('.cart-toast-icon i') : null;
    if (!toast) return;
    if (nameEl)  nameEl.textContent  = productName || '';
    if (titleEl) titleEl.textContent = 'Added to Wishlist!';
    if (iconEl)  { iconEl.className = 'fas fa-heart'; iconEl.style.color = '#e74c3c'; }
    clearTimeout(toastTimer);
    toast.classList.add('show');
    toastTimer = setTimeout(function () {
      toast.classList.remove('show');
      // restore defaults
      setTimeout(function () {
        if (titleEl) titleEl.textContent = 'Added to Cart!';
        if (iconEl)  { iconEl.className = 'fa fa-check-circle'; iconEl.style.color = '#27ae60'; }
      }, 400);
    }, 4000);
  };

  /* ─── Intercept all .add-to-cart-form submits ─────────────── */
  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!form.classList.contains('add-to-cart-form')) return;
    e.preventDefault();

    const data = {};
    new FormData(form).forEach(function (v, k) { data[k] = v; });
    if (!data.action) data.action = 'add';

    // Optimistically animate button
    const btn = form.querySelector('[type=submit]');
    const origHTML = btn ? btn.innerHTML : '';
    if (btn) { btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'; btn.disabled = true; }

    post(CART_URL, data)
      .then(r => r.json())
      .then(function (res) {
        if (btn) { btn.innerHTML = '<i class="fa fa-check"></i> Added!'; btn.style.background = '#27ae60'; }
        if (res.success) {
          updateNavBadge('cart-badge-nav', res.cart_count);
          // Get product name from nearest parent or form dataset
          const nameEl = form.closest('[data-product-name]');
          const pname  = nameEl ? nameEl.dataset.productName : (form.dataset.productName || '');
          showCartToast(pname);
          loadCartDrawer(); // pre-load so drawer is ready
        }
        setTimeout(function () {
          if (btn) { btn.innerHTML = origHTML; btn.disabled = false; btn.style.background = ''; }
        }, 2200);
      })
      .catch(function () {
        if (btn) { btn.innerHTML = origHTML; btn.disabled = false; }
      });
  });

  /* ─── Intercept wishlist buttons ─────────────────────────── */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-wishlist-toggle]');
    if (!btn) return;
    const pid    = btn.dataset.wishlistToggle;
    const pname  = btn.dataset.productName || '';
    const inWish = btn.classList.contains('wishlisted');
    const action = inWish ? 'wishlist_remove' : 'wishlist_add';

    post(CART_URL, { action: action, product_id: pid })
      .then(r => r.json())
      .then(function (res) {
        if (res.success) {
          btn.classList.toggle('wishlisted', !inWish);
          const icon = btn.querySelector('i');
          if (icon) icon.className = inWish ? 'far fa-heart' : 'fas fa-heart';
          if (!inWish) showWishlistToast(pname);
          updateNavBadge('wishlist-badge', res.wishlist_count);
        }
      });
  });

  /* ─── Escape key closes drawers ──────────────────────────── */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllDrawers();
  });

})();
