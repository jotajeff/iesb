<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class EmailService
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->Host = 'mail.posmedica.com.br';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'ti@posmedica.com.br';
        $this->mail->Password = 'T!@po5M4d!c@';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = 465;
        $this->mail->CharSet = 'UTF-8';

        $this->mail->setFrom('ti@posmedica.com.br', 'IESB - Área do Aluno');
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
        } catch (Exception) {
            return false;
        }
    }
}
