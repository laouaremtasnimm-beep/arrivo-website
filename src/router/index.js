import { createRouter, createWebHistory } from 'vue-router'
import Home             from '@/views/Home.vue'
import AuthPage         from '@/views/AuthPage.vue'
import SearchPage       from '@/views/SearchPage.vue'
import DashboardPage    from '@/views/DashboardPage.vue'
import DestinationsPage from '@/views/DestinationsPage.vue'
import PackagesPage     from '@/views/PackagesPage.vue'
import ServicesPage     from '@/views/ServicesPage.vue'

import { useAuth } from '@/composables/useAuth'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    { path: '/',             component: Home             },
    { path: '/auth',         component: AuthPage         },
    { path: '/search',       component: SearchPage       },
    { path: '/destinations', component: DestinationsPage },
    { path: '/packages',     component: PackagesPage     },
    { path: '/services',     component: ServicesPage     },
    {
      path: '/dashboard',
      component: DashboardPage,
      meta: { requiresDashboard: true },
    },
    // ── Catch-all — must stay last ─────────────
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  if (!to.meta.requiresDashboard) return true

  const { canAccessDashboard } = useAuth()

  if (!canAccessDashboard.value) {
    return {
      path: '/auth',
      query: { mode: 'login', redirect: to.fullPath },
    }
  }

  return true
})

export default router