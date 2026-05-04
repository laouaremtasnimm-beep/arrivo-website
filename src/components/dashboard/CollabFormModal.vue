<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="modelValue" class="modal-backdrop" @click.self="close">
        <div class="modal">

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
               STEP 1 — Offer details
          ══════════════════════════════════════════ -->
          <div class="modal__body" v-if="step === 1">

            <!--
              Locked asset banner.
              Agency flow   → shows the SERVICE that was clicked on ServiceDetail.
              Provider flow → shows the PACKAGE that was clicked on PackageDetail.
            -->
            <div v-if="lockedAsset" class="locked-asset">
              <div class="locked-asset__img-wrap">
                <img
                  v-if="lockedAsset.img_url"
                  :src="lockedAsset.img_url"
                  class="locked-asset__img"
                  alt=""
                />
                <div v-else class="locked-asset__icon">
                  {{ isAgency ? (lockedAsset.icon || '🔧') : '🧳' }}
                </div>
              </div>
              <div class="locked-asset__info">
                <div class="locked-asset__label">
                  {{ isAgency ? 'Service locked in' : 'Package locked in' }}
                </div>
                <div class="locked-asset__title">{{ lockedAsset.title }}</div>
                <div class="locked-asset__meta">
                  <template v-if="isAgency">
                    {{ lockedAsset.type }} · {{ lockedAsset.provider_name || lockedAsset.providerName }}
                  </template>
                  <template v-else>
                    {{ lockedAsset.destination }} · {{ lockedAsset.duration_days }}d ·
                    ${{ Number(lockedAsset.price).toLocaleString() }}
                  </template>
                </div>
              </div>
              <span class="lock-icon">🔒</span>
            </div>

            <!-- Joint offer title -->
            <div class="form-group">
              <label class="form-label">Joint offer title *</label>
              <input
                class="form-input"
                v-model="form.title"
                placeholder="e.g. Alps Fly & Drive Combo"
              />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <!-- Discount + type -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Discount (%) *</label>
                <div class="discount-input-wrap">
                  <input
                    type="number" min="1" max="80"
                    class="form-input"
                    v-model.number="form.discount_pct"
                    placeholder="e.g. 20"
                  />
                  <span class="discount-suffix">% off</span>
                </div>
                <p class="field-error" v-if="errors.discount_pct">{{ errors.discount_pct }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">Offer type</label>
                <select v-model="form.offer_type" class="form-input form-select">
                  <option value="Bundle">📦 Bundle</option>
                  <option value="Seasonal">🌸 Seasonal</option>
                  <option value="Flash Sale">⚡ Flash Sale</option>
                  <option value="Loyalty">💎 Loyalty</option>
                </select>
              </div>
            </div>

            <!-- Dates -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start date</label>
                <div class="date-wrap">
                  <input type="date" class="form-input" v-model="form.start_date" :min="today" />
                  <span class="date-icon">🗓️</span>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">End date</label>
                <div class="date-wrap">
                  <input
                    type="date" class="form-input"
                    v-model="form.end_date"
                    :min="form.start_date || today"
                  />
                  <span class="date-icon">🗓️</span>
                </div>
                <p class="field-error" v-if="errors.end_date">{{ errors.end_date }}</p>
              </div>
            </div>

            <!-- Proposal message -->
            <div class="form-group">
              <label class="form-label">Proposal message *</label>
              <textarea
                class="form-input form-textarea"
                v-model="form.message"
                :placeholder="messagePlaceholder"
                rows="3"
              />
              <p class="field-error" v-if="errors.message">{{ errors.message }}</p>
            </div>

          </div>

          <!-- ══════════════════════════════════════════
               STEP 2 — Pick your asset
               Agency   → pick their PACKAGE
               Provider → pick their SERVICE
          ══════════════════════════════════════════ -->
          <div class="modal__body" v-else-if="step === 2">

            <div class="form-group">
              <label class="form-label">{{ pickerLabel }} *</label>
              <div class="dropdown-container">
                <button
                  class="dropdown-trigger"
                  :class="{ open: pickerOpen }"
                  type="button"
                  @click="pickerOpen = !pickerOpen"
                >
                  <div class="dropdown-trigger__content">
                    <span class="trigger-icon">{{ isAgency ? '🧳' : '🔧' }}</span>
                    <div class="trigger-text">
                      <div class="trigger-title"       v-if="selectedAssetLabel">{{ selectedAssetLabel }}</div>
                      <div class="trigger-placeholder" v-else>{{ pickerPlaceholder }}</div>
                    </div>
                  </div>
                  <span class="dropdown-arrow">{{ pickerOpen ? '▴' : '▾' }}</span>
                </button>

                <Transition name="dropdown-slide">
                  <div class="dropdown-menu" v-if="pickerOpen">
                    <div v-if="loadingAssets" class="loading-row">
                      <span class="spinner"></span> Loading…
                    </div>
                    <div v-else-if="myAssets.length === 0" class="empty-state">
                      <p>No active {{ isAgency ? 'packages' : 'services' }} found.</p>
                      <a
                        :href="isAgency ? '/dashboard/packages/new' : '/dashboard/services/new'"
                        class="empty-link"
                      >
                        Create one →
                      </a>
                    </div>
                    <div v-else class="dropdown-list">
                      <div
                        v-for="asset in myAssets"
                        :key="asset.id"
                        class="dropdown-item"
                        :class="{ selected: isAssetSelected(asset) }"
                        @click="selectAsset(asset)"
                      >
                        <div class="item-img">
                          <img v-if="asset.img_url" :src="asset.img_url" alt="" />
                          <div v-else class="item-img--fallback">{{ isAgency ? '🧳' : '🔧' }}</div>
                        </div>
                        <div class="item-info">
                          <div class="item-name">{{ asset.title }}</div>
                          <!-- Package meta (agency picks package) -->
                          <div class="item-meta" v-if="isAgency">
                            {{ asset.destination }} · {{ asset.duration_days }}d ·
                            ${{ Number(asset.price).toLocaleString() }}
                          </div>
                          <!-- Service meta (provider picks service) -->
                          <div class="item-meta" v-else>
                            {{ asset.type }} · {{ asset.location || asset.city || '—' }}
                          </div>
                        </div>
                        <div class="item-check" v-if="isAssetSelected(asset)">✓</div>
                      </div>
                    </div>
                  </div>
                </Transition>
              </div>
              <p class="field-error" v-if="errors.asset_id">{{ errors.asset_id }}</p>
            </div>

            <!--
              Discount preview — always shows a package price.
              Agency flow:   the package they just picked (selectedAsset).
              Provider flow: the package that was locked in (lockedAsset).
            -->
            <div class="discount-preview" v-if="previewPackage && form.discount_pct">
              <div class="discount-preview__label">Price preview with discount</div>
              <div class="discount-preview__items">
                <div class="discount-preview__row">
                  <span class="dp-name">{{ previewPackage.title }}</span>
                  <span class="dp-prices">
                    <s class="dp-old">${{ Number(previewPackage.price).toLocaleString() }}</s>
                    <strong class="dp-new">${{ discountedPrice }}</strong>
                    <span class="dp-badge">-{{ form.discount_pct }}%</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Proposal summary -->
            <div class="proposal-summary" v-if="lockedAsset">
              <div class="proposal-summary__row">
                <span class="ps-label">{{ isAgency ? 'Service' : 'Package' }}</span>
                <span class="ps-value">{{ lockedAsset.title }}</span>
              </div>
              <div class="proposal-summary__row" v-if="selectedAssetLabel">
                <span class="ps-label">{{ isAgency ? 'Package' : 'Service' }}</span>
                <span class="ps-value">{{ selectedAssetLabel }}</span>
              </div>
              <div class="proposal-summary__row">
                <span class="ps-label">Discount</span>
                <span class="ps-value ps-value--highlight">{{ form.discount_pct }}% off</span>
              </div>
              <div class="proposal-summary__row" v-if="form.start_date && form.end_date">
                <span class="ps-label">Dates</span>
                <span class="ps-value">{{ form.start_date }} → {{ form.end_date }}</span>
              </div>
            </div>

          </div>

          <!-- ── Footer ── -->
          <div class="modal__footer">
            <button type="button" class="btn btn-outline" @click="back">
              {{ step === 1 ? 'Cancel' : '← Back' }}
            </button>
            <div class="footer-right">
              <span class="step-dots">
                <span class="dot" :class="{ active: step === 1, done: step > 1 }"></span>
                <span class="dot" :class="{ active: step === 2 }"></span>
              </span>
              <button type="button" class="btn btn-coral" @click="next" :disabled="submitting">
                <span v-if="submitting" class="btn-spinner"></span>
                {{ step === 1 ? 'Next →' : submitButtonLabel }}
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
import { useAuth } from '@/composables/useAuth'
import { useNotifications } from '@/composables/useNotifications'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
  modelValue: Boolean,

  /**
   * 'agency'   → user is an agency;   lockedAsset is a SERVICE  → step 2 picks a PACKAGE
   * 'provider' → user is a provider;  lockedAsset is a PACKAGE  → step 2 picks a SERVICE
   */
  initiatorType: {
    type:      String,
    default:   'agency',
    validator: (v) => ['agency', 'provider'].includes(v),
  },

  /**
   * The asset already decided when the user clicked Collaborate.
   *
   * Agency flow  → full service object:
   *   { id, title, type, icon?, img_url?, provider_id, provider_name }
   *
   * Provider flow → full package object:
   *   { id, title, destination, duration_days, price, img_url?, agency_id }
   */
  lockedAsset: { type: Object, default: null },

  /** The logged-in user's own id (agency_id or provider_id). */
  ownId: { type: [Number, String], default: null },
})

