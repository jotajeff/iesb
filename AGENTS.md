# IESB — Guia para Agentes OpenCode

## Stack

PHP 8.0+, MySQL, Bootstrap 5, vanilla JS, PHPMailer. Framework MVC **custom** (`app/Core/`), monolítico (sem npm, sem build step).

## Servidor de desenvolvimento

```bash
php -S localhost:8000 -t public
```

## Estrutura

| Diretório         | Propósito                                   |
|-------------------|---------------------------------------------|
| `public/`         | Front controller, assets estáticos (CSS, JS, img, upload) |
| `bootstrap/`      | `app.php` — autoload, env, sessão, roteador |
| `config/`         | `app.php` (config), `routes.php` (rotas)    |
| `app/Core/`       | Framework: App, Router, Controller, Database, Env, View |
| `app/Controllers/`| Lógica HTTP: `Admin/` (subdir), Home, Page, Auth, Student |
| `app/Services/`   | Regras de negócio                           |
| `app/Repositories/`| Acesso a dados (PDO MySQL ou JSON)         |
| `app/Views/`      | Templates PHP puros, layouts em `layouts/`  |
| `storage/`        | Schema SQL (`db_estrutura.sql`), JSON files (`users.json`, `courses.json`, `enrollments.json`) |

## Convenções e regras importantes

- **Rotas**: todas estáticas via query string (`?id=X`). Não há parâmetros de path. Definidas em `config/routes.php`.
- **Layouts**: `base` (público), `admin` (painel staff), `aluno` (portal aluno). Passados como terceiro argumento de `View::render()`.
- **Auth**: sessão (`$_SESSION`) via `App\Support\Session`. Staff (admin/operador/professor) autentica contra `storage/users.json`. Alunos autenticam via MySQL (tabela `alunos`).
- **Controller base**: `App\Core\Controller` fornece `render()`, `redirect()`, `input()`. Use `$this->render(view, data, layout)`.
- **Services são instanciados nos controllers** com `new`, sem DI container.
- **Código em português** (mensagens flash, nomes de views, comentários). Manter consistência.
- **strict_types=1** obrigatório em todos os arquivos PHP.
- **declare()** sempre na linha 2, após `<?php`.
- **Namespace `App\`** mapeia para `app/` (PSR-4 no `composer.json`).

## Banco de dados

- MySQL via PDO (`App\Core\Database::connection()`).
- Schema completo em `storage/db_estrutura.sql`.
- Migrações incrementais em `storage/migration_*.sql`.
- Tabelas principais: `cursos_iesb`, `turmas`, `alunos`, `matriculas`, `usuarios`, `logs_auditoria`, `tarefas`, `setores`, `modalidade`, `nivel`, `segmento`.

## Frontend

- Bootstrap 5, tema custom via CSS variables em `public/assets/css/app.css`.
- JS vanilla em `public/assets/js/app.js` (tema escuro/claro, AOS).
- Bootstrap Icons e bibliotecas CDN carregadas nos layouts.
- Uploads de imagem vão para `public/assets/img/cursos/`.

## Dependências

- `composer install` para instalar PHPMailer.
- `vendor/` e `.env` estão no `.gitignore`.
- Arquivo `.env.example` com variáveis esperadas.
