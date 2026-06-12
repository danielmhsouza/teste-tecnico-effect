# Documentação Técnica

## Arquitetura

O projeto segue **MVC sem View** — a camada de View foi substituída por uma camada de **Service**, resultando no padrão:

```
Controller  →  Service  →  Model  →  Banco de dados
```

Cada módulo (clients, services, contracts, strategies) é completamente auto-contido:

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
- Paginação delegada a `ClientsModel::paginate()` (`LIMIT / OFFSET`)
- Validação de email via `filter_var`
- Validação matemática de CPF (dois dígitos verificadores) e CNPJ (dois dígitos verificadores)
- Sanitização do documento (remove não-numéricos)

### ServicesService
- Paginação delegada a `ServicesModel::paginate()` (`LIMIT / OFFSET`)
- Validação de `base_monthly_value > 0`

### ContractsService
- Paginação e busca delegadas a `ContractsModel::paginateWithClient()` e `ContractsModel::findWithClient()` (JOIN com `clients`)
- Carregamento em batch dos itens via `ContractItemsModel::findByContracts()` (evita N+1)
- Bloqueio de edição em contratos cancelados (status = `canceled`)
- Soma de quantidade quando o mesmo serviço é adicionado duas vezes ao contrato
- Delegação do cálculo ao `ContractCalculatorService`

### StrategiesService
- Lista todas as estratégias cadastradas (`all()`)
- Atualiza label, taxa de desconto, threshold e status ativo/inativo de uma estratégia
- Valida `discount_rate` entre 0,01 e 1,00 e `threshold_value > 0`

---

## Cálculo Dinâmico — Strategy Pattern

Localização: `src/contracts/services/`

```
ContractDiscountStrategyInterface   interface com apply(array $contract, float $total): float
ContractCalculatorService           orquestra a cadeia de strategies
strategies/
  VolumeDiscountStrategy            desconto configurável se itens distintos > threshold OU qtd total > threshold×2
  LoyaltyDiscountStrategy           desconto configurável se contrato tem ≥ threshold meses de vigência
```

As taxas e thresholds são **DB-driven** — lidos da tabela `discount_strategies` em tempo de execução com cache estático por processo.
Os padrões aplicados quando não há registro no banco: volume 10% com threshold 3, fidelidade 5% com threshold 12 meses.

### Como adicionar uma nova regra

1. Crie `src/contracts/services/strategies/minhaRegra​Strategy.php`
2. Implemente `ContractDiscountStrategyInterface`
3. Adicione `new MinhaRegraStrategy()` em `ContractCalculatorService::create()`

Nenhum outro arquivo precisa ser alterado.

---

## Banco de Dados

### Tabelas

| Tabela               | Chave primária | Relacionamentos                                                    |
|----------------------|----------------|--------------------------------------------------------------------|
| clients              | id             | —                                                                  |
| services             | id             | —                                                                  |
| contracts            | id             | client_id → clients.id                                            |
| contract_items       | id             | contract_id → contracts.id, service_id → services.id              |
| discount_strategies  | id             | — (referenciada pelas strategies via `name`)                      |

### Campos notáveis
- `clients.document` — CPF/CNPJ sem máscara (somente dígitos)
- `contracts.status` — ENUM `active | canceled`
- `contract_items.unit_value` — pode diferir do `services.base_monthly_value`
- `discount_strategies.discount_rate` — DECIMAL(5,4), ex.: 0.10 = 10%
- `discount_strategies.threshold_value` — limite configurável pelo painel de estratégias
- `discount_strategies.is_active` — desativa a estratégia sem removê-la

---

## Regras de negócio

| Regra | Onde |
|-------|------|
| Email inválido bloqueado | ClientsService::validateEmail |
| CPF/CNPJ inválido bloqueado | ClientsService::validateDocument |
| Contrato cancelado não aceita itens | ContractsService::addItem / removeItem |
| Mesmo serviço soma quantidade em vez de duplicar linha | ContractsService::addItem |
| Desconto de volume (itens > threshold ou qtd total > threshold×2) | VolumeDiscountStrategy (DB-driven) |
| Desconto de fidelidade (≥ threshold meses) | LoyaltyDiscountStrategy (DB-driven) |
| Estratégia inativa não é aplicada | VolumeDiscountStrategy / LoyaltyDiscountStrategy |

---

## Frontend (Vue 3)

- **Composition API** com `<script setup>`
- Estado local por view (`ref`)
- Todas as chamadas HTTP centralizadas em `src/services/api.js`
- Tratamento de loading e erros em cada view
- Paginação vinda do backend (meta: total, per_page, current_page, last_page)
- Painel de contratos: adicionar/remover itens em tempo real via modal
- Feedback visual de descontos: preço base tachado + badges amarelas com o valor economizado
- Botão de cancelamento desabilita edições visualmente quando `status === 'canceled'`
- Painel de estratégias: cards por estratégia com toggle ativo/inativo, taxa de desconto e threshold editáveis
