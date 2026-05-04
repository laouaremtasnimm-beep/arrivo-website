<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="modelValue && collab" class="modal-backdrop" @click.self="close">
        <div class="modal">

          <div class="modal__header">
            <div>
              <div class="step-badge">Counter-proposal</div>
              <h2 class="modal__title">Adjust the terms</h2>
              <p class="step-sub">
                You can adjust discount and/or dates. Service and package stay the same.
              </p>
            </div>
            <button class="modal__close" @click="close">✕</button>
          </div>

          <div class="modal__body">

            <!-- Original terms snapshot -->
            <div class="original-terms">
              <div class="original-terms__label">Original terms from agency</div>
              <div class="original-terms__row">
                <span>Discount</span><strong>{{ collab.discount_pct }}%</strong>
              </div>
              <div class="original-terms__row" v-if="collab.start_date">
                <span>Dates</span>
                <strong>{{ fmtDate(collab.start_date) }} → {{ fmtDate(collab.end_date) }}</strong>
              </div>
            </div>

            <!-- New discount -->
            <div class="form-group">
              <label class="form-label">New discount (%)</label>
              <div class="discount-input-wrap">
                <input
                  type="number" min="1" max="99"
                  class="form-input"
                  v-model.number="form.counter_discount_pct"
                  :placeholder="collab.discount_pct"
                />
                <span class="discount-suffix">% off</span>
              </div>
              <p class="field-error" v-if="errors.counter_discount_pct">
                {{ errors.counter_discount_pct }}
              </p>
            </div>

            <!-- New dates -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">New start date</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="form.counter_start_date" :min="today" />
                  <span class="date-icon">🗓️</span>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">New end date</label>
                <div class="date-wrap">
                  <input type="date" class="form-input"
                    v-model="form.counter_end_date"
                    :min="form.counter_start_date || today"
                  />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.counter_end_date">{{ errors.counter_end_date }}</p>
              </div>
            </div>

            <!-- Counter message -->
            <div class="form-group">
              <label class="form-label">Message (optional)</label>
              <textarea
                class="form-input form-textarea"
                v-model="form.counter_message"
                placeholder="Explain why you'd like different terms…"
                rows="3"
              />
            </div>

            <p class="field-error" v-if="errors.general">{{ errors.general }}</p>

          </div>

          <div class="modal__footer">
            <button type="button" class="btn btn-outline" @click="close">Cancel</button>
            <button type="button" class="btn btn-amber" @click="submit" :disabled="submitting">
              <span v-if="submitting" class="btn-spinner"></span>
              Send Counter-Proposal
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

const props = defineProps({
  modelValue: Boolean,
  collab:     { type: Object, default: null },
})
const emit = defineEmits(['update:modelValue', 'submitted'])

const { user } = useAuth()
const BASE = import.meta.env.VITE_API_URL ?? '/arrivo-website/backend/api/v1'

const submitting = ref(false)
const errors     = ref({})

const blankForm = () => ({
  counter_discount_pct: null,
  counter_start_date:   '',
  counter_end_date:     '',
  counter_message:      '',
})
const form = ref(blankForm())

const today = computed(() => new Date().toISOString().split('T')[0])

watch(() => props.modelValue, (open) => {
  if (open) { form.value = blankForm(); errors.value = {} }
})

function fmtDate(d) {
  if (!d) return ''
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}

function validate() {
  const e = {}
  if (form.value.counter_discount_pct !== null && form.value.counter_discount_pct !== '') {
    const v = Number(form.value.counter_discount_pct)
    if (v < 1 || v > 99) e.counter_discount_pct = 'Must be between 1 and 99.'
  }
  if (form.value.counter_start_date && form.value.counter_end_date
      && form.value.counter_end_date < form.value.counter_start_date) {
    e.counter_end_date = 'End date must be after start date.'
  }

  const hasAny = form.value.counter_discount_pct || form.value.counter_start_date
    || form.value.counter_end_date || form.value.counter_message
  if (!hasAny) e.general = 'Adjust at least one field to send a counter-proposal.'

  errors.value = e
  return !Object.keys(e).length
}

