/**
 * useOffers — single source of truth for all offers across the app.
 *
 * Homepage HotOffers, the /deals page, and the Agency Dashboard
 * all read from and write to this one store.
 */
import { ref, computed } from 'vue'

// ── Seed data (your existing homepage specialOffers) ───────────────────────
const _offers = ref([
  {
    offerID:     1,
    discount:    30,
    title:       'Maldives Escape',
    startDate:   'Jun 1',
    endDate:     'Jun 30',
    description: 'Overwater bungalows, reef snorkeling & more.',
    source:      'manual',
    active:      true,
    type:        'Beach',
  },
  {
    offerID:     2,
    discount:    20,
    title:       'Sahara Desert Tour',
    startDate:   'Jul 10',
    endDate:     'Aug 10',
    description: 'Camel treks and starlit desert camps.',
    source:      'manual',
    active:      true,
    type:        'Adventure',
  },
  {
    offerID:     3,
    discount:    25,
    title:       'Japan Cherry Blossom',
    startDate:   'Mar 20',
    endDate:     'Apr 15',
    description: 'Tokyo, Kyoto & Osaka in bloom season.',
    source:      'manual',
    active:      true,
    type:        'Cultural',
  },
  {
    offerID:     4,
    discount:    15,
    title:       'Greek Island Hop',
    startDate:   'May 1',
    endDate:     'Sep 30',
    description: 'Santorini, Mykonos, and Crete by ferry.',
    source:      'manual',
    active:      true,
    type:        'Beach',
  },
  {
    offerID:     5,
    discount:    35,
    title:       'Patagonia Trekking',
    startDate:   'Nov 1',
    endDate:     'Feb 28',
    description: 'Torres del Paine in peak summer season.',
    source:      'manual',
    active:      true,
    type:        'Adventure',
  },
])

export function useOffers() {
  // ── Read ───────────────────────────────────────────────────────────────
  const allOffers    = computed(() => _offers.value)
  const activeOffers = computed(() => _offers.value.filter(o => o.active !== false))
  const collabOffers = computed(() => _offers.value.filter(o => o.source === 'collab'))
  const manualOffers = computed(() => _offers.value.filter(o => o.source !== 'collab'))

  // ── Write ──────────────────────────────────────────────────────────────
  function addOffer(offer) {
    _offers.value.unshift({
      ...offer,
      offerID: offer.offerID ?? Date.now(),
      active:  offer.active  ?? true,
      source:  offer.source  ?? 'manual',
    })
  }

  function updateOffer(updated) {
    const idx = _offers.value.findIndex(o => o.offerID === updated.offerID)
    if (idx !== -1) _offers.value[idx] = { ..._offers.value[idx], ...updated }
  }

  function deleteOffer(offerID) {
    _offers.value = _offers.value.filter(o => o.offerID !== offerID)
  }

  // Unified save handler — add or update
  function saveOffer(offer) {
    const exists = _offers.value.some(o => o.offerID === offer.offerID)
    exists ? updateOffer(offer) : addOffer(offer)
  }

  return {
    allOffers,
    activeOffers,
    collabOffers,
    manualOffers,
    addOffer,
    updateOffer,
    deleteOffer,
    saveOffer,
  }
}