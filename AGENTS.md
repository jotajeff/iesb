# IESB — Guia para Agentes

## Stack

PHP 8.0+, MySQL, Bootstrap 5, vanilla JS, PHPMailer, google/apiclient. Framework MVC custom (`app/Core/`), monolítico (sem npm, sem build step).

## Comandos

```bash
php -S localhost:8000 -t public   # servidor de dev
composer install                   # instalar dependências (PHPMailer, google/apiclient)
```

Não há testes, linting, typecheck ou CI.

## Estrutura

| Diretório | Propósito |
|---|---|
| `public/` | Front controller (`index.php`), assets, uploads |
| `public/assets/img/professor/` | Fotos dos professores (upload via `ProfessorController::uploadFoto`) |
| `public/assets/img/banner/` | Banners com prefixo `aluno_` |
| `bootstrap/app.php` | Autoload (PSR-4 + fallback), `.env`, sessão, rotas |
| `config/` | `app.php` (config), `routes.php` (rotas) |
| `app/Core/` | Framework: App, Router, Controller, Database, Env, View |
| `app/Controllers/Admin/` | Controllers do painel staff |
| `app/Controllers/` | `StudentController` (portal aluno), `HomeController`, `AuthController` |
| `app/Services/` | Regras de negócio |
| `app/Services/Storage/` | Módulo Storage (Google Drive): fachada `StorageService`, provider, OAuth |
| `app/Repositories/` | Acesso a dados (PDO MySQL) |
| `app/Views/` | Templates PHP, layouts em `layouts/` |
| `storage/` | Schema SQL, migrações, JSON legados, logs |
| `vendor/` | PHPMailer + google/apiclient (deps Composer) |

## Convenções

