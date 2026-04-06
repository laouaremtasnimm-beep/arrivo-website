<template>
  <div class="home">

    <!-- ── Navigation ─────────────────────────── -->
    <NavBar @open-auth="openAuth" />

    <!-- ── Hero ──────────────────────────────── -->
    <HeroSection @open-auth="openAuth" />

    <!-- ── Search ────────────────────────────── -->
    <SearchBar
      :selected-dates="search.dates"
      :selected-guests="search.guests"
      @search="handleSearch"
      @filter-dates="search.datesOpen = true"
      @filter-guests="search.guestsOpen = true"
    />

    <!-- ── Special Offers ─────────────────────── -->
    <SpecialOffers :offers="specialOffers" @select="handleOfferSelect" />

    <!-- ── Destinations ───────────────────────── -->
    <DestinationGrid :destinations="destinations" @select="handleDestSelect" />

    <!-- ── Travel Packages ────────────────────── -->
    <TravelPackages :packages="packages" @book="openBooking" />

    <!-- ── Services ──────────────────────────── -->
    <ServicesGrid :services="services" @select="handleServiceSelect" />

    <!-- ── How It Works ───────────────────────── -->
    <HowItWorks />

    <!-- ── Reviews ───────────────────────────── -->
    <ReviewsSection :reviews="reviews" />

    <!-- ── Agency CTA ─────────────────────────── -->
    <AgencyCTA @open-auth="openAuth" />

    <!-- ── Newsletter ─────────────────────────── -->
    <NewsletterSection />

    <!-- ── Footer ────────────────────────────── -->
    <SiteFooter />

    <!-- ── Modals ────────────────────────────── -->
    <AuthModal
      v-model="authModal"
      @submit="handleAuthSubmit"
    />

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
const router = useRouter()

// Components

import NavBar          from '@/components/NavBar.vue'
import HeroSection     from '@/components/HeroSection.vue'
import SearchBar       from '@/components/SearchBar.vue'
import SpecialOffers   from '@/components/SpecialOffers.vue'
import DestinationGrid from '@/components/DestinationGrid.vue'
import TravelPackages  from '@/components/TravelPackages.vue'
import ServicesGrid    from '@/components/ServicesGrid.vue'
import HowItWorks      from '@/components/HowItWorks.vue'
import ReviewsSection  from '@/components/ReviewsSection.vue'
import AgencyCTA       from '@/components/AgencyCTA.vue'
import NewsletterSection from '@/components/NewsletterSection.vue'
import SiteFooter      from '@/components/SiteFooter.vue'
import AuthModal       from '@/components/AuthModal.vue'
import BookingModal    from '@/components/BookingModal.vue'

// ── Modal state ────────────────────────────────────────────────────────────
const authModal      = ref(null)   // 'login' | 'register' | null
const bookingOpen    = ref(false)
const selectedPackage = ref(null)

function openAuth(type)   { authModal.value = type }
function openBooking(pkg) { selectedPackage.value = pkg; bookingOpen.value = true }

function handleAuthSubmit(payload)    { console.log('Auth submit:', payload) }
function handleBookingSubmit(payload) { console.log('Booking submit:', payload) }

// ── Search state ───────────────────────────────────────────────────────────
const search = ref({ query: '', dates: '', guests: null, datesOpen: false, guestsOpen: false })

function handleSearch(query) {
  router.push({ path: '/search', query: { q: query } })
}

// ── Handler stubs (wire to router / store as needed) ──────────────────────
function handleOfferSelect(offer)   { console.log('Offer selected:', offer) }
function handleDestSelect(dest)     { console.log('Destination selected:', dest) }
function handleServiceSelect(svc)   { console.log('Service selected:', svc) }

// ─────────────────────────────────────────────────────────────────────────
// DATA  –  In a real app, move this to a Pinia store or fetch from API.
// ─────────────────────────────────────────────────────────────────────────

const specialOffers = ref([
  {
    offerID: 1,
    discount: 30,
    title: 'Maldives Escape',
    startDate: 'Jun 1',
    endDate: 'Jun 30',
    description: 'Overwater bungalows, reef snorkeling & more.',
  },
  {
    offerID: 2,
    discount: 20,
    title: 'Sahara Desert Tour',
    startDate: 'Jul 10',
    endDate: 'Aug 10',
    description: 'Camel treks and starlit desert camps.',
  },
  {
    offerID: 3,
    discount: 25,
    title: 'Japan Cherry Blossom',
    startDate: 'Mar 20',
    endDate: 'Apr 15',
    description: 'Tokyo, Kyoto & Osaka in bloom season.',
  },
  {
    offerID: 4,
    discount: 15,
    title: 'Greek Island Hop',
    startDate: 'May 1',
    endDate: 'Sep 30',
    description: 'Santorini, Mykonos, and Crete by ferry.',
  },
  {
    offerID: 5,
    discount: 35,
    title: 'Patagonia Trekking',
    startDate: 'Nov 1',
    endDate: 'Feb 28',
    description: 'Torres del Paine in peak summer season.',
  },
])

const destinations = ref([
  {
    id: 1,
    name: 'Santorini, Greece',
    img: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=75',
    badge: '🔥 Trending',
    rating: 4.9,
    reviews: 2140,
    from: 890,
  },
  {
    id: 2,
    name: 'Kyoto, Japan',
    img: 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=600&q=75',
    badge: '🌸 Seasonal',
    rating: 4.8,
    reviews: 1830,
    from: 1200,
  },
  {
    id: 3,
    name: 'Marrakech, Morocco',
    img: 'https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=600&q=75',
    badge: '✨ New',
    rating: 4.7,
    reviews: 950,
    from: 640,
  },
  {
    id: 4,
    name: 'Bali, Indonesia',
    img: 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=600&q=75',
    badge: '🏝️ Popular',
    rating: 4.8,
    reviews: 3200,
    from: 780,
  },
])

