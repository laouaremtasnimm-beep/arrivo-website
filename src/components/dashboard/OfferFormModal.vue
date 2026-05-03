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

            <div class="form-group">
              <label class="form-label">Offer title *</label>
              <input class="form-input" v-model="form.title" placeholder="e.g. Adventure Bundle Deal" />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Discount (%) *</label>
                <input type="number" min="1" max="80" class="form-input"
                  v-model.number="form.discount" placeholder="e.g. 20" />
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
                  <option value="Bundle">📦 Bundle</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date *</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="form.startDate" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.startDate">{{ errors.startDate }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">End Date *</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="form.endDate" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.endDate">{{ errors.endDate }}</p>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Description *</label>
              <textarea class="form-input form-textarea" v-model="form.description"
                placeholder="Describe what makes this offer special…" />
              <p class="field-error" v-if="errors.description">{{ errors.description }}</p>
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
              {{ step === 1 ? 'Cancel' : '← Back' }}
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
         Nested: Create Package sub-modal
    ══════════════════════════════════════════ -->
    <Transition name="wizard-fade">
      <div class="modal-backdrop modal-backdrop--top" v-if="showCreatePackage" @click.self="showCreatePackage = false">
        <div class="modal modal--package">

          <div class="wizard-progress">
            <div class="wizard-progress__fill" style="width:50%"></div>
          </div>

          <div class="modal__header">
            <div class="header-left">
              <div class="step-badge">New Package</div>
              <h2 class="modal__title">Create a package for this offer</h2>
              <p class="step-sub">Hidden from public listings — exclusive to this offer</p>
            </div>
            <button class="modal__close" @click="showCreatePackage = false">✕</button>
          </div>

          <div class="modal__body">

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
                <label class="form-label">Duration (days)</label>
                <input type="number" min="1" class="form-input" v-model.number="newPkg.duration_days" placeholder="e.g. 7" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="newPkg.start_date" />
                  <span class="date-icon">🗓️</span>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">End Date</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="newPkg.end_date" />
                  <span class="date-icon">🗓️</span>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea class="form-input form-textarea" v-model="newPkg.description"
                placeholder="Describe this package…" />
            </div>

            <div class="form-group">
              <label class="form-label">Image URL</label>
              <input class="form-input" v-model="newPkg.img_url" placeholder="https://…" />
            </div>

            <div class="autofill-notice" v-if="form.startDate || form.endDate">
              <span>💡</span>
              <span>Offer dates have been pre-filled from your offer settings.</span>
            </div>

          </div>

          <div class="modal__footer">
            <button class="btn btn-outline" @click="showCreatePackage = false">Cancel</button>
            <button class="btn btn-coral" @click="saveNewPackage" :disabled="savingPackage">
              <span v-if="savingPackage" class="btn-spinner"></span>
              Save &amp; Add to Offer
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

const blankForm = () => ({
  title:              '',
  discount:           null,
  startDate:          '',
  endDate:            '',
  description:        '',
  type:               'Bundle',
  selectedPackageIds: [],
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

function selectOne(pkg) {
  form.value.selectedPackageIds = [pkg.id]
  existingOpen.value = false
  if (!form.value.title) form.value.title = pkg.title
}

async function fetchPackages() {
  if (!props.agencyId) return
  loadingPackages.value = true
  try {
    // Fetch both packages and services
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

/* ── Create-package sub-modal ─────────────────── */
const showCreatePackage = ref(false)
const savingPackage     = ref(false)
const pkgErrors         = ref({})

const blankPkg = () => ({
  title:        '',
  destination:  '',
  type:         'Adventure',
  price:        null,
  duration_days: null,
  start_date:   '',
  end_date:     '',
  description:  '',
  img_url:      '',
})
const newPkg = ref(blankPkg())

function openCreatePackage() {
  pkgErrors.value = {}
  newPkg.value = {
    ...blankPkg(),
    start_date: form.value.startDate || '',
    end_date:   form.value.endDate   || '',
  }
  showCreatePackage.value = true
}

function validatePkg() {
  const e = {}
  if (!newPkg.value.title?.trim())       e.title       = 'Title is required.'
  if (!newPkg.value.destination?.trim()) e.destination = 'Destination is required.'
  if (!newPkg.value.price || newPkg.value.price <= 0) e.price = 'Enter a valid price.'
  pkgErrors.value = e
  return !Object.keys(e).length
}

async function saveNewPackage() {
  if (!validatePkg()) return
  savingPackage.value = true
  try {
    const res  = await fetch('/arrivo-website/backend/api/v1/Packages.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        ...newPkg.value,
        agency_id:  props.agencyId,
        offer_only: 1,
        is_active:  1,
      }),
    })
    const data = await res.json()

    if (data.package_id) {
      const created = {
        id:            data.package_id,
        title:         newPkg.value.title,
        destination:   newPkg.value.destination,
        duration_days: newPkg.value.duration_days,
        price:         newPkg.value.price,
        img_url:       newPkg.value.img_url,
        offer_only:    1,
      }
      agencyPackages.value.unshift(created)
      form.value.selectedPackageIds.push(data.package_id)
      if (!form.value.title) form.value.title = newPkg.value.title
    }
    showCreatePackage.value = false
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
      step.value   = 1
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
          }
        : blankForm()
      
      if (props.agencyId) fetchPackages()
    }
  }
)

