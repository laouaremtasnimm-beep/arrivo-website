<template>
  <section class="hero">
    <div class="hero__bg" aria-hidden="true"></div>

    <div class="hero__content">
      <div class="hero__tag">
        <span class="hero__tag-dot" />
        Trusted by 50k+ travellers
      </div>

      <h1 class="hero__heading">
        Discover your next <em>adventure</em> with ease
      </h1>

      <p class="hero__sub">
        Connect with top travel agencies, book curated packages, and find the
        best local services — all in one modern marketplace.
      </p>

      <div class="hero__cta">
        <a href="#packages" class="btn btn-coral hero__cta-main">Explore packages</a>
        <button class="btn btn-outline" @click="router.push('/auth?mode=register')">
          Become a provider
        </button>
      </div>

      <div class="hero__stats">
        <div class="hero__stat" v-for="s in stats" :key="s.label">
          <div class="hero__stat-num">{{ s.num }}</div>
          <div class="hero__stat-label">{{ s.label }}</div>
        </div>
      </div>
    </div>

    <!-- Floating visual card (desktop) -->
    <div class="hero__visual" aria-hidden="true">
      <div class="hero__float-badge">
        <span class="hero__float-icon">🧭</span>
        <div>
          <div class="hero__float-label">Active Bookings</div>
          <div class="hero__float-val">+128 today</div>
        </div>
      </div>

      <div class="hero__card">
        <img
          src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80"
          alt="Swiss Alps"
        />
        <div class="hero__card-overlay">
          <div>
            <div class="hero__card-name">Swiss Alps Retreat</div>
            <div class="hero__card-meta">⭐ 4.9 · 12 days · Switzerland</div>
          </div>
          <div class="hero__card-price">$2,490</div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const stats = [
  { num: '1,200+', label: 'Destinations' },
  { num: '340+',   label: 'Travel Agencies' },
  { num: '4.9★',   label: 'Avg Rating' },
]
</script>

<style scoped>
.hero {
  min-height: 100vh;
  display: flex; align-items: center;
  padding: 100px 5% 60px;
  position: relative; overflow: hidden;
}

.hero__bg {
  position: absolute; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 70% 60% at 70% 50%, rgba(244,235,208,.55) 0%, transparent 70%),
    radial-gradient(ellipse 40% 40% at 10% 80%, rgba(46,196,182,.08) 0%, transparent 60%);
}

.hero__content { position: relative; z-index: 1; max-width: 580px; }

.hero__tag {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--sand); color: var(--indigo);
  font-size: .82rem; font-weight: 600;
  letter-spacing: .04em; text-transform: uppercase;
  padding: 6px 16px; border-radius: 50px; margin-bottom: 28px;
}
.hero__tag-dot {
  width: 7px; height: 7px;
  background: var(--coral); border-radius: 50%; flex-shrink: 0;
}

.hero__heading {
  font-size: clamp(2.8rem, 5vw, 4.4rem);
  font-weight: 700; color: var(--indigo); margin-bottom: 24px;
}
.hero__heading em { color: var(--coral); font-style: italic; }

.hero__sub {
  font-size: 1.1rem; color: var(--gray-600);
  max-width: 460px; margin-bottom: 40px; line-height: 1.7;
}

.hero__cta { display: flex; gap: 14px; flex-wrap: wrap; }
.hero__cta-main { padding: 14px 32px; font-size: 1rem; }

.hero__stats {
  display: flex; gap: 40px; margin-top: 56px;
  padding-top: 40px; border-top: 1px solid var(--gray-200);
}
.hero__stat-num {
  font-family: 'Fraunces', serif;
  font-size: 2rem; font-weight: 700; color: var(--indigo);
}
.hero__stat-label { font-size: .82rem; color: var(--gray-400); font-weight: 500; margin-top: 2px; }

.hero__visual {
  position: absolute; right: 5%; top: 50%; transform: translateY(-50%);
  width: min(480px, 42vw); z-index: 1;
}

.hero__float-badge {
  position: absolute; top: 30px; right: -16px; z-index: 2;
  background: var(--white); border-radius: 14px;
  padding: 14px 18px; box-shadow: var(--shadow-md);
  display: flex; align-items: center; gap: 10px; font-size: .85rem;
}
.hero__float-icon  { font-size: 1.4rem; }
.hero__float-label { font-weight: 600; font-size: .8rem; color: var(--gray-600); }
.hero__float-val   { font-family: 'Fraunces', serif; font-weight: 700; font-size: 1rem; color: var(--teal); }

.hero__card {
  border-radius: 24px; overflow: hidden; height: 420px;
  box-shadow: var(--shadow-md); position: relative;
}
.hero__card img { height: 100%; }

.hero__card-overlay {
  position: absolute; bottom: 20px; left: 20px; right: 20px;
  background: rgba(255,255,255,.93); backdrop-filter: blur(10px);
  border-radius: 14px; padding: 16px 20px;
  display: flex; align-items: center; justify-content: space-between;
}
.hero__card-name  { font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 600; }
.hero__card-meta  { font-size: .82rem; color: var(--gray-400); margin-top: 2px; }
.hero__card-price { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; color: var(--coral); }

@media (max-width: 900px)  { .hero__visual { display: none; } }
@media (max-width: 640px)  {
  .hero { padding: 90px 4% 50px; }
  .hero__stats { gap: 24px; }
}
</style>