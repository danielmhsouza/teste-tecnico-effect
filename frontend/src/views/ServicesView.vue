<template>
  <div>
    <div class="page-header">
      <h2>Serviços</h2>
      <button class="btn-primary" @click="openCreate">+ Novo Serviço</button>
    </div>

    <div v-if="error" class="error-msg">{{ error }}</div>

    <table v-if="!loading">
      <thead>
        <tr><th>Nome</th><th>Valor Mensal Base</th><th></th></tr>
      </thead>
      <tbody>
        <tr v-for="s in services" :key="s.id">
          <td>{{ s.name }}</td>
          <td>{{ formatCurrency(s.base_monthly_value) }}</td>
          <td style="display:flex;gap:.5rem">
            <button class="btn-warning btn-sm" @click="openEdit(s)">Editar</button>
            <button class="btn-danger btn-sm"  @click="removeService(s.id)">Excluir</button>
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

    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <h3>{{ editing ? 'Editar Serviço' : 'Novo Serviço' }}</h3>
        <div v-if="formErrors.length" class="error-msg">
          <div v-for="e in formErrors" :key="e">{{ e }}</div>
        </div>
        <div class="form-group">
          <label>Nome</label>
          <input v-model="form.name" placeholder="Nome do serviço" />
        </div>
        <div class="form-group">
          <label>Valor Mensal Base (R$)</label>
          <input v-model="form.base_monthly_value" type="number" min="0.01" step="0.01" />
        </div>
        <div class="modal-actions">
          <button @click="showModal = false">Cancelar</button>
          <button class="btn-primary" @click="submit">Salvar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { servicesApi } from '../services/api.js'

const services   = ref([])
const meta       = ref({ current_page: 1, last_page: 1 })
const loading    = ref(false)
const error      = ref(null)
const showModal  = ref(false)
const editing    = ref(null)
const formErrors = ref([])
const form       = ref({ name: '', base_monthly_value: '' })

const formatCurrency = (v) =>
  Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })

async function loadPage(page = 1) {
  loading.value = true
  error.value   = null
  try {
    const { data } = await servicesApi.list(page)
    services.value = data.data
    meta.value     = data.meta
  } catch (e) {
    error.value = e.response?.data?.error ?? 'Erro ao carregar serviços.'
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value    = null
  formErrors.value = []
  form.value       = { name: '', base_monthly_value: '' }
  showModal.value  = true
}

function openEdit(s) {
  editing.value    = s.id
  formErrors.value = []
  form.value       = { name: s.name, base_monthly_value: s.base_monthly_value }
  showModal.value  = true
}

async function submit() {
  formErrors.value = []
  try {
    if (editing.value) {
      await servicesApi.update(editing.value, form.value)
    } else {
      await servicesApi.create(form.value)
    }
    showModal.value = false
    loadPage(meta.value.current_page)
  } catch (e) {
    formErrors.value = e.response?.data?.errors ?? ['Erro ao salvar.']
  }
}

async function removeService(id) {
  if (!confirm('Excluir serviço?')) return
  try {
    await servicesApi.remove(id)
    loadPage(meta.value.current_page)
  } catch (e) {
    error.value = e.response?.data?.error ?? 'Erro ao excluir.'
  }
}

onMounted(() => loadPage())
</script>
