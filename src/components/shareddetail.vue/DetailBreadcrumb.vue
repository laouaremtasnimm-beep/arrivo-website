<template>
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <RouterLink to="/" class="breadcrumb__item">Home</RouterLink>
    <span class="breadcrumb__sep">›</span>
    <RouterLink :to="parentPath" class="breadcrumb__item">{{ parentLabel }}</RouterLink>
    <span class="breadcrumb__sep">›</span>
    <span class="breadcrumb__item breadcrumb__item--current">{{ current }}</span>
  </nav>
</template>

<script setup>
defineProps({
  parentLabel: { type: String, required: true },
  parentPath:  { type: String, required: true },
  current:     { type: String, required: true },
})
</script>

<style scoped>
.breadcrumb {
  display: flex; align-items: center; gap: 8px;
  font-size: .82rem; color: var(--gray-400);
  padding: 20px 5% 0;
  flex-wrap: wrap;
}
.breadcrumb__item {
  text-decoration: none; color: var(--gray-400);
  transition: color var(--transition);
}
.breadcrumb__item:hover { color: var(--coral); }
.breadcrumb__item--current {
  color: var(--indigo); font-weight: 600;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px;
}
.breadcrumb__sep { color: var(--gray-200); }
</style>