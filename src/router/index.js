// ─── Full updated router/index.js for reference ───
import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/composables/useAuth.js'

import Home             from '@/views/Home.vue'
import AuthPage         from '@/views/AuthPage.vue'
import SearchPage       from '@/views/SearchPage.vue'
import DestinationsPage from '@/views/DestinationsPage.vue'
import PackagesPage     from '@/views/PackagesPage.vue'
import ServicesPage     from '@/views/ServicesPage.vue'
import DestinationDetail from '@/views/DestinationDetail.vue'
import PackageDetail    from '@/views/PackageDetail.vue'
import ServiceDetail    from '@/views/ServiceDetail.vue'
import DashboardPage    from '@/views/DashboardPage.vue'
import ProfilePage      from '@/views/ProfilePage.vue'  
import SettingsPage     from '@/views/SettingsPage.vue'
import DealsPage        from '@/views/DealsPage.vue'

const routes = [
  { path: '/',                  component: Home },
  { path: '/auth',              component: AuthPage },
  { path: '/search',            component: SearchPage },
  { path: '/destinations',      component: DestinationsPage },
  { path: '/destinations/:id',  component: DestinationDetail },
  { path: '/packages',          component: PackagesPage },
  { path: '/packages/:id',      component: PackageDetail },
  { path: '/services',          component: ServicesPage },
  { path: '/services/:id',      component: ServiceDetail },
  {
    path: '/dashboard',
    component: DashboardPage,
    beforeEnter: (to, from, next) => {
      const { canAccessDashboard } = useAuth()
      if (!canAccessDashboard.value) next('/auth?mode=login&redirect=/dashboard')
      else next()
    }
  },
  {
    path: '/profile',            // ← NEW
    component: ProfilePage,
    beforeEnter: (to, from, next) => {
      const { isLoggedIn } = useAuth()
      if (!isLoggedIn.value) next('/auth?mode=login&redirect=/profile')
      else next()
    }
  },
  {
  path: '/settings',
  component: SettingsPage,
  beforeEnter: (to, from, next) => {
    const { isLoggedIn } = useAuth()
    if (!isLoggedIn.value) next('/auth?mode=login&redirect=/settings')
    else next()
  }
},
{ path: '/deals', component: DealsPage }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

export default router