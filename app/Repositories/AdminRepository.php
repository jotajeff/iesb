<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class AdminRepository
{
    public function dashboardIndicators(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [
                'total_alunos' => 0,
                'total_cursos' => 0,
                'total_matriculas' => 0,
            ];
        }

        return [
            'total_alunos' => $this->count($pdo, 'SELECT COUNT(*) FROM alunos'),
            'total_cursos' => $this->count($pdo, 'SELECT COUNT(*) FROM cursos_iesb'),
            'total_matriculas' => $this->count($pdo, 'SELECT COUNT(*) FROM matriculas'),
        ];
    }

    public function dashboardTaskIndicators(int $userId, bool $isAdmin): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [
                'tarefas_criadas' => 0,
                'tarefas_execucao' => 0,
                'tarefas_finalizadas' => 0,
            ];
        }

        try {
            $sql = 'SELECT
                        COALESCE(SUM(CASE WHEN t.situacao = "criada" THEN 1 ELSE 0 END), 0) AS tarefas_criadas,
                        COALESCE(SUM(CASE WHEN t.situacao IN ("execucao", "revisao") THEN 1 ELSE 0 END), 0) AS tarefas_execucao,
                        COALESCE(SUM(CASE WHEN t.situacao = "finalizada" THEN 1 ELSE 0 END), 0) AS tarefas_finalizadas
                    FROM tarefas t';

            $params = [];
            if (!$isAdmin && $userId > 0) {
                $sql .= ' WHERE t.responsavel = :responsavel';
                $params[':responsavel'] = $userId;
            }

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();

            $row = $stmt->fetch();
            if (!is_array($row)) {
                return [
                    'tarefas_criadas' => 0,
                    'tarefas_execucao' => 0,
                    'tarefas_finalizadas' => 0,
                ];
            }

            return [
                'tarefas_criadas' => (int) ($row['tarefas_criadas'] ?? 0),
                'tarefas_execucao' => (int) ($row['tarefas_execucao'] ?? 0),
                'tarefas_finalizadas' => (int) ($row['tarefas_finalizadas'] ?? 0),
            ];
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro em dashboardTaskIndicators: ' . $e->getMessage());
            return [
                'tarefas_criadas' => 0,
                'tarefas_execucao' => 0,
                'tarefas_finalizadas' => 0,
            ];
        }
    }

    public function listCursos(int $limit = 200, string $order = 'desc', ?int $nivelId = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $direction = strtoupper($order) === 'asc' ? 'ASC' : 'DESC';
        $nivelFilter = ($nivelId !== null && $nivelId > 0) ? (int) $nivelId : null;

        try {
        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso,
                        c.ativo, c.exibir_home, c.imagem_card, c.confirmado, c.modalidade AS modalidade_id, c.segmento AS segmento_id, c.nivel AS nivel_id, c.created_at,
                        m.nome AS modalidade_nome, s.nome AS segmento_nome, n.nome AS nivel_nome
                 FROM cursos_iesb c
                 LEFT JOIN modalidade m ON m.id = c.modalidade
                 LEFT JOIN segmento s ON s.id = c.segmento
                 LEFT JOIN nivel n ON n.id = c.nivel';

            if ($nivelFilter !== null) {
                $sql .= ' WHERE c.nivel = :nivel_id';
            }

            $sql .= ' ORDER BY c.id ' . $direction . ' LIMIT :limit';

            $stmt = $pdo->prepare($sql);
            if ($nivelFilter !== null) {
                $stmt->bindValue(':nivel_id', $nivelFilter, PDO::PARAM_INT);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em listCursos: ' . $e->getMessage());
            return [];
        }
    }

    public function listCursosDisponiveis(int $limit = 200, string $referenceDate = ''): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $referenceDate = $referenceDate !== '' ? $referenceDate : (new \DateTime())->format('Y-m-d');

        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.imagem_card, c.exibir_home, c.confirmado
                FROM cursos_iesb c
                WHERE c.ativo = "S" AND c.exibir_home = "S" AND c.curso_calendario IS NOT NULL AND c.curso_calendario >= :maxDate
                ORDER BY c.curso_calendario ASC, c.id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':maxDate', $referenceDate);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listCursosDisponiveisHome(int $limit = 6, string $referenceDate = ''): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $referenceDate = $referenceDate !== '' ? $referenceDate : (new \DateTime())->format('Y-m-d');

        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.imagem_card, c.exibir_home, c.confirmado, c.segmento, s.nome AS segmento_nome
                FROM cursos_iesb c
                LEFT JOIN segmento s ON s.id = c.segmento
                WHERE c.ativo = "S" AND c.exibir_home = "S" AND c.curso_calendario IS NOT NULL AND c.curso_calendario >= :maxDate
                ORDER BY c.curso_calendario ASC, c.id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':maxDate', $referenceDate);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listCursosByNivel(int $nivelId, int $limit = 200): array
    {
        return $this->listCursosByNivelAndSegmento($nivelId, null, $limit);
    }

    public function listCursosByNivelAndSegmento(int $nivelId, ?int $segmentoId = null, int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        if ($nivelId <= 0) {
            return [];
        }

        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.imagem_card, c.confirmado,
                       c.segmento AS segmento_id, s.nome AS segmento_nome
                FROM cursos_iesb c
                LEFT JOIN segmento s ON s.id = c.segmento
                WHERE c.ativo = "S" AND c.nivel = :nivel_id';

        if ($segmentoId !== null && $segmentoId > 0) {
            $sql .= ' AND c.segmento = :segmento_id';
        }

        $sql .= ' ORDER BY c.curso_calendario ASC, c.id DESC LIMIT :limit';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nivel_id', $nivelId, PDO::PARAM_INT);
        if ($segmentoId !== null && $segmentoId > 0) {
            $stmt->bindValue(':segmento_id', $segmentoId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listSegmentosByNivel(int $nivelId): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        if ($nivelId <= 0) {
            return [];
        }

        $sql = 'SELECT DISTINCT s.id, s.nome, s.ativo
                FROM cursos_iesb c
                INNER JOIN segmento s ON s.id = c.segmento
                WHERE c.ativo = "S" AND s.ativo = "S" AND c.nivel = :nivel_id
                ORDER BY s.nome ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nivel_id', $nivelId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listCursosAtivos(int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.imagem_card, c.confirmado, c.modalidade AS modalidade_id, c.segmento AS segmento_id, c.nivel AS nivel_id, m.nome AS modalidade_nome, s.nome AS segmento_nome, n.nome AS nivel_nome
                     FROM cursos_iesb c
                     LEFT JOIN modalidade m ON m.id = c.modalidade
                     LEFT JOIN segmento s ON s.id = c.segmento
                     LEFT JOIN nivel n ON n.id = c.nivel
                     WHERE c.ativo = "S"
                     ORDER BY c.id DESC
                     LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em listCursosAtivos: ' . $e->getMessage());
            return [];
        }
    }

    public function listTurmasByCurso(int $idCurso): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, nome, data_inicio, ativa FROM turmas WHERE id_curso = :id_curso AND ativa = "S" ORDER BY data_inicio DESC LIMIT 10';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em listTurmasByCurso: ' . $e->getMessage());
            return [];
        }
    }

    public function listarIdsCursosComDetalhe(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id_curso FROM detalhes WHERE ativo = "S"';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return is_array($rows) ? array_map('intval', $rows) : [];
        } catch (\Throwable $e) {
            error_log('[DETALHES] Erro em listarIdsCursosComDetalhe: ' . $e->getMessage());
            return [];
        }
    }

    public function findDetalheByCursoId(int $idCurso): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, id_curso, detalhe, ativo, criado_em FROM detalhes WHERE id_curso = :id_curso LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[DETALHES] Erro em findDetalheByCursoId: ' . $e->getMessage());
            return null;
        }
    }

    public function saveDetalhe(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO detalhes (id_curso, detalhe, ativo) VALUES (:id_curso, :detalhe, :ativo)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_curso', $payload['id_curso'], PDO::PARAM_INT);
            $stmt->bindValue(':detalhe', $payload['detalhe'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':ativo', $payload['ativo'] ?? 'S', PDO::PARAM_STR);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[DETALHES] Erro em saveDetalhe: ' . $e->getMessage());
            return 0;
        }
    }

    public function updateDetalhe(int $id, array $payload): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $sql = 'UPDATE detalhes SET detalhe = :detalhe, ativo = :ativo WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':detalhe', $payload['detalhe'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':ativo', $payload['ativo'] ?? 'S', PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[DETALHES] Erro em updateDetalhe: ' . $e->getMessage());
        }
    }

    public function findCursoById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.imagem_card, c.link_ingresso, c.ativo, c.exibir_home, c.confirmado, c.modalidade AS modalidade_id, c.segmento AS segmento_id, c.nivel AS nivel_id, c.created_at, s.nome AS segmento_nome
                     FROM cursos_iesb c
                     LEFT JOIN segmento s ON s.id = c.segmento
                     WHERE c.id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em findCursoById: ' . $e->getMessage());
            return null;
        }
    }

    public function updateCurso(int $id, array $payload): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE cursos_iesb
                SET nome = :nome, slug = :slug, data_curso = :data_curso, curso_calendario = :curso_calendario, horario = :horario, local_curso = :local_curso,
                    imagem_card = :imagem_card, link_ingresso = :link_ingresso, ativo = :ativo, exibir_home = :exibir_home, confirmado = :confirmado,
                    modalidade = :modalidade_id, segmento = :segmento_id, nivel = :nivel_id
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $payload['nome']);
        $stmt->bindValue(':slug', $payload['slug']);
        $stmt->bindValue(':data_curso', $payload['data_curso']);
        $stmt->bindValue(':curso_calendario', $payload['curso_calendario']);
        $stmt->bindValue(':horario', $payload['horario']);
        $stmt->bindValue(':local_curso', $payload['local_curso']);
        $stmt->bindValue(':imagem_card', $payload['imagem_card']);
        $stmt->bindValue(':link_ingresso', $payload['link_ingresso']);
        $stmt->bindValue(':ativo', (string) $payload['ativo']);
        $stmt->bindValue(':exibir_home', (string) ($payload['exibir_home'] ?? 'N'));
        $stmt->bindValue(':confirmado', (string) ($payload['confirmado'] ?? 'N'));
        $stmt->bindValue(':modalidade_id', $payload['modalidade_id'] ?? null, $payload['modalidade_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':segmento_id', $payload['segmento_id'] ?? null, $payload['segmento_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':nivel_id', $payload['nivel_id'] ?? null, $payload['nivel_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->execute();
    }

    public function listTurmas(int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT t.id, t.nome, c.nome AS curso_nome, n.nome AS nivel_nome, t.data_inicio, t.ativa,'
                 . ' (SELECT COUNT(*) FROM matriculas WHERE id_turma = t.id) AS total_inscritos'
                 . ' FROM turmas t'
                 . ' LEFT JOIN cursos_iesb c ON t.id_curso = c.id'
                 . ' LEFT JOIN nivel n ON c.nivel = n.id'
                 . ' ORDER BY t.id DESC'
                 . ' LIMIT :limit';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em listTurmas: ' . $e->getMessage());
            return [];
        }
    }    

    public function saveTurma(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            if (!empty($payload['id'])) {
                $sql = 'UPDATE turmas SET nome = :nome, id_curso = :id_curso, data_inicio = :data_inicio, ativa = :ativa WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $payload['id'], PDO::PARAM_INT);
            } else {
                $sql = 'INSERT INTO turmas (nome, id_curso, data_inicio, ativa) VALUES (:nome, :id_curso, :data_inicio, :ativa)';
                $stmt = $pdo->prepare($sql);
            }

            $stmt->bindValue(':nome', trim($payload['nome'] ?? ''), PDO::PARAM_STR);
            $stmt->bindValue(':id_curso', $payload['id_curso'], PDO::PARAM_INT);
            $stmt->bindValue(':data_inicio', $payload['data_inicio'], PDO::PARAM_STR);
            $stmt->bindValue(':ativa', strtoupper(trim($payload['ativa'] ?? 'N')), PDO::PARAM_STR);
            $stmt->execute();

            if (empty($payload['id'])) {
                return (int) $pdo->lastInsertId();
            }
            return $payload['id'];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em saveTurma: ' . $e->getMessage());
            return 0;
        }
    }

    public function findTurmaById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT t.id, t.nome, t.id_curso, t.data_inicio, t.ativa, '
                 . 'c.nome AS curso_nome, n.nome AS nivel_nome'
                 . ' FROM turmas t'
                 . ' LEFT JOIN cursos_iesb c ON t.id_curso = c.id'
                 . ' LEFT JOIN nivel n ON c.nivel = n.id'
                 . ' WHERE t.id = :id'
                 . ' LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em findTurmaById: ' . $e->getMessage());
            return null;
        }
    }

    public function listAlunos(int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, nome, cpf, data_nascimento, telefone, email, ativo,'
                 . ' (SELECT COUNT(*) FROM matriculas WHERE id_aluno = alunos.id) AS total_matriculas'
                 . ' FROM alunos'
                 . ' ORDER BY nome ASC'
                 . ' LIMIT :limit';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em listAlunos: ' . $e->getMessage());
            return [];
        }
    }

    public function findAlunoById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, nome, cpf, data_nascimento, telefone, email, ativo, criado_em, atualizado_em'
                 . ' FROM alunos'
                 . ' WHERE id = :id'
                 . ' LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em findAlunoById: ' . $e->getMessage());
            return null;
        }
    }

    public function findAlunoByEmail(string $email): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, nome, email, senha, ativo'
                 . ' FROM alunos'
                 . ' WHERE email = :email'
                 . ' LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em findAlunoByEmail: ' . $e->getMessage());
            return null;
        }
    }

    public function saveAluno(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            if (!empty($payload['id'])) {
                $sql = 'UPDATE alunos SET nome = :nome, cpf = :cpf, data_nascimento = :data_nascimento, telefone = :telefone, email = :email, ativo = :ativo';
                $sql .= isset($payload['senha']) ? ', senha = :senha' : '';
                $sql .= ' WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $payload['id'], PDO::PARAM_INT);
            } else {
                $sql = 'INSERT INTO alunos (nome, cpf, data_nascimento, telefone, email, ativo, senha) VALUES (:nome, :cpf, :data_nascimento, :telefone, :email, :ativo, :senha)';
                $stmt = $pdo->prepare($sql);
            }

            $stmt->bindValue(':nome', trim($payload['nome'] ?? ''), PDO::PARAM_STR);
            $stmt->bindValue(':cpf', trim($payload['cpf'] ?? ''), PDO::PARAM_STR);
            $stmt->bindValue(':data_nascimento', $payload['data_nascimento'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':telefone', trim($payload['telefone'] ?? ''), PDO::PARAM_STR);
            $stmt->bindValue(':email', trim($payload['email'] ?? ''), PDO::PARAM_STR);
            $stmt->bindValue(':ativo', strtoupper(trim($payload['ativo'] ?? 'N')), PDO::PARAM_STR);
            if (isset($payload['senha'])) {
                $stmt->bindValue(':senha', $payload['senha'], PDO::PARAM_STR);
            }
            $stmt->execute();

            if (empty($payload['id'])) {
                return (int) $pdo->lastInsertId();
            }
            return $payload['id'];
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em saveAluno: ' . $e->getMessage());
            return 0;
        }
    }

    public function listMatriculasByAluno(int $idAluno): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT m.id, m.id_aluno, m.id_turma, m.data_matricula, m.status, t.nome AS turma_nome'
                 . ' FROM matriculas m'
                 . ' LEFT JOIN turmas t ON m.id_turma = t.id'
                 . ' WHERE m.id_aluno = :id_aluno'
                 . ' ORDER BY m.data_matricula DESC';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[MATRICULAS] Erro em listMatriculasByAluno: ' . $e->getMessage());
            return [];
        }
    }

    public function listCursosByAluno(int $idAluno): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT m.id AS matricula_id, m.status, m.data_matricula,'
                 . ' t.id AS turma_id, t.nome AS turma_nome,'
                 . ' c.id AS curso_id, c.nome AS curso_nome'
                 . ' FROM matriculas m'
                 . ' INNER JOIN turmas t ON m.id_turma = t.id'
                 . ' INNER JOIN cursos_iesb c ON t.id_curso = c.id'
                 . ' WHERE m.id_aluno = :id_aluno'
                 . ' ORDER BY c.nome ASC';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em listCursosByAluno: ' . $e->getMessage());
            return [];
        }
    }

    public function saveMatricula(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO matriculas (id_aluno, id_turma, status) VALUES (:id_aluno, :id_turma, :status)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $payload['id_aluno'], PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', $payload['id_turma'], PDO::PARAM_INT);
            $stmt->bindValue(':status', $payload['status'] ?? 'matriculado', PDO::PARAM_STR);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[MATRICULAS] Erro em saveMatricula: ' . $e->getMessage());
            return 0;
        }
    }

    public function findMatriculaByAlunoAndTurma(int $idAluno, int $idTurma): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id FROM matriculas'
                 . ' WHERE id_aluno = :id_aluno AND id_turma = :id_turma'
                 . ' LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[MATRICULAS] Erro em findMatriculaByAlunoAndTurma: ' . $e->getMessage());
            return null;
        }
    }

    public function listInscritosPorTurma(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT a.id, a.nome, a.cpf, a.email, a.telefone, m.status, m.data_matricula'
                 . ' FROM matriculas m'
                 . ' INNER JOIN alunos a ON m.id_aluno = a.id'
                 . ' WHERE m.id_turma = :id_turma'
                 . ' ORDER BY a.nome ASC';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TURMAS] Erro em listInscritosPorTurma: ' . $e->getMessage());
            return [];
        }
    }

    public function updateCursoImagem(int $id, string $imagemPath): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE cursos_iesb SET imagem_card = :imagem_card WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':imagem_card', $imagemPath);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function listModalidades(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT id, nome, ativo FROM modalidade ORDER BY nome ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro ao carregar modalidades: ' . $e->getMessage());
            return [];
        }
    }

    public function findModalidadeById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, ativo FROM modalidade WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function saveModalidade(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $id = (int) ($payload['id'] ?? 0);
        $nome = trim((string) ($payload['nome'] ?? ''));
        $ativo = (int) ($payload['ativo'] ?? 1);

        if ($id > 0) {
            $sql = 'UPDATE modalidade SET nome = :nome, ativo = :ativo WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();
            return $id;
        }

        $sql = 'INSERT INTO modalidade (nome, ativo) VALUES (:nome, :ativo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function listSegmentos(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT id, nome, ativo FROM segmento ORDER BY nome ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro ao carregar segmentos: ' . $e->getMessage());
            return [];
        }
    }

    public function findSegmentoById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, ativo FROM segmento WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function saveSegmento(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($payload['id'] ?? 0);
            $nome = trim((string) ($payload['nome'] ?? ''));
            $ativo = strtoupper(trim((string) ($payload['ativo'] ?? 'S'))) === 'N' ? 'N' : 'S';

            if ($id > 0) {
                $sql = 'UPDATE segmento SET nome = :nome, ativo = :ativo WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':nome', $nome);
                $stmt->bindValue(':ativo', $ativo);
                $stmt->execute();
                return $id;
            }

            $sql = 'INSERT INTO segmento (nome, ativo) VALUES (:nome, :ativo)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':ativo', $ativo);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro ao salvar segmento: ' . $e->getMessage());
            return 0;
        }
    }

    public function listNiveis(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT id, slug, nome, ativo, apresentacao FROM nivel ORDER BY nome ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro ao carregar niveis: ' . $e->getMessage());
            return [];
        }
    }

    public function findNivelById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, slug, nome, ativo, apresentacao FROM nivel WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findNivelBySlug(string $slug): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $sql = 'SELECT id, slug, nome, ativo, apresentacao FROM nivel WHERE slug = :slug LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function saveNivel(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $id = (int) ($payload['id'] ?? 0);
        $nome = trim((string) ($payload['nome'] ?? ''));
        $ativo = (int) ($payload['ativo'] ?? 1);
        $apresentacao = (string) ($payload['apresentacao'] ?? '');

        if ($id > 0) {
            $sql = 'UPDATE nivel SET nome = :nome, ativo = :ativo, apresentacao = :apresentacao WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->bindValue(':apresentacao', $apresentacao);
            $stmt->execute();
            return $id;
        }

        $sql = 'INSERT INTO nivel (nome, ativo, apresentacao) VALUES (:nome, :ativo, :apresentacao)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindValue(':apresentacao', $apresentacao);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function createCurso(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $sql = 'INSERT INTO cursos_iesb (nome, slug, data_curso, curso_calendario, horario, local_curso, imagem_card, link_ingresso, ativo, exibir_home, confirmado, modalidade, segmento, nivel)
                VALUES (:nome, :slug, :data_curso, :curso_calendario, :horario, :local_curso, :imagem_card, :link_ingresso, :ativo, :exibir_home, :confirmado, :modalidade_id, :segmento_id, :nivel_id)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $payload['nome']);
        $stmt->bindValue(':slug', $payload['slug']);
        $stmt->bindValue(':data_curso', $payload['data_curso']);
        $stmt->bindValue(':curso_calendario', $payload['curso_calendario']);
        $stmt->bindValue(':horario', $payload['horario']);
        $stmt->bindValue(':local_curso', $payload['local_curso']);
        $stmt->bindValue(':imagem_card', $payload['imagem_card']);
        $stmt->bindValue(':link_ingresso', $payload['link_ingresso']);
        $stmt->bindValue(':ativo', (string) $payload['ativo']);
        $stmt->bindValue(':exibir_home', (string) ($payload['exibir_home'] ?? 'N'));
        $stmt->bindValue(':confirmado', (string) ($payload['confirmado'] ?? 'N'));
        $stmt->bindValue(':modalidade_id', $payload['modalidade_id'] ?? null, $payload['modalidade_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':segmento_id', $payload['segmento_id'] ?? null, $payload['segmento_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':nivel_id', $payload['nivel_id'] ?? null, $payload['nivel_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function listCursosSemSlug(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT id, nome, slug
                FROM cursos_iesb
                WHERE slug IS NULL OR slug = ""
                ORDER BY id ASC';
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function updateCursoSlug(int $id, string $slug): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE cursos_iesb SET slug = :slug WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
    }

    public function cursoSlugExists(string $slug, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        $sql = 'SELECT id FROM cursos_iesb WHERE slug = :slug';
        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
        }
        $sql .= ' LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($ignoreId !== null && $ignoreId > 0) {
            $stmt->bindValue(':ignore_id', $ignoreId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function recentLogs(int $limit = 50): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT l.id, l.usuario_id, l.acao, l.entidade, l.entidade_id, l.descricao, l.ip, l.sucesso, l.created_at,
                       u.nome AS usuario_nome
                FROM logs_auditoria l
                LEFT JOIN usuarios u ON u.id = l.usuario_id
                ORDER BY l.id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function recentVisits(int $limit = 100): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT v.id, v.ip, v.user_agent, v.endereco_pagina, v.data_visita, v.hora_visita, v.created_at,
                       p.nome AS pagina_nome, p.slug AS pagina_slug
                FROM visitas_paginas v
                INNER JOIN paginas p ON p.id = v.pagina_id
                ORDER BY v.id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function visitsByDayInMonth(int $month, int $year): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $monthStr = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $period = sprintf('%04d-%s', $year, $monthStr);

        $sql = 'SELECT data_visita, COUNT(*) AS total
                FROM visitas_paginas
                WHERE data_visita LIKE :period
                GROUP BY data_visita
                ORDER BY data_visita ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':period', $period . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function visitsInMonthWithPage(int $month, int $year): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $monthStr = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $period = sprintf('%04d-%s', $year, $monthStr);

        $sql = 'SELECT v.id, v.ip, v.user_agent, v.data_visita,
                       p.nome AS pagina_nome, p.slug AS pagina_slug
                FROM visitas_paginas v
                INNER JOIN paginas p ON p.id = v.pagina_id
                WHERE v.data_visita LIKE :period
                ORDER BY v.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':period', $period . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function pageVisitTotalsInMonth(int $month, int $year): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $monthStr = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $period = sprintf('%04d-%s', $year, $monthStr);

        $sql = 'SELECT p.nome AS pagina_nome, p.slug AS pagina_slug, COUNT(*) AS total
                FROM visitas_paginas v
                INNER JOIN paginas p ON p.id = v.pagina_id
                WHERE v.data_visita LIKE :period
                GROUP BY p.id, p.nome, p.slug
                ORDER BY total DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':period', $period . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listUsuarios(int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT id, nome, email, tipo, ativo, created_at, updated_at
                FROM usuarios
                ORDER BY id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findUsuarioById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, email, tipo, ativo, created_at, updated_at
                FROM usuarios
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateUsuario(int $id, array $payload): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sets = [];
        $params = [':id' => $id];

        if (isset($payload['nome'])) {
            $sets[] = 'nome = :nome';
            $params[':nome'] = $payload['nome'];
        }
        if (isset($payload['email'])) {
            $sets[] = 'email = :email';
            $params[':email'] = $payload['email'];
        }
        if (isset($payload['senha'])) {
            $sets[] = 'senha = :senha';
            $params[':senha'] = $payload['senha'];
        }
        if (isset($payload['tipo'])) {
            $sets[] = 'tipo = :tipo';
            $params[':tipo'] = $payload['tipo'];
        }
        if (isset($payload['ativo'])) {
            $sets[] = 'ativo = :ativo';
            $params[':ativo'] = (int) $payload['ativo'];
        }

        if (empty($sets)) {
            return;
        }

        $sql = 'UPDATE usuarios SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
    }

    public function createUsuario(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $sql = 'INSERT INTO usuarios (nome, email, senha, tipo, ativo)
                VALUES (:nome, :email, :senha, :tipo, :ativo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $payload['nome']);
        $stmt->bindValue(':email', $payload['email']);
        $stmt->bindValue(':senha', $payload['senha']);
        $stmt->bindValue(':tipo', $payload['tipo']);
        $stmt->bindValue(':ativo', (int) $payload['ativo'], PDO::PARAM_INT);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function listSetores(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, setor FROM setores ORDER BY setor ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();

            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao carregar setores: ' . $e->getMessage());
            return [];
        }
    }

    public function findSetorById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, setor FROM setores WHERE id = :id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao buscar setor: ' . $e->getMessage());
            return null;
        }
    }

    public function saveSetor(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($payload['id'] ?? 0);
            $setor = trim((string) ($payload['setor'] ?? ''));

            if ($id > 0) {
                $sql = 'UPDATE setores SET setor = :setor WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':setor', $setor);
                $stmt->execute();
                return $id;
            }

            $nextId = (int) ($pdo->query('SELECT COALESCE(MAX(id), 0) + 1 FROM setores')->fetchColumn() ?: 1);

            $sql = 'INSERT INTO setores (id, setor) VALUES (:id, :setor)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $nextId, PDO::PARAM_INT);
            $stmt->bindValue(':setor', $setor);
            $stmt->execute();

            return $nextId;
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao salvar setor: ' . $e->getMessage());
            return 0;
        }
    }

    public function listTarefas(int $limit = 300): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            return $this->fetchTarefas($pdo, $limit, true);
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao listar tarefas com comentários: ' . $e->getMessage());

            try {
                return $this->fetchTarefas($pdo, $limit, false);
            } catch (\Throwable $fallbackError) {
                error_log('[TAREFAS] Erro no fallback da listagem: ' . $fallbackError->getMessage());
                return [];
            }
        }
    }

    public function findTarefaById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            return $this->fetchTarefaById($pdo, $id, true);
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao buscar tarefa com comentários: ' . $e->getMessage());

            try {
                return $this->fetchTarefaById($pdo, $id, false);
            } catch (\Throwable $fallbackError) {
                error_log('[TAREFAS] Erro no fallback da busca: ' . $fallbackError->getMessage());
                return null;
            }
        }
    }

    public function createTarefa(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO tarefas (setor, tarefa, prioridade, criado_por, responsavel, situacao)
                    VALUES (:setor, :tarefa, :prioridade, :criado_por, :responsavel, :situacao)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':setor', (int) $payload['setor'], PDO::PARAM_INT);
            $stmt->bindValue(':tarefa', (string) $payload['tarefa']);
            $stmt->bindValue(':prioridade', (int) ($payload['prioridade'] ?? 1), PDO::PARAM_INT);
            $stmt->bindValue(':criado_por', (int) $payload['criado_por'], PDO::PARAM_INT);
            $responsavel = isset($payload['responsavel']) && (int) $payload['responsavel'] > 0 ? (int) $payload['responsavel'] : null;
            $stmt->bindValue(':responsavel', $responsavel, $responsavel !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':situacao', (string) $payload['situacao']);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao criar tarefa: ' . $e->getMessage());
            return 0;
        }
    }

    public function updateTarefa(int $id, array $payload): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $sql = 'UPDATE tarefas
                    SET setor = :setor,
                        tarefa = :tarefa,
                        prioridade = :prioridade,
                        responsavel = :responsavel,
                        situacao = :situacao
                    WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':setor', (int) $payload['setor'], PDO::PARAM_INT);
            $stmt->bindValue(':tarefa', (string) $payload['tarefa']);
            $stmt->bindValue(':prioridade', (int) ($payload['prioridade'] ?? 1), PDO::PARAM_INT);
            $responsavel = isset($payload['responsavel']) && (int) $payload['responsavel'] > 0 ? (int) $payload['responsavel'] : null;
            $stmt->bindValue(':responsavel', $responsavel, $responsavel !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':situacao', (string) $payload['situacao']);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[TAREFAS] Erro ao atualizar tarefa: ' . $e->getMessage());
        }
    }

    private function fetchTarefas(PDO $pdo, int $limit, bool $withComments): array
    {
        $sql = 'SELECT t.id,
                       t.setor AS setor_id,
                       s.setor AS setor_nome,
                       t.tarefa,
                       t.prioridade,
                       t.criado_em,
                       t.criado_por AS criado_por_id,
                       uc.nome AS criado_por_nome,
                       t.responsavel AS responsavel_id,
                       ur.nome AS responsavel_nome,
                       t.situacao';

        if ($withComments) {
            $sql .= ',
                       COALESCE(com.total_comentarios, 0) AS comentarios_total';
        } else {
            $sql .= ',
                       0 AS comentarios_total';
        }

        $sql .= ' FROM tarefas t
                  LEFT JOIN setores s ON s.id = t.setor
                  LEFT JOIN usuarios uc ON uc.id = t.criado_por
                  LEFT JOIN usuarios ur ON ur.id = t.responsavel';

        if ($withComments) {
            $sql .= '
                  LEFT JOIN (
                      SELECT id_fg, COUNT(*) AS total_comentarios
                      FROM comentarios
                      WHERE tabela_fg = "tarefas"
                      GROUP BY id_fg
                  ) com ON com.id_fg = t.id';
        }

        $sql .= ' ORDER BY t.id DESC
                  LIMIT :limit';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function fetchTarefaById(PDO $pdo, int $id, bool $withComments): ?array
    {
        $sql = 'SELECT t.id,
                       t.setor AS setor_id,
                       s.setor AS setor_nome,
                       t.tarefa,
                       t.prioridade,
                       t.criado_em,
                       t.criado_por AS criado_por_id,
                       uc.nome AS criado_por_nome,
                       t.responsavel AS responsavel_id,
                       ur.nome AS responsavel_nome,
                       t.situacao';

        if ($withComments) {
            $sql .= ',
                       COALESCE(com.total_comentarios, 0) AS comentarios_total';
        } else {
            $sql .= ',
                       0 AS comentarios_total';
        }

        $sql .= ' FROM tarefas t
                  LEFT JOIN setores s ON s.id = t.setor
                  LEFT JOIN usuarios uc ON uc.id = t.criado_por
                  LEFT JOIN usuarios ur ON ur.id = t.responsavel';

        if ($withComments) {
            $sql .= '
                  LEFT JOIN (
                      SELECT id_fg, COUNT(*) AS total_comentarios
                      FROM comentarios
                      WHERE tabela_fg = "tarefas"
                      GROUP BY id_fg
                  ) com ON com.id_fg = t.id';
        }

        $sql .= ' WHERE t.id = :id
                  LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function registrarLog(int $usuarioId, string $perfil, string $acao, string $entidade, int $entidadeId, string $descricao, bool $sucesso = true): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $sql = 'INSERT INTO logs_auditoria (usuario_id, perfil, acao, entidade, entidade_id, descricao, ip, sucesso)
                VALUES (:usuario_id, :perfil, :acao, :entidade, :entidade_id, :descricao, :ip, :sucesso)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':perfil', $perfil);
        $stmt->bindValue(':acao', $acao);
        $stmt->bindValue(':entidade', $entidade);
        $stmt->bindValue(':entidade_id', $entidadeId, PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':sucesso', $sucesso ? 1 : 0, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function count(PDO $pdo, string $sql): int
    {
        try {
            $result = $pdo->query($sql)->fetchColumn();
            return (int) $result;
        } catch (\Throwable) {
            return 0;
        }
    }
}
