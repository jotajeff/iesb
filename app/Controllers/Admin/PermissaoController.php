<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\LogService;
use App\Services\PermissaoService;
use App\Support\Session;

final class PermissaoController extends Controller
{
    public function __construct(
        private readonly PermissaoService $permissaoService = new PermissaoService(),
        private readonly LogService $logService = new LogService(),
    ) {
    }

    public function index(): void
    {
        if (!$this->podeGerenciarPermissoes()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $idFuncao = (int) ($_GET['funcao_id'] ?? 0);
        $idModulo = (int) ($_GET['editar_modulo'] ?? 0);
        $idFuncaoEdicao = (int) ($_GET['editar_funcao'] ?? 0);

        $funcoes = $this->permissaoService->funcoes();
        if ($idFuncao <= 0 && !empty($funcoes)) {
            $idFuncao = (int) ($funcoes[0]['id'] ?? 0);
        }

        $this->render('pages/admin/permissoes/index', [
            'title' => 'Permissões',
            'currentRoute' => '/admin/permissoes',
            'modulos' => $this->permissaoService->modulos(),
            'funcoes' => $funcoes,
            'moduloEdicao' => $idModulo > 0 ? $this->permissaoService->modulo($idModulo) : null,
            'funcaoEdicao' => $idFuncaoEdicao > 0 ? $this->permissaoService->funcao($idFuncaoEdicao) : null,
            'funcaoSelecionada' => $idFuncao > 0 ? $this->permissaoService->funcao($idFuncao) : null,
            'permissoes' => $idFuncao > 0 ? $this->permissaoService->permissoesDaFuncao($idFuncao) : [],
        ], 'admin');
    }

    public function salvarModulo(): void
    {
        if (!$this->podeGerenciarPermissoes()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $nome = trim((string) $this->input('nome', ''));
        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome do módulo.');
            $this->redirect('/admin/permissoes');
            return;
        }

        $id = (int) $this->input('id', 0);
        $moduloId = $this->permissaoService->salvarModulo([
            'id' => $id,
            'nome' => $nome,
            'rota' => trim((string) $this->input('rota', '')),
            'icone' => trim((string) $this->input('icone', '')),
            'ordem' => (int) $this->input('ordem', 0),
            'ativo' => (int) $this->input('ativo', 1),
        ]);

        if ($moduloId <= 0) {
            Session::setFlash('flash', 'Não foi possível salvar o módulo.');
        } else {
            $this->logService->log($id > 0 ? 'atualizar' : 'criar', 'modulo', $moduloId, 'Módulo: ' . $nome);
            Session::setFlash('flash', $id > 0 ? 'Módulo atualizado com sucesso.' : 'Módulo criado com sucesso.');
        }
        $this->redirect('/admin/permissoes');
    }

    public function salvarFuncao(): void
    {
        if (!$this->podeGerenciarPermissoes()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $nome = trim((string) $this->input('nome', ''));
        if ($nome === '') {
            Session::setFlash('flash', 'Informe o nome da função.');
            $this->redirect('/admin/permissoes');
            return;
        }

        $id = (int) $this->input('id', 0);
        $funcaoId = $this->permissaoService->salvarFuncao([
            'id' => $id,
            'nome' => $nome,
            'descricao' => trim((string) $this->input('descricao', '')),
            'ativo' => (int) $this->input('ativo', 1),
        ]);

        if ($funcaoId <= 0) {
            Session::setFlash('flash', 'Não foi possível salvar a função.');
        } else {
            $this->logService->log($id > 0 ? 'atualizar' : 'criar', 'usuarios_funcao', $funcaoId, 'Função: ' . $nome);
            Session::setFlash('flash', $id > 0 ? 'Função atualizada com sucesso.' : 'Função criada com sucesso.');
        }
        $this->redirect('/admin/permissoes?funcao_id=' . $funcaoId);
    }

    public function salvarPermissoes(): void
    {
        if (!$this->podeGerenciarPermissoes()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $idFuncao = (int) $this->input('id_funcao', 0);
        $permissoesEnviadas = (array) ($_POST['permissoes'] ?? []);
        $permissoes = [];
        foreach ($this->permissaoService->modulos() as $modulo) {
            $idModulo = (int) ($modulo['id'] ?? 0);
            if ($idModulo > 0) {
                // Checkboxes desmarcados não chegam no POST e precisam zerar
                // a permissão anteriormente salva.
                $permissoes[$idModulo] = (array) ($permissoesEnviadas[$idModulo] ?? []);
            }
        }
        if ($idFuncao <= 0 || !$this->permissaoService->salvarPermissoes($idFuncao, $permissoes)) {
            Session::setFlash('flash', 'Não foi possível salvar as permissões.');
        } else {
            $this->logService->log('atualizar', 'usuarios_funcao_permissao', $idFuncao, 'Permissões atualizadas para a função #' . $idFuncao);
            Session::setFlash('flash', 'Permissões salvas com sucesso.');
        }
        $this->redirect('/admin/permissoes?funcao_id=' . $idFuncao);
    }

    private function podeGerenciarPermissoes(): bool
    {
        $user = Session::get('user');
        $role = is_array($user) ? (string) ($user['role'] ?? $user['tipo'] ?? '') : '';
        return in_array($role, ['admin', 'operador'], true);
    }
}
