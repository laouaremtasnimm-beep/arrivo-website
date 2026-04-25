/**
 * useListPage — shared composable for Destinations, Packages, Services pages.
 *
 * Handles:
 *  - text search query  (synced with ?q= route param)
 *  - active category tab
 *  - sidebar filters    (price, rating, type, duration) — pre-filled from URL params
 *  - advanced filters   (language, group size, months, accommodation, perks, reviews, instant)
 *  - sorting            (pre-filled from ?sortBy=)
 *  - pagination
 *  - loading simulation
 *  - wishlist           ← powered by useWishlist (shared, persisted)
 *
 * URL params recognised on mount (all optional):
 *   ?q=         text query
 *   ?sortBy=    recommended | price_asc | price_desc | rating | popular
 *   ?priceMin=  number
 *   ?priceMax=  number
 *   ?minRating= number
 *   ?durations= comma-separated  e.g. "1-3,4-7"
 *   ?types=     comma-separated  e.g. "Beach,Adventure"
 *
 * @param {Ref<Array>} allItems  — the full data array for this page
 * @param {string}     itemType  — 'destination' | 'package' | 'service'
 * @param {object}     options   — { perPage: number }
 */

import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useWishlist } from '@/composables/useWishlist.js'

export function useListPage(allItems, itemType, opts = {}) {
  const route = useRoute()
  const router = useRouter()

  const perPage = opts.perPage ?? 9

  // ── Helpers to parse URL params ────────────────────────────────────────
  function qNum(key) {
    const v = route.query[key]
    return v != null && v !== '' ? +v : null
  }
  function qStr(key, fallback = '') {
    return route.query[key] ?? fallback
  }
  function qArr(key) {
    const v = route.query[key]
    if (!v) return []
    return String(v).split(',').map(s => s.trim()).filter(Boolean)
  }

  // ── State — initialised from URL params ────────────────────────────────
  const query = ref(qStr('q'))
  const sortBy = ref(qStr('sortBy', 'recommended'))
  const activeCategory = ref('all')
  const viewMode = ref('grid')
  const loading = ref(false)
  const page = ref(1)

  const filters = ref({
    priceMin: qNum('priceMin'),
    priceMax: qNum('priceMax'),
    minRating: qNum('minRating'),
    durations: qArr('durations'),
    types: qArr('types'),
  })

  // ── Advanced filters ───────────────────────────────────────────────────
  const advancedFilters = ref({
    languages: [],
    groupSizes: [],
    months: [],
    difficulty: null,
    accommodations: [],
    perks: [],
    minReviews: 0,
    instantConfirmation: false,
  })

  // ── Sync state → URL (replace so back-button still works cleanly) ──────
  function syncURL() {
    const q = {}
    if (query.value?.trim()) q.q = query.value.trim()
    if (sortBy.value !== 'recommended') q.sortBy = sortBy.value
    if (filters.value.priceMin != null) q.priceMin = filters.value.priceMin
    if (filters.value.priceMax != null) q.priceMax = filters.value.priceMax
    if (filters.value.minRating) q.minRating = filters.value.minRating
    if (filters.value.durations?.length) q.durations = filters.value.durations.join(',')
    if (filters.value.types?.length) q.types = filters.value.types.join(',')

    router.replace({ query: Object.keys(q).length ? q : undefined })
  }

  watch([query, sortBy], syncURL)
  watch(filters, syncURL, { deep: true })

  // ── Filter counts ──────────────────────────────────────────────────────
  const activeFilterCount = computed(() => {
    let c = 0
    if (filters.value.priceMin) c++
    if (filters.value.priceMax) c++
    if (filters.value.durations.length) c += filters.value.durations.length
    if (filters.value.types.length) c += filters.value.types.length
    if (filters.value.minRating) c++
    return c
  })

  // Count of active advanced filters — used for the badge on the trigger button
  const advancedFilterCount = computed(() => {
    const af = advancedFilters.value
    return (
      af.languages.length +
      af.groupSizes.length +
      af.months.length +
      af.accommodations.length +
      af.perks.length +
      (af.difficulty ? 1 : 0) +
      (af.minReviews > 0 ? 1 : 0) +
      (af.instantConfirmation ? 1 : 0)
    )
  })

  function resetFilters() {
    filters.value = { priceMin: null, priceMax: null, durations: [], types: [], minRating: null }
    advancedFilters.value = {
      languages: [], groupSizes: [], months: [], difficulty: null,
      accommodations: [], perks: [], minReviews: 0, instantConfirmation: false,
    }
    activeCategory.value = 'all'
    query.value = ''
    page.value = 1
  }

  // ── Loading simulation ─────────────────────────────────────────────────
  function runSearch() {
    loading.value = true
    page.value = 1
    setTimeout(() => { loading.value = false }, 700)
  }

  // Auto-trigger search if page was opened with a query/filters (e.g. from SearchPage redirect)
  onMounted(() => {
    if (query.value || activeFilterCount.value > 0) runSearch()
  })

  watch(activeCategory, () => { page.value = 1 })
  watch(filters, () => { page.value = 1 }, { deep: true })
  watch(advancedFilters, () => { page.value = 1 }, { deep: true })

  // ── Duration helper ────────────────────────────────────────────────────
  function matchesDuration(item) {
    if (!filters.value.durations.length || !item.duration) return true
    return filters.value.durations.some(d => {
      if (d === '1-3') return item.duration >= 1 && item.duration <= 3
      if (d === '4-7') return item.duration >= 4 && item.duration <= 7
      if (d === '8-14') return item.duration >= 8 && item.duration <= 14
      if (d === '15+') return item.duration >= 15
      return true
    })
  }

  // ── Advanced filter helper ─────────────────────────────────────────────
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

  // ── Filtered + sorted results ──────────────────────────────────────────
  const allFiltered = computed(() => {
    let r = [...allItems.value]

    // Category tab (e.g. "Beach", "Adventure" on the list pages)
    if (activeCategory.value !== 'all')
      r = r.filter(i => i.type?.toLowerCase() === activeCategory.value.toLowerCase())

    // Text search
    if (query.value.trim()) {
      const q = query.value.toLowerCase()
      r = r.filter(i =>
        i.title?.toLowerCase().includes(q) ||
        i.name?.toLowerCase().includes(q) ||
        i.desc?.toLowerCase().includes(q) ||
        i.description?.toLowerCase().includes(q)
      )
    }

    // Price
    if (filters.value.priceMin != null)
      r = r.filter(i => (i.price ?? i.from ?? 0) >= filters.value.priceMin)
    if (filters.value.priceMax != null)
      r = r.filter(i => (i.price ?? i.from ?? 0) <= filters.value.priceMax)

    // Rating
    if (filters.value.minRating)
      r = r.filter(i => i.rating >= filters.value.minRating)

    // Types
    if (filters.value.types.length)
      r = r.filter(i => filters.value.types.includes(i.type))

    // Duration
    r = r.filter(matchesDuration)

    // Advanced filters
    r = r.filter(matchesAdvanced)

    // Sort
    if (sortBy.value === 'price_asc')
      r.sort((a, b) => (a.price ?? a.from ?? 0) - (b.price ?? b.from ?? 0))
    if (sortBy.value === 'price_desc')
      r.sort((a, b) => (b.price ?? b.from ?? 0) - (a.price ?? a.from ?? 0))
    if (sortBy.value === 'rating')
      r.sort((a, b) => b.rating - a.rating)
    if (sortBy.value === 'popular')
      r.sort((a, b) => (b.reviews ?? 0) - (a.reviews ?? 0))

    return r
  })

  const totalPages = computed(() =>
    Math.max(1, Math.ceil(allFiltered.value.length / perPage))
  )

  const pagedResults = computed(() => {
    const start = (page.value - 1) * perPage
    return allFiltered.value.slice(start, start + perPage)
  })

  watch(allFiltered, () => {
    if (page.value > totalPages.value) page.value = 1
  })

  // ── Wishlist ───────────────────────────────────────────────────────────
  const { isSaved, toggle } = useWishlist()

  function isItemSaved(item) {
    return isSaved.value(itemType, item.id)
  }

  function toggleWishlist(item) {
    const wasAdded = toggle(itemType, item.id)
    if (wasAdded) router.push('/wishlist')
  }

  return {
    // state
    query, activeCategory, sortBy, viewMode,
    loading, page, filters, advancedFilters,
    // computed
    activeFilterCount, advancedFilterCount, allFiltered, totalPages, pagedResults,
    // methods
    resetFilters, runSearch,
    // wishlist
    isItemSaved, toggleWishlist,
  }
}