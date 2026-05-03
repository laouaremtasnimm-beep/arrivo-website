<template>
  <div class="review-card" :class="{ 'review-card--editing': isEditing }">
    <div class="review-card__top">
      <div class="review-card__author">
        <div class="review-card__avatar">{{ review.name ? review.name[0] : 'U' }}</div>
        <div>
          <div class="review-card__name">{{ review.name }}</div>
          <div class="review-card__location">{{ review.location }}</div>
        </div>
      </div>
      <div class="review-card__top-right">
        <div class="review-card__right">
          <div class="review-card__stars" v-if="!isEditing">
            <span class="star" v-for="i in review.rating" :key="i">★</span>
            <span class="star star--empty" v-for="i in 5 - review.rating" :key="'e'+i">★</span>
          </div>
          <div class="review-card__date-row">
            <div class="review-card__date">{{ review.date }}</div>
            <button v-if="canDelete && !isEditing" class="review-card__delete-btn" title="Delete Review" @click="handleDelete">🗑️</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Mode -->
    <div v-if="isEditing" class="review-card__edit-form">
      <div class="edit-form__rating">
        <label>Rating:</label>
        <div class="star-rating">
          <span
            v-for="i in 5"
            :key="i"
            class="star-btn"
            :class="{ active: i <= editRating }"
            @click="editRating = i"
          >★</span>
        </div>
      </div>
      <textarea v-model="editText" class="edit-form__textarea" rows="3" placeholder="Update your review..."></textarea>
      <div class="edit-form__actions">
        <button class="btn-cancel" @click="isEditing = false" :disabled="loading">Cancel</button>
        <button class="btn-save" @click="handleSave" :disabled="loading">{{ loading ? 'Saving...' : 'Save' }}</button>
      </div>
    </div>

    <!-- View Mode -->
    <div v-else>
      <p class="review-card__text">"{{ review.text }}"</p>

      <!-- Reply display -->
      <div class="review-card__reply" v-if="review.reply && !isReplying">
        <div class="review-card__reply-header">
          <span class="review-card__reply-label">Response from owner</span>
          <div class="review-card__reply-meta" v-if="canReply">
            <div class="reply-kebab">
              <button class="reply-kebab-btn" @click="isReplyMenuOpen = !isReplyMenuOpen">⋮</button>
              <div class="reply-kebab-menu" v-if="isReplyMenuOpen">
                <button @click="openReplyMenu('edit')">✎ Edit reply</button>
                <button @click="openReplyMenu('delete')" class="danger">🗑 Delete reply</button>
              </div>
              <div class="reply-kebab-overlay" v-if="isReplyMenuOpen" @click="isReplyMenuOpen = false"></div>
            </div>
          </div>
        </div>
        <p class="review-card__reply-text">{{ review.reply }}</p>
      </div>

      <!-- Reply form -->
      <div v-if="canReply && isReplying" class="review-card__reply-form">
        <textarea
          v-model="replyText"
          class="review-card__reply-input"
          rows="3"
          :placeholder="review.reply ? 'Edit your reply...' : 'Write your reply...'"
          autofocus
        />
        <div class="review-card__reply-actions">
          <button @click="isReplying = false">Cancel</button>
          <button @click="submitReply" :disabled="!replyText.trim() || replyLoading">
            {{ replyLoading ? 'Posting...' : 'Post Reply' }}
          </button>
        </div>
      </div>

      <!-- Reply button (only if no reply yet and can reply) -->
      <button
        v-if="canReply && !review.reply && !isReplying"
        class="review-card__reply-btn review-card__reply-btn--main"
        @click="openReply"
      >↩ Reply</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuth } from '@/composables/useAuth.js'
import { useNotifications } from '@/composables/useNotifications'
import { useReviews } from '@/composables/useReviews'

const props = defineProps({
  review:      { type: Object,  required: true },
  canModerate: { type: Boolean, default: false },
  itemOwnerId: { type: [Number, String], default: null },
})
const emit = defineEmits(['deleted', 'updated'])

const { user, isLoggedIn, isAgency, isProvider } = useAuth()
const { saveReply: apiSaveReply, deleteReview: apiDeleteReview } = useReviews()

const isMine = computed(() => {
  if (!isLoggedIn.value || !user.value) return false
  return props.review.user_id === Number(user.value.userID ?? user.value.id)
})

const canDelete = computed(() => isMine.value)

// Reply allowed if user is agency/provider AND owns this item (canModerate is passed as isOwner)
const canReply = computed(() => {
  if (!isLoggedIn.value || !user.value) return false
  if (!isAgency.value && !isProvider.value) return false
  return props.canModerate
})

const isEditing   = ref(false)
const isMenuOpen  = ref(false)
const editRating  = ref(props.review.rating)
const editText    = ref(props.review.text)
const loading     = ref(false)

