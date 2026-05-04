<template>
  <div class="req-card" :class="`req-card--${collab.status}`">

    <!-- Status ribbon -->
    <div class="req-ribbon">
      <span class="req-badge" :class="`badge--${collab.status}`">
        {{ displayStatus }}
      </span>
      <div class="req-ribbon-right">
        <span v-if="collab.isCounter" class="req-counter-tag">↩ Counter proposal</span>
        <span class="req-date">{{ collab.sentDate }}</span>
      </div>
    </div>

    <!-- Partner info -->
    <div class="req-partner">
      <div class="req-avatar" :style="{ background: collab.partner?.color || '#aaa' }">
        {{ initials(collab.partner?.name || '?') }}
      </div>
      <div>
        <div class="req-partner-name">{{ collab.partner?.name }}</div>
        <div class="req-partner-role">{{ collab.partner?.role }}</div>
      </div>
      <div class="req-arrow">→</div>
      <div>
        <div class="req-partner-name req-self">{{ initiatorName }}</div>
        <div class="req-partner-role">You</div>
      </div>
    </div>

    <!-- Offer details -->
    <div class="req-body">
      <div class="req-offer-title">{{ collab.title }}</div>
      <div class="req-tags">
        <span class="req-tag req-tag--discount">{{ displayDiscount }}% off</span>
        <span v-if="collab.type"      class="req-tag req-tag--type">{{ collab.type }}</span>
        <span v-if="displayStartDate" class="req-tag req-tag--date">
          {{ formatDate(displayStartDate) }} – {{ formatDate(displayEndDate) }}
        </span>
      </div>
      <div class="req-terms">
        <button type="button" class="req-term req-term--clickable" @click="openDetails('package')">
          <span class="req-term__label">Package</span>
          <strong>{{ packageShort }}</strong>
          <small v-if="packageMeta">{{ packageMeta }}</small>
        </button>
        <button type="button" class="req-term req-term--clickable" @click="openDetails('services')">
          <span class="req-term__label">Services</span>
          <strong>{{ serviceCount }}</strong>
          <small>{{ serviceCount === 1 ? 'service' : 'services' }}</small>
        </button>
        <div class="req-term">
          <span class="req-term__label">Discount</span>
          <strong>{{ displayDiscount }}% off</strong>
        </div>
        <div class="req-term">
          <span class="req-term__label">Dates</span>
          <strong>{{ dateSummary }}</strong>
        </div>
      </div>
      <p class="req-desc" v-if="displayMessage">{{ displayMessage }}</p>
    </div>

    <!-- ── Actions ── -->
    <template v-if="showActions">
      <div class="req-actions" v-if="!counterOpen">
        <button class="btn-decline" @click="$emit('decline', collab)">Decline</button>
        <button v-if="canCounter" class="btn-counter" @click="counterOpen = true">↩ Counter</button>
        <button class="btn-accept"  @click="$emit('accept',  collab)">Accept</button>
      </div>

      <!-- Inline counter-proposal form (only for providers) -->
      <CollabCounterForm
        v-if="canCounter"
        v-model="counterOpen"
        :collab="collab"
        @submitted="handleCounter"
      />
    </template>

    <!-- Resolved / Awaiting state -->
    <div v-else class="req-resolved">
      <span v-if="collab.status === 'accepted'" class="resolved-text resolved-text--accepted">
        ✓ Accepted — collaboration is now active
      </span>
      <span v-else-if="collab.status === 'countered'" class="resolved-text resolved-text--countered">
        ↩ Counter proposal sent — awaiting response
      </span>
      <span v-else class="resolved-text resolved-text--declined">
        ✗ Declined
      </span>
    </div>

    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="detailModal" class="detail-modal-backdrop" @click.self="detailModal = null">
          <div class="detail-modal">
            <div class="detail-modal__header">
              <div class="detail-modal__deal">
                <div class="detail-modal__discount">{{ displayDiscount }}% OFF</div>
                <div>
                  <h3>{{ collab.title }}</h3>
                  <p v-if="dateSummary !== 'No dates set'">Ends {{ formatFullDate(displayEndDate) || formatFullDate(displayStartDate) }}</p>
                </div>
              </div>
              <button class="detail-modal__close" type="button" @click="detailModal = null">X</button>
            </div>

            <div class="detail-modal__intro">
              <p>{{ detailIntro }}</p>
              <div class="detail-modal__trust">
                <span>Verified deal</span>
                <span>Partner proposal</span>
                <span>Flexible terms</span>
              </div>
              <div class="detail-section-title">
                {{ detailModal === 'package' ? 'Package included in this offer' : 'Services included in this offer' }}
              </div>
            </div>

            <div class="detail-modal__body" v-if="detailModal === 'package'">
              <article class="detail-card">
                <img v-if="packageImage" :src="packageImage" alt="" />
                <div v-else class="detail-card__fallback">🧳</div>
                <div class="detail-card__content">
                  <div class="detail-card__label">Package</div>
                  <h4>{{ packageShort }}</h4>
                  <p v-if="packageMeta">{{ packageMeta }}</p>
                  <p v-if="collab.packagePrice">Original price</p>
                  <strong v-if="collab.packagePrice">${{ Number(collab.packagePrice).toLocaleString() }}</strong>
                </div>
              </article>
            </div>

            <div class="detail-modal__body detail-modal__grid" v-else>
              <article v-for="svc in serviceList" :key="svc.id || svc.title" class="detail-card">
                <img v-if="serviceImage(svc)" :src="serviceImage(svc)" alt="" />
                <div v-else class="detail-card__fallback">{{ serviceIcon(svc) }}</div>
                <div class="detail-card__content">
                  <div class="detail-card__label">Service</div>
                  <h4>{{ svc.title }}</h4>
                  <p v-if="svc.type">{{ svc.type }}</p>
                  <strong v-if="svc.price">${{ Number(svc.price).toLocaleString() }} / {{ svc.price_unit || 'unit' }}</strong>
                </div>
              </article>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import CollabCounterForm from '@/components/dashboard/CollabCounterForm.vue'