const emit = defineEmits(['update:modelValue', 'created'])

const { user }            = useAuth()
const { push: pushNotif } = useNotifications()

const BASE = import.meta.env.VITE_API_URL ?? '/arrivo-website/backend/api/v1'

// ─── Convenience flag ─────────────────────────────────────────────────────────
const isAgency = computed(() => props.initiatorType === 'agency')

// ─── Wizard ───────────────────────────────────────────────────────────────────
const step       = ref(1)
const submitting = ref(false)
const errors     = ref({})

const progressWidth = computed(() => `${(step.value / 2) * 100}%`)

const stepTitle = computed(() =>
  step.value === 1
    ? 'Offer details'
    : isAgency.value ? 'Choose your package' : 'Choose your service'
)
const stepSub = computed(() =>
  step.value === 1
    ? 'Set the terms for your collaboration proposal'
    : isAgency.value
      ? 'Pick the package tourists will book through this deal'
      : 'Pick the service you will contribute to this deal'
)
const messagePlaceholder = computed(() =>
  isAgency.value
    ? 'Describe what each party contributes and why this partnership makes sense…'
    : 'Explain which service you want to pair with this package and what value you bring…'
)

const today = computed(() => new Date().toISOString().split('T')[0])

// ─── Form ─────────────────────────────────────────────────────────────────────
const blankForm = () => ({
  title:        '',
  discount_pct: null,
  offer_type:   'Bundle',
  start_date:   '',
  end_date:     '',
  message:      '',
  asset_id:     null, // resolved to package_id or service_id on submit
  asset_ids:    [],   // provider flow: service IDs selected for this request
})
const form = ref(blankForm())

