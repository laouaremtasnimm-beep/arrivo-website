import { ref, computed, onMounted, onUnmounted } from 'vue'

const STORAGE_KEY = 'voyago_notifications'

// ── Initial State ────────────────────────────────────────────────────────────
const defaultNotification = {
  id: 1,
  roles: ['tourist', 'agency', 'provider'],
  targetUserId: null,
  type: 'system',
  icon: '📢',
  title: 'Welcome to Voyago!',
  body: 'Discover destinations, packages and services from trusted providers.',
  time: 'Just now',
  read: false,
  link: '/',
}

// ── Singleton State ──────────────────────────────────────────────────────────
const saved = localStorage.getItem(STORAGE_KEY)
const notifications = ref(saved ? JSON.parse(saved) : [defaultNotification])

function saveToStorage() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(notifications.value))
}

function syncFromStorage() {
  try {
    const latest = localStorage.getItem(STORAGE_KEY)
    if (latest) {
      notifications.value = JSON.parse(latest)
    }
  } catch (e) { console.error('Sync failed', e) }
}

// Initialize listener only once
if (typeof window !== 'undefined') {
  window.addEventListener('storage', (e) => {
    if (e.key === STORAGE_KEY) syncFromStorage()
  })
}

export function useNotifications() {

  function forRole(role, currentUserId = null) {
    if (!role) return []
    const r = role.toLowerCase()

    return notifications.value.filter(n => {
      // 1. Role Check: notification must target this user's role
      const roleMatch = n.roles.some(roleStr => roleStr.toLowerCase() === r)
      if (!roleMatch) return false

      // 2. Ownership Check: if targeted, ID must match
      if (n.targetUserId != null) {
        if (currentUserId == null) return false
        return String(n.targetUserId) === String(currentUserId)
      }

      // Global role-based notification
      return true
    })
  }

  function unreadCount(role, currentUserId = null) {
    return computed(() => forRole(role, currentUserId).filter(n => !n.read).length)
  }

  function markRead(id) {
    const n = notifications.value.find(n => n.id === id)
    if (n) {
      n.read = true
      saveToStorage()
    }
  }

  function markAllRead(role, currentUserId = null) {
    forRole(role, currentUserId).forEach(n => { n.read = true })
    saveToStorage()
  }

  function deleteNotification(id) {
    notifications.value = notifications.value.filter(n => n.id !== id)
    saveToStorage()
  }

  function push(notification) {
    notifications.value.unshift({
      id: Date.now(),
      read: false,
      time: 'Just now',
      targetUserId: null,
      ...notification,
    })
    saveToStorage()
    syncFromStorage()
  }

  return {
    notifications,
    forRole,
    unreadCount,
    markRead,
    markAllRead,
    deleteNotification,
    push,
  }
}