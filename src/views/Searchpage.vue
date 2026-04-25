<template>
  <div class="search-page">

    <NavBar />

    <PageHero
      title="Search Everything"
      subtitle="Find destinations, packages and services all in one place. Use the filters to narrow down your perfect trip."
      tagline="Explore Voyago"
      icon="🔍"
      color="teal"
      search-placeholder="Destinations, packages, services…"
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
      v-model:advancedFilters="advancedFilters"
    />

    <Breadcrumb :crumbs="breadcrumbs" />

    <div class="search-body">

      <SidebarFilters
        v-model:filters="filters"
        :open="mobileSidebarOpen"
        :result-count="allFiltered.length"
        @reset="resetFilters"
        @close="mobileSidebarOpen = false"
      />

      <Transition name="backdrop-fade">
        <div
          v-if="mobileSidebarOpen"
          class="sidebar-backdrop"
          @click="mobileSidebarOpen = false"
        />
      </Transition>

      <main class="results-area">

        <ResultsToolbar
          :count="allFiltered.length"
          :query="query"
          :loading="loading"
          v-model:sortBy="sortBy"
          v-model:viewMode="viewMode"
          :active-filter-count="activeFilterCount"
          @open-filters="mobileSidebarOpen = true"
        />

        <!-- Loading skeletons -->
        <div v-if="loading" :class="viewMode === 'grid' ? 'results-grid' : 'results-list'">
          <div class="skeleton-card" v-for="i in 6" :key="i">
            <div class="skeleton-img" />
            <div class="skeleton-body">
              <div class="skeleton-line skeleton-line--short" />
              <div class="skeleton-line" />
              <div class="skeleton-line skeleton-line--med" />
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div class="empty-state" v-else-if="pagedResults.length === 0">
          <div class="empty-state__icon">🗺️</div>
          <h3 class="empty-state__title">No results found</h3>
          <p class="empty-state__sub">Try adjusting your filters or search a different destination.</p>
          <button class="btn btn-coral" @click="resetFilters">Clear filters</button>
        </div>

        <!-- Grid view -->
        <div v-else-if="viewMode === 'grid'" class="results-grid">
          <ResultCard
            v-for="item in pagedResults"
            :key="item.id + '-grid'"
            :item="item"
            :saved="wishlist.includes(item.id)"
            @select="goToDetail"
            @book="handleBook"
            @toggle-wishlist="toggleWishlist"
          />
        </div>

        <!-- List view -->
        <div v-else class="results-list">
          <ResultListCard
            v-for="item in pagedResults"
            :key="item.id + '-list'"
            :item="item"
            :saved="wishlist.includes(item.id)"
            @select="goToDetail"
            @book="handleBook"
            @toggle-wishlist="toggleWishlist"
          />
        </div>

        <SearchPagination
          v-if="!loading && pagedResults.length > 0"
          v-model="page"
          :total-pages="totalPages"
        />

      </main>
    </div>

    <BookingModal
      v-model="bookingOpen"
      :pkg="selectedItem"
      @submit="handleBookingSubmit"
    />

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { allForSearch } from '@/data/content.js'

import NavBar           from '@/components/home/NavBar.vue'
import PageHero         from '@/components/shared/PageHero.vue'
import Breadcrumb       from '@/components/shared/Breadcrumb.vue'
import FilterBar        from '@/components/shared/FilterBar.vue'
import SidebarFilters   from '@/components/search/SidebarFilters.vue'

import ResultCard       from '@/components/search/ResultCard.vue'
import ResultListCard   from '@/components/search/ResultListCard.vue'
import SearchPagination from '@/components/search/SearchPagination.vue'
import BookingModal     from '@/components/search/BookingModal.vue'

const route  = useRoute()
const router = useRouter()

// ── Category → route map ──────────────────────────────────────────────────
const CATEGORY_ROUTES = {
  dest:    '/destinations',
  package: '/packages',
  service: '/services',
}

// Note: FilterBar uses `value` not `key` for its modelValue
const categories = [
  { value: 'all',     label: 'All',          icon: '🌐' },
  { value: 'dest',    label: 'Destinations', icon: '📍' },
  { value: 'package', label: 'Packages',     icon: '✈️' },
  { value: 'service', label: 'Services',     icon: '🛎️' },
]

const heroStats = [
  { icon: '🌍', value: '2,000+', label: 'listings'     },
  { icon: '📍', value: '1,200+', label: 'destinations' },
  { icon: '⭐', value: '4.8',    label: 'avg rating'   },
]

