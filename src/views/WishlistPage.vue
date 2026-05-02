<template>
  <div class="wishlist-page">
    <NavBar />

    <!-- Hero -->
    <section class="wishlist-hero">
      <div class="wishlist-hero__inner">
        <div class="wishlist-hero__tag"><span>❤️</span> My Wishlist</div>
        <h1 class="wishlist-hero__title">Your saved <em>favourites</em></h1>
        <p class="wishlist-hero__sub">
          Everything you've bookmarked — destinations, packages, and services — all in one place.
        </p>
      </div>
      <div class="wishlist-hero__blob" aria-hidden="true" />
    </section>

    <!-- Tab Bar -->
    <div class="wishlist-tabs-wrap">
      <div class="wishlist-tabs">
        <button
          v-for="tab in tabs" :key="tab.key"
          class="wishlist-tab" :class="{ active: activeTab === tab.key }"
          @click="activeTab = tab.key"
        >
          <span class="wishlist-tab__icon">{{ tab.icon }}</span>
          {{ tab.label }}
          <span class="wishlist-tab__count" :class="{ 'has-items': tab.count > 0 }">
            {{ tab.count }}
          </span>
        </button>
      </div>
      <button v-if="currentItems.length" class="wishlist-clear-btn" @click="confirmClear = true">
        🗑 Clear {{ activeTabLabel }}
      </button>
    </div>

    <!-- Content -->
    <div class="wishlist-content">
      <Transition name="fade" mode="out-in">

        <div v-if="!currentItems.length" class="wishlist-empty" :key="'empty-' + activeTab">
          <div class="wishlist-empty__illustration">{{ activeTabEmpty.icon }}</div>
          <h2 class="wishlist-empty__title">{{ activeTabEmpty.title }}</h2>
          <p class="wishlist-empty__sub">{{ activeTabEmpty.sub }}</p>
          <RouterLink :to="activeTabEmpty.link" class="btn btn-coral">{{ activeTabEmpty.cta }}</RouterLink>
        </div>

        <div v-else class="wishlist-grid" :key="'grid-' + activeTab">
          <template v-if="activeTab === 'destinations'">
            <DestinationCard
              v-for="item in currentItems" :key="'dest-' + item.id"
              :item="item" :saved="true"
              @select="goToDetail"
              @toggle-wishlist="handleRemove('destination', item.id)"
            />
          </template>
          <template v-else-if="activeTab === 'packages'">
            <PackageCard
              v-for="pkg in savedPkgItems"
              :key="'pkg-' + pkg.id"
              :item="pkg"
              saved
              :booked="isBooked('package', pkg.id)"
              @toggle-wishlist="toggle('package', pkg.id)"
              @select="router.push(`/packages/${pkg.id}`)"
              @book="openBooking('package', pkg)"
              @cancel="handleCancel('package', pkg)"
            />
            
          </template>
          <template v-else-if="activeTab === 'offers'">
  <DealCard
    v-for="offer in savedOfferItems"
    :key="'offer-' + offer.id"
    :offer="offer"
    :saved="true"
    :booked="isBooked('offer', offer.id)"
    @toggle-save="toggle('offer', offer.id)"
    @select="router.push(`/offers/${offer.id}`)"
    @book="handleOfferBook"
  />
