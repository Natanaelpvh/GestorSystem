@extends('layouts.main')

@section('content')
    <h1>Editar Empresa: {{ $empresa['nome'] }}</h1>

    <form action="{{ $basePath }}/empresas/{{ $empresa['id'] }}" method="POST" class="mt-4">
        <input type="hidden" name="_method" value="PUT">
        <!-- O método HTTP real é POST, mas o campo _method simula o PUT para o nosso roteador. -->
        @include('empresa.form')
        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>

    <br>
    <a href="{{ $basePath }}/empresas" class="btn btn-secondary mt-2">Voltar para a lista</a>
@endsection