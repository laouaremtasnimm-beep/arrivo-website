<template>
  <section class="section section-alt" id="destinations">
    <div class="section-header">
      <div>
        <div class="section-overline">Explore the World</div>
        <h2 class="section-title">Trending <em>destinations</em></h2>
        <p class="section-sub">Handpicked places loved by thousands of travellers this season.</p>
      </div>
      <a href="#" class="see-all">See all →</a>
    </div>

    <div class="dest-grid">
      <article
        class="dest-card"
        v-for="dest in destinations"
        :key="dest.id"
        @click="$emit('select', dest)"
        role="button"
        tabindex="0"
      >
        <div class="dest-img">
          <img :src="dest.img" :alt="dest.name" loading="lazy" />
          <span class="dest-badge">{{ dest.badge }}</span>
        </div>
        <div class="dest-body">
          <h3 class="dest-name">{{ dest.name }}</h3>
          <div class="dest-rating">
            <span class="star">★</span> {{ dest.rating }}
            <span class="dest-reviews">· {{ dest.reviews }} reviews</span>
          </div>
          <div class="dest-footer">
            <span class="dest-from">Packages from</span>
            <span class="dest-price">${{ dest.from }}</span>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  destinations: { type: Array, default: () => [] },
})
defineEmits(['select'])
</script>

<style scoped>
.dest-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 24px;
}

.dest-card {
  background: var(--white); border-radius: var(--radius);
  overflow: hidden; box-shadow: var(--shadow);
  transition: transform var(--transition), box-shadow var(--transition);
  cursor: pointer;
}
.dest-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); }

/* Image */
.dest-img { height: 210px; position: relative; overflow: hidden; }
.dest-img img { height: 100%; transition: transform .5s ease; }
.dest-card:hover .dest-img img { transform: scale(1.07); }

.dest-badge {
  position: absolute; top: 14px; left: 14px;
  background: var(--white); border-radius: 50px;
  padding: 4px 12px; font-size: .75rem; font-weight: 700; color: var(--indigo);
}

/* Body */
.dest-body { padding: 20px; }
.dest-name { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 600; }
.dest-rating {
  display: flex; align-items: center; gap: 4px;
  font-size: .82rem; font-weight: 600; margin-top: 8px; color: var(--indigo);
}
.dest-reviews { color: var(--gray-400); font-weight: 400; }
.dest-footer {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 12px;
}
.dest-from  { font-size: .8rem; color: var(--gray-400); }
.dest-price { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; color: var(--coral); }
</style>
