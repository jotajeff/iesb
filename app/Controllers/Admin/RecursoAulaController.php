<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AuthService;
use App\Services\IpLocationService;
use App\Support\Session;
use PDO;

final class RecursoAulaController extends Controller
{
    private const TIPOS = ['todos', 'video', 'drive', 'excel', 'doc'];

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os recursos de aula.');
            $this->redirect('/admin/login');
        }

        $tipo = trim((string) ($_GET['tipo'] ?? 'video'));
        if (!in_array($tipo, self::TIPOS, true)) {
            $tipo = 'video';
        }

        $materiais = $this->materiais($tipo);
        $visualizacoes = $this->visualizacoes(array_column($materiais, 'id'));

        $totalAcessos = 0;
        $alunosUnicos = [];
        foreach ($visualizacoes as $registros) {
            foreach ($registros as $registro) {
                $totalAcessos += (int) ($registro['total_visualizacoes'] ?? 0);
                $alunosUnicos[(int) ($registro['usuario_id'] ?? 0)] = true;
            }
        }

        foreach ($materiais as &$material) {
            $id = (int) ($material['id'] ?? 0);
            $material['visualizacoes'] = $visualizacoes[$id] ?? [];
        }
        unset($material);

        $this->render('pages/admin/logs/recursos', [
            'title' => 'Recursos de aula',
            'currentRoute' => '/admin/logs/recursos',
            'materiais' => $materiais,
            'tipo' => $tipo,
            'totalAcessos' => $totalAcessos,
            'totalAlunos' => count($alunosUnicos),
        ], 'admin');
    }

    public function aluno(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os recursos de aula.');
            $this->redirect('/admin/login');
        }

        $alunoId = (int) ($_GET['aluno_id'] ?? 0);
        $alunos = $this->alunos();

        $alunoSelecionado = null;
        foreach ($alunos as $alunoItem) {
            if ((int) ($alunoItem['id'] ?? 0) === $alunoId) {
                $alunoSelecionado = $alunoItem;
                break;
            }
        }

        if ($alunoSelecionado === null) {
            $alunoId = 0;
        }

        $recursos = $alunoId > 0 ? $this->recursosDoAluno($alunoId) : [];
        $totalAcessos = 0;
        foreach ($recursos as $recurso) {
            $totalAcessos += (int) ($recurso['total_acessos'] ?? 0);
        }

        $this->render('pages/admin/logs/aluno_recursos', [
            'title' => 'Recursos por aluno',
            'currentRoute' => '/admin/logs/recursos',
            'alunos' => $alunos,
            'alunoId' => $alunoId,
            'alunoSelecionado' => $alunoSelecionado,
            'recursos' => $recursos,
            'totalAcessos' => $totalAcessos,
        ], 'admin');
    }

    public function ranking(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Faça login como admin, operador ou professor para acessar os recursos de aula.');
            $this->redirect('/admin/login');
        }

        $ranking = $this->rankingAlunos();
        $totalAcessos = 0;
        foreach ($ranking as $alunoRanking) {
            $totalAcessos += (int) ($alunoRanking['total_acessos'] ?? 0);
        }

        $this->render('pages/admin/logs/aluno_ranking', [
            'title' => 'Ranking de acessos',
            'currentRoute' => '/admin/logs/recursos',
            'ranking' => $ranking,
            'totalAcessos' => $totalAcessos,
        ], 'admin');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rankingAlunos(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->query(
                'SELECT a.id, a.nome, a.email,'
                . ' COUNT(l.id) AS total_acessos,'
                . ' COUNT(DISTINCT l.entidade_id) AS total_recursos'
                . ' FROM alunos a'
                . ' LEFT JOIN logs_auditoria l ON l.usuario_id = a.id'
                . "   AND l.perfil = 'aluno' AND l.acao = 'visualizar' AND l.sucesso = 1"
                . "   AND l.entidade IN ('video', 'drive')"
                . ' WHERE a.ativo = 1'
                . ' GROUP BY a.id, a.nome, a.email'
                . ' ORDER BY total_acessos DESC, a.nome ASC'
            );

            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('[RECURSOS AULA] Erro ao montar ranking: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function alunos(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->query('SELECT id, nome, email FROM alunos WHERE ativo = 1 ORDER BY nome ASC');
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('[RECURSOS AULA] Erro ao buscar alunos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recursosDoAluno(int $alunoId): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT m.id, m.tipo, m.titulo, m.link, m.ativo, m.created_at,'
                . ' t.nome AS turma_nome, c.nome AS curso_nome, d.nome AS disciplina_nome,'
                . ' acessos.total_acessos, acessos.primeiro_acesso, acessos.ultimo_acesso, acessos.ultimo_ip,'
                . ' CASE WHEN EXISTS ('
                . '   SELECT 1 FROM visualizacao v'
                . '   WHERE v.id_material = m.id AND v.id_aluno = :aluno_visualizacao AND v.ativo = 1'
                . ' ) THEN 1 ELSE 0 END AS visualizado'
                . ' FROM ('
                . '   SELECT l.entidade_id AS material_id, COUNT(*) AS total_acessos,'
                . '          MIN(l.created_at) AS primeiro_acesso, MAX(l.created_at) AS ultimo_acesso,'
                . "          SUBSTRING_INDEX(GROUP_CONCAT(l.ip ORDER BY l.id DESC SEPARATOR ','), ',', 1) AS ultimo_ip"
                . '   FROM logs_auditoria l'
                . "   WHERE l.usuario_id = :aluno_id AND l.perfil = 'aluno'"
                . "     AND l.acao = 'visualizar' AND l.sucesso = 1"
                . "     AND l.entidade IN ('video', 'drive')"
                . '   GROUP BY l.entidade_id'
                . ' ) acessos'
                . ' INNER JOIN material m ON m.id = CAST(acessos.material_id AS UNSIGNED)'
                . ' LEFT JOIN turmas t ON t.id = m.id_fk'
                . ' LEFT JOIN cursos c ON c.id = t.id_curso'
                . ' LEFT JOIN disciplina d ON d.id = m.id_disciplina'
                . ' ORDER BY m.titulo ASC, m.id ASC'
            );
            $stmt->bindValue(':aluno_id', $alunoId, PDO::PARAM_INT);
            $stmt->bindValue(':aluno_visualizacao', $alunoId, PDO::PARAM_INT);
            $stmt->execute();

            $ipLocation = new IpLocationService();
            $geoCache = [];
            $recursos = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $ip = (string) ($row['ultimo_ip'] ?? '');
                if ($ip !== '' && !array_key_exists($ip, $geoCache)) {
                    $geoCache[$ip] = $ipLocation->resolve($ip);
                }
                $row['localizacao'] = $ip !== '' ? $geoCache[$ip] : [];
                unset($row['ultimo_ip']);

                $recursos[] = $row;
            }

            return $recursos;
        } catch (\Throwable $e) {
            error_log('[RECURSOS AULA] Erro ao buscar recursos do aluno: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function materiais(string $tipo): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT m.id, m.tipo, m.titulo, m.link, m.id_fk, m.id_disciplina, m.created_at,'
                . ' t.nome AS turma_nome, c.nome AS curso_nome, d.nome AS disciplina_nome'
                . ' FROM material m'
                . ' LEFT JOIN turmas t ON t.id = m.id_fk'
                . ' LEFT JOIN cursos c ON c.id = t.id_curso'
                . ' LEFT JOIN disciplina d ON d.id = m.id_disciplina'
                . ' WHERE m.ativo = 1';

            if ($tipo === 'drive') {
                $sql .= " AND m.tipo IN ('drive', 'pdf')";
            } elseif ($tipo !== 'todos') {
                $sql .= ' AND m.tipo = :tipo';
            }

            $sql .= ' ORDER BY m.created_at DESC, m.id DESC';

            $stmt = $pdo->prepare($sql);
            if ($tipo !== 'todos' && $tipo !== 'drive') {
                $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            }
            $stmt->execute();

            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('[RECURSOS AULA] Erro ao buscar materiais: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param array<int, int|string> $materialIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function visualizacoes(array $materialIds): array
    {
        $materialIds = array_values(array_unique(array_filter(
            array_map('intval', $materialIds),
            static fn (int $id): bool => $id > 0
        )));

        if ($materialIds === []) {
            return [];
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($materialIds), '?'));
            $stmt = $pdo->prepare(
                'SELECT l.entidade_id AS material_id, l.usuario_id, a.nome, a.email,'
                . ' COUNT(*) AS total_visualizacoes,'
                . ' MIN(l.created_at) AS primeira_visualizacao,'
                . ' MAX(l.created_at) AS ultima_visualizacao,'
                . " SUBSTRING_INDEX(GROUP_CONCAT(l.ip ORDER BY l.id DESC SEPARATOR ','), ',', 1) AS ultimo_ip"
                . ' FROM logs_auditoria l'
                . ' INNER JOIN alunos a ON a.id = l.usuario_id'
                . " WHERE l.perfil = 'aluno' AND l.acao = 'visualizar' AND l.sucesso = 1"
                . " AND l.entidade IN ('video', 'drive')"
                . " AND l.entidade_id IN ($placeholders)"
                . ' GROUP BY l.entidade_id, l.usuario_id, a.nome, a.email'
                . ' ORDER BY a.nome ASC'
            );
            $stmt->execute(array_map('strval', $materialIds));

            $ipLocation = new IpLocationService();
            $geoCache = [];
            $grouped = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $id = (int) ($row['material_id'] ?? 0);
                if ($id <= 0) {
                    continue;
                }

                $ip = (string) ($row['ultimo_ip'] ?? '');
                if ($ip !== '' && !array_key_exists($ip, $geoCache)) {
                    $geoCache[$ip] = $ipLocation->resolve($ip);
                }
                $row['localizacao'] = $ip !== '' ? $geoCache[$ip] : [];
                unset($row['ultimo_ip']);

                $grouped[$id][] = $row;
            }

            return $grouped;
        } catch (\Throwable $e) {
            error_log('[RECURSOS AULA] Erro ao buscar visualizações: ' . $e->getMessage());
            return [];
        }
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}
