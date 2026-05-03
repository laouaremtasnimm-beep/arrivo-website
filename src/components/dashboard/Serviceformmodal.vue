<template>
  <Teleport to="body">
    <Transition name="fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal">

          <button class="modal__close" @click="close">✕</button>

          <h2 class="modal__title">{{ isEdit ? 'Edit Service' : 'Add New Service' }}</h2>
          <p class="modal__sub">{{ isEdit ? 'Update your service details below.' : 'Fill in the details to list a new service.' }}</p>

          <div class="modal__body">

            <!-- Title -->
            <div class="form-group">
              <label class="form-label">Service title *</label>
              <input class="form-input" v-model="form.title" placeholder="e.g. Private Airport Transfer" />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <!-- Category + Icon -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Category *</label>
                <select class="form-input form-select" v-model="form.type">
                  <option value="">Select type…</option>
                  <option v-for="t in categories" :key="t" :value="t">{{ t }}</option>
                </select>
                <p class="field-error" v-if="errors.type">{{ errors.type }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Icon (Emoji)</label>
                <input class="form-input text-center" v-model="form.icon" maxlength="2" placeholder="🛎️" />
              </div>
            </div>

            <!-- Price + Unit -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Price ($) *</label>
                <input class="form-input" v-model.number="form.price" type="number" min="0" placeholder="e.g. 45" />
                <p class="field-error" v-if="errors.price">{{ errors.price }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Price unit</label>
                <select class="form-input form-select" v-model="form.unit">
                  <option value="day">Per day</option>
                  <option value="trip">Per trip</option>
                  <option value="person">Per person</option>
                  <option value="hour">Per hour</option>
                </select>
              </div>
            </div>

            <!-- Start & End Dates (Obligatory) -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Available from *</label>
                <div class="date-input-wrap">
                  <input class="form-input" v-model="form.start_date" type="date" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.start_date">{{ errors.start_date }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Available until *</label>
                <div class="date-input-wrap">
                  <input class="form-input" v-model="form.end_date" type="date" :min="form.start_date" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.end_date">{{ errors.end_date }}</p>
              </div>
            </div>

            <!-- Description -->
            <div class="form-group">
              <label class="form-label">Short description *</label>
              <textarea
                class="form-input form-textarea"
                v-model="form.desc"
                placeholder="A brief summary of your service…"
                rows="3"
              />
              <p class="field-error" v-if="errors.desc">{{ errors.desc }}</p>
            </div>

            <!-- Features -->
            <div class="form-group">
              <label class="form-label">Features / Inclusions <span class="form-hint">(one per line)</span></label>
              <textarea
                class="form-input form-textarea"
                v-model="featuresRaw"
                placeholder="Free cancellation&#10;Insurance included&#10;English speaking guide"
                rows="4"
              />
            </div>

          </div>

          <div class="modal__footer">
            <button class="btn btn-outline" @click="close">Cancel</button>
            <button class="btn btn-coral modal__submit" @click="submit" :disabled="loading">
              <span class="spinner" v-if="loading" />
              <span v-else>{{ isEdit ? 'Save changes' : 'Add service' }}</span>
            </button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  service:    { type: Object,  default: null  },
})

const emit = defineEmits(['update:modelValue', 'save'])

const isEdit  = computed(() => !!props.service)
const today = computed(() => new Date().toISOString().split('T')[0])
const loading = ref(false)
const errors  = ref({})

const categories = ['Transport', 'Accommodation', 'Activity', 'Tour', 'Restaurant', 'Wellness', 'Other']

// ── Form state ─────────────────────────────────────────────────────────────
const defaultForm = () => ({
  title: '', type: '', icon: '🛎️',
  price: null, unit: 'day', start_date: '', end_date: '', desc: '',
})
const form        = ref(defaultForm())
const featuresRaw = ref('')

// Populate form when editing
watch(() => props.service, (svc) => {
  if (svc) {
    form.value = {
      id:          svc.id,
      title:       svc.title       || '',
      type:        svc.type        || '',
      icon:        svc.icon        || '🛎️',
      price:       svc.price       || null,
      unit:        svc.price_unit  || svc.unit || 'day',
      start_date:  svc.start_date  || '',
      end_date:    svc.end_date    || '',
      desc:        svc.description || svc.desc || '',
    }
    
    let feat = []
    try {
      feat = typeof svc.features === 'string' ? JSON.parse(svc.features) : (svc.features || [])
    } catch { feat = [] }
    featuresRaw.value = feat.join('\n')
  } else {
    form.value    = defaultForm()
    featuresRaw.value = ''
  }
  errors.value = {}
}, { immediate: true })

