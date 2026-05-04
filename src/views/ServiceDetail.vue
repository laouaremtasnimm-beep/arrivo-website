<template>
  <!-- Loading State -->
  <div class="svc-detail-loading" v-if="!item">
    <NavBar />
    <div class="svc-detail-loading__inner">
      <div class="svc-loading-spinner"></div>
      <p>Loading service details…</p>
    </div>
  </div>

  <div class="svc-detail" v-else>
    <NavBar />

    <div class="svc-detail__inner">
      <DetailBreadcrumb parent-label="Services" parent-path="/services" :current="item.title" />

      <!-- Emoji Hero Banner -->
      <div class="svc-hero" :class="item.iconBg || 'svc-icon-teal'">
        <div class="svc-hero__glow"></div>
        <div class="svc-hero__emoji">{{ item.icon || '🛎️' }}</div>
        <div class="svc-hero__meta">
          <div class="svc-hero__tags">
            <span class="svc-hero__type">{{ item.type }}</span>
            <span class="svc-hero__offer-tag" v-if="item.activeOffer">Special Offer</span>
          </div>
          <div class="svc-hero__actions">
            <button
              class="svc-hero__btn"
              :class="{ saved: isSavedVal }"
              @click="handleToggleWishlist"
            >{{ isSavedVal ? '❤️ Saved' : '🤍 Save' }}</button>
            <button class="svc-hero__btn" @click="handleShare">🔗 Share</button>
          </div>
        </div>
      </div>

      <!-- Title Row -->
      <div class="svc-detail__title-row">
        <div class="svc-detail__title-left">
          <h1 class="svc-detail__title">{{ item.title }}</h1>
          <div class="svc-detail__meta-row">
            <span class="svc-detail__provider-badge">by <strong>{{ item.provider }}</strong></span>
            <span class="svc-detail__dot">·</span>
            <span class="svc-detail__rating">
              <span class="svc-star">★</span>
              {{ Number(item.rating).toFixed(1) }}
              <span class="svc-detail__review-count">({{ item.reviews }} reviews)</span>
            </span>
            <span class="svc-detail__dot">·</span>
            <span
              class="svc-detail__avail"
              :class="item.availability ? 'avail--yes' : 'avail--no'"
            >
              {{ item.availability ? '● Available now' : '○ Unavailable' }}
            </span>
            <span v-if="alreadyBooked" class="svc-detail__booked-badge">✓ Already booked</span>
          </div>
        </div>
      </div>

      <!-- Two-column body -->
      <div class="svc-detail__body">
        <div class="svc-detail__content">

          <ServiceAbout :long-desc="item.longDesc" />

          <ServiceFAQ :faqs="item.faqs" />

          <ServiceInclusions
            :features="item.features"
            :excludes="item.excludes"
            :options="item.vehicleOptions"
            :options-title="item.type === 'Transport' ? 'Vehicle options' : 'Options'"
            :unit="item.unit"
            @select-option="handleOptionSelect"
          />

            <EntityCard
              ref="entityCardRef"
              :name="item.provider"
              :bio="item.providerBio"
              :rating="item.providerRating || 0"
              :reviews="item.providerReviews || 0"
              :receiver-id="item.provider_id"
              entity-label="Provider"
              hide-card
              @contact="handleContact"
            />

        </div>

        <DetailSidebar
          class="sticky-sidebar"
          :price="item.price"
          price-label="from"
          :unit="item.unit"
          :rating="item.rating"
          :reviews="item.reviews"
          :facts="item.facts"
          :cta-label="ctaLabel"
          :cta-danger="alreadyBooked && !isOwner"
          :is-owner="isOwner"
          :active-offer="item.activeOffer"
          :start-date="item.startDate"
          :end-date="item.endDate"
          entity-label="Contact Provider"
          note="You won't be charged until confirmed."
          @book="handleCTAClick"
          @cancel="handleCancel"
          @message="handleContact"
        />
      </div>

      <DetailReviews
        :rating="item.rating"
        :total-reviews="item.reviews"
        :reviews="isDemo ? mockReviews : []"
        item-type="service"
        :item-id="item.id"
        :can-moderate="isOwner"
        :item-owner-id="item.provider_id"
        :hide-write-review="isOwner"
        @stats-update="updateStats"
      />

      <DetailMoreLike :items="moreLike" see-all-path="/services" @select="goToService" />
    </div>

    <BookingModal v-model="bookingOpen" :pkg="item" @submit="handleBooking" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
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
import ServiceAbout      from '@/components/detail/svc/ServiceAbout.vue'
import ServiceInclusions from '@/components/detail/svc/ServiceInclusions.vue'
import ServiceFAQ        from '@/components/detail/svc/ServiceFAQ.vue'
import BookingModal     from '@/components/home/BookingModal.vue'

