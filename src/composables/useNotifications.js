import { ref, computed } from 'vue'

// ── Module-level state (singleton — shared across all components) ──────────
const notifications = ref([
  // ── Tourist notifications ─────────────────────────────────────────────
  {
    id: 1,
    roles:    ['tourist'],
    type:     'booking',
    icon:     '✈️',
    title:    'Booking confirmed!',
    body:     'Your Swiss Alps Winter Retreat booking for Jun 12 is confirmed.',
    time:     '2 min ago',
    read:     false,
    link:     '/packages/101',
  },
  {
    id: 2,
    roles:    ['tourist'],
    type:     'offer',
    icon:     '🏷️',
    title:    'New offer: 30% off Maldives',
    body:     'A limited-time deal just dropped. Book before Jun 30.',
    time:     '1 hr ago',
    read:     false,
    link:     '/packages',
  },
  {
    id: 3,
    roles:    ['tourist'],
    type:     'review',
    icon:     '⭐',
    title:    'Leave a review',
    body:     'How was your Bali Spiritual Journey? Share your experience.',
    time:     '3 hrs ago',
    read:     true,
    link:     '/packages/102',
  },
  {
    id: 4,
    roles:    ['tourist'],
    type:     'system',
    icon:     '📢',
    title:    'Welcome to Voyago!',
    body:     'Discover destinations, packages and services from trusted providers.',
    time:     'Jun 10',
    read:     true,
    link:     '/',
  },

  // ── Agency notifications ───────────────────────────────────────────────
  {
    id: 5,
    roles:    ['agency'],
    type:     'booking',
    icon:     '📋',
    title:    'New booking request',
    body:     'Carlos Mendez requested the Greek Island Odyssey — Jun 10.',
    time:     '5 min ago',
    read:     false,
    link:     '/dashboard',
    section:  'bookings',
  },
  {
    id: 6,
    roles:    ['agency'],
    type:     'collab',
    icon:     '🤝',
    title:    'Collaboration request',
    body:     'Alpine Escapes proposed a joint offer: Alps Fly & Drive Bundle.',
    time:     '30 min ago',
    read:     false,
    link:     '/dashboard',
    section:  'collaborations',
  },
  {
    id: 7,
    roles:    ['agency'],
    type:     'review',
    icon:     '⭐',
    title:    'New review received',
    body:     'Amelia Rhodes gave Swiss Alps Winter Retreat 5 stars.',
    time:     '2 hrs ago',
    read:     false,
    link:     '/dashboard',
    section:  'reviews',
  },
  {
    id: 8,
    roles:    ['agency'],
    type:     'offer',
    icon:     '🏷️',
    title:    'Offer expiring soon',
    body:     '"Last Minute Alps" offer ends in 5 days. Consider extending it.',
    time:     'Yesterday',
    read:     true,
    link:     '/dashboard',
    section:  'offers',
  },
  {
    id: 9,
    roles:    ['agency'],
    type:     'system',
    icon:     '📊',
    title:    'Monthly summary ready',
    body:     'Your May performance report is available in the dashboard.',
    time:     'Jun 1',
    read:     true,
    link:     '/dashboard',
    section:  'overview',
  },

  // ── Provider notifications ─────────────────────────────────────────────
  {
    id: 10,
    roles:    ['provider'],
    type:     'booking',
    icon:     '📋',
    title:    'New service booking',
    body:     'Yuki Tanaka booked Mountain Guide for Jun 14.',
    time:     '10 min ago',
    read:     false,
    link:     '/dashboard',
    section:  'bookings',
  },
  {
    id: 11,
    roles:    ['provider'],
    type:     'collab',
    icon:     '🤝',
    title:    'Collaboration request',
    body:     'Wanderlust Travels wants to bundle your Mountain Guide service.',
    time:     '45 min ago',
    read:     false,
    link:     '/dashboard',
    section:  'collaborations',
  },
  {
    id: 12,
    roles:    ['provider'],
    type:     'review',
    icon:     '⭐',
    title:    'New 5-star review',
    body:     'Lena Müller left a glowing review for your Private Chef Experience.',
    time:     '3 hrs ago',
    read:     false,
    link:     '/dashboard',
    section:  'reviews',
  },
  {
    id: 13,
    roles:    ['provider'],
    type:     'system',
    icon:     '🛎️',
    title:    'Availability reminder',
    body:     '"Private Chef Experience" is marked unavailable. Update when ready.',
    time:     'Yesterday',
    read:     true,
    link:     '/dashboard',
    section:  'services',
  },
  {
    id: 14,
    roles:    ['provider'],
    type:     'system',
    icon:     '📊',
    title:    'Weekly stats',
    body:     'You had 12 bookings this week — up 18% from last week.',
    time:     'Jun 9',
    read:     true,
    link:     '/dashboard',
    section:  'overview',
  },
])

// ── Helpers ───────────────────────────────────────────────────────────────
export function useNotifications() {

  function forRole(role) {
    if (!role) return []
    return notifications.value.filter(n => n.roles.includes(role))
  }

  function unreadCount(role) {
    return computed(() => forRole(role).filter(n => !n.read).length)
  }

  function markRead(id) {
    const n = notifications.value.find(n => n.id === id)
    if (n) n.read = true
  }

  function markAllRead(role) {
    forRole(role).forEach(n => { n.read = true })
  }

  function deleteNotification(id) {
    notifications.value = notifications.value.filter(n => n.id !== id)
  }

  // Add a notification programmatically (e.g. after accepting a collab)
  function push(notification) {
    notifications.value.unshift({
      id:   Date.now(),
      read: false,
      time: 'Just now',
      ...notification,
    })
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