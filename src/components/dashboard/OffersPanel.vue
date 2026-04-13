<template>
  <div class="dash-card" id="offers">

    <!-- Header -->
    <div class="dash-card__header">
      <div>
        <div class="dash-card__overline">Agency Only</div>
        <h2 class="dash-card__title">Special Offers</h2>
      </div>
      <button class="btn btn-coral dash-card__add" @click="$emit('add')">+ New Offer</button>
    </div>

    <!-- ── Collaboration Offers subsection ───────── -->
    <div class="offers-section" v-if="collabOffers.length">
      <div class="offers-section__header">
        <div class="offers-section__title">
          <span class="offers-section__icon">🤝</span>
          Joint Collaboration Offers
        </div>
        <span class="offers-section__count">{{ collabOffers.length }}</span>
      </div>
      <div class="offers-grid">
        <OfferCard
          v-for="offer in collabOffers"
          :key="offer.offerID"
          :offer="offer"
          @delete="$emit('delete', offer)"
        />
      </div>
    </div>

    <!-- Empty collab state (only shown when no manual offers either, to avoid double empty) -->
    <div class="offers-collab-empty" v-else>
      <span class="offers-collab-empty__icon">🤝</span>
      <span>No collaboration offers yet — accepted partner collaborations will appear here automatically.</span>
    </div>

    <!-- Divider -->
    <div class="offers-divider" />

    <!-- ── Manual Offers subsection ──────────────── -->
    <div class="offers-section">
      <div class="offers-section__header">
        <div class="offers-section__title">
          <span class="offers-section__icon">🏷️</span>
          Your Offers
        </div>
        <span class="offers-section__count">{{ manualOffers.length }}</span>
      </div>

      <div class="offers-grid" v-if="manualOffers.length">
        <OfferCard
          v-for="offer in manualOffers"
          :key="offer.offerID"
          :offer="offer"
          @edit="$emit('edit', offer)"
          @delete="$emit('delete', offer)"
        />
      </div>

      <div class="offer-empty" v-else>
        <div class="offer-empty__icon">🏷️</div>
        <div class="offer-empty__text">No Solo offers yet</div>
        <button
          class="btn btn-teal"
          style="margin-top:12px; padding:8px 20px; font-size:.84rem;"
          @click="$emit('add')"
        >
          Create first offer
        </button>
      </div>
    </div>

    <!-- Footer -->
    <div class="dash-card__footer">
      <span class="dash-card__count">{{ offers.length }} total offers</span>
      <span class="dash-card__count">{{ collabOffers.length }} joint · {{ manualOffers.length }} Solo</span>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import OfferCard from '@/components/dashboard/OfferCard.vue'

const props = defineProps({
  offers: { type: Array, default: () => [] },
})
defineEmits(['add', 'edit', 'delete'])

const collabOffers = computed(() => props.offers.filter(o => o.source === 'collab'))
const manualOffers = computed(() => props.offers.filter(o => o.source !== 'collab'))
</script>

<style scoped>
.dash-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 28px; overflow: hidden; }

.dash-card__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 24px 24px 0; gap: 16px; flex-wrap: wrap;
}
.dash-card__overline {
  font-size: .72rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--teal); margin-bottom: 4px;
}
.dash-card__title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; }
.dash-card__add   { padding: 9px 20px; font-size: .86rem; }
.dash-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 24px; border-top: 1px solid var(--gray-100);
}
.dash-card__count { font-size: .82rem; color: var(--gray-400); }

/* Sections */
.offers-section { padding: 20px 24px 4px; }
.offers-section__header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 14px;
}
.offers-section__title {
  display: flex; align-items: center; gap: 7px;
  font-size: .84rem; font-weight: 700; color: var(--indigo);
}
.offers-section__icon { font-size: 1rem; }
.offers-section__count {
  font-size: .76rem; font-weight: 700; min-width: 22px; height: 22px;
  background: var(--gray-100); color: var(--gray-600);
  border-radius: 50px; display: flex; align-items: center;
  justify-content: center; padding: 0 7px;
}

/* Collab empty hint */
.offers-collab-empty {
  display: flex; align-items: center; gap: 10px;
  margin: 20px 24px 0; padding: 13px 16px;
  background: var(--gray-50); border-radius: var(--radius-sm);
  border: 1.5px dashed var(--gray-200);
  font-size: .82rem; color: var(--gray-600); line-height: 1.5;
}
.offers-collab-empty__icon { font-size: 1.1rem; flex-shrink: 0; }

/* Divider */
.offers-divider { height: 1px; background: var(--gray-100); margin: 4px 24px 0; }

/* Grid */
.offers-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px;
}

/* Empty */
.offer-empty { text-align: center; padding: 32px 20px; }
.offer-empty__icon { font-size: 2rem; margin-bottom: 8px; }
.offer-empty__text { font-size: .88rem; color: var(--gray-400); }
</style>