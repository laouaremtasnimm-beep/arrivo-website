<template>
  <Teleport to="body">
    <Transition name="wizard-fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal" :class="`step-${step}`">

          <!-- ── Progress bar ── -->
          <div class="wizard-progress">
            <div class="wizard-progress__fill" :style="{ width: progressWidth }"></div>
          </div>

          <!-- ── Header ── -->
          <div class="modal__header">
            <div class="header-left">
              <div class="step-badge">Step {{ step }} of 3</div>
              <h2 class="modal__title">{{ stepTitle }}</h2>
            </div>
            <button class="modal__close" @click="close">✕</button>
          </div>

          <!-- ══════════════════════════════════════════
               STEP 1 — Choose offer type
          ══════════════════════════════════════════ -->
          <div class="modal__body" v-if="step === 1">
            <p class="step-hint">What kind of offer do you want to create?</p>

            <div class="type-cards">
              <button
                class="type-card"
                :class="{ selected: form.offerType === 'general' }"
                @click="form.offerType = 'general'"
              >
                <span class="type-card__icon">✨</span>
                <div class="type-card__body">
                  <div class="type-card__name">General Offer</div>
                  <div class="type-card__desc">
                    A standalone promo — discount code, seasonal deal, or flash sale.
                    No specific package attached.
                  </div>
                </div>
                <div class="type-card__check" v-if="form.offerType === 'general'">✓</div>
              </button>

              <button
                class="type-card"
                :class="{ selected: form.offerType === 'package' }"
                @click="form.offerType = 'package'"
              >
                <span class="type-card__icon">📦</span>
                <div class="type-card__body">
                  <div class="type-card__name">Package Offer</div>
                  <div class="type-card__desc">
                    Bundle one or more of your existing packages under a special
                    discount. Packages appear only inside this offer.
                  </div>
                </div>
                <div class="type-card__check" v-if="form.offerType === 'package'">✓</div>
              </button>
            </div>

            <p class="field-error center" v-if="errors.offerType">{{ errors.offerType }}</p>
          </div>

          <!-- ══════════════════════════════════════════
               STEP 2 — Offer details
          ══════════════════════════════════════════ -->
          <div class="modal__body" v-else-if="step === 2">

            <div class="form-group">
              <label class="form-label">Title *</label>
              <input class="form-input" v-model="form.title"
                :disabled="isEdit"
                :placeholder="form.offerType === 'package' ? 'e.g. Adventure Bundle Deal' : 'e.g. Early Bird Summer Special'" />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Discount (%) *</label>
                <input type="number" min="1" max="80" class="form-input"
                  v-model.number="form.discount" placeholder="e.g. 20" :disabled="isEdit" />
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
                  <input type="date" class="form-input" v-model="form.startDate" :disabled="isEdit" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.startDate">{{ errors.startDate }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">End Date *</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="form.endDate" :disabled="isEdit" />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.endDate">{{ errors.endDate }}</p>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Description *</label>
              <textarea class="form-input form-textarea" v-model="form.description"
                :placeholder="form.offerType === 'package'
                  ? 'Describe what makes this bundle special…'
                  : 'e.g. Book 3 months in advance and save big on beachfront resorts.'" />
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

          </div>

          <!-- ══════════════════════════════════════════
               STEP 3a — Pick existing packages (package offer)
               STEP 3b — Preview (general offer)
          ══════════════════════════════════════════ -->
          <div class="modal__body" v-else-if="step === 3">

            <!-- Package picker -->
            <template v-if="form.offerType === 'package'">
              <p class="step-hint">
                Select the packages to include. They will be
                <strong>hidden from normal listings</strong> and shown only inside this offer.
              </p>

              <div v-if="loadingPackages" class="loading-row">
                <span class="spinner"></span> Loading your packages…
              </div>

              <div v-else-if="agencyPackages.length === 0" class="empty-packages">
                <span>📭</span>
                <p>You have no packages yet. Create one first from the Packages tab.</p>
              </div>

              <div v-else class="pkg-list">
                <label
                  v-for="pkg in agencyPackages"
                  :key="pkg.id"
                  class="pkg-row"
                  :class="{ selected: form.selectedPackageIds.includes(pkg.id) }"
                >
                  <input
                    type="checkbox"
                    class="pkg-check"
                    :value="pkg.id"
                    v-model="form.selectedPackageIds"
                  />
                  <div class="pkg-img-wrap">
                    <img :src="pkg.img_url || 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg'" class="pkg-thumb" />
                  </div>
                  <div class="pkg-info">
                    <div class="pkg-name">{{ pkg.title }}</div>
                    <div class="pkg-meta">{{ pkg.destination }} · {{ pkg.duration_days }} days · ${{ pkg.price }}</div>
                  </div>
                  <div class="pkg-badge" v-if="form.selectedPackageIds.includes(pkg.id)">
                    -{{ form.discount }}%
                  </div>
                </label>
              </div>

              <p class="field-error" v-if="errors.packages">{{ errors.packages }}</p>
            </template>

            <!-- General offer: preview card -->
            <template v-else>
              <p class="step-hint">Here's how your offer will appear to travellers.</p>
              <div class="preview-card">
                <div class="preview-card__header">
                  <div class="preview-discount">{{ form.discount }}% OFF</div>
                  <div class="preview-card__info">
                    <div class="preview-title">{{ form.title }}</div>
                    <div class="preview-dates">🗓️ {{ form.startDate }} → {{ form.endDate }}</div>
                  </div>
                </div>
                <div class="preview-card__body">
                  <div class="preview-desc">{{ form.description }}</div>
                  <div class="preview-badges">
                    <span class="pbadge">✅ Verified deal</span>
                    <span class="pbadge">🔒 Secure booking</span>
                    <span class="pbadge">↩️ Free cancellation</span>
                  </div>
                  <p class="preview-cta-hint">
                    This is a limited-time promotional offer. Travellers will see
                    <strong>Browse packages</strong> and <strong>Browse services</strong> buttons.
                  </p>
                </div>
                <div class="preview-card__footer">
                  🔥 Limited time — offer ends {{ form.endDate }}
                </div>
              </div>
            </template>

          </div>

          <!-- ── Footer nav ── -->
          <div class="modal__footer">
            <button class="btn btn-outline" @click="back">
              {{ (step === 1 || (isEdit && step === 2)) ? 'Cancel' : '← Back' }}
            </button>
            <button class="btn btn-coral" @click="next" :disabled="submitting">
              <span v-if="submitting" class="btn-spinner"></span>
              {{ step < 3 ? 'Continue →' : (isEdit ? 'Save Changes' : 'Create Offer') }}
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

