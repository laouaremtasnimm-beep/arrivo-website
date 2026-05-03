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
               STEP 1 — Pick or create a package
          ══════════════════════════════════════════ -->
          <div class="modal__body" v-if="step === 1">

            <!-- Create new package card (always first) -->
            <button class="new-pkg-card" @click="openCreatePackage">
              <div class="new-pkg-card__icon">＋</div>
              <div class="new-pkg-card__text">
                <div class="new-pkg-card__title">Create a new package</div>
                <div class="new-pkg-card__sub">It will be exclusive to this offer</div>
              </div>
              <span class="new-pkg-card__arrow">→</span>
            </button>

            <!-- Dropdown Existing Packages -->
            <div class="existing-pkgs-wrap">
              <label class="form-label">Or choose existing</label>
              <div class="dropdown-container">
                <button
                  class="dropdown-trigger"
                  :class="{ open: existingOpen }"
                  type="button"
                  @click="existingOpen = !existingOpen"
                >
                  <div class="dropdown-trigger__content">
                    <span class="trigger-icon">📦</span>
                    <div class="trigger-text">
                      <div class="trigger-title" v-if="selectedPackage">
                        <span class="type-mini-badge" :class="selectedPackage.itemType">
                          {{ selectedPackage.itemType === 'package' ? 'PKG' : 'SVC' }}
                        </span>
                        {{ selectedPackage.title }}
                      </div>
                      <div class="trigger-placeholder" v-else>Select from your listings...</div>
                    </div>
                  </div>
                  <span class="dropdown-arrow">{{ existingOpen ? '▴' : '▾' }}</span>
                </button>

                <Transition name="dropdown-slide">
                  <div class="dropdown-menu" v-if="existingOpen">
                    <div v-if="loadingPackages" class="loading-row">
                      <span class="spinner"></span> Loading listings…
                    </div>
                    <div v-else-if="agencyPackages.length === 0" class="empty-state">
                      <p>No listings found.</p>
                    </div>
                    <div v-else class="dropdown-list">
                      <div
                        v-for="pkg in agencyPackages"
                        :key="pkg.id"
                        class="dropdown-item"
                        :class="{ selected: form.selectedPackageIds.includes(pkg.id) }"
                        @click="selectOne(pkg)"
                      >
                        <div class="item-img">
                          <img v-if="pkg.img_url" :src="pkg.img_url" />
                          <div v-else class="item-img--fallback">🏔️</div>
                        </div>
                        <div class="item-info">
                          <div class="item-name">
                            <span class="type-mini-badge" :class="pkg.itemType">{{ pkg.itemType === 'package' ? 'PKG' : 'SVC' }}</span>
                            {{ pkg.title }}
                          </div>
                          <div class="item-meta">{{ pkg.destination || pkg.type }} · ${{ Number(pkg.price).toLocaleString() }}</div>
                        </div>
                        <div class="item-check" v-if="form.selectedPackageIds.includes(pkg.id)">✓</div>
                      </div>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>

            <p class="field-error" v-if="errors.packages">{{ errors.packages }}</p>
          </div>

          <!-- ══════════════════════════════════════════
               STEP 2 — Offer details
          ══════════════════════════════════════════ -->
          <div class="modal__body" v-else-if="step === 2">

            <!-- Selected packages summary pills -->
            <div class="selected-summary" v-if="selectedPackages.length">
              <span v-for="pkg in selectedPackages" :key="pkg.id" class="summary-pill">
                📦 {{ pkg.title }}
              </span>
            </div>

            <div class="form-group" v-if="!selectedPackage">
              <label class="form-label">Offer title *</label>
              <input class="form-input" v-model="form.title" placeholder="e.g. Adventure Bundle Deal" :disabled="isEdit" />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <div class="form-group">
              <label class="form-label">Discount (%) *</label>
              <input type="number" min="1" max="99" class="form-input"
                v-model.number="form.discount" placeholder="e.g. 20" :disabled="isEdit" />
              <p class="field-error" v-if="errors.discount">{{ errors.discount }}</p>
            </div>

            <div class="form-group" v-if="!selectedPackage">
              <label class="form-label">Category</label>
              <select class="form-input form-select" v-model="form.type">
                <option value="General">✨ General</option>
                <option value="Adventure">🧗 Adventure</option>
                <option value="Beach">🏖️ Beach</option>
                <option value="Cultural">🏛️ Cultural</option>
                <option value="Family">👨‍👩‍👧 Family</option>
                <option value="Wellness">🧘 Wellness</option>
                <option value="Bundle">📦 Bundle</option>
              </select>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date *</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="form.startDate" :min="today" :disabled="isEdit" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.startDate">{{ errors.startDate }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">End Date *</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="form.endDate" :min="today" :max="maxOfferEndDate" :disabled="isEdit" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.endDate">{{ errors.endDate }}</p>
              </div>
            </div>

            <div class="form-group" v-if="!selectedPackage">
              <label class="form-label">Description *</label>
              <textarea class="form-input form-textarea" v-model="form.description"
                placeholder="Describe what makes this offer special…" />
              <p class="field-error" v-if="errors.description">{{ errors.description }}</p>
            </div>

            <!-- Image URL (For any offer that has linked packages) -->
            <div class="form-group" v-if="form.selectedPackageIds.length > 0">
              <label class="form-label">Cover image URL</label>
              <input class="form-input" v-model="form.img_url" placeholder="https://…" />
              <div class="img-preview" v-if="form.img_url">
                <img :src="form.img_url || 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg'" alt="Preview" />
              </div>
            </div>

            <!-- What's included -->
            <div class="form-group" v-if="!selectedPackage">
              <label class="form-label">What's included <span class="form-hint">(one per line)</span></label>
              <textarea
                class="form-input form-textarea"
                v-model="includesRaw"
                placeholder="Free breakfast&#10;Airport pick-up&#10;Premium insurance"
                rows="3"
              />
            </div>

            <!-- Live discount preview -->
            <div class="discount-preview" v-if="form.discount && selectedPackages.length">
              <div class="discount-preview__label">Price preview with discount</div>
              <div class="discount-preview__items">
                <div v-for="pkg in selectedPackages" :key="pkg.id" class="discount-preview__row">
                  <span class="dp-name">{{ pkg.title }}</span>
                  <span class="dp-prices">
                    <s class="dp-old">${{ Number(pkg.price).toLocaleString() }}</s>
                    <strong class="dp-new">${{ discountedPrice(pkg.price) }}</strong>
                  </span>
                </div>
              </div>
            </div>

          </div>

          <!-- ── Footer ── -->
          <div class="modal__footer">
            <button class="btn btn-outline" @click="back">
              {{ (step === 1 || (isEdit && step === 2)) ? 'Cancel' : '← Back' }}
            </button>
            <button class="btn btn-coral" @click="next" :disabled="submitting">
              <span v-if="submitting" class="btn-spinner"></span>
              {{ step === 1 ? 'Continue →' : (isEdit ? 'Save Changes' : 'Create Offer') }}
            </button>
          </div>

        </div>
      </div>
    </Transition>

    <!-- ══════════════════════════════════════════
         Nested: Create Package sub-modal (2-step mini-wizard)
    ══════════════════════════════════════════ -->
    <Transition name="wizard-fade">
      <div class="modal-backdrop modal-backdrop--top" v-if="showCreatePackage" @click.self="showCreatePackage = false">
        <div class="modal modal--package">

          <!-- Sub-modal progress bar -->
          <div class="wizard-progress">
            <div class="wizard-progress__fill" :style="{ width: subProgressWidth }"></div>
          </div>

          <div class="modal__header">
            <div class="header-left">
              <div class="step-badge">New Package · Step {{ subStep }} of 2</div>
              <h2 class="modal__title">{{ subStepTitle }}</h2>
              <p class="step-sub">{{ subStepSub }}</p>
            </div>
            <button class="modal__close" @click="showCreatePackage = false">✕</button>
          </div>

          <!-- ── Sub-step 1: Package details ── -->
          <div class="modal__body" v-if="subStep === 1">

            <div class="form-group">
              <label class="form-label">Package title *</label>
              <input class="form-input" v-model="newPkg.title" placeholder="e.g. Swiss Alps Winter Retreat" />
              <p class="field-error" v-if="pkgErrors.title">{{ pkgErrors.title }}</p>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Destination *</label>
                <input class="form-input" v-model="newPkg.destination" placeholder="e.g. Switzerland" />
                <p class="field-error" v-if="pkgErrors.destination">{{ pkgErrors.destination }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Type</label>
                <select class="form-input form-select" v-model="newPkg.type">
                  <option value="Adventure">🧗 Adventure</option>
                  <option value="Beach">🏖️ Beach</option>
                  <option value="Cultural">🏛️ Cultural</option>
                  <option value="Family">👨‍👩‍👧 Family</option>
                  <option value="Wellness">🧘 Wellness</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Price ($) *</label>
                <input type="number" min="0" class="form-input" v-model.number="newPkg.price" placeholder="e.g. 1200" />
                <p class="field-error" v-if="pkgErrors.price">{{ pkgErrors.price }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Spots Available</label>
                <input type="number" min="1" class="form-input" v-model.number="newPkg.spots_available" placeholder="e.g. 10" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date *</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="newPkg.start_date" :min="today" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="pkgErrors.start_date">{{ pkgErrors.start_date }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">End Date *</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="newPkg.end_date" :min="newPkg.start_date || today" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="pkgErrors.end_date">{{ pkgErrors.end_date }}</p>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Description *</label>
              <textarea class="form-input form-textarea" v-model="newPkg.description"
                placeholder="Describe this package…" />
              <p class="field-error" v-if="pkgErrors.description">{{ pkgErrors.description }}</p>
            </div>

            <div class="form-group">
              <label class="form-label">Image URL</label>
              <input class="form-input" v-model="newPkg.img_url" placeholder="https://…" />
              <div class="img-preview" v-if="newPkg.img_url">
                <img :src="newPkg.img_url" alt="Preview" @error="newPkg.img_url = ''" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">What's included <span class="form-hint">(one per line)</span></label>
              <textarea
                class="form-input form-textarea"
                v-model="pkgIncludesRaw"
                placeholder="All meals&#10;Private transport"
                rows="3"
              />
            </div>

            <div class="autofill-notice" v-if="form.startDate || form.endDate">
              <span>💡</span>
              <span>Offer dates have been pre-filled from your offer settings.</span>
            </div>

          </div>

          <!-- ── Sub-step 2: Discount + category ── -->
          <div class="modal__body" v-else-if="subStep === 2">

            <!-- Package preview pills -->
            <div class="selected-summary">
              <span class="summary-pill">📦 {{ newPkg.title }}</span>
              <span class="summary-pill summary-pill--meta" v-if="newPkg.price">
                ${{ Number(newPkg.price).toLocaleString() }}
              </span>
            </div>

            <div class="form-group">
              <label class="form-label">Discount (%) *</label>
              <div class="discount-input-wrap">
                <input
                  type="number" min="1" max="99"
                  class="form-input"
                  v-model.number="newPkg.discount"
                  placeholder="e.g. 20"
                />
                <span class="discount-suffix">% off</span>
              </div>
              <p class="field-error" v-if="pkgErrors.discount">{{ pkgErrors.discount }}</p>
            </div>

            <!-- Live discount preview -->
            <div class="discount-preview" v-if="newPkg.discount && newPkg.price">
              <div class="discount-preview__label">Price preview</div>
              <div class="discount-preview__items">
                <div class="discount-preview__row">
                  <span class="dp-name">{{ newPkg.title }}</span>
                  <span class="dp-prices">
                    <s class="dp-old">${{ Number(newPkg.price).toLocaleString() }}</s>
                    <strong class="dp-new">${{ subDiscountedPrice }}</strong>
                    <span class="dp-badge">-{{ newPkg.discount }}%</span>
                  </span>
                </div>
              </div>
            </div>

          </div>

          <!-- Sub-modal footer -->
          <div class="modal__footer">
            <button class="btn btn-outline" @click="subBack">
              {{ subStep === 1 ? 'Cancel' : '← Back' }}
            </button>
            <div class="footer-right">
              <span class="step-dots">
                <span class="dot" :class="{ active: subStep === 1, done: subStep > 1 }"></span>
                <span class="dot" :class="{ active: subStep === 2 }"></span>
              </span>
              <button class="btn btn-coral" @click="subNext" :disabled="savingPackage">
                <span v-if="savingPackage" class="btn-spinner"></span>
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
  modelValue: Boolean,
  offer:      { type: Object, default: null },
  agencyId:   { type: [Number, String], default: null },
})
const emit = defineEmits(['update:modelValue', 'save'])

const isEdit = computed(() => !!props.offer)

/* ── Wizard state ─────────────────────────────── */
const step       = ref(1)
const submitting = ref(false)
const errors     = ref({})
const existingOpen = ref(false)
const includesRaw  = ref('')
const pkgIncludesRaw = ref('')

const blankForm = () => ({
  title:              '',
  discount:           null,
  startDate:          '',
  endDate:            '',
  description:        '',
  type:               'Bundle',
  selectedPackageIds: [],
  includes:           [],
  img_url:            '',
})
const form = ref(blankForm())

/* ── Packages list ────────────────────────────── */
const agencyPackages  = ref([])
const loadingPackages = ref(false)

const selectedPackage = computed(() =>
  agencyPackages.value.find(p => p.id === form.value.selectedPackageIds[0]) || null
)
const selectedPackages = computed(() =>
  agencyPackages.value.filter(p => form.value.selectedPackageIds.includes(p.id))
)

const maxOfferEndDate = computed(() => {
  // If a package is selected, the offer (booking deadline) must end before the trip starts
  const firstPkg = selectedPackage.value
  if (firstPkg && firstPkg.start_date) return firstPkg.start_date
  return null
})

const isExclusive = computed(() => {
  if (!selectedPackage.value) return false
  return selectedPackage.value.offer_only == 1 || selectedPackage.value.offer_only == true
})

const today = computed(() => new Date().toISOString().split('T')[0])

function selectOne(pkg) {
  form.value.selectedPackageIds = [pkg.id]
  existingOpen.value = false
  if (!form.value.title) form.value.title = pkg.title
}

async function fetchPackages() {
  if (!props.agencyId) return
  loadingPackages.value = true
  try {
    const [pkgRes, svcRes] = await Promise.all([
      fetch(`/arrivo-website/backend/api/v1/Packages.php?agency_id=${props.agencyId}`),
      fetch(`/arrivo-website/backend/api/v1/Services.php?provider_id=${props.agencyId}`)
    ])
    const pkgData = await pkgRes.json()
    const svcData = await svcRes.json()
    const pkgs = (pkgData.packages || []).map(p => ({ ...p, itemType: 'package' }))
    const svcs = (svcData.services || []).map(s => ({ ...s, itemType: 'service' }))
    agencyPackages.value = [...pkgs, ...svcs]
  } catch (e) {
    console.error('Failed to load items', e)
  } finally {
    loadingPackages.value = false
  }
}

/* ── Create-package sub-modal (2-step) ────────── */
const showCreatePackage = ref(false)
const savingPackage     = ref(false)
const pkgErrors         = ref({})
const subStep           = ref(1)
const showCustomDates   = ref(false)

const blankPkg = () => ({
  title:            '',
  destination:      '',
  type:             'Adventure',
  price:            null,
  spots_available:  10,
  start_date:       '',
  end_date:         '',
  description:      '',
  img_url:          '',
  discount:         null,
  offerType:        'General',
  offer_start_date: '',
  offer_end_date:   '',
})
const newPkg = ref(blankPkg())

const subProgressWidth = computed(() => `${(subStep.value / 2) * 100}%`)

const subStepTitle = computed(() =>
  subStep.value === 1
    ? 'Create a package for this offer'
    : 'Apply a discount'
)
const subStepSub = computed(() =>
  subStep.value === 1
    ? 'Hidden from public listings — exclusive to this offer'
    : 'Set a discount and category to turn this package into a deal'
)

const subDiscountedPrice = computed(() => {
  const d = newPkg.value.discount || 0
  return (Number(newPkg.value.price) * (1 - d / 100))
    .toFixed(0)
    .replace(/\B(?=(\d{3})+(?!\d))/g, ',')
})

function openCreatePackage() {
  pkgErrors.value       = {}
  subStep.value         = 1
  showCustomDates.value = false
  newPkg.value = {
    ...blankPkg(),
    start_date:       form.value.startDate || '',
    end_date:         form.value.endDate   || '',
    offer_start_date: form.value.startDate || '',
    offer_end_date:   form.value.endDate   || '',
  }
  pkgIncludesRaw.value    = ''
  showCreatePackage.value = true
}

function validateSubStep1() {
  const e = {}
  if (!newPkg.value.title?.trim())       e.title       = 'Title is required.'
  if (!newPkg.value.destination?.trim()) e.destination = 'Destination is required.'
  if (!newPkg.value.price || newPkg.value.price <= 0) e.price = 'Enter a valid price.'
  if (!newPkg.value.start_date)          e.start_date  = 'Start date is required.'
  if (!newPkg.value.end_date)            e.end_date    = 'End date is required.'
  if (newPkg.value.start_date && newPkg.value.end_date && newPkg.value.end_date < newPkg.value.start_date)
    e.end_date = 'End date must be after start date.'
  if (!newPkg.value.description?.trim()) e.description = 'Description is required.'
  pkgErrors.value = e
  return !Object.keys(e).length
}

function validateSubStep2() {
  const e = {}
  if (!newPkg.value.discount || newPkg.value.discount < 1 || newPkg.value.discount > 80)
    e.discount = 'Enter a valid discount between 1 and 80.'
  pkgErrors.value = e
  return !Object.keys(e).length
}

function subNext() {
  if (subStep.value === 1) {
    if (!validateSubStep1()) return
    subStep.value = 2
    return
  }
  saveNewPackage()
}

function subBack() {
  if (subStep.value === 1) {
    showCreatePackage.value = false
    return
  }
  pkgErrors.value = {}
  subStep.value   = 1
}

async function saveNewPackage() {
  if (!validateSubStep2()) return
  savingPackage.value = true
  try {
    const effectiveStart = showCustomDates.value ? newPkg.value.offer_start_date : (form.value.startDate || newPkg.value.offer_start_date)
    const effectiveEnd   = showCustomDates.value ? newPkg.value.offer_end_date   : (form.value.endDate   || newPkg.value.offer_end_date)

    const res = await fetch('/arrivo-website/backend/api/v1/Packages.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        ...newPkg.value,
        duration_days: 1,
        agency_id:     props.agencyId,
        offer_only:    1,
        is_active:     1,
        includes:      pkgIncludesRaw.value.split('\n').map(s => s.trim()).filter(Boolean),
      }),
    })
    const data = await res.json()

    if (data.package_id) {
      const created = {
        id:              data.package_id,
        title:           newPkg.value.title,
        type:            newPkg.value.type,
        duration_days:   1,
        price:           newPkg.value.price,
        spots_available: newPkg.value.spots_available || 10,
        description:     newPkg.value.description,
        img_url:         newPkg.value.img_url,
        offer_only:      1,
        itemType:        'package',
      }
      agencyPackages.value.unshift(created)
      form.value.selectedPackageIds.push(data.package_id)

      // Auto-fill offer fields from the new package's offer settings
      if (!form.value.title)    form.value.title    = newPkg.value.title
      if (!form.value.discount) form.value.discount = newPkg.value.discount
      if (form.value.type === 'Bundle' || !form.value.type) form.value.type = newPkg.value.offerType
      if (!form.value.startDate) form.value.startDate = effectiveStart
      if (!form.value.endDate)   form.value.endDate   = effectiveEnd
      
      // Also sync description and inclusions if empty
      if (!form.value.description) form.value.description = newPkg.value.description
      if (!includesRaw.value)      includesRaw.value      = pkgIncludesRaw.value

      showCreatePackage.value = false
      
      // Automatically save the offer too, as requested
      submit()
    }
  } catch (e) {
    console.error('Failed to create package', e)
  } finally {
    savingPackage.value = false
  }
}

