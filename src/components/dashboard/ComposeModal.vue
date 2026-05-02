<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal">
          <div class="modal__header">
            <h3 class="modal__title">{{ isReply ? 'Reply' : 'New Message' }}</h3>
            <button class="modal__close" @click="close">✕</button>
          </div>

          <div class="modal__body">

            <!-- TO field — locked when replying, editable when composing -->
            <div class="field">
              <label class="field__label">To</label>

              <!-- Reply mode: show resolved name, no editing -->
              <div v-if="isReply" class="field__resolved">
                <span class="field__resolved-name">{{ resolvedName }}</span>
                <span class="field__resolved-badge">Reply</span>
              </div>

              <!-- Compose mode: email input with lookup -->
              <template v-else>
                <input
                  v-model="form.to"
                  class="field__input"
                  placeholder="Recipient email"
                  @blur="lookupUser"
                />
                <p v-if="lookupStatus === 'found'"    class="field__found">✓ {{ resolvedName }}</p>
                <p v-if="lookupStatus === 'notfound'" class="field__error">No user found with that email.</p>
              </template>

              <p v-if="errors.to" class="field__error">{{ errors.to }}</p>
            </div>

            <div class="field">
              <label class="field__label">Subject</label>
              <input
                v-model="form.subject"
                class="field__input"
                placeholder="Message subject"
              />
              <p v-if="errors.subject" class="field__error">{{ errors.subject }}</p>
            </div>

            <!-- Quote original message when replying -->
            <div v-if="isReply && replyTo?.content" class="field__quote">
              <span class="field__quote-label">Original message</span>
              <p class="field__quote-body">{{ replyTo.content }}</p>
            </div>

            <div class="field">
              <label class="field__label">Message</label>
              <textarea
                v-model="form.body"
                class="field__input field__textarea"
                placeholder="Write your message…"
                rows="5"
              />
              <p v-if="errors.body" class="field__error">{{ errors.body }}</p>
            </div>
          </div>

          <div class="modal__footer">
            <button class="btn-ghost" @click="close">Cancel</button>
            <button
              class="btn-primary"
              @click="submit"
              :disabled="(!isReply && lookupStatus === 'notfound') || sending"
            >
              {{ sending ? 'Sending…' : (isReply ? 'Send Reply' : 'Send Message') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useNotifications } from '@/composables/useNotifications'

const props = defineProps({
  modelValue:    { type: Boolean, default: false },
  currentUserId: { type: Number,  default: null  },
  // Pass the message being replied to.
  // Shape: { messageID, from, sender_id, title, content }
  replyTo:       { type: Object,  default: null  },
})
const emit = defineEmits(['update:modelValue', 'send'])
const { push: pushNotification } = useNotifications()

const API = '/arrivo-website/backend/api/v1'

const isReply = computed(() => !!props.replyTo)

const blankForm = () => ({ to: '', subject: '', body: '' })
const form         = ref(blankForm())
const errors       = ref({})
const sending      = ref(false)
const lookupStatus = ref(null)   // null | 'found' | 'notfound'
const resolvedName = ref('')
const resolvedId   = ref(null)

// When the modal opens, reset state and pre-fill if replying
watch(() => props.modelValue, async (open) => {
  if (!open) return

  form.value         = blankForm()
  errors.value       = {}
  lookupStatus.value = null

  if (props.replyTo) {
    // Pre-resolve from the message object.
    // sender_id comes from the DB; fall back to id field if needed.
    const senderId = props.replyTo.sender_id ?? props.replyTo.id ?? null
    const senderName = props.replyTo.from ?? ''

    if (senderId) {
      resolvedId.value   = senderId
      resolvedName.value = senderName
    } else if (senderName) {
      // sender_id not available — try to look up by name isn't reliable,
      // so we fetch by id if we have it, else show name only
      resolvedName.value = senderName
      resolvedId.value   = null
      // Try fetching sender id from the DB using the message id
      await resolveSenderId()
    }

    // Pre-fill subject as Re: original title
    const originalSubject = props.replyTo.title ?? props.replyTo.subject ?? ''
    form.value.subject = originalSubject.startsWith('Re:')
      ? originalSubject
      : `Re: ${originalSubject}`
  } else {
    resolvedId.value   = null
    resolvedName.value = ''
  }
})

// Fetch the sender's user id from the DB using the message id
async function resolveSenderId() {
  const msgId = props.replyTo?.messageID ?? props.replyTo?.id
  if (!msgId) return
  try {
    // messages.php returns sender_id in each message row — re-fetch the single message
    const res  = await fetch(`${API}/messages.php?message_id=${msgId}`)
    const data = await res.json()
    if (res.ok && data.message?.sender_id) {
      resolvedId.value = data.message.sender_id
    }
  } catch { /* silent — validate() will catch missing id */ }
}

async function lookupUser() {
  const email = form.value.to.trim()
  if (!email) return
  lookupStatus.value = null
  try {
    const res  = await fetch(`${API}/users.php?email=${encodeURIComponent(email)}`)
    const data = await res.json()
    if (res.ok && data.user) {
      resolvedId.value   = data.user.id
      resolvedName.value = data.user.name
      lookupStatus.value = 'found'
    } else {
      resolvedId.value   = null
      lookupStatus.value = 'notfound'
    }
  } catch {
    lookupStatus.value = 'notfound'
  }
}

function validate() {
  const e = {}

  if (isReply.value) {
    // In reply mode the recipient is pre-resolved — only body is needed
    if (!resolvedId.value) e.to = 'Could not resolve original sender. Please compose a new message instead.'
  } else {
    if (!form.value.to.trim()) e.to = 'Recipient is required.'
    if (!resolvedId.value)     e.to = 'Please enter a valid user email and wait for it to resolve.'
  }

  if (!form.value.subject.trim()) e.subject = 'Subject is required.'
  if (!form.value.body.trim())    e.body    = 'Message cannot be empty.'
  errors.value = e
  return !Object.keys(e).length
}

async function submit() {
  if (!validate()) return
  sending.value = true
  try {
    const res = await fetch(`${API}/messages.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        sender_id:   props.currentUserId,
        receiver_id: resolvedId.value,
        subject:     form.value.subject,
        content:     form.value.body,
      }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Send failed')

    emit('send', {
      messageID: data.message_id,
      from:      'You',
      to:        resolvedName.value,
      title:     form.value.subject,
      content:   form.value.body,
      date:      'Just now',
      sent:      true,
      replies:   [],
    })

    // ── Notify Recipient ──────────────────────────────────────────
    pushNotification({
      roles: ['agency', 'provider'], 
      targetUserId: resolvedId.value,
      type: 'message',
      icon: '✉️',
      title: isReply.value ? 'New reply received' : 'New message received',
      body: `You have a new message: "${form.value.subject}"`,
      link: '/dashboard',
      section: 'messages'
    })

    close()
  } catch (err) {
    errors.value.body = err.message
  } finally {
    sending.value = false
  }
}

function close() { emit('update:modelValue', false) }
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(45,49,66,.45);
  display: flex; align-items: center; justify-content: center;
  z-index: 200; padding: 20px;
}
.modal {
  background: #fff; border-radius: 20px; width: 100%; max-width: 500px;
  box-shadow: 0 16px 64px rgba(45,49,66,.18);
  display: flex; flex-direction: column; max-height: 90vh;
}
.modal__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 22px 26px 16px; border-bottom: 1px solid var(--gray-200);
}
.modal__title {
  font-family: 'Fraunces', serif; font-size: 1.15rem;
  font-weight: 700; color: var(--indigo); margin: 0;
}
.modal__close {
  width: 30px; height: 30px; border-radius: 50%; border: none;
  background: var(--gray-100); color: var(--gray-600);
  cursor: pointer; font-size: .85rem; transition: background var(--transition);
}
.modal__close:hover { background: var(--gray-200); }
.modal__body   { padding: 20px 26px; display: flex; flex-direction: column; gap: 14px; overflow-y: auto; }
.modal__footer {
  padding: 14px 26px; border-top: 1px solid var(--gray-200);
  display: flex; justify-content: flex-end; gap: 10px;
}

/* Resolved recipient chip (reply mode) */
.field__resolved {
  display: flex; align-items: center; gap: 10px;
  background: var(--gray-50); border: 1.5px solid var(--gray-200);
  border-radius: var(--radius-sm); padding: 9px 12px;
}
.field__resolved-name  { font-size: .88rem; font-weight: 600; color: var(--indigo); }
.field__resolved-badge {
  font-size: .68rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; color: var(--teal);
  background: rgba(46,196,182,.12); padding: 2px 8px; border-radius: 50px;
}

/* Quoted original message */
.field__quote {
  background: var(--gray-50); border-left: 3px solid var(--gray-200);
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
  padding: 10px 14px; display: flex; flex-direction: column; gap: 4px;
}
.field__quote-label {
  font-size: .7rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .08em; color: var(--gray-400);
}
.field__quote-body {
  font-size: .82rem; color: var(--gray-600); line-height: 1.5;
  margin: 0; max-height: 80px; overflow: hidden; text-overflow: ellipsis;
}

.field        { display: flex; flex-direction: column; gap: 5px; }
.field__label { font-size: .8rem; font-weight: 600; color: var(--indigo); }
.field__input {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 9px 12px; font-size: .88rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); outline: none; transition: border-color var(--transition);
}
.field__input:focus  { border-color: var(--teal); }
.field__textarea     { resize: vertical; min-height: 110px; }
.field__error { font-size: .76rem; color: var(--coral); margin: 0; }
.field__found { font-size: .76rem; color: var(--teal);  margin: 0; font-weight: 600; }

.btn-primary {
  background: var(--indigo); color: #fff; border: none;
  padding: 9px 22px; border-radius: 50px; font-size: .86rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition);
}
.btn-primary:hover:not(:disabled) { background: #3d4460; }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; }
.btn-ghost {
  background: transparent; border: 1.5px solid var(--gray-200);
  color: var(--gray-600); padding: 9px 18px; border-radius: 50px;
  font-size: .86rem; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: all var(--transition);
}
.btn-ghost:hover { border-color: var(--gray-400); color: var(--indigo); }
.modal-fade-enter-active, .modal-fade-leave-active { transition: all .2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; transform: scale(.96); }
</style>