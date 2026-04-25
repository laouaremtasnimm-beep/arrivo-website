<template>
  <div class="adv-filters">

    <button
      class="adv-trigger"
      :class="{ 'adv-trigger--active': panelOpen || advancedCount > 0 }"
      @click="panelOpen = !panelOpen"
    >
      <svg width="15" height="15" viewBox="0 0 15 15" fill="none" style="flex-shrink:0">
        <path d="M1.5 3.5h12M3.5 7.5h8M5.5 11.5h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
      </svg>
      Advanced filters
      <span v-if="advancedCount > 0" class="adv-badge">{{ advancedCount }}</span>
      <svg
        width="12" height="12" viewBox="0 0 12 12" fill="none"
        :style="{ transform: panelOpen ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform .22s ease', flexShrink: 0 }"
      >
        <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>

    <Transition name="adv-slide">
      <div v-if="panelOpen" class="adv-panel">

        <div class="adv-row">
          <div class="adv-block">
            <p class="adv-label">Guide language</p>
            <div class="tag-group">
              <button
                v-for="lang in LANGUAGES" :key="lang"
                class="tag-btn" :class="{ 'tag-btn--active': local.languages.includes(lang) }"
                @click="toggleArr('languages', lang)"
              >{{ lang }}</button>
            </div>
          </div>

          <div class="adv-block">
            <p class="adv-label">Group size</p>
            <div class="tag-group">
              <button
                v-for="g in GROUP_SIZES" :key="g.value"
                class="tag-btn" :class="{ 'tag-btn--active': local.groupSizes.includes(g.value) }"
                @click="toggleArr('groupSizes', g.value)"
              >{{ g.label }}</button>
            </div>
          </div>
        </div>

        <div class="adv-block">
          <p class="adv-label">Departure month</p>
          <div class="month-grid">
            <button
              v-for="m in MONTHS" :key="m.v"
              class="month-btn" :class="{ 'month-btn--active': local.months.includes(m.v) }"
              @click="toggleArr('months', m.v)"
            >{{ m.l }}</button>
          </div>
        </div>

        <div class="adv-block">
          <p class="adv-label">Accommodation</p>
          <div class="tag-group">
            <button
              v-for="a in ACCOMMODATIONS" :key="a"
              class="tag-btn" :class="{ 'tag-btn--active': local.accommodations.includes(a) }"
              @click="toggleArr('accommodations', a)"
            >{{ a }}</button>
          </div>
        </div>

        <div class="adv-block">
          <p class="adv-label">Includes</p>
          <div class="perks-grid">
            <label
              v-for="p in PERKS" :key="p.value"
              class="perk-item" :class="{ 'perk-item--active': local.perks.includes(p.value) }"
            >
              <input type="checkbox" class="perk-check" :checked="local.perks.includes(p.value)" @change="toggleArr('perks', p.value)" />
              <span class="perk-icon">{{ p.icon }}</span>
              <span class="perk-label">{{ p.label }}</span>
            </label>
          </div>
        </div>

        <div class="adv-block">
          <p class="adv-label">
            Minimum reviews
            <span class="adv-val">{{ local.minReviews > 0 ? local.minReviews + '+' : 'Any' }}</span>
          </p>
          <input
            type="range" class="adv-slider"
            min="0" max="500" step="25"
            v-model.number="local.minReviews"
          />
          <div class="slider-ticks">
            <span>0</span><span>100</span><span>200</span><span>300</span><span>400</span><span>500+</span>
          </div>
        </div>

        <div class="adv-block adv-block--row">
          <p class="adv-label" style="margin:0">Instant confirmation only</p>
          <button
            class="toggle-btn" :class="{ 'toggle-btn--on': local.instantConfirmation }"
            @click="local.instantConfirmation = !local.instantConfirmation"
          >
            <span class="toggle-knob"></span>
          </button>
        </div>

        <div class="adv-footer">
          <button class="adv-clear" @click="clearAll">Clear all</button>
          <button class="btn btn-coral adv-apply" @click="apply">
            Apply<span v-if="advancedCount > 0">&nbsp;({{ advancedCount }})</span>
          </button>
        </div>

      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Object, required: true },
})
const emit = defineEmits(['update:modelValue'])

