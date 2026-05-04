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
        <div v-if="mobileSidebarOpen" class="sidebar-backdrop" @click="mobileSidebarOpen = false" />
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

        <!-- Error state -->
        <div class="empty-state" v-else-if="fetchError">
          <div class="empty-state__icon">⚠️</div>
          <h3 class="empty-state__title">Could not load results</h3>
          <p class="empty-state__sub">{{ fetchError }}</p>
          <button class="btn btn-coral" @click="runSearch">Try again</button>
        </div>

        <!-- Empty state -->
        <div class="empty-state" v-else-if="pagedResults.length === 0">
          <div class="empty-state__icon">🗺️</div>
          <h3 class="empty-state__title">No results found</h3>
          <p class="empty-state__sub">Try adjusting your filters or search a different term.</p>
          <button class="btn btn-coral" @click="resetFilters">Clear filters</button>
        </div>

        <!-- Grid view -->
        <div v-else-if="viewMode === 'grid'" class="results-grid">
          <template v-for="item in pagedResults" :key="item.id + '-' + item.category">
            <!-- 🟠 Offers use DealCard -->
            <DealCard
              v-if="item.category === 'offer'"
              :offer="item"
              :saved="isSaved('offer', item.id)"
              :booked="isBooked('offer', item.id, item.package_id || item.linked_package_id)"
              :is-owner="isItemOwner(item)"
              @select="goToDetail"
              @toggle-save="toggleWishlist('offer', item.id)"
              @book="handleBook"
              @manage="router.push('/dashboard')"
            />

            <!-- 🔵 Everything else -->
            <ServiceCard
              v-else-if="item.category === 'service'"
              :item="item"
              :saved="isSaved('service', item.id)"
              :booked="isBooked('service', item.id, item.activeOffer?.id)"
              :is-owner="isItemOwner(item)"
              @select="goToDetail"
              @book="handleBook"
              @cancel="handleCancel"
              @toggle-wishlist="toggleWishlist('service', item.id)"
              @manage="router.push('/dashboard')"
            />

            <!-- 🔵 DESTINATION -->
            <DestinationCard
              v-else-if="item.category === 'dest'"
              :item="item"
              :saved="isSaved('destination', item.id)"
              @select="goToDetail"
              @toggle-wishlist="toggleWishlist('dest', item.id)"
            />

            <!-- 🟢 PACKAGE — use the exact same PackageCard as the packages page -->
            <PackageCard
              v-else-if="item.category === 'package'"
              :item="item"
              :saved="isSaved('package', item.id)"
              :booked="isBooked('package', item.id, item.activeOffer?.id)"
              :is-owner="isItemOwner(item)"
              @select="goToDetail"
              @book="handleBook"
              @cancel="handleCancel"
              @toggle-wishlist="toggleWishlist('package', item.id)"
              @manage="router.push('/dashboard')"
            />

            <!-- 🔵 Everything else (destinations, etc.) -->
            <ResultCard
              v-else
              :item="item"
              :saved="isSaved(item.category === 'dest' ? 'destination' : item.category, item.id)"
              :booked="isBooked(item.category === 'dest' ? 'destination' : item.category, item.id, item.activeOffer?.id)"
              :is-owner="isItemOwner(item)"
              @select="goToDetail"
              @book="handleBook"
              @cancel="handleCancel"
              @toggle-wishlist="toggleWishlist(item.category, item.id)"
              @manage="router.push('/dashboard')"
            />
          </template>
        </div>

        <!-- List view -->
        <div v-else class="results-list">
          <template v-for="item in pagedResults" :key="item.id + '-' + item.category">
            <!-- 🟠 Offers -->
            <DealCard
              v-if="item.category === 'offer'"
              :offer="item"
              :saved="isSaved('offer', item.id)"
              :booked="isBooked('offer', item.id, item.package_id || item.linked_package_id)"
              :is-owner="isItemOwner(item)"
              @select="goToDetail"
              @toggle-save="toggleWishlist('offer', item.id)"
              @book="handleBook"
              @manage="router.push('/dashboard')"
            />

            <!-- 🔵 Others (Packages & Services) -->
            <PackageCard
              v-else-if="item.category === 'package'"
              :item="item"
              :saved="isSaved('package', item.id)"
              :booked="isBooked('package', item.id, item.activeOffer?.id)"
              :is-owner="isItemOwner(item)"
              @select="goToDetail"
              @book="handleBook"
              @cancel="handleCancel"
              @toggle-wishlist="toggleWishlist('package', item.id)"
              @manage="router.push('/dashboard')"
            />

            <ResultListCard
              v-else
              :item="item"
              :saved="isSaved(item.category === 'dest' ? 'destination' : item.category, item.id)"
              :booked="isBooked(item.category === 'dest' ? 'destination' : item.category, item.id, item.activeOffer?.id)"
              :is-owner="isItemOwner(item)"
              @select="goToDetail"
              @book="handleBook"
              @cancel="handleCancel"
              @toggle-wishlist="toggleWishlist(item.category, item.id)"
              @manage="router.push('/dashboard')"
            />
          </template>
        </div>

        <SearchPagination
          v-if="!loading && pagedResults.length > 0"
          v-model="page"
          :total-pages="totalPages"
        />
      </main>
    </div>

    <BookingModal v-model="bookingOpen" :pkg="selectedItem" @submit="handleBookingSubmit" />
    <OfferDetailModal v-model="offerModalOpen" :offer="selectedOffer" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth }    from '@/composables/useAuth'