const breadcrumbs = computed(() => {
  const base = [
    { label: 'Home',   to: '/' },
    { label: 'Search', to: '/search' },
  ]
  if (activeCategory.value !== 'all') {
    const cat = categories.find(c => c.value === activeCategory.value)
    if (cat) base.push({ label: cat.label })
  } else {
    base[1] = { label: 'Search' }
  }
  return base
})

// ── UI state ──────────────────────────────────────────────────────────────
const query             = ref(route.query.q || '')
const activeCategory    = ref('all')
const sortBy            = ref('recommended')
const viewMode          = ref('grid')
const loading           = ref(false)
const page              = ref(1)
const perPage           = 9
const mobileSidebarOpen = ref(false)
const wishlist          = ref([])
const bookingOpen       = ref(false)
const selectedItem      = ref(null)

// ── Basic filters ─────────────────────────────────────────────────────────
const filters = ref({
  priceMin:  null,
  priceMax:  null,
  durations: [],
  types:     [],
  minRating: null,
})

// ── Advanced filters ──────────────────────────────────────────────────────
const advancedFilters = ref({
  languages:           [],
  groupSizes:          [],
  months:              [],
  difficulty:          null,
  accommodations:      [],
  perks:               [],
  minReviews:          0,
  instantConfirmation: false,
})

// ── Redirect when a specific category is selected ─────────────────────────
watch(activeCategory, (cat) => {
  const targetPath = CATEGORY_ROUTES[cat]
  if (!targetPath) return

  const q = {}
  if (query.value?.trim())             q.q         = query.value.trim()
  if (sortBy.value !== 'recommended')  q.sortBy    = sortBy.value
  if (filters.value.priceMin != null)  q.priceMin  = filters.value.priceMin
  if (filters.value.priceMax != null)  q.priceMax  = filters.value.priceMax
  if (filters.value.minRating)         q.minRating = filters.value.minRating
  if (filters.value.durations?.length) q.durations = filters.value.durations.join(',')
  if (filters.value.types?.length)     q.types     = filters.value.types.join(',')

  router.push({ path: targetPath, query: q })
})

// ── Filter count ──────────────────────────────────────────────────────────
const activeFilterCount = computed(() => {
  let c = 0
  if (filters.value.priceMin)         c++
  if (filters.value.priceMax)         c++
  if (filters.value.durations.length) c += filters.value.durations.length
  if (filters.value.types.length)     c += filters.value.types.length
  if (filters.value.minRating)        c++
  const af = advancedFilters.value
  if (af.languages.length)      c += af.languages.length
  if (af.groupSizes.length)     c += af.groupSizes.length
  if (af.months.length)         c += af.months.length
  if (af.difficulty)            c++
  if (af.accommodations.length) c += af.accommodations.length
  if (af.perks.length)          c += af.perks.length
  if (af.minReviews > 0)        c++
  if (af.instantConfirmation)   c++
  return c
})

function resetFilters() {
  filters.value = { priceMin: null, priceMax: null, durations: [], types: [], minRating: null }
  advancedFilters.value = {
    languages: [], groupSizes: [], months: [], difficulty: null,
    accommodations: [], perks: [], minReviews: 0, instantConfirmation: false,
  }
  activeCategory.value = 'all'
  page.value = 1
}

function goToDetail(item) {
  if (item.category === 'dest')    return router.push(`/destinations/${item.id}`)
  if (item.category === 'package') return router.push(`/packages/${item.id}`)
  if (item.category === 'service') return router.push(`/services/${item.id}`)
}

function runSearch() {
  loading.value = true
  page.value    = 1
  setTimeout(() => { loading.value = false }, 700)
}

onMounted(() => { if (query.value) runSearch() })
watch(activeCategory, () => { page.value = 1 })

function toggleWishlist(id) {
  const idx = wishlist.value.indexOf(id)
  idx === -1 ? wishlist.value.push(id) : wishlist.value.splice(idx, 1)
}

function handleBook(item) {
  selectedItem.value = item
  bookingOpen.value  = true
}

function handleBookingSubmit(payload) {
  console.log('Booking submitted:', payload)
}

// ── Data ──────────────────────────────────────────────────────────────────
const allResults = ref(allForSearch)

