<template>
  <section class="section section-alt" id="services">
    <div class="section-header">
      <div>
        <div class="section-overline">Local Experts</div>
        <h2 class="section-title">Top <em>services</em></h2>
        <p class="section-sub">Transportation, guides, accommodation and more.</p>
      </div>
      <a href="#" class="see-all">All services →</a>
    </div>

    <div class="svc-grid">
      <article
        class="svc-card"
        v-for="svc in services"
        :key="svc.id"
        @click="$emit('select', svc)"
      >
        <div class="svc-icon" :class="svc.iconBg">{{ svc.icon }}</div>
        <h3 class="svc-title">{{ svc.title }}</h3>
        <p class="svc-desc">{{ svc.desc }}</p>
        <div class="svc-footer">
          <div class="svc-rating">
            <span class="star">★</span> {{ svc.rating }}
          </div>
          <div class="svc-price">
            ${{ svc.price }}<span class="svc-unit">/day</span>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  services: { type: Array, default: () => [] },
})
defineEmits(['select'])
</script>

<style scoped>
.svc-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 22px;
}

.svc-card {
  background: var(--white); border-radius: var(--radius);
  padding: 28px 24px; box-shadow: var(--shadow);
  transition: transform var(--transition), box-shadow var(--transition), border-bottom-color var(--transition);
  cursor: pointer;
  border-bottom: 3px solid transparent;
}
.svc-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
  border-bottom-color: var(--coral);
}

.svc-icon {
  width: 52px; height: 52px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; margin-bottom: 18px;
}
.svc-icon-coral { background: rgba(255,90,95,.10); }
.svc-icon-teal  { background: rgba(46,196,182,.10); }
.svc-icon-sand  { background: var(--sand); }

.svc-title { font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 600; margin-bottom: 8px; }
.svc-desc  { font-size: .85rem; color: var(--gray-600); line-height: 1.65; }

.svc-footer {
  display: flex; align-items: center; justify-content: space-between; margin-top: 20px;
}
.svc-rating {
  display: flex; align-items: center; gap: 4px;
  font-size: .82rem; font-weight: 600; color: var(--indigo);
}
.svc-price { font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700; color: var(--coral); }
.svc-unit  { font-size: .72rem; color: var(--gray-400); font-family: 'DM Sans', sans-serif; font-weight: 400; }
</style>
