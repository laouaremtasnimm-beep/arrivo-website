/**
 * useOffers — single source of truth for all offers across the app.
 *
 * Offers are persisted to the DB via /api/v1/offers.php.
 * The module-level _offers ref is shared between the dashboard and the /deals
 * page so that a newly-created offer is visible on both instantly.
 */
import { ref, computed } from 'vue'

import { destinations as demoDestinations, packages as demoPackages, services as demoServices, offers as demoOffers } from '@/data/content.js'

const API = '/arrivo-website/backend/api/v1'

// ── Module-level state (singleton) ───────────────────────────────────────────
const _offers = ref([...demoOffers])
const _loading = ref(false)
const _loaded = ref(false)

function toArray(value) {
  if (Array.isArray(value)) return value
  if (typeof value === 'string' && value.trim()) {
    try {
      const parsed = JSON.parse(value)
      return Array.isArray(parsed) ? parsed : []
    } catch {
      return []
    }
  }
  return []
}

function normalizeOffer(dbOff) {
  const packageIds = toArray(dbOff.packageIds ?? dbOff.package_ids)
    .map(Number)
    .filter(Boolean)
  const services = toArray(dbOff.services)
  const serviceIds = toArray(dbOff.service_ids)
    .map(Number)
    .filter(Boolean)
  const image = dbOff.img || dbOff.img_url || dbOff.package_img_url || dbOff.service_img_url || null

  return {
    ...dbOff,
    offerID: dbOff.offerID ?? dbOff.id,
    discount: dbOff.discount ?? dbOff.discount_pct,
    discount_pct: dbOff.discount_pct ?? dbOff.discount,
    startDate: dbOff.startDate ?? dbOff.start_date,
    endDate: dbOff.endDate ?? dbOff.end_date,
    description: dbOff.description ?? dbOff.desc ?? '',
    source: dbOff.source ?? 'manual',
    type: dbOff.type ?? dbOff.offer_type ?? dbOff.offerType,
    offerType: dbOff.offerType ?? dbOff.offer_type ?? dbOff.type,
    owner_id: Number(dbOff.owner_id ?? dbOff.agency_id ?? dbOff.userId ?? 0) || null,
    agency_id: dbOff.agency_id != null ? Number(dbOff.agency_id) : null,
    provider_id: dbOff.provider_id != null ? Number(dbOff.provider_id) : null,
    package_id: dbOff.package_id != null ? Number(dbOff.package_id) : (packageIds[0] ?? null),
    packageIds,
    service_id: dbOff.service_id != null ? Number(dbOff.service_id) : (serviceIds[0] ?? null),
    service_ids: serviceIds,
    services,
    img: image,
    img_url: image,
    price: Number(dbOff.price ?? dbOff.package_price ?? services[0]?.price ?? 0) || 0,
    partnerName: dbOff.partnerName ?? dbOff.provider_name ?? dbOff.partner_company ?? null,
    partnerColor: dbOff.partnerColor ?? '#2EC4B6',
    active: dbOff.active ?? (dbOff.is_active === 1 || dbOff.is_active === '1'),
  }
}

// Auto-fetch all active public offers the moment the module is first imported.
async function _bootstrap() {
  if (_loaded.value || _loading.value) return
  _loading.value = true
  try {
    const res = await fetch(`${API}/offers.php`)
    const data = await res.json()
    if (data.offers) {
      // Merge: avoid duplicates if demo offers were somehow in the DB
      data.offers.forEach(dbOff => {
        const mapped = normalizeOffer(dbOff)
        if (!_offers.value.some(o => o.offerID === mapped.offerID)) {
          _offers.value.push(mapped)
        }
      })
    }
    _loaded.value = true
  } catch (e) {
    console.warn('[useOffers] Failed to load offers:', e)
  } finally {
    _loading.value = false
  }
}
_bootstrap()

// ── Composable ────────────────────────────────────────────────────────────────
export function useOffers() {
  // ── Reads ──────────────────────────────────────────────────────────────────
  const allOffers = computed(() => _offers.value)
  const activeOffers = computed(() => _offers.value.filter(o => o.active !== false))
  const collabOffers = computed(() => _offers.value.filter(o => o.source === 'collab'))
  const manualOffers = computed(() => _offers.value.filter(o => o.source !== 'collab'))

  // Returns only this user's non-collab offers (for the dashboard panel)
  function userOffers(userId) {
    return computed(() =>
      _offers.value.filter(o => o.source !== 'collab' && o.owner_id === Number(userId))
    )
  }
  // ── Writes — in-memory state mutations ────────────────────────────────────
  function addOffer(offer) {
    _offers.value.unshift(normalizeOffer({
      ...offer,
      offerID: offer.offerID ?? Date.now(),
      active: offer.active ?? true,
      source: offer.source ?? 'manual',
    }))
  }

  function updateOffer(updated) {
    const idx = _offers.value.findIndex(o => o.offerID === updated.offerID)
    if (idx !== -1) _offers.value[idx] = { ..._offers.value[idx], ...updated }
  }

  function deleteOffer(offerID) {
    _offers.value = _offers.value.filter(o => o.offerID !== offerID)
  }

  // ── Writes — persisted to the DB ──────────────────────────────────────────
  async function saveOfferToDB(offer) {
    try {
      const isNew = !offer.offerID || typeof offer.offerID === 'number' && offer.offerID > 1e10
      // offerID > 1e10 means it was generated by Date.now() — not a real DB id

      if (isNew) {
        const res = await fetch(`${API}/offers.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(offer),
        })
        const data = await res.json()
        if (!res.ok) throw new Error(data.error || 'Failed to create offer')
        // Add to local store with the real DB id
        addOffer({ ...offer, offerID: data.offer_id })
      } else {
        const res = await fetch(`${API}/offers.php`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ...offer, id: offer.offerID }),
        })
        const data = await res.json()
        if (!res.ok) throw new Error(data.error || 'Failed to update offer')
        updateOffer(offer)
      }
    } catch (e) {
      console.error('[useOffers] saveOfferToDB error:', e)
    }
  }

  async function deleteOfferFromDB(offerID) {
    try {
      const res = await fetch(`${API}/offers.php`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: offerID }),
      })
      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.error || 'Failed to delete offer')
      }
      deleteOffer(offerID)
    } catch (e) {
      console.error('[useOffers] deleteOfferFromDB error:', e)
    }
  }

  // Legacy alias kept for collab flows that call saveOffer directly
  function saveOffer(offer) {
    const exists = _offers.value.some(o => o.offerID === offer.offerID)
    exists ? updateOffer(offer) : addOffer(offer)
  }

  return {
    allOffers,
    activeOffers,
    collabOffers,
    manualOffers,
    userOffers,
    loading: _loading,
    addOffer,
    updateOffer,
    deleteOffer,
    deleteOfferFromDB,
    saveOffer,
    saveOfferToDB,
  }
}