/* ── State ────────────────────────────────────── */
const step       = ref(1)
const submitting = ref(false)
const errors     = ref({})

const blankForm = () => ({
  offerType:          'general',   // 'general' | 'package'
  title:              '',
  discount:           null,
  startDate:          '',
  endDate:            '',
  description:        '',
  type:               'General',
  selectedPackageIds: [],
  img_url:            '',
})

const form = ref(blankForm())

/* ── Packages list (loaded on step 3) ─────────── */
const agencyPackages  = ref([])
const loadingPackages = ref(false)

async function fetchPackages() {
  if (!props.agencyId) return
  loadingPackages.value = true
  try {
    const res  = await fetch(`/backend/api/v1/Packages.php?agency_id=${props.agencyId}`)
    const data = await res.json()
    // Only show packages not already offer-only attached to another offer
    agencyPackages.value = (data.packages || []).filter(p => !p.offer_only || p.offer_only == 0)
  } catch (e) {
    console.error('Failed to load packages', e)
  } finally {
    loadingPackages.value = false
  }
}

/* ── Computed helpers ─────────────────────────── */
const progressWidth = computed(() => `${(step.value / 3) * 100}%`)

const isExclusive = computed(() => {
  if (!form.value.selectedPackageIds.length) return false
  // For simplicity during edit, we check if ANY of the selected packages are exclusive
  // In CollabOfferCard, usually it's existing packages, but if they were created as offer_only...
  return agencyPackages.value.some(p => 
    form.value.selectedPackageIds.includes(p.id) && 
    (p.offer_only == 1 || p.offer_only == true)
  )
})