import { useBookings } from '@/composables/useBookings'
import { useOffers } from '@/composables/useOffers'
import { useWishlist } from '@/composables/useWishlist'
import { destinations as demoDestinations, packages as demoPackages, services as demoServices } from '@/data/content.js'
import { calculateDays } from '@/utils/dateUtils.js'

import NavBar           from '@/components/home/NavBar.vue'
import PageHero         from '@/components/shared/PageHero.vue'
import Breadcrumb       from '@/components/shared/Breadcrumb.vue'
import FilterBar        from '@/components/shared/FilterBar.vue'
import SidebarFilters   from '@/components/search/SidebarFilters.vue'
import ResultsToolbar   from '@/components/search/ResultsToolbar.vue'
import ResultCard       from '@/components/search/ResultCard.vue'
import ResultListCard   from '@/components/search/ResultListCard.vue'
import SearchPagination from '@/components/search/SearchPagination.vue'
import BookingModal     from '@/components/search/BookingModal.vue'
import DealCard         from '@/components/search/DealCard.vue'
import PackageCard      from '@/components/shared/PackageCard.vue'
import ServiceCard      from '@/components/shared/ServiceCard.vue'
import DestinationCard  from '@/components/shared/DestinationCard.vue'
import OfferDetailModal from '@/components/home/OfferDetailModal.vue'


const route  = useRoute()
const router = useRouter()
const { user, isLoggedIn } = useAuth()
const { createBooking, isBooked, getBookingId, cancelBooking } = useBookings()
const { toggle, isSaved }  = useWishlist()

function isItemOwner(item) {
  if (!user.value || !item) return false
  const uid = String(user.value.userID || user.value.id)
  const oid = String(item.agency_id || item.provider_id || item.userId || item.owner_id || item.item_owner_id || '')
  return oid !== '' && oid === uid
}



const FALLBACK_IMGS = {
  dest:    'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=400&q=80',
  package: 'https://images.unsplash.com/photo-1530521954074-e64f6810b32d?w=400&q=80',
  service: 'https://images.unsplash.com/photo-1542314831-c6a4d14eff4c?w=400&q=80',
  offer:   'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=400&q=80',
}

const CATEGORY_LABELS = {
  dest: 'Destination', package: 'Package', service: 'Service', offer: 'Special Offer',
}

// ── Categories (includes offers now) ─────────────────────────────────────
const categories = [
  { value: 'all',     label: 'All',          icon: '🌐' },
  { value: 'dest',    label: 'Destinations', icon: '📍' },
  { value: 'package', label: 'Packages',     icon: '✈️' },
  { value: 'service', label: 'Services',     icon: '🛎️' },
  { value: 'offer',   label: 'Offers',       icon: '🔥' },
]

const heroStats = [
  { icon: '🌍', value: '2,000+', label: 'listings'     },
  { icon: '📍', value: '1,200+', label: 'destinations' },
  { icon: '⭐', value: '4.8',    label: 'avg rating'   },
]

const breadcrumbs = computed(() => {
  const base = [{ label: 'Home', to: '/' }, { label: 'Search', to: '/search' }]
  if (activeCategory.value !== 'all') {
    const cat = categories.find(c => c.value === activeCategory.value)
    if (cat) base.push({ label: cat.label })
  }
  return base
})

// ── State ─────────────────────────────────────────────────────────────────
const query             = ref(route.query.q || '')
const activeCategory    = ref('all')
const sortBy            = ref('recommended')
const viewMode          = ref('grid')
const loading           = ref(false)
const fetchError        = ref(null)
const page              = ref(1)
const perPage           = 9
const mobileSidebarOpen = ref(false)
const bookingOpen       = ref(false)
const offerModalOpen     = ref(false)
const selectedItem      = ref(null)
const selectedOffer     = ref(null)