const route  = useRoute()
const router = useRouter()

const { isSaved, toggle }                    = useWishlist()
const { user, isLoggedIn }                   = useAuth()
const { isBooked, createBooking, fetchBookings, loaded, getBookingId, cancelBooking } = useBookings()

const item    = ref(null)
const loading = ref(true)
const moreLike = ref([])
const bookingOpen = ref(false)

const API = '/arrivo-website/backend/api/v1'

function normalizeService(s) {
  return {
    id:           s.id,
    title:        s.title,
    provider:     s.provider_name ?? s.provider    ?? 'Provider',
    icon:         s.icon          ?? '🛎️',
    iconBg:       s.icon_bg       ?? 'svc-icon-coral',
    type:         s.type          ?? 'Transport',
    price:        Number(s.price),
    unit:         s.price_unit    ?? s.unit         ?? 'day',
    rating:       Number(s.rating ?? 4.5),
    reviews:      Number(s.review_count ?? s.reviews ?? 0),
    availability: s.is_available  == 1,
    longDesc:     s.long_desc      ?? s.description  ?? '',
    img:          s.img_url       ?? null,
    provider_id:  s.provider_id   ?? null,
    startDate:    s.start_date    ?? s.startDate,
    endDate:      s.end_date      ?? s.endDate,
    activeOffer: (s.active_offer_id || s.activeOffer?.id) ? {
      id:        s.active_offer_id       || s.activeOffer?.id,
      discount:  s.active_offer_discount || s.activeOffer?.discount,
      startDate: s.active_offer_start    || s.activeOffer?.startDate,
      endDate:   s.active_offer_end      || s.activeOffer?.endDate,
      title:     s.active_offer_title    || s.activeOffer?.title
    } : null,
    features:     typeof s.features === 'string'
                    ? JSON.parse(s.features || '[]')
                    : (s.features ?? []),
    faqs:         typeof s.faqs === 'string'
                    ? JSON.parse(s.faqs || '[]')
                    : (s.faqs ?? []),
  }
}



async function loadItem(id) {
  window.scrollTo({ top: 0, behavior: 'smooth' })
  loading.value  = true
  item.value     = null

  moreLike.value = []
  id = Number(id)

  try {
    const res = await fetch(`${API}/services.php?id=${id}`)
    if (res.ok) {
      const data = await res.json()
      if (data.service) {
        const s = normalizeService(data.service)
        const demo = services.find(x => x.id === id)
        if (demo) {
          // Merge: demo provides fallback for some UI fields, DB/Offer wins for data
          item.value = { ...normalizeService(demo), ...s }
        } else {
          item.value = s
        }
      }
    }
  } catch (e) {
    console.error('Failed to fetch service from DB:', e)
  }

  if (!item.value) {
    const mockS = services.find(s => s.id === id)
    if (mockS) item.value = { ...mockS, longDesc: mockS.desc, reviews: Number(mockS.reviews), rating: Number(mockS.rating), availability: !!mockS.availability }
  }

  try {
    const allRes  = await fetch(`${API}/services.php`)
    const allData = await allRes.json()
    const dbRows  = allData.services || []
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
        .filter(x => String(x.id) !== String(item.value.id) && x.type === item.value.type)
        .slice(0, 6)
    }
  } catch (e) { console.error(e) }

  loading.value = false
}

onMounted(() => {
  if (isLoggedIn.value && !loaded.value) fetchBookings(user.value)
  loadItem(route.params.id)
})
watch(() => route.params.id, (newId) => { if (newId) loadItem(newId) })
const isDemo = computed(() => {
  if (!item.value) return false
  return services.some(s => s.id === item.value.id)
})

const isSavedVal    = computed(() => item.value ? isSaved.value('service', item.value.id) : false)
const alreadyBooked = computed(() => item.value ? isBooked('service', item.value.id, item.value.activeOffer?.id) : false)
const isOwner = computed(() => {
  if (!item.value || !user.value) return false
  const uid = String(user.value.userID || user.value.id)
  const oid = String(item.value.provider_id || item.value.userId || item.value.owner_id || item.value.item_owner_id || '')
  
  console.log('--- Service Ownership Check ---')
  console.log('User ID (uid):', uid)
  console.log('Service Owner ID (oid):', oid)
  console.log('Is Owner:', oid !== '' && oid === uid)
  console.log('Raw item data:', item.value)
  
  return oid !== '' && oid === uid
})

