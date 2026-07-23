<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AsaasService;
use App\Services\ConfigService;
use App\Services\CursoInscricaoService;
use App\Services\CursoPagamentoService;
use App\Services\CursoService;
use App\Services\ImageService;
use App\Services\LogService;
use App\Services\NoticiaService;
use App\Services\PreInscricaoService;
use App\Services\SessaoService;
use App\Support\Session;

final class PageController extends Controller
{
    private ConfigService $configService;
    private CursoService $cursoService;
    private CursoPagamentoService $pagamentoService;
    private CursoInscricaoService $inscricaoService;
    private ImageService $imageService;
    private NoticiaService $noticiaService;
    private SessaoService $sessaoService;

    public function __construct()
    {
        $this->configService = new ConfigService();
        $this->cursoService = new CursoService();
        $this->pagamentoService = new CursoPagamentoService();
        $this->inscricaoService = new CursoInscricaoService();
        $this->imageService = new ImageService();
        $this->noticiaService = new NoticiaService();
        $this->sessaoService = new SessaoService();
    }

    public function sobre(): void
    {
        $sessao = $this->sessaoService->findBySlug('sobre');
        $sessaoBanner = null;
        $sessaoTexto = '';

        if ($sessao !== null) {
            $banner = trim((string) ($sessao['banner'] ?? ''));
            if ($banner !== '') {
                $sessaoBanner = $banner;
            }
            $sessaoTexto = (string) ($sessao['texto'] ?? '');
        }

        $this->render('pages/sobre', [
            'title' => 'Sobre',
            'currentRoute' => '/sobre',
            'sessaoBanner' => $sessaoBanner,
            'sessaoTexto' => $sessaoTexto,
        ]);
    }

    public function cursos(): void
    {
        $niveisAtivos = array_values(array_filter(
            $this->configService->niveis(),
            static fn (array $nivel): bool => (int) ($nivel['ativo'] ?? 0) === 1
        ));

        $nivelSlugRequest = trim((string) ($_GET['nivel'] ?? ($_GET['slug'] ?? '')));
        $nivelIdRequest = (int) ($_GET['nivel_id'] ?? 0);
        $segmentoIdRequest = (int) ($_GET['segmento_id'] ?? 0);
        $nivelSelecionado = null;

        if ($nivelSlugRequest !== '') {
            $nivelSelecionado = $this->configService->findNivelBySlug($nivelSlugRequest);
        } elseif ($nivelIdRequest > 0) {
            $nivelSelecionado = $this->configService->findNivel($nivelIdRequest);
        }

        if ($nivelSelecionado === null) {
            $nivelSession = Session::get('nivel_selecionado');
            $nivelSessionId = is_array($nivelSession) ? (int) ($nivelSession['id'] ?? 0) : 0;

            if ($nivelSessionId > 0) {
                $nivelSelecionado = $this->configService->findNivel($nivelSessionId);
            }
        }

        if ($nivelSelecionado === null && !empty($niveisAtivos)) {
            $nivelSelecionado = $niveisAtivos[0];
        }

        if ($nivelSelecionado !== null) {
            Session::set('nivel_selecionado', [
                'id' => (int) ($nivelSelecionado['id'] ?? 0),
                'nome' => (string) ($nivelSelecionado['nome'] ?? ''),
                'apresentacao' => (string) ($nivelSelecionado['apresentacao'] ?? ''),
            ]);
        }

        $catalogo = $nivelSelecionado !== null
            ? $this->cursoService->catalogoCursosPorNivel((int) ($nivelSelecionado['id'] ?? 0), $segmentoIdRequest)
            : [
                'nivel' => null,
                'segmentos' => [],
                'segmentoSelecionado' => null,
                'cursos' => [],
            ];

        $nivelCursoId = (int) ($catalogo['nivel']['id'] ?? ($nivelSelecionado['id'] ?? 0));
        $nivelCursoSlug = trim((string) ($catalogo['nivel']['slug'] ?? ($nivelSelecionado['slug'] ?? '')));
        $segmentosMenu = array_map(
            static function (array $segmento) use ($segmentoIdRequest, $nivelCursoSlug, $nivelCursoId): array {
                $segmentoId = (int) ($segmento['id'] ?? 0);
                $nivelParam = $nivelCursoSlug !== '' ? 'nivel=' . rawurlencode($nivelCursoSlug) : 'nivel_id=' . $nivelCursoId;

                return [
                    'id' => $segmentoId,
                    'nome' => (string) ($segmento['nome'] ?? '-'),
                    'active' => $segmentoId === $segmentoIdRequest,
                    'url' => '/cursos?' . $nivelParam . '&segmento_id=' . $segmentoId . '#lista-cursos',
                ];
            },
            $catalogo['segmentos'] ?? []
        );

        $nivelParam = $nivelCursoSlug !== '' ? 'nivel=' . rawurlencode($nivelCursoSlug) : 'nivel_id=' . $nivelCursoId;

        $sessaoBanner = null;
        if ($nivelSlugRequest !== '') {
            $sessao = $this->sessaoService->findBySlug($nivelSlugRequest);
            if ($sessao !== null) {
                $banner = trim((string) ($sessao['banner'] ?? ''));
                if ($banner !== '') {
                    $sessaoBanner = $banner;
                }
            }
        }

        $this->render('pages/cursos', [
            'title' => 'Cursos',
            'currentRoute' => '/cursos',
            'courses' => $catalogo['cursos'] ?? [],
            'nivelSelecionado' => $catalogo['nivel'] ?? $nivelSelecionado,
            'segmentosMenu' => $segmentosMenu,
            'segmentoSelecionado' => $catalogo['segmentoSelecionado'] ?? null,
            'segmentoSelecionadoId' => $segmentoIdRequest,
            'nivelCursoUrl' => '/cursos?' . $nivelParam,
            'niveisMenu' => $niveisAtivos,
            'sessaoBanner' => $sessaoBanner,
        ]);
    }

