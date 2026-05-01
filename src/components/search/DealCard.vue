<template>
  <div
    class="deal-card"
    :class="{ 'deal-card--collab': offer.source === 'collab' }"
    @click="$emit('select', offer)"
  >
    <!-- Featured ribbon (if applicable, though search results might not use it) -->
    <div v-if="offer.featured" class="deal-ribbon">⭐ Featured</div>

    <!-- Collab badge -->
    <div v-if="offer.source === 'collab'" class="deal-collab">🤝 Joint Offer</div>

    <!-- Discount badge -->
    <div class="deal-discount">
      <span class="deal-discount__num">{{ offer.discount }}</span>
      <span class="deal-discount__pct">% OFF</span>
    </div>

    <!-- Card body -->
    <div class="deal-body">
      <div class="deal-meta">
        <span v-if="offer.type" class="deal-type">{{ offer.type }}</span>
        <span v-if="offer.startDate && offer.endDate" class="deal-dates">
          📅 {{ offer.startDate }} – {{ offer.endDate }}
        </span>
      </div>

      <h3 class="deal-title">{{ offer.title }}</h3>
      <p class="deal-desc">{{ offer.desc }}</p>

      <div v-if="offer.source === 'collab' && offer.partnerName" class="deal-partner">
        <span class="deal-partner-dot" :style="{ background: offer.partnerColor || '#2EC4B6' }" />
        with {{ offer.partnerName }}
      </div>
    </div>

    <!-- Card footer -->
    <div class="deal-footer">
      <button
        class="btn deal-cta"
        :class="booked ? 'btn-outline-danger' : 'btn-teal'"
        @click.stop="$emit('book', offer)"
      >
        {{ booked ? 'Cancel booking' : 'Grab deal →' }}
      </button>
      <!-- Save button (keeping it consistent with search card actions if possible, 
           or just as a decorative element if wishlist logic differs) -->
      <button
        v-if="showSave"
        class="deal-save"
        :class="{ saved: saved }"
        @click.stop="$emit('toggle-save', offer.id)"
      >
        {{ saved ? '♥' : '♡' }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  offer:    { type: Object,  required: true },
  saved:    { type: Boolean, default: false },
  booked:   { type: Boolean, default: false },
  showSave: { type: Boolean, default: true },
})
defineEmits(['select', 'toggle-save', 'book'])
</script>

<style scoped>
.deal-card {
  background: var(--white);
  border-radius: var(--radius);
  border-left: 4px solid var(--teal);
  box-shadow: var(--shadow);
  display: flex; flex-direction: column;
  cursor: pointer; position: relative; overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
  height: 420px;
}
.deal-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.deal-card--collab { border-left-color: var(--coral); }

/* Ribbon */
.deal-ribbon {
  position: absolute; top: 0; right: 0;
  background: #f59e0b; color: #fff;
  font-size: .7rem; font-weight: 700; letter-spacing: .04em;
  padding: 5px 14px 5px 10px;
  border-bottom-left-radius: 10px;
}

/* Collab badge */
.deal-collab {
  position: absolute; top: 12px; left: 12px;
  display: inline-flex; align-items: center; gap: 5px;
  font-size: .68rem; font-weight: 700; letter-spacing: .04em;
  text-transform: uppercase; color: var(--coral);
  background: var(--coral-lt); padding: 3px 10px; border-radius: 50px;
}

/* Discount badge */
.deal-discount {
  padding: 22px 22px 0;
  display: flex; align-items: baseline; gap: 3px;
  margin-top: 4px;
}
.deal-discount__num {
  font-family: 'Fraunces', serif;
  font-size: 3rem; font-weight: 700; line-height: 1;
  color: var(--coral);
}
.deal-discount__pct {
  font-size: .9rem; font-weight: 700; color: var(--coral); opacity: .7;
}

/* Body */
.deal-body { padding: 12px 22px 0; flex: 1; display: flex; flex-direction: column; gap: 8px; }

.deal-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.deal-type {
  font-size: .72rem; font-weight: 700; padding: 2px 9px;
  border-radius: 50px; background: var(--teal-lt); color: var(--teal-dk);
}
.deal-dates { font-size: .76rem; color: var(--gray-400); }

.deal-title {
  font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700;
  color: var(--indigo); line-height: 1.2; margin: 0;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.deal-desc {
  font-size: .84rem; color: var(--gray-600); line-height: 1.55;
  margin: 0; flex: 1;
  display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
}

.deal-partner {
  display: flex; align-items: center; gap: 7px;
  font-size: .76rem; color: var(--gray-600); margin-top: 4px;
}
.deal-partner-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* Footer */
.deal-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 22px 20px; margin-top: 8px;
  border-top: 1px solid var(--gray-100);
}
.deal-cta { padding: 9px 20px; font-size: .84rem; border-radius: 10px; width: 100%; max-width: 140px; }
.deal-save {
  width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid var(--gray-200);
  background: var(--white); font-size: 1.1rem; cursor: pointer; line-height: 1;
  display: flex; align-items: center; justify-content: center;
  color: var(--gray-400); transition: all var(--transition);
}
.deal-save:hover  { border-color: var(--coral); color: var(--coral); }
.deal-save.saved  { background: var(--coral-lt); border-color: var(--coral); color: var(--coral); }

.btn-outline-danger {
  background: transparent;
  border: 1.5px solid var(--coral);
  color: var(--coral);
}
.btn-outline-danger:hover {
  background: var(--coral-lt);
}
</style>