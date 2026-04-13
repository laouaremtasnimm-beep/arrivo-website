<template>
  <section class="more-like">
    <div class="more-like__header">
      <h2 class="more-like__title">You might also like</h2>
      <RouterLink :to="seeAllPath" class="see-all">See all →</RouterLink>
    </div>

    <div class="more-like__strip">
      <article
        class="more-like__card"
        v-for="item in items"
        :key="item.id"
        @click="$emit('select', item)"
      >
        <div class="more-like__img">
          <img :src="item.img" :alt="item.title || item.name" loading="lazy" />
          <span class="more-like__badge" v-if="item.badge || item.type">
            {{ item.badge || item.type }}
          </span>
        </div>
        <div class="more-like__body">
          <div class="more-like__name">{{ item.title || item.name }}</div>
          <div class="more-like__meta">
            <span class="star">★</span> {{ item.rating }}
            <span class="more-like__dot">·</span>
            <span class="more-like__price">${{ (item.price || item.from)?.toLocaleString() }}</span>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  items:      { type: Array,  default: () => [] },
  seeAllPath: { type: String, default: '/'       },
})
defineEmits(['select'])
</script>

<style scoped>
.more-like { padding: 40px 0 0; }

.more-like__header {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;
}
.more-like__title {
  font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700;
}

.more-like__strip {
  display: flex; gap: 18px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none;
}
.more-like__strip::-webkit-scrollbar { display: none; }

.more-like__card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
  min-width: 220px; flex-shrink: 0; cursor: pointer;
  transition: transform var(--transition), box-shadow var(--transition);
}
.more-like__card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }

.more-like__img { height: 150px; position: relative; overflow: hidden; }
.more-like__img img { height: 100%; transition: transform .5s ease; }
.more-like__card:hover .more-like__img img { transform: scale(1.06); }

.more-like__badge {
  position: absolute; top: 10px; left: 10px;
  background: var(--white); border-radius: 50px;
  padding: 3px 10px; font-size: .7rem; font-weight: 700; color: var(--indigo);
}

.more-like__body { padding: 14px 16px; }
.more-like__name { font-family: 'Fraunces', serif; font-size: .95rem; font-weight: 600; margin-bottom: 6px; }
.more-like__meta { display: flex; align-items: center; gap: 4px; font-size: .8rem; color: var(--gray-600); }
.more-like__dot  { color: var(--gray-200); }
.more-like__price{ font-weight: 700; color: var(--coral); }
</style>