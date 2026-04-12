<template>
  <div class="item-grid-wrap">

    <!-- Loading skeletons -->
    <div v-if="loading" :class="viewMode === 'grid' ? 'item-grid' : 'item-list'">
      <div class="skeleton-card" v-for="i in skeletonCount" :key="i">
        <div class="skeleton-img" />
        <div class="skeleton-body">
          <div class="skeleton-line skeleton-line--short" />
          <div class="skeleton-line" />
          <div class="skeleton-line skeleton-line--med" />
          <div class="skeleton-line skeleton-line--short" />
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div class="empty-state" v-else-if="!items.length">
      <div class="empty-state__icon">{{ emptyIcon }}</div>
      <h3 class="empty-state__title">{{ emptyTitle }}</h3>
      <p class="empty-state__sub">{{ emptySub }}</p>
      <button class="btn btn-coral" @click="$emit('reset')">Clear filters</button>
    </div>

    <!-- Items -->
    <div
      v-else
      :class="viewMode === 'grid' ? 'item-grid' : 'item-list'"
    >
      <slot />
    </div>

    <!-- Result count -->
    <div class="item-grid-wrap__count" v-if="!loading && items.length">
      Showing <strong>{{ items.length }}</strong> of <strong>{{ total }}</strong> results
    </div>

  </div>
</template>

<script setup>
defineProps({
  items:         { type: Array,   default: () => [] },
  total:         { type: Number,  default: 0        },
  loading:       { type: Boolean, default: false    },
  viewMode:      { type: String,  default: 'grid'   },
  skeletonCount: { type: Number,  default: 8        },
  emptyIcon:     { type: String,  default: '🔍'     },
  emptyTitle:    { type: String,  default: 'No results found' },
  emptySub:      { type: String,  default: 'Try adjusting your filters.' },
})

defineEmits(['reset'])
</script>

<style scoped>
.item-grid-wrap { }

.item-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.item-list {
  display: flex; flex-direction: column; gap: 18px;
}

/* Skeletons */
.skeleton-card {
  background: var(--white); border-radius: var(--radius);
  overflow: hidden; box-shadow: var(--shadow);
}
.skeleton-img {
  height: 210px;
  background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
  background-size: 200% 100%; animation: shimmer 1.4s infinite;
}
.skeleton-body { padding: 20px; display: flex; flex-direction: column; gap: 10px; }
.skeleton-line {
  height: 13px; border-radius: 6px;
  background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
  background-size: 200% 100%; animation: shimmer 1.4s infinite;
}
.skeleton-line--short { width: 38%; }
.skeleton-line--med   { width: 62%; }
@keyframes shimmer { to { background-position: -200% 0; } }

/* Empty */
.empty-state        { text-align: center; padding: 80px 20px; }
.empty-state__icon  { font-size: 3.5rem; margin-bottom: 16px; }
.empty-state__title { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; margin-bottom: 10px; }
.empty-state__sub   { font-size: .95rem; color: var(--gray-400); margin-bottom: 28px; }

/* Count */
.item-grid-wrap__count {
  margin-top: 28px; text-align: center;
  font-size: .84rem; color: var(--gray-400);
}
.item-grid-wrap__count strong { color: var(--indigo); font-weight: 700; }

@media (max-width: 640px) {
  .item-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
}
</style>