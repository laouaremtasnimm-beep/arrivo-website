<template>
  <Teleport to="body">
    <Transition name="wizard-fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal" :class="['step-' + step]">

          <!-- ── Progress bar ── -->
          <div class="wizard-progress">
            <div class="wizard-progress__fill" :style="{ width: progressWidth }"></div>
          </div>

          <!-- ── Header ── -->
          <div class="modal__header">
            <div class="header-left">
              <div class="step-badge">Step {{ step }} of 2</div>
              <h2 class="modal__title">{{ stepTitle }}</h2>
              <p class="step-sub">{{ stepSub }}</p>
            </div>
            <button class="modal__close" @click="close">✕</button>
          </div>

          <!-- ══════════════════════════════════════════
               STEP 1 — Pick a service
          ══════════════════════════════════════════ -->
          <div class="modal__body" v-if="step === 1">

            <!-- Info notice — no create-new for services -->
            <div class="info-notice">
              <span>💡</span>
              <span>
                Select one of your existing services to apply a discount offer.
                To add a new service first, close this and use the
                <strong>Services</strong> tab.
              </span>
            </div>

            <!-- Dropdown -->
            <div class="existing-pkgs-wrap">
              <label class="form-label">Choose a service</label>
              <div class="dropdown-container">
                <button
                  class="dropdown-trigger"
                  :class="{ open: dropdownOpen }"
                  type="button"
                  @click="dropdownOpen = !dropdownOpen"
                >
                  <div class="dropdown-trigger__content">
                    <span class="trigger-icon">🛎️</span>
                    <div class="trigger-text">
                      <div class="trigger-title" v-if="selectedService">
                        {{ selectedService.title }}
                      </div>
                      <div class="trigger-placeholder" v-else>Select from your services…</div>
                    </div>
                  </div>
                  <span class="dropdown-arrow">{{ dropdownOpen ? '▴' : '▾' }}</span>
                </button>

                <Transition name="dropdown-slide">
                  <div class="dropdown-menu" v-if="dropdownOpen">
                    <div v-if="loadingServices" class="loading-row">
                      <span class="spinner"></span> Loading services…
                    </div>
                    <div v-else-if="providerServices.length === 0" class="empty-state">
                      <p>No services found. Create one in the Services tab first.</p>
                    </div>
                    <div v-else class="dropdown-list">
                      <div
                        v-for="svc in providerServices"
                        :key="svc.id"
                        class="dropdown-item"
                        :class="{ selected: form.selectedServiceId === svc.id }"
                        @click="selectService(svc)"
                      >
                        <div class="item-img">
                          <img v-if="svc.img_url" :src="svc.img_url" />
                          <div v-else class="item-img--fallback">🛎️</div>
                        </div>
                        <div class="item-info">
                          <div class="item-name">{{ svc.title }}</div>
                          <div class="item-meta">
                            {{ svc.type }} · ${{ Number(svc.price).toLocaleString() }}/{{ svc.price_unit || 'day' }}
                          </div>
                        </div>
                        <div class="item-check" v-if="form.selectedServiceId === svc.id">✓</div>
                      </div>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>

            <p class="field-error" v-if="errors.service">{{ errors.service }}</p>
          </div>

          <!-- ══════════════════════════════════════════
               STEP 2 — Offer details
          ══════════════════════════════════════════ -->
          <div class="modal__body" v-else-if="step === 2">

            <!-- Selected service pill -->
            <div class="selected-summary" v-if="selectedService">
              <span class="summary-pill">🛎️ {{ selectedService.title }}</span>
              <span class="summary-pill summary-pill--meta">
                ${{ Number(selectedService.price).toLocaleString() }}/{{ selectedService.price_unit || 'day' }}
              </span>
            </div>

            <div class="form-group">
              <label class="form-label">Offer title *</label>
              <input
                class="form-input"
                v-model="form.title"
                placeholder="e.g. Summer Mountain Guide Deal"
              />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Discount (%) *</label>
                <div class="discount-input-wrap">
                  <input
                    type="number" min="1" max="80"
                    class="form-input"
                    v-model.number="form.discount"
                    placeholder="e.g. 15"
                    :disabled="isEdit && hasBookings"
                  />
                  <span class="discount-suffix">% off</span>
                </div>
                <p class="lock-notice" v-if="isEdit && hasBookings">
                  🔒 Locked — this offer has active bookings.
                </p>
                <p class="field-error" v-if="errors.discount">{{ errors.discount }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Category</label>
                <select class="form-input form-select" v-model="form.type">
                  <option value="General">✨ General</option>
                  <option value="Adventure">🧗 Adventure</option>
                  <option value="Beach">🏖️ Beach</option>
                  <option value="Cultural">🏛️ Cultural</option>
                  <option value="Family">👨‍👩‍👧 Family</option>
                  <option value="Wellness">🧘 Wellness</option>
                  <option value="Flash Sale">⚡ Flash Sale</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date *</label>
                <div class="date-wrap">
                  <input
                    type="date" class="form-input"
                    v-model="form.startDate"
                    :min="today"
                    :disabled="isEdit && offerIsLive"
                  />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="lock-notice" v-if="isEdit && offerIsLive">🔒 Offer is live — start date locked.</p>
                <p class="field-error" v-if="errors.startDate">{{ errors.startDate }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">End Date *</label>
                <div class="date-wrap">
                  <input
                    type="date" class="form-input"
                    v-model="form.endDate"
                    :min="minEndDate"
                  />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.endDate">{{ errors.endDate }}</p>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Description *</label>
              <textarea
                class="form-input form-textarea"
                v-model="form.description"
                placeholder="Describe what makes this offer special…"
              />
              <p class="field-error" v-if="errors.description">{{ errors.description }}</p>
            </div>

            <!-- Live discount preview -->
            <div class="discount-preview" v-if="form.discount && selectedService">
              <div class="discount-preview__label">Price preview with discount</div>
              <div class="discount-preview__items">
                <div class="discount-preview__row">
                  <span class="dp-name">{{ selectedService.title }}</span>
                  <span class="dp-prices">
                    <s class="dp-old">${{ Number(selectedService.price).toLocaleString() }}</s>
                    <strong class="dp-new">${{ discountedPrice(selectedService.price) }}</strong>
                    <span class="dp-unit">/{{ selectedService.price_unit || 'day' }}</span>
                    <span class="dp-badge">-{{ form.discount }}%</span>
                  </span>
                </div>
              </div>
            </div>

          </div>

          <!-- ── Footer ── -->
          <div class="modal__footer">
            <button class="btn btn-outline" @click="back">
              {{ step === 1 || (isEdit && step === 2) ? 'Cancel' : '← Back' }}
            </button>
            <button class="btn btn-coral" @click="next" :disabled="submitting">
              <span v-if="submitting" class="btn-spinner"></span>
              {{ step === 1 ? 'Continue →' : (isEdit ? 'Save Changes' : 'Create Offer') }}
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
  modelValue:  Boolean,
  offer:       { type: Object,           default: null  },
  providerId:  { type: [Number, String], default: null  },
  hasBookings: { type: Boolean,          default: false },
})
const emit = defineEmits(['update:modelValue', 'save'])

