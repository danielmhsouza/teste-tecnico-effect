<template>
  <div>
    <div class="page-header">
      <h2>Contratos</h2>
      <button class="btn-primary" @click="openCreate">+ Novo Contrato</button>
    </div>

    <div v-if="error" class="error-msg">{{ error }}</div>

    <table v-if="!loading">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Início</th>
          <th>Status</th>
          <th>Itens</th>
          <th>Total Mensal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in contracts" :key="c.id">
          <td>{{ c.client_name }}</td>
          <td>{{ c.start_date }}</td>
          <td><span :class="['badge', `badge-${c.status}`]">{{ c.status }}</span></td>
          <td>{{ c.items?.length ?? 0 }} serviço(s)</td>
          <td>
            <div style="display:flex;flex-direction:column;gap:.3rem">
              <strong>{{ formatCurrency(c.monthly_total) }}</strong>
              <template v-if="c.discounts?.length">
                <span style="font-size:.75rem;color:#888;text-decoration:line-through">
                  {{ formatCurrency(c.base_total) }}
                </span>
                <span
                  v-for="d in c.discounts" :key="d.label"
                  class="discount-tag"
                >{{ d.label }} −{{ formatCurrency(d.amount) }}</span>
              </template>
            </div>
          </td>
          <td style="display:flex;gap:.5rem;flex-wrap:wrap">
            <button class="btn-primary btn-sm" @click="openDetail(c)">Gerenciar</button>
            <button
              v-if="c.status !== 'canceled'"
              class="btn-danger btn-sm"
              @click="cancelContract(c.id)"
            >Cancelar</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else>Carregando...</p>

    <div class="pagination">
      <button :disabled="meta.current_page <= 1"             @click="loadPage(meta.current_page - 1)">‹</button>
      <span>{{ meta.current_page }} / {{ meta.last_page }}</span>
      <button :disabled="meta.current_page >= meta.last_page" @click="loadPage(meta.current_page + 1)">›</button>
    </div>

    <!-- Modal: novo contrato -->
    <div v-if="showCreate" class="modal-overlay" @click.self="showCreate = false">
      <div class="modal">
        <h3>Novo Contrato</h3>
        <div v-if="formErrors.length" class="error-msg">
          <div v-for="e in formErrors" :key="e">{{ e }}</div>
        </div>
        <div class="form-group">
          <label>Cliente</label>
          <select v-model="createForm.client_id">
            <option value="">Selecione...</option>
            <option v-for="cl in allClients" :key="cl.id" :value="cl.id">{{ cl.name }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Data de Início</label>
          <input v-model="createForm.start_date" type="date" />
        </div>
        <div class="form-group">
          <label>Data de Término (opcional)</label>
          <input v-model="createForm.end_date" type="date" />
        </div>
        <div class="modal-actions">
          <button @click="showCreate = false">Cancelar</button>
          <button class="btn-primary" @click="submitCreate">Criar</button>
        </div>
      </div>
    </div>

    <!-- Modal: gerenciar itens -->
    <div v-if="detail" class="modal-overlay" @click.self="detail = null">
      <div class="modal" style="min-width:520px">
        <h3>Contrato #{{ detail.id }} — {{ detail.client_name }}</h3>
        <p style="margin-bottom:.75rem;color:#555">
          Status: <span :class="['badge', `badge-${detail.status}`]">{{ detail.status }}</span>
          &nbsp;|  Total mensal: <strong>{{ formatCurrency(detail.monthly_total) }}</strong>
          <template v-if="detail.base_total !== detail.monthly_total">
            &nbsp;<span style="font-size:.82rem;color:#888;text-decoration:line-through">{{ formatCurrency(detail.base_total) }}</span>
          </template>
        </p>

        <!-- Breakdown de descontos aplicados -->
        <div v-if="detail.discounts?.length" class="discount-breakdown">
          <span v-for="d in detail.discounts" :key="d.label" class="discount-tag">
            ✔ {{ d.label }}: −{{ formatCurrency(d.amount) }}
          </span>
        </div>

        <table style="margin-bottom:1rem">
          <thead><tr><th>Serviço</th><th>Qtd</th><th>Unit.</th><th>Subtotal</th><th></th></tr></thead>
          <tbody>
            <tr v-for="item in detail.items" :key="item.id">
              <td>{{ item.service_name }}</td>
              <td>{{ item.quantity }}</td>
              <td>{{ formatCurrency(item.unit_value) }}</td>
              <td>{{ formatCurrency(item.unit_value * item.quantity) }}</td>
              <td>
                <button
                  v-if="detail.status !== 'canceled'"
                  class="btn-danger btn-sm"
                  @click="removeItem(detail.id, item.id)"
                >✕</button>
              </td>
            </tr>
            <tr v-if="!detail.items?.length">
              <td colspan="5" style="color:#999;text-align:center">Nenhum serviço adicionado</td>
            </tr>
          </tbody>
        </table>

        <!-- Add item form -->
        <div v-if="detail.status !== 'canceled'" style="background:#f9f9f9;padding:1rem;border-radius:6px">
          <h4 style="margin-bottom:.75rem">Adicionar Serviço</h4>
          <div v-if="itemErrors.length" class="error-msg">
            <div v-for="e in itemErrors" :key="e">{{ e }}</div>
          </div>
          <div style="display:grid;grid-template-columns:1fr auto auto;gap:.5rem;align-items:end">
            <div class="form-group" style="margin:0">
              <label>Serviço</label>
              <select v-model="itemForm.service_id">
                <option value="">Selecione...</option>
                <option v-for="s in allServices" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div class="form-group" style="margin:0;width:80px">
              <label>Qtd</label>
              <input v-model="itemForm.quantity" type="number" min="1" />
            </div>
            <button class="btn-primary" style="height:38px" @click="addItem">Adicionar</button>
          </div>
        </div>
        <div v-else style="color:#c0392b;font-size:.85rem;margin-top:.5rem">
          Contrato cancelado — edições desabilitadas.
        </div>

        <div class="modal-actions">
          <button @click="detail = null">Fechar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { contractsApi, clientsApi, servicesApi } from '../services/api.js'

const contracts  = ref([])
const meta       = ref({ current_page: 1, last_page: 1 })
const loading    = ref(false)
const error      = ref(null)

const showCreate = ref(false)
const formErrors = ref([])
const createForm = ref({ client_id: '', start_date: '', end_date: '' })

const detail     = ref(null)
const itemForm   = ref({ service_id: '', quantity: 1 })
const itemErrors = ref([])

const allClients  = ref([])
const allServices = ref([])

const formatCurrency = (v) =>
  Number(v ?? 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })

