<template>
  <div class="dash-card" id="offers">
    <div class="dash-card__header">
      <div>
        <div class="dash-card__overline">Agency Only</div>
        <h2 class="dash-card__title">Special Offers</h2>
      </div>
      <button class="btn btn-coral dash-card__add" @click="$emit('add')">+ New Offer</button>
    </div>

    <div class="offers-grid">
      <div class="offer-card" v-for="offer in offers" :key="offer.offerID">
        <div class="offer-card__top">
          <div class="offer-discount">{{ offer.discount }}% OFF</div>
          <div class="offer-card__actions">
            <button class="icon-btn" @click="$emit('edit', offer)" title="Edit">✏️</button>
            <button class="icon-btn icon-btn--del" @click="$emit('delete', offer)" title="Delete">🗑️</button>
          </div>
        </div>
        <div class="offer-title">{{ offer.title }}</div>
        <div class="offer-dates">{{ offer.startDate }} → {{ offer.endDate }}</div>
        <p class="offer-desc">{{ offer.description }}</p>
        <div class="offer-card__footer">
          <span class="offer-status" :class="isActive(offer) ? 'active' : 'inactive'">
            {{ isActive(offer) ? '● Active' : '○ Inactive' }}
          </span>
        </div>
      </div>

      <!-- Empty state -->
      <div class="offer-empty" v-if="!offers.length">
        <div class="offer-empty__icon">🏷️</div>
        <div class="offer-empty__text">No offers yet</div>
        <button class="btn btn-teal" style="margin-top:12px;padding:8px 20px;font-size:.84rem;" @click="$emit('add')">Create first offer</button>
      </div>
    </div>

    <div class="dash-card__footer">
      <span class="dash-card__count">{{ offers.length }} offers</span>
      <a href="#" class="see-all">Manage all →</a>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  offers: { type: Array, default: () => [] },
})
defineEmits(['add', 'edit', 'delete'])

function isActive(offer) {
  const now = new Date()
  // Simple check — replace with real date parsing for production
  return true
}
</script>

<style scoped>
.dash-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 28px; overflow: hidden; }
.dash-card__header { display: flex; align-items: flex-start; justify-content: space-between; padding: 24px 24px 0; gap: 16px; flex-wrap: wrap; }
.dash-card__overline { font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--teal); margin-bottom: 4px; }
.dash-card__title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; }
.dash-card__add { padding: 9px 20px; font-size: .86rem; }
.dash-card__footer { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-top: 1px solid var(--gray-100); }
.dash-card__count { font-size: .82rem; color: var(--gray-400); }

.offers-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 18px; padding: 20px 24px;
}

.offer-card {
  background: var(--gray-50); border-radius: 14px;
  padding: 20px; border-left: 4px solid var(--teal);
  transition: transform var(--transition), box-shadow var(--transition);
}
.offer-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }

.offer-card__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.offer-discount { font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700; color: var(--coral); }
.offer-card__actions { display: flex; gap: 6px; }

.icon-btn {
  width: 28px; height: 28px; border-radius: 7px; border: none; cursor: pointer;
  background: var(--white); font-size: .82rem; transition: all var(--transition);
  display: flex; align-items: center; justify-content: center;
}
.icon-btn:hover      { background: var(--teal); }
.icon-btn--del:hover { background: #e74c3c; }

.offer-title { font-weight: 600; font-size: .95rem; color: var(--indigo); margin-bottom: 4px; }
.offer-dates { font-size: .76rem; color: var(--gray-400); margin-bottom: 8px; }
.offer-desc  { font-size: .82rem; color: var(--gray-600); line-height: 1.5; }

.offer-card__footer { margin-top: 14px; }
.offer-status { font-size: .76rem; font-weight: 700; }
.offer-status.active   { color: #27ae60; }
.offer-status.inactive { color: var(--gray-400); }

.offer-empty { grid-column: 1 / -1; text-align: center; padding: 40px; }
.offer-empty__icon { font-size: 2.5rem; margin-bottom: 10px; }
.offer-empty__text { font-size: .9rem; color: var(--gray-400); }
</style>
