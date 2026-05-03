<template>
  <article class="result-card" @click="$emit('select', item)">

    <div class="result-card__img">
      <img :src="item.img" :alt="item.title" loading="lazy" />
      
      <button
        class="wishlist-btn"
        :class="{ saved: saved }"
        @click.stop="$emit('toggle-wishlist', item.id)"
        :title="saved ? 'Remove from saved' : 'Save'"
      >{{ saved ? '❤️' : '🤍' }}</button>

      <!-- Pulse Tag for Offers -->
      <span class="result-card__offer-tag" v-if="item.activeOffer">Special Offer</span>
      <span class="result-card__type-tag" v-else-if="item.tag">{{ item.tag }}</span>

      <div class="result-card__spots" v-if="item.spots <= 5">
        🔥 {{ item.spots }} spots left
      </div>
    </div>

    <div class="result-card__body">
      <div class="result-card__meta-top">
        <span class="result-card__category">{{ item.categoryLabel }}</span>
        <div class="result-card__rating">
          <span class="star">★</span> {{ Number(item.rating).toFixed(1) }}
          <span class="result-card__reviews">({{ item.reviews }})</span>
        </div>
      </div>

      <h3 class="result-card__title">{{ item.title }}</h3>
      
      <div class="result-card__meta-details">
        <span class="meta-item" v-if="item.duration">📅 {{ item.duration }} days</span>
        <span class="meta-item meta-item--offer" v-if="item.activeOffer">
          ⏳ Ends {{ item.activeOffer.endDate }}
        </span>
      </div>

      <p class="result-card__desc">{{ item.desc }}</p>

      <!-- Inclusions (if any) -->
      <div class="result-card__includes" v-if="item.includes?.length">
        <span class="result-include" v-for="inc in item.includes.slice(0, 2)" :key="inc">
          ✓ {{ inc }}
        </span>
      </div>

      <div class="result-card__footer">
        <div>
          <div class="result-card__from">{{ item.priceLabel || 'per person' }}</div>
          
          <div class="price-wrap" v-if="item.activeOffer">
            <div class="price-old-row">
              <span class="price-old">${{ item.price?.toLocaleString() }}</span>
              <span class="discount-pill">-{{ item.activeOffer.discount }}%</span>
            </div>
            <div class="price price--sale">
              ${{ (item.price * (1 - item.activeOffer.discount / 100)).toLocaleString(undefined, {maximumFractionDigits: 0}) }}
            </div>
          </div>
          <div class="price" v-else>${{ item.price?.toLocaleString() }}</div>
        </div>

        <button
          class="btn card-cta"
          :class="isOwner ? 'btn-manage' : (booked ? (item.activeOffer ? 'btn-outline-teal' : 'btn-outline-danger') : (item.activeOffer ? 'btn-teal' : 'btn-coral'))"
          @click.stop="isOwner ? $emit('manage', item) : $emit(booked ? 'cancel' : 'book', item)"
        >
          {{ isOwner ? 'Manage' : (booked ? 'Cancel' : (item.ctaLabel || 'Book now')) }}
        </button>
      </div>
    </div>

  </article>
</template>

<script setup>
defineProps({
  item:    { type: Object,  required: true },
  saved:   { type: Boolean, default: false },
  booked:  { type: Boolean, default: false },
  isOwner: { type: Boolean, default: false },
})
defineEmits(['select', 'book', 'cancel', 'toggle-wishlist', 'manage'])
</script>

<style scoped>
.result-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
  cursor: pointer;
  display: flex; flex-direction: column;
  height: 480px;
}
.result-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }

.result-card__img { height: 200px; flex-shrink: 0; position: relative; overflow: hidden; }
.result-card__img img { height: 100%; width: 100%; object-fit: cover; transition: transform .5s ease; }
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

.result-card__offer-tag {
  position: absolute; top: 12px; left: 12px;
  background: var(--coral); color: #fff;
  font-size: .65rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
  padding: 5px 12px; border-radius: 50px;
  box-shadow: 0 4px 12px rgba(255, 90, 95, 0.35);
  animation: pulse-coral 2s infinite;
}
@keyframes pulse-coral {
  0% { box-shadow: 0 0 0 0 rgba(255, 90, 95, 0.4); }
  70% { box-shadow: 0 0 0 6px rgba(255, 90, 95, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 90, 95, 0); }
}

.result-card__type-tag {
  position: absolute; top: 12px; left: 12px;
  background: var(--white); border-radius: 50px;
  padding: 4px 12px; font-size: .7rem; font-weight: 700; color: var(--indigo);
  text-transform: uppercase;
}

.result-card__spots {
  position: absolute; bottom: 12px; left: 12px;
  background: rgba(255,255,255,.95); color: var(--coral);
  font-size: .7rem; font-weight: 700; padding: 4px 10px; border-radius: 8px;
}

.result-card__body     { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
.result-card__meta-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.result-card__category { font-size: .72rem; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .05em; }
.result-card__rating   { display: flex; align-items: center; gap: 4px; font-size: .8rem; font-weight: 600; color: var(--indigo); }
.result-card__reviews  { color: var(--gray-400); font-weight: 400; font-size: .75rem; }

.result-card__title    { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 600; margin-bottom: 10px; color: var(--indigo); }

.result-card__meta-details { display: flex; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
.meta-item { font-size: .78rem; color: var(--gray-500); }
.meta-item--offer { color: var(--coral); font-weight: 700; }

.result-card__desc {
  font-size: .82rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 14px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.result-card__includes { display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px; }
.result-include { font-size: .75rem; color: var(--gray-500); }

.result-card__footer {
  display: flex; align-items: flex-end; justify-content: space-between;
  padding-top: 16px; border-top: 1px solid var(--gray-100);
  margin-top: auto;
}
.result-card__from  { font-size: .65rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }

.price-wrap { display: flex; flex-direction: column; }
.price-old-row { display: flex; align-items: center; gap: 8px; margin-bottom: -2px; }
.price-old { font-size: .85rem; color: var(--gray-400); text-decoration: line-through; }
.discount-pill {
  font-size: .62rem; font-weight: 800; background: var(--coral-lt); color: var(--coral);
  padding: 1px 6px; border-radius: 4px;
}
.price { font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700; color: var(--indigo); }
.price--sale { color: var(--coral); }

.btn-teal { background: var(--teal); color: #fff; }
.btn-teal:hover { background: #26a69a; }
.btn-outline-teal { border: 1.5px solid var(--teal); color: var(--teal); background: transparent; }
.btn-outline-teal:hover { background: var(--teal); color: #fff; }
</style>