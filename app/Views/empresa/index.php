@extends('layouts.main')

@section('content')
    <h1>Lista de Empresas</h1>

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ $basePath }}/empresas/create" class="btn btn-primary">Cadastrar Nova Empresa</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nome</th>
                    <th scope="col">CNPJ</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                @if(empty($empresas))
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma empresa encontrada.</td>
                    </tr>
                @else
                    @foreach($empresas as $empresa)
                        <tr>
                            <th scope="row">{{ $empresa['id'] }}</th>
                            <td>{{ $empresa['nome'] }}</td>
                            <td>{{ $empresa['cnpj'] }}</td>
                            <td>{{ $empresa['email'] }}</td>
                            <td>
                                <a href="{{ $basePath }}/empresas/edit/{{ $empresa['id'] }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form action="{{ $basePath }}/empresas/delete/{{ $empresa['id'] }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                    <input type="hidden" name="_method" value="DELETE">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
@endsection