const stepTitle = computed(() => {
  if (step.value === 1) return isEdit.value ? 'Edit Offer' : 'New Offer'
  if (step.value === 2) return 'Offer Details'
  return form.value.offerType === 'package' ? 'Choose Packages' : 'Preview'
})

/* ── Watchers ────────────────────────────────── */
watch(
  () => [props.modelValue, props.offer],
  ([open, offer]) => {
    if (open) {
      step.value  = offer ? 2 : 1
      errors.value = {}
      if (offer) {
        form.value = {
          offerType:          offer.offerType ?? 'general',
          title:              offer.title        ?? '',
          discount:           offer.discount     ?? null,
          startDate:          offer.startDate    ?? '',
          endDate:            offer.endDate      ?? '',
          description:        offer.description  ?? '',
          type:               offer.type         ?? 'General',
          selectedPackageIds: offer.packageIds   ?? [],
          img_url:            offer.packages?.[0]?.img_url ?? '',
        }
      } else {
        form.value = blankForm()
      }
    }
  }
)

/* ── Validation per step ─────────────────────── */
function validateStep() {
  const e = {}

  if (step.value === 1) {
    if (!form.value.offerType) e.offerType = 'Please choose an offer type.'
  }

  if (step.value === 2) {
    if (!form.value.title?.trim())               e.title       = 'Title is required.'
    if (!form.value.discount || form.value.discount < 1) e.discount = 'Enter a valid discount (1–80).'
    if (!form.value.startDate)                   e.startDate   = 'Start date is required.'
    if (!form.value.endDate)                     e.endDate     = 'End date is required.'
    if (form.value.startDate && form.value.endDate && form.value.endDate < form.value.startDate)
      e.endDate = 'End date must be after start date.'
    if (!form.value.description?.trim())         e.description = 'Description is required.'
  }

  if (step.value === 3 && form.value.offerType === 'package') {
    if (!form.value.selectedPackageIds.length) e.packages = 'Select at least one package.'
  }

  errors.value = e
  return !Object.keys(e).length
}

/* ── Navigation ──────────────────────────────── */
function next() {
  if (!validateStep()) return

  if (step.value === 3) {
    submit()
    return
  }

  step.value++

  // Load packages when entering step 3 for package offers
  if (step.value === 3 && form.value.offerType === 'package') {
    fetchPackages()
  }
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
      ...form.value,
      source:   'manual',
      offerID:  props.offer?.offerID ?? null,
      active:   true,
      // Pass package ids so parent / backend can mark them offer_only
      packageIds: form.value.offerType === 'package' ? form.value.selectedPackageIds : [],
      img_url:    form.value.img_url,
    })
    close()
  } finally {
    submitting.value = false
  }
}

function close() {
  emit('update:modelValue', false)
  // Reset after transition
  setTimeout(() => { step.value = 1; form.value = blankForm(); errors.value = {} }, 300)
}
</script>

<style scoped>
/* ── Backdrop & shell ─────────────────────────── */
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(45,49,66,.48);
  display: flex; align-items: center; justify-content: center;
  z-index: 200; padding: 20px;
}
.modal {
  background: #fff; border-radius: 22px; width: 100%; max-width: 520px;
  box-shadow: 0 20px 72px rgba(45,49,66,.22); position: relative;
  max-height: 92vh; display: flex; flex-direction: column;
  overflow: hidden;
}

