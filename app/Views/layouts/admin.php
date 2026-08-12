<?php require dirname(__DIR__) . '/layouts/admin_topo.php'; ?>
<?php require dirname(__DIR__) . '/layouts/admin_menu.php'; ?>
<div class="admin-preloader" id="adminPreloader" aria-hidden="true">
  <div class="admin-preloader-bar"></div>
</div>
<main class="flex-grow-1 w-100"><?php require $viewPath; ?></main>
<?php require dirname(__DIR__) . '/layouts/admin_footer.php'; ?>
<script>
(function() {
  var bar = document.getElementById('adminPreloader');
  if (!bar) return;
  var done = false;
  function ocultar() {
    if (done) return;
    done = true;
    bar.classList.add('admin-preloader--done');
    setTimeout(function() { bar.remove(); }, 400);
  }
  if (document.readyState === 'complete') {
    ocultar();
  } else {
    window.addEventListener('load', ocultar);
    setTimeout(ocultar, 3000);
  }
})();
</script>
