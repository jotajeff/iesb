<?php

declare(strict_types=1);

use App\Controllers\Admin\AlunoController;
use App\Controllers\Admin\ConfigController;
use App\Controllers\Admin\CursoController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\TarefaController;
use App\Controllers\Admin\TurmaController;
use App\Controllers\Admin\ProfessorController;
use App\Controllers\Admin\UsuarioController;
use App\Controllers\Admin\VisitaController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\StudentController;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/home', [HomeController::class, 'index']);
    $router->get('/sobre', [PageController::class, 'sobre']);
    $router->get('/cursos', [PageController::class, 'cursos']);
    $router->get('/eventos', [PageController::class, 'eventos']);
    $router->get('/parcerias', [PageController::class, 'parcerias']);
    $router->get('/privacidade', [PageController::class, 'privacidade']);

    $router->get('/admin/login', [AuthController::class, 'adminLoginForm']);
    $router->post('/admin/login', [AuthController::class, 'adminLogin']);

    $router->get('/admin', [DashboardController::class, 'index']);
    $router->get('/admin/dashboard', [DashboardController::class, 'index']);
    $router->get('/admin/logs', [DashboardController::class, 'logs']);
    $router->get('/admin/dbase', [DashboardController::class, 'dbase']);

    $router->get('/admin/cursos', [CursoController::class, 'index']);
    $router->get('/admin/cursos/novo', [CursoController::class, 'novo']);
    $router->post('/admin/cursos/salvar', [CursoController::class, 'salvar']);
    $router->get('/admin/cursos/show', [CursoController::class, 'show']);
    $router->get('/admin/cursos/editar', [CursoController::class, 'editar']);
    $router->get('/admin/cursos/edit', [CursoController::class, 'editar']);
    $router->get('/admin/cursos/edit.php', [CursoController::class, 'editar']);
    $router->post('/admin/cursos/atualizar', [CursoController::class, 'atualizar']);
    $router->get('/admin/cursos/upload', [CursoController::class, 'uploadForm']);
    $router->post('/admin/cursos/upload', [CursoController::class, 'uploadImagem']);
    $router->get('/admin/cursos/detalhes', [CursoController::class, 'detalhes']);
    $router->post('/admin/cursos/detalhes/salvar', [CursoController::class, 'salvarDetalhe']);

    $router->get('/admin/professores', [ProfessorController::class, 'index']);
    $router->get('/admin/professores/novo', [ProfessorController::class, 'novo']);
    $router->post('/admin/professores/salvar', [ProfessorController::class, 'salvar']);
    $router->get('/admin/professores/editar', [ProfessorController::class, 'editar']);
    $router->post('/admin/professores/atualizar', [ProfessorController::class, 'atualizar']);
    $router->get('/admin/professores/endereco', [ProfessorController::class, 'endereco']);
    $router->post('/admin/professores/salvar-endereco', [ProfessorController::class, 'salvarEndereco']);
    $router->get('/admin/professores/vincular', [ProfessorController::class, 'vincular']);
    $router->post('/admin/professores/salvar-vinculo', [ProfessorController::class, 'salvarVinculo']);
    $router->get('/admin/professores/buscar-cep', [ProfessorController::class, 'buscarCep']);
    $router->get('/admin/professores/perfil', [ProfessorController::class, 'perfil']);
    $router->get('/admin/professores/turmas', [ProfessorController::class, 'turmas']);
    $router->get('/admin/professores/social', [ProfessorController::class, 'social']);
    $router->post('/admin/professores/salvar-social', [ProfessorController::class, 'salvarSocial']);
    $router->post('/admin/professores/deletar-social', [ProfessorController::class, 'deletarSocial']);
    $router->get('/admin/professores/curriculo', [ProfessorController::class, 'curriculo']);
    $router->post('/admin/professores/salvar-curriculo', [ProfessorController::class, 'salvarCurriculo']);
    $router->get('/admin/professores/videos', [ProfessorController::class, 'videos']);
    $router->post('/admin/professores/salvar-video', [ProfessorController::class, 'salvarVideo']);
    $router->post('/admin/professores/deletar-video', [ProfessorController::class, 'deletarVideo']);
    $router->get('/admin/professores/drive', [ProfessorController::class, 'drive']);
    $router->post('/admin/professores/salvar-drive', [ProfessorController::class, 'salvarDrive']);

    $router->get('/admin/usuarios', [UsuarioController::class, 'index']);
    $router->get('/admin/usuarios/novo', [UsuarioController::class, 'novo']);
    $router->post('/admin/usuarios/salvar', [UsuarioController::class, 'salvar']);
    $router->get('/admin/usuarios/editar', [UsuarioController::class, 'editar']);
    $router->post('/admin/usuarios/atualizar', [UsuarioController::class, 'atualizar']);

    $router->get('/admin/alunos', [AlunoController::class, 'index']);
    $router->get('/admin/alunos/novo', [AlunoController::class, 'novo']);
    $router->get('/admin/alunos/editar', [AlunoController::class, 'editar']);
    $router->post('/admin/alunos/salvar', [AlunoController::class, 'salvar']);
    $router->post('/admin/alunos/atualizar', [AlunoController::class, 'atualizar']);
    $router->get('/admin/alunos/show', [AlunoController::class, 'show']);
    $router->get('/admin/alunos/matricula', [AlunoController::class, 'matricula']);
    $router->post('/admin/alunos/matricular', [AlunoController::class, 'matricular']);
    $router->get('/admin/alunos/troca-historico', [AlunoController::class, 'trocaHistorico']);
    $router->get('/admin/alunos/troca', [AlunoController::class, 'troca']);
    $router->post('/admin/alunos/trocar', [AlunoController::class, 'trocar']);
    $router->post('/admin/alunos/restaurar-senha', [AlunoController::class, 'restaurarSenha']);

    $router->get('/admin/tarefas', [TarefaController::class, 'index']);
    $router->get('/admin/tarefas/lista', [TarefaController::class, 'lista']);
    $router->get('/admin/tarefas/novo', [TarefaController::class, 'novo']);
    $router->post('/admin/tarefas/salvar', [TarefaController::class, 'salvar']);
    $router->get('/admin/tarefas/show', [TarefaController::class, 'show']);
    $router->get('/admin/tarefas/editar', [TarefaController::class, 'editar']);
    $router->post('/admin/tarefas/atualizar', [TarefaController::class, 'atualizar']);
    $router->post('/admin/tarefas/comentario', [TarefaController::class, 'comentario']);

    $router->get('/admin/turmas', [TurmaController::class, 'index']);
    $router->get('/admin/turmas/show', [TurmaController::class, 'show']);
    $router->get('/admin/turmas/novo', [TurmaController::class, 'novo']);
    $router->get('/admin/turmas/editar', [TurmaController::class, 'editar']);
    $router->post('/admin/turmas/salvar', [TurmaController::class, 'salvar']);
    $router->post('/admin/turmas/atualizar', [TurmaController::class, 'atualizar']);
    $router->get('/admin/turmas/ver-video', [TurmaController::class, 'verVideo']);
    $router->get('/admin/turmas/ver-drive', [TurmaController::class, 'verDrive']);

    $router->get('/admin/modalidade', [ConfigController::class, 'modalidade']);
    $router->get('/admin/modalidade/edit', [ConfigController::class, 'editModalidade']);
    $router->post('/admin/modalidade/update', [ConfigController::class, 'updateModalidade']);

    $router->get('/admin/segmento', [ConfigController::class, 'segmento']);
    $router->get('/admin/segmento/edit', [ConfigController::class, 'editSegmento']);
    $router->post('/admin/segmento/update', [ConfigController::class, 'updateSegmento']);

    $router->get('/admin/setor', [ConfigController::class, 'setor']);
    $router->get('/admin/setor/edit', [ConfigController::class, 'editSetor']);
    $router->post('/admin/setor/update', [ConfigController::class, 'updateSetor']);

    $router->get('/admin/nivel', [ConfigController::class, 'nivel']);
    $router->get('/admin/nivel/edit', [ConfigController::class, 'editNivel']);
    $router->post('/admin/nivel/update', [ConfigController::class, 'updateNivel']);

    $router->get('/admin/visitas', [VisitaController::class, 'index']);
    $router->get('/admin/visitas/mensal', [VisitaController::class, 'mensal']);
    $router->get('/admin/visitas/analytics', [VisitaController::class, 'analytics']);
    $router->get('/admin/visitas/paginas', [VisitaController::class, 'paginas']);

    $router->get('/aluno/login', [AuthController::class, 'alunoLoginForm']);
    $router->post('/aluno/login', [AuthController::class, 'alunoLogin']);
    $router->get('/aluno/solicitar-redefinicao', [AuthController::class, 'mostrarSolicitarRedefinicao']);
    $router->post('/aluno/solicitar-redefinicao', [AuthController::class, 'solicitarRedefinicao']);
    $router->get('/aluno/redefinir-senha', [AuthController::class, 'mostrarRedefinirSenha']);
    $router->post('/aluno/redefinir-senha', [AuthController::class, 'redefinirSenha']);
    $router->get('/aluno', [StudentController::class, 'dashboard']);
    $router->get('/aluno/cursos', [StudentController::class, 'cursos']);
    $router->get('/aluno/show', [StudentController::class, 'show']);
    $router->get('/aluno/video', [StudentController::class, 'video']);
    $router->get('/aluno/drive', [StudentController::class, 'drive']);
    $router->get('/aluno/detalhes', [StudentController::class, 'detalhes']);
    $router->post('/aluno/matricular-curso', [StudentController::class, 'enrollCurso']);
    $router->get('/aluno/logs', [StudentController::class, 'logs']);
    $router->get('/aluno/perfil', [StudentController::class, 'perfil']);
    $router->post('/aluno/perfil/atualizar', [StudentController::class, 'atualizarPerfil']);
    $router->post('/aluno/foto', [StudentController::class, 'foto']);
    $router->post('/aluno/matricular', [StudentController::class, 'enroll']);

    $router->get('/area-do-aluno', [StudentController::class, 'dashboard']);
    $router->post('/logout', [AuthController::class, 'logout']);
};
