<template>
  <div class="dash-card" id="messages">
    <div class="dash-card__header">
      <div>
        <div class="dash-card__overline">Inbox</div>
        <h2 class="dash-card__title">
          Messages
          <span class="unread-badge" v-if="unreadCount">{{ unreadCount }}</span>
        </h2>
      </div>
      <div class="header-actions">
        <div class="msg-tabs">
          <button
            v-for="tab in ['all', 'unread', 'sent']"
            :key="tab"
            class="msg-tab"
            :class="{ active: activeTab === tab }"
            @click="activeTab = tab"
          >
            {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
          </button>
        </div>
        <button class="btn btn-outline dash-card__btn" @click="composeOpen = true">+ Compose</button>
      </div>
    </div>

    <div class="msg-empty" v-if="!filteredConversations.length">
      <div class="msg-empty__icon">💬</div>
      <p class="msg-empty__title">No {{ activeTab === 'all' ? '' : activeTab + ' ' }}conversations</p>
      <button class="btn-compose-cta" @click="composeOpen = true">Write a message</button>
    </div>

    <div class="messages-list" v-else>
      <div
        class="message-row"
        :class="{ unread: conv.unread }"
        v-for="conv in filteredConversations"
        :key="conv.id"
        @click="openThread(conv)"
      >
        <div class="msg-avatar">
          {{ conv.name[0] }}
        </div>
        <div class="msg-body">
          <div class="msg-top">
            <span class="msg-from">{{ conv.name }}</span>
            <span class="msg-date">{{ formatTime(conv.time) }}</span>
          </div>
          <div class="msg-preview">{{ conv.lastMessage }}</div>
        </div>
        <div class="msg-indicators">
          <div class="msg-unread-dot" v-if="conv.unread" />
          <div class="msg-reply-count">
            {{ conv.messages.length }} msgs
          </div>
        </div>
      </div>
    </div>

    <div class="dash-card__footer">
      <span class="dash-card__count">
        {{ conversations.length }} conversations · {{ unreadCount }} unread
      </span>
      <button class="see-all-btn" v-if="unreadCount" @click="markAllRead">
        Mark all read
      </button>
    </div>

    <MessageThread
      v-model="threadOpen"
      :conversation="activeConversation"
      :current-user-id="currentUserId"
    />

    <ComposeModal
      v-model="composeOpen"
      :current-user-id="currentUserId"
      @send="handleSend"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import MessageThread from '@/components/dashboard/MessageThread.vue'
import ComposeModal  from '@/components/dashboard/ComposeModal.vue'
import { useMessages } from '@/composables/useMessages'

const props = defineProps({
  messages:             { type: Array,  default: () => [] },
  currentUserId:        { type: Number, default: null },
  autoOpenConversation: { type: Object, default: null },
})

const emit = defineEmits(['open', 'compose', 'delete', 'conversation-opened'])

const { getConversations, markAsRead } = useMessages()

const conversations = computed(() => getConversations(props.currentUserId))

const activeTab          = ref('all')
const threadOpen         = ref(false)
const composeOpen        = ref(false)
const activeConversation = ref(null)

const unreadCount = computed(() => conversations.value.filter(c => c.unread).length)

const filteredConversations = computed(() => {
  if (activeTab.value === 'unread') return conversations.value.filter(c => c.unread)
  return conversations.value
})

function openThread(conv) {
  activeConversation.value = conv
  threadOpen.value = true
  conv.messages.forEach(m => {
    if (!m.is_read && !m.isMe) markAsRead(m.id)
  })
}

function formatTime(t) {
  if (!t) return ''
  const d = new Date(t)
  const now = new Date()
  if (d.toDateString() === now.toDateString())
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  return d.toLocaleDateString([], { day: 'numeric', month: 'short' })
}

function handleSend() { composeOpen.value = false }

function markAllRead() {
  conversations.value.forEach(conv => {
    conv.messages.forEach(m => {
      if (!m.is_read && !m.isMe) markAsRead(m.id)
    })
  })
}

// Auto-open a conversation when navigated from overview
watch(() => props.autoOpenConversation, async (conv) => {
  if (!conv) return
  await nextTick()
  const match = conversations.value.find(c =>
    String(c.id) === String(conv.id) ||
    c.messages?.some(m => m.id === conv.messageID || m.id === conv.id)
  )
  if (match) {
    openThread(match)
    emit('conversation-opened')
  }
}, { immediate: true })
</script>

<style scoped>
.dash-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 28px; overflow: hidden; }
.dash-card__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 24px 24px 0; gap: 16px; flex-wrap: wrap;
}
.dash-card__overline {
  font-size: .72rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--teal); margin-bottom: 4px;
}
.dash-card__title {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  display: flex; align-items: center; gap: 10px;
}
.dash-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 24px; border-top: 1px solid var(--gray-100);
}
.dash-card__count { font-size: .82rem; color: var(--gray-400); }
.dash-card__btn   { padding: 8px 18px; font-size: .84rem; }
.header-actions   { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.msg-tabs { display: flex; gap: 2px; background: var(--gray-100); border-radius: 50px; padding: 3px; }
.msg-tab {
  padding: 5px 14px; border-radius: 50px; border: none;
  background: transparent; font-size: .78rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
  font-family: 'DM Sans', sans-serif;
}
.msg-tab.active { background: #fff; color: var(--indigo); box-shadow: 0 1px 4px rgba(45,49,66,.1); }
.unread-badge {
  background: var(--coral); color: #fff;
  font-size: .68rem; font-weight: 700; padding: 2px 8px;
  border-radius: 50px; font-family: 'DM Sans', sans-serif;
}
.messages-list { padding: 8px 0; }
.message-row {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 14px 24px; cursor: pointer; position: relative;
  transition: background var(--transition);
}
.message-row:hover  { background: var(--gray-50); }
.message-row.unread { background: rgba(46,196,182,.04); }
.message-row.sent   { opacity: .85; }
.msg-avatar {
  width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--teal), var(--indigo));
  display: flex; align-items: center; justify-content: center;
  font-family: 'Fraunces', serif; font-size: .95rem; font-weight: 700; color: #fff;
}
.msg-avatar--sent { background: linear-gradient(135deg, var(--coral), #ff8a8d); font-size: .65rem; }
.msg-body    { flex: 1; min-width: 0; }
.msg-top     { display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px; }
.msg-from    { font-weight: 600; font-size: .88rem; color: var(--indigo); }
.msg-date    { font-size: .75rem; color: var(--gray-400); flex-shrink: 0; }
.msg-title   { font-size: .88rem; font-weight: 600; color: var(--indigo); margin-bottom: 3px; }
.message-row.unread .msg-title { color: var(--coral); }
.msg-preview { font-size: .8rem; color: var(--gray-400); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-indicators { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; padding-top: 4px; }
.msg-unread-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--teal); flex-shrink: 0; }
.msg-reply-count { font-size: .7rem; color: var(--gray-400); font-weight: 600; white-space: nowrap; }
.see-all-btn {
  font-size: .78rem; font-weight: 600; color: var(--teal-dk);
  background: none; border: none; cursor: pointer;
  font-family: 'DM Sans', sans-serif; transition: color var(--transition);
}
.see-all-btn:hover { color: var(--teal); }
.msg-empty {
  padding: 48px 20px; text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.msg-empty__icon  { font-size: 2rem; }
.msg-empty__title { font-size: .9rem; color: var(--gray-400); margin: 0; }
.btn-compose-cta {
  margin-top: 8px; background: var(--indigo); color: #fff; border: none;
  padding: 8px 20px; border-radius: 50px; font-size: .84rem; font-weight: 600;
  cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition);
}
.btn-compose-cta:hover { background: #3d4460; }
</style>