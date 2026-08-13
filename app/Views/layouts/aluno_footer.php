<footer style="background:#111827; color:#cbd5e1;">
  <div class="container py-3 d-flex justify-content-between align-items-center">
    <small>Área do Aluno IESB</small>
    <small>&copy; 2026</small>
  </div>
</footer>

<button id="alunoScrollTop" class="btn btn-sm btn-primary position-fixed" style="bottom:30px;right:30px;z-index:1200;display:none;border-radius:50%;width:44px;height:44px;padding:0;align-items:center;justify-content:center;" title="Voltar ao topo">
  <i class="bi bi-arrow-up"></i>
</button>

<script>
(function(){
  var preloader = document.getElementById('alunoPreloader');
  if (preloader) {
    var minimumVisibleTime = 500;
    var preloaderStartedAt = window.__alunoPreloaderStartedAt || performance.now();
    var pageLoaded = document.readyState === 'complete';
    var finished = false;

    function mostrarPreloader() {
      finished = false;
      preloaderStartedAt = performance.now();
      pageLoaded = false;
      preloader.classList.remove('aluno-preloader--done');
      preloader.setAttribute('aria-hidden', 'false');
    }

    function ocultarPreloader() {
      if (finished) return;
      finished = true;
      preloader.classList.add('aluno-preloader--done');
      preloader.setAttribute('aria-hidden', 'true');
    }

    function finalizarQuandoPronto() {
      if (!pageLoaded || finished) return;
      var elapsed = performance.now() - preloaderStartedAt;
      var remaining = Math.max(0, minimumVisibleTime - elapsed);
      window.setTimeout(ocultarPreloader, remaining);
    }

    function isInternalAlunoLink(link) {
      if (!link || !link.href || link.target === '_blank' || link.hasAttribute('download')) return false;
      if (link.getAttribute('href').charAt(0) === '#') return false;
      if (link.protocol !== window.location.protocol || link.host !== window.location.host) return false;
      return link.pathname.indexOf('/aluno') === 0 || link.pathname === '/area-do-aluno';
    }

    document.addEventListener('click', function(event){
      if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      var link = event.target.closest ? event.target.closest('a') : null;
      if (isInternalAlunoLink(link)) mostrarPreloader();
    });

    document.addEventListener('submit', function(event){
      var form = event.target;
      if (!form || !form.action) return;
      var action = new URL(form.action, window.location.href);
      if (action.origin === window.location.origin && (action.pathname.indexOf('/aluno') === 0 || action.pathname === '/area-do-aluno' || action.pathname === '/logout')) {
        mostrarPreloader();
      }
    });

    window.addEventListener('load', function(){
      pageLoaded = true;
      finalizarQuandoPronto();
    }, { once: true });
    window.addEventListener('pageshow', function(){
      pageLoaded = true;
      finalizarQuandoPronto();
    }, { once: true });
    finalizarQuandoPronto();
    window.setTimeout(function(){
      pageLoaded = true;
      finalizarQuandoPronto();
    }, 10000);
  }

  var btn=document.getElementById('alunoScrollTop');
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
<script src="/assets/js/app.js"></script>
</body>

</html>
