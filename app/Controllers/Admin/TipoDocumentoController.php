<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\LogService;
use App\Support\Session;

final class TipoDocumentoController extends Controller
{
    private LogService $logService;

    public function __construct()
    {
        $this->logService = new LogService();
    }

    private function isStaff(): bool
    {
        return (new \App\Services\AuthService())->isStaff();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $tipos = [];
        $gruposNomes = [];
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $grupos = $pdo->query('SELECT id, descricao FROM documento_grupo ORDER BY descricao ASC')->fetchAll() ?: [];
                foreach ($grupos as $g) {
                    $gruposNomes[(int) $g['id']] = $g['descricao'];
                }

                $stmt = $pdo->prepare(
                    'SELECT t.id, t.id_grupo, t.descricao, t.obrigatorio, t.ordem, t.ativo, g.descricao AS grupo_nome'
                    . ' FROM documento_tipo t'
                    . ' LEFT JOIN documento_grupo g ON g.id = t.id_grupo'
                    . ' ORDER BY g.descricao ASC, t.ordem ASC, t.descricao ASC'
                );
                $stmt->execute();
                $tipos = $stmt->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[TIPO DOC] Erro: ' . $e->getMessage());
            }
        }

        $this->render('pages/admin/tipos_documentos/index', [
            'title' => 'Tipos de Documentos',
            'currentRoute' => '/admin/tipos-documentos',
            'tipos' => $tipos,
            'gruposNomes' => $gruposNomes,
        ], 'admin');
    }

    public function novo(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $this->renderForm(null, []);
    }

    public function editar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        $tipo = null;
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT id, id_grupo, descricao, obrigatorio, ordem, ativo FROM documento_tipo WHERE id = :id LIMIT 1');
                $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $tipo = $stmt->fetch() ?: null;
            } catch (\Throwable $e) {
                error_log('[TIPO DOC] Erro: ' . $e->getMessage());
            }
        }

        if (!$tipo) {
            Session::setFlash('flash', 'Tipo de documento não encontrado.');
            $this->redirect('/admin/tipos-documentos');
            return;
        }

        $this->renderForm($tipo, [(int) ($tipo['id_grupo'] ?? 0)]);
    }

    private function renderForm(?array $tipo, array $selecionados): void
    {
        $grupos = [];
        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $grupos = $pdo->query('SELECT id, descricao, ativo FROM documento_grupo ORDER BY descricao ASC')->fetchAll() ?: [];
            } catch (\Throwable $e) {
                error_log('[TIPO DOC] Erro: ' . $e->getMessage());
            }
        }

        $this->render('pages/admin/tipos_documentos/form', [
            'title' => $tipo ? 'Editar Tipo de Documento' : 'Novo Tipo de Documento',
            'currentRoute' => '/admin/tipos-documentos',
            'tipo' => $tipo,
            'grupos' => $grupos,
            'selecionados' => $selecionados,
        ], 'admin');
    }

    public function salvar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $descricao = trim((string) $this->input('descricao', ''));
        $obrigatorio = $this->input('obrigatorio', '0') === '1' ? 1 : 0;
        $ordem = (int) $this->input('ordem', 0);
        $ativo = $this->input('ativo', '1') === '1' ? 1 : 0;
        $grupos = array_unique(array_map('intval', (array) ($_POST['grupos'] ?? [])));
        $grupos = array_filter($grupos, static fn (int $g): bool => $g > 0);

        if ($descricao === '') {
            Session::setFlash('flash', 'Informe a descrição do tipo de documento.');
            $this->redirect('/admin/tipos-documentos/novo');
            return;
        }

        if (empty($grupos)) {
            Session::setFlash('flash', 'Selecione ao menos um grupo.');
            $this->redirect('/admin/tipos-documentos/novo');
            return;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            Session::setFlash('flash', 'Erro de conexão com o banco de dados.');
            $this->redirect('/admin/tipos-documentos/novo');
            return;
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO documento_tipo (id_grupo, descricao, obrigatorio, ordem, ativo) VALUES (:id_grupo, :descricao, :obrigatorio, :ordem, :ativo)');
            foreach ($grupos as $gid) {
                $stmt->bindValue(':id_grupo', $gid, \PDO::PARAM_INT);
                $stmt->bindValue(':descricao', $descricao, \PDO::PARAM_STR);
                $stmt->bindValue(':obrigatorio', $obrigatorio, \PDO::PARAM_INT);
                $stmt->bindValue(':ordem', $ordem, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', $ativo, \PDO::PARAM_INT);
                $stmt->execute();
            }
            $pdo->commit();
            $this->logService->log('criar', 'documento_tipo', 0, "Tipo de documento criado para " . count($grupos) . " grupo(s): $descricao");
            Session::setFlash('flash', 'Tipo de documento criado com sucesso para ' . count($grupos) . ' grupo(s).');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[TIPO DOC] Erro ao salvar: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao salvar o tipo de documento.');
        }

        $this->redirect('/admin/tipos-documentos');
    }

    public function atualizar(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $id = (int) $this->input('id', 0);
        $descricao = trim((string) $this->input('descricao', ''));
        $idGrupo = (int) $this->input('id_grupo', 0);
        $obrigatorio = $this->input('obrigatorio', '0') === '1' ? 1 : 0;
        $ordem = (int) $this->input('ordem', 0);
        $ativo = $this->input('ativo', '1') === '1' ? 1 : 0;

        if ($id <= 0 || $descricao === '' || $idGrupo <= 0) {
            Session::setFlash('flash', 'Dados inválidos.');
            $this->redirect('/admin/tipos-documentos');
            return;
        }

        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('UPDATE documento_tipo SET id_grupo = :id_grupo, descricao = :descricao, obrigatorio = :obrigatorio, ordem = :ordem, ativo = :ativo WHERE id = :id');
                $stmt->bindValue(':id_grupo', $idGrupo, \PDO::PARAM_INT);
                $stmt->bindValue(':descricao', $descricao, \PDO::PARAM_STR);
                $stmt->bindValue(':obrigatorio', $obrigatorio, \PDO::PARAM_INT);
                $stmt->bindValue(':ordem', $ordem, \PDO::PARAM_INT);
                $stmt->bindValue(':ativo', $ativo, \PDO::PARAM_INT);
                $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $this->logService->log('atualizar', 'documento_tipo', $id, "Tipo de documento atualizado: $descricao");
                Session::setFlash('flash', 'Tipo de documento atualizado com sucesso.');
            } catch (\Throwable $e) {
                error_log('[TIPO DOC] Erro ao atualizar: ' . $e->getMessage());
                Session::setFlash('flash', 'Erro ao atualizar o tipo de documento.');
            }
        }

        $this->redirect('/admin/tipos-documentos');
    }

    public function excluir(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
            return;
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        if ($id <= 0) {
            $this->redirect('/admin/tipos-documentos');
            return;
        }

        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('DELETE FROM documento_tipo WHERE id = :id');
                $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $this->logService->log('excluir', 'documento_tipo', $id, "Tipo de documento excluído: $id");
                Session::setFlash('flash', 'Tipo de documento excluído com sucesso.');
            } catch (\Throwable $e) {
                error_log('[TIPO DOC] Erro ao excluir: ' . $e->getMessage());
                Session::setFlash('flash', 'Erro ao excluir o tipo de documento.');
            }
        }

        $this->redirect('/admin/tipos-documentos');
    }
}
