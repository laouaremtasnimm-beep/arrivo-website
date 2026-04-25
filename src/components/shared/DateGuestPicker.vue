<template>
  <!-- Teleport to body so it layers above sticky headers -->
  <Teleport to="body">
    <Transition name="picker-fade">
      <div v-if="modelValue !== null" class="picker-overlay" @mousedown.self="close">

        <div
          class="picker-popup"
          :style="popupStyle"
          @mousedown.stop
        >

          <!-- Tab bar -->
          <div class="picker-tabs">
            <button
              class="picker-tab" :class="{ active: tab === 'dates' }"
              @click="tab = 'dates'"
            >📅 Dates</button>
            <button
              class="picker-tab" :class="{ active: tab === 'guests' }"
              @click="tab = 'guests'"
            >👤 Guests</button>
          </div>

          <!-- ── DATES TAB ──────────────────────────────────────── -->
          <div v-if="tab === 'dates'" class="picker-body">

            <div class="calendar-nav">
              <button class="cal-nav-btn" @click="prevMonth">‹</button>
              <span class="cal-month-label">{{ monthLabel }}</span>
              <button class="cal-nav-btn" @click="nextMonth">›</button>
            </div>

            <div class="calendar-grid">
              <div class="cal-dow" v-for="d in DAYS" :key="d">{{ d }}</div>
              <!-- leading blanks -->
              <div v-for="_ in leadingBlanks" :key="'b' + _" class="cal-blank" />
              <!-- days -->
              <button
                v-for="day in daysInMonth" :key="day"
                class="cal-day"
                :class="{
                  'cal-day--today':    isToday(day),
                  'cal-day--start':    isStart(day),
                  'cal-day--end':      isEnd(day),
                  'cal-day--in-range': isInRange(day),
                  'cal-day--past':     isPast(day),
                }"
                :disabled="isPast(day)"
                @click="selectDay(day)"
              >{{ day }}</button>
            </div>

            <div class="date-summary">
              <span v-if="!rangeStart">Select check-in date</span>
              <span v-else-if="!rangeEnd">
                <b>{{ formatDate(rangeStart) }}</b> → select check-out
              </span>
              <span v-else>
                <b>{{ formatDate(rangeStart) }}</b> → <b>{{ formatDate(rangeEnd) }}</b>
                <em class="nights-badge">{{ nightCount }} night{{ nightCount !== 1 ? 's' : '' }}</em>
              </span>
            </div>

            <div class="picker-actions">
              <button class="picker-clear" @click="clearDates">Clear</button>
              <button class="btn btn-coral picker-done" :disabled="!rangeStart" @click="applyDates">
                {{ rangeEnd ? 'Apply' : 'Skip checkout' }}
              </button>
            </div>
          </div>

          <!-- ── GUESTS TAB ─────────────────────────────────────── -->
          <div v-if="tab === 'guests'" class="picker-body">

            <div class="guest-row" v-for="g in guestTypes" :key="g.key">
              <div class="guest-info">
                <div class="guest-name">{{ g.label }}</div>
                <div class="guest-sub">{{ g.sub }}</div>
              </div>
              <div class="guest-counter">
                <button
                  class="counter-btn"
                  :disabled="localGuests[g.key] <= (g.key === 'adults' ? 1 : 0)"
                  @click="dec(g.key)"
                >−</button>
                <span class="counter-val">{{ localGuests[g.key] }}</span>
                <button
                  class="counter-btn"
                  :disabled="totalGuests >= 20"
                  @click="inc(g.key)"
                >+</button>
              </div>
            </div>

            <div class="picker-actions">
              <button class="picker-clear" @click="clearGuests">Clear</button>
              <button class="btn btn-coral picker-done" @click="applyGuests">Apply</button>
            </div>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

// modelValue = 'dates' | 'guests' | null  (which picker is open)
const props = defineProps({
  modelValue:    { type: String, default: null },   // null = closed
  anchorRect:    { type: Object, default: null },   // getBoundingClientRect() of trigger
  initialDates:  { type: Object, default: () => ({ start: null, end: null }) },
  initialGuests: { type: Object, default: () => ({ adults: 1, children: 0, infants: 0 }) },
})
const emit = defineEmits(['update:modelValue', 'apply-dates', 'apply-guests'])

// ── Tab ────────────────────────────────────────────────────────────────────
const tab = ref(props.modelValue || 'dates')
watch(() => props.modelValue, v => { if (v) tab.value = v })

function close() { emit('update:modelValue', null) }

