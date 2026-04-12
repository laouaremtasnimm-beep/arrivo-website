<template>
  <div class="strength-bar" v-if="password">
    <div class="strength-bar__track">
      <div
        class="strength-bar__fill"
        :style="{ width: width }"
        :class="strengthClass"
      />
    </div>
    <span class="strength-bar__label" :class="strengthClass">{{ label }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  password: { type: String, default: '' },
})

const score = computed(() => {
  const p = props.password
  if (!p) return 0
  let s = 0
  if (p.length >= 8)          s++
  if (/[A-Z]/.test(p))        s++
  if (/[0-9]/.test(p))        s++
  if (/[^A-Za-z0-9]/.test(p)) s++
  return s
})

const width         = computed(() => ['0%', '25%', '50%', '75%', '100%'][score.value])
const label         = computed(() => ['', 'Weak', 'Fair', 'Good', 'Strong'][score.value])
const strengthClass = computed(() => ['', 'weak', 'fair', 'good', 'strong'][score.value])
</script>

<style scoped>
.strength-bar {
  display: flex; align-items: center; gap: 10px; margin-top: 8px;
}

.strength-bar__track {
  flex: 1; height: 4px; background: var(--gray-200);
  border-radius: 2px; overflow: hidden;
}
.strength-bar__fill {
  height: 100%; border-radius: 2px;
  transition: width .4s ease, background .4s ease;
}
.strength-bar__fill.weak   { background: #e74c3c; }
.strength-bar__fill.fair   { background: #f39c12; }
.strength-bar__fill.good   { background: var(--teal); }
.strength-bar__fill.strong { background: #27ae60; }

.strength-bar__label {
  font-size: .78rem; font-weight: 600; min-width: 42px; text-align: right;
}
.strength-bar__label.weak   { color: #e74c3c; }
.strength-bar__label.fair   { color: #f39c12; }
.strength-bar__label.good   { color: var(--teal); }
.strength-bar__label.strong { color: #27ae60; }
</style>
