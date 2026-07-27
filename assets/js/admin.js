// admin/assets/js/admin.js — Admin Panel JavaScript
document.addEventListener('DOMContentLoaded', function () {

  // ── Confirm delete
  document.querySelectorAll('.delete-confirm').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm('Delete this item? This cannot be undone.')) e.preventDefault();
    });
  });

  // ── Image preview on file select
  document.querySelectorAll('input[type=file][accept="image/*"]').forEach(function (input) {
    input.addEventListener('change', function () {
      var preview = this.closest('.form-group').querySelector('img.img-preview');
      if (preview && this.files[0]) {
        preview.src = URL.createObjectURL(this.files[0]);
        preview.style.display = 'block';
      }
    });
  });

  // ── Auto-dismiss flash messages
  var flash = document.querySelector('.flash-message');
  if (flash) setTimeout(function () { flash.style.opacity = '0'; setTimeout(function () { flash.remove(); }, 400); }, 4000);

  // ── Sidebar toggle on mobile
  var sidebarToggle = document.getElementById('sidebar-toggle');
  var sidebar       = document.getElementById('admin-sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
      if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // ── Data table search filter
  var tableSearch = document.getElementById('table-search');
  if (tableSearch) {
    tableSearch.addEventListener('input', function () {
      var q = this.value.toLowerCase();
      document.querySelectorAll('.admin-table tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

});
