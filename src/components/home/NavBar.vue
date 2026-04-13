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

      <!-- Logged out -->
      <template v-if="!isLoggedIn">
        <button class="btn btn-outline" @click="router.push('/auth?mode=login')">Sign in</button>
        <button class="btn btn-coral"   @click="router.push('/auth?mode=register')">Register</button>
      </template>

      <!-- Logged in -->
      <template v-else>
        <RouterLink v-if="canAccessDashboard" to="/dashboard" class="btn btn-outline">Dashboard</RouterLink>

        <!-- ── Notification bell ── -->
        <div class="notif-trigger" ref="bellRef">
          <button
            class="navbar__icon-btn"
            :class="{ active: notifOpen }"
            @click="toggleNotifPanel"
            title="Notifications"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="navbar__notif-badge" v-if="unreadCount > 0">{{ unreadCount }}</span>
          </button>
        </div>

        <!-- Avatar dropdown -->
        <div class="user-menu" ref="menuRef">
          <button class="avatar-btn" @click="menuOpen = !menuOpen">
            <div class="nav-avatar">{{ initials }}</div>
            <svg class="chevron" :class="{ open: menuOpen }" width="14" height="14"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>

          <Transition name="dropdown">
            <div v-if="menuOpen" class="dropdown-menu">
              <div class="dropdown-header">
                <p class="dropdown-name">{{ user.name }}</p>
                <p class="dropdown-email">{{ user.email }}</p>
                <span class="dropdown-role" :class="`role-${user.role}`">{{ roleLabel }}</span>
              </div>
              <div class="dropdown-divider"></div>
              <RouterLink to="/profile" class="dropdown-item" @click="menuOpen = false">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                My Profile
              </RouterLink>
              <RouterLink to="/settings" class="dropdown-item" @click="menuOpen = false">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="3"/>
                  <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                Settings
              </RouterLink>
              <RouterLink v-if="canAccessDashboard" to="/dashboard" class="dropdown-item" @click="menuOpen = false">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                  <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                Dashboard
              </RouterLink>
              <div class="dropdown-divider"></div>
              <button class="dropdown-item dropdown-logout" @click="handleLogout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                  <polyline points="16 17 21 12 16 7"/>
                  <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Log out
              </button>
            </div>
          </Transition>
        </div>
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
          <template v-if="!isLoggedIn">
            <button class="btn btn-outline" @click="router.push('/auth?mode=login');    mobileOpen = false">Sign in</button>
            <button class="btn btn-coral"   @click="router.push('/auth?mode=register'); mobileOpen = false">Join free</button>
          </template>
          <template v-else>
            <RouterLink to="/profile" class="btn btn-outline" @click="mobileOpen = false">My Profile</RouterLink>
            <RouterLink v-if="canAccessDashboard" to="/dashboard" class="btn btn-outline" @click="mobileOpen = false">Dashboard</RouterLink>
            <button class="btn btn-coral" @click="handleLogout; mobileOpen = false">Log out</button>
          </template>
        </div>
      </div>
    </Transition>

    <!-- ── Notification panel (shared, floats over everything) ── -->
    <NotificationPanel
      v-model="notifOpen"
      :role="user?.role"
      :anchor="bellAnchor"
    />

  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useNotifications } from '@/composables/useNotifications'
import NotificationPanel from '@/components/shared/NotificationPanel.vue'

const router = useRouter()
const { user, isLoggedIn, canAccessDashboard, logout } = useAuth()
const { unreadCount: getUnreadCount } = useNotifications()

const scrolled   = ref(false)
const mobileOpen = ref(false)
const menuOpen   = ref(false)
const menuRef    = ref(null)
const bellRef    = ref(null)
const notifOpen  = ref(false)
const bellAnchor = ref(null)

const unreadCount = computed(() => getUnreadCount(user.value?.role).value)

const initials = computed(() => {
  if (!user.value?.name) return '?'
  return user.value.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})
const roleLabel = computed(() => ({
  tourist:  'Traveler',
  agency:   'Travel Agency',
  provider: 'Service Provider',
}[user.value?.role] || ''))

function toggleNotifPanel() {
  // Capture anchor position for panel placement
  if (bellRef.value) {
    bellAnchor.value = bellRef.value.getBoundingClientRect()
  }
  notifOpen.value = !notifOpen.value
  // Close avatar dropdown if open
  if (menuOpen.value) menuOpen.value = false
}

