<section class="container py-4">
    <div class="bg-white border rounded-3 p-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0"><i class="bi bi-envelope me-2"></i>Enviar E-mail</h4>
        </div>

        <p class="text-muted small">
            Preencha o destinatário e a mensagem. O envio utiliza o serviço de e-mail cadastrado na instituição (SMTP).
        </p>

        <form method="post" action="/admin/config/email/enviar" class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="emailDestinatario">Destinatário</label>
                <input type="email" class="form-control" id="emailDestinatario" name="destinatario"
                       placeholder="exemplo@dominio.com" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label" for="emailAssunto">Assunto</label>
                <input type="text" class="form-control" id="emailAssunto" name="assunto"
                       placeholder="Opcional" maxlength="180">
            </div>

            <div class="col-12">
                <label class="form-label" for="emailMensagem">Mensagem</label>
                <textarea class="form-control" id="emailMensagem" name="mensagem" rows="8"
                          placeholder="Escreva a mensagem aqui..." required></textarea>
            </div>

            <div class="col-12 d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Enviar</button>
                <a href="/admin" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</section>