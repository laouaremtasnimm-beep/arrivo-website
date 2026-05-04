<template>
  <div class="dashboard-reviews dash-card">
    <div class="dash-card__header">
      <div class="dash-header-left">
        <h2 class="dash-card__title">Customer Reviews</h2>
        <div class="reviews-tabs" v-if="localReviews.length">
          <button 
            v-for="tab in ratingTabs" :key="tab.value"
            class="tab-btn" :class="{ active: activeTab === tab.value }"
            @click="activeTab = tab.value"
          >
            {{ tab.label }}
            <span class="tab-count" v-if="tab.count">{{ tab.count }}</span>
          </button>
        </div>
      </div>
      <div class="reviews-stats" v-if="localReviews.length">
        <div class="stat-item">
          <span class="stat-value">{{ avgRating }}</span>
          <span class="stat-label">Avg Rating</span>
        </div>
      </div>
    </div>

    <div class="dash-card__body">
      <!-- Loading State -->
      <div class="reviews-loading" v-if="loading">
        <div class="loading-spinner"></div>
        <p>Fetching reviews…</p>
      </div>

      <!-- Empty State -->
      <div class="reviews-empty" v-else-if="!localReviews.length">
        <div class="reviews-empty__icon">💬</div>
        <h3>No reviews yet</h3>
        <p>When customers review your {{ role === 'agency' ? 'packages' : 'services' }}, they will appear here.</p>
      </div>

      <!-- Review List -->
      <div class="review-row" v-for="rev in filtered" :key="rev.reviewID">
        <div class="review-row__top">
          <div class="review-row__author">
            <div class="review-avatar">{{ rev.touristName[0] }}</div>
            <div class="review-meta">
              <div class="review-name">{{ rev.touristName }}</div>
              <div class="review-item">on <em>{{ rev.itemName }}</em>
                <span class="review-item__type">{{ rev.itemType }}</span>
              </div>
            </div>
          </div>
          <div class="review-row__right">
            <div class="review-stars">
              <span class="star"            v-for="i in rev.rating"       :key="i">★</span>
              <span class="star star--empty" v-for="i in (5 - rev.rating)" :key="'e'+i">★</span>
            </div>
            <div class="review-date">{{ rev.date }}</div>
          </div>
        </div>

        <p class="review-comment">"{{ rev.comment }}"</p>

        <!-- Existing reply -->
        <div class="review-reply" v-if="rev.reply">
          <div class="review-reply__header">
            <div class="review-reply__label">Your reply</div>
            <div class="review-reply__actions">
              <button class="reply-edit-btn" @click="startEdit(rev)" title="Edit">✎ Edit</button>
              <button class="reply-delete-btn" @click="deleteReply(rev)" title="Delete">🗑 Delete</button>
            </div>
          </div>
          <p class="review-reply__text">{{ rev.reply }}</p>
        </div>

        <!-- Inline reply box -->
        <ReviewReplyBox
          :open="openReplyId === rev.reviewID"
          :reviewer-name="rev.touristName"
          :initial-text="rev.reply || ''"
          @submit="text => saveReply(rev.reviewID, text)"
          @cancel="openReplyId = null"
        />

        <div class="review-comment-actions" v-if="!rev.reply && openReplyId !== rev.reviewID">
          <button class="tbl-btn tbl-btn--reply" @click="toggleReply(rev)">
            ↩ Reply
          </button>
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
import { ref, computed, onMounted } from 'vue'
import ReviewReplyBox from '@/components/dashboard/ReviewReplyBox.vue'
import { useNotifications } from '@/composables/useNotifications'
import { useReviews } from '@/composables/useReviews'

const props = defineProps({
  reviews: { type: Array, default: () => [] },
  role:   { type: String, default: null },
  userId: { type: [String, Number], default: null },
})
const emit = defineEmits(['reply'])

const { reviews: allReviews, loading, fetchAllForOwner, saveReply: apiSaveReply } = useReviews()

const localReviews = computed(() => {
  return allReviews.value.filter(r => r.itemName)
})

const openReplyId  = ref(null)
const activeTab    = ref('all')

function getUser() {
  try {
    const s = localStorage.getItem('user')
    return s ? JSON.parse(s) : null
  } catch { return null }
}

onMounted(async () => {
  const fallback = getUser()
  const role   = props.role   || fallback?.role
  const userId = props.userId || fallback?.userID
  if (role && userId) {
    await fetchAllForOwner(role, userId)
  }
})

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

async function saveReply(reviewID, text) {
  const rev = localReviews.value.find(r => r.reviewID === reviewID)
  if (!rev) return
  try {
    await apiSaveReply(reviewID, text)
    rev.reply = text
    emit('reply', { reviewID, text })
  } catch (e) {
    console.error('saveReply failed:', e)
  } finally {
    openReplyId.value = null
  }
}

async function deleteReply(rev) {
  if (!confirm('Delete this reply?')) return
  try {
    await apiSaveReply(rev.reviewID, '')
    const idx = localReviews.value.findIndex(r => r.reviewID === rev.reviewID)
    if (idx !== -1) localReviews.value[idx].reply = null
  } catch (e) {
    alert(e.message)
  }
}

</script>

