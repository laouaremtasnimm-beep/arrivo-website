<template>
  <div class="dest-detail" v-if="item">
    <NavBar />

    <div class="dest-detail__inner">
      <DetailBreadcrumb parent-label="Destinations" parent-path="/destinations" :current="item.name" />

      <!-- Hero -->
      <div class="dest-detail__hero">
        <DetailHero
          :main-img="item.img"
          :gallery="item.gallery"
          :title="item.name"
          :saved="isSavedVal"
          @toggle-wishlist="handleToggleWishlist"
        />
      </div>

      <!-- Title Row -->
      <div class="dest-detail__title-row">
        <div class="dest-detail__title-left">
          <div class="dest-detail__badges">
            <span class="dest-badge dest-badge--country">🗺️ {{ item.country }}</span>
            <span class="dest-badge dest-badge--region" v-if="item.region">🔖 {{ item.region }}</span>
            <span class="dest-badge dest-badge--best" v-if="item.bestTime">☀️ Best: {{ item.bestTime }}</span>
            <span class="dest-badge dest-badge--booked" v-if="alreadyBooked">✓ Already booked</span>
          </div>
          <h1 class="dest-detail__title">{{ item.name }}</h1>
          <div class="dest-detail__meta">
            <span class="dest-detail__rating">
              <span class="dest-star">★</span>
              {{ Number(item.rating).toFixed(1) }}
              <span class="dest-detail__rev-count">({{ item.reviews?.toLocaleString() }} reviews)</span>
            </span>
            <span class="dest-detail__sep" v-if="item.from">·</span>
            <span class="dest-detail__price-teaser" v-if="item.from">Packages starting from <strong>${{ item.from?.toLocaleString() }}</strong></span>
          </div>
        </div>
      </div>

      <!-- Two-column body -->
      <div class="dest-detail__body">
        <div class="dest-detail__content">

          <DestinationHighlights
            :name="item.name"
            :long-desc="item.longDesc"
            :highlights="item.highlights"
            :facts="item.facts"
            :best-time="item.bestTime"
          />


          <!-- Hidden EntityCard just for the contact modal -->
          <EntityCard
            ref="entityCardRef"
            :name="item.name + ' Support'"
            bio="Contact our support team for any questions about this destination."
            :rating="5"
            :reviews="100"
            :receiver-id="1"
            entity-label="Support"
            hide-card
          />
        </div>

        <DetailSidebar
          class="sticky-sidebar"
          :price="item.from"
          price-label="Packages starting from"
          :rating="item.rating"
          :reviews="item.reviews"
          :facts="item.facts?.slice(0, 4)"
          cta-label="Find packages here"
          entity-label="Contact Agency"
          note="Browse packages and services for this destination."
          @book="goToPackages"
          @message="handleContact"
        />
      </div>

      <DetailReviews
        :rating="item.rating"
        :total-reviews="item.reviews"
        :reviews="mockReviews"
        item-type="destination"
        :item-id="item.id"
        @stats-update="updateStats"
      />

      <DestinationThingsToDo class="detail-card" :things-to-do="item.thingsToDo" />

      <DetailMoreLike
        :items="moreLike"
        see-all-path="/destinations"
        @select="goToDestination"
      />
    </div>
  </div>

  <div class="dest-not-found" v-else>
    <h2>Destination not found</h2>
    <RouterLink to="/destinations" class="btn btn-coral">← Back to destinations</RouterLink>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
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
import DestinationThingsToDo from '@/components/detail/dest/DestinationThingsToDo.vue'
import BookingModal          from '@/components/home/BookingModal.vue'
import EntityCard            from '@/components/detail/EntityCard.vue'

const route  = useRoute()
const router = useRouter()

const { isSaved, toggle }                            = useWishlist()
const { user, isLoggedIn }                           = useAuth()
const { isBooked, createBooking, fetchBookings, loaded, getBookingId, cancelBooking } = useBookings()

const item = ref(null)
const loading = ref(true)
const entityCardRef = ref(null)

