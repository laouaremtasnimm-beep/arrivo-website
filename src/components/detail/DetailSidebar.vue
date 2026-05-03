<template>
  <aside class="detail-sidebar">
    <div class="detail-sidebar__card">

      <!-- Active Offer Banner -->
      <div class="detail-sidebar__offer-banner" v-if="activeOffer">
        <span class="banner-icon">🔥</span>
        <div class="banner-text">
          <strong>Special Offer Active!</strong>
          <div>{{ activeOffer.title }} (-{{ activeOffer.discount }}%)</div>
        </div>
      </div>

      <!-- Price -->
      <div class="detail-sidebar__price-row">
        <div>
          <div class="detail-sidebar__price-from">{{ priceLabel }}</div>
          <div class="detail-sidebar__price-wrap" v-if="activeOffer">
            <s class="detail-sidebar__price-old">${{ price?.toLocaleString() }}</s>
            <div class="detail-sidebar__price detail-sidebar__price--sale">
              ${{ (price * (1 - activeOffer.discount / 100)).toLocaleString(undefined, {maximumFractionDigits: 0}) }}
              <span class="detail-sidebar__unit" v-if="unit">/ {{ unit }}</span>
            </div>
          </div>
          <div class="detail-sidebar__price" v-else>
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
        <!-- Promotional Deadline -->
        <div class="detail-sidebar__fact" v-if="activeOffer?.endDate">
          <span class="detail-sidebar__fact-icon">⏳</span>
          <div>
            <div class="detail-sidebar__fact-label" style="color: var(--coral); font-weight: 700;">Exclusive Deal Ends</div>
            <div class="detail-sidebar__fact-val" style="color: var(--coral); font-weight: 800;">
              {{ activeOffer.endDate }}
            </div>
          </div>
        </div>
        <!-- Standard Dates (for non-offer items) -->
        <div class="detail-sidebar__fact" v-else-if="startDate">
          <span class="detail-sidebar__fact-icon">📅</span>
          <div>
            <div class="detail-sidebar__fact-label">Travel Window</div>
            <div class="detail-sidebar__fact-val">{{ startDate }} <span v-if="endDate">→ {{ endDate }}</span></div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <button 
        class="btn detail-sidebar__cta" 
        :class="isOwner ? 'btn-manage' : (ctaDanger ? (activeOffer ? 'btn-outline-teal' : 'btn-outline-danger') : (activeOffer ? 'btn-teal' : 'btn-coral'))"
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
  isOwner:     { type: Boolean, default: false },
  activeOffer: { type: Object,  default: null  },
  duration:    { type: String,  default: ''    },
  startDate:   { type: String,  default: ''    },
  endDate:     { type: String,  default: ''    },
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

.detail-sidebar__item-duration {
  text-align: center; font-size: .95rem; font-weight: 700; color: var(--indigo);
  margin-bottom: 12px; padding: 10px; background: var(--gray-50);
  border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;
}

/* Price */
.detail-sidebar__offer-banner {
  background: rgba(255, 90, 95, 0.08); border: 1.5px solid var(--coral); border-radius: 12px;
  padding: 12px; display: flex; align-items: center; gap: 12px; margin-bottom: 20px;
}
.banner-icon { font-size: 1.4rem; }
.banner-text { display: flex; flex-direction: column; }
.banner-text strong { color: var(--coral); font-size: .85rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
.banner-text div { color: var(--indigo); font-size: .85rem; font-weight: 600; }

.detail-sidebar__price-row {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: 16px; gap: 8px;
}
.detail-sidebar__price-from { font-size: .78rem; color: var(--gray-400); margin-bottom: 2px; }
.detail-sidebar__price-wrap { display: flex; flex-direction: column; }
.detail-sidebar__price-old { font-size: 1rem; color: var(--gray-400); text-decoration: line-through; margin-bottom: -2px; }
.detail-sidebar__price {
  font-family: 'Fraunces', serif;
  font-size: 2rem; font-weight: 700; color: var(--indigo); line-height: 1;
}
.detail-sidebar__price--sale { color: var(--coral); }
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
/* Buttons */
.detail-sidebar__cta {
  width: 100%;
  padding: 14px 28px;
  margin-bottom: 12px;
  border-radius: 14px;
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
</style>