function matchesDuration(item) {
  if (!filters.value.durations.length || !item.duration) return true
  return filters.value.durations.some(d => {
    if (d === '1-3')  return item.duration >= 1  && item.duration <= 3
    if (d === '4-7')  return item.duration >= 4  && item.duration <= 7
    if (d === '8-14') return item.duration >= 8  && item.duration <= 14
    if (d === '15+')  return item.duration >= 15
    return true
  })
}

function matchesAdvanced(item) {
  const af = advancedFilters.value
  if (af.languages.length && item.languages?.length)
    if (!af.languages.some(l => item.languages.includes(l))) return false
  if (af.groupSizes.length && item.groupSize)
    if (!af.groupSizes.includes(item.groupSize)) return false
  if (af.months.length && item.departureMonths?.length)
    if (!af.months.some(m => item.departureMonths.includes(m))) return false
  if (af.difficulty && item.difficulty)
    if (item.difficulty !== af.difficulty) return false
  if (af.accommodations.length && item.accommodation)
    if (!af.accommodations.includes(item.accommodation)) return false
  if (af.perks.length && item.perks?.length)
    if (!af.perks.every(p => item.perks.includes(p))) return false
  if (af.minReviews > 0 && item.reviewCount != null)
    if (item.reviewCount < af.minReviews) return false
  if (af.instantConfirmation && !item.instantConfirmation) return false
  return true
}

const allFiltered = computed(() => {
  let r = [...allResults.value]

  if (activeCategory.value !== 'all')
    r = r.filter(i => i.category === activeCategory.value)

  if (query.value.trim()) {
    const q = query.value.toLowerCase()
    r = r.filter(i =>
      i.title?.toLowerCase().includes(q) ||
      i.desc?.toLowerCase().includes(q)
    )
  }

  if (filters.value.priceMin != null) r = r.filter(i => i.price >= filters.value.priceMin)
  if (filters.value.priceMax != null) r = r.filter(i => i.price <= filters.value.priceMax)
  if (filters.value.minRating)        r = r.filter(i => i.rating >= filters.value.minRating)
  if (filters.value.types.length)     r = r.filter(i => filters.value.types.includes(i.type))
  r = r.filter(matchesDuration)
  r = r.filter(matchesAdvanced)

  if (sortBy.value === 'price_asc')  r.sort((a, b) => a.price - b.price)
  if (sortBy.value === 'price_desc') r.sort((a, b) => b.price - a.price)
  if (sortBy.value === 'rating')     r.sort((a, b) => b.rating - a.rating)

  return r
})

const totalPages   = computed(() => Math.max(1, Math.ceil(allFiltered.value.length / perPage)))
const pagedResults = computed(() => {
  const start = (page.value - 1) * perPage
  return allFiltered.value.slice(start, start + perPage)
})

watch(allFiltered, () => { if (page.value > totalPages.value) page.value = 1 })
</script>

<style scoped>
.search-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--gray-50);
}

.search-body {
  display: grid;
  grid-template-columns: 280px 1fr;
  flex: 1;
  align-items: flex-start;
}

.results-area { padding: 28px 32px; min-height: 80vh; }
.results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
.results-list { display: flex; flex-direction: column; gap: 18px; }

/* Skeletons */
.skeleton-card { background: var(--white); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); }
.skeleton-img  { height: 200px; background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton-body { padding: 20px; }
.skeleton-line { height: 14px; border-radius: 7px; margin-bottom: 10px; background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton-line--short { width: 40%; }
.skeleton-line--med   { width: 65%; }
@keyframes shimmer { to { background-position: -200% 0; } }

/* Empty state */
.empty-state        { text-align: center; padding: 80px 20px; }
.empty-state__icon  { font-size: 3.5rem; margin-bottom: 16px; }
.empty-state__title { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; margin-bottom: 10px; }
.empty-state__sub   { font-size: .95rem; color: var(--gray-400); margin-bottom: 28px; }

/* Sidebar backdrop */
.sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 60; }
.backdrop-fade-enter-active, .backdrop-fade-leave-active { transition: opacity .25s ease; }
.backdrop-fade-enter-from, .backdrop-fade-leave-to       { opacity: 0; }

@media (max-width: 1024px) {
  .results-area { padding: 24px 20px; }
  .results-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
}
@media (max-width: 768px) {
  .search-body      { grid-template-columns: 1fr; }
  .sidebar-backdrop { display: block; }
  .results-area     { padding: 18px 4%; }
}
</style>