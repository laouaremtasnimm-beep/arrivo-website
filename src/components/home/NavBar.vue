<template>
  <nav class="navbar" :class="{ 
    'navbar--scrolled': scrolled || !isHome,
    'navbar--home': isHome && !scrolled
  }">

    <!-- Logo -->
    <RouterLink to="/" class="logo" @click="closeAll">Voya<span>go</span></RouterLink>

    <!-- Desktop nav -->
    <ul class="navbar__links" @mouseleave="activeMenu = null">

      <!-- Explore dropdown -->
      <li class="nav-item" @mouseenter="activeMenu = 'explore'">
        <button class="nav-link" :class="{ active: activeMenu === 'explore' }">
          Explore
          <svg class="nav-chevron" :class="{ open: activeMenu === 'explore' }" width="12" height="12" viewBox="0 0 12 12" fill="none">
            <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <Transition name="menu-drop">
          <div v-if="activeMenu === 'explore'" class="mega-menu mega-menu--sm">
            <RouterLink v-for="item in exploreLinks" :key="item.to"
              :to="item.to" class="mega-item" @click="closeAll">
              <span class="mega-icon">{{ item.icon }}</span>
              <span class="mega-text">
                <span class="mega-title">{{ item.title }}</span>
                <span class="mega-sub">{{ item.sub }}</span>
              </span>
            </RouterLink>
          </div>
        </Transition>
      </li>

      <!-- Deals — direct link with badge -->
      <li class="nav-item">
        <RouterLink to="/deals" class="nav-link nav-link--deals">
          Deals
          <span class="nav-badge">Hot</span>
        </RouterLink>
      </li>

      <!-- Travel Tools dropdown -->
      <li class="nav-item" @mouseenter="activeMenu = 'tools'">
        <button class="nav-link" :class="{ active: activeMenu === 'tools' }">
          Travel Tools
          <svg class="nav-chevron" :class="{ open: activeMenu === 'tools' }" width="12" height="12" viewBox="0 0 12 12" fill="none">
            <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <Transition name="menu-drop">
          <div v-if="activeMenu === 'tools'" class="mega-menu mega-menu--sm">
            <RouterLink v-for="item in toolLinks" :key="item.to"
              :to="item.to" class="mega-item" @click="closeAll">
              <span class="mega-icon">{{ item.icon }}</span>
              <span class="mega-text">
                <span class="mega-title">{{ item.title }}</span>
                <span class="mega-sub">{{ item.sub }}</span>
              </span>
            </RouterLink>
          </div>
        </Transition>
      </li>

      <!-- Partners mega-menu -->
      <li class="nav-item" @mouseenter="activeMenu = 'partners'">
        <button class="nav-link" :class="{ active: activeMenu === 'partners' }">
          Partners
          <svg class="nav-chevron" :class="{ open: activeMenu === 'partners' }" width="12" height="12" viewBox="0 0 12 12" fill="none">
            <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <Transition name="menu-drop">
          <div v-if="activeMenu === 'partners'" class="mega-menu mega-menu--wide">
            <div class="mega-col">
              <p class="mega-col-label">For Business</p>
              <RouterLink v-for="item in partnerLinks" :key="item.to"
                :to="item.to" class="mega-item" @click="closeAll">
                <span class="mega-icon">{{ item.icon }}</span>
                <span class="mega-text">
                  <span class="mega-title">{{ item.title }}</span>
                  <span class="mega-sub">{{ item.sub }}</span>
                </span>
              </RouterLink>
            </div>
            <div class="mega-promo">
              <div class="mega-promo__badge">✨ New</div>
              <p class="mega-promo__title">Grow your travel business</p>
              <p class="mega-promo__sub">Join 2,000+ agencies already using Voyago to reach more travelers worldwide.</p>
              <RouterLink to="/auth?mode=register" class="btn btn-coral mega-promo__btn" @click="closeAll">
                Join now →
              </RouterLink>
            </div>
          </div>
        </Transition>
      </li>

    </ul>

    <!-- Actions -->
    <div class="navbar__actions">

      <!-- Wishlist (logged in) -->
      <RouterLink v-if="isLoggedIn" to="/wishlist" class="navbar__icon-btn" title="Wishlist">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <span class="navbar__notif-badge" v-if="wishlistCount > 0">{{ wishlistCount }}</span>
      </RouterLink>

      <!-- Logged out -->
      <template v-if="!isLoggedIn">
        <button class="btn btn-outline" @click="router.push('/auth?mode=login')">Sign in</button>
        <button class="btn btn-coral"   @click="router.push('/auth?mode=register')">Get started</button>
      </template>

      <!-- Logged in -->
      <template v-else>
        <RouterLink v-if="canAccessDashboard" to="/dashboard" class="btn btn-outline">Dashboard</RouterLink>

        <!-- Messages -->
        <div class="notif-trigger" ref="msgRef">
          <button
            class="navbar__icon-btn"
            :class="{ active: msgOpen }"
            @click="toggleMsgPanel"
            title="Messages"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <span class="navbar__notif-badge" v-if="unreadMsgCount > 0">
              {{ unreadMsgCount > 9 ? '9+' : unreadMsgCount }}
            </span>
          </button>
        </div>

        <!-- Notification bell -->
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
            <span class="navbar__notif-badge" v-if="unreadNotifCount > 0">
              {{ unreadNotifCount > 9 ? '9+' : unreadNotifCount }}
            </span>
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
              <RouterLink to="/profile"   class="dropdown-item" @click="menuOpen = false">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                My Profile
              </RouterLink>
              <RouterLink to="/bookings" class="dropdown-item" @click="menuOpen = false">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M8 6h13M8 12h13M8 18h13"/>
    <path d="M3 6h.01M3 12h.01M3 18h.01"/>
  </svg>
  My Bookings
