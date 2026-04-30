<template>
  <section class="detail-reviews">
    <h2 class="detail-reviews__title">
      <span class="star">★</span> {{ displayRating }} · {{ totalLabel }} reviews
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

    <!-- Loading -->
    <div class="detail-reviews__loading" v-if="loading">
      <div class="loading-dot" v-for="i in 3" :key="i" :style="{ animationDelay: (i * 0.15) + 's' }" />
    </div>

    <!-- Review cards -->
    <div class="detail-reviews__grid" v-else>
      <ReviewCard v-for="review in displayed" :key="review.id" :review="review" />
    </div>

    <!-- Show more -->
    <button
      class="detail-reviews__more"
      v-if="!loading && allReviews.length > displayCount"
      @click="displayCount += 6"
    >
      Show more reviews ({{ allReviews.length - displayCount }} remaining)
    </button>

    <!-- Write a review -->
    <WriteReview
      v-if="itemType && itemId"
      :item-type="itemType"
      :item-id="itemId"
      @submitted="onNewReview"
    />
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import ReviewCard  from '@/components/detail/ReviewCard.vue'
import WriteReview from '@/components/detail/WriteReview.vue'

const props = defineProps({
  rating:       { type: Number, required: true },
  totalReviews: { type: Number, required: true },
  reviews:      { type: Array,  default: () => [] },  // fallback / mock reviews
  // Pass these to enable live fetch + write review
  itemType:     { type: String, default: null },       // 'package' | 'service' | 'destination'
  itemId:       { type: Number, default: null },
})

const API_BASE    = 'http://localhost/arrivo-website/backend/api/v1/reviews.php'
const displayCount = ref(6)
const loading      = ref(false)
const liveReviews  = ref([])   // fetched from API

// Merge live + prop reviews, deduplicate by id
const allReviews = computed(() => {
  if (!liveReviews.value.length) return props.reviews
  // normalise live reviews to same shape as ReviewCard expects
  const live = liveReviews.value.map(r => ({
    id:       r.id,
    name:     `${r.first_name} ${r.last_name}`,
    location: '',
    rating:   r.rating,
    date:     new Date(r.created_at).toLocaleDateString('en-GB', { month: 'short', year: 'numeric' }),
    text:     r.comment || '',
  }))
  // merge: live first, then any prop reviews not already present
  const liveIds = new Set(live.map(r => r.id))
  const extra   = props.reviews.filter(r => !liveIds.has(r.id))
  return [...live, ...extra]
})

const displayed     = computed(() => allReviews.value.slice(0, displayCount.value))
const displayRating = computed(() => {
  if (!allReviews.value.length) return props.rating.toFixed(1)
  const avg = allReviews.value.reduce((s, r) => s + r.rating, 0) / allReviews.value.length
  return avg.toFixed(1)
})
const totalLabel = computed(() => allReviews.value.length || props.totalReviews)

const bars = computed(() => {
  const avg = Number(displayRating.value)
  return [
    { stars: 5, pct: Math.round(Math.min(100, (avg - 3.5) / 1.5 * 100)) },
    { stars: 4, pct: Math.round(Math.max(0, 100 - (avg - 3.5) / 1.5 * 130)) },
    { stars: 3, pct: Math.max(0, Math.round(10  - (avg - 3) * 8)) },
    { stars: 2, pct: Math.max(0, Math.round(4   - (avg - 3) * 4)) },
    { stars: 1, pct: Math.max(0, Math.round(2   - (avg - 3) * 2)) },
  ]
})

async function fetchReviews() {
  if (!props.itemType || !props.itemId) return
  loading.value = true
  try {
    const res  = await fetch(`${API_BASE}?item_type=${props.itemType}&item_id=${props.itemId}`)
    const data = await res.json()
    if (res.ok) liveReviews.value = data.reviews || []
  } catch (e) {
    console.error('Failed to load reviews:', e)
  } finally {
    loading.value = false
  }
}

// When a new review is submitted via WriteReview, prepend it immediately
function onNewReview(review) {
  // Re-fetch to get the full review with user name
  fetchReviews()
}

onMounted(fetchReviews)
watch(() => [props.itemType, props.itemId], fetchReviews)
</script>

<style scoped>
.detail-reviews { padding: 40px 0 0; }

.detail-reviews__title {
  font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 28px;
  display: flex; align-items: center; gap: 8px;
}
.star { color: #FFB400; }

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

/* Loading dots */
.detail-reviews__loading {
  display: flex; gap: 8px; padding: 40px 0; justify-content: center;
}
.loading-dot {
  width: 9px; height: 9px; border-radius: 50%; background: var(--teal);
  animation: bounce 0.9s ease-in-out infinite;
}
@keyframes bounce {
  0%, 100% { transform: translateY(0); opacity: .4; }
  50%       { transform: translateY(-8px); opacity: 1; }
}

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