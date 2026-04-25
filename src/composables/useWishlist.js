/**
 * useWishlist.js  ─  src/composables/useWishlist.js
 *
 * Singleton wishlist — module-level refs mean every component that calls
 * useWishlist() reads & writes the SAME reactive arrays.
 *
 * ID ranges (from content.js):
 *   destinations  →  1  – 100
 *   packages      →  101 – 200
 *   services      →  201 – 999
 */

import { ref, computed } from 'vue'

const KEYS = {
  destinations: 'voyago_wish_dest',
  packages:     'voyago_wish_pkg',
  services:     'voyago_wish_svc',
}

function load(key) {
  try { return JSON.parse(localStorage.getItem(key) || '[]') } catch { return [] }
}
function persist(key, ids) {
  localStorage.setItem(key, JSON.stringify(ids))
}

// Module-level — created once, shared across all components
const destIds = ref(load(KEYS.destinations))
const pkgIds  = ref(load(KEYS.packages))
const svcIds  = ref(load(KEYS.services))

// Flat merged list — used for :saved="wishlist.includes(item.id)" checks
const wishlist = computed(() => [...destIds.value, ...pkgIds.value, ...svcIds.value])

function bucketOf(id) {
  if (id >= 1   && id <= 100) return 'destinations'
  if (id >= 101 && id <= 200) return 'packages'
  return 'services'
}
function idsRefFor(type) {
  if (type === 'destinations') return destIds
  if (type === 'packages')     return pkgIds
  return svcIds
}

function toggleWishlist(id) {
  const type   = bucketOf(id)
  const idsRef = idsRefFor(type)
  if (idsRef.value.includes(id)) {
    idsRef.value = idsRef.value.filter(x => x !== id)
  } else {
    idsRef.value = [...idsRef.value, id]
  }
  persist(KEYS[type], idsRef.value)
}

function clearCategory(type) {
  idsRefFor(type).value = []
  persist(KEYS[type], [])
}

export function useWishlist() {
  return { wishlist, destIds, pkgIds, svcIds, toggleWishlist, clearCategory }
}