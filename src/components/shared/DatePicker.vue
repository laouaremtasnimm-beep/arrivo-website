<template>
  <div class="dp-wrap" ref="wrapRef">
    <div class="dp-input-row" @click="toggle">
      <input
        class="dp-input"
        :value="displayValue"
        :placeholder="placeholder"
        readonly
      />
      <span class="dp-icon">🗓️</span>
    </div>

    <Transition name="dp-drop">
      <div class="dp-calendar" v-if="open">
        <div class="dp-nav">
          <button class="dp-nav-btn" @click.stop="prevMonth">‹</button>
          <span class="dp-month-label">{{ monthLabel }}</span>
          <button class="dp-nav-btn" @click.stop="nextMonth">›</button>
        </div>

        <div class="dp-grid-head">
          <span v-for="d in days" :key="d">{{ d }}</span>
        </div>

        <div class="dp-grid">
          <button
            v-for="(cell, i) in cells"
            :key="i"
            class="dp-cell"
            :class="{
              'dp-cell--empty':    !cell,
              'dp-cell--selected': cell && isSelected(cell),
              'dp-cell--today':    cell && isToday(cell),
              'dp-cell--disabled': cell && isDisabled(cell),
            }"
            :disabled="!cell || isDisabled(cell)"
            @click.stop="select(cell)"
          >{{ cell?.getDate() }}</button>
        </div>

        <div class="dp-footer" v-if="modelValue">
          <button class="dp-clear" @click.stop="clear">Clear</button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Select date' },
  minDate: { type: String, default: '' },
  maxDate: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue'])

const open    = ref(false)
const wrapRef = ref(null)
const today   = new Date()

const cursor = ref(
  props.modelValue
    ? new Date(props.modelValue + 'T00:00:00')
    : new Date(today.getFullYear(), today.getMonth(), 1)
)

const days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

const monthLabel = computed(() =>
  cursor.value.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' })
)

const displayValue = computed(() => {
  if (!props.modelValue) return ''
  const d = new Date(props.modelValue + 'T00:00:00')
  return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
})

const cells = computed(() => {
  const year  = cursor.value.getFullYear()
  const month = cursor.value.getMonth()
  const first = new Date(year, month, 1).getDay()
  const last  = new Date(year, month + 1, 0).getDate()
  const grid  = []
  for (let i = 0; i < first; i++) grid.push(null)
  for (let d = 1; d <= last; d++) grid.push(new Date(year, month, d))
  while (grid.length % 7 !== 0) grid.push(null)
  return grid
})

function toggle() { open.value = !open.value }

function prevMonth() {
  cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() - 1, 1)
}
function nextMonth() {
  cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() + 1, 1)
}

function toISO(d) {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

function select(d) {
  emit('update:modelValue', toISO(d))
  open.value = false
}

function clear() {
  emit('update:modelValue', '')
  open.value = false
}

function isSelected(d) {
  return props.modelValue === toISO(d)
}

function isToday(d) {
  return toISO(d) === toISO(today)
}

function isDisabled(d) {
  const iso = toISO(d)
  if (props.minDate && iso < props.minDate) return true
  if (props.maxDate && iso > props.maxDate) return true
  return false
}

function onOutsideClick(e) {
  if (wrapRef.value && !wrapRef.value.contains(e.target)) open.value = false
}

onMounted(() => document.addEventListener('mousedown', onOutsideClick))
onUnmounted(() => document.removeEventListener('mousedown', onOutsideClick))
</script>

<style scoped>
.dp-wrap { position: relative; width: 100%; }

.dp-input-row {
  position: relative; cursor: pointer;
}
.dp-input {
  width: 100%; padding: 11px 40px 11px 14px;
  border: 1.5px solid var(--gray-200); border-radius: 11px;
  font-family: 'DM Sans', sans-serif; font-size: .92rem; color: var(--indigo);
  background: var(--white); outline: none; cursor: pointer;
  transition: border-color .2s;
  box-sizing: border-box;
}
.dp-input-row:hover .dp-input,
.dp-input:focus { border-color: var(--coral); }

.dp-icon {
  position: absolute; right: 13px; top: 50%;
  transform: translateY(-50%); font-size: 1rem; pointer-events: none;
}

.dp-calendar {
  position: absolute; top: calc(100% + 6px); left: 0; right: 0;
  background: var(--white); border: 1.5px solid var(--gray-200);
  border-radius: 14px; padding: 14px;
  box-shadow: 0 8px 32px rgba(45,49,66,.12);
  z-index: 500;
}

.dp-nav {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 12px;
}
.dp-month-label {
  font-family: 'Fraunces', serif; font-size: .95rem;
  font-weight: 700; color: var(--indigo);
}
.dp-nav-btn {
  background: var(--gray-100); border: none; border-radius: 8px;
  width: 28px; height: 28px; font-size: 1.1rem; cursor: pointer;
  color: var(--indigo); display: flex; align-items: center; justify-content: center;
  transition: background .15s;
}
.dp-nav-btn:hover { background: var(--gray-200); }

.dp-grid-head {
  display: grid; grid-template-columns: repeat(7, 1fr);
  margin-bottom: 4px;
}
.dp-grid-head span {
  text-align: center; font-size: .7rem; font-weight: 700;
  color: var(--gray-400); text-transform: uppercase; padding: 2px 0;
}

.dp-grid {
  display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;
}
.dp-cell {
  aspect-ratio: 1; border: none; background: none; border-radius: 8px;
  font-family: 'DM Sans', sans-serif; font-size: .82rem; cursor: pointer;
  color: var(--indigo); transition: background .12s, color .12s;
  display: flex; align-items: center; justify-content: center;
}
.dp-cell:hover:not(.dp-cell--empty):not(.dp-cell--disabled) {
  background: var(--gray-100);
}
.dp-cell--empty    { pointer-events: none; }
.dp-cell--today    { font-weight: 700; color: var(--coral); }
.dp-cell--selected { background: var(--indigo) !important; color: #fff !important; border-radius: 8px; }
.dp-cell--disabled { opacity: .3; cursor: not-allowed; }

.dp-footer {
  margin-top: 10px; padding-top: 10px;
  border-top: 1px solid var(--gray-100);
  display: flex; justify-content: flex-end;
}
.dp-clear {
  background: none; border: none; font-size: .78rem;
  color: var(--gray-400); cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: color .15s;
}
.dp-clear:hover { color: var(--coral); }

.dp-drop-enter-active, .dp-drop-leave-active { transition: opacity .15s, transform .15s; }
.dp-drop-enter-from, .dp-drop-leave-to { opacity: 0; transform: translateY(-6px); }
</style>