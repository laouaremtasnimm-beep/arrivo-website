<template>
  <button
    class="toggle"
    :class="{ on: modelValue }"
    @click="$emit('update:modelValue', !modelValue)"
    :aria-pressed="modelValue"
    role="switch"
    type="button"
  >
    <span class="toggle-knob" />
  </button>
</template>

<script setup>
defineProps({ modelValue: { type: Boolean, default: false } })
defineEmits(['update:modelValue'])
</script>

<style scoped>
.toggle {
  width: 46px; height: 26px; border-radius: 13px;
  background: var(--gray-200, #E8E8E8);
  border: none; cursor: pointer; position: relative;
  transition: background .25s; flex-shrink: 0; padding: 0;
}
.toggle.on { background: var(--teal, #2EC4B6); }

.toggle-knob {
  position: absolute; top: 3px; left: 3px;
  width: 20px; height: 20px; border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0,0,0,.18);
  transition: transform .25s cubic-bezier(.4,0,.2,1);
}
.toggle.on .toggle-knob { transform: translateX(20px); }
</style>