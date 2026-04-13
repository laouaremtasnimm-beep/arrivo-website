<template>
  <div class="req-card" :class="`req-card--${collab.status}`">

    <!-- Status ribbon -->
    <div class="req-ribbon">
      <span class="req-badge" :class="`badge--${collab.status}`">
        {{ statusLabel }}
      </span>
      <span class="req-date">{{ collab.sentDate }}</span>
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
        <span class="req-tag req-tag--discount">{{ collab.discount }}% off</span>
        <span v-if="collab.type" class="req-tag req-tag--type">{{ collab.type }}</span>
        <span v-if="collab.startDate" class="req-tag req-tag--date">
          {{ formatDate(collab.startDate) }} – {{ formatDate(collab.endDate) }}
        </span>
      </div>
      <p class="req-desc">{{ collab.description }}</p>
    </div>

    <!-- Actions — only if pending -->
    <div v-if="collab.status === 'pending'" class="req-actions">
      <button class="btn-decline" @click="$emit('decline', collab)">Decline</button>
      <button class="btn-accept"  @click="$emit('accept',  collab)">Accept &amp; Activate</button>
    </div>

    <!-- Accepted / Declined state -->
    <div v-else class="req-resolved">
      <span v-if="collab.status === 'accepted'" class="resolved-text resolved-text--accepted">
        ✓ Accepted — collaboration is now active
      </span>
      <span v-else class="resolved-text resolved-text--declined">
        ✗ Declined
      </span>
    </div>

  </div>
</template>

<script setup>
const props = defineProps({
  collab: { type: Object, required: true },
})
defineEmits(['accept', 'decline'])

const statusLabel = { pending: 'Pending', accepted: 'Accepted', declined: 'Declined' }[props.collab.status] || ''

const initiatorName = props.collab.initiator?.name || 'Partner'

function initials(name) {
  return name.split(' ').map(w => w[0]).slice(0, 2).join('')
}

function formatDate(d) {
  if (!d) return ''
  try {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
  } catch { return d }
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
.req-card--accepted { border-color: rgba(46,196,182,.4); background: rgba(46,196,182,.03); }
.req-card--declined { border-color: var(--gray-200); opacity: .7; }

/* Ribbon */
.req-ribbon { display: flex; align-items: center; justify-content: space-between; }
.req-badge {
  font-size: .73rem; font-weight: 700; padding: 3px 10px; border-radius: 50px;
  text-transform: uppercase; letter-spacing: .04em;
}
.badge--pending  { background: rgba(255,90,95,.12); color: var(--coral); }
.badge--accepted { background: rgba(46,196,182,.15); color: var(--teal-dk); }
.badge--declined { background: var(--gray-100); color: var(--gray-600); }
.req-date { font-size: .78rem; color: var(--gray-400); }

/* Partner row */
.req-partner {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; background: var(--gray-50);
  border-radius: var(--radius-sm);
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

/* Offer body */
.req-offer-title  { font-size: 1rem; font-weight: 700; color: var(--indigo); }
.req-tags         { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.req-tag          { font-size: .75rem; padding: 3px 10px; border-radius: 50px; font-weight: 600; }
.req-tag--discount { background: var(--coral-lt); color: var(--coral); }
.req-tag--type     { background: var(--teal-lt); color: var(--teal-dk); }
.req-tag--date     { background: var(--gray-100); color: var(--gray-600); }
.req-desc          { font-size: .85rem; color: var(--gray-600); margin: 8px 0 0; line-height: 1.55; }

/* Actions */
.req-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-accept {
  background: var(--teal); color: #fff; border: none;
  padding: 9px 22px; border-radius: 50px; font-size: .85rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition);
}
.btn-accept:hover { background: var(--teal-dk); }
.btn-decline {
  background: transparent; color: var(--gray-600);
  border: 1.5px solid var(--gray-200); padding: 9px 18px;
  border-radius: 50px; font-size: .85rem; cursor: pointer;
  font-family: 'DM Sans', sans-serif; transition: all var(--transition);
}
.btn-decline:hover { border-color: var(--coral); color: var(--coral); }

/* Resolved */
.req-resolved    { text-align: right; }
.resolved-text   { font-size: .82rem; font-weight: 600; }
.resolved-text--accepted { color: var(--teal-dk); }
.resolved-text--declined { color: var(--gray-400); }
</style>