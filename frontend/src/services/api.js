import axios from 'axios'

const http = axios.create({ baseURL: '/api' })

// ─── Clients ─────────────────────────────────────────────────────────────────
export const clientsApi = {
  list:    (page = 1, perPage = 15) => http.get('/clients',      { params: { page, per_page: perPage } }),
  get:     (id)                      => http.get(`/clients/${id}`),
  create:  (data)                    => http.post('/clients', data),
  update:  (id, data)                => http.put(`/clients/${id}`, data),
  remove:  (id)                      => http.delete(`/clients/${id}`),
}

// ─── Services ────────────────────────────────────────────────────────────────
export const servicesApi = {
  list:    (page = 1, perPage = 15) => http.get('/services',      { params: { page, per_page: perPage } }),
  get:     (id)                      => http.get(`/services/${id}`),
  create:  (data)                    => http.post('/services', data),
  update:  (id, data)                => http.put(`/services/${id}`, data),
  remove:  (id)                      => http.delete(`/services/${id}`),
}

// ─── Strategies ──────────────────────────────────────────────────────────────
export const strategiesApi = {
  list:   ()        => http.get('/strategies'),
  update: (id, data) => http.put(`/strategies/${id}`, data),
}
export const contractsApi = {
  list:       (page = 1, perPage = 15) => http.get('/contracts',           { params: { page, per_page: perPage } }),
  get:        (id)                      => http.get(`/contracts/${id}`),
  create:     (data)                    => http.post('/contracts', data),
  cancel:     (id)                      => http.put(`/contracts/${id}/status`, { status: 'canceled' }),
  addItem:    (id, data)                => http.post(`/contracts/${id}/items`, data),
  removeItem: (id, itemId)              => http.delete(`/contracts/${id}/items/${itemId}`),
}
