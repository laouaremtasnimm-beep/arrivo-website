<template>
  <div class="list-page">
    <NavBar />

    <PageHero
      title="Travel Packages"
      subtitle="Curated all-inclusive experiences crafted by our verified agencies. Every detail handled, every moment memorable."
      tagline="Curated Experiences"
      icon="✈️"
      color="coral"
      search-placeholder="Search packages…"
      :stats="heroStats"
      v-model="query"
      @search="runSearch"
    />

    <FilterBar
      :categories="categories"
      v-model="activeCategory"
      v-model:sortBy="sortBy"
      v-model:viewMode="viewMode"
      :active-filter-count="activeFilterCount"
      @open-filters="sidebarOpen = true"
    />

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
          empty-icon="✈️"
          empty-title="No packages found"
          empty-sub="Try different filters or browse all types."
          @reset="resetFilters"
        >
          <PackageCard
            v-for="item in pagedResults"
            :key="item.id"
            :item="item"
            :saved="isItemSaved(item)"
            @select="goToDetail"
            @book="openBooking"
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

    <BookingModal v-model="bookingOpen" :pkg="selectedItem" @submit="handleBooking" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useListPage } from '@/composables/useListPage'
import { packages } from '@/data/content.js'

// ── All imports use the correct paths ─────────────────────────────────────
import NavBar           from '@/components/home/NavBar.vue'
import PageHero         from '@/components/shared/PageHero.vue'
import FilterBar        from '@/components/shared/FilterBar.vue'
import ItemGrid         from '@/components/shared/ItemGrid.vue'
import PackageCard      from '@/components/shared/PackageCard.vue'
import SidebarFilters   from '@/components/search/SidebarFilters.vue'
import SearchPagination from '@/components/search/SearchPagination.vue'
import BookingModal     from '@/components/home/BookingModal.vue'

const router       = useRouter()
const sidebarOpen  = ref(false)
const bookingOpen  = ref(false)
const selectedItem = ref(null)
const allItems     = ref(packages)

// Clicking the card body → go to detail page
function goToDetail(item) {
  router.push(`/packages/${item.id}`)
}

// Clicking "Book now" button → open booking modal (button uses @click.stop so won't also navigate)
function openBooking(item) {
  selectedItem.value = item
  bookingOpen.value  = true
}
function handleBooking(payload) { console.log('Booked:', payload) }

const heroStats = [
  { icon: '✈️', value: '340+', label: 'packages'         },
  { icon: '🏢', value: '120+', label: 'verified agencies' },
  { icon: '⭐', value: '4.9',  label: 'avg rating'        },
]

const categories = [
  { value: 'all',       label: 'All',       icon: '🌐' },
  { value: 'Adventure', label: 'Adventure', icon: '🧗' },
  { value: 'Beach',     label: 'Beach',     icon: '🏖️' },
  { value: 'Cultural',  label: 'Cultural',  icon: '🏛️' },
  { value: 'Family',    label: 'Family',    icon: '👨‍👩‍👧' },
  { value: 'Wellness',  label: 'Wellness',  icon: '🧘' },
]

const {
  query, activeCategory, sortBy, viewMode, loading, page,
  filters, activeFilterCount, allFiltered, totalPages, pagedResults,
  resetFilters, runSearch, isItemSaved, toggleWishlist,
} = useListPage(allItems, 'package')
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