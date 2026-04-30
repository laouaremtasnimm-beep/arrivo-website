// composables/useBookings.js
import { ref, computed } from 'vue'

const API = '/arrivo-website/backend/api/v1/bookings.php'

// Module-level state — shared across all components in the session
const _bookings = ref([])
const _loaded = ref(false)
const _loading = ref(false)

export function useBookings() {

    // ── Fetch all bookings for current user ──────────────────────────────────
    async function fetchBookings(user) {
        if (!user) return
        _loading.value = true
        const uid = user.userID ?? user.id
        const role =
            user.role === 'agency' ? `agency_id=${uid}` :
                user.role === 'provider' ? `provider_id=${uid}` :
                    `user_id=${uid}`

        try {
            const res = await fetch(`${API}?${role}`, { cache: 'no-store' })
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

    // ── Create a booking and add it optimistically ───────────────────────────
    async function createBooking(payload) {
        // payload: { user_id, type, item_id, title, img, price, check_in, guests, notes }
        const body = {
            user_id: payload.user_id,
            // map type → correct id field
            booking_type: payload.type,
            ...(payload.type === 'package' && { package_id: payload.item_id }),
            ...(payload.type === 'service' && { service_id: payload.item_id }),
            ...(payload.type === 'destination' && { destination_id: payload.item_id }),
            ...(payload.type === 'offer' && { offer_id: payload.item_id }),
            check_in: payload.check_in ?? null,
            guests: payload.guests ?? 1,
            total_price: payload.price ?? 0,
            notes: payload.notes ?? '',
            title: payload.title ?? '',
        }

        try {
            const res = await fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            })
            const data = await res.json()
            if (!res.ok) return { ok: false, error: data.error ?? 'Unknown error' }

            // Optimistic update — insert a normalised record immediately
            const newBooking = {
                id: data.id ?? Date.now(),
                booking_type: payload.type,
                status: 'pending',
                total_price: payload.price,
                check_in: payload.check_in,
                guests: payload.guests,
                // convenience title fields the Bookings page uses
                package_title: payload.type === 'package' ? payload.title : undefined,
                service_title: payload.type === 'service' ? payload.title : undefined,
                destination_name: payload.type === 'destination' ? payload.title : undefined,
                offer_title: payload.type === 'offer' ? payload.title : undefined,
                // store item_id so isBooked() can look it up
                package_id: payload.type === 'package' ? payload.item_id : undefined,
                service_id: payload.type === 'service' ? payload.item_id : undefined,
                destination_id: payload.type === 'destination' ? payload.item_id : undefined,
                offer_id: payload.type === 'offer' ? payload.item_id : undefined,
            }
            _bookings.value.unshift(newBooking)
            return { ok: true }
        } catch (e) {
            console.error('createBooking failed', e)
            return { ok: false, error: e.message }
        }
    }

    // ── Update status (confirm / cancel) ─────────────────────────────────────
    async function updateStatus(id, status) {
        // Optimistic
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
            // Roll back on failure
            if (b) b.status = b.status
        }
    }

    // ── Delete a booking (Cancellation) ──────────────────────────────────────
    async function cancelBooking(id) {
        // Optimistic removal
        const prev = [..._bookings.value]
        _bookings.value = _bookings.value.filter(b => b.id !== id)

        try {
            const res = await fetch(API, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
            })
            const data = await res.json()
            if (!res.ok) throw new Error(data.error || 'Failed to cancel booking')
            return { ok: true }
        } catch (e) {
            console.error('cancelBooking failed', e)
            _bookings.value = prev // Rollback on failure
            return { ok: false, error: e.message }
        }
    }

    // ── Helper: has the user already booked this item? ───────────────────────
    function isBooked(type, itemId) {
        return _bookings.value.some(b => {
            if (b.booking_type !== type) return false
            if (type === 'package') return Number(b.package_id) === Number(itemId)
            if (type === 'service') return Number(b.service_id) === Number(itemId)
            if (type === 'destination') return Number(b.destination_id) === Number(itemId)
            if (type === 'offer') return Number(b.offer_id) === Number(itemId)
            return false
        })
    }

    function getBookingId(type, itemId) {
        const b = _bookings.value.find(b => {
            if (b.booking_type !== type) return false
            if (type === 'package') return Number(b.package_id) === Number(itemId)
            if (type === 'service') return Number(b.service_id) === Number(itemId)
            if (type === 'destination') return Number(b.destination_id) === Number(itemId)
            if (type === 'offer') return Number(b.offer_id) === Number(itemId)
            return false
        })
        return b?.id
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