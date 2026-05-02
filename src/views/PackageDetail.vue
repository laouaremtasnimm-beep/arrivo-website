<template>
  <!-- Loading skeleton -->
  <div class="pkg-loading" v-if="loading">
    <NavBar />
    <div class="pkg-loading__inner"><div class="pkg-spinner"></div><p>Loading package…</p></div>
  </div>

  <div class="pkg-detail" v-else-if="item">
    <NavBar />

    <div class="pkg-detail__inner">
      <DetailBreadcrumb parent-label="Packages" parent-path="/packages" :current="item.title" />

      <!-- Hero -->
      <div class="pkg-detail__hero">
        <DetailHero
          :main-img="item.img"
          :gallery="item.gallery"
          :title="item.title"
          :saved="isSavedVal"
          @toggle-wishlist="handleToggleWishlist"
        />
      </div>

      <!-- Title Row -->
      <div class="pkg-detail__title-row">
        <div class="pkg-detail__title-left">
          <div class="pkg-detail__badges">
            <span class="pkg-badge pkg-badge--type">{{ item.type }}</span>
            <span class="pkg-badge pkg-badge--duration">📅 {{ item.duration }} days</span>
            <span class="pkg-badge pkg-badge--spots" v-if="item.spots <= 5">🔥 {{ item.spots }} spots left</span>
            <span class="pkg-badge pkg-badge--booked" v-if="alreadyBooked">✓ Already booked</span>
          </div>
          <h1 class="pkg-detail__title">{{ item.title }}</h1>
          <div class="pkg-detail__meta">
            <span class="pkg-detail__agency">by <strong>{{ item.agency }}</strong></span>
            <span class="pkg-detail__sep">·</span>
            <span class="pkg-detail__rating">
              <span class="pkg-detail__star">★</span>
              {{ Number(item.rating).toFixed(1) }}
              <span class="pkg-detail__rev-count">({{ item.reviews }} reviews)</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Two-column body -->
      <div class="pkg-detail__body">
        <div class="pkg-detail__content">

          <div class="pkg-detail__about detail-card">
            <h3 class="pkg-section-title">About this package</h3>
            <p v-if="item.longDesc?.length > 350" class="pkg-detail__about-text">
              {{ item.longDesc.slice(0, 320) }}...
              <button class="read-more-btn" @click="isAboutModalOpen = true">Read more</button>
            </p>
            <p v-else class="pkg-detail__about-text">{{ item.longDesc }}</p>
          </div>

          <!-- ── Full Description Modal ── -->
          <Teleport to="body">
            <Transition name="modal-fade">
              <div v-if="isAboutModalOpen" class="desc-modal-overlay" @click.self="isAboutModalOpen = false">
                <div class="desc-modal">
                  <div class="desc-modal__header">
                    <h3>About this package</h3>
                    <button class="desc-modal__close" @click="isAboutModalOpen = false">✕</button>
                  </div>
                  <div class="desc-modal__content">
                    <p>{{ item.longDesc }}</p>
                  </div>
                </div>
              </div>
            </Transition>
          </Teleport>

          <PackageItinerary :itinerary="item.itinerary" />
          <PackageInclusions class="detail-card" :includes="item.includes" :excludes="item.excludes" />

            <EntityCard
              ref="entityCardRef"
              :name="item.agency"
              :bio="item.agencyBio"
              :img="item.agencyImg"
              :rating="item.agencyRating || 0"
              :reviews="item.agencyReviews || 0"
              :receiver-id="item.agency_id"
              entity-label="Agency"
              hide-card
              @contact="handleContact"
            />

        </div>

        <DetailSidebar
          class="sticky-sidebar"
          :price="item.price"
          price-label="per person"
          :rating="item.rating"
          :reviews="item.reviews"
          :spots="item.spots"
          :facts="item.facts"
          :cta-label="ctaLabel"
          :cta-danger="alreadyBooked && !isOwner"
          :is-owner="isOwner"
          entity-label="Contact Agency"
          @book="handleCTAClick"
          @cancel="handleCancel"
          @message="handleContact"
        />
      </div>

      <DetailReviews
        :rating="item.rating"
        :total-reviews="item.reviews"
        :reviews="isDemo ? mockReviews : []"
        item-type="package"
        :item-id="item.id"
        @stats-update="updateStats"
      />

      <DetailMoreLike :items="moreLike" see-all-path="/packages" @select="goToPackage" />
    </div>

    <BookingModal v-model="bookingOpen" :pkg="item" @submit="handleBooking" />
  </div>

  <div v-else class="pkg-not-found">
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

const { isSaved, toggle } = useWishlist()
const { user, isLoggedIn } = useAuth()
const { isBooked, createBooking, fetchBookings, loaded, getBookingId, cancelBooking } = useBookings()