    public function eventos(): void
    {
        $sessao = $this->sessaoService->findBySlug('eventos');
        $sessaoBanner = null;
        $sessaoTitulo = '';
        $sessaoTexto = '';
        $sessaoMidia = null;
        $galeria = [];

        if ($sessao !== null) {
            $banner = trim((string) ($sessao['banner'] ?? ''));
            if ($banner !== '') {
                $sessaoBanner = $banner;
            }
            $sessaoTitulo = htmlspecialchars((string) ($sessao['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
            $sessaoTexto = (string) ($sessao['texto'] ?? '');
            $sessaoMidia = isset($sessao['midia']) ? (int) $sessao['midia'] : null;

            $sessaoId = (int) ($sessao['id'] ?? 0);
            $slugSessao = trim((string) ($sessao['slug'] ?? ''));
            if ($sessaoId > 0 && $slugSessao !== '' && $sessaoMidia !== null) {
                $galeria = $this->imageService->listarPorFk($slugSessao, $sessaoId);
            }
        }

        $this->render('pages/eventos', [
            'title' => 'Eventos',
            'currentRoute' => '/eventos',
            'sessaoBanner' => $sessaoBanner,
            'sessaoTitulo' => $sessaoTitulo,
            'sessaoTexto' => $sessaoTexto,
            'sessaoMidia' => $sessaoMidia,
            'galeria' => $galeria,
        ]);
    }

    public function parcerias(): void
    {
        $sessao = $this->sessaoService->findBySlug('parcerias');
        $sessaoBanner = null;
        $sessaoTitulo = '';
        $sessaoTexto = '';
        $sessaoMidia = null;
        $galeria = [];

        if ($sessao !== null) {
            $banner = trim((string) ($sessao['banner'] ?? ''));
            if ($banner !== '') {
                $sessaoBanner = $banner;
            }
            $sessaoTitulo = htmlspecialchars((string) ($sessao['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
            $sessaoTexto = (string) ($sessao['texto'] ?? '');
            $sessaoMidia = isset($sessao['midia']) ? (int) $sessao['midia'] : null;

            $sessaoId = (int) ($sessao['id'] ?? 0);
            $slugSessao = trim((string) ($sessao['slug'] ?? ''));
            if ($sessaoId > 0 && $slugSessao !== '' && $sessaoMidia !== null) {
                $galeria = $this->imageService->listarPorFk($slugSessao, $sessaoId);
            }
        }

        $this->render('pages/parcerias', [
            'title' => 'Parcerias',
            'currentRoute' => '/parcerias',
            'sessaoBanner' => $sessaoBanner,
            'sessaoTitulo' => $sessaoTitulo,
            'sessaoTexto' => $sessaoTexto,
            'sessaoMidia' => $sessaoMidia,
            'galeria' => $galeria,
        ]);
    }

    public function noticias(): void
    {
        $slug = trim((string) ($_GET['slug'] ?? ''));
        $todas = $this->noticiaService->listPublicados();
        $destaque = null;
        $historico = $todas;

        if ($slug !== '') {
            $noticia = $this->noticiaService->findBySlug($slug);
            if ($noticia !== null) {
                $destaque = $noticia;
                $historico = array_values(array_filter($todas, static fn (array $n) => ((string) ($n['slug'] ?? '')) !== $slug));
            }
        }

        if ($destaque === null && !empty($todas)) {
            $destaque = $todas[0];
            $historico = array_slice($todas, 1);
        }

        $this->render('pages/noticias', [
            'title' => $destaque !== null ? ((string) ($destaque['titulo'] ?? 'Notícias')) : 'Notícias',
            'currentRoute' => '/noticias',
            'destaque' => $destaque,
            'historico' => $historico,
        ]);
    }

    public function cursoDetalhe(): void
    {
        $slug = trim((string) ($_GET['slug'] ?? ''));

        if ($slug === '') {
            http_response_code(404);
            $this->render('pages/404', ['title' => 'Curso não encontrado', 'currentRoute' => '/curso']);
            return;
        }

        $curso = $this->cursoService->findCursoBySlug($slug);

        if (!$curso) {
            http_response_code(404);
            $this->render('pages/404', ['title' => 'Curso não encontrado', 'currentRoute' => '/curso']);
            return;
        }

        $id = (int) ($curso['id'] ?? 0);

        $detalhe = $this->cursoService->findDetalheByCurso($id);
        $pagamentos = $this->pagamentoService->listarPorCurso($id);

        $dateText = '-';
        $rawDate = (string) ($curso['data_curso'] ?? '');
        $dtDate = \DateTime::createFromFormat('Y-m-d', $rawDate);
        if ($dtDate instanceof \DateTime) {
            $dateText = $dtDate->format('d/m/Y');
        } elseif ($rawDate !== '') {
            $dateText = $rawDate;
        }

        $isConfirmed = strtoupper(trim((string) ($curso['confirmado'] ?? 'N'))) === 'S';
        $linkIngresso = trim((string) ($curso['link_ingresso'] ?? ''));
        $isExternalLink = $linkIngresso !== '' && !str_contains(strtolower($linkIngresso), 'saiba');

        $nivelSlug = (string) ($curso['nivel_slug'] ?? '');
        $disciplinas = [];
        $coordenadores = [];
        $professores = [];

        if ($nivelSlug === 'pos-graduacao') {
            try {
                $pdo = \App\Core\Database::connection();
                if ($pdo instanceof \PDO) {
                    $stmt = $pdo->prepare('SELECT id, nome, carga_horaria FROM disciplina WHERE id_curso = :id_curso AND ativo = :ativo ORDER BY nome ASC');
                    $stmt->bindValue(':id_curso', $id, \PDO::PARAM_INT);
                    $stmt->bindValue(':ativo', 'S', \PDO::PARAM_STR);
                    $stmt->execute();
                    $disciplinas = $stmt->fetchAll() ?: [];

                    $stmt = $pdo->prepare(
                        'SELECT u.id, u.nome AS usuario_nome, i.path AS foto_path, cr.resumo AS curriculo_resumo'
                        . ' FROM corpo_docente cd'
                        . ' JOIN usuarios u ON cd.id_usuario = u.id'
                        . ' LEFT JOIN imagem i ON i.id_fk = u.id AND i.tabela_fk = :tabela_fk AND i.ativa = :ativa_img'
                        . ' LEFT JOIN curriculo cr ON cr.id_fk = u.id AND cr.tipo = :tipo_curriculo AND cr.ativo = :ativo_curriculo'
                        . ' WHERE cd.id_curso = :id_curso AND cd.id_funcao = :id_funcao AND cd.ativo = :ativo_cd'
                        . ' ORDER BY u.nome ASC'
                    );
                    $stmt->bindValue(':id_curso', $id, \PDO::PARAM_INT);
                    $stmt->bindValue(':id_funcao', 1, \PDO::PARAM_INT);
                    $stmt->bindValue(':tabela_fk', 'usuarios', \PDO::PARAM_STR);
                    $stmt->bindValue(':ativa_img', 1, \PDO::PARAM_INT);
                    $stmt->bindValue(':tipo_curriculo', 'professor', \PDO::PARAM_STR);
                    $stmt->bindValue(':ativo_curriculo', 'S', \PDO::PARAM_STR);
                    $stmt->bindValue(':ativo_cd', 'S', \PDO::PARAM_STR);
                    $stmt->execute();
                    $coordenadores = $stmt->fetchAll() ?: [];
                }
                    $professores = [];
                    $stmt = $pdo->prepare(
                        'SELECT u.id, u.nome AS usuario_nome, i.path AS foto_path'
                        . ' FROM corpo_docente cd'
                        . ' JOIN usuarios u ON cd.id_usuario = u.id'
                        . ' LEFT JOIN imagem i ON i.id_fk = u.id AND i.tabela_fk = :tabela_fk2 AND i.ativa = :ativa_img2'
                        . ' WHERE cd.id_curso = :id_curso2 AND cd.ativo = :ativo_cd2'
                        . ' ORDER BY u.nome ASC'
                    );
                    $stmt->bindValue(':id_curso2', $id, \PDO::PARAM_INT);
                    $stmt->bindValue(':tabela_fk2', 'usuarios', \PDO::PARAM_STR);
                    $stmt->bindValue(':ativa_img2', 1, \PDO::PARAM_INT);
                    $stmt->bindValue(':ativo_cd2', 'S', \PDO::PARAM_STR);
                    $stmt->execute();
                    $professores = $stmt->fetchAll() ?: [];
                } catch (\Throwable) {
                $disciplinas = [];
                $coordenador = null;
                $professores = [];
            }
        }

        $this->render('pages/curso', [
            'title' => (string) ($curso['nome'] ?? 'Curso'),
            'currentRoute' => '/curso',
            'curso' => $curso,
            'detalhe' => $detalhe,
            'pagamentos' => $pagamentos,
            'dateText' => $dateText,
            'isConfirmed' => $isConfirmed,
            'linkIngresso' => $linkIngresso,
            'isExternalLink' => $isExternalLink,
            'nivelSlug' => $nivelSlug,
            'disciplinas' => $disciplinas,
            'coordenadores' => $coordenadores,
            'professores' => $professores,
        ]);
    }

    public function inscricao(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            http_response_code(404);
            $this->render('pages/404', ['title' => 'Curso não encontrado', 'currentRoute' => '/curso']);
            return;
        }

        $curso = $this->cursoService->findCurso($id);

        if (!$curso) {
            http_response_code(404);
            $this->render('pages/404', ['title' => 'Curso não encontrado', 'currentRoute' => '/curso']);
            return;
        }

        $pagamentos = $this->pagamentoService->listarPorCurso($id);

        $this->render('pages/inscricao', [
            'title' => 'Inscrição — ' . ($curso['nome'] ?? ''),
            'currentRoute' => '/inscricao',
            'curso' => $curso,
            'pagamentos' => $pagamentos,
            'erro' => null,
            'dados' => [],
        ]);
    }

    public function salvarInscricao(): void
    {
        $idCurso = (int) $this->input('id_curso', 0);
        $idPagamento = (int) $this->input('id_pagamento', 0);
        $nome = trim((string) $this->input('nome', ''));
        $cpf = trim((string) $this->input('cpf', ''));
        $email = trim((string) $this->input('email', ''));
        $telefone = trim((string) $this->input('telefone', ''));

        $curso = $this->cursoService->findCurso($idCurso);
        $pagamentos = $this->pagamentoService->listarPorCurso($idCurso);
        $dados = compact('idCurso', 'idPagamento', 'nome', 'cpf', 'email', 'telefone');

        if (!$curso) {
            $this->render('pages/inscricao', [
                'title' => 'Curso não encontrado',
                'currentRoute' => '/inscricao',
                'curso' => null,
                'pagamentos' => [],
                'erro' => 'Curso não encontrado.',
                'dados' => $dados,
            ]);
            return;
        }

        if ($idPagamento <= 0 || $nome === '' || $cpf === '' || $email === '' || $telefone === '') {
            $this->render('pages/inscricao', [
                'title' => 'Inscrição — ' . ($curso['nome'] ?? ''),
                'currentRoute' => '/inscricao',
                'curso' => $curso,
                'pagamentos' => $pagamentos,
                'erro' => 'Preencha todos os campos obrigatórios.',
                'dados' => $dados,
            ]);
            return;
        }

        $pagamento = null;
        foreach ($pagamentos as $p) {
            if ((int) ($p['id'] ?? 0) === $idPagamento) {
                $pagamento = $p;
                break;
            }
        }

        if (!$pagamento) {
            $this->render('pages/inscricao', [
                'title' => 'Inscrição — ' . ($curso['nome'] ?? ''),
                'currentRoute' => '/inscricao',
                'curso' => $curso,
                'pagamentos' => $pagamentos,
                'erro' => 'Forma de pagamento inválida.',
                'dados' => $dados,
            ]);
            return;
        }

        $result = $this->inscricaoService->criar([
            'id_curso' => $idCurso,
            'id_pagamento' => $idPagamento,
            'descricao_pagamento' => (string) ($pagamento['descricao'] ?? ''),
            'nome' => $nome,
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => $telefone,
            'valor' => (float) ($pagamento['valor'] ?? 0),
        ]);

        if ($result <= 0) {
            $this->render('pages/inscricao', [
                'title' => 'Inscrição — ' . ($curso['nome'] ?? ''),
                'currentRoute' => '/inscricao',
                'curso' => $curso,
                'pagamentos' => $pagamentos,
                'erro' => 'Erro ao processar inscrição. Tente novamente.',
                'dados' => $dados,
            ]);
            return;
        }

        $asaas = new AsaasService();
        $cliente = $asaas->criarCliente([
            'nome' => $nome,
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => $telefone,
        ]);

        $invoiceUrl = '';
        $bankSlipUrl = '';
        $pixQrCode = null;
        $linhaDigitavel = null;
        $cobranca = null;
        $billingType = match ((string) ($pagamento['tipo'] ?? 'PIX')) {
            'BOLETO' => 'BOLETO',
            'CARTAO' => 'CREDIT_CARD',
            default => 'PIX',
        };

        if ($cliente) {
            $cobranca = $asaas->criarCobranca([
                'customer_id' => $cliente['id'],
                'billing_type' => $billingType,
                'value' => (float) ($pagamento['valor'] ?? 0),
                'description' => ($curso['nome'] ?? 'Curso') . ' - ' . ($pagamento['descricao'] ?? ''),
                'external_reference' => (string) $result,
            ]);

            if ($cobranca) {
                $invoiceUrl = $cobranca['invoiceUrl'] ?? '';
                $bankSlipUrl = $cobranca['bankSlipUrl'] ?? '';

                if ($billingType === 'PIX') {
                    $pixQrCode = $asaas->obterPixQrCode((string) ($cobranca['id'] ?? ''));
                } elseif ($billingType === 'BOLETO') {
                    $linhaDigitavel = $asaas->obterLinhaDigitavel((string) ($cobranca['id'] ?? ''));
                }
            }
        }

        $this->inscricaoService->atualizarAsaasInfo($result, [
            'asaas_customer' => $cliente['id'] ?? null,
            'asaas_payment' => $cobranca['id'] ?? null,
            'invoice_url' => $invoiceUrl !== '' ? $invoiceUrl : $bankSlipUrl,
            'status' => $cobranca['status'] ?? 'PENDENTE',
        ]);

        $this->render('pages/inscricao', [
            'title' => 'Inscrição confirmada',
            'currentRoute' => '/inscricao',
            'curso' => $curso,
            'pagamentos' => $pagamentos,
            'erro' => null,
            'dados' => [],
            'sucesso' => true,
            'inscricaoId' => $result,
            'invoiceUrl' => $invoiceUrl,
            'bankSlipUrl' => $bankSlipUrl,
            'pixQrCode' => $pixQrCode,
            'linhaDigitavel' => $linhaDigitavel,
            'billingType' => $billingType,
            'asaasError' => $asaas->getLastError(),
        ]);
    }

    public function privacidade(): void
    {
        $this->render('pages/privacidade', ['title' => 'Política de Privacidade', 'currentRoute' => '/privacidade']);
    }

    public function preInscricao(): void
    {
        $cursoId = (int) ($_GET['curso_id'] ?? 0);
        $cursoNome = '';

        if ($cursoId > 0) {
            $curso = $this->cursoService->findCurso($cursoId);
            if ($curso) {
                $cursoNome = (string) ($curso['nome'] ?? '');
            }
        }

        $this->render('pages/pre-inscricao', [
            'title' => 'Pré-inscrição',
            'currentRoute' => '/pre-inscricao',
            'enviado' => false,
            'cursoNome' => $cursoNome,
            'cursoId' => $cursoId,
        ]);
    }

    public function enviarPreInscricao(): void
    {
        $nome = trim((string) $this->input('nome', ''));
        $email = trim((string) $this->input('email', ''));
        $whatsapp = trim((string) $this->input('whatsapp', ''));
        $cursoId = (int) $this->input('curso_id', 0);
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        if ($nome === '' || $email === '' || $whatsapp === '') {
            $this->render('pages/pre-inscricao', [
                'title' => 'Pré-inscrição',
                'currentRoute' => '/pre-inscricao',
                'enviado' => false,
                'erro' => 'Preencha todos os campos.',
                'nome' => $nome,
                'email' => $email,
                'whatsapp' => $whatsapp,
                'cursoNome' => $this->getCursoNome($cursoId),
                'cursoId' => $cursoId,
            ]);
            return;
        }

        $cursoNome = $this->getCursoNome($cursoId);

        $preService = new PreInscricaoService();
        $id = $preService->salvar([
            'nome' => $nome,
            'email' => $email,
            'whatsapp' => $whatsapp,
            'ip' => $ip,
            'curso_id' => $cursoId,
        ]);

        if ($id > 0) {
            (new LogService())->log('criar', 'pre_inscricao', $id, 'Pré-inscrição recebida: ' . $nome);
        }

        $this->render('pages/pre-inscricao', [
            'title' => 'Pré-inscrição',
            'currentRoute' => '/pre-inscricao',
            'enviado' => true,
            'cursoNome' => $cursoNome,
            'cursoId' => $cursoId,
        ]);
    }

    private function getCursoNome(int $cursoId): string
    {
        if ($cursoId < 1) {
            return '';
        }
        $curso = $this->cursoService->findCurso($cursoId);
        return $curso ? (string) ($curso['nome'] ?? '') : '';
    }
}
