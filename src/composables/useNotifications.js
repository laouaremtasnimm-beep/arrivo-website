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

let pollInterval = null

const notifications = ref([])
const API_URL = '/arrivo-website/backend/api/v1/notifications.php'

async function fetchNotifications(userId) {
  if (!userId) return
  try {
    const res = await fetch(`${API_URL}?user_id=${userId}`)
    const data = await res.json()
    if (data.notifications) {
      notifications.value = data.notifications.map(n => ({
        ...n,
        read: !!Number(n.is_read),
        time: new Date(n.created_at).toLocaleDateString()
      }))
    }
  } catch (e) { console.error('Fetch notifications failed', e) }
}

function startPolling(userId) {
  if (pollInterval) return
  fetchNotifications(userId)
  pollInterval = setInterval(() => fetchNotifications(userId), 10000) // Poll every 10s
}

function stopPolling() {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

export function useNotifications() {

  function forRole(role, currentUserId = null) {
    if (!role) return []
    // Role filtering is handled by DB user_id for targeted ones
    // and we can filter globally if needed.
    return notifications.value
  }

  function forType(role, type, currentUserId = null) {
    return computed(() => {
      const all = notifications.value
      if (type === 'message') return all.filter(n => n.type === 'message')
      return all.filter(n => n.type !== 'message')
    })
  }

  function unreadCount(role, currentUserId = null, type = 'all') {
    return computed(() => {
      let list = notifications.value
      if (type === 'message') list = list.filter(n => n.type === 'message')
      else if (type === 'notification') list = list.filter(n => n.type !== 'message')
      return list.filter(n => !n.read).length
    })
  }

  async function markRead(id) {
    const n = notifications.value.find(n => n.id === id)
    if (n) {
      n.read = true
      try {
        await fetch(API_URL, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        })
      } catch (e) { console.error('Mark read failed', e) }
    }
  }

  async function markAllRead(role, currentUserId = null) {
    notifications.value.forEach(n => { n.read = true })
    try {
      await fetch(API_URL, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: currentUserId })
      })
    } catch (e) { console.error('Mark all read failed', e) }
  }

  async function deleteNotification(id) {
    notifications.value = notifications.value.filter(n => n.id !== id)
    try {
      await fetch(`${API_URL}?id=${id}`, { method: 'DELETE' })
    } catch (e) { console.error('Delete notification failed', e) }
  }

  async function push(notification) {
    // Add locally first for instant feedback
    const localId = Date.now()
    notifications.value.unshift({
      id: localId,
      read: false,
      time: 'Just now',
      ...notification,
    })

    // Persist to DB
    try {
      await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          user_id: notification.targetUserId,
          type:    notification.type,
          title:   notification.title,
          body:    notification.body,
          icon:    notification.icon,
          link:    notification.link
        })
      })
    } catch (e) { console.error('Push notification failed', e) }
  }

  return {
    notifications,
    fetchNotifications,
    startPolling,
    stopPolling,
    forRole,
    forType,
    unreadCount,
    markRead,
    markAllRead,
    deleteNotification,
    push,
  }
}