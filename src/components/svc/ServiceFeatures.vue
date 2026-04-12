<template>
  <div class="svc-features">

    <!-- Long description -->
    <div class="svc-features__section">
      <h3 class="svc-features__title">About this service</h3>
      <p class="svc-features__text">{{ longDesc }}</p>
    </div>

    <!-- Features checklist -->
    <div class="svc-features__section">
      <h3 class="svc-features__title">What's included</h3>
      <div class="svc-features__grid">
        <div class="svc-feature" v-for="f in features" :key="f">
          <span class="svc-feature__check">✓</span>
          <span>{{ f }}</span>
        </div>
      </div>
    </div>

    <!-- Vehicle / option table (optional) -->
    <div class="svc-features__section" v-if="options?.length">
      <h3 class="svc-features__title">{{ optionsTitle }}</h3>
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
  longDesc:     { type: String, default: ''    },
  features:     { type: Array,  default: () => [] },
  options:      { type: Array,  default: () => [] },
  optionsTitle: { type: String, default: 'Options' },
  unit:         { type: String, default: 'trip' },
})
defineEmits(['select-option'])
</script>

<style scoped>
.svc-features__section { margin-bottom: 36px; }
.svc-features__title {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 16px;
}
.svc-features__text { font-size: .95rem; color: var(--gray-600); line-height: 1.75; }

/* Features grid */
.svc-features__grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;
}
.svc-feature {
  display: flex; align-items: center; gap: 10px;
  font-size: .88rem; color: var(--gray-600); background: var(--gray-50);
  padding: 10px 14px; border-radius: 10px;
}
.svc-feature__check { color: var(--teal); font-weight: 700; flex-shrink: 0; }

/* Options table */
.svc-options { display: flex; flex-direction: column; gap: 10px; }
.svc-option {
  display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
  padding: 16px 20px; background: var(--gray-50); border-radius: 12px;
  border: 1.5px solid var(--gray-200); transition: border-color var(--transition);
}
.svc-option:hover { border-color: var(--coral); }
.svc-option__type     { font-weight: 700; font-size: .92rem; color: var(--indigo); flex: 1; }
.svc-option__capacity { font-size: .82rem; color: var(--gray-400); }
.svc-option__price    { font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: var(--coral); }
.svc-option__price span { font-size: .75rem; color: var(--gray-400); font-family: 'DM Sans', sans-serif; font-weight: 400; }
.svc-option__btn      { padding: 8px 18px; font-size: .82rem; }
</style>