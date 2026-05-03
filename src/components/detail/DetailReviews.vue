<template>
  <section class="detail-reviews detail-card">


    <div class="detail-reviews__body">
      <div class="detail-reviews__left">
        <!-- Loading -->
        <div class="detail-reviews__loading" v-if="loading">
          <div class="loading-dot" v-for="i in 3" :key="i" :style="{ animationDelay: (i * 0.15) + 's' }" />
        </div>

        <!-- Review cards -->
        <div class="detail-reviews__grid" v-else>
         <ReviewCard v-for="review in displayed" :key="review.id" :review="review" :can-moderate="canModerate" :item-owner-id="itemOwnerId" @deleted="handleDeleteReview" @updated="fetchReviews" />
        </div>

        <!-- Show all button -->
        <button
          class="detail-reviews__link"
          v-if="!loading && allReviews.length > 4"
          @click="isModalOpen = true"
        >
          See all {{ allReviews.length }} reviews →
        </button>


      </div>

      <div class="detail-reviews__right">
        <h2 class="detail-reviews__title">
          <template v-if="allReviews.length">
            <span class="star">★</span> {{ displayRating }} · {{ allReviews.length }} reviews
          </template>
          <template v-else-if="props.totalReviews">
            <span class="star">★</span> {{ props.rating.toFixed(1) }} · {{ props.totalReviews }} reviews
          </template>
          <template v-else>No reviews yet</template>
        </h2>

        <!-- Rating breakdown bars -->
        <div class="detail-reviews__breakdown">
          <div class="rating-bar" v-for="bar in bars" :key="bar.stars">
            <span class="rating-bar__label">{{ bar.stars }} ★</span>
            <div class="rating-bar__track">
              <div class="rating-bar__fill" :style="{ width: bar.pct + '%' }" />
            </div>
            <span class="rating-bar__count">{{ bar.count }}</span>
          </div>
        </div>

        <!-- Write a review -->
       <WriteReview
  v-if="itemType && itemId && !hideWriteReview"
  class="detail-reviews__write-wrap"
  :item-type="itemType"
  :item-id="itemId"
  :existing-review="myReview"
  @submitted="onNewReview"
/>
      </div>
    </div>

    <!-- ── All Reviews Modal ── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isModalOpen" class="reviews-modal-overlay" @click.self="isModalOpen = false">
          <div class="reviews-modal">
            <div class="reviews-modal__header">
              <h3><span class="star">★</span> {{ displayRating }} · {{ allReviews.length }} reviews</h3>
              <button class="reviews-modal__close" @click="isModalOpen = false">✕</button>
            </div>
            <div class="reviews-modal__content">
              <div class="reviews-modal__grid">
               <ReviewCard v-for="review in allReviews" :key="review.id" :review="review" :can-moderate="canModerate" :item-owner-id="itemOwnerId" @deleted="handleDeleteReview" @updated="fetchReviews" />
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import ReviewCard  from '@/components/detail/ReviewCard.vue'
import WriteReview from '@/components/detail/WriteReview.vue'
import { useReviews } from '@/composables/useReviews'
import { useAuth }    from '@/composables/useAuth'

const props = defineProps({
  hideWriteReview: { type: Boolean, default: false },
  rating:       { type: Number, required: true },
  totalReviews: { type: Number, required: true },
  reviews:      { type: Array,  default: () => [] },
  itemType:     { type: String, default: null },
  itemId:       { type: Number, default: null },
  canModerate:  { type: Boolean, default: false },
  itemOwnerId:  { type: [Number, String], default: null },
})

const emit = defineEmits(['stats-update'])

const { user, isLoggedIn } = useAuth()
const { reviews: globalReviews, loading, fetchReviews: apiFetchReviews } = useReviews()

const displayCount = ref(4)
const isModalOpen  = ref(false)

const allReviews = computed(() => {
  // Filter global reviews for THIS item
  const live = globalReviews.value.filter(r => 
    r.item_type === props.itemType && 
    (r.package_id === props.itemId || r.service_id === props.itemId || r.destination_id === props.itemId)
  ).map(r => ({
    ...r,
    id:       r.id || r.reviewID,
    user_id:  r.user_id,
    name:     r.name || `${r.first_name} ${r.last_name}`,
    location: '',
    rating:   Number(r.rating),
    date:     new Date(r.created_at).toLocaleDateString('en-GB', { month: 'short', year: 'numeric' }),
    text:     r.comment || '',
    reply:    r.reply || null
  }))

  const liveIds = new Set(live.map(r => r.id))
  const extra   = props.reviews.filter(r => !liveIds.has(r.id))
  return [...live, ...extra]
})