const props = defineProps({
  collab: { type: Object, required: true },
})
const emit = defineEmits(['accept', 'decline', 'counter'])

const counterOpen = ref(false)
const detailModal = ref(null)

const statusLabel = {
  pending:   'Pending',
  accepted:  'Accepted',
  declined:  'Declined',
  countered: 'Counter',
  ended:     'Ended',
}[props.collab.status] || ''

const isIncoming = props.collab.direction === 'incoming' // Provider side
const isOutgoing = props.collab.direction === 'outgoing' // Agency side

const displayStatus = computed(() => {
  if (props.collab.status === 'countered') {
    return isOutgoing ? 'Counter Received' : 'Counter Sent'
  }
  return statusLabel
})

/**
 * Agencies (outgoing) can respond to counters.
 * Providers (incoming) can respond to pending requests.
 */
const showActions = computed(() => {
  if (props.collab.status === 'pending'   && isIncoming) return true
  if (props.collab.status === 'countered' && isOutgoing) return true
  return false
})

/** Only Providers can send a counter-proposal in this flow */
const canCounter = computed(() => props.collab.status === 'pending' && isIncoming)

// ── Terms Resolution (Show counter terms if active) ──
const displayDiscount = computed(() => props.collab.counterData?.discount_pct ?? props.collab.discount)
const displayStartDate = computed(() => props.collab.counterData?.start_date  ?? props.collab.startDate)
const displayEndDate   = computed(() => props.collab.counterData?.end_date    ?? props.collab.endDate)
const displayMessage   = computed(() => props.collab.counterData?.message     ?? props.collab.description)
const serviceList = computed(() =>
  Array.isArray(props.collab.services) && props.collab.services.length
    ? props.collab.services
    : props.collab.serviceTitle ? [{ title: props.collab.serviceTitle }] : []
)
const serviceSummary = computed(() => {
  const titles = serviceList.value.map(s => s.title).filter(Boolean)
  if (!titles.length) return 'Service not specified'
  if (titles.length <= 2) return titles.join(', ')
  return `${titles.slice(0, 2).join(', ')} +${titles.length - 2} more`
})
const packageSummary = computed(() => {
  const parts = [props.collab.packageDestination, props.collab.packageDurationDays ? `${props.collab.packageDurationDays}d` : null].filter(Boolean)
  return parts.length && props.collab.packageTitle
    ? `${props.collab.packageTitle} (${parts.join(' · ')})`
    : props.collab.packageTitle || 'Package not specified'
})
const packageShort = computed(() => props.collab.packageTitle || 'Package')
const packageImage = computed(() =>
  props.collab.packageImg ||
  props.collab.package_img_url ||
  props.collab.img_url ||
  ''
)
const packageMeta = computed(() =>
  [props.collab.packageDestination, props.collab.packageDurationDays ? `${props.collab.packageDurationDays}d` : null]
    .filter(Boolean)
    .join(' · ')
)
const serviceCount = computed(() => serviceList.value.length || 0)
const dateSummary = computed(() =>
  displayStartDate.value
    ? [formatDate(displayStartDate.value), formatDate(displayEndDate.value)].filter(Boolean).join(' - ')
    : 'No dates set'
)
const detailIntro = computed(() =>
  detailModal.value === 'package'
    ? `Collaboration proposed around ${packageShort.value}.`
    : `${serviceCount.value} ${serviceCount.value === 1 ? 'service is' : 'services are'} proposed for ${packageShort.value}.`
)

