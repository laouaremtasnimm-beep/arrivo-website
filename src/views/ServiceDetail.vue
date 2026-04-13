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
            by <strong>{{ item.provider }}</strong>
            <span class="detail-page__dot">·</span>
            <span class="star">★</span> {{ item.rating }}
            <span class="detail-page__reviews">({{ item.reviews }} reviews)</span>
            <span class="detail-page__avail" :class="item.availability ? 'avail--yes' : 'avail--no'">
              · {{ item.availability ? '● Available' : '○ Unavailable' }}
            </span>
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

          <!-- Provider -->
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
          />
        </div>

        <DetailSidebar
          :price="item.price"
          :price-label="'from'"
          :unit="item.unit"
          :rating="item.rating"
          :reviews="item.reviews"
          :facts="item.facts"
          cta-label="Book this service"
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
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { services } from '@/data/content.js'

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

const item = computed(() => services.find(s => s.id === Number(route.params.id)))
const saved = ref(false)
const bookingOpen = ref(false)

const moreLike = computed(() =>
  services.filter(s => s.id !== item.value?.id && s.type === item.value?.type).slice(0, 6)
)

function goToService(svc)        { router.push(`/services/${svc.id}`) }
function handleContact()         { console.log('Contact provider') }
function handleOptionSelect(opt) { console.log('Selected option:', opt); bookingOpen.value = true }
function handleBooking(p)        { console.log('Service booked:', p) }

const mockReviews = [
  { id:1, name:'Lena M.',   location:'Berlin, Germany', rating:5, date:'Jun 2025', text:'Absolutely seamless experience. The driver was waiting with a sign, the car was immaculate and the price was exactly as quoted. Will use every time.' },
  { id:2, name:'James O.',  location:'Dublin, Ireland', rating:5, date:'May 2025', text:'Professional, punctual and friendly. The flight was delayed by 90 minutes and they tracked it and were there waiting. Fantastic service.' },
  { id:3, name:'Yuki T.',   location:'Tokyo, Japan',    rating:4, date:'Apr 2025', text:'Good service overall. The vehicle was clean and modern. Slightly difficult to find the driver at first but they were very helpful once we connected.' },
  { id:4, name:'Sophie L.', location:'Paris, France',   rating:5, date:'Mar 2025', text:'Worth every cent. No stress, no searching, just a friendly face with your name on a board. Makes such a difference after a long flight.' },
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
.avail--yes { color: #27ae60; font-weight: 600; }
.avail--no  { color: var(--gray-400); }

.detail-page__body    { display: grid; grid-template-columns: 1fr 360px; gap: 48px; align-items: flex-start; }
.detail-page__content { min-width: 0; }
.pkg-section-title    { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; color: var(--indigo); }

.not-found { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
.not-found h2 { font-family: 'Fraunces', serif; font-size: 1.8rem; }

@media (max-width: 900px) { .detail-page__body { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .detail-page__inner { padding: 0 4% 60px; } }
</style>