</template>
          <template v-else>
            <ServiceCard
              v-for="svc in savedSvcItems"
              :key="'svc-' + svc.id"
              :item="svc"
              saved
              :booked="isBooked('service', svc.id)"
              @toggle-wishlist="toggle('service', svc.id)"
              @select="router.push(`/services/${svc.id}`)"
              @book="openBooking('service', svc)"
              @cancel="handleCancel('service', svc)"
            />
          </template>
        </div>

      </Transition>
    </div>

    <!-- Clear confirm modal -->
    <Transition name="modal-fade">
      <div v-if="confirmClear" class="modal-overlay" @click.self="confirmClear = false">
        <div class="confirm-modal">
          <div class="confirm-modal__icon">🗑️</div>
          <h3>Clear {{ activeTabLabel }}?</h3>
          <p>This will remove all {{ currentItems.length }} saved {{ activeTabLabel.toLowerCase() }} from your wishlist.</p>
          <div class="confirm-modal__actions">
            <button class="btn btn-ghost" @click="confirmClear = false">Cancel</button>
            <button class="btn btn-coral" @click="clearTab">Yes, clear all</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Toast -->
    <Transition name="toast-slide">
      <div v-if="toast" class="wishlist-toast">
        <span>{{ toast }}</span>
        <button @click="toast = null">✕</button>
      </div>
    </Transition>

    <BookingModal v-model="bookingOpen" :pkg="selectedItem" @submit="handleBookingSubmit" />
    <OfferDetailModal v-model="offerModalOpen" :offer="selectedOffer" />
    <SiteFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { destinations, packages as mockPackages, services as mockServices } from '@/data/content.js'
import { useWishlist } from '@/composables/useWishlist.js'
import { useBookings } from '@/composables/useBookings'
import { useAuth } from '@/composables/useAuth'
import { useOffers } from '@/composables/useOffers'

import DealCard from '@/components/search/DealCard.vue'

import NavBar          from '@/components/home/NavBar.vue'
import SiteFooter      from '@/components/home/SiteFooter.vue'
import DestinationCard from '@/components/shared/DestinationCard.vue'
import PackageCard     from '@/components/shared/PackageCard.vue'
import ServiceCard     from '@/components/shared/ServiceCard.vue'
import BookingModal    from '@/components/home/BookingModal.vue'
import OfferDetailModal from '@/components/home/OfferDetailModal.vue'

const router = useRouter()

// ── Shared wishlist state ──────────────────────────────────────────────────
const { itemsOfType, toggle, clearType } = useWishlist()
const { user, isLoggedIn } = useAuth()
const { createBooking, isBooked, getBookingId, cancelBooking } = useBookings()
const { activeOffers } = useOffers()

// Get the saved entries for each type
const destEntries  = itemsOfType('destination')
const pkgEntries   = itemsOfType('package')
const svcEntries   = itemsOfType('service')
const offerEntries = itemsOfType('offer')

// Resolve entries → actual data objects, preserving wishlist order
const savedDestItems = computed(() =>
  destEntries.value
    .map(e => destinations.find(d => d.id === e.id))
    .filter(Boolean)
)

const savedOfferItems = computed(() =>
  offerEntries.value
    .map(e => {
      // Find in DB offers or mock offers
      const o = activeOffers.value.find(off => off.offerID === e.id) || mockOffers.find(off => off.id === e.id)
      if (!o) return null
      return {
        ...o,
        id: o.offerID || o.id,
        desc: o.description,
        tag: o.discount + '% OFF'
      }
    })
    .filter(Boolean)
)

const API = '/arrivo-website/backend/api/v1'

const allPackages = ref([...mockPackages])
const allServices = ref([...mockServices])