const allResults = ref([])

function normalizeDestination(d) {
  return {
    id: d.id,
    category: 'dest',
    name: d.name,
    title: d.name,
    desc: d.description || '',
    img: d.image,
    price: d.price || 0,
    from: d.price || 0,
    rating: d.rating || 4.5,
    reviews: d.reviews || 0,
    type: d.type || 'City',
    agency_id: d.agency_id || null,
    provider_id: d.provider_id || null,
  }
}

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
    id: p.id,
    category: 'package',
    title: p.title,
    agency: p.agency_name || p.agency || 'Agency',
    desc: p.description || p.desc || '',
    img: p.img_url || p.img || 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg',
    price: Number(p.price || 0),
    rating: Number(p.rating || 4.5),
    reviews: Number(p.review_count || p.reviews || 0),
    duration: calcDur || p.duration_days || p.duration || 0,
    type: p.type || 'Adventure',
    spots: Number(p.spots_available || p.spots || 0),
    activeOffer: offer,
    startDate,
    endDate,
    agency_id: p.agency_id || p.userId || p.owner_id || null,
    includes: p.includes ? (typeof p.includes === 'string' ? JSON.parse(p.includes) : p.includes) : [],
  }
}

function normalizeService(s) {
  return {
    id: s.id,
    category: 'service',
    title: s.title,
    provider: s.provider_name || 'Provider',
    desc: s.description || '',
    img: null, // ✅ REMOVED image support for services (use icons only)
    icon: s.icon || '🛎️',
    iconBg: s.icon_bg || 'svc-icon-teal',
    price: Number(s.price || 0),
    unit: s.price_unit || 'trip',
    rating: Number(s.rating || 4.5),
    reviews: Number(s.review_count || 0),
    type: s.type || 'Transport',
    availability: !!Number(s.is_available),
    startDate: s.start_date || s.startDate,
    endDate: s.end_date || s.endDate,
    activeOffer: (s.active_offer_id || s.activeOffer?.id) ? {
      id:        s.active_offer_id       || s.activeOffer?.id,
      discount:  s.active_offer_discount || s.activeOffer?.discount,
      startDate: s.active_offer_start    || s.activeOffer?.startDate,
      endDate:   s.active_offer_end      || s.activeOffer?.endDate,
      title:     s.active_offer_title    || s.activeOffer?.title
    } : null,
    provider_id: s.provider_id || s.userId || s.owner_id || null,
  }
}

