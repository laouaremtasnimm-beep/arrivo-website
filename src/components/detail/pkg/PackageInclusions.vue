<template>
  <div class="inclusions">
    <h3 class="inclusions__title">What's included</h3>

    <div class="inclusions__grid">
      <div class="inclusions__col">
        <div class="inclusions__col-label inclusions__col-label--yes">✅ Included</div>
        <ul class="inclusions__list">
          <li class="inclusions__item inclusions__item--yes" v-for="item in includes" :key="item">
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
</template>

<script setup>
defineProps({
  includes: { type: Array, default: () => [] },
  excludes: { type: Array, default: () => [] },
})
</script>

<style scoped>
.inclusions { margin: 40px 0; }

.inclusions__title {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 20px;
}

.inclusions__grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 28px;
}

.inclusions__col-label {
  font-size: .78rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; margin-bottom: 14px;
}
.inclusions__col-label--yes { color: #27ae60; }
.inclusions__col-label--no  { color: #e74c3c; }

.inclusions__list { list-style: none; display: flex; flex-direction: column; gap: 10px; }

.inclusions__item {
  display: flex; align-items: flex-start; gap: 10px;
  font-size: .88rem; color: var(--gray-600); line-height: 1.4;
}
.inclusions__check { font-size: .8rem; flex-shrink: 0; margin-top: 1px; font-weight: 700; }
.inclusions__item--yes .inclusions__check { color: #27ae60; }
.inclusions__item--no  .inclusions__check { color: #e74c3c; }

@media (max-width: 560px) {
  .inclusions__grid { grid-template-columns: 1fr; }
}
</style>