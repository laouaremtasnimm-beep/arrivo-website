<template>
  <Teleport to="body">
    <Transition name="panel-pop">
      <div
        v-if="modelValue"
        class="msg-panel"
        :style="positionStyle"
        ref="panelRef"
      >
        <!-- Header -->
        <div class="msg-panel__header">
          <div class="msg-header__left">
            <button v-if="selectedConversation" class="msg-back-btn" @click="selectedConversation = null">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
              </svg>
            </button>
            <h3 class="msg-panel__title">{{ selectedConversation ? selectedConversation.title : 'Messages' }}</h3>
          </div>
          <div class="msg-panel__header-actions">
            <button class="msg-panel__close" @click="$emit('update:modelValue', false)">✕</button>
          </div>
        </div>

        <!-- Conversation View -->
        <div v-if="selectedConversation" class="msg-conversation">
          <div class="msg-chat-window" ref="chatRef">
            <div 
              v-for="(m, i) in selectedConversation.messages" :key="i"
              class="chat-bubble"
              :class="{ 'chat-bubble--me': m.isMe }"
            >
              <div class="chat-bubble__content">{{ m.text }}</div>
              <div class="chat-bubble__time">{{ m.time }}</div>
            </div>
          </div>
          <div class="msg-chat-input">
            <input 
              v-model="replyText" 
              placeholder="Type a message..." 
              @keyup.enter="sendReply"
            />
            <button class="send-btn" @click="sendReply" :disabled="!replyText.trim()">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- List View -->
        <div v-else class="msg-panel__list" ref="listRef">
          <template v-if="messageItems.length">
            <NotificationItem
              v-for="n in messageItems"
              :key="n.id"
              :notification="n"
              @click="handleItemClick"
            />
          </template>
          <div v-else class="msg-panel__empty">
            <div class="msg-empty__icon">💬</div>
            <p class="msg-empty__title">No messages</p>
            <p class="msg-empty__sub">Your conversations will appear here.</p>
          </div>
        </div>

      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/composables/useNotifications'
import { useMessages } from '@/composables/useMessages'
import NotificationItem from '@/components/shared/NotificationItem.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  role:       { type: String,  default: null  },
  anchor:     { type: Object,  default: null  },
  currentUserId: { type: [Number, String], default: null },
})
const emit = defineEmits(['update:modelValue'])

const router     = useRouter()
const panelRef   = ref(null)
const listRef    = ref(null)
const chatRef    = ref(null)
const selectedConversation = ref(null)
const replyText = ref('')

const { markRead } = useNotifications()
const { fetchMessages, sendMessage, markAsRead, getConversations, loading } = useMessages()

const conversations = computed(() => getConversations(props.currentUserId))

// Map conversations to notification-like items for the list
const messageItems = computed(() => {
  return conversations.value.map(c => ({
    id: c.id,
    title: c.name,
    body: c.lastMessage,
    time: new Date(c.time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    read: !c.unread,
    type: 'message',
    icon: '✉️',
    conversation: c
  }))
})

// Refresh messages on open
watch(() => props.modelValue, v => {
  if (v) {
    selectedConversation.value = null
    fetchMessages(props.currentUserId)
    if (listRef.value) listRef.value.scrollTop = 0
  }
})

// Scroll chat to bottom
watch(selectedConversation, async (v) => {
  if (v) {
    await nextTick()
    if (chatRef.value) chatRef.value.scrollTop = chatRef.value.scrollHeight
    // Mark all messages in this conversation as read
    v.messages.forEach(m => {
      if (!m.is_read && !m.isMe) markAsRead(m.id)
    })
  }
})

const positionStyle = computed(() => {
  if (!props.anchor) return { top: '72px', right: '80px' }
  const gap  = 8
  const top  = (props.anchor.bottom || 72) + gap
  const right = window.innerWidth - (props.anchor.right || window.innerWidth - 80)
  return {
    top:   `${top}px`,
    right: `${Math.max(right, 8)}px`,
  }
})

function onClickOutside(e) {
  if (!props.modelValue) return
  if (panelRef.value && !panelRef.value.contains(e.target)) {
    emit('update:modelValue', false)
  }
}
onMounted(()    => document.addEventListener('mousedown', onClickOutside))
onUnmounted(()  => document.removeEventListener('mousedown', onClickOutside))

function handleItemClick(item) {
  selectedConversation.value = item.conversation
}

async function sendReply() {
  if (!replyText.value.trim() || !selectedConversation.value) return
  const text = replyText.value
  replyText.value = ''
  
  const res = await sendMessage({
    sender_id: props.currentUserId,
    receiver_id: selectedConversation.value.id,
    content: text
  })

  if (res.ok) {
    // Refresh to show new message
    await fetchMessages(props.currentUserId)
    // Update selected conversation to include new message
    selectedConversation.value = conversations.value.find(c => String(c.id) === String(selectedConversation.value.id))
  } else {
    alert('Failed to send: ' + res.error)
  }
}
</script>

<style scoped>
.msg-panel {
  position: fixed;
  width: 400px;
  height: 520px;
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow-lg, 0 16px 64px rgba(45,49,66,.14));
  border: 1.5px solid var(--gray-200);
  z-index: 300;
  display: flex; flex-direction: column;
  overflow: hidden;
}

