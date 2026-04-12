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

      <!-- Notifications -->
      <button class="dash-header__icon-btn" @click="$emit('open-notifications')" title="Notifications">
        🔔
        <span class="dash-header__notif-dot" v-if="notificationCount > 0">{{ notificationCount }}</span>
      </button>

      <!-- Avatar -->
      <div class="dash-header__avatar" :title="user.name">{{ initials }}</div>

    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  user:              { type: Object, required: true },
  notificationCount: { type: Number, default: 0 },
})

defineEmits(['open-mobile-sidebar', 'open-notifications', 'quick-action'])

const firstName = computed(() => props.user.name?.split(' ')[0] || '')

const initials = computed(() => {
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
  font-family: 'Fraunces', serif;
  font-size: 1.25rem; font-weight: 700;
  color: var(--indigo); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.dash-header__sub { font-size: .8rem; color: var(--gray-400); margin-top: 1px; }

.dash-header__right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

.dash-header__action { padding: 9px 20px; font-size: .88rem; }

.dash-header__icon-btn {
  width: 40px; height: 40px; border-radius: 10px; border: none;
  background: var(--gray-100); font-size: 1.1rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  position: relative; transition: background var(--transition);
}
.dash-header__icon-btn:hover { background: var(--gray-200); }

.dash-header__notif-dot {
  position: absolute; top: 4px; right: 4px;
  min-width: 16px; height: 16px; padding: 0 4px;
  background: var(--coral); color: #fff; border-radius: 50px;
  font-size: .62rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
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
