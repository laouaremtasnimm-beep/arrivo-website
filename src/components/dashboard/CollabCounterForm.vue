<template>
  <Transition name="counter-expand">
    <div v-if="open" class="counter-form">

      <div class="counter-form__header">
        <div class="counter-form__title">Counter Proposal</div>
        <p class="counter-form__sub">
          Suggest different terms — your partner will receive this as a new incoming request.
        </p>
      </div>

      <div class="counter-form__fields">

        <div class="field-row">
          <div class="field">
            <label class="field__label">New title</label>
            <input
              v-model="form.title"
              class="field__input"
              :placeholder="original.title"
            />
          </div>
          <div class="field field--sm">
            <label class="field__label">Discount (%)</label>
            <input
              v-model.number="form.discount"
              type="number" min="1" max="80"
              class="field__input"
              :placeholder="original.discount"
            />
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label class="field__label">Start date</label>
            <input v-model="form.startDate" type="date" class="field__input" />
          </div>
          <div class="field">
            <label class="field__label">End date</label>
            <input v-model="form.endDate" type="date" class="field__input" />
          </div>
        </div>

        <div class="field">
          <label class="field__label">Updated proposal message</label>
          <textarea
            v-model="form.description"
            class="field__input field__textarea"
            :placeholder="original.description"
            rows="3"
          />
          <p v-if="errors.description" class="field__error">{{ errors.description }}</p>
        </div>

      </div>

      <div class="counter-form__actions">
        <button class="btn-cancel" @click="$emit('cancel')">Cancel</button>
        <button class="btn-send" @click="submit">Send Counter Proposal →</button>
      </div>

    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  open:     { type: Boolean, required: true },
  original: { type: Object,  required: true },
})
const emit = defineEmits(['submit', 'cancel'])

const errors = ref({})

// Pre-fill with original values so the user only edits what they want to change
const form = ref({
  title:       '',
  discount:    '',
  startDate:   '',
  endDate:     '',
  description: '',
})

watch(() => props.open, v => {
  if (v) {
    form.value = {
      title:       props.original.title       || '',
      discount:    props.original.discount    || '',
      startDate:   props.original.startDate   || '',
      endDate:     props.original.endDate     || '',
      description: props.original.description || '',
    }
    errors.value = {}
  }
})

function submit() {
  const e = {}
  if (!form.value.description?.trim()) e.description = 'Please include a message explaining your changes.'
  errors.value = e
  if (Object.keys(e).length) return

  emit('submit', {
    ...props.original,
    // Override only what changed, fall back to original if left blank
    title:       form.value.title       || props.original.title,
    discount:    form.value.discount    || props.original.discount,
    startDate:   form.value.startDate   || props.original.startDate,
    endDate:     form.value.endDate     || props.original.endDate,
    description: form.value.description,
    isCounter:   true,
  })
}
</script>

<style scoped>
.counter-form {
  border-top: 1.5px dashed var(--gray-200);
  margin-top: 12px; padding-top: 16px;
  display: flex; flex-direction: column; gap: 14px;
}

.counter-form__header { display: flex; flex-direction: column; gap: 4px; }
.counter-form__title {
  font-size: .82rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; color: var(--indigo);
}
.counter-form__sub { font-size: .78rem; color: var(--gray-600); margin: 0; line-height: 1.5; }

.counter-form__fields { display: flex; flex-direction: column; gap: 10px; }

.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field--sm { max-width: 120px; }

.field__label {
  font-size: .76rem; font-weight: 600; color: var(--indigo);
}
.field__input {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 8px 11px; font-size: .84rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); outline: none; background: #fff;
  transition: border-color var(--transition);
}
.field__input:focus { border-color: var(--coral); }
.field__textarea    { resize: vertical; min-height: 80px; }
.field__error       { font-size: .74rem; color: var(--coral); margin: 0; }

.counter-form__actions {
  display: flex; gap: 8px; justify-content: flex-end;
}
.btn-cancel {
  background: transparent; border: 1.5px solid var(--gray-200);
  color: var(--gray-600); padding: 7px 16px; border-radius: 50px;
  font-size: .8rem; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: all var(--transition);
}
.btn-cancel:hover { border-color: var(--gray-400); color: var(--indigo); }
.btn-send {
  background: var(--indigo); color: #fff; border: none;
  padding: 7px 18px; border-radius: 50px; font-size: .8rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition);
}
.btn-send:hover { background: #3d4460; }

/* Expand animation */
.counter-expand-enter-active, .counter-expand-leave-active {
  transition: opacity .2s ease, transform .2s ease;
  overflow: hidden;
}
.counter-expand-enter-from, .counter-expand-leave-to {
  opacity: 0; transform: translateY(-8px);
}
</style>