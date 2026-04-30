<template>
  <div class="write-review" :class="{ 'write-review--open': isOpen }">
    <!-- Collapsed trigger -->
    <button v-if="!isOpen" class="write-review__trigger" @click="isOpen = true">
      <span class="write-review__trigger-icon">✏️</span>
      <span>Write a review</span>
      <span class="write-review__trigger-arrow">→</span>
    </button>

    <!-- Expanded form -->
    <Transition name="review-form">
      <div v-if="isOpen" class="write-review__form">
        <div class="write-review__form-header">
          <h3 class="write-review__form-title">Share your experience</h3>
          <button class="write-review__close" @click="cancel">✕</button>
        </div>

        <!-- Star rating picker -->
        <div class="write-review__rating-row">
          <span class="write-review__rating-label">Your rating</span>
          <div class="star-picker">
            <button
              v-for="n in 5"
              :key="n"
              class="star-picker__star"
              :class="{ filled: n <= hovered || n <= form.rating }"
              @mouseenter="hovered = n"
              @mouseleave="hovered = 0"
              @click="form.rating = n"
              type="button"
            >★</button>
          </div>
          <span class="write-review__rating-hint" v-if="form.rating">
            {{ ratingLabels[form.rating - 1] }}
          </span>
        </div>

        <!-- Comment -->
        <div class="write-review__field">
          <label class="write-review__field-label">Your review</label>
          <textarea
            v-model="form.comment"
            class="write-review__textarea"
            placeholder="Tell others what you loved (or didn't) about this experience…"
            rows="4"
          />
        </div>

        <!-- Error / success -->
        <Transition name="fade">
          <div v-if="error" class="write-review__msg write-review__msg--error">{{ error }}</div>
        </Transition>
        <Transition name="fade">
          <div v-if="success" class="write-review__msg write-review__msg--success">
            ✅ Thank you! Your review has been posted.
          </div>
        </Transition>

        <div class="write-review__actions">
          <button class="wr-btn wr-btn--cancel" @click="cancel" :disabled="loading">Cancel</button>
          <button
            class="wr-btn wr-btn--submit"
            @click="submit"
            :disabled="!form.rating || loading"
          >
            <span v-if="loading" class="wr-btn__spinner"></span>
            <span>{{ loading ? 'Posting…' : 'Post review' }}</span>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const props = defineProps({
  itemType: { type: String, required: true },   // 'package' | 'service' | 'destination'
  itemId:   { type: Number, required: true },
  userId:   { type: Number, default: null },     // pass logged-in user id
})

const emit = defineEmits(['submitted'])

const API_BASE = 'http://localhost/arrivo-website/backend/api/v1/reviews.php'

const isOpen  = ref(false)
const loading = ref(false)
const error   = ref('')
const success = ref(false)
const hovered = ref(0)

const ratingLabels = ['Poor', 'Below average', 'Good', 'Very good', 'Excellent']

const form = reactive({ rating: 0, comment: '' })

function cancel() {
  isOpen.value  = false
  error.value   = ''
  success.value = false
  form.rating   = 0
  form.comment  = ''
  hovered.value = 0
}

