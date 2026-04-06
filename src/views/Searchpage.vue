<template>
  <div class="search-page">

    <!-- ── Sticky search header ───────────────── -->
    <SearchHeader
      v-model="query"
      v-model:activeCategory="activeCategory"
      @search="runSearch"
    />

    <!-- ── Body ──────────────────────────────── -->
    <div class="search-body">

      <!-- ── Sidebar filters ──────────────────── -->
      <SidebarFilters
        v-model:filters="filters"
        :open="mobileSidebarOpen"
        :result-count="allFiltered.length"
        @reset="resetFilters"
        @close="mobileSidebarOpen = false"
      />

      <!-- Mobile backdrop -->
      <Transition name="backdrop-fade">
        <div
          v-if="mobileSidebarOpen"
          class="sidebar-backdrop"
          @click="mobileSidebarOpen = false"
        />
      </Transition>

      <!-- ── Results ───────────────────────────── -->
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
            @book="handleBook"
            @toggle-wishlist="toggleWishlist"
          />
        </div>

        <!-- Pagination -->
        <SearchPagination
          v-if="!loading && pagedResults.length > 0"
          v-model="page"
          :total-pages="totalPages"
        />

      </main>
    </div>

    <!-- Reuse existing BookingModal component -->
    <BookingModal
      v-model="bookingOpen"
      :pkg="selectedItem"
      @submit="handleBookingSubmit"
    />

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'

import SearchHeader     from '@/components/SearchHeader.vue'
import SidebarFilters   from '@/components/SidebarFilters.vue'
import ResultsToolbar   from '@/components/ResultsToolbar.vue'
import ResultCard       from '@/components/ResultCard.vue'
import ResultListCard   from '@/components/ResultListCard.vue'
import SearchPagination from '@/components/SearchPagination.vue'
import BookingModal     from '@/components/BookingModal.vue'

const route = useRoute()

// ── UI state ───────────────────────────────────────────────────────────────
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

// ── Filters ────────────────────────────────────────────────────────────────
const filters = ref({
  priceMin:  null,
  priceMax:  null,
  durations: [],
  types:     [],
  minRating: null,
})

const activeFilterCount = computed(() => {
  let c = 0
  if (filters.value.priceMin)         c++
  if (filters.value.priceMax)         c++
  if (filters.value.durations.length) c += filters.value.durations.length
  if (filters.value.types.length)     c += filters.value.types.length
  if (filters.value.minRating)        c++
  return c
})

function resetFilters() {
  filters.value        = { priceMin: null, priceMax: null, durations: [], types: [], minRating: null }
  activeCategory.value = 'all'
  page.value           = 1
}

// ── Search ─────────────────────────────────────────────────────────────────
function runSearch() {
  loading.value = true
  page.value    = 1
  setTimeout(() => { loading.value = false }, 900)
}

onMounted(() => { if (query.value) runSearch() })
watch(activeCategory, () => { page.value = 1 })

// ── Wishlist & Booking ─────────────────────────────────────────────────────
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
  // TODO: call your bookings API
}

