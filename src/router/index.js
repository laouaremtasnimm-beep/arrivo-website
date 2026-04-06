import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import AuthPage from '@/views/AuthPage.vue'
import SearchPage from '@/views/SearchPage.vue'



export default createRouter({
  history: createWebHistory(),
  routes: [{ path: '/', component: Home },
    { path: '/auth', component: AuthPage },
    { path: '/search', component: SearchPage }
  ]
})



