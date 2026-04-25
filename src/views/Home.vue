<template>
  <div class="home">

    <NavBar />
    <HeroSection />

    <SearchBar
      :selected-dates="search.dates"
      :selected-guests="search.guests"
      @search="handleSearch"
    />

    <HotOffers @select="handleOfferSelect" />

    <DestinationGrid :destinations="homeDestinations" @select="handleDestSelect" />
    <TravelPackages  :packages="homePackages"         @select="handlePackageSelect" @book="openBooking" />
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { destinations, packages, services } from '@/data/content.js'

import NavBar            from '@/components/home/NavBar.vue'
import HeroSection       from '@/components/home/HeroSection.vue'
import SearchBar         from '@/components/home/SearchBar.vue'
import HotOffers         from '@/components/home/HotOffers.vue'        // ← was SpecialOffers
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

// ── Booking modal ──────────────────────────────────────────────────────────
const bookingOpen     = ref(false)
const selectedPackage = ref(null)

function openBooking(pkg) {
  selectedPackage.value = pkg
  bookingOpen.value     = true
}
function handleBookingSubmit(payload) {
  console.log('Booking submitted:', payload)
}

// ── Search ─────────────────────────────────────────────────────────────────
const search = ref({ dates: '', guests: null })

function handleSearch(query) {
  router.push({ path: '/search', query: { q: query } })
}

// ── Card navigation ────────────────────────────────────────────────────────
function handleOfferSelect(offer) {
  router.push({ path: '/search', query: { q: offer.title } })
}
function handleDestSelect(dest)   { router.push(`/destinations/${dest.id}`) }
function handlePackageSelect(pkg) { router.push(`/packages/${pkg.id}`) }
function handleServiceSelect(svc) { router.push(`/services/${svc.id}`) }

// ── Homepage preview data ──────────────────────────────────────────────────
const homeDestinations = ref(destinations.slice(0, 4))
const homePackages     = ref(packages.slice(0, 6))
const homeServices     = ref(services.slice(0, 8))

const reviews = ref([
  { id: 1, rating: 5, text: 'Voyago made planning our honeymoon completely effortless. The agency was professional and delivered everything promised. Santorini was magical!',            name: 'Amelia R.', location: 'London, UK'   },
  { id: 2, rating: 5, text: "Found an incredible desert tour in Morocco at a price I couldn't believe. The service provider was vetted and the experience was absolutely unforgettable.", name: 'Carlos M.', location: 'Madrid, Spain' },
  { id: 3, rating: 5, text: 'The private chef service in Bali was the highlight of our trip. Booking through Voyago gave us full confidence and everything went seamlessly.',              name: 'Yuki T.',   location: 'Tokyo, Japan'  },
])
</script>

<style scoped>
.home { padding-top: 72px; }
</style>