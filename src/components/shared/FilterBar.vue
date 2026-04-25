<template>
  <div class="filter-bar-wrapper">
    <div class="filter-bar">
      <div class="filter-bar__inner">

        <!-- Category pills -->
        <button
          class="filter-pill"
          :class="{ active: modelValue === cat.value }"
          v-for="cat in categories"
          :key="cat.value"
          @click="$emit('update:modelValue', cat.value)"
        >
          <span v-if="cat.icon">{{ cat.icon }}</span>
          {{ cat.label }}
          <span class="filter-pill__count" v-if="cat.count != null">{{ cat.count }}</span>
        </button>

      </div>

      <!-- Right: sort + view + filter button -->
      <div class="filter-bar__right">
        <select
          class="filter-sort"
          :value="sortBy"
          @change="$emit('update:sortBy', $event.target.value)"
        >
          <option value="recommended">Recommended</option>
          <option value="price_asc">Price ↑</option>
          <option value="price_desc">Price ↓</option>
          <option value="rating">Top rated</option>
          <option value="popular">Most popular</option>
        </select>

        <div class="view-toggle">
          <button
            class="view-btn"
            :class="{ active: viewMode === 'grid' }"
            @click="$emit('update:viewMode', 'grid')"
            title="Grid"
          >⊞</button>
          <button
            class="view-btn"
            :class="{ active: viewMode === 'list' }"
            @click="$emit('update:viewMode', 'list')"
            title="List"
          >☰</button>
        </div>

        <button
          class="filter-bar__filter-btn btn btn-outline"
          :class="{ 'is-open': panelOpen, 'has-filters': activeFilterCount > 0 }"
          @click="panelOpen = !panelOpen"
        >
          <span class="filter-btn-icon">⚙</span>
          Filters
          <span class="filter-badge" v-if="activeFilterCount">{{ activeFilterCount }}</span>
          <span class="filter-chevron" :class="{ rotated: panelOpen }">›</span>
        </button>
      </div>
    </div>

    <!-- ── Advanced Filters drop-down panel ─────────────────────────────── -->
    <Transition name="panel-slide">
      <div class="advanced-panel" v-if="panelOpen">
        <div class="advanced-panel__inner">

          <div class="ap-grid">

            <!-- Languages -->
            <div class="ap-group">
              <div class="ap-group__label">🗣 Languages</div>
              <div class="ap-chips">
                <button
                  v-for="lang in LANGUAGES"
                  :key="lang"
                  class="ap-chip"
                  :class="{ active: advancedFilters.languages.includes(lang) }"
                  @click="toggle(advancedFilters.languages, lang)"
                >{{ lang }}</button>
              </div>
            </div>

            <!-- Group Size -->
            <div class="ap-group">
              <div class="ap-group__label">👥 Group Size</div>
              <div class="ap-chips">
                <button
                  v-for="size in GROUP_SIZES"
                  :key="size.value"
                  class="ap-chip"
                  :class="{ active: advancedFilters.groupSizes.includes(size.value) }"
                  @click="toggle(advancedFilters.groupSizes, size.value)"
                >{{ size.label }}</button>
              </div>
            </div>

            <!-- Travel Months -->
            <div class="ap-group ap-group--wide">
              <div class="ap-group__label">📅 Best Months</div>
              <div class="ap-chips">
                <button
                  v-for="month in MONTHS"
                  :key="month"
                  class="ap-chip"
                  :class="{ active: advancedFilters.months.includes(month) }"
                  @click="toggle(advancedFilters.months, month)"
                >{{ month }}</button>
              </div>
            </div>

            <!-- Difficulty -->
           
            <!-- Accommodation -->
            <div class="ap-group">
              <div class="ap-group__label">🏨 Accommodation</div>
              <div class="ap-chips">
                <button
                  v-for="acc in ACCOMMODATIONS"
                  :key="acc"
                  class="ap-chip"
                  :class="{ active: advancedFilters.accommodations.includes(acc) }"
                  @click="toggle(advancedFilters.accommodations, acc)"
                >{{ acc }}</button>
              </div>
            </div>

            <!-- Perks -->
            <div class="ap-group">
              <div class="ap-group__label">✨ Perks & Inclusions</div>
              <div class="ap-chips">
                <button
                  v-for="perk in PERKS"
                  :key="perk"
                  class="ap-chip"
                  :class="{ active: advancedFilters.perks.includes(perk) }"
                  @click="toggle(advancedFilters.perks, perk)"
                >{{ perk }}</button>
              </div>
            </div>

            <!-- Min Reviews -->
            <div class="ap-group">
              <div class="ap-group__label">💬 Min Reviews</div>
              <div class="ap-range-row">
                <input
                  type="range" min="0" max="500" step="10"
                  :value="advancedFilters.minReviews"
                  @input="advancedFilters.minReviews = +$event.target.value; emitChange()"
                  class="ap-range"
                />
                <span class="ap-range-val">{{ advancedFilters.minReviews > 0 ? advancedFilters.minReviews + '+' : 'Any' }}</span>
              </div>
            </div>

            <!-- Instant Confirmation -->
            <div class="ap-group">
              <div class="ap-group__label">⚡ Instant Confirmation</div>
              <label class="ap-toggle">
                <input
                  type="checkbox"
                  :checked="advancedFilters.instantConfirmation"
                  @change="advancedFilters.instantConfirmation = $event.target.checked; emitChange()"
                />
                <span class="ap-toggle__track">
                  <span class="ap-toggle__thumb" />
                </span>
                <span class="ap-toggle__label">Only show instantly confirmed</span>
              </label>
            </div>

          </div>

          <div class="ap-footer">
            <button class="ap-reset" @click="resetAdvanced">Reset advanced filters</button>
            <button class="ap-apply btn btn-coral" @click="panelOpen = false">
              Apply{{ activeFilterCount ? ` (${activeFilterCount})` : '' }}
            </button>
          </div>

        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  categories:        { type: Array,  default: () => [] },
  modelValue:        { type: String, default: 'all'    },
  sortBy:            { type: String, default: 'recommended' },
  viewMode:          { type: String, default: 'grid'   },
  activeFilterCount: { type: Number, default: 0        },
  advancedFilters:   {
    type: Object,
    default: () => ({
      languages: [], groupSizes: [], months: [], difficulty: null,
      accommodations: [], perks: [], minReviews: 0, instantConfirmation: false,
    }),
  },
})

