<template>
  <article class="svc-card" @click="$emit('select', item)">

    <!-- Provider image or icon fallback -->
    <div class="svc-card__img" v-if="item.img">
      <img :src="item.img" :alt="item.title" loading="lazy" />
      <button
        class="wishlist-btn"
        :class="{ saved }"
        @click.stop="$emit('toggle-wishlist', item.id)"
      >{{ saved ? '❤️' : '🤍' }}</button>
      <span class="svc-card__type-tag">{{ item.type }}</span>
    </div>

    <div class="svc-card__icon-header" v-else>
      <div class="svc-card__icon-wrap" :class="item.iconBg || 'svc-icon-teal'">
        {{ item.icon }}
      </div>
      <button
        class="wishlist-btn wishlist-btn--plain"
        :class="{ saved }"
        @click.stop="$emit('toggle-wishlist', item.id)"
      >{{ saved ? '❤️' : '🤍' }}</button>
    </div>

    <div class="svc-card__body">
      <div class="svc-card__provider" v-if="item.provider">{{ item.provider }}</div>

      <h3 class="svc-card__title">{{ item.title }}</h3>
      <p class="svc-card__desc">{{ item.desc }}</p>

      <!-- Features list -->
      <div class="svc-card__features" v-if="item.features?.length">
        <span class="svc-feature" v-for="f in item.features.slice(0, 3)" :key="f">
          ✓ {{ f }}
        </span>
      </div>

      <div class="svc-card__footer">
        <div class="svc-card__meta">
          <div class="svc-card__rating">
            <span class="star">★</span> {{ item.rating }}
            <span class="svc-card__reviews">({{ item.reviews }})</span>
          </div>
          <span
            class="svc-card__avail"
            :class="item.availability !== false ? 'available' : 'unavailable'"
          >
            {{ item.availability !== false ? '● Available' : '○ Unavailable' }}
          </span>
        </div>

        <div class="svc-card__pricing">
          <div class="svc-card__price-label">{{ item.priceLabel || 'from' }}</div>
          <div class="svc-card__price">
            ${{ item.price }}
            <span class="svc-card__unit">/{{ item.unit || 'day' }}</span>
          </div>
        </div>
      </div>

      <button
        class="btn svc-card__cta"
        :class="booked ? 'btn-outline-danger' : 'btn-teal'"
        @click.stop="$emit(booked ? 'cancel' : 'book', item)"
      >
        {{ booked ? 'Cancel Booking' : 'Book service' }}
      </button>
    </div>

  </article>
</template>

<script setup>

defineProps({
  item:   { type: Object,  required: true },
  saved:  { type: Boolean, default: false },
  booked: { type: Boolean, default: false },
})
defineEmits(['select', 'book', 'cancel', 'toggle-wishlist'])
</script>

<style scoped>
.svc-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition), border-bottom-color var(--transition);
  cursor: pointer; border-bottom: 3px solid transparent;
}
.svc-card:hover {
  transform: translateY(-5px); box-shadow: var(--shadow-md);
  border-bottom-color: var(--teal);
}

/* Image variant */
.svc-card__img { height: 190px; position: relative; overflow: hidden; }
.svc-card__img img { height: 100%; transition: transform .5s ease; }
.svc-card:hover .svc-card__img img { transform: scale(1.05); }

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

.svc-card__type-tag {
  position: absolute; bottom: 12px; left: 12px;
  background: rgba(46,196,182,.92); color: #fff;
  font-size: .7rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
  padding: 4px 12px; border-radius: 50px;
}

/* Icon variant header */
.svc-card__icon-header {
  padding: 20px 20px 0;
  display: flex; align-items: center; justify-content: space-between;
}
.svc-card__icon-wrap {
  width: 56px; height: 56px; border-radius: 16px;
  display: flex; align-items: center; justify-content: center; font-size: 1.6rem;
}
.svc-icon-coral { background: rgba(255,90,95,.10); }
.svc-icon-teal  { background: rgba(46,196,182,.10); }
.svc-icon-sand  { background: var(--sand); }

.wishlist-btn--plain {
  position: static; background: var(--gray-100);
}

/* Body */
.svc-card__body { padding: 16px 20px 20px; }
.svc-card__provider {
  font-size: .74rem; font-weight: 700; color: var(--teal);
  text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;
}
.svc-card__title {
  font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;
}
.svc-card__desc {
  font-size: .83rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 12px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.svc-card__features { display: flex; flex-direction: column; gap: 3px; margin-bottom: 14px; }
.svc-feature { font-size: .76rem; color: var(--gray-600); }

.svc-card__footer {
  display: flex; align-items: flex-end; justify-content: space-between;
  margin-bottom: 14px; flex-wrap: wrap; gap: 8px;
}
.svc-card__meta { display: flex; flex-direction: column; gap: 4px; }
.svc-card__rating {
  display: flex; align-items: center; gap: 4px;
  font-size: .82rem; font-weight: 600; color: var(--indigo);
}
.svc-card__reviews { color: var(--gray-400); font-weight: 400; }
.svc-card__avail { font-size: .72rem; font-weight: 700; }
.svc-card__avail.available   { color: #27ae60; }
.svc-card__avail.unavailable { color: var(--gray-400); }

.svc-card__pricing    { text-align: right; }
.svc-card__price-label{ font-size: .72rem; color: var(--gray-400); }
.svc-card__price      { font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700; color: var(--coral); }
.svc-card__unit       { font-size: .75rem; color: var(--gray-400); font-family: 'DM Sans', sans-serif; font-weight: 400; }

.svc-card__cta { width: 100%; padding: 11px; font-size: .88rem; border-radius: 10px; }
.btn-outline-danger {
  background: transparent;
  border: 1px solid var(--coral);
  color: var(--coral);
}
.btn-outline-danger:hover {
  background: rgba(255, 90, 95, 0.1);
}
</style>