// Also fetch if agencyId arrives late
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
    if (!form.value.title?.trim())
      e.title = 'Title is required.'
    if (!form.value.discount || form.value.discount < 1)
      e.discount = 'Enter a valid discount (1–80).'
    if (!form.value.startDate)
      e.startDate = 'Start date is required.'
    if (!form.value.endDate)
      e.endDate = 'End date is required.'
    if (form.value.startDate && form.value.endDate && form.value.endDate < form.value.startDate)
      e.endDate = 'End date must be after start date.'
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
  if (step.value === 1) { close(); return }
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
      offerType:   'package',
      packageIds:  form.value.selectedPackageIds,
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
    form.value              = blankForm()
    errors.value            = {}
    showCreatePackage.value = false
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
  /* overflow: hidden; */ /* Removed to allow dropdowns to pop out */
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
  position: relative;
  /* Allow dropdowns to be visible outside body if needed */
  min-height: 200px;
}
.modal.step-1 .modal__body { overflow: visible; } /* Specifically allow step 1 to overflow for dropdown */

.modal__footer {
  padding: 14px 26px; border-top: 1px solid var(--gray-100);
  display: flex; justify-content: space-between; gap: 10px; flex-shrink: 0;
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

.divider {
  display: flex; align-items: center; gap: 10px;
  color: var(--gray-400); font-size: .78rem;
}
.divider::before, .divider::after {
  content: ''; flex: 1; height: 1px; background: var(--gray-200);
}

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
  text-align: center; padding: 20px 0; color: var(--gray-500);
  font-size: .88rem; display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.empty-state span { font-size: 1.8rem; }

.pkg-list { display: flex; flex-direction: column; gap: 8px; }
.pkg-row {
  display: flex; align-items: center; gap: 12px; padding: 10px 14px;
  border-radius: 12px; border: 1.5px solid var(--gray-200);
  cursor: pointer; transition: all .15s ease;
}
.pkg-row:hover    { border-color: var(--teal); background: var(--teal-lt, #f0fafa); }
.pkg-row.selected { border-color: var(--teal); background: var(--teal-lt, #f0fafa); }
.pkg-check { accent-color: var(--teal); width: 16px; height: 16px; flex-shrink: 0; cursor: pointer; }
.pkg-thumb {
  width: 50px; height: 42px; border-radius: 8px; object-fit: cover; flex-shrink: 0;
}
.pkg-thumb--fallback {
  display: flex; align-items: center; justify-content: center;
  background: var(--gray-100); font-size: 1.2rem;
  width: 50px; height: 42px; border-radius: 8px;
}
.pkg-info  { flex: 1; min-width: 0; }
.pkg-name  { font-size: .88rem; font-weight: 600; color: var(--indigo); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pkg-meta  { font-size: .75rem; color: var(--gray-600); margin-top: 2px; }
.pkg-selected-check {
  width: 22px; height: 22px; border-radius: 50%; background: var(--teal);
  color: #fff; font-size: .7rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* Step 2 */
.selected-summary { display: flex; flex-wrap: wrap; gap: 6px; }
.summary-pill {
  background: var(--teal-lt, #f0fafa); border: 1px solid var(--teal);
  border-radius: 20px; padding: 4px 12px;
  font-size: .76rem; color: var(--indigo); font-weight: 600;
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
.form-textarea { resize: vertical; min-height: 86px; }
.form-select   { cursor: pointer; }
.field-error   { font-size: .78rem; color: var(--coral); margin: 0; }

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
.discount-preview__items { padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; }
.discount-preview__row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.dp-name   { font-size: .85rem; color: var(--indigo); font-weight: 500; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-prices { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.dp-old    { color: var(--gray-400); font-size: .82rem; }
.dp-new    { color: var(--coral); font-size: .95rem; }

.autofill-notice {
  display: flex; align-items: flex-start; gap: 8px;
  background: #fffbf0; border: 1px solid #ffe8a3; border-radius: 10px;
  padding: 10px 14px; font-size: .8rem; color: #7a5c00; line-height: 1.4;
}

/* Dropdown styles */
.existing-pkgs-wrap { display: flex; flex-direction: column; gap: 6px; }
.dropdown-container { position: relative; }
.dropdown-trigger {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  padding: 10px 16px; border-radius: 12px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  cursor: pointer; transition: all .2s ease; min-height: 52px;
}
.dropdown-trigger:hover { border-color: var(--teal); background: var(--teal-lt, #f0fafa); }
.dropdown-trigger.open { border-color: var(--teal); box-shadow: 0 0 0 4px rgba(46,196,182,.1); }

.dropdown-trigger__content { display: flex; align-items: center; gap: 12px; text-align: left; }
.trigger-icon { font-size: 1.2rem; }
.trigger-placeholder { color: var(--gray-400); font-size: .9rem; }
.trigger-title { font-size: .92rem; font-weight: 600; color: var(--indigo); }
.dropdown-arrow { font-size: 1.1rem; color: var(--teal); transition: transform .2s; }

.dropdown-menu {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0;
  background: var(--white); border-radius: 14px;
  border: 1px solid var(--gray-200);
  box-shadow: 0 12px 48px rgba(45,49,66,.25);
  z-index: 1000; max-height: 190px; overflow-y: auto;
  padding: 8px;
}

.dropdown-list { display: flex; flex-direction: column; gap: 4px; }
.dropdown-item {
  display: flex; align-items: center; gap: 12px; padding: 10px;
  border-radius: 10px; cursor: pointer; transition: background .15s;
}
.dropdown-item:hover { background: var(--gray-50); }
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