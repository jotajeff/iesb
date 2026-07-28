<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ConfigRepository;
use App\Repositories\CursoRepository;

final class CursoService
{
    public function __construct(
        private readonly CursoRepository $repository = new CursoRepository(),
    ) {
    }

    public function cursos(string $order = 'desc', int $limit = 200, int $nivelId = 0): array
    {
        $nivelFilter = $nivelId > 0 ? $nivelId : null;
        return array_map(
            fn (array $course): array => $this->normalizeCursoSlug($course),
            $this->repository->list($limit, $order, $nivelFilter)
        );
    }

    public function cursosDisponiveis(int $limit = 200, string $referenceDate = ''): array
    {
        return array_map(
            fn (array $course): array => $this->normalizeCursoSlug($course),
            $this->repository->listDisponiveis($limit, $referenceDate)
        );
    }

    public function cursosDisponiveisParaHome(int $limit = 6): array
    {
        $today = (new \DateTime())->format('Y-m-d');
        return array_map(
            fn (array $course): array => $this->normalizeCursoSlug($course),
            $this->repository->listDisponiveisHome($limit, $today)
        );
    }

    public function cursosPorNivel(int $nivelId, int $limit = 200): array
    {
        return array_map(
            fn (array $course): array => $this->normalizeCursoSlug($course),
            $this->repository->listByNivel($nivelId, $limit)
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

        $configRepo = new ConfigRepository();
        $segmentos = $configRepo->listSegmentosByNivel($nivelId);
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
            $this->repository->listByNivelAndSegmento($nivelId, $segmentoFiltroId > 0 ? $segmentoFiltroId : null, $limit)
        );

        return [
            'nivel' => $nivel,
            'segmentos' => $segmentos,
            'segmentoSelecionado' => $segmentoSelecionado,
            'cursos' => $cursos,
        ];
    }

    public function cursosAtivos(): array
    {
        return array_map(
            fn (array $course): array => $this->normalizeCursoSlug($course),
            $this->repository->listAtivos()
        );
    }

    public function idsCursosComDetalhe(): array
    {
        return $this->repository->listIdsComDetalhe();
    }

    public function idsCursosComTurma(): array
    {
        return $this->repository->listIdsComTurma();
    }

    public function listarCursosTurmas(): array
    {
        return $this->repository->listarCursosTurmas();
    }

    public function findCurso(int $id): ?array
    {
        $course = $this->repository->findById($id);
        return $course ? $this->normalizeCursoSlug($course) : null;
    }

    public function findCursoBySlug(string $slug): ?array
    {
        $course = $this->repository->findBySlug($slug);
        return $course ? $this->normalizeCursoSlug($course) : null;
    }

    public function criarCurso(
        string $nome,
        string $dataCurso,
        string $horario,
        string $localCurso,
        string $linkIngresso,
        string $cursoCalendario = '',
        int $ativo = 1,
        int $exibirHome = 0,
        int $confirmado = 0,
        string $imagemCard = '',
        int $modalidadeId = 0,
        int $segmentoId = 0,
        int $nivelId = 0,
        int $cargaHoraria = 0,
        string $publicoAlvo = ''
    ): int {
        $slug = $this->generateUniqueSlug($nome);

        return $this->repository->create([
            'nome' => trim($nome),
            'slug' => $slug,
            'data_curso' => trim($dataCurso),
            'horario' => trim($horario),
            'local_curso' => trim($localCurso),
            'imagem_card' => trim($imagemCard),
            'curso_calendario' => trim($cursoCalendario),
            'link_ingresso' => trim($linkIngresso),
            'ativo' => $ativo ? 1 : 0,
            'exibir_home' => $exibirHome ? 1 : 0,
            'confirmado' => $confirmado ? 1 : 0,
            'carga_horaria' => $cargaHoraria,
            'modalidade_id' => $modalidadeId > 0 ? $modalidadeId : null,
            'segmento_id' => $segmentoId > 0 ? $segmentoId : null,
            'nivel_id' => $nivelId > 0 ? $nivelId : null,
            'publico_alvo' => trim($publicoAlvo),
        ]);
    }

    public function atualizarCurso(
        int $id,
        string $nome,
        string $dataCurso,
        string $horario,
        string $localCurso,
        string $linkIngresso,
        string $cursoCalendario = '',
        int $ativo = 1,
        int $exibirHome = 0,
        int $confirmado = 0,
        string $imagemCard = '',
        int $modalidadeId = 0,
        int $segmentoId = 0,
        int $nivelId = 0,
        int $cargaHoraria = 0,
        string $publicoAlvo = ''
    ): void {
        $slug = $this->generateUniqueSlug($nome, $id);

        $this->repository->update($id, [
            'nome' => trim($nome),
            'slug' => $slug,
            'data_curso' => trim($dataCurso),
            'horario' => trim($horario),
            'local_curso' => trim($localCurso),
            'imagem_card' => trim($imagemCard),
            'curso_calendario' => trim($cursoCalendario),
            'link_ingresso' => trim($linkIngresso),
            'ativo' => $ativo ? 1 : 0,
            'exibir_home' => $exibirHome ? 1 : 0,
            'confirmado' => $confirmado ? 1 : 0,
            'carga_horaria' => $cargaHoraria,
            'modalidade_id' => $modalidadeId > 0 ? $modalidadeId : null,
            'segmento_id' => $segmentoId > 0 ? $segmentoId : null,
            'nivel_id' => $nivelId > 0 ? $nivelId : null,
            'publico_alvo' => trim($publicoAlvo),
        ]);
    }

    public function atualizarImagem(int $id, string $imagemPath): void
    {
        $this->repository->updateImagem($id, $imagemPath);
    }

    public function sincronizarSlugs(): int
    {
        $updated = 0;
        foreach ($this->repository->listSemSlug() as $course) {
            $id = (int) ($course['id'] ?? 0);
            $nome = (string) ($course['nome'] ?? 'curso');

            if ($id <= 0) {
                continue;
            }

            $slug = $this->generateUniqueSlug($nome, $id);
            $this->repository->updateSlug($id, $slug);
            $updated++;
        }

        return $updated;
    }

    public function findDetalheByCurso(int $idCurso): ?array
    {
        return $this->repository->findDetalheByCursoId($idCurso);
    }

    public function salvarDetalhe(array $payload): int
    {
        return $this->repository->saveDetalhe($payload);
    }

    public function atualizarDetalhe(int $id, array $payload): void
    {
        $this->repository->updateDetalhe($id, $payload);
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

    private function generateUniqueSlug(string $nome, ?int $ignoreId = null): string
    {
        $baseSlug = self::slugify($nome);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->repository->slugExists($slug, $ignoreId)) {
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
            'slug' => trim((string) ($course['slug'] ?? '')),
            'imagem_card' => trim((string) ($course['imagem_card'] ?? '')),
            'local_curso' => trim((string) ($course['local_curso'] ?? '-')),
            'horario' => trim((string) ($course['horario'] ?? '-')),
            'link_ingresso' => trim((string) ($course['link_ingresso'] ?? '')),
            'confirmado' => intval($course['confirmado'] ?? 0) === 1 ? 1 : 0,
            'date_text' => $dateText,
            'segmento_id' => (int) ($course['segmento_id'] ?? 0),
            'segmento_nome' => trim((string) ($course['segmento_nome'] ?? '')),
            'modalidade_nome' => trim((string) ($course['modalidade_nome'] ?? '')),
        ];
    }

    private function findNivel(int $id): ?array
    {
        $configRepo = new ConfigRepository();
        return $configRepo->findNivelById($id);
    }
}
