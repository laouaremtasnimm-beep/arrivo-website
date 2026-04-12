<template>
  <section class="section">
    <div class="section-header">
      <div>
        <div class="section-overline">Limited Time</div>
        <h2 class="section-title">Hot <em>offers</em></h2>
        <p class="section-sub">Don't miss exclusive deals from our partner agencies.</p>
      </div>
      <a href="#" class="see-all">View all →</a>
    </div>

    <div class="offers-strip">
      <div
        class="offer-card"
        v-for="offer in offers"
        :key="offer.offerID"
        @click="$emit('select', offer)"
      >
        <div class="offer-discount">{{ offer.discount }}% OFF</div>
        <div class="offer-title">{{ offer.title }}</div>
        <div class="offer-dates">{{ offer.startDate }} → {{ offer.endDate }}</div>
        <p class="offer-desc">{{ offer.description }}</p>
        <button class="btn btn-teal offer-btn">Grab deal</button>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  offers: { type: Array, default: () => [] },
})
defineEmits(['select'])
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
  cursor: pointer;
}
.offer-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }

.offer-discount {
  font-family: 'Fraunces', serif;
  font-size: 1.7rem; font-weight: 700; color: var(--coral);
}
.offer-title {
  font-weight: 600; font-size: .97rem; margin: 6px 0 4px;
  color: var(--indigo);
}
.offer-dates  { font-size: .78rem; color: var(--gray-400); }
.offer-desc   { font-size: .82rem; color: var(--gray-600); margin-top: 10px; line-height: 1.5; }
.offer-btn    { margin-top: 18px; padding: 8px 20px; font-size: .82rem; }
</style>
