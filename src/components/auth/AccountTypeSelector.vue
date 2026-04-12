<template>
  <div class="account-types">
    <button
      type="button"
      class="account-type"
      :class="{ active: modelValue === t.value }"
      v-for="t in types"
      :key="t.value"
      @click="$emit('update:modelValue', t.value)"
    >
      <span class="account-type__icon">{{ t.icon }}</span>
      <span class="account-type__label">{{ t.label }}</span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: String, default: 'tourist' },
})
defineEmits(['update:modelValue'])

const types = [
  { value: 'tourist',  icon: '🧳', label: 'Tourist'  },
  { value: 'agency',   icon: '🏢', label: 'Agency'   },
  { value: 'provider', icon: '🛎️', label: 'Provider' },
]
</script>

<style scoped>
.account-types {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 10px; margin-bottom: 22px;
}

.account-type {
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  padding: 14px 10px; border-radius: 12px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  cursor: pointer; transition: all var(--transition);
}
.account-type:hover {
  border-color: var(--coral);
  background: rgba(255, 90, 95, .05);
}
.account-type.active {
  border-color: var(--coral);
  background: rgba(255, 90, 95, .07);
  box-shadow: 0 0 0 3px rgba(255, 90, 95, .10);
}

.account-type__icon  { font-size: 1.5rem; }
.account-type__label {
  font-family: 'DM Sans', sans-serif;
  font-size: .82rem; font-weight: 600; color: var(--indigo);
}
</style>
