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
        class="offer-card"
        v-for="offer in visibleOffers"
        :key="offer.offerID"
        @click="openOffer(offer)"
      >
        <div v-if="offer.source === 'collab'" class="offer-collab-badge">🤝 Joint Offer</div>

        <div class="offer-card__top">
          <div class="offer-discount">{{ offer.discount }}% OFF</div>
          <span v-if="offer.type" class="offer-type-tag">{{ offer.type }}</span>
        </div>

        <div class="offer-title">{{ offer.title }}</div>
        <div class="offer-dates">{{ offer.startDate }} → {{ offer.endDate }}</div>
        <p class="offer-desc">{{ offer.description }}</p>
        <button 
          class="btn offer-btn" 
          :class="isBooked('offer', offer.offerID) ? 'btn-outline-danger' : 'btn-teal'"
          @click.stop="openOffer(offer)"
        >
          {{ isBooked('offer', offer.offerID) ? 'Cancel booking' : 'Grab deal' }}
        </button>
      </div>

      <div v-if="visibleOffers.length === 0" class="offers-empty">
        <span>No active deals right now — check back soon!</span>
      </div>
    </div>

    <OfferDetailModal
      v-model="modalOpen"
      :offer="selectedOffer"
    />
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useOffers } from '@/composables/useOffers'
import { useBookings } from '@/composables/useBookings'
import OfferDetailModal from '@/components/home/OfferDetailModal.vue'

const { activeOffers } = useOffers()
const { isBooked, getBookingId, cancelBooking } = useBookings()
const visibleOffers = computed(() => activeOffers.value.slice(0, 6))

const modalOpen     = ref(false)
const selectedOffer = ref(null)

async function openOffer(offer) {
  if (isBooked('offer', offer.offerID)) {
    if (!confirm('Are you sure you want to cancel this booking?')) return
    const bid = getBookingId('offer', offer.offerID)
    if (bid) {
      const res = await cancelBooking(bid)
      if (res.ok) alert('Booking cancelled successfully.')
      else alert('Failed to cancel: ' + res.error)
    }
    return
  }
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
  min-width: 248px; max-width: 280px; flex-shrink: 0;
  border-left: 4px solid var(--teal);
  transition: transform var(--transition), box-shadow var(--transition);
  cursor: pointer; display: flex; flex-direction: column;
}
.offer-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }

.offer-collab-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: .7rem; font-weight: 700; letter-spacing: .04em;
  text-transform: uppercase; color: var(--coral);
  background: var(--coral-lt); padding: 3px 10px;
  border-radius: 50px; margin-bottom: 10px; width: fit-content;
}

.offer-card__top {
  display: flex; align-items: center;
  justify-content: space-between; margin-bottom: 2px;
}
.offer-discount {
  font-family: 'Fraunces', serif;
  font-size: 1.7rem; font-weight: 700; color: var(--coral);
}
.offer-type-tag {
  font-size: .7rem; font-weight: 600; padding: 2px 9px;
  border-radius: 50px; background: var(--teal-lt); color: var(--teal-dk);
  white-space: nowrap;
}

.offer-title { font-weight: 600; font-size: .97rem; margin: 6px 0 4px; color: var(--indigo); }
.offer-dates { font-size: .78rem; color: var(--gray-400); }
.offer-desc  { font-size: .82rem; color: var(--gray-600); margin-top: 10px; line-height: 1.5; flex: 1; }
.offer-btn   { margin-top: 18px; padding: 8px 20px; font-size: .82rem; }

.btn-outline-danger {
  background: transparent;
  border: 1.5px solid var(--coral);
  color: var(--coral);
}
.btn-outline-danger:hover {
  background: var(--coral-lt);
}

.offers-empty {
  display: flex; align-items: center; justify-content: center;
  min-width: 280px; padding: 40px 24px;
  background: var(--gray-50); border-radius: var(--radius);
  border: 1.5px dashed var(--gray-200);
  font-size: .88rem; color: var(--gray-400);
}
</style>