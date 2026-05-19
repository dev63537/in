<?php
// admin/includes/admin_footer.php
?>
  </div><!-- /admin-content -->
</div><!-- /admin-main -->
</div><!-- /admin-layout -->
<script>
// Admin sidebar mobile toggle
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.delete-confirm').forEach(function(btn){
    btn.addEventListener('click', function(e){
      if(!confirm('Are you sure you want to delete this? This cannot be undone.')) e.preventDefault();
    });
  });
});
</script>
</body>
</html>
