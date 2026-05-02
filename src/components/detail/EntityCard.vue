<template>
  <div class="entity-card-wrapper">
    <div class="entity-card" v-if="!hideCard">
      <div class="entity-card__top">
        <div class="entity-card__avatar">
          <img v-if="img" :src="img" :alt="name" />
          <span v-else>{{ name[0] }}</span>
        </div>
        <div class="entity-card__info">
          <div class="entity-card__label">{{ entityLabel }}</div>
          <div class="entity-card__name">{{ name }}</div>
          <div class="entity-card__rating">
            <span class="star">★</span> {{ rating }}
            <span class="entity-card__reviews">· {{ reviews }} reviews</span>
          </div>
        </div>
      </div>

      <p class="entity-card__bio">{{ bio }}</p>
    </div>

    <!-- ── Contact Modal ── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div class="contact-overlay" v-if="modalOpen" @click.self="close">
          <div class="contact-modal">
            <button class="contact-modal__close" @click="close">✕</button>

            <div class="contact-modal__header">
              <div class="contact-modal__avatar">
                <img v-if="img" :src="img" :alt="name" />
                <span v-else>{{ name[0] }}</span>
              </div>
              <div>
                <div class="contact-modal__label">{{ entityLabel }}</div>
                <div class="contact-modal__name">{{ name }}</div>
              </div>
            </div>

            <div class="contact-modal__body">
              <div v-if="!isLoggedIn" class="contact-modal__auth-warn">
                ⚠️ Please log in to send a message.
              </div>

              <template v-else>
                <div v-if="sent" class="contact-modal__success">
                  ✅ Message sent! They'll reply in your dashboard inbox.
                </div>

                <template v-else>
                  <div class="contact-modal__field">
                    <label>Subject</label>
                    <input v-model="subject" type="text" placeholder="e.g. Question about your package" :disabled="sending" />
                  </div>
                  <div class="contact-modal__field">
                    <label>Message</label>
                    <textarea v-model="message" rows="5" placeholder="Write your message here…" :disabled="sending" />
                  </div>
                  <div v-if="error" class="contact-modal__error">{{ error }}</div>
                  <button class="contact-modal__send" :disabled="sending || !message.trim()" @click="send">
                    {{ sending ? 'Sending…' : '💬 Send Message' }}
                  </button>
                </template>
              </template>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '@/composables/useAuth.js'
import { useNotifications } from '@/composables/useNotifications'

const props = defineProps({
  name:        { type: String,  required: true  },
  bio:         { type: String,  default: ''     },
  img:         { type: String,  default: ''     },
  rating:      { type: Number,  default: 0  },
  reviews:     { type: Number,  default: 0  },
  entityLabel: { type: String,  default: 'Agency' },
  receiverId:  { type: Number,  default: null   },  // agency_id or provider_id
  hideCard:    { type: Boolean, default: false  },
})

defineEmits(['contact'])

const { user, isLoggedIn } = useAuth()
const { push: pushNotification } = useNotifications()
const API = '/arrivo-website/backend/api/v1'

const modalOpen = ref(false)
const subject   = ref('')
const message   = ref('')
const sending   = ref(false)
const sent      = ref(false)
const error     = ref('')

defineExpose({ modalOpen })

function close() {
  if (sending.value) return
  modalOpen.value = false
  // reset after transition
  setTimeout(() => { subject.value = ''; message.value = ''; sent.value = false; error.value = '' }, 300)
}

