<template>
  <div class="itinerary detail-card">
    <h3 class="itinerary__title">Day-by-day itinerary</h3>

    <div class="itinerary__list">
      <div
        class="itinerary__item"
        v-for="(day, idx) in itinerary"
        :key="day.day"
        :class="{ open: openIndex === idx }"
      >
        <button class="itinerary__header" @click="toggleDay(idx)">
          <div class="itinerary__day-badge">Day {{ day.day }}</div>
          <div class="itinerary__day-title">{{ day.title }}</div>
          <span class="itinerary__chevron">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </span>
        </button>

        <div class="itinerary__body-wrapper">
          <div class="itinerary__body">
            <p class="itinerary__desc">{{ day.desc }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  itinerary: { type: Array, default: () => [] },
})

const openIndex = ref(0) // Start with Day 1 open

function toggleDay(idx) {
  openIndex.value = openIndex.value === idx ? null : idx
}
</script>

<style scoped>
.itinerary__title {
  font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 24px;
}

.itinerary__list { display: flex; flex-direction: column; gap: 12px; }

.itinerary__item {
  border: 1px solid var(--gray-100); border-radius: 14px; overflow: hidden;
  transition: all var(--transition); background: var(--gray-50);
}
.itinerary__item.open { border-color: var(--teal); background: var(--white); box-shadow: var(--shadow-sm); }

.itinerary__header {
  width: 100%; display: flex; align-items: center; gap: 14px;
  padding: 16px 20px; background: transparent; border: none; cursor: pointer;
  text-align: left; transition: background var(--transition);
}
.itinerary__header:hover { background: rgba(0,0,0,0.02); }

.itinerary__day-badge {
  background: var(--teal); color: #fff;
  font-size: .75rem; font-weight: 700; padding: 4px 14px; border-radius: 50px;
  white-space: nowrap; flex-shrink: 0; transition: background 0.3s;
}
.itinerary__item.open .itinerary__day-badge { background: var(--indigo); }

.itinerary__day-title {
  flex: 1; font-weight: 600; font-size: .95rem; color: var(--indigo);
}

.itinerary__chevron { 
  font-size: .7rem; color: var(--gray-400); flex-shrink: 0; 
  transition: transform 0.3s ease;
}
.itinerary__item.open .itinerary__chevron { transform: rotate(180deg); color: var(--indigo); }

/* Smooth height animation using grid-template-rows */
.itinerary__body-wrapper {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.itinerary__item.open .itinerary__body-wrapper {
  grid-template-rows: 1fr;
}

.itinerary__body { 
  overflow: hidden; 
}

.itinerary__desc { 
  padding: 0 20px 20px 20px;
  font-size: .9rem; color: var(--gray-600); line-height: 1.7; 
}
</style>