onMounted(async () => {
  try {
    const pRes = await fetch(`${API}/packages.php`)
    if (pRes.ok) {
      const pData = await pRes.json()
      const dbPackages = (pData.packages || []).map(p => ({
        id: p.id, title: p.title, agency: p.agency_name || 'Unknown Agency',
        img: p.img_url, type: p.type, duration: p.duration_days,
        rating: Number(p.rating), reviews: Number(p.review_count),
        spots: Number(p.spots_available), price: Number(p.price), desc: p.description
      }))
      const demoTitles = new Set(mockPackages.map(p => p.title))
      const newOnly = dbPackages.filter(p => !demoTitles.has(p.title))
      allPackages.value = [...mockPackages, ...newOnly]
    }
  } catch (e) { console.error('Failed to load packages', e) }

  try {
    const sRes = await fetch(`${API}/services.php`)
    if (sRes.ok) {
      const sData = await sRes.json()
      const dbServices = (sData.services || []).map(s => ({
        id: s.id, icon: s.icon, iconBg: 'svc-icon-teal', title: s.title,
        provider: s.provider_name || 'Unknown Provider', type: s.type,
        price: Number(s.price), unit: s.price_unit, rating: Number(s.rating),
        reviews: Number(s.review_count), availability: !!Number(s.is_available),
        desc: s.description, img: s.img_url
      }))
      const demoServiceTitles = new Set(mockServices.map(s => s.title))
      const newSvcOnly = dbServices.filter(s => !demoServiceTitles.has(s.title))
      allServices.value = [...mockServices, ...newSvcOnly]
    }
  } catch (e) { console.error('Failed to load services', e) }
})

// Resolve entries → actual data objects, preserving wishlist order

const savedPkgItems = computed(() =>
  pkgEntries.value
    .map(e => allPackages.value.find(p => p.id === e.id))
    .filter(Boolean)
)
const savedSvcItems = computed(() =>
  svcEntries.value
    .map(e => allServices.value.find(s => s.id === e.id))
    .filter(Boolean)
)

// ── Tabs ───────────────────────────────────────────────────────────────────
const activeTab = ref('destinations')
const tabs = computed(() => [
  { key: 'destinations', label: 'Destinations', icon: '📍', count: savedDestItems.value.length },
  { key: 'packages',     label: 'Packages',     icon: '🧳', count: savedPkgItems.value.length },
  { key: 'services',     label: 'Services',     icon: '✨', count: savedSvcItems.value.length },
  { key: 'offers',       label: 'Offers',       icon: '🏷️', count: savedOfferItems.value.length },
])

const currentItems = computed(() => {
  if (activeTab.value === 'destinations') return savedDestItems.value
  if (activeTab.value === 'packages')     return savedPkgItems.value
  if (activeTab.value === 'services')     return savedSvcItems.value
  return savedOfferItems.value
})

const activeTabLabel = computed(() => tabs.value.find(t => t.key === activeTab.value)?.label || '')

const emptyStates = {
  destinations: { icon: '🗺️', title: 'No saved destinations yet', sub: 'Browse destinations and tap the heart to save your favourites here.', link: '/destinations', cta: 'Explore destinations' },
  packages:     { icon: '🧳', title: 'No saved packages yet',     sub: 'Find travel packages that inspire you and save them for later.',        link: '/packages',     cta: 'Browse packages'      },
  services:     { icon: '✨', title: 'No saved services yet',     sub: 'Discover guides, drivers, chefs and more — save the ones you love.',    link: '/services',     cta: 'Discover services'    },
  offers:       { icon: '🏷️', title: 'No saved offers yet',       sub: 'Check back often for exclusive deals and discounts from our partners.', link: '/deals',        cta: 'View deals'         },}
const activeTabEmpty = computed(() => emptyStates[activeTab.value])

// ── Remove + toast ─────────────────────────────────────────────────────────
const toast        = ref(null)
const confirmClear = ref(false)
let toastTimer     = null

function showToast(msg) {
  clearTimeout(toastTimer)
  toast.value = msg
  toastTimer  = setTimeout(() => { toast.value = null }, 3000)
}

/** Called when the heart on a wishlist card is clicked — always a removal here */
function handleRemove(type, id) {
  toggle(type, id)   // item is definitely saved, so this removes it
  showToast('Removed from wishlist')
}

function clearTab() {
  confirmClear.value = false
  const typeMap = { 
    destinations: 'destination', 
    packages:     'package', 
    services:     'service',
    offers:       'offer'
  }
  clearType(typeMap[activeTab.value])
  showToast(`All ${activeTabLabel.value.toLowerCase()} cleared`)
}

