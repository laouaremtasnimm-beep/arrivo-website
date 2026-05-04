<template>
  <div class="collab-card" :class="`collab-card--${collab.status}`">

    <!-- ── Status ribbon ── -->
    <div class="status-ribbon">
      <span class="status-dot" :class="`dot--${collab.status}`"></span>
      {{ statusLabel }}
    </div>

    <!-- ── Main content ── -->
    <div class="card-body">

      <!-- Avatars row -->
      <div class="avatars-row">
        <div class="avatar-wrap">
          <img v-if="collab.initiator_avatar" :src="collab.initiator_avatar" class="avatar" alt="" />
          <div v-else class="avatar avatar--initial" :style="{ background: colorFor(collab.initiator_id) }">
            {{ initials(collab.initiator_first_name, collab.initiator_last_name) }}
          </div>
          <span class="avatar-label">{{ collab.initiator_company || collab.initiator_first_name }}</span>
        </div>

        <div class="collab-arrow">🤝</div>

        <div class="avatar-wrap">
          <img v-if="collab.partner_avatar" :src="collab.partner_avatar" class="avatar" alt="" />
          <div v-else class="avatar avatar--initial" :style="{ background: colorFor(collab.partner_id) }">
            {{ initials(collab.partner_first_name, collab.partner_last_name) }}
          </div>
          <span class="avatar-label">{{ collab.partner_company || collab.partner_first_name }}</span>
        </div>
      </div>

      <!-- Title + meta -->
      <div class="card-info">
        <h3 class="card-title">{{ collab.title }}</h3>
        <div class="card-meta">
          <span class="meta-pill meta-pill--discount">{{ collab.discount_pct }}% off</span>
          <span class="meta-pill">{{ collab.offer_type }}</span>
          <span class="meta-pill" v-if="collab.start_date">
            {{ fmtDate(collab.start_date) }} → {{ fmtDate(collab.end_date) }}
          </span>
        </div>
      </div>

      <!-- Service + Package summary -->
      <div class="assets-row">
        <div class="asset-card">
          <div class="asset-img">
            <img v-if="collab.service_img_url" :src="collab.service_img_url" alt="" />
            <div v-else class="asset-img__fallback">{{ collab.service_icon || '🔧' }}</div>
          </div>
          <div class="asset-info">
            <div class="asset-badge">Service</div>
            <div class="asset-name">{{ collab.service_title }}</div>
            <div class="asset-meta">{{ collab.service_type }}</div>
          </div>
        </div>

        <div class="asset-plus">+</div>

        <div class="asset-card">
          <div class="asset-img">
            <img v-if="collab.package_img_url" :src="collab.package_img_url" alt="" />
            <div v-else class="asset-img__fallback">🧳</div>
          </div>
          <div class="asset-info">
            <div class="asset-badge">Package</div>
            <div class="asset-name">{{ collab.package_title }}</div>
            <div class="asset-meta">{{ collab.package_destination }} · {{ collab.package_duration_days }}d</div>
          </div>
        </div>
      </div>

      <!-- Counter-proposal banner -->
      <div class="counter-banner" v-if="collab.status === 'countered' && collab.counter_data">
        <div class="counter-banner__label">⚡ Counter-proposal from provider</div>
        <div class="counter-banner__items">
          <span v-if="collab.counter_data.discount_pct">
            Discount: <strong>{{ collab.counter_data.discount_pct }}%</strong>
            <s class="counter-old">(was {{ collab.collab_discount_pct || collab.discount_pct }}%)</s>
          </span>
          <span v-if="collab.counter_data.start_date">
            Dates: <strong>{{ fmtDate(collab.counter_data.start_date) }} → {{ fmtDate(collab.counter_data.end_date) }}</strong>
          </span>
          <span v-if="collab.counter_data.message" class="counter-msg">
            "{{ collab.counter_data.message }}"
          </span>
        </div>
      </div>

      <!-- Message -->
      <p class="card-message" v-if="collab.message && collab.status === 'pending'">
        "{{ collab.message }}"
      </p>

    </div>

    <!-- ── Actions ── -->
    <div class="card-actions" v-if="showActions">

      <!-- Provider sees pending → can accept, decline, counter -->
      <template v-if="isPartner && collab.status === 'pending'">
        <button class="action-btn action-btn--decline" @click="emit('action', { action: 'decline', collabId: collab.id })">
          Decline
        </button>
        <button class="action-btn action-btn--counter" @click="emit('action', { action: 'counter', collabId: collab.id })">
          Counter ↩
        </button>
        <button class="action-btn action-btn--accept" @click="emit('action', { action: 'accept', collabId: collab.id })">
          Accept ✓
        </button>
      </template>

      <!-- Agency sees countered → can accept or decline -->
      <template v-if="isInitiator && collab.status === 'countered'">
        <button class="action-btn action-btn--decline" @click="emit('action', { action: 'decline', collabId: collab.id })">
          Decline
        </button>
        <button class="action-btn action-btn--accept" @click="emit('action', { action: 'accept', collabId: collab.id })">
          Accept counter ✓
        </button>
      </template>

      <!-- Agency sent pending → can withdraw -->
      <template v-if="isInitiator && collab.status === 'pending'">
        <button class="action-btn action-btn--withdraw" @click="withdraw">
          Withdraw request
        </button>
      </template>

    </div>

    <!-- Accepted: link to offer -->
    <div class="accepted-footer" v-if="collab.status === 'accepted' && collab.offer_id">
      <span class="accepted-label">✅ Offer live</span>
      <a :href="`/offers/${collab.offer_id}`" class="offer-link">View offer →</a>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuth }  from '@/composables/useAuth'

