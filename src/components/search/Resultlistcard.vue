<template>
  <article class="list-card" @click="$emit('select', item)"style="cursor:pointer">

    <div class="list-card__img">
      <img :src="item.img" :alt="item.title" loading="lazy" />
      <span class="result-tag" v-if="item.tag">{{ item.tag }}</span>
    </div>

    <div class="list-card__body">
      <div class="list-card__top">
        <div class="list-card__info">
          <span class="list-card__category">{{ item.categoryLabel }}</span>
          <h3 class="list-card__title">{{ item.title }}</h3>
          <p class="list-card__desc">{{ item.desc }}</p>
        </div>
        <button
          class="wishlist-btn"
          :class="{ saved: saved }"
          @click.stop="$emit('toggle-wishlist', item.id)"
          :title="saved ? 'Remove from saved' : 'Save'"
        >{{ saved ? '❤️' : '🤍' }}</button>
      </div>

      <div class="list-card__details">
        <span class="result-detail" v-if="item.duration">📅 {{ item.duration }} days</span>
        <span class="result-detail" :class="{ scarce: item.spots <= 3 }" v-if="item.spots">
          {{ item.spots <= 3 ? '🔥' : '👥' }} {{ item.spots }} spots left
        </span>
        <div class="list-card__rating">
          <span class="star">★</span> {{ item.rating }}
          <span class="list-card__reviews">({{ item.reviews }})</span>
        </div>
      </div>

      <div class="list-card__footer">
        <div>
          <div class="list-card__from">{{ item.priceLabel || 'from' }}</div>
          <div class="list-card__price">${{ item.price.toLocaleString() }}</div>
        </div>
        <button class="btn btn-coral" @click.stop="$emit('book', item)">
          {{ item.ctaLabel || 'Book now' }}
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
defineEmits(['select', 'book', 'toggle-wishlist'])
</script>

<style scoped>
.list-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
  display: grid; grid-template-columns: 240px 1fr;
  transition: box-shadow var(--transition); cursor: pointer;
}
.list-card:hover { box-shadow: var(--shadow-md); }

.list-card__img { height: 100%; min-height: 180px; position: relative; overflow: hidden; }
.list-card__img img { height: 100%; transition: transform .5s ease; }
.list-card:hover .list-card__img img { transform: scale(1.05); }

.result-tag {
  position: absolute; bottom: 12px; left: 12px;
  background: var(--white); border-radius: 50px;
  padding: 4px 12px; font-size: .72rem; font-weight: 700; color: var(--indigo);
}

.list-card__body {
  padding: 24px; display: flex; flex-direction: column; justify-content: space-between;
}
.list-card__top {
  display: flex; justify-content: space-between; gap: 12px; margin-bottom: 14px;
}
.list-card__info     { flex: 1; }
.list-card__category { font-size: .75rem; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .05em; }
.list-card__title    { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 600; margin: 6px 0 8px; }
.list-card__desc {
  font-size: .87rem; color: var(--gray-600); line-height: 1.6;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.wishlist-btn {
  width: 34px; height: 34px; border-radius: 50%; border: none; flex-shrink: 0;
  background: var(--gray-100); font-size: 1rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: transform var(--transition), background var(--transition);
}
.wishlist-btn:hover { transform: scale(1.12); }
.wishlist-btn.saved { background: rgba(255,90,95,.12); }

.list-card__details {
  display: flex; align-items: center; gap: 18px; margin-bottom: 16px; flex-wrap: wrap;
}
.result-detail       { font-size: .82rem; color: var(--gray-600); }
.result-detail.scarce{ color: var(--coral); font-weight: 600; }
.list-card__rating   { display: flex; align-items: center; gap: 4px; font-size: .82rem; font-weight: 600; color: var(--indigo); }
.list-card__reviews  { color: var(--gray-400); font-weight: 400; }

.list-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding-top: 16px; border-top: 1px solid var(--gray-100);
}
.list-card__from  { font-size: .72rem; color: var(--gray-400); }
.list-card__price { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--coral); }

@media (max-width: 640px) {
  .list-card { grid-template-columns: 1fr; }
  .list-card__img { min-height: 200px; }
}
</style>