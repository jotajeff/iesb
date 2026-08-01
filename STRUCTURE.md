# IESB - Estrutura do Projeto

## Visão Geral

Aplicação PHP monolítica com arquitetura MVC customizada. Sem frameworks externos (Laravel, Symfony, etc).

---

## Estrutura de Diretórios

```
iesb/
├── app/
│   ├── Controllers/          # Lógica HTTP (recebe request, retorna response)
│   │   ├── Admin/            # Controllers do painel administrativo
│   │   │   ├── AlunoController.php
│   │   │   ├── ConfigController.php
│   │   │   ├── CursoController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProfessorController.php
│   │   │   ├── TarefaController.php
│   │   │   ├── TurmaController.php
│   │   │   ├── UsuarioController.php
│   │   │   └── VisitaController.php
│   │   ├── AdminController.php      # Legacy admin (será deprecado)
│   │   ├── AuthController.php       # Login/logout
│   │   ├── HomeController.php       # Página inicial pública
│   │   ├── PageController.php       # Páginas estáticas (sobre, cursos, etc)
│   │   └── StudentController.php    # Portal do aluno
│   │
│   ├── Core/                 # Framework MVC customizado
│   │   ├── App.php           # Bootstrap da aplicação
│   │   ├── Controller.php    # Classe base dos controllers
│   │   ├── Database.php      # Conexão PDO (singleton)
│   │   ├── Env.php           # Parser de variáveis de ambiente
│   │   ├── Router.php        # Roteamento de URLs
│   │   └── View.php          # Renderização de templates
│   │
│   ├── Helpers/              # Funções auxiliares
│   │   ├── LogHelper.php
│   │   └── MaterialHelper.php
│   │
│   ├── Repositories/         # Camada de acesso a dados (SQL/JSON)
│   │   ├── AlunoRepository.php
│   │   ├── CarouselRepository.php
│   │   ├── CommentRepository.php
│   │   ├── ConfigRepository.php
│   │   ├── CourseRepository.php
│   │   ├── CursoRepository.php
│   │   ├── DashboardRepository.php
│   │   ├── EnderecoRepository.php
│   │   ├── EnrollmentRepository.php
│   │   ├── JsonRepository.php
│   │   ├── LogRepository.php
│   │   ├── TarefaRepository.php
│   │   ├── TurmaRepository.php
│   │   ├── UserRepository.php
│   │   ├── UsuarioRepository.php
│   │   └── VisitaRepository.php
│   │
│   ├── Services/             # Camada de negócio (regras, validações)
│   │   ├── AlunoService.php
│   │   ├── AuthService.php
│   │   ├── CarouselService.php
│   │   ├── CommentService.php
│   │   ├── ConfigService.php
│   │   ├── CourseService.php
│   │   ├── CursoService.php
│   │   ├── DashboardService.php
│   │   ├── EmailService.php
│   │   ├── EnrollmentService.php
│   │   ├── IpLocationService.php
│   │   ├── LogService.php
│   │   ├── TarefaService.php
│   │   ├── TurmaService.php
│   │   ├── UserAgentParserService.php
│   │   ├── UsuarioService.php
│   │   ├── VisitaService.php
│   │   └── VisitTrackerService.php
│   │
│   ├── Support/              # Utilitários transversais
│   │   ├── Session.php       # Gerenciamento de sessão
│   │   └── UiIconHelper.php  # Ícones Bootstrap
│   │
│   └── Views/                # Templates PHP
│       ├── layouts/          # Layouts base (admin, aluno, público)
│       │   ├── admin.php
│       │   ├── admin_menu.php
│       │   ├── admin_topo.php
│       │   ├── admin_footer.php
│       │   ├── aluno.php
│       │   ├── aluno_topo.php
│       │   ├── aluno_footer.php
│       │   ├── base.php      # Layout público
│       │   ├── menu.php
│       │   ├── topo.php
│       │   └── footer.php
│       └── pages/            # Páginas da aplicação
│           ├── home.php
│           ├── sobre.php
│           ├── cursos.php
│           ├── admin/        # Páginas do painel admin
│           └── aluno/        # Páginas do portal do aluno
│
├── bootstrap/                # Inicialização da aplicação
│   └── app.php               # Autoload, env, sessão, rotas
│
├── config/                   # Configurações
│   ├── app.php               # Configurações gerais
│   └── routes.php            # Definição de rotas
│
├── public/                   # Document root (acessível via web)
│   ├── index.php             # Front controller
│   └── assets/               # CSS, JS, imagens, uploads
│       ├── css/app.css
│       ├── js/app.js
│       └── img/
│
├── storage/                  # Dados e schemas
│   ├── db_estrutura.sql      # Schema completo do banco
│   ├── migration_*.sql       # Migrações incrementais
│   ├── users.json            # Usuários (JSON)
│   ├── courses.json          # Cursos (JSON)
│   └── enrollments.json      # Matrículas (JSON)
│
├── vendor/                   # Dependências Composer (PHPMailer)
│
├── composer.json
├── .env                      # Variáveis de ambiente (não versionado)
└── .env.example              # Template de variáveis
```

---

## Camadas e Responsabilidades

### Controllers
- Recebem requests HTTP
- Validam input básico
- Delegam para Services
- Retornam responses (render, redirect, json)