const props = defineProps({
  collab:        { type: Object, required: true },
  currentUserId: { type: [Number, String], required: true },
})
const emit = defineEmits(['action'])

const BASE = import.meta.env.VITE_API_URL ?? '/arrivo-website/backend/api/v1'

const isInitiator = computed(() => Number(props.collab.initiator_id) === Number(props.currentUserId))
const isPartner   = computed(() => Number(props.collab.partner_id)   === Number(props.currentUserId))

const showActions = computed(() =>
  ['pending', 'countered'].includes(props.collab.status) && (isInitiator.value || isPartner.value)
)

const statusLabel = computed(() => ({
  pending:   'Pending response',
  countered: 'Counter-proposal received',
  accepted:  'Accepted — offer live',
  declined:  'Declined',
})[props.collab.status] ?? props.collab.status)

function initials(first, last) {
  return ((first?.[0] ?? '') + (last?.[0] ?? '')).toUpperCase() || '?'
}

const COLORS = ['#2EC4B6','#FF6B6B','#FFB347','#6C63FF','#3AAFA9','#F77F00','#E63946']
function colorFor(id) {
  return COLORS[Number(id) % COLORS.length]
}

function fmtDate(d) {
  if (!d) return ''
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}

async function withdraw() {
  try {
    await fetch(`${BASE}/collaborations.php`, {
      method:  'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ id: props.collab.id, actor_id: props.currentUserId }),
    })
    emit('action', { action: '__withdrawn', collabId: props.collab.id })
  } catch (err) {
    console.error('CollabCard withdraw error', err)
  }
}
</script>

<style scoped>
/* ── Card shell ─────────────────────────────────────────────────────────────── */
.collab-card {
  background: #fff; border-radius: 18px;
  border: 1.5px solid var(--gray-200);
  overflow: hidden; transition: box-shadow .2s ease;
}
.collab-card:hover { box-shadow: 0 6px 28px rgba(45,49,66,.1); }

