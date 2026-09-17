<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ChamadaRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $temIdTurma = $this->chamadaHasIdTurma($pdo);
            $profTd = $this->professorNameExpression($pdo);

            $selectTurma = $temIdTurma ? 'ch.id_turma,' : 'td.id_turma,';
            $joinTurma = $temIdTurma
                ? ' JOIN turmas t ON t.id = ch.id_turma'
                : ' JOIN turmas t ON t.id = td.id_turma';

            $sql = 'SELECT ch.id, ' . $selectTurma . ' ch.id_turma_disciplina, ch.id_usuario_professor, ch.modo,'
                . ' ch.data_aula, ch.numero_aula, ch.hora_inicio, ch.hora_fim,'
                . ' ch.conteudo, ch.observacao, ch.status, ch.created_at, ch.origem_chamada,'
                . ' t.nome AS turma_nome, cu.nome AS curso_nome, d.nome AS disciplina_nome,'
                . ' COALESCE(uprof.nome, (' . $profTd . ')) AS professor_nome,'
                . ' (SELECT COUNT(*) FROM chamada_presenca cp WHERE cp.id_chamada = ch.id) AS total_presencas,'
                . " (SELECT COUNT(*) FROM chamada_presenca cp2 WHERE cp2.id_chamada = ch.id AND cp2.presenca = 'PRESENTE') AS total_presentes,"
                . ' (SELECT COUNT(*) FROM matricula mm WHERE mm.id_turma = td.id_turma AND mm.ativo = 1) AS total_inscritos'
                . ' FROM chamada ch'
                . $joinTurma
                . ' JOIN turma_disciplina td ON td.id = ch.id_turma_disciplina'
                . ' JOIN disciplina d ON d.id = td.id_disciplina'
                . ' LEFT JOIN cursos cu ON cu.id = t.id_curso'
                . ' LEFT JOIN usuarios uprof ON uprof.id = ch.id_usuario_professor'
                . ' ORDER BY cu.nome ASC, t.nome ASC, ch.data_aula ASC, ch.id ASC';

            $rows = $pdo->query($sql)->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function professoresDaTurma(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idTurma <= 0) {
            return [];
        }

        try {
            $ids = $this->professoresViaJunction($pdo, $idTurma, 'id_turma_junction');

            $sql = 'SELECT DISTINCT u.id, u.nome FROM usuarios u'
                . ' WHERE u.ativo = 1 AND u.tipo = \'professor\' AND u.id IN ('
                . $ids
                . ' UNION SELECT tp.id_usuario FROM turma_professor tp'
                . ' WHERE tp.id_turma = :id_turma_geral AND tp.status = \'A\' AND tp.id_usuario IS NOT NULL'
                . ' UNION SELECT td.id_usuario_professor FROM turma_disciplina td'
                . ' WHERE td.id_turma = :id_turma_disciplina AND td.ativo = 1 AND td.id_usuario_professor IS NOT NULL'
                . ') ORDER BY u.nome ASC';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_turma_junction', $idTurma, PDO::PARAM_INT);
            $stmt->bindValue(':id_turma_geral', $idTurma, PDO::PARAM_INT);
            $stmt->bindValue(':id_turma_disciplina', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em professoresDaTurma: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function disciplinasDaTurma(int $idTurma, ?int $idProfessor): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idTurma <= 0) {
            return [];
        }

        try {
            $resultado = $this->disciplinasQuery($pdo, $idTurma, $idProfessor);
            if (empty($resultado) && $idProfessor !== null && $idProfessor > 0) {
                $resultado = $this->disciplinasQuery($pdo, $idTurma, null);
            }
            return $resultado;
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em disciplinasDaTurma: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function turmas(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->query(
                'SELECT t.id, t.nome AS turma_nome, c.nome AS curso_nome'
                . ' FROM turmas t'
                . ' JOIN cursos c ON c.id = t.id_curso'
                . ' WHERE t.ativo = 1'
                . ' ORDER BY c.nome ASC, t.nome ASC'
            );
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em turmas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function relatorioPresencas(int $idTurma): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idTurma <= 0) {
            return ['turma' => null, 'alunos' => [], 'chamadas' => [], 'presencas' => []];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT t.id AS id_turma, t.nome AS turma_nome, c.nome AS curso_nome'
                . ' FROM turmas t'
                . ' JOIN cursos c ON c.id = t.id_curso'
                . ' WHERE t.id = :id_turma LIMIT 1'
            );
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            $turma = $stmt->fetch() ?: null;

            $stmt = $pdo->prepare(
                'SELECT m.id AS id_matricula, m.id_aluno, a.nome AS aluno_nome'
                . ' FROM matricula m'
                . ' JOIN alunos a ON a.id = m.id_aluno'
                . ' WHERE m.id_turma = :id_turma AND m.ativo = 1'
                . ' ORDER BY a.nome ASC'
            );
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            $alunos = $stmt->fetchAll() ?: [];

            $stmt = $pdo->prepare(
                'SELECT c.id, c.data_aula, c.hora_inicio, c.hora_fim,'
                . ' d.nome AS disciplina_nome, t.nome AS turma_nome'
                . ' FROM chamada c'
                . ' JOIN turma_disciplina td ON td.id = c.id_turma_disciplina'
                . ' JOIN turmas t ON t.id = td.id_turma'
                . ' JOIN disciplina d ON d.id = td.id_disciplina'
                . ' WHERE td.id_turma = :id_turma'
                . ' ORDER BY c.data_aula ASC, c.id ASC'
            );
            $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            $stmt->execute();
            $chamadas = $stmt->fetchAll() ?: [];

            $presencas = [];
            if (!empty($chamadas)) {
                $ids = array_column($chamadas, 'id');
                $in = implode(',', array_map('intval', $ids));
                $stmt = $pdo->query(
                    'SELECT cp.id_chamada, cp.id_matricula, cp.presenca'
                    . ' FROM chamada_presenca cp'
                    . ' WHERE cp.id_chamada IN (' . $in . ')'
                );
                foreach ($stmt->fetchAll() ?: [] as $row) {
                    $presencas[(int) ($row['id_chamada'] ?? 0) . ':' . (int) ($row['id_matricula'] ?? 0)] = (string) ($row['presenca'] ?? '');
                }
            }

            return [
                'turma' => $turma,
                'alunos' => $alunos,
                'chamadas' => $chamadas,
                'presencas' => $presencas,
            ];
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em relatorioPresencas: ' . $e->getMessage());
            return ['turma' => null, 'alunos' => [], 'chamadas' => [], 'presencas' => []];
        }
    }

    public function atualizarChamada(int $id, array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return 0;
        }

        $dataAula = trim((string) ($data['data_aula'] ?? ''));
        if ($dataAula === '') {
            return 0;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id FROM chamada WHERE id_turma_disciplina = (SELECT id_turma_disciplina FROM chamada WHERE id = :id)'
                . ' AND data_aula = :data_aula AND id <> :id2 LIMIT 1'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':id2', $id, PDO::PARAM_INT);
            $stmt->bindValue(':data_aula', $dataAula, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->fetchColumn() !== false) {
                return -1;
            }

            $status = (string) ($data['status'] ?? 'ABERTA');
            if (!in_array($status, ['ABERTA', 'FECHADA', 'CANCELADA'], true)) {
                $status = 'ABERTA';
            }
            $modo = (int) ($data['modo'] ?? 1);
            $modo = in_array($modo, [1, 2], true) ? $modo : 1;

            $stmt = $pdo->prepare(
                'UPDATE chamada SET id_usuario_professor = :id_prof, data_aula = :data_aula, numero_aula = :numero_aula,'
                . ' hora_inicio = :hora_inicio, hora_fim = :hora_fim, conteudo = :conteudo, observacao = :observacao,'
                . ' status = :status, modo = :modo, updated_at = NOW()'
                . ' WHERE id = :id'
            );
            $stmt->bindValue(':id_prof', (int) ($data['id_usuario_professor'] ?? 0) > 0 ? (int) $data['id_usuario_professor'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':data_aula', $dataAula, PDO::PARAM_STR);
            $stmt->bindValue(':numero_aula', (int) ($data['numero_aula'] ?? 0) > 0 ? (int) $data['numero_aula'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':hora_inicio', trim((string) ($data['hora_inicio'] ?? '')) !== '' ? $data['hora_inicio'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':hora_fim', trim((string) ($data['hora_fim'] ?? '')) !== '' ? $data['hora_fim'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':conteudo', trim((string) ($data['conteudo'] ?? '')) !== '' ? $data['conteudo'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':observacao', trim((string) ($data['observacao'] ?? '')) !== '' ? $data['observacao'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':modo', $modo, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return 1;
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em atualizarChamada: ' . $e->getMessage());
            return 0;
        }
    }

    public function atualizarOrigem(int $id, string $origem): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE chamada SET origem_chamada = :origem, updated_at = NOW() WHERE id = :id');
            $stmt->bindValue(':origem', mb_substr($origem, 0, 100), PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em atualizarOrigem: ' . $e->getMessage());
            return false;
        }
    }

    public function alterarStatus(int $id, string $status): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0 || !in_array($status, ['ABERTA', 'FECHADA', 'CANCELADA'], true)) {
            return false;
        }

        try {
            $stmt = $pdo->prepare('UPDATE chamada SET status = :status WHERE id = :id');
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em alterarStatus: ' . $e->getMessage());
            return false;
        }
    }

    public function buscarPorId(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return null;
        }

        try {
            $prof = $this->professorNameExpression($pdo);

            $stmt = $pdo->prepare(
                'SELECT c.id, c.status, c.modo, c.data_aula, c.numero_aula, c.hora_inicio, c.hora_fim, c.conteudo,'
                . ' c.origem_chamada, c.id_usuario_professor, c.id_turma_disciplina, td.id_turma,'
                . ' t.nome AS turma_nome, d.nome AS disciplina_nome,'
                . ' (' . $prof . ') AS professor_nome'
                . ' FROM chamada c'
                . ' JOIN turma_disciplina td ON td.id = c.id_turma_disciplina'
                . ' JOIN turmas t ON t.id = td.id_turma'
                . ' JOIN disciplina d ON d.id = td.id_disciplina'
                . ' WHERE c.id = :id LIMIT 1'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em buscarPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Alunos matriculados na turma da chamada (ordem alfabética).
     *
     * @return array<int, array<string, mixed>>
     */
    public function inscritosDaChamada(int $idChamada): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idChamada <= 0) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT m.id AS id_matricula, a.nome AS aluno_nome, a.email AS aluno_email'
                . ' FROM chamada c'
                . ' JOIN turma_disciplina td ON td.id = c.id_turma_disciplina'
                . ' JOIN matricula m ON m.id_turma = td.id_turma AND m.ativo = 1'
                . ' JOIN alunos a ON a.id = m.id_aluno'
                . ' WHERE c.id = :id'
                . ' ORDER BY a.nome ASC'
            );
            $stmt->bindValue(':id', $idChamada, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em inscritosDaChamada: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Presenças já registradas, indexadas por id_matricula.
     *
     * @return array<int, array<string, mixed>>
     */
    public function presencasDaChamada(int $idChamada): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idChamada <= 0) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id, id_matricula, presenca, entrada, permanencia, observacao'
                . ' FROM chamada_presenca WHERE id_chamada = :id'
            );
            $stmt->bindValue(':id', $idChamada, PDO::PARAM_INT);
            $stmt->execute();

            $mapa = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $mapa[(int) ($row['id_matricula'] ?? 0)] = $row;
            }
            return $mapa;
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em presencasDaChamada: ' . $e->getMessage());
            return [];
        }
    }

    public function registrarPresenca(
        int $idChamada,
        int $idMatricula,
        string $presenca,
        ?string $entrada,
        ?string $permanencia,
        ?string $observacao,
        string $ip,
        string $responsavel
    ): bool {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idChamada <= 0 || $idMatricula <= 0) {
            return false;
        }
        if (!in_array($presenca, ['PRESENTE', 'AUSENTE', 'JUSTIFICADA'], true)) {
            return false;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id FROM chamada_presenca WHERE id_chamada = :id_chamada AND id_matricula = :id_matricula LIMIT 1'
            );
            $stmt->bindValue(':id_chamada', $idChamada, PDO::PARAM_INT);
            $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
            $stmt->execute();
            $idPresenca = (int) ($stmt->fetchColumn() ?: 0);

            if ($idPresenca > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE chamada_presenca SET presenca = :presenca, entrada = :entrada, permanencia = :permanencia,'
                    . ' observacao = :observacao, ip = :ip, responsavel = :responsavel, updated_at = NOW()'
                    . ' WHERE id = :id'
                );
                $stmt->bindValue(':id', $idPresenca, PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO chamada_presenca (id_chamada, id_matricula, presenca, entrada, permanencia, observacao, ip, responsavel, created_at)'
                    . ' VALUES (:id_chamada, :id_matricula, :presenca, :entrada, :permanencia, :observacao, :ip, :responsavel, NOW())'
                );
                $stmt->bindValue(':id_chamada', $idChamada, PDO::PARAM_INT);
                $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
            }

            $stmt->bindValue(':presenca', $presenca, PDO::PARAM_STR);
            $stmt->bindValue(':entrada', $entrada, $entrada === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':permanencia', $permanencia, $permanencia === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':observacao', $observacao, $observacao === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
            $stmt->bindValue(':responsavel', $responsavel, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[CHAMADA] Erro em registrarPresenca: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $idTurma = (int) ($data['id_turma'] ?? 0);
        $idTurmaDisciplina = (int) ($data['id_turma_disciplina'] ?? 0);
        $dataAula = trim((string) ($data['data_aula'] ?? ''));
        if ($idTurma <= 0 || $idTurmaDisciplina <= 0 || $dataAula === '') {
            return 0;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'SELECT id FROM chamada WHERE id_turma_disciplina = :id_td AND data_aula = :data_aula LIMIT 1'
            );
            $stmt->bindValue(':id_td', $idTurmaDisciplina, PDO::PARAM_INT);
            $stmt->bindValue(':data_aula', $dataAula, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->fetchColumn() !== false) {
                $pdo->rollBack();
                return -1;
            }

            $temIdTurma = $this->chamadaHasIdTurma($pdo);

            $colunas = $temIdTurma
                ? '(id_turma, id_turma_disciplina, id_usuario_professor, modo, data_aula, numero_aula, hora_inicio, hora_fim, conteudo, observacao, status, created_at)'
                : '(id_turma_disciplina, id_usuario_professor, modo, data_aula, numero_aula, hora_inicio, hora_fim, conteudo, observacao, status, created_at)';
            $valores = $temIdTurma
                ? ' VALUES (:id_turma, :id_td, :id_prof, :modo, :data_aula, :numero_aula, :hora_inicio, :hora_fim, :conteudo, :observacao, :status, NOW())'
                : ' VALUES (:id_td, :id_prof, :modo, :data_aula, :numero_aula, :hora_inicio, :hora_fim, :conteudo, :observacao, :status, NOW())';

            $stmt = $pdo->prepare('INSERT INTO chamada ' . $colunas . $valores);
            if ($temIdTurma) {
                $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
            }
            $stmt->bindValue(':id_td', $idTurmaDisciplina, PDO::PARAM_INT);
            $stmt->bindValue(':id_prof', (int) ($data['id_usuario_professor'] ?? 0) > 0 ? (int) $data['id_usuario_professor'] : null, PDO::PARAM_INT);
            $modo = (int) ($data['modo'] ?? 1);
            $stmt->bindValue(':modo', in_array($modo, [1, 2], true) ? $modo : 1, PDO::PARAM_INT);
            $stmt->bindValue(':data_aula', $dataAula, PDO::PARAM_STR);
            $stmt->bindValue(':numero_aula', (int) ($data['numero_aula'] ?? 0) > 0 ? (int) $data['numero_aula'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':hora_inicio', trim((string) ($data['hora_inicio'] ?? '')) !== '' ? $data['hora_inicio'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':hora_fim', trim((string) ($data['hora_fim'] ?? '')) !== '' ? $data['hora_fim'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':conteudo', trim((string) ($data['conteudo'] ?? '')) !== '' ? $data['conteudo'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':observacao', trim((string) ($data['observacao'] ?? '')) !== '' ? $data['observacao'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':status', (string) ($data['status'] ?? 'ABERTA'), PDO::PARAM_STR);
            $stmt->execute();

            $chamadaId = (int) $pdo->lastInsertId();

            $pdo->commit();

            return $chamadaId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[CHAMADA] Erro em save: ' . $e->getMessage());
            return 0;
        }
    }

    private function professoresViaJunction(PDO $pdo, int $idTurma, string $parametroTurma): string
    {
        $mode = $this->junctionMode($pdo);

        if ($mode === 'turma') {
            return 'SELECT tdp.id_usuario_professor FROM turma_disciplina_professor tdp'
                . ' WHERE tdp.id_turma = :' . $parametroTurma . ' AND tdp.ativo = 1 AND tdp.id_usuario_professor IS NOT NULL';
        }

        return 'SELECT tdp.id_usuario_professor FROM turma_disciplina_professor tdp'
            . ' INNER JOIN turma_disciplina td2 ON td2.id = tdp.id_turma_disciplina'
            . ' WHERE td2.id_turma = :' . $parametroTurma . ' AND td2.ativo = 1 AND tdp.ativo = 1'
            . ' AND tdp.id_usuario_professor IS NOT NULL';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function disciplinasQuery(PDO $pdo, int $idTurma, ?int $idProfessor): array
    {
        $mode = $this->junctionMode($pdo);
        $filtroProfessor = '';

        if ($idProfessor !== null && $idProfessor > 0) {
            if ($mode === 'turma') {
                $filtroProfessor = ' AND (EXISTS (SELECT 1 FROM turma_disciplina_professor tdp2'
                    . ' WHERE tdp2.id_turma = :id_prof_turma'
                    . ' AND tdp2.id_usuario_professor = :id_prof_junction AND tdp2.ativo = 1)'
                    . ' OR td.id_usuario_professor = :id_prof_legacy)';
            } else {
                $filtroProfessor = ' AND (td.id_usuario_professor = :id_prof_legacy'
                    . ' OR EXISTS (SELECT 1 FROM turma_disciplina_professor tdp'
                    . ' WHERE tdp.id_turma_disciplina = td.id'
                    . ' AND tdp.id_usuario_professor = :id_prof_junction AND tdp.ativo = 1))';
            }
        }

        $sql = 'SELECT td.id, td.id_usuario_professor, d.nome AS disciplina_nome'
            . ' FROM turma_disciplina td'
            . ' INNER JOIN disciplina d ON d.id = td.id_disciplina AND d.ativo = 1'
            . ' WHERE td.id_turma = :id_turma AND td.ativo = 1'
            . $filtroProfessor
            . ' ORDER BY d.nome ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
        if ($filtroProfessor !== '') {
            if ($mode === 'turma') {
                $stmt->bindValue(':id_prof_turma', $idTurma, PDO::PARAM_INT);
            }
            $stmt->bindValue(':id_prof_legacy', $idProfessor, PDO::PARAM_INT);
            $stmt->bindValue(':id_prof_junction', $idProfessor, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function junctionMode(PDO $pdo): string
    {
        try {
            $stmt = $pdo->query('SHOW COLUMNS FROM turma_disciplina_professor');
            $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (in_array('id_turma', $colunas, true)) {
                return 'turma';
            }
        } catch (\Throwable) {
            // tabela ausente
        }

        return 'turma_disciplina';
    }

    private function chamadaHasIdTurma(PDO $pdo): bool
    {
        try {
            $stmt = $pdo->query('SHOW COLUMNS FROM chamada');
            $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return in_array('id_turma', $colunas, true);
        } catch (\Throwable) {
            return false;
        }
    }

    private function professorNameExpression(PDO $pdo): string
    {
        $mode = $this->junctionMode($pdo);

        $ids = 'SELECT td2.id_usuario_professor FROM turma_disciplina td2'
            . ' WHERE td2.id = td.id AND td2.id_usuario_professor IS NOT NULL';

        if ($mode === 'turma') {
            $ids = 'SELECT tdp.id_usuario_professor FROM turma_disciplina_professor tdp'
                . ' WHERE tdp.id_turma = td.id_turma AND tdp.ativo = 1'
                . ' AND tdp.id_usuario_professor IS NOT NULL'
                . ' UNION ' . $ids;
        } else {
            $ids = 'SELECT tdp.id_usuario_professor FROM turma_disciplina_professor tdp'
                . ' WHERE tdp.id_turma_disciplina = td.id AND tdp.ativo = 1'
                . ' AND tdp.id_usuario_professor IS NOT NULL'
                . ' UNION ' . $ids;
        }

        return 'SELECT GROUP_CONCAT(DISTINCT u.nome ORDER BY u.nome SEPARATOR \', \')'
            . ' FROM usuarios u WHERE u.id IN (' . $ids . ')';
    }
}