// ── Data ───────────────────────────────────────────────────────────────────
// In production: move to a Pinia store and replace with API calls.
const allResults = ref([
  { id:1,   category:'package', categoryLabel:'Travel Package', title:'Swiss Alps Winter Retreat',  desc:'Ski, snowboard and relax in cozy mountain chalets with breathtaking alpine views.',                                img:'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=700&q=75', price:2490, rating:4.9, reviews:214,  duration:8,  spots:4,  type:'Adventure', tag:'🔥 Popular',  ctaLabel:'Book now',     priceLabel:'per person'    },
  { id:2,   category:'package', categoryLabel:'Travel Package', title:'Bali Spiritual Journey',     desc:'Discover temples, rice terraces and traditional healing rituals across the island of gods.',                      img:'https://images.unsplash.com/photo-1604999565976-8913ad2ddb7c?w=700&q=75', price:1650, rating:4.8, reviews:183,  duration:10, spots:8,  type:'Cultural', tag:'✨ New',       ctaLabel:'Book now',     priceLabel:'per person'    },
  { id:3,   category:'package', categoryLabel:'Travel Package', title:'Greek Island Odyssey',       desc:'Sail between Santorini, Mykonos and Crete on a private yacht with full crew.',                                   img:'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=700&q=75', price:3100, rating:4.9, reviews:320,  duration:14, spots:2,  type:'Beach',    tag:'⚡ 2 left',   ctaLabel:'Book now',     priceLabel:'per person'    },
  { id:4,   category:'package', categoryLabel:'Travel Package', title:'Sahara Desert Adventure',   desc:'Ride camels at sunset, sleep under the stars and explore ancient kasbahs.',                                       img:'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=700&q=75', price:980,  rating:4.7, reviews:98,   duration:6,  spots:12, type:'Adventure', tag:null,           ctaLabel:'Book now',     priceLabel:'per person'    },
  { id:5,   category:'package', categoryLabel:'Travel Package', title:'Tokyo Family Explorer',      desc:'Anime, tech, temples and theme parks — a magical Japanese adventure for the whole family.',                       img:'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=700&q=75', price:2200, rating:4.8, reviews:145,  duration:9,  spots:6,  type:'Family',   tag:null,           ctaLabel:'Book now',     priceLabel:'per person'    },
  { id:6,   category:'package', categoryLabel:'Travel Package', title:'Amalfi Coast Drive',         desc:'Wind along the cliffside roads of southern Italy, stopping in Positano and Capri.',                              img:'https://images.unsplash.com/photo-1533606688076-b6683a5f59f1?w=700&q=75', price:1890, rating:4.9, reviews:267,  duration:7,  spots:5,  type:'Beach',    tag:'🏅 Top rated', ctaLabel:'Book now',     priceLabel:'per person'    },
  { id:7,   category:'package', categoryLabel:'Travel Package', title:'Morocco Medina Discovery',   desc:'Explore the souks, palaces and riads of Fez, Marrakech and Chefchaouen.',                                        img:'https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=700&q=75', price:1100, rating:4.7, reviews:112,  duration:8,  spots:10, type:'Cultural', tag:null,           ctaLabel:'Book now',     priceLabel:'per person'    },
  { id:8,   category:'package', categoryLabel:'Travel Package', title:'Maldives Overwater Escape',  desc:'Luxury overwater bungalows, snorkelling, spa and pristine private beaches.',                                      img:'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=700&q=75', price:4200, rating:5.0, reviews:89,   duration:7,  spots:4,  type:'Beach',    tag:'💎 Luxury',    ctaLabel:'Book now',     priceLabel:'per person'    },
  { id:101, category:'service', categoryLabel:'Service',        title:'Private Airport Transfer',   desc:'Comfortable door-to-door transfers from any airport. Available 24/7.',                                            img:'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=700&q=75', price:45,   rating:4.9, reviews:540,  duration:null,spots:null,type:'City Break',tag:null,           ctaLabel:'Book service', priceLabel:'per trip'      },
  { id:102, category:'service', categoryLabel:'Service',        title:'Certified Mountain Guide',   desc:'Expert local guides for hiking, climbing and multi-day treks in any terrain.',                                    img:'https://images.unsplash.com/photo-1551632811-561732d1e306?w=700&q=75', price:120,  rating:4.8, reviews:312,  duration:null,spots:null,type:'Adventure', tag:null,           ctaLabel:'Book service', priceLabel:'per day'       },
  { id:103, category:'service', categoryLabel:'Service',        title:'Private Chef Experience',    desc:'A local chef comes to your villa and prepares a fully authentic dinner.',                                          img:'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=700&q=75', price:180,  rating:5.0, reviews:178,  duration:null,spots:null,type:'Wellness',  tag:'⭐ Top rated', ctaLabel:'Book service', priceLabel:'per evening'   },
  { id:104, category:'service', categoryLabel:'Service',        title:'Scuba Diving Lessons',       desc:'PADI-certified instructors for beginners and advanced divers alike.',                                              img:'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=700&q=75', price:95,   rating:4.9, reviews:223,  duration:null,spots:null,type:'Beach',     tag:null,           ctaLabel:'Book service', priceLabel:'per session'   },
  { id:105, category:'service', categoryLabel:'Service',        title:'Travel Photography',         desc:'Professional photographer to document your journey with stunning shots.',                                          img:'https://images.unsplash.com/photo-1471341971476-ae15ff5dd4ea?w=700&q=75', price:150,  rating:4.7, reviews:98,   duration:null,spots:null,type:'City Break',tag:null,           ctaLabel:'Book service', priceLabel:'per day'       },
  { id:201, category:'dest',    categoryLabel:'Destination',    title:'Santorini, Greece',          desc:'Famous for its white-washed cycladic houses, stunning sunsets and crystal-clear Aegean waters.',                 img:'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=700&q=75', price:890,  rating:4.9, reviews:2140, duration:null,spots:null,type:'Beach',     tag:'🔥 Trending',  ctaLabel:'Explore',      priceLabel:'packages from' },
  { id:202, category:'dest',    categoryLabel:'Destination',    title:'Kyoto, Japan',               desc:'Ancient temples, geisha districts, bamboo groves and world-class traditional cuisine.',                          img:'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=700&q=75', price:1200, rating:4.8, reviews:1830, duration:null,spots:null,type:'Cultural', tag:'🌸 Seasonal',  ctaLabel:'Explore',      priceLabel:'packages from' },
  { id:203, category:'dest',    categoryLabel:'Destination',    title:'Marrakech, Morocco',         desc:'A sensory feast of souks, spices, stunning riads and the vibrant Djemaa el-Fna square.',                        img:'https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=700&q=75', price:640,  rating:4.7, reviews:950,  duration:null,spots:null,type:'Cultural', tag:null,           ctaLabel:'Explore',      priceLabel:'packages from' },
])

