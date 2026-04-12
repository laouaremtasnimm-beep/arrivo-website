<template>
  <div class="auth-tabs">
    <button
      class="auth-tab"
      :class="{ active: modelValue === 'login' }"
      @click="$emit('update:modelValue', 'login')"
    >
      Sign in
    </button>
    <button
      class="auth-tab"
      :class="{ active: modelValue === 'register' }"
      @click="$emit('update:modelValue', 'register')"
    >
      Create account
    </button>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: String, required: true }, // 'login' | 'register'
})
defineEmits(['update:modelValue'])
</script>

<style scoped>
.auth-tabs {
  display: flex;
  background: var(--gray-100);
  border-radius: 14px;
  padding: 5px;
  margin-bottom: 36px;
}

.auth-tab {
  flex: 1; padding: 11px; text-align: center;
  font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: .92rem;
  border-radius: 11px; border: none; background: none;
  cursor: pointer; color: var(--gray-400);
  transition: all var(--transition);
}
.auth-tab.active {
  background: var(--white);
  color: var(--indigo);
  box-shadow: var(--shadow);
}
</style>
