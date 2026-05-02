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
          <span class="star">★</span> {{ Number(item.rating).toFixed(1) }}
          <span class="dest-card__reviews">· {{ item.reviews?.toLocaleString() }} reviews</span>
        </div>
      </div>

      <div class="dest-card__footer">
        <div class="dest-card__from-wrap">
          <span class="dest-card__from-label">Packages from</span>
          <span class="dest-card__price">${{ item.from?.toLocaleString() || item.price?.toLocaleString() }}</span>
        </div>
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
  display: flex; flex-direction: column; height: 420px; /* Unified height */
}
.dest-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); }

/* Image */
.dest-card__img { height: 210px; position: relative; overflow: hidden; flex-shrink: 0; }
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
.dest-card__body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }

.dest-card__top { margin-bottom: 8px; }
.dest-card__name    { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 600; margin-bottom: 4px; }
.dest-card__rating  { display: flex; align-items: center; gap: 4px; font-size: .82rem; font-weight: 600; color: var(--indigo); }
.dest-card__reviews { color: var(--gray-400); font-weight: 400; }

.dest-card__footer {
  margin-top: auto;
  padding-top: 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.dest-card__from-wrap { display: flex; align-items: center; justify-content: space-between; width: 100%; }
.dest-card__from-label { font-size: .8rem; color: var(--gray-400); }
.dest-card__price      { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; color: var(--coral); }
</style>