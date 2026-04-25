<template>
  <div class="search-header">
    <div class="search-header__inner">

      <RouterLink to="/" class="logo">Voya<span>go</span></RouterLink>

      <div class="search-bar">
        <span class="search-bar__icon">🔍</span>
        <input
          class="search-bar__input"
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          @keyup.enter="$emit('search')"
          placeholder="Destinations, packages, services…"
        />
        <div class="search-bar__divider" />

        <!-- Dates trigger -->
        <div
          ref="datesTrigger"
          class="search-bar__filter"
          :class="{ 'search-bar__filter--active': dateLabel }"
          @click="openPicker('dates', $event)"
        >
          📅 <span>{{ dateLabel || 'Any dates' }}</span>
        </div>

        <div class="search-bar__divider" />

        <!-- Guests trigger -->
        <div
          ref="guestsTrigger"
          class="search-bar__filter"
          :class="{ 'search-bar__filter--active': guestLabel }"
          @click="openPicker('guests', $event)"
        >
          👤 <span>{{ guestLabel || 'Guests' }}</span>
        </div>

        <button class="btn btn-coral search-bar__btn" @click="$emit('search')">Search</button>
      </div>

      <div class="search-categories">
        <button
          class="category-pill"
          :class="{ active: activeCategory === c.key }"
          v-for="c in categories"
          :key="c.key"
          @click="$emit('update:activeCategory', c.key)"
        >
          <span>{{ c.icon }}</span> {{ c.label }}
        </button>
      </div>

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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import DateGuestPicker from '@/components/shared/DateGuestPicker.vue'

defineProps({
  modelValue:     { type: String, default: '' },
  activeCategory: { type: String, default: 'all' },
})
defineEmits(['update:modelValue', 'update:activeCategory', 'search'])

const categories = [
  { key: 'all',     icon: '🌐', label: 'All'         },
  { key: 'package', icon: '✈️', label: 'Packages'    },
  { key: 'service', icon: '🛎️', label: 'Services'    },
  { key: 'dest',    icon: '📍', label: 'Destinations' },
]

// ── Picker state ───────────────────────────────────────────────────────────
const openTab      = ref(null)          // null | 'dates' | 'guests'
const anchorRect   = ref(null)
const datesTrigger = ref(null)
const guestsTrigger= ref(null)

const dateLabel    = ref('')
const guestLabel   = ref('')
const pickerDates  = ref({ start: null, end: null })
const pickerGuests = ref({ adults: 1, children: 0, infants: 0 })

function openPicker(tab, event) {
  const trigger = tab === 'dates' ? datesTrigger.value : guestsTrigger.value
  anchorRect.value = trigger?.getBoundingClientRect() ?? null
  // Toggle: close if already open on same tab
  openTab.value = openTab.value === tab ? null : tab
}

function onDates(payload) {
  pickerDates.value = { start: payload.start, end: payload.end }
  dateLabel.value   = payload.label
}
function onGuests(payload) {
  pickerGuests.value = payload
  guestLabel.value   = payload.label
}
</script>

<style scoped>
.search-header {
  background: var(--white);
  border-bottom: 1px solid var(--gray-200);
  position: sticky; top: 0; z-index: 50;
  box-shadow: var(--shadow);
}
.search-header__inner {
  padding: 16px 5% 0;
  display: flex; flex-direction: column; gap: 16px;
}

.logo {
  font-family: 'Fraunces', serif;
  font-size: 1.5rem; font-weight: 700;
  color: var(--indigo); letter-spacing: -.5px;
  text-decoration: none; width: fit-content;
}
.logo span { color: var(--coral); }

.search-bar {
  display: flex; align-items: center;
  background: var(--white); border: 1.5px solid var(--gray-200);
  border-radius: 18px; padding: 7px 7px 7px 20px;
  box-shadow: var(--shadow); max-width: 860px;
}
.search-bar__icon  { font-size: 1.1rem; margin-right: 10px; flex-shrink: 0; }
.search-bar__input {
  flex: 1; border: none; outline: none;
  font-family: 'DM Sans', sans-serif; font-size: .97rem;
  color: var(--indigo); background: transparent; min-width: 0;
}
.search-bar__input::placeholder { color: var(--gray-400); }
.search-bar__divider { width: 1px; height: 30px; background: var(--gray-200); margin: 0 14px; flex-shrink: 0; }
.search-bar__filter {
  display: flex; align-items: center; gap: 6px;
  font-size: .88rem; color: var(--gray-600); font-weight: 500;
  padding: 6px 12px; border-radius: 8px; cursor: pointer;
  transition: background var(--transition), color var(--transition);
  white-space: nowrap; flex-shrink: 0; user-select: none;
}
.search-bar__filter:hover         { background: var(--gray-100); }
.search-bar__filter--active       { color: var(--coral); font-weight: 600; }
.search-bar__filter--active:hover { background: var(--coral-lt); }
.search-bar__btn { padding: 11px 24px; font-size: .92rem; border-radius: 12px; }

.search-categories {
  display: flex; gap: 6px; overflow-x: auto; padding-bottom: 14px;
  scrollbar-width: none;
}
.search-categories::-webkit-scrollbar { display: none; }

.category-pill {
  display: flex; align-items: center; gap: 7px;
  padding: 8px 18px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .86rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; white-space: nowrap;
  transition: all var(--transition);
}
.category-pill:hover  { border-color: var(--coral); color: var(--coral); }
.category-pill.active { background: var(--coral); border-color: var(--coral); color: #fff; }

@media (max-width: 768px) {
  .search-bar__divider,
  .search-bar__filter { display: none; }
  .search-bar { border-radius: 14px; }
  .search-header__inner { padding: 14px 4% 0; }
}
</style>