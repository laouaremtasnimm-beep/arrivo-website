/**
 * useWishlist.js  —  src/composables/useWishlist.js
 *
 * Ground-up rewrite. The core bug in the previous version was that
 * destinations and packages share the same ID space (both 1–12), so
 * saving by numeric ID alone made it impossible to tell them apart.
 *
 * This version stores wishlist entries as { type, id } objects, where
 * type is one of: 'destination' | 'package' | 'service'.
 *
 * Public API (returned by useWishlist()):
 *   isSaved(type, id)   → Boolean  — is this item in the wishlist?
 *   toggle(type, id)    → Boolean  — toggles item; returns true if ADDED
 *   clearType(type)     → void     — removes all items of that type
 *   itemsOfType(type)   → Ref<{type,id}[]>  — reactive list for one type
 */

import { ref, computed } from 'vue'

const STORAGE_KEY = 'arrivo_wishlist_v2'

// One-time cleanup: remove old single-bucket keys from the previous buggy version
;['voyago_wish_dest', 'voyago_wish_pkg', 'voyago_wish_svc'].forEach(k => localStorage.removeItem(k))

/** Load the saved list from localStorage. Returns an array of {type, id}. */
function load() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
  } catch {
    return []
  }
}

/** Persist the current list to localStorage. */
function save(list) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(list))
}

// Module-level — one reactive array, shared across every component that calls useWishlist().
const entries = ref(load())

/**
 * Check whether a given (type, id) pair is currently wishlisted.
 */
const isSaved = computed(() => (type, id) =>
  entries.value.some(e => e.type === type && e.id === id)
)

/**
 * Toggle a (type, id) pair.
 * Returns true  → item was just ADDED
 * Returns false → item was just REMOVED
 */
function toggle(type, id) {
  const idx = entries.value.findIndex(e => e.type === type && e.id === id)
  if (idx !== -1) {
    // Remove
    entries.value = entries.value.filter((_, i) => i !== idx)
    save(entries.value)
    return false
  } else {
    // Add
    entries.value = [...entries.value, { type, id }]
    save(entries.value)
    return true
  }
}

/**
 * Remove all entries of a given type.
 */
function clearType(type) {
  entries.value = entries.value.filter(e => e.type !== type)
  save(entries.value)
}

/**
 * Return a computed ref containing only the entries for a specific type.
 */
function itemsOfType(type) {
  return computed(() => entries.value.filter(e => e.type === type))
}

export function useWishlist() {
  return {
    entries,
    isSaved,
    toggle,
    clearType,
    itemsOfType,
  }
}