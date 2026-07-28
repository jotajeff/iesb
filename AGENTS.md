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

| Diretório         | Propósito |
|-------------------|-----------|
| `public/`         | Front controller (`index.php`), assets estáticos (CSS, JS, img, upload) |
| `bootstrap/`      | `app.php` — autoload, env, sessão, roteador |
| `config/`         | `app.php` (config), `routes.php` (rotas) |
| `app/Core/`       | Framework: App, Router, Controller, Database, Env, View |
| `app/Controllers/`| HTTP: `Admin/` (subdir), Home, Page, Auth, Student |
| `app/Services/`   | Regras de negócio |
| `app/Repositories/`| Acesso a dados (PDO MySQL ou JSON) |
| `app/Views/`      | Templates PHP puros, layouts em `layouts/` |
| `storage/`        | Schema SQL (`db_estrutura.sql`), JSON files (`users.json`, etc.) |

## Convenções

- **Rotas**: estáticas e com path params (`{slug}`, `{id}`) definidas em `config/routes.php`. Params são injetados em `$_GET`.
- **Layouts**: `base` (público), `admin` (painel staff), `aluno` (portal aluno). Terceiro argumento de `View::render()`.
- **Auth**: sessão via `App\Support\Session`. Staff (admin/operador/professor) autentica contra `storage/users.json`. Alunos autenticam via MySQL (tabela `alunos`).
- **Controller base**: `render()`, `redirect()`, `input()`, `json()`. `render()` injeta automaticamente `authUser`, `flash`, `niveisMenu`, `nivelSelecionado` em todas as views.
- **Services** instanciados nos controllers com `new`, sem DI container.
- **Código em português** (mensagens flash, nomes de views, comentários).
- **strict_types=1** + `declare()` na linha 2 obrigatório em todos os PHP.
- **Namespace `App\`** mapeia para `app/` (PSR-4).

## Banco de dados

- MySQL via PDO (`App\Core\Database::connection()`). Retorna `null` se as credenciais do `.env` não estiverem configuradas.
- Schema completo em `storage/db_estrutura.sql`.
- Migrações incrementais em `storage/migration_*.sql`.
- Tabelas: `cursos_iesb`, `turmas`, `alunos`, `matriculas`, `usuarios`, `logs_auditoria`, `tarefas`, `setores`, `modalidade`, `nivel`, `segmento`.

## Frontend

- Bootstrap 5, tema custom via CSS variables em `public/assets/css/app.css`.
- JS vanilla em `public/assets/js/app.js` (tema escuro/claro, AOS).
- CDN: Bootstrap Icons, AOS, Quill.js.
- Uploads de imagem → `public/assets/img/cursos/`.

## Dependências

- `composer install` para instalar PHPMailer.
- `vendor/` e `.env` estão no `.gitignore`.
- Arquivo `.env.example` com variáveis esperadas (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_URL`, mail vars).

## Notas

- `.gitignore` exclui `*.md` — AGENTS.md e STRUCTURE.md não são versionados.
- Não há rotinas de build ou transpilação. Edições em PHP/CSS/JS refletem imediatamente.