</RouterLink>
              <RouterLink to="/wishlist"  class="dropdown-item" @click="menuOpen = false">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Wishlist
              </RouterLink>
              <RouterLink to="/settings"  class="dropdown-item" @click="menuOpen = false">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Settings
              </RouterLink>
              <RouterLink v-if="canAccessDashboard" to="/dashboard" class="dropdown-item" @click="menuOpen = false">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
              </RouterLink>
              <div class="dropdown-divider"></div>
              <button class="dropdown-item dropdown-logout" @click="handleLogout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
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

    <!-- Mobile drawer -->
    <Transition name="slide-down">
      <div class="navbar__drawer" v-if="mobileOpen">

        <div class="drawer-section">
          <p class="drawer-label">Explore</p>
          <RouterLink v-for="item in exploreLinks" :key="item.to" :to="item.to" class="drawer-link" @click="mobileOpen = false">
            {{ item.icon }} {{ item.title }}
          </RouterLink>
        </div>

        <div class="drawer-section">
          <p class="drawer-label">Travel Tools</p>
          <RouterLink v-for="item in toolLinks" :key="item.to" :to="item.to" class="drawer-link" @click="mobileOpen = false">
            {{ item.icon }} {{ item.title }}
          </RouterLink>
          <RouterLink to="/deals" class="drawer-link" @click="mobileOpen = false">🔥 Deals</RouterLink>
        </div>

        <div class="drawer-section">
          <p class="drawer-label">Partners</p>
          <RouterLink v-for="item in partnerLinks" :key="item.to" :to="item.to" class="drawer-link" @click="mobileOpen = false">
            {{ item.icon }} {{ item.title }}
          </RouterLink>
        </div>

        <div class="drawer-actions">
          <template v-if="!isLoggedIn">
            <button class="btn btn-outline" @click="router.push('/auth?mode=login');    mobileOpen = false">Sign in</button>
            <button class="btn btn-coral"   @click="router.push('/auth?mode=register'); mobileOpen = false">Get started</button>
          </template>
          <template v-else>
            <RouterLink to="/profile"  class="btn btn-outline" @click="mobileOpen = false">My Profile</RouterLink>
            <RouterLink v-if="canAccessDashboard" to="/dashboard" class="btn btn-outline" @click="mobileOpen = false">Dashboard</RouterLink>
            <button class="btn btn-coral" @click="handleLogout; mobileOpen = false">Log out</button>
          </template>
        </div>

      </div>
    </Transition>

    <!-- Notification panel -->
    <NotificationPanel
      v-model="notifOpen"
      :role="user?.role"
      :current-user-id="user?.userID ?? user?.id"
      :anchor="bellAnchor"
    />

    <!-- Message panel -->
    <MessagePanel
      v-model="msgOpen"
      :role="user?.role"
      :current-user-id="user?.userID ?? user?.id"
      :anchor="msgAnchor"
    />

  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useNotifications } from '@/composables/useNotifications'