const emit = defineEmits([
  'update:modelValue', 'update:sortBy', 'update:viewMode',
  'update:advancedFilters',
  'open-filters', // kept for backwards-compat but no longer needed
])

const panelOpen = ref(false)

// Deep-clone so we can mutate locally and emit changes
const advancedFilters = ref(JSON.parse(JSON.stringify(props.advancedFilters)))

watch(() => props.advancedFilters, (val) => {
  advancedFilters.value = JSON.parse(JSON.stringify(val))
}, { deep: true })

function emitChange() {
  emit('update:advancedFilters', JSON.parse(JSON.stringify(advancedFilters.value)))
}

function toggle(arr, val) {
  const idx = arr.indexOf(val)
  idx === -1 ? arr.push(val) : arr.splice(idx, 1)
  emitChange()
}

function resetAdvanced() {
  advancedFilters.value = {
    languages: [], groupSizes: [], months: [], difficulty: null,
    accommodations: [], perks: [], minReviews: 0, instantConfirmation: false,
  }
  emitChange()
}

// ── Constants ────────────────────────────────────────────────────────────
const LANGUAGES     = ['English', 'Spanish', 'French', 'German', 'Arabic', 'Mandarin', 'Portuguese', 'Italian']
const GROUP_SIZES   = [
  { value: 'solo',   label: 'Solo'    },
  { value: 'couple', label: 'Couple'  },
  { value: 'small',  label: '3–8'     },
  { value: 'medium', label: '9–20'    },
  { value: 'large',  label: '20+'     },
]
const MONTHS        = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
const DIFFICULTIES  = ['Easy', 'Moderate', 'Challenging', 'Expert']
const ACCOMMODATIONS = ['Hotel', 'Resort', 'Hostel', 'Villa', 'Camping', 'Glamping', 'Boat']
const PERKS         = ['Meals included', 'Airport transfer', 'Equipment', 'Photography', 'Insurance', 'Visa support']
</script>

<style scoped>
.filter-bar-wrapper {
  position: sticky;
  top: 0;
  z-index: 30;
  background: var(--white);
  border-bottom: 1px solid var(--gray-200);
}

/* ── Main bar row ─────────────────────────────────────────────────────────── */
.filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 0 5%;
  flex-wrap: wrap;
}

.filter-bar__inner {
  display: flex;
  gap: 6px;
  overflow-x: auto;
  padding: 14px 0;
  scrollbar-width: none;
  flex: 1;
}
.filter-bar__inner::-webkit-scrollbar { display: none; }