const filters = ref({
  priceMin: null, priceMax: null, durations: [], types: [], minRating: null,
})
const advancedFilters = ref({
  languages: [], groupSizes: [], months: [], difficulty: null,
  accommodations: [], perks: [], minReviews: 0, instantConfirmation: false,
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
  query.value = ''
  page.value  = 1
  runSearch()
}

// ── Fetch from API ────────────────────────────────────────────────────────
async function runSearch() {
  loading.value = true
  fetchError.value = null

  try {
    const [destRes, pkgRes, svcRes] = await Promise.all([
      fetch('/arrivo-website/backend/api/v1/listings.php'),
      fetch('/arrivo-website/backend/api/v1/packages.php'),
      fetch('/arrivo-website/backend/api/v1/services.php'),
    ])

    const destData = await destRes.json()
    const pkgData  = await pkgRes.json()
    const svcData  = await svcRes.json()

    // 1. Merge Destinations (Deduplicate by ID or Name)
    const dbDests = (destData.listings || []).map(normalizeDestination)
    const demoDests = (demoDestinations || []).map(d => ({ ...d, category: 'dest' }))

    const finalDests = [...demoDests]
    dbDests.forEach(dbItem => {
      const dbName = String(dbItem.name || '').toLowerCase().trim()
      const exists = finalDests.find(d => 
        d.id === dbItem.id || 
        String(d.name || '').toLowerCase().trim() === dbName
      )
      if (!exists) {
        finalDests.push(dbItem)
      } else {
        // Merge: demo wins
        const idx = finalDests.findIndex(d => 
          d.id === dbItem.id || 
          String(d.name || '').toLowerCase().trim() === dbName
        )
        finalDests[idx] = { ...dbItem, ...finalDests[idx] }
      }
    })

    // 2. Merge Packages (Deduplicate by ID or Title)
    const dbPkgs = (pkgData.packages || []).map(normalizePackage)
    const demoPkgs = (demoPackages || []).map(p => normalizePackage({ ...p, category: 'package' }))

    const finalPkgs = [...demoPkgs]
    dbPkgs.forEach(dbItem => {
      const dbTitle = String(dbItem.title || '').toLowerCase().trim()
      const exists = finalPkgs.find(p => 
        p.id === dbItem.id || 
        String(p.title || '').toLowerCase().trim() === dbTitle
      )
      if (!exists) {
        finalPkgs.push(dbItem)
      } else {
        const idx = finalPkgs.findIndex(p => p.id === dbItem.id || String(p.title || '').toLowerCase().trim() === dbTitle)
        finalPkgs[idx] = {
          ...dbItem,
          ...finalPkgs[idx],
          // Prioritize DB for offers and ownership
          activeOffer: dbItem.activeOffer || finalPkgs[idx].activeOffer || null,
          agency_id:   dbItem.agency_id   ?? finalPkgs[idx].agency_id   ?? null,
          // Keep the demo's recalculated duration if it's longer (usually more accurate for demo)
          duration:    Math.max(dbItem.duration || 0, finalPkgs[idx].duration || 0)
        }
      }
    })

    // 3. Merge Services (Deduplicate by ID or Title)
    // Filter out old services that use images (user request)
    const dbSvcs = (svcData.services || []).map(normalizeService)
    
    const demoSvcs = (demoServices || []).map(s => normalizeService({ ...s, category: 'service' }))

    const finalSvcs = [...demoSvcs]
    dbSvcs.forEach(dbItem => {
      const dbTitle = String(dbItem.title || '').toLowerCase().trim()
      const exists = finalSvcs.find(s => 
        s.id === dbItem.id || 
        String(s.title || '').toLowerCase().trim() === dbTitle
      )
      if (!exists) {
        finalSvcs.push(dbItem)
      } else {
        const idx = finalSvcs.findIndex(s => 
          s.id === dbItem.id || 
          String(s.title || '').toLowerCase().trim() === dbTitle
        )
        finalSvcs[idx] = { 
          ...dbItem, 
          ...finalSvcs[idx],
          activeOffer: dbItem.activeOffer || finalSvcs[idx].activeOffer || null,
          provider_id: dbItem.provider_id || finalSvcs[idx].provider_id || null
        }
      }
    })

    // 4. Offers
    const offers = useOffers().activeOffers.value
      .map(o => ({
        ...o,
        id: o.offerID,
        category: 'offer',
        title: o.title,
        desc: o.description,
        tag: (o.discount_pct || o.discount) + '% OFF',
        agency_id: o.agency_id || o.userId || o.owner_id || null,
        provider_id: o.provider_id || null,
        img: o.img || '/arrivo-website/src/assets/images/placeholder-deal.jpg',
        price: o.price || 0,
        rating: 5.0,
        reviews: 0,
        categoryLabel: o.source === 'collab' ? 'Joint Offer' : 'Special Offer',
        ctaLabel: 'View Deal',
        priceLabel: 'Starting from'
      }))

    allResults.value = [
      ...finalDests,
      ...finalPkgs,
      ...finalSvcs,
      ...offers
    ]

  } catch (err) {
    console.error(err)
    fetchError.value = 'Failed to load data'
    allResults.value = []
  } finally {
    loading.value = false
  }
}
    

// ── Watchers ──────────────────────────────────────────────────────────────
let searchTimeout = null
watch(query, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(runSearch, 400)
})

watch(activeCategory, () => {
  page.value = 1
  runSearch()
})

onMounted(runSearch)

// ── Actions ───────────────────────────────────────────────────────────────
function toggleWishlist(category, id) {
  // Normalize category names for the shared wishlist if necessary
  const typeMap = { dest: 'destination', package: 'package', service: 'service', offer: 'offer' }
  const type = typeMap[category] || category
  toggle(type, id)
}

function goToDetail(item) {
  if (item.category === 'dest')    return router.push('/destinations/' + item.id)
  if (item.category === 'package') return router.push('/packages/'     + item.id)
  if (item.category === 'service') return router.push('/services/'     + item.id)
  if (item.category === 'offer')   return router.push('/deals')
}

async function handleBook(item) {
  const typeMap = { dest: 'destination', package: 'package', service: 'service', offer: 'offer' }
  const type = typeMap[item.category] || item.category
  const linkedId = item.activeOffer?.id || null

  if (isBooked(type, item.id, linkedId)) {
    const bid = getBookingId(type, item.id, linkedId)
    if (bid) {
      const res = await cancelBooking(bid)
      if (res.ok) alert('Booking cancelled successfully.')
      else alert('Failed to cancel: ' + res.error)
    }
    return
  }

  if (item.category === 'offer') {
    selectedOffer.value = item
    offerModalOpen.value = true
    return
  }
  selectedItem.value = item
  bookingOpen.value  = true
}