const isEdit = computed(() => !!props.offer)

/* ── State ───────────────────────────────────── */
const step        = ref(1)
const submitting  = ref(false)
const errors      = ref({})
const dropdownOpen = ref(false)

const blankForm = () => ({
  title:             '',
  discount:          null,
  startDate:         '',
  endDate:           '',
  description:       '',
  type:              'General',
  selectedServiceId: null,
})
const form = ref(blankForm())

/* ── Services list ────────────────────────────── */
const providerServices = ref([])
const loadingServices  = ref(false)

const selectedService = computed(() =>
  providerServices.value.find(s => s.id === form.value.selectedServiceId) || null
)

async function fetchServices() {
  if (!props.providerId) return
  loadingServices.value = true
  try {
    const res  = await fetch(`/arrivo-website/backend/api/v1/Services.php?provider_id=${props.providerId}`)
    const data = await res.json()
    providerServices.value = (data.services || []).filter(s => s.is_available != 0)
  } catch (e) {
    console.error('Failed to load services', e)
  } finally {
    loadingServices.value = false
  }
}

function selectService(svc) {
  form.value.selectedServiceId = svc.id
  dropdownOpen.value = false
  if (!form.value.title) form.value.title = `${svc.title} — Special Offer`
}

/* ── Computed ─────────────────────────────────── */
const today = computed(() => new Date().toISOString().split('T')[0])