const initiatorName = props.collab.initiator?.name || 'Partner'

function initials(name) {
  return name.split(' ').map(w => w[0]).slice(0, 2).join('')
}

function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }) }
  catch { return d }
}

function formatFullDate(d) {
  if (!d) return ''
  try { return new Date(d).toISOString().split('T')[0] }
  catch { return d }
}

function handleCounter(payload) {
  counterOpen.value = false
  emit('counter', { original: props.collab, counter: payload })
}

function openDetails(type) {
  if (type === 'services' && serviceCount.value === 0) return
  detailModal.value = type
}

function serviceImage(svc) {
  return svc?.img_url || svc?.service_img_url || svc?.image || ''
}

function serviceIcon(svc) {
  return svc?.icon || svc?.service_icon || '🛎️'
}
</script>

<style scoped>
.req-card {
  background: var(--gray-50); border-radius: 14px;
  border: 1.5px solid var(--gray-200);
  border-left: 4px solid var(--teal);
  box-shadow: none; padding: 18px;
  display: flex; flex-direction: column; gap: 14px;
  transition: transform var(--transition), box-shadow var(--transition);
}
.req-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
.req-card--pending   { border-left-color: var(--coral); background: rgba(255,90,95,.03); }
.req-card--accepted  { border-left-color: var(--teal); background: rgba(46,196,182,.04); }
.req-card--declined  { border-color: var(--gray-200); opacity: .7; }
.req-card--countered { border-left-color: var(--indigo); background: rgba(45,49,66,.03); }

/* Ribbon */
.req-ribbon { display: flex; align-items: center; justify-content: space-between; }
.req-ribbon-right { display: flex; align-items: center; gap: 8px; }
.req-badge {
  font-size: .73rem; font-weight: 700; padding: 3px 10px; border-radius: 50px;
  text-transform: uppercase; letter-spacing: .04em;
}
.badge--pending   { background: rgba(255,90,95,.12);  color: var(--coral); }
.badge--accepted  { background: rgba(46,196,182,.15); color: var(--teal-dk); }
.badge--declined  { background: var(--gray-100);      color: var(--gray-600); }
.badge--countered { background: rgba(45,49,66,.08);  color: var(--indigo); }
.badge--ended     { background: var(--gray-100);      color: var(--gray-400); }
.req-counter-tag {
  font-size: .7rem; font-weight: 700; color: var(--indigo);
  background: rgba(45,49,66,.08); padding: 2px 8px; border-radius: 50px;
}
.req-date { font-size: .78rem; color: var(--gray-400); }