const item = ref(null)
const loading = ref(true)
const moreLike = ref([])
const bookingOpen = ref(false)
const isAboutModalOpen = ref(false)

const API = '/arrivo-website/backend/api/v1'

async function loadItem(id) {
  window.scrollTo({ top: 0, behavior: 'smooth' })
  loading.value = true
  item.value = null
  moreLike.value = []
  id = Number(id)

  try {
    const res = await fetch(`${API}/packages.php?id=${id}`)
    if (res.ok) {
      const data = await res.json()
      if (data.package) {
        const p = data.package
        const dbItem = {
          agency_id: p.agency_id,
          id: p.id, title: p.title, agency: p.agency_name || 'Unknown Agency',
          img: p.img_url || p.img || 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg',
          type: p.type, duration: p.duration_days,
          rating: Number(p.rating ?? 0), reviews: Number(p.review_count || 0),
          spots: Number(p.spots_available || 0), price: Number(p.price || 0),
          longDesc: p.long_desc || p.description || '',
          includes:  p.includes  && p.includes  !== 'null' ? JSON.parse(p.includes)  : [],
          excludes:  p.excludes  && p.excludes  !== 'null' ? JSON.parse(p.excludes)  : [],
          itinerary: p.itinerary && p.itinerary !== 'null' ? JSON.parse(p.itinerary) : [],
        }
        const demo = packages.find(x => x.id === id)
        if (demo) {
          // Merge: demo provides static assets, but DB provides real-time stats
          item.value = { 
            ...dbItem, 
            ...demo, 
            rating: dbItem.rating > 0 ? dbItem.rating : demo.rating,
            reviews: dbItem.review_count > 0 ? dbItem.review_count : demo.reviews,
            agency_id: dbItem.agency_id 
          }
        } else {
          item.value = dbItem
        }
      }
    }
  } catch (e) {
    console.error('Failed to fetch package from DB:', e)
  }

  // Fallback to static data if DB fails
  if (!item.value) {
    const mockP = packages.find(p => p.id === id)
    if (mockP) item.value = { ...mockP, longDesc: mockP.desc, reviews: Number(mockP.reviews), rating: Number(mockP.rating) }
  }

  // Load "More Like This"
  try {
    const allRes = await fetch(`${API}/packages.php`)
    const allData = await allRes.json()
    const dbRows = allData.packages || []
    const demoTitles = new Set(packages.map(p => p.title))
    const newOnly = dbRows.filter(p => !demoTitles.has(p.title)).map(p => ({
      id: p.id, title: p.title, agency: p.agency_name || 'Unknown Agency',
      img: p.img_url || p.img || 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg',
      type: p.type, duration: p.duration_days,
      rating: Number(p.rating), reviews: Number(p.review_count),
      spots: Number(p.spots_available), price: Number(p.price)
    }))
    const allItems = [...packages, ...newOnly]
    if (item.value) {
      moreLike.value = allItems
        .filter(x => String(x.id) !== String(item.value.id) && x.type === item.value.type)
        .slice(0, 6)
    }
  } catch (e) { console.error(e) }

  loading.value = false
}

// FIXED: Properly closed onMounted
onMounted(() => {
  if (isLoggedIn.value && !loaded.value) fetchBookings(user.value)
  loadItem(route.params.id)
})
watch(() => route.params.id, (newId) => { if (newId) loadItem(newId) })

// FIXED: Computed properties moved outside of onMounted
const isDemo = computed(() => {
  if (!item.value) return false
  return packages.some(p => p.id === item.value.id)
})

const isSavedVal    = computed(() => item.value ? isSaved.value('package', item.value.id) : false)
const alreadyBooked = computed(() => item.value ? isBooked('package', item.value.id) : false)
const isOwner = computed(() => {
  if (!item.value || !user.value) return false
  const uid = String(user.value.userID || user.value.id)
  const oid = String(item.value.agency_id || item.value.userId || item.value.owner_id || item.value.item_owner_id || '')
  
  console.log('--- Package Ownership Check ---')
  console.log('User ID (uid):', uid)
  console.log('Package Owner ID (oid):', oid)
  console.log('Is Owner:', oid !== '' && oid === uid)
  console.log('Raw item data:', item.value)
  
  return oid !== '' && oid === uid
})

const ctaLabel = computed(() => {
  if (isOwner.value) return 'Manage Package'
  return alreadyBooked.value ? 'Cancel Booking' : 'Book this package'
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
  toggle('package', item.value.id)
}

const entityCardRef = ref(null)

function goToPackage(pkg) { router.push(`/packages/${pkg.id}`) }
function handleContact() {
  if (entityCardRef.value) {
    entityCardRef.value.modalOpen = true
  }
}

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
    target_user_id: item.value.agency_id
  })

  if (result.ok) {
    bookingOpen.value = false
    alert('Package booked successfully!')
  } else {
    alert('Failed to book: ' + result.error)
  }
}

