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

            <!-- Title -->
            <div class="form-group">
              <label class="form-label">Title *</label>
              <input class="form-input" v-model="form.title" />
              <p class="field-error" v-if="errors.title">{{ errors.title }}</p>
            </div>

            <!-- Discount -->
            <div class="form-group">
              <label class="form-label">Discount (%) *</label>
              <input type="number" class="form-input" v-model.number="form.discount" />
              <p class="field-error" v-if="errors.discount">{{ errors.discount }}</p>
            </div>

            <!-- Dates -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date *</label>
                <input type="text" class="form-input" v-model="form.startDate" />
              </div>
              <div class="form-group">
                <label class="form-label">End Date *</label>
                <input type="text" class="form-input" v-model="form.endDate" />
              </div>
            </div>

            <!-- Description -->
            <div class="form-group">
              <label class="form-label">Description *</label>
              <textarea class="form-input" v-model="form.description" />
              <p class="field-error" v-if="errors.description">{{ errors.description }}</p>
            </div>

          </div>

          <div class="modal__footer">
            <button class="btn btn-outline" @click="close">Cancel</button>
            <button class="btn btn-coral" @click="submit">Save</button>
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
  offer: Object
})

const emit = defineEmits(['update:modelValue', 'save'])

const isEdit = computed(() => !!props.offer)

const form = ref({
  title: '',
  discount: null,
  startDate: '',
  endDate: '',
  description: ''
})

const errors = ref({})

watch(() => props.offer, (o) => {
  if (o) form.value = { ...o }
  else form.value = { title:'', discount:null, startDate:'', endDate:'', description:'' }
}, { immediate: true })

function validate() {
  const e = {}
  if (!form.value.title) e.title = 'Required'
  if (!form.value.discount) e.discount = 'Required'
  if (!form.value.description) e.description = 'Required'
  errors.value = e
  return Object.keys(e).length === 0
}

function submit() {
  if (!validate()) return

  emit('save', {
    ...form.value,
    offerID: props.offer?.offerID ?? Date.now()
  })

  close()
}

function close() {
  emit('update:modelValue', false)
}
</script>