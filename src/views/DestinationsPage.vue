<template>
  <div class="list-page">
    <NavBar />

    <PageHero
      title="Explore Destinations"
      subtitle="Discover breathtaking places handpicked by our travel experts. From tropical islands to alpine retreats."
      tagline="World Destinations"
      icon="📍"
      color="teal"
      search-placeholder="Search destinations…"
      :stats="heroStats"
      v-model="query"
      @search="runSearch"
    />

    <FilterBar
      :categories="categories"
      v-model="activeCategory"
      v-model:sortBy="sortBy"
      v-model:viewMode="viewMode"
      :active-filter-count="activeFilterCount + advancedFilterCount"
      v-model:advancedFilters="advancedFilters"
    />

    <Breadcrumb :crumbs="[
      { label: 'Home',         to: '/'       },
      { label: 'Search',       to: '/search' },
      { label: 'Destinations'               },
    ]" />

    <div class="list-page__body">

      <SidebarFilters
        v-model:filters="filters"
        :open="sidebarOpen"
        :result-count="allFiltered.length"
        @reset="resetFilters"
        @close="sidebarOpen = false"
      />
      <Transition name="backdrop-fade">
        <div class="sidebar-backdrop" v-if="sidebarOpen" @click="sidebarOpen = false" />
      </Transition>

      <main class="list-page__main">

        

        <ItemGrid
          :items="pagedResults"
          :total="allFiltered.length"
          :loading="loading"
          :view-mode="viewMode"
          empty-icon="🗺️"
          empty-title="No destinations found"
          empty-sub="Try adjusting your search or filters."
          @reset="resetFilters"
        >
          <DestinationCard
            v-for="item in pagedResults"
            :key="item.id"
            :item="item"
            :saved="isItemSaved(item)"
            @select="goToDetail"
            @toggle-wishlist="toggleWishlist(item)"
          />
        </ItemGrid>

        <SearchPagination
          v-if="!loading && pagedResults.length"
          v-model="page"
          :total-pages="totalPages"
        />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useListPage } from '@/composables/useListPage'
import { destinations } from '@/data/content.js'

import NavBar           from '@/components/home/NavBar.vue'
import PageHero         from '@/components/shared/PageHero.vue'
import Breadcrumb       from '@/components/shared/Breadcrumb.vue'
import FilterBar        from '@/components/shared/FilterBar.vue'
import ItemGrid         from '@/components/shared/ItemGrid.vue'
import DestinationCard  from '@/components/shared/DestinationCard.vue'
import SidebarFilters   from '@/components/search/SidebarFilters.vue'
import ResultsToolbar   from '@/components/search/ResultsToolbar.vue'
import SearchPagination from '@/components/search/SearchPagination.vue'

const router      = useRouter()
const sidebarOpen = ref(false)
const allItems    = ref([])

onMounted(async () => {
  try {
    const response = await fetch('http://localhost/arrivo-website/backend/api/v1/listings.php')
    const data = await response.json()
    if (data.listings) {
      const dbRows = data.listings.map(dest => ({
        ...dest,
        title: dest.name,
        price: dest.price,
        img:   dest.image,
      }))

      const final = [...destinations]
      dbRows.forEach(dbItem => {
        const exists = final.find(d => d.id === dbItem.id || d.name === dbItem.name)
        if (!exists) {
          final.push(dbItem)
        } else {
          // Merge: demo wins
          const idx = final.findIndex(d => d.id === dbItem.id || d.name === dbItem.name)
          final[idx] = { ...dbItem, ...final[idx] }
        }
      })
      allItems.value = final
    }
  } catch (error) {
    console.error('Failed to load destinations:', error)
  }
})

function goToDetail(item) {
  router.push(`/destinations/${item.id}`)
}

const heroStats = [
  { icon: '📍', value: '1,200+', label: 'destinations' },
  { icon: '🌍', value: '85',     label: 'countries'    },
  { icon: '⭐', value: '4.8',    label: 'avg rating'   },
]

const categories = [
  { value: 'all',       label: 'All',        icon: '🌐' },
  { value: 'Beach',     label: 'Beach',      icon: '🏖️' },
  { value: 'Cultural',  label: 'Cultural',   icon: '🏛️' },
  { value: 'Adventure', label: 'Adventure',  icon: '🧗' },
  { value: 'City',      label: 'City Break', icon: '🌆' },
  { value: 'Nature',    label: 'Nature',     icon: '🌿' },
]

const {
  query, activeCategory, sortBy, viewMode, loading, page,
  filters, advancedFilters,
  activeFilterCount, advancedFilterCount,
  allFiltered, totalPages, pagedResults,
  resetFilters, runSearch, isItemSaved, toggleWishlist,
} = useListPage(allItems, 'destination')
</script>

<style scoped>
.list-page { min-height: 100vh; background: var(--gray-50); }
.list-page__body { display: grid; grid-template-columns: 280px 1fr; align-items: flex-start; }
.list-page__main { padding: 32px; }
.sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 60; }
.backdrop-fade-enter-active, .backdrop-fade-leave-active { transition: opacity .25s ease; }
.backdrop-fade-enter-from, .backdrop-fade-leave-to       { opacity: 0; }
@media (max-width: 768px) {
  .list-page__body  { grid-template-columns: 1fr; }
  .list-page__main  { padding: 20px 4%; }
  .sidebar-backdrop { display: block; }
}
</style>