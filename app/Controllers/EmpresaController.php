<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Empresa;

/**
 * Controlador para gerenciar as operações CRUD de Empresas.
 */
class EmpresaController extends Controller
{
    private Empresa $empresaModel;

    /**
     * Construtor do controller.
     *
     * Inicializa o model da Empresa e chama o construtor pai.
     */
    public function __construct()
    {
        parent::__construct();
        $this->empresaModel = new Empresa();
    }

    /**
     * Exibe a lista de todas as empresas.
     */
    public function index(): void
    {
        $empresas = $this->empresaModel->all();
        $this->view('empresa.index', ['empresas' => $empresas]);
    }

    /**
     * Exibe o formulário para criar uma nova empresa.
     */
    public function create(): void
    {
        $this->view('empresa.create');
    }

    /**
     * Salva uma nova empresa no banco de dados.
     */
    public function store(): void
    {
        $data = Request::all();

        $validator = new Validator();
        $validator->validate($data, [
            'nome' => 'required|max:191',
            'cnpj' => 'required|max:18',
            'email' => 'required|email|max:191',
        ]);

        if ($validator->fails()) {
            // Idealmente, usaríamos flash messages para os erros.
            $this->view('empresa.create', [
                'errors' => $validator->getErrors(),
                'old' => $data
            ]);
            return;
        }

        $this->empresaModel->create($data);
        $this->redirect('/empresas');
    }

    /**
     * Exibe o formulário para editar uma empresa existente.
     */
    public function edit(int $id): void
    {
        $empresa = $this->empresaModel->find($id);

        if (!$empresa) {
            http_response_code(404);
            $this->view('errors.404'); // Supõe que uma view de erro 404 existe.
            return;
        }

        $this->view('empresa.edit', ['empresa' => $empresa]);
    }

    /**
     * Atualiza uma empresa existente no banco de dados.
     */
    public function update(int $id): void
    {
        $empresa = $this->empresaModel->find($id);

        if (!$empresa) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $data = Request::all();

        $validator = new Validator();
        $validator->validate($data, [
            'nome' => 'required|max:191',
            'cnpj' => 'required|max:18',
            'email' => 'required|email|max:191',
        ]);

        if ($validator->fails()) {
            $this->view('empresa.edit', ['empresa' => $empresa, 'errors' => $validator->getErrors(), 'old' => $data]);
            return;
        }

        $this->empresaModel->update($id, $data);
        $this->redirect('/empresas');
    }

    /**
     * Exclui uma empresa do banco de dados.
     */
    public function destroy(int $id): void
    {
        $this->empresaModel->delete($id);
        $this->redirect('/empresas');
    }
}