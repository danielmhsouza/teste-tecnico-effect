<template>
  <div>
    <div class="page-header">
      <h2>Clientes</h2>
      <button class="btn-primary" @click="openCreate">+ Novo Cliente</button>
    </div>

    <div v-if="error" class="error-msg">{{ error }}</div>

    <table v-if="!loading">
      <thead>
        <tr>
          <th>Nome</th><th>Documento</th><th>Email</th><th>Status</th><th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in clients" :key="c.id">
          <td>{{ c.name }}</td>
          <td>{{ c.document }}</td>
          <td>{{ c.email }}</td>
          <td><span :class="['badge', `badge-${c.status}`]">{{ c.status }}</span></td>
          <td style="display:flex;gap:.5rem">
            <button class="btn-warning btn-sm" @click="openEdit(c)">Editar</button>
            <button class="btn-danger btn-sm"  @click="removeClient(c.id)">Excluir</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else>Carregando...</p>

    <div class="pagination">
      <button :disabled="meta.current_page <= 1" @click="loadPage(meta.current_page - 1)">‹</button>
      <span>{{ meta.current_page }} / {{ meta.last_page }}</span>
      <button :disabled="meta.current_page >= meta.last_page" @click="loadPage(meta.current_page + 1)">›</button>
    </div>

    <!-- Modal create/edit -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <h3>{{ editing ? 'Editar Cliente' : 'Novo Cliente' }}</h3>
        <div v-if="formErrors.length" class="error-msg">
          <div v-for="e in formErrors" :key="e">{{ e }}</div>
        </div>
        <div class="form-group">
          <label>Nome</label>
          <input v-model="form.name" placeholder="Nome completo" />
        </div>
        <div class="form-group">
          <label>Documento (CPF/CNPJ)</label>
          <input v-model="form.document" placeholder="Somente números" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" />
        </div>
        <div class="form-group">
          <label>Status</label>
          <select v-model="form.status">
            <option value="active">Ativo</option>
            <option value="inactive">Inativo</option>
          </select>
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
import { clientsApi } from '../services/api.js'

const clients    = ref([])
const meta       = ref({ current_page: 1, last_page: 1 })
const loading    = ref(false)
const error      = ref(null)
const showModal  = ref(false)
const editing    = ref(null)
const formErrors = ref([])
const form       = ref({ name: '', document: '', email: '', status: 'active' })

async function loadPage(page = 1) {
  loading.value = true
  error.value   = null
  try {
    const { data } = await clientsApi.list(page)
    clients.value  = data.data
    meta.value     = data.meta
  } catch (e) {
    error.value = e.response?.data?.error ?? 'Erro ao carregar clientes.'
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value    = null
  formErrors.value = []
  form.value       = { name: '', document: '', email: '', status: 'active' }
  showModal.value  = true
}

function openEdit(client) {
  editing.value    = client.id
  formErrors.value = []
  form.value       = { name: client.name, document: client.document, email: client.email, status: client.status }
  showModal.value  = true
}

async function submit() {
  formErrors.value = []
  try {
    if (editing.value) {
      await clientsApi.update(editing.value, form.value)
    } else {
      await clientsApi.create(form.value)
    }
    showModal.value = false
    loadPage(meta.value.current_page)
  } catch (e) {
    formErrors.value = e.response?.data?.errors ?? ['Erro ao salvar.']
  }
}

async function removeClient(id) {
  if (!confirm('Excluir cliente?')) return
  try {
    await clientsApi.remove(id)
    loadPage(meta.value.current_page)
  } catch (e) {
    error.value = e.response?.data?.error ?? 'Erro ao excluir.'
  }
}

onMounted(() => loadPage())
</script>
