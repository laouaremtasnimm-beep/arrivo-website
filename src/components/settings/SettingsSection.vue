<template>
  <div class="settings-section">
    <div class="section-header">
      <div>
        <h2 class="section-title">{{ title }}</h2>
        <p v-if="subtitle" class="section-sub">{{ subtitle }}</p>
      </div>
      <slot name="header-action" />
    </div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title:    { type: String, required: true },
  subtitle: { type: String, default: '' },
})
</script>

<style scoped>
.settings-section { padding: 36px 40px; }

.section-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: 28px; gap: 16px;
}

.section-title {
  font-family: 'Fraunces', serif;
  font-size: 22px; color: var(--indigo, #2D3142); margin: 0 0 4px;
}

.section-sub {
  font-size: 14px; color: var(--gray-600, #666); margin: 0;
}

@media (max-width: 600px) {
  .settings-section { padding: 24px 20px; }
}
</style>