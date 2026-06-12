# Documentação Técnica

## Arquitetura

O projeto segue **MVC sem View** — a camada de View foi substituída por uma camada de **Service**, resultando no padrão:

```
Controller  →  Service  →  Model  →  Banco de dados
```

Cada módulo (clients, services, contracts) é completamente auto-contido:

```
src/<modulo>/
  controllers/   Recebe HTTP, delega ao Service, devolve JSON
  models/        Acesso ao banco via PDO (Model base do Crescent)
  services/      Orquestra casos de uso, validações e regras de negócio
  routes/        Registra as rotas no $app
  init.php       Bootstrap do módulo
```

---

## Camada de Serviço

### ClientsService
- Paginação com query manual (`LIMIT / OFFSET`)
- Validação de email via `filter_var`
- Validação matemática de CPF (dois dígitos verificadores) e CNPJ (dois dígitos verificadores)
- Sanitização do documento (remove não-numéricos)

### ServicesService
- Paginação
- Validação de `base_monthly_value > 0`

### ContractsService
- Paginação com JOIN em `clients` para trazer nome/email do cliente
- Carregamento em batch dos itens de todos os contratos da página (evita N+1)
- Bloqueio de edição em contratos cancelados (status = `canceled`)
- Delegação do cálculo ao `ContractCalculatorService`

---

## Cálculo Dinâmico — Strategy Pattern

Localização: `src/contracts/services/`

```
ContractDiscountStrategyInterface   interface com apply(array $contract, float $total): float
ContractCalculatorService           orquestra a cadeia de strategies
strategies/
  VolumeDiscountStrategy            -10% se > 3 itens OU qtd acumulada > 5
  LoyaltyDiscountStrategy           -5% se contrato tem ≥ 12 meses de vigência
```

### Como adicionar uma nova regra

1. Crie `src/contracts/services/strategies/minhaRegra​Strategy.php`
2. Implemente `ContractDiscountStrategyInterface`
3. Adicione `new MinhaRegraStrategy()` em `ContractCalculatorService::create()`

Nenhum outro arquivo precisa ser alterado.

---

## Banco de Dados

### Tabelas

| Tabela          | Chave primária | Relacionamentos                        |
|-----------------|----------------|----------------------------------------|
| clients         | id             | —                                      |
| services        | id             | —                                      |
| contracts       | id             | client_id → clients.id                 |
| contract_items  | id             | contract_id → contracts.id, service_id → services.id |

### Campos notáveis
- `clients.document` — CPF/CNPJ sem máscara (somente dígitos)
- `contracts.status` — ENUM `active | canceled`
- `contract_items.unit_value` — pode diferir do `services.base_monthly_value`

---

## Regras de negócio

| Regra | Onde |
|-------|------|
| Email inválido bloqueado | ClientsService::validateEmail |
| CPF/CNPJ inválido bloqueado | ClientsService::validateDocument |
| Contrato cancelado não aceita itens | ContractsService::addItem / removeItem |
| Desconto de volume (>3 itens ou qtd>5) | VolumeDiscountStrategy |
| Desconto de fidelidade (≥12 meses) | LoyaltyDiscountStrategy |

---

## Frontend (Vue 3)

- **Composition API** com `<script setup>`
- Estado local por view (`ref`)
- Todas as chamadas HTTP centralizadas em `src/services/api.js`
- Tratamento de loading e erros em cada view
- Paginação vinda do backend (meta: total, per_page, current_page, last_page)
- Painel de contratos: adicionar/remover itens em tempo real via modal
- Botão de cancelamento desabilita edições visualmente quando `status === 'canceled'`