/* ── Computed helpers ─────────────────────────── */
const progressWidth = computed(() => `${(step.value / 2) * 100}%`)

const stepTitle = computed(() =>
  step.value === 1
    ? (isEdit.value ? 'Edit Offer — Packages' : 'Choose Packages')
    : (isEdit.value ? 'Edit Offer Details'    : 'Offer Details')
)
const stepSub = computed(() =>
  step.value === 1
    ? 'Pick one or more packages to bundle into this offer.'
    : 'Set the discount, dates and description for your offer.'
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
      step.value   = offer ? 2 : 1
      errors.value = {}
      existingOpen.value = false
      form.value   = offer
        ? {
            title:              offer.title        ?? '',
            discount:           offer.discount_pct ?? offer.discount ?? null,
            startDate:          offer.start_date   ?? offer.startDate ?? '',
            endDate:            offer.end_date     ?? offer.endDate   ?? '',
            description:        offer.description  ?? '',
            type:               offer.type         ?? 'Bundle',
            selectedPackageIds: offer.packageIds   ?? [],
            includes:           offer.includes     ?? [],
            img_url:            offer.packages?.[0]?.img_url ?? '',
          }
        : blankForm()

      if (offer?.includes) {
        includesRaw.value = Array.isArray(offer.includes) ? offer.includes.join('\n') : ''
      } else {
        includesRaw.value = ''
      }

      if (props.agencyId) fetchPackages()
    }
  }
)

