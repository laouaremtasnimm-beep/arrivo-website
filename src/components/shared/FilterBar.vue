<template>
  <div class="filter-bar">
    <div class="filter-bar__inner">

      <!-- Category pills -->
      <button
        class="filter-pill"
        :class="{ active: modelValue === cat.value }"
        v-for="cat in categories"
        :key="cat.value"
        @click="$emit('update:modelValue', cat.value)"
      >
        <span v-if="cat.icon">{{ cat.icon }}</span>
        {{ cat.label }}
        <span class="filter-pill__count" v-if="cat.count != null">{{ cat.count }}</span>
      </button>

    </div>

    <!-- Right: sort + view + filter button -->
    <div class="filter-bar__right">
      <select
        class="filter-sort"
        :value="sortBy"
        @change="$emit('update:sortBy', $event.target.value)"
      >
        <option value="recommended">Recommended</option>
        <option value="price_asc">Price ↑</option>
        <option value="price_desc">Price ↓</option>
        <option value="rating">Top rated</option>
        <option value="popular">Most popular</option>
      </select>

      <div class="view-toggle">
        <button
          class="view-btn"
          :class="{ active: viewMode === 'grid' }"
          @click="$emit('update:viewMode', 'grid')"
          title="Grid"
        >⊞</button>
        <button
          class="view-btn"
          :class="{ active: viewMode === 'list' }"
          @click="$emit('update:viewMode', 'list')"
          title="List"
        >☰</button>
      </div>

      <button
        class="filter-bar__filter-btn btn btn-outline"
        @click="$emit('open-filters')"
      >
        ⚙ Filters
        <span class="filter-badge" v-if="activeFilterCount">{{ activeFilterCount }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  categories:       { type: Array,  default: () => [] },
  modelValue:       { type: String, default: 'all'    }, // active category
  sortBy:           { type: String, default: 'recommended' },
  viewMode:         { type: String, default: 'grid'   },
  activeFilterCount:{ type: Number, default: 0        },
})

defineEmits(['update:modelValue', 'update:sortBy', 'update:viewMode', 'open-filters'])
</script>

<style scoped>
.filter-bar {
  display: flex; align-items: center; justify-content: space-between;
  gap: 16px; padding: 0 5% 0; margin-bottom: 0;
  background: var(--white);
  border-bottom: 1px solid var(--gray-200);
  position: sticky; top: 0; z-index: 30;
  flex-wrap: wrap;
}

.filter-bar__inner {
  display: flex; gap: 6px; overflow-x: auto;
  padding: 14px 0; scrollbar-width: none; flex: 1;
}
.filter-bar__inner::-webkit-scrollbar { display: none; }

.filter-pill {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 16px; border-radius: 50px; white-space: nowrap;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
  flex-shrink: 0;
}
.filter-pill:hover  { border-color: var(--coral); color: var(--coral); }
.filter-pill.active { background: var(--coral); border-color: var(--coral); color: #fff; }

.filter-pill__count {
  background: rgba(255,255,255,.25); border-radius: 50px;
  padding: 1px 7px; font-size: .7rem; font-weight: 700;
}
.filter-pill.active .filter-pill__count { background: rgba(255,255,255,.25); }
.filter-pill:not(.active) .filter-pill__count {
  background: var(--gray-100); color: var(--gray-400);
}

/* Right controls */
.filter-bar__right {
  display: flex; align-items: center; gap: 10px;
  padding: 14px 0; flex-shrink: 0;
}

.filter-sort {
  padding: 8px 12px; border: 1.5px solid var(--gray-200); border-radius: 10px;
  font-family: 'DM Sans', sans-serif; font-size: .84rem; color: var(--indigo);
  background: var(--white); outline: none; cursor: pointer;
  transition: border-color var(--transition);
}
.filter-sort:focus { border-color: var(--coral); }

.view-toggle { display: flex; background: var(--gray-100); border-radius: 10px; padding: 3px; }
.view-btn {
  width: 32px; height: 32px; border-radius: 7px; border: none; background: none;
  font-size: .95rem; cursor: pointer; color: var(--gray-400); transition: all var(--transition);
}
.view-btn.active { background: var(--white); color: var(--indigo); box-shadow: var(--shadow); }

.filter-bar__filter-btn { position: relative; padding: 8px 16px; font-size: .84rem; }
.filter-badge {
  position: absolute; top: -6px; right: -6px;
  width: 17px; height: 17px; border-radius: 50%;
  background: var(--coral); color: #fff;
  font-size: .65rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

@media (max-width: 640px) {
  .filter-bar { padding: 0 4%; }
  .view-toggle { display: none; }
  .filter-bar__right { padding: 10px 0; }
}
</style>