const LANGUAGES    = ['English', 'French', 'Spanish', 'Arabic', 'German', 'Italian']
const GROUP_SIZES  = [
  { label: 'Solo', value: 'solo' },
  { label: 'Couple', value: 'couple' },
  { label: 'Small (3–8)', value: 'small' },
  { label: 'Large (9+)', value: 'large' },
]
const MONTHS = [
  { l: 'Jan', v: 1 }, { l: 'Feb', v: 2 }, { l: 'Mar', v: 3 },
  { l: 'Apr', v: 4 }, { l: 'May', v: 5 }, { l: 'Jun', v: 6 },
  { l: 'Jul', v: 7 }, { l: 'Aug', v: 8 }, { l: 'Sep', v: 9 },
  { l: 'Oct', v: 10 }, { l: 'Nov', v: 11 }, { l: 'Dec', v: 12 },
]
const ACCOMMODATIONS = ['Hotel', 'Hostel', 'Resort', 'Camping', 'Boutique', 'Airbnb']
const PERKS = [
  { value: 'flights',   icon: '✈️', label: 'Flights'     },
  { value: 'meals',     icon: '🍽️', label: 'Meals'       },
  { value: 'transfers', icon: '🚌', label: 'Transfers'   },
  { value: 'guide',     icon: '🧭', label: 'Local guide' },
  { value: 'insurance', icon: '🛡️', label: 'Insurance'   },
  { value: 'visa',      icon: '📄', label: 'Visa assist' },
  { value: 'pets',      icon: '🐾', label: 'Pets allowed' },
]

function blank() {
  return {
    languages: [], groupSizes: [], months: [],
    accommodations: [], perks: [], minReviews: 0, instantConfirmation: false,
  }
}
function clone(src) {
  return {
    languages:           [...(src.languages           ?? [])],
    groupSizes:          [...(src.groupSizes          ?? [])],
    months:              [...(src.months              ?? [])],
    accommodations:      [...(src.accommodations      ?? [])],
    perks:               [...(src.perks               ?? [])],
    minReviews:          src.minReviews              ?? 0,
    instantConfirmation: src.instantConfirmation     ?? false,
  }
}

const panelOpen = ref(false)
const local     = ref(clone(props.modelValue))

watch(() => props.modelValue, v => { local.value = clone(v) }, { deep: true })

function toggleArr(key, val) {
  const arr = local.value[key]
  const i   = arr.indexOf(val)
  i === -1 ? arr.push(val) : arr.splice(i, 1)
}
function clearAll() {
  local.value = blank()
  emit('update:modelValue', { ...props.modelValue, ...blank() })
}
function apply() {
  emit('update:modelValue', { ...props.modelValue, ...clone(local.value) })
  panelOpen.value = false
}

const advancedCount = computed(() => {
  const l = local.value
  return (
    l.languages.length + l.groupSizes.length + l.months.length + l.accommodations.length + l.perks.length +
    (l.minReviews > 0 ? 1 : 0) + (l.instantConfirmation ? 1 : 0)
  )
})
</script>

<style scoped>
.adv-filters { width: 100%; }

/* ── Trigger button ───────────────────────────────────────────────────────── */
.adv-trigger {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 20px;
  border-radius: 50px;
  border: 1.5px solid var(--gray-200);
  background: var(--white);
  font-family: 'DM Sans', sans-serif;
  font-size: .86rem;
  font-weight: 600;
  color: var(--gray-600);
  cursor: pointer;
  transition: border-color var(--transition), color var(--transition);
}
.adv-trigger:hover,
.adv-trigger--active {
  border-color: var(--coral);
  color: var(--coral);
}
.adv-badge {
  background: var(--coral);
  color: #fff;
  font-size: .72rem;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 50px;
  line-height: 1.5;
}

/* ── Panel transition ────────────────────────────────────────────────────── */
.adv-slide-enter-active,
.adv-slide-leave-active { transition: opacity .22s ease, transform .22s cubic-bezier(.4,0,.2,1); }
.adv-slide-enter-from,
.adv-slide-leave-to     { opacity: 0; transform: translateY(-8px); }

/* ── Panel ───────────────────────────────────────────────────────────────── */
.adv-panel {
  margin-top: 10px;
  background: var(--white);
  border: 1.5px solid var(--gray-200);
  border-radius: var(--radius);
  padding: 24px 28px;
  display: flex;
  flex-direction: column;
  gap: 22px;
  box-shadow: var(--shadow-md);
}

/* ── Layout ──────────────────────────────────────────────────────────────── */
.adv-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
.adv-block { display: flex; flex-direction: column; gap: 10px; }
.adv-block--row { flex-direction: row; align-items: center; justify-content: space-between; }

