<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\LogService;
use App\Services\NoticiaService;
use App\Support\Session;

final class NoticiaController extends Controller
{
    private NoticiaService $noticiaService;
    private LogService $logService;

    public function __construct()
    {
        $this->noticiaService = new NoticiaService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/config/noticias/index', [
            'title' => 'Notícias',
            'currentRoute' => '/admin/config/noticias',
            'noticias' => $this->noticiaService->list(),
        ], 'admin');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $noticia = $id > 0 ? $this->noticiaService->find($id) : null;

        if ($id > 0 && !$noticia) {
            Session::setFlash('flash', 'Notícia não encontrada.');
            $this->redirect('/admin/config/noticias');
            return;
        }

        $this->render('pages/admin/config/noticias/editar', [
            'title' => $id > 0 ? 'Editar Notícia' : 'Nova Notícia',
            'currentRoute' => '/admin/config/noticias',
            'noticia' => $noticia,
            'categorias' => $this->noticiaService->categorias(),
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
        $resumo = trim((string) $this->input('resumo', ''));
        $conteudo = (string) $this->input('conteudo', '');
        $autor = trim((string) $this->input('autor', ''));
        $dataPublicacao = trim((string) $this->input('data_publicacao', date('Y-m-d H:i:s')));
        $dataEventoStr = trim((string) $this->input('data_evento', ''));
        $dataEvento = $dataEventoStr !== '' ? $dataEventoStr : null;
        $destaque = (int) $this->input('destaque', 0);
        $status = (string) $this->input('status', 'rascunho');
        $metaTitle = trim((string) $this->input('meta_title', ''));
        $metaDescription = trim((string) $this->input('meta_description', ''));
        $idCategoria = $this->input('id_categoria', null);
        $legendaImagem = trim((string) $this->input('legenda_imagem', ''));

        if ($titulo === '') {
            Session::setFlash('flash', 'Informe o título da notícia.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/config/noticias/editar' . $suffix);
            return;
        }

        $imagemCapa = '';
        $file = $_FILES['imagem_capa'] ?? null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed, true)) {
                $filename = 'noticia-' . time() . '-' . mt_rand(100, 999) . '.' . $ext;
                $destDir = dirname(__DIR__, 3) . '/public/assets/img/noticias';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $destPath = $destDir . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $imagemCapa = 'assets/img/noticias/' . $filename;
                }
            }
        }

        if ($id > 0 && $imagemCapa === '') {
            $existing = $this->noticiaService->find($id);
            $imagemCapa = (string) ($existing['imagem_capa'] ?? '');
        }

        $noticiaId = $this->noticiaService->save([
            'id' => $id,
            'titulo' => $titulo,
            'slug' => $slug,
            'resumo' => $resumo,
            'conteudo' => $conteudo,
            'imagem_capa' => $imagemCapa,
            'legenda_imagem' => $legendaImagem,
            'autor' => $autor,
            'data_publicacao' => $dataPublicacao,
            'data_evento' => $dataEvento,
            'destaque' => $destaque,
            'status' => $status,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'id_categoria' => $idCategoria,
        ]);

        if ($noticiaId <= 0) {
            Session::setFlash('flash', 'Erro ao salvar notícia.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/config/noticias/editar' . $suffix);
            return;
        }

        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricaoLog = ($id > 0 ? 'Notícia atualizada: ' : 'Notícia criada: ') . $titulo;
        $this->logService->log($acao, 'noticia', $noticiaId, $descricaoLog);

        Session::setFlash('flash', $id > 0 ? 'Notícia atualizada com sucesso.' : 'Notícia criada com sucesso.');
        $this->redirect('/admin/config/noticias');
    }

    public function deletar(): void
    {
        if (!$this->isStaff()) {
            $this->json(['sucesso' => false, 'erro' => 'Acesso negado.']);
        }

        $id = (int) ($this->input('id', 0) ?: ($_POST['id'] ?? 0));

        if ($id <= 0) {
            $this->json(['sucesso' => false, 'erro' => 'ID inválido.']);
        }

        $noticia = $this->noticiaService->find($id);

        if (!$noticia) {
            $this->json(['sucesso' => false, 'erro' => 'Notícia não encontrada.']);
        }

        if ((string) ($noticia['imagem_capa'] ?? '') !== '') {
            $imgPath = dirname(__DIR__, 3) . '/public/' . $noticia['imagem_capa'];
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }
        }

        $this->noticiaService->delete($id);
        $this->logService->log('deletar', 'noticia', $id, 'Notícia deletada: ' . ($noticia['titulo'] ?? ''));

        $this->json(['sucesso' => true]);
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}