// ── Navigation ─────────────────────────────────────────────────────────────
function goToDetail(item) {
  if (activeTab.value === 'destinations') router.push(`/destinations/${item.id}`)
  else if (activeTab.value === 'packages') router.push(`/packages/${item.id}`)
  else if (activeTab.value === 'services') router.push(`/services/${item.id}`)
  else router.push(`/deals`) // Offers go to deals page or dynamic detail if it existed
}

// ── Booking ────────────────────────────────────────────────────────────────
const bookingOpen  = ref(false)
const offerModalOpen = ref(false)
const selectedItem = ref(null)
const selectedOffer = ref(null)
const selectedItemType = ref(null)

function openBooking(type, item) { 
  selectedItemType.value = type;
  selectedItem.value = item; 
  bookingOpen.value = true 
}

function handleOfferBook(offer) {
  if (isBooked('offer', offer.id)) {
    handleCancel('offer', offer)
    return
  }
  selectedOffer.value = offer
  offerModalOpen.value = true
}

async function handleBookingSubmit(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  const result = await createBooking({
    user_id:  user.value?.userID ?? user.value?.id,
    type:     selectedItemType.value,
    item_id:  selectedItem.value.id,
    title:    selectedItem.value.title ?? selectedItem.value.name,
    price:    selectedItem.value.price,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
  })

  if (result.ok) {
    bookingOpen.value = false
    alert(`${selectedItemType.value} booked successfully!`)
  } else {
    alert('Failed to book: ' + result.error)
  }
}

async function handleCancel(type, item) {
  const id = getBookingId(type, item.id)
  if (!id) return
  const res = await cancelBooking(id)
  if (res.ok) alert('Booking cancelled successfully.')
  else alert('Failed to cancel: ' + res.error)
}
</script>

<style scoped>
.wishlist-page { min-height: 100vh; background: var(--gray-50); padding-top: 72px; }

.wishlist-hero {
  position: relative;
  background: linear-gradient(135deg, var(--indigo) 0%, #3d4260 100%);
  padding: 72px var(--section-px) 80px; overflow: hidden;
}
.wishlist-hero__inner { position: relative; z-index: 2; max-width: 600px; }
.wishlist-hero__tag {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,.1); backdrop-filter: blur(8px);
  color: #fff; font-size: .82rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
  padding: 6px 16px; border-radius: 50px; margin-bottom: 20px; border: 1px solid rgba(255,255,255,.15);
}
.wishlist-hero__title {
  font-family: 'Fraunces', serif; font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 700; color: #fff; line-height: 1.15; margin-bottom: 14px;
}
.wishlist-hero__title em { font-style: italic; color: var(--coral); }
.wishlist-hero__sub { font-size: 1rem; color: rgba(255,255,255,.72); line-height: 1.65; max-width: 460px; }
.wishlist-hero__blob {
  position: absolute; right: -80px; top: -80px; width: 420px; height: 420px; border-radius: 50%;
  background: radial-gradient(circle, rgba(255,90,95,.18) 0%, transparent 70%); pointer-events: none;
}

.wishlist-tabs-wrap {
  position: sticky; top: 72px; z-index: 40;
  background: var(--white); border-bottom: 1px solid var(--gray-200);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 var(--section-px); box-shadow: 0 2px 12px rgba(45,49,66,.05);
}
.wishlist-tabs { display: flex; align-items: center; gap: 4px; }
.wishlist-tab {
  display: flex; align-items: center; gap: 8px; padding: 18px 20px 16px;
  background: none; border: none; border-bottom: 3px solid transparent;
  font-family: 'DM Sans', sans-serif; font-size: .9rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer;
  transition: color var(--transition), border-color var(--transition); white-space: nowrap;
}
.wishlist-tab:hover { color: var(--indigo); }
.wishlist-tab.active { color: var(--indigo); border-bottom-color: var(--coral); }
.wishlist-tab__icon { font-size: 1rem; }
.wishlist-tab__count {
  background: var(--gray-100); color: var(--gray-600);
  font-size: .72rem; font-weight: 700; padding: 2px 8px; border-radius: 50px;
  transition: background var(--transition), color var(--transition);
}
.wishlist-tab__count.has-items { background: var(--coral-lt); color: var(--coral); }
.wishlist-clear-btn {
  background: none; border: 1px solid var(--gray-200); color: var(--gray-600);
  font-family: 'DM Sans', sans-serif; font-size: .82rem; font-weight: 600;
  padding: 8px 16px; border-radius: 50px; cursor: pointer;
  transition: all var(--transition); white-space: nowrap;
}
.wishlist-clear-btn:hover { border-color: var(--coral); color: var(--coral); background: var(--coral-lt); }

