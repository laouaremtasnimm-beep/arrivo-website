<template>
  <section class="section">
    <div class="section-header">
      <div>
        <div class="section-overline">Traveller Stories</div>
        <h2 class="section-title">What they <em>say</em></h2>
      </div>
    </div>

    <div class="reviews-grid">
      <article class="review-card" v-for="rev in reviews" :key="rev.id">
        <div class="review-stars">
          <span class="star" v-for="i in rev.rating" :key="i">★</span>
        </div>
        <p class="review-text">"{{ rev.text }}"</p>
        <div class="review-author">
          <div class="review-avatar">{{ rev.name[0] }}</div>
          <div>
            <div class="review-name">{{ rev.name }}</div>
            <div class="review-loc">{{ rev.location }}</div>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  reviews: { type: Array, default: () => [] },
})
</script>

<style scoped>
.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.review-card {
  background: var(--white); border-radius: var(--radius);
  padding: 28px; box-shadow: var(--shadow);
  transition: transform var(--transition);
}
.review-card:hover { transform: translateY(-3px); }

.review-stars { display: flex; gap: 3px; margin-bottom: 14px; font-size: 1rem; }

.review-text {
  font-size: .92rem; color: var(--gray-600);
  line-height: 1.75; margin-bottom: 22px; font-style: italic;
}

.review-author { display: flex; align-items: center; gap: 12px; }

.review-avatar {
  width: 44px; height: 44px; border-radius: 50%;
  background: var(--sand); flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 700; color: var(--indigo);
}

.review-name { font-weight: 600; font-size: .9rem; color: var(--indigo); }
.review-loc  { font-size: .78rem; color: var(--gray-400); margin-top: 2px; }
</style>
