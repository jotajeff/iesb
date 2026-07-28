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

### Padronização de tabelas

Conforme `padronizacao.md`, toda tabela **deve** seguir:

| Regra | Padrão | Nunca usar |
|---|---|---|
| **Chave primária** | `id INT AUTO_INCREMENT PRIMARY KEY` | — |
| **Status** | `ativo TINYINT(1) NOT NULL DEFAULT 1` | `CHAR(1)`, `ENUM`, `'S'/'N'`, `'A'/'I'` |
| **Datas** | `created_at DATETIME DEFAULT CURRENT_TIMESTAMP` e `updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | `criado_em`, `atualizado_em`, `data_cadastro`, `data` |
| **Tabelas** | Sempre no **singular** (`curso`, `turma`, `matricula`) | Plurais (`cursos`, `turmas`) |
| **Campos FK** | `id_usuario`, `id_curso`, `id_turma` | `usuario_id`, `cursoID`, `codCurso` |
| **Exclusão** | Soft delete: `UPDATE tabela SET ativo = 0` | `DELETE` físico |

**Modelo obrigatório para novas tabelas:**

```sql
CREATE TABLE exemplo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ...
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**No código PHP:**
- Valores de `ativo`: `1` (ativo) e `0` (inativo) — nunca `'S'`/`'N'`
- Queries devem usar `created_at`/`updated_at`, nunca `criado_em`/`atualizado_em`
- Campos FK seguem prefixo `id_` — ex: `$aluno['id_curso']`, nunca `$aluno['curso_id']`

## Frontend

- Bootstrap 5, tema custom via CSS variables em `public/assets/css/app.css`.
- JS vanilla em `public/assets/js/app.js` (tema escuro/claro, AOS).
- CDN: Bootstrap Icons, AOS, Quill.js.
- Uploads de imagem → `public/assets/img/cursos/`.

## SEO & Meta Tags

### robots.txt

Arquivo estático em `public/robots.txt`. Bloqueia `/admin`, `/aluno`, `/area-do-aluno`, `/asaas-webhook.php`. Aponta para `sitemap.xml`.

### OpenGraph

Meta tags OpenGraph no layout `app/Views/layouts/topo.php` (linhas 11-17). Variáveis suportadas via views: `$ogTitle`, `$ogDescription`, `$ogImage`, `$ogUrl`, `$ogType`. Defaults: título = `IESB`, descrição = texto fixo, imagem = `logo-main.png`, tipo = `website`, locale = `pt_BR`, site_name = `IESB - Inteligência Educacional Souza Brazil`.

Não há Twitter Cards configurados.

### Schema.org (JSON-LD)

Dois schemas fixos em `topo.php` (linhas 19-38):
- `EducationalOrganization` — dados da instituição (nome, url, logo, descrição, endereço).
- `WebSite` — nome e URL do site.

Suporte a schemas extras via variável `$schema` (array): controllers podem passar `$schema` para views e o layout injeta cada item como `<script type="application/ld+json">`. Nenhum controller usa isso no momento.

### sitemap.xml

Gerado dinamicamente por `PageController::sitemap()` (rota `/sitemap.xml`, `app/Controllers/PageController.php:629`). Inclui:
- Páginas estáticas fixas (`/`, `/sobre`, `/cursos`, `/eventos`, `/parcerias`, `/noticias`, `/privacidade`, `/pre-inscricao`).
- Cursos publicados (`/curso/{slug}`).
- Notícias publicadas (`/noticias/{slug}`).

Retorna XML direto (`Content-Type: application/xml`), sem template.

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