// ── Validation ─────────────────────────────────────────────────────────────
function validate() {
  const e = {}
  if (!form.value.title?.trim())      e.title      = 'Title is required.'
  if (!form.value.type)               e.type       = 'Please select a category.'
  if (!form.value.price || form.value.price < 0) e.price = 'Enter a valid price.'
  if (!form.value.start_date)         e.start_date = 'Start date is required.'
  if (!form.value.end_date)           e.end_date   = 'End date is required.'
  if (form.value.start_date && form.value.end_date && form.value.end_date < form.value.start_date)
    e.end_date = 'End date must be after start date.'
  if (!form.value.desc?.trim())       e.desc       = 'Description is required.'
  errors.value = e
  return Object.keys(e).length === 0
}

// ── Submit ─────────────────────────────────────────────────────────────────
async function submit() {
  if (!validate()) return
  loading.value = true
  await new Promise(r => setTimeout(r, 400))
  loading.value = false

  const payload = {
    id:           form.value.id,
    title:        form.value.title,
    type:         form.value.type,
    icon:         form.value.icon,
    price:        form.value.price,
    price_unit:   form.value.unit,
    start_date:   form.value.start_date,
    end_date:     form.value.end_date,
    description:  form.value.desc,
    features:     featuresRaw.value.split('\n').map(s => s.trim()).filter(Boolean),
    is_available: 1,
  }

  emit('save', payload)
  close()
}

function close() { emit('update:modelValue', false) }
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 200;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal {
  background: var(--white); border-radius: 24px;
  width: 100%; max-width: 600px;
  box-shadow: 0 24px 80px rgba(0,0,0,.20);
  display: flex; flex-direction: column; max-height: 90vh;
  position: relative;
}
.modal__close {
  position: absolute; top: 20px; right: 20px;
  width: 36px; height: 36px; border: none; border-radius: 50%;
  background: var(--gray-100); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; color: var(--gray-600); transition: background var(--transition);
  z-index: 1;
}
.modal__close:hover { background: var(--gray-200); }

.modal__title { font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700; padding: 32px 32px 4px; color: var(--indigo); }
.modal__sub   { font-size: .88rem; color: var(--gray-400); padding: 0 32px 20px; }

.modal__body  { padding: 0 32px; overflow-y: auto; flex: 1; }

/* Form */
.form-row    { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group  { margin-bottom: 18px; }
.form-label  { font-size: .84rem; font-weight: 600; color: var(--indigo); margin-bottom: 7px; display: block; }
.form-hint   { font-weight: 400; color: var(--gray-400); font-size: .78rem; }
.form-input  {
  width: 100%; padding: 11px 14px;
  border: 1.5px solid var(--gray-200); border-radius: 11px;
  font-family: 'DM Sans', sans-serif; font-size: .92rem; color: var(--indigo);
  outline: none; transition: border-color var(--transition); background: var(--white);
}
.form-input:focus { border-color: var(--coral); }
.form-select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;
  padding-right: 36px; appearance: none; cursor: pointer;
}
.form-textarea { resize: vertical; min-height: 80px; line-height: 1.5; }
.field-error { font-size: .76rem; color: #e74c3c; margin-top: 4px; }

.text-center { text-align: center; }

/* Date inputs */
.date-input-wrap { position: relative; }
.date-input-wrap .form-input { padding-right: 36px; cursor: pointer; }
.date-input-wrap input::-webkit-calendar-picker-indicator {
  position: absolute; right: 0; top: 0; width: 40px; height: 100%; opacity: 0; cursor: pointer;
}
.date-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; font-size: 1rem; }

/* Footer */
.modal__footer {
  display: flex; justify-content: flex-end; gap: 12px;
  padding: 20px 32px; border-top: 1px solid var(--gray-100);
}
.modal__submit { padding: 11px 28px; }
.modal__submit:disabled { opacity: .7; cursor: not-allowed; transform: none !important; }

.spinner {
  display: inline-block; width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.fade-enter-active, .fade-leave-active { transition: opacity .2s ease, transform .2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; transform: scale(.97); }

@media (max-width: 480px) {
  .modal { border-radius: 20px; }
  .modal__title { padding: 24px 24px 4px; font-size: 1.4rem; }
  .modal__sub   { padding: 0 24px 16px; }
  .modal__body  { padding: 0 24px; }
  .modal__footer{ padding: 16px 24px; }
  .form-row { grid-template-columns: 1fr; }
}
</style>