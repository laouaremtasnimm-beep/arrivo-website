<template>
  <div class="home">

    <NavBar />
    <HeroSection />

    <SearchBar
      :selected-dates="search.dates"
      :selected-guests="search.guests"
      @search="handleSearch"
    />

    <HotOffers id="offers" @select="handleOfferSelect" />

    <DestinationGrid :destinations="homeDestinations" @select="handleDestSelect" />
    <TravelPackages id="packages" :packages="homePackages"         @select="handlePackageSelect" @book="openBooking" @cancel="handleCancel" />
    <ServicesGrid    :services="homeServices"         @select="handleServiceSelect" />
    <HowItWorks />
    <ReviewsSection  :reviews="reviews" />
    <AgencyCTA />
    <NewsletterSection />
    <SiteFooter />

    <BookingModal
      v-model="bookingOpen"
      :pkg="selectedPackage"
      @submit="handleBookingSubmit"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { destinations as demoDestinations, packages as demoPackages, services as demoServices } from '@/data/content.js'
import { useAuth } from '@/composables/useAuth'
import { useBookings } from '@/composables/useBookings'

import NavBar            from '@/components/home/NavBar.vue'
import HeroSection       from '@/components/home/HeroSection.vue'
import SearchBar         from '@/components/home/SearchBar.vue'
import HotOffers         from '@/components/home/HotOffers.vue'
import DestinationGrid   from '@/components/home/DestinationGrid.vue'
import TravelPackages    from '@/components/home/TravelPackages.vue'
import ServicesGrid      from '@/components/home/ServicesGrid.vue'
import HowItWorks        from '@/components/home/HowItWorks.vue'
import ReviewsSection    from '@/components/home/ReviewsSection.vue'
import AgencyCTA         from '@/components/home/AgencyCTA.vue'
import NewsletterSection from '@/components/home/NewsletterSection.vue'
import SiteFooter        from '@/components/home/SiteFooter.vue'
import BookingModal      from '@/components/home/BookingModal.vue'

const router = useRouter()
const { user, isLoggedIn } = useAuth()
const { createBooking, cancelBooking, getBookingId } = useBookings()

// ── Booking modal ──────────────────────────────────────────────────────────
const bookingOpen     = ref(false)
const selectedPackage = ref(null)

function openBooking(pkg) {
  selectedPackage.value = pkg
  bookingOpen.value     = true
}
async function handleBookingSubmit(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  const result = await createBooking({
    user_id:  user.value?.userID ?? user.value?.id,
    type:     'package',
    item_id:  selectedPackage.value.id,
    title:    selectedPackage.value.title,
    price:    selectedPackage.value.price,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
  })

  if (result.ok) {
    bookingOpen.value = false
    alert('Package booked successfully!')
  } else {
    alert('Failed to book: ' + result.error)
  }
}

async function handleCancel(pkg) {
  const id = getBookingId('package', pkg.id)
  if (!id) return
  const res = await cancelBooking(id)
  if (res.ok) alert('Booking cancelled successfully.')
  else alert('Failed to cancel: ' + res.error)
}

// ── Search ─────────────────────────────────────────────────────────────────
const search = ref({ dates: '', guests: null })

function handleSearch(query) {
  router.push({ path: '/search', query: { q: query } })
}

// ── Card navigation ────────────────────────────────────────────────────────

function handleDestSelect(dest)   { router.push(`/destinations/${dest.id}`) }
function handlePackageSelect(pkg) { router.push(`/packages/${pkg.id}`) }
function handleServiceSelect(svc) { router.push(`/services/${svc.id}`) }

// ── Homepage preview data ──────────────────────────────────────────────────
const homeDestinations = ref(demoDestinations.slice(0, 4))
const homePackages     = ref(demoPackages.slice(0, 6))
const homeServices     = ref(demoServices.slice(0, 8))

onMounted(async () => {
  // Fetch dynamic destinations
  try {
    const res = await fetch('/arrivo-website/backend/api/v1/listings.php')
    const data = await res.json()
    if (data.listings?.length) {
      const dbDests = data.listings.map(d => ({
        ...d,
        img: d.image,
        from: d.price
      }))
      
      // Merge: content.js takes precedence for images/titles if IDs match
      const merged = dbDests.map(dbItem => {
        const demo = demoDestinations.find(d => d.id === dbItem.id)
        return demo ? { ...dbItem, ...demo } : dbItem
      })
      homeDestinations.value = merged.slice(0, 4)
    }
  } catch (e) { console.error('Home: Failed to fetch dests', e) }

  // Dynamic destinations and services are still fetched, 
  // but packages are kept static (demo only) per request.
})

const reviews = ref([
  { id: 1, rating: 5, text: 'Voyago made planning our honeymoon completely effortless. The agency was professional and delivered everything promised. Santorini was magical!',            name: 'Amelia R.', location: 'London, UK'   },
  { id: 2, rating: 5, text: "Found an incredible desert tour in Morocco at a price I couldn't believe. The service provider was vetted and the experience was absolutely unforgettable.", name: 'Carlos M.', location: 'Madrid, Spain' },
  { id: 3, rating: 5, text: 'The private chef service in Bali was the highlight of our trip. Booking through Voyago gave us full confidence and everything went seamlessly.',              name: 'Yuki T.',   location: 'Tokyo, Japan'  },
])
</script>

<style scoped>

</style>