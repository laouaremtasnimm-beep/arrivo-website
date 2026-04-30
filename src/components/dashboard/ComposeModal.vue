<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal">
          <div class="modal__header">
            <h3 class="modal__title">New Message</h3>
            <button class="modal__close" @click="close">✕</button>
          </div>

          <div class="modal__body">
            <div class="field">
              <label class="field__label">To (email)</label>
              <input
                v-model="form.to"
                class="field__input"
                placeholder="Recipient email"
                @blur="lookupUser"
              />
              <p v-if="lookupStatus === 'found'" class="field__found">✓ {{ resolvedName }}</p>
              <p v-if="lookupStatus === 'notfound'" class="field__error">No user found with that email.</p>
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
            <button class="btn-primary" @click="submit" :disabled="lookupStatus === 'notfound' || sending">
              {{ sending ? 'Sending…' : 'Send Message' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue:    Boolean,
  currentUserId: { type: Number, default: null },
})
const emit = defineEmits(['update:modelValue', 'send'])

const API = '/arrivo-website/backend/api/v1'

const blankForm = () => ({ to: '', subject: '', body: '' })
const form          = ref(blankForm())
const errors        = ref({})
const sending       = ref(false)
const lookupStatus  = ref(null)   // null | 'found' | 'notfound'
const resolvedName  = ref('')
const resolvedId    = ref(null)

watch(() => props.modelValue, v => {
  if (v) {
    form.value      = blankForm()
    errors.value    = {}
    lookupStatus.value = null
    resolvedId.value   = null
    resolvedName.value = ''
  }
})

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
  if (!form.value.to.trim())      e.to      = 'Recipient is required.'
  if (!resolvedId.value)          e.to      = 'Please enter a valid user email.'
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
      read:      true,
      sent:      true,
      replies:   [],
    })
    close()
  } catch (e) {
    errors.value.body = e.message
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
.field        { display: flex; flex-direction: column; gap: 5px; }
.field__label { font-size: .8rem; font-weight: 600; color: var(--indigo); }
.field__input {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 9px 12px; font-size: .88rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); outline: none; transition: border-color var(--transition);
}
.field__input:focus { border-color: var(--teal); }
.field__textarea    { resize: vertical; min-height: 110px; }
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