const packages = ref([
  {
    id: 1,
    title: 'Swiss Alps Winter Retreat',
    agency: 'Alpine Escapes',
    img: 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=700&q=75',
    type: 'Adventure',
    duration: 8,
    rating: 4.9,
    spots: 4,
    price: 2490,
    desc: 'Ski, snowboard and relax in cozy mountain chalets with breathtaking alpine views.',
  },
  {
    id: 2,
    title: 'Bali Spiritual Journey',
    agency: 'Serenity Travels',
    img: 'https://images.unsplash.com/photo-1604999565976-8913ad2ddb7c?w=700&q=75',
    type: 'Cultural',
    duration: 10,
    rating: 4.8,
    spots: 8,
    price: 1650,
    desc: 'Discover temples, rice terraces and traditional healing rituals across the island of gods.',
  },
  {
    id: 3,
    title: 'Greek Island Odyssey',
    agency: 'Aegean Blue Tours',
    img: 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=700&q=75',
    type: 'Beach',
    duration: 14,
    rating: 4.9,
    spots: 2,
    price: 3100,
    desc: 'Sail between Santorini, Mykonos and Crete on a private yacht with full crew.',
  },
  {
    id: 4,
    title: 'Sahara Desert Adventure',
    agency: 'Desert Wind Co.',
    img: 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=700&q=75',
    type: 'Adventure',
    duration: 6,
    rating: 4.7,
    spots: 12,
    price: 980,
    desc: 'Ride camels at sunset, sleep under the stars and explore ancient kasbahs in Morocco.',
  },
  {
    id: 5,
    title: 'Tokyo Family Explorer',
    agency: 'Rising Sun Tours',
    img: 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=700&q=75',
    type: 'Family',
    duration: 9,
    rating: 4.8,
    spots: 6,
    price: 2200,
    desc: 'Anime, tech, temples and theme parks — a magical Japanese adventure for the whole family.',
  },
  {
    id: 6,
    title: 'Amalfi Coast Drive',
    agency: 'Bella Italia Travel',
    img: 'https://images.unsplash.com/photo-1533606688076-b6683a5f59f1?w=700&q=75',
    type: 'Beach',
    duration: 7,
    rating: 4.9,
    spots: 5,
    price: 1890,
    desc: 'Wind along the cliffside roads of southern Italy, stopping in Positano, Ravello and Capri.',
  },
])

const services = ref([
  {
    id: 1,
    icon: '🚐',
    iconBg: 'svc-icon-coral',
    title: 'Private Airport Transfer',
    desc: 'Comfortable door-to-door transfers from any airport. Available 24/7.',
    rating: 4.9,
    price: 45,
  },
  {
    id: 2,
    icon: '🧗',
    iconBg: 'svc-icon-teal',
    title: 'Mountain Guide',
    desc: 'Certified local guides for hiking, climbing and multi-day treks.',
    rating: 4.8,
    price: 120,
  },
  {
    id: 3,
    icon: '🍽️',
    iconBg: 'svc-icon-sand',
    title: 'Private Chef Experience',
    desc: 'A local chef comes to your villa and prepares an authentic dinner.',
    rating: 5.0,
    price: 180,
  },
  {
    id: 4,
    icon: '🤿',
    iconBg: 'svc-icon-coral',
    title: 'Scuba Diving Lessons',
    desc: 'PADI-certified instructors for beginners and advanced divers alike.',
    rating: 4.9,
    price: 95,
  },
  {
    id: 5,
    icon: '📸',
    iconBg: 'svc-icon-teal',
    title: 'Travel Photography',
    desc: 'Professional photographer to document your journey with stunning shots.',
    rating: 4.7,
    price: 150,
  },
  {
    id: 6,
    icon: '🏨',
    iconBg: 'svc-icon-sand',
    title: 'Luxury Hotel Concierge',
    desc: 'Premium hotel bookings with curated insider recommendations.',
    rating: 4.8,
    price: 30,
  },
  {
    id: 7,
    icon: '🗺️',
    iconBg: 'svc-icon-coral',
    title: 'City Walking Tour',
    desc: 'Immersive 3-hour walking tours in 50+ cities with expert local guides.',
    rating: 4.9,
    price: 35,
  },
  {
    id: 8,
    icon: '🧘',
    iconBg: 'svc-icon-teal',
    title: 'Wellness & Spa Retreat',
    desc: 'Yoga, meditation and spa packages at partner wellness resorts.',
    rating: 4.8,
    price: 200,
  },
])

const reviews = ref([
  {
    id: 1,
    rating: 5,
    text: 'Voyago made planning our honeymoon completely effortless. The agency we booked through was professional, responsive and delivered everything they promised. Santorini was magical!',
    name: 'Amelia R.',
    location: 'London, UK',
  },
  {
    id: 2,
    rating: 5,
    text: 'Found an incredible desert tour in Morocco at a price I couldn\'t believe. The service provider was vetted and the experience was absolutely unforgettable. Will definitely use again!',
    name: 'Carlos M.',
    location: 'Madrid, Spain',
  },
  {
    id: 3,
    rating: 5,
    text: 'The private chef service in Bali was the highlight of our entire trip. Booking through Voyago gave us full confidence and everything went seamlessly. Highly recommended platform!',
    name: 'Yuki T.',
    location: 'Tokyo, Japan',
  },
])
</script>

<style scoped>
.home {
  /* Ensure child sections stack properly below the fixed navbar */
  padding-top: 72px;
}
</style>
