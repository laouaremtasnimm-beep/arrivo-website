// composables/useBookings.js
import { ref } from 'vue'
import { useNotifications } from './useNotifications'

const API = '/arrivo-website/backend/api/v1/bookings.php'

const _bookings = ref([])
const _loaded = ref(false)
const _loading = ref(false)

export function useBookings() {
    const { push: pushNotification } = useNotifications()

    async function fetchBookings(user) {
        if (!user || _loaded.value) return
        _loading.value = true
        const uid = user.userID ?? user.id
        const queryParam = `user_id=${uid}`

        try {
            const res = await fetch(`${API}?${queryParam}`, { cache: 'no-store' })
            const data = await res.json()
            if (res.ok) {
                _bookings.value = data.bookings ?? []
                _loaded.value = true
            }
        } catch (e) {
            console.error('fetchBookings failed', e)
        } finally {
            _loading.value = false
        }
    }

    async function createBooking(payload) {
        // Map frontend payload to backend schema
        const body = {
            user_id: payload.user_id,
            package_id: payload.package_id ?? (payload.type === 'package' ? payload.item_id : null),
            service_id: payload.service_id ?? (payload.type === 'service' ? payload.item_id : null),
            destination_id: payload.destination_id ?? (payload.type === 'destination' ? payload.item_id : null),
            offer_id: payload.offer_id ?? (payload.type === 'offer' ? payload.item_id : null),
            title: payload.title,
            booking_type: payload.type,
            total_price: payload.price ?? 0,
            check_in: payload.check_in ?? null,
            guests: payload.guests ?? 1,
            notes: payload.notes ?? '',
        }

        try {
            const res = await fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            })

            const data = await res.json()
            if (!res.ok) throw new Error(data.error || 'Booking failed')

            const newBooking = {
                id: data.booking_id ?? Date.now(),
                booking_type: payload.type,
                status: 'pending',
                total_price: payload.price,
                check_in: payload.check_in,
                guests: payload.guests,
                package_title: payload.type === 'package' ? payload.title : (payload.package_id ? payload.title : undefined),
                service_title: payload.type === 'service' ? payload.title : (payload.service_id ? payload.title : undefined),
                destination_name: payload.type === 'destination' ? payload.title : undefined,
                offer_title: payload.type === 'offer' ? payload.title : undefined,
                package_id: body.package_id,
                service_id: body.service_id,
                destination_id: body.destination_id,
                offer_id: body.offer_id,
            }

            _bookings.value.unshift(newBooking)

            // ✅ Targeted Notifications using Server-Side Ownership
            if (data.owner_id && data.owner_role) {
                pushNotification({
                    roles: [data.owner_role],
                    targetUserId: data.owner_id,
                    type: 'booking',
                    icon: '📋',
                    title: 'New pending booking',
                    body: `A new reservation for "${payload.title}" is awaiting your confirmation.`,
                    link: '/dashboard',
                    section: 'bookings',
                })
            }

            return { ok: true }

        } catch (e) {
            console.error('createBooking failed', e)
            return { ok: false, error: e.message }
        }
    }

    async function updateStatus(id, status) {
        const b = _bookings.value.find(x => x.id === id)
        if (b) b.status = status

        try {
            await fetch(API, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status }),
            })
        } catch (e) {
            console.error('updateStatus failed', e)
        }
    }

    async function cancelBooking(id) {
        const b = _bookings.value.find(x => x.id === id)
        const oldStatus = b?.status
        const title = b?.package_title || b?.service_title || b?.destination_name || b?.offer_title || 'a booking'

        if (b) b.status = 'cancelled'

        try {
            const res = await fetch(API, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
            })
            const data = await res.json()
            if (!res.ok) throw new Error(data.error || 'Failed to cancel booking')

            // ✅ Targeted Cancellation Notification
            if (data.owner_id && data.owner_role) {
                pushNotification({
                    roles: [data.owner_role],
                    targetUserId: data.owner_id,
                    type: 'booking',
                    icon: '🚫',
                    title: 'Booking Cancelled',
                    body: `A booking for "${title}" has been cancelled.`,
                    link: '/dashboard',
                    section: 'bookings',
                })
            }

            return { ok: true }
        } catch (e) {
            console.error('cancelBooking failed', e)
            if (b) b.status = oldStatus
            return { ok: false, error: e.message }
        }
    }

    /**
 * Returns the discounted total ONLY for collab bundle offers.
 * Individual service/package bookings always receive null (no discount).
 *
 * @param {number} basePrice  - sum of raw item prices
 * @param {object|null} offer - offer object from useOffers, or null
 * @returns {number}
 */
    function collabBundlePrice(basePrice, offer) {
        if (!offer || offer.source !== 'collab') return basePrice
        const discount = Number(offer.discount_pct ?? offer.discount ?? 0)
        if (!discount) return basePrice
        return +(basePrice * (1 - discount / 100)).toFixed(2)
    }

    function isBooked(type, itemId, linkedId = null) {
        return _bookings.value.some(b => {
            if (b.status === 'cancelled') return false

            // 1. Direct match
            if (b.booking_type === type) {
                if (type === 'package' && String(b.package_id) === String(itemId)) return true
                if (type === 'service' && String(b.service_id) === String(itemId)) return true
                if (type === 'destination' && String(b.destination_id) === String(itemId)) return true
                if (type === 'offer' && String(b.offer_id) === String(itemId)) return true
            }

            // 2. Cross-match: Base item booked via offer
            if (linkedId && b.booking_type === 'offer' && String(b.offer_id) === String(linkedId)) return true

            // 3. Cross-match: Offer checked, base item booked
            if (type === 'offer' && linkedId) {
                if (b.booking_type === 'package' && String(b.package_id) === String(linkedId)) return true
                if (b.booking_type === 'service' && String(b.service_id) === String(linkedId)) return true
            }

            return false
        })
    }

    function getBookingId(type, itemId, linkedId = null) {
        const b = _bookings.value.find(b => {
            if (b.status === 'cancelled') return false

            // Direct match
            if (b.booking_type === type) {
                if (type === 'package' && String(b.package_id) === String(itemId)) return true
                if (type === 'service' && String(b.service_id) === String(itemId)) return true
                if (type === 'destination' && String(b.destination_id) === String(itemId)) return true
                if (type === 'offer' && String(b.offer_id) === String(itemId)) return true
            }

            // Cross-match: Base item booked via offer
            if (linkedId && b.booking_type === 'offer' && String(b.offer_id) === String(linkedId)) return true

            // Cross-match: Offer checked, base item booked
            if (type === 'offer' && linkedId) {
                if (b.booking_type === 'package' && String(b.package_id) === String(linkedId)) return true
                if (b.booking_type === 'service' && String(b.service_id) === String(linkedId)) return true
            }

            return false
        })
        return b?.id
    }

    /**
 * Returns the discounted price ONLY if the offer is a collab bundle
 * AND the caller is booking the full bundle (offer-type booking).
 * For individual item pages, pass no offer → gets original price.
 */
    function collabBundlePrice(basePrice, offer) {
        if (!offer || offer.source !== 'collab') return basePrice
        const discount = Number(offer.discount_pct ?? offer.discount ?? 0)
        if (!discount) return basePrice
        return +(basePrice * (1 - discount / 100)).toFixed(2)
    }

    return {
        bookings: _bookings,
        loading: _loading,
        loaded: _loaded,
        fetchBookings,
        createBooking,
        updateStatus,
        cancelBooking,
        isBooked,
        getBookingId,
    }
}