<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="modelValue" class="modal-backdrop" @click.self="close">
        <div class="modal">

          <div class="modal__header">
            <div>
              <h2 class="modal__title">Propose a Collaboration</h2>
              <p class="modal__sub">Invite a partner to co-create a joint offer</p>
            </div>
            <button class="modal__close" @click="close">✕</button>
          </div>

          <div class="modal__body">

            <!-- Partner selector -->
            <div class="field">
              <label class="field__label">Choose a partner</label>
              <div class="partner-grid">
                <button
                  v-for="p in availablePartners"
                  :key="p.id"
                  class="partner-card"
                  :class="{ selected: form.partnerId === p.id }"
                  @click="form.partnerId = p.id"
                >
                  <div class="partner-avatar" :style="{ background: p.color }">
                    {{ initials(p.name) }}
                  </div>
                  <div class="partner-info">
                    <div class="partner-name">{{ p.name }}</div>
                    <div class="partner-role">{{ p.role }}</div>
                  </div>
                  <div v-if="form.partnerId === p.id" class="partner-check">✓</div>
                </button>
              </div>
              <p v-if="errors.partnerId" class="field__error">{{ errors.partnerId }}</p>
            </div>

            <!-- Offer title -->
            <div class="field">
              <label class="field__label">Joint offer title</label>
              <input
                v-model="form.title"
                class="field__input"
                placeholder="e.g. Alps Fly & Drive Combo"
              />
              <p v-if="errors.title" class="field__error">{{ errors.title }}</p>
            </div>

            <!-- Discount + dates row -->
            <div class="row-2">
              <div class="field">
                <label class="field__label">Discount (%)</label>
                <input
                  v-model.number="form.discount"
                  type="number" min="1" max="80"
                  class="field__input"
                  placeholder="e.g. 20"
                />
                <p v-if="errors.discount" class="field__error">{{ errors.discount }}</p>
              </div>
              <div class="field">
                <label class="field__label">Offer type</label>
                <select v-model="form.type" class="field__input">
                  <option value="">Select type</option>
                  <option value="Bundle">Bundle</option>
                  <option value="Seasonal">Seasonal</option>
                  <option value="Flash Sale">Flash Sale</option>
                  <option value="Loyalty">Loyalty</option>
                </select>
              </div>
            </div>

            <div class="row-2">
              <div class="field">
                <label class="field__label">Start date</label>
                <input v-model="form.startDate" type="date" class="field__input" />
              </div>
              <div class="field">
                <label class="field__label">End date</label>
                <input v-model="form.endDate" type="date" class="field__input" />
              </div>
            </div>

            <!-- Description -->
            <div class="field">
              <label class="field__label">Proposal message</label>
              <textarea
                v-model="form.description"
                class="field__input field__textarea"
                placeholder="Describe the joint offer and what each party contributes…"
                rows="3"
              />
              <p v-if="errors.description" class="field__error">{{ errors.description }}</p>
            </div>

          </div>

          <div class="modal__footer">
            <button class="btn-ghost" @click="close">Cancel</button>
            <button class="btn-primary" @click="submit">
              Send Request
            </button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { useNotifications } from '@/composables/useNotifications'

const props = defineProps({
  modelValue: Boolean,
})
const emit = defineEmits(['update:modelValue', 'send'])

const { user, isAgency } = useAuth()
const { push: pushNotification } = useNotifications()

// Partners list — agencies see providers, providers see agencies
const allPartners = [
  { id: 'p1', name: 'Alpine Escapes',     role: 'Service Provider', color: '#2EC4B6' },
  { id: 'p2', name: 'Coastal Rides',      role: 'Service Provider', color: '#FF5A5F' },
  { id: 'p3', name: 'Summit Transfers',   role: 'Service Provider', color: '#F4EBD0' },
  { id: 'a1', name: 'Wanderlust Travels', role: 'Travel Agency',    color: '#2D3142' },
  { id: 'a2', name: 'Horizon Trips',      role: 'Travel Agency',    color: '#7B61FF' },
  { id: 'a3', name: 'SkyRoute Agency',    role: 'Travel Agency',    color: '#FF8C42' },
]

const availablePartners = computed(() =>
  isAgency.value
    ? allPartners.filter(p => p.role === 'Service Provider')
    : allPartners.filter(p => p.role === 'Travel Agency')
)

const defaultForm = () => ({
  partnerId: '',
  title: '',
  discount: '',
  type: '',
  startDate: '',
  endDate: '',
  description: '',
})

const form   = ref(defaultForm())
const errors = ref({})

