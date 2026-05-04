<template>
  <Teleport to="body">
    <Transition name="wizard-fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal" :class="['step-' + step]">
          <div class="wizard-progress"><div class="wizard-progress__fill" :style="{ width: progressWidth }"></div></div>
          <div class="modal__header">
            <div class="header-left">
              <div class="step-badge" v-if="!isEdit">Step {{ step }} of 2</div>
              <div class="step-badge" v-else>Edit Offer</div>
              <h2 class="modal__title">{{ stepTitle }}</h2>
              <p class="step-sub">{{ stepSub }}</p>
            </div>
            <button class="modal__close" @click="close">X</button>
          </div>
          <div class="modal__body" v-if="step === 1">
            <button class="new-svc-card" @click="openCreateService">
              <div class="new-svc-card__icon">+</div>
              <div class="new-svc-card__text">
                <div class="new-svc-card__title">Create a new service</div>
                <div class="new-svc-card__sub">exclusive to this offer</div>
              </div>
              <span class="new-svc-card__arrow">→</span>
            </button>
            <div class="existing-svcs-wrap">
              <label class="form-label">Or choose existing</label>
              <div class="dropdown-container">
                <button class="dropdown-trigger" :class="{ open: dropdownOpen }" type="button" @click="dropdownOpen = !dropdownOpen">
                  <div class="dropdown-trigger__content">
                    <span class="trigger-icon">&#x1F6CE;&#xFE0F;</span>
                    <div class="trigger-text">
                      <div class="trigger-title" v-if="selectedService">{{ selectedService.title }}</div>
                      <div class="trigger-placeholder" v-else>Select from your services...</div>
                    </div>
                  </div>
                  <span class="dropdown-arrow">{{ dropdownOpen ? '&#9652;' : '&#9662;' }}</span>
                </button>
                <Transition name="dropdown-slide">
                  <div class="dropdown-menu" v-if="dropdownOpen">
                    <div v-if="loadingServices" class="loading-row"><span class="spinner"></span> Loading...</div>
                    <div v-else-if="providerServices.length === 0" class="empty-state"><p>No services found.</p></div>
                    <div v-else class="dropdown-list">
                      <div v-for="svc in providerServices" :key="svc.id" class="dropdown-item" :class="{ selected: form.selectedServiceId === svc.id }" @click="selectService(svc)">
                        <div class="item-img"><div class="item-img--fallback">{{ svc.icon || '&#x1F6CE;&#xFE0F;' }}</div></div>
                        <div class="item-info">
                          <div class="item-name">{{ svc.title }}</div>
                          <div class="item-meta">{{ svc.type }} ${{ Number(svc.price).toLocaleString() }}/{{ svc.price_unit || 'day' }}</div>
                        </div>
                        <div class="item-check" v-if="form.selectedServiceId === svc.id">&#10003;</div>
                      </div>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>
            <p class="field-error" v-if="errors.service">{{ errors.service }}</p>
          </div>
          <div class="modal__body" v-else-if="step === 2">
            <!-- Normal Offer Creation Step 2 -->
            <template v-if="!isEdit">
              <div class="selected-summary" v-if="selectedService">
                <span class="summary-pill">&#x1F6CE;&#xFE0F; {{ selectedService.title }}</span>
                <span class="summary-pill summary-pill--meta" v-if="selectedService.price">${{ Number(selectedService.price).toLocaleString() }}/{{ selectedService.price_unit || 'day' }}</span>
              </div>
              <div class="form-group">
                <label class="form-label">Discount (%) *</label>
                <div class="discount-input-wrap">
                  <input type="number" min="1" max="99" class="form-input" v-model.number="form.discount" placeholder="e.g. 20" />
                  <span class="discount-suffix">% off</span>
                </div>
                <p class="field-error" v-if="errors.discount">{{ errors.discount }}</p>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Start Date *</label>
                  <div class="date-wrap">
                    <input type="date" class="form-input" v-model="form.startDate" :min="today" />
                    <span class="date-icon">&#x1F5D3;&#xFE0F;</span>
                  </div>
                  <p class="field-error" v-if="errors.startDate">{{ errors.startDate }}</p>
                </div>
                <div class="form-group">
                  <label class="form-label">End Date *</label>
                  <div class="date-wrap">
                    <input type="date" class="form-input" v-model="form.endDate" :min="minEndDate" :max="maxOfferEndDate" />
                    <span class="date-icon">&#x1F5D3;&#xFE0F;</span>
                  </div>
                  <p class="field-error" v-if="errors.endDate">{{ errors.endDate }}</p>
                </div>
              </div>
              <div class="discount-preview" v-if="form.discount && selectedService">
                <div class="discount-preview__label">Price preview with discount</div>
                <div class="discount-preview__items">
                  <div class="discount-preview__row">
                    <span class="dp-name">{{ selectedService.title }}</span>
                    <span class="dp-prices">
                      <s class="dp-old">${{ Number(selectedService.price).toLocaleString() }}</s>
                      <strong class="dp-new">${{ discountedPrice(selectedService.price) }}</strong>
                    </span>
                  </div>
                </div>
              </div>
            </template>

            <!-- 1x1 Service-style Edit UI -->
            <template v-else>
              <!-- Title — locked -->
              <div class="form-group">
                <label class="form-label">Service title *</label>
                <div class="input-wrap">
                  <input class="form-input input--locked" v-model="form.title" disabled />
                  <svg class="lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                </div>
              </div>

              <!-- Category + Icon — editable -->
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Category *</label>
                  <select class="form-input form-select" v-model="form.type">
                    <option value="">Select type...</option>
                    <option v-for="t in serviceTypes" :key="t" :value="t">{{ t }}</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Icon (Emoji)</label>
                  <input class="form-input text-center" v-model="form.icon" maxlength="4" placeholder="🛎️" />
                </div>
              </div>

              <!-- Price + Unit — locked -->
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Price ($) *</label>
                  <div class="input-wrap">
                    <input class="form-input input--locked" v-model.number="form.price" type="number" disabled />
                    <svg class="lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Price unit</label>
                  <div class="input-wrap">
                    <select class="form-input form-select input--locked" v-model="form.unit" disabled>
                      <option value="day">Per day</option>
                      <option value="trip">Per trip</option>
                      <option value="person">Per person</option>
                      <option value="hour">Per hour</option>
                    </select>
                    <svg class="lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Dates — locked -->
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Available from *</label>
                  <div class="input-wrap">
                    <input class="form-input input--locked" v-model="form.startDate" type="date" disabled />
                    <svg class="lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Available until *</label>
                  <div class="input-wrap">
                    <input class="form-input input--locked" v-model="form.endDate" type="date" disabled />
                    <svg class="lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Description — editable -->
              <div class="form-group">
                <label class="form-label">Short description *</label>
                <textarea class="form-input form-textarea" v-model="form.description" placeholder="A brief summary..." rows="3" />
              </div>

              <!-- Features — editable -->
              <div class="form-group">
                <label class="form-label">Features / Inclusions <span class="form-hint">(one per line)</span></label>
                <textarea class="form-input form-textarea" v-model="form.featuresRaw" placeholder="One feature per line..." rows="4" />
              </div>
            </template>
          </div>
          <div class="modal__footer">
            <button class="btn btn-outline" @click="back">{{ (step === 1 || (isEdit && step === 2)) ? 'Cancel' : '← Back' }}</button>
            <button class="btn btn-coral" @click="next" :disabled="submitting">
              <span v-if="submitting" class="btn-spinner"></span>
              {{ step === 1 ? 'Continue →' : (isEdit ? 'Save Changes' : 'Create Offer') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
    <Transition name="wizard-fade">
      <div class="modal-backdrop modal-backdrop--top" v-if="showNewServiceForm" @click.self="showNewServiceForm = false">
        <div class="modal modal--package">
          <div class="wizard-progress"><div class="wizard-progress__fill" :style="{ width: subProgressWidth }"></div></div>
          <div class="modal__header">
            <div class="header-left">
              <div class="step-badge">New Service - Step {{ subStep }} of 2</div>
              <h2 class="modal__title">{{ subStepTitle }}</h2>
              <p class="step-sub">{{ subStepSub }}</p>
            </div>
            <button class="modal__close" @click="showNewServiceForm = false">X</button>
          </div>
          <div class="modal__body" v-if="subStep === 1">
            <div class="form-group">
              <label class="form-label">Service title *</label>
              <input class="form-input" v-model="newService.title" placeholder="e.g. Private Airport Transfer" />
              <p class="field-error" v-if="subErrors.title">{{ subErrors.title }}</p>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Category *</label>
                <select class="form-input form-select" v-model="newService.type">
                  <option value="">Select type...</option>
                  <option v-for="t in serviceTypes" :key="t" :value="t">{{ t }}</option>
                </select>
                <p class="field-error" v-if="subErrors.type">{{ subErrors.type }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Icon (emoji)</label>
                <input class="form-input text-center" v-model="newService.icon" placeholder="e.g. 🚐" maxlength="4" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Price ($) *</label>
                <input type="number" min="0" class="form-input" v-model.number="newService.price" placeholder="e.g. 45" />
                <p class="field-error" v-if="subErrors.price">{{ subErrors.price }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Price unit</label>
                <select class="form-input form-select" v-model="newService.unit">
                  <option value="day">Per day</option>
                  <option value="trip">Per trip</option>
                  <option value="person">Per person</option>
                  <option value="session">Per session</option>
                  <option value="hour">Per hour</option>
                </select>
              </div>
            </div>
            <!-- Service dates -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date *</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="newService.startDate" :min="today" />
                  <span class="date-icon">&#x1F5D3;&#xFE0F;</span>
                </div>
                <p class="field-error" v-if="subErrors.startDate">{{ subErrors.startDate }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">End Date *</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="newService.endDate" :min="newService.startDate || today" />
                  <span class="date-icon">&#x1F5D3;&#xFE0F;</span>
                </div>
                <p class="field-error" v-if="subErrors.endDate">{{ subErrors.endDate }}</p>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Description *</label>
              <textarea class="form-input form-textarea" v-model="newService.desc" placeholder="Describe this service..." />
              <p class="field-error" v-if="subErrors.desc">{{ subErrors.desc }}</p>
            </div>

            <div class="form-group">
              <label class="form-label">Features (one per line)</label>
              <textarea class="form-input form-textarea" v-model="newService.featuresRaw" placeholder="Meet and greet&#10;Free waiting time" rows="3" />
            </div>
          </div>
          <div class="modal__body" v-else-if="subStep === 2">
            <div class="selected-summary">
              <span class="summary-pill">{{ newService.icon || '&#x1F6CE;&#xFE0F;' }} {{ newService.title }}</span>
              <span class="summary-pill summary-pill--meta" v-if="newService.price">${{ Number(newService.price).toLocaleString() }}/{{ newService.unit }}</span>
            </div>
            <div class="form-group">
              <label class="form-label">Discount (%) *</label>
              <div class="discount-input-wrap">
                <input type="number" min="1" max="99" class="form-input" v-model.number="newService.discount" placeholder="e.g. 20" />
                <span class="discount-suffix">% off</span>
              </div>
              <p class="field-error" v-if="subErrors.discount">{{ subErrors.discount }}</p>
            </div>

            <div class="discount-preview" v-if="newService.discount && newService.price">
              <div class="discount-preview__label">Price preview</div>
              <div class="discount-preview__items">
                <div class="discount-preview__row">
                  <span class="dp-name">{{ newService.title }}</span>
                  <span class="dp-prices">
                    <s class="dp-old">${{ Number(newService.price).toLocaleString() }}</s>
                    <strong class="dp-new">${{ subDiscountedPrice }}</strong>
                    <span class="dp-badge">-{{ newService.discount }}%</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="modal__footer">
            <button class="btn btn-outline" @click="subBack">{{ subStep === 1 ? 'Cancel' : '← Back' }}</button>
            <div class="footer-right">
              <span class="step-dots">
                <span class="dot" :class="{ active: subStep === 1, done: subStep > 1 }"></span>
                <span class="dot" :class="{ active: subStep === 2 }"></span>
              </span>
              <button class="btn btn-coral" @click="subNext" :disabled="savingService">
                <span v-if="savingService" class="btn-spinner"></span>
                {{ subStep === 1 ? 'Next →' : 'Save & Add to Offer' }}
              </button>
            </div>
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
const providerServices = ref([])
const loadingServices  = ref(false)

const blankForm = () => ({
  title:             '',
  discount:          null,
  startDate:         '',
  endDate:           '',
  description:       '',
  type:              'General',
  selectedServiceId: null,
  isFromScratch:     false,
  newServiceData:    null,
  // New fields for 1x1 parity with service edit
  icon:              '🛎️',
  price:             null,
  unit:              'day',
  featuresRaw:       '',
})
const form = ref(blankForm())

/* ── New Service Form State ── */
const showNewServiceForm = ref(false)
const subStep           = ref(1)
const subErrors         = ref({})
const serviceTypes      = ['Transport', 'Adventure', 'Food', 'Wellness', 'Photography', 'Tours', 'City Break']
const savingService = ref(false)
const newService    = ref({
  title: '', type: '', icon: '🛎️', price: null, unit: 'day',
  desc: '', featuresRaw: '', availability: true,
  discount: null, startDate: '', endDate: ''
})

function openCreateService() {
  newService.value = {
    title: '', type: '', icon: '🛎️', price: null, unit: 'day',
    desc: '', featuresRaw: '', availability: true,
    discount: null, startDate: '', endDate: ''
  }
  subErrors.value = {}
  subStep.value = 1
  showNewServiceForm.value = true
}

function validateSubStep1() {
  const e = {}
  if (!newService.value.title?.trim()) e.title = 'Title is required.'
  if (!newService.value.type)          e.type  = 'Please select a category.'
  if (!newService.value.price || newService.value.price < 0) e.price = 'Enter a valid price.'
  if (!newService.value.desc?.trim())   e.desc  = 'Description is required.'
  if (!newService.value.startDate)      e.startDate = 'Start date is required.'
  if (!newService.value.endDate)        e.endDate   = 'End date is required.'
  if (newService.value.startDate && newService.value.endDate && newService.value.endDate < newService.value.startDate)
                                        e.endDate   = 'End date must be after start date.'
  subErrors.value = e
  return !Object.keys(e).length
}

function confirmNewService() {
  form.value.isFromScratch  = true
  form.value.newServiceData = { 
    ...newService.value, 
    is_available: newService.value.availability ? 1 : 0,
    start_date:   newService.value.startDate,
    end_date:     newService.value.endDate
  }
  form.value.selectedServiceId = 'new-' + Date.now()
  form.value.title       = `${newService.value.title} — Special Offer`
  form.value.description = newService.value.desc
  form.value.type        = newService.value.type || 'General'
  form.value.discount    = newService.value.discount
  form.value.startDate   = newService.value.startDate || today.value
  form.value.endDate     = newService.value.endDate || (() => { const d = new Date(); d.setMonth(d.getMonth()+3); return d.toISOString().split('T')[0] })()
  submit()
}

const selectedService = computed(() => {
  if (form.value.isFromScratch) return form.value.newServiceData
  return providerServices.value.find(s => s.id === form.value.selectedServiceId) || null
})

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
  form.value.isFromScratch     = false
  dropdownOpen.value           = false
  
  // Auto-fill metadata
  form.value.title       = `${svc.title} — Special Offer`
  form.value.description = svc.desc || svc.description || ''
  form.value.type        = svc.type || 'General'
}

/* ── Computed helpers ─────────────────────────── */
const progressWidth = computed(() => `${(step.value / 2) * 100}%`)
const subProgressWidth = computed(() => `${(subStep.value / 2) * 100}%`)

const stepTitle = computed(() =>
  step.value === 1
    ? (isEdit.value ? 'Edit Offer — Service' : 'Choose a Service')
    : (isEdit.value ? 'Edit Offer Details' : 'Offer Details')
)
const stepSub = computed(() => {
  if (isEdit.value) {
    return 'Category, icon, description and features can be updated.'
  }
  return step.value === 1 ? 'Pick a service to create this offer.' : 'Set the discount and dates for your offer.'
})
const subStepTitle = computed(() => subStep.value === 1 ? 'Create a service for this offer' : 'Apply a discount')
const subStepSub   = computed(() => subStep.value === 1 ? 'Exclusive to this offer' : 'Set a discount to turn this service into a deal')

const subDiscountedPrice = computed(() => {
  if (!newService.value.discount || !newService.value.price) return '0'
  return (Number(newService.value.price) * (1 - newService.value.discount / 100)).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
})

const today = computed(() => new Date().toISOString().split('T')[0])

const offerIsLive = computed(() => {
  if (!props.offer?.start_date && !props.offer?.startDate) return false
  const start = props.offer.start_date ?? props.offer.startDate
  return start <= today.value
})

const minEndDate = computed(() => {
  if (isEdit.value && props.offer) {
    const current = props.offer.end_date ?? props.offer.endDate ?? ''
    return current > today.value ? current : today.value
  }
  return form.value.startDate || today.value
})

const maxOfferEndDate = computed(() => {
  const s = selectedService.value
  if (!s) return null
  return s.end_date || s.endDate || null
})

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
      
      if (offer) {
        const sId = offer.serviceId ?? offer.service_id
        const svc = providerServices.value.find(s => String(s.id) === String(sId))
        
        form.value = {
          title:             offer.title        ?? '',
          discount:          offer.discount_pct ?? offer.discount ?? null,
          startDate:         offer.start_date   ?? offer.startDate ?? '',
          endDate:           offer.end_date      ?? offer.endDate   ?? '',
          description:       offer.description  ?? '',
          type:              offer.type         ?? 'General',
          selectedServiceId: sId                ?? null,
          icon:              svc?.icon || '🛎️',
          price:             svc?.price || null,
          unit:              svc?.price_unit || svc?.unit || 'day',
          featuresRaw:       '',
        }

        if (svc?.features) {
          try {
            const feat = typeof svc.features === 'string' ? JSON.parse(svc.features) : svc.features
            form.value.featuresRaw = Array.isArray(feat) ? feat.join('\n') : ''
          } catch {
            form.value.featuresRaw = ''
          }
        }
      } else {
        form.value = blankForm()
      }
      fetchServices()
    }
  }
)

// Also watch providerServices so we can populate fields if they load after the offer is set
watch(() => providerServices.value, (svcs) => {
  if (isEdit.value && form.value.selectedServiceId && svcs.length) {
    const svc = svcs.find(s => String(s.id) === String(form.value.selectedServiceId))
    if (svc) {
      form.value.icon  = svc.icon || form.value.icon
      form.value.price = svc.price || form.value.price
      form.value.unit  = svc.price_unit || svc.unit || form.value.unit
      if (svc.features && !form.value.featuresRaw) {
        try {
          const feat = typeof svc.features === 'string' ? JSON.parse(svc.features) : svc.features
          form.value.featuresRaw = Array.isArray(feat) ? feat.join('\n') : ''
        } catch { /* ignore */ }
      }
    }
  }
}, { deep: true })

watch(() => props.providerId, (id) => {
  if (id && props.modelValue) fetchServices()
})

/* ── Validation ──────────────────────────────── */
function validateStep() {
  const e = {}
  if (step.value === 1) {
    if (!form.value.selectedServiceId) e.service = 'Please select a service.'
  }
  if (step.value === 2) {
    if (!form.value.discount || form.value.discount < 1 || form.value.discount > 99)
      e.discount = 'Enter a valid discount (1–99).'
    if (!form.value.startDate) e.startDate = 'Start date is required.'
    if (!form.value.endDate) e.endDate = 'End date is required.'
    if (form.value.startDate && form.value.endDate && form.value.endDate < form.value.startDate)
      e.endDate = 'End date must be after start date.'
    if (form.value.endDate && maxOfferEndDate.value && form.value.endDate > maxOfferEndDate.value)
      e.endDate = `Offer must end before the service ends (${maxOfferEndDate.value}).`
  }
  errors.value = e
  return !Object.keys(e).length
}

/* ── Main wizard navigation ──────────────────── */
function next() {
  if (!validateStep()) return
  if (step.value === 2) { submit(); return }
  step.value++
}
function back() {
  if (step.value === 1 || (isEdit.value && step.value === 2)) { close(); return }
  step.value--; errors.value = {}
}

/* ── Sub-modal navigation ────────────────────── */
function subNext() {
  if (subStep.value === 1) {
    if (validateSubStep1()) subStep.value = 2
    return
  }
  // Step 2: only discount is required
  const e = {}
  if (!newService.value.discount || newService.value.discount < 1) e.discount = 'Enter a valid discount.'
  subErrors.value = e
  if (Object.keys(e).length) return
  confirmNewService()
  showNewServiceForm.value = false
  step.value = 2
}
function subBack() {
  if (subStep.value === 1) { showNewServiceForm.value = false; return }
  subStep.value = 1
}

async function submit() {
  submitting.value = true
  try {
    if (!form.value.startDate) form.value.startDate = today.value
    if (!form.value.endDate) {
      const d = new Date()
      d.setMonth(d.getMonth() + 3)
      form.value.endDate = d.toISOString().split('T')[0]
    }
    emit('save', {
      title:       form.value.title,
      discount:    form.value.discount,
      startDate:   form.value.startDate,
      endDate:     form.value.endDate,
      description: form.value.description,
      type:        form.value.type,
      offerType:   'service',
      serviceId:   form.value.isFromScratch ? null : form.value.selectedServiceId,
      newService:  form.value.isFromScratch ? {
        ...form.value.newServiceData,
        features: form.value.newServiceData.featuresRaw.split('\n').map(s => s.trim()).filter(Boolean)
      } : (isEdit.value ? {
        // If editing, also pass the service updates
        id:         form.value.selectedServiceId,
        title:      form.value.title,
        type:       form.value.type,
        icon:       form.value.icon,
        price:      form.value.price,
        price_unit: form.value.unit,
        description: form.value.description,
        features:   form.value.featuresRaw.split('\n').map(s => s.trim()).filter(Boolean),
        is_available: 1
      } : null),
      packageIds:  [],
      agency_id:   props.providerId,
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
    step.value              = 1
    subStep.value           = 1
    showNewServiceForm.value = false
    form.value              = blankForm()
    errors.value            = {}
    dropdownOpen.value      = false
  }, 300)
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(45,49,66,.48);
  display: flex; align-items: center; justify-content: center;
  z-index: 200; padding: 20px;
}
.modal-backdrop--top { z-index: 210; }
.modal {
  background: #fff; border-radius: 22px; width: 100%; max-width: 520px;
  box-shadow: 0 20px 72px rgba(45,49,66,.22);
  max-height: 92vh; display: flex; flex-direction: column;
  position: relative;
}
.modal--package { max-width: 500px; }

.wizard-progress { height: 4px; background: var(--gray-100); flex-shrink: 0; }
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
  position: relative; min-height: 200px;
}
.modal.step-1 .modal__body { overflow: visible; }

.modal__footer {
  padding: 14px 26px; border-top: 1px solid var(--gray-100);
  display: flex; justify-content: space-between; align-items: center;
  gap: 10px; flex-shrink: 0;
}
.footer-right { display: flex; align-items: center; gap: 12px; }
.step-dots    { display: flex; gap: 5px; align-items: center; }
.dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--gray-200); transition: all .25s ease;
}
.dot.active { background: var(--coral); transform: scale(1.3); }
.dot.done   { background: var(--teal); }

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

