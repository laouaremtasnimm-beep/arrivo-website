<template>
  <Teleport to="body">
    <Transition name="fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal">

          <button class="modal__close" @click="close">✕</button>

          <h2 class="modal__title">{{ isEdit ? 'Edit Service' : 'Add New Service' }}</h2>
          <p class="modal__sub">{{ isEdit ? 'Update the details below.' : 'Fill in the details to list a new service.' }}</p>

          <div class="modal__body">

            <!-- Title -->
            <div class="form-group">
              <label class="form-label">Service title *</label>
              <input class="form-input" v-model="form.title" placeholder="e.g. Private Airport Transfer" />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <!-- Type + Icon -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Category *</label>
                <select class="form-input" v-model="form.type">
                  <option value="">Select category…</option>
                  <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                </select>
                <p class="field-error" v-if="errors.type">{{ errors.type }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Icon (emoji)</label>
                <input class="form-input" v-model="form.icon" placeholder="e.g. 🚐" maxlength="4" />
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
                <label class="form-label">Price unit *</label>
                <select class="form-input" v-model="form.unit">
                  <option value="day">Per day</option>
                  <option value="trip">Per trip</option>
                  <option value="person">Per person</option>
                  <option value="session">Per session</option>
                  <option value="evening">Per evening</option>
                  <option value="hour">Per hour</option>
                </select>
              </div>
            </div>

            <!-- Description -->
            <div class="form-group">
              <label class="form-label">Short description *</label>
              <textarea
                class="form-input form-textarea"
                v-model="form.desc"
                placeholder="A brief summary shown on listing cards…"
                rows="3"
              />
              <p class="field-error" v-if="errors.desc">{{ errors.desc }}</p>
            </div>

            <!-- Features -->
            <div class="form-group">
              <label class="form-label">Features / inclusions <span class="form-hint">(one per line)</span></label>
              <textarea
                class="form-input form-textarea"
                v-model="featuresRaw"
                placeholder="Meet & greet&#10;Flight tracking&#10;Free waiting time"
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
const loading = ref(false)
const errors  = ref({})

const types = ['Transport', 'Adventure', 'Food', 'Wellness', 'Photography', 'Tours', 'City Break']

const defaultForm = () => ({
  title: '', type: '', icon: '🛎️', price: null, unit: 'day',
  desc: '', availability: true, features: [],
})

const form        = ref(defaultForm())
const featuresRaw = ref('')

watch(() => props.service, (svc) => {
  if (svc) {
    form.value = {
      id:           svc.id,
      title:        svc.title        || '',
      type:         svc.type         || '',
      icon:         svc.icon         || '🛎️',
      price:        svc.price        || null,
      unit:         svc.price_unit   || svc.unit || 'day',
      desc:         svc.description  || svc.desc || '',
      availability: Number(svc.is_available) === 1,
    }
    
    // Handle features - might be JSON string or array
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

function validate() {
  const e = {}
  if (!form.value.title?.trim()) e.title = 'Title is required.'
  if (!form.value.type)         e.type  = 'Please select a category.'
  if (!form.value.price || form.value.price < 0) e.price = 'Enter a valid price.'
  if (!form.value.desc?.trim())  e.desc  = 'Description is required.'
  errors.value = e
  return Object.keys(e).length === 0
}

async function submit() {
  if (!validate()) return
  loading.value = true
  await new Promise(r => setTimeout(r, 400))
  loading.value = false

  const payload = {
    id:           props.service?.id,
    title:        form.value.title,
    type:         form.value.type,
    icon:         form.value.icon,
    price:        form.value.price,
    price_unit:   form.value.unit,
    description:  form.value.desc,
    is_available: form.value.availability ? 1 : 0,
    features:     featuresRaw.value.split('\n').map(s => s.trim()).filter(Boolean),
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
  width: 100%; max-width: 580px;
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
.modal__title { font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700; padding: 32px 32px 4px; }
.modal__sub   { font-size: .88rem; color: var(--gray-400); padding: 0 32px 20px; }
.modal__body  { padding: 0 32px; overflow-y: auto; flex: 1; }

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
.form-textarea { resize: vertical; min-height: 80px; line-height: 1.5; }
.field-error { font-size: .76rem; color: #e74c3c; margin-top: 4px; }

.img-preview { margin-top: 10px; height: 100px; border-radius: 10px; overflow: hidden; }
.img-preview img { width: 100%; height: 100%; object-fit: cover; }

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
  .modal__title { padding: 24px 24px 4px; font-size: 1.4rem; }
  .modal__sub   { padding: 0 24px 16px; }
  .modal__body  { padding: 0 24px; }
  .modal__footer{ padding: 16px 24px; }
  .form-row { grid-template-columns: 1fr; }
}
</style>