async function send() {
  if (!message.value.trim()) return
  if (!props.receiverId) { error.value = 'Cannot determine recipient.'; return }

  sending.value = true
  error.value   = ''

  try {
    const res  = await fetch(`${API}/messages.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        sender_id:   user.value?.userID ?? user.value?.id,
        receiver_id: props.receiverId,
        subject:     subject.value.trim() || `Message from ${user.value?.first_name ?? 'a traveller'}`,
        content:     message.value.trim(),
      }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Failed to send')
    sent.value = true

    // ── Notify Recipient ──────────────────────────────────────────
    const targetRole = props.entityLabel === 'Provider' ? 'provider' : 'agency'
    pushNotification({
      roles: [targetRole],
      targetUserId: props.receiverId,
      type: 'message',
      icon: '✉️',
      title: 'New inquiry received',
      body: `${user.value?.name || 'A traveler'} sent a message: "${subject.value.trim() || 'No subject'}"`,
      link: '/dashboard',
      section: 'messages'
    })
  } catch (e) {
    error.value = e.message
  } finally {
    sending.value = false
  }
}
</script>

<style scoped>
/* ── Card ── */
.entity-card { background: var(--gray-50); border-radius: var(--radius); padding: 24px; border: 1px solid var(--gray-200); }
.entity-card__top { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
.entity-card__avatar { width: 60px; height: 60px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: linear-gradient(135deg, var(--teal), var(--indigo)); display: flex; align-items: center; justify-content: center; font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: #fff; }
.entity-card__avatar img { width: 100%; height: 100%; object-fit: cover; }
.entity-card__label   { font-size: .72rem; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
.entity-card__name    { font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: var(--indigo); margin-bottom: 4px; }
.entity-card__rating  { display: flex; align-items: center; gap: 4px; font-size: .84rem; font-weight: 600; color: var(--indigo); }
.entity-card__reviews { color: var(--gray-400); font-weight: 400; }
.entity-card__bio     { font-size: .88rem; color: var(--gray-600); line-height: 1.7; margin-bottom: 18px; }
.entity-card__contact { width: 100%; padding: 12px; border: 1.5px solid var(--gray-200); border-radius: 12px; background: var(--white); font-family: 'DM Sans', sans-serif; font-size: .9rem; font-weight: 600; color: var(--indigo); cursor: pointer; transition: all var(--transition); }
.entity-card__contact:hover { border-color: var(--teal); color: var(--teal); }

/* ── Modal overlay ── */
.contact-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 200; display: flex; align-items: center; justify-content: center; padding: 16px; }
.contact-modal   { background: var(--white); border-radius: 20px; width: 100%; max-width: 480px; padding: 32px; position: relative; box-shadow: 0 24px 60px rgba(0,0,0,.18); }
.contact-modal__close { position: absolute; top: 16px; right: 16px; background: var(--gray-100); border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-size: .9rem; color: var(--gray-600); display: flex; align-items: center; justify-content: center; transition: background var(--transition); }
.contact-modal__close:hover { background: var(--gray-200); }

.contact-modal__header { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--gray-100); }
.contact-modal__avatar { width: 50px; height: 50px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: linear-gradient(135deg, var(--teal), var(--indigo)); display: flex; align-items: center; justify-content: center; font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; color: #fff; }
.contact-modal__avatar img { width: 100%; height: 100%; object-fit: cover; }
.contact-modal__label { font-size: .72rem; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }
.contact-modal__name  { font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 700; color: var(--indigo); }

.contact-modal__body { display: flex; flex-direction: column; gap: 16px; }
.contact-modal__field { display: flex; flex-direction: column; gap: 6px; }
.contact-modal__field label { font-size: .8rem; font-weight: 700; color: var(--gray-600); text-transform: uppercase; letter-spacing: .04em; }
.contact-modal__field input,
.contact-modal__field textarea { padding: 11px 14px; border: 1.5px solid var(--gray-200); border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: .92rem; color: var(--indigo); background: var(--gray-50); resize: vertical; transition: border-color var(--transition); }
.contact-modal__field input:focus,
.contact-modal__field textarea:focus { outline: none; border-color: var(--teal); background: var(--white); }
.contact-modal__field input:disabled,
.contact-modal__field textarea:disabled { opacity: .6; }

.contact-modal__send { padding: 13px; background: var(--teal); color: var(--white); border: none; border-radius: 12px; font-family: 'DM Sans', sans-serif; font-size: .95rem; font-weight: 700; cursor: pointer; transition: background var(--transition); }
.contact-modal__send:hover:not(:disabled) { background: var(--teal-dk); }
.contact-modal__send:disabled { opacity: .5; cursor: not-allowed; }

.contact-modal__success  { background: rgba(39,174,96,.1); color: #1a7a45; border: 1px solid rgba(39,174,96,.25); border-radius: 10px; padding: 14px 16px; font-size: .9rem; font-weight: 600; text-align: center; }
.contact-modal__error    { background: rgba(235,87,87,.08); color: #c0392b; border: 1px solid rgba(235,87,87,.2);  border-radius: 10px; padding: 10px 14px; font-size: .85rem; }
.contact-modal__auth-warn{ background: rgba(255,193,7,.1); color: #856404; border: 1px solid rgba(255,193,7,.3);   border-radius: 10px; padding: 14px 16px; font-size: .9rem; text-align: center; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity .2s ease; }
.modal-fade-enter-from,   .modal-fade-leave-to     { opacity: 0; }
</style>