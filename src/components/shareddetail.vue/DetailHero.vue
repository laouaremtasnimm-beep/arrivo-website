<template>
  <div class="detail-hero">

    <!-- Main image -->
    <div class="detail-hero__main" @click="lightboxIndex = 0">
      <img :src="mainImg" :alt="title" />
      <div class="detail-hero__overlay" />
    </div>

    <!-- Thumbnail strip -->
    <div class="detail-hero__thumbs" v-if="gallery?.length">
      <div
        class="detail-hero__thumb"
        v-for="(img, i) in gallery.slice(0, 3)"
        :key="i"
        @click="lightboxIndex = i + 1"
      >
        <img :src="img" :alt="`${title} ${i + 2}`" />
        <div class="detail-hero__thumb-more" v-if="i === 2 && gallery.length > 3">
          +{{ gallery.length - 3 }} photos
        </div>
      </div>
    </div>

    <!-- Floating actions -->
    <div class="detail-hero__actions">
      <button class="detail-hero__action-btn" @click="$router.back()" title="Go back">
        ← Back
      </button>
      <div class="detail-hero__action-right">
        <button
          class="detail-hero__action-btn"
          :class="{ saved }"
          @click="$emit('toggle-wishlist')"
          :title="saved ? 'Remove from saved' : 'Save'"
        >
          {{ saved ? '❤️' : '🤍' }} {{ saved ? 'Saved' : 'Save' }}
        </button>
        <button class="detail-hero__action-btn" @click="handleShare" title="Share">
          🔗 Share
        </button>
      </div>
    </div>

    <!-- Lightbox -->
    <Transition name="fade">
      <div class="lightbox" v-if="lightboxIndex !== null" @click.self="lightboxIndex = null">
        <button class="lightbox__close" @click="lightboxIndex = null">✕</button>
        <button class="lightbox__prev" @click="lightboxPrev" v-if="allImages.length > 1">‹</button>
        <img class="lightbox__img" :src="allImages[lightboxIndex]" :alt="title" />
        <button class="lightbox__next" @click="lightboxNext" v-if="allImages.length > 1">›</button>
        <div class="lightbox__counter">{{ lightboxIndex + 1 }} / {{ allImages.length }}</div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  mainImg: { type: String, required: true },
  gallery: { type: Array,  default: () => [] },
  title:   { type: String, required: true },
  saved:   { type: Boolean, default: false },
})

defineEmits(['toggle-wishlist'])

const lightboxIndex = ref(null)
const allImages = computed(() => [props.mainImg, ...(props.gallery || [])])

function lightboxPrev() {
  lightboxIndex.value = (lightboxIndex.value - 1 + allImages.value.length) % allImages.value.length
}
function lightboxNext() {
  lightboxIndex.value = (lightboxIndex.value + 1) % allImages.value.length
}

function handleShare() {
  if (navigator.share) {
    navigator.share({ title: props.title, url: window.location.href })
  } else {
    navigator.clipboard?.writeText(window.location.href)
    alert('Link copied to clipboard!')
  }
}
</script>

<style scoped>
.detail-hero {
  display: grid;
  grid-template-columns: 2fr 1fr;
  grid-template-rows: auto auto;
  gap: 4px;
  height: 480px;
  position: relative;
  border-radius: var(--radius-xl);
  overflow: hidden;
}

.detail-hero__main {
  grid-row: 1 / 3;
  position: relative; overflow: hidden; cursor: pointer;
}
.detail-hero__main img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform .6s ease;
}
.detail-hero__main:hover img { transform: scale(1.03); }
.detail-hero__overlay {
  position: absolute; inset: 0;
  background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,.25) 100%);
  pointer-events: none;
}

.detail-hero__thumbs { grid-column: 2; display: contents; }
.detail-hero__thumb {
  position: relative; overflow: hidden; cursor: pointer;
}
.detail-hero__thumb img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform .4s ease;
}
.detail-hero__thumb:hover img { transform: scale(1.06); }
.detail-hero__thumb-more {
  position: absolute; inset: 0;
  background: rgba(0,0,0,.5); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: .95rem;
}

/* Floating actions */
.detail-hero__actions {
  position: absolute; top: 16px; left: 16px; right: 16px;
  display: flex; align-items: center; justify-content: space-between;
  z-index: 5;
}
.detail-hero__action-right { display: flex; gap: 8px; }

.detail-hero__action-btn {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(255,255,255,.92); backdrop-filter: blur(8px);
  border: none; border-radius: 50px; padding: 8px 16px;
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 600;
  color: var(--indigo); cursor: pointer; transition: all var(--transition);
}
.detail-hero__action-btn:hover  { background: #fff; transform: translateY(-1px); box-shadow: var(--shadow); }
.detail-hero__action-btn.saved  { background: rgba(255,90,95,.1); color: var(--coral); }

/* Lightbox */
.lightbox {
  position: fixed; inset: 0; background: rgba(0,0,0,.92); z-index: 200;
  display: flex; align-items: center; justify-content: center;
}
.lightbox__img {
  max-width: 90vw; max-height: 85vh; object-fit: contain;
  border-radius: 8px; box-shadow: 0 0 60px rgba(0,0,0,.5);
}
.lightbox__close {
  position: absolute; top: 20px; right: 20px;
  background: rgba(255,255,255,.15); border: none; color: #fff;
  width: 42px; height: 42px; border-radius: 50%;
  font-size: 1.1rem; cursor: pointer; transition: background var(--transition);
}
.lightbox__close:hover { background: rgba(255,255,255,.25); }

.lightbox__prev, .lightbox__next {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(255,255,255,.15); border: none; color: #fff;
  width: 48px; height: 48px; border-radius: 50%;
  font-size: 1.5rem; cursor: pointer; transition: background var(--transition);
}
.lightbox__prev { left: 20px; }
.lightbox__next { right: 20px; }
.lightbox__prev:hover, .lightbox__next:hover { background: rgba(255,255,255,.25); }

.lightbox__counter {
  position: absolute; bottom: 20px;
  background: rgba(0,0,0,.5); color: #fff;
  font-size: .82rem; padding: 5px 14px; border-radius: 50px;
}

.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .detail-hero { grid-template-columns: 1fr; grid-template-rows: 260px; height: auto; }
  .detail-hero__main { grid-row: 1; }
  .detail-hero__thumb { display: none; }
}
</style>