import { ref, computed } from 'vue'
import { services as demoServices } from '@/data/content.js'

const API = '/arrivo-website/backend/api/v1'

const _services = ref([...demoServices])
const _loading = ref(false)
const _loaded = ref(false)

async function _bootstrap() {
  if (_loaded.value || _loading.value) return
  _loading.value = true
  try {
    const res = await fetch(`${API}/services.php`)
    const data = await res.json()
    if (data.services) {
      data.services.forEach(dbItem => {
        const mapped = {
          id:         dbItem.id,
          title:      dbItem.title,
          provider:   dbItem.provider_name ?? dbItem.provider ?? 'Provider',
          icon:       dbItem.icon ?? '🛎️',
          type:       dbItem.type ?? 'Service',
          price:      dbItem.price,
          unit:       dbItem.unit ?? 'trip',
          rating:     dbItem.rating ?? 4.8,
          reviews:    dbItem.review_count ?? 0,
          img:        dbItem.img_url || dbItem.img,
          img_url:    dbItem.img_url,
          provider_id: dbItem.provider_id,
        }
        
        const existsIdx = _services.value.findIndex(s => s.id === mapped.id)
        if (existsIdx === -1) {
          _services.value.push(mapped)
        } else {
          _services.value[existsIdx] = { ..._services.value[existsIdx], ...mapped }
        }
      })
    }
    _loaded.value = true
  } catch (e) {
    console.warn('[useServices] Failed to load services:', e)
  } finally {
    _loading.value = false
  }
}

// Auto-bootstrap
_bootstrap()

export function useServices() {
  return {
    allServices: computed(() => _services.value),
    loading: _loading,
    refresh: _bootstrap
  }
}
