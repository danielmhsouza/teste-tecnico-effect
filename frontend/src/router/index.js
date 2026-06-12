import { createRouter, createWebHistory } from 'vue-router'
import ClientsView    from '../views/ClientsView.vue'
import ServicesView   from '../views/ServicesView.vue'
import ContractsView  from '../views/ContractsView.vue'
import StrategiesView from '../views/StrategiesView.vue'

const routes = [
  { path: '/',            redirect: '/contracts' },
  { path: '/clients',     component: ClientsView },
  { path: '/services',    component: ServicesView },
  { path: '/contracts',   component: ContractsView },
  { path: '/strategies',  component: StrategiesView },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
