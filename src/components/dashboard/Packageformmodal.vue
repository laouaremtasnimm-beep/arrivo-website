<template>
  <Teleport to="body">
    <Transition name="fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal">

          <button class="modal__close" @click="close">✕</button>

          <h2 class="modal__title">{{ isEdit ? 'Edit Package' : 'Add New Package' }}</h2>
          <p class="modal__sub">{{ isEdit ? 'Update the details below.' : 'Fill in the details to list a new travel package.' }}</p>

          <div class="modal__body">

            <!-- Title -->
            <div class="form-group">
              <label class="form-label">Package title *</label>
              <input class="form-input" v-model="form.title" placeholder="e.g. Swiss Alps Winter Retreat" :disabled="isEdit" />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <!-- Destination + Type -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Destination *</label>
                <input class="form-input" v-model="form.destination" placeholder="e.g. Switzerland" :disabled="isEdit" />
                <p class="field-error" v-if="errors.destination">{{ errors.destination }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Type *</label>
                <select class="form-input" v-model="form.type">
                  <option value="">Select type…</option>
                  <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                </select>
                <p class="field-error" v-if="errors.type">{{ errors.type }}</p>
              </div>
            </div>

            <!-- Duration + Price -->
          <div class="form-row">
  <div class="form-group">
    <label class="form-label">Price per person ($) *</label>
    <input class="form-input" v-model.number="form.price" type="number" min="0" placeholder="e.g. 2490" :disabled="isEdit" />
    <p class="field-error" v-if="errors.price">{{ errors.price }}</p>
  </div>
  <div class="form-group">
    <label class="form-label">Spots available</label>
    <input class="form-input" v-model.number="form.spots" type="number" min="0" placeholder="e.g. 8" :disabled="isEdit" />
  </div>
</div>

<div class="form-row">
  <div class="form-group">
    <label class="form-label">Start date *</label>
    <div class="date-input-wrap">
      <input class="form-input" v-model="form.startDate" type="date" :disabled="isEdit" />
      <span class="date-icon">🗓️</span>
    </div>
    <p class="field-error" v-if="errors.startDate">{{ errors.startDate }}</p>
  </div>
  <div class="form-group">
    <label class="form-label">End date *</label>
    <div class="date-input-wrap">
      <input class="form-input" v-model="form.endDate" type="date" :disabled="isEdit" />
      <span class="date-icon">🗓️</span>
    </div>
    <p class="field-error" v-if="errors.endDate">{{ errors.endDate }}</p>
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

            <!-- Image URL -->
            <div class="form-group">
              <label class="form-label">Cover image URL</label>
              <input class="form-input" v-model="form.img" placeholder="https://…" />
              <div class="img-preview">
                <img :src="form.img || 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg'" alt="Preview" />
              </div>
            </div>

            <!-- Includes -->
            <div class="form-group">
              <label class="form-label">What's included <span class="form-hint">(one per line)</span></label>
              <textarea
                class="form-input form-textarea"
                v-model="includesRaw"
                placeholder="Ski pass&#10;Chalet accommodation&#10;Airport transfer"
                rows="4"
              />
            </div>

          </div>

          <div class="modal__footer">
            <button class="btn btn-outline" @click="close">Cancel</button>
            <button class="btn btn-coral modal__submit" @click="submit" :disabled="loading">
              <span class="spinner" v-if="loading" />
              <span v-else>{{ isEdit ? 'Save changes' : 'Add package' }}</span>
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
  package:    { type: Object,  default: null  }, // null = create mode
})

const emit = defineEmits(['update:modelValue', 'save'])

const isEdit  = computed(() => !!props.package)
const today = computed(() => new Date().toISOString().split('T')[0])
const loading = ref(false)
const errors  = ref({})

const types = ['Adventure', 'Beach', 'Cultural', 'Family', 'Wellness', 'City Break']

// ── Form state ─────────────────────────────────────────────────────────────
const defaultForm = () => ({
  title: '', destination: '', type: '',
  price: null, spots: null, startDate: '', endDate: '', desc: '', img: '',
  includes: [],
})
const form        = ref(defaultForm())
const includesRaw = ref('')

