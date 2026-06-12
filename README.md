# ERP — Gestão de Contratos e Serviços

Sistema de gestão de contratos, clientes e serviços construído com **Crescent PHP** (backend) e **Vue 3** (frontend).

---

## Requisitos

- PHP 8.1+
- MySQL 8+ ou PostgreSQL 14+
- Node.js 18+

---

## Setup — Backend

```bash
cd backend
cp .env.example .env   # preencha DB_*, APP_ENV, etc.
php crecli.php migrate
php crecli.php serve    # http://localhost:8000
```

## Rodar testes
```bash
cd backend
php crecli.php test
```

## Setup — Frontend

```bash
cd frontend
npm install
npm run dev             # http://localhost:5173
```

O Vite faz proxy de `/api/*` para `http://localhost:8000/api/*` automaticamente.

---

## Variáveis de ambiente principais (backend/.env)

| Variável   | Exemplo           |
|------------|-------------------|
| DB_DRIVER  | mysql             |
| DB_HOST    | localhost         |
| DB_PORT    | 3306              |
| DB_NAME    | erp               |
| DB_USER    | root              |
| DB_PASS    | secret            |
| APP_ENV    | development       |

---

## Endpoints da API

### Clientes — `/api/clients`
| Método | Rota            | Ação               |
|--------|-----------------|--------------------|
| GET    | /               | Listagem paginada  |
| GET    | /:id            | Detalhe            |
| POST   | /               | Criar              |
| PUT    | /:id            | Editar             |
| DELETE | /:id            | Excluir            |

### Serviços — `/api/services`
_(mesma estrutura que Clientes)_

### Contratos — `/api/contracts`
| Método | Rota                     | Ação                         |
|--------|--------------------------|------------------------------|
| GET    | /                        | Listagem paginada com totais |
| GET    | /:id                     | Detalhe                      |
| POST   | /                        | Criar contrato               |
| PUT    | /:id/status              | Cancelar contrato            |
| POST   | /:id/items               | Adicionar serviço            |
| DELETE | /:id/items/:item_id      | Remover serviço              |

### Estratégias de desconto — `/api/strategies`
| Método | Rota  | Ação                              |
|--------|-------|-----------------------------------|
| GET    | /     | Listar estratégias                |
| PUT    | /:id  | Atualizar taxa, threshold e label |

---

## Estrutura do projeto

```
backend/
  src/
    clients/    controllers/ models/ services/ routes/
    services/   controllers/ models/ services/ routes/
    contracts/  controllers/ models/ services/ routes/
                services/strategies/   ← Strategy Pattern
    strategies/ controllers/ models/ services/ routes/
  migrations/
frontend/
  src/
    views/      ClientsView.vue  ServicesView.vue  ContractsView.vue  StrategiesView.vue
    services/   api.js
    router/     index.js
docs/
  PDF com o desafio
  PDF com modelo Entidade Relacionamento
```
