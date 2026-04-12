/**
 * useListPage — shared composable for Destinations, Packages, Services pages.
 *
 * Handles:
 *  - text search query (synced with ?q= route param)
 *  - active category tab
 *  - sidebar filters (price, rating, type, duration)
 *  - sorting
 *  - pagination
 *  - loading simulation
 *  - wishlist
 */

import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export function useListPage(allItems, { perPage = 12 } = {}) {
  const route  = useRoute()
  const router = useRouter()

  // ── Search & category ──────────────────────────────────────────────────
  const query          = ref(route.query.q || '')
  const activeCategory = ref('all')
  const sortBy         = ref('recommended')
  const viewMode       = ref('grid')
  const loading        = ref(false)
  const page           = ref(1)

  // ── Sidebar filters ────────────────────────────────────────────────────
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
    query.value          = ''
    page.value           = 1
  }

  // ── Loading simulation ─────────────────────────────────────────────────
  function runSearch() {
    loading.value = true
    page.value    = 1
    setTimeout(() => { loading.value = false }, 700)
  }

  onMounted(() => { if (query.value) runSearch() })
  watch(activeCategory, () => { page.value = 1 })
  watch(filters, () => { page.value = 1 }, { deep: true })

  // ── Duration helper ────────────────────────────────────────────────────
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

  // ── Filtered + sorted results ──────────────────────────────────────────
  const allFiltered = computed(() => {
    let r = [...allItems.value]

    // Category
    if (activeCategory.value !== 'all')
      r = r.filter(i => i.type?.toLowerCase() === activeCategory.value.toLowerCase())

    // Text search
    if (query.value.trim()) {
      const q = query.value.toLowerCase()
      r = r.filter(i =>
        i.title?.toLowerCase().includes(q) ||
        i.name?.toLowerCase().includes(q)  ||
        i.desc?.toLowerCase().includes(q)  ||
        i.description?.toLowerCase().includes(q)
      )
    }

    // Price
    if (filters.value.priceMin != null) r = r.filter(i => (i.price ?? i.from) >= filters.value.priceMin)
    if (filters.value.priceMax != null) r = r.filter(i => (i.price ?? i.from) <= filters.value.priceMax)

    // Rating
    if (filters.value.minRating) r = r.filter(i => i.rating >= filters.value.minRating)

    // Types
    if (filters.value.types.length)
      r = r.filter(i => filters.value.types.includes(i.type))

    // Duration (packages only)
    r = r.filter(matchesDuration)

    // Sort
    if (sortBy.value === 'price_asc')  r.sort((a, b) => (a.price ?? a.from ?? 0) - (b.price ?? b.from ?? 0))
    if (sortBy.value === 'price_desc') r.sort((a, b) => (b.price ?? b.from ?? 0) - (a.price ?? a.from ?? 0))
    if (sortBy.value === 'rating')     r.sort((a, b) => b.rating - a.rating)
    if (sortBy.value === 'popular')    r.sort((a, b) => (b.reviews ?? 0) - (a.reviews ?? 0))

    return r
  })

  const totalPages = computed(() => Math.max(1, Math.ceil(allFiltered.value.length / perPage)))

  const pagedResults = computed(() => {
    const start = (page.value - 1) * perPage
    return allFiltered.value.slice(start, start + perPage)
  })

  watch(allFiltered, () => {
    if (page.value > totalPages.value) page.value = 1
  })

  // ── Wishlist ───────────────────────────────────────────────────────────
  const wishlist = ref([])
  function toggleWishlist(id) {
    const idx = wishlist.value.indexOf(id)
    idx === -1 ? wishlist.value.push(id) : wishlist.value.splice(idx, 1)
  }

  // ── Sync query to URL ──────────────────────────────────────────────────
  watch(query, (val) => {
    router.replace({ query: val ? { q: val } : {} })
  })

  return {
    // state
    query, activeCategory, sortBy, viewMode,
    loading, page, filters,
    // computed
    activeFilterCount, allFiltered, totalPages, pagedResults,
    // methods
    resetFilters, runSearch, toggleWishlist,
    wishlist,
  }
}
