<template>
  <aside class="sidebar" :class="{ 'sidebar--collapsed': collapsed }">

    <!-- Logo + collapse toggle -->
    <div class="sidebar__top">
      <RouterLink to="/" class="sidebar__logo">
        <span class="sidebar__logo-mark">V</span>
        <Transition name="fade-text">
          <span class="sidebar__logo-text" v-if="!collapsed">oyago</span>
        </Transition>
      </RouterLink>
      <button class="sidebar__collapse-btn" @click="$emit('toggle')" :title="collapsed ? 'Expand' : 'Collapse'">
        {{ collapsed ? '→' : '←' }}
      </button>
    </div>

    <!-- User card -->
    <div class="sidebar__user" v-if="!collapsed">
      <div class="sidebar__avatar">{{ initials }}</div>
      <div class="sidebar__user-info">
        <div class="sidebar__user-name">{{ user.name }}</div>
        <div class="sidebar__role-badge" :class="`role--${user.role}`">
          {{ user.role === 'agency' ? '🏢 Agency' : '🛎️ Provider' }}
        </div>
      </div>
    </div>
    <div class="sidebar__avatar sidebar__avatar--mini" v-else :title="user.name">
      {{ initials }}
    </div>

    <!-- Nav links -->
    <nav class="sidebar__nav">
      <div class="sidebar__nav-label" v-if="!collapsed">Menu</div>
      <button
        v-for="link in filteredLinks"
        :key="link.section"
        class="sidebar__link"
        :class="{ active: activeSection === link.section }"
        @click="$emit('navigate', link.section)"
        :title="collapsed ? link.label : ''"
      >
        <span class="sidebar__link-icon">{{ link.icon }}</span>
        <Transition name="fade-text">
          <span class="sidebar__link-label" v-if="!collapsed">{{ link.label }}</span>
        </Transition>
        <span class="sidebar__link-badge" v-if="link.badge && !collapsed">{{ link.badge }}</span>
      </button>
    </nav>

    <!-- Logout -->
    <div class="sidebar__footer">
      <button class="sidebar__logout" @click="$emit('logout')" :title="collapsed ? 'Logout' : ''">
        <span>🚪</span>
        <Transition name="fade-text">
          <span v-if="!collapsed">Log out</span>
        </Transition>
      </button>
    </div>

  </aside>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  user:          { type: Object,  required: true },
  activeSection: { type: String,  default: 'overview' },
  collapsed:     { type: Boolean, default: false },
  unreadMessages:{ type: Number,  default: 0 },
})

defineEmits(['toggle', 'navigate', 'logout'])

const initials = computed(() => {
  const parts = props.user.name?.split(' ') || []
  return parts.map(p => p[0]).slice(0, 2).join('').toUpperCase()
})

const allLinks = [
  { section: 'overview',  to: '#overview',  icon: '📊', label: 'Overview',       roles: ['agency', 'provider'] },
  { section: 'bookings',  to: '#bookings',  icon: '📋', label: 'Bookings',        roles: ['agency', 'provider'] },
  { section: 'packages',  to: '#packages',  icon: '✈️', label: 'Travel Packages', roles: ['agency'] },
  { section: 'services',  to: '#services',  icon: '🛎️', label: 'My Services',     roles: ['provider'] },
  { section: 'messages',  to: '#messages',  icon: '💬', label: 'Messages',         roles: ['agency', 'provider'], get badge() { return props.unreadMessages || null } },
  { section: 'reviews',   to: '#reviews',   icon: '⭐', label: 'Reviews',          roles: ['agency', 'provider'] },
  { section: 'offers',    to: '#offers',    icon: '🏷️', label: 'Special Offers',   roles: ['agency'] },
]

const filteredLinks = computed(() =>
  allLinks.filter(l => l.roles.includes(props.user.role))
)
</script>

<style scoped>
.sidebar {
  width: 256px; min-height: 100vh;
  background: var(--indigo);
  display: flex; flex-direction: column;
  transition: width .25s ease;
  position: relative; flex-shrink: 0;
}
.sidebar--collapsed { width: 72px; }

/* Top */
.sidebar__top {
  display: flex; align-items: center; justify-content: space-between;
  padding: 24px 20px 20px; border-bottom: 1px solid rgba(255,255,255,.08);
}
.sidebar__logo {
  display: flex; align-items: center; gap: 4px;
  font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700;
  text-decoration: none; overflow: hidden; white-space: nowrap;
}
.sidebar__logo-mark { color: var(--coral); }
.sidebar__logo-text { color: #fff; }

.sidebar__collapse-btn {
  background: rgba(255,255,255,.08); border: none; border-radius: 8px;
  color: rgba(255,255,255,.5); width: 28px; height: 28px;
  cursor: pointer; font-size: .85rem; flex-shrink: 0;
  transition: background var(--transition), color var(--transition);
}
.sidebar__collapse-btn:hover { background: rgba(255,255,255,.15); color: #fff; }

/* User */
.sidebar__user {
  display: flex; align-items: center; gap: 12px;
  padding: 20px; border-bottom: 1px solid rgba(255,255,255,.08);
}
.sidebar__avatar {
  width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--coral), var(--teal));
  display: flex; align-items: center; justify-content: center;
  font-family: 'Fraunces', serif; font-size: .95rem; font-weight: 700; color: #fff;
}
.sidebar__avatar--mini {
  margin: 16px auto; cursor: default;
}
.sidebar__user-name  { font-size: .88rem; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar__role-badge {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: .72rem; font-weight: 700; padding: 3px 10px;
  border-radius: 50px; margin-top: 4px;
}
.role--agency   { background: rgba(46,196,182,.2);  color: var(--teal); }
.role--provider { background: rgba(255,90,95,.18);  color: #ff8a8d; }

/* Nav */
.sidebar__nav       { flex: 1; padding: 16px 12px; display: flex; flex-direction: column; gap: 2px; }
.sidebar__nav-label { font-size: .68rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: rgba(255,255,255,.3); padding: 0 8px; margin-bottom: 6px; }

.sidebar__link {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 12px; border-radius: 10px;
  text-decoration: none; color: rgba(255,255,255,.6);
  font-size: .9rem; font-weight: 500; width: 100%;
  background: none; border: none; cursor: pointer;
  transition: all var(--transition); white-space: nowrap; overflow: hidden;
  font-family: 'DM Sans', sans-serif; text-align: left;
}
.sidebar__link:hover  { background: rgba(255,255,255,.08); color: #fff; }
.sidebar__link.active { background: rgba(255,255,255,.12); color: #fff; font-weight: 600; }

.sidebar__link-icon  { font-size: 1.1rem; flex-shrink: 0; }
.sidebar__link-label { flex: 1; }
.sidebar__link-badge {
  background: var(--coral); color: #fff;
  font-size: .68rem; font-weight: 700;
  padding: 2px 7px; border-radius: 50px;
}

/* Footer */
.sidebar__footer { padding: 16px 12px; border-top: 1px solid rgba(255,255,255,.08); }
.sidebar__logout {
  display: flex; align-items: center; gap: 12px;
  width: 100%; padding: 10px 12px; border-radius: 10px;
  background: none; border: none; cursor: pointer;
  color: rgba(255,255,255,.5); font-family: 'DM Sans', sans-serif;
  font-size: .9rem; font-weight: 500;
  transition: all var(--transition);
}
.sidebar__logout:hover { background: rgba(255,90,95,.15); color: #ff8a8d; }

/* Text fade for collapse */
.fade-text-enter-active, .fade-text-leave-active { transition: opacity .15s ease; }
.fade-text-enter-from, .fade-text-leave-to { opacity: 0; }
</style>