// ── Popup position ─────────────────────────────────────────────────────────
const popupStyle = computed(() => {
  if (!props.anchorRect) return { top: '80px', left: '50%', transform: 'translateX(-50%)' }
  const r = props.anchorRect
  const left = Math.min(r.left, window.innerWidth - 420)
  return {
    top:  `${r.bottom + 8}px`,
    left: `${Math.max(8, left)}px`,
  }
})

// ── Calendar state ─────────────────────────────────────────────────────────
const today = new Date(); today.setHours(0,0,0,0)
const viewYear  = ref(today.getFullYear())
const viewMonth = ref(today.getMonth())   // 0-indexed

const rangeStart = ref(props.initialDates.start ? new Date(props.initialDates.start) : null)
const rangeEnd   = ref(props.initialDates.end   ? new Date(props.initialDates.end)   : null)

const DAYS = ['Su','Mo','Tu','We','Th','Fr','Sa']

const monthLabel = computed(() =>
  new Date(viewYear.value, viewMonth.value, 1)
    .toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
)

const daysInMonth = computed(() =>
  new Date(viewYear.value, viewMonth.value + 1, 0).getDate()
)

const leadingBlanks = computed(() =>
  new Date(viewYear.value, viewMonth.value, 1).getDay()
)

function prevMonth() {
  if (viewMonth.value === 0) { viewMonth.value = 11; viewYear.value-- }
  else viewMonth.value--
}
function nextMonth() {
  if (viewMonth.value === 11) { viewMonth.value = 0; viewYear.value++ }
  else viewMonth.value++
}

function dayDate(d) { return new Date(viewYear.value, viewMonth.value, d) }

function isPast(d)     { return dayDate(d) < today }
function isToday(d)    { return dayDate(d).getTime() === today.getTime() }
function isStart(d)    { return rangeStart.value && dayDate(d).getTime() === rangeStart.value.getTime() }
function isEnd(d)      { return rangeEnd.value   && dayDate(d).getTime() === rangeEnd.value.getTime() }
function isInRange(d) {
  if (!rangeStart.value || !rangeEnd.value) return false
  const t = dayDate(d).getTime()
  return t > rangeStart.value.getTime() && t < rangeEnd.value.getTime()
}

function selectDay(d) {
  const date = dayDate(d)
  if (!rangeStart.value || (rangeStart.value && rangeEnd.value)) {
    rangeStart.value = date; rangeEnd.value = null
  } else if (date <= rangeStart.value) {
    rangeStart.value = date; rangeEnd.value = null
  } else {
    rangeEnd.value = date
  }
}

