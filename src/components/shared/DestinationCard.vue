<template>
  <article class="dest-card" @click="$emit('select', item)">

    <div class="dest-card__img">
      <img :src="item.img" :alt="item.name" loading="lazy" />

      <button
        class="wishlist-btn"
        :class="{ saved }"
        @click.stop="$emit('toggle-wishlist', item.id)"
        :title="saved ? 'Saved' : 'Save'"
      >{{ saved ? '❤️' : '🤍' }}</button>

      <span class="dest-card__badge" v-if="item.badge">{{ item.badge }}</span>

      <div class="dest-card__country">
        <span>{{ item.country || item.name?.split(',')[1]?.trim() }}</span>
      </div>
    </div>

    <div class="dest-card__body">
      <div class="dest-card__top">
        <h3 class="dest-card__name">{{ item.name }}</h3>
        <div class="dest-card__rating">
          <span class="star">★</span> {{ item.rating }}
          <span class="dest-card__reviews">({{ item.reviews?.toLocaleString() }})</span>
        </div>
      </div>

      <p class="dest-card__desc" v-if="item.desc">{{ item.desc }}</p>

      <div class="dest-card__tags" v-if="item.highlights?.length">
        <span class="dest-tag" v-for="h in item.highlights.slice(0, 3)" :key="h">{{ h }}</span>
      </div>

      <div class="dest-card__footer">
        <div>
          <div class="dest-card__from-label">Packages from</div>
          <div class="dest-card__price">${{ item.from?.toLocaleString() }}</div>
        </div>
        <button class="btn btn-coral dest-card__cta" @click.stop="$emit('select', item)">
          Explore
        </button>
      </div>
    </div>

  </article>
</template>

<script setup>
defineProps({
  item:  { type: Object,  required: true },
  saved: { type: Boolean, default: false },
})
defineEmits(['select', 'toggle-wishlist'])

</script>

<style scoped>
.dest-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
  cursor: pointer;
}
.dest-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); }

/* Image */
.dest-card__img { height: 220px; position: relative; overflow: hidden; }
.dest-card__img img { height: 100%; transition: transform .5s ease; }
.dest-card:hover .dest-card__img img { transform: scale(1.07); }

.wishlist-btn {
  position: absolute; top: 12px; right: 12px;
  width: 34px; height: 34px; border-radius: 50%; border: none;
  background: rgba(255,255,255,.92); backdrop-filter: blur(4px);
  font-size: 1rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: transform var(--transition);
}
.wishlist-btn:hover { transform: scale(1.15); }
.wishlist-btn.saved { background: rgba(255,90,95,.12); }

.dest-card__badge {
  position: absolute; top: 12px; left: 12px;
  background: var(--white); border-radius: 50px;
  padding: 4px 12px; font-size: .72rem; font-weight: 700; color: var(--indigo);
}

.dest-card__country {
  position: absolute; bottom: 12px; left: 12px;
  background: rgba(0,0,0,.45); backdrop-filter: blur(6px);
  color: #fff; border-radius: 50px;
  padding: 4px 12px; font-size: .75rem; font-weight: 600;
}

/* Body */
.dest-card__body { padding: 18px 20px 20px; }

.dest-card__top {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 8px; margin-bottom: 8px;
}
.dest-card__name    { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; }
.dest-card__rating  { display: flex; align-items: center; gap: 4px; font-size: .82rem; font-weight: 600; color: var(--indigo); white-space: nowrap; }
.dest-card__reviews { color: var(--gray-400); font-weight: 400; }

.dest-card__desc {
  font-size: .83rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 12px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.dest-card__tags { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
.dest-tag {
  background: rgba(46,196,182,.10); color: var(--teal-dk);
  font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 50px;
}

.dest-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding-top: 14px; border-top: 1px solid var(--gray-100);
}
.dest-card__from-label { font-size: .72rem; color: var(--gray-400); }
.dest-card__price      { font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700; color: var(--coral); }
.dest-card__cta        { padding: 8px 18px; font-size: .84rem; }
</style>