watch(() => props.agencyId, (newId) => {
  if (newId && props.modelValue) fetchPackages()
})

/* ── Validation ──────────────────────────────── */
function validateStep() {
  const e = {}
  if (step.value === 1) {
    if (!form.value.selectedPackageIds.length)
      e.packages = 'Select at least one package, or create a new one above.'
  }
  if (step.value === 2) {
    if (!form.value.title?.trim() && !selectedPackage.value)
      e.title = 'Title is required.'
    if (!form.value.discount || form.value.discount < 1 || form.value.discount > 99)
      e.discount = 'Enter a valid discount (1–99).'
    if (!form.value.startDate)
      e.startDate = 'Start date is required.'
    else if (form.value.startDate < today.value)
      e.startDate = 'Start date cannot be in the past.'

    if (!form.value.endDate)
      e.endDate = 'End date is required.'
    if (form.value.startDate && form.value.endDate && form.value.endDate < form.value.startDate)
      e.endDate = 'End date must be after start date.'
    
    // NEW: Scarcity rule — Offer must end before the trip starts
    if (form.value.endDate && maxOfferEndDate.value && form.value.endDate > maxOfferEndDate.value)
      e.endDate = `Offer must end before the package starts (${maxOfferEndDate.value}).`
    if (!form.value.description?.trim() && !selectedPackage.value)
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
    const payload = {
      ...form.value,
      title:       selectedPackage.value ? selectedPackage.value.title : form.value.title,
      description: selectedPackage.value ? (selectedPackage.value.desc || selectedPackage.value.description) : form.value.description,
      type:        selectedPackage.value ? selectedPackage.value.type : form.value.type,
      id:          isEdit.value ? props.offer.id : undefined,
      agency_id:  props.agencyId,
      offerType:  form.value.selectedPackageIds.length > 0 ? 'package' : 'general',
      packageIds: form.value.selectedPackageIds,
      includes:   selectedPackage.value ? (selectedPackage.value.includes || []) : includesRaw.value.split('\n').map(s => s.trim()).filter(Boolean),
      source:     'manual',
      offerID:    props.offer?.offerID ?? props.offer?.id ?? null,
      is_active:  1,
      img_url:    form.value.img_url,
    }
    emit('save', payload)
    close()
  } finally {
    submitting.value = false
  }
}

