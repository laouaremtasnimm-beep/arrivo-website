<template>
  <Teleport to="body">
    <Transition name="fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal">

          <button class="modal__close" @click="close">✕</button>

          <h2 class="modal__title">
            {{ isEdit ? 'Edit Offer' : 'Add New Offer' }}
          </h2>

          <div class="modal__body">

            <div class="form-group">
              <label class="form-label">Title *</label>
              <input class="form-input" v-model="form.title" placeholder="e.g. Early Bird Summer" />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <div class="form-group">
              <label class="form-label">Discount (%) *</label>
              <input type="number" min="1" max="80" class="form-input" v-model.number="form.discount" placeholder="e.g. 20" />
              <p class="field-error" v-if="errors.discount">{{ errors.discount }}</p>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date</label>
                <input type="text" class="form-input" v-model="form.startDate" placeholder="e.g. Jul 1" />
              </div>
              <div class="form-group">
                <label class="form-label">End Date</label>
                <input type="text" class="form-input" v-model="form.endDate" placeholder="e.g. Jul 31" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Description *</label>
              <textarea class="form-input form-textarea" v-model="form.description" placeholder="Describe this offer…" />
              <p class="field-error" v-if="errors.description">{{ errors.description }}</p>
            </div>

            <div class="form-group">
              <label class="form-label">Category</label>
              <select class="form-input form-select" v-model="form.type">
                <option value="General">✨ General</option>
                <option value="Adventure">🧗 Adventure</option>
                <option value="Beach">🏖️ Beach</option>
                <option value="Cultural">🏛️ Cultural</option>
                <option value="Family">👨‍👩‍👧 Family</option>
                <option value="Wellness">🧘 Wellness</option>
                <option value="Bundle">📦 Bundle</option>
              </select>
            </div>

          </div>

          <div class="modal__footer">
            <button class="btn btn-outline" @click="close">Cancel</button>
            <button class="btn btn-coral" @click="submit">
              {{ isEdit ? 'Save Changes' : 'Create Offer' }}
            </button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  offer: { type: Object, default: null },
})
const emit = defineEmits(['update:modelValue', 'save'])

const isEdit = computed(() => !!props.offer)

const blankForm = () => ({ title: '', discount: null, startDate: '', endDate: '', description: '', type: 'General' })
const form   = ref(blankForm())
const errors = ref({})

// Reset form whenever the modal opens or the offer prop changes
watch(
  () => [props.modelValue, props.offer],
  ([open, offer]) => {
    if (open) {
      form.value   = offer ? { ...offer } : blankForm()
      errors.value = {}
    }
  }
)

function validate() {
  const e = {}
  if (!form.value.title?.trim())       e.title       = 'Title is required.'
  if (!form.value.discount || form.value.discount < 1) e.discount = 'Enter a valid discount.'
  if (!form.value.description?.trim()) e.description = 'Description is required.'
  errors.value = e
  return !Object.keys(e).length
}

function submit() {
  if (!validate()) return
  emit('save', {
    ...form.value,
    source:  'manual',
    offerID: props.offer?.offerID ?? null,   // null = new offer → DB assigns real id
    active:  true,
  })
  close()
}

function close() { emit('update:modelValue', false) }
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(45,49,66,.45);
  display: flex; align-items: center; justify-content: center;
  z-index: 200; padding: 20px;
}
.modal {
  background: #fff; border-radius: 20px; width: 100%; max-width: 480px;
  box-shadow: 0 16px 64px rgba(45,49,66,.18); position: relative;
  max-height: 90vh; display: flex; flex-direction: column;
}
.modal__close {
  position: absolute; top: 16px; right: 16px;
  background: var(--gray-100); border: none; border-radius: 50%;
  width: 32px; height: 32px; cursor: pointer; font-size: .9rem;
  color: var(--gray-600); transition: background var(--transition);
}
.modal__close:hover { background: var(--gray-200); }
.modal__title {
  font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700;
  color: var(--indigo); padding: 24px 28px 0; margin: 0;
}
.modal__body { padding: 20px 28px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 14px; }
.modal__footer {
  padding: 16px 28px; border-top: 1px solid var(--gray-200);
  display: flex; justify-content: flex-end; gap: 10px;
}

.form-group  { display: flex; flex-direction: column; gap: 5px; }
.form-label  { font-size: .82rem; font-weight: 600; color: var(--indigo); }
.form-row    { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-input  {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 10px 13px; font-size: .9rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); outline: none; background: #fff;
  transition: border-color var(--transition);
}
.form-input:focus { border-color: var(--coral); }
.form-textarea { resize: vertical; min-height: 90px; }
.form-select   { cursor: pointer; }
.field-error { font-size: .78rem; color: var(--coral); margin: 0; }

.btn {
  padding: 10px 22px; border-radius: 50px; font-size: .88rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  border: none; transition: all var(--transition);
}
.btn-coral   { background: var(--coral); color: #fff; }
.btn-coral:hover { background: var(--coral-dk); }
.btn-outline { background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-600); }
.btn-outline:hover { border-color: var(--gray-400); color: var(--indigo); }

.fade-enter-active, .fade-leave-active { transition: all .22s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: scale(.97); }
</style>