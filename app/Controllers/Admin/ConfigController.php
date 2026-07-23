<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\ConfigService;
use App\Services\CarouselService;
use App\Services\LogService;
use App\Services\TarefaService;
use App\Support\Session;

final class ConfigController extends Controller
{
    private ConfigService $configService;
    private CarouselService $carouselService;
    private LogService $logService;
    private TarefaService $tarefaService;

    public function __construct()
    {
        $this->configService = new ConfigService();
        $this->carouselService = new CarouselService();
        $this->logService = new LogService();
        $this->tarefaService = new TarefaService();
    }

    public function modalidade(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/modalidade/index', [
            'title' => 'Modalidades',
            'currentRoute' => '/admin/modalidade',
            'modalidades' => $this->configService->modalidades(),
        ], 'admin');
    }

    public function editModalidade(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $modalidade = $id > 0 ? $this->configService->findModalidade($id) : null;

        if ($id > 0 && !$modalidade) {
            Session::setFlash('flash', 'Modalidade não encontrada.');
            $this->redirect('/admin/modalidade');
            return;
        }

        $this->render('pages/admin/modalidade/edit', [
            'title' => $id > 0 ? 'Editar Modalidade' : 'Nova Modalidade',
            'currentRoute' => '/admin/modalidade',
            'modalidade' => $modalidade,
        ], 'admin');
    }

    public function updateModalidade(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $ativo = (int) $this->input('ativo', 1);

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome da modalidade.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/modalidade/edit' . $suffix);
            return;
        }

        $modalidadeId = $this->configService->saveModalidade($id, $nome, $ativo);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Modalidade atualizada: ' : 'Modalidade criada: ') . $nome;
        $this->logService->log($acao, 'modalidade', $modalidadeId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Modalidade atualizada com sucesso.' : 'Modalidade criada com sucesso.');
        $this->redirect('/admin/modalidade');
    }

    public function segmento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/segmento/index', [
            'title' => 'Segmentos',
            'currentRoute' => '/admin/segmento',
            'segmentos' => $this->configService->segmentos(),
        ], 'admin');
    }

    public function editSegmento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $segmento = $id > 0 ? $this->configService->findSegmento($id) : null;

        if ($id > 0 && !$segmento) {
            Session::setFlash('flash', 'Segmento não encontrado.');
            $this->redirect('/admin/segmento');
            return;
        }

        $this->render('pages/admin/segmento/edit', [
            'title' => $id > 0 ? 'Editar Segmento' : 'Novo Segmento',
            'currentRoute' => '/admin/segmento',
            'segmento' => $segmento,
        ], 'admin');
    }

    public function updateSegmento(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $ativo = strtoupper(trim((string) $this->input('ativo', 'S')));

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome do segmento.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/segmento/edit' . $suffix);
            return;
        }

        $segmentoId = $this->configService->saveSegmento($id, $nome, $ativo);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Segmento atualizado: ' : 'Segmento criado: ') . $nome;
        $this->logService->log($acao, 'segmento', $segmentoId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Segmento atualizado com sucesso.' : 'Segmento criado com sucesso.');
        $this->redirect('/admin/segmento');
    }

    public function nivel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/nivel/index', [
            'title' => 'Níveis',
            'currentRoute' => '/admin/nivel',
            'niveis' => $this->configService->niveis(),
        ], 'admin');
    }

    public function editNivel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $nivel = $id > 0 ? $this->configService->findNivel($id) : null;

        if ($id > 0 && !$nivel) {
            Session::setFlash('flash', 'Nível não encontrado.');
            $this->redirect('/admin/nivel');
            return;
        }

        $this->render('pages/admin/nivel/edit', [
            'title' => $id > 0 ? 'Editar Nível' : 'Novo Nível',
            'currentRoute' => '/admin/nivel',
            'nivel' => $nivel,
        ], 'admin');
    }

    public function carousel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/config/carousel/index', [
            'title' => 'Carrossel - Itens',
            'currentRoute' => '/admin/config/carousel',
            'items' => $this->carouselService->allItems(),
        ], 'admin');
    }

    public function editCarousel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $item = $id > 0 ? $this->carouselService->findItem($id) : null;

        if ($id > 0 && !$item) {
            Session::setFlash('flash', 'Item não encontrado.');
            $this->redirect('/admin/config/carousel');
            return;
        }

        $this->render('pages/admin/config/carousel/editar', [
            'title' => $id > 0 ? 'Editar Item' : 'Novo Item',
            'currentRoute' => '/admin/config/carousel',
            'item' => $item,
        ], 'admin');
    }

    public function updateCarousel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $link = trim((string) $this->input('link', ''));
        $titulo = trim((string) $this->input('titulo', ''));
        $ordem = (int) $this->input('ordem', 0);
        $ativo = (string) $this->input('ativo', 'S');
        $target = trim((string) $this->input('target', '_self'));
        $user = Session::get('user');
        $userId = (int) ($user['id'] ?? 1);

        $imagemPath = '';

        $file = $_FILES['imagem'] ?? null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed, true)) {
                $filename = 'carousel-item-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destDir = dirname(__DIR__, 3) . '/public/assets/img/carousel';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $destPath = $destDir . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $imagemPath = 'assets/img/carousel/' . $filename;
                }
            }
        }

        $data = [
            'id' => $id,
            'id_carousel' => 1,
            'titulo' => $titulo,
            'link' => $link,
            'target' => $target,
            'ordem' => $ordem,
            'ativo' => $ativo,
            'criado_por' => $userId,
        ];

        if ($imagemPath !== '') {
            $data['imagem'] = $imagemPath;
        } elseif ($id > 0) {
            $existing = $this->carouselService->findItem($id);
            $data['imagem'] = (string) ($existing['imagem'] ?? '');
        }

        $itemId = $this->carouselService->saveCarouselItem($data);

        if ($itemId <= 0) {
            Session::setFlash('flash', 'Erro ao salvar item.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/config/carousel/editar' . $suffix);
            return;
        }

        $acao = $id > 0 ? 'atualizar' : 'criar';
        $this->logService->log($acao, 'carousel_item', $itemId, ($id > 0 ? 'Item atualizado' : 'Item criado') . ': ' . ($titulo ?: 'sem título'));

        Session::setFlash('flash', $id > 0 ? 'Item atualizado com sucesso.' : 'Item criado com sucesso.');
        $this->redirect('/admin/config/carousel/editar?id=' . $itemId);
    }

    public function deleteCarouselItem(): void
    {
        if (!$this->isStaff()) {
            $this->json(['sucesso' => false, 'erro' => 'Acesso negado.']);
        }

        $itemId = (int) ($this->input('item_id', 0) ?: ($_POST['item_id'] ?? 0));

        if ($itemId <= 0) {
            $this->json(['sucesso' => false, 'erro' => 'Item inválido.']);
        }

        $this->carouselService->deleteCarouselItem($itemId);
        $this->logService->log('deletar', 'carousel_item', $itemId, 'Item do carrossel removido');
        $this->json(['sucesso' => true]);
    }

    public function setor(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/setor/index', [
            'title' => 'Setores',
            'currentRoute' => '/admin/setor',
            'setores' => $this->tarefaService->setores(),
        ], 'admin');
    }

    public function editSetor(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $setor = $id > 0 ? $this->tarefaService->findSetor($id) : null;

        if ($id > 0 && !$setor) {
            Session::setFlash('flash', 'Setor não encontrado.');
            $this->redirect('/admin/setor');
            return;
        }

        $this->render('pages/admin/setor/edit', [
            'title' => $id > 0 ? 'Editar Setor' : 'Novo Setor',
            'currentRoute' => '/admin/setor',
            'setor' => $setor,
        ], 'admin');
    }

    public function updateSetor(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $setorNome = trim((string) $this->input('setor', ''));

        if ($setorNome === '') {
            Session::setFlash('flash', 'Informe o nome do setor.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/setor/edit' . $suffix);
            return;
        }

        $setorId = $this->tarefaService->saveSetor($id, $setorNome);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Setor atualizado: ' : 'Setor criado: ') . $setorNome;
        $this->logService->log($acao, 'setor', $setorId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Setor atualizado com sucesso.' : 'Setor criado com sucesso.');
        $this->redirect('/admin/setor');
    }

    public function updateNivel(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $ativo = (int) $this->input('ativo', 1);
        $apresentacao = (string) $this->input('apresentacao', '');

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome do nível.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/nivel/edit' . $suffix);
            return;
        }

        $nivelId = $this->configService->saveNivel($id, $nome, $ativo, $apresentacao);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Nível atualizado: ' : 'Nível criado: ') . $nome;
        $this->logService->log($acao, 'nivel', $nivelId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Nível atualizado com sucesso.' : 'Nível criado com sucesso.');
        $this->redirect('/admin/nivel');
    }

    public function categoria(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/categoria/index', [
            'title' => 'Categorias',
            'currentRoute' => '/admin/config/categoria',
            'categorias' => $this->configService->categorias(),
        ], 'admin');
    }

    public function editCategoria(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $categoria = $id > 0 ? $this->configService->findCategoria($id) : null;

        if ($id > 0 && !$categoria) {
            Session::setFlash('flash', 'Categoria não encontrada.');
            $this->redirect('/admin/config/categoria');
            return;
        }

        $this->render('pages/admin/categoria/edit', [
            'title' => $id > 0 ? 'Editar Categoria' : 'Nova Categoria',
            'currentRoute' => '/admin/config/categoria',
            'categoria' => $categoria,
        ], 'admin');
    }

    public function updateCategoria(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $slug = trim((string) $this->input('slug', ''));
        $ativo = (int) $this->input('ativo', 1);

        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome da categoria.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/config/categoria/edit' . $suffix);
            return;
        }

        $categoriaId = $this->configService->saveCategoria($id, $nome, $slug, $ativo);
        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Categoria atualizada: ' : 'Categoria criada: ') . $nome;
        $this->logService->log($acao, 'categoria_noticia', $categoriaId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Categoria atualizada com sucesso.' : 'Categoria criada com sucesso.');
        $this->redirect('/admin/config/categoria');
    }

    public function cliente(): void
    {
        if (!$this->canAccessConfig()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/config/cliente/index', [
            'title' => 'Configurações - Clientes',
            'currentRoute' => '/admin/config/cliente',
            'instituicoes' => $this->configService->instituicoes(),
        ], 'admin');
    }

    public function editCliente(): void
    {
        if (!$this->canAccessConfig()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $instituicao = $id > 0 ? $this->configService->findInstituicao($id) : null;

        if ($id > 0 && !$instituicao) {
            Session::setFlash('flash', 'Instituição não encontrada.');
            $this->redirect('/admin/config/cliente');
            return;
        }

        $this->render('pages/admin/config/cliente/editar', [
            'title' => $id > 0 ? 'Editar Instituição' : 'Nova Instituição',
            'currentRoute' => '/admin/config/cliente',
            'instituicao' => $instituicao,
        ], 'admin');
    }

    public function updateCliente(): void
    {
        if (!$this->canAccessConfig()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $razaoSocial = trim((string) $this->input('razao_social', ''));
        $nomeFantasia = trim((string) $this->input('nome_fantasia', ''));
        $dominio = trim((string) $this->input('dominio', ''));
        $documento = trim((string) $this->input('documento', ''));
        $inscricaoEstadual = trim((string) $this->input('inscricao_estadual', ''));
        $telefone = trim((string) $this->input('telefone', ''));
        $email = trim((string) $this->input('email', ''));
        $responsavelNome = trim((string) $this->input('responsavel_nome', ''));
        $tipoCliente = trim((string) $this->input('tipo_cliente', 'PJ'));
        $status = trim((string) $this->input('status', 'Ativo'));
        $senha = trim((string) $this->input('senha', ''));

        if ($razaoSocial === '' || $documento === '' || $email === '') {
            Session::setFlash('flash', 'Preencha razão social, documento e email.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/config/cliente/editar' . $suffix);
            return;
        }

        if ($id === 0 && $senha === '') {
            Session::setFlash('flash', 'Informe a senha.');
            $this->redirect('/admin/config/cliente/editar');
            return;
        }

        if ($id > 0 && $senha === '') {
            $existing = $this->configService->findInstituicao($id);
            $senha = (string) ($existing['senha'] ?? '');
        }

        $instituicaoId = $this->configService->saveInstituicao([
            'id' => $id,
            'razao_social' => $razaoSocial,
            'nome_fantasia' => $nomeFantasia,
            'dominio' => $dominio,
            'documento' => $documento,
            'inscricao_estadual' => $inscricaoEstadual,
            'telefone' => $telefone,
            'email' => $email,
            'responsavel_nome' => $responsavelNome,
            'tipo_cliente' => $tipoCliente,
            'status' => $status,
            'senha' => $senha,
        ]);

        if ($instituicaoId <= 0) {
            Session::setFlash('flash', 'Erro ao salvar instituição.');
            $suffix = $id > 0 ? '?id=' . $id : '';
            $this->redirect('/admin/config/cliente/editar' . $suffix);
            return;
        }

        $acao = $id > 0 ? 'atualizar' : 'criar';
        $descricao = ($id > 0 ? 'Instituição atualizada: ' : 'Instituição criada: ') . $razaoSocial;
        $this->logService->log($acao, 'instituicao', $instituicaoId, $descricao);

        Session::setFlash('flash', $id > 0 ? 'Instituição atualizada com sucesso.' : 'Instituição criada com sucesso.');
        $this->redirect('/admin/config/cliente');
    }

    public function funcoesDocente(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/config/funcoes-docente/index', [
            'title' => 'Funções Docente',
            'currentRoute' => '/admin/funcoes-docente',
            'funcoes' => $this->configService->funcoesDocente(),
        ], 'admin');
    }

    public function editFuncaoDocente(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $funcao = $id > 0 ? $this->configService->findFuncaoDocente($id) : null;

        $this->render('pages/admin/config/funcoes-docente/edit', [
            'title' => $funcao ? 'Editar Função Docente' : 'Nova Função Docente',
            'currentRoute' => '/admin/funcoes-docente/edit',
            'funcao' => $funcao,
        ], 'admin');
    }

    public function updateFuncaoDocente(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $nome = trim((string) $this->input('nome', ''));
        $descricao = trim((string) $this->input('descricao', ''));
        $ativo = (string) $this->input('ativo', 'S');

        if ($nome === '') {
            Session::setFlash('flash', 'O nome da função é obrigatório.');
            $this->redirect('/admin/funcoes-docente/edit' . ($id > 0 ? '?id=' . $id : ''));
            return;
        }

        $this->configService->saveFuncaoDocente($id, $nome, $descricao, $ativo);
        $this->logService->log($id > 0 ? 'atualizar' : 'criar', 'funcoes_docente', $id > 0 ? $id : 0, "Função docente " . ($id > 0 ? 'atualizada' : 'criada') . ": $nome");
        Session::setFlash('flash', 'Função docente salva com sucesso.');
        $this->redirect('/admin/funcoes-docente');
    }

    private function canAccessConfig(): bool
    {
        $auth = new AuthService();
        if (!$auth->isStaff()) {
            return false;
        }
        $user = Session::get('user');
        $userId = (int) ($user['id'] ?? 0);
        return in_array($userId, [1, 6, 7], true);
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}
