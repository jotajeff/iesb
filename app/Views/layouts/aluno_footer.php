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
</body>

</html>