async function submit() {
  error.value   = ''
  success.value = false

  // Resolve user from prop or localStorage
  let uid = props.userId
  if (!uid) {
    try {
      const stored = localStorage.getItem('user')
      uid = stored ? JSON.parse(stored).id : null
    } catch { uid = null }
  }

  if (!uid) {
    error.value = 'You must be logged in to leave a review.'
    return
  }
  if (!form.rating) {
    error.value = 'Please select a star rating.'
    return
  }

  loading.value = true
  try {
    const res = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id:   uid,
        item_type: props.itemType,
        item_id:   props.itemId,
        rating:    form.rating,
        comment:   form.comment,
      }),
    })

    const data = await res.json()
    if (!res.ok) {
      error.value = data.error || 'Something went wrong.'
    } else {
      success.value = true
      emit('submitted', {
        id:         data.review_id,
        rating:     form.rating,
        comment:    form.comment,
        created_at: new Date().toISOString(),
      })
      setTimeout(() => {
        cancel()
      }, 2200)
    }
  } catch (e) {
    error.value = 'Network error — please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.write-review { margin-top: 32px; }

/* Trigger button */
.write-review__trigger {
  display: flex; align-items: center; gap: 10px;
  background: var(--white); border: 2px dashed var(--gray-200);
  border-radius: var(--radius); padding: 16px 20px; width: 100%;
  font-family: 'DM Sans', sans-serif; font-size: .92rem; font-weight: 600;
  color: var(--indigo); cursor: pointer; transition: all var(--transition);
}
.write-review__trigger:hover {
  border-color: var(--teal); background: var(--teal-lt); color: var(--teal-dk);
}
.write-review__trigger-icon { font-size: 1.1rem; }
.write-review__trigger-arrow { margin-left: auto; font-size: 1rem; transition: transform var(--transition); }
.write-review__trigger:hover .write-review__trigger-arrow { transform: translateX(4px); }

/* Expanded form */
.write-review__form {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); padding: 28px;
}
.write-review__form-header {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;
}
.write-review__form-title {
  font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: var(--indigo);
}
.write-review__close {
  background: none; border: none; font-size: 1rem; color: var(--gray-400);
  cursor: pointer; padding: 4px 8px; border-radius: 6px;
  transition: color var(--transition);
}
.write-review__close:hover { color: var(--indigo); }

/* Rating row */
.write-review__rating-row {
  display: flex; align-items: center; gap: 14px; margin-bottom: 20px; flex-wrap: wrap;
}
.write-review__rating-label {
  font-size: .82rem; font-weight: 700; color: var(--gray-600); min-width: 75px;
}
.star-picker { display: flex; gap: 4px; }
.star-picker__star {
  background: none; border: none; font-size: 1.6rem; cursor: pointer; color: var(--gray-200);
  transition: color .1s, transform .1s; line-height: 1; padding: 0;
}
.star-picker__star.filled { color: #FFB400; transform: scale(1.12); }
.star-picker__star:hover  { transform: scale(1.2); }
.write-review__rating-hint {
  font-size: .82rem; font-weight: 600; color: var(--teal-dk);
  background: var(--teal-lt); padding: 3px 10px; border-radius: 50px;
}

/* Textarea field */
.write-review__field { margin-bottom: 18px; }
.write-review__field-label {
  display: block; font-size: .82rem; font-weight: 700; color: var(--gray-600); margin-bottom: 8px;
}
.write-review__textarea {
  width: 100%; border: 1.5px solid var(--gray-200); border-radius: 10px;
  padding: 12px 14px; font-family: 'DM Sans', sans-serif; font-size: .9rem;
  color: var(--indigo); resize: vertical; outline: none; box-sizing: border-box;
  transition: border-color var(--transition);
}
.write-review__textarea:focus { border-color: var(--teal); }

/* Messages */
.write-review__msg {
  font-size: .84rem; font-weight: 600; padding: 10px 14px;
  border-radius: 8px; margin-bottom: 14px;
}
.write-review__msg--error   { background: rgba(231,76,60,.08); color: #e74c3c; border: 1px solid rgba(231,76,60,.2); }
.write-review__msg--success { background: rgba(39,174,96,.08);  color: #27ae60; border: 1px solid rgba(39,174,96,.2); }

/* Actions */
.write-review__actions { display: flex; gap: 10px; justify-content: flex-end; }
.wr-btn {
  padding: 9px 22px; border-radius: 50px; font-family: 'DM Sans', sans-serif;
  font-size: .85rem; font-weight: 700; cursor: pointer; transition: all var(--transition);
  display: flex; align-items: center; gap: 8px;
}
.wr-btn--cancel {
  background: none; border: 1.5px solid var(--gray-200); color: var(--gray-600);
}
.wr-btn--cancel:hover { border-color: var(--gray-400); color: var(--indigo); }
.wr-btn--submit {
  background: var(--teal); color: #fff; border: none;
}
.wr-btn--submit:hover:not(:disabled) { background: var(--teal-dk); }
.wr-btn--submit:disabled { opacity: .45; cursor: not-allowed; }
.wr-btn__spinner {
  width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.3);
  border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Transitions */
.review-form-enter-active { transition: all .22s ease; }
.review-form-leave-active { transition: all .18s ease; }
.review-form-enter-from, .review-form-leave-to { opacity: 0; transform: translateY(-8px); }

.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>