const isReplying      = ref(false)
const isReplyMenuOpen = ref(false)
const replyText       = ref(props.review.reply || '')
const replyLoading    = ref(false)

function openReplyMenu(action) {
  isReplyMenuOpen.value = false
  if (action === 'edit')   openReply()
  if (action === 'delete') deleteReply()
}

function openReply() {
  replyText.value = props.review.reply || ''
  isReplying.value = true
}

function startEdit() {
  isMenuOpen.value = false
  isEditing.value  = true
}

async function submitReply() {
  if (!replyText.value.trim()) return
  replyLoading.value = true
  try {
    await apiSaveReply(
      props.review.id || props.review.reviewID,
      replyText.value.trim()
    )
    props.review.reply = replyText.value.trim()
    isReplying.value   = false
  } catch (e) {
    console.error('submitReply failed:', e)
  } finally {
    replyLoading.value = false
  }
}

async function deleteReply() {
  if (!confirm('Delete this reply?')) return
  replyLoading.value = true
  try {
    const data = await apiSaveReply(props.review.id || props.review.reviewID, '')
    props.review.reply = null
  } catch (e) {
    console.error('deleteReply failed:', e)
  } finally {
    replyLoading.value = false
  }
}

async function handleDelete() {
  isMenuOpen.value = false
  if (!confirm('Are you sure you want to delete this review?')) return
  loading.value = true
  try {
    await apiDeleteReview(props.review.id || props.review.reviewID)
    emit('deleted', props.review.id || props.review.reviewID)
  } catch (e) {
    alert(e.message)
  } finally {
    loading.value = false
  }
}

