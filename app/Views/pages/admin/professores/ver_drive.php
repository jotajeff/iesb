<div class="modal fade" id="verDriveModal" tabindex="-1" aria-labelledby="verDriveLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verDriveLabel"><i class="bi bi-google me-2"></i><span id="driveTitulo"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body text-center" id="driveContainer">
        <div class="ratio ratio-4x3">
          <iframe id="driveIframe" src="" allowfullscreen></iframe>
        </div>
        <div id="driveFallback" class="d-none py-4">
          <i class="bi bi-file-earmark-pdf fs-1 text-muted"></i>
          <p class="mt-2 mb-0">Não foi possível incorporar a visualização.</p>
        </div>
      </div>
      <div class="modal-footer">
        <a id="driveDownload" href="#" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-download me-1"></i>Baixar PDF
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