const myReview = computed(() => {
  if (!isLoggedIn.value || !user.value) return null
  const uid = Number(user.value.userID ?? user.value.id)
  return allReviews.value.find(r => Number(r.user_id) === uid)
})

function handleDeleteReview(reviewId) {
  globalReviews.value = globalReviews.value.filter(r => (r.id || r.reviewID) !== reviewId)
  emit('stats-update', { rating: Number(displayRating.value), count: allReviews.value.length })
}

const displayed = computed(() => allReviews.value.slice(0, displayCount.value))

// Real average from actual review data
const displayRating = computed(() => {
  if (!allReviews.value.length) return props.rating.toFixed(1)
  const sum = allReviews.value.reduce((s, r) => s + Number(r.rating), 0)
  return (sum / allReviews.value.length).toFixed(1)
})

// Real count per star level
const bars = computed(() => {
  const total = allReviews.value.length
  return [5, 4, 3, 2, 1].map(stars => {
    const count = allReviews.value.filter(r => Math.round(Number(r.rating)) === stars).length
    return {
      stars,
      count,
      pct: total ? Math.round((count / total) * 100) : 0,
    }
  })
})

async function fetchReviews() {
  if (!props.itemType || !props.itemId) return
  await apiFetchReviews(props.itemType, props.itemId)
  emit('stats-update', {
    rating: Number(displayRating.value),
    count:  allReviews.value.length
  })
}

function onNewReview() { fetchReviews() }

onMounted(fetchReviews)
watch(() => [props.itemType, props.itemId], fetchReviews)
</script>

<style scoped>
.detail-reviews { margin-top: 24px; }

.detail-reviews__title {
  font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 24px;
  display: flex; align-items: center; gap: 8px;
}
.star { color: #FFB400; }

.detail-reviews__body {
  display: grid; grid-template-columns: 1fr 360px; gap: 40px; align-items: start;
}
.detail-reviews__left { min-width: 0; }
.detail-reviews__right { padding-top: 50px; }

.detail-reviews__breakdown {
  display: flex; flex-direction: column; gap: 10px;
  width: 100%;
}
.rating-bar { display: flex; align-items: center; gap: 12px; }
.rating-bar__label { font-size: .82rem; font-weight: 600; color: var(--indigo); width: 28px; flex-shrink: 0; }
.rating-bar__track { flex: 1; height: 6px; background: var(--gray-100); border-radius: 3px; overflow: hidden; }
.rating-bar__fill  { height: 100%; background: #FFB400; border-radius: 3px; transition: width .6s ease; }
.rating-bar__count { font-size: .78rem; color: var(--gray-400); width: 20px; text-align: right; flex-shrink: 0; }

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

.detail-reviews__grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
}
@media (max-width: 1024px) {
  .detail-reviews__grid { grid-template-columns: 1fr; }
}

.detail-reviews__link {
  margin-top: 24px; background: none; border: none; padding: 0;
  font-family: 'DM Sans', sans-serif; font-size: .95rem; font-weight: 600;
  color: var(--coral); cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
  transition: all var(--transition);
}
.detail-reviews__link:hover { color: var(--coral-dk); text-decoration: underline; transform: translateX(4px); }

.detail-reviews__write-wrap {
  margin-top: 40px;
}

/* ── All Reviews Modal ── */
.reviews-modal-overlay {
  position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); z-index: 1000;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.reviews-modal {
  background: var(--white); width: 100%; max-width: 700px;
  max-height: 85vh; display: flex; flex-direction: column;
  border-radius: var(--radius-xl, 20px); overflow: hidden;
  box-shadow: var(--shadow-xl, 0 20px 40px rgba(0,0,0,0.2));
}
.reviews-modal__header {
  padding: 24px 32px; border-bottom: 1px solid var(--gray-100);
  display: flex; align-items: center; justify-content: space-between;
}
.reviews-modal__header h3 { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; color: var(--indigo); display: flex; align-items: center; gap: 8px;}
.reviews-modal__close { background: none; border: none; font-size: 1.4rem; color: var(--gray-400); cursor: pointer; padding: 4px;}
.reviews-modal__close:hover { color: var(--indigo); }
.reviews-modal__content {
  padding: 32px; overflow-y: auto; flex: 1;
}
.reviews-modal__grid {
  display: flex; flex-direction: column; gap: 24px;
}

/* Transitions */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-active .reviews-modal { animation: modal-slide-up 0.3s ease-out; }
@keyframes modal-slide-up {
  from { opacity: 0; transform: translateY(30px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 960px) {
  .detail-reviews__body { grid-template-columns: 1fr; gap: 32px; }
  .detail-reviews__breakdown { max-width: 400px; margin-bottom: 24px; }
  .detail-reviews__right { grid-row: 1; }
}
</style>