watch(() => props.modelValue, v => { if (v) { form.value = defaultForm(); errors.value = {} } })

function initials(name) {
  return name.split(' ').map(w => w[0]).slice(0, 2).join('')
}

function validate() {
  const e = {}
  if (!form.value.partnerId)   e.partnerId   = 'Please select a partner.'
  if (!form.value.title.trim()) e.title       = 'Offer title is required.'
  if (!form.value.discount || form.value.discount < 1) e.discount = 'Enter a valid discount.'
  if (!form.value.description.trim()) e.description = 'Add a short proposal message.'
  errors.value = e
  return !Object.keys(e).length
}

function submit() {
  if (!validate()) return
  const partner = allPartners.find(p => p.id === form.value.partnerId)
  emit('send', {
    collabID:    Date.now(),
    status:      'pending',   // pending | accepted | declined
    initiator:   { name: user.value?.name || 'You', role: user.value?.role },
    partner,
    ...form.value,
    sentDate:    new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }),
  })

  // ── Notify Partner ──────────────────────────────────────────────
  const targetRole = partner.role === 'Service Provider' ? 'provider' : 'agency'
  pushNotification({
    roles: [targetRole],
    targetUserId: partner.id,
    type: 'collab',
    icon: '🤝',
    title: 'New collaboration request',
    body: `${user.value?.name || 'A partner'} wants to collaborate on "${form.value.title}".`,
    link: '/dashboard',
    section: 'collaborations'
  })

  close()
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
  background: #fff; border-radius: 20px; width: 100%; max-width: 560px;
  box-shadow: 0 16px 64px rgba(45,49,66,.18); max-height: 90vh;
  display: flex; flex-direction: column;
}

.modal__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 24px 28px 16px; border-bottom: 1px solid var(--gray-200);
}
.modal__title {
  font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700; color: var(--indigo); margin: 0;
}
.modal__sub { font-size: .83rem; color: var(--gray-600); margin: 4px 0 0; }
.modal__close {
  background: var(--gray-100); border: none; border-radius: 50%;
  width: 32px; height: 32px; cursor: pointer; font-size: .9rem;
  color: var(--gray-600); flex-shrink: 0;
  transition: background var(--transition);
}
.modal__close:hover { background: var(--gray-200); }

.modal__body { padding: 20px 28px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 16px; }
.modal__footer {
  padding: 16px 28px; border-top: 1px solid var(--gray-200);
  display: flex; justify-content: flex-end; gap: 10px;
}

/* Fields */
.field { display: flex; flex-direction: column; gap: 6px; }
.field__label { font-size: .82rem; font-weight: 600; color: var(--indigo); }
.field__input {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 10px 13px; font-size: .9rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); outline: none; background: #fff;
  transition: border-color var(--transition);
}
.field__input:focus { border-color: var(--coral); }
.field__textarea { resize: vertical; min-height: 80px; }
.field__error { font-size: .78rem; color: var(--coral); margin: 0; }
.row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* Partner cards */
.partner-grid { display: flex; flex-direction: column; gap: 8px; }
.partner-card {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 14px; border-radius: var(--radius-sm);
  border: 1.5px solid var(--gray-200); background: #fff;
  cursor: pointer; transition: all var(--transition); text-align: left;
}
.partner-card:hover  { border-color: var(--teal); background: var(--teal-lt); }
.partner-card.selected { border-color: var(--teal); background: var(--teal-lt); }
.partner-avatar {
  width: 38px; height: 38px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .78rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.partner-info { flex: 1; }
.partner-name { font-size: .88rem; font-weight: 600; color: var(--indigo); }
.partner-role { font-size: .76rem; color: var(--gray-600); }
.partner-check {
  width: 22px; height: 22px; border-radius: 50%; background: var(--teal);
  color: #fff; font-size: .7rem; display: flex; align-items: center; justify-content: center;
}

/* Buttons */
.btn-primary {
  background: var(--coral); color: #fff; border: none;
  padding: 10px 24px; border-radius: 50px; font-size: .88rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition);
}
.btn-primary:hover { background: var(--coral-dk); }
.btn-ghost {
  background: transparent; color: var(--gray-600);
  border: 1.5px solid var(--gray-200); padding: 10px 20px;
  border-radius: 50px; font-size: .88rem; cursor: pointer;
  font-family: 'DM Sans', sans-serif; transition: all var(--transition);
}
.btn-ghost:hover { border-color: var(--gray-400); color: var(--indigo); }

/* Transitions */
.modal-fade-enter-active, .modal-fade-leave-active { transition: all .22s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; transform: scale(.96); }
</style>