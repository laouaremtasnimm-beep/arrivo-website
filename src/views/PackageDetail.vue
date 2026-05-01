<template>
  <div v-if="loading">Loading...</div>
  <div class="detail-page" v-if="item">
    <NavBar />

    <div class="detail-page__inner">
      <DetailBreadcrumb parent-label="Packages" parent-path="/packages" :current="item.title" />

      <div class="detail-page__hero-wrap">
        <DetailHero
          :main-img="item.img"
          :gallery="item.gallery"
          :title="item.title"
          :saved="isSavedVal"
          @toggle-wishlist="handleToggleWishlist"
        />
      </div>

      <div class="detail-page__title-row">
        <div>
          <div class="detail-page__type-tag">{{ item.type }}</div>
          <h1 class="detail-page__title">{{ item.title }}</h1>
          <div class="detail-page__subtitle">
            by <strong>{{ item.agency }}</strong>
            <span class="detail-page__dot">·</span>
            {{ item.duration }} days
            <span class="detail-page__dot">·</span>
            <span class="star">★</span> {{ item.rating }}
            <span class="detail-page__reviews">({{ item.reviews }} reviews)</span>
            <span class="detail-page__spots" v-if="item.spots <= 5">
              · 🔥 {{ item.spots }} spots left
            </span>
            <!-- Already booked badge -->
            <span v-if="alreadyBooked" class="booked-badge">✓ Already booked</span>
          </div>
        </div>
      </div>

      <div class="detail-page__body">
        <div class="detail-page__content">

          <div class="pkg-about">
            <h3 class="pkg-section-title">About this package</h3>
            <p class="pkg-about__text">{{ item.longDesc }}</p>
          </div>

          <PackageItinerary :itinerary="item.itinerary" />
          <PackageInclusions :includes="item.includes" :excludes="item.excludes" />

          <div class="pkg-section-title" style="margin-bottom:16px">About the agency</div>
          <EntityCard
           :name="item.agency"
  :bio="item.agencyBio"
  :img="item.agencyImg"
  :rating="item.agencyRating"
  :reviews="item.agencyReviews"
  :receiver-id="item.agency_id"   
  entity-label="Agency"
  @contact="handleContact"
/>

          <DetailReviews
            :rating="item.rating"
            :total-reviews="item.reviews"
            :reviews="mockReviews"
            item-type="package"
            :item-id="item.id"
          />
        </div>

        <DetailSidebar
          :price="item.price"
          price-label="per person"
          :rating="item.rating"
          :reviews="item.reviews"
          :spots="item.spots"
          :facts="item.facts"
          :cta-label="alreadyBooked ? 'Cancel Booking' : 'Book this package'"
          :cta-danger="alreadyBooked"
          entity-label="agency"
          @book="bookingOpen = true"
          @cancel="handleCancel"
          @message="handleContact"
        />
      </div>

      <DetailMoreLike :items="moreLike" see-all-path="/packages" @select="goToPackage" />
    </div>

    <BookingModal v-model="bookingOpen" :pkg="item" @submit="handleBooking" />
  </div>

  <div v-else-if="!loading" class="not-found">
    <h2>Package not found</h2>
    <RouterLink to="/packages" class="btn btn-coral">← Back to packages</RouterLink>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useWishlist } from '@/composables/useWishlist.js'
import { useBookings } from '@/composables/useBookings.js'
import { useAuth }     from '@/composables/useAuth.js'
import { packages }    from '@/data/content.js'

import NavBar             from '@/components/home/NavBar.vue'
import DetailBreadcrumb   from '@/components/detail/DetailBreadcrumb.vue'
import DetailHero         from '@/components/detail/DetailHero.vue'
import DetailSidebar      from '@/components/detail/DetailSidebar.vue'
import DetailReviews      from '@/components/detail/DetailReviews.vue'
import DetailMoreLike     from '@/components/detail/DetailMoreLike.vue'
import EntityCard         from '@/components/detail/EntityCard.vue'
import PackageItinerary   from '@/components/detail/pkg/PackageItinerary.vue'
import PackageInclusions  from '@/components/detail/pkg/PackageInclusions.vue'
import BookingModal       from '@/components/home/BookingModal.vue'

const route  = useRoute()
const router = useRouter()

const { isSaved, toggle }             = useWishlist()
const { user, isLoggedIn }            = useAuth()
const { isBooked, createBooking, fetchBookings, loaded, getBookingId, cancelBooking } = useBookings()

const item    = ref(null)
const loading = ref(true)
const moreLike = ref([])
const bookingOpen = ref(false)

const API = '/arrivo-website/backend/api/v1'