/* ── Progress bar ─────────────────────────────── */
.wizard-progress {
  height: 4px; background: var(--gray-100); width: 100%; flex-shrink: 0;
}
.wizard-progress__fill {
  height: 100%; background: linear-gradient(90deg, var(--teal), var(--coral));
  transition: width .4s cubic-bezier(.4,0,.2,1);
}

/* ── Header ───────────────────────────────────── */
.modal__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 22px 28px 16px; border-bottom: 1px solid var(--gray-100);
  flex-shrink: 0;
}
.step-badge {
  font-size: .72rem; font-weight: 700; letter-spacing: .06em;
  color: var(--teal); text-transform: uppercase; margin-bottom: 4px;
}
.modal__title {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin: 0;
}
.modal__close {
  background: var(--gray-100); border: none; border-radius: 50%;
  width: 32px; height: 32px; cursor: pointer; font-size: .85rem;
  color: var(--gray-600); transition: background var(--transition); flex-shrink: 0;
}
.modal__close:hover { background: var(--gray-200); }

/* ── Body ─────────────────────────────────────── */
.modal__body {
  padding: 22px 28px; overflow-y: auto; flex: 1;
  display: flex; flex-direction: column; gap: 16px;
}
.step-hint { font-size: .88rem; color: var(--gray-600); margin: 0; line-height: 1.5; }
.step-hint strong { color: var(--indigo); }

/* ── Footer ───────────────────────────────────── */
.modal__footer {
  padding: 16px 28px; border-top: 1px solid var(--gray-100);
  display: flex; justify-content: space-between; gap: 10px; flex-shrink: 0;
}

/* ── Buttons ──────────────────────────────────── */
.btn {
  padding: 10px 24px; border-radius: 50px; font-size: .88rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  border: none; transition: all var(--transition); display: flex; align-items: center; gap: 6px;
}
.btn:disabled { opacity: .6; cursor: not-allowed; }
.btn-coral   { background: var(--coral); color: #fff; }
.btn-coral:hover:not(:disabled) { background: var(--coral-dk); }
.btn-outline { background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-600); }
.btn-outline:hover { border-color: var(--gray-400); color: var(--indigo); }

.btn-spinner {
  width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4);
  border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Step 1 — Type cards ──────────────────────── */
