<template>
  <div class="svc-inclusions">
    <!-- Features grid (Includes / Excludes) -->
    <div class="svc-inclusions__card detail-card">
      <h3 class="svc-inclusions__title">What's included</h3>
      
      <div class="inclusions__grid">
        <div class="inclusions__col">
          <div class="inclusions__col-label inclusions__col-label--yes">✅ Included</div>
          <ul class="inclusions__list">
            <li class="inclusions__item inclusions__item--yes" v-for="item in features" :key="item">
              <span class="inclusions__check">✓</span> {{ item }}
            </li>
          </ul>
        </div>

        <div class="inclusions__col" v-if="excludes?.length">
          <div class="inclusions__col-label inclusions__col-label--no">❌ Not included</div>
          <ul class="inclusions__list">
            <li class="inclusions__item inclusions__item--no" v-for="item in excludes" :key="item">
              <span class="inclusions__check">✕</span> {{ item }}
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Vehicle / option table (optional) -->
    <div class="svc-inclusions__card detail-card" v-if="options?.length">
      <h3 class="svc-inclusions__title">{{ optionsTitle }}</h3>
      <div class="svc-options">
        <div class="svc-option" v-for="opt in options" :key="opt.type">
          <div class="svc-option__type">{{ opt.type }}</div>
          <div class="svc-option__capacity" v-if="opt.capacity">{{ opt.capacity }}</div>
          <div class="svc-option__price">${{ opt.price }} <span>/ {{ unit }}</span></div>
          <button class="btn btn-coral svc-option__btn" @click="$emit('select-option', opt)">Select</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  features:     { type: Array,  default: () => [] },
  excludes:     { type: Array,  default: () => [] },
  options:      { type: Array,  default: () => [] },
  optionsTitle: { type: String, default: 'Options' },
  unit:         { type: String, default: 'trip' },
})
defineEmits(['select-option'])
</script>

<style scoped>
.svc-inclusions__card:not(:last-child) { margin-bottom: 32px; }
.svc-inclusions__title {
  font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 24px;
}

/* Replicated Package Inclusions structure */
.inclusions__grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 32px;
}
.inclusions__col-label {
  font-size: .78rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; margin-bottom: 14px;
}
.inclusions__col-label--yes { color: #27ae60; }
.inclusions__col-label--no  { color: #e74c3c; }

.inclusions__list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
.inclusions__item {
  display: flex; align-items: flex-start; gap: 10px;
  font-size: .92rem; color: var(--gray-600); line-height: 1.5;
}
.inclusions__check { font-size: .8rem; flex-shrink: 0; margin-top: 2px; font-weight: 700; }
.inclusions__item--yes .inclusions__check { color: #27ae60; }
.inclusions__item--no  .inclusions__check { color: #e74c3c; }

/* Options table */
.svc-options { display: flex; flex-direction: column; gap: 12px; }
.svc-option {
  display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
  padding: 18px 22px; background: var(--gray-50); border-radius: 14px;
  border: 1px solid var(--gray-100); transition: all var(--transition);
}
.svc-option:hover { border-color: var(--coral); background: var(--white); box-shadow: var(--shadow-sm); }
.svc-option__type     { font-weight: 700; font-size: .95rem; color: var(--indigo); flex: 1; }
.svc-option__capacity { font-size: .82rem; color: var(--gray-400); }
.svc-option__price    { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; color: var(--coral); }
.svc-option__price span { font-size: .75rem; color: var(--gray-400); font-family: 'DM Sans', sans-serif; font-weight: 400; }
.svc-option__btn      { padding: 8px 18px; font-size: .82rem; }

@media (max-width: 640px) {
  .inclusions__grid { grid-template-columns: 1fr; gap: 24px; }
}
</style>