async function loadItem(id) {
  window.scrollTo({ top: 0, behavior: 'smooth' })
  loading.value = true
  item.value    = null
  // inside loadItem, right after `item.value = demo ? ...`
console.log('agency_id on item:', item.value?.agency_id)
  moreLike.value = []
  id = Number(id)

  try {
    const res = await fetch(`${API}/packages.php?id=${id}`)
    if (res.ok) {
      const data = await res.json()
      if (data.package) {
        console.log('raw package from API:', data.package)
console.log('agency_id value:', data.package?.agency_id)
        const p      = data.package
        const dbItem = {
          agency_id: p.agency_id,
          id: p.id, title: p.title, agency: p.agency_name || 'Unknown Agency',
          img: p.img_url || p.img || 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg',
          type: p.type, duration: p.duration_days,
          rating: Number(p.rating || 4.5), reviews: Number(p.review_count || 0),
          spots: Number(p.spots_available || 0), price: Number(p.price || 0),
          longDesc: p.long_desc || p.description || '',
          includes:  p.includes  && p.includes  !== 'null' ? JSON.parse(p.includes)  : [],
          excludes:  p.excludes  && p.excludes  !== 'null' ? JSON.parse(p.excludes)  : [],
          itinerary: p.itinerary && p.itinerary !== 'null' ? JSON.parse(p.itinerary) : [],
        }
       const demo = packages.find(x => x.id === id)
item.value = demo ? { ...dbItem, ...demo, agency_id: dbItem.agency_id } : dbItem
      }
    }
  } catch (e) {
    console.error('Failed to fetch package from DB:', e)
  }

  if (!item.value) {
    const mockP = packages.find(p => p.id === id)
    if (mockP) item.value = { ...mockP, longDesc: mockP.desc, reviews: Number(mockP.reviews), rating: Number(mockP.rating) }
  }

  try {
    const allRes  = await fetch(`${API}/packages.php`)
    const allData = await allRes.json()
    const dbRows  = allData.packages || []
    const demoTitles = new Set(packages.map(p => p.title))
    const newOnly = dbRows.filter(p => !demoTitles.has(p.title)).map(p => ({
      id: p.id, title: p.title, agency: p.agency_name || 'Unknown Agency',
      img: p.img_url, type: p.type, duration: p.duration_days,
      rating: Number(p.rating), reviews: Number(p.review_count),
      spots: Number(p.spots_available), price: Number(p.price)
    }))
    const allItems = [...packages, ...newOnly]
    if (item.value) {
      moreLike.value = allItems.filter(x => x.id !== item.value.id && x.type === item.value.type).slice(0, 6)
    }
  } catch (e) { console.error(e) }

  loading.value = false
}


onMounted(() => {
  if (isLoggedIn.value && !loaded.value) fetchBookings(user.value)
  loadItem(route.params.id)
})
watch(() => route.params.id, (newId) => { if (newId) loadItem(newId) })

const isSavedVal    = computed(() => item.value ? isSaved.value('package', item.value.id) : false)
const alreadyBooked = computed(() => item.value ? isBooked('package', item.value.id) : false)

function handleToggleWishlist() {
  if (!item.value) return
  const wasAdded = toggle('package', item.value.id)
  if (wasAdded) router.push('/wishlist')
}

function goToPackage(pkg) { router.push(`/packages/${pkg.id}`) }
function handleContact()  { console.log('Contact agency') }

async function handleBooking(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  const result = await createBooking({
    user_id:  user.value?.userID ?? user.value?.id,
    type:     'package',
    item_id:  item.value.id,
    title:    item.value.title,
    price:    item.value.price,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
  })

  if (result.ok) {
    bookingOpen.value = false
    alert('Package booked successfully!')
    router.push('/bookings')
  } else {
    alert('Failed to book: ' + result.error)
  }
}

async function handleCancel() {
  if (!confirm('Are you sure you want to cancel this booking?')) return
  const id = getBookingId('package', item.value.id)
  if (!id) return
  const res = await cancelBooking(id)
  if (res.ok) alert('Booking cancelled successfully.')
  else alert('Failed to cancel: ' + res.error)
}

const mockReviews = [
  { id:1, name:'Amelia R.',  location:'London, UK',      rating:5, date:'May 2025', text:'The entire package was flawlessly organised. The chalet was stunning and the ski instructor was brilliant. Worth every penny.' },
  { id:2, name:'Thomas K.',  location:'Munich, Germany', rating:5, date:'Feb 2025', text:"Best ski holiday I've ever had. The Zermatt day trip alone was worth the price. The off-piste guide knew the mountain perfectly." },
  { id:3, name:'Maria S.',   location:'Rome, Italy',     rating:4, date:'Jan 2025', text:'Excellent organisation and beautiful location. The food was outstanding. Slightly more free time would have been nice but overall brilliant.' },
  { id:4, name:'David C.',   location:'New York, USA',   rating:5, date:'Mar 2025', text:'Genuinely life-changing experience. The Swiss Alps in winter are something everyone should see. The team was professional and warm.' },
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
.detail-page__dot { color: var(--gray-200); }
.detail-page__reviews { color: var(--gray-400); }
.detail-page__spots { color: var(--coral); font-weight: 600; }
.booked-badge { background: rgba(39,174,96,.12); color: #1a7a45; font-size: .78rem; font-weight: 700; padding: 3px 12px; border-radius: 50px; border: 1px solid rgba(39,174,96,.25); }
.detail-page__body    { display: grid; grid-template-columns: 1fr 360px; gap: 48px; align-items: flex-start; }
.detail-page__content { min-width: 0; }
.pkg-about { margin-bottom: 36px; }
.pkg-section-title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; color: var(--indigo); margin-bottom: 16px; }
.pkg-about__text { font-size: .95rem; color: var(--gray-600); line-height: 1.75; }
.not-found { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
.not-found h2 { font-family: 'Fraunces', serif; font-size: 1.8rem; }
@media (max-width: 900px) { .detail-page__body { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .detail-page__inner { padding: 0 4% 60px; } }
</style>