const ctaLabel = computed(() => {
  if (isOwner.value) return 'Manage Service'
  if (alreadyBooked.value) {
    return item.value?.activeOffer ? 'Cancel Offer' : 'Cancel Booking'
  }
  return item.value?.activeOffer ? 'Book Offer' : 'Book this service'
})

function handleCTAClick() {
  if (isOwner.value) {
    router.push('/dashboard')
  } else {
    bookingOpen.value = true
  }
}

function handleToggleWishlist() {
  if (!item.value) return
  toggle('service', item.value.id)
}

const entityCardRef = ref(null)

function goToService(svc)       { router.push(`/services/${svc.id}`) }
function handleContact() {
  if (entityCardRef.value) {
    entityCardRef.value.modalOpen = true
  }
}
function handleOptionSelect()   { bookingOpen.value = true }

async function handleBooking(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  const isOffer = !!item.value.activeOffer
  let finalPrice = item.value.price
  if (isOffer) {
    finalPrice = Math.round(finalPrice * (1 - item.value.activeOffer.discount / 100))
  }

  const result = await createBooking({
    user_id:  user.value?.userID ?? user.value?.id,
    type:     isOffer ? 'offer' : 'service',
    item_id:  isOffer ? item.value.activeOffer.id : item.value.id,
    service_id: item.value.id,
    title:    isOffer ? item.value.activeOffer.title : item.value.title,
    price:    finalPrice,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
    target_user_id: item.value.provider_id
  })

  if (result.ok) {
    bookingOpen.value = false
    alert('Service booked successfully!')
  } else {
    alert('Failed to book service: ' + result.error)
  }
}

async function handleCancel() {
  const id = getBookingId('service', item.value.id, item.value.activeOffer?.id)
  if (!id) return
  const res = await cancelBooking(id)
  if (res.ok) alert('Booking cancelled successfully.')
  else alert('Failed to cancel: ' + res.error)
}

const mockReviews = [
  { id:1, name:'Lena M.',   location:'Berlin, Germany', rating:5, date:'Jun 2025', text:'Absolutely seamless experience. The driver was waiting with a sign, the car was immaculate and the price was exactly as quoted. Will use every time.' },
  { id:2, name:'James O.',  location:'Dublin, Ireland', rating:5, date:'May 2025', text:'Professional, punctual and friendly. The flight was delayed by 90 minutes and they tracked it and were there waiting. Fantastic service.' },
  { id:3, name:'Yuki T.',   location:'Tokyo, Japan',    rating:4, date:'Apr 2025', text:'Good service overall. The vehicle was clean and modern. Slightly difficult to find the driver at first but they were very helpful once we connected.' },
  { id:4, name:'Sophie L.', location:'Paris, France',   rating:5, date:'Mar 2025', text:"Worth every cent. No stress, no searching, just a friendly face with your name on a board. Makes such a difference after a long flight." },
]
function updateStats(stats) {
  if (item.value) {
    item.value.rating = stats.rating
    item.value.reviews = stats.count
  }
}

function handleShare() {
  if (navigator.share) {
    navigator.share({ title: item.value?.title, url: window.location.href })
  } else {
    navigator.clipboard?.writeText(window.location.href)
    alert('Link copied to clipboard!')
  }
}
</script>

<style scoped>
/* ─── Page shell ─────────────────────────────────── */
.svc-detail { min-height: 100vh; background: var(--gray-50); padding-top: 72px; }
.svc-detail__inner { max-width: 1240px; margin: 0 auto; padding: 0 5% 100px; }

