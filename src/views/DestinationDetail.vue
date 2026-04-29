<template>
  <div class="detail-page" v-if="item">
    <NavBar />

    <div class="detail-page__inner">
      <DetailBreadcrumb parent-label="Destinations" parent-path="/destinations" :current="item.name" />

      <!-- Hero -->
      <div class="detail-page__hero-wrap">
        <DetailHero
          :main-img="item.img"
          :gallery="item.gallery"
          :title="item.name"
          :saved="isSavedVal"
          @toggle-wishlist="handleToggleWishlist"
        />
      </div>

      <!-- Title + meta -->
      <div class="detail-page__title-row">
        <div>
          <div class="detail-page__type-tag">{{ item.type }}</div>
          <h1 class="detail-page__title">{{ item.name }}</h1>
          <div class="detail-page__subtitle">
            {{ item.region }}, {{ item.country }}
            <span class="detail-page__dot">·</span>
            <span class="star">★</span> {{ item.rating }}
            <span class="detail-page__reviews">({{ item.reviews?.toLocaleString() }} reviews)</span>
          </div>
        </div>
      </div>

      <!-- Two-column layout -->
      <div class="detail-page__body">
        <!-- Left: content -->
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
          />

        </div>

        <!-- Right: sidebar -->
        <DetailSidebar
  :price="item.from"
  price-label="Packages from"
  :rating="item.rating"
  :reviews="item.reviews"
  :facts="item.facts?.slice(0, 4)"
  cta-label="Find packages here"
  entity-label="Find services here"
  note="Browse packages and services for this destination."
  @book="goToPackages"
  @message="goToServices"
/>
      </div>

      <!-- More like this -->
      <DetailMoreLike
        :items="moreLike"
        see-all-path="/destinations"
        @select="goToDestination"
      />
    </div>

    <BookingModal v-model="bookingOpen" :pkg="item" @submit="handleBooking" />
  </div>

  <!-- Not found -->
  <div class="not-found" v-else>
    <h2>Destination not found</h2>
    <RouterLink to="/destinations" class="btn btn-coral">← Back to destinations</RouterLink>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { destinations } from '@/data/content.js'
import { useWishlist } from '@/composables/useWishlist.js'

import NavBar                  from '@/components/home/NavBar.vue'
import DetailBreadcrumb        from '@/components/detail/DetailBreadcrumb.vue'
import DetailHero              from '@/components/detail/DetailHero.vue'
import DetailSidebar           from '@/components/detail/DetailSidebar.vue'
import DetailReviews           from '@/components/detail/DetailReviews.vue'
import DetailMoreLike          from '@/components/detail/DetailMoreLike.vue'
import DestinationHighlights   from '@/components/detail/dest/DestinationHighlights.vue'
import BookingModal            from '@/components/home/BookingModal.vue'

const route  = useRoute()
const router = useRouter()

const { isSaved, toggle } = useWishlist()

const item    = computed(() => destinations.find(d => d.id === Number(route.params.id)))
const isSavedVal = computed(() => item.value ? isSaved.value('destination', item.value.id) : false)
const bookingOpen = ref(false)

function handleToggleWishlist() {
  if (!item.value) return
  const wasAdded = toggle('destination', item.value.id)
  if (wasAdded) router.push('/wishlist')
}

const moreLike = computed(() =>
  destinations.filter(d => d.id !== item.value?.id && d.type === item.value?.type).slice(0, 6)
)

function goToPackages() {
  router.push({ path: '/packages', query: { q: item.value?.name } })
}
function goToDestination(dest) {
  router.push(`/destinations/${dest.id}`)
}
function goToServices() {
  router.push({ path: '/services', query: { q: item.value?.name } })
}
async function handleBooking(payload) {
  const localUserStr = localStorage.getItem('user')
  let userId = null
  if (localUserStr) {
    const localUser = JSON.parse(localUserStr)
    userId = localUser.id
  }

  if (!userId) {
    alert('Please log in to book.')
    return
  }

  try {
    const res = await fetch('http://localhost/arrivo-website/backend/api/v1/bookings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: userId,
        destination_id: item.value.id,
        check_in: payload.checkin,
        guests: parseInt(payload.guests) || 1,
        total_price: item.value.from || item.value.price || 0,
        notes: payload.notes
      })
    })

    const data = await res.json()
    if (res.ok) {
      alert('Booking created successfully!')
      router.push('/dashboard')
    } else {
      alert('Failed to book: ' + (data.error || 'Unknown error'))
    }
  } catch (e) {
    console.error('Booking error:', e)
    alert('An error occurred while booking.')
  }
}

// Mock reviews — replace with real API data
const mockReviews = [
  { id:1, name:'Amelia R.',  location:'London, UK',      rating:5, date:'May 2025', text:'Absolutely breathtaking. The sunsets from Oia are everything you dream they\'ll be. We stayed in a caldera-view cave suite and it was the most romantic trip of our lives.' },
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

/* Title row */
.detail-page__title-row {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 16px; margin-bottom: 40px; flex-wrap: wrap;
}
.detail-page__type-tag {
  display: inline-block; background: var(--teal-lt); color: var(--teal-dk);
  font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
  padding: 4px 12px; border-radius: 50px; margin-bottom: 10px;
}
.detail-page__title {
  font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 3.5vw, 2.8rem);
  font-weight: 700; color: var(--indigo); margin-bottom: 8px;
}
.detail-page__subtitle {
  display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
  font-size: .92rem; color: var(--gray-600);
}
.detail-page__dot     { color: var(--gray-200); }
.detail-page__reviews { color: var(--gray-400); }

/* Two-column layout */
.detail-page__body {
  display: grid; grid-template-columns: 1fr 360px; gap: 48px;
  align-items: flex-start;
}
.detail-page__content { min-width: 0; }

/* Not found */
.not-found { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
.not-found h2 { font-family: 'Fraunces', serif; font-size: 1.8rem; }

@media (max-width: 900px) {
  .detail-page__body { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .detail-page__inner { padding: 0 4% 60px; }
}
</style>