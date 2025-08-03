# GestorSystem

**GestorSystem** é um framework PHP moderno, desenvolvido com foco em robustez, organização e escalabilidade. Ele oferece uma base sólida para a criação de sistemas web utilizando os princípios da Programação Orientada a Objetos (POO) e uma arquitetura limpa e padronizada.

---

## 🚀 Principais Características

- Estrutura modular seguindo o padrão **MVC (Model-View-Controller)**.
- Autoload inteligente com **PSR-4**.
- Separação clara entre lógica de negócio, exibição e controle.
- Organização moderna de arquivos e pastas.
- Suporte a múltiplas empresas (multi-tenant).
- Pronto para expansão com novas funcionalidades.
- Script de migração CLI inteligente incluso.

---

## 🧠 Padrões e Tecnologias Utilizadas

| Categoria         | Implementação                      |
| ----------------- | ---------------------------------- |
| Arquitetura       | MVC (Model-View-Controller)        |
| Autoload          | PSR-4                              |
| Conexão com Banco | PDO com boas práticas de segurança |
| Programação       | PHP 8+, Orientada a Objetos (OOP)  |
| Estrutura         | Modular e escalável                |
| Design de Projeto | Separação de responsabilidades     |
| Versionamento     | Git + GitHub                       |

---

## 🤖 Assistência de IA

Este projeto contou com suporte inteligente da IA **Gemini**, que auxiliou no desenvolvimento estrutural e organizacional do framework, otimizando tempo e aplicando boas práticas do ecossistema PHP moderno.

---

## 📁 Estrutura de Diretórios

```
GestorSystem/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   └── Core/
├── config/
├── database/
│   ├── migrate.php
│   └── migrations/
├── public/
│   └── index.php
├── routes.php
├── storage/
├── composer.json
├── .env
└── README.md
```

---

## ⚙️ Instalação

```bash
git clone https://github.com/Natanaelpvh/GestorSystem.git
cd GestorSystem
composer install
```

---

## 🛠️ Comandos do Script de Migração

Utilize o script `database/migrate.php` para automatizar tarefas comuns com Models, Controllers e Migrations:

```bash
php database/migrate.php make:NomeDaEntidade
```

Cria automaticamente:

- `app/Models/NomeDaEntidade.php`
- `app/Controllers/NomeDaEntidadeController.php`
- `database/migrations/aaaa_mm_dd_create_nome_da_entidade_table.php`

```bash
php database/migrate.php update:NomeDaEntidade
```

Remove a migration anterior e gera uma nova atualizada para o model.

```bash
php database/migrate.php
```

Executa todas as migrations disponíveis (chama o método `up()`).

```bash
php database/migrate.php rollback
```

Reverte todas as migrations aplicadas (chama o método `down()`).

> Todas as migrations seguem o padrão `CreateNometabelaTable` e utilizam a classe `Database` do núcleo.

---

## 🌐 Configuração de Rotas

O arquivo de rotas do sistema está localizado em:

```bash
routes.php
```

Dentro dele, você pode importar arquivos como `routes/web.php` para organizar melhor suas rotas. Exemplo:

```php
require_once __DIR__ . '/routes/web.php';
```

No `routes/web.php`, defina suas rotas utilizando uma estrutura simples e direta:

```php
use App\Core\Router;

Router::get('/empresas', 'EmpresaController@index');
Router::post('/empresas', 'EmpresaController@store');
```

Essa estrutura envia as requisições HTTP para os métodos correspondentes nos controllers, com base na rota e no verbo HTTP (GET, POST, etc).

> O sistema utiliza uma classe `Router` personalizada que interpreta as rotas definidas e despacha para o controller correto com base na URL.

---

## 📢 Contato

Caso tenha sugestões, dúvidas ou deseje colaborar, sinta-se à vontade para entrar em contato:

📧 Email: [rnh.personalizados@gmail.com](mailto:rnh.personalizados@gmail.com)  
🔗 GitHub: [@Natanaelpvh](https://github.com/Natanaelpvh)
