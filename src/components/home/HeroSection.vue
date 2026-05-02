<template>
  <section class="hero">
    <!-- Slideshow Background -->
    <div class="hero__bg-container">
      <transition name="fade">
        <div 
          :key="images[currentIndex]"
          class="hero__bg-slide"
          :style="{ backgroundImage: `url(${images[currentIndex]})` }"
        ></div>
      </transition>
      <!-- Dark Overlay -->
      <div class="hero__overlay"></div>
    </div>

    <div class="hero__content">
      <h1 class="hero__heading">
        Discover your next <span class="hero__heading-accent">adventure<svg class="hero__scribble" viewBox="0 0 200 8" fill="none" preserveAspectRatio="none"><path d="M2 6C30 3 60 4 90 5C120 6 150 5 198 2" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg></span> with ease
      </h1>

      <p class="hero__sub">
        Connect with top travel agencies, book curated packages, and find the
        best local services — all in one modern marketplace.
      </p>

      <div class="hero__cta-group">
        <div class="hero__cta">
          <button class="btn btn-primary-theme rounded-full" @click="scrollTo('#offers')">
            Start exploring
          </button>
          <button class="btn btn-ghost-white rounded-full" @click="router.push('/destinations')">
            Browse destinations
          </button>
        </div>
      </div>

      <div class="hero__trending">
        <span class="hero__trending-label">Trending now</span>
        <div class="hero__trending-tags">
          <span class="tag">Bali</span>
          <span class="tag">Swiss Alps</span>
          <span class="tag">Santorini</span>
        </div>
      </div>

      <div class="hero__stats-wrapper" ref="statsRef">
        <div class="hero__stats">
          <div class="hero__stat" v-for="s in stats" :key="s.label">
            <div class="hero__stat-num">{{ s.current }}<span>{{ s.suffix }}</span></div>
            <div class="hero__stat-label">{{ s.label }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const statsRef = ref(null)

const images = [
  'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?q=80&w=2000',
  'https://images.unsplash.com/photo-1479030160180-b1860951d696?q=80&w=2000',
  'https://images.unsplash.com/photo-1469474968028-56623f02e42e?q=80&w=2000',
  'https://cdn.mos.cms.futurecdn.net/aNBwuzsJEypiovBHG5sHke-2000-100.jpg.webp',
  'https://cdn.mos.cms.futurecdn.net/7S6jnGYqKcTQ59L6X6S2BX-2000-100.jpg.webp'
]

const currentIndex = ref(0)
let timer = null

const startSlideshow = () => {
  timer = setInterval(() => {
    currentIndex.value = (currentIndex.value + 1) % images.length
  }, 9000)
}

function scrollTo(id) {
  const el = document.querySelector(id)
  if (el) el.scrollIntoView({ behavior: 'smooth' })
}

const stats = ref([
  { target: 1200, current: 0, suffix: '+', label: 'Destinations' },
  { target: 340,  current: 0, suffix: '+', label: 'Travel Agencies' },
  { target: 4.9,  current: 0, suffix: '★', label: 'Avg Rating', isFloat: true },
])

function animateCount() {
  const duration = 1500
  const start = performance.now()

  function update(now) {
    const elapsed = now - start
    const progress = Math.min(elapsed / duration, 1)
    const easeOut = 1 - Math.pow(1 - progress, 3)

    stats.value.forEach(s => {
      let val = s.target * easeOut
      s.current = s.isFloat ? val.toFixed(1) : Math.floor(val)
    })

    if (progress < 1) requestAnimationFrame(update)
    else {
      stats.value.forEach(s => s.current = s.target)
    }
  }
  requestAnimationFrame(update)
}

onMounted(() => {
  startSlideshow()
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      animateCount()
      observer.disconnect()
    }
  }, { threshold: 0.2 })
  if (statsRef.value) observer.observe(statsRef.value)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<style scoped>
.hero {
  height: 110vh;
  display: flex; align-items: center;
  padding: 100px 5% 60px;
  position: relative; overflow: hidden;
  color: white;
}

.hero__bg-container {
  position: absolute; inset: 0; z-index: 0;
}
.hero__bg-slide {
  position: absolute; inset: 0;
  background-size: cover;
  background-position: center;
  backface-visibility: hidden;
  transform: translateZ(0);
}
.hero__overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to right, rgba(10,15,25,0.80) 40%, transparent 100%);
  z-index: 1;
}

/* Crossfade transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 1.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.hero__content { position: relative; z-index: 2; max-width: 680px; }

.hero__heading {
  font-size: clamp(2.8rem, 5vw, 4.4rem);
  font-weight: 700; color: white; margin-bottom: 24px;
  line-height: 1.1;
}
.hero__heading-accent { position: relative; color: var(--coral); font-style: italic; display: inline-block; }
.hero__scribble {
  position: absolute; bottom: -4px; left: 0; width: 100%; height: 8px;
  z-index: -1;
}

.hero__sub {
  font-size: 1.15rem; color: rgba(255,255,255,0.8);
  max-width: 520px; margin-bottom: 40px; line-height: 1.6;
}

.hero__cta-group { margin-bottom: 48px; }
.hero__cta { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 16px; }

.btn-primary-theme { background: var(--coral); color: white; border: none; padding: 14px 32px; font-weight: 600; transition: transform 0.2s ease, filter 0.2s ease; }
.btn-primary-theme:hover { filter: brightness(1.1); transform: translateY(-2px); }

.btn-ghost-white { background: transparent; color: white; border: 1px solid white; padding: 14px 32px; font-weight: 600; }
.btn-ghost-white:hover { background: rgba(255,255,255,0.1); }

.hero__cta-link {
  font-size: 12px; color: rgba(255,255,255,0.5); text-decoration: underline;
  background: none; border: none; cursor: pointer; padding: 0;
}
.hero__cta-link:hover { color: rgba(255,255,255,0.8); }

.hero__trending { margin-bottom: 56px; }
.hero__trending-label { font-size: 11px; color: rgba(255,255,255,0.4); display: block; margin-bottom: 8px; }
.hero__trending-tags { display: flex; gap: 8px; }
.hero__trending-tags .tag {
  background: rgba(255,255,255,0.10); border: 0.5px solid rgba(255,255,255,0.15);
  color: rgba(255,255,255,0.7); font-size: 11px; padding: 4px 12px; border-radius: 9999px;
  font-weight: 500;
}

.hero__stats-wrapper {
  display: inline-block;
  margin-top: 12px;
}
.hero__stats { display: flex; gap: 48px; }
.hero__stat-num {
  font-family: 'Fraunces', serif;
  font-size: 2.2rem; font-weight: 700; color: white;
  text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.hero__stat-num span { font-size: 1.2rem; margin-left: 2px; }
.hero__stat-label { font-size: .82rem; color: rgba(255,255,255,0.5); font-weight: 500; margin-top: 2px; }

@media (max-width: 640px) {
  .hero { padding: 80px 5% 40px; }
  .hero__stats { gap: 24px; flex-direction: column; }
  .hero__stats-wrapper { padding: 16px 24px; width: 100%; }
}
</style>