- **Rotas**: em `config/routes.php`. Params `{slug}`, `{id}` injetados em `$_GET`.
- **Layouts**: `base` (público), `admin` (staff), `aluno` (portal aluno). 3º arg de `View::render()`.
- **Auth**: `$_SESSION['user']` com `id`, `name`, `email`, `role`. Staff via `storage/users.json` (bcrypt). Alunos via MySQL.
- **Roles**: `admin`, `professor`, `coordenador`, `operador`, `secretaria`, `aluno`. `isStaff()` = admin || professor || coordenador || secretaria || operador.
- **Controller base**: `render()`, `redirect()`, `input()`, `json()`. `render()` injeta `authUser`, `flash`, `niveisMenu`, `nivelSelecionado`.
- **Services** instanciados com `new` nos controllers, sem DI container.
- **`declare(strict_types=1)`** em todo PHP.
- **Namespace `App\`** → `app/` (PSR-4).

## Banco de dados

- MySQL via PDO (`App\Core\Database::connection()`). Retorna `null` se `.env` não configurado.
- Schema: `storage/db_estrutura.sql`. Migrações: `storage/migration_*.sql`.
- Dados legados: `storage/users.json`, `courses.json`, `enrollments.json`.
- `.gitignore` ignora `*.md` e `*.sql` — arquivos SQL/migration são criados via `CREATE TABLE IF NOT EXISTS` no código (self-healing), não versionados.

### Padronização obrigatória (padronizacao.md)

| Regra | Padrão | Nunca |
|---|---|---|
| PK | `id INT AUTO_INCREMENT PRIMARY KEY` | — |
| Status | `ativo TINYINT(1) NOT NULL DEFAULT 1` | `CHAR(1)`, `ENUM`, `'S'/'N'` |
| Datas | `created_at`, `updated_at` (DATETIME) | `criado_em`, `data_cadastro` |
| Tabelas | Singular (`curso`, `turma`) | Plurais |
| FK | `id_usuario`, `id_curso` | `usuario_id`, `cursoID` |
| Exclusão | Soft delete: `UPDATE SET ativo = 0` | `DELETE` |

### Tabelas principais

| Tabela | Propósito |
|---|---|
| `chamada` | Chamadas geradas (id_turma, id_turma_disciplina, id_usuario_professor, data_aula, hora_inicio/fim, status) |
| `chamada_presenca` | Presenças: id_chamada, id_matricula, presenca, ip, responsavel |
| `material` | Materiais: tipo (`video`/`drive`/`excel`/`doc`), titulo, link, id_fk (turma), id_disciplina (0=secretaria) |
| `link` | Links externos (tipo=link): titulo, link, id_fk (turma), id_disciplina, extra. Self-healing via `LinkRepository::ensureSchema()` + `storage/migration_link.sql` |
| `visualizacao` | Confirmação de vídeo assistido: id_material, id_aluno, ativo, ip, created_at |
| `banner_aluno` | Banners do portal: banner, texto, link, id_curso, ativo |
| `turma_disciplina_professor` | Junção professor↔turma_disciplina (ou professor↔turma, schema flexível) |
| `turma_disciplina` | Vínculo turma↔disciplina com professor (legado id_usuario_professor) |
| `matricula_disciplina` | Matrícula do aluno em disciplinas da turma |
| `corpo_docente` | Professores vinculados ao curso (id_curso, id_usuario) |
| `turma_coordenador` | Coordenadores por turma (id_turma, id_usuario, created_at) |
| `usuarios_funcao` | Cadastro de funções (id=5 = coordenador) |
| `usuario_liga_funcao` | Vínculo usuário↔função (id_usuario, id_funcao, ativo) |
| `usuarios` | Usuários (inclui `titulacao`; `tipo` é legado, usado apenas para fallback/vínculo de função) |
| `imagem` | Fotos: tabela_fk='usuarios', id_fk=id_professor, path='assets/img/professor/...' |
| `modulo` | Módulos do painel: nome, rota, icone, menu_grupo, menu_icone, menu_grupo_ordem, menu_ordem, ativo |
| `logs_auditoria` | Auditoria: usuario_id, perfil, acao, entidade, entidade_id, ip, sucesso, created_at. Base dos recursos de aula/ranking |

## Frontend

- Bootstrap 5, tema via CSS vars em `public/assets/css/app.css`.
- JS vanilla em `public/assets/js/app.js`.
- CDN: Bootstrap Icons, AOS, Quill.js.
- Uploads → `public/assets/img/cursos/`, `public/assets/img/professor/`, `public/assets/img/banner/`.

## Menu admin

- **Dinâmico**: `app/Views/layouts/admin_menu.php` carrega `PermissaoRepository::listarModulos()` (tabela `modulo`) e monta os dropdowns por `menu_grupo`/`menu_grupo_ordem`.
- Cada item só aparece se `modulo.ativo=1`, tiver `menu_grupo` e o usuário tiver permissão explícita `consultar` na rota (`AuthService::hasExactPermission`, sem herdar do módulo pai).
- Ícones: `modulo.icone` no item e `menu_icone` no grupo. Ordenação: `menu_grupo_ordem`, `menu_ordem`, `ordem`, `nome`.
- **Grupos usuais**: Cadastros, Secretaria, Acadêmico, Asaas, Acesso, Conteúdo, Setup, Sistema.
- **Secretaria**: Tarefas, Notificações, Protocolos, Documentos, Chamadas, Relatório de Presenças, Material, Email Matrículas.
- **Conteúdo**: Sessões, Carrossel, Banner-Aluno, Notícias.
- **Acesso**: inclui `/admin/logs/recursos` (Recursos de aula), autoinserido por `PermissaoRepository::ensureModuloRecursosAula()`.
- Acesso ao `admin/db` (ferramenta DB) em `public/admin/db/` (fora do roteador MVC).
- Botão Asaas: visível para usuários com `tipo='admin'` em `usuarios`.

## Integração Asaas

- `.env`: `ASAAS_API_KEY`, `ASAAS_SANDBOX`, `ASAAS_WEBHOOK_TOKEN`.
- Webhook: `public/asaas-webhook.php` (físico, fora do roteador, `APP_DISABLE_SESSION`).
- Controllers: `AsaasController` (admin), `WebhookController` (callbacks).

## Storage (Google Drive)

- `.env`: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`.
- Arquitetura: `StorageService` (fachada) → `StorageProviderInterface` → `GoogleDriveProvider` → `GoogleDriveService` (API) + `GoogleOAuthService` (auth).
- Admin: `/admin/storage` (conectar, desconectar, criar estrutura de pastas).
- Banco: `integracao_google`, `documento`.
- Materiais usam `StorageService` para upload de PDFs, Excel e DOC ao Drive.

## Módulo Chamadas (admin + aluno)

- **Admin**: CRUD (`/admin/chamadas`, `/admin/chamadas/novo`), status inline (ABERTA/FECHADA/CANCELADA), relatório por turma (`/admin/chamadas/relatorio`) com export CSV.
- **Aluno**: presença via `chamada_presenca` (id_chamada, id_matricula, presenca, ip, responsavel='aluno'), com restrição de horário (hora_inicio/fim) e modal de confirmação.
- **Dashboard do aluno**: exibe chamada aberta do dia (com botões Presente/Ausente/Justificada) e calendário de aulas.
- `turma_disciplina_professor`: tabela de junção com schema flexível (detecta `id_turma` vs `id_turma_disciplina` via `SHOW COLUMNS`).
- Presence only registered by student (no auto-insert on chamada creation).

