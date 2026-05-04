<template>
  <section class="section">
    <div class="section-header">
      <div>
        <div class="section-overline">Limited Time</div>
        <h2 class="section-title">Hot <em>offers</em></h2>
        <p class="section-sub">Don't miss exclusive deals from our partner agencies.</p>
      </div>
      <RouterLink to="/deals" class="see-all">View all →</RouterLink>
    </div>

    <div class="offers-strip">
      <div
        v-for="offer in offers"
        :key="offer.offerID"
        class="offer-card"
        :class="{ 'offer-card--collab': offer.source === 'collab' }"
        @click="openOffer(offer)"
      >
        <div v-if="offer.source === 'collab'" class="offer-collab-badge">🤝 Joint Offer</div>
        <div class="offer-discount">{{ offer.discount }}% OFF</div>
        <div class="offer-title">{{ offer.title }}</div>
        <div class="offer-dates" v-if="offer.startDate">{{ offer.startDate }} → {{ offer.endDate }}</div>
        <p class="offer-desc">{{ offer.description }}</p>
        <div v-if="offer.source === 'collab' && offer.partnerName" class="offer-partner">
          <span class="offer-partner-dot" :style="{ background: offer.partnerColor || '#2EC4B6' }" />
          with {{ offer.partnerName }}
        </div>
        <button class="btn btn-teal offer-btn" @click.stop="openOffer(offer)">
          Grab deal
        </button>
      </div>
    </div>

    <OfferDetailModal v-model="modalOpen" :offer="selectedOffer" />
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useOffers } from '@/composables/useOffers'
import OfferDetailModal from '@/components/home/OfferDetailModal.vue'

const { activeOffers } = useOffers()

// Max 6 on homepage — collab offers float to the top
const offers = computed(() => {
  return [...activeOffers.value]
    .sort((a, b) => {
      if (a.source === 'collab' && b.source !== 'collab') return -1
      if (b.source === 'collab' && a.source !== 'collab') return  1
      return 0
    })
    .slice(0, 6)
})

const modalOpen     = ref(false)
const selectedOffer = ref(null)

function openOffer(offer) {
  selectedOffer.value = offer
  modalOpen.value     = true
}
</script>

<style scoped>
.offers-strip {
  display: flex; gap: 20px;
  overflow-x: auto; padding-bottom: 8px;
  scrollbar-width: none;
}
.offers-strip::-webkit-scrollbar { display: none; }

.offer-card {
  background: var(--white); border-radius: var(--radius);
  padding: 24px; box-shadow: var(--shadow);
  min-width: 248px; flex-shrink: 0;
  border-left: 4px solid var(--teal);
  transition: transform var(--transition), box-shadow var(--transition);
  cursor: pointer; display: flex; flex-direction: column;
}
.offer-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.offer-card--collab { border-left-color: var(--coral); }

.offer-collab-badge {
  display: inline-block; font-size: .68rem; font-weight: 700;
  letter-spacing: .04em; text-transform: uppercase;
  color: var(--coral); background: var(--coral-lt);
  padding: 2px 9px; border-radius: 50px; margin-bottom: 8px;
  align-self: flex-start;
}
.offer-discount {
  font-family: 'Fraunces', serif;
  font-size: 1.7rem; font-weight: 700; color: var(--coral);
}
.offer-title  { font-weight: 600; font-size: .97rem; margin: 6px 0 4px; color: var(--indigo); }
.offer-dates  { font-size: .78rem; color: var(--gray-400); }
.offer-desc   { font-size: .82rem; color: var(--gray-600); margin-top: 10px; line-height: 1.5; flex: 1; }

.offer-partner {
  display: flex; align-items: center; gap: 6px;
  font-size: .76rem; color: var(--gray-600); margin-top: 8px;
}
.offer-partner-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

.offer-btn { margin-top: 18px; padding: 8px 20px; font-size: .82rem; align-self: flex-start; }
</style>