.type-cards { display: flex; flex-direction: column; gap: 12px; }
.type-card {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 16px 18px; border-radius: 14px;
  border: 2px solid var(--gray-200); background: #fff;
  cursor: pointer; transition: all .18s ease; text-align: left; width: 100%;
}
.type-card:hover { border-color: var(--teal); background: var(--teal-lt, #f0fafa); }
.type-card.selected { border-color: var(--teal); background: var(--teal-lt, #f0fafa); }
.type-card__icon { font-size: 1.6rem; line-height: 1; flex-shrink: 0; margin-top: 2px; }
.type-card__body { flex: 1; }
.type-card__name { font-size: .95rem; font-weight: 700; color: var(--indigo); margin-bottom: 4px; }
.type-card__desc { font-size: .82rem; color: var(--gray-600); line-height: 1.5; }
.type-card__check {
  width: 24px; height: 24px; border-radius: 50%; background: var(--teal);
  color: #fff; font-size: .75rem; display: flex; align-items: center;
  justify-content: center; flex-shrink: 0; margin-top: 2px;
}

/* ── Step 2 — Form ────────────────────────────── */
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
.form-textarea { resize: vertical; min-height: 88px; }
.form-select   { cursor: pointer; }
.field-error   { font-size: .78rem; color: var(--coral); margin: 0; }
.field-error.center { text-align: center; }

.date-wrap { position: relative; }
.date-wrap .form-input { padding-right: 36px; cursor: pointer; }
.date-wrap input::-webkit-calendar-picker-indicator {
  position: absolute; right: 0; top: 0;
  width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 1;
}
.date-icon {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  pointer-events: none; font-size: 1rem; z-index: 2;
}

/* ── Step 3 — Package picker ──────────────────── */
.loading-row {
  display: flex; align-items: center; gap: 10px;
  color: var(--gray-600); font-size: .88rem; padding: 16px 0;
}
.spinner {
  width: 16px; height: 16px; border: 2px solid var(--gray-200);
  border-top-color: var(--teal); border-radius: 50%;
  animation: spin .7s linear infinite; flex-shrink: 0;
}
.empty-packages {
  text-align: center; padding: 32px 0; color: var(--gray-600);
  font-size: .9rem; display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.empty-packages span { font-size: 2rem; }

.pkg-list { display: flex; flex-direction: column; gap: 8px; }
.pkg-row {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 14px; border-radius: 12px;
  border: 1.5px solid var(--gray-200); cursor: pointer;
  transition: all .16s ease; position: relative;
}
.pkg-row:hover   { border-color: var(--teal); background: var(--teal-lt, #f0fafa); }
.pkg-row.selected { border-color: var(--teal); background: var(--teal-lt, #f0fafa); }
.pkg-check { accent-color: var(--teal); width: 16px; height: 16px; flex-shrink: 0; cursor: pointer; }
.pkg-img-wrap { flex-shrink: 0; }
.pkg-thumb {
  width: 48px; height: 40px; border-radius: 8px; object-fit: cover;
}
.pkg-thumb--fallback {
  display: flex; align-items: center; justify-content: center;
  background: var(--gray-100); font-size: 1.2rem;
}
.pkg-info { flex: 1; min-width: 0; }
.pkg-name { font-size: .88rem; font-weight: 600; color: var(--indigo); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pkg-meta { font-size: .76rem; color: var(--gray-600); margin-top: 2px; }
.pkg-badge {
  background: var(--coral); color: #fff; border-radius: 20px;
  padding: 3px 10px; font-size: .75rem; font-weight: 700; flex-shrink: 0;
}

/* ── Step 3 — Preview card ────────────────────── */
.preview-card {
  border-radius: 16px; overflow: hidden;
  border: 1.5px solid var(--gray-200);
  box-shadow: 0 4px 24px rgba(45,49,66,.08);
}
.preview-card__header {
  background: var(--indigo); color: #fff;
  display: flex; align-items: center; gap: 20px; padding: 20px 22px;
}
.preview-discount {
  font-family: 'Fraunces', serif; font-size: 1.9rem; font-weight: 900;
  color: var(--coral); white-space: nowrap; flex-shrink: 0;
}
.preview-title  { font-size: 1rem; font-weight: 700; line-height: 1.3; }
.preview-dates  { font-size: .78rem; color: rgba(255,255,255,.65); margin-top: 4px; }
.preview-card__body { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }
.preview-desc { font-size: .88rem; color: var(--gray-700, #3d3f52); line-height: 1.55; }
.preview-badges { display: flex; flex-wrap: wrap; gap: 8px; }
.pbadge {
  background: var(--gray-100); border-radius: 20px;
  padding: 4px 12px; font-size: .76rem; color: var(--indigo); font-weight: 500;
}
.preview-cta-hint {
  font-size: .8rem; color: var(--gray-600); background: var(--gray-50, #f9f9fc);
  border-radius: 10px; padding: 10px 14px; line-height: 1.5; margin: 0;
}
.preview-cta-hint strong { color: var(--indigo); }
.preview-card__footer {
  padding: 12px 22px; border-top: 1px solid var(--gray-100);
  font-size: .8rem; color: var(--gray-600);
}

/* ── Transitions ─────────────────────────────── */
.wizard-fade-enter-active, .wizard-fade-leave-active { transition: all .24s ease; }
.wizard-fade-enter-from, .wizard-fade-leave-to { opacity: 0; transform: scale(.96); }
</style>