<template>
  <div class="results-toolbar">

    <div class="results-count">
      <template v-if="!loading">
        <strong>{{ count }}</strong>
        result{{ count !== 1 ? 's' : '' }}
        <span class="results-query" v-if="query"> for "{{ query }}"</span>
      </template>
      <span v-else class="results-loading">Searching…</span>
    </div>

    <div class="toolbar-right">

      <select
        class="sort-select"
        :value="sortBy"
        @change="$emit('update:sortBy', $event.target.value)"
      >
        <option value="recommended">Recommended</option>
        <option value="price_asc">Price: Low → High</option>
        <option value="price_desc">Price: High → Low</option>
        <option value="rating">Top rated</option>
      </select>

      <div class="view-toggle">
        <button
          class="view-btn"
          :class="{ active: viewMode === 'grid' }"
          @click="$emit('update:viewMode', 'grid')"
          title="Grid view"
        >⊞</button>
        <button
          class="view-btn"
          :class="{ active: viewMode === 'list' }"
          @click="$emit('update:viewMode', 'list')"
          title="List view"
        >☰</button>
      </div>

      <button class="btn btn-outline filter-toggle-btn" @click="$emit('open-filters')">
        ⚙ Filters
        <span class="filter-badge" v-if="activeFilterCount">{{ activeFilterCount }}</span>
      </button>

    </div>
  </div>
</template>

<script setup>
defineProps({
  count:            { type: Number,  default: 0 },
  query:            { type: String,  default: '' },
  loading:          { type: Boolean, default: false },
  sortBy:           { type: String,  default: 'recommended' },
  viewMode:         { type: String,  default: 'grid' },
  activeFilterCount:{ type: Number,  default: 0 },
})
defineEmits(['update:sortBy', 'update:viewMode', 'open-filters'])
</script>

<style scoped>
.results-toolbar {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
}

.results-count       { font-size: .95rem; color: var(--gray-600); }
.results-count strong{ font-weight: 700; color: var(--indigo); }
.results-query       { color: var(--coral); }
.results-loading     { color: var(--gray-400); }

.toolbar-right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

.sort-select {
  padding: 9px 14px; border: 1.5px solid var(--gray-200); border-radius: 10px;
  font-family: 'DM Sans', sans-serif; font-size: .88rem; color: var(--indigo);
  background: var(--white); outline: none; cursor: pointer;
  transition: border-color var(--transition);
}
.sort-select:focus { border-color: var(--coral); }

.view-toggle { display: flex; background: var(--gray-100); border-radius: 10px; padding: 3px; }
.view-btn {
  width: 34px; height: 34px; border-radius: 8px; border: none; background: none;
  font-size: 1rem; cursor: pointer; color: var(--gray-400); transition: all var(--transition);
}
.view-btn.active { background: var(--white); color: var(--indigo); box-shadow: var(--shadow); }

.filter-toggle-btn { display: none; position: relative; }
.filter-badge {
  position: absolute; top: -6px; right: -6px;
  width: 18px; height: 18px; border-radius: 50%;
  background: var(--coral); color: #fff;
  font-size: .7rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

@media (max-width: 768px) {
  .filter-toggle-btn { display: flex; }
  .view-toggle       { display: none; }
}
</style>