<template>
  <div class="dash-card" id="offers">

    <!-- Header -->
    <div class="dash-card__header">
      <div>
        <div class="dash-card__overline">Agency Only</div>
        <h2 class="dash-card__title">Special Offers</h2>
        <p class="dash-card__sub">All active offers appear on the homepage and /deals page.</p>
      </div>
      <button class="btn btn-coral dash-card__add" @click="$emit('add')">+ New Offer</button>
    </div>

    <!-- Live preview hint -->
    <div class="live-hint">
      <span class="live-dot" />
      <span>You have {{ userActiveOffers.length }} offer{{ userActiveOffers.length !== 1 ? 's' : '' }} currently live</span>
      <RouterLink to="/deals" class="live-link" target="_blank">Preview →</RouterLink>
    </div>

    <!-- ── Collaboration Offers ───────────────────── -->
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
          @delete="handleDelete(offer)"
        />
      </div>
    </div>

    <div class="offers-collab-empty" v-else>
      <span class="offers-collab-empty__icon">🤝</span>
      <span>No collaboration offers yet — accepted partner collaborations appear here automatically.</span>
    </div>

    <div class="offers-divider" />

    <!-- ── Manual Offers ──────────────────────────── -->
    <div class="offers-section">
      <div class="offers-section__header">
        <div class="offers-section__title">
          <span class="offers-section__icon">🏷️</span>
          Your Offers
        </div>
        <span class="offers-section__count">{{ manualOffers.length }}</span>
      </div>

      <div class="offers-grid" v-if="userManualOffers.length">
        <OfferCard
          v-for="offer in userManualOffers"
          :key="offer.offerID"
          :offer="offer"
          @edit="$emit('edit', offer)"
          @delete="handleDelete(offer)"
        />
      </div>

      <div class="offer-empty" v-else>
        <div class="offer-empty__icon">🏷️</div>
        <div class="offer-empty__text">No offers yet</div>
        <button class="btn btn-teal" style="margin-top:12px;padding:8px 20px;font-size:.84rem;" @click="$emit('add')">
          Create first offer
        </button>
      </div>
    </div>

    <!-- Footer -->
    <div class="dash-card__footer">
      <span class="dash-card__count">{{ (collabOffers.length + manualOffers.length) }} total · {{ collabOffers.length }} joint · {{ manualOffers.length }} manual</span>
      <span class="dash-card__count">{{ userActiveOffers.length }} active</span>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useOffers } from '@/composables/useOffers'
import OfferCard from '@/components/dashboard/OfferCard.vue'

const props = defineProps({
  userId: { type: [Number, String], default: null },
})
defineEmits(['add', 'edit'])

const { allOffers, activeOffers: allActiveOffers, collabOffers: allCollabOffers, manualOffers: allManualOffers, deleteOfferFromDB } = useOffers()

function belongsToCurrentUser(offer) {
  if (!props.userId) return true
  const id = Number(props.userId)
  return [offer.owner_id, offer.agency_id, offer.provider_id]
    .some(owner => owner != null && Number(owner) === id)
}

// Filter all categories by current owner/agency
const userActiveOffers = computed(() =>
  allActiveOffers.value.filter(belongsToCurrentUser)
)
const collabOffers = computed(() =>
  allCollabOffers.value.filter(belongsToCurrentUser)
)
const manualOffers = computed(() =>
  allManualOffers.value.filter(o => !props.userId || o.owner_id === Number(props.userId))
)

// Legacy alias for template consistency
const userManualOffers = manualOffers

function handleDelete(offer) {
  deleteOfferFromDB(offer.offerID)
}
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
.dash-card__title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; margin: 0 0 4px; }
.dash-card__sub   { font-size: .78rem; color: var(--gray-400); margin: 0; }
.dash-card__add   { padding: 9px 20px; font-size: .86rem; flex-shrink: 0; }

.dash-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 24px; border-top: 1px solid var(--gray-100);
}
.dash-card__count { font-size: .82rem; color: var(--gray-400); }

/* Live hint bar */
.live-hint {
  display: flex; align-items: center; gap: 8px;
  margin: 16px 24px 0;
  padding: 10px 14px;
  background: rgba(39, 174, 96, .06);
  border: 1px solid rgba(39, 174, 96, .2);
  border-radius: var(--radius-sm);
  font-size: .8rem; color: var(--gray-600);
}
.live-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: #27ae60; flex-shrink: 0;
  box-shadow: 0 0 0 3px rgba(39,174,96,.2);
  animation: live-pulse 2s ease-in-out infinite;
}
@keyframes live-pulse {
  0%, 100% { box-shadow: 0 0 0 3px rgba(39,174,96,.2); }
  50%       { box-shadow: 0 0 0 6px rgba(39,174,96,.05); }
}
.live-link {
  margin-left: auto; font-size: .8rem; font-weight: 600;
  color: var(--teal); text-decoration: none;
  transition: opacity var(--transition);
}
.live-link:hover { opacity: .7; }

/* Sections */
.offers-section { padding: 20px 24px 4px; }
.offers-section__header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.offers-section__title  { display: flex; align-items: center; gap: 7px; font-size: .84rem; font-weight: 700; color: var(--indigo); }
.offers-section__icon   { font-size: 1rem; }
.offers-section__count  {
  font-size: .76rem; font-weight: 700; min-width: 22px; height: 22px;
  background: var(--gray-100); color: var(--gray-600);
  border-radius: 50px; display: flex; align-items: center; justify-content: center; padding: 0 7px;
}

.offers-collab-empty {
  display: flex; align-items: center; gap: 10px;
  margin: 20px 24px 0; padding: 13px 16px;
  background: var(--gray-50); border-radius: var(--radius-sm);
  border: 1.5px dashed var(--gray-200);
  font-size: .82rem; color: var(--gray-600); line-height: 1.5;
}
.offers-collab-empty__icon { font-size: 1.1rem; flex-shrink: 0; }

.offers-divider { height: 1px; background: var(--gray-100); margin: 4px 24px 0; }

.offers-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px; }

.offer-empty { text-align: center; padding: 32px 20px; }
.offer-empty__icon { font-size: 2rem; margin-bottom: 8px; }
.offer-empty__text { font-size: .88rem; color: var(--gray-400); }
</style>
