# IESB — Guia para Agentes

## Stack

PHP 8.0+, MySQL, Bootstrap 5, vanilla JS, PHPMailer. Framework MVC custom (`app/Core/`), monolítico (sem npm, sem build step).

## Comandos

```bash
php -S localhost:8000 -t public   # servidor de dev
composer install                   # instalar dependências (PHPMailer)
```

Não há testes, linting, typecheck ou CI.

## Estrutura

| Diretório | Propósito |
|---|---|
| `public/` | Front controller (`index.php`), assets, uploads |
| `bootstrap/app.php` | Autoload (PSR-4 + fallback), `.env`, sessão, rotas |
| `config/` | `app.php` (config), `routes.php` (rotas) |
| `app/Core/` | Framework: App, Router, Controller, Database, Env, View |
| `app/Controllers/Admin/` | 15 controllers do painel staff |
| `app/Services/` | Regras de negócio |
| `app/Repositories/` | Acesso a dados (PDO MySQL) |
| `app/Views/` | Templates PHP, layouts em `layouts/` |
| `storage/` | Schema SQL, migrações, JSON legados, logs |
| `vendor/` | PHPMailer (única dep Composer) |

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

### Padronização obrigatória (padronizacao.md)

| Regra | Padão | Nunca |
|---|---|---|
| PK | `id INT AUTO_INCREMENT PRIMARY KEY` | — |
| Status | `ativo TINYINT(1) NOT NULL DEFAULT 1` | `CHAR(1)`, `ENUM`, `'S'/'N'` |
| Datas | `created_at`, `updated_at` (DATETIME) | `criado_em`, `data_cadastro` |
| Tabelas | Singular (`curso`, `turma`) | Plurais |
| FK | `id_usuario`, `id_curso` | `usuario_id`, `cursoID` |
| Exclusão | Soft delete: `UPDATE SET ativo = 0` | `DELETE` |

## Frontend

- Bootstrap 5, tema via CSS vars em `public/assets/css/app.css`.
- JS vanilla em `public/assets/js/app.js`.
- CDN: Bootstrap Icons, AOS, Quill.js.
- Uploads → `public/assets/img/cursos/`.

## Integração Asaas

- `.env`: `ASAAS_API_KEY`, `ASAAS_SANDBOX`, `ASAAS_WEBHOOK_TOKEN`.
- Webhook: `public/asaas-webhook.php` (físico, fora do roteador, `APP_DISABLE_SESSION`).
- Controllers: `AsaasController` (admin), `WebhookController` (callbacks).

## Peculiaridades

- `App::run()` chama `VisitTrackerService::track()` em **toda requisição**.
- `.htaccess` reescreve tudo para `public/`, exceto `asaas-webhook.php`.
- Rota `/area-do-aluno` alias para `StudentController::dashboard`.
- `isStaff()` está duplicado como método privado em cada Admin controller (15 cópias). Não está no Controller base.
- `.gitignore` ignora `*.md` e `*.sql` — arquivos markdown e SQL não são versionados.
- CORS: `App::run()` retorna 204 para `OPTIONS /api/*` com header fixo para `magdabrazilcursos.com.br`.

## Auth

- Staff: `storage/users.json` com senhas bcrypt.
- Alunos: tabela `alunos` no MySQL.
- Session helper: `App\Support\Session`.
