<template>
  <div class="detail-page" v-if="item">
    <NavBar />

    <div class="detail-page__inner">
      <DetailBreadcrumb parent-label="Destinations" parent-path="/destinations" :current="item.name" />

      <div class="detail-page__hero-wrap">
        <DetailHero
          :main-img="item.img"
          :gallery="item.gallery"
          :title="item.name"
          :saved="isSavedVal"
          @toggle-wishlist="handleToggleWishlist"
        />
      </div>

      <div class="detail-page__title-row">
        <div>
          <div class="detail-page__type-tag">{{ item.type }}</div>
          <h1 class="detail-page__title">{{ item.name }}</h1>
          <div class="detail-page__subtitle">
            {{ item.region }}, {{ item.country }}
            <span class="detail-page__dot">·</span>
            <span class="star">★</span> {{ item.rating }}
            <span class="detail-page__reviews">({{ item.reviews?.toLocaleString() }} reviews)</span>
            <span v-if="alreadyBooked" class="booked-badge">✓ Already booked</span>
          </div>
        </div>
      </div>

      <div class="detail-page__body">
        <div class="detail-page__content">

          <DestinationHighlights
            :name="item.name"
            :long-desc="item.longDesc"
            :highlights="item.highlights"
            :things-to-do="item.thingsToDo"
            :facts="item.facts"
            :best-time="item.bestTime"
          />

          <DetailReviews
            :rating="item.rating"
            :total-reviews="item.reviews"
            :reviews="mockReviews"
            item-type="destination"
            :item-id="item.id"
          />
        </div>

        <DetailSidebar
          :price="item.from"
          price-label="Packages from"
          :rating="item.rating"
          :reviews="item.reviews"
          :facts="item.facts?.slice(0, 4)"
          :cta-label="alreadyBooked ? 'Cancel Booking' : 'Book your trip'"
          :cta-danger="alreadyBooked"
          entity-label="Find services here"
          note="Browse packages and services for this destination."
          @book="openBookingOrPackages"
          @cancel="handleCancel"
          @message="goToServices"
        />
      </div>

      <DetailMoreLike
        :items="moreLike"
        see-all-path="/destinations"
        @select="goToDestination"
      />
    </div>

    <BookingModal v-model="bookingOpen" :pkg="item" @submit="handleBooking" />
  </div>

  <div class="not-found" v-else>
    <h2>Destination not found</h2>
    <RouterLink to="/destinations" class="btn btn-coral">← Back to destinations</RouterLink>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { destinations } from '@/data/content.js'
import { useWishlist }  from '@/composables/useWishlist.js'
import { useBookings }  from '@/composables/useBookings.js'
import { useAuth }      from '@/composables/useAuth.js'

import NavBar                from '@/components/home/NavBar.vue'
import DetailBreadcrumb      from '@/components/detail/DetailBreadcrumb.vue'
import DetailHero            from '@/components/detail/DetailHero.vue'
import DetailSidebar         from '@/components/detail/DetailSidebar.vue'
import DetailReviews         from '@/components/detail/DetailReviews.vue'
import DetailMoreLike        from '@/components/detail/DetailMoreLike.vue'
import DestinationHighlights from '@/components/detail/dest/DestinationHighlights.vue'
import BookingModal          from '@/components/home/BookingModal.vue'

const route  = useRoute()
const router = useRouter()

const { isSaved, toggle }                            = useWishlist()
const { user, isLoggedIn }                           = useAuth()
const { isBooked, createBooking, fetchBookings, loaded, getBookingId, cancelBooking } = useBookings()

// Ensure bookings are loaded
if (isLoggedIn.value && !loaded.value) {
  fetchBookings(user.value)
}

const item         = computed(() => destinations.find(d => d.id === Number(route.params.id)))
const isSavedVal   = computed(() => item.value ? isSaved.value('destination', item.value.id) : false)
const alreadyBooked = computed(() => item.value ? isBooked('destination', item.value.id) : false)
const bookingOpen  = ref(false)

function handleToggleWishlist() {
  if (!item.value) return
  const wasAdded = toggle('destination', item.value.id)
  if (wasAdded) router.push('/wishlist')
}

const moreLike = computed(() =>
  destinations.filter(d => d.id !== item.value?.id && d.type === item.value?.type).slice(0, 6)
)

