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

    public function enviarPreInscricao(string $nome, string $email, string $whatsapp, string $cursoNome = ''): bool
        {
            try {
            $this->mail->addAddress('direcao@inteligenciasouzabrazil.com', 'Direção IESB');
            $this->mail->addReplyTo($email, $nome);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Pré-inscrição - IESB';

            $cursoRow = $cursoNome !== ''
                ? '<tr><td style="font-weight: 700;">Curso:</td><td>' . htmlspecialchars($cursoNome, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                : '';

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
            <td style="background: #efc02b; padding: 30px; text-align: center;">
              <h1 style="color: #4d4f4e; margin: 0; font-size: 24px;">IESB - Pré-inscrição</h1>
            </td>
          </tr>
          <tr>
            <td style="padding: 30px;">
              <p style="font-size: 16px; color: #333;">Nova pré-inscrição recebida:</p>
              <table width="100%" cellpadding="8" style="font-size: 15px; color: #333;">
                <tr><td style="font-weight: 700; width: 100px;">Nome:</td><td>{$nome}</td></tr>
                <tr><td style="font-weight: 700;">E-mail:</td><td>{$email}</td></tr>
                <tr><td style="font-weight: 700;">WhatsApp:</td><td>{$whatsapp}</td></tr>
                {$cursoRow}
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

            $cursoAlt = $cursoNome !== '' ? "\nCurso: {$cursoNome}" : '';
$this->mail->AltBody = "Nova pré-inscrição\n\nNome: {$nome}\nE-mail: {$email}\nWhatsApp: {$whatsapp}{$cursoAlt}";

            $this->mail->send();
            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function enviarBoasVindasMatricula(string $nome, string $email, string $cpf, string $senha, string $numeroMatricula): bool
    {
        try {
            $this->mail->addAddress($email, $nome);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Bem-vindo(a) à IESB - Matrícula Realizada';

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
              <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Bem-vindo(a) à IESB</h1>
            </td>
          </tr>
          <tr>
            <td style="padding: 30px;">
              <p style="font-size: 16px; color: #333;">Olá, <strong>{$nome}</strong>!</p>
              <p style="font-size: 16px; color: #333;">Sua matrícula foi realizada com sucesso. A partir de agora você tem acesso ao Portal do Aluno.</p>
              <table width="100%" cellpadding="8" style="font-size: 15px; color: #333;">
                <tr><td style="font-weight: 700; width: 160px;">Matrícula:</td><td>{$numeroMatricula}</td></tr>
                <tr><td style="font-weight: 700;">CPF:</td><td>{$cpf}</td></tr>
                <tr><td style="font-weight: 700;">E-mail:</td><td>{$email}</td></tr>
                <tr><td style="font-weight: 700;">Senha de acesso:</td><td>{$senha}</td></tr>
              </table>
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding: 20px 0;">
                    <a href="https://inteligenciaeducacionalsouzabrazil.com/aluno/login" style="background: #0d6efd; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 6px; font-size: 16px; display: inline-block;">Acessar Portal do Aluno</a>
                  </td>
                </tr>
              </table>
              <p style="font-size: 14px; color: #999;">Recomendamos alterar sua senha no primeiro acesso.</p>
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

            $this->mail->AltBody = "Olá, {$nome}!\n\nSua matrícula foi realizada com sucesso.\nMatrícula: {$numeroMatricula}\nCPF: {$cpf}\nE-mail: {$email}\nSenha de acesso: {$senha}\n\nAcesse o Portal do Aluno em: https://inteligenciaeducacionalsouzabrazil.com/aluno/login\n\nEquipe IESB";

            $this->mail->send();
            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}
