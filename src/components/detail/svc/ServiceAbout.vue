<template>
  <div class="svc-about detail-card">
    <h3 class="svc-about__title">About this service</h3>
    <p v-if="longDesc.length > 350" class="svc-about__text">
      {{ longDesc.slice(0, 320) }}...
      <button class="read-more-btn" @click="isModalOpen = true">Read more</button>
    </p>
    <p v-else class="svc-about__text">{{ longDesc }}</p>

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
  </div>
</template>

<script setup>
import { ref } from 'vue'

const isModalOpen = ref(false)
defineProps({
  longDesc: { type: String, default: '' }
})
</script>

<style scoped>
.svc-about__title {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 16px;
}
.svc-about__text { font-size: .95rem; color: var(--gray-600); line-height: 1.75; }

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