async function handleCancel(item) {
  const typeMap = { dest: 'destination', package: 'package', service: 'service', offer: 'offer' }
  const type = typeMap[item.category] || item.category
  const linkedId = item.activeOffer?.id || null
  const bid = getBookingId(type, item.id, linkedId)
  if (!bid) return
  
  if (!confirm('Are you sure you want to cancel this booking?')) return
  const res = await cancelBooking(bid)
  if (res.ok) alert('Booking cancelled successfully.')
  else alert('Failed to cancel: ' + res.error)
}

async function handleBookingSubmit(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  const item = selectedItem.value
  const isOffer = !!item.activeOffer
  let finalPrice = item.price
  if (isOffer) {
    finalPrice = Math.round(finalPrice * (1 - item.activeOffer.discount / 100))
  }

  const result = await createBooking({
    user_id:  user.value ? (user.value.userID || user.value.id) : null,
    type:     isOffer ? 'offer' : (item.category === 'dest' ? 'destination' : item.category),
    item_id:  isOffer ? item.activeOffer.id : item.id,
    title:    isOffer ? item.activeOffer.title : item.title,
    price:    finalPrice,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
  })
  if (result.ok) {
    bookingOpen.value = false
    alert(isOffer ? 'Offer booked successfully!' : 'Booked successfully!')
  } else {
    alert('Failed to book: ' + result.error)
  }
}

// ── Client-side filtering + sorting ──────────────────────────────────────
function matchesDuration(item) {
  if (!filters.value.durations.length) return true
  if (!item.duration) return false
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
  if (af.minReviews > 0 && item.reviews != null)
    if (item.reviews < af.minReviews) return false
  if (af.instantConfirmation && !item.instantConfirmation) return false
  return true
}

const allFiltered = computed(() => {
  let r = [...allResults.value]

  // Category (also filtered server-side, but belt-and-suspenders)
  if (activeCategory.value !== 'all')
    r = r.filter(i => i.category === activeCategory.value)

  // Text search (client-side refinement for instant feel)
  if (query.value.trim()) {
    const q = query.value.toLowerCase()
    r = r.filter(i =>
      i.title?.toLowerCase().includes(q) ||
      i.desc?.toLowerCase().includes(q)
    )
  }

  // Price
  if (filters.value.priceMin != null)
    r = r.filter(i => Number(i.price) >= Number(filters.value.priceMin))
  if (filters.value.priceMax != null)
    r = r.filter(i => Number(i.price) <= Number(filters.value.priceMax))

  // Rating
  if (filters.value.minRating)
    r = r.filter(i => Number(i.rating) >= Number(filters.value.minRating))

  // Type
  if (filters.value.types.length)
    r = r.filter(i => i.type && filters.value.types.includes(i.type))

  r = r.filter(matchesDuration)
  r = r.filter(matchesAdvanced)

  // Sort
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


.search-page { min-height: 100vh; display: flex; flex-direction: column; background: var(--gray-50); }
.search-body { display: grid; grid-template-columns: 280px 1fr; flex: 1; align-items: flex-start; }
.results-area { padding: 28px 32px; min-height: 80vh; }
.results-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
  align-items: stretch;
}
.results-list { display: flex; flex-direction: column; gap: 18px; }

.skeleton-card { background: var(--white); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); }
.skeleton-img  { height: 200px; background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton-body { padding: 20px; }
.skeleton-line { height: 14px; border-radius: 7px; margin-bottom: 10px; background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton-line--short { width: 40%; }
.skeleton-line--med   { width: 65%; }
@keyframes shimmer { to { background-position: -200% 0; } }

.empty-state        { text-align: center; padding: 80px 20px; }
.empty-state__icon  { font-size: 3.5rem; margin-bottom: 16px; }
.empty-state__title { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; margin-bottom: 10px; }
.result-detail        { font-size: .8rem; color: var(--gray-600); }
.result-detail.scarce { color: var(--coral); font-weight: 600; }
.result-detail--offer { color: var(--coral); font-weight: 700; }

.sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 60; }
.backdrop-fade-enter-active, .backdrop-fade-leave-active { transition: opacity .25s ease; }
.backdrop-fade-enter-from, .backdrop-fade-leave-to { opacity: 0; }

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
