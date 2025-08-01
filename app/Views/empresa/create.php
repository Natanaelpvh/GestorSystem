@extends('layouts.main')

@section('content')
    <h1>Cadastrar Nova Empresa</h1>

    <form action="{{ $basePath }}/empresas" method="POST">
        @include('empresa.form')
        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>

    <br>
    <a href="{{ $basePath }}/empresas" class="btn btn-secondary mt-2">Voltar para a lista</a>
@endsection