<template>
  <div class="dash-card" id="reviews">
    <div class="dash-card__header">
      <div>
        <div class="dash-card__overline">Feedback</div>
        <h2 class="dash-card__title">Recent Reviews</h2>
      </div>
      <div class="reviews-avg">
        <span class="star">★</span>
        <span class="reviews-avg__val">{{ avgRating }}</span>
        <span class="reviews-avg__count">/ 5 ({{ reviews.length }} reviews)</span>
      </div>
    </div>

    <div class="reviews-list">
      <div class="review-row" v-for="rev in reviews" :key="rev.reviewID">
        <div class="review-row__top">
          <div class="review-author">
            <div class="review-avatar">{{ rev.touristName[0] }}</div>
            <div>
              <div class="review-name">{{ rev.touristName }}</div>
              <div class="review-item">on <em>{{ rev.itemName }}</em></div>
            </div>
          </div>
          <div class="review-row__right">
            <div class="review-stars">
              <span class="star" v-for="i in rev.rating" :key="i">★</span>
              <span class="star star--empty" v-for="i in (5 - rev.rating)" :key="'e' + i">★</span>
            </div>
            <div class="review-date">{{ rev.date }}</div>
          </div>
        </div>
        <p class="review-comment">"{{ rev.comment }}"</p>
        <div class="review-actions">
          <button class="tbl-btn tbl-btn--reply" @click="$emit('reply', rev)">↩ Reply</button>
          <button class="tbl-btn tbl-btn--delete" @click="$emit('delete', rev)">🗑️</button>
        </div>
      </div>
    </div>

    <div class="dash-card__footer">
      <span class="dash-card__count">{{ reviews.length }} reviews</span>
      <a href="#" class="see-all">View all →</a>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  reviews: { type: Array, default: () => [] },
})
defineEmits(['reply', 'delete'])

const avgRating = computed(() => {
  if (!props.reviews.length) return '–'
  const avg = props.reviews.reduce((s, r) => s + r.rating, 0) / props.reviews.length
  return avg.toFixed(1)
})
</script>

<style scoped>
.dash-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 28px; overflow: hidden; }
.dash-card__header { display: flex; align-items: flex-start; justify-content: space-between; padding: 24px 24px 0; gap: 16px; flex-wrap: wrap; }
.dash-card__overline { font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--teal); margin-bottom: 4px; }
.dash-card__title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; }
.dash-card__footer { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-top: 1px solid var(--gray-100); }
.dash-card__count { font-size: .82rem; color: var(--gray-400); }

.reviews-avg { display: flex; align-items: center; gap: 6px; }
.reviews-avg__val { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--indigo); }
.reviews-avg__count { font-size: .8rem; color: var(--gray-400); }

.reviews-list { padding: 8px 0; }

.review-row { padding: 18px 24px; border-bottom: 1px solid var(--gray-100); }
.review-row:last-child { border-bottom: none; }

.review-row__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.review-author { display: flex; align-items: center; gap: 12px; }
.review-avatar {
  width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
  background: var(--sand); color: var(--indigo);
  font-family: 'Fraunces', serif; font-size: .9rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.review-name { font-weight: 600; font-size: .88rem; color: var(--indigo); }
.review-item { font-size: .76rem; color: var(--gray-400); margin-top: 2px; }
.review-item em { color: var(--teal); font-style: normal; font-weight: 600; }

.review-row__right { text-align: right; }
.review-stars { display: flex; gap: 2px; justify-content: flex-end; margin-bottom: 3px; }
.star        { color: #FFB400; font-size: .85rem; }
.star--empty { color: var(--gray-200); }
.review-date { font-size: .75rem; color: var(--gray-400); }

.review-comment { font-size: .86rem; color: var(--gray-600); line-height: 1.65; font-style: italic; margin-bottom: 12px; }

.review-actions { display: flex; gap: 8px; }
.tbl-btn { padding: 5px 14px; border-radius: 8px; border: none; cursor: pointer; font-size: .8rem; font-weight: 600; font-family: 'DM Sans', sans-serif; transition: all var(--transition); }
.tbl-btn--reply  { background: rgba(46,196,182,.1); color: var(--teal-dk); }
.tbl-btn--reply:hover  { background: var(--teal); color: #fff; }
.tbl-btn--delete { background: rgba(231,76,60,.1); color: #e74c3c; }
.tbl-btn--delete:hover { background: #e74c3c; color: #fff; }
</style>
