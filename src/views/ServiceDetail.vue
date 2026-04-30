<template>
  <div class="detail-page" v-if="item">
    <NavBar />

    <div class="detail-page__inner">
      <DetailBreadcrumb parent-label="Services" parent-path="/services" :current="item.title" />

      <div class="detail-page__hero-wrap">
        <DetailHero
          :main-img="item.img"
          :gallery="item.gallery || []"
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
            by <strong>{{ item.provider }}</strong>
            <span class="detail-page__dot">·</span>
            <span class="star">★</span> {{ item.rating }}
            <span class="detail-page__reviews">({{ item.reviews }} reviews)</span>
            <span class="detail-page__avail" :class="item.availability ? 'avail--yes' : 'avail--no'">
              · {{ item.availability ? '● Available' : '○ Unavailable' }}
            </span>
            <span v-if="alreadyBooked" class="booked-badge">✓ Already booked</span>
          </div>
        </div>
      </div>

      <div class="detail-page__body">
        <div class="detail-page__content">

          <ServiceFeatures
            :long-desc="item.longDesc"
            :features="item.features"
            :options="item.vehicleOptions"
            :options-title="item.type === 'Transport' ? 'Vehicle options' : 'Options'"
            :unit="item.unit"
            @select-option="handleOptionSelect"
          />

          <div style="margin-bottom:36px">
            <div class="pkg-section-title" style="margin-bottom:16px">About the provider</div>
            <EntityCard
              :name="item.provider"
              :bio="item.providerBio"
              :rating="item.providerRating"
              :reviews="item.providerReviews"
              entity-label="Provider"
              @contact="handleContact"
            />
          </div>

          <DetailReviews
            :rating="item.rating"
            :total-reviews="item.reviews"
            :reviews="mockReviews"
            item-type="service"
            :item-id="item.id"
          />
        </div>

        <DetailSidebar
          :price="item.price"
          price-label="from"
          :unit="item.unit"
          :rating="item.rating"
          :reviews="item.reviews"
          :facts="item.facts"
          :cta-label="alreadyBooked ? '✓ Booked' : 'Book this service'"
          :cta-disabled="alreadyBooked"
          entity-label="provider"
          note="You won't be charged until confirmed."
          @book="bookingOpen = true"
          @message="handleContact"
        />
      </div>

      <DetailMoreLike :items="moreLike" see-all-path="/services" @select="goToService" />
    </div>

    <BookingModal v-model="bookingOpen" :pkg="item" @submit="handleBooking" />
  </div>

  <div class="not-found" v-else>
    <h2>Service not found</h2>
    <RouterLink to="/services" class="btn btn-coral">← Back to services</RouterLink>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useWishlist } from '@/composables/useWishlist.js'
import { useBookings } from '@/composables/useBookings.js'
import { useAuth }     from '@/composables/useAuth.js'
import { services }    from '@/data/content.js'

import NavBar           from '@/components/home/NavBar.vue'
import DetailBreadcrumb from '@/components/detail/DetailBreadcrumb.vue'
import DetailHero       from '@/components/detail/DetailHero.vue'
import DetailSidebar    from '@/components/detail/DetailSidebar.vue'
import DetailReviews    from '@/components/detail/DetailReviews.vue'
import DetailMoreLike   from '@/components/detail/DetailMoreLike.vue'
import EntityCard       from '@/components/detail/EntityCard.vue'
import ServiceFeatures  from '@/components/detail/svc/ServiceFeatures.vue'
import BookingModal     from '@/components/home/BookingModal.vue'

const route  = useRoute()
const router = useRouter()

const { isSaved, toggle }                    = useWishlist()
const { user, isLoggedIn }                   = useAuth()
const { isBooked, createBooking, fetchBookings, loaded } = useBookings()

const item    = ref(null)
const loading = ref(true)
const moreLike = ref([])
const bookingOpen = ref(false)

const API = '/arrivo-website/backend/api/v1'

