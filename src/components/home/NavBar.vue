<template>
  <nav class="navbar" :class="{ 'navbar--scrolled': scrolled }">

    <RouterLink to="/" class="logo">Voya<span>go</span></RouterLink>

    <ul class="navbar__links">
      <li><RouterLink to="/destinations">Destinations</RouterLink></li>
      <li><RouterLink to="/packages">Packages</RouterLink></li>
      <li><RouterLink to="/services">Services</RouterLink></li>
      <li><RouterLink to="/#agencies">For Agencies</RouterLink></li>
    </ul>

    <div class="navbar__actions">
      <template v-if="canAccessDashboard">
        <RouterLink to="/dashboard" class="btn btn-outline">Dashboard</RouterLink>
        <button class="btn btn-coral" @click="handleLogout">Log out</button>
      </template>
      <template v-else>
        <button class="btn btn-outline" @click="router.push('/auth?mode=login')">Sign in</button>
        <button class="btn btn-coral"   @click="router.push('/auth?mode=register')">Join free</button>
      </template>
    </div>

    <!-- Mobile burger -->
    <button class="navbar__burger" @click="mobileOpen = !mobileOpen" :class="{ open: mobileOpen }">
      <span /><span /><span />
    </button>

    <Transition name="slide-down">
      <div class="navbar__drawer" v-if="mobileOpen">
        <ul>
          <li><RouterLink to="/destinations" @click="mobileOpen = false">Destinations</RouterLink></li>
          <li><RouterLink to="/packages"     @click="mobileOpen = false">Packages</RouterLink></li>
          <li><RouterLink to="/services"     @click="mobileOpen = false">Services</RouterLink></li>
          <li><RouterLink to="/#agencies"    @click="mobileOpen = false">For Agencies</RouterLink></li>
        </ul>
        <div class="drawer-actions">
          <template v-if="canAccessDashboard">
            <RouterLink to="/dashboard" class="btn btn-outline" @click="mobileOpen = false">Dashboard</RouterLink>
            <button class="btn btn-coral" @click="handleLogout; mobileOpen = false">Log out</button>
          </template>
          <template v-else>
            <button class="btn btn-outline" @click="router.push('/auth?mode=login');    mobileOpen = false">Sign in</button>
            <button class="btn btn-coral"   @click="router.push('/auth?mode=register'); mobileOpen = false">Join free</button>
          </template>
        </div>
      </div>
    </Transition>

  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const { canAccessDashboard, logout } = useAuth()

const scrolled   = ref(false)
const mobileOpen = ref(false)

function handleLogout() { logout(); router.push('/') }

function onScroll() { scrolled.value = window.scrollY > 30 }
onMounted(()  => window.addEventListener('scroll', onScroll))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.navbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 5%; height: 72px;
  background: rgba(255,255,255,.90);
  backdrop-filter: blur(14px);
  border-bottom: 1px solid rgba(0,0,0,.06);
  transition: box-shadow var(--transition);
}
.navbar--scrolled { box-shadow: var(--shadow); }

.logo {
  font-family: 'Fraunces', serif;
  font-size: 1.6rem; font-weight: 700;
  color: var(--indigo); letter-spacing: -.5px;
  text-decoration: none;
}
.logo span { color: var(--coral); }

.navbar__links {
  display: flex; align-items: center; gap: 32px;
  list-style: none; font-size: .95rem; font-weight: 500;
}
.navbar__links a {
  color: var(--gray-600); transition: color var(--transition); text-decoration: none;
}
.navbar__links a:hover,
.navbar__links a.router-link-active { color: var(--coral); }

.navbar__actions { display: flex; align-items: center; gap: 12px; }

.navbar__burger {
  display: none; flex-direction: column; gap: 5px;
  background: none; border: none; cursor: pointer; padding: 6px;
}
.navbar__burger span {
  display: block; width: 24px; height: 2px;
  background: var(--indigo); border-radius: 2px;
  transition: transform .22s ease, opacity .22s ease;
}
.navbar__burger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.navbar__burger.open span:nth-child(2) { opacity: 0; }
.navbar__burger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

.navbar__drawer {
  position: fixed; top: 72px; left: 0; right: 0;
  background: var(--white); border-bottom: 1px solid var(--gray-200);
  padding: 24px 5% 28px; box-shadow: var(--shadow-md);
}
.navbar__drawer ul { list-style: none; display: flex; flex-direction: column; gap: 18px; }
.navbar__drawer ul a { font-size: 1.1rem; font-weight: 600; color: var(--indigo); text-decoration: none; }
.drawer-actions { display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap; }
.drawer-actions .btn { flex: 1; justify-content: center; }

.slide-down-enter-active, .slide-down-leave-active { transition: opacity .2s ease, transform .2s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-10px); }

@media (max-width: 768px) {
  .navbar__links, .navbar__actions { display: none; }
  .navbar__burger { display: flex; }
}
</style>