/* ─── Spinner ────────────────────────────────────── */
.svc-detail-loading { min-height: 100vh; background: var(--gray-50); }
.svc-detail-loading__inner { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; min-height: 60vh; color: var(--gray-400); }
.svc-loading-spinner { width: 36px; height: 36px; border: 3px solid var(--gray-200); border-top-color: var(--teal); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Emoji Hero Banner ──────────────────────────── */
.svc-hero {
  position: relative; margin: 24px 0 0;
  height: 260px; border-radius: 24px; overflow: hidden;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  background: linear-gradient(135deg, rgba(46,196,182,.18) 0%, rgba(99,102,241,.14) 100%);
  border: 1px solid rgba(46,196,182,.2);
}
.svc-icon-coral .svc-hero { background: linear-gradient(135deg, rgba(255,90,95,.14) 0%, rgba(255,170,80,.10) 100%); border-color: rgba(255,90,95,.2); }
.svc-icon-sand  .svc-hero { background: linear-gradient(135deg, rgba(240,230,210,.6) 0%, rgba(200,185,160,.4) 100%); border-color: rgba(200,185,160,.4); }

.svc-hero__glow {
  position: absolute; width: 220px; height: 220px;
  background: radial-gradient(circle, rgba(46,196,182,.22) 0%, transparent 70%);
  border-radius: 50%; pointer-events: none;
}
.svc-hero__emoji { font-size: 5.5rem; line-height: 1; position: relative; z-index: 2; filter: drop-shadow(0 8px 24px rgba(0,0,0,.12)); }
.svc-hero__meta {
  position: absolute; top: 16px; left: 20px; right: 20px;
  display: flex; align-items: center; justify-content: space-between; z-index: 3;
}
.svc-hero__type {
  background: rgba(255,255,255,.88); backdrop-filter: blur(8px);
  padding: 5px 14px; border-radius: 50px;
  font-size: .74rem; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .06em;
}
.svc-hero__actions { display: flex; gap: 8px; }
.svc-hero__btn {
  background: rgba(255,255,255,.88); backdrop-filter: blur(8px);
  border: none; border-radius: 50px; padding: 6px 16px;
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 600;
  color: var(--indigo); cursor: pointer; transition: all 0.2s;
}
.svc-hero__btn:hover { background: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.svc-hero__btn.saved { background: rgba(255,90,95,.1); color: var(--coral); }

.svc-hero__tags { display: flex; align-items: center; gap: 10px; }
.svc-hero__offer-tag {
  background: var(--coral); color: #fff;
  padding: 5px 14px; border-radius: 50px;
  font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
  box-shadow: 0 4px 12px rgba(255, 90, 95, 0.3);
  animation: pulse-coral 2s infinite;
}
@keyframes pulse-coral {
  0% { box-shadow: 0 0 0 0 rgba(255, 90, 95, 0.4); }
  70% { box-shadow: 0 0 0 8px rgba(255, 90, 95, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 90, 95, 0); }
}

/* ─── Title Row ──────────────────────────────────── */
.svc-detail__title-row { margin: 28px 0 36px; }
.svc-detail__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(1.9rem, 3.5vw, 2.8rem);
  font-weight: 700; color: var(--indigo);
  margin: 0 0 12px; line-height: 1.15;
}
.svc-detail__meta-row {
  display: flex; align-items: center; gap: 10px;
  flex-wrap: wrap; font-size: .9rem; color: var(--gray-600);
}
.svc-detail__dot { color: var(--gray-300); }
.svc-detail__provider-badge strong { color: var(--indigo); }
.svc-detail__rating { display: flex; align-items: center; gap: 4px; font-weight: 600; color: var(--indigo); }
.svc-star { color: #FFB400; }
.svc-detail__review-count { color: var(--gray-400); font-weight: 400; }
.avail--yes { color: #27ae60; font-weight: 700; }
.avail--no  { color: var(--gray-400); font-weight: 600; }
.svc-detail__booked-badge { background: rgba(39,174,96,.1); color: #1a7a45; font-size: .78rem; font-weight: 700; padding: 4px 12px; border-radius: 50px; border: 1px solid rgba(39,174,96,.2); }

/* ─── Two-column body ────────────────────────────── */
.svc-detail__body { display: grid; grid-template-columns: 1fr 360px; gap: 40px; align-items: start;}
.svc-detail__content { min-width: 0; }

/* ─── Section headings ───────────────────────────── */
.svc-detail__section { margin-bottom: 40px; }
.svc-detail__section-title { font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700; color: var(--indigo); margin: 0 0 18px; }

/* ─── Not found ──────────────────────────────────── */
.not-found { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
.not-found h2 { font-family: 'Fraunces', serif; font-size: 1.8rem; }

/* ─── Responsive ─────────────────────────────────── */
@media (max-width: 960px) { .svc-detail__body { grid-template-columns: 1fr; } }
@media (max-width: 640px)  {
  .svc-detail__inner { padding: 0 4% 60px; }
  .svc-hero { height: 200px; }
  .svc-hero__emoji { font-size: 4rem; }
}
</style>