// Offer is "live" if start_date <= today
const offerIsLive = computed(() => {
  if (!props.offer?.start_date && !props.offer?.startDate) return false
  const start = props.offer.start_date ?? props.offer.startDate
  return start <= today.value
})

// End date minimum: if editing, can only extend (never shorten)
const minEndDate = computed(() => {
  if (isEdit.value && props.offer) {
    const current = props.offer.end_date ?? props.offer.endDate ?? ''
    return current > today.value ? current : today.value
  }
  return form.value.startDate || today.value
})

const progressWidth = computed(() => `${(step.value / 2) * 100}%`)

const stepTitle = computed(() =>
  step.value === 1
    ? (isEdit.value ? 'Edit Offer — Service' : 'Choose a Service')
    : (isEdit.value ? 'Edit Offer Details'   : 'Offer Details')
)
const stepSub = computed(() =>
  step.value === 1
    ? 'Pick the service you want to put on offer.'
    : 'Set the discount, dates and description.'
)

function discountedPrice(price) {
  const d = form.value.discount || 0
  return (Number(price) * (1 - d / 100))
    .toFixed(0)
    .replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

/* ── Watchers ────────────────────────────────── */
watch(
  () => [props.modelValue, props.offer],
  ([open, offer]) => {
    if (open) {
      step.value    = offer ? 2 : 1
      errors.value  = {}
      dropdownOpen.value = false
      form.value    = offer
        ? {
            title:             offer.title        ?? '',
            discount:          offer.discount_pct ?? offer.discount ?? null,
            startDate:         offer.start_date   ?? offer.startDate ?? '',
            endDate:           offer.end_date      ?? offer.endDate   ?? '',
            description:       offer.description  ?? '',
            type:              offer.type         ?? 'General',
            selectedServiceId: offer.serviceId    ?? offer.service_id ?? null,
          }
        : blankForm()
      fetchServices()
    }
  }
)

watch(() => props.providerId, (id) => {
  if (id && props.modelValue) fetchServices()
})

/* ── Validation ──────────────────────────────── */
function validateStep() {
  const e = {}

  if (step.value === 1) {
    if (!form.value.selectedServiceId)
      e.service = 'Please select a service.'
  }

  if (step.value === 2) {
    if (!form.value.title?.trim())
      e.title = 'Title is required.'
    if (!form.value.discount || form.value.discount < 1 || form.value.discount > 80)
      e.discount = 'Enter a valid discount (1–80).'
    if (!form.value.startDate)
      e.startDate = 'Start date is required.'
    if (!form.value.endDate)
      e.endDate = 'End date is required.'
    if (form.value.startDate && form.value.endDate && form.value.endDate < form.value.startDate)
      e.endDate = 'End date must be after start date.'
    // Edit guard: end date can only be extended
    if (isEdit.value && props.offer) {
      const originalEnd = props.offer.end_date ?? props.offer.endDate ?? ''
      if (originalEnd && form.value.endDate < originalEnd)
        e.endDate = 'End date can only be extended, not shortened.'
    }
    if (!form.value.description?.trim())
      e.description = 'Description is required.'
  }

  errors.value = e
  return !Object.keys(e).length
}

/* ── Navigation ──────────────────────────────── */
function next() {
  if (!validateStep()) return
  if (step.value === 2) { submit(); return }
  step.value++
}

function back() {
  if (step.value === 1 || (isEdit.value && step.value === 2)) { close(); return }
  step.value--
  errors.value = {}
}

/* ── Submit ──────────────────────────────────── */
async function submit() {
  submitting.value = true
  try {
    emit('save', {
      title:       form.value.title,
      discount:    form.value.discount,
      startDate:   form.value.startDate,
      endDate:     form.value.endDate,
      description: form.value.description,
      type:        form.value.type,
      offerType:   'service',
      serviceId:   form.value.selectedServiceId,
      packageIds:  [],
      source:      'manual',
      offerID:     props.offer?.offerID ?? props.offer?.id ?? null,
      is_active:   1,
    })
    close()
  } finally {
    submitting.value = false
  }
}

function close() {
  emit('update:modelValue', false)
  setTimeout(() => {
    step.value         = 1
    form.value         = blankForm()
    errors.value       = {}
    dropdownOpen.value = false
  }, 300)
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(45,49,66,.48);
  display: flex; align-items: center; justify-content: center;
  z-index: 200; padding: 20px;
}
.modal {
  background: #fff; border-radius: 22px; width: 100%; max-width: 500px;
  box-shadow: 0 20px 72px rgba(45,49,66,.22);
  max-height: 92vh; display: flex; flex-direction: column;
  position: relative;
}

.wizard-progress { height: 4px; background: var(--gray-100); flex-shrink: 0; border-radius: 22px 22px 0 0; overflow: hidden; }
.wizard-progress__fill {
  height: 100%; background: linear-gradient(90deg, var(--teal), var(--coral));
  transition: width .4s cubic-bezier(.4,0,.2,1);
}

.modal__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 20px 26px 14px; border-bottom: 1px solid var(--gray-100); flex-shrink: 0;
}
.step-badge {
  font-size: .7rem; font-weight: 700; letter-spacing: .07em;
  color: var(--teal); text-transform: uppercase; margin-bottom: 3px;
}
.modal__title {
  font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700;
  color: var(--indigo); margin: 0 0 3px;
}
.step-sub { font-size: .8rem; color: var(--gray-600); margin: 0; line-height: 1.4; }
.modal__close {
  background: var(--gray-100); border: none; border-radius: 50%;
  width: 32px; height: 32px; cursor: pointer; font-size: .85rem;
  color: var(--gray-600); transition: background var(--transition); flex-shrink: 0;
}
.modal__close:hover { background: var(--gray-200); }

