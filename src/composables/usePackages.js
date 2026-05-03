import { ref, computed } from 'vue'
import { packages as demoPackages } from '@/data/content.js'

const API = '/arrivo-website/backend/api/v1'

const _packages = ref([...demoPackages])
const _loading = ref(false)
const _loaded = ref(false)

async function _bootstrap() {
  if (_loaded.value || _loading.value) return
  _loading.value = true
  try {
    const res = await fetch(`${API}/packages.php`)
    const data = await res.json()
    if (data.packages) {
      // Normalize and Merge
      data.packages.forEach(dbItem => {
        const mapped = {
          id:         dbItem.id,
          title:      dbItem.title,
          agency:     dbItem.agency_name  ?? dbItem.agency    ?? 'Agency',
          img:        dbItem.img_url || dbItem.img || 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg',
          img_url:    dbItem.img_url,
          type:       dbItem.type         ?? 'Adventure',
          duration:   dbItem.duration_days ?? dbItem.duration ?? 1,
          rating:     dbItem.rating       ?? 4.5,
          reviews:    dbItem.review_count ?? dbItem.reviews   ?? 0,
          spots:      dbItem.spots_available ?? dbItem.spots  ?? 10,
          price:      dbItem.price,
          desc:       dbItem.description  ?? dbItem.desc      ?? '',
          agency_id:  dbItem.agency_id    ?? null,
          includes:   typeof dbItem.includes === 'string'
                        ? JSON.parse(dbItem.includes || '[]')
                        : (dbItem.includes ?? []),
          activeOffer: dbItem.active_offer_id ? {
            id: dbItem.active_offer_id,
            discount: dbItem.active_offer_discount,
            startDate: dbItem.active_offer_start,
            endDate: dbItem.active_offer_end,
            title: dbItem.active_offer_title
          } : null,
        }
        
        const existsIdx = _packages.value.findIndex(p => p.id === mapped.id)
        if (existsIdx === -1) {
          _packages.value.push(mapped)
        } else {
          _packages.value[existsIdx] = { ..._packages.value[existsIdx], ...mapped }
        }
      })
    }
    _loaded.value = true
  } catch (e) {
    console.warn('[usePackages] Failed to load packages:', e)
  } finally {
    _loading.value = false
  }
}

// Auto-bootstrap
_bootstrap()

export function usePackages() {
  return {
    allPackages: computed(() => _packages.value),
    loading: _loading,
    refresh: _bootstrap
  }
}
