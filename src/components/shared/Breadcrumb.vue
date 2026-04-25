<template>
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <ol class="breadcrumb__list">
      <li
        v-for="(crumb, i) in crumbs"
        :key="crumb.to ?? crumb.label"
        class="breadcrumb__item"
      >
        <!-- separator (not on first item) -->
        <span v-if="i > 0" class="breadcrumb__sep" aria-hidden="true">
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
            <path d="M4 2l4 4-4 4" stroke="currentColor" stroke-width="1.6"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>

        <!-- active (last) crumb — plain text -->
        <span v-if="i === crumbs.length - 1" class="breadcrumb__current" aria-current="page">
          {{ crumb.label }}
        </span>

        <!-- link crumb -->
        <RouterLink v-else :to="crumb.to" class="breadcrumb__link">
          {{ crumb.label }}
        </RouterLink>
      </li>
    </ol>
  </nav>
</template>

<script setup>
defineProps({
  /**
   * Array of { label: string, to?: string }
   * Last item is always rendered as plain text (current page).
   * Example: [{ label: 'Home', to: '/' }, { label: 'Search', to: '/search' }, { label: 'Destinations' }]
   */
  crumbs: {
    type: Array,
    required: true,
  },
})
</script>

<style scoped>
.breadcrumb {
  padding: 12px 5%;
  background: var(--white);
  border-bottom: 1px solid var(--gray-100);
}

.breadcrumb__list {
  display: flex;
  align-items: center;
  gap: 4px;
  list-style: none;
  margin: 0;
  padding: 0;
  flex-wrap: wrap;
}

.breadcrumb__item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.breadcrumb__sep {
  color: var(--gray-300);
  display: flex;
  align-items: center;
}

.breadcrumb__link {
  font-size: .82rem;
  font-weight: 500;
  color: var(--gray-500);
  text-decoration: none;
  padding: 2px 4px;
  border-radius: 4px;
  transition: color var(--transition), background var(--transition);
}
.breadcrumb__link:hover {
  color: var(--coral);
  background: var(--coral-lt);
}

.breadcrumb__current {
  font-size: .82rem;
  font-weight: 600;
  color: var(--indigo);
  padding: 2px 4px;
}

@media (max-width: 640px) {
  .breadcrumb { padding: 10px 4%; }
}
</style>