// ─── Step-2 asset picker ──────────────────────────────────────────────────────
const myAssets      = ref([])
const loadingAssets = ref(false)
const pickerOpen    = ref(false)

const pickerLabel       = computed(() => isAgency.value ? 'Your package' : 'Your service')
const pickerPlaceholder = computed(() =>
  isAgency.value ? 'Select one of your packages…' : 'Select one of your services…'
)

const selectedAsset = computed(() =>
  myAssets.value.find(a => a.id === form.value.asset_id) ?? null
)
const submitButtonLabel = computed(() =>
  'Send Request'
)
const selectedAssets = computed(() =>
  myAssets.value.filter(a => form.value.asset_ids.includes(a.id))
)
const selectedAssetLabel = computed(() => {
  if (isAgency.value) return selectedAsset.value?.title ?? ''
  const count = selectedAssets.value.length
  if (count === 0) return ''
  if (count === 1) return selectedAssets.value[0].title
  return `${count} services selected`
})

/**
 * The package used for the discount preview widget.
 * Agency picks their own package in step 2 → use selectedAsset.
 * Provider has the package locked in already → use lockedAsset.
 */
const previewPackage = computed(() =>
  isAgency.value ? selectedAsset.value : props.lockedAsset
)

const discountedPrice = computed(() => {
  if (!previewPackage.value || !form.value.discount_pct) return '—'
  return (Number(previewPackage.value.price) * (1 - form.value.discount_pct / 100))
    .toFixed(0)
    .replace(/\B(?=(\d{3})+(?!\d))/g, ',')
})

function selectAsset (asset) {
  if (isAgency.value) {
    form.value.asset_id = asset.id
    pickerOpen.value    = false
    return
  }

  const ids = form.value.asset_ids
  form.value.asset_ids = ids.includes(asset.id)
    ? ids.filter(id => id !== asset.id)
    : [...ids, asset.id]
}

function isAssetSelected (asset) {
  return isAgency.value
    ? form.value.asset_id === asset.id
    : form.value.asset_ids.includes(asset.id)
}