function openBookingOrPackages() {
  // If user is logged in, allow direct destination booking; otherwise redirect to packages
  if (isLoggedIn.value) {
    bookingOpen.value = true
  } else {
    goToPackages()
  }
}
function goToPackages()   { router.push({ path: '/packages',     query: { q: item.value?.name } }) }
function goToServices()   { router.push({ path: '/services',     query: { q: item.value?.name } }) }
function goToDestination(dest) { router.push(`/destinations/${dest.id}`) }

async function handleBooking(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  const result = await createBooking({
    user_id:  user.value?.userID ?? user.value?.id,
    type:     'destination',
    item_id:  item.value.id,
    title:    item.value.name,
    price:    item.value.from ?? 0,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
  })

  if (result.ok) {
    bookingOpen.value = false
    alert('Destination booked successfully!')
    router.push('/bookings')
  } else {
    alert('Failed to book destination: ' + result.error)
  }
}

async function handleCancel() {
  if (!confirm('Are you sure you want to cancel this booking?')) return
  const id = getBookingId('destination', item.value.id)
  if (!id) return
  const res = await cancelBooking(id)
  if (res.ok) alert('Booking cancelled successfully.')
  else alert('Failed to cancel: ' + res.error)
}

const mockReviews = [
  { id:1, name:'Amelia R.',  location:'London, UK',      rating:5, date:'May 2025', text:"Absolutely breathtaking. The sunsets from Oia are everything you dream they'll be. We stayed in a caldera-view cave suite and it was the most romantic trip of our lives." },
  { id:2, name:'Carlos M.',  location:'Madrid, Spain',   rating:5, date:'Apr 2025', text:'Santorini exceeded every expectation. The food was incredible and the caldera sailing trip was the highlight of our holiday. Will definitely return.' },
  { id:3, name:'Yuki T.',    location:'Tokyo, Japan',    rating:4, date:'Mar 2025', text:'Stunning place — slightly crowded in peak season but worth it. The Akrotiri archaeological site is underrated and should not be missed.' },
  { id:4, name:'Sophie L.',  location:'Paris, France',   rating:5, date:'Jun 2025', text:'The most beautiful place I have ever visited. The volcanic beaches and the wine are extraordinary. Highly recommend hiring a private boat for a day.' },
  { id:5, name:'James O.',   location:'Dublin, Ireland', rating:4, date:'Jul 2025', text:'Great destination though accommodation can be pricey in high summer. Book well in advance. The sunset views are genuinely stunning.' },
  { id:6, name:'Lena M.',    location:'Berlin, Germany', rating:5, date:'Sep 2025', text:'We visited in September — perfect timing. Less crowded, warm water, golden light. The local Assyrtiko wine is a revelation.' },
]
</script>

<style scoped>
.detail-page { min-height: 100vh; background: var(--gray-50); padding-top: 72px; }
.detail-page__inner { max-width: 1200px; margin: 0 auto; padding: 0 5% 80px; }
.detail-page__hero-wrap { margin: 24px 0 32px; }
.detail-page__title-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 40px; flex-wrap: wrap; }
.detail-page__type-tag  { display: inline-block; background: var(--teal-lt); color: var(--teal-dk); font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; padding: 4px 12px; border-radius: 50px; margin-bottom: 10px; }
.detail-page__title     { font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 3.5vw, 2.8rem); font-weight: 700; color: var(--indigo); margin-bottom: 8px; }
.detail-page__subtitle  { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: .92rem; color: var(--gray-600); }
.detail-page__dot     { color: var(--gray-200); }
.detail-page__reviews { color: var(--gray-400); }
.booked-badge { background: rgba(39,174,96,.12); color: #1a7a45; font-size: .78rem; font-weight: 700; padding: 3px 12px; border-radius: 50px; border: 1px solid rgba(39,174,96,.25); }
.detail-page__body    { display: grid; grid-template-columns: 1fr 360px; gap: 48px; align-items: flex-start; }
.detail-page__content { min-width: 0; }
.not-found { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
.not-found h2 { font-family: 'Fraunces', serif; font-size: 1.8rem; }
@media (max-width: 900px) { .detail-page__body { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .detail-page__inner { padding: 0 4% 60px; } }
</style>