/* Choice Cards */
.new-svc-card {
  width: 100%; display: flex; align-items: center; gap: 14px;
  padding: 14px 18px; background: var(--teal-lt, #f0fafa); border: 2px dashed var(--teal);
  border-radius: 14px; cursor: pointer; text-align: left;
  transition: all .25s ease;
}
.new-svc-card:hover { background: #e2f8f6; transform: translateY(-2px); }
.new-svc-card__icon {
  width: 36px; height: 36px; border-radius: 50%; background: var(--teal);
  color: #fff; display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem; font-weight: 700; flex-shrink: 0;
}
.new-svc-card__text { flex: 1; }
.new-svc-card__title { font-size: .92rem; font-weight: 700; color: var(--indigo); }
.new-svc-card__sub   { font-size: .78rem; color: var(--gray-600); margin-top: 2px; }
.new-svc-card__arrow { font-size: 1.1rem; color: var(--teal); font-weight: 700; }

/* Dropdown */
.existing-svcs-wrap { display: flex; flex-direction: column; gap: 6px; }
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
  box-shadow: 0 12px 48px rgba(45,49,66,.25);
  z-index: 1000; max-height: 190px; overflow-y: auto; padding: 8px;
}
.dropdown-list { display: flex; flex-direction: column; gap: 4px; }
.dropdown-item {
  display: flex; align-items: center; gap: 12px; padding: 10px;
  border-radius: 10px; cursor: pointer; transition: background .15s;
}
.dropdown-item:hover    { background: var(--gray-50); }
.dropdown-item.selected { background: var(--teal-lt); }
.item-img { width: 44px; height: 36px; border-radius: 6px; overflow: hidden; flex-shrink: 0; }
.item-img--fallback {
  background: var(--gray-100); display: flex; align-items: center;
  justify-content: center; font-size: 1rem; height: 100%; width: 100%;
}
.item-info { flex: 1; min-width: 0; }
.item-name { font-size: .88rem; font-weight: 600; color: var(--indigo); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-meta { font-size: .76rem; color: var(--gray-500); margin-top: 2px; }
.item-check { color: var(--teal); font-weight: 900; font-size: 1rem; }

/* Step 2 specific */
.selected-summary { display: flex; flex-wrap: wrap; gap: 6px; }
.summary-pill {
  background: var(--teal-lt, #f0fafa); border: 1px solid var(--teal);
  border-radius: 20px; padding: 4px 12px;
  font-size: .76rem; color: var(--indigo); font-weight: 600;
  display: flex; align-items: center; gap: 6px;
}

.form-group  { display: flex; flex-direction: column; gap: 7px; margin-bottom: 6px; }
.form-label  { font-size: .84rem; font-weight: 600; color: var(--indigo); }
.form-row    { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-input  {
  width: 100%;
  border: 1.5px solid var(--gray-200); border-radius: 8px;
  padding: 10px 13px; font-size: .9rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); outline: none; background: #fff;
  transition: all .2s ease;
}
.form-input:focus { border-color: var(--coral); }
.form-select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;
  padding-right: 36px; appearance: none; cursor: pointer;
}
.form-input--lg { font-size: 1.1rem; }
.form-textarea { resize: vertical; min-height: 86px; }
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

/* ── Locked Field Styles (1x1 with Serviceformmodal) ── */
.input-wrap { position: relative; }
.input--locked {
  background-color: var(--gray-50, #f9f9fc) !important;
  color: var(--gray-400, #a0aec0) !important;
  border-color: var(--gray-100, #edf2f7) !important;
  cursor: not-allowed !important;
  opacity: 0.8;
  padding-right: 36px !important;
  background-image: none !important;
}
.lock-icon {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  width: 13px; height: 13px; pointer-events: none;
  color: var(--gray-400, #a0aec0); opacity: 0.6;
}
.form-hint { font-weight: 400; color: var(--gray-400); font-size: .78rem; }
.text-center { text-align: center; }

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
.dp-prices { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.dp-old    { color: var(--gray-400); font-size: .82rem; }
.dp-new    { color: var(--coral); font-size: .95rem; }
.dp-unit   { font-size: .76rem; color: var(--gray-500); }
.dp-badge  {
  background: var(--coral); color: #fff; font-size: .68rem;
  font-weight: 800; padding: 2px 7px; border-radius: 20px;
}

/* Availability toggle */
.availability-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 16px; border-radius: 12px;
  border: 1.5px solid var(--gray-200); background: var(--gray-50, #f9f9fc); gap: 12px;
}
.availability-row__label { font-size: .88rem; font-weight: 600; color: var(--indigo); }
.availability-row__sub   { font-size: .76rem; color: var(--gray-500); margin-top: 2px; }
.toggle {
  width: 44px; height: 24px; border-radius: 12px; background: var(--gray-200);
  border: none; cursor: pointer; position: relative;
  transition: background .2s ease; flex-shrink: 0; padding: 0;
}
.toggle.on { background: var(--teal); }
.toggle__knob {
  position: absolute; top: 3px; left: 3px; width: 18px; height: 18px;
  border-radius: 50%; background: #fff; transition: transform .2s ease;
  box-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.toggle.on .toggle__knob { transform: translateX(20px); }

.dropdown-slide-enter-active, .dropdown-slide-leave-active { transition: all .2s ease; }
.dropdown-slide-enter-from, .dropdown-slide-leave-to { opacity: 0; transform: translateY(-10px); }

.wizard-fade-enter-active, .wizard-fade-leave-active { transition: all .22s ease; }
.wizard-fade-enter-from, .wizard-fade-leave-to { opacity: 0; transform: scale(.97); }
</style>