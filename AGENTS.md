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
- **Roles**: `admin`, `professor`, `operador`, `aluno`. `isStaff()` = admin || professor || operador.
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
| `material` | Materiais: tipo (video/drive), titulo, link, id_fk (turma), id_disciplina (0=secretaria) |
| `banner_aluno` | Banners do portal: banner, texto, link, id_curso, ativo |
| `turma_disciplina_professor` | Junção professor↔turma_disciplina (ou professor↔turma, schema flexível) |
| `turma_disciplina` | Vínculo turma↔disciplina com professor (legado id_usuario_professor) |
| `matricula_disciplina` | Matrícula do aluno em disciplinas da turma |
| `corpo_docente` | Professores vinculados ao curso (id_curso, id_usuario) |
| `usuarios` | Usuários (inclui campo `titulacao`) |
| `imagem` | Fotos: tabela_fk='usuarios', id_fk=id_professor, path='assets/img/professor/...' |

## Frontend

- Bootstrap 5, tema via CSS vars em `public/assets/css/app.css`.
- JS vanilla em `public/assets/js/app.js`.
- CDN: Bootstrap Icons, AOS, Quill.js.
- Uploads → `public/assets/img/cursos/`, `public/assets/img/professor/`, `public/assets/img/banner/`.

## Menu admin

- **Dropdowns**: Cadastros, Secretaria, Acadêmico, Asaas, Acesso, Conteúdo, Setup, Sistema.
- **Secretaria**: Tarefas, Notificações, Chamadas, Relatório de Presenças, Material, Email Matrículas.
- **Conteúdo**: Sessões, Carrossel, Banner-Aluno, Notícias.
- Acesso ao `admin/db` (ferramenta DB) em `public/admin/db/` (fora do roteador MVC).
- Botão Asaas: visible for users with `tipo='admin'` in `usuarios`.

## Integração Asaas

- `.env`: `ASAAS_API_KEY`, `ASAAS_SANDBOX`, `ASAAS_WEBHOOK_TOKEN`.
- Webhook: `public/asaas-webhook.php` (físico, fora do roteador, `APP_DISABLE_SESSION`).
- Controllers: `AsaasController` (admin), `WebhookController` (callbacks).

## Storage (Google Drive)

- `.env`: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`.
- Arquitetura: `StorageService` (fachada) → `StorageProviderInterface` → `GoogleDriveProvider` → `GoogleDriveService` (API) + `GoogleOAuthService` (auth).
- Admin: `/admin/storage` (conectar, desconectar, criar estrutura de pastas).
- Banco: `integracao_google`, `documento`.
- Materiais usam `StorageService` para upload de PDFs ao Drive.

## Módulo Chamadas (admin + aluno)

- **Admin**: CRUD (`/admin/chamadas`, `/admin/chamadas/novo`), status inline (ABERTA/FECHADA/CANCELADA), relatório por turma (`/admin/chamadas/relatorio`) com export CSV.
- **Aluno**: presença via `chamada_presenca` (id_chamada, id_matricula, presenca, ip, responsavel='aluno'), com restrição de horário (hora_inicio/fim) e modal de confirmação.
- **Dashboard do aluno**: exibe chamada aberta do dia (com botões Presente/Ausente/Justificada) e calendário de aulas.
- `turma_disciplina_professor`: tabela de junção com schema flexível (detecta `id_turma` vs `id_turma_disciplina` via `SHOW COLUMNS`).
- Presence only registered by student (no auto-insert on chamada creation).

## Módulo Material (admin/operador)

- `/admin/material` → listagem agrupada por turma, filtro por turma ativa.
- `/admin/material/novo` → tipo (Vídeo/PDF) → turma → disciplina (Secretaria=0 ou vinculada) → campos → upload.
- PDF: upload via `StorageService` ao Google Drive, tipo 'drive'. Vídeo: insert direto, tipo 'video'.
- `material.id_fk` = turma, `material.id_disciplina` = disciplina (0 = geral/secretaria).
- Acesso: admin ou operador.

## Módulo Banner-Aluno

- `/admin/config/banner-aluno` (admin/operador). CRUD com upload para `public/assets/img/banner/` com prefixo `aluno_`.
- `banner_aluno`: id, banner, texto, link, id_curso, ativo.
- Portal aluno: banners exibidos full width no dashboard (abaixo dos avisos, antes dos cards).

## Módulo Notificações Email

- `/admin/config/email` → envia email via `EmailService` (SMTP Gmail da instituição).
- `chamada_presenca`: campos `ip` e `responsavel` criados via ALTER TABLE self-healing.

## Portal Aluno

- **Layout**: `aluno_topo.php` (menu com Secretaria: Perfil, Endereço, Documentos, divider, Chamadas, Calendário), `aluno_footer.php` (fixo ao fundo via `</main>` correto no `aluno.php`).
- **Dashboard**: documentos pendentes, aviso endereço, aviso parcela, chamada aberta, banners, cards (cursos matriculados, notificações), grid "Amplie Seus Conhecimentos", notícias, rodapé com horário do servidor.
- **Chamadas**: página dedicada (`/aluno/chamadas`) com chamada aberta + histórico completo (presença e ausentes futuras = "Agendada").
- **Calendário**: grade mensal por ano (apenas meses com aulas), dias de aula em badge preto, indicadores de presença/ausência (cores do sistema) — aulas futuras sem indicador.
- **Professor**: foto via tabela `imagem` (`tabela_fk='usuarios'`), titulação exibida, cards com nome + titulação.
- **Matérias do curso**: agrupadas por disciplina, Secretaria primeiro (id_disciplina=0), com `table-striped`.

## Login Aluno

- `aluno/login.php`: pode ser colocado em manutenção (desabilitar inputs + alerta) e reativado removendo o bloco. Estado atual: ATIVO.

## Módulo Permissões

- `PermissaoController`, `PermissaoRepository`, `PermissaoService` → rotas `/admin/permissoes/*`.

## Peculiaridades

- `App::run()` chama `VisitTrackerService::track()` em **toda requisição**.
- `.htaccess` reescreve tudo para `public/`, exceto `asaas-webhook.php`, `poc/` e `admin/db/`.
- Rota `/area-do-aluno` alias para `StudentController::dashboard`.
- `isStaff()` está duplicado como método privado em cada Admin controller. Não está no Controller base.
- `.gitignore` ignora `*.md` e `*.sql` — arquivos markdown e SQL não são versionados.
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
- Duplicidade de email: verificada antes de criar (erro amigável sem expor SQL).