.modal__body {
  padding: 18px 26px; overflow-y: auto; flex: 1;
  display: flex; flex-direction: column; gap: 14px;
  position: relative;
}
.modal.step-1 .modal__body { overflow: visible; }

.modal__footer {
  padding: 14px 26px; border-top: 1px solid var(--gray-100);
  display: flex; justify-content: space-between; align-items: center;
  gap: 10px; flex-shrink: 0;
}

.btn {
  padding: 10px 22px; border-radius: 50px; font-size: .88rem; font-weight: 700;
  cursor: pointer; font-family: 'DM Sans', sans-serif; border: none;
  transition: all var(--transition); display: flex; align-items: center; gap: 6px;
}
.btn:disabled { opacity: .55; cursor: not-allowed; }
.btn-coral { background: var(--coral); color: #fff; }
.btn-coral:hover:not(:disabled) { background: var(--coral-dk); }
.btn-outline { background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-600); }
.btn-outline:hover { border-color: var(--gray-400); color: var(--indigo); }
.btn-spinner {
  width: 13px; height: 13px; border: 2px solid rgba(255,255,255,.4);
  border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Info notice */
.info-notice {
  display: flex; align-items: flex-start; gap: 8px;
  background: #f0fafa; border: 1px solid var(--teal);
  border-radius: 10px; padding: 10px 14px;
  font-size: .8rem; color: var(--indigo); line-height: 1.5;
}
.info-notice strong { font-weight: 700; }

/* Dropdown */
.existing-pkgs-wrap { display: flex; flex-direction: column; gap: 6px; }
.dropdown-container { position: relative; }
.dropdown-trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  padding: 10px 16px; border-radius: 12px;
  border: 1.5px solid var(--gray-200); background: #fff;
  cursor: pointer; transition: all .2s ease; min-height: 52px;
}
.dropdown-trigger:hover { border-color: var(--teal); background: var(--teal-lt, #f0fafa); }
.dropdown-trigger.open  { border-color: var(--teal); box-shadow: 0 0 0 4px rgba(46,196,182,.1); }
.dropdown-trigger__content { display: flex; align-items: center; gap: 12px; text-align: left; }
.trigger-icon { font-size: 1.2rem; }
.trigger-placeholder { color: var(--gray-400); font-size: .9rem; }
.trigger-title { font-size: .92rem; font-weight: 600; color: var(--indigo); }
.dropdown-arrow { font-size: 1.1rem; color: var(--teal); }

.dropdown-menu {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0;
  background: #fff; border-radius: 14px;
  border: 1px solid var(--gray-200);
  box-shadow: 0 12px 48px rgba(45,49,66,.2);
  z-index: 1000; max-height: 200px; overflow-y: auto; padding: 8px;
}
.dropdown-list { display: flex; flex-direction: column; gap: 4px; }
.dropdown-item {
  display: flex; align-items: center; gap: 12px; padding: 10px;
  border-radius: 10px; cursor: pointer; transition: background .15s;
}
.dropdown-item:hover    { background: var(--gray-50); }
.dropdown-item.selected { background: var(--teal-lt, #f0fafa); }
.item-img { width: 44px; height: 36px; border-radius: 6px; overflow: hidden; flex-shrink: 0; }
.item-img img { width: 100%; height: 100%; object-fit: cover; }
.item-img--fallback {
  background: var(--gray-100); display: flex; align-items: center;
  justify-content: center; font-size: 1rem; height: 100%; width: 100%;
}
.item-info { flex: 1; min-width: 0; }
.item-name { font-size: .88rem; font-weight: 600; color: var(--indigo); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-meta { font-size: .76rem; color: var(--gray-500); margin-top: 2px; }
.item-check { color: var(--teal); font-weight: 900; font-size: 1rem; }

.loading-row {
  display: flex; align-items: center; gap: 10px;
  color: var(--gray-600); font-size: .88rem; padding: 8px 4px;
}
.spinner {
  width: 15px; height: 15px; border: 2px solid var(--gray-200);
  border-top-color: var(--teal); border-radius: 50%;
  animation: spin .7s linear infinite; flex-shrink: 0;
}
.empty-state {
  padding: 12px 4px; color: var(--gray-500); font-size: .85rem;
}

/* Step 2 */
.selected-summary { display: flex; flex-wrap: wrap; gap: 6px; }
.summary-pill {
  background: var(--teal-lt, #f0fafa); border: 1px solid var(--teal);
  border-radius: 20px; padding: 4px 12px;
  font-size: .76rem; color: var(--indigo); font-weight: 600;
}
.summary-pill--meta {
  background: var(--gray-50, #f9f9fc); border-color: var(--gray-300); color: var(--gray-600);
}

.form-group  { display: flex; flex-direction: column; gap: 5px; }
.form-label  { font-size: .82rem; font-weight: 600; color: var(--indigo); }
.form-row    { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-input  {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 10px 13px; font-size: .9rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); outline: none; background: #fff;
  transition: border-color var(--transition);
}
.form-input:focus { border-color: var(--coral); }
.form-input:disabled {
  background: var(--gray-50); color: var(--gray-400);
  border-color: var(--gray-100); cursor: not-allowed;
}
.form-textarea { resize: vertical; min-height: 86px; }
.form-select   { cursor: pointer; }
.field-error   { font-size: .78rem; color: var(--coral); margin: 0; }
.lock-notice   { font-size: .76rem; color: var(--gray-500); margin: 0; }

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

.discount-preview {
  border-radius: 12px; border: 1.5px solid var(--gray-200); overflow: hidden;
}
.discount-preview__label {
  background: var(--gray-50, #f9f9fc); padding: 8px 14px;
  font-size: .72rem; font-weight: 700; color: var(--gray-600);
  text-transform: uppercase; letter-spacing: .05em;
  border-bottom: 1px solid var(--gray-200);
}
.discount-preview__items { padding: 10px 14px; }
.discount-preview__row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.dp-name   { font-size: .85rem; color: var(--indigo); font-weight: 500; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-prices { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.dp-old    { color: var(--gray-400); font-size: .82rem; }
.dp-new    { color: var(--coral); font-size: .95rem; }
.dp-unit   { font-size: .76rem; color: var(--gray-500); }
.dp-badge  {
  background: var(--coral); color: #fff; font-size: .68rem;
  font-weight: 800; padding: 2px 7px; border-radius: 20px;
}

.dropdown-slide-enter-active, .dropdown-slide-leave-active { transition: all .2s ease; }
.dropdown-slide-enter-from, .dropdown-slide-leave-to { opacity: 0; transform: translateY(-8px); }

.wizard-fade-enter-active, .wizard-fade-leave-active { transition: all .22s ease; }
.wizard-fade-enter-from, .wizard-fade-leave-to { opacity: 0; transform: scale(.97); }
</style>