async function loadPage(page = 1) {
  loading.value = true
  error.value   = null
  try {
    const { data } = await contractsApi.list(page)
    contracts.value = data.data
    meta.value      = data.meta
  } catch (e) {
    error.value = e.response?.data?.error ?? 'Erro ao carregar contratos.'
  } finally {
    loading.value = false
  }
}

async function openCreate() {
  formErrors.value = []
  createForm.value = { client_id: '', start_date: '', end_date: '' }
  // load clients for select
  if (!allClients.value.length) {
    const { data } = await clientsApi.list(1, 100)
    allClients.value = data.data
  }
  showCreate.value = true
}

async function submitCreate() {
  formErrors.value = []
  try {
    await contractsApi.create(createForm.value)
    showCreate.value = false
    loadPage()
  } catch (e) {
    formErrors.value = e.response?.data?.errors ?? ['Erro ao criar contrato.']
  }
}

async function cancelContract(id) {
  if (!confirm('Cancelar contrato? Esta ação não pode ser desfeita.')) return
  try {
    await contractsApi.cancel(id)
    loadPage(meta.value.current_page)
    if (detail.value?.id === id) {
      const { data } = await contractsApi.get(id)
      detail.value = data.data
    }
  } catch (e) {
    error.value = e.response?.data?.error ?? 'Erro ao cancelar.'
  }
}

async function openDetail(contract) {
  itemErrors.value = []
  itemForm.value   = { service_id: '', quantity: 1 }
  if (!allServices.value.length) {
    const { data } = await servicesApi.list(1, 100)
    allServices.value = data.data
  }
  const { data } = await contractsApi.get(contract.id)
  detail.value = data.data
}

async function addItem() {
  itemErrors.value = []
  try {
    const { data } = await contractsApi.addItem(detail.value.id, itemForm.value)
    detail.value     = data.data
    itemForm.value   = { service_id: '', quantity: 1 }
    // refresh list row
    loadPage(meta.value.current_page)
  } catch (e) {
    itemErrors.value = e.response?.data?.errors ?? [e.response?.data?.error ?? 'Erro ao adicionar.']
  }
}

async function removeItem(contractId, itemId) {
  if (!confirm('Remover serviço do contrato?')) return
  try {
    await contractsApi.removeItem(contractId, itemId)
    const { data } = await contractsApi.get(contractId)
    detail.value = data.data
    loadPage(meta.value.current_page)
  } catch (e) {
    itemErrors.value = [e.response?.data?.error ?? 'Erro ao remover.']
  }
}

onMounted(() => loadPage())
</script>

<style scoped>
.discount-tag {
  display: inline-block;
  background: #fff3cd;
  color: #856404;
  border: 1px solid #ffc107;
  border-radius: 12px;
  font-size: .75rem;
  font-weight: 600;
  padding: .15rem .55rem;
  margin-right: .3rem;
}
.discount-breakdown {
  margin-bottom: .9rem;
  display: flex;
  flex-wrap: wrap;
  gap: .35rem;
}
</style>