import { useMessages } from '@/composables/useMessages'
import { useWishlist } from '@/composables/useWishlist.js'
import NotificationPanel from '@/components/shared/NotificationPanel.vue'
import MessagePanel from '@/components/shared/MessagePanel.vue'

const router = useRouter()
const route  = useRoute()
const { user, isLoggedIn, canAccessDashboard, logout } = useAuth()
const { unreadCount: getUnreadCount } = useNotifications()
const { fetchMessages, getUnreadCount: getMsgUnreadCount } = useMessages()
const { entries: wishlistEntries } = useWishlist()

const unreadNotifCount = getUnreadCount(user.value?.role, user.value?.userID ?? user.value?.id, 'notification')
const unreadMsgCount   = getMsgUnreadCount(user.value?.userID ?? user.value?.id)

const wishlistCount = computed(() => wishlistEntries.value.length)

const isHome = computed(() => route.path === '/')

// ── Nav menu data ──────────────────────────────────────────────────────────
const exploreLinks = [
  { to: '/destinations', icon: '📍', title: 'Destinations',     sub: 'Browse curated places worldwide'     },
  { to: '/packages',     icon: '✈️', title: 'Travel Packages',  sub: 'All-inclusive tours & itineraries'   },
  { to: '/services',     icon: '🛎️', title: 'Travel Services',  sub: 'Guides, transfers, experiences'      },
  { to: '/search',       icon: '🔍', title: 'Search Everything', sub: 'Find exactly what you need'          },
]

const toolLinks = [
  { to: '/planner',       icon: '🗺️', title: 'Trip Planner',     sub: 'Build your perfect itinerary'        },
  { to: '/deals',         icon: '🔥', title: 'Hot Deals',         sub: 'Limited-time offers & flash sales'   },
  { to: '/guides',        icon: '📖', title: 'Travel Guides',     sub: 'Tips, culture, and local insights'   },
  { to: '/visa-info',     icon: '📄', title: 'Visa & Entry Info', sub: 'Requirements by destination'         },
  { to: '/travel-insurance', icon: '🛡️', title: 'Travel Insurance', sub: 'Protect your trip'               },
]

const partnerLinks = [
  { to: '/partners/agencies',   icon: '🏢', title: 'For Travel Agencies', sub: 'List packages, manage bookings'      },
  { to: '/partners/providers',  icon: '🤝', title: 'For Service Providers', sub: 'Offer guides, transfers, tours'    },
  { to: '/partners/affiliates', icon: '💰', title: 'Affiliate Program',   sub: 'Earn by recommending Voyago'         },
]

// ── UI state ───────────────────────────────────────────────────────────────
const scrolled   = ref(false)
const mobileOpen = ref(false)
const menuOpen   = ref(false)
const activeMenu = ref(null)   // 'explore' | 'tools' | 'partners' | null
const menuRef    = ref(null)
const bellRef    = ref(null)
const msgRef     = ref(null)
const notifOpen  = ref(false)
const msgOpen    = ref(false)
const bellAnchor = ref(null)
const msgAnchor  = ref(null)

function closeAll() {
  activeMenu.value = null
  menuOpen.value   = false
  mobileOpen.value = false
}

const unreadCount = computed(() => getUnreadCount(user.value?.role, user.value?.userID ?? user.value?.id).value)
const initials    = computed(() => {
  if (!user.value?.name) return '?'
  return user.value.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})
const roleLabel = computed(() => ({
  tourist:  'Traveler',
  agency:   'Travel Agency',
  provider: 'Service Provider',
}[user.value?.role] || ''))