/* Partner row */
.req-partner {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; background: var(--white); border-radius: var(--radius-sm);
  border: 1px solid var(--gray-100);
}
.req-avatar {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .76rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.req-partner-name { font-size: .88rem; font-weight: 600; color: var(--indigo); }
.req-partner-role { font-size: .76rem; color: var(--gray-600); }
.req-self         { color: var(--teal-dk); }
.req-arrow        { color: var(--gray-400); font-size: 1.1rem; margin: 0 4px; }

/* Body */
.req-offer-title { font-size: 1rem; font-weight: 700; color: var(--indigo); }
.req-tags        { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.req-tag         { font-size: .75rem; padding: 3px 10px; border-radius: 50px; font-weight: 600; }
.req-tag--discount { background: var(--coral-lt); color: var(--coral); }
.req-tag--type     { background: var(--teal-lt);  color: var(--teal-dk); }
.req-tag--date     { background: var(--gray-100); color: var(--gray-600); }
.req-desc          { font-size: .85rem; color: var(--gray-600); margin: 8px 0 0; line-height: 1.55; }
.req-terms {
  display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 6px; margin-top: 8px;
}
.req-term {
  padding: 7px 8px; border-radius: 9px;
  background: var(--white); border: 1px solid var(--gray-200);
  min-width: 0; text-align: left; font-family: 'DM Sans', sans-serif;
}
.req-term--clickable { cursor: pointer; transition: border-color .18s ease, background .18s ease; }
.req-term--clickable:hover { border-color: var(--teal); background: var(--teal-lt); }
.req-term__label {
  display: block; font-size: .58rem; font-weight: 800;
  color: var(--gray-500); text-transform: uppercase; letter-spacing: .04em;
  margin-bottom: 1px;
}
.req-term strong {
  display: block; font-size: .78rem; color: var(--indigo);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.req-term small {
  display: block; font-size: .68rem; color: var(--gray-500);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Actions */
.req-actions { display: flex; gap: 8px; justify-content: flex-end; }
.btn-accept {
  background: var(--teal); color: #fff; border: none;
  padding: 9px 20px; border-radius: 50px; font-size: .84rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition);
}
.btn-accept:hover { background: var(--teal-dk); }
.btn-counter {
  background: rgba(45,49,66,.06); color: var(--indigo);
  border: 1.5px solid rgba(45,49,66,.12); padding: 9px 18px;
  border-radius: 50px; font-size: .84rem; font-weight: 700;
  cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: all var(--transition);
}
.btn-counter:hover { background: rgba(45,49,66,.1); border-color: rgba(45,49,66,.2); }
.btn-decline {
  background: transparent; color: var(--gray-600);
  border: 1.5px solid var(--gray-200); padding: 9px 18px;
  border-radius: 50px; font-size: .84rem; cursor: pointer;
  font-family: 'DM Sans', sans-serif; transition: all var(--transition);
}
.btn-decline:hover { border-color: var(--coral); color: var(--coral); }

/* Resolved */
.req-resolved    { text-align: right; }
.resolved-text   { font-size: .82rem; font-weight: 600; }
.resolved-text--accepted  { color: var(--teal-dk); }
.resolved-text--countered { color: var(--indigo); }
.resolved-text--declined  { color: var(--gray-400); }

.detail-modal-backdrop {
  position: fixed; inset: 0; z-index: 500;
  background: rgba(45,49,66,.48);
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.detail-modal {
  width: min(720px, 100%); max-height: 86vh; overflow: hidden;
  background: var(--white); border-radius: 22px;
  box-shadow: 0 20px 72px rgba(45,49,66,.22);
  display: flex; flex-direction: column;
}
.detail-modal__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 22px 26px 18px;
  border-bottom: 1px solid var(--gray-100);
  background: var(--white);
  color: var(--indigo);
}
.detail-modal__deal { display: flex; align-items: flex-start; gap: 24px; min-width: 0; }
.detail-modal__discount {
  font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 5vw, 2.6rem);
  line-height: 1; font-weight: 800; color: var(--coral);
  white-space: nowrap;
}
.detail-modal__header h3 {
  margin: 0; font-size: 1.1rem; color: var(--indigo);
  font-family: 'Fraunces', serif;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  max-width: 390px;
}
.detail-modal__header p {
  margin: 6px 0 0; font-size: .82rem; color: var(--gray-500);
}
.detail-modal__close {
  width: 32px; height: 32px; border-radius: 50%; border: 0;
  background: var(--gray-100); color: var(--gray-600);
  cursor: pointer; font-weight: 700; font-size: .82rem;
  flex-shrink: 0;
}
.detail-modal__intro {
  padding: 20px 26px 0;
}
.detail-modal__intro p {
  margin: 0 0 16px; color: var(--gray-600);
  font-size: .9rem; line-height: 1.6;
}
.detail-modal__trust {
  display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
  background: var(--gray-50); border-radius: 10px;
  padding: 12px 14px; color: var(--gray-600); font-size: .8rem;
  margin-bottom: 18px;
}
.detail-modal__trust span::before {
  content: "✓"; color: #39b77a; font-weight: 900; margin-right: 8px;
}
.detail-section-title {
  font-size: .72rem; font-weight: 800; letter-spacing: .08em;
  text-transform: uppercase; color: var(--gray-600);
}
.detail-modal__body {
  padding: 18px 26px 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px;
}
.detail-modal__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); }
.detail-card {
  display: flex; flex-direction: column;
  border: 1px solid var(--gray-200); border-radius: 10px;
  background: var(--gray-50); min-width: 0; overflow: hidden;
  width: min(232px, 100%);
}
.detail-card img,
.detail-card__fallback {
  width: 100%; height: 126px; object-fit: cover;
}
.detail-card__fallback {
  background: var(--gray-200); display: flex; align-items: center; justify-content: center;
  color: var(--gray-600); font-size: .9rem; font-weight: 800;
}
.detail-card__content { min-width: 0; padding: 14px 15px 15px; }
.detail-card__label {
  font-size: .7rem; font-weight: 800; color: var(--teal);
  text-transform: uppercase; letter-spacing: .05em;
}
.detail-card h4 {
  margin: 6px 0 8px; font-size: .95rem; color: var(--indigo);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.detail-card p { margin: 0 0 8px; font-size: .82rem; color: var(--gray-500); }
.detail-card strong { font-size: .95rem; color: var(--coral); }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity .18s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

@media (max-width: 560px) {
  .req-terms { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .detail-modal { border-radius: 20px; }
  .detail-modal__header { padding: 24px 22px 28px; }
  .detail-modal__deal { flex-direction: column; gap: 10px; }
  .detail-modal__header h3 { white-space: normal; max-width: none; }
  .detail-modal__intro { padding: 24px 22px 0; }
  .detail-modal__body { padding: 22px; }
  .detail-card { width: 100%; }
}
</style>
