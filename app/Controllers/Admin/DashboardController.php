<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\DashboardService;
use App\Services\LogService;
use App\Support\Session;

final class DashboardController extends Controller
{
    private DashboardService $dashboardService;
    private LogService $logService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar o painel.');
            $this->redirect('/admin/login');
        }

        $authUser = $this->authUser();
        $isAdmin = (string) ($authUser['role'] ?? $authUser['type'] ?? '') === 'admin';
        $userId = (int) ($authUser['id'] ?? 0);

        $this->render('pages/admin/dashboard/index', [
            'title' => 'Painel Admin',
            'currentRoute' => '/admin',
            'indicators' => $this->dashboardService->indicators($userId, $isAdmin),
            'taskIndicators' => $this->dashboardService->taskIndicators($userId, $isAdmin),
            'isAdmin' => $isAdmin,
        ], 'admin');
    }

    public function logs(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os logs.');
            $this->redirect('/admin/login');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perfil = trim((string) ($_GET['perfil'] ?? ''));
        $perfil = $perfil !== '' && in_array($perfil, ['sistema', 'aluno'], true) ? $perfil : null;
        $nome = trim((string) ($_GET['nome'] ?? ''));
        $nome = $nome !== '' ? $nome : null;

        $result = $this->logService->logs($page, 50, $perfil, $nome);

        $this->render('pages/admin/logs/index', [
            'title' => 'Logs de Auditoria',
            'currentRoute' => '/admin/logs',
            'logs' => $result['data'],
            'pagination' => $result['pagination'],
            'perfil' => $perfil,
            'nome' => $nome,
        ], 'admin');
    }

    public function dbase(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $dbName = getenv('DB_NAME') ?: '';
        $tables = [];
        $rows = [];
        $columns = [];
        $currentTable = '';
        $totalRows = 0;
        $error = '';
        $viewMode = 'structure';
        $record = null;

        $pdo = \App\Core\Database::connection();

        if (!$pdo instanceof \PDO) {
            $error = 'Nao foi possivel conectar ao banco de dados.';
        } else {
            $table = trim((string) ($_GET['table'] ?? ''));

            if ($table !== '') {
                $currentTable = $table;

                $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . $table . '`');
                $stmt->execute();
                $columns = $stmt->fetchAll();

                $stmt = $pdo->prepare('SELECT COUNT(*) FROM `' . $table . '`');
                $stmt->execute();
                $totalRows = (int) $stmt->fetchColumn();

                $viewMode = ($_GET['view'] ?? '') === 'records' ? 'records' : 'structure';
                $recordId = (int) ($_GET['id'] ?? 0);

                if ($recordId > 0) {
                    $viewMode = 'detail';
                    $firstCol = $columns[0]['Field'] ?? 'id';
                    $stmt = $pdo->prepare('SELECT * FROM `' . $table . '` WHERE `' . $firstCol . '` = :id LIMIT 1');
                    $stmt->bindValue(':id', $recordId, \PDO::PARAM_INT);
                    $stmt->execute();
                    $record = $stmt->fetch();
                    if (!$record) {
                        $error = 'Registro #' . $recordId . ' nao encontrado na tabela ' . $table;
                    }
                } elseif ($viewMode === 'records') {
                    $limit = 200;
                    $stmt = $pdo->prepare('SELECT * FROM `' . $table . '` LIMIT :limit');
                    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
                    $stmt->execute();
                    $rows = $stmt->fetchAll();
                }
            } else {
                $stmt = $pdo->prepare(
                    "SELECT t.table_name AS name,
                            t.table_rows AS row_count
                     FROM information_schema.tables t
                     WHERE t.table_schema = :schema
                     ORDER BY t.table_name ASC"
                );
                $stmt->bindValue(':schema', $dbName);
                $stmt->execute();
                $tables = $stmt->fetchAll();
            }
        }

        $this->render('pages/admin/dbase/index', [
            'title' => 'Explorador de Banco de Dados',
            'currentRoute' => '/admin/dbase',
            'dbName' => $dbName,
            'tables' => $tables,
            'rows' => $rows,
            'columns' => $columns,
            'currentTable' => $currentTable,
            'totalRows' => $totalRows,
            'viewMode' => $viewMode,
            'error' => $error,
            'record' => $record,
        ], 'admin');
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