async function handleCancel() {
  const id = getBookingId('package', item.value.id)
  if (!id) return
  const res = await cancelBooking(id)
  if (res.ok) alert('Booking cancelled successfully.')
  else alert('Failed to cancel: ' + res.error)
}

const mockReviews = [
  { id:1, name:'Amelia R.',  location:'London, UK',      rating:5, date:'May 2025', text:'The entire package was flawlessly organised.' },
  { id:2, name:'Thomas K.',  location:'Munich, Germany', rating:5, date:'Feb 2025', text:"Best ski holiday I've ever had." },
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
.pkg-detail { min-height: 100vh; background: var(--gray-50); padding-top: 72px; }
.pkg-detail__inner { max-width: 1240px; margin: 0 auto; padding: 0 5% 100px; }

/* ─── Loading ─────────────────────────────────────── */
.pkg-loading { min-height: 100vh; background: var(--gray-50); }
.pkg-loading__inner { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; min-height: 60vh; color: var(--gray-400); }
.pkg-spinner { width: 36px; height: 36px; border: 3px solid var(--gray-200); border-top-color: var(--coral); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Hero ───────────────────────────────────────── */
.pkg-detail__hero { margin: 24px 0 0; }

/* ─── Title Row ──────────────────────────────────── */
.pkg-detail__title-row { margin: 28px 0 36px; }
.pkg-detail__badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.pkg-badge {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: .74rem; font-weight: 700; padding: 5px 13px;
  border-radius: 50px; font-family: 'DM Sans', sans-serif;
}
.pkg-badge--type    { background: rgba(46,196,182,.12); color: var(--teal); }
.pkg-badge--duration{ background: rgba(99,102,241,.09); color: var(--indigo); }
.pkg-badge--spots   { background: rgba(255,90,95,.1);   color: var(--coral); }
.pkg-badge--booked  { background: rgba(39,174,96,.1);   color: #1a7a45; }

.pkg-detail__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(1.9rem, 3.5vw, 2.8rem);
  font-weight: 700; color: var(--indigo);
  margin: 0 0 12px; line-height: 1.15;
}
.pkg-detail__meta {
  display: flex; align-items: center; gap: 10px;
  flex-wrap: wrap; font-size: .9rem; color: var(--gray-600);
}
.pkg-detail__sep { color: var(--gray-300); }
.pkg-detail__agency strong { color: var(--indigo); }
.pkg-detail__rating { display: flex; align-items: center; gap: 4px; font-weight: 600; color: var(--indigo); }
.pkg-detail__star { color: #FFB400; }
.pkg-detail__rev-count { color: var(--gray-400); font-weight: 400; }

/* ─── Two-column body ──────────────────────────────── */
.pkg-detail__body { display: grid; grid-template-columns: 1fr 360px; gap: 40px; align-items: start; }
.pkg-detail__content { min-width: 0; }

/* ─── About ──────────────────────────────────────── */
.pkg-detail__about-text { font-size: .98rem; color: var(--gray-600); line-height: 1.8; }

/* ─── Section titles ──────────────────────────────── */
.pkg-section-title { font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700; color: var(--indigo); margin-bottom: 24px; }

/* ─── Not found ──────────────────────────────────── */
.pkg-not-found { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
.pkg-not-found h2 { font-family: 'Fraunces', serif; font-size: 1.8rem; }

/* ─── Responsive ──────────────────────────────────── */
@media (max-width: 960px) { .pkg-detail__body { grid-template-columns: 1fr; } }
@media (max-width: 640px)  { .pkg-detail__inner { padding: 0 4% 60px; } }
.read-more-btn {
  background: none; border: none; padding: 0;
  color: var(--coral); font-weight: 700; cursor: pointer;
  margin-left: 5px; font-family: inherit; font-size: .95rem;
}
.read-more-btn:hover { text-decoration: underline; color: var(--coral-dk); }

/* ── Modal Styles ── */
.desc-modal-overlay {
  position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); z-index: 2000;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.desc-modal {
  background: var(--white); width: 100%; max-width: 650px;
  max-height: 80vh; border-radius: 20px; overflow: hidden;
  display: flex; flex-direction: column; box-shadow: var(--shadow-xl);
}
.desc-modal__header {
  padding: 24px 32px; border-bottom: 1px solid var(--gray-100);
  display: flex; align-items: center; justify-content: space-between;
}
.desc-modal__header h3 { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--indigo); }
.desc-modal__close { background: none; border: none; font-size: 1.4rem; color: var(--gray-400); cursor: pointer; }
.desc-modal__close:hover { color: var(--indigo); }
.desc-modal__content { padding: 32px; overflow-y: auto; flex: 1; }
.desc-modal__content p { font-size: 1rem; color: var(--gray-600); line-height: 1.8; margin-bottom: 16px; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>