<template>
  <section class="section" id="agencies" style="padding-top:0">
    <div class="agency-cta">
      <div class="cta-content">
        <div class="cta-overline">For Agencies &amp; Providers</div>
        <h2 class="cta-title">Grow your travel business with Voyago</h2>
        <p class="cta-sub">
          Join hundreds of agencies and service providers already reaching
          thousands of tourists on our platform.
        </p>
        <div class="cta-actions">
          <button class="btn btn-white cta-btn" @click="router.push('/auth?mode=register')">
            Start for free
          </button>
          <button class="btn btn-ghost cta-btn">Learn more</button>
        </div>
      </div>

      <ul class="cta-features">
        <li class="cta-feat" v-for="f in features" :key="f.label">
          <span class="cta-feat-icon">{{ f.icon }}</span>
          {{ f.label }}
        </li>
      </ul>
    </div>
  </section>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const features = [
  { icon: '📊', label: 'Full analytics dashboard' },
  { icon: '💬', label: 'Direct messaging with tourists' },
  { icon: '🔔', label: 'Smart booking notifications' },
  { icon: '⭐', label: 'Verified reviews system' },
  { icon: '💳', label: 'Secure payment processing' },
]
</script>

<style scoped>
.agency-cta {
  background: var(--indigo);
  border-radius: 28px;
  padding: 64px 60px;
  display: flex; align-items: center; justify-content: space-between; gap: 48px;
  position: relative; overflow: hidden;
}
.agency-cta::before {
  content: ''; position: absolute; right: -80px; top: -80px;
  width: 320px; height: 320px; border-radius: 50%;
  background: rgba(46,196,182,.12); pointer-events: none;
}
.agency-cta::after {
  content: ''; position: absolute; left: 40%; bottom: -60px;
  width: 200px; height: 200px; border-radius: 50%;
  background: rgba(255,90,95,.10); pointer-events: none;
}

.cta-content { position: relative; z-index: 1; }
.cta-overline {
  font-size: .78rem; font-weight: 700; letter-spacing: .10em;
  text-transform: uppercase; color: var(--teal); margin-bottom: 12px;
}
.cta-title {
  font-family: 'Fraunces', serif;
  font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 700;
  color: #fff; margin-bottom: 14px;
}
.cta-sub { font-size: .95rem; color: rgba(255,255,255,.65); line-height: 1.65; max-width: 420px; }

.cta-actions { display: flex; gap: 14px; margin-top: 32px; flex-wrap: wrap; }
.cta-btn { padding: 14px 30px; }

.cta-features {
  position: relative; z-index: 1;
  list-style: none; display: flex; flex-direction: column; gap: 18px;
  flex-shrink: 0;
}
.cta-feat {
  display: flex; align-items: center; gap: 12px;
  color: rgba(255,255,255,.80); font-size: .9rem;
}
.cta-feat-icon {
  width: 36px; height: 36px; border-radius: 10px;
  background: rgba(255,255,255,.10);
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0;
}

@media (max-width: 900px) {
  .agency-cta { flex-direction: column; padding: 48px 36px; }
  .cta-features { flex-direction: row; flex-wrap: wrap; }
}
@media (max-width: 640px) {
  .agency-cta { padding: 36px 24px; border-radius: 20px; }
}
</style>