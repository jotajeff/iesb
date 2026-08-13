<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AcordoPagamentoRepository;
use App\Repositories\NotificacaoEmailRepository;
use App\Repositories\PreInscricaoRepository;
use App\Repositories\UsuarioRepository;

final class NotificacaoEmailService
{
    public function __construct(
        private readonly NotificacaoEmailRepository $repository = new NotificacaoEmailRepository(),
        private readonly AcordoPagamentoRepository $acordoRepository = new AcordoPagamentoRepository(),
        private readonly PreInscricaoRepository $preInscricaoRepository = new PreInscricaoRepository(),
        private readonly UsuarioRepository $usuarioRepository = new UsuarioRepository(),
        private readonly EmailService $emailService = new EmailService(),
    ) {
    }

    public function enviarEmailAcordo(int $idAcordo, int $idUsuarioEnvio): array
    {
        $acordo = $this->acordoRepository->findById($idAcordo);
        if ($acordo === null) {
            return ['sucesso' => false, 'status' => 'NAO_ENCONTRADO', 'mensagem' => 'Acordo não encontrado.'];
        }

        if ((int) ($acordo['ativo'] ?? 0) !== 1) {
            return ['sucesso' => false, 'status' => 'INATIVO', 'mensagem' => 'Acordo inativo. Não é possível enviar o e-mail.'];
        }

        $idPreInscricao = (int) ($acordo['id_pre_inscricao'] ?? 0);
        $pre = $idPreInscricao > 0 ? $this->preInscricaoRepository->findById($idPreInscricao) : null;
        if ($pre === null) {
            return ['sucesso' => false, 'status' => 'SEM_PRE_INSCRICAO', 'mensagem' => 'Pré-inscrição vinculada ao acordo não encontrada.'];
        }

        $emailDestinatario = trim((string) ($pre['email'] ?? ''));
        if ($emailDestinatario === '' || !filter_var($emailDestinatario, FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'status' => 'SEM_EMAIL', 'mensagem' => 'A pré-inscrição não possui um e-mail válido. Registre um e-mail antes de enviar.'];
        }

        $nomeDestinatario = trim((string) ($pre['nome'] ?? ''));
        $token = (string) ($acordo['token'] ?? '');
        if ($token === '') {
            return ['sucesso' => false, 'status' => 'SEM_LINK', 'mensagem' => 'Acordo sem link de pagamento disponível.'];
        }

        $appUrl = rtrim((string) (getenv('APP_URL') ?: 'https://inteligenciaeducacionalsouzabrazil.com'), '/');
        $link = $appUrl . '/financeiro/' . $token;

        $assunto = 'Seu acordo de pagamento está disponível';
        $mensagem = $this->montarMensagemAcordo($nomeDestinatario, $link);

        $idUsuarioValidado = null;
        if ($idUsuarioEnvio > 0 && $this->usuarioRepository->findById($idUsuarioEnvio) !== null) {
            $idUsuarioValidado = $idUsuarioEnvio;
        }

        $registroId = $this->repository->criar([
            'tipo_origem' => 'ACORDO',
            'id_origem' => $idAcordo,
            'id_pre_inscricao' => $idPreInscricao,
            'id_acordo_pagamento' => $idAcordo,
            'id_aluno' => $this->acordoRepository->buscarIdAlunoPorAcordo($idAcordo),
            'nome_destinatario' => $nomeDestinatario !== '' ? $nomeDestinatario : null,
            'email_destinatario' => $emailDestinatario,
            'assunto' => $assunto,
            'mensagem' => $mensagem,
            'link' => $link,
            'status' => 'PENDENTE',
            'id_usuario_envio' => $idUsuarioValidado,
        ]);

        if ($registroId <= 0) {
            return ['sucesso' => false, 'status' => 'FALHA_REGISTRO', 'mensagem' => 'Erro ao registrar a tentativa de envio.'];
        }

        $enviado = $this->emailService->enviarHtml(
            $emailDestinatario,
            $nomeDestinatario,
            $assunto,
            $mensagem,
            $this->montarTextoAcordo($nomeDestinatario, $link)
        );

        if ($enviado) {
            $this->repository->marcarEnviado($registroId);
            return [
                'sucesso' => true,
                'status' => 'ENVIADO',
                'mensagem' => 'E-mail enviado com sucesso.',
                'registro_id' => $registroId,
                'destinatario' => $emailDestinatario,
            ];
        }

        $erro = $this->emailService->getLastError();
        $this->repository->marcarErro($registroId, $erro);

        return [
            'sucesso' => false,
            'status' => 'ERRO',
            'mensagem' => 'O e-mail não foi enviado. ' . ($erro !== '' ? $erro : 'Tente novamente.'),
            'registro_id' => $registroId,
            'destinatario' => $emailDestinatario,
        ];
    }

    public function buscarUltimoEnvio(int $idAcordo): ?array
    {
        return $this->repository->buscarUltimoPorAcordo($idAcordo);
    }

    public function listarPorAcordo(int $idAcordo): array
    {
        return $this->repository->listarPorAcordo($idAcordo);
    }

    public function listarPorAcordos(array $acordoIds): array
    {
        $envios = $this->repository->listarPorAcordos($acordoIds);
        $agrupado = [];

        foreach ($envios as $envio) {
            $agrupado[(int) ($envio['id_acordo_pagamento'] ?? 0)][] = $envio;
        }

        return $agrupado;
    }

    private function montarMensagemAcordo(string $nome, string $link): string
    {
        $nomeHtml = htmlspecialchars($nome !== '' ? $nome : 'candidato(a)', ENT_QUOTES, 'UTF-8');
        $linkHtml = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background: #f4f4f4; padding: 40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 8px; overflow: hidden;">
          <tr>
            <td style="background: #efc02b; padding: 30px; text-align: center;">
              <h1 style="color: #4d4f4e; margin: 0; font-size: 24px;">Seu acordo está disponível</h1>
            </td>
          </tr>
          <tr>
            <td style="padding: 30px;">
              <p style="font-size: 16px; color: #333;">Olá, <strong>{$nomeHtml}</strong>.</p>
              <p style="font-size: 16px; color: #333;">Seu acordo/renegociação está disponível para pagamento.</p>
              <p style="font-size: 16px; color: #333;">Para acessar os detalhes e realizar o pagamento, clique no botão abaixo:</p>
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding: 20px 0;">
                    <a href="{$linkHtml}" style="background: #efc02b; color: #4d4f4e; text-decoration: none; padding: 14px 36px; border-radius: 6px; font-size: 16px; display: inline-block; font-weight: 700;">ACESSAR ACORDO</a>
                  </td>
                </tr>
              </table>
              <p style="font-size: 14px; color: #999;">Link: <a href="{$linkHtml}">{$linkHtml}</a></p>
              <p style="font-size: 14px; color: #333;">Caso tenha alguma dúvida, entre em contato com a instituição.</p>
              <p style="font-size: 14px; color: #999;">Atenciosamente,<br>IESB</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    private function montarTextoAcordo(string $nome, string $link): string
    {
        $nomeTexto = $nome !== '' ? $nome : 'candidato(a)';

        return "Olá, {$nomeTexto}.\n\n"
            . "Seu acordo/renegociação está disponível para pagamento.\n\n"
            . "Para acessar os detalhes e realizar o pagamento, acesse o link abaixo:\n"
            . "{$link}\n\n"
            . "Caso tenha alguma dúvida, entre em contato com a instituição.\n\n"
            . "Atenciosamente,\nIESB";
    }
}