async function fetchMyAssets () {
  const id = props.ownId ?? user.value?.userID ?? user.value?.id
  if (!id) return
  loadingAssets.value = true
  try {
    if (isAgency.value) {
      const res  = await fetch(`${BASE}/packages.php?agency_id=${id}`)
      const data = await res.json()
      myAssets.value = (data.packages ?? []).filter(p => p.is_active)
    } else {
      const res  = await fetch(`${BASE}/Services.php?provider_id=${id}`)
      const data = await res.json()
      myAssets.value = (data.services ?? []).filter(s => s.is_available != 0)
    }
  } catch (err) {
    console.error('CollabFormModal: failed to load assets', err)
  } finally {
    loadingAssets.value = false
  }
}

// ─── Reset on open ────────────────────────────────────────────────────────────
watch(() => props.modelValue, (open) => {
  if (!open) return
  step.value       = 1
  errors.value     = {}
  pickerOpen.value = false
  form.value       = blankForm()
  myAssets.value   = []
  fetchMyAssets()
})

// ─── Validation ───────────────────────────────────────────────────────────────
function validateStep () {
  const e = {}
  if (step.value === 1) {
    if (!form.value.title?.trim())
      e.title = 'Offer title is required.'
    if (!form.value.discount_pct || form.value.discount_pct < 1 || form.value.discount_pct > 80)
      e.discount_pct = 'Enter a valid discount between 1 and 80.'
    if (!form.value.message?.trim())
      e.message = 'Add a short proposal message.'
    if (form.value.start_date && form.value.end_date && form.value.end_date < form.value.start_date)
      e.end_date = 'End date must be after start date.'
  }
  if (step.value === 2) {
    if (isAgency.value && !form.value.asset_id)
      e.asset_id = `Please select one of your ${isAgency.value ? 'packages' : 'services'}.`
    if (!isAgency.value && form.value.asset_ids.length === 0)
      e.asset_id = 'Please select at least one of your services.'
  }
  errors.value = e
  return !Object.keys(e).length
}

// ─── Navigation ───────────────────────────────────────────────────────────────
function next () {
  if (!validateStep()) return
  if (step.value === 2) { submit(); return }
  step.value++
}
function back () {
  if (step.value === 1) { close(); return }
  errors.value = {}
  step.value--
}

// ─── Submit ───────────────────────────────────────────────────────────────────
async function submit () {
  submitting.value = true

  if (!props.lockedAsset?.id) {
    errors.value.asset_id = 'No locked asset found. Please close and reopen from the correct card.'
    submitting.value = false
    return
  }

  const myId = props.ownId ?? user.value?.userID ?? user.value?.id

  const basePayload = {
    initiator_id:   myId,
    initiator_type: isAgency.value ? 'agency' : 'provider',
    partner_id:     isAgency.value ? props.lockedAsset.provider_id : props.lockedAsset.agency_id,
    partner_type:   isAgency.value ? 'provider' : 'agency',
    title:        form.value.title,
    discount_pct: form.value.discount_pct,
    offer_type:   form.value.offer_type || 'Bundle',
    start_date:   form.value.start_date || null,
    end_date:     form.value.end_date   || null,
    message:      form.value.message,
  }

  const payload = isAgency.value
    ? {
        ...basePayload,
        service_id:  props.lockedAsset.id,
        service_ids: [props.lockedAsset.id],
        package_id:  form.value.asset_id,
      }
    : {
        ...basePayload,
        package_id:  props.lockedAsset.id,
        service_id:  form.value.asset_ids[0],
        service_ids: form.value.asset_ids,
      }

  try {
    const res  = await fetch(`${BASE}/collaborations.php`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload),
    })
    const data = await res.json()

    if (!res.ok) {
      errors.value.asset_id = data.error ?? 'Something went wrong. Please try again.'
      submitting.value = false
      return
    }

    // Notify the partner
    const senderLabel =
      user.value?.company_name ||
      `${user.value?.first_name ?? ''} ${user.value?.last_name ?? ''}`.trim() ||
      (isAgency.value ? 'An agency' : 'A provider')

    pushNotif({
      roles:        [isAgency.value ? 'provider' : 'agency'],
      targetUserId: basePayload.partner_id,
      type:         'collab',
      icon:         '🤝',
      title:        'New collaboration request',
      body:         isAgency.value || form.value.asset_ids.length === 1
        ? `${senderLabel} wants to collaborate: "${form.value.title}".`
        : `${senderLabel} wants to collaborate on ${form.value.asset_ids.length} services: "${form.value.title}".`,
      link:         '/dashboard',
      section:      'collaborations',
    })

    emit('created', data.collaboration)
    close()

  } catch (err) {
    console.error('CollabFormModal: submit error', err)
    errors.value.asset_id = 'Network error — please try again.'
  } finally {
    submitting.value = false
  }
}

