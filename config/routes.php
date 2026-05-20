<?php

declare(strict_types=1);

use App\Controllers\AdminController;
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

    $router->get('/admin/login', [AuthController::class, 'adminLoginForm']);
    $router->post('/admin/login', [AuthController::class, 'adminLogin']);
    $router->get('/admin', [AdminController::class, 'dashboard']);
    $router->get('/admin/logs', [AdminController::class, 'logs']);
    $router->get('/admin/logs/', [AdminController::class, 'logs']);
    $router->get('/admin/visitas', [AdminController::class, 'visits']);
    $router->get('/admin/visitas/', [AdminController::class, 'visits']);
    $router->post('/admin/cursos', [AdminController::class, 'createCourse']);

    $router->get('/aluno/login', [AuthController::class, 'alunoLoginForm']);
    $router->post('/aluno/login', [AuthController::class, 'alunoLogin']);
    $router->get('/aluno', [StudentController::class, 'dashboard']);
    $router->post('/aluno/matricular', [StudentController::class, 'enroll']);

    $router->get('/area-do-aluno', [StudentController::class, 'dashboard']);
    $router->post('/logout', [AuthController::class, 'logout']);
};