async function submit() {
  if (!validate()) return
  submitting.value = true

  const payload = {
    id:       props.collab.id,
    action:   'counter',
    actor_id: user.value?.id,
  }
  if (form.value.counter_discount_pct) payload.counter_discount_pct = form.value.counter_discount_pct
  if (form.value.counter_start_date)   payload.counter_start_date   = form.value.counter_start_date
  if (form.value.counter_end_date)     payload.counter_end_date     = form.value.counter_end_date
  if (form.value.counter_message)      payload.counter_message      = form.value.counter_message

  try {
    const res  = await fetch(`${BASE}/collaborations.php`, {
      method:  'PUT',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload),
    })
    const data = await res.json()

    if (!res.ok) {
      errors.value.general = data.error ?? 'Something went wrong.'
      return
    }

    emit('submitted', data.collaboration)
    close()
  } catch (err) {
    errors.value.general = 'Network error — please try again.'
  } finally {
    submitting.value = false
  }
}

function close() {
  emit('update:modelValue', false)
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(45,49,66,.48);
  display: flex; align-items: center; justify-content: center;
  z-index: 300; padding: 20px;
}
.modal {
  background: #fff; border-radius: 22px; width: 100%; max-width: 460px;
  box-shadow: 0 20px 72px rgba(45,49,66,.22);
  max-height: 92vh; display: flex; flex-direction: column;
}
.modal__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 20px 26px 14px; border-bottom: 1px solid var(--gray-100); flex-shrink: 0;
}
.step-badge {
  font-size: .7rem; font-weight: 700; letter-spacing: .07em;
  color: #c47a00; text-transform: uppercase; margin-bottom: 3px;
}
.modal__title {
  font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700;
  color: var(--indigo); margin: 0 0 3px;
}
.step-sub { font-size: .8rem; color: var(--gray-600); margin: 0; }
.modal__close {
  background: var(--gray-100); border: none; border-radius: 50%;
  width: 32px; height: 32px; cursor: pointer; font-size: .85rem; color: var(--gray-600);
  flex-shrink: 0;
}
.modal__close:hover { background: var(--gray-200); }
.modal__body {
  padding: 18px 26px; overflow-y: auto; flex: 1;
  display: flex; flex-direction: column; gap: 14px;
}
.modal__footer {
  padding: 14px 26px; border-top: 1px solid var(--gray-100);
  display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;
}

/* Original terms */
.original-terms {
  border-radius: 10px; background: var(--gray-50, #f9f9fc);
  border: 1px solid var(--gray-200); padding: 12px 14px;
  display: flex; flex-direction: column; gap: 7px;
}
.original-terms__label {
  font-size: .7rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: .06em; color: var(--gray-500); margin-bottom: 2px;
}
.original-terms__row {
  display: flex; justify-content: space-between;
  font-size: .84rem; color: var(--gray-700);
}

/* Form */
.form-group   { display: flex; flex-direction: column; gap: 5px; }
.form-label   { font-size: .82rem; font-weight: 600; color: var(--indigo); }
.form-row     { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-input   {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 10px 13px; font-size: .9rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); outline: none; background: #fff;
  transition: border-color var(--transition);
}
.form-input:focus { border-color: #FFB347; }
.form-textarea { resize: vertical; min-height: 80px; }
.field-error   { font-size: .78rem; color: var(--coral); margin: 0; }

.discount-input-wrap { position: relative; display: flex; align-items: center; }
.discount-input-wrap .form-input { padding-right: 52px; width: 100%; }
.discount-suffix {
  position: absolute; right: 13px; font-size: .82rem;
  font-weight: 700; color: var(--gray-500); pointer-events: none;
}
.date-wrap { position: relative; }
.date-wrap .form-input { padding-right: 36px; cursor: pointer; }
.date-wrap input::-webkit-calendar-picker-indicator {
  position: absolute; right: 0; top: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 1;
}
.date-icon {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  pointer-events: none; font-size: 1rem; z-index: 2;
}

/* Buttons */
.btn {
  padding: 10px 22px; border-radius: 50px; font-size: .88rem; font-weight: 700;
  cursor: pointer; font-family: 'DM Sans', sans-serif; border: none;
  display: flex; align-items: center; gap: 6px; transition: all var(--transition);
}
.btn:disabled { opacity: .55; cursor: not-allowed; }
.btn-amber { background: #FFB347; color: #fff; }
.btn-amber:hover:not(:disabled) { background: #e09c2e; }
.btn-outline { background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-600); }
.btn-outline:hover { border-color: var(--gray-400); }
.btn-spinner {
  width: 13px; height: 13px; border: 2px solid rgba(255,255,255,.4);
  border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.modal-fade-enter-active, .modal-fade-leave-active { transition: all .22s ease; }
.modal-fade-enter-from,   .modal-fade-leave-to     { opacity: 0; transform: scale(.97); }
</style>