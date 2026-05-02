<template>
  <aside class="detail-sidebar">
    <div class="detail-sidebar__card">

      <!-- Price -->
      <div class="detail-sidebar__price-row">
        <div>
          <div class="detail-sidebar__price-from">{{ priceLabel }}</div>
          <div class="detail-sidebar__price">
            ${{ price?.toLocaleString() }}
            <span class="detail-sidebar__unit" v-if="unit">/ {{ unit }}</span>
          </div>
        </div>
        <div class="detail-sidebar__rating">
          <span class="star">★</span> {{ rating }}
          <span class="detail-sidebar__reviews">({{ reviews }})</span>
        </div>
      </div>

      <!-- Slots / spots warning -->
      <div class="detail-sidebar__spots" v-if="spots && spots <= 5">
        🔥 Only {{ spots }} {{ spotsLabel || 'spots' }} left!
      </div>

      <!-- Key facts -->
      <div class="detail-sidebar__facts">
        <div class="detail-sidebar__fact" v-for="f in facts" :key="f.label">
          <span class="detail-sidebar__fact-icon">{{ f.icon }}</span>
          <div>
            <div class="detail-sidebar__fact-label">{{ f.label }}</div>
            <div class="detail-sidebar__fact-val">{{ f.value }}</div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <button 
        class="btn detail-sidebar__cta" 
        :class="ctaDanger ? 'btn-outline-danger' : 'btn-coral'"
        @click="$emit(ctaDanger ? 'cancel' : 'book')"
      >
        {{ ctaLabel }}
      </button>

      <button class="detail-sidebar__secondary" @click="$emit('message')">
  {{ entityLabel }}
</button>

      <p class="detail-sidebar__note">{{ note }}</p>

    </div>

    <!-- Trust badges -->
    <div class="detail-sidebar__trust">
      <div class="trust-item"><span>✅</span> Verified listing</div>
      <div class="trust-item"><span>🔒</span> Secure payment</div>
      <div class="trust-item"><span>↩️</span> Free cancellation</div>
    </div>
  </aside>
</template>

<script setup>
defineProps({
  price:       { type: Number, required: true    },
  priceLabel:  { type: String, default: 'from'   },
  unit:        { type: String, default: ''        },
  rating:      { type: Number, required: true    },
  reviews:     { type: Number, required: true    },
  spots:       { type: Number, default: null     },
  spotsLabel:  { type: String, default: 'spots'  },
  facts:       { type: Array,  default: () => [] },
  ctaLabel:    { type: String, default: 'Book now'},
  entityLabel: { type: String, default: 'provider'},
  note:        { type: String, default: "You won't be charged yet." },
  ctaDanger:   { type: Boolean, default: false },
})

defineEmits(['book', 'cancel', 'message'])
</script>

<style scoped>
.detail-sidebar {
  display: flex; flex-direction: column; gap: 16px;
  z-index: 10;
}

.detail-sidebar__card {
  background: var(--white); border-radius: var(--radius-xl);
  padding: 28px; box-shadow: var(--shadow-md);
  border: 1px solid var(--gray-200);
}

/* Price */
.detail-sidebar__price-row {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: 16px; gap: 8px;
}
.detail-sidebar__price-from { font-size: .78rem; color: var(--gray-400); margin-bottom: 2px; }
.detail-sidebar__price {
  font-family: 'Fraunces', serif;
  font-size: 2rem; font-weight: 700; color: var(--indigo); line-height: 1;
}
.detail-sidebar__unit { font-size: .9rem; color: var(--gray-400); font-family: 'DM Sans', sans-serif; font-weight: 400; }
.detail-sidebar__rating {
  display: flex; align-items: center; gap: 4px;
  font-size: .88rem; font-weight: 600; color: var(--indigo); white-space: nowrap;
  background: var(--sand); padding: 6px 12px; border-radius: 50px;
}
.detail-sidebar__reviews { color: var(--gray-400); font-weight: 400; }

/* Spots */
.detail-sidebar__spots {
  background: rgba(255,90,95,.08); color: var(--coral);
  font-size: .82rem; font-weight: 700;
  padding: 10px 14px; border-radius: 10px; margin-bottom: 16px;
}

/* Facts */
.detail-sidebar__facts {
  display: flex; flex-direction: column; gap: 14px;
  padding: 18px 0; margin-bottom: 18px;
  border-top: 1px solid var(--gray-100);
  border-bottom: 1px solid var(--gray-100);
}
.detail-sidebar__fact { display: flex; align-items: center; gap: 12px; }
.detail-sidebar__fact-icon { font-size: 1.2rem; flex-shrink: 0; }
.detail-sidebar__fact-label{ font-size: .75rem; color: var(--gray-400); font-weight: 500; }
.detail-sidebar__fact-val  { font-size: .88rem; font-weight: 600; color: var(--indigo); }

/* Buttons */
.detail-sidebar__cta {
  width: 100%; padding: 15px; font-size: 1rem; border-radius: 14px; margin-bottom: 12px;
}
.detail-sidebar__secondary {
  width: 100%; padding: 12px; font-size: .9rem; font-weight: 600;
  border: 1.5px solid var(--gray-200); border-radius: 14px;
  background: transparent; color: var(--indigo); cursor: pointer;
  transition: all var(--transition); font-family: 'DM Sans', sans-serif;
}
.detail-sidebar__secondary:hover { border-color: var(--indigo); background: var(--gray-50); }

.detail-sidebar__note {
  text-align: center; font-size: .78rem; color: var(--gray-400); margin-top: 12px;
}

/* Trust */
.detail-sidebar__trust {
  background: var(--white); border-radius: var(--radius);
  padding: 16px 20px; box-shadow: var(--shadow);
  display: flex; flex-direction: column; gap: 10px;
}
.trust-item {
  display: flex; align-items: center; gap: 8px;
  font-size: .84rem; color: var(--gray-600);
}
.btn-outline-danger {
  background: transparent;
  border: 1px solid var(--coral);
  color: var(--coral);
}
.btn-outline-danger:hover {
  background: rgba(255, 90, 95, 0.1);
}
</style>