function toggleNotifPanel() {
  if (bellRef.value) bellAnchor.value = bellRef.value.getBoundingClientRect()
  notifOpen.value = !notifOpen.value
  if (notifOpen.value) msgOpen.value = false
  if (menuOpen.value) menuOpen.value = false
}

function toggleMsgPanel() {
  if (msgRef.value) msgAnchor.value = msgRef.value.getBoundingClientRect()
  msgOpen.value = !msgOpen.value
  if (msgOpen.value) notifOpen.value = false
  if (menuOpen.value) menuOpen.value = false
}

function handleLogout() {
  closeAll()
  logout()
  router.push('/')
}

function onScroll() { scrolled.value = window.scrollY > 50 }

function onClickOutside(e) {
  if (menuRef.value && !menuRef.value.contains(e.target)) menuOpen.value = false
}

onMounted(() => {
  if (isLoggedIn.value) {
    fetchMessages(user.value.userID || user.value.id)
  }
  window.addEventListener('scroll', onScroll)
  document.addEventListener('click', onClickOutside)
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  document.removeEventListener('click', onClickOutside)
})
</script>

<style scoped>
/* ── Base ────────────────────────────────────────────────────────────────── */
.navbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 5%; height: 68px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  box-shadow: 0 1px 12px rgba(0,0,0,0.06);
  transition: all 0.3s ease;
}
.navbar--home {
  background: transparent;
  backdrop-filter: none;
  box-shadow: none;
}
.navbar--scrolled {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  box-shadow: 0 1px 12px rgba(0,0,0,0.06);
}

/* ── Logo ────────────────────────────────────────────────────────────────── */
.logo {
  font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700;
  color: var(--indigo); letter-spacing: -.5px; text-decoration: none;
  flex-shrink: 0;
  transition: color 0.3s ease;
}
.navbar--home .logo { color: white; }
.navbar--scrolled .logo { color: var(--indigo); }
.logo span { color: var(--coral); }

/* ── Nav links list ──────────────────────────────────────────────────────── */
.navbar__links {
  display: flex; align-items: center; gap: 2px;
  list-style: none; margin: 0; padding: 0;
  height: 100%;
}

.nav-item {
  position: relative; height: 100%;
  display: flex; align-items: center;
}

/* Nav link — button or RouterLink */
.nav-link {
  display: flex; align-items: center; gap: 5px;
  padding: 8px 14px; border-radius: 8px;
  border: none; background: none; cursor: pointer;
  font-family: 'DM Sans', sans-serif; font-size: .9rem; font-weight: 500;
  color: var(--gray-600); text-decoration: none;
  transition: all 0.3s ease;
  white-space: nowrap; line-height: 1;
}
.navbar--home .nav-link { color: white; }
.navbar--scrolled .nav-link { color: var(--gray-600); }
.nav-link:hover,
.nav-link.active,
.nav-link.router-link-active { color: var(--coral); background: var(--coral-lt); }
.navbar--home .nav-link:hover,
.navbar--home .nav-link.active,
.navbar--home .nav-link.router-link-active { color: var(--coral); background: rgba(255,255,255,0.1); }

/* Hot deals badge */
.nav-link--deals { font-weight: 600; }
.nav-badge {
  display: inline-flex; align-items: center;
  background: var(--coral); color: #fff;
  font-size: .65rem; font-weight: 700; letter-spacing: .04em;
  padding: 2px 6px; border-radius: 50px; line-height: 1.4;
  margin-left: 2px;
  animation: pulse-badge 2s ease-in-out infinite;
}
@keyframes pulse-badge {
  0%, 100% { opacity: 1; }
  50%       { opacity: .7; }
}

.nav-chevron {
  color: var(--gray-400);
  transition: transform .2s ease;
  flex-shrink: 0;
}
.nav-chevron.open { transform: rotate(180deg); }

/* ── Mega menus ──────────────────────────────────────────────────────────── */
.mega-menu {
  position: absolute; top: calc(100% + 4px); left: 0;
  background: var(--white);
  border: 1.5px solid var(--gray-200);
  border-radius: var(--radius);
  box-shadow: var(--shadow-lg);
  z-index: 300;
  padding: 8px;
}