async function handleSave() {
  loading.value = true
  try {
    const res = await fetch('/arrivo-website/backend/api/v1/reviews.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: props.review.id, rating: editRating.value, comment: editText.value }),
    })
    if (res.ok) {
      isEditing.value     = false
      props.review.rating = editRating.value
      props.review.text   = editText.value
      emit('updated', props.review)
    } else {
      alert('Failed to update review.')
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.review-card {
  background: var(--white); border: 1px solid var(--gray-100); border-radius: 16px;
  padding: 24px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative;
}
.review-card:hover { border-color: var(--teal-lt); box-shadow: var(--shadow-md); transform: translateY(-2px); }
.review-card--editing { border-color: var(--teal); box-shadow: var(--shadow-lg); }

.review-card__top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
.review-card__author { display: flex; align-items: center; gap: 12px; }
.review-card__avatar {
  width: 44px; height: 44px; border-radius: 50%; background: var(--teal-lt);
  color: var(--teal-dk); display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 1.1rem; border: 2px solid var(--white); box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.review-card__name { font-weight: 700; color: var(--indigo); font-size: 0.95rem; }
.review-card__location { font-size: 0.75rem; color: var(--gray-400); margin-top: 1px; }

.review-card__right { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
.review-card__date-row { display: flex; align-items: center; gap: 8px; }
.review-card__date { font-size: .74rem; color: var(--gray-400); }
.review-card__delete-btn {
  background: none; border: none; font-size: 0.9rem; cursor: pointer;
  opacity: 0.3; transition: all 0.2s; padding: 2px;
}
.review-card__delete-btn:hover { opacity: 1; transform: scale(1.1); }

.review-card__body {
  display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;
}
.review-card__text {
  font-size: .88rem; color: var(--gray-600); line-height: 1.7; font-style: italic; 
  flex: 1; min-width: 0; word-break: break-word; overflow-wrap: break-word;
}
.review-card__stars-side {
  display: flex; gap: 2px; flex-shrink: 0; margin-top: 4px;
}

/* Kebab Menu */
.review-card__top-right { display: flex; align-items: flex-start; gap: 8px; }
.review-card__kebab-wrapper { position: relative; margin-top: -4px; margin-right: -8px; }

.kebab-btn {
  background: none; border: none; font-size: 1.2rem; line-height: 1; padding: 4px 8px;
  color: var(--gray-400); cursor: pointer; border-radius: 4px; transition: color 0.2s;
}
.kebab-btn:hover { color: var(--indigo); background: var(--gray-50); }

.kebab-overlay { position: fixed; inset: 0; z-index: 100; }
.kebab-menu {
  position: absolute; top: 100%; right: 0; z-index: 101;
  background: var(--white); border-radius: 8px; border: 1px solid var(--gray-100);
  box-shadow: var(--shadow-md); padding: 4px 0; min-width: 130px;
}
.kebab-item {
  display: block; width: 100%; text-align: left; background: none; border: none;
  padding: 10px 16px; font-size: .85rem; font-weight: 500; color: var(--gray-600);
  cursor: pointer; transition: background 0.2s, color 0.2s;
}
.kebab-item:hover { background: var(--gray-50); color: var(--indigo); }
.kebab-item--danger:hover { color: var(--coral); }

.dropdown-fade-enter-active, .dropdown-fade-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-fade-enter-from, .dropdown-fade-leave-to { opacity: 0; transform: translateY(-5px) scale(0.95); transform-origin: top right; }

/* Edit Form */
.review-card__edit-form {
  display: flex; flex-direction: column; gap: 12px; margin-top: 10px;
}
.edit-form__rating {
  display: flex; align-items: center; gap: 12px; font-size: .85rem; font-weight: 600; color: var(--indigo);
}
.star-rating { display: flex; gap: 4px; }
.star-btn { color: var(--gray-200); font-size: 1.2rem; cursor: pointer; transition: color 0.2s; }
.star-btn:hover, .star-btn.active { color: #FFB400; }

.edit-form__textarea {
  width: 100%; padding: 12px; border: 1px solid var(--gray-200);
  border-radius: 8px; font-family: inherit; font-size: .85rem;
  resize: vertical; outline: none; transition: border-color 0.2s;
}
.edit-form__textarea:focus { border-color: var(--teal); }

.edit-form__actions {
  display: flex; justify-content: flex-end; gap: 10px;
}
.btn-cancel, .btn-save {
  padding: 6px 14px; border-radius: 6px; font-size: .8rem; font-weight: 600; cursor: pointer;
  border: none; transition: all 0.2s;
}
.btn-cancel { background: var(--gray-100); color: var(--gray-600); }
.btn-cancel:hover { background: var(--gray-200); }
.btn-save { background: var(--teal); color: var(--white); }
.btn-save:hover { background: var(--teal-dk); }
.btn-save:disabled, .btn-cancel:disabled { opacity: 0.6; cursor: not-allowed; }

.review-card__reply {
  background: var(--gray-50); border-left: 3px solid var(--teal);
  border-radius: 0 8px 8px 0; padding: 12px 14px; margin: 12px 0;
}
.review-card__reply-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;
}
.review-card__reply-label {
  font-size: .7rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; color: var(--teal); display: block;
}
.review-card__reply-text { font-size: .84rem; color: var(--indigo); margin: 0; line-height: 1.6; }

.review-card__reply-meta { display: flex; gap: 8px; align-items: center; }

.reply-kebab { position: relative; }
.reply-kebab-btn {
  background: none; border: none; font-size: 1.1rem; color: var(--gray-400);
  cursor: pointer; padding: 4px 8px; border-radius: 4px; transition: all 0.2s;
  display: flex; align-items: center;
}
.reply-kebab-btn:hover { color: var(--indigo); background: rgba(0,0,0,0.05); }

.reply-kebab-menu {
  position: absolute; top: 100%; right: 0; z-index: 101;
  background: var(--white); border-radius: 10px; border: 1px solid var(--gray-100);
  box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 5px 0; min-width: 140px;
  animation: slideIn 0.2s ease;
}
@keyframes slideIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

.reply-kebab-menu button {
  display: block; width: 100%; text-align: left; background: none; border: none;
  padding: 10px 16px; font-size: .82rem; font-weight: 600; color: var(--gray-600);
  cursor: pointer; transition: background 0.2s;
}
.reply-kebab-menu button:hover { background: var(--gray-50); color: var(--indigo); }
.reply-kebab-menu button.danger { color: #e74c3c; }
.reply-kebab-menu button.danger:hover { background: #fff5f5; }

.reply-kebab-overlay { position: fixed; inset: 0; z-index: 100; }

.review-card__reply-btn--main {
  background: none; border: 1.5px solid var(--gray-200); border-radius: 50px;
  padding: 5px 14px; margin-top: 8px; font-size: .78rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; transition: all 0.2s;
  font-family: 'DM Sans', sans-serif;
}
.review-card__reply-btn--main:hover { border-color: var(--teal); color: var(--teal); }

.review-card__reply-input {
  width: 100%; padding: 10px 12px; border: 1.5px solid var(--gray-200);
  border-radius: 10px; font-size: .84rem; font-family: inherit;
  resize: none; outline: none; margin-top: 8px; box-sizing: border-box;
}
.review-card__reply-input:focus { border-color: var(--teal); }
.review-card__reply-actions {
  display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px;
}
.review-card__reply-actions button {
  padding: 6px 16px; border-radius: 50px; font-size: .78rem;
  font-weight: 600; cursor: pointer; border: none;
}
.review-card__reply-actions button:first-child {
  background: var(--gray-100); color: var(--gray-600);
}
.review-card__reply-actions button:last-child {
  background: var(--teal); color: #fff;
}
.review-card__reply-actions button:disabled { opacity: .4; cursor: not-allowed; }
</style>