function close() {
  emit('update:modelValue', false)
  setTimeout(() => {
    step.value              = 1
    form.value              = blankForm()
    errors.value            = {}
    showCreatePackage.value = false
    subStep.value           = 1
    showCustomDates.value   = false
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

/* Footer right — dots + button */
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

/* Step 1 */
.new-pkg-card {
  display: flex; align-items: center; gap: 14px; padding: 14px 18px;
  border-radius: 14px; border: 2px dashed var(--teal);
  background: var(--teal-lt, #f0fafa); cursor: pointer;
  transition: all .18s ease; text-align: left; width: 100%;
}
.new-pkg-card:hover { background: #e2f8f6; }
.new-pkg-card__icon {
  width: 36px; height: 36px; border-radius: 50%; background: var(--teal);
  color: #fff; font-size: 1.3rem; display: flex; align-items: center;
  justify-content: center; flex-shrink: 0;
}
.new-pkg-card__text { flex: 1; }
.new-pkg-card__title { font-size: .92rem; font-weight: 700; color: var(--indigo); }
.new-pkg-card__sub   { font-size: .78rem; color: var(--gray-600); margin-top: 2px; }
.new-pkg-card__arrow { font-size: 1.1rem; color: var(--teal); font-weight: 700; }

.loading-row {
  display: flex; align-items: center; gap: 10px;
  color: var(--gray-600); font-size: .88rem;
}
.spinner {
  width: 15px; height: 15px; border: 2px solid var(--gray-200);
  border-top-color: var(--teal); border-radius: 50%;
  animation: spin .7s linear infinite; flex-shrink: 0;
}
.empty-state {
  text-align: center; padding: 20px 0; color: var(--gray-500); font-size: .88rem;
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
.form-textarea { resize: vertical; min-height: 86px; }
.form-select   { cursor: pointer; }
.field-error   { font-size: .78rem; color: var(--coral); margin: 0; }
.form-hint     { font-weight: 400; color: var(--gray-400); font-size: .76rem; }

/* Discount input */
.discount-input-wrap { position: relative; display: flex; align-items: center; }
.discount-input-wrap .form-input { padding-right: 52px; width: 100%; }
.discount-suffix {
  position: absolute; right: 13px; font-size: .82rem;
  font-weight: 700; color: var(--gray-500); pointer-events: none;
}

/* Inherited dates */
.inherited-dates {
  display: flex; align-items: center; gap: 10px;
  background: #f0fafa; border: 1px solid var(--teal);
  border-radius: 12px; padding: 10px 14px;
}
.inherited-dates__icon { font-size: 1.1rem; flex-shrink: 0; }
.inherited-dates__info { flex: 1; min-width: 0; }
.inherited-dates__label {
  font-size: .68rem; color: var(--gray-500); font-weight: 700;
  text-transform: uppercase; letter-spacing: .05em;
}
.inherited-dates__value { font-size: .83rem; color: var(--indigo); font-weight: 600; margin-top: 2px; }
.inherited-dates__change {
  background: none; border: 1px solid var(--teal); border-radius: 20px;
  color: var(--teal); font-size: .74rem; font-weight: 700;
  padding: 4px 10px; cursor: pointer; flex-shrink: 0;
  transition: all .15s ease; font-family: 'DM Sans', sans-serif;
}
.inherited-dates__change:hover { background: var(--teal); color: #fff; }

/* Date inputs */
.date-wrap { position: relative; }
.date-wrap .form-input { padding-right: 36px; cursor: pointer; }
.date-wrap input::-webkit-calendar-picker-indicator {
  position: absolute; right: 0; top: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 1;
}
.date-icon {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  pointer-events: none; font-size: 1rem; z-index: 2;
}

/* Image preview */
.img-preview { margin-top: 8px; height: 110px; border-radius: 10px; overflow: hidden; }
.img-preview img { width: 100%; height: 100%; object-fit: cover; }

/* Discount preview */
.discount-preview {
  border-radius: 12px; border: 1.5px solid var(--gray-200); overflow: hidden;
}
.discount-preview__label {
  background: var(--gray-50, #f9f9fc); padding: 8px 14px;
  font-size: .72rem; font-weight: 700; color: var(--gray-600);
  text-transform: uppercase; letter-spacing: .05em;
  border-bottom: 1px solid var(--gray-200);
}
.discount-preview__items { padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; }
.discount-preview__row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.dp-name   { font-size: .85rem; color: var(--indigo); font-weight: 500; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-prices { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.dp-old    { color: var(--gray-400); font-size: .82rem; }
.dp-new    { color: var(--coral); font-size: .95rem; }
.dp-badge  {
  background: var(--coral); color: #fff; font-size: .68rem;
  font-weight: 800; padding: 2px 7px; border-radius: 20px;
}

.autofill-notice {
  display: flex; align-items: flex-start; gap: 8px;
  background: #fffbf0; border: 1px solid #ffe8a3; border-radius: 10px;
  padding: 10px 14px; font-size: .8rem; color: #7a5c00; line-height: 1.4;
}

/* Dropdown */
.existing-pkgs-wrap { display: flex; flex-direction: column; gap: 6px; }
.dropdown-container { position: relative; }
.dropdown-trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  padding: 10px 16px; border-radius: 12px;
  border: 1.5px solid var(--gray-200); background: var(--white);
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
  background: var(--white); border-radius: 14px;
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
.item-img img { width: 100%; height: 100%; object-fit: cover; }
.item-img--fallback {
  background: var(--gray-100); display: flex; align-items: center;
  justify-content: center; font-size: 1rem; height: 100%; width: 100%;
}
.item-info { flex: 1; min-width: 0; }
.item-name { font-size: .88rem; font-weight: 600; color: var(--indigo); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-meta { font-size: .76rem; color: var(--gray-500); margin-top: 2px; }
.item-check { color: var(--teal); font-weight: 900; font-size: 1rem; }

.dropdown-slide-enter-active, .dropdown-slide-leave-active { transition: all .2s ease; }
.dropdown-slide-enter-from, .dropdown-slide-leave-to { opacity: 0; transform: translateY(-10px); }

.type-mini-badge {
  font-size: .65rem; font-weight: 800; padding: 1px 4px; border-radius: 4px;
  margin-right: 4px; vertical-align: middle;
}
.type-mini-badge.package { background: var(--teal-lt); color: var(--teal-dk); }
.type-mini-badge.service { background: var(--coral-lt, #fff0f0); color: var(--coral); }

.wizard-fade-enter-active, .wizard-fade-leave-active { transition: all .22s ease; }
.wizard-fade-enter-from, .wizard-fade-leave-to { opacity: 0; transform: scale(.97); }
</style>