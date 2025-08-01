<?php

/**
 * Arquivo de Rotas da Aplicação Web
 *
 * Aqui é onde você pode registrar todas as rotas para sua aplicação.
 * É um lugar centralizado, semelhante ao que frameworks como o Laravel oferecem.
 */

use App\Controllers\EmpresaController;
use App\Core\Route;

// Rotas para o CRUD de Empresas
Route::get('/empresas', [EmpresaController::class, 'index']);
Route::get('/empresas/create', [EmpresaController::class, 'create']);
Route::post('/empresas', [EmpresaController::class, 'store']);
Route::get('/empresas/edit/{id}', [EmpresaController::class, 'edit']);
Route::put('/empresas/{id}', [EmpresaController::class, 'update']);
Route::delete('/empresas/delete/{id}', [EmpresaController::class, 'destroy']);

// Rota de exemplo para a página inicial
Route::get('/', function () {
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    echo '<h1>Página Inicial</h1><p>Bem-vindo ao seu framework customizado!</p><a href="' . $basePath . '/empresas">Ver Empresas</a>';
});