<style scoped>
.dashboard-reviews { min-height: 400px; }
.dash-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden; display: flex; flex-direction: column;
}
.dash-card__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 24px 24px 16px; border-bottom: 1px solid var(--gray-100);
}
.dash-card__title {
  font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700; color: var(--indigo);
  margin-bottom: 4px;
}
.dash-card__body { padding: 0 24px; flex: 1; }
.dash-card__footer {
  padding: 16px 24px; border-top: 1px solid var(--gray-100);
  display: flex; align-items: center; justify-content: space-between;
  background: var(--gray-50); font-size: .84rem; color: var(--gray-400);
}
.dash-header-left { display: flex; flex-direction: column; gap: 12px; }
.reviews-tabs { display: flex; gap: 4px; background: var(--gray-100); padding: 4px; border-radius: 10px; width: fit-content; }
.tab-btn {
  padding: 6px 14px; border: none; background: none; border-radius: 8px;
  font-size: .8rem; font-weight: 600; color: var(--gray-500); cursor: pointer;
  display: flex; align-items: center; gap: 6px; transition: all .2s;
}
.tab-btn:hover { color: var(--indigo); }
.tab-btn.active { background: #fff; color: var(--teal-dk); box-shadow: var(--shadow-sm); }
.tab-count {
  font-size: .68rem; background: var(--gray-200); color: var(--gray-500);
  padding: 1px 6px; border-radius: 10px;
}
.tab-btn.active .tab-count { background: var(--teal-lt); color: var(--teal-dk); }
.stat-item { text-align: center; }
.stat-value { display: block; font-size: 1.6rem; font-weight: 800; color: var(--indigo); line-height: 1; }
.stat-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--gray-400); letter-spacing: .08em; }

.review-row { padding: 24px 0; border-bottom: 1px solid var(--gray-100); }
.review-row:last-child { border-bottom: none; }
.review-row__top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
.review-row__author { display: flex; gap: 12px; align-items: center; }
.review-avatar {
  width: 42px; height: 42px; border-radius: 50%; background: var(--teal-lt); color: var(--teal-dk);
  font-family: 'Fraunces', serif; font-size: .95rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.review-name { font-weight: 700; font-size: .9rem; color: var(--indigo); }
.review-item { font-size: .78rem; color: var(--gray-400); margin-top: 2px; }
.review-item em { color: var(--teal); font-style: normal; font-weight: 600; }
.review-item__type {
  display: inline-block; margin-left: 6px; font-size: .65rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .05em;
  background: var(--gray-100); color: var(--gray-500);
  padding: 2px 8px; border-radius: 4px;
}
.review-row__right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
.review-stars { display: flex; gap: 2px; justify-content: flex-end; }
.star         { color: #FFB400; font-size: .9rem; }
.star--empty  { color: var(--gray-200); }
.review-date  { font-size: .74rem; color: var(--gray-400); }

.review-comment { font-size: .9rem; color: var(--gray-600); line-height: 1.7; font-style: italic; margin-bottom: 16px; padding-left: 54px; }

.review-reply {
  background: var(--gray-50); border-left: 4px solid var(--teal);
  border-radius: 0 12px 12px 0; padding: 14px 18px; margin-bottom: 12px; margin-left: 54px;
}
.review-reply__header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;
}
.review-reply__label {
  font-size: .68rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: .08em; color: var(--teal-dk); margin: 0;
}
.review-reply__text { font-size: .86rem; color: var(--indigo); line-height: 1.6; flex: 1; margin: 0; }

.review-reply__actions { display: flex; gap: 12px; align-items: center; }
.reply-edit-btn, .reply-delete-btn {
  background: none; border: none; font-size: .72rem; font-weight: 600; color: var(--gray-400);
  cursor: pointer; font-family: 'DM Sans', sans-serif; padding: 0;
  transition: all .2s; display: flex; align-items: center; gap: 4px;
}
.reply-edit-btn:hover   { color: var(--teal-dk); }
.reply-delete-btn       { color: #e74c3c; opacity: 0.7; }
.reply-delete-btn:hover { color: #e74c3c; opacity: 1; }

.review-comment-actions { display: flex; gap: 10px; margin-top: 10px; padding-left: 54px; align-items: center; }
.tbl-btn {
  border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: all var(--transition); border-radius: 8px;
}
.tbl-btn--reply {
  padding: 6px 18px; font-size: .82rem; font-weight: 700; font-family: 'DM Sans', sans-serif;
  background: var(--teal-lt); color: var(--teal-dk); border-radius: 50px;
}
.tbl-btn--reply:hover { background: var(--teal); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px var(--teal-lt); }

.reviews-loading { padding: 64px 20px; text-align: center; color: var(--gray-400); }
.loading-spinner {
  width: 40px; height: 40px; border: 3px solid var(--gray-100); border-top-color: var(--teal);
  border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 16px;
}
@keyframes spin { to { transform: rotate(360deg); } }

.reviews-empty {
  padding: 80px 20px; text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 12px;
  color: var(--gray-400);
}
.reviews-empty__icon { font-size: 3rem; margin-bottom: 8px; opacity: .5; }
.reviews-empty h3 { font-size: 1.4rem; color: var(--indigo); }
.reviews-footer-avg strong { color: var(--indigo); font-weight: 800; font-size: 1rem; }
</style>