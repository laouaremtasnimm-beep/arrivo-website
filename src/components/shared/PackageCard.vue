<template>
  <article class="pkg-card" @click="$emit('select', item)">

    <div class="pkg-card__img">
      <img :src="item.img" :alt="item.title" loading="lazy" />

      <button
        class="wishlist-btn"
        :class="{ saved }"
        @click.stop="$emit('toggle-wishlist', item.id)"
      >{{ saved ? '❤️' : '🤍' }}</button>

      <span class="pkg-card__type-tag">{{ item.type }}</span>

      <div class="pkg-card__spots" v-if="item.spots <= 5">
        🔥 {{ item.spots }} spots left
      </div>
    </div>

    <div class="pkg-card__body">
      <div class="pkg-card__agency">{{ item.agency }}</div>

      <h3 class="pkg-card__title">{{ item.title }}</h3>

      <div class="pkg-card__meta">
        <span class="pkg-meta-item">📅 {{ item.duration }} days</span>
        <span class="pkg-meta-item">⭐ {{ item.rating }}
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
          <div class="pkg-card__price">${{ item.price?.toLocaleString() }}</div>
        </div>
        <button class="btn btn-coral pkg-card__cta" @click.stop="$emit('book', item)">
          Book now
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

.pkg-card__spots {
  position: absolute; bottom: 12px; left: 12px;
  background: rgba(255,90,95,.92); color: #fff;
  font-size: .72rem; font-weight: 700;
  padding: 4px 12px; border-radius: 50px;
}

/* Body */
.pkg-card__body { padding: 20px; }

.pkg-card__agency {
  font-size: .74rem; font-weight: 700; color: var(--teal);
  text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;
}
.pkg-card__title {
  font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 10px;
}

.pkg-card__meta { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 10px; }
.pkg-meta-item  { font-size: .8rem; color: var(--gray-600); display: flex; align-items: center; gap: 4px; }
.pkg-meta-reviews { color: var(--gray-400); }

.pkg-card__desc {
  font-size: .83rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 12px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.pkg-card__includes { display: flex; flex-direction: column; gap: 4px; margin-bottom: 14px; }
.pkg-include { font-size: .76rem; color: var(--gray-600); }

.pkg-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding-top: 14px; border-top: 1px solid var(--gray-100);
}
.pkg-card__from  { font-size: .72rem; color: var(--gray-400); }
.pkg-card__price { font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700; color: var(--coral); }
.pkg-card__cta   { padding: 9px 20px; font-size: .84rem; }
</style>