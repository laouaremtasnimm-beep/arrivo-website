import { ref, computed } from 'vue'

const API_BASE = '/arrivo-website/backend/api/v1'

// Singleton state
const reviews = ref([])
const loading = ref(false)
const error   = ref(null)

export function useReviews() {

  async function fetchReviews(itemType, itemId) {
    loading.value = true
    try {
      const res = await fetch(`${API_BASE}/reviews.php?item_type=${itemType}&item_id=${itemId}`, { cache: 'no-store' })
      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Failed to fetch reviews')
      
      const newReviews = (data.reviews || []).map(r => ({
        ...r,
        reviewID: r.id, // For compatibility with dashboard
        text: r.comment, // For compatibility with detail cards
        name: `${r.first_name} ${r.last_name}`,
      }))

      // Merge into singleton: replace existing reviews for this item
      const otherReviews = reviews.value.filter(r => !(r.item_type === itemType && (r.package_id === itemId || r.service_id === itemId || r.destination_id === itemId)))
      reviews.value = [...otherReviews, ...newReviews]

    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function saveReply(reviewId, text) {
    try {
      const res = await fetch(`${API_BASE}/reviews.php`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: reviewId, reply: text })
      })
      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Failed to save reply')

      // Update local state instantly
      const rev = reviews.value.find(r => r.id === reviewId || r.reviewID === reviewId)
      if (rev) rev.reply = text

      return data
    } catch (e) {
      console.error('saveReply error:', e)
      throw e
    }
  }

  async function fetchAllForOwner(role, userId) {
    loading.value = true
    try {
      const pkgUrl = `${API_BASE}/packages.php${role === 'agency' ? '?agency_id=' + userId : ''}`
      const svcUrl = `${API_BASE}/services.php${role === 'provider' ? '?provider_id=' + userId : ''}`
      
      const [pkgsRes, svcsRes] = await Promise.all([
        fetch(pkgUrl, { cache: 'no-store' }),
        fetch(svcUrl, { cache: 'no-store' })
      ])
      const pkgsData = await pkgsRes.json()
      const svcsData = await svcsRes.json()
      
      let owned = []
      if (role === 'agency') {
        owned = (pkgsData.packages || []).map(p => ({ id: p.id, name: p.title, type: 'package' }))
      } else if (role === 'provider') {
        owned = (svcsData.services || []).map(s => ({ id: s.id, name: s.title, type: 'service' }))
      } else {
        // Admin or other
        owned = [
            ...(pkgsData.packages || []).map(p => ({ id: p.id, name: p.title, type: 'package' })),
            ...(svcsData.services || []).map(s => ({ id: s.id, name: s.title, type: 'service' }))
        ]
      }

      const promises = owned.map(item => 
        fetch(`${API_BASE}/reviews.php?item_type=${item.type}&item_id=${item.id}`)
          .then(r => r.json())
          .then(d => (d.reviews || []).map(r => ({
            ...r,
            reviewID: r.id,
            itemName: item.name,
            itemType: item.type,
            itemId:   item.id,
            touristName: `${r.first_name} ${r.last_name}`
          })))
          .catch(() => [])
      )

      const results = await Promise.all(promises)
      const newReviews = results.flat()
      
      // Merge into singleton: replace existing reviews with newer versions if they exist
      const existingIds = new Set(newReviews.map(r => r.id))
      const others = reviews.value.filter(r => !existingIds.has(r.id && r.reviewID))
      
      reviews.value = [...others, ...newReviews].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))

    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function deleteReview(reviewId) {
    try {
      const res = await fetch(`${API_BASE}/reviews.php`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: reviewId })
      })
      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.error || 'Failed to delete review')
      }

      // Remove from local state
      reviews.value = reviews.value.filter(r => (r.id !== reviewId && r.reviewID !== reviewId))
      return true
    } catch (e) {
      console.error('deleteReview error:', e)
      throw e
    }
  }

  return {
    reviews,
    loading,
    error,
    fetchReviews,
    fetchAllForOwner,
    saveReply,
    deleteReview
  }
}
