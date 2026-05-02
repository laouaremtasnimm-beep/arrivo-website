<template>
  <Teleport to="body">
    <Transition name="panel-pop">
      <div
        v-if="modelValue"
        class="notif-panel"
        :style="positionStyle"
        ref="panelRef"
      >

        <!-- Header -->
        <div class="notif-panel__header">
          <h3 class="notif-panel__title">Notifications</h3>
          <div class="notif-panel__header-actions">
            <button
              v-if="unread > 0"
              class="notif-panel__mark-all"
              @click="markAllRead(role, currentUserId)"
            >
              Mark all read
            </button>
            <button class="notif-panel__close" @click="$emit('update:modelValue', false)">✕</button>
          </div>
        </div>

        <!-- (Tabs removed for simplicity/visibility) -->

        <!-- List -->
        <div class="notif-panel__list" ref="listRef">
          <template v-if="roleNotifications.length">
            <NotificationItem
              v-for="n in roleNotifications"
              :key="n.id"
              :notification="n"
              @click="handleItemClick"
            />
          </template>
          <div v-else class="notif-panel__empty">
            <div class="notif-empty__icon">🔔</div>
            <p class="notif-empty__title">All caught up!</p>
            <p class="notif-empty__sub">No {{ activeTab === 'all' ? '' : activeTab + ' ' }}notifications yet.</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="notif-panel__footer" v-if="roleNotifications.length">
          <span class="notif-footer__count">{{ unread }} unread of {{ roleNotifications.length }}</span>
        </div>

      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/composables/useNotifications'
import NotificationItem from '@/components/shared/NotificationItem.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  role:       { type: String,  default: null  },
  anchor:     { type: Object,  default: null  },
  currentUserId: { type: [Number, String], default: null },
})
const emit = defineEmits(['update:modelValue', 'navigate'])

const router     = useRouter()
const panelRef   = ref(null)
const listRef    = ref(null)
const activeTab  = ref('all')

const { forType, unreadCount, markRead, markAllRead, deleteNotification } = useNotifications()

const roleNotifications = forType(props.role, 'notification', props.currentUserId)
const unread            = computed(() => roleNotifications.value.filter(n => !n.read).length)

// Reset scroll when panel opens
watch(() => props.modelValue, v => {
  if (v) {
    if (listRef.value) listRef.value.scrollTop = 0
  }
})

// ── Panel positioning — anchored to trigger button ────────────────────────
const positionStyle = computed(() => {
  if (!props.anchor) return { top: '72px', right: '20px' }
  const gap  = 8
  const top  = (props.anchor.bottom || 72) + gap
  const right = window.innerWidth - (props.anchor.right || window.innerWidth - 20)
  return {
    top:   `${top}px`,
    right: `${Math.max(right, 8)}px`,
  }
})

// ── Click outside to close ────────────────────────────────────────────────
function onClickOutside(e) {
  if (!props.modelValue) return
  if (panelRef.value && !panelRef.value.contains(e.target)) {
    emit('update:modelValue', false)
  }
}
onMounted(()    => document.addEventListener('mousedown', onClickOutside))
onUnmounted(()  => document.removeEventListener('mousedown', onClickOutside))

// ── Item click — mark read and navigate ───────────────────────────────────
function handleItemClick(notification) {
  markRead(notification.id)

  // If it has a section, emit navigate (used by dashboard)
  if (notification.section) {
    emit('navigate', notification.section)
  }

  // Navigate via router
  if (notification.link) {
    router.push(notification.link)
  }

  emit('update:modelValue', false)
}
</script>

<style scoped>
.notif-panel {
  position: fixed;
  width: 380px;
  max-height: 520px;
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-lg, 0 16px 64px rgba(45,49,66,.14));
  border: 1.5px solid var(--gray-200);
  z-index: 300;
  display: flex; flex-direction: column;
  overflow: hidden;
}

/* Header */
.notif-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 16px 12px;
  border-bottom: 1px solid var(--gray-100);
  flex-shrink: 0;
}
.notif-panel__title {
  font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 700;
  color: var(--indigo); margin: 0;
}
.notif-panel__header-actions { display: flex; align-items: center; gap: 8px; }
.notif-panel__mark-all {
  font-size: .76rem; font-weight: 600; color: var(--teal-dk, #26b0a4);
  background: none; border: none; cursor: pointer; padding: 4px 8px;
  border-radius: var(--radius-sm); transition: background var(--transition);
  font-family: 'DM Sans', sans-serif;
}
.notif-panel__mark-all:hover { background: var(--teal-lt); }
.notif-panel__close {
  width: 28px; height: 28px; border-radius: 50%; border: none;
  background: var(--gray-100); color: var(--gray-600);
  font-size: .8rem; cursor: pointer; display: flex;
  align-items: center; justify-content: center;
  transition: background var(--transition);
}
.notif-panel__close:hover { background: var(--gray-200); }

/* Tabs */
.notif-panel__tabs {
  display: flex; gap: 2px; padding: 8px 12px 6px;
  border-bottom: 1px solid var(--gray-100); flex-shrink: 0;
}
.notif-panel__tab {
  padding: 5px 14px; border-radius: 50px; border: none;
  background: transparent; font-size: .78rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer;
  transition: all var(--transition); display: flex; align-items: center; gap: 5px;
  font-family: 'DM Sans', sans-serif;
}
.notif-panel__tab:hover  { background: var(--gray-100); color: var(--indigo); }
.notif-panel__tab.active { background: var(--indigo); color: #fff; }
.notif-tab-count {
  background: var(--coral); color: #fff;
  font-size: .65rem; font-weight: 700;
  min-width: 16px; height: 16px; padding: 0 4px;
  border-radius: 50px; display: flex; align-items: center; justify-content: center;
}

/* List */
.notif-panel__list {
  flex: 1; overflow-y: auto; overscroll-behavior: contain;
  scrollbar-width: thin; scrollbar-color: var(--gray-200) transparent;
}
.notif-panel__list::-webkit-scrollbar { width: 4px; }
.notif-panel__list::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 4px; }

/* Empty state */
.notif-panel__empty {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 48px 20px; gap: 6px;
}
.notif-empty__icon  { font-size: 2rem; }
.notif-empty__title { font-size: .95rem; font-weight: 700; color: var(--indigo); margin: 0; }
.notif-empty__sub   { font-size: .8rem; color: var(--gray-400); margin: 0; }

/* Footer */
.notif-panel__footer {
  padding: 10px 16px; border-top: 1px solid var(--gray-100);
  flex-shrink: 0;
}
.notif-footer__count { font-size: .75rem; color: var(--gray-400); }

/* Pop animation */
.panel-pop-enter-active, .panel-pop-leave-active {
  transition: opacity .18s ease, transform .18s ease;
}
.panel-pop-enter-from, .panel-pop-leave-to {
  opacity: 0; transform: translateY(-8px) scale(.97);
}

/* Mobile: full-width bottom sheet */
@media (max-width: 480px) {
  .notif-panel {
    position: fixed; inset: auto 0 0 0;
    width: 100%; max-height: 75vh;
    border-radius: 20px 20px 0 0;
    border-bottom: none;
  }
}
</style>