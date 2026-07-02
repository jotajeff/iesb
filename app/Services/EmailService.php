<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;

final class EmailService
{
    private PHPMailer $mail;
    private string $lastError = '';
    private bool $configured = false;
    private string $debugInfo = '';

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        $pdo = Database::connection();

        if ($pdo instanceof PDO) {
            $stmt = $pdo->prepare('SELECT email, senha FROM instituicao WHERE id = 1 LIMIT 1');
            $stmt->execute();
            $row = $stmt->fetch();

            if ($row && !empty($row['email']) && !empty($row['senha'])) {
                $email = trim((string) $row['email']);
                $senha = trim((string) $row['senha']);

                $this->debugInfo = "Host: smtp.gmail.com, Port: 587, User: {$email}";

                $this->mail->isSMTP();
                $this->mail->Host = 'smtp.gmail.com';
                $this->mail->SMTPAuth = true;
                $this->mail->Username = $email;
                $this->mail->Password = $senha;
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $this->mail->Port = 587;
                $this->mail->CharSet = 'UTF-8';

                $this->mail->setFrom($email, 'IESB - Área do Aluno');
                $this->configured = true;
            } else {
                $this->lastError = 'Nenhuma instituição ativa encontrada com email e senha preenchidos.';
            }
        } else {
            $this->lastError = 'Sem conexão com o banco de dados.';
        }
    }

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function getDebugInfo(): string
    {
        return $this->debugInfo;
    }

    public function enviarRedefinicaoSenha(string $destinatario, string $nome, string $link): bool
    {
        try {
            $this->mail->addAddress($destinatario, $nome);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Redefinição de Senha - IESB';

            $this->mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background: #f4f4f4; padding: 40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 8px; overflow: hidden;">
          <tr>
            <td style="background: #0d6efd; padding: 30px; text-align: center;">
              <h1 style="color: #ffffff; margin: 0; font-size: 24px;">IESB - Redefinição de Senha</h1>
            </td>
          </tr>
          <tr>
            <td style="padding: 30px;">
              <p style="font-size: 16px; color: #333;">Olá, <strong>{$nome}</strong>!</p>
              <p style="font-size: 16px; color: #333;">Recebemos uma solicitação de redefinição de senha para sua conta na Área do Aluno.</p>
              <p style="font-size: 16px; color: #333;">Clique no botão abaixo para definir uma nova senha:</p>
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding: 20px 0;">
                    <a href="{$link}" style="background: #0d6efd; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 6px; font-size: 16px; display: inline-block;">Redefinir Senha</a>
                  </td>
                </tr>
              </table>
              <p style="font-size: 14px; color: #999;">Se você não solicitou esta redefinição, ignore este email. O link expira em 1 hora.</p>
              <p style="font-size: 14px; color: #999;">Atenciosamente,<br>Equipe IESB</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

            $this->mail->AltBody = "Olá, {$nome}!\n\nRecebemos uma solicitação de redefinição de senha.\n\nAcesse o link para definir uma nova senha: {$link}\n\nSe você não solicitou, ignore este email.\n\nEquipe IESB";

            $this->mail->send();
            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}
