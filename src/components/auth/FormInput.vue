<template>
  <div class="form-group" :class="{ error: !!error }">

    <div class="form-group__header">
      <label class="form-label">{{ label }}</label>
      <slot name="label-action" />
    </div>

    <div class="input-wrap">
      <span class="input-icon" v-if="icon">{{ icon }}</span>

      <input
        class="form-input"
        :class="{ 'form-input--no-icon': !icon }"
        v-bind="$attrs"
        :value="modelValue"
        :type="inputType"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
      />

      <!-- Show/hide toggle for password fields -->
      <button
        v-if="type === 'password'"
        type="button"
        class="input-toggle"
        @click="showPassword = !showPassword"
        :aria-label="showPassword ? 'Hide password' : 'Show password'"
      >
        {{ showPassword ? '🙈' : '👁️' }}
      </button>
    </div>

    <p class="field-error" v-if="error">{{ error }}</p>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: { type: String,  default: '' },
  label:      { type: String,  required: true },
  icon:       { type: String,  default: '' },
  type:       { type: String,  default: 'text' },
  error:      { type: String,  default: '' },
})

defineEmits(['update:modelValue', 'blur'])

// Expose attrs to the <input> not the wrapper div
defineOptions({ inheritAttrs: false })

const showPassword = ref(false)

const inputType = computed(() => {
  if (props.type !== 'password') return props.type
  return showPassword.value ? 'text' : 'password'
})
</script>

<style scoped>
.form-group { margin-bottom: 18px; }

.form-group__header {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;
}

.form-label { font-size: .85rem; font-weight: 600; color: var(--indigo); }

.input-wrap { position: relative; }

.input-icon {
  position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
  font-size: 1rem; pointer-events: none;
}

.form-input {
  width: 100%; padding: 13px 16px 13px 42px;
  border: 1.5px solid var(--gray-200); border-radius: 12px;
  font-family: 'DM Sans', sans-serif; font-size: .95rem; color: var(--indigo);
  outline: none; transition: border-color var(--transition), box-shadow var(--transition);
  background: var(--white);
}
.form-input--no-icon { padding-left: 16px; }
.form-input:focus {
  border-color: var(--coral);
  box-shadow: 0 0 0 3px rgba(255, 90, 95, .10);
}

.form-group.error .form-input { border-color: #e74c3c; }
.form-group.error .form-input:focus { box-shadow: 0 0 0 3px rgba(231, 76, 60, .10); }

.input-toggle {
  position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  font-size: 1rem; padding: 0; line-height: 1;
}

.field-error { font-size: .78rem; color: #e74c3c; margin-top: 5px; }
</style>
