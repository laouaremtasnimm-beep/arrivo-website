<template>
  <div class="offer-card" :class="offer.source === 'collab' ? 'offer-card--collab' : 'offer-card--manual'">

    <!-- Collab badge -->
    <div v-if="offer.source === 'collab'" class="offer-collab-badge">
      🤝 Joint Offer
    </div>

    <div class="offer-card__top">
      <div class="offer-discount">{{ offer.discount }}% OFF</div>
      <div class="offer-card__actions">
        <!-- Collab offers can only be ended, not edited -->
        <template v-if="offer.source === 'collab'">
          <button class="icon-btn icon-btn--del" @click="$emit('delete', offer)" title="End collaboration offer">🗑️</button>
        </template>
        <template v-else>
          <button class="icon-btn" @click="$emit('edit', offer)" title="Edit">✏️</button>
          <button class="icon-btn icon-btn--del" @click="$emit('delete', offer)" title="Delete">🗑️</button>
        </template>
      </div>
    </div>

    <div class="offer-title">{{ offer.title }}</div>

    <!-- Collab partner strip -->
    <div v-if="offer.source === 'collab' && offer.partnerName" class="offer-partner-strip">
      <div class="offer-partner-dot" :style="{ background: offer.partnerColor || '#2EC4B6' }"></div>
      <span>with {{ offer.partnerName }}</span>
    </div>

    <div class="offer-dates">{{ offer.startDate }} → {{ offer.endDate }}</div>
    <p class="offer-desc">{{ offer.description }}</p>

    <div class="offer-card__footer">
      <span class="offer-status" :class="offer.active !== false ? 'active' : 'inactive'">
        {{ offer.active !== false ? '● Active' : '○ Inactive' }}
      </span>
      <span v-if="offer.type" class="offer-type-tag">{{ offer.type }}</span>
    </div>

  </div>
</template>

<script setup>
defineProps({ offer: { type: Object, required: true } })
defineEmits(['edit', 'delete'])
</script>

<style scoped>
.offer-card {
  background: var(--gray-50); border-radius: 14px;
  padding: 20px; border-left: 4px solid var(--teal);
  transition: transform var(--transition), box-shadow var(--transition);
  display: flex; flex-direction: column; gap: 0;
}
.offer-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
.offer-card--collab { border-left-color: var(--coral); background: rgba(255,90,95,.03); }
.offer-card--manual { border-left-color: var(--teal); }

/* Collab badge */
.offer-collab-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: .7rem; font-weight: 700; letter-spacing: .04em;
  text-transform: uppercase; color: var(--coral);
  background: var(--coral-lt); padding: 3px 10px;
  border-radius: 50px; margin-bottom: 10px; width: fit-content;
}

/* Top row */
.offer-card__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.offer-discount  { font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700; color: var(--coral); }
.offer-card__actions { display: flex; gap: 6px; }

.icon-btn {
  width: 28px; height: 28px; border-radius: 7px; border: none; cursor: pointer;
  background: var(--white); font-size: .82rem; transition: all var(--transition);
  display: flex; align-items: center; justify-content: center;
}
.icon-btn:hover      { background: var(--teal); }
.icon-btn--del:hover { background: #e74c3c; }

/* Partner strip */
.offer-partner-strip {
  display: flex; align-items: center; gap: 6px;
  font-size: .76rem; color: var(--gray-600); margin-bottom: 4px;
}
.offer-partner-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

.offer-title { font-weight: 600; font-size: .95rem; color: var(--indigo); margin-bottom: 4px; }
.offer-dates { font-size: .76rem; color: var(--gray-400); margin-bottom: 8px; }
.offer-desc  { font-size: .82rem; color: var(--gray-600); line-height: 1.5; flex: 1; }

.offer-card__footer { display: flex; align-items: center; justify-content: space-between; margin-top: 14px; }
.offer-status { font-size: .76rem; font-weight: 700; }
.offer-status.active   { color: #27ae60; }
.offer-status.inactive { color: var(--gray-400); }
.offer-type-tag {
  font-size: .7rem; font-weight: 600; padding: 2px 9px;
  border-radius: 50px; background: var(--teal-lt); color: var(--teal-dk);
}
</style>