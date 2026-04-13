<template>
  <div class="detail-page" v-if="item">
    <NavBar />

    <div class="detail-page__inner">
      <DetailBreadcrumb parent-label="Packages" parent-path="/packages" :current="item.title" />

      <div class="detail-page__hero-wrap">
        <DetailHero
          :main-img="item.img"
          :gallery="item.gallery"
          :title="item.title"
          :saved="saved"
          @toggle-wishlist="saved = !saved"
        />
      </div>

      <!-- Title + meta -->
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
          </div>
        </div>
      </div>

      <div class="detail-page__body">
        <div class="detail-page__content">

          <!-- About -->
          <div class="pkg-about">
            <h3 class="pkg-section-title">About this package</h3>
            <p class="pkg-about__text">{{ item.longDesc }}</p>
          </div>

          <!-- Itinerary -->
          <PackageItinerary :itinerary="item.itinerary" />

          <!-- Inclusions -->
          <PackageInclusions :includes="item.includes" :excludes="item.excludes" />

          <!-- Agency -->
          <div class="pkg-section-title" style="margin-bottom:16px">About the agency</div>
          <EntityCard
            :name="item.agency"
            :bio="item.agencyBio"
            :img="item.agencyImg"
            :rating="item.agencyRating"
            :reviews="item.agencyReviews"
            entity-label="Agency"
            @contact="handleContact"
          />

          <DetailReviews
            :rating="item.rating"
            :total-reviews="item.reviews"
            :reviews="mockReviews"
          />
        </div>

        <DetailSidebar
          :price="item.price"
          price-label="per person"
          :rating="item.rating"
          :reviews="item.reviews"
          :spots="item.spots"
          :facts="item.facts"
          cta-label="Book this package"
          entity-label="agency"
          @book="bookingOpen = true"
          @message="handleContact"
        />
      </div>

      <DetailMoreLike :items="moreLike" see-all-path="/packages" @select="goToPackage" />
    </div>

    <BookingModal v-model="bookingOpen" :pkg="item" @submit="handleBooking" />
  </div>

  <div class="not-found" v-else>
    <h2>Package not found</h2>
    <RouterLink to="/packages" class="btn btn-coral">← Back to packages</RouterLink>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { packages } from '@/data/content.js'

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

const item = computed(() => packages.find(p => p.id === Number(route.params.id)))
const saved = ref(false)
const bookingOpen = ref(false)

const moreLike = computed(() =>
  packages.filter(p => p.id !== item.value?.id && p.type === item.value?.type).slice(0, 6)
)

function goToPackage(pkg) { router.push(`/packages/${pkg.id}`) }
function handleContact()  { console.log('Contact agency') }
function handleBooking(p) { console.log('Booked:', p) }

const mockReviews = [
  { id:1, name:'Amelia R.',  location:'London, UK',     rating:5, date:'May 2025', text:'The entire package was flawlessly organised. The chalet was stunning and the ski instructor was brilliant. Worth every penny.' },
  { id:2, name:'Thomas K.',  location:'Munich, Germany',rating:5, date:'Feb 2025', text:'Best ski holiday I\'ve ever had. The Zermatt day trip alone was worth the price. The off-piste guide knew the mountain perfectly.' },
  { id:3, name:'Maria S.',   location:'Rome, Italy',    rating:4, date:'Jan 2025', text:'Excellent organisation and beautiful location. The food was outstanding. We\'d have liked slightly more free time but overall brilliant.' },
  { id:4, name:'David C.',   location:'New York, USA',  rating:5, date:'Mar 2025', text:'Genuinely life-changing experience. The Swiss Alps in winter are something everyone should see. The team was professional and warm.' },
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
.detail-page__dot       { color: var(--gray-200); }
.detail-page__reviews   { color: var(--gray-400); }
.detail-page__spots     { color: var(--coral); font-weight: 600; }

.detail-page__body    { display: grid; grid-template-columns: 1fr 360px; gap: 48px; align-items: flex-start; }
.detail-page__content { min-width: 0; }

.pkg-about { margin-bottom: 36px; }
.pkg-section-title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; color: var(--indigo); margin-bottom: 16px; }
.pkg-about__text   { font-size: .95rem; color: var(--gray-600); line-height: 1.75; }

.not-found { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
.not-found h2 { font-family: 'Fraunces', serif; font-size: 1.8rem; }

@media (max-width: 900px) { .detail-page__body { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .detail-page__inner { padding: 0 4% 60px; } }
</style>