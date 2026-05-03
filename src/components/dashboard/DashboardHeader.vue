<template>
  <header class="dash-header">

    <!-- Mobile sidebar toggle -->
    <button class="dash-header__menu-btn" @click="$emit('open-mobile-sidebar')">☰</button>

    <!-- Greeting -->
    <div class="dash-header__greeting">
      <h1 class="dash-header__title">{{ greeting }}, {{ firstName }} 👋</h1>
      <p class="dash-header__sub">{{ dateLabel }}</p>
    </div>

    <div class="dash-header__right">

      <!-- Quick action -->
      <button class="btn btn-coral dash-header__action" @click="$emit('quick-action')">
        + {{ user.role === 'agency' ? 'New Package' : 'New Service' }}
      </button>

      <!-- Message icon -->
      <div class="notif-trigger" ref="msgRef">
        <button
          class="dash-header__icon-btn"
          :class="{ active: msgOpen }"
          @click="toggleMsgPanel"
          title="Messages"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          <span class="dash-header__notif-dot" v-if="messageCount > 0">
            {{ messageCount > 9 ? '9+' : messageCount }}
          </span>
        </button>
      </div>

      <!-- Notifications bell -->
      <div class="notif-trigger" ref="bellRef">
        <button
          class="dash-header__icon-btn"
          :class="{ active: notifOpen }"
          @click="toggleNotifPanel"
          title="Notifications"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <span class="dash-header__notif-dot" v-if="notificationCount > 0">
            {{ notificationCount > 9 ? '9+' : notificationCount }}
          </span>
        </button>
      </div>

      <!-- Avatar -->
      <div class="dash-header__avatar" :title="user.name" @click="goToProfile">{{ initials }}</div>

    </div>

    <!-- Notification panel — floats below the bell -->
    <NotificationPanel
      v-model="notifOpen"
      :role="user.role"
      :current-user-id="user.userID ?? user.id"
      :anchor="bellAnchor"
      @navigate="handleNavigate"
    />

    <!-- Message panel — floats below the icon -->
    <MessagePanel
      v-model="msgOpen"
      :role="user.role"
      :current-user-id="user.userID ?? user.id"
      :anchor="msgAnchor"
      @navigate="handleNavigate"
    />

  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ref, computed } from 'vue'
import { useNotifications } from '@/composables/useNotifications'
import NotificationPanel from '@/components/shared/NotificationPanel.vue'
import MessagePanel from '@/components/shared/MessagePanel.vue'
const router = useRouter()
const props = defineProps({
  user:              { type: Object, required: true },
  notificationCount: { type: Number, default: 0 },
  messageCount:      { type: Number, default: 0 },
})

const emit = defineEmits(['open-mobile-sidebar', 'quick-action', 'navigate-section'])

const { unreadCount: getUnreadCount } = useNotifications()

const bellRef    = ref(null)
const msgRef     = ref(null)
const notifOpen  = ref(false)
const msgOpen    = ref(false)
const bellAnchor = ref(null)
const msgAnchor  = ref(null)

function goToProfile() {
  router.push('/profile')
}

function toggleNotifPanel() {
  if (bellRef.value) bellAnchor.value = bellRef.value.getBoundingClientRect()
  notifOpen.value = !notifOpen.value
  if (notifOpen.value) msgOpen.value = false
}

function toggleMsgPanel() {
  if (msgRef.value) msgAnchor.value = msgRef.value.getBoundingClientRect()
  msgOpen.value = !msgOpen.value
  if (msgOpen.value) notifOpen.value = false
}

// Re-emit navigate so DashboardPage can call setSection
function handleNavigate(section) {
  emit('navigate-section', section)
  notifOpen.value = false
  msgOpen.value = false
}

const firstName = computed(() => props.user.name?.split(' ')[0] || '')
const initials  = computed(() => {
  const parts = props.user.name?.split(' ') || []
  return parts.map(p => p[0]).slice(0, 2).join('').toUpperCase()
})
const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 18) return 'Good afternoon'
  return 'Good evening'
})
const dateLabel = computed(() =>
  new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
)
</script>

<style scoped>
.dash-header {
  display: flex; align-items: center; gap: 20px;
  padding: 0 32px; height: 72px;
  background: var(--white);
  border-bottom: 1px solid var(--gray-200);
  position: sticky; top: 0; z-index: 40;
  flex-shrink: 0;
}

.dash-header__menu-btn {
  display: none; background: none; border: none;
  font-size: 1.3rem; cursor: pointer; color: var(--indigo);
  padding: 6px; border-radius: 8px; transition: background var(--transition);
}
.dash-header__menu-btn:hover { background: var(--gray-100); }

.dash-header__greeting { flex: 1; min-width: 0; }
.dash-header__title {
  font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700;
  color: var(--indigo); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.dash-header__sub { font-size: .8rem; color: var(--gray-400); margin-top: 1px; }

.dash-header__right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

.dash-header__action { padding: 9px 20px; font-size: .88rem; }

/* Bell button */
.notif-trigger { position: relative; }
.dash-header__icon-btn {
  width: 40px; height: 40px; border-radius: 10px; border: none;
  background: var(--gray-100); color: var(--indigo);
  font-size: 1rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  position: relative; transition: background var(--transition);
}
.dash-header__icon-btn:hover,
.dash-header__icon-btn.active { background: var(--gray-200); }
.dash-header__notif-dot {
  position: absolute; top: 6px; right: 6px;
  min-width: 16px; height: 16px; padding: 0 4px;
  background: var(--coral); color: #fff; border-radius: 50px;
  font-size: .62rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff;
}

.dash-header__avatar {
  width: 40px; height: 40px; border-radius: 50%;
  background: linear-gradient(135deg, var(--coral), var(--teal));
  display: flex; align-items: center; justify-content: center;
  font-family: 'Fraunces', serif; font-size: .88rem; font-weight: 700;
  color: #fff; cursor: pointer;
}

@media (max-width: 768px) {
  .dash-header__menu-btn { display: flex; }
  .dash-header__action   { display: none; }
  .dash-header           { padding: 0 16px; }
}
</style>