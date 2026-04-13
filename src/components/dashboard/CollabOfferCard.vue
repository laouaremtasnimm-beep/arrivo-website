<template>
  <div class="offer-card">

    <!-- Co-brand header -->
    <div class="offer-header">
      <div class="offer-avatars">
        <div class="offer-avatar offer-avatar--a" :style="{ background: collab.partner?.color || '#2D3142' }">
          {{ initials(collab.partner?.name || '?') }}
        </div>
        <div class="offer-avatar offer-avatar--b" style="background: var(--coral)">
          {{ initials(collab.initiator?.name || 'You') }}
        </div>
      </div>
      <div class="offer-discount-badge">{{ collab.discount }}% OFF</div>
    </div>

    <!-- Title + type -->
    <div class="offer-title">{{ collab.title }}</div>
    <div class="offer-meta">
      <span v-if="collab.type" class="offer-tag">{{ collab.type }}</span>
      <span v-if="collab.startDate" class="offer-tag offer-tag--date">
        {{ formatDate(collab.startDate) }} – {{ formatDate(collab.endDate) }}
      </span>
    </div>

    <!-- Partners strip -->
    <div class="offer-partners">
      <div class="offer-partner">
        <div class="offer-partner-dot" :style="{ background: collab.partner?.color || '#aaa' }"></div>
        <span>{{ collab.partner?.name }}</span>
        <span class="offer-partner-role">{{ collab.partner?.role }}</span>
      </div>
      <span class="offer-x">×</span>
      <div class="offer-partner">
        <div class="offer-partner-dot" style="background: var(--coral)"></div>
        <span>{{ collab.initiator?.name }}</span>
        <span class="offer-partner-role">You</span>
      </div>
    </div>

    <!-- Description -->
    <p class="offer-desc">{{ collab.description }}</p>

    <!-- Footer -->
    <div class="offer-footer">
      <span class="offer-active-label">● Active</span>
      <button class="btn-end" @click="$emit('end', collab)">End collaboration</button>
    </div>

  </div>
</template>

<script setup>
defineProps({ collab: { type: Object, required: true } })
defineEmits(['end'])

function initials(name) {
  return name.split(' ').map(w => w[0]).slice(0, 2).join('')
}
function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }) }
  catch { return d }
}
</script>

<style scoped>
.offer-card {
  background: #fff; border-radius: var(--radius);
  border: 1.5px solid rgba(46,196,182,.3);
  box-shadow: var(--shadow); padding: 20px;
  display: flex; flex-direction: column; gap: 12px;
  transition: all var(--transition);
}
.offer-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

/* Header */
.offer-header { display: flex; align-items: center; justify-content: space-between; }
.offer-avatars { display: flex; }
.offer-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .72rem; font-weight: 700; color: #fff;
  border: 2px solid #fff;
}
.offer-avatar--b { margin-left: -10px; }
.offer-discount-badge {
  background: var(--coral); color: #fff;
  font-size: .78rem; font-weight: 800;
  padding: 4px 12px; border-radius: 50px; letter-spacing: .05em;
}

/* Title */
.offer-title {
  font-family: 'Fraunces', serif; font-size: 1.05rem;
  font-weight: 700; color: var(--indigo);
}
.offer-meta { display: flex; flex-wrap: wrap; gap: 6px; }
.offer-tag {
  font-size: .74rem; font-weight: 600; padding: 3px 10px;
  border-radius: 50px; background: var(--teal-lt); color: var(--teal-dk);
}
.offer-tag--date { background: var(--gray-100); color: var(--gray-600); }

/* Partners strip */
.offer-partners {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 13px; background: var(--gray-50);
  border-radius: var(--radius-sm); flex-wrap: wrap;
}
.offer-partner { display: flex; align-items: center; gap: 6px; }
.offer-partner-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.offer-partner span { font-size: .84rem; font-weight: 600; color: var(--indigo); }
.offer-partner-role { font-size: .74rem !important; font-weight: 400 !important; color: var(--gray-400) !important; }
.offer-x { color: var(--gray-400); font-weight: 700; }

/* Description */
.offer-desc { font-size: .84rem; color: var(--gray-600); line-height: 1.55; margin: 0; }

/* Footer */
.offer-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 4px; }
.offer-active-label { font-size: .78rem; font-weight: 700; color: var(--teal-dk); }
.btn-end {
  background: transparent; border: 1.5px solid var(--gray-200);
  color: var(--gray-600); padding: 6px 16px; border-radius: 50px;
  font-size: .78rem; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: all var(--transition);
}
.btn-end:hover { border-color: var(--coral); color: var(--coral); }
</style>