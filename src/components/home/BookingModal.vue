<template>
  <Teleport to="body">
    <Transition name="fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal" role="dialog" aria-label="Book package">

          <button class="modal__close" @click="close" aria-label="Close">✕</button>

          <h2 class="modal__title">Book this package</h2>
          <p class="modal__sub">Fill in your details to confirm your reservation.</p>

          <!-- Package summary pill -->
          <div class="pkg-summary" v-if="pkg">
            <div class="pkg-summary__img">
              <img :src="pkg.img" :alt="pkg.title" />
            </div>
            <div>
              <div class="pkg-summary__name">{{ pkg.title }}</div>
              <div class="pkg-summary__meta">{{ pkg.duration }} days · {{ pkg.agency }}</div>
            </div>
            <div class="pkg-summary__price-wrap" v-if="pkg.activeOffer">
              <s class="pkg-summary__price-old">${{ pkg.price?.toLocaleString() }}</s>
              <div class="pkg-summary__price pkg-summary__price--sale">${{ (pkg.price * (1 - pkg.activeOffer.discount / 100)).toLocaleString(undefined, {maximumFractionDigits: 0}) }}</div>
            </div>
            <div class="pkg-summary__price" v-else>${{ pkg.price?.toLocaleString() }}</div>
          </div>

          <!-- Form -->
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Check-in date</label>
              <input class="form-input" v-model="form.checkin" type="date" />
            </div>
            <div class="form-group">
              <label class="form-label">Number of guests</label>
              <input class="form-input" v-model="form.guests" type="number" min="1" placeholder="2" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Full name</label>
            <input class="form-input" v-model="form.name" placeholder="Your full name" />
          </div>

          <div class="form-group">
            <label class="form-label">Special requests</label>
            <input class="form-input" v-model="form.notes" placeholder="Any preferences or notes…" />
          </div>

          <button class="btn btn-coral modal__submit" @click="submit">
            Confirm Booking
          </button>

          <p class="modal__note">🔒 You won't be charged yet.</p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  modelValue: { type: Boolean, default: false },
  pkg:        { type: Object,  default: null },
})
const emit = defineEmits(['update:modelValue', 'submit'])

const form = ref({ checkin: '', guests: '', name: '', notes: '' })

function close()  { emit('update:modelValue', false) }
function submit() { emit('submit', { ...form.value }); close() }
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(0,0,0,.40); z-index: 200;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal {
  background: var(--white); border-radius: 24px;
  width: 100%; max-width: 520px; padding: 44px;
  position: relative; box-shadow: 0 24px 80px rgba(0,0,0,.20);
  max-height: 90vh; overflow-y: auto;
}
.modal__close {
  position: absolute; top: 20px; right: 20px;
  width: 36px; height: 36px; border: none; border-radius: 50%;
  background: var(--gray-100); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; color: var(--gray-600); transition: background var(--transition);
}
.modal__close:hover { background: var(--gray-200); }

.modal__title { font-family: 'Fraunces', serif; font-size: 1.8rem; font-weight: 700; margin-bottom: 6px; }
.modal__sub   { font-size: .9rem; color: var(--gray-400); margin-bottom: 24px; }

/* Package summary */
.pkg-summary {
  display: flex; align-items: center; gap: 14px;
  background: var(--gray-50); border-radius: 14px; padding: 14px;
  margin-bottom: 28px;
}
.pkg-summary__img {
  width: 54px; height: 54px; border-radius: 10px; overflow: hidden; flex-shrink: 0;
}
.pkg-summary__img img { height: 100%; }
.pkg-summary__name { font-weight: 600; font-size: .92rem; color: var(--indigo); }
.pkg-summary__meta { font-size: .78rem; color: var(--gray-400); margin-top: 2px; }
.pkg-summary__price-wrap { display: flex; flex-direction: column; align-items: flex-end; margin-left: auto; }
.pkg-summary__price-old { font-size: .8rem; color: var(--gray-400); text-decoration: line-through; margin-bottom: -2px; }
.pkg-summary__price {
  font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700;
  color: var(--indigo); margin-left: auto;
}
.pkg-summary__price--sale { color: var(--coral); margin-left: 0; }


/* Form */
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 18px; }
.form-label { font-size: .85rem; font-weight: 600; color: var(--indigo); margin-bottom: 8px; display: block; }
.form-input {
  width: 100%; padding: 13px 16px; border: 1.5px solid var(--gray-200);
  border-radius: 12px; font-family: 'DM Sans', sans-serif; font-size: .95rem; color: var(--indigo);
  outline: none; transition: border-color var(--transition); background: var(--white);
}
.form-input:focus { border-color: var(--coral); }

.modal__submit { width: 100%; padding: 15px; font-size: 1rem; margin-top: 6px; }
.modal__note   { text-align: center; font-size: .82rem; color: var(--gray-400); margin-top: 14px; }

.fade-enter-active, .fade-leave-active { transition: opacity .2s ease, transform .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: scale(.97); }

@media (max-width: 480px) {
  .modal { padding: 28px 20px; }
  .form-row { grid-template-columns: 1fr; }
}
</style>
