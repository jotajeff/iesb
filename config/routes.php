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
use App\Controllers\Admin\NoticiaController;
use App\Controllers\Admin\NotificacaoController;
use App\Controllers\Admin\AsaasController;
use App\Controllers\Admin\PreInscricaoController;
use App\Controllers\Admin\SessaoController;
use App\Controllers\Admin\TipoDocumentoController;
use App\Controllers\Admin\StorageController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\StudentController;
use App\Controllers\ApiController;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/home', [HomeController::class, 'index']);
    $router->get('/sobre', [PageController::class, 'sobre']);
    $router->get('/cursos', [PageController::class, 'cursos']);
    $router->get('/cursos/{slug}', [PageController::class, 'cursos']);
    $router->get('/curso/{slug}', [PageController::class, 'cursoDetalhe']);
    $router->get('/curso/{id}/inscricao', [PageController::class, 'inscricao']);
    $router->post('/inscricao/salvar', [PageController::class, 'salvarInscricao']);
    $router->get('/eventos', [PageController::class, 'eventos']);
    $router->get('/parcerias', [PageController::class, 'parcerias']);
    $router->get('/noticias', [PageController::class, 'noticias']);
    $router->get('/noticias/{slug}', [PageController::class, 'noticias']);
    $router->get('/privacidade', [PageController::class, 'privacidade']);
    $router->get('/sitemap.xml', [PageController::class, 'sitemap']);
    $router->get('/pre-inscricao', [PageController::class, 'preInscricao']);
    $router->post('/pre-inscricao', [PageController::class, 'enviarPreInscricao']);

    $router->get('/admin/login', [AuthController::class, 'adminLoginForm']);
    $router->post('/admin/login', [AuthController::class, 'adminLogin']);

    $router->get('/admin', [DashboardController::class, 'index']);
    $router->get('/admin/dashboard', [DashboardController::class, 'index']);
    $router->get('/admin/logs', [DashboardController::class, 'logs']);
    $router->get('/admin/dbase', [DashboardController::class, 'dbase']);
    $router->get('/admin/asaas', [AsaasController::class, 'index']);

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
    $router->post('/admin/cursos/excluir-imagem', [CursoController::class, 'excluirImagemCard']);
    $router->get('/admin/cursos/detalhes', [CursoController::class, 'detalhes']);
    $router->post('/admin/cursos/detalhes/salvar', [CursoController::class, 'salvarDetalhe']);
    $router->get('/admin/cursos/disciplinas', [CursoController::class, 'disciplinas']);
    $router->post('/admin/cursos/salvar-disciplina', [CursoController::class, 'salvarDisciplina']);
    $router->get('/admin/cursos/importar-disciplinas', [CursoController::class, 'importarDisciplinas']);
    $router->post('/admin/cursos/importar-disciplinas', [CursoController::class, 'processarImportarDisciplinas']);
    $router->get('/admin/cursos/ementa', [CursoController::class, 'ementa']);
    $router->post('/admin/cursos/salvar-ementa', [CursoController::class, 'salvarEmenta']);
    $router->get('/admin/cursos/corpo-docente', [CursoController::class, 'corpoDocente']);
    $router->post('/admin/cursos/salvar-corpo-docente', [CursoController::class, 'salvarCorpoDocente']);
    $router->post('/admin/cursos/remover-corpo-docente', [CursoController::class, 'removerCorpoDocente']);
    $router->get('/admin/cursos/galeria', [CursoController::class, 'galeria']);
    $router->post('/admin/cursos/upload-galeria', [CursoController::class, 'uploadGaleria']);
    $router->post('/admin/cursos/deletar-galeria', [CursoController::class, 'deletarGaleria']);
    $router->get('/admin/cursos/definir-valor', [CursoController::class, 'definirValor']);
    $router->post('/admin/cursos/salvar-pagamento', [CursoController::class, 'salvarPagamento']);
    $router->get('/admin/cursos/cursos-turma', [CursoController::class, 'cursosTurma']);

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
    $router->get('/admin/professores/fotos', [ProfessorController::class, 'fotos']);
    $router->post('/admin/professores/upload-foto', [ProfessorController::class, 'uploadFoto']);
    $router->post('/admin/professores/deletar-foto', [ProfessorController::class, 'deletarFoto']);
    $router->get('/admin/professores/detalhe', [ProfessorController::class, 'detalhe']);
    $router->get('/admin/professores/documentos', [ProfessorController::class, 'documentos']);
    $router->post('/admin/professores/documentos/enviar', [ProfessorController::class, 'uploadDocumento']);
    $router->get('/admin/professores/documentos/visualizar', [ProfessorController::class, 'visualizarDocumento']);
    $router->get('/admin/professores/documentos/baixar', [ProfessorController::class, 'baixarDocumento']);
    $router->post('/admin/professores/documentos/excluir', [ProfessorController::class, 'excluirDocumento']);

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
    $router->post('/admin/alunos/compartilhar-documento', [AlunoController::class, 'compartilharDocumento']);
    $router->post('/admin/alunos/liberar-documentos', [AlunoController::class, 'liberarDocumentosPublicos']);

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

    $router->get('/admin/nivel',            [ConfigController::class, 'nivel']);
    $router->get('/admin/nivel/edit',        [ConfigController::class, 'editNivel']);
    $router->post('/admin/nivel/update',      [ConfigController::class, 'updateNivel']);
    $router->get('/admin/tipos-documentos', [TipoDocumentoController::class, 'index']);
    $router->get('/admin/tipos-documentos/novo', [TipoDocumentoController::class, 'novo']);
    $router->post('/admin/tipos-documentos/salvar', [TipoDocumentoController::class, 'salvar']);
    $router->get('/admin/tipos-documentos/editar', [TipoDocumentoController::class, 'editar']);
    $router->post('/admin/tipos-documentos/atualizar', [TipoDocumentoController::class, 'atualizar']);
    $router->get('/admin/tipos-documentos/excluir', [TipoDocumentoController::class, 'excluir']);

    $router->get('/admin/funcoes-docente',            [ConfigController::class, 'funcoesDocente']);
    $router->get('/admin/funcoes-docente/edit',        [ConfigController::class, 'editFuncaoDocente']);
    $router->post('/admin/funcoes-docente/update',      [ConfigController::class, 'updateFuncaoDocente']);

    $router->get('/admin/config/carousel', [ConfigController::class, 'carousel']);
    $router->get('/admin/config/carousel/editar', [ConfigController::class, 'editCarousel']);
    $router->post('/admin/config/carousel/salvar', [ConfigController::class, 'updateCarousel']);
    $router->post('/admin/config/carousel/deletar', [ConfigController::class, 'deleteCarouselItem']);

    $router->get('/admin/config/noticias', [NoticiaController::class, 'index']);
    $router->get('/admin/config/noticias/editar', [NoticiaController::class, 'editar']);
    $router->post('/admin/config/noticias/salvar', [NoticiaController::class, 'salvar']);
    $router->post('/admin/config/noticias/deletar', [NoticiaController::class, 'deletar']);

    $router->get('/admin/preinscricao', [PreInscricaoController::class, 'index']);
    $router->get('/admin/preinscricao/kanban', [PreInscricaoController::class, 'kanban']);
    $router->get('/admin/preinscricao/detalhe', [PreInscricaoController::class, 'detalhe']);
    $router->post('/admin/preinscricao/comentario', [PreInscricaoController::class, 'comentario']);
    $router->post('/admin/preinscricao/atualizar-situacao', [PreInscricaoController::class, 'atualizarSituacao']);

    $router->get('/admin/sessao', [SessaoController::class, 'index']);
    $router->get('/admin/sessao/novo', [SessaoController::class, 'novo']);
    $router->get('/admin/sessao/editar', [SessaoController::class, 'editar']);
    $router->post('/admin/sessao/salvar', [SessaoController::class, 'salvar']);
    $router->post('/admin/sessao/deletar', [SessaoController::class, 'deletar']);
    $router->get('/admin/sessao/imagem', [SessaoController::class, 'imagem']);
    $router->post('/admin/sessao/upload-imagem', [SessaoController::class, 'uploadImagem']);
    $router->post('/admin/sessao/deletar-imagem', [SessaoController::class, 'deletarImagem']);

    $router->get('/admin/notificacoes', [NotificacaoController::class, 'index']);
    $router->get('/admin/notificacoes/leitura', [NotificacaoController::class, 'leitura']);
    $router->get('/admin/notificacoes/clone', [NotificacaoController::class, 'clone']);
    $router->post('/admin/notificacoes/salvar', [NotificacaoController::class, 'salvar']);
    $router->post('/admin/notificacoes/marcar-lida', [NotificacaoController::class, 'marcarLida']);

    $router->get('/admin/config/categoria', [ConfigController::class, 'categoria']);
    $router->get('/admin/config/categoria/edit', [ConfigController::class, 'editCategoria']);
    $router->post('/admin/config/categoria/update', [ConfigController::class, 'updateCategoria']);

    $router->get('/admin/config/cliente', [ConfigController::class, 'cliente']);
    $router->get('/admin/config/cliente/editar', [ConfigController::class, 'editCliente']);
    $router->post('/admin/config/cliente/atualizar', [ConfigController::class, 'updateCliente']);

    $router->get('/admin/visitas', [VisitaController::class, 'index']);
    $router->get('/admin/visitas/mensal', [VisitaController::class, 'mensal']);
    $router->get('/admin/visitas/analytics', [VisitaController::class, 'analytics']);
    $router->get('/admin/visitas/paginas', [VisitaController::class, 'paginas']);
    $router->get('/admin/visitas/cursos', [VisitaController::class, 'cursos']);
    $router->get('/admin/visitas/referer', [VisitaController::class, 'referer']);

    $router->get('/admin/storage', [StorageController::class, 'index']);
    $router->get('/admin/storage/callback', [StorageController::class, 'callback']);
    $router->post('/admin/storage/disconnect', [StorageController::class, 'disconnect']);
    $router->post('/admin/storage/estrutura', [StorageController::class, 'estrutura']);

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
    $router->get('/aluno/endereco', [StudentController::class, 'endereco']);
    $router->post('/aluno/endereco/atualizar', [StudentController::class, 'atualizarEndereco']);
    $router->get('/aluno/buscar-cep', [StudentController::class, 'buscarCep']);
    $router->post('/aluno/foto', [StudentController::class, 'foto']);
    $router->post('/aluno/matricular', [StudentController::class, 'enroll']);
    $router->get('/aluno/notificacoes', [StudentController::class, 'notificacoes']);
    $router->post('/aluno/notificacoes/marcar-lida', [StudentController::class, 'marcarLida']);
    $router->get('/aluno/documentos', [StudentController::class, 'documentos']);
    $router->post('/aluno/documentos/enviar', [StudentController::class, 'uploadDocumento']);
    $router->get('/aluno/documentos/visualizar', [StudentController::class, 'visualizarDocumento']);
    $router->get('/aluno/documentos/baixar', [StudentController::class, 'baixarDocumento']);
    $router->post('/aluno/documentos/excluir', [StudentController::class, 'excluirDocumento']);
    $router->get('/aluno/noticia', [StudentController::class, 'noticia']);

    $router->get('/area-do-aluno', [StudentController::class, 'dashboard']);
    $router->post('/logout', [AuthController::class, 'logout']);

    /* =============================================
       APIs externas
       ============================================= */
    $router->get('/api/cursos-home', [ApiController::class, 'cursosHome']);
    $router->get('/api/cursos-ativos', [ApiController::class, 'cursosAtivos']);
};
