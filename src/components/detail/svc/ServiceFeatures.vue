<template>
  <div class="svc-features">
    <!-- Long description -->
    <div class="svc-features__section detail-card">
      <h3 class="svc-features__title">About this service</h3>
      <p v-if="longDesc.length > 350" class="svc-features__text">
        {{ longDesc.slice(0, 320) }}...
        <button class="read-more-btn" @click="isModalOpen = true">Read more</button>
      </p>
      <p v-else class="svc-features__text">{{ longDesc }}</p>
    </div>

    <!-- ── Full Description Modal ── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isModalOpen" class="desc-modal-overlay" @click.self="isModalOpen = false">
          <div class="desc-modal">
            <div class="desc-modal__header">
              <h3>About this service</h3>
              <button class="desc-modal__close" @click="isModalOpen = false">✕</button>
            </div>
            <div class="desc-modal__content">
              <p>{{ longDesc }}</p>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Features checklist -->
    <div class="svc-features__section detail-card" v-if="features?.length">
      <h3 class="svc-features__title">What's included</h3>
      <div class="svc-features__grid">
        <div class="svc-feature" v-for="f in features" :key="f">
          <span class="svc-feature__check">✓</span>
          <span>{{ f }}</span>
        </div>
      </div>
    </div>

    <!-- Vehicle / option table (optional) -->
    <div class="svc-features__section detail-card" v-if="options?.length">
      <h3 class="svc-features__title">{{ optionsTitle }}</h3>
      <div class="svc-options">
        <div class="svc-option" v-for="opt in options" :key="opt.type">
          <div class="svc-option__type">{{ opt.type }}</div>
          <div class="svc-option__capacity" v-if="opt.capacity">{{ opt.capacity }}</div>
          <div class="svc-option__price">${{ opt.price }} <span>/ {{ unit }}</span></div>
          <button class="btn btn-coral svc-option__btn" @click="$emit('select-option', opt)">Select</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'

const isModalOpen = ref(false)
defineProps({
  longDesc:     { type: String, default: ''    },
  features:     { type: Array,  default: () => [] },
  options:      { type: Array,  default: () => [] },
  optionsTitle: { type: String, default: 'Options' },
  unit:         { type: String, default: 'trip' },
})
defineEmits(['select-option'])
</script>

<style scoped>
.svc-features__section { margin-bottom: 0; }
.svc-features__section:not(:last-child) { margin-bottom: 32px; }
.svc-features__title {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 16px;
}
.svc-features__text { font-size: .95rem; color: var(--gray-600); line-height: 1.75; }

/* Features grid */
.svc-features__grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;
}
.svc-feature {
  display: flex; align-items: center; gap: 10px;
  font-size: .88rem; color: var(--gray-600); background: var(--gray-50);
  padding: 10px 14px; border-radius: 10px;
}
.svc-feature__check { color: var(--teal); font-weight: 700; flex-shrink: 0; }

/* Options table */
.svc-options { display: flex; flex-direction: column; gap: 10px; }
.svc-option {
  display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
  padding: 16px 20px; background: var(--gray-50); border-radius: 12px;
  border: 1.5px solid var(--gray-200); transition: border-color var(--transition);
}
.svc-option:hover { border-color: var(--coral); }
.svc-option__type     { font-weight: 700; font-size: .92rem; color: var(--indigo); flex: 1; }
.svc-option__capacity { font-size: .82rem; color: var(--gray-400); }
.svc-option__price    { font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: var(--coral); }
.svc-option__price span { font-size: .75rem; color: var(--gray-400); font-family: 'DM Sans', sans-serif; font-weight: 400; }
.svc-option__btn      { padding: 8px 18px; font-size: .82rem; }
.read-more-btn {
  background: none; border: none; padding: 0;
  color: var(--coral); font-weight: 700; cursor: pointer;
  margin-left: 5px; font-family: inherit; font-size: .95rem;
}
.read-more-btn:hover { text-decoration: underline; color: var(--coral-dk); }

/* ── Modal Styles ── */
.desc-modal-overlay {
  position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); z-index: 2000;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.desc-modal {
  background: var(--white); width: 100%; max-width: 650px;
  max-height: 80vh; border-radius: 20px; overflow: hidden;
  display: flex; flex-direction: column; box-shadow: var(--shadow-xl);
}
.desc-modal__header {
  padding: 24px 32px; border-bottom: 1px solid var(--gray-100);
  display: flex; align-items: center; justify-content: space-between;
}
.desc-modal__header h3 { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--indigo); }
.desc-modal__close { background: none; border: none; font-size: 1.4rem; color: var(--gray-400); cursor: pointer; }
.desc-modal__close:hover { color: var(--indigo); }
.desc-modal__content { padding: 32px; overflow-y: auto; flex: 1; }
.desc-modal__content p { font-size: 1rem; color: var(--gray-600); line-height: 1.8; margin-bottom: 16px; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>