.msg-panel__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 16px 12px;
  border-bottom: 1px solid var(--gray-100);
  flex-shrink: 0;
}
.msg-header__left { display: flex; align-items: center; gap: 12px; }
.msg-back-btn {
  background: none; border: none; padding: 4px; color: var(--gray-500); cursor: pointer;
  display: flex; align-items: center; justify-content: center; border-radius: 50%;
}
.msg-back-btn:hover { background: var(--gray-100); color: var(--indigo); }

.msg-panel__title {
  font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 700;
  color: var(--indigo); margin: 0;
}
.msg-panel__close {
  width: 28px; height: 28px; border-radius: 50%; border: none;
  background: var(--gray-100); color: var(--gray-600);
  font-size: .8rem; cursor: pointer; display: flex;
  align-items: center; justify-content: center;
}

.msg-panel__list {
  flex: 1; overflow-y: auto;
  scrollbar-width: thin; scrollbar-color: var(--gray-200) transparent;
}

.msg-panel__empty {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 60px 20px; gap: 6px;
}
.msg-empty__icon { font-size: 2.5rem; margin-bottom: 8px; }
.msg-empty__title { font-size: 1rem; font-weight: 700; color: var(--indigo); margin: 0; }
.msg-empty__sub { font-size: .85rem; color: var(--gray-400); margin: 0; text-align: center; }

/* Conversation View */
.msg-conversation { flex: 1; display: flex; flex-direction: column; background: var(--gray-50); }
.msg-chat-window {
  flex: 1; overflow-y: auto; padding: 20px;
  display: flex; flex-direction: column; gap: 12px;
}
.chat-bubble {
  max-width: 80%; padding: 10px 14px; border-radius: 16px;
  font-size: .9rem; line-height: 1.4; position: relative;
}
.chat-bubble--me {
  align-self: flex-end; background: var(--indigo); color: #fff;
  border-bottom-right-radius: 4px;
}
.chat-bubble:not(.chat-bubble--me) {
  align-self: flex-start; background: #fff; color: var(--indigo);
  border-bottom-left-radius: 4px; border: 1px solid var(--gray-200);
}
.chat-bubble__time { font-size: .65rem; margin-top: 4px; opacity: .7; }
.chat-bubble--me .chat-bubble__time { text-align: right; }

.msg-chat-input {
  padding: 12px 16px; background: #fff; border-top: 1px solid var(--gray-100);
  display: flex; gap: 10px; align-items: center;
}
.msg-chat-input input {
  flex: 1; border: 1.5px solid var(--gray-200); border-radius: 20px;
  padding: 8px 16px; font-size: .9rem; outline: none; transition: border-color .2s;
}
.msg-chat-input input:focus { border-color: var(--teal); }
.send-btn {
  width: 36px; height: 36px; border-radius: 50%; border: none;
  background: var(--teal); color: #fff; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: transform .2s;
}
.send-btn:hover:not(:disabled) { transform: scale(1.05); }
.send-btn:disabled { background: var(--gray-200); cursor: not-allowed; }

.panel-pop-enter-active, .panel-pop-leave-active {
  transition: opacity .18s ease, transform .18s ease;
}
.panel-pop-enter-from, .panel-pop-leave-to {
  opacity: 0; transform: translateY(-8px) scale(.97);
}

@media (max-width: 480px) {
  .msg-panel { position: fixed; inset: auto 0 0 0; width: 100%; max-height: 85vh; border-radius: 20px 20px 0 0; }
}
</style>