/* ── Labels ──────────────────────────────────────────────────────────────── */
.adv-label {
  font-size: .75rem;
  font-weight: 700;
  color: var(--indigo);
  text-transform: uppercase;
  letter-spacing: .07em;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
}
.adv-val {
  background: var(--gray-100);
  color: var(--coral);
  font-size: .73rem;
  padding: 2px 8px;
  border-radius: 50px;
  font-weight: 700;
  text-transform: none;
  letter-spacing: 0;
}

/* ── Tag pills ───────────────────────────────────────────────────────────── */
.tag-group { display: flex; flex-wrap: wrap; gap: 8px; }
.tag-btn {
  padding: 6px 14px;
  border-radius: 50px;
  border: 1.5px solid var(--gray-200);
  background: var(--white);
  font-family: 'DM Sans', sans-serif;
  font-size: .82rem;
  font-weight: 600;
  color: var(--gray-600);
  cursor: pointer;
  transition: all var(--transition);
}
.tag-btn:hover      { border-color: var(--coral); color: var(--coral); }
.tag-btn--active    { background: var(--coral); border-color: var(--coral); color: #fff; }


/* ── Months ──────────────────────────────────────────────────────────────── */
.month-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 6px; }
.month-btn {
  padding: 7px 2px; border-radius: var(--radius-sm);
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .76rem; font-weight: 600;
  color: var(--gray-600); text-align: center; cursor: pointer;
  transition: all var(--transition);
}
.month-btn:hover    { border-color: var(--coral); color: var(--coral); }
.month-btn--active  { background: var(--coral); border-color: var(--coral); color: #fff; }

/* ── Perks ───────────────────────────────────────────────────────────────── */
.perks-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(128px, 1fr)); gap: 10px; }
.perk-item {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 12px; border-radius: var(--radius-sm);
  border: 1.5px solid var(--gray-200);
  cursor: pointer; transition: all var(--transition); user-select: none;
}
.perk-item:hover      { border-color: var(--coral); }
.perk-item--active    { border-color: var(--coral); background: var(--coral-lt); }
.perk-item--active .perk-label { color: var(--coral); }
.perk-check  { display: none; }
.perk-icon   { font-size: 1rem; flex-shrink: 0; }
.perk-label  { font-size: .82rem; font-weight: 600; color: var(--gray-600); }

/* ── Slider ──────────────────────────────────────────────────────────────── */
.adv-slider {
  -webkit-appearance: none;
  width: 100%; height: 4px; border-radius: 2px;
  background: var(--gray-200); outline: none; cursor: pointer;
  accent-color: var(--coral);
}
.adv-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 20px; height: 20px; border-radius: 50%;
  background: var(--white); border: 2.5px solid var(--coral);
  box-shadow: 0 2px 6px rgba(0,0,0,.12); cursor: pointer;
  transition: transform var(--transition);
}
.adv-slider::-webkit-slider-thumb:hover { transform: scale(1.15); }
.slider-ticks {
  display: flex; justify-content: space-between;
  font-size: .7rem; color: var(--gray-400);
}

/* ── Toggle ──────────────────────────────────────────────────────────────── */
.toggle-btn {
  width: 46px; height: 26px; border-radius: 50px; padding: 3px;
  background: var(--gray-200); border: none; cursor: pointer;
  transition: background var(--transition);
  display: flex; align-items: center; flex-shrink: 0;
}
.toggle-btn--on { background: var(--coral); }
.toggle-knob {
  width: 20px; height: 20px; border-radius: 50%;
  background: var(--white); box-shadow: 0 1px 4px rgba(0,0,0,.18);
  transition: transform var(--transition);
}
.toggle-btn--on .toggle-knob { transform: translateX(20px); }

/* ── Footer ──────────────────────────────────────────────────────────────── */
.adv-footer {
  display: flex; justify-content: flex-end; align-items: center; gap: 12px;
  padding-top: 14px; border-top: 1px solid var(--gray-100);
}
.adv-clear {
  background: none; border: none;
  font-family: 'DM Sans', sans-serif; font-size: .86rem; font-weight: 600;
  color: var(--gray-400); cursor: pointer; padding: 8px 12px; border-radius: 50px;
  transition: color var(--transition);
}
.adv-clear:hover { color: var(--indigo); }
.adv-apply { padding: 9px 22px !important; font-size: .88rem !important; }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .adv-row    { grid-template-columns: 1fr; gap: 20px; }
  .month-grid { grid-template-columns: repeat(6, 1fr); }
  .adv-panel  { padding: 18px 16px; }
}
</style>