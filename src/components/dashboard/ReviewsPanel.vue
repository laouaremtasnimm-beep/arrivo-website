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
        <span class="reviews-avg__count">/ 5 ({{ localReviews.length }} reviews)</span>
      </div>
    </div>

    <!-- Filter tabs -->
    <div class="reviews-tabs">
      <button
        v-for="tab in ratingTabs"
        :key="tab.value"
        class="reviews-tab"
        :class="{ active: activeTab === tab.value }"
        @click="activeTab = tab.value"
      >
        {{ tab.label }}
        <span v-if="tab.count" class="tab-count">{{ tab.count }}</span>
      </button>
    </div>

    <!-- Empty state -->
    <div class="reviews-empty" v-if="!filtered.length">
      <div class="reviews-empty__icon">⭐</div>
      <p>No {{ activeTab === 'all' ? '' : activeTab + '-star ' }}reviews yet.</p>
    </div>

    <div class="reviews-list" v-else>
      <div class="review-row" v-for="rev in filtered" :key="rev.reviewID">

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
              <span class="star"       v-for="i in rev.rating"        :key="i">★</span>
              <span class="star star--empty" v-for="i in (5 - rev.rating)" :key="'e'+i">★</span>
            </div>
            <div class="review-date">{{ rev.date }}</div>
          </div>
        </div>

        <p class="review-comment">"{{ rev.comment }}"</p>

        <!-- Existing reply (already posted) -->
        <div class="review-reply" v-if="rev.reply">
          <div class="review-reply__label">Your reply</div>
          <p class="review-reply__text">{{ rev.reply }}</p>
          <button class="reply-edit-btn" @click="startEdit(rev)">Edit</button>
        </div>

        <!-- Inline reply box -->
        <ReviewReplyBox
          :open="openReplyId === rev.reviewID"
          :reviewer-name="rev.touristName"
          @submit="text => saveReply(rev.reviewID, text)"
          @cancel="openReplyId = null"
        />

        <div class="review-actions">
          <button
            class="tbl-btn tbl-btn--reply"
            @click="toggleReply(rev)"
          >
            {{ rev.reply ? '✏️ Edit reply' : '↩ Reply' }}
          </button>
          <button class="tbl-btn tbl-btn--delete" @click="deleteReview(rev)">🗑️</button>
        </div>

      </div>
    </div>

    <div class="dash-card__footer">
      <span class="dash-card__count">{{ localReviews.length }} reviews</span>
      <span class="reviews-footer-avg" v-if="localReviews.length">
        Avg: <strong>{{ avgRating }} ★</strong>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ReviewReplyBox from '@/components/dashboard/ReviewReplyBox.vue'

const props = defineProps({
  reviews: { type: Array, default: () => [] },
})
const emit = defineEmits(['reply', 'delete'])

// Local reactive copy
const localReviews = ref(
  props.reviews.map(r => ({ ...r, reply: r.reply || null }))
)

const activeTab   = ref('all')
const openReplyId = ref(null)

const avgRating = computed(() => {
  if (!localReviews.value.length) return '–'
  const avg = localReviews.value.reduce((s, r) => s + r.rating, 0) / localReviews.value.length
  return avg.toFixed(1)
})

const ratingTabs = computed(() => {
  const counts = [5, 4, 3, 2, 1].map(n => ({
    value: String(n),
    label: `${n} ★`,
    count: localReviews.value.filter(r => r.rating === n).length || null,
  }))
  return [{ value: 'all', label: 'All', count: null }, ...counts.filter(t => t.count)]
})

const filtered = computed(() => {
  if (activeTab.value === 'all') return localReviews.value
  return localReviews.value.filter(r => r.rating === Number(activeTab.value))
})

function toggleReply(rev) {
  openReplyId.value = openReplyId.value === rev.reviewID ? null : rev.reviewID
}

function startEdit(rev) {
  openReplyId.value = rev.reviewID
}

function saveReply(reviewID, text) {
  const idx = localReviews.value.findIndex(r => r.reviewID === reviewID)
  if (idx !== -1) {
    localReviews.value[idx].reply = text
    emit('reply', { reviewID, text })
  }
  openReplyId.value = null
}

function deleteReview(rev) {
  if (!confirm(`Delete review by ${rev.touristName}?`)) return
  localReviews.value = localReviews.value.filter(r => r.reviewID !== rev.reviewID)
  emit('delete', rev)
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
.dash-card__title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; }
.dash-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 24px; border-top: 1px solid var(--gray-100);
}
.dash-card__count { font-size: .82rem; color: var(--gray-400); }
.reviews-footer-avg { font-size: .82rem; color: var(--gray-600); }
.reviews-footer-avg strong { color: var(--indigo); }

.reviews-avg { display: flex; align-items: center; gap: 6px; }
.reviews-avg__val { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--indigo); }
.reviews-avg__count { font-size: .8rem; color: var(--gray-400); }

/* Tabs */
.reviews-tabs {
  display: flex; gap: 4px; padding: 14px 24px 0; flex-wrap: wrap;
}
.reviews-tab {
  padding: 5px 14px; border-radius: 50px; border: none;
  background: var(--gray-100); font-size: .78rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
  font-family: 'DM Sans', sans-serif; display: flex; align-items: center; gap: 5px;
}
.reviews-tab:hover  { background: var(--gray-200); color: var(--indigo); }
.reviews-tab.active { background: var(--indigo); color: #fff; }
.tab-count {
  background: var(--coral); color: #fff; font-size: .65rem;
  font-weight: 700; min-width: 16px; height: 16px;
  border-radius: 50px; display: flex; align-items: center;
  justify-content: center; padding: 0 4px;
}

/* List */
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
.star         { color: #FFB400; font-size: .85rem; }
.star--empty  { color: var(--gray-200); }
.review-date  { font-size: .75rem; color: var(--gray-400); }
.review-comment { font-size: .86rem; color: var(--gray-600); line-height: 1.65; font-style: italic; margin-bottom: 12px; }

/* Reply display */
.review-reply {
  background: var(--gray-50); border-left: 3px solid var(--teal);
  border-radius: 0 8px 8px 0; padding: 10px 14px; margin-bottom: 10px;
  display: flex; flex-wrap: wrap; align-items: flex-start; gap: 6px;
}
.review-reply__label {
  font-size: .7rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; color: var(--teal-dk); width: 100%; margin-bottom: 2px;
}
.review-reply__text {
  font-size: .84rem; color: var(--indigo); line-height: 1.5; flex: 1; margin: 0;
}
.reply-edit-btn {
  background: none; border: none; font-size: .74rem; color: var(--gray-400);
  cursor: pointer; font-family: 'DM Sans', sans-serif; padding: 2px 6px;
  border-radius: 4px; transition: color var(--transition);
}
.reply-edit-btn:hover { color: var(--teal-dk); }

/* Actions */
.review-actions { display: flex; gap: 8px; }
.tbl-btn { padding: 5px 14px; border-radius: 8px; border: none; cursor: pointer; font-size: .8rem; font-weight: 600; font-family: 'DM Sans', sans-serif; transition: all var(--transition); }
.tbl-btn--reply  { background: rgba(46,196,182,.1); color: var(--teal-dk); }
.tbl-btn--reply:hover  { background: var(--teal); color: #fff; }
.tbl-btn--delete { background: rgba(231,76,60,.1); color: #e74c3c; }
.tbl-btn--delete:hover { background: #e74c3c; color: #fff; }

/* Empty */
.reviews-empty {
  padding: 48px 20px; text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  color: var(--gray-400); font-size: .88rem;
}
.reviews-empty__icon { font-size: 2rem; }
</style>