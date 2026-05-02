<template>
  <div class="dest-highlights">

    <!-- Quick facts strip -->
    <div class="dest-facts detail-card" v-if="facts?.length">
      <div class="dest-fact" v-for="f in facts" :key="f.label">
        <span class="dest-fact__icon">{{ f.icon }}</span>
        <div>
          <div class="dest-fact__label">{{ f.label }}</div>
          <div class="dest-fact__val">{{ f.value }}</div>
        </div>
      </div>
    </div>

    <!-- About -->
    <div class="dest-section detail-card">
      <h3 class="dest-section__title">About {{ name }}</h3>
      <div class="dest-section__text">
        <p v-if="longDesc.length > 350">
          {{ longDesc.slice(0, 320) }}...
          <button class="read-more-btn" @click="isModalOpen = true">Read more</button>
        </p>
        <p v-else>{{ longDesc }}</p>
        
        <p v-if="longDesc.length < 200" class="dest-section__text-secondary">
          Experience the vibrant culture, rich history, and breathtaking landscapes that make this destination truly unique. Whether you're seeking adventure, relaxation, or culinary delights, you'll find an unforgettable journey waiting for you.
        </p>
      </div>
    </div>

    <!-- ── Full Description Modal ── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isModalOpen" class="desc-modal-overlay" @click.self="isModalOpen = false">
          <div class="desc-modal">
            <div class="desc-modal__header">
              <h3>About {{ name }}</h3>
              <button class="desc-modal__close" @click="isModalOpen = false">✕</button>
            </div>
            <div class="desc-modal__content">
              <p>{{ longDesc }}</p>
              <p v-if="longDesc.length < 200" class="desc-modal__secondary">
                Experience the vibrant culture, rich history, and breathtaking landscapes that make this destination truly unique. Whether you're seeking adventure, relaxation, or culinary delights, you'll find an unforgettable journey waiting for you.
              </p>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Highlights grid -->
    <div class="dest-section detail-card" v-if="highlights?.length">
      <h3 class="dest-section__title">Highlights</h3>
      <div class="dest-highlights-grid">
        <div class="highlight-card" v-for="h in highlights" :key="h">
          <div class="highlight-card__icon">{{ getIcon(h) }}</div>
          <div class="highlight-card__label">{{ h }}</div>
        </div>
      </div>
    </div>





  </div>
</template>

<script setup>
import { ref } from 'vue'

const isModalOpen = ref(false)
defineProps({
  name:        { type: String, required: true },
  longDesc:    { type: String, default: ''    },
  highlights:  { type: Array,  default: () => [] },

  facts:       { type: Array,  default: () => [] },
  bestTime:    { type: String, default: ''    },
})

const iconMap = {
  'sunsets': '🌅', 'volcanic beaches': '🌋', 'greek cuisine': '🥙',
  'temples': '⛩️', 'tea ceremony': '🍵', 'bamboo forest': '🎋',
  'souks': '🏺', 'riads': '🕌', 'desert day trips': '🐪',
  'rice terraces': '🌾', 'surfing': '🏄', 'spiritual healing': '🧘',
  'trekking': '🥾', 'glaciers': '🧊', 'wildlife': '🦅',
  'boat trips': '🚤', 'hiking trails': '🥾', 'coastal villages': '🏘️',
  'central park': '🌳', 'broadway': '🎭', 'food scene': '🍔',
  'safari': '🚙', 'big five': '🦁', 'hot air balloon': '🎈',
  'skiing': '⛷️', 'hiking': '🥾', 'scenic trains': '🚂',
  'overwater villas': '🛖', 'snorkelling': '🤿', 'diving': '🤿',
  'old town square': '🏰', 'castle district': '🏰', 'river cruises': '🛳️',
  'jungle treks': '🌴', 'river boats': '🛶', 'wildlife spotting': '🦜'
}

function getIcon(h) {
  const key = h.toLowerCase()
  return iconMap[key] || '✨'
}
</script>

<style scoped>
/* Quick facts */
.dest-facts {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px;
}
.dest-fact {
  background: var(--white); border-radius: 12px; padding: 16px;
  display: flex; align-items: center; gap: 12px;
  border: 1px solid var(--gray-200); box-shadow: var(--shadow-sm);
}
.dest-fact__icon  { font-size: 1.3rem; flex-shrink: 0; }
.dest-fact__label { font-size: .72rem; color: var(--gray-400); font-weight: 500; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.05em; }
.dest-fact__val   { font-size: .95rem; font-weight: 700; color: var(--indigo); }

/* Sections */
.dest-section       { }
.dest-section__title{
  font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 20px;
}
.dest-section__text p { font-size: .98rem; color: var(--gray-600); line-height: 1.8; margin-bottom: 16px; }
.dest-section__text-secondary { color: var(--gray-500); }

/* Highlights Grid */
.dest-highlights-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
.highlight-card {
  background: var(--white); border: 1px solid var(--gray-200); border-radius: 16px;
  padding: 16px; display: flex; flex-direction: column; align-items: flex-start; gap: 10px;
  box-shadow: var(--shadow-sm); transition: transform 0.3s ease, border-color 0.3s ease;
}
.highlight-card:hover { transform: translateY(-3px); border-color: var(--teal); box-shadow: var(--shadow-md); }
.highlight-card__icon {
  width: 40px; height: 40px; border-radius: 10px; background: var(--teal-lt);
  display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
}
.highlight-card__label { font-size: .88rem; font-weight: 700; color: var(--indigo); line-height: 1.2; }



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
.desc-modal__secondary { color: var(--gray-500); font-style: italic; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>