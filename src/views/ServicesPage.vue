<template>
  <div class="list-page">
    <NavBar />

    <PageHero
      title="Local Services"
      subtitle="Trusted local experts for every part of your journey. Transportation, guides, experiences and more."
      tagline="Verified Providers"
      icon="🛎️"
      color="sand"
      search-placeholder="Search services…"
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
      { label: 'Home',     to: '/'       },
      { label: 'Search',   to: '/search' },
      { label: 'Services'               },
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
          empty-icon="🛎️"
          empty-title="No services found"
          empty-sub="Try a different category or clear your filters."
          @reset="resetFilters"
        >
          <ServiceCard
            v-for="item in pagedResults"
            :key="item.id"
            :item="item"
            :saved="isItemSaved(item)"
            :booked="isBooked('service', item.id)"
            @select="goToDetail"
            @book="openBooking"
            @cancel="handleCancel"
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useListPage } from '@/composables/useListPage'
import { services } from '@/data/content.js'
import { useAuth } from '@/composables/useAuth'
import { useBookings } from '@/composables/useBookings'

import NavBar           from '@/components/home/NavBar.vue'
import PageHero         from '@/components/shared/PageHero.vue'
import Breadcrumb       from '@/components/shared/Breadcrumb.vue'
import FilterBar        from '@/components/shared/FilterBar.vue'
import ItemGrid         from '@/components/shared/ItemGrid.vue'
import ServiceCard      from '@/components/shared/ServiceCard.vue'
import SidebarFilters   from '@/components/search/SidebarFilters.vue'
import ResultsToolbar   from '@/components/search/ResultsToolbar.vue'
import SearchPagination from '@/components/search/SearchPagination.vue'
import BookingModal     from '@/components/home/BookingModal.vue'

const router       = useRouter()
const sidebarOpen  = ref(false)
const bookingOpen  = ref(false)
const selectedItem = ref(null)

const { user, isLoggedIn } = useAuth()
const { createBooking, isBooked, getBookingId, cancelBooking } = useBookings()

const DEMO_IDS = new Set(services.map(s => s.id))
const allItems = ref([...services])

function normalizeService(s) {
  return {
    id:           s.id,
    title:        s.title,
    provider:     s.provider_name ?? s.provider    ?? 'Provider',
    icon:         s.icon          ?? '🛎️',
    iconBg:       s.icon_bg       ?? 'svc-icon-coral',
    type:         s.type          ?? 'Transport',
    price:        s.price,
    unit:         s.price_unit    ?? s.unit         ?? 'day',
    rating:       s.rating        ?? 4.5,
    reviews:      s.review_count  ?? s.reviews      ?? 0,
    availability: s.is_available  == 1,
    desc:         s.description   ?? s.desc         ?? '',
    img:          null, // ✅ Legacy image removal
    features:     typeof s.features === 'string'
                    ? JSON.parse(s.features || '[]')
                    : (s.features ?? []),
  }
}

onMounted(async () => {
  try {
    const res  = await fetch('/arrivo-website/backend/api/v1/services.php')
    const data = await res.json()
    
    // Filter out old services with images
    const dbRows = (data.services ?? [])
      .filter(s => !s.img_url)
      .map(normalizeService)

    const final = [...services]
    dbRows.forEach(dbItem => {
      const exists = final.find(s => s.id === dbItem.id || s.title === dbItem.title)
      if (!exists) {
        final.push(dbItem)
      } else {
        // Merge: demo wins
        const idx = final.findIndex(s => s.id === dbItem.id || s.title === dbItem.title)
        final[idx] = { ...dbItem, ...final[idx] }
      }
    })
    allItems.value = final
  } catch (e) {
    console.error('Failed to load services from API:', e)
  }
})

function goToDetail(item)  { router.push(`/services/${item.id}`) }
function openBooking(item) { selectedItem.value = item; bookingOpen.value = true }
async function handleBooking(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  const result = await createBooking({
    user_id:  user.value?.userID ?? user.value?.id,
    type:     'service',
    item_id:  selectedItem.value.id,
    title:    selectedItem.value.title,
    price:    selectedItem.value.price,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
  })

  if (result.ok) {
    bookingOpen.value = false
    alert('Service booked successfully!')
  } else {
    alert('Failed to book: ' + result.error)
  }
}

async function handleCancel(item) {
  const id = getBookingId('service', item.id)
  if (!id) return
  const res = await cancelBooking(id)
  if (res.ok) alert('Booking cancelled successfully.')
  else alert('Failed to cancel: ' + res.error)
}

const heroStats = [
  { icon: '🛎️', value: '500+', label: 'services'          },
  { icon: '✅',  value: '100%', label: 'verified providers' },
  { icon: '⭐',  value: '4.8',  label: 'avg rating'         },
]

const categories = [
  { value: 'all',         label: 'All',          icon: '🌐' },
  { value: 'Transport',   label: 'Transport',    icon: '🚐' },
  { value: 'Adventure',   label: 'Adventure',    icon: '🧗' },
  { value: 'Food',        label: 'Food & Drink', icon: '🍽️' },
  { value: 'Wellness',    label: 'Wellness',     icon: '🧘' },
  { value: 'Photography', label: 'Photography',  icon: '📸' },
  { value: 'Tours',       label: 'Tours',        icon: '🗺️' },
]

const {
  query, activeCategory, sortBy, viewMode, loading, page,
  filters, advancedFilters,
  activeFilterCount, advancedFilterCount,
  allFiltered, totalPages, pagedResults,
  resetFilters, runSearch, isItemSaved, toggleWishlist,
} = useListPage(allItems, 'service', { perPage: 12 })
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