### Services
- Contêm lógica de negócio
- Orquestram múltiplos Repositories quando necessário
- Validam regras de domínio
- **Não acessam $_POST, $_GET, $_SESSION diretamente**

### Repositories
- Acesso a dados (SQL via PDO ou JSON)
- Métodos CRUD específicos por entidade
- **Não contêm lógica de negócio**
- Retornam arrays associativos (não objetos/DTOs)

### Views
- Templates PHP puros
- Recebem dados via `$data` do controller
- Usam layouts via `View::render($view, $data, $layout)`

---

## Padrões e Convenções

### Nomenclatura
- **Controllers**: `XxxController.php` (PascalCase)
- **Services**: `XxxService.php` (PascalCase)
- **Repositories**: `XxxRepository.php` (PascalCase)
- **Views**: `kebab-case.php` ou `snake_case.php`
- **Métodos**: `camelCase()`
- **Variáveis**: `$camelCase`

### Estrutura de Métodos em Repositories
```php
public function list(int $limit = 200): array
public function findById(int $id): ?array
public function create(array $data): int
public function update(int $id, array $data): void
public function delete(int $id): void
```

### Estrutura de Métodos em Services
```php
public function __construct(
    private readonly XxxRepository $repository = new XxxRepository()
) {}

public function all(): array
public function find(int $id): ?array
public function create(array $data): int
public function update(int $id, array $data): void
public function delete(int $id): void
```

### Controllers
```php
public function __construct(
    private XxxService $xxxService = new XxxService()
) {}

public function index(): void
public function show(int $id): void
public function store(): void
public function update(int $id): void
public function destroy(int $id): void
```

---

## Banco de Dados

### Tabelas Principais (MySQL)
- `alunos` - Alunos matriculados
- `cursos` - Cursos oferecidos
- `turmas` - Turmas dos cursos
- `matriculas` - Relação aluno-turma
- `usuarios` - Usuários do sistema (admin, professor, operador)
- `logs_auditoria` - Logs de ações
- `visitas_paginas` - Analytics de visitas
- `carousel` - Carrosséis da home
- `carousel_item` - Itens do carrossel
- `tarefas` - Tarefas do sistema
- `setores` - Setores organizacionais
- `modalidade` - Modalidades de ensino
- `segmento` - Segmentos de cursos
- `nivel` - Níveis de ensino

### Dados em JSON (storage/)
- `users.json` - Usuários legados
- `courses.json` - Cursos legados
- `enrollments.json` - Matrículas legadas

---

## Autenticação e Autorização

### Sessões
- `$_SESSION['user']` - Usuário autenticado (array com id, nome, email, role)
- Roles: `admin`, `professor`, `operador`, `aluno`

### Middlewares (implementados nos controllers)
```php
if (!$this->isStaff()) {
    $this->redirect('/admin/login');
}
```

---

## Rotas

Definidas em `config/routes.php`:

```php
// Exemplo
$router->get('/', [HomeController::class, 'index']);
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->post('/admin/aluno/salvar', [AlunoController::class, 'store']);
```

### Convenção de URLs
- `/admin/*` - Painel administrativo
- `/aluno/*` - Portal do aluno
- `/` - Páginas públicas

---

## Variáveis de Ambiente (.env)

```env
DB_HOST=localhost
DB_NAME=iesb
DB_USER=root
DB_PASS=
DB_PORT=3306

APP_ENV=local
APP_DEBUG=true

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=
MAIL_PASS=
```

---

## Dependências

### Composer
- `phpmailer/phpmailer` - Envio de emails

### Frontend (CDN)
- Bootstrap 5.3
- Bootstrap Icons
- AOS (Animate On Scroll)
- Quill.js (editor rich text)

---

## Deploy

### Requisitos
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Composer
- Extensões PHP: pdo_mysql, mbstring, json

### Instalação
```bash
composer install
cp .env.example .env
# Configurar .env
php storage/db_estrutura.sql  # Importar schema
```

### Servidor de Desenvolvimento
```bash
php -S localhost:8000 -t public
```

---

## Notas de Manutenção

### Adicionar Novo Recurso
1. Criar Repository com métodos CRUD
2. Criar Service orquestrando o Repository
3. Criar Controller usando o Service
4. Criar Views em `app/Views/pages/`
5. Adicionar rotas em `config/routes.php`

### Refatoração em Andamento
- **AdminController.php** (legacy) será deprecado em favor dos controllers específicos em `Admin/`
- Migração de `users.json`, `courses.json`, `enrollments.json` para MySQL
- Padronização de nomenclatura (alguns arquivos ainda usam nomes inconsistentes)

### Problemas Conhecidos
- Alguns controllers ainda instanciam Services diretamente no método (deveria ser no construtor)
- Views admin têm código duplicado (modal, tabelas) que poderia ser extraído para partials
- Falta validação de CSRF em formulários
- Falta paginação em algumas listagens grandes

---

## Atualizações

| Data | Autor | Mudança |
|------|-------|---------|
| 2026-06-17 | refatoração | Separados AdminRepository/AdminService em módulos específicos |
| 2026-06-17 | carousel | Adicionado campo `link` na tabela `carousel` |
| 2026-06-17 | home | Carrossel público com visual Instagram |

---

## Contato e Suporte

- Documentação interna: `AGENTS.md` (para agentes IA)
- Issues: registrar no repositório do projeto