.filter-pill {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 16px; border-radius: 50px; white-space: nowrap;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
  flex-shrink: 0;
}
.filter-pill:hover  { border-color: var(--coral); color: var(--coral); }
.filter-pill.active { background: var(--coral); border-color: var(--coral); color: #fff; }

.filter-pill__count {
  background: rgba(255,255,255,.25); border-radius: 50px;
  padding: 1px 7px; font-size: .7rem; font-weight: 700;
}
.filter-pill.active .filter-pill__count { background: rgba(255,255,255,.25); }
.filter-pill:not(.active) .filter-pill__count { background: var(--gray-100); color: var(--gray-400); }

.filter-bar__right {
  display: flex; align-items: center; gap: 10px;
  padding: 14px 0; flex-shrink: 0;
}

.filter-sort {
  padding: 8px 12px; border: 1.5px solid var(--gray-200); border-radius: 10px;
  font-family: 'DM Sans', sans-serif; font-size: .84rem; color: var(--indigo);
  background: var(--white); outline: none; cursor: pointer;
  transition: border-color var(--transition);
}
.filter-sort:focus { border-color: var(--coral); }

.view-toggle { display: flex; background: var(--gray-100); border-radius: 10px; padding: 3px; }
.view-btn {
  width: 32px; height: 32px; border-radius: 7px; border: none; background: none;
  font-size: .95rem; cursor: pointer; color: var(--gray-400); transition: all var(--transition);
}
.view-btn.active { background: var(--white); color: var(--indigo); box-shadow: var(--shadow); }

/* ── Filter button ──────────────────────────────────────────────────────── */
.filter-bar__filter-btn {
  position: relative;
  padding: 8px 14px;
  font-size: .84rem;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all var(--transition);
}
.filter-bar__filter-btn.is-open,
.filter-bar__filter-btn.has-filters {
  border-color: var(--coral);
  color: var(--coral);
}
.filter-btn-icon { font-size: .9rem; }
.filter-chevron {
  font-size: 1.1rem;
  line-height: 1;
  display: inline-block;
  transition: transform .2s ease;
  transform: rotate(90deg);
}
.filter-chevron.rotated { transform: rotate(-90deg); }

.filter-badge {
  position: absolute; top: -6px; right: -6px;
  width: 17px; height: 17px; border-radius: 50%;
  background: var(--coral); color: #fff;
  font-size: .65rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

/* ── Advanced Panel ─────────────────────────────────────────────────────── */
.advanced-panel {
  border-top: 1px solid var(--gray-100);
  background: var(--white);
  box-shadow: 0 8px 24px rgba(0,0,0,.07);
}

.advanced-panel__inner {
  padding: 24px 5%;
}

.ap-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 24px 32px;
}

.ap-group--wide {
  grid-column: span 2;
}

.ap-group__label {
  font-family: 'DM Sans', sans-serif;
  font-size: .78rem;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: var(--gray-400);
  margin-bottom: 10px;
}

.ap-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.ap-chip {
  padding: 5px 13px;
  border-radius: 50px;
  border: 1.5px solid var(--gray-200);
  background: var(--white);
  font-family: 'DM Sans', sans-serif;
  font-size: .82rem;
  font-weight: 500;
  color: var(--gray-600);
  cursor: pointer;
  transition: all var(--transition);
  white-space: nowrap;
}
.ap-chip:hover  { border-color: var(--coral); color: var(--coral); }
.ap-chip.active { background: var(--coral); border-color: var(--coral); color: #fff; }

/* Range slider */
.ap-range-row {
  display: flex;
  align-items: center;
  gap: 12px;
}
.ap-range {
  flex: 1;
  accent-color: var(--coral);
  cursor: pointer;
}
.ap-range-val {
  font-family: 'DM Sans', sans-serif;
  font-size: .85rem;
  font-weight: 700;
  color: var(--indigo);
  min-width: 40px;
  text-align: right;
}

/* Toggle switch */
.ap-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}
.ap-toggle input { display: none; }
.ap-toggle__track {
  width: 42px; height: 24px;
  border-radius: 12px;
  background: var(--gray-200);
  position: relative;
  transition: background .2s ease;
  flex-shrink: 0;
}
.ap-toggle input:checked + .ap-toggle__track { background: var(--coral); }
.ap-toggle__thumb {
  position: absolute;
  top: 3px; left: 3px;
  width: 18px; height: 18px;
  border-radius: 50%;
  background: var(--white);
  box-shadow: 0 1px 4px rgba(0,0,0,.2);
  transition: transform .2s ease;
}
.ap-toggle input:checked ~ .ap-toggle__track .ap-toggle__thumb,
.ap-toggle input:checked + .ap-toggle__track .ap-toggle__thumb {
  transform: translateX(18px);
}
.ap-toggle__label {
  font-family: 'DM Sans', sans-serif;
  font-size: .85rem;
  font-weight: 500;
  color: var(--gray-600);
}

/* Footer */
.ap-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid var(--gray-100);
}
.ap-reset {
  background: none;
  border: none;
  font-family: 'DM Sans', sans-serif;
  font-size: .85rem;
  color: var(--gray-400);
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 3px;
  transition: color var(--transition);
}
.ap-reset:hover { color: var(--coral); }
.ap-apply {
  padding: 10px 28px;
  font-size: .88rem;
}

/* ── Transition ─────────────────────────────────────────────────────────── */
.panel-slide-enter-active,
.panel-slide-leave-active {
  transition: all .22s ease;
  overflow: hidden;
}
.panel-slide-enter-from,
.panel-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* ── Responsive ─────────────────────────────────────────────────────────── */
@media (max-width: 640px) {
  .filter-bar { padding: 0 4%; }
  .view-toggle { display: none; }
  .filter-bar__right { padding: 10px 0; }
  .advanced-panel__inner { padding: 20px 4%; }
  .ap-group--wide { grid-column: span 1; }
}
</style>