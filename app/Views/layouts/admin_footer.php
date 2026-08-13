<footer class="mt-auto" style="background:#111827; color:#cbd5e1;">
  <div class="container py-3 d-flex justify-content-between align-items-center">
    <small>Painel Administrativo IESB</small>
    <small>&copy; 2026</small>
  </div>
</footer>

<button id="adminScrollTop" class="btn btn-sm btn-primary position-fixed" style="bottom:30px;right:30px;z-index:1200;display:none;border-radius:50%;width:44px;height:44px;padding:0;align-items:center;justify-content:center;" title="Voltar ao topo">
  <i class="bi bi-arrow-up"></i>
</button>

<script>
(function(){
  var loader = document.getElementById('adminPageLoader');
  if (!loader) return;

  var minimumVisibleTime = 500;
  var startedAt = window.performance && typeof window.performance.now === 'function'
    ? window.performance.now()
    : Date.now();
  var pageLoaded = document.readyState === 'complete';
  var finished = false;

  function showLoader() {
    finished = false;
    startedAt = window.performance && typeof window.performance.now === 'function'
      ? window.performance.now()
      : Date.now();
    pageLoaded = false;
    loader.classList.remove('admin-page-loader--done');
    loader.setAttribute('aria-hidden', 'false');
  }

  function hideLoader() {
    if (finished) return;
    finished = true;
    loader.classList.add('admin-page-loader--done');
    loader.setAttribute('aria-hidden', 'true');
    window.setTimeout(function(){ loader.remove(); }, 450);
  }

  function finishWhenReady() {
    if (!pageLoaded || finished) return;
    var now = window.performance && typeof window.performance.now === 'function'
      ? window.performance.now()
      : Date.now();
    var remaining = minimumVisibleTime - (now - startedAt);
    if (remaining > 0) {
      window.setTimeout(hideLoader, remaining);
      return;
    }
    hideLoader();
  }

  function isInternalAdminLink(link) {
    if (!link || !link.href || link.target === '_blank' || link.hasAttribute('download')) return false;
    if (link.getAttribute('href').charAt(0) === '#') return false;
    if (link.protocol !== window.location.protocol || link.host !== window.location.host) return false;
    return link.pathname.indexOf('/admin') === 0;
  }

  document.addEventListener('click', function(event){
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    var link = event.target.closest ? event.target.closest('a') : null;
    if (isInternalAdminLink(link)) showLoader();
  });

  document.addEventListener('submit', function(event){
    var form = event.target;
    if (!form || !form.action) return;
    var action = new URL(form.action, window.location.href);
    if (action.origin === window.location.origin && (action.pathname.indexOf('/admin') === 0 || action.pathname === '/logout')) {
      showLoader();
    }
  });

  window.addEventListener('load', function(){
    pageLoaded = true;
    finishWhenReady();
  }, {once: true});
  window.addEventListener('pageshow', function(){
    pageLoaded = true;
    finishWhenReady();
  }, {once: true});
  finishWhenReady();
  window.setTimeout(function(){
    pageLoaded = true;
    finishWhenReady();
  }, 10000);
})();

(function(){
  var btn=document.getElementById('adminScrollTop');
  if(!btn)return;
  window.addEventListener('scroll',function(){
    btn.style.display=window.scrollY>300?'inline-flex':'none';
  });
  btn.addEventListener('click',function(){
    window.scrollTo({top:0,behavior:'smooth'});
  });
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