.collab-card--accepted { border-color: #2EC4B6; }
.collab-card--declined { opacity: .65; }
.collab-card--countered { border-color: #FFB347; }

/* ── Status ribbon ──────────────────────────────────────────────────────────── */
.status-ribbon {
  display: flex; align-items: center; gap: 7px;
  padding: 7px 16px; font-size: .74rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .06em;
  background: var(--gray-50, #f9f9fc); border-bottom: 1px solid var(--gray-100);
  color: var(--gray-600);
}
.status-dot {
  width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
}
.dot--pending   { background: #FFB347; }
.dot--countered { background: #6C63FF; }
.dot--accepted  { background: #2EC4B6; }
.dot--declined  { background: #ccc; }

/* ── Body ───────────────────────────────────────────────────────────────────── */
.card-body { padding: 16px; display: flex; flex-direction: column; gap: 14px; }

/* ── Avatars ────────────────────────────────────────────────────────────────── */
.avatars-row {
  display: flex; align-items: center; gap: 12px;
}
.avatar-wrap {
  display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1;
}
.avatar {
  width: 44px; height: 44px; border-radius: 50%; object-fit: cover;
  border: 2px solid var(--gray-200); display: block;
}
.avatar--initial {
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: .85rem; font-weight: 800; border: none;
}
.avatar-label {
  font-size: .72rem; font-weight: 600; color: var(--gray-600);
  text-align: center; max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.collab-arrow { font-size: 1.3rem; flex-shrink: 0; }

/* ── Title ──────────────────────────────────────────────────────────────────── */
.card-info { display: flex; flex-direction: column; gap: 6px; }
.card-title {
  font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700;
  color: var(--indigo); margin: 0;
}
.card-meta { display: flex; flex-wrap: wrap; gap: 6px; }
.meta-pill {
  padding: 3px 10px; border-radius: 20px; font-size: .73rem; font-weight: 600;
  background: var(--gray-100); color: var(--gray-700);
}
.meta-pill--discount { background: var(--coral); color: #fff; }

/* ── Assets ─────────────────────────────────────────────────────────────────── */
.assets-row {
  display: flex; align-items: center; gap: 10px;
}
.asset-card {
  flex: 1; display: flex; align-items: center; gap: 10px;
  padding: 10px; border-radius: 10px; background: var(--gray-50, #f9f9fc);
  border: 1px solid var(--gray-200); min-width: 0;
}
.asset-plus { font-size: 1.1rem; color: var(--gray-400); flex-shrink: 0; }
.asset-img {
  width: 40px; height: 36px; border-radius: 6px; overflow: hidden; flex-shrink: 0;
}
.asset-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.asset-img__fallback {
  width: 100%; height: 100%; background: var(--gray-200);
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
}
.asset-info { min-width: 0; flex: 1; }
.asset-badge {
  font-size: .62rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: .06em; color: var(--teal); margin-bottom: 1px;
}
.asset-name {
  font-size: .82rem; font-weight: 600; color: var(--indigo);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.asset-meta { font-size: .72rem; color: var(--gray-500); }

/* ── Counter banner ─────────────────────────────────────────────────────────── */
.counter-banner {
  padding: 12px 14px; border-radius: 10px;
  background: #fffbf0; border: 1.5px solid #FFB347;
}
.counter-banner__label {
  font-size: .76rem; font-weight: 800; color: #c47a00; margin-bottom: 7px;
  text-transform: uppercase; letter-spacing: .04em;
}
.counter-banner__items {
  display: flex; flex-wrap: wrap; gap: 10px; font-size: .84rem; color: var(--indigo);
}
.counter-old { color: var(--gray-400); font-size: .78rem; margin-left: 4px; }
.counter-msg { font-style: italic; color: var(--gray-600); width: 100%; }

/* ── Message ────────────────────────────────────────────────────────────────── */
.card-message {
  font-size: .84rem; color: var(--gray-600); font-style: italic;
  border-left: 3px solid var(--teal); padding-left: 10px; margin: 0;
}

/* ── Actions ────────────────────────────────────────────────────────────────── */
.card-actions {
  display: flex; justify-content: flex-end; gap: 8px;
  padding: 12px 16px; border-top: 1px solid var(--gray-100);
  background: var(--gray-50, #f9f9fc);
}
.action-btn {
  padding: 8px 18px; border-radius: 50px; font-size: .82rem; font-weight: 700;
  cursor: pointer; border: none; font-family: 'DM Sans', sans-serif;
  transition: all .18s ease;
}
.action-btn--accept   { background: var(--teal); color: #fff; }
.action-btn--accept:hover   { background: #26b0a2; }
.action-btn--decline  { background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-600); }
.action-btn--decline:hover  { border-color: #e63946; color: #e63946; }
.action-btn--counter  { background: #fff8ee; border: 1.5px solid #FFB347; color: #c47a00; }
.action-btn--counter:hover  { background: #fff0d0; }
.action-btn--withdraw { background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-500); font-size: .78rem; }
.action-btn--withdraw:hover { border-color: #e63946; color: #e63946; }

/* ── Accepted footer ────────────────────────────────────────────────────────── */
.accepted-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 16px; border-top: 1px solid rgba(46,196,182,.2);
  background: rgba(46,196,182,.06);
}
.accepted-label { font-size: .8rem; font-weight: 700; color: #2EC4B6; }
.offer-link {
  font-size: .8rem; font-weight: 700; color: var(--teal); text-decoration: none;
}
.offer-link:hover { text-decoration: underline; }
</style>