.mega-menu--sm  { min-width: 280px; }
.mega-menu--wide {
  min-width: 520px;
  display: grid; grid-template-columns: 1fr 200px;
  padding: 0; overflow: hidden;
}

/* ── Mega items ──────────────────────────────────────────────────────────── */
.mega-item {
  display: flex; align-items: center; gap: 12px;
  padding: 11px 14px; border-radius: var(--radius-sm);
  text-decoration: none; color: var(--indigo);
  transition: background var(--transition);
  cursor: pointer;
}
.mega-item:hover { background: var(--gray-50); }

.mega-icon {
  width: 36px; height: 36px; border-radius: 10px;
  background: var(--gray-100);
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0;
  transition: background var(--transition);
}
.mega-item:hover .mega-icon { background: var(--coral-lt); }

.mega-text    { display: flex; flex-direction: column; gap: 2px; }
.mega-title   { font-size: .88rem; font-weight: 600; color: var(--indigo); }
.mega-sub     { font-size: .76rem; color: var(--gray-400); line-height: 1.3; }

/* Wide menu col */
.mega-col { padding: 12px 8px; }
.mega-col-label {
  font-size: .7rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--gray-400);
  padding: 6px 14px 4px;
}

/* Promo column */
.mega-promo {
  background: linear-gradient(145deg, var(--indigo) 0%, #3d4561 100%);
  padding: 24px 20px;
  display: flex; flex-direction: column; gap: 10px;
  justify-content: center;
}
.mega-promo__badge {
  display: inline-flex; width: fit-content;
  background: var(--coral); color: #fff;
  font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 50px;
}
.mega-promo__title { font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700; color: #fff; line-height: 1.2; }
.mega-promo__sub   { font-size: .78rem; color: rgba(255,255,255,.65); line-height: 1.5; }
.mega-promo__btn   { padding: 9px 16px; font-size: .82rem; border-radius: 10px; margin-top: 4px; }

/* Transition */
.menu-drop-enter-active,
.menu-drop-leave-active { transition: opacity .16s ease, transform .16s cubic-bezier(.4,0,.2,1); }
.menu-drop-enter-from,
.menu-drop-leave-to     { opacity: 0; transform: translateY(-6px); }

/* ── Actions area ────────────────────────────────────────────────────────── */
.navbar__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

/* Icon buttons (bell, wishlist) */
.navbar__icon-btn {
  width: 38px; height: 38px; border-radius: 10px; border: none;
  background: var(--gray-100); color: var(--indigo);
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  position: relative; transition: all 0.3s ease; text-decoration: none;
}
.navbar--home .navbar__icon-btn { background: rgba(255,255,255,0.1); color: white; }
.navbar--scrolled .navbar__icon-btn { background: var(--gray-100); color: var(--indigo); }
.navbar__icon-btn:hover,
.navbar__icon-btn.active { background: var(--gray-200); }
.navbar--home .navbar__icon-btn:hover,
.navbar--home .navbar__icon-btn.active { background: rgba(255,255,255,0.2); }
.navbar__notif-badge {
  position: absolute; top: 5px; right: 5px;
  min-width: 15px; height: 15px; padding: 0 3px;
  background: var(--coral); color: #fff; border-radius: 50px;
  font-size: .58rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff;
}

/* Avatar / user menu */
.user-menu { position: relative; }
.avatar-btn {
  display: flex; align-items: center; gap: 8px;
  background: none; border: 1.5px solid var(--gray-200);
  border-radius: 50px; padding: 4px 12px 4px 4px;
  cursor: pointer; transition: all 0.3s ease;
  color: var(--indigo);
}
.navbar--home .avatar-btn { border-color: rgba(255,255,255,0.3); color: white; }
.navbar--scrolled .avatar-btn { border-color: var(--gray-200); color: var(--indigo); }
.avatar-btn:hover { border-color: var(--teal); }
.navbar--home .avatar-btn:hover { border-color: white; }
.nav-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  background: var(--teal-lt, rgba(46,196,182,.10)); color: var(--teal, #2EC4B6);
  font-size: 12px; font-weight: 700; font-family: 'DM Sans', sans-serif;
  display: flex; align-items: center; justify-content: center;
}
.chevron { color: var(--gray-600); transition: transform var(--transition); }
.chevron.open { transform: rotate(180deg); }

/* Avatar dropdown */
.dropdown-menu {
  position: absolute; right: 0; top: calc(100% + 10px);
  width: 240px; background: #fff;
  border: 1.5px solid var(--gray-200); border-radius: var(--radius);
  box-shadow: var(--shadow-md); z-index: 300; overflow: hidden;
}
.dropdown-header  { padding: 16px 16px 12px; }
.dropdown-name    { font-size: 14px; font-weight: 700; color: var(--indigo); margin: 0 0 2px; }
.dropdown-email   { font-size: 12px; color: var(--gray-600); margin: 0 0 8px; }
.dropdown-role    { display: inline-block; font-size: 11px; font-weight: 600; padding: 2px 10px; border-radius: 50px; }
.role-tourist     { background: var(--teal-lt, rgba(46,196,182,.10)); color: var(--teal, #2EC4B6); }
.role-agency      { background: rgba(255,90,95,.10); color: var(--coral); }
.role-provider    { background: rgba(45,49,66,.08); color: var(--indigo); }
.dropdown-divider { height: 1px; background: var(--gray-200); margin: 4px 0; }
.dropdown-item {
  display: flex; align-items: center; gap: 10px; padding: 10px 16px;
  font-size: 14px; font-weight: 500; color: var(--indigo);
  text-decoration: none; background: none; border: none; width: 100%;
  cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition); text-align: left;
}
.dropdown-item:hover   { background: var(--gray-50); }
.dropdown-logout       { color: var(--coral); }
.dropdown-logout:hover { background: rgba(255,90,95,.07); }

.dropdown-enter-active, .dropdown-leave-active { transition: opacity .18s ease, transform .18s ease; }
.dropdown-enter-from,   .dropdown-leave-to     { opacity: 0; transform: translateY(-8px); }

/* ── Mobile burger ───────────────────────────────────────────────────────── */
.navbar__burger {
  display: none; flex-direction: column; gap: 5px;
  background: none; border: none; cursor: pointer; padding: 6px;
}
.navbar__burger span {
  display: block; width: 24px; height: 2px;
  background: var(--indigo); border-radius: 2px;
  transition: transform .22s ease, opacity .22s ease, background 0.3s ease;
}
.navbar--home .navbar__burger span { background: white; }
.navbar--scrolled .navbar__burger span { background: var(--indigo); }
.navbar__burger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.navbar__burger.open span:nth-child(2) { opacity: 0; }
.navbar__burger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ── Mobile drawer ───────────────────────────────────────────────────────── */
.navbar__drawer {
  position: fixed; top: 68px; left: 0; right: 0; bottom: 0;
  background: var(--white); overflow-y: auto;
  padding: 20px 5% 32px;
  box-shadow: var(--shadow-lg);
  display: flex; flex-direction: column; gap: 24px;
}

.drawer-section { display: flex; flex-direction: column; gap: 4px; }
.drawer-label {
  font-size: .7rem; font-weight: 700; letter-spacing: .09em;
  text-transform: uppercase; color: var(--gray-400);
  padding: 0 4px; margin-bottom: 4px;
}
.drawer-link {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px; border-radius: var(--radius-sm);
  font-size: .95rem; font-weight: 500; color: var(--indigo);
  text-decoration: none; transition: background var(--transition);
}
.drawer-link:hover { background: var(--gray-100); }

.drawer-actions { display: flex; gap: 10px; flex-wrap: wrap; padding-top: 8px; border-top: 1px solid var(--gray-100); }
.drawer-actions .btn { flex: 1; justify-content: center; }

.slide-down-enter-active, .slide-down-leave-active { transition: opacity .22s ease, transform .22s ease; }
.slide-down-enter-from,   .slide-down-leave-to     { opacity: 0; transform: translateY(-8px); }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1024px) {
  .navbar__links { gap: 0; }
  .nav-link { padding: 8px 10px; font-size: .85rem; }
}
@media (max-width: 768px) {
  .navbar__links, .navbar__actions { display: none; }
  .navbar__burger { display: flex; }
}
</style>