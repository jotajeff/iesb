# IESB — Guia para Agentes OpenCode

## Stack

PHP 8.0+, MySQL, Bootstrap 5, vanilla JS, PHPMailer. Framework MVC **custom** (`app/Core/`), monolítico (sem npm, sem build step).

## Comandos

```bash
php -S localhost:8000 -t public   # servidor de desenvolvimento
composer install                   # instalar dependências (PHPMailer)
```

Não há testes, linting, typecheck ou CI configurados.

## Estrutura

| Diretório | Propósito |
|---|---|
| `public/` | Front controller (`index.php`), assets estáticos, uploads |
| `bootstrap/app.php` | Autoload (PSR-4 + fallback manual), `.env`, sessão, rotas |
| `config/` | `app.php` (config), `routes.php` (rotas) |
| `app/Core/` | Framework: App, Router, Controller, Database, Env, View |
| `app/Controllers/` | HTTP: `Admin/` (14 controllers), Auth, Home, Page, Student, Webhook |
| `app/Services/` | Regras de negócio |
| `app/Repositories/` | Acesso a dados (PDO MySQL) |
| `app/Views/` | Templates PHP puros, layouts em `layouts/` |
| `storage/` | Schema SQL, migrações SQL, JSON files legados, logs |
| `vendor/` | PHPMailer (única dep Composer) |

## Convenções

- **Rotas**: estáticas e com path params (`{slug}`, `{id}`) em `config/routes.php`. Params são injetados em `$_GET`.
- **Layouts**: `base` (público), `admin` (painel staff), `aluno` (portal aluno). Terceiro argumento de `View::render()`.
- **Auth**: sessão via `App\Support\Session` (`$_SESSION['user']` com `id`, `name`, `email`, `role`). Staff (admin/operador/professor) autentica via `storage/users.json` (bcrypt). Alunos autenticam via MySQL (`alunos` tabela).
- **Roles**: `admin`, `professor`, `operador`, `aluno`. `isStaff()` = admin || professor || operador — **não está no Controller base**, é duplicado como método privado em cada Admin controller.
- **Controller base**: `render()`, `redirect()`, `input()`, `json()`. `render()` injeta automaticamente `authUser`, `flash`, `niveisMenu`, `nivelSelecionado` em todas as views.
- **Services** instanciados nos controllers com `new`, sem DI container.
- **Código em português** (mensagens flash, nomes de views, comentários).
- **`declare(strict_types=1)`** linha 3 em todos os PHP.
- **Namespace `App\`** mapeia para `app/` (PSR-4). `bootstrap/app.php` tem fallback autoload manual se `vendor/autoload.php` não existir.

## Banco de dados

- MySQL via PDO (`App\Core\Database::connection()`). Retorna `null` se credenciais do `.env` não estiverem configuradas.
- Schema completo em `storage/db_estrutura.sql`. Migrações incrementais em `storage/migration_*.sql`.
- Dados legados em JSON (`storage/users.json`, `courses.json`, `enrollments.json`).

## Frontend

- Bootstrap 5, tema custom via CSS variables em `public/assets/css/app.css`.
- JS vanilla em `public/assets/js/app.js` (tema escuro/claro, AOS).
- CDN: Bootstrap Icons, AOS, Quill.js.
- Uploads de imagem → `public/assets/img/cursos/`.

## Dependências & Ambiente

- `composer install` para instalar PHPMailer.
- `vendor/` e `.env` no `.gitignore`. `.env.example` com variáveis esperadas.
- `.env` requer: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_URL`, `ASAAS_API_KEY`, `ASAAS_SANDBOX`, `ASAAS_WEBHOOK_TOKEN`.

## Integração Asaas (pagamento)

- Ambiente definido por `ASAAS_SANDBOX=true/false` no `.env`.
- Webhook físico em `public/asaas-webhook.php` (fora do roteador, com `APP_DISABLE_SESSION`). `.htaccess` faz rewrite direto para ele.
- Token de validação: `ASAAS_WEBHOOK_TOKEN`.
- Controllers: `AsaasController` (admin), `WebhookController` (processa callbacks).

## Peculiaridades

- `App::run()` chama `VisitTrackerService::track()` em **toda requisição** (registra analytics de visita).
- `.htaccess` na raiz faz rewrite de tudo para `public/`, exceto `asaas-webhook.php`.
- Rota `/area-do-aluno` é alias para `StudentController::dashboard`.
- `isStaff()` está duplicado em cada Admin controller (14 cópias). Não depende do Controller base.
