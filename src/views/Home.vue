<template>
  <div class="home">

    <NavBar />
    <HeroSection />

    <SearchBar
      :selected-dates="search.dates"
      :selected-guests="search.guests"
      @search="handleSearch"
    />

    <SpecialOffers   :offers="specialOffers"      @select="handleOfferSelect"   />
    <DestinationGrid :destinations="homeDestinations" @select="handleDestSelect" />
    <TravelPackages  :packages="homePackages"      @select="handlePackageSelect" @book="openBooking" />
    <ServicesGrid    :services="homeServices"      @select="handleServiceSelect" />
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

// ── All imports use the correct paths ─────────────────────────────────────
import NavBar          from '@/components/home/NavBar.vue'
import HeroSection     from '@/components/home/HeroSection.vue'
import SearchBar       from '@/components/home/SearchBar.vue'
import SpecialOffers   from '@/components/home/SpecialOffers.vue'
import DestinationGrid from '@/components/home/DestinationGrid.vue'
import TravelPackages  from '@/components/home/TravelPackages.vue'
import ServicesGrid    from '@/components/home/ServicesGrid.vue'
import HowItWorks      from '@/components/home/HowItWorks.vue'
import ReviewsSection  from '@/components/home/ReviewsSection.vue'
import AgencyCTA       from '@/components/home/AgencyCTA.vue'
import NewsletterSection from '@/components/home/NewsletterSection.vue'
import SiteFooter      from '@/components/home/SiteFooter.vue'
import AuthModal       from '@/components/home/AuthModal.vue'
import BookingModal    from '@/components/home/BookingModal.vue'

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

// ── Card navigation — every card click goes to its detail page ─────────────
function handleOfferSelect(offer) {
  router.push({ path: '/search', query: { q: offer.title } })
}
function handleDestSelect(dest) {
  router.push(`/destinations/${dest.id}`)
}
function handlePackageSelect(pkg) {
  router.push(`/packages/${pkg.id}`)
}
function handleServiceSelect(svc) {
  router.push(`/services/${svc.id}`)
}

// ── Homepage preview data — sliced from content.js ────────────────────────
const homeDestinations = ref(destinations.slice(0, 4))
const homePackages     = ref(packages.slice(0, 6))
const homeServices     = ref(services.slice(0, 8))

const specialOffers = ref([
  { offerID: 1, discount: 30, title: 'Maldives Escape',      startDate: 'Jun 1',  endDate: 'Jun 30', description: 'Overwater bungalows, reef snorkeling & more.'   },
  { offerID: 2, discount: 20, title: 'Sahara Desert Tour',   startDate: 'Jul 10', endDate: 'Aug 10', description: 'Camel treks and starlit desert camps.'           },
  { offerID: 3, discount: 25, title: 'Japan Cherry Blossom', startDate: 'Mar 20', endDate: 'Apr 15', description: 'Tokyo, Kyoto & Osaka in bloom season.'           },
  { offerID: 4, discount: 15, title: 'Greek Island Hop',     startDate: 'May 1',  endDate: 'Sep 30', description: 'Santorini, Mykonos, and Crete by ferry.'         },
  { offerID: 5, discount: 35, title: 'Patagonia Trekking',   startDate: 'Nov 1',  endDate: 'Feb 28', description: 'Torres del Paine in peak summer season.'         },
])

const reviews = ref([
  { id: 1, rating: 5, text: 'Voyago made planning our honeymoon completely effortless. The agency was professional and delivered everything promised. Santorini was magical!',          name: 'Amelia R.', location: 'London, UK'   },
  { id: 2, rating: 5, text: "Found an incredible desert tour in Morocco at a price I couldn't believe. The service provider was vetted and the experience was absolutely unforgettable.", name: 'Carlos M.', location: 'Madrid, Spain' },
  { id: 3, rating: 5, text: 'The private chef service in Bali was the highlight of our trip. Booking through Voyago gave us full confidence and everything went seamlessly.',              name: 'Yuki T.',   location: 'Tokyo, Japan'  },
])
</script>

<style scoped>
.home { padding-top: 72px; }
</style>