.wishlist-content { padding: 40px var(--section-px) 80px; min-height: 360px; }
.wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }

.wishlist-empty {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 80px 24px; text-align: center;
}
.wishlist-empty__illustration { font-size: 4rem; margin-bottom: 20px; animation: float 3s ease-in-out infinite; }
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.wishlist-empty__title { font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700; color: var(--indigo); margin-bottom: 10px; }
.wishlist-empty__sub { font-size: .95rem; color: var(--gray-600); line-height: 1.7; max-width: 360px; margin-bottom: 28px; }

.modal-overlay {
  position: fixed; inset: 0; z-index: 200;
  background: rgba(0,0,0,.45); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
}
.confirm-modal {
  background: var(--white); border-radius: var(--radius-xl);
  padding: 40px 36px; max-width: 400px; width: 90%;
  box-shadow: var(--shadow-lg); text-align: center;
}
.confirm-modal__icon { font-size: 2.5rem; margin-bottom: 12px; }
.confirm-modal h3 { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--indigo); margin-bottom: 8px; }
.confirm-modal p { font-size: .9rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 28px; }
.confirm-modal__actions { display: flex; gap: 12px; justify-content: center; }
.btn-ghost {
  background: none; border: 1.5px solid var(--gray-200); color: var(--gray-600);
  padding: 10px 24px; border-radius: 50px; font-family: 'DM Sans', sans-serif;
  font-weight: 600; font-size: .9rem; cursor: pointer; transition: all var(--transition);
}
.btn-ghost:hover { border-color: var(--indigo); color: var(--indigo); }

.wishlist-toast {
  position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%); z-index: 300;
  background: var(--indigo); color: #fff; display: flex; align-items: center; gap: 12px;
  padding: 12px 20px; border-radius: 50px; font-size: .88rem; font-weight: 600;
  box-shadow: 0 8px 32px rgba(0,0,0,.2); white-space: nowrap;
}
.wishlist-toast button {
  background: rgba(255,255,255,.15); border: none; color: #fff;
  width: 22px; height: 22px; border-radius: 50%; cursor: pointer; font-size: .75rem;
  display: flex; align-items: center; justify-content: center; transition: background var(--transition);
}
.wishlist-toast button:hover { background: rgba(255,255,255,.3); }

.fade-enter-active, .fade-leave-active         { transition: opacity .22s ease, transform .22s ease; }
.fade-enter-from, .fade-leave-to               { opacity: 0; transform: translateY(8px); }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity .2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to   { opacity: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all .28s cubic-bezier(.4,0,.2,1); }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(-50%) translateY(16px); }

@media (max-width: 768px) {
  .wishlist-tabs-wrap { padding: 0 4%; gap: 8px; }
  .wishlist-tab       { padding: 16px 12px 14px; font-size: .82rem; }
  .wishlist-tab__icon { display: none; }
  .wishlist-clear-btn { padding: 7px 12px; font-size: .78rem; }
  .wishlist-content   { padding: 24px 4% 60px; }
  .wishlist-grid      { grid-template-columns: 1fr; gap: 16px; }
}
</style>