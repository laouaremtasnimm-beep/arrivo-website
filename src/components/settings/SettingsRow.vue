<template>
  <div class="settings-row" :class="{ 'settings-row--danger': danger }">
    <div class="row-info">
      <span class="row-label">{{ label }}</span>
      <span v-if="desc" class="row-desc">{{ desc }}</span>
    </div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  label:  { type: String, required: true },
  desc:   { type: String, default: '' },
  danger: { type: Boolean, default: false },
})
</script>

<style scoped>
.settings-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 0; border-bottom: 1px solid var(--gray-100, #F2F2F2);
  gap: 24px;
}
.settings-row:last-child { border-bottom: none; }
.settings-row--danger .row-label { color: var(--coral, #FF5A5F); }

.row-info  { display: flex; flex-direction: column; gap: 3px; flex: 1; }
.row-label { font-size: 14px; font-weight: 600; color: var(--indigo, #2D3142); }
.row-desc  { font-size: 13px; color: var(--gray-600, #666); line-height: 1.5; }
</style>