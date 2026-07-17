<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\ImageService;
use App\Services\LogService;
use App\Services\SessaoService;
use App\Support\Session;

final class SessaoController extends Controller
{
    private SessaoService $sessaoService;
    private LogService $logService;
    private ImageService $imageService;

    public function __construct()
    {
        $this->sessaoService = new SessaoService();
        $this->logService = new LogService();
        $this->imageService = new ImageService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/sessao/index', [
            'title' => 'Sessões',
            'currentRoute' => '/admin/sessao',
            'sessoes' => $this->sessaoService->list(),
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/sessao/novo', [
            'title' => 'Nova Sessão',
            'currentRoute' => '/admin/sessao',
        ], 'admin');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $sessao = $id > 0 ? $this->sessaoService->find($id) : null;

        if ($id > 0 && !$sessao) {
            Session::setFlash('flash', 'Sessão não encontrada.');
            $this->redirect('/admin/sessao');
            return;
        }

        $this->render('pages/admin/sessao/edit', [
            'title' => $id > 0 ? 'Editar Sessão' : 'Nova Sessão',
            'currentRoute' => '/admin/sessao',
            'sessao' => $sessao,
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $titulo = trim((string) $this->input('titulo', ''));
        $slug = trim((string) $this->input('slug', ''));
        $badge = trim((string) $this->input('badge', ''));
        $apresenta = trim((string) $this->input('apresenta', ''));
        $banner = '';
        $file = $_FILES['banner'] ?? null;
        $uploadError = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed, true)) {
                $uploadError = 'Formato de imagem não permitido. Use JPG, JPEG, PNG, GIF ou WebP.';
            } else {
                $filename = 'banner-' . time() . '-' . mt_rand(100, 999) . '.' . $ext;
                $destDir = dirname(__DIR__, 3) . '/public/assets/img/banner';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $destPath = $destDir . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $banner = 'assets/img/banner/' . $filename;
                } else {
                    $uploadError = 'Erro ao salvar o arquivo de imagem. Verifique as permissões do diretório.';
                }
            }
        } elseif ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadError = 'Erro no upload da imagem (código: ' . $file['error'] . ').';
        }

        if ($uploadError) {
            Session::setFlash('flash', $uploadError);
        }

        if ($id > 0 && $banner === '') {
            $existing = $this->sessaoService->find($id);
            $banner = (string) ($existing['banner'] ?? '');
        }
        $texto = (string) $this->input('texto', '');
        $midiaInput = (string) $this->input('midia', '');
        $midia = match ($midiaInput) {
            'C' => 1,
            'G' => 0,
            default => null,
        };

        if ($titulo === '') {
            Session::setFlash('flash', 'Informe o título da sessão.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/sessao/editar' . $suffix);
            return;
        }

        $sessaoId = $this->sessaoService->save([
            'id' => $id,
            'titulo' => $titulo,
            'slug' => $slug,
            'badge' => $badge,
            'apresenta' => $apresenta,
            'banner' => $banner,
            'texto' => $texto,
            'midia' => $midia,
        ]);

        if ($sessaoId <= 0) {
            Session::setFlash('flash', 'Erro ao salvar sessão.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/sessao/editar' . $suffix);
            return;
        }

        $acao = $id > 0 ? 'atualizar' : 'criar';
        $this->logService->log($acao, 'sessao', $sessaoId, ($id > 0 ? 'Sessão atualizada: ' : 'Sessão criada: ') . $titulo);
        $msg = $id > 0 ? 'Sessão atualizada com sucesso.' : 'Sessão criada com sucesso.';
        if ($uploadError) {
            $msg .= ' ' . $uploadError;
        }
        Session::setFlash('flash', $msg);
        $this->redirect('/admin/sessao');
    }

    public function imagem(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idFk = (int) ($_GET['id_fk'] ?? 0);
        $tabelaFk = trim((string) ($_GET['tabela_fk'] ?? ''));

        if ($idFk <= 0 || $tabelaFk === '') {
            Session::setFlash('flash', 'Parâmetros inválidos.');
            $this->redirect('/admin/sessao');
        }

        $sessao = $this->sessaoService->find($idFk);
        if (!$sessao) {
            Session::setFlash('flash', 'Sessão não encontrada.');
            $this->redirect('/admin/sessao');
        }

        $imagens = $this->imageService->listarPorFk($tabelaFk, $idFk);

        $this->render('pages/admin/sessao/imagem', [
            'title' => 'Imagens — ' . ($sessao['titulo'] ?? ''),
            'currentRoute' => '/admin/sessao',
            'sessao' => $sessao,
            'idFk' => $idFk,
            'tabelaFk' => $tabelaFk,
            'imagens' => $imagens,
        ], 'admin');
    }

    public function uploadImagem(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $idFk = (int) $this->input('id_fk', 0);
        $tabelaFk = trim((string) $this->input('tabela_fk', ''));
        $legenda = trim((string) $this->input('legenda', ''));

        if ($idFk <= 0 || $tabelaFk === '') {
            Session::setFlash('flash', 'Parâmetros inválidos.');
            $this->redirect('/admin/sessao');
        }

        $path = '';
        $file = $_FILES['imagem'] ?? null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed, true)) {
                $filename = 'sessao-' . $idFk . '-' . time() . '-' . mt_rand(100, 999) . '.' . $ext;
                $destDir = dirname(__DIR__, 3) . '/public/assets/img/sessao';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $destPath = $destDir . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $path = 'assets/img/sessao/' . $filename;
                }
            }
        }

        if ($path === '') {
            Session::setFlash('flash', 'Erro ao fazer upload da imagem. Verifique o formato e tamanho.');
            $this->redirect('/admin/sessao/imagem?id_fk=' . $idFk . '&tabela_fk=' . $tabelaFk);
            return;
        }

        $this->imageService->salvar($tabelaFk, $idFk, $path, $legenda ?: null);
        $this->logService->log('criar', 'imagem', 0, 'Imagem adicionada à sessão ' . $idFk);

        Session::setFlash('flash', 'Imagem salva com sucesso.');
        $this->redirect('/admin/sessao/imagem?id_fk=' . $idFk . '&tabela_fk=' . $tabelaFk);
    }

    public function deletar(): void
    {
        if (!$this->isStaff()) {
            $this->json(['sucesso' => false, 'erro' => 'Acesso negado.']);
            return;
        }

        $id = (int) ($this->input('id', 0) ?: ($_POST['id'] ?? 0));

        if ($id <= 0) {
            $this->json(['sucesso' => false, 'erro' => 'ID inválido.']);
            return;
        }

        $sessao = $this->sessaoService->find($id);

        if (!$sessao) {
            $this->json(['sucesso' => false, 'erro' => 'Sessão não encontrada.']);
            return;
        }

        $this->sessaoService->delete($id);
        $this->logService->log('deletar', 'sessao', $id, 'Sessão deletada: ' . ($sessao['titulo'] ?? ''));
        $this->json(['sucesso' => true]);
    }

    public function deletarImagem(): void
    {
        if (!$this->isStaff()) {
            $this->json(['sucesso' => false, 'erro' => 'Acesso negado.']);
            return;
        }

        $id = (int) ($this->input('id', 0) ?: ($_POST['id'] ?? 0));
        $idFk = (int) ($this->input('id_fk', 0) ?: ($_POST['id_fk'] ?? 0));
        $tabelaFk = trim((string) ($this->input('tabela_fk', '') ?: ($_POST['tabela_fk'] ?? '')));

        if ($id <= 0 || $idFk <= 0 || $tabelaFk === '') {
            $this->json(['sucesso' => false, 'erro' => 'Parâmetros inválidos.']);
            return;
        }

        $this->imageService->deletar($id);
        $this->logService->log('deletar', 'imagem', $id, 'Imagem removida da sessão ' . $idFk);
        $this->json(['sucesso' => true]);
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}
