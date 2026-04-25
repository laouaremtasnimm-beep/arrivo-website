<template>
  <section class="search-section">
    <div class="search-bar">
      <span class="search-icon">🔍</span>

      <input
        class="search-input"
        v-model="query"
        :placeholder="placeholder"
        @keyup.enter="emit('search', query)"
      />

      <div class="search-divider" />

      <!-- Dates trigger -->
      <div
        ref="datesTrigger"
        class="search-filter"
        :class="{ 'search-filter--active': dateLabel }"
        @click="openPicker('dates')"
      >
        📅 <span>{{ dateLabel || 'Any dates' }}</span>
      </div>

      <div class="search-divider" />

      <!-- Guests trigger -->
      <div
        ref="guestsTrigger"
        class="search-filter"
        :class="{ 'search-filter--active': guestLabel }"
        @click="openPicker('guests')"
      >
        👤 <span>{{ guestLabel || 'Guests' }}</span>
      </div>

      <button class="btn btn-coral search-btn" @click="emit('search', query)">
        Search
      </button>
    </div>

    <!-- Shared picker -->
    <DateGuestPicker
      v-model="openTab"
      :anchor-rect="anchorRect"
      :initial-dates="pickerDates"
      :initial-guests="pickerGuests"
      @apply-dates="onDates"
      @apply-guests="onGuests"
    />
  </section>
</template>

<script setup>
import { ref } from 'vue'
import DateGuestPicker from '@/components/shared/DateGuestPicker.vue'

const props = defineProps({
  placeholder:    { type: String, default: 'Search destinations, packages, or services…' },
})
const emit = defineEmits(['search', 'filter-dates', 'filter-guests'])

const query = ref('')

// ── Picker state ───────────────────────────────────────────────────────────
const openTab       = ref(null)
const anchorRect    = ref(null)
const datesTrigger  = ref(null)
const guestsTrigger = ref(null)

const dateLabel    = ref('')
const guestLabel   = ref('')
const pickerDates  = ref({ start: null, end: null })
const pickerGuests = ref({ adults: 1, children: 0, infants: 0 })

function openPicker(tab) {
  const trigger = tab === 'dates' ? datesTrigger.value : guestsTrigger.value
  anchorRect.value = trigger?.getBoundingClientRect() ?? null
  openTab.value = openTab.value === tab ? null : tab
}

function onDates(payload) {
  pickerDates.value = { start: payload.start, end: payload.end }
  dateLabel.value   = payload.label
  emit('filter-dates', payload)
}
function onGuests(payload) {
  pickerGuests.value = payload
  guestLabel.value   = payload.label
  emit('filter-guests', payload)
}
</script>

<style scoped>
.search-section {
  background: var(--gray-50);
  padding: 48px 5%;
}

.search-bar {
  max-width: 900px; margin: 0 auto;
  background: var(--white);
  border-radius: 20px;
  padding: 8px 8px 8px 24px;
  display: flex; align-items: center; gap: 0;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--gray-200);
}

.search-icon { font-size: 1.2rem; margin-right: 10px; flex-shrink: 0; }

.search-input {
  flex: 1; border: none; outline: none;
  font-family: 'DM Sans', sans-serif;
  font-size: 1rem; color: var(--indigo);
  background: transparent; padding: 8px 0;
  min-width: 0;
}
.search-input::placeholder { color: var(--gray-400); }

.search-divider { width: 1px; height: 36px; background: var(--gray-200); margin: 0 18px; flex-shrink: 0; }

.search-filter {
  display: flex; align-items: center; gap: 7px;
  font-size: .9rem; color: var(--gray-600); font-weight: 500;
  padding: 8px 14px; cursor: pointer; border-radius: 10px;
  transition: background var(--transition), color var(--transition);
  white-space: nowrap; flex-shrink: 0; user-select: none;
}
.search-filter:hover          { background: var(--gray-100); }
.search-filter--active        { color: var(--coral); font-weight: 600; }
.search-filter--active:hover  { background: var(--coral-lt); }

.search-btn { padding: 13px 26px; font-size: .95rem; border-radius: 14px; }

@media (max-width: 640px) {
  .search-section { padding: 32px 4%; }
  .search-bar { padding: 8px; gap: 6px; border-radius: 16px; }
  .search-divider, .search-filter { display: none; }
  .search-input { padding: 8px 10px; }
  .search-btn { width: 100%; border-radius: 12px; }
}
</style>