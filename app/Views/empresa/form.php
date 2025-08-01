@csrf

<div class="mb-3">
    <label for="nome" class="form-label">Nome:</label>
    <input type="text" id="nome" name="nome" class="form-control @if(isset($errors['nome'])) is-invalid @endif" value="{{ $empresa['nome'] ?? $old['nome'] ?? '' }}" required>
    @if(isset($errors['nome']))
        <div class="invalid-feedback d-block">{{ $errors['nome'][0] }}</div>
    @endif
</div>

<div class="mb-3">
    <label for="cnpj" class="form-label">CNPJ:</label>
    <input type="text" id="cnpj" name="cnpj" class="form-control @if(isset($errors['cnpj'])) is-invalid @endif" value="{{ $empresa['cnpj'] ?? $old['cnpj'] ?? '' }}" required>
    @if(isset($errors['cnpj']))
        <div class="invalid-feedback d-block">{{ $errors['cnpj'][0] }}</div>
    @endif
</div>

<div class="mb-3">
    <label for="email" class="form-label">E-mail:</label>
    <input type="email" id="email" name="email" class="form-control @if(isset($errors['email'])) is-invalid @endif" value="{{ $empresa['email'] ?? $old['email'] ?? '' }}" required>
    @if(isset($errors['email']))
        <div class="invalid-feedback d-block">{{ $errors['email'][0] }}</div>
    @endif
</div>

<div class="mb-3">
    <label for="telefone" class="form-label">Telefone:</label>
    <input type="text" id="telefone" name="telefone" class="form-control" value="{{ $empresa['telefone'] ?? $old['telefone'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="endereco" class="form-label">Endereço:</label>
    <input type="text" id="endereco" name="endereco" class="form-control" value="{{ $empresa['endereco'] ?? $old['endereco'] ?? '' }}">
</div>