<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\CursoService;
use App\Services\IpLocationService;
use App\Services\PreInscricaoService;
use App\Support\Session;

final class PreInscricaoController extends Controller
{
    private PreInscricaoService $preService;
    private IpLocationService $ipLocation;
    private CursoService $cursoService;

    public function __construct()
    {
        $this->preService = new PreInscricaoService();
        $this->ipLocation = new IpLocationService();
        $this->cursoService = new CursoService();
    }

    public function index(): void
    {
        if (!$this->isStaff()) {
            Session::setFlash('flash', 'Acesso negado.');
            $this->redirect('/admin/login');
        }

        $preInscricoes = $this->preService->listarRecebidos();

        foreach ($preInscricoes as &$p) {
            $ip = (string) ($p['ip'] ?? '');
            $location = $ip !== '' ? $this->ipLocation->resolve($ip) : [];
            $p['cidade'] = (string) ($location['city'] ?? '-');
            $p['pais'] = (string) ($location['country'] ?? '-');
            $p['bandeira'] = (string) ($location['flag'] ?? '🏳️');

            $cursoId = (int) ($p['curso_id'] ?? 0);
            if ($cursoId > 0) {
                $curso = $this->cursoService->findCurso($cursoId);
                $p['curso_nome'] = $curso ? (string) ($curso['nome'] ?? '-') : '-';
            }
        }
        unset($p);

        $this->render('pages/admin/preinscricao/index', [
            'title' => 'Pré-inscrições',
            'currentRoute' => '/admin/preinscricao',
            'preInscricoes' => $preInscricoes,
        ], 'admin');
    }

    private function isStaff(): bool
    {
        return (new AuthService())->isStaff();
    }
}
