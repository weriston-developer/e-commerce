# 🛒 E-Commerce API

API RESTful para E-commerce desenvolvida com Laravel 11, seguindo os princípios de **Clean Architecture**, **Domain-Driven Design (DDD)** e **CQRS** (Command Query Responsibility Segregation).

## 📋 Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Arquitetura](#-arquitetura)
- [Tecnologias](#-tecnologias)
- [Pré-requisitos](#-pré-requisitos)
- [Instalação](#-instalação)
- [Executando o Projeto](#-executando-o-projeto)
- [Executando os Testes](#-executando-os-testes)
- [Documentação da API](#-documentação-da-api)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Padrões de Código](#-padrões-de-código)

---

## 🎯 Sobre o Projeto

Este é um projeto de E-commerce completo que fornece uma API REST para gerenciamento de:

- **Autenticação e Autorização** (JWT)
- **Usuários** com diferentes níveis de permissão
- **Produtos** com categorias, preços, estoque e imagens
- **Categorias** organizacionais
- **Busca e Filtros** avançados de produtos
- **Soft Delete** para exclusões lógicas

### 🎨 Características Principais

- ✅ Autenticação JWT com refresh token
- ✅ Sistema de permissões (Gates)
- ✅ CQRS para separação de leitura/escrita
- ✅ Value Objects para tipos complexos (MoneyVO)
- ✅ DTOs para Input/Output
- ✅ Repository Pattern
- ✅ Validação de requisições (Form Requests)
- ✅ Tratamento de erros centralizado
- ✅ Testes unitários com 100% mocks (Pest PHP)
- ✅ Soft Delete em produtos e categorias
- ✅ Paginação e filtros dinâmicos

---

## 🏗️ Arquitetura

O projeto segue uma arquitetura em camadas inspirada em Clean Architecture e DDD:

```
┌─────────────────────────────────────────┐
│     Presentation Layer (Controllers)     │
│  - HTTP Requests/Responses               │
│  - Form Requests (Validation)            │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│       Application Layer                  │
│  - Use Cases (Commands)                  │
│  - Queries (CQRS)                        │
│  - DTOs (Input/Output)                   │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│         Domain Layer                     │
│  - Entities                              │
│  - Value Objects                         │
│  - Repository Interfaces                 │
│  - Business Rules                        │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│      Infrastructure Layer                │
│  - Eloquent Models                       │
│  - Repository Implementations            │
│  - Database Migrations                   │
│  - External Services                     │
└─────────────────────────────────────────┘
```

### CQRS Pattern

- **Commands**: UseCases para operações de escrita (Create, Update, Delete)
- **Queries**: Classes dedicadas para operações de leitura (List, Search, Filter)

---

## 🚀 Tecnologias

- **[Laravel 11](https://laravel.com/)** - Framework PHP
- **[PHP 8.3+](https://www.php.net/)** - Linguagem
- **[MySQL 8.4](https://www.mysql.com/)** - Banco de dados
- **[Docker](https://www.docker.com/)** - Containerização
- **[Laravel Sail](https://laravel.com/docs/11.x/sail)** - Ambiente Docker para Laravel
- **[JWT Auth](https://jwt-auth.readthedocs.io/)** - Autenticação JWT
- **[Pest PHP](https://pestphp.com/)** - Framework de testes
- **[Mockery](https://github.com/mockery/mockery)** - Biblioteca de mocks

---

## 📦 Pré-requisitos

- **Docker** e **Docker Compose** instalados
- **Git**
- **WSL2** (para Windows)

> **Nota**: Não é necessário ter PHP ou MySQL instalados localmente, pois tudo roda em containers Docker.

---

## 🔧 Instalação

### 1. Clone o repositório

```bash
git clone <repository-url>
cd e-commerce
```

### 2. Configure o arquivo de ambiente

```bash
cp .env.example .env
```

### 3. Instale as dependências

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

### 4. Suba os containers

```bash
./vendor/bin/sail up -d
```

Ou no Windows PowerShell:

```powershell
docker compose up -d
```

### 5. Gere a chave da aplicação

```bash
./vendor/bin/sail artisan key:generate
```

### 6. Execute as migrations

```bash
./vendor/bin/sail artisan migrate
```

### 7. Gere a chave JWT

```bash
./vendor/bin/sail artisan jwt:secret
```

### 8. (Opcional) Execute os seeders

```bash
./vendor/bin/sail artisan db:seed
```

---

## ▶️ Executando o Projeto

### Iniciar os containers

```bash
./vendor/bin/sail up -d
```

Ou no Windows PowerShell:

```powershell
docker compose up -d
```

### Parar os containers

```bash
./vendor/bin/sail down
```

Ou:

```powershell
docker compose down
```

### Acessar a aplicação

- **API**: http://localhost
- **Health Check**: http://localhost/api/v1/health

### Acessar o container

```bash
./vendor/bin/sail shell
```

Ou:

```powershell
docker compose exec laravel.test bash
```

### Ver logs

```bash
./vendor/bin/sail logs -f
```

---

## 🧪 Executando os Testes

O projeto possui **58 testes unitários** com **259 assertions**, todos utilizando **100% mocks** (sem interação com banco de dados).

### Executar todos os testes

```bash
./vendor/bin/sail artisan test
```

Ou com Pest:

```bash
./vendor/bin/sail pest
```

No Windows PowerShell:

```powershell
docker compose exec laravel.test ./vendor/bin/pest
```

### Executar testes específicos

#### Testes de Auth

```bash
docker compose exec laravel.test ./vendor/bin/pest tests/Unit/Auth/
```

#### Testes de Product

```bash
docker compose exec laravel.test ./vendor/bin/pest tests/Unit/Product/
```

#### Testes de Category

```bash
docker compose exec laravel.test ./vendor/bin/pest tests/Unit/Category/
```

#### Testes de Queries (CQRS)

```bash
docker compose exec laravel.test ./vendor/bin/pest tests/Unit/Queries/
```

### Cobertura de Testes

- ✅ **Auth UseCases**: 7 testes (Login, Register)
- ✅ **Product UseCases**: 16 testes (Create, Update, Delete)
- ✅ **Category UseCases**: 16 testes (Create, Update, Delete)
- ✅ **Product Queries**: 18 testes (GetAll, Search, GetByCategory)
- ✅ **Total**: **58 testes, 259 assertions**

---

## 📚 Documentação da API

Base URL: `http://localhost/api/v1`

### 🔐 Autenticação

Todos os endpoints protegidos requerem o header:

```
Authorization: Bearer {token}
```

#### POST `/auth/register`

Registra um novo usuário.

**Request Body:**

```json
{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

**Response:** `201 Created`

```json
{
  "user": {
    "uuid": "123e4567-e89b-12d3-a456-426614174000",
    "name": "João Silva",
    "email": "joao@example.com",
    "role": "CUSTOMER"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
  "expires_in": 3600
}
```

#### POST `/auth/login`

Autentica um usuário.

**Request Body:**

```json
{
  "email": "joao@example.com",
  "password": "senha123"
}
```

**Response:** `200 OK`

```json
{
  "user": {
    "uuid": "123e4567-e89b-12d3-a456-426614174000",
    "name": "João Silva",
    "email": "joao@example.com",
    "role": "CUSTOMER"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
  "expires_in": 3600
}
```

#### POST `/auth/logout` 🔒

Faz logout do usuário autenticado.

**Response:** `200 OK`

```json
{
  "message": "Successfully logged out"
}
```

#### POST `/auth/refresh` 🔒

Renova o token JWT.

**Response:** `200 OK`

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
  "expires_in": 3600
}
```

#### GET `/auth/me` 🔒

Retorna dados do usuário autenticado.

**Response:** `200 OK`

```json
{
  "uuid": "123e4567-e89b-12d3-a456-426614174000",
  "name": "João Silva",
  "email": "joao@example.com",
  "role": "CUSTOMER",
  "created_at": "2024-01-01T00:00:00Z"
}
```

---

### 📦 Produtos

#### GET `/products`

Lista produtos com filtros e paginação.

**Query Parameters:**

- `search` - Busca por nome/descrição
- `categories[]` - Array de UUIDs de categorias
- `min_price` - Preço mínimo
- `max_price` - Preço máximo
- `only_active` - Apenas ativos (true/false)
- `only_in_stock` - Apenas com estoque (true/false)
- `sort_by` - Campo para ordenação (default: created_at)
- `sort_order` - Ordem (asc/desc, default: desc)
- `per_page` - Itens por página (default: 15)

**Response:** `200 OK`

```json
{
  "data": [
    {
      "uuid": "prod-123",
      "name": "Notebook Dell",
      "slug": "notebook-dell",
      "description": "Notebook Dell Inspiron 15",
      "price": 3500.00,
      "price_formatted": "R$ 3.500,00",
      "stock": 10,
      "is_available": true,
      "image_url": "https://...",
      "category": {
        "uuid": "cat-123",
        "name": "Eletrônicos",
        "slug": "eletronicos"
      },
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

#### GET `/products/active`

Lista apenas produtos ativos.

**Response:** `200 OK` (mesmo formato acima)

#### GET `/products/search`

Busca produtos por termo.

**Query Parameters:**

- `term` (required) - Termo de busca
- `per_page` - Itens por página (default: 15)

**Response:** `200 OK`

#### GET `/products/price-range`

Busca produtos por faixa de preço.

**Query Parameters:**

- `min_price` (required) - Preço mínimo
- `max_price` (required) - Preço máximo
- `per_page` - Itens por página (default: 15)

**Response:** `200 OK`

#### GET `/products/category/{categoryUuid}`

Lista produtos de uma categoria.

**Response:** `200 OK`

#### GET `/products/{uuid}`

Detalhes de um produto.

**Response:** `200 OK`

```json
{
  "uuid": "prod-123",
  "name": "Notebook Dell",
  "slug": "notebook-dell",
  "description": "Notebook Dell Inspiron 15",
  "price": 3500.00,
  "price_formatted": "R$ 3.500,00",
  "stock": 10,
  "is_available": true,
  "sku": "NB-DELL-001",
  "image_url": "https://...",
  "category": {
    "uuid": "cat-123",
    "name": "Eletrônicos",
    "slug": "eletronicos",
    "description": "Produtos eletrônicos",
    "is_active": true
  },
  "created_at": "2024-01-01T00:00:00Z",
  "updated_at": "2024-01-01T00:00:00Z"
}
```

#### POST `/products` 🔒

Cria um novo produto (requer permissão `manage-products`).

**Request Body:**

```json
{
  "name": "Notebook Dell",
  "description": "Notebook Dell Inspiron 15",
  "price": 3500.00,
  "stock": 10,
  "category_uuid": "cat-123",
  "sku": "NB-DELL-001",
  "image_url": "https://..."
}
```

**Response:** `201 Created`

#### PUT `/products/{uuid}` 🔒

Atualiza um produto (requer permissão `manage-products`).

**Request Body:** (todos os campos são opcionais)

```json
{
  "name": "Notebook Dell Atualizado",
  "price": 3200.00,
  "stock": 15
}
```

**Response:** `200 OK`

#### DELETE `/products/{uuid}` 🔒

Deleta um produto (soft delete) (requer permissão `manage-products`).

**Response:** `200 OK`

```json
{
  "message": "Produto deletado com sucesso"
}
```

---

### 🏷️ Categorias

#### GET `/categories`

Lista todas as categorias.

**Response:** `200 OK`

```json
{
  "data": [
    {
      "uuid": "cat-123",
      "name": "Eletrônicos",
      "slug": "eletronicos",
      "description": "Produtos eletrônicos",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

#### GET `/categories/active`

Lista apenas categorias ativas.

**Response:** `200 OK`

#### GET `/categories/{uuid}`

Detalhes de uma categoria.

**Response:** `200 OK`

#### POST `/categories` 🔒

Cria uma nova categoria (requer permissão `manage-categories`).

**Request Body:**

```json
{
  "name": "Eletrônicos",
  "description": "Produtos eletrônicos",
  "is_active": true
}
```

**Response:** `201 Created`

#### PUT `/categories/{uuid}` 🔒

Atualiza uma categoria (requer permissão `manage-categories`).

**Request Body:** (todos os campos são opcionais)

```json
{
  "name": "Eletrônicos e Informática",
  "is_active": false
}
```

**Response:** `200 OK`

#### DELETE `/categories/{uuid}` 🔒

Deleta uma categoria (soft delete) (requer permissão `manage-categories`).

**Response:** `200 OK`

---

### 👥 Usuários

#### GET `/users` 🔒

Lista todos os usuários (requer permissão `delete-users`).

**Response:** `200 OK`

```json
{
  "data": [
    {
      "uuid": "user-123",
      "name": "João Silva",
      "email": "joao@example.com",
      "role": "CUSTOMER",
      "created_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

#### DELETE `/users/{uuid}` 🔒

Deleta um usuário (requer permissão `delete-users`).

**Response:** `200 OK`

---

### 🏥 Health Check

#### GET `/health`

Verifica o status da API.

**Response:** `200 OK`

```json
{
  "status": "ok",
  "timestamp": "2024-01-01T00:00:00Z",
  "version": "v1"
}
```

---

## 🗂️ Estrutura do Projeto

```
app/
├── Application/              # Camada de Aplicação
│   ├── CQRS/
│   │   └── Queries/         # Queries CQRS (Read)
│   │       ├── Auth/
│   │       ├── Category/
│   │       ├── Product/
│   │       └── User/
│   ├── DTOs/
│   │   ├── Inputs/          # DTOs de entrada
│   │   └── Outputs/         # DTOs de saída
│   ├── Errors/              # Classes de erro customizadas
│   ├── Exceptions/          # Exception handler
│   ├── Services/            # Serviços da aplicação
│   └── UseCases/            # Use Cases (Commands)
│       ├── Auth/
│       ├── Category/
│       ├── Product/
│       └── User/
│
├── Domain/                   # Camada de Domínio
│   ├── Entities/            # Entidades de domínio
│   ├── Enums/               # Enumerações
│   ├── Interfaces/          # Repository Interfaces
│   └── ValueObjects/        # Value Objects
│
└── Infrastructure/           # Camada de Infraestrutura
    ├── Persistence/
    │   ├── Models/          # Eloquent Models
    │   └── Repositories/    # Repository Implementations
    └── Presentation/
        ├── Controllers/     # HTTP Controllers
        └── Requests/        # Form Requests (Validation)

database/
├── factories/               # Model Factories
├── migrations/              # Migrations
└── seeders/                # Seeders

tests/
└── Unit/                    # Testes Unitários
    ├── Auth/
    ├── Category/
    ├── Product/
    └── Queries/
```

---

## 📏 Padrões de Código

### Use Cases

Todos os Use Cases seguem o padrão:

```php
readonly class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(CreateProductInput $input): ProductOutput
    {
        // Lógica de negócio
    }
}
```

### Queries (CQRS)

Queries são separadas dos Use Cases:

```php
readonly class GetAllProductsQuery
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(ProductFiltersInput $filters): array
    {
        // Apenas leitura
    }
}
```

### DTOs

- **Input DTOs**: Validação e tipagem de entrada
- **Output DTOs**: Formatação e tipagem de saída

```php
readonly class ProductOutput
{
    public function __construct(
        public string $uuid,
        public string $name,
        public float $price,
        // ...
    ) {}

    public static function fromModel(Product $product): self
    {
        // Conversão de Model para DTO
    }

    public function toArray(): array
    {
        // Serialização
    }
}
```

### Value Objects

Encapsulam lógica de valor:

```php
readonly class MoneyVO
{
    public function __construct(private float $amount) {}

    public function toFloat(): float { /* ... */ }
    public function format(): string { /* ... */ }
    public function toCents(): int { /* ... */ }
}
```

### Repository Pattern

```php
interface ProductRepositoryInterface
{
    public function create(array $data): Product;
    public function update(string $uuid, array $data): Product;
    public function delete(string $uuid): bool;
    public function findByUuid(string $uuid): ?Product;
    // ...
}
```

---

## 🔒 Permissões

### Roles

- **ADMIN**: Acesso total
- **MANAGER**: Gerenciar produtos e categorias
- **CUSTOMER**: Acesso apenas leitura

### Gates

- `manage-products`: Criar, editar, deletar produtos
- `manage-categories`: Criar, editar, deletar categorias
- `delete-users`: Deletar usuários

---

## 🐛 Tratamento de Erros

Todos os erros retornam JSON no formato:

```json
{
  "message": "Mensagem de erro",
  "errors": {
    "field": ["Lista de erros do campo"]
  }
}
```

### Códigos HTTP

- `200` - Sucesso
- `201` - Criado com sucesso
- `400` - Requisição inválida
- `401` - Não autenticado
- `403` - Sem permissão
- `404` - Não encontrado
- `422` - Validação falhou
- `500` - Erro interno

---

## 📝 Licença

Este projeto é de código aberto.

---

## 👥 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

---

## 📧 Contato

Para dúvidas ou sugestões, abra uma issue no repositório.

---

**Desenvolvido com ❤️ usando Laravel, Clean Architecture e DDD**