// ─── Close ────────────────────────────────────────────────────────────────────
function close () {
  emit('update:modelValue', false)
  setTimeout(() => {
    step.value       = 1
    form.value       = blankForm()
    errors.value     = {}
    pickerOpen.value = false
  }, 300)
}
</script>

<style scoped>
/* ── Backdrop / shell ───────────────────────────────────────────────────────── */
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(45,49,66,.48);
  display: flex; align-items: center; justify-content: center;
  z-index: 200; padding: 20px;
}
.modal {
  background: #fff; border-radius: 22px; width: 100%; max-width: 520px;
  box-shadow: 0 20px 72px rgba(45,49,66,.22);
  max-height: 92vh; display: flex; flex-direction: column; position: relative;
}

/* ── Progress bar ───────────────────────────────────────────────────────────── */
.wizard-progress {
  height: 4px; background: var(--gray-100); flex-shrink: 0;
  border-radius: 22px 22px 0 0; overflow: hidden;
}
.wizard-progress__fill {
  height: 100%; background: linear-gradient(90deg, var(--teal), var(--coral));
  transition: width .4s cubic-bezier(.4,0,.2,1);
}

/* ── Header ─────────────────────────────────────────────────────────────────── */
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

/* ── Body ───────────────────────────────────────────────────────────────────── */
.modal__body {
  padding: 18px 26px; overflow-y: auto; flex: 1;
  display: flex; flex-direction: column; gap: 14px; min-height: 200px;
}

/* ── Footer ─────────────────────────────────────────────────────────────────── */
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