function formatDate(d) {
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const nightCount = computed(() => {
  if (!rangeStart.value || !rangeEnd.value) return 0
  return Math.round((rangeEnd.value - rangeStart.value) / 86400000)
})

function clearDates()  { rangeStart.value = null; rangeEnd.value = null }

function applyDates() {
  emit('apply-dates', {
    start: rangeStart.value,
    end:   rangeEnd.value,
    label: rangeEnd.value
      ? `${formatDate(rangeStart.value)} – ${formatDate(rangeEnd.value)}`
      : formatDate(rangeStart.value),
  })
  close()
}

// ── Guests state ───────────────────────────────────────────────────────────
const localGuests = ref({ ...props.initialGuests })

const guestTypes = [
  { key: 'adults',   label: 'Adults',   sub: 'Age 13+' },
  { key: 'children', label: 'Children', sub: 'Ages 2–12' },
  { key: 'infants',  label: 'Infants',  sub: 'Under 2' },
]

const totalGuests = computed(() =>
  localGuests.value.adults + localGuests.value.children + localGuests.value.infants
)

function inc(k) { if (totalGuests.value < 20) localGuests.value[k]++ }
function dec(k) {
  const min = k === 'adults' ? 1 : 0
  if (localGuests.value[k] > min) localGuests.value[k]--
}

function clearGuests()  { localGuests.value = { adults: 1, children: 0, infants: 0 } }

function applyGuests() {
  const g = localGuests.value
  const total = g.adults + g.children
  const label = total === 1 && g.adults === 1 ? '1 guest'
    : `${total} guest${total !== 1 ? 's' : ''}${g.infants ? ` + ${g.infants} infant${g.infants > 1 ? 's' : ''}` : ''}`
  emit('apply-guests', { ...g, total, label })
  close()
}
</script>

<style scoped>
/* ── Overlay ─────────────────────────────────────────────────────────────── */
.picker-overlay {
  position: fixed; inset: 0; z-index: 200;
}

.picker-popup {
  position: fixed;
  width: 400px;
  background: var(--white);
  border-radius: var(--radius);
  border: 1.5px solid var(--gray-200);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
}

.picker-fade-enter-active,
.picker-fade-leave-active { transition: opacity .18s ease, transform .18s cubic-bezier(.4,0,.2,1); }
.picker-fade-enter-from,
.picker-fade-leave-to     { opacity: 0; transform: translateY(-6px) scale(.98); }

/* ── Tabs ────────────────────────────────────────────────────────────────── */
.picker-tabs {
  display: flex;
  border-bottom: 1px solid var(--gray-200);
}
.picker-tab {
  flex: 1; padding: 14px;
  background: none; border: none; cursor: pointer;
  font-family: 'DM Sans', sans-serif; font-size: .88rem; font-weight: 600;
  color: var(--gray-400); transition: all var(--transition);
  border-bottom: 2px solid transparent; margin-bottom: -1px;
}
.picker-tab:hover  { color: var(--indigo); }
.picker-tab.active { color: var(--coral); border-bottom-color: var(--coral); }

/* ── Body ────────────────────────────────────────────────────────────────── */
.picker-body { padding: 20px; display: flex; flex-direction: column; gap: 16px; }

/* ── Calendar ────────────────────────────────────────────────────────────── */
.calendar-nav {
  display: flex; align-items: center; justify-content: space-between;
}
.cal-month-label {
  font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 600;
  color: var(--indigo);
}
.cal-nav-btn {
  width: 32px; height: 32px; border-radius: 50%;
  border: 1.5px solid var(--gray-200); background: none;
  font-size: 1.1rem; cursor: pointer; color: var(--gray-600);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.cal-nav-btn:hover { border-color: var(--coral); color: var(--coral); }

.calendar-grid {
  display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px;
}
.cal-dow {
  text-align: center; font-size: .72rem; font-weight: 700;
  color: var(--gray-400); padding: 4px 0; text-transform: uppercase;
}
.cal-blank { /* empty */ }
.cal-day {
  aspect-ratio: 1; border-radius: 8px;
  border: none; background: none; cursor: pointer;
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 500;
  color: var(--indigo); transition: all var(--transition);
  display: flex; align-items: center; justify-content: center;
}
.cal-day:hover:not(:disabled) { background: var(--gray-100); }
.cal-day:disabled { color: var(--gray-200); cursor: not-allowed; }
.cal-day--today { font-weight: 700; color: var(--coral); }
.cal-day--start,
.cal-day--end   { background: var(--coral) !important; color: #fff !important; font-weight: 700; }
.cal-day--in-range { background: var(--coral-lt); color: var(--coral); }

.date-summary {
  text-align: center; font-size: .86rem; color: var(--gray-600);
  background: var(--gray-50); padding: 10px 14px; border-radius: var(--radius-sm);
}
.date-summary b { color: var(--indigo); }
.nights-badge {
  margin-left: 8px; background: var(--coral-lt); color: var(--coral);
  font-size: .78rem; font-style: normal; font-weight: 700;
  padding: 2px 8px; border-radius: 50px;
}

/* ── Guests ──────────────────────────────────────────────────────────────── */
.guest-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 0; border-bottom: 1px solid var(--gray-100);
}
.guest-row:last-of-type { border-bottom: none; }
.guest-name { font-weight: 600; font-size: .92rem; color: var(--indigo); }
.guest-sub  { font-size: .78rem; color: var(--gray-400); margin-top: 2px; }

.guest-counter { display: flex; align-items: center; gap: 14px; }
.counter-btn {
  width: 32px; height: 32px; border-radius: 50%;
  border: 1.5px solid var(--gray-200); background: none;
  font-size: 1.1rem; cursor: pointer; color: var(--gray-600);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.counter-btn:hover:not(:disabled) { border-color: var(--coral); color: var(--coral); }
.counter-btn:disabled { opacity: .35; cursor: not-allowed; }
.counter-val { font-size: 1rem; font-weight: 700; color: var(--indigo); min-width: 20px; text-align: center; }

/* ── Actions ─────────────────────────────────────────────────────────────── */
.picker-actions { display: flex; justify-content: space-between; align-items: center; padding-top: 4px; }
.picker-clear {
  background: none; border: none; cursor: pointer;
  font-family: 'DM Sans', sans-serif; font-size: .86rem; font-weight: 600;
  color: var(--gray-400); padding: 8px 4px;
  transition: color var(--transition);
}
.picker-clear:hover { color: var(--indigo); }
.picker-done { padding: 10px 24px; font-size: .88rem; }
.picker-done:disabled { opacity: .5; cursor: not-allowed; }

@media (max-width: 480px) {
  .picker-popup { width: calc(100vw - 16px); left: 8px !important; }
}
</style>