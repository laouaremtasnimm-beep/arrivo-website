<template>
  <div class="search-header">
    <div class="search-header__inner">

      <RouterLink to="/" class="logo">Voya<span>go</span></RouterLink>

      <div class="search-bar">
        <span class="search-bar__icon">🔍</span>
        <input
          class="search-bar__input"
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          @keyup.enter="$emit('search')"
          placeholder="Destinations, packages, services…"
        />
        <div class="search-bar__divider" />
        <div class="search-bar__filter">📅 <span>Any dates</span></div>
        <div class="search-bar__divider" />
        <div class="search-bar__filter">👤 <span>Guests</span></div>
        <button class="btn btn-coral search-bar__btn" @click="$emit('search')">Search</button>
      </div>

      <div class="search-categories">
        <button
          class="category-pill"
          :class="{ active: activeCategory === c.key }"
          v-for="c in categories"
          :key="c.key"
          @click="$emit('update:activeCategory', c.key)"
        >
          <span>{{ c.icon }}</span> {{ c.label }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue:     { type: String, default: '' },
  activeCategory: { type: String, default: 'all' },
})
defineEmits(['update:modelValue', 'update:activeCategory', 'search'])

const categories = [
  { key: 'all',     icon: '🌐', label: 'All'          },
  { key: 'package', icon: '✈️', label: 'Packages'     },
  { key: 'service', icon: '🛎️', label: 'Services'     },
  { key: 'dest',    icon: '📍', label: 'Destinations'  },
]
</script>

<style scoped>
.search-header {
  background: var(--white);
  border-bottom: 1px solid var(--gray-200);
  position: sticky; top: 0; z-index: 50;
  box-shadow: var(--shadow);
}
.search-header__inner {
  padding: 16px 5% 0;
  display: flex; flex-direction: column; gap: 16px;
}

.logo {
  font-family: 'Fraunces', serif;
  font-size: 1.5rem; font-weight: 700;
  color: var(--indigo); letter-spacing: -.5px;
  text-decoration: none; width: fit-content;
}
.logo span { color: var(--coral); }

.search-bar {
  display: flex; align-items: center;
  background: var(--white); border: 1.5px solid var(--gray-200);
  border-radius: 18px; padding: 7px 7px 7px 20px;
  box-shadow: var(--shadow); max-width: 860px;
}
.search-bar__icon  { font-size: 1.1rem; margin-right: 10px; flex-shrink: 0; }
.search-bar__input {
  flex: 1; border: none; outline: none;
  font-family: 'DM Sans', sans-serif; font-size: .97rem;
  color: var(--indigo); background: transparent; min-width: 0;
}
.search-bar__input::placeholder { color: var(--gray-400); }
.search-bar__divider { width: 1px; height: 30px; background: var(--gray-200); margin: 0 14px; flex-shrink: 0; }
.search-bar__filter {
  display: flex; align-items: center; gap: 6px;
  font-size: .88rem; color: var(--gray-600); font-weight: 500;
  padding: 6px 12px; border-radius: 8px; cursor: pointer;
  transition: background var(--transition); white-space: nowrap; flex-shrink: 0;
}
.search-bar__filter:hover { background: var(--gray-100); }
.search-bar__btn { padding: 11px 24px; font-size: .92rem; border-radius: 12px; }

.search-categories {
  display: flex; gap: 6px; overflow-x: auto; padding-bottom: 14px;
  scrollbar-width: none;
}
.search-categories::-webkit-scrollbar { display: none; }

.category-pill {
  display: flex; align-items: center; gap: 7px;
  padding: 8px 18px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .86rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; white-space: nowrap;
  transition: all var(--transition);
}
.category-pill:hover  { border-color: var(--coral); color: var(--coral); }
.category-pill.active { background: var(--coral); border-color: var(--coral); color: #fff; }

@media (max-width: 768px) {
  .search-bar__divider,
  .search-bar__filter { display: none; }
  .search-bar { border-radius: 14px; }
  .search-header__inner { padding: 14px 4% 0; }
}
</style>