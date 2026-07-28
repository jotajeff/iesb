<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AuthService;
use App\Services\LogService;
use App\Support\Session;

final class NotificacaoController extends Controller
{
    private AuthService $auth;
    private LogService $logService;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->logService = new LogService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login para acessar as notificações.');
            $this->redirect('/admin/login');
        }

        $authUser = Session::get('user');
        $userRole = (string) ($authUser['role'] ?? '');
        $userId = (int) ($authUser['id'] ?? 0);
        $podeCriar = $userRole === 'admin' || $userRole === 'operador';

        $notificacoes = [];
        $turmas = [];
        $professores = [];

        $pdo = Database::connection();
        if ($pdo instanceof \PDO) {
            try {
                $sql = 'SELECT n.*, u.nome AS origem_nome,'
                     . ' t.nome AS destino_turma_nome,'
                     . ' ud.nome AS destino_usuario_nome,'
                     . ' (SELECT COUNT(*) FROM notificacao_leitura nl2 WHERE nl2.id_notificacao = n.id)'
                     . ' + (SELECT COUNT(*) FROM notificacao_leitura_aluno nla2 WHERE nla2.id_notificacao = n.id) AS total_leitura,';

                if (!$podeCriar && $userId > 0) {
                    $sql .= ' nl.id IS NOT NULL AS lida';
                } else {
                    $sql .= ' 0 AS lida';
                }

                $sql .= ' FROM notificacao n'
                     . ' LEFT JOIN usuarios u ON n.id_usuario_origem = u.id'
                     . ' LEFT JOIN turmas t ON n.tipo_destino = \'turma\' AND n.id_destino = t.id'
                     . ' LEFT JOIN usuarios ud ON n.tipo_destino = \'usuario\' AND n.id_destino = ud.id';

                if (!$podeCriar && $userId > 0) {
                    $sql .= ' LEFT JOIN notificacao_leitura nl'
                          . ' ON nl.id_notificacao = n.id AND nl.id_usuario = :nl_user';
                }

                if ($userRole === 'professor' && $userId > 0) {
                    $sql .= ' WHERE n.tipo_destino = \'usuario\' AND n.id_destino = :user_id'
                          . ' OR (n.tipo_destino = \'turma\' AND n.id_destino IN ('
                          . '   SELECT tp.id_turma FROM turma_professor tp WHERE tp.id_usuario = :user_id2 AND tp.status = \'A\''
                          . ' ))';
                }

                $sql .= ' ORDER BY n.created_at DESC LIMIT 200';

                $stmt = $pdo->prepare($sql);
                if (!$podeCriar && $userId > 0) {
                    $stmt->bindValue(':nl_user', $userId, \PDO::PARAM_INT);
                }
                if ($userRole === 'professor' && $userId > 0) {
                    $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
                    $stmt->bindValue(':user_id2', $userId, \PDO::PARAM_INT);
                }
                $stmt->execute();
                $notificacoes = $stmt->fetchAll() ?: [];
            } catch (\Throwable) {
                $notificacoes = [];
            }

            if ($podeCriar) {
                try {
                    $stmt = $pdo->query("SELECT id, nome FROM turmas WHERE ativa = 'S' ORDER BY nome");
                    $turmas = $stmt->fetchAll() ?: [];
                } catch (\Throwable) {
                    $turmas = [];
                }

                try {
                    $stmt = $pdo->query("SELECT id, nome FROM usuarios WHERE tipo = 'professor' AND ativo = 1 ORDER BY nome");
                    $professores = $stmt->fetchAll() ?: [];
                } catch (\Throwable) {
                    $professores = [];
                }
            }
        }

        $this->render('pages/admin/notificacao/index', [
            'title' => 'Notificações',
            'currentRoute' => '/admin/notificacoes',
            'notificacoes' => $notificacoes,
            'podeCriar' => $podeCriar,
            'podeLer' => !$podeCriar && $userId > 0,
            'turmas' => $turmas,
            'professores' => $professores,
            'userRole' => $userRole,
            'userId' => $userId,
        ], 'admin');
    }

    public function salvar(): void
    {
        $userRole = (string) (Session::get('user')['role'] ?? '');
        if ($userRole !== 'admin' && $userRole !== 'operador') {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/notificacoes');
        }

        $titulo = trim((string) $this->input('titulo', ''));
        $mensagem = trim((string) $this->input('mensagem', ''));
        $destino = (string) $this->input('destino', '');

        if ($titulo === '' || $mensagem === '' || $destino === '') {
            Session::setFlash('flash', 'Preencha título, mensagem e destino.');
            $this->redirect('/admin/notificacoes');
        }

        $parts = explode(':', $destino, 2);
        if (count($parts) !== 2) {
            Session::setFlash('flash', 'Destino inválido.');
            $this->redirect('/admin/notificacoes');
        }

        $tipoDestino = $parts[0];
        $idDestino = (int) $parts[1];

        if ($idDestino <= 0 || !in_array($tipoDestino, ['turma', 'professor'], true)) {
            Session::setFlash('flash', 'Destino inválido.');
            $this->redirect('/admin/notificacoes');
        }

        $authUser = Session::get('user');
        $origemId = (int) ($authUser['id'] ?? 0);

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            Session::setFlash('flash', 'Erro de conexão com o banco.');
            $this->redirect('/admin/notificacoes');
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO notificacao (id_usuario_origem, tipo_destino, id_destino, titulo, mensagem, `ativo`)'
                . ' VALUES (:origem, :tipo, :destino_id, :titulo, :mensagem, 1)'
            );
            $stmt->bindValue(':origem', $origemId, \PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipoDestino === 'turma' ? 'turma' : 'usuario', \PDO::PARAM_STR);
            $stmt->bindValue(':destino_id', $idDestino, \PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $titulo, \PDO::PARAM_STR);
            $stmt->bindValue(':mensagem', $mensagem, \PDO::PARAM_STR);
            $stmt->execute();
            $notificacaoId = (int) $pdo->lastInsertId();

            $this->logService->log('criar', 'notificacao', $notificacaoId, "Notificação criada: $titulo");

            Session::setFlash('flash', 'Notificação enviada com sucesso.');
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO] Erro: ' . $e->getMessage());
            Session::setFlash('flash', 'Erro ao enviar notificação.');
        }

        $this->redirect('/admin/notificacoes');
    }

    public function marcarLida(): void
    {
        $authUser = Session::get('user');
        $userRole = (string) ($authUser['role'] ?? '');
        $userId = (int) ($authUser['id'] ?? 0);

        if ($userRole !== 'professor' || $userId <= 0) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }

        $notificacaoId = (int) $this->input('id', 0);
        if ($notificacaoId <= 0) {
            $this->json(['erro' => 'ID inválido.'], 400);
        }

        $pdo = Database::connection();
        if (!$pdo instanceof \PDO) {
            $this->json(['erro' => 'Erro de conexão.'], 500);
        }

        try {
            $stmt = $pdo->prepare(
                'INSERT IGNORE INTO notificacao_leitura (id_notificacao, id_usuario) VALUES (:id_notificacao, :id_usuario)'
            );
            $stmt->bindValue(':id_notificacao', $notificacaoId, \PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $userId, \PDO::PARAM_INT);
            $stmt->execute();

            $this->logService->log('ler', 'notificacao', $notificacaoId, 'Notificação marcada como lida');

            $this->json(['sucesso' => true]);
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO] Erro ao marcar lida: ' . $e->getMessage());
            $this->json(['erro' => 'Erro ao marcar como lida.'], 500);
        }
    }

    public function leitura(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login para acessar as leituras.');
            $this->redirect('/admin/login');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        if ($id <= 0) {
            Session::setFlash('flash', 'ID inválido.');
            $this->redirect('/admin/notificacoes');
        }

        $pdo = Database::connection();
        $notificacao = null;
        $leituras = [];

        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT n.*, u.nome AS origem_nome, t.nome AS destino_turma_nome, ud.nome AS destino_usuario_nome'
                    . ' FROM notificacao n'
                    . ' LEFT JOIN usuarios u ON n.id_usuario_origem = u.id'
                    . ' LEFT JOIN turmas t ON n.tipo_destino = \'turma\' AND n.id_destino = t.id'
                    . ' LEFT JOIN usuarios ud ON n.tipo_destino = \'usuario\' AND n.id_destino = ud.id'
                    . ' WHERE n.id = :id'
                );
                $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $notificacao = $stmt->fetch() ?: null;

                if ($notificacao) {
                    $tipo = (string) ($notificacao['tipo_destino'] ?? '');
                    if ($tipo === 'usuario') {
                        $stmt = $pdo->prepare(
                            'SELECT nl.lida_em, u.id, u.nome, u.email'
                            . ' FROM notificacao_leitura nl'
                            . ' JOIN usuarios u ON nl.id_usuario = u.id'
                            . ' WHERE nl.id_notificacao = :id'
                            . ' ORDER BY u.nome ASC'
                        );
                        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                        $stmt->execute();
                        $leituras = $stmt->fetchAll() ?: [];
                    } elseif ($tipo === 'turma') {
                        $stmt = $pdo->prepare(
                            'SELECT nl.lida_em, a.id, a.nome, a.email'
                            . ' FROM notificacao_leitura_aluno nl'
                            . ' JOIN alunos a ON nl.id_aluno = a.id'
                            . ' WHERE nl.id_notificacao = :id'
                            . ' ORDER BY a.nome ASC'
                        );
                        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                        $stmt->execute();
                        $leituras = $stmt->fetchAll() ?: [];
                    }
                }
            } catch (\Throwable $e) {
                error_log('[NOTIFICACAO LEITURA] Erro: ' . $e->getMessage());
            }
        }

        if (!$notificacao) {
            Session::setFlash('flash', 'Notificação não encontrada.');
            $this->redirect('/admin/notificacoes');
        }

        $this->render('pages/admin/notificacao/leitura', [
            'title' => 'Leituras — ' . ($notificacao['titulo'] ?? ''),
            'currentRoute' => '/admin/notificacoes',
            'notificacao' => $notificacao,
            'leituras' => $leituras,
        ], 'admin');
    }

    public function clone(): void
    {
        $userRole = (string) (Session::get('user')['role'] ?? '');
        if ($userRole !== 'admin' && $userRole !== 'operador') {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/notificacoes');
        }

        $id = (int) ($this->input('id', 0) ?: ($_GET['id'] ?? 0));
        if ($id <= 0) {
            Session::setFlash('flash', 'Notificação inválida.');
            $this->redirect('/admin/notificacoes');
        }

        $pdo = Database::connection();
        $notificacao = null;
        $turmas = [];
        $professores = [];

        if ($pdo instanceof \PDO) {
            try {
                $stmt = $pdo->prepare('SELECT * FROM notificacao WHERE id = :id');
                $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $notificacao = $stmt->fetch() ?: null;
            } catch (\Throwable) {
                $notificacao = null;
            }

            if ($notificacao) {
                $tipo = (string) ($notificacao['tipo_destino'] ?? '');
                $idDestino = (int) ($notificacao['id_destino'] ?? 0);

                try {
                    $stmt = $pdo->query("SELECT id, nome FROM turmas WHERE ativa = 'S' ORDER BY nome");
                    $all = $stmt->fetchAll() ?: [];
                    if ($tipo === 'turma') {
                        foreach ($all as $t) {
                            if ((int) $t['id'] !== $idDestino) {
                                $turmas[] = $t;
                            }
                        }
                    } else {
                        $turmas = $all;
                    }
                } catch (\Throwable) {
                    $turmas = [];
                }

                try {
                    $stmt = $pdo->query("SELECT id, nome FROM usuarios WHERE tipo = 'professor' AND ativo = 1 ORDER BY nome");
                    $all = $stmt->fetchAll() ?: [];
                    if ($tipo === 'usuario') {
                        foreach ($all as $p) {
                            if ((int) $p['id'] !== $idDestino) {
                                $professores[] = $p;
                            }
                        }
                    } else {
                        $professores = $all;
                    }
                } catch (\Throwable) {
                    $professores = [];
                }
            }
        }

        if (!$notificacao) {
            Session::setFlash('flash', 'Notificação não encontrada.');
            $this->redirect('/admin/notificacoes');
        }

        $this->render('pages/admin/notificacao/clone', [
            'title' => 'Clonar Notificação',
            'currentRoute' => '/admin/notificacoes',
            'notificacao' => $notificacao,
            'turmas' => $turmas,
            'professores' => $professores,
        ], 'admin');
    }

    private function isStaff(): bool
    {
        return $this->auth->isStaff();
    }
}