// ── Filtering & Sorting ────────────────────────────────────────────────────
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

const allFiltered = computed(() => {
  let r = [...allResults.value]

  if (activeCategory.value !== 'all')
    r = r.filter(i => i.category === activeCategory.value)

  if (query.value.trim()) {
    const q = query.value.toLowerCase()
    r = r.filter(i => i.title.toLowerCase().includes(q) || i.desc.toLowerCase().includes(q))
  }

  if (filters.value.priceMin != null) r = r.filter(i => i.price >= filters.value.priceMin)
  if (filters.value.priceMax != null) r = r.filter(i => i.price <= filters.value.priceMax)
  if (filters.value.minRating)        r = r.filter(i => i.rating >= filters.value.minRating)
  if (filters.value.types.length)     r = r.filter(i => filters.value.types.includes(i.type))
  r = r.filter(matchesDuration)

  if (sortBy.value === 'price_asc')  r.sort((a, b) => a.price - b.price)
  if (sortBy.value === 'price_desc') r.sort((a, b) => b.price - a.price)
  if (sortBy.value === 'rating')     r.sort((a, b) => b.rating - a.rating)

  return r
})

const totalPages = computed(() => Math.max(1, Math.ceil(allFiltered.value.length / perPage)))

const pagedResults = computed(() => {
  const start = (page.value - 1) * perPage
  return allFiltered.value.slice(start, start + perPage)
})

watch(allFiltered, () => { if (page.value > totalPages.value) page.value = 1 })
</script>

<style scoped>
.search-page {
  min-height: 100vh;
  display: flex; flex-direction: column;
  background: var(--gray-50);
}

.search-body {
  display: grid;
  grid-template-columns: 280px 1fr;
  flex: 1; align-items: flex-start;
}

.results-area  { padding: 28px 32px; min-height: 80vh; }
.results-grid  { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
.results-list  { display: flex; flex-direction: column; gap: 18px; }

/* Skeletons */
.skeleton-card { background: var(--white); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); }
.skeleton-img  {
  height: 200px;
  background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
  background-size: 200% 100%; animation: shimmer 1.4s infinite;
}
.skeleton-body        { padding: 20px; }
.skeleton-line        { height: 14px; border-radius: 7px; margin-bottom: 10px; background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton-line--short { width: 40%; }
.skeleton-line--med   { width: 65%; }
@keyframes shimmer { to { background-position: -200% 0; } }

/* Empty state */
.empty-state        { text-align: center; padding: 80px 20px; }
.empty-state__icon  { font-size: 3.5rem; margin-bottom: 16px; }
.empty-state__title { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; margin-bottom: 10px; }
.empty-state__sub   { font-size: .95rem; color: var(--gray-400); margin-bottom: 28px; }

/* Backdrop */
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