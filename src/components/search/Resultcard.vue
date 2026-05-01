<template>
  <article class="result-card" @click="$emit('select', item)"style="cursor:pointer">

    <div class="result-card__img">
      <img :src="item.img" :alt="item.title" loading="lazy" />
      <button
        class="wishlist-btn"
        :class="{ saved: saved }"
        @click.stop="$emit('toggle-wishlist', item.id)"
        :title="saved ? 'Remove from saved' : 'Save'"
      >{{ saved ? '❤️' : '🤍' }}</button>
      <span class="result-tag" v-if="item.tag">{{ item.tag }}</span>
    </div>

    <div class="result-card__body">
      <div class="result-card__meta">
        <span class="result-card__category">{{ item.categoryLabel }}</span>
        <div class="result-card__rating">
          <span class="star">★</span> {{ item.rating }}
          <span class="result-card__reviews">({{ item.reviews }})</span>
        </div>
      </div>

      <h3 class="result-card__title">{{ item.title }}</h3>
      <p class="result-card__desc">{{ item.desc }}</p>

      <div class="result-card__details" v-if="item.duration || item.spots">
        <span class="result-detail" v-if="item.duration">📅 {{ item.duration }} days</span>
        <span class="result-detail" :class="{ scarce: item.spots <= 3 }" v-if="item.spots">
          {{ item.spots <= 3 ? '🔥' : '👥' }} {{ item.spots }} spots
        </span>
      </div>

      <div class="result-card__footer">
        <div>
          <div class="result-card__from">{{ item.priceLabel || 'from' }}</div>
          <div class="result-card__price">${{ item.price?.toLocaleString?.() || '' }}</div>
        </div>
        <button class="btn btn-coral result-card__cta" @click.stop="$emit('book', item)">
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
.result-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
  cursor: pointer;
  display: flex; flex-direction: column; height: 420px;
}
.result-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }

.result-card__img { height: 200px; flex-shrink: 0; position: relative; overflow: hidden; }
.result-card__img img { height: 100%; transition: transform .5s ease; }
.result-card:hover .result-card__img img { transform: scale(1.06); }

.wishlist-btn {
  position: absolute; top: 12px; right: 12px;
  width: 34px; height: 34px; border-radius: 50%; border: none;
  background: rgba(255,255,255,.90); backdrop-filter: blur(4px);
  font-size: 1rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: transform var(--transition);
}
.wishlist-btn:hover { transform: scale(1.15); }
.wishlist-btn.saved { background: rgba(255,90,95,.12); }

.result-tag {
  position: absolute; bottom: 12px; left: 12px;
  background: var(--white); border-radius: 50px;
  padding: 4px 12px; font-size: .72rem; font-weight: 700; color: var(--indigo);
}

.result-card__body     { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
.result-card__meta     { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.result-card__category { font-size: .75rem; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .05em; }
.result-card__rating   { display: flex; align-items: center; gap: 4px; font-size: .82rem; font-weight: 600; color: var(--indigo); }
.result-card__reviews  { color: var(--gray-400); font-weight: 400; }
.result-card__title    { font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 600; margin-bottom: 8px; }
.result-card__desc {
  font-size: .83rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 12px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.result-card__details { display: flex; gap: 14px; margin-bottom: 14px; }
.result-detail        { font-size: .8rem; color: var(--gray-600); }
.result-detail.scarce { color: var(--coral); font-weight: 600; }

.result-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding-top: 14px; border-top: 1px solid var(--gray-100);
  margin-top: auto;
}
.result-card__from  { font-size: .72rem; color: var(--gray-400); }
.result-card__price { font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700; color: var(--coral); }
.result-card__cta   { padding: 8px 18px; font-size: .84rem; }
</style>