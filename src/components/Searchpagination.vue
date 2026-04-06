<template>
  <div class="pagination" v-if="totalPages > 1">
    <button class="page-btn" :disabled="modelValue === 1" @click="$emit('update:modelValue', modelValue - 1)">
      ← Prev
    </button>

    <template v-for="p in displayedPages" :key="p">
      <span v-if="p === '...'" class="page-ellipsis">…</span>
      <button
        v-else
        class="page-btn"
        :class="{ active: p === modelValue }"
        @click="$emit('update:modelValue', p)"
      >{{ p }}</button>
    </template>

    <button class="page-btn" :disabled="modelValue === totalPages" @click="$emit('update:modelValue', modelValue + 1)">
      Next →
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Number, required: true },  // current page
  totalPages: { type: Number, required: true },
})
defineEmits(['update:modelValue'])

// Show first, last, current ±1, with ellipsis in between
const displayedPages = computed(() => {
  const { modelValue: cur, totalPages: total } = props
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

  const pages = []
  const add = (p) => { if (!pages.includes(p)) pages.push(p) }

  add(1)
  if (cur > 3)         pages.push('...')
  if (cur > 2)         add(cur - 1)
                       add(cur)
  if (cur < total - 1) add(cur + 1)
  if (cur < total - 2) pages.push('...')
  add(total)

  return pages
})
</script>

<style scoped>
.pagination {
  display: flex; align-items: center; justify-content: center;
  gap: 8px; margin-top: 48px; flex-wrap: wrap;
}

.page-btn {
  min-width: 40px; height: 40px; padding: 0 14px;
  border-radius: 10px; border: 1.5px solid var(--gray-200);
  background: var(--white); font-family: 'DM Sans', sans-serif;
  font-size: .88rem; font-weight: 600; color: var(--indigo);
  cursor: pointer; transition: all var(--transition);
}
.page-btn:hover:not(:disabled) { border-color: var(--coral); color: var(--coral); }
.page-btn.active  { background: var(--coral); border-color: var(--coral); color: #fff; }
.page-btn:disabled{ opacity: .4; cursor: not-allowed; }

.page-ellipsis { color: var(--gray-400); font-size: .9rem; padding: 0 4px; line-height: 40px; }
</style>