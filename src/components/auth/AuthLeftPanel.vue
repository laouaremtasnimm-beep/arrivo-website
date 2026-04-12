<template>
  <div class="auth-left">

    <!-- Decorative blobs -->
    <div class="auth-left__blob auth-left__blob--teal" aria-hidden="true" />
    <div class="auth-left__blob auth-left__blob--coral" aria-hidden="true" />

    <div class="auth-left__inner">

      <!-- Logo -->
      <RouterLink to="/" class="logo">Voya<span>go</span></RouterLink>

      <!-- Hero image card -->
      <div class="auth-card">
        <img
          src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900&q=80"
          alt="Travel destination"
          class="auth-card__img"
        />
        <div class="auth-card__overlay">
          <div class="auth-card__tag">
            <span class="tag-dot" />
            50,000+ happy travellers
          </div>
          <h2 class="auth-card__title">Your next adventure starts with one click</h2>
        </div>
      </div>

      <!-- Floating stat badges -->
      <div class="auth-float auth-float--top">
        <span class="auth-float__icon">🌍</span>
        <div>
          <div class="auth-float__val">1,200+</div>
          <div class="auth-float__label">Destinations</div>
        </div>
      </div>

      <div class="auth-float auth-float--bottom">
        <span class="auth-float__icon">⭐</span>
        <div>
          <div class="auth-float__val">4.9 / 5</div>
          <div class="auth-float__label">Average rating</div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
// No props needed — this panel is purely presentational
</script>

<style scoped>
.auth-left {
  background: var(--indigo);
  position: relative; overflow: hidden;
  display: flex; align-items: stretch;
}

/* Decorative blobs */
.auth-left__blob {
  position: absolute; border-radius: 50%; pointer-events: none;
}
.auth-left__blob--teal {
  top: -100px; right: -100px;
  width: 400px; height: 400px;
  background: rgba(46, 196, 182, .12);
}
.auth-left__blob--coral {
  bottom: -80px; left: -80px;
  width: 300px; height: 300px;
  background: rgba(255, 90, 95, .10);
}

.auth-left__inner {
  position: relative; z-index: 1;
  padding: 48px 52px;
  display: flex; flex-direction: column;
  width: 100%;
}

/* Logo */
.logo {
  font-family: 'Fraunces', serif;
  font-size: 1.6rem; font-weight: 700;
  color: #fff; letter-spacing: -.5px;
  text-decoration: none; width: fit-content;
}
.logo span { color: var(--coral); }

/* Card */
.auth-card {
  margin-top: 40px; border-radius: 22px; overflow: hidden;
  flex: 1; position: relative; min-height: 320px;
  box-shadow: var(--shadow-lg);
}
.auth-card__img { width: 100%; height: 100%; object-fit: cover; }

.auth-card__overlay {
  position: absolute; inset: 0;
  background: linear-gradient(180deg, transparent 35%, rgba(20, 22, 38, .82) 100%);
  display: flex; flex-direction: column; justify-content: flex-end;
  padding: 32px;
}
.auth-card__tag {
  display: inline-flex; align-items: center; gap: 7px;
  background: rgba(255, 255, 255, .15); backdrop-filter: blur(8px);
  color: #fff; font-size: .78rem; font-weight: 600;
  letter-spacing: .05em; text-transform: uppercase;
  padding: 6px 14px; border-radius: 50px; margin-bottom: 14px;
  width: fit-content;
}
.tag-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--teal); flex-shrink: 0;
}
.auth-card__title {
  font-family: 'Fraunces', serif;
  font-size: 1.5rem; font-weight: 700;
  color: #fff; line-height: 1.25; max-width: 320px;
}

/* Floating badges */
.auth-float {
  position: absolute; z-index: 2;
  background: var(--white); border-radius: 14px;
  padding: 14px 18px; box-shadow: var(--shadow-md);
  display: flex; align-items: center; gap: 12px;
}
.auth-float--top    { top: 140px; right: 28px; }
.auth-float--bottom { bottom: 130px; left: 28px; }
.auth-float__icon   { font-size: 1.4rem; }
.auth-float__val    { font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700; color: var(--indigo); }
.auth-float__label  { font-size: .75rem; color: var(--gray-400); margin-top: 2px; }

@media (max-width: 900px) { .auth-left { display: none; } }
</style>
