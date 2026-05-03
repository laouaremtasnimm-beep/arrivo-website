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
      :active-filter-count="activeFilterCount + advancedFilterCount"
      v-model:advancedFilters="advancedFilters"
    />

    <Breadcrumb :crumbs="[
      { label: 'Home',     to: '/'       },
      { label: 'Search',   to: '/search' },
      { label: 'Packages'               },
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
            :booked="isBooked('package', item.id, item.activeOffer?.id)"
            :is-owner="isItemOwner(item)"
            @select="goToDetail"
            @book="openBooking"
            @cancel="handleCancel"
            @manage="router.push('/dashboard')"
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
import { packages } from '@/data/content.js'
import { useAuth } from '@/composables/useAuth'
import { useBookings } from '@/composables/useBookings'

import NavBar           from '@/components/home/NavBar.vue'
import PageHero         from '@/components/shared/PageHero.vue'
import Breadcrumb       from '@/components/shared/Breadcrumb.vue'
import FilterBar        from '@/components/shared/FilterBar.vue'
import ItemGrid         from '@/components/shared/ItemGrid.vue'
import PackageCard      from '@/components/shared/PackageCard.vue'
import SidebarFilters   from '@/components/search/SidebarFilters.vue'
import ResultsToolbar   from '@/components/search/ResultsToolbar.vue'
import SearchPagination from '@/components/search/SearchPagination.vue'
import BookingModal     from '@/components/home/BookingModal.vue'

import { calculateDays } from '@/utils/dateUtils.js'

const router       = useRouter()
const sidebarOpen  = ref(false)
const bookingOpen  = ref(false)
const selectedItem = ref(null)

const { user, isLoggedIn } = useAuth()
const { createBooking, isBooked, getBookingId, cancelBooking } = useBookings()

const DEMO_IDS = new Set(packages.map(p => p.id))
const allItems = ref([...packages])

function normalizePackage(p) {
  const startDate = p.start_date || p.startDate
  const endDate   = p.end_date   || p.endDate

  const offer = (p.active_offer_id || p.activeOffer?.id) ? {
    id:        p.active_offer_id       || p.activeOffer?.id,
    discount:  p.active_offer_discount || p.activeOffer?.discount,
    startDate: p.active_offer_start    || p.activeOffer?.startDate,
    endDate:   p.active_offer_end      || p.activeOffer?.endDate,
    title:     p.active_offer_title    || p.activeOffer?.title
  } : null

  // If offer exists, use its dates for duration, otherwise use standard dates
  const calcDur = offer 
    ? calculateDays(offer.startDate, offer.endDate)
    : calculateDays(startDate, endDate)

  return {
    id:         p.id,
    title:      p.title,
    agency:     p.agency_name  ?? p.agency      ?? 'Agency',
    img:        p.img_url      ?? p.img         ?? 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg',
    type:       p.type         ?? 'Adventure',
    duration:   calcDur || p.duration_days || p.duration || 0,
    rating:     p.rating       ?? 4.5,
    reviews:    p.review_count ?? p.reviews      ?? 0,
    spots:      p.spots_available ?? p.spots     ?? 0,
    price:      p.price,
    desc:       p.description  ?? p.desc      ?? '',
    agency_id:  p.agency_id    ?? null,
    includes:   typeof p.includes === 'string'
                  ? JSON.parse(p.includes || '[]')
                  : (p.includes ?? []),
    activeOffer: offer,
    startDate,
    endDate,
  }
}

function isItemOwner(item) {
  if (!user.value || !item) return false
  const uid = String(user.value.userID || user.value.id)
  const oid = String(item.agency_id || item.userId || item.owner_id || item.item_owner_id || '')
  return oid !== '' && oid === uid
}

onMounted(async () => {
  try {
    const res  = await fetch('/arrivo-website/backend/api/v1/packages.php')
    const data = await res.json()
    const dbRows = (data.packages ?? []).map(normalizePackage)

    const final = packages.map(normalizePackage)
    dbRows.forEach(dbItem => {
      const exists = final.find(p => p.id === dbItem.id || p.title === dbItem.title)
      if (!exists) {
        final.push(dbItem)
      } else {
        // Merge: demo wins for display fields, but DB always wins for ownership fields
        const idx = final.findIndex(p => p.id === dbItem.id || p.title === dbItem.title)
        final[idx] = {
          ...dbItem,
          ...final[idx],
          // Prioritize DB for offers and ownership
          activeOffer: dbItem.activeOffer || final[idx].activeOffer || null,
          agency_id:   dbItem.agency_id   ?? final[idx].agency_id   ?? null,
          // Keep the demo's recalculated duration if it's longer (usually more accurate for demo)
          duration:    Math.max(dbItem.duration || 0, final[idx].duration || 0)
        }
      }
    })
    allItems.value = final
  } catch (e) {
    console.error('Failed to load packages from API:', e)
  }
})

function goToDetail(item)  { router.push(`/packages/${item.id}`) }
function openBooking(item) { selectedItem.value = item; bookingOpen.value = true }
async function handleBooking(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  let finalPrice = selectedItem.value.price;
  const isOffer = !!selectedItem.value.activeOffer;
  
  if (isOffer) {
    finalPrice = Math.round(finalPrice * (1 - selectedItem.value.activeOffer.discount / 100));
  }

  const result = await createBooking({
    user_id:  user.value?.userID ?? user.value?.id,
    type:     isOffer ? 'offer' : 'package',
    item_id:  isOffer ? selectedItem.value.activeOffer.id : selectedItem.value.id,
    title:    isOffer ? selectedItem.value.activeOffer.title : selectedItem.value.title,
    price:    finalPrice,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
  })

  if (result.ok) {
    bookingOpen.value = false
    alert(isOffer ? 'Offer booked successfully!' : 'Package booked successfully!')
  } else {
    alert('Failed to book: ' + result.error)
  }
}

async function handleCancel(item) {
  const id = getBookingId('package', item.id, item.activeOffer?.id)

  if (!id) return
  const res = await cancelBooking(id)
  if (res.ok) alert('Booking cancelled successfully.')
  else alert('Failed to cancel: ' + res.error)
}

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
  filters, advancedFilters,
  activeFilterCount, advancedFilterCount,
  allFiltered, totalPages, pagedResults,
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