/* ── Locked asset banner ────────────────────────────────────────────────────── */
.locked-asset {
  display: flex; align-items: center; gap: 12px; padding: 12px 14px;
  border-radius: 12px; background: var(--teal-lt, #f0fafa);
  border: 1.5px solid var(--teal, #2EC4B6);
}
.locked-asset__img-wrap { flex-shrink: 0; }
.locked-asset__img {
  width: 48px; height: 48px; border-radius: 8px; object-fit: cover; display: block;
}
.locked-asset__icon {
  width: 48px; height: 48px; border-radius: 8px; background: var(--gray-100);
  display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
}
.locked-asset__info { flex: 1; }
.locked-asset__label {
  font-size: .68rem; font-weight: 700; color: var(--teal);
  text-transform: uppercase; letter-spacing: .05em;
}
.locked-asset__title { font-size: .92rem; font-weight: 600; color: var(--indigo); }
.locked-asset__meta  { font-size: .78rem; color: var(--gray-600); margin-top: 1px; }
.lock-icon { font-size: .95rem; flex-shrink: 0; }

/* ── Form primitives ────────────────────────────────────────────────────────── */
.form-group  { display: flex; flex-direction: column; gap: 5px; }
.form-label  { font-size: .82rem; font-weight: 600; color: var(--indigo); }
.form-row    { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-input  {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 10px 13px; font-size: .9rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); outline: none; background: #fff;
  transition: border-color var(--transition);
}
.form-input:focus  { border-color: var(--coral); }
.form-textarea     { resize: vertical; min-height: 86px; }
.form-select       { cursor: pointer; }
.field-error       { font-size: .78rem; color: var(--coral); margin: 0; }

/* ── Discount input ─────────────────────────────────────────────────────────── */
.discount-input-wrap { position: relative; display: flex; align-items: center; }
.discount-input-wrap .form-input { padding-right: 52px; width: 100%; }
.discount-suffix {
  position: absolute; right: 13px; font-size: .82rem;
  font-weight: 700; color: var(--gray-500); pointer-events: none;
}

/* ── Date inputs ────────────────────────────────────────────────────────────── */
.date-wrap { position: relative; }
.date-wrap .form-input { padding-right: 36px; cursor: pointer; }
.date-wrap input::-webkit-calendar-picker-indicator {
  position: absolute; right: 0; top: 0; width: 100%; height: 100%;
  opacity: 0; cursor: pointer; z-index: 1;
}
.date-icon {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  pointer-events: none; font-size: 1rem; z-index: 2;
}

/* ── Asset picker dropdown ──────────────────────────────────────────────────── */
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
.trigger-icon        { font-size: 1.2rem; }
.trigger-placeholder { color: var(--gray-400); font-size: .9rem; }
.trigger-title       { font-size: .92rem; font-weight: 600; color: var(--indigo); }
.dropdown-arrow      { font-size: 1.1rem; color: var(--teal); }

.dropdown-menu {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0;
  background: var(--white); border-radius: 14px; border: 1px solid var(--gray-200);
  box-shadow: 0 12px 48px rgba(45,49,66,.25);
  z-index: 1000; max-height: 210px; overflow-y: auto; padding: 8px;
}
.dropdown-list { display: flex; flex-direction: column; gap: 4px; }
.dropdown-item {
  display: flex; align-items: center; gap: 12px; padding: 10px;
  border-radius: 10px; cursor: pointer; transition: background .15s;
}
.dropdown-item:hover    { background: var(--gray-50, #f9f9fc); }
.dropdown-item.selected { background: var(--teal-lt, #f0fafa); }
.item-img { width: 44px; height: 36px; border-radius: 6px; overflow: hidden; flex-shrink: 0; }
.item-img img { width: 100%; height: 100%; object-fit: cover; }
.item-img--fallback {
  background: var(--gray-100); display: flex; align-items: center;
  justify-content: center; font-size: 1rem; height: 100%; width: 100%;
}
.item-info { flex: 1; min-width: 0; }
.item-name {
  font-size: .88rem; font-weight: 600; color: var(--indigo);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.item-meta  { font-size: .76rem; color: var(--gray-500); margin-top: 2px; }
.item-check { color: var(--teal); font-weight: 900; font-size: 1rem; }

/* ── Loading / empty ────────────────────────────────────────────────────────── */
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
  text-align: center; padding: 14px 0; color: var(--gray-500); font-size: .88rem;
}
.empty-link { color: var(--teal); font-weight: 600; text-decoration: none; }
.empty-link:hover { text-decoration: underline; }

/* ── Discount preview ───────────────────────────────────────────────────────── */
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
.discount-preview__row   { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.dp-name   { font-size: .85rem; color: var(--indigo); font-weight: 500; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dp-prices { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.dp-old    { color: var(--gray-400); font-size: .82rem; }
.dp-new    { color: var(--coral); font-size: .95rem; }
.dp-badge  {
  background: var(--coral); color: #fff; font-size: .68rem;
  font-weight: 800; padding: 2px 7px; border-radius: 20px;
}

/* ── Proposal summary ───────────────────────────────────────────────────────── */
.proposal-summary {
  border-radius: 12px; border: 1.5px solid var(--gray-200); overflow: hidden;
}
.proposal-summary__row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 9px 14px; border-bottom: 1px solid var(--gray-100); gap: 12px;
}
.proposal-summary__row:last-child { border-bottom: none; }
.ps-label {
  font-size: .75rem; font-weight: 700; color: var(--gray-500);
  text-transform: uppercase; letter-spacing: .04em; flex-shrink: 0;
}
.ps-value { font-size: .85rem; color: var(--indigo); font-weight: 500; text-align: right; }
.ps-value--highlight { color: var(--coral); font-weight: 700; }

/* ── Buttons ────────────────────────────────────────────────────────────────── */
.btn {
  padding: 10px 22px; border-radius: 50px; font-size: .88rem; font-weight: 700;
  cursor: pointer; font-family: 'DM Sans', sans-serif; border: none;
  transition: all var(--transition); display: flex; align-items: center; gap: 6px;
}
.btn:disabled { opacity: .55; cursor: not-allowed; }
.btn-coral { background: var(--coral); color: #fff; }
.btn-coral:hover:not(:disabled) { background: var(--coral-dk); }
.btn-outline {
  background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-600);
}
.btn-outline:hover { border-color: var(--gray-400); color: var(--indigo); }
.btn-spinner {
  width: 13px; height: 13px; border: 2px solid rgba(255,255,255,.4);
  border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Transitions ────────────────────────────────────────────────────────────── */
.modal-fade-enter-active, .modal-fade-leave-active { transition: all .22s ease; }
.modal-fade-enter-from,   .modal-fade-leave-to     { opacity: 0; transform: scale(.97); }
.dropdown-slide-enter-active, .dropdown-slide-leave-active { transition: all .2s ease; }
.dropdown-slide-enter-from,   .dropdown-slide-leave-to     { opacity: 0; transform: translateY(-10px); }
</style>
