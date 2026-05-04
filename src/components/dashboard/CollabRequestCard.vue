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

const initiatorName = props.collab.initiator?.name || 'Partner'

function initials(name) {
  return name.split(' ').map(w => w[0]).slice(0, 2).join('')
}

function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }) }
  catch { return d }
}

function handleCounter(payload) {
  counterOpen.value = false
  emit('counter', { original: props.collab, counter: payload })
}
</script>

<style scoped>
.req-card {
  background: #fff; border-radius: var(--radius);
  border: 1.5px solid var(--gray-200);
  box-shadow: var(--shadow); padding: 20px;
  display: flex; flex-direction: column; gap: 14px;
  transition: box-shadow var(--transition);
}
.req-card:hover { box-shadow: var(--shadow-md); }
.req-card--accepted  { border-color: rgba(46,196,182,.4);  background: rgba(46,196,182,.03); }
.req-card--declined  { border-color: var(--gray-200); opacity: .7; }
.req-card--countered { border-color: rgba(255,165,0,.35);  background: rgba(255,165,0,.03); }

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
.badge--countered { background: rgba(255,165,0,.15);  color: #b37400; }
.badge--ended     { background: var(--gray-100);      color: var(--gray-400); }
.req-counter-tag {
  font-size: .7rem; font-weight: 700; color: #b37400;
  background: rgba(255,165,0,.12); padding: 2px 8px; border-radius: 50px;
}
.req-date { font-size: .78rem; color: var(--gray-400); }

/* Partner row */
.req-partner {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; background: var(--gray-50); border-radius: var(--radius-sm);
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
  background: rgba(255,165,0,.1); color: #b37400;
  border: 1.5px solid rgba(255,165,0,.3); padding: 9px 18px;
  border-radius: 50px; font-size: .84rem; font-weight: 700;
  cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: all var(--transition);
}
.btn-counter:hover { background: rgba(255,165,0,.18); border-color: rgba(255,165,0,.5); }
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
.resolved-text--countered { color: #b37400; }
.resolved-text--declined  { color: var(--gray-400); }
</style>