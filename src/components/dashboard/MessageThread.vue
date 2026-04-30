<template>
  <Teleport to="body">
    <Transition name="thread-slide">
      <div class="thread-backdrop" v-if="modelValue" @click.self="close">
        <div class="thread-panel">

          <div class="thread__header">
            <button class="thread__back" @click="close">← Back</button>
            <div class="thread__header-info">
              <div class="thread__subject">{{ message?.title }}</div>
              <div class="thread__meta">Conversation with {{ message?.from }}</div>
            </div>
            <button class="thread__del" @click="$emit('delete', message)" title="Delete">🗑️</button>
          </div>

          <div class="thread__body" ref="bodyRef">

            <div class="thread-msg thread-msg--incoming">
              <div class="thread-msg__avatar">{{ message?.from?.[0] }}</div>
              <div class="thread-msg__bubble">
                <div class="thread-msg__name">{{ message?.from }}</div>
                <p class="thread-msg__text">{{ message?.content }}</p>
                <div class="thread-msg__time">{{ message?.date }}</div>
              </div>
            </div>

            <div
              v-for="reply in localReplies"
              :key="reply.id"
              class="thread-msg"
              :class="reply.fromMe ? 'thread-msg--outgoing' : 'thread-msg--incoming'"
            >
              <div class="thread-msg__avatar thread-msg__avatar--you" v-if="reply.fromMe">You</div>
              <div class="thread-msg__avatar" v-else>{{ message?.from?.[0] }}</div>
              <div class="thread-msg__bubble" :class="{ 'bubble--you': reply.fromMe }">
                <div class="thread-msg__name">{{ reply.fromMe ? 'You' : message?.from }}</div>
                <p class="thread-msg__text">{{ reply.text }}</p>
                <div class="thread-msg__time">{{ reply.time }}</div>
              </div>
            </div>

          </div>

          <div class="thread__reply">
            <p v-if="replyError" class="thread__reply-error">⚠️ {{ replyError }}</p>
            <textarea
              v-model="replyText"
              class="thread__reply-input"
              placeholder="Write a reply…"
              rows="3"
              @keydown.ctrl.enter="sendReply"
            />
            <div class="thread__reply-footer">
              <span class="thread__reply-hint">Ctrl + Enter to send</span>
              <button class="btn-send" @click="sendReply" :disabled="!replyText.trim() || sending">
                {{ sending ? 'Sending…' : 'Send ↑' }}
              </button>
            </div>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import { useAuth } from '@/composables/useAuth'

const props = defineProps({
  modelValue: Boolean,
  message:    { type: Object, default: null },
})
const emit = defineEmits(['update:modelValue', 'delete'])

const API          = '/arrivo-website/backend/api/v1'
const { user }     = useAuth()
const replyText    = ref('')
const replyError   = ref('')
const bodyRef      = ref(null)
const sending      = ref(false)
const localReplies = ref([])

// Reset replies when a new message is opened
watch(() => props.message, () => {
  localReplies.value = props.message?.replies ? [...props.message.replies] : []
})

watch(() => props.modelValue, v => {
  if (v) {
    replyText.value  = ''
    replyError.value = ''
    localReplies.value = props.message?.replies ? [...props.message.replies] : []
    scrollToBottom()
  }
})

function scrollToBottom() {
  nextTick(() => {
    if (bodyRef.value) bodyRef.value.scrollTop = bodyRef.value.scrollHeight
  })
}