## Módulo Material (staff com permissão)

- `/admin/material` → listagem agrupada por turma, filtro por turma ativa.
- `/admin/material/novo` → seleção do tipo (Vídeo/PDF/**Excel**/**DOC**/**Link**) → turma → disciplina (Secretaria=0 ou vinculada) → campos → upload.
- **PDF/Excel/DOC**: upload via `StorageService` ao Google Drive; `tipo` no banco: `drive` (PDF), `excel` (XLS/XLSX/CSV) e `doc` (DOC/DOCX). Limite de 20MB.
- **Vídeo**: insert direto em `material`, tipo `video`. **Link**: insert na tabela `link` (`LinkRepository`), mesmo fluxo turma/disciplina (não passa por `material`).
- Listagens admin, turma e portal misturam `material` + `link` com badges/ícones por tipo (`excel` verde, `doc` azul, `link` verde, `drive` azul, `video` vermelho).
- Editar/deletar de link usa `tabela=link` (query/hidden input). `material.id_fk` = turma, `material.id_disciplina` = disciplina (0 = geral/secretaria).
- Acesso via permissão `editar` em `/admin/material`, com escopo por perfil:
  - **admin**: todas as turmas/materiais.
  - **coordenador**: somente turmas vinculadas em `turma_coordenador`.
  - **professor**: somente pares turma+disciplina vinculados em `turma_disciplina_professor` (fallback legado em `turma_disciplina.id_usuario_professor`); não acessa material geral/Secretaria (`id_disciplina=0`).
  - **demais perfis staff** com permissão: sem restrição adicional.
- O escopo é calculado em `MaterialController::escopoAcesso()` e aplicado na listagem, select de turmas, AJAX de disciplinas e nas ações criar/editar/remover; há validação server-side em todas as operações.
- A view recebe `permite_secretaria` por turma; o select de disciplina mostra “Secretaria” apenas quando o usuário pode publicar material geral naquela turma.

## Módulo Recursos de Aula (admin/staff)

- `Admin\RecursoAulaController`; rotas `/admin/logs/recursos` (lista), `/admin/logs/recursos/aluno` e `/admin/logs/recursos/ranking`.
- Exige permissão explícita `consultar` em `/admin/logs/recursos` (não herda de `/admin/logs`); link hard-coded em `logs/index.php` também é condicional.
- Filtro de tipo: `todos`, `video` (padrão), `drive` (inclui PDF legado), `excel`, `doc`; lista apenas `material.ativo=1`.
- Acessos vêm de `logs_auditoria` (`perfil='aluno'`, `acao='visualizar'`, `sucesso=1`, `entidade IN ('video','drive')`).
- Lista por recurso exibe alunos, total de visualizações, primeira/última data e localização do IP (`IpLocationService`); totais de acessos e alunos únicos no topo.
- **Por aluno** (`?aluno_id=`): recursos acessados, datas, IP/localização e coluna **Visualizado** via `EXISTS` em `visualizacao`.
- **Ranking**: somente `alunos.ativo=1`; ordena por total de acessos e recursos distintos. Renderizado com barras horizontais.

## Módulo Documentos (Secretaria)

- Admin: `/admin/documentos` (lista + filtro por turma), `/admin/documentos/novo` (upload) → `DocumentoController` (`GRUPO_SECRETARIA = 7`).
- Salva via `StorageService::upload(..., id_grupo=7, ...)` (pasta do aluno, `GROUP_ALUNOS=1`; grupo 7 sem `groupKey` → `root`); log via `LogService`.
- Portal: `/aluno/documentos` (aluno envia) e `/aluno/secretaria` (vê docs da secretaria, `StudentController::secretaria`, view `pages/aluno/secretaria.php`).
- Upload com overlay + barra de progresso (`XMLHttpRequest.upload.onprogress`), PDF/PNG/JPG ≤20MB.

## Módulo Protocolos

- `ProtocoloController` (aluno), `Admin\ProtocoloController`, `ProtocoloRepository`, `ProtocoloService`; views `pages/{aluno,admin}/protocolos/*`.
- Rotas `/aluno/protocolos/*` e `/admin/protocolos/*`. Mensagens ficam em `protocolo_mensagens`; sem DELETE físico.
- Abertura transacional; aluno só acessa os próprios (`id_aluno` da sessão).
- Campo **Assunto** é um `<select>` com opções fixas: Solicitação de Documentos, Assunto acadêmico, Emissão de Diploma/Certificado, Questão Financeira, Evento, Solicitação Especial, Outros assuntos.

## Módulo Notificações (admin)

- `/admin/notificacoes` (`Admin\NotificacaoController`). **Filtro padrão `ativo=1`**; botões Ativas (`?ativo=1`) / Inativas (`?ativo=0`) / Todas (`?ativo=all`).
- Coluna Ações: badge clicável Ativo (verde) / Inativo (vermelho) alterna via `POST /admin/notificacoes/alternar-ativo` (admin/operador) + `LogService`.
- `markas lida` professor via `POST /admin/notificacoes/marcar-lida`. Portal aluno: `POST /aluno/notificacoes/marcar-lida` com ícone de edição na 1ª coluna.
- Datas (`created_at`, `lida_em`) formatadas em **dd/mm/aaaa** nas views.
- Professores veem somente `ativo=1`; portal aluno filtra `n.ativo = 1`.

## Módulo Banner-Aluno

- `/admin/config/banner-aluno` (admin/operador). CRUD com upload para `public/assets/img/banner/` com prefixo `aluno_`.
- `banner_aluno`: id, banner, texto, link, id_curso, ativo.
- Portal aluno: banners exibidos full width no dashboard (abaixo dos avisos, antes dos cards).

## Módulo Notificações Email

- `/admin/config/email` → envia email via `EmailService` (SMTP Gmail da instituição).
- `chamada_presenca`: campos `ip` e `responsavel` criados via ALTER TABLE self-healing.

## Módulo Visitas (admin)

- Rotas: `/admin/visitas` (lista), `/admin/visitas/mensal`, `/admin/visitas/analytics`, `/admin/visitas/paginas`, `/admin/visitas/cursos`, `/admin/visitas/referer` → `Admin\VisitaController`.
- `VisitaService` + `VisitaRepository` sobre `visitas_paginas`; `VisitTrackerService::track()` registra em toda requisição (`App::run()`).
- Filtro de origem Brasil/Todos (`?pais=br|todos`) via partial `_filtro_pais.php`; período por `month`/`year`.
- `UserAgentParserService` classifica `device` (Mobile/Tablet/Desktop), `os` e `browser`; `App\Support\UiIconHelper` mapeia cada valor para ícone Bootstrap.
- `/admin/visitas/analytics` mostra países, cidades, dispositivos, sistemas e navegadores. A seção final **Perfil** (`analytics.php`) exibe 3 cards — Dispositivo, Sistema e Navegador — com o item mais acessado do período, ícone central estilizado (gradiente + círculo translúcido), contagem, percentual e barra de progresso.

## Portal Aluno

- **Layout**: `aluno_topo.php` (menu com Secretaria: Perfil, Endereço, **Documentos** → subitens *Aluno* `/aluno/documentos` e *Secretaria* `/aluno/secretaria`, Protocolos, divider, Chamadas, Calendário), `aluno_footer.php` (fixo ao fundo via `</main>` correto no `aluno.php`).
- **Dashboard**: documentos pendentes, aviso endereço, aviso parcela, chamada aberta, banners, cards (cursos matriculados, notificações), grid "Amplie Seus Conhecimentos", notícias, rodapé com horário do servidor.
- **Chamadas**: página dedicada (`/aluno/chamadas`) com chamada aberta + histórico completo (presença e ausentes futuras = "Agendada").
- **Calendário**: grade mensal por ano (apenas meses com aulas), dias de aula em badge preto, indicadores de presença/ausência (cores do sistema) — aulas futuras sem indicador.
- **Vídeo**: `/aluno/video?id=` registra acesso em `logs_auditoria` (`acao='visualizar'`, `entidade='video'`). Botão **Marcar vídeo como visto** → `POST /aluno/video/confirmar`: idempotente, insere em `visualizacao` (`ativo=1`, IP) e log `confirmar`.
- **Professor**: foto via tabela `imagem` (`tabela_fk='usuarios'`), titulação exibida, cards com nome + titulação.
- **Matérias do curso**: agrupadas por disciplina, Secretaria primeiro (id_disciplina=0), com `table-striped`.

## Login Aluno

- `aluno/login.php`: pode ser colocado em manutenção (desabilitar inputs + alerta) e reativado removendo o bloco. Estado atual: ATIVO.

## Módulo Permissões

- `PermissaoController`, `PermissaoRepository`, `PermissaoService` → rotas `/admin/permissoes/*`.

## Usuários, Funções e Dashboard

- **Fonte de verdade**: autorização, listagens e filtros usam `usuario_liga_funcao` + `usuarios_funcao`. Não usar `usuarios.tipo` para exibir função/perfil.
- `FuncaoUsuarioRepository::listarUsuariosComFuncoes()` faz `LEFT JOIN usuarios → usuario_liga_funcao → usuarios_funcao` e agrupa as funções por usuário.
- `/admin/usuarios`: filtros por busca (nome/email), **Função** (`usuario_liga_funcao.id_funcao`) e **Ativo**, com filtragem client-side; coluna **Funções** exibe os vínculos ativos.
- `/admin/usuarios/novo`: sem campo **Tipo** na view. O controller define `usuarios.tipo` como `professor` (operador logado) ou `operador` (admin), apenas para vínculo legado de função.
- `/admin/usuarios/editar`: sem campo **Tipo**. Admin edita nome, email, ativo e senha; operador edita apenas a própria senha. Email validado por formato e duplicidade (`UsuarioService::emailEmUso()`).
- `/admin` (dashboard): exibe as funções vinculadas do usuário (session `authUser.functions`), em badges, em vez do antigo `usuarios.tipo`.
- `AuthService`/`UserRepository` montam `role`, `roles`, `functions` e `permissions` a partir de `usuario_liga_funcao` (com fallback legado apenas quando não há vínculo).

## Cursos

- **Coordenação**: em `/admin/cursos/show` (seção abaixo do Corpo Docente), lista os coordenadores das turmas do curso e permite desvincular via `POST /admin/cursos/remover-coordenador`. Vínculo em `/admin/cursos/coordenador?id_curso=X` → `POST /admin/cursos/salvar-coordenador`.
- O select de coordenador lista somente usuários com `usuario_liga_funcao.id_funcao = 5` (coordenador) e `ativo = 1`; o vínculo é gravado em `turma_coordenador` (`id_turma`, `id_usuario`, `created_at`).
- `cursos.bolsa tinyint(1) DEFAULT 0` (já existe no schema). Checkbox "Exibir Bolsa de Estudos disponível" em `/admin/cursos/show` → `POST /admin/cursos/alternar-bolsa` (staff, `LogService`).
- `CursoRepository::findById` e `findBySlug` selecionam `c.bolsa`.
- Página pública `pages/curso.php`: se `bolsa=1`, imagem `assets/img/bolsa.png` logo abaixo de "Quero mais informações", link WhatsApp `wa.me/5551992975503?text=...Bolsas de Estudos`, hover `scale(1.2)` (com `overflow-hidden` no link).
- Rodapé `layouts/footer.php` 3ª coluna: imagem `atendente-comunidade-vip.png` logo abaixo do texto do Grupo VIP, link `wa.me/5551992975503`, efeitos pulse+`scale(1.02)` (CSS `.grupo-vip-*` dentro do footer, vale em todas as páginas).

## Peculiaridades

- `App::run()` chama `VisitTrackerService::track()` em **toda requisição**.
- `.htaccess` reescreve tudo para `public/`, exceto `asaas-webhook.php`, `poc/` e `admin/db/`.
- Rota `/area-do-aluno` alias para `StudentController::dashboard`.
- `isStaff()` está duplicado como método privado em cada Admin controller. Não está no Controller base.
- `.gitignore` ignora `*.md` e `*.sql` — exceto `AGENTS.md`, que é rastreado; demais markdown e SQL não são versionados.
- CORS: `App::run()` retorna 204 para `OPTIONS /api/*` com header fixo para `magdabrazilcursos.com.br`.
- Dashboard do admin: cards Matriculados/Cursos (apenas admin/operador) ao lado de Pré-inscrições.

## Auth

- Staff: `storage/users.json` com senhas bcrypt.
- Alunos: tabela `alunos` no MySQL.
- Session helper: `App\Support\Session`.
- `turma_disciplina_professor`: schema flexível (`id_turma` ou `id_turma_disciplina`), detectado via `SHOW COLUMNS` no repositório.

## Professor (cadastro admin)

- `titulacao` em `usuarios`: campo `varchar(100)`, editável no novo/editar/perfil.
- Foto: tabela `imagem` (`tabela_fk='usuarios'`, `id_fk`=id_professor, path em `assets/img/professor/`).
- Duplicidade de email: verificada antes de criar e editar (erro amigável sem expor SQL).