onMounted(async () => {
  if (isLoggedIn.value && !loaded.value) {
    fetchBookings(user.value)
  }

  const id = Number(route.params.id)
  const mockS = services.find(s => s.id === id)

  if (mockS) {
    item.value = {
      id: mockS.id, icon: mockS.icon, iconBg: 'svc-icon-teal',
      title: mockS.title, provider: mockS.provider || 'Unknown Provider',
      type: mockS.type, price: Number(mockS.price), unit: mockS.unit,
      rating: Number(mockS.rating), reviews: Number(mockS.reviews),
      availability: !!mockS.availability,
      desc: mockS.desc, longDesc: mockS.desc, img: mockS.img,
      features: mockS.features || [],
    }
  } else {
    try {
      const res  = await fetch(`${API}/services.php?id=${id}`)
      if (res.ok) {
        const data = await res.json()
        if (data.service) {
          const s = data.service
          item.value = {
            id: s.id, icon: s.icon, iconBg: 'svc-icon-teal',
            title: s.title, provider: s.provider_name || 'Unknown Provider',
            type: s.type, price: Number(s.price), unit: s.price_unit,
            rating: Number(s.rating), reviews: Number(s.review_count),
            availability: !!Number(s.is_available),
            desc: s.description, longDesc: s.long_desc, img: s.img_url,
            features: s.features && s.features !== 'null' ? JSON.parse(s.features) : [],
          }
        }
      }
    } catch (e) {
      console.error(e)
    }
  }

  try {
    const allRes  = await fetch(`${API}/services.php`)
    const allData = await allRes.json()
    const dbRows = allData.services || []
    
    const demoTitles = new Set(services.map(s => s.title))
    const newOnly = dbRows.filter(s => !demoTitles.has(s.title)).map(s => ({
        id: s.id, icon: s.icon, iconBg: 'svc-icon-teal', 
        title: s.title, provider: s.provider_name || 'Unknown Provider', 
        type: s.type, price: Number(s.price), unit: s.price_unit, 
        rating: Number(s.rating), reviews: Number(s.review_count), 
        availability: !!Number(s.is_available)
    }))
    
    const allItems = [...services, ...newOnly]

    if (item.value) {
      moreLike.value = allItems
        .filter(x => x.id !== item.value.id && x.type === item.value.type).slice(0, 6)
    }
  } catch (e) {
    console.error(e)
  }
  
  loading.value = false
})

const isSavedVal    = computed(() => item.value ? isSaved.value('service', item.value.id) : false)
const alreadyBooked = computed(() => item.value ? isBooked('service', item.value.id) : false)

function handleToggleWishlist() {
  if (!item.value) return
  const wasAdded = toggle('service', item.value.id)
  if (wasAdded) router.push('/wishlist')
}

function goToService(svc)       { router.push(`/services/${svc.id}`) }
function handleContact()        { console.log('Contact provider') }
function handleOptionSelect()   { bookingOpen.value = true }

async function handleBooking(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  const result = await createBooking({
    user_id:  user.value?.userID ?? user.value?.id,
    type:     'service',
    item_id:  item.value.id,
    title:    item.value.title,
    price:    item.value.price,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
  })

  if (result.ok) {
    bookingOpen.value = false
    alert('Service booked! View it in your bookings.')
  } else {
    alert('Failed to book: ' + result.error)
  }
}

const mockReviews = [
  { id:1, name:'Lena M.',   location:'Berlin, Germany', rating:5, date:'Jun 2025', text:'Absolutely seamless experience. The driver was waiting with a sign, the car was immaculate and the price was exactly as quoted. Will use every time.' },
  { id:2, name:'James O.',  location:'Dublin, Ireland', rating:5, date:'May 2025', text:'Professional, punctual and friendly. The flight was delayed by 90 minutes and they tracked it and were there waiting. Fantastic service.' },
  { id:3, name:'Yuki T.',   location:'Tokyo, Japan',    rating:4, date:'Apr 2025', text:'Good service overall. The vehicle was clean and modern. Slightly difficult to find the driver at first but they were very helpful once we connected.' },
  { id:4, name:'Sophie L.', location:'Paris, France',   rating:5, date:'Mar 2025', text:"Worth every cent. No stress, no searching, just a friendly face with your name on a board. Makes such a difference after a long flight." },
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
.avail--yes { color: #27ae60; font-weight: 600; }
.avail--no  { color: var(--gray-400); }
.booked-badge { background: rgba(39,174,96,.12); color: #1a7a45; font-size: .78rem; font-weight: 700; padding: 3px 12px; border-radius: 50px; border: 1px solid rgba(39,174,96,.25); }
.detail-page__body    { display: grid; grid-template-columns: 1fr 360px; gap: 48px; align-items: flex-start; }
.detail-page__content { min-width: 0; }
.pkg-section-title    { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; color: var(--indigo); }
.not-found { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
.not-found h2 { font-family: 'Fraunces', serif; font-size: 1.8rem; }
@media (max-width: 900px) { .detail-page__body { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .detail-page__inner { padding: 0 4% 60px; } }
</style>