function handleLogout() {
  menuOpen.value  = false
  notifOpen.value = false
  logout()
  router.push('/')
}

function onScroll() { scrolled.value = window.scrollY > 30 }

function onClickOutside(e) {
  if (menuRef.value && !menuRef.value.contains(e.target)) menuOpen.value = false
}

onMounted(() => {
  window.addEventListener('scroll', onScroll)
  document.addEventListener('click', onClickOutside)
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  document.removeEventListener('click', onClickOutside)
})
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
  font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700;
  color: var(--indigo); letter-spacing: -.5px; text-decoration: none;
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

/* Bell button */
.notif-trigger { position: relative; }
.navbar__icon-btn {
  width: 40px; height: 40px; border-radius: 10px; border: none;
  background: var(--gray-100); color: var(--indigo);
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  position: relative; transition: all var(--transition);
}
.navbar__icon-btn:hover,
.navbar__icon-btn.active { background: var(--gray-200); }
.navbar__notif-badge {
  position: absolute; top: 6px; right: 6px;
  min-width: 16px; height: 16px; padding: 0 4px;
  background: var(--coral); color: #fff; border-radius: 50px;
  font-size: .6rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff;
}

/* Avatar button */
.user-menu { position: relative; }
.avatar-btn {
  display: flex; align-items: center; gap: 8px;
  background: none; border: 1.5px solid var(--gray-200);
  border-radius: 50px; padding: 4px 12px 4px 4px;
  cursor: pointer; transition: border-color var(--transition);
}
.avatar-btn:hover { border-color: var(--teal); }
.nav-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--teal-lt, rgba(46,196,182,.10));
  color: var(--teal, #2EC4B6);
  font-size: 13px; font-weight: 700; font-family: 'DM Sans', sans-serif;
  display: flex; align-items: center; justify-content: center;
}
.chevron { color: var(--gray-600); transition: transform var(--transition); }
.chevron.open { transform: rotate(180deg); }

/* Dropdown */
.dropdown-menu {
  position: absolute; right: 0; top: calc(100% + 10px);
  width: 240px; background: #fff;
  border: 1.5px solid var(--gray-200);
  border-radius: var(--radius, 16px);
  box-shadow: var(--shadow-md, 0 8px 40px rgba(45,49,66,.11));
  z-index: 200; overflow: hidden;
}
.dropdown-header  { padding: 16px 16px 12px; }
.dropdown-name    { font-size: 14px; font-weight: 700; color: var(--indigo); margin: 0 0 2px; }
.dropdown-email   { font-size: 12px; color: var(--gray-600); margin: 0 0 8px; }
.dropdown-role    { display: inline-block; font-size: 11px; font-weight: 600; padding: 2px 10px; border-radius: 50px; }
.role-tourist     { background: var(--teal-lt, rgba(46,196,182,.10)); color: var(--teal, #2EC4B6); }
.role-agency      { background: rgba(255,90,95,.10); color: var(--coral, #FF5A5F); }
.role-provider    { background: rgba(45,49,66,.08); color: var(--indigo, #2D3142); }
.dropdown-divider { height: 1px; background: var(--gray-200, #E8E8E8); margin: 4px 0; }
.dropdown-item {
  display: flex; align-items: center; gap: 10px; padding: 11px 16px;
  font-size: 14px; font-weight: 500; color: var(--indigo);
  text-decoration: none; background: none; border: none; width: 100%;
  cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition); text-align: left;
}
.dropdown-item:hover   { background: var(--gray-50, #F9F9F9); }
.dropdown-logout       { color: var(--coral, #FF5A5F); }
.dropdown-logout:hover { background: rgba(255,90,95,.07); }

/* Dropdown animation */
.dropdown-enter-active, .dropdown-leave-active { transition: opacity .18s ease, transform .18s ease; }
.dropdown-enter-from,   .dropdown-leave-to     { opacity: 0; transform: translateY(-8px); }

/* Burger */
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

/* Mobile drawer */
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
.slide-down-enter-from,   .slide-down-leave-to     { opacity: 0; transform: translateY(-10px); }

@media (max-width: 768px) {
  .navbar__links, .navbar__actions { display: none; }
  .navbar__burger { display: flex; }
}
</style>