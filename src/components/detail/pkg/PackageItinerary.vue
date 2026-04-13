<template>
  <div class="itinerary">
    <h3 class="itinerary__title">Day-by-day itinerary</h3>

    <div class="itinerary__list">
      <div
        class="itinerary__item"
        v-for="(day, idx) in itinerary"
        :key="day.day"
        :class="{ open: openIndex === idx }"
      >
        <button class="itinerary__header" @click="openIndex = openIndex === idx ? null : idx">
          <div class="itinerary__day-badge">Day {{ day.day }}</div>
          <div class="itinerary__day-title">{{ day.title }}</div>
          <span class="itinerary__chevron">{{ openIndex === idx ? '▲' : '▼' }}</span>
        </button>

        <Transition name="slide-down">
          <div class="itinerary__body" v-if="openIndex === idx">
            <p class="itinerary__desc">{{ day.desc }}</p>
          </div>
        </Transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  itinerary: { type: Array, default: () => [] },
})

const openIndex = ref(0) // first day open by default
</script>

<style scoped>
.itinerary__title {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 20px;
}

.itinerary__list { display: flex; flex-direction: column; gap: 8px; }

.itinerary__item {
  border: 1.5px solid var(--gray-200); border-radius: 12px; overflow: hidden;
  transition: border-color var(--transition);
}
.itinerary__item.open { border-color: var(--teal); }

.itinerary__header {
  width: 100%; display: flex; align-items: center; gap: 14px;
  padding: 16px 20px; background: var(--white); border: none; cursor: pointer;
  text-align: left; transition: background var(--transition);
}
.itinerary__header:hover { background: var(--gray-50); }

.itinerary__day-badge {
  background: var(--teal); color: #fff;
  font-size: .72rem; font-weight: 700; padding: 4px 12px; border-radius: 50px;
  white-space: nowrap; flex-shrink: 0;
}
.itinerary__item.open .itinerary__day-badge { background: var(--teal-dk); }

.itinerary__day-title {
  flex: 1; font-weight: 600; font-size: .92rem; color: var(--indigo);
}
.itinerary__chevron { font-size: .7rem; color: var(--gray-400); flex-shrink: 0; }

.itinerary__body { padding: 0 20px 18px; background: var(--white); }
.itinerary__desc { font-size: .88rem; color: var(--gray-600); line-height: 1.7; }

.slide-down-enter-active, .slide-down-leave-active { transition: opacity .18s ease, transform .18s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-6px); }
</style>