<template>
  <article class="list-card" @click="$emit('select', item)">

    <div class="list-card__img">
      <img :src="item.img" :alt="item.title" loading="lazy" />
      
      <!-- Pulse Tag for Offers -->
      <span class="list-card__offer-tag" v-if="item.activeOffer">Special Offer</span>
      <span class="list-card__type-tag" v-else-if="item.tag">{{ item.tag }}</span>

      <div class="list-card__spots" v-if="item.spots <= 5">
        🔥 {{ item.spots }} left
      </div>
    </div>

    <div class="list-card__body">
      <div class="list-card__top">
        <div class="list-card__info">
          <span class="list-card__category">{{ item.categoryLabel }}</span>
          <h3 class="list-card__title">{{ item.title }}</h3>
          <p class="list-card__desc">{{ item.desc }}</p>

          <!-- Inclusions (List View) -->
          <div class="list-card__includes" v-if="item.includes?.length">
            <span class="include-item" v-for="inc in item.includes.slice(0, 3)" :key="inc">
              ✓ {{ inc }}
            </span>
          </div>
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
        <span class="result-detail result-detail--offer" v-if="item.activeOffer">
          ⏳ Ends {{ item.activeOffer.endDate }}
        </span>
        
        <div class="list-card__rating">
          <span class="star">★</span> {{ Number(item.rating).toFixed(1) }}
          <span class="list-card__reviews">({{ item.reviews }})</span>
        </div>
      </div>

      <div class="list-card__footer">
        <div>
          <div class="list-card__from">{{ item.priceLabel || 'per person' }}</div>
          
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
.list-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
  display: grid; grid-template-columns: 280px 1fr;
  transition: box-shadow var(--transition), transform var(--transition);
  cursor: pointer; min-height: 240px;
}
.list-card:hover { box-shadow: var(--shadow-md); transform: translateX(4px); }

.list-card__img { height: 100%; position: relative; overflow: hidden; }
.list-card__img img { height: 100%; width: 100%; object-fit: cover; transition: transform .5s ease; }
.list-card:hover .list-card__img img { transform: scale(1.05); }

.list-card__offer-tag {
  position: absolute; top: 12px; left: 12px;
  background: var(--coral); color: #fff;
  font-size: .6rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
  padding: 4px 10px; border-radius: 50px;
  box-shadow: 0 4px 12px rgba(255, 90, 95, 0.35);
  animation: pulse-coral 2s infinite;
}
@keyframes pulse-coral {
  0% { box-shadow: 0 0 0 0 rgba(255, 90, 95, 0.4); }
  70% { box-shadow: 0 0 0 6px rgba(255, 90, 95, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 90, 95, 0); }
}

.list-card__type-tag {
  position: absolute; top: 12px; left: 12px;
  background: var(--white); border-radius: 50px;
  padding: 4px 10px; font-size: .65rem; font-weight: 700; color: var(--indigo);
  text-transform: uppercase;
}

.list-card__spots {
  position: absolute; bottom: 12px; left: 12px;
  background: rgba(255,255,255,.92); color: var(--coral);
  font-size: .65rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;
}

.list-card__body { padding: 24px; display: flex; flex-direction: column; }
.list-card__top { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 12px; }
.list-card__info { flex: 1; }

.list-card__category { font-size: .7rem; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .05em; }
.list-card__title { font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 600; color: var(--indigo); margin: 6px 0 10px; }
.list-card__desc {
  font-size: .85rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 16px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.list-card__includes { display: flex; gap: 14px; margin-bottom: 16px; }
.include-item { font-size: .75rem; color: var(--gray-500); }

.wishlist-btn {
  width: 36px; height: 36px; border-radius: 50%; border: none; flex-shrink: 0;
  background: var(--gray-50); font-size: 1rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: transform var(--transition);
}
.wishlist-btn:hover { transform: scale(1.15); }
.wishlist-btn.saved { background: rgba(255,90,95,.12); }

.list-card__details { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; border-top: 1px solid var(--gray-50); padding-top: 14px; }
.result-detail { font-size: .8rem; color: var(--gray-600); }
.result-detail--offer { color: var(--coral); font-weight: 700; }

.list-card__rating { display: flex; align-items: center; gap: 4px; font-size: .82rem; font-weight: 600; color: var(--indigo); margin-left: auto; }
.list-card__reviews { color: var(--gray-400); font-weight: 400; font-size: .75rem; }

.list-card__footer {
  display: flex; align-items: flex-end; justify-content: space-between;
  padding-top: 16px; border-top: 1px solid var(--gray-100); margin-top: auto;
}
.list-card__from { font-size: .65rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }

.price-wrap { display: flex; flex-direction: column; }
.price-old-row { display: flex; align-items: center; gap: 8px; margin-bottom: -2px; }
.price-old { font-size: .9rem; color: var(--gray-400); text-decoration: line-through; }
.discount-pill {
  font-size: .65rem; font-weight: 800; background: var(--coral-lt); color: var(--coral);
  padding: 2px 8px; border-radius: 4px;
}
.price { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; color: var(--indigo); }
.price--sale { color: var(--coral); }

.btn-teal { background: var(--teal); color: #fff; }
.btn-teal:hover { background: #26a69a; }
.btn-outline-teal { border: 1.5px solid var(--teal); color: var(--teal); background: transparent; }
.btn-outline-teal:hover { background: var(--teal); color: #fff; }

@media (max-width: 768px) {
  .list-card { grid-template-columns: 1fr; }
  .list-card__img { height: 200px; }
}
</style>