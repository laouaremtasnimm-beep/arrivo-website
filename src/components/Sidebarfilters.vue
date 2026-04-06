<template>
  <aside class="sidebar" :class="{ 'sidebar--open': open }">

    <div class="sidebar__header">
      <h3 class="sidebar__title">Filters</h3>
      <button class="sidebar__reset" @click="$emit('reset')">Reset all</button>
    </div>

    <!-- Price range -->
    <div class="filter-block">
      <div class="filter-block__label">Price range</div>
      <div class="price-inputs">
        <div class="price-input-wrap">
          <span class="price-currency">$</span>
          <input
            class="price-input"
            :value="filters.priceMin"
            @input="update('priceMin', +$event.target.value || null)"
            type="number" min="0" placeholder="0"
          />
        </div>
        <span class="price-sep">—</span>
        <div class="price-input-wrap">
          <span class="price-currency">$</span>
          <input
            class="price-input"
            :value="filters.priceMax"
            @input="update('priceMax', +$event.target.value || null)"
            type="number" min="0" placeholder="5000"
          />
        </div>
      </div>
    </div>

    <!-- Duration -->
    <div class="filter-block">
      <div class="filter-block__label">Duration (days)</div>
      <div class="checkbox-list">
        <label class="check-item" v-for="d in durationOptions" :key="d.value">
          <input
            type="checkbox" class="check-input"
            :value="d.value"
            :checked="filters.durations.includes(d.value)"
            @change="toggleArray('durations', d.value)"
          />
          <span class="check-label">{{ d.label }}</span>
        </label>
      </div>
    </div>

    <!-- Minimum rating -->
    <div class="filter-block">
      <div class="filter-block__label">Minimum rating</div>
      <div class="rating-options">
        <button
          class="rating-btn"
          :class="{ active: filters.minRating === r }"
          v-for="r in [3, 3.5, 4, 4.5]"
          :key="r"
          @click="update('minRating', filters.minRating === r ? null : r)"
        >
          <span class="star">★</span> {{ r }}+
        </button>
      </div>
    </div>

    <!-- Type -->
    <div class="filter-block">
      <div class="filter-block__label">Type</div>
      <div class="checkbox-list">
        <label class="check-item" v-for="t in typeOptions" :key="t">
          <input
            type="checkbox" class="check-input"
            :value="t"
            :checked="filters.types.includes(t)"
            @change="toggleArray('types', t)"
          />
          <span class="check-label">{{ t }}</span>
        </label>
      </div>
    </div>

    <!-- Mobile apply button -->
    <button class="btn btn-coral sidebar__apply" @click="$emit('close')">
      Show {{ resultCount }} results
    </button>

  </aside>
</template>

<script setup>
const props = defineProps({
  filters:     { type: Object, required: true },
  open:        { type: Boolean, default: false },
  resultCount: { type: Number, default: 0 },
})

const emit = defineEmits(['update:filters', 'reset', 'close'])

const durationOptions = [
  { label: '1–3 days',  value: '1-3'  },
  { label: '4–7 days',  value: '4-7'  },
  { label: '8–14 days', value: '8-14' },
  { label: '15+ days',  value: '15+'  },
]
const typeOptions = ['Adventure', 'Beach', 'Cultural', 'Family', 'Wellness', 'City Break']

function update(key, value) {
  emit('update:filters', { ...props.filters, [key]: value })
}

function toggleArray(key, value) {
  const arr = [...props.filters[key]]
  const idx = arr.indexOf(value)
  idx === -1 ? arr.push(value) : arr.splice(idx, 1)
  emit('update:filters', { ...props.filters, [key]: arr })
}
</script>

<style scoped>
.sidebar {
  background: var(--white);
  border-right: 1px solid var(--gray-200);
  padding: 28px 24px;
  position: sticky; top: 133px;
  height: calc(100vh - 133px);
  overflow-y: auto; scrollbar-width: thin;
}

.sidebar__header {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;
}
.sidebar__title {
  font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700;
}
.sidebar__reset {
  font-size: .82rem; font-weight: 600; color: var(--coral);
  background: none; border: none; cursor: pointer; transition: opacity var(--transition);
}
.sidebar__reset:hover { opacity: .7; }
.sidebar__apply { display: none; width: 100%; padding: 13px; margin-top: 24px; }

/* Filter blocks */
.filter-block {
  margin-bottom: 28px; padding-bottom: 28px;
  border-bottom: 1px solid var(--gray-100);
}
.filter-block:last-of-type { border-bottom: none; }
.filter-block__label {
  font-size: .78rem; font-weight: 700; color: var(--indigo);
  text-transform: uppercase; letter-spacing: .06em; margin-bottom: 14px;
}

/* Price */
.price-inputs { display: flex; align-items: center; gap: 10px; }
.price-input-wrap { position: relative; flex: 1; }
.price-currency {
  position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
  font-size: .88rem; color: var(--gray-400); font-weight: 600;
}
.price-input {
  width: 100%; padding: 10px 10px 10px 26px;
  border: 1.5px solid var(--gray-200); border-radius: 10px;
  font-family: 'DM Sans', sans-serif; font-size: .88rem; color: var(--indigo);
  outline: none; transition: border-color var(--transition);
}
.price-input:focus { border-color: var(--coral); }
.price-sep { color: var(--gray-400); font-weight: 600; flex-shrink: 0; }

/* Checkboxes */
.checkbox-list  { display: flex; flex-direction: column; gap: 10px; }
.check-item     { display: flex; align-items: center; gap: 10px; cursor: pointer; }
.check-input    { width: 16px; height: 16px; border-radius: 4px; accent-color: var(--coral); cursor: pointer; flex-shrink: 0; }
.check-label    { font-size: .88rem; color: var(--gray-600); }

/* Rating */
.rating-options { display: flex; gap: 8px; flex-wrap: wrap; }
.rating-btn {
  display: flex; align-items: center; gap: 4px;
  padding: 7px 14px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
}
.rating-btn:hover  { border-color: var(--coral); color: var(--coral); }
.rating-btn.active { background: var(--coral); border-color: var(--coral); color: #fff; }

@media (max-width: 768px) {
  .sidebar {
    position: fixed; top: 0; left: -100%; bottom: 0;
    width: 300px; z-index: 70; height: 100%;
    transition: left .3s ease; box-shadow: var(--shadow-lg);
  }
  .sidebar--open  { left: 0; }
  .sidebar__apply { display: block; }
}
</style>