async function sendReply() {
  if (!replyText.value.trim() || sending.value) return
  replyError.value = ''

  const senderId   = parseInt(user.value?.userID ?? user.value?.id) || null
  // For demo messages sender_id is stored directly; for DB messages it comes from normalizeMessage
  const receiverId = parseInt(props.message?.sender_id) || null

  if (!senderId) {
    replyError.value = 'Not logged in — please refresh and try again.'
    return
  }

  if (!receiverId) {
    // Demo message — just add locally without saving to DB
    localReplies.value.push({
      id:     Date.now(),
      text:   replyText.value.trim(),
      fromMe: true,
      time:   'Just now',
    })
    replyText.value = ''
    scrollToBottom()
    return
  }

  sending.value = true
  try {
    const res = await fetch(`${API}/messages.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        sender_id:   senderId,
        receiver_id: receiverId,
        subject:     `Re: ${props.message?.title ?? ''}`,
        content:     replyText.value.trim(),
      }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Reply failed')

    localReplies.value.push({
      id:     data.message_id,
      text:   replyText.value.trim(),
      fromMe: true,
      time:   'Just now',
    })
    replyText.value = ''
    scrollToBottom()
  } catch (e) {
    replyError.value = e.message
  } finally {
    sending.value = false
  }
}

function close() { emit('update:modelValue', false) }
</script>

<style scoped>
.thread-backdrop {
  position: fixed; inset: 0; background: rgba(45,49,66,.35);
  z-index: 150; display: flex; justify-content: flex-end;
}
.thread-panel {
  width: 100%; max-width: 480px; height: 100%;
  background: #fff; display: flex; flex-direction: column;
  box-shadow: -8px 0 40px rgba(45,49,66,.12);
}
.thread__header {
  display: flex; align-items: center; gap: 12px;
  padding: 18px 20px; border-bottom: 1px solid var(--gray-200);
  flex-shrink: 0;
}
.thread__back {
  background: none; border: none; font-size: .84rem; font-weight: 600;
  color: var(--teal-dk); cursor: pointer; padding: 6px 10px;
  border-radius: var(--radius-sm); transition: background var(--transition);
  font-family: 'DM Sans', sans-serif; white-space: nowrap;
}
.thread__back:hover { background: var(--teal-lt); }
.thread__header-info { flex: 1; min-width: 0; }
.thread__subject {
  font-family: 'Fraunces', serif; font-size: 1rem;
  font-weight: 700; color: var(--indigo);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.thread__meta { font-size: .76rem; color: var(--gray-400); margin-top: 2px; }
.thread__del {
  background: none; border: none; cursor: pointer; font-size: 1rem;
  padding: 6px; border-radius: 8px; color: var(--gray-400);
  transition: all var(--transition);
}
.thread__del:hover { background: rgba(231,76,60,.1); color: #e74c3c; }
.thread__body {
  flex: 1; overflow-y: auto; padding: 20px;
  display: flex; flex-direction: column; gap: 16px;
  scrollbar-width: thin; scrollbar-color: var(--gray-200) transparent;
}
.thread-msg { display: flex; gap: 10px; align-items: flex-end; }
.thread-msg--outgoing { flex-direction: row-reverse; }
.thread-msg__avatar {
  width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--teal), var(--indigo));
  display: flex; align-items: center; justify-content: center;
  font-size: .72rem; font-weight: 700; color: #fff;
}
.thread-msg__avatar--you { background: linear-gradient(135deg, var(--coral), #ff8a8d); font-size: .6rem; }
.thread-msg__bubble {
  max-width: 75%; padding: 12px 14px;
  background: var(--gray-50); border-radius: 16px 16px 16px 4px;
  border: 1px solid var(--gray-200);
}
.bubble--you {
  background: var(--indigo); border-color: var(--indigo);
  border-radius: 16px 16px 4px 16px;
}
.bubble--you .thread-msg__name,
.bubble--you .thread-msg__text,
.bubble--you .thread-msg__time { color: rgba(255,255,255,.9); }
.bubble--you .thread-msg__time { color: rgba(255,255,255,.5); }
.thread-msg__name { font-size: .72rem; font-weight: 700; color: var(--gray-400); margin-bottom: 4px; }
.thread-msg__text { font-size: .86rem; color: var(--indigo); line-height: 1.55; margin: 0 0 6px; }
.thread-msg__time { font-size: .7rem; color: var(--gray-400); }
.thread__reply {
  border-top: 1px solid var(--gray-200); padding: 14px 16px;
  flex-shrink: 0; background: #fff;
}
.thread__reply-error {
  font-size: .78rem; color: var(--coral); font-weight: 600;
  margin: 0 0 8px; padding: 8px 12px;
  background: rgba(255,90,95,.08); border-radius: 8px;
}
.thread__reply-input {
  width: 100%; border: 1.5px solid var(--gray-200); border-radius: 12px;
  padding: 10px 13px; font-size: .88rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); resize: none; outline: none;
  transition: border-color var(--transition); box-sizing: border-box;
}
.thread__reply-input:focus { border-color: var(--teal); }
.thread__reply-footer {
  display: flex; align-items: center; justify-content: space-between; margin-top: 8px;
}
.thread__reply-hint { font-size: .72rem; color: var(--gray-400); }
.btn-send {
  background: var(--indigo); color: #fff; border: none;
  padding: 8px 20px; border-radius: 50px; font-size: .84rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition);
}
.btn-send:hover:not(:disabled) { background: #3d4460; }
.btn-send:disabled { opacity: .4; cursor: not-allowed; }
.thread-slide-enter-active, .thread-slide-leave-active { transition: all .28s ease; }
.thread-slide-enter-from, .thread-slide-leave-to { opacity: 0; transform: translateX(40px); }
</style>