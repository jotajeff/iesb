<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class EstruturaCurricularRepository
{
    // ==================== MATRIZ (estrutura_curricular) ====================

    public function listarMatrizes(?int $idCurso = null, ?int $ativo = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT ec.*, c.nome AS curso_nome,
                           (SELECT COUNT(*) FROM estrutura_modulo em WHERE em.id_estrutura = ec.id) AS total_modulos
                    FROM estrutura_curricular ec
                    LEFT JOIN cursos c ON c.id = ec.id_curso
                    WHERE 1 = 1';
            $params = [];

            if ($idCurso !== null && $idCurso > 0) {
                $sql .= ' AND ec.id_curso = :id_curso';
                $params[':id_curso'] = $idCurso;
            }

            if ($ativo !== null) {
                $sql .= ' AND ec.ativo = :ativo';
                $params[':ativo'] = $ativo;
            }

            $sql .= ' ORDER BY ec.id DESC';

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar matrizes: ' . $e->getMessage());
            return [];
        }
    }

    public function findMatriz(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT ec.*, c.nome AS curso_nome
                                   FROM estrutura_curricular ec
                                   LEFT JOIN cursos c ON c.id = ec.id_curso
                                   WHERE ec.id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao buscar matriz: ' . $e->getMessage());
            return null;
        }
    }

    public function validarMatrizParaCurso(int $idEstrutura, int $idCurso): bool
    {
        if ($idEstrutura < 1 || $idCurso < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('SELECT id
                                   FROM estrutura_curricular
                                   WHERE id = :id_estrutura
                                     AND id_curso = :id_curso
                                     AND ativo = 1
                                   LIMIT 1');
            $stmt->bindValue(':id_estrutura', $idEstrutura, PDO::PARAM_INT);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao validar matriz para curso: ' . $e->getMessage());
            return false;
        }
    }

    public function salvarMatriz(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($data['id'] ?? 0);
            $idCurso = (int) ($data['id_curso'] ?? 0);
            $nome = trim((string) ($data['nome'] ?? ''));
            $descricao = isset($data['descricao']) ? trim((string) $data['descricao']) : '';
            $cargaHoraria = (int) ($data['carga_horaria'] ?? 0);
            $versao = trim((string) ($data['versao'] ?? '1.0')) !== '' ? trim((string) ($data['versao'] ?? '1.0')) : '1.0';
            $ativo = (int) ($data['ativo'] ?? 1);

            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE estrutura_curricular
                                       SET id_curso = :id_curso, nome = :nome, descricao = :descricao,
                                           carga_horaria = :carga_horaria, versao = :versao, ativo = :ativo
                                       WHERE id = :id');
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
                $stmt->bindValue(':nome', $nome);
                $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null, $descricao !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':carga_horaria', $cargaHoraria > 0 ? $cargaHoraria : null, $cargaHoraria > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(':versao', $versao);
                $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
                $stmt->execute();
                return $id;
            }

            $stmt = $pdo->prepare('INSERT INTO estrutura_curricular (id_curso, nome, descricao, carga_horaria, versao, ativo)
                                   VALUES (:id_curso, :nome, :descricao, :carga_horaria, :versao, :ativo)');
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null, $descricao !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':carga_horaria', $cargaHoraria > 0 ? $cargaHoraria : null, $cargaHoraria > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':versao', $versao);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao salvar matriz: ' . $e->getMessage());
            return 0;
        }
    }

    public function desativarMatriz(int $id): bool
    {
        if ($id < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE estrutura_curricular SET ativo = 0 WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao desativar matriz: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== MÓDULOS (estrutura_modulo) ====================

    public function listarModulos(int $idEstrutura): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT em.*,
                           (SELECT COUNT(*) FROM estrutura_disciplina ed WHERE ed.id_modulo = em.id AND ed.ativo = 1) AS total_disciplinas
                    FROM estrutura_modulo em
                    WHERE em.id_estrutura = :id_estrutura
                    ORDER BY em.ordem ASC, em.id ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_estrutura', $idEstrutura, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar módulos: ' . $e->getMessage());
            return [];
        }
    }

    public function listarModulosDaTurma(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idTurma < 1) {
            return [];
        }

        try {
            $stmt = $pdo->prepare('SELECT em.*, ec.nome AS estrutura_nome, ec.versao AS estrutura_versao,
                                          COUNT(DISTINCT ed.id) AS total_disciplinas
                                   FROM turmas t
                                   INNER JOIN estrutura_curricular ec ON ec.id = t.id_estrutura
                                   INNER JOIN estrutura_modulo em ON em.id_estrutura = ec.id
                                   LEFT JOIN estrutura_disciplina ed ON ed.id_modulo = em.id AND ed.ativo = 1
                                   WHERE t.id = :id_turma
                                   GROUP BY em.id, ec.id
                                   ORDER BY em.ordem ASC, em.id ASC');
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar módulos da turma: ' . $e->getMessage());
            return [];
        }
    }

    public function listarModulosComContexto(?int $idEstrutura = null, ?int $idTurma = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT em.id, em.nome, em.descricao, em.ordem, em.carga_horaria, em.ativo,
                           ec.id AS id_estrutura, ec.nome AS estrutura_nome, ec.versao AS estrutura_versao,
                           c.nome AS curso_nome, t.id AS id_turma, t.nome AS turma_nome,
                           COUNT(DISTINCT ed.id) AS total_disciplinas
                    FROM estrutura_modulo em
                    INNER JOIN estrutura_curricular ec ON ec.id = em.id_estrutura
                    INNER JOIN cursos c ON c.id = ec.id_curso
                    LEFT JOIN turmas t ON t.id_estrutura = ec.id AND t.ativo = 1
                    LEFT JOIN estrutura_disciplina ed ON ed.id_modulo = em.id AND ed.ativo = 1
                    WHERE 1 = 1';
            $params = [];

            if ($idEstrutura !== null && $idEstrutura > 0) {
                $sql .= ' AND ec.id = :id_estrutura';
                $params[':id_estrutura'] = $idEstrutura;
            }
            if ($idTurma !== null && $idTurma > 0) {
                $sql .= ' AND t.id = :id_turma';
                $params[':id_turma'] = $idTurma;
            }

            $sql .= ' GROUP BY em.id, ec.id, c.id, t.id ORDER BY c.nome ASC, ec.nome ASC, em.ordem ASC, em.id ASC';
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar módulos com contexto: ' . $e->getMessage());
            return [];
        }
    }

    public function listarDisciplinasDaMatriz(int $idEstrutura): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idEstrutura < 1) {
            return [];
        }

        try {
            $stmt = $pdo->prepare('SELECT DISTINCT d.id, d.nome, d.carga_horaria, d.ordem, d.ativo
                                   FROM estrutura_modulo em
                                   INNER JOIN estrutura_disciplina ed ON ed.id_modulo = em.id AND ed.ativo = 1
                                   INNER JOIN disciplina d ON d.id = ed.id_disciplina AND d.ativo = 1
                                   WHERE em.id_estrutura = :id_estrutura AND em.ativo = 1
                                   ORDER BY d.ordem ASC, d.nome ASC');
            $stmt->bindValue(':id_estrutura', $idEstrutura, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar disciplinas da matriz: ' . $e->getMessage());
            return [];
        }
    }

    public function findModulo(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM estrutura_modulo WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao buscar módulo: ' . $e->getMessage());
            return null;
        }
    }

    public function salvarModulo(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($data['id'] ?? 0);
            $idEstrutura = (int) ($data['id_estrutura'] ?? 0);
            $nome = trim((string) ($data['nome'] ?? ''));
            $descricao = isset($data['descricao']) ? trim((string) $data['descricao']) : '';
            $ordem = (int) ($data['ordem'] ?? 0);
            $cargaHoraria = (int) ($data['carga_horaria'] ?? 0);
            $ativo = (int) ($data['ativo'] ?? 1);

            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE estrutura_modulo
                                       SET nome = :nome, descricao = :descricao, ordem = :ordem,
                                           carga_horaria = :carga_horaria, ativo = :ativo
                                       WHERE id = :id AND id_estrutura = :id_estrutura');
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':id_estrutura', $idEstrutura, PDO::PARAM_INT);
                $stmt->bindValue(':nome', $nome);
                $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null, $descricao !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':ordem', $ordem, PDO::PARAM_INT);
                $stmt->bindValue(':carga_horaria', $cargaHoraria > 0 ? $cargaHoraria : null, $cargaHoraria > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
                $stmt->execute();
                return $id;
            }

            $stmt = $pdo->prepare('INSERT INTO estrutura_modulo (id_estrutura, nome, descricao, ordem, carga_horaria, ativo)
                                   VALUES (:id_estrutura, :nome, :descricao, :ordem, :carga_horaria, :ativo)');
            $stmt->bindValue(':id_estrutura', $idEstrutura, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null, $descricao !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':ordem', $ordem, PDO::PARAM_INT);
            $stmt->bindValue(':carga_horaria', $cargaHoraria > 0 ? $cargaHoraria : null, $cargaHoraria > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao salvar módulo: ' . $e->getMessage());
            return 0;
        }
    }

    public function desativarModulo(int $id): bool
    {
        if ($id < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE estrutura_modulo SET ativo = 0 WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao desativar módulo: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== DISCIPLINAS DA MATRIZ (estrutura_disciplina) ====================

    public function listarDisciplinasDoModulo(int $idModulo): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare('SELECT ed.*, d.nome AS disciplina_nome, d.carga_horaria AS disciplina_carga_horaria
                                   FROM estrutura_disciplina ed
                                   LEFT JOIN disciplina d ON d.id = ed.id_disciplina
                                   WHERE ed.id_modulo = :id_modulo
                                   ORDER BY ed.ordem ASC, ed.id ASC');
            $stmt->bindValue(':id_modulo', $idModulo, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar disciplinas do módulo: ' . $e->getMessage());
            return [];
        }
    }

    public function findDisciplinaDaMatriz(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT ed.*, d.nome AS disciplina_nome
                                   FROM estrutura_disciplina ed
                                   LEFT JOIN disciplina d ON d.id = ed.id_disciplina
                                   WHERE ed.id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao buscar disciplina da matriz: ' . $e->getMessage());
            return null;
        }
    }

    public function salvarDisciplinaDaMatriz(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($data['id'] ?? 0);
            $idModulo = (int) ($data['id_modulo'] ?? 0);
            $idDisciplina = (int) ($data['id_disciplina'] ?? 0);
            $ordem = (int) ($data['ordem'] ?? 0);
            $obrigatoria = (int) ($data['obrigatoria'] ?? 1);
            $ativo = (int) ($data['ativo'] ?? 1);

            // Duplicidade: mesma disciplina no mesmo módulo
            $check = $pdo->prepare('SELECT id FROM estrutura_disciplina WHERE id_modulo = :id_modulo AND id_disciplina = :id_disciplina' . ($id > 0 ? ' AND id != :id' : ''));
            $check->bindValue(':id_modulo', $idModulo, PDO::PARAM_INT);
            $check->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
            if ($id > 0) {
                $check->bindValue(':id', $id, PDO::PARAM_INT);
            }
            $check->execute();
            if ($check->fetch()) {
                return -1;
            }

            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE estrutura_disciplina SET id_disciplina = :id_disciplina, ordem = :ordem, obrigatoria = :obrigatoria, ativo = :ativo WHERE id = :id AND id_modulo = :id_modulo');
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':id_modulo', $idModulo, PDO::PARAM_INT);
                $stmt->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
                $stmt->bindValue(':ordem', $ordem, PDO::PARAM_INT);
                $stmt->bindValue(':obrigatoria', $obrigatoria, PDO::PARAM_INT);
                $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
                $stmt->execute();
                return $id;
            }

            $stmt = $pdo->prepare('INSERT INTO estrutura_disciplina (id_modulo, id_disciplina, ordem, obrigatoria, ativo)
                                   VALUES (:id_modulo, :id_disciplina, :ordem, :obrigatoria, :ativo)');
            $stmt->bindValue(':id_modulo', $idModulo, PDO::PARAM_INT);
            $stmt->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
            $stmt->bindValue(':ordem', $ordem, PDO::PARAM_INT);
            $stmt->bindValue(':obrigatoria', $obrigatoria, PDO::PARAM_INT);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao salvar disciplina da matriz: ' . $e->getMessage());
            return 0;
        }
    }

    public function desativarDisciplinaDaMatriz(int $id): bool
    {
        if ($id < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE estrutura_disciplina SET ativo = 0 WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao desativar disciplina da matriz: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== DISCIPLINAS DA TURMA (turma_disciplina) ====================

    public function listarDisciplinasDaTurma(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare('SELECT td.*, d.nome AS disciplina_nome, d.carga_horaria AS disciplina_carga_horaria,
                                          u.nome AS professor_nome
                                   FROM turma_disciplina td
                                   LEFT JOIN disciplina d ON d.id = td.id_disciplina
                                   LEFT JOIN usuarios u ON u.id = td.id_usuario_professor
                                   WHERE td.id_turma = :id_turma
                                   ORDER BY td.id ASC');
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar disciplinas da turma: ' . $e->getMessage());
            return [];
        }
    }

    public function findDisciplinaDaTurma(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT td.*, d.nome AS disciplina_nome
                                   FROM turma_disciplina td
                                   LEFT JOIN disciplina d ON d.id = td.id_disciplina
                                   WHERE td.id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao buscar disciplina da turma: ' . $e->getMessage());
            return null;
        }
    }

    public function salvarDisciplinaDaTurma(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($data['id'] ?? 0);
            $idTurma = (int) ($data['id_turma'] ?? 0);
            $idDisciplina = (int) ($data['id_disciplina'] ?? 0);
            $idProfessor = isset($data['id_usuario_professor']) && (int) $data['id_usuario_professor'] > 0 ? (int) $data['id_usuario_professor'] : null;
            $dataInicio = isset($data['data_inicio']) && $data['data_inicio'] !== '' ? (string) $data['data_inicio'] : null;
            $dataFim = isset($data['data_fim']) && $data['data_fim'] !== '' ? (string) $data['data_fim'] : null;
            $status = (string) ($data['status'] ?? 'PLANEJADA');
            $statusValidos = ['PLANEJADA', 'EM_ANDAMENTO', 'CONCLUIDA', 'CANCELADA'];
            if (!in_array($status, $statusValidos, true)) {
                $status = 'PLANEJADA';
            }
            $ativo = (int) ($data['ativo'] ?? 1);

            // Duplicidade: mesma disciplina na mesma turma
            $check = $pdo->prepare('SELECT id FROM turma_disciplina WHERE id_turma = :id_turma AND id_disciplina = :id_disciplina' . ($id > 0 ? ' AND id != :id' : ''));
            $check->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $check->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
            if ($id > 0) {
                $check->bindValue(':id', $id, PDO::PARAM_INT);
            }
            $check->execute();
            if ($check->fetch()) {
                return -1;
            }

            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE turma_disciplina
                                       SET id_disciplina = :id_disciplina, id_usuario_professor = :id_professor,
                                           data_inicio = :data_inicio, data_fim = :data_fim, status = :status, ativo = :ativo
                                       WHERE id = :id AND id_turma = :id_turma');
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
                $stmt->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
                $stmt->bindValue(':id_professor', $idProfessor, $idProfessor !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(':data_inicio', $dataInicio, $dataInicio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':data_fim', $dataFim, $dataFim !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':status', $status);
                $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
                $stmt->execute();
                return $id;
            }

            $stmt = $pdo->prepare('INSERT INTO turma_disciplina (id_turma, id_disciplina, id_usuario_professor, data_inicio, data_fim, status, ativo)
                                   VALUES (:id_turma, :id_disciplina, :id_professor, :data_inicio, :data_fim, :status, :ativo)');
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
            $stmt->bindValue(':id_professor', $idProfessor, $idProfessor !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':data_inicio', $dataInicio, $dataInicio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':data_fim', $dataFim, $dataFim !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao salvar disciplina da turma: ' . $e->getMessage());
            return 0;
        }
    }

    public function desativarDisciplinaDaTurma(int $id): bool
    {
        if ($id < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE turma_disciplina SET ativo = 0 WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao desativar disciplina da turma: ' . $e->getMessage());
            return false;
        }
    }

    public function vincularProfessorDaDisciplina(int $idTurma, int $idDisciplina, ?int $idProfessor): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idTurma < 1 || $idDisciplina < 1) {
            return 0;
        }

        try {
            $check = $pdo->prepare('SELECT id FROM turma_disciplina WHERE id_turma = :id_turma AND id_disciplina = :id_disciplina LIMIT 1');
            $check->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $check->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
            $check->execute();
            $idTurmaDisciplina = (int) $check->fetchColumn();

            if ($idTurmaDisciplina > 0) {
                $stmt = $pdo->prepare('UPDATE turma_disciplina SET id_usuario_professor = :id_professor WHERE id = :id');
                $stmt->bindValue(':id', $idTurmaDisciplina, PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare('INSERT INTO turma_disciplina (id_turma, id_disciplina, id_usuario_professor, status, ativo)
                                       VALUES (:id_turma, :id_disciplina, :id_professor, :status, :ativo)');
                $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
                $stmt->bindValue(':id_disciplina', $idDisciplina, PDO::PARAM_INT);
                $stmt->bindValue(':status', 'PLANEJADA');
                $stmt->bindValue(':ativo', 1, PDO::PARAM_INT);
            }
            $stmt->bindValue(':id_professor', $idProfessor, $idProfessor !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->execute();

            if ($idTurmaDisciplina < 1) {
                $idTurmaDisciplina = (int) $pdo->lastInsertId();
            }

            $this->salvarProfessoresDaDisciplina($idTurmaDisciplina, $idProfessor !== null ? [$idProfessor] : []);

            return $idTurmaDisciplina;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao vincular professor à disciplina da turma: ' . $e->getMessage());
            return 0;
        }
    }

    private function ensureTabelaProfessoresDisciplina(): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS turma_disciplina_professor (' .
                ' id INT(11) NOT NULL AUTO_INCREMENT,' .
                ' id_turma_disciplina INT(11) NOT NULL,' .
                ' id_usuario_professor INT(11) NOT NULL,' .
                ' ativo TINYINT(1) NOT NULL DEFAULT 1,' .
                ' created_at DATETIME DEFAULT CURRENT_TIMESTAMP,' .
                ' PRIMARY KEY (id),' .
                ' UNIQUE KEY uk_tdp_turma_disciplina_professor (id_turma_disciplina, id_usuario_professor),' .
                ' KEY idx_tdp_turma_disciplina (id_turma_disciplina)' .
                ') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
            );
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao criar tabela turma_disciplina_professor: ' . $e->getMessage());
        }
    }

    public function listarProfessoresDaDisciplina(int $idTurmaDisciplina): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $this->ensureTabelaProfessoresDisciplina();
            $stmt = $pdo->prepare(
                'SELECT tdp.id_usuario_professor AS id, u.nome AS nome' .
                ' FROM turma_disciplina_professor tdp' .
                ' LEFT JOIN usuarios u ON u.id = tdp.id_usuario_professor' .
                ' WHERE tdp.id_turma_disciplina = :id AND tdp.ativo = :ativo' .
                ' ORDER BY u.nome ASC'
            );
            $stmt->bindValue(':id', $idTurmaDisciplina, PDO::PARAM_INT);
            $stmt->bindValue(':ativo', 1, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar professores da disciplina: ' . $e->getMessage());
            return [];
        }
    }

    public function salvarProfessoresDaDisciplina(int $idTurmaDisciplina, array $idsProfessores): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idTurmaDisciplina < 1) {
            return;
        }

        try {
            $this->ensureTabelaProfessoresDisciplina();
            $ids = array_values(array_filter(array_map('intval', $idsProfessores), static fn (int $v): bool => $v > 0));

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare('DELETE FROM turma_disciplina_professor WHERE id_turma_disciplina = ? AND id_usuario_professor NOT IN (' . $placeholders . ')');
                $stmt->bindValue(1, $idTurmaDisciplina, PDO::PARAM_INT);
                foreach ($ids as $i => $id) {
                    $stmt->bindValue($i + 2, $id, PDO::PARAM_INT);
                }
                $stmt->execute();

                $stmtIns = $pdo->prepare('INSERT IGNORE INTO turma_disciplina_professor (id_turma_disciplina, id_usuario_professor, ativo) VALUES (?, ?, 1)');
                foreach ($ids as $id) {
                    $stmtIns->bindValue(1, $idTurmaDisciplina, PDO::PARAM_INT);
                    $stmtIns->bindValue(2, $id, PDO::PARAM_INT);
                    $stmtIns->execute();
                }
            } else {
                $stmt = $pdo->prepare('DELETE FROM turma_disciplina_professor WHERE id_turma_disciplina = ?');
                $stmt->bindValue(1, $idTurmaDisciplina, PDO::PARAM_INT);
                $stmt->execute();
            }

            $legacy = $ids[0] ?? null;
            $stmtUpd = $pdo->prepare('UPDATE turma_disciplina SET id_usuario_professor = :prof WHERE id = :id');
            $stmtUpd->bindValue(':prof', $legacy, $legacy !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmtUpd->bindValue(':id', $idTurmaDisciplina, PDO::PARAM_INT);
            $stmtUpd->execute();
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao salvar professores da disciplina: ' . $e->getMessage());
        }
    }

    // ==================== DISCIPLINAS DA MATRÍCULA (matricula_disciplina) ====================

    /**
     * Disciplinas da turma já vinculadas à matrícula.
     */
    public function listarDisciplinasDaMatricula(int $idMatricula): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare('SELECT md.*, td.id_disciplina, d.nome AS disciplina_nome,
                                          u.nome AS professor_nome
                                   FROM matricula_disciplina md
                                   JOIN turma_disciplina td ON td.id = md.id_turma_disciplina
                                   JOIN disciplina d ON d.id = td.id_disciplina
                                   LEFT JOIN usuarios u ON u.id = td.id_usuario_professor
                                   WHERE md.id_matricula = :id_matricula
                                   ORDER BY d.nome ASC');
            $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao listar disciplinas da matrícula: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * IDs das turma_disciplina já vinculadas à matrícula.
     */
    public function listarIdsDisciplinasDaMatricula(int $idMatricula): array
    {
        $rows = $this->listarDisciplinasDaMatricula($idMatricula);
        return array_map(static fn (array $r): int => (int) ($r['id_turma_disciplina'] ?? 0), $rows);
    }

    /**
     * Vincula uma disciplina da turma à matrícula. Retorna false se já vinculada.
     */
    public function vincularDisciplinaDaMatricula(int $idMatricula, int $idTurmaDisciplina): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idMatricula < 1 || $idTurmaDisciplina < 1) {
            return false;
        }

        try {
            $check = $pdo->prepare('SELECT id FROM matricula_disciplina WHERE id_matricula = :id_matricula AND id_turma_disciplina = :id_turma_disciplina');
            $check->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
            $check->bindValue(':id_turma_disciplina', $idTurmaDisciplina, PDO::PARAM_INT);
            $check->execute();
            if ($check->fetch()) {
                return false;
            }

            $stmt = $pdo->prepare('INSERT INTO matricula_disciplina (id_matricula, id_turma_disciplina, situacao)
                                   VALUES (:id_matricula, :id_turma_disciplina, \'MATRICULADO\')');
            $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
            $stmt->bindValue(':id_turma_disciplina', $idTurmaDisciplina, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao vincular disciplina da matrícula: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Desvincula (remove fisicamente quando sem histórico acadêmico).
     */
    public function desvincularDisciplinaDaMatricula(int $idMatricula, int $idTurmaDisciplina): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idMatricula < 1 || $idTurmaDisciplina < 1) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM matricula_disciplina WHERE id_matricula = :id_matricula AND id_turma_disciplina = :id_turma_disciplina');
            $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
            $stmt->bindValue(':id_turma_disciplina', $idTurmaDisciplina, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao desvincular disciplina da matrícula: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== SITUAÇÃO ACADÊMICA ====================

    /**
     * Busca matrículas para a situação acadêmica, filtrando por aluno,
     * CPF, curso, turma ou número de matrícula.
     */
    public function buscarMatriculasSituacao(?string $termo = null, ?int $idCurso = null, ?int $idTurma = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT m.id AS id_matricula, m.numero, m.status, m.ativo, m.id_aluno, m.id_curso, m.id_turma,
                           a.nome AS aluno_nome, a.cpf, a.email,
                           c.nome AS curso_nome, t.nome AS turma_nome,
                           (SELECT COUNT(*) FROM matricula_disciplina md WHERE md.id_matricula = m.id) AS total_disciplinas
                    FROM matricula m
                    INNER JOIN alunos a ON a.id = m.id_aluno
                    INNER JOIN cursos c ON c.id = m.id_curso
                    INNER JOIN turmas t ON t.id = m.id_turma
                    WHERE m.ativo = 1';
            $params = [];

            if ($idCurso !== null && $idCurso > 0) {
                $sql .= ' AND m.id_curso = :id_curso';
                $params[':id_curso'] = $idCurso;
            }

            if ($idTurma !== null && $idTurma > 0) {
                $sql .= ' AND m.id_turma = :id_turma';
                $params[':id_turma'] = $idTurma;
            }

            if ($termo !== null && trim($termo) !== '') {
                $termo = trim($termo);
                $sql .= ' AND (a.nome LIKE :termo OR a.cpf LIKE :termo OR m.numero LIKE :termo)';
                $params[':termo'] = '%' . $termo . '%';
            }

            $sql .= ' ORDER BY a.nome ASC LIMIT 200';

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao buscar matrículas para situação: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Dados de uma matrícula com informações do aluno, curso e turma.
     */
    public function findMatriculaParaSituacao(int $idMatricula): ?array
    {
        if ($idMatricula < 1) {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT m.id AS id_matricula, m.numero, m.status, m.id_aluno, m.id_curso, m.id_turma,
                                          a.nome AS aluno_nome, a.cpf, a.email,
                                          c.nome AS curso_nome, t.nome AS turma_nome
                                   FROM matricula m
                                   INNER JOIN alunos a ON a.id = m.id_aluno
                                   INNER JOIN cursos c ON c.id = m.id_curso
                                   INNER JOIN turmas t ON t.id = m.id_turma
                                   WHERE m.id = :id LIMIT 1');
            $stmt->bindValue(':id', $idMatricula, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao buscar matrícula para situação: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Atualiza a situação acadêmica de uma disciplina da matrícula.
     */
    public function atualizarDisciplinaDaMatricula(int $id, array $data): bool
    {
        if ($id < 1) {
            return false;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            $situacao = (string) ($data['situacao'] ?? 'MATRICULADO');
            $situacoesValidas = ['MATRICULADO', 'CURSANDO', 'APROVADO', 'REPROVADO', 'DISPENSADO', 'TRANCADO', 'CANCELADO'];
            if (!in_array($situacao, $situacoesValidas, true)) {
                $situacao = 'MATRICULADO';
            }

            $nota = isset($data['nota']) && $data['nota'] !== '' ? (float) $data['nota'] : null;
            $frequencia = isset($data['frequencia']) && $data['frequencia'] !== '' ? (float) $data['frequencia'] : null;
            $dataConclusao = isset($data['data_conclusao']) && $data['data_conclusao'] !== '' ? (string) $data['data_conclusao'] : null;
            $observacao = isset($data['observacao']) ? trim((string) $data['observacao']) : '';
            $observacao = $observacao !== '' ? $observacao : null;

            $stmt = $pdo->prepare('UPDATE matricula_disciplina
                                   SET situacao = :situacao, nota = :nota, frequencia = :frequencia,
                                       data_conclusao = :data_conclusao, observacao = :observacao
                                   WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':situacao', $situacao);
            $stmt->bindValue(':nota', $nota, $nota !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':frequencia', $frequencia, $frequencia !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':data_conclusao', $dataConclusao, $dataConclusao !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':observacao', $observacao, $observacao !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->execute();
            return true;
        } catch (\Throwable $e) {
            error_log('[ESTRUTURA] Erro ao atualizar disciplina da matrícula: ' . $e->getMessage());
            return false;
        }
    }
}
