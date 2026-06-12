<template>
  <div>
    <div class="page-header">
      <h2>Estratégias de Desconto</h2>
    </div>
    <p style="margin-bottom:1.25rem;color:#555;font-size:.9rem">
      Configure as condições e taxas de desconto aplicadas automaticamente nos contratos.
    </p>

    <div v-if="error" class="error-msg">{{ error }}</div>
    <p v-if="loading">Carregando...</p>

    <div v-for="s in strategies" :key="s.id" class="strategy-card">
      <div class="strategy-header">
        <div>
          <span class="strategy-name">{{ s.label }}</span>
          <span class="strategy-key">{{ s.name }}</span>
        </div>
        <label class="toggle">
          <input type="checkbox" :checked="!!s.is_active" @change="toggleActive(s)" />
          <span class="toggle-track"></span>
          <span class="toggle-label">{{ s.is_active ? 'Ativa' : 'Inativa' }}</span>
        </label>
      </div>

      <div class="strategy-body" :class="{ disabled: !s.is_active }">
        <div class="field-row">
          <div class="form-group">
            <label>Taxa de desconto</label>
            <div class="input-suffix">
              <input
                v-model="drafts[s.id].discount_rate_pct"
                type="number" min="0.1" max="100" step="0.1"
                :disabled="!s.is_active"
              />
              <span>%</span>
            </div>
          </div>

          <div class="form-group">
            <label>{{ thresholdLabel(s) }}</label>
            <div class="input-suffix">
              <input
                v-model="drafts[s.id].threshold_value"
                type="number" min="1" step="1"
                :disabled="!s.is_active"
              />
              <span>{{ thresholdUnit(s) }}</span>
            </div>
          </div>

          <div class="form-group" style="align-self:flex-end">
            <button
              class="btn-primary"
              :disabled="!s.is_active || saving[s.id]"
              @click="save(s)"
            >{{ saving[s.id] ? 'Salvando…' : 'Salvar' }}</button>
          </div>
        </div>

        <p class="strategy-hint">{{ hint(s) }}</p>

        <div v-if="feedbacks[s.id]" :class="['feedback', feedbacks[s.id].type]">
          {{ feedbacks[s.id].msg }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { strategiesApi } from '../services/api.js'

const strategies = ref([])
const loading    = ref(false)
const error      = ref(null)
const drafts     = reactive({})
const saving     = reactive({})
const feedbacks  = reactive({})

function thresholdLabel(s) {
  return s.threshold_type === 'months' ? 'Mínimo de meses de vigência' : 'Mínimo de itens distintos'
}

function thresholdUnit(s) {
  return s.threshold_type === 'months' ? 'meses' : 'itens'
}

function hint(s) {
  const rate = (parseFloat(drafts[s.id]?.discount_rate_pct) || 0).toFixed(1)
  const thr  = drafts[s.id]?.threshold_value ?? s.threshold_value
  if (s.name === 'volume') {
    return `Aplica ${rate}% de desconto quando o contrato tiver mais de ${thr} serviços distintos ou quantidade acumulada > ${thr * 2}.`
  }
  if (s.name === 'loyalty') {
    return `Aplica ${rate}% de desconto para contratos com ${thr} ou mais meses de vigência.`
  }
  return ''
}

async function load() {
  loading.value = true
  error.value   = null
  try {
    const { data } = await strategiesApi.list()
    strategies.value = data.data
    for (const s of data.data) {
      drafts[s.id] = {
        discount_rate_pct: +(parseFloat(s.discount_rate) * 100).toFixed(2),
        threshold_value:   +parseFloat(s.threshold_value),
      }
      saving[s.id]   = false
      feedbacks[s.id] = null
    }
  } catch (e) {
    error.value = 'Erro ao carregar estratégias.'
  } finally {
    loading.value = false
  }
}

async function save(s) {
  saving[s.id]   = true
  feedbacks[s.id] = null
  try {
    await strategiesApi.update(s.id, {
      discount_rate:   parseFloat(drafts[s.id].discount_rate_pct) / 100,
      threshold_value: parseFloat(drafts[s.id].threshold_value),
    })
    feedbacks[s.id] = { type: 'ok', msg: 'Salvo com sucesso.' }
  } catch (e) {
    const errs = e.response?.data?.errors ?? [e.response?.data?.error ?? 'Erro ao salvar.']
    feedbacks[s.id] = { type: 'err', msg: errs.join(' ') }
  } finally {
    saving[s.id] = false
  }
}

async function toggleActive(s) {
  try {
    const { data } = await strategiesApi.update(s.id, { is_active: s.is_active ? 0 : 1 })
    const idx = strategies.value.findIndex(x => x.id === s.id)
    if (idx !== -1) strategies.value[idx] = data.data
  } catch {
    error.value = 'Erro ao alterar status.'
  }
}

onMounted(load)
</script>

<style scoped>
.strategy-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,.08);
  margin-bottom: 1.25rem;
  overflow: hidden;
}
.strategy-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: .9rem 1.25rem;
  background: #f0f0f8;
  border-bottom: 1px solid #e4e4f0;
}
.strategy-name { font-weight: 600; font-size: 1rem; }
.strategy-key  { margin-left: .6rem; font-size: .75rem; color: #888; background: #e8e8f4; padding: .15rem .45rem; border-radius: 10px; }
.strategy-body { padding: 1.25rem; }
.strategy-body.disabled { opacity: .5; pointer-events: none; }
.field-row { display: flex; gap: 1rem; align-items: flex-start; flex-wrap: wrap; }
.field-row .form-group { flex: 1; min-width: 160px; }
.input-suffix { display: flex; align-items: center; gap: .4rem; }
.input-suffix input { flex: 1; }
.input-suffix span { color: #666; font-size: .9rem; white-space: nowrap; }
.strategy-hint { margin-top: .75rem; font-size: .82rem; color: #666; }
.toggle { display: flex; align-items: center; gap: .5rem; cursor: pointer; user-select: none; }
.toggle input { display: none; }
.toggle-track {
  width: 40px; height: 22px; background: #ccc; border-radius: 11px;
  position: relative; transition: background .2s;
}
.toggle input:checked ~ .toggle-track { background: #1a1a2e; }
.toggle-track::after {
  content: ''; position: absolute; top: 3px; left: 3px;
  width: 16px; height: 16px; border-radius: 50%; background: #fff;
  transition: left .2s;
}
.toggle input:checked ~ .toggle-track::after { left: 21px; }
.toggle-label { font-size: .85rem; font-weight: 500; }
.feedback { margin-top: .75rem; font-size: .85rem; padding: .4rem .75rem; border-radius: 4px; }
.feedback.ok  { background: #d4edda; color: #155724; }
.feedback.err { background: #f8d7da; color: #721c24; }
</style>