// Populate form when editing
watch(() => props.package, (pkg) => {
  if (pkg) {
   form.value = {
  id:          pkg.id,
  title:       pkg.title           || '',
  destination: pkg.destination     || '',
  type:        pkg.type            || '',
  price:       pkg.price           || null,
  spots:       pkg.spots_available || pkg.spots || null,
  startDate:   pkg.start_date      || '',
  endDate:     pkg.end_date        || '',
  desc:        pkg.description     || pkg.desc || '',
  img:         pkg.img_url         || pkg.img  || '',
}
    
    // Handle includes - might be JSON string or array
    let inc = []
    try {
      inc = typeof pkg.includes === 'string' ? JSON.parse(pkg.includes) : (pkg.includes || [])
    } catch { inc = [] }
    includesRaw.value = inc.join('\n')
  } else {
    form.value    = defaultForm()
    includesRaw.value = ''
  }
  errors.value = {}
}, { immediate: true })

// ── Validation ─────────────────────────────────────────────────────────────
function validate() {
  const e = {}
  if (!form.value.title?.trim())       e.title       = 'Title is required.'
  if (!form.value.destination?.trim()) e.destination = 'Destination is required.'
  if (!form.value.type)               e.type        = 'Please select a type.'

  if (!form.value.price || form.value.price < 0) e.price = 'Enter a valid price.'
  if (!form.value.startDate) e.startDate = 'Start date is required.'
  else if (form.value.startDate < today.value) e.startDate = 'Start date cannot be in the past.'
if (!form.value.endDate)   e.endDate   = 'End date is required.'
if (form.value.startDate && form.value.endDate && form.value.endDate < form.value.startDate)
  e.endDate = 'End date must be after start date.'
if (!form.value.desc?.trim()) e.desc = 'Description is required.'
  errors.value = e
  return Object.keys(e).length === 0
}

// ── Submit ─────────────────────────────────────────────────────────────────
async function submit() {
  if (!validate()) return
  loading.value = true
  // Small delay for UX
  await new Promise(r => setTimeout(r, 400))
  loading.value = false

 const payload = {
  id:              props.package?.id,
  title:           form.value.title,
  destination:     form.value.destination,
  type:            form.value.type,
  price:           form.value.price,
  spots_available: form.value.spots,
  start_date:      form.value.startDate || null,
  end_date:        form.value.endDate   || null,
  description:     form.value.desc,
  img_url:         form.value.img,
  includes:        includesRaw.value.split('\n').map(s => s.trim()).filter(Boolean),
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

.modal__title { font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700; padding: 32px 32px 4px; }
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
.form-input:disabled, .form-textarea:disabled {
  background-color: var(--gray-50);
  color: var(--gray-400);
  border-color: var(--gray-100);
  cursor: not-allowed;
  opacity: 0.8;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23a0aec0' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='11' width='18' height='11' rx='2' ry='2'%3E%3C/rect%3E%3Cpath d='M7 11V7a5 5 0 0 1 10 0v4'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 32px;
}
.form-textarea { resize: vertical; min-height: 80px; line-height: 1.5; }
.field-error { font-size: .76rem; color: #e74c3c; margin-top: 4px; }

/* Image preview */
.img-preview { margin-top: 10px; height: 120px; border-radius: 10px; overflow: hidden; }
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
  .modal { border-radius: 20px; }
  .modal__title { padding: 24px 24px 4px; font-size: 1.4rem; }
  .modal__sub   { padding: 0 24px 16px; }
  .modal__body  { padding: 0 24px; }
  .modal__footer{ padding: 16px 24px; }
  .form-row { grid-template-columns: 1fr; }
}
.date-input-wrap {
  position: relative;
}
.date-input-wrap .form-input {
  padding-right: 36px;
  cursor: pointer;
}
/* Hide native calendar icon but keep it clickable */
.date-input-wrap input::-webkit-calendar-picker-indicator {
  position: absolute;
  right: 0;
  top: 0;
  width: 40px;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}
.date-icon {
  position: absolute; right: 12px; top: 50%;
  transform: translateY(-50%); pointer-events: none; font-size: 1rem;
}
</style>