<template>
  <article class="pkg-card" @click="$emit('select', item)">

    <div class="pkg-card__img">
      <img :src="item.img" :alt="item.title" loading="lazy" />

      <button
        class="wishlist-btn"
        :class="{ saved }"
        @click.stop="$emit('toggle-wishlist', item.id)"
      >{{ saved ? '❤️' : '🤍' }}</button>

      <span class="pkg-card__type-tag" v-if="!item.activeOffer">{{ item.type }}</span>
      <span class="pkg-card__offer-tag" v-else>Special Offer</span>

      <div class="pkg-card__spots" v-if="item.spots <= 5">
        🔥 {{ item.spots }} spots left
      </div>
    </div>

    <div class="pkg-card__body">
      <div class="pkg-card__agency">{{ item.agency }}</div>

      <h3 class="pkg-card__title">{{ item.title }}</h3>

      <div class="pkg-card__meta">
        <span class="pkg-meta-item">📅 {{ item.duration }} days</span>
        <span class="pkg-meta-item" v-if="item.startDate">· {{ item.startDate }} <span v-if="item.endDate">→ {{ item.endDate }}</span></span>
        <span class="pkg-meta-item pkg-meta-item--offer" v-if="item.activeOffer">
          ⏳ <span class="new-val">Ends {{ item.activeOffer.endDate }}</span>
        </span>
        <span class="pkg-meta-item">⭐ {{ Number(item.rating).toFixed(1) }}
          <span class="pkg-meta-reviews">({{ item.reviews }})</span>
        </span>
        <span class="pkg-meta-item" v-if="item.groupSize">👥 Max {{ item.groupSize }}</span>
      </div>

      <p class="pkg-card__desc">{{ item.desc }}</p>

      <!-- Inclusions -->
      <div class="pkg-card__includes" v-if="item.includes?.length">
        <span class="pkg-include" v-for="inc in item.includes.slice(0, 3)" :key="inc">
          ✓ {{ inc }}
        </span>
      </div>

      <div class="pkg-card__footer">
        <div>
          <div class="pkg-card__from">per person</div>
          <div class="pkg-card__price-wrap" v-if="item.activeOffer">
            <div class="price-old-row">
              <span class="pkg-card__price-old">${{ item.price?.toLocaleString() }}</span>
              <span class="pkg-card__discount-pill">-{{ item.activeOffer.discount }}%</span>
            </div>
            <div class="pkg-card__price pkg-card__price--sale">${{ (item.price * (1 - item.activeOffer.discount / 100)).toLocaleString(undefined, {maximumFractionDigits: 0}) }}</div>
          </div>
          <div class="pkg-card__price" v-else>${{ item.price?.toLocaleString() }}</div>
        </div>
        <button
          class="btn card-cta"
          :class="isOwner ? 'btn-manage' : (booked ? (item.activeOffer ? 'btn-outline-teal' : 'btn-outline-danger') : (item.activeOffer ? 'btn-teal' : 'btn-coral'))"
          @click.stop="isOwner ? $emit('manage', item) : $emit(booked ? 'cancel' : 'book', item)"
        >
          {{ isOwner ? 'Manage package' : (booked ? (item.activeOffer ? 'Cancel Offer' : 'Cancel Booking') : (item.activeOffer ? 'Book Offer' : 'Book now')) }}
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
.pkg-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
  cursor: pointer;
}
.pkg-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }

.pkg-card__img { height: 210px; position: relative; overflow: hidden; }
.pkg-card__img img { height: 100%; transition: transform .5s ease; }
.pkg-card:hover .pkg-card__img img { transform: scale(1.05); }

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

.pkg-card__type-tag {
  position: absolute; top: 12px; left: 12px;
  background: var(--teal); color: #fff;
  font-size: .7rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
  padding: 4px 12px; border-radius: 50px;
}
.pkg-card__offer-tag {
  position: absolute; top: 12px; left: 12px;
  background: var(--coral); color: #fff;
  font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
  padding: 5px 14px; border-radius: 50px;
  box-shadow: 0 4px 12px rgba(255, 90, 95, 0.35);
  animation: pulse-coral 2s infinite;
}
@keyframes pulse-coral {
  0% { box-shadow: 0 0 0 0 rgba(255, 90, 95, 0.4); }
  70% { box-shadow: 0 0 0 6px rgba(255, 90, 95, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 90, 95, 0); }
}

.pkg-card__spots {
  position: absolute; bottom: 12px; left: 12px;
  background: rgba(255,255,255,.95); color: var(--coral);
  font-size: .7rem; font-weight: 700; padding: 4px 10px; border-radius: 8px;
}

/* Body */
.pkg-card__body  { padding: 20px; display: flex; flex-direction: column; height: calc(100% - 210px); }
.pkg-card__agency { font-size: .7rem; font-weight: 700; color: var(--gray-400); text-transform: uppercase; margin-bottom: 6px; letter-spacing: .05em; }
.pkg-card__title { font-size: 1.1rem; font-weight: 700; color: var(--indigo); line-height: 1.35; margin-bottom: 12px; }

.pkg-card__meta { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
.pkg-meta-item  { font-size: .8rem; color: var(--gray-600); display: flex; align-items: center; gap: 4px; }
.pkg-meta-reviews { color: var(--gray-400); font-size: .75rem; }
.pkg-meta-item--offer { gap: 6px; }
.pkg-meta-item--offer .old-val { text-decoration: line-through; opacity: 0.5; font-size: 0.75rem; }
.pkg-meta-item--offer .new-val { color: var(--coral); font-weight: 700; font-size: 0.82rem; }

.pkg-card__desc {
  font-size: .83rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 12px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.pkg-card__includes { display: flex; flex-direction: column; gap: 4px; margin-bottom: 14px; }
.pkg-include { font-size: .76rem; color: var(--gray-600); }

.pkg-card__footer { display: flex; align-items: flex-end; justify-content: space-between; margin-top: auto; padding-top: 16px; border-top: 1px solid var(--gray-100); }
.pkg-card__from { font-size: .68rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }
.pkg-card__price { font-family: 'Fraunces', serif; font-size: 1.35rem; font-weight: 700; color: var(--indigo); }
.pkg-card__price-wrap { display: flex; flex-direction: column; }
.price-old-row { display: flex; align-items: center; gap: 8px; margin-bottom: -2px; }
.pkg-card__price-old { font-size: .85rem; color: var(--gray-400); text-decoration: line-through; }
.pkg-card__discount-pill {
  font-size: .65rem; font-weight: 800; background: var(--coral-lt); color: var(--coral);
  padding: 2px 6px; border-radius: 4px; text-transform: uppercase;
}
.pkg-card__price--sale { color: var(--coral); }
</style>