async function loadItem(id) {
  window.scrollTo({ top: 0, behavior: 'smooth' })
  loading.value = true
  item.value = null
  try {
    const res  = await fetch(`/arrivo-website/backend/api/v1/listings.php?id=${id}`)
    const data = await res.json()
    if (data.listing) {
      const dbItem = { ...data.listing, img: data.listing.image, from: data.listing.price }
      const demo   = destinations.find(d => d.id === dbItem.id)
      item.value   = demo ? { ...dbItem, ...demo } : dbItem
    } else {
      item.value = destinations.find(d => d.id === Number(id)) ?? null
    }
  } catch (e) {
    console.error('Failed to fetch destination:', e)
    item.value = destinations.find(d => d.id === Number(id)) ?? null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (isLoggedIn.value && !loaded.value) fetchBookings(user.value)
  loadItem(route.params.id)
})
watch(() => route.params.id, (newId) => { if (newId) loadItem(newId) })

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

function goToPackages()   { router.push({ path: '/packages',     query: { q: item.value?.name } }) }
function goToServices()   { router.push({ path: '/services',     query: { q: item.value?.name } }) }
function goToDestination(dest) { router.push(`/destinations/${dest.id}`) }

function handleContact() {
  if (entityCardRef.value) {
    entityCardRef.value.modalOpen = true
  }
}

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
function updateStats(stats) {
  if (item.value) {
    item.value.rating = stats.rating
    item.value.reviews = stats.count
  }
}
</script>

<style scoped>
/* ─── Page shell ─────────────────────────────────── */
.dest-detail { min-height: 100vh; background: var(--gray-50); padding-top: 72px; }
.dest-detail__inner { max-width: 1240px; margin: 0 auto; padding: 0 5% 100px; }

/* ─── Hero ────────────────────────────────────────── */
.dest-detail__hero { margin: 24px 0 0; }

/* ─── Title Row ──────────────────────────────────── */
.dest-detail__title-row { margin: 28px 0 36px; }
.dest-detail__badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.dest-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: .74rem; font-weight: 700; padding: 5px 13px;
  border-radius: 50px; font-family: 'DM Sans', sans-serif;
}
.dest-badge--country { background: rgba(99,102,241,.09); color: var(--indigo); }
.dest-badge--region  { background: rgba(46,196,182,.12);  color: var(--teal); }
.dest-badge--best    { background: rgba(255,190,60,.14);   color: #9a6a00; }
.dest-badge--booked  { background: rgba(39,174,96,.1);     color: #1a7a45; }

.dest-detail__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2rem, 4vw, 3.2rem);
  font-weight: 700; color: var(--indigo);
  margin: 0 0 14px; line-height: 1.1;
}
.dest-detail__meta {
  display: flex; align-items: center; gap: 12px;
  flex-wrap: wrap; font-size: .92rem; color: var(--gray-600);
}
.dest-detail__sep { color: var(--gray-300); }
.dest-detail__rating { display: flex; align-items: center; gap: 5px; font-weight: 600; color: var(--indigo); }
.dest-star { color: #FFB400; font-size: 1rem; }
.dest-detail__rev-count { color: var(--gray-400); font-weight: 400; }
.dest-detail__price-teaser { font-size: .9rem; color: var(--gray-500); }
.dest-detail__price-teaser strong { color: var(--coral); }

/* ─── Two-column body ────────────────────────────── */
.dest-detail__body {
  display: grid; grid-template-columns: 1fr 360px; gap: 40px; align-items: start;
}
.dest-detail__content { min-width: 0; }

/* ─── Not found ──────────────────────────────────── */
.dest-not-found { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
.dest-not-found h2 { font-family: 'Fraunces', serif; font-size: 1.8rem; }

/* ─── Responsive ─────────────────────────────────── */
@media (max-width: 960px) { .dest-detail__body { grid-template-columns: 1fr; } }
@media (max-width: 640px)  { .dest-detail__inner { padding: 0 4% 60px; } }
</style>