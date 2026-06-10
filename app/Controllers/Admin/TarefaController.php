<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\TarefaService;
use App\Services\UsuarioService;
use App\Services\AdminService;
use App\Services\CommentService;
use App\Support\Session;

final class TarefaController extends Controller
{
    private TarefaService $tarefaService;
    private UsuarioService $usuarioService;
    private AdminService $adminService;
    private CommentService $comments;

    public function __construct()
    {
        $this->tarefaService = new TarefaService();
        $this->usuarioService = new UsuarioService();
        $this->adminService = new AdminService();
        $this->comments = new CommentService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as tarefas.');
            $this->redirect('/admin/login');
        }

        [$tarefas, $authUser, $isAdmin] = $this->prepareTarefasData();
        $colunas = [
            'tarefa' => [],
            'execucao' => [],
            'finalizado' => [],
        ];

        foreach ($tarefas as $tarefa) {
            $coluna = $this->taskColumnForStatus((string) ($tarefa['situacao'] ?? 'criada'));
            $tarefa['coluna'] = $coluna;
            $colunas[$coluna][] = $tarefa;
        }

        $this->render('pages/admin/tarefas/index', [
            'title' => 'Tarefas',
            'currentRoute' => '/admin/tarefas',
            'colunas' => $colunas,
            'setores' => $this->tarefaService->setores(),
            'usuarios' => $this->usuarioService->usuarios(1000),
            'isAdmin' => $isAdmin,
            'authUser' => $authUser,
        ], 'admin');
    }

    public function lista(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as tarefas.');
            $this->redirect('/admin/login');
        }

        [$tarefas, $authUser, $isAdmin] = $this->prepareTarefasData();
        $situacaoFiltro = strtolower(trim((string) ($_GET['situacao'] ?? '')));
        $situacoesValidas = array_keys($this->taskSituations());
        if ($situacaoFiltro !== '' && !in_array($situacaoFiltro, $situacoesValidas, true)) {
            $situacaoFiltro = '';
        }

        if ($situacaoFiltro !== '') {
            $tarefas = array_values(array_filter(
                $tarefas,
                static fn (array $tarefa): bool => strtolower((string) ($tarefa['situacao'] ?? '')) === $situacaoFiltro
            ));
        }

        $this->render('pages/admin/tarefas/lista', [
            'title' => 'Lista de Tarefas',
            'currentRoute' => '/admin/tarefas',
            'tarefas' => $tarefas,
            'isAdmin' => $isAdmin,
            'authUser' => $authUser,
            'situacoes' => $this->taskSituations(),
            'filtroSituacao' => $situacaoFiltro,
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->render('pages/admin/tarefas/novo', [
            'title' => 'Nova Tarefa',
            'currentRoute' => '/admin/tarefas',
            'setores' => $this->tarefaService->setores(),
            'usuarios' => $this->usuarioService->usuarios(1000),
            'situacoes' => $this->taskSituations(),
            'prioridades' => $this->taskPriorities(),
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $setorId = (int) $this->input('setor', 0);
        $tarefa = trim((string) $this->input('tarefa', ''));
        $responsavel = (int) $this->input('responsavel', 0);
        $situacao = $this->normalizeTaskSituation((string) $this->input('situacao', 'criada'));
        $prioridade = $this->normalizeTaskPriority((int) $this->input('prioridade', 1));
        $criadoPor = (int) (($this->authUser()['id'] ?? 0));

        if ($setorId <= 0 || $tarefa === '' || $criadoPor <= 0) {
            Session::setFlash('flash', 'Preencha setor e descrição da tarefa.');
            $this->redirect('/admin/tarefas/novo');
            return;
        }

        $tarefaId = $this->tarefaService->criarTarefa($setorId, $tarefa, $criadoPor, $responsavel > 0 ? $responsavel : null, $situacao, $prioridade);
        $this->adminService->log('criar', 'tarefa', $tarefaId, 'Tarefa criada: ' . $tarefa);
        Session::setFlash('flash', 'Tarefa criada com sucesso.');
        $this->redirect('/admin/tarefas');
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $tarefa = $this->tarefaService->findTarefa($id);

        if (!$tarefa) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $this->render('pages/admin/tarefas/editar', [
            'title' => 'Editar Tarefa',
            'currentRoute' => '/admin/tarefas',
            'tarefa' => $tarefa,
            'setores' => $this->tarefaService->setores(),
            'usuarios' => $this->usuarioService->usuarios(1000),
            'situacoes' => $this->taskSituations(),
            'prioridades' => $this->taskPriorities(),
        ], 'admin');
    }

    public function atualizar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) $this->input('id', 0);
        $setorId = (int) $this->input('setor', 0);
        $tarefa = trim((string) $this->input('tarefa', ''));
        $responsavel = (int) $this->input('responsavel', 0);
        $situacao = $this->normalizeTaskSituation((string) $this->input('situacao', 'criada'));
        $prioridade = $this->normalizeTaskPriority((int) $this->input('prioridade', 1));

        if ($id <= 0 || $setorId <= 0 || $tarefa === '') {
            Session::setFlash('flash', 'Preencha setor e descrição da tarefa.');
            $this->redirect('/admin/tarefas/editar?id=' . $id);
            return;
        }

        $existing = $this->tarefaService->findTarefa($id);
        if (!$existing) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $this->tarefaService->atualizarTarefa($id, $setorId, $tarefa, $responsavel > 0 ? $responsavel : null, $situacao, $prioridade);
        $this->adminService->log('atualizar', 'tarefa', $id, 'Tarefa atualizada: ' . $tarefa);
        Session::setFlash('flash', 'Tarefa atualizada com sucesso.');
        $this->redirect('/admin/tarefas');
    }

    public function show(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar as tarefas.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $tarefa = $this->tarefaService->findTarefa($id);

        if (!$tarefa) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $tarefa['situacao_label'] = $this->taskStatusLabel((string) ($tarefa['situacao'] ?? 'criada'));
        $tarefa['situacao_class'] = $this->taskStatusClass((string) ($tarefa['situacao'] ?? 'criada'));
        $tarefa['prioridade_label'] = $this->taskPriorityLabel((int) ($tarefa['prioridade'] ?? 1));
        $tarefa['prioridade_class'] = $this->taskPriorityClass((int) ($tarefa['prioridade'] ?? 1));
        $tarefa['comentarios_total'] = $this->comments->countFor('tarefas', $id);

        $this->render('pages/admin/tarefas/show', [
            'title' => 'Tarefa #' . $id,
            'currentRoute' => '/admin/tarefas',
            'tarefa' => $tarefa,
            'comentarios' => $this->comments->listFor('tarefas', $id),
        ], 'admin');
    }

    public function comentario(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $tarefaId = (int) $this->input('tarefa_id', 0);
        $comentario = trim((string) $this->input('comentario', ''));

        if ($tarefaId <= 0 || $comentario === '') {
            Session::setFlash('flash', 'Digite um comentário válido.');
            $this->redirect('/admin/tarefas/show?id=' . $tarefaId);
            return;
        }

        $tarefa = $this->tarefaService->findTarefa($tarefaId);
        if (!$tarefa) {
            Session::setFlash('flash', 'Tarefa não encontrada.');
            $this->redirect('/admin/tarefas');
            return;
        }

        $comentarioId = $this->comments->createFor('tarefas', $tarefaId, $comentario);
        $this->adminService->log('criar', 'comentario', $comentarioId, 'Comentário adicionado na tarefa #' . $tarefaId);
        Session::setFlash('flash', 'Comentário adicionado com sucesso.');
        $this->redirect('/admin/tarefas/show?id=' . $tarefaId);
    }

    private function prepareTarefasData(): array
    {
        $tarefas = $this->tarefaService->tarefas();
        $authUser = $this->authUser();
        $isAdmin = (string) ($authUser['role'] ?? $authUser['type'] ?? '') === 'admin';
        $currentUserId = (int) ($authUser['id'] ?? 0);

        if (!$isAdmin && $currentUserId > 0) {
            $tarefas = array_values(array_filter(
                $tarefas,
                static fn (array $tarefa): bool => (int) ($tarefa['responsavel_id'] ?? 0) === $currentUserId
            ));
        }

        $tarefas = array_map(function (array $tarefa): array {
            $situacao = (string) ($tarefa['situacao'] ?? 'criada');
            $prioridade = (int) ($tarefa['prioridade'] ?? 1);
            $tarefa['situacao_label'] = $this->taskStatusLabel($situacao);
            $tarefa['situacao_class'] = $this->taskStatusClass($situacao);
            $tarefa['prioridade_label'] = $this->taskPriorityLabel($prioridade);
            $tarefa['prioridade_class'] = $this->taskPriorityClass($prioridade);
            $tarefa['comentarios_total'] = (int) ($tarefa['comentarios_total'] ?? 0);
            return $tarefa;
        }, $tarefas);

        return [$tarefas, $authUser, $isAdmin];
    }

    private function taskSituations(): array
    {
        return [
            'criada' => 'Criada',
            'execucao' => 'Execução',
            'finalizada' => 'Finalizada',
            'revisao' => 'Revisão',
        ];
    }

    private function taskPriorities(): array
    {
        return [
            1 => 'Baixa',
            2 => 'Média',
            3 => 'Alta',
        ];
    }

    private function normalizeTaskSituation(string $value): string
    {
        $value = strtolower(trim($value));
        return array_key_exists($value, $this->taskSituations()) ? $value : 'criada';
    }

    private function normalizeTaskPriority(int $value): int
    {
        return match (true) {
            $value >= 3 => 3,
            $value === 2 => 2,
            default => 1,
        };
    }

    private function taskColumnForStatus(string $status): string
    {
        $status = $this->normalizeTaskSituation($status);

        return match ($status) {
            'finalizada' => 'finalizado',
            'execucao', 'revisao' => 'execucao',
            default => 'tarefa',
        };
    }

    private function taskStatusLabel(string $status): string
    {
        $status = $this->normalizeTaskSituation($status);
        return $this->taskSituations()[$status] ?? 'Criada';
    }

    private function taskStatusClass(string $status): string
    {
        return match ($this->normalizeTaskSituation($status)) {
            'criada' => 'bg-secondary',
            'execucao' => 'bg-primary',
            'finalizada' => 'bg-success',
            'revisao' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    private function taskPriorityLabel(int $priority): string
    {
        return $this->taskPriorities()[$this->normalizeTaskPriority($priority)] ?? 'Baixa';
    }

    private function taskPriorityClass(int $priority): string
    {
        return match ($this->normalizeTaskPriority($priority)) {
            3 => 'bg-danger',
            2 => 'bg-warning text-dark',
            default => 'bg-success',
        };
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }

    private function authUser(): array
    {
        $user = Session::get('user');
        return is_array($user) ? $user : [];
    }
}
