<template>
  <div class="review-card" :class="{ 'review-card--editing': isEditing }">
    <div class="review-card__top">
      <div class="review-card__author">
        <div class="review-card__avatar">{{ review.name[0] }}</div>
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
          <div class="review-card__date">{{ review.date }}</div>
        </div>
        
        <div class="review-card__kebab-wrapper" v-if="isMine && !isEditing">
          <button class="kebab-btn" @click="isMenuOpen = !isMenuOpen">⋮</button>
          
          <div class="kebab-overlay" v-if="isMenuOpen" @click="isMenuOpen = false"></div>
          
          <Transition name="dropdown-fade">
            <div class="kebab-menu" v-if="isMenuOpen">
              <button class="kebab-item" @click="startEdit">Edit review</button>
              <button class="kebab-item kebab-item--danger" @click="handleDelete" :disabled="loading">Delete</button>
            </div>
          </Transition>
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuth } from '@/composables/useAuth.js'

const props = defineProps({
  review: { type: Object, required: true },
})

const emit = defineEmits(['deleted', 'updated'])

const { user, isLoggedIn } = useAuth()
const isMine = computed(() => {
  if (!isLoggedIn.value || !user.value) return false
  const currentUserId = user.value.userID ?? user.value.id
  return props.review.user_id === Number(currentUserId)
})

const isEditing = ref(false)
const isMenuOpen = ref(false)
const editRating = ref(props.review.rating)
const editText = ref(props.review.text)
const loading = ref(false)

function startEdit() {
  isMenuOpen.value = false
  isEditing.value = true
}

async function handleDelete() {
  isMenuOpen.value = false
  if (!confirm('Are you sure you want to delete this review?')) return
  loading.value = true
  try {
    const res = await fetch('/arrivo-website/backend/api/v1/reviews.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: props.review.id })
    })
    if (res.ok) {
      emit('deleted', props.review.id)
    } else {
      alert('Failed to delete review.')
    }
  } catch (e) {
    console.error(e)
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
      body: JSON.stringify({
        id: props.review.id,
        rating: editRating.value,
        comment: editText.value
      })
    })
    if (res.ok) {
      isEditing.value = false
      props.review.rating = editRating.value
      props.review.text = editText.value
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
  background: var(--white); border-radius: var(--radius);
  padding: 22px; box-shadow: var(--shadow);
  transition: transform var(--transition);
}
.review-card:hover { transform: translateY(-2px); }

.review-card__top {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 12px; margin-bottom: 14px;
}
.review-card__author { display: flex; align-items: center; gap: 12px; }
.review-card__avatar {
  width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
  background: var(--sand); color: var(--indigo);
  font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.review-card__name     { font-weight: 600; font-size: .9rem; color: var(--indigo); }
.review-card__location { font-size: .76rem; color: var(--gray-400); margin-top: 2px; }

.review-card__right { text-align: right; flex-shrink: 0; }
.review-card__stars { display: flex; gap: 2px; justify-content: flex-end; margin-bottom: 3px; }
.star       { color: #FFB400; font-size: .82rem; }
.star--empty{ color: var(--gray-200); }
.review-card__date { font-size: .74rem; color: var(--gray-400); }

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
</style>