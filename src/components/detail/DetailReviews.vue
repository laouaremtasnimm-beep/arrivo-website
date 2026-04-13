<template>
  <section class="detail-reviews">
    <h2 class="detail-reviews__title">
      <span class="star">★</span> {{ rating }} · {{ totalReviews?.toLocaleString() }} reviews
    </h2>

    <!-- Rating breakdown bars -->
    <div class="detail-reviews__breakdown">
      <div class="rating-bar" v-for="bar in bars" :key="bar.stars">
        <span class="rating-bar__label">{{ bar.stars }} ★</span>
        <div class="rating-bar__track">
          <div class="rating-bar__fill" :style="{ width: bar.pct + '%' }" />
        </div>
        <span class="rating-bar__pct">{{ bar.pct }}%</span>
      </div>
    </div>

    <!-- Review cards -->
    <div class="detail-reviews__grid">
      <ReviewCard v-for="review in displayed" :key="review.id" :review="review" />
    </div>

    <!-- Show more -->
    <button
      class="detail-reviews__more"
      v-if="reviews.length > displayCount"
      @click="displayCount += 6"
    >
      Show more reviews ({{ reviews.length - displayCount }} remaining)
    </button>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'
import ReviewCard from '@/components/detail/ReviewCard.vue'

const props = defineProps({
  rating:       { type: Number, required: true },
  totalReviews: { type: Number, required: true },
  reviews:      { type: Array,  default: () => [] },
})

const displayCount = ref(6)
const displayed    = computed(() => props.reviews.slice(0, displayCount.value))

// Fake breakdown distribution based on the avg rating
const bars = computed(() => {
  const avg = props.rating
  return [
    { stars: 5, pct: Math.round(Math.min(100, (avg - 3.5) / 1.5 * 100)) },
    { stars: 4, pct: Math.round(Math.max(0, 100 - (avg - 3.5) / 1.5 * 130)) },
    { stars: 3, pct: Math.max(0, Math.round(10 - (avg - 3) * 8)) },
    { stars: 2, pct: Math.max(0, Math.round(4  - (avg - 3) * 4)) },
    { stars: 1, pct: Math.max(0, Math.round(2  - (avg - 3) * 2)) },
  ]
})
</script>

<style scoped>
.detail-reviews { padding: 40px 0 0; }

.detail-reviews__title {
  font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 28px;
  display: flex; align-items: center; gap: 8px;
}

/* Rating breakdown */
.detail-reviews__breakdown {
  display: flex; flex-direction: column; gap: 8px; margin-bottom: 36px;
  max-width: 320px;
}
.rating-bar { display: flex; align-items: center; gap: 12px; }
.rating-bar__label { font-size: .82rem; font-weight: 600; color: var(--indigo); width: 28px; flex-shrink: 0; }
.rating-bar__track { flex: 1; height: 6px; background: var(--gray-100); border-radius: 3px; overflow: hidden; }
.rating-bar__fill  { height: 100%; background: #FFB400; border-radius: 3px; transition: width .8s ease; }
.rating-bar__pct   { font-size: .78rem; color: var(--gray-400); width: 32px; text-align: right; flex-shrink: 0; }

/* Cards grid */
.detail-reviews__grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px;
}

/* Show more */
.detail-reviews__more {
  margin-top: 28px; display: block; width: 100%;
  padding: 14px; border: 1.5px solid var(--gray-200); border-radius: 12px;
  background: var(--white); font-family: 'DM Sans', sans-serif;
  font-size: .9rem; font-weight: 600; color: var(--indigo); cursor: pointer;
  transition: all var(--transition);
}
.detail-reviews__more:hover { border-color: var(--coral); color: var(--coral); }
</style>