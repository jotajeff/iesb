<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminRepository;

final class AdminService
{
    public function __construct(
        private readonly AdminRepository $repository = new AdminRepository(),
        private readonly IpLocationService $ipLocation = new IpLocationService(),
        private readonly UserAgentParserService $uaParser = new UserAgentParserService(),
    ) {
    }

    public function indicators(): array
    {
        return $this->repository->dashboardIndicators();
    }

    public function taskIndicators(int $userId, bool $isAdmin): array
    {
        return $this->repository->dashboardTaskIndicators($userId, $isAdmin);
    }

    public function logs(int $limit = 50): array
    {
        return $this->repository->recentLogs($limit);
    }

    public function usuarios(int $limit = 200): array
    {
        return $this->repository->listUsuarios($limit);
    }

    public function findUsuario(int $id): ?array
    {
        return $this->repository->findUsuarioById($id);
    }

    public function criarUsuario(string $nome, string $email, string $senha, string $tipo = 'aluno', string $ativo = '1'): int
    {
        return $this->repository->createUsuario([
            'nome' => trim($nome),
            'email' => trim($email),
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'tipo' => $tipo,
            'ativo' => $ativo,
        ]);
    }

    public function atualizarUsuario(int $id, string $senha = '', string $ativo = '1', string $nome = '', string $email = '', string $tipo = ''): void
    {
        $payload = [];
        if ($nome !== '') {
            $payload['nome'] = $nome;
        }
        if ($email !== '') {
            $payload['email'] = $email;
        }
        if ($senha !== '') {
            $payload['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }
        if ($tipo !== '') {
            $payload['tipo'] = $tipo;
        }
        $payload['ativo'] = $ativo;

        $this->repository->updateUsuario($id, $payload);
    }

    public function setores(): array
    {
        return $this->repository->listSetores();
    }

    public function findSetor(int $id): ?array
    {
        return $this->repository->findSetorById($id);
    }

    public function saveSetor(int $id, string $setor): int
    {
        return $this->repository->saveSetor([
            'id' => $id,
            'setor' => trim($setor),
        ]);
    }

    public function tarefas(int $limit = 300): array
    {
        return $this->repository->listTarefas($limit);
    }

    public function findTarefa(int $id): ?array
    {
        return $this->repository->findTarefaById($id);
    }

    public function criarTarefa(int $setorId, string $tarefa, int $criadoPor, ?int $responsavelId, string $situacao, int $prioridade = 1): int
    {
        return $this->repository->createTarefa([
            'setor' => $setorId,
            'tarefa' => trim($tarefa),
            'criado_por' => $criadoPor,
            'responsavel' => $responsavelId ?? 0,
            'situacao' => $situacao,
            'prioridade' => $prioridade,
        ]);
    }

    public function atualizarTarefa(int $id, int $setorId, string $tarefa, ?int $responsavelId, string $situacao, int $prioridade = 1): void
    {
        $this->repository->updateTarefa($id, [
            'setor' => $setorId,
            'tarefa' => trim($tarefa),
            'responsavel' => $responsavelId ?? 0,
            'situacao' => $situacao,
            'prioridade' => $prioridade,
        ]);
    }

    public function cursos(string $order = 'desc', int $limit = 200, int $nivelId = 0): array
    {
        $nivelFilter = $nivelId > 0 ? $nivelId : null;
        return array_map(
            fn (array $course): array => $this->normalizeCursoSlug($course),
            $this->repository->listCursos($limit, $order, $nivelFilter)
        );
    }

    public function cursosPorNivel(int $nivelId, int $limit = 200): array
    {
        return array_map(
            fn (array $course): array => $this->normalizeCursoSlug($course),
            $this->repository->listCursosByNivel($nivelId, $limit)
        );
    }

    public function catalogoCursosPorNivel(int $nivelId, int $segmentoId = 0, int $limit = 200): array
    {
        $nivel = $this->findNivel($nivelId);
        if (!$nivel || (int) ($nivel['ativo'] ?? 0) !== 1) {
            return [
                'nivel' => null,
                'segmentos' => [],
                'segmentoSelecionado' => null,
                'cursos' => [],
            ];
        }

        $segmentos = $this->repository->listSegmentosByNivel($nivelId);
        $segmentoSelecionado = null;
        $segmentoFiltroId = 0;
        if ($segmentoId > 0) {
            foreach ($segmentos as $segmento) {
                if ((int) ($segmento['id'] ?? 0) === $segmentoId) {
                    $segmentoSelecionado = $segmento;
                    $segmentoFiltroId = $segmentoId;
                    break;
                }
            }
        }

        $cursos = array_map(
            fn (array $course): array => $this->formatCatalogoCurso($course),
            $this->repository->listCursosByNivelAndSegmento($nivelId, $segmentoFiltroId > 0 ? $segmentoFiltroId : null, $limit)
        );

        return [
            'nivel' => $nivel,
            'segmentos' => $segmentos,
            'segmentoSelecionado' => $segmentoSelecionado,
            'cursos' => $cursos,
        ];
    }

    public function findCurso(int $id): ?array
    {
        $course = $this->repository->findCursoById($id);
        return $course ? $this->normalizeCursoSlug($course) : null;
    }

    public function atualizarCurso(
        int $id,
        string $nome,
        string $dataCurso,
        string $horario,
        string $localCurso,
        string $linkIngresso,
        string $cursoCalendario = '',
        string $ativo = 'S',
        string $exibirHome = 'N',
        string $confirmado = 'N',
        string $imagemCard = '',
        int $modalidadeId = 0,
        int $segmentoId = 0,
        int $nivelId = 0
    ): void {
        $slug = $this->generateUniqueCursoSlug($nome, $id);

        $this->repository->updateCurso($id, [
            'nome' => trim($nome),
            'slug' => $slug,
            'data_curso' => trim($dataCurso),
            'horario' => trim($horario),
            'local_curso' => trim($localCurso),
            'imagem_card' => trim($imagemCard),
            'curso_calendario' => trim($cursoCalendario),
            'link_ingresso' => trim($linkIngresso),
            'ativo' => trim($ativo),
            'exibir_home' => trim($exibirHome),
            'confirmado' => trim($confirmado),
            'modalidade_id' => $modalidadeId > 0 ? $modalidadeId : null,
            'segmento_id' => $segmentoId > 0 ? $segmentoId : null,
            'nivel_id' => $nivelId > 0 ? $nivelId : null,
        ]);
    }

    public function modalidades(): array
    {
        return $this->repository->listModalidades();
    }

    public function findModalidade(int $id): ?array
    {
        return $this->repository->findModalidadeById($id);
    }

    public function saveModalidade(int $id, string $nome, int $ativo): int
    {
        return $this->repository->saveModalidade([
            'id' => $id,
            'nome' => trim($nome),
            'ativo' => $ativo === 1 ? 1 : 0,
        ]);
    }

    public function segmentos(): array
    {
        return $this->repository->listSegmentos();
    }

    public function findSegmento(int $id): ?array
    {
        return $this->repository->findSegmentoById($id);
    }

    public function saveSegmento(int $id, string $nome, string $ativo): int
    {
        $ativoSanitizado = strtoupper(trim($ativo)) === 'N' ? 'N' : 'S';

        return $this->repository->saveSegmento([
            'id' => $id,
            'nome' => trim($nome),
            'ativo' => $ativoSanitizado,
        ]);
    }

    public function niveis(): array
    {
        return $this->repository->listNiveis();
    }

    public function findNivel(int $id): ?array
    {
        return $this->repository->findNivelById($id);
    }

    public function findNivelBySlug(string $slug): ?array
    {
        return $this->repository->findNivelBySlug($slug);
    }

    public function saveNivel(int $id, string $nome, int $ativo, string $apresentacao): int
    {
        return $this->repository->saveNivel([
            'id' => $id,
            'nome' => trim($nome),
            'ativo' => $ativo === 1 ? 1 : 0,
            'apresentacao' => trim($apresentacao),
        ]);
    }

    public function cursosDisponiveisParaHome(int $limit = 6): array
    {
        $today = (new \DateTime())->format('Y-m-d');
        return array_map(
            fn (array $course): array => $this->normalizeCursoSlug($course),
            $this->repository->listCursosDisponiveisHome($limit, $today)
        );
    }

    public function atualizarCursoImagem(int $id, string $imagemPath): void
    {
        $this->repository->updateCursoImagem($id, $imagemPath);
    }

    public static function slugify(string $text): string
    {
        $text = trim($text);
        $text = mb_strtolower($text, 'UTF-8');

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($ascii !== false) {
            $text = $ascii;
        } else {
            $map = [
                'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
                'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
                'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
                'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
                'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            ];
            $text = strtr($text, $map);
        }

        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text === '' ? 'sem-nome' : $text;
    }

    public function sincronizarSlugsCursos(): int
    {
        $updated = 0;
        foreach ($this->repository->listCursosSemSlug() as $course) {
            $id = (int) ($course['id'] ?? 0);
            $nome = (string) ($course['nome'] ?? 'curso');

            if ($id <= 0) {
                continue;
            }

            $slug = $this->generateUniqueCursoSlug($nome, $id);
            $this->repository->updateCursoSlug($id, $slug);
            $updated++;
        }

        return $updated;
    }

    public function log(
        string $acao,
        string $entidade,
        int $entidadeId,
        string $descricao,
        bool $sucesso = true
    ): void {
        $user = \App\Support\Session::get('user');
        $usuarioId = (int) ($user['id'] ?? 0);
        $perfil = (string) ($user['role'] ?? 'admin');

        $this->repository->registrarLog($usuarioId, $perfil, $acao, $entidade, $entidadeId, $descricao, $sucesso);
    }

    public function criarCurso(
        string $nome,
        string $dataCurso,
        string $horario,
        string $localCurso,
        string $linkIngresso,
        string $cursoCalendario = '',
        string $ativo = 'S',
        string $exibirHome = 'N',
        string $confirmado = 'N',
        string $imagemCard = '',
        int $modalidadeId = 0,
        int $segmentoId = 0,
        int $nivelId = 0
    ): int {
        $slug = $this->generateUniqueCursoSlug($nome);

        return $this->repository->createCurso([
            'nome' => trim($nome),
            'slug' => $slug,
            'data_curso' => trim($dataCurso),
            'horario' => trim($horario),
            'local_curso' => trim($localCurso),
            'imagem_card' => trim($imagemCard),
            'curso_calendario' => trim($cursoCalendario),
            'link_ingresso' => trim($linkIngresso),
            'ativo' => trim($ativo),
            'exibir_home' => trim($exibirHome),
            'confirmado' => trim($confirmado),
            'modalidade_id' => $modalidadeId > 0 ? $modalidadeId : null,
            'segmento_id' => $segmentoId > 0 ? $segmentoId : null,
            'nivel_id' => $nivelId > 0 ? $nivelId : null,
        ]);
    }

    private function generateUniqueCursoSlug(string $nome, ?int $ignoreId = null): string
    {
        $baseSlug = self::slugify($nome);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->repository->cursoSlugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function normalizeCursoSlug(array $course): array
    {
        $slug = trim((string) ($course['slug'] ?? ''));
        if ($slug === '') {
            $slug = self::slugify((string) ($course['nome'] ?? 'curso'));
            $course['slug'] = $slug;
        }

        return $course;
    }

    private function formatCatalogoCurso(array $course): array
    {
        $course = $this->normalizeCursoSlug($course);

        $dateText = '-';
        $rawDate = (string) ($course['data_curso'] ?? '');
        $dtDate = \DateTime::createFromFormat('Y-m-d', $rawDate);
        if ($dtDate instanceof \DateTime) {
            $dateText = $dtDate->format('d/m/Y');
        } elseif ($rawDate !== '') {
            $dateText = $rawDate;
        }

        return [
            'id' => (int) ($course['id'] ?? 0),
            'nome' => trim((string) ($course['nome'] ?? '-')),
            'imagem_card' => trim((string) ($course['imagem_card'] ?? '')),
            'local_curso' => trim((string) ($course['local_curso'] ?? '-')),
            'horario' => trim((string) ($course['horario'] ?? '-')),
            'link_ingresso' => trim((string) ($course['link_ingresso'] ?? '')),
            'confirmado' => strtoupper(trim((string) ($course['confirmado'] ?? 'N'))) === 'S' ? 'S' : 'N',
            'date_text' => $dateText,
            'segmento_id' => (int) ($course['segmento_id'] ?? 0),
            'segmento_nome' => trim((string) ($course['segmento_nome'] ?? '')),
        ];
    }

    public function visits(int $limit = 100): array
    {
        $visits = $this->repository->recentVisits($limit);
        foreach ($visits as &$visit) {
            $ip = (string) ($visit['ip'] ?? '');
            $visit['location'] = $this->ipLocation->resolve($ip);
            $visit['user_agent'] = $this->uaParser->parse((string) ($visit['user_agent'] ?? ''));

            $rawDate = (string) ($visit['data_visita'] ?? '');
            $visit['data_visita_formatada'] = $this->formatDate($rawDate);
        }

        return $visits;
    }

    public function visitsByMonthDaily(?int $month = null, ?int $year = null): array
    {
        $month = $this->sanitizeMonth($month);
        $year = $this->sanitizeYear($year);

        $rows = $this->repository->visitsByDayInMonth($month, $year);
        $totalMonth = 0;
        $days = [];
        foreach ($rows as $row) {
            $total = (int) ($row['total'] ?? 0);
            $day = substr((string) ($row['data_visita'] ?? ''), -2);
            $dayInt = (int) $day;
            $totalMonth += $total;
            $days[] = [
                'day' => $dayInt,
                'total' => $total,
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_label' => $this->monthNamePtBr($month),
            'total_month' => $totalMonth,
            'days' => $days,
        ];
    }

    public function visitsAnalytics(?int $month = null, ?int $year = null): array
    {
        $month = $this->sanitizeMonth($month);
        $year = $this->sanitizeYear($year);
        $visits = $this->repository->visitsInMonthWithPage($month, $year);

        $countries = [];
        $cities = [];
        $devices = [];
        $systems = [];
        $browsers = [];

        foreach ($visits as $visit) {
            $location = $this->ipLocation->resolve((string) ($visit['ip'] ?? ''));
            $ua = $this->uaParser->parse((string) ($visit['user_agent'] ?? ''));

            $country = trim((string) ($location['country'] ?? '-'));
            $city = trim((string) ($location['city'] ?? '-'));
            $device = trim((string) ($ua['device'] ?? '-'));
            $os = trim((string) ($ua['os'] ?? '-'));
            $browser = trim((string) ($ua['browser'] ?? '-'));

            $this->incrementBucket($countries, $country !== '' ? $country : '-');
            $this->incrementBucket($cities, $city !== '' ? $city : '-');
            $this->incrementBucket($devices, $device !== '' ? $device : '-');
            $this->incrementBucket($systems, $os !== '' ? $os : '-');
            $this->incrementBucket($browsers, $browser !== '' ? $browser : '-');
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_label' => $this->monthNamePtBr($month),
            'total' => count($visits),
            'countries' => $this->toDistribution($countries),
            'cities' => $this->toDistribution($cities),
            'devices' => $this->toDistribution($devices),
            'systems' => $this->toDistribution($systems),
            'browsers' => $this->toDistribution($browsers),
        ];
    }

    public function visitsByPage(?int $month = null, ?int $year = null): array
    {
        $month = $this->sanitizeMonth($month);
        $year = $this->sanitizeYear($year);
        $rows = $this->repository->pageVisitTotalsInMonth($month, $year);
        $total = 0;
        foreach ($rows as $row) {
            $total += (int) ($row['total'] ?? 0);
        }

        $pages = [];
        foreach ($rows as $row) {
            $count = (int) ($row['total'] ?? 0);
            $pages[] = [
                'name' => (string) ($row['pagina_nome'] ?? '-'),
                'slug' => (string) ($row['pagina_slug'] ?? '-'),
                'total' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_label' => $this->monthNamePtBr($month),
            'total' => $total,
            'pages' => $pages,
        ];
    }

    private function incrementBucket(array &$bucket, string $key): void
    {
        if (!isset($bucket[$key])) {
            $bucket[$key] = 0;
        }
        $bucket[$key]++;
    }

    private function toDistribution(array $bucket): array
    {
        arsort($bucket);
        $total = array_sum($bucket);
        $result = [];
        foreach ($bucket as $label => $count) {
            $result[] = [
                'label' => $label,
                'count' => (int) $count,
                'percent' => $total > 0 ? round(((int) $count / $total) * 100, 1) : 0.0,
            ];
        }

        return $result;
    }

    private function sanitizeMonth(?int $month): int
    {
        $month = $month ?? (int) date('m');
        if ($month < 1 || $month > 12) {
            return (int) date('m');
        }

        return $month;
    }

    private function sanitizeYear(?int $year): int
    {
        $year = $year ?? (int) date('Y');
        if ($year < 2000 || $year > 2100) {
            return (int) date('Y');
        }

        return $year;
    }

    private function monthNamePtBr(int $month): string
    {
        $months = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Marco',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];

        return $months[$month] ?? 'Mes';
    }

    private function formatDate(string $date): string
    {
        if ($date === '') {
            return '-';
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        if ($dt instanceof \DateTime) {
            return $dt->format('d/m/Y');
        }

        return $date;
    }
}
