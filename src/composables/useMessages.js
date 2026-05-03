import { ref, computed } from 'vue'

const API_BASE = '/arrivo-website/backend/api/v1'

const messages = ref([])
const loading  = ref(false)
const error    = ref(null)

  let pollInterval = null

export function useMessages() {

  async function fetchMessages(userId) {
    if (!userId) return
    loading.value = true
    error.value   = null
    try {
      const res = await fetch(`${API_BASE}/Messages.php?user_id=${userId}`)
      const data = await res.json()
      if (res.ok) {
        messages.value = data.messages || []
      } else {
        error.value = data.error || 'Failed to fetch messages'
      }
    } catch (e) {
      error.value = 'Connection error'
      console.error(e)
    } finally {
      loading.value = false
    }
  }

  async function sendMessage({ sender_id, receiver_id, content, subject = 'Chat' }) {
    try {
      const res = await fetch(`${API_BASE}/Messages.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sender_id, receiver_id, content, subject })
      })
      const data = await res.json()
      return { ok: res.ok, data }
    } catch (e) {
      return { ok: false, error: e.message }
    }
  }

  async function markAsRead(id) {
    // Optimistic Update
    const msg = messages.value.find(m => m.id === id)
    if (msg) msg.is_read = 1

    try {
      await fetch(`${API_BASE}/Messages.php`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      })
    } catch (e) {
      console.error('Failed to mark as read', e)
      // Rollback if needed? Usually not critical for read status
    }
  }

  async function deleteMessage(id) {
    try {
      const res = await fetch(`${API_BASE}/Messages.php`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      })
      if (res.ok) {
        messages.value = messages.value.filter(m => m.id !== id)
      }
    } catch (e) {
      console.error('Failed to delete message', e)
    }
  }

  // Groups messages into conversations by the other person's ID
  function getConversations(currentUserId) {
    const groups = {}
    messages.value.forEach(m => {
      const isSent = String(m.sender_id) === String(currentUserId)
      const otherId = isSent ? m.receiver_id : m.sender_id
      const otherName = isSent 
        ? `${m.receiver_first} ${m.receiver_last}`
        : `${m.sender_first} ${m.sender_last}`

      if (!groups[otherId]) {
        groups[otherId] = {
          id: otherId,
          name: otherName,
          lastMessage: m.content,
          time: m.created_at,
          unread: !m.is_read && !isSent,
          messages: []
        }
      }
      groups[otherId].messages.push({
        ...m,
        isMe: isSent,
        text: m.content,
        time: new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      })
      // Update last message
      groups[otherId].lastMessage = m.content
      groups[otherId].time = m.created_at
      if (!m.is_read && !isSent) groups[otherId].unread = true
    })
    return Object.values(groups).sort((a, b) => new Date(b.time) - new Date(a.time))
  }

  function getUnreadCount(userId) {
    return computed(() => {
      if (!userId) return 0
      return messages.value.filter(m => 
        String(m.receiver_id) === String(userId) && !m.is_read
      ).length
    })
  }


  function startPolling(userId, intervalMs = 10000) {
    if (pollInterval) stopPolling()
    if (!userId) return
    pollInterval = setInterval(() => fetchMessages(userId), intervalMs)
  }

  function stopPolling() {
    if (pollInterval) {
      clearInterval(pollInterval)
      pollInterval = null
    }
  }

  return {
    messages,
    loading,
    error,
    getConversations,
    getUnreadCount,
    fetchMessages,
    startPolling,
    stopPolling,
    sendMessage,
    markAsRead,
    deleteMessage
  }
}
