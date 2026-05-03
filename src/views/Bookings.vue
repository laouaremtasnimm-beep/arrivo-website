<template>
  <div class="bookings-page">
    <NavBar />

    <!-- ── Hero ── -->
    <section class="bk-hero">
      <div class="bk-hero__inner">
        <div class="bk-hero__tag"><span>🗓️</span> {{ roleLabel }}</div>
        <h1 class="bk-hero__title">Your <em>bookings</em></h1>
        <p class="bk-hero__sub">
          All your reservations in one place — packages, services and deals. Track status, confirm or cancel with ease.
        </p>
      </div>
      <div class="bk-hero__blob" aria-hidden="true" />
    </section>

    <!-- ── Sticky Tabs ── -->
    <div class="bk-tabs-wrap">
      <div class="bk-tabs">
        <button
          v-for="tab in tabs" :key="tab.key"
          class="bk-tab" :class="{ active: activeTab === tab.key }"
          @click="activeTab = tab.key"
        >
          <span class="bk-tab__icon">{{ tab.icon }}</span>
          {{ tab.label }}
          <span class="bk-tab__count" :class="{ 'has-items': tabCount(tab.key) > 0 }">
            {{ tab.key === 'all' ? bookings.length : tabCount(tab.key) }}
          </span>
        </button>
      </div>

      <div class="bk-status-pills">
        <button
          v-for="s in statuses" :key="s.value"
          class="status-pill" :class="{ active: statusFilter === s.value }"
          @click="statusFilter = s.value"
        >{{ s.label }}</button>
      </div>
    </div>

    <!-- ── Content ── -->
    <div class="bk-content">

      <div class="bk-search-wrap">
        <div class="bk-search">
          <svg class="bk-search__icon" viewBox="0 0 20 20" fill="none">
            <circle cx="9" cy="9" r="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="m14 14 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <input v-model="searchQuery" class="bk-search__input" placeholder="Search by title…" />
        </div>
        <span class="bk-results-count">{{ filtered.length }} booking{{ filtered.length !== 1 ? 's' : '' }}</span>
      </div>

      <Transition name="fade" mode="out-in">

        <div v-if="loading" class="bk-empty" key="loading">
          <div class="bk-empty__illustration">⏳</div>
          <h2 class="bk-empty__title">Loading…</h2>
          <p class="bk-empty__sub">Fetching your reservations.</p>
        </div>

        <div v-else-if="!filtered.length" class="bk-empty" :key="'empty-' + activeTab">
          <div class="bk-empty__illustration">{{ emptyState.icon }}</div>
          <h2 class="bk-empty__title">{{ emptyState.title }}</h2>
          <p class="bk-empty__sub">{{ emptyState.sub }}</p>
          <RouterLink :to="emptyState.link" class="btn btn-coral">{{ emptyState.cta }}</RouterLink>
        </div>

        <div v-else class="bk-grid" :key="'grid-' + activeTab">
          <div
            v-for="b in filtered" :key="b.id"
            class="bk-card" :class="{ 'bk-card--cancelled': b.status === 'cancelled' }"
            @click="handleView(b)"
          >
            <div class="bk-card__thumb">
              <img 
                v-if="bookingImage(b)"
                :src="bookingImage(b)" 
                class="bk-card__img" 
                alt=""
              />
              <div v-else class="bk-card__thumb-bg">{{ typeIcon(b.booking_type) }}</div>
              <span class="bk-card__type-tag">{{ typeName(b.booking_type) }}</span>
              <span class="status-badge" :class="`status--${b.status}`">{{ statusLabel(b.status) }}</span>
            </div>

            <div class="bk-card__body">
              <h3 class="bk-card__title">{{ bookingTitle(b) }}</h3>

              <div class="bk-card__meta">
                <span class="meta-item" v-if="b.check_in">
                  📅 {{ formatDate(b.check_in) }}
                </span>
                <span class="meta-item" v-if="b.guests">
                  👤 {{ b.guests }} {{ b.guests == 1 ? 'guest' : 'guests' }}
                </span>
                <span class="meta-item" v-if="b.guest_first">
                  👤 {{ b.guest_first }} {{ b.guest_last }}
                </span>
              </div>

              <div class="bk-card__footer">
                <div class="bk-card__price" v-if="b.total_price">
                  <span class="price-amount">${{ Number(b.total_price).toLocaleString() }}</span>
                  <span class="price-label">total</span>
                </div>
                <div class="bk-card__actions">
                  <button class="btn btn-ghost btn--sm" @click.stop="handleView(b)">Details</button>
                  <button v-if="canConfirm(b)" class="btn btn-teal btn--sm" @click.stop="handleConfirm(b)">Confirm</button>
                  <button v-if="canCancel(b)"  class="btn btn-coral btn--sm" @click.stop="handleCancel(b)">Cancel</button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </Transition>
    </div>

    <SiteFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useBookings } from '@/composables/useBookings'
import NavBar     from '@/components/home/NavBar.vue'
import SiteFooter from '@/components/home/SiteFooter.vue'

const router = useRouter()
const { user, isAgency, isProvider, isLoggedIn } = useAuth()
const { bookings, loading, fetchBookings, updateStatus, cancelBooking } = useBookings()

const activeTab    = ref('all')
const statusFilter = ref('all')
const searchQuery  = ref('')

const role = computed(() => {
  return 'tourist' // This page always shows personal bookings
})

const roleLabel = computed(() => 'My Account')

const tabs = [
  { key: 'all',         label: 'All',          icon: '📋' },
  { key: 'package',     label: 'Packages',     icon: '✈️' },
  { key: 'service',     label: 'Services',     icon: '🎯' },
  { key: 'destination', label: 'Destinations', icon: '📍' },
  { key: 'offer',       label: 'Offers',       icon: '🏷️' },
]

const statuses = [
  { value: 'all',       label: 'All Status' },
  { value: 'pending',   label: 'Pending'    },
  { value: 'confirmed', label: 'Booked'     },
  { value: 'cancelled', label: 'Cancelled'  },
]



function tabCount(key) {
  if (key === 'all') return bookings.value.length
  return bookings.value.filter(b => b.booking_type === key).length
}

const filtered = computed(() => {
  let list = bookings.value
  if (activeTab.value !== 'all')    list = list.filter(b => b.booking_type === activeTab.value)
  if (statusFilter.value !== 'all') list = list.filter(b => b.status === statusFilter.value)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(b => bookingTitle(b).toLowerCase().includes(q))
  }
  return list
})

const emptyStates = {
  all:         { icon: '🗺️', title: 'No bookings yet',        sub: 'Start exploring and make your first booking.',        link: '/packages',      cta: 'Browse Packages'       },
  package:     { icon: '✈️', title: 'No package bookings',     sub: 'Find the perfect travel package for your next trip.', link: '/packages',      cta: 'Browse Packages'       },
  service:     { icon: '🎯', title: 'No service bookings',     sub: 'Discover guides, drivers, chefs and more.',           link: '/services',      cta: 'Browse Services'       },
  destination: { icon: '📍', title: 'No destination bookings', sub: 'Explore our curated destinations.',                   link: '/destinations',  cta: 'Explore Destinations'  },
  offer:       { icon: '🏷️', title: 'No offer bookings',       sub: 'Grab a limited-time deal before it expires.',         link: '/deals',         cta: 'View Deals'            },
}
const emptyState = computed(() => emptyStates[activeTab.value] ?? emptyStates.all)

function bookingTitle(b) {
  return b.package_title || b.service_title || b.destination_name || b.offer_title || `Booking #${b.id}`
}
function bookingImage(b) {
  const img = b.package_img || b.service_img || b.destination_img || b.img_url
  if (b.booking_type === 'package' && !img) {
    return 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg'
  }
  return img
}
function typeIcon(type) { return { package: '✈️', service: '🎯', destination: '📍', offer: '🏷️' }[type] ?? '📋' }
function typeName(type) { return { package: 'Package', service: 'Service', destination: 'Destination', offer: 'Offer' }[type] ?? 'Booking' }
function statusLabel(s) {
  return {
    pending:   'Pending',
    confirmed: 'Booked',
    cancelled: 'Cancelled',
    completed: 'Completed'
  }[s] || s
}
function formatDate(d)  { return d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '' }
function canConfirm(b)  { return role.value !== 'tourist' && b.status === 'pending' }
function canCancel(b)   { return b.status === 'pending' || (role.value === 'tourist' && b.status === 'confirmed') }

async function handleConfirm(b) { await updateStatus(b.id, 'confirmed') }
async function handleCancel(b) {
  await cancelBooking(b.id)
}
function handleView(b) {
  const paths = { package: '/packages', service: '/services', destination: '/destinations', offer: '/deals' }
  const id = b.package_id ?? b.service_id ?? b.destination_id
  if (id && b.booking_type !== 'offer') {
    router.push(`${paths[b.booking_type]}/${id}`)
  } else if (b.booking_type === 'offer') {
    router.push('/deals')
  }
}

onMounted(() => {
  if (isLoggedIn.value) fetchBookings(user.value)
})
</script>

<!-- styles unchanged — paste your existing <style scoped> block here -->
<style scoped>
.bookings-page { min-height: 100vh; background: var(--gray-50); }

.bk-hero {
  position: relative;
  background: linear-gradient(135deg, var(--indigo) 0%, #3d4260 100%);
  padding: 72px var(--section-px, 6%) 80px;
  padding-top: calc(72px + 72px);
  overflow: hidden;
}
.bk-hero__inner { position: relative; z-index: 2; max-width: 600px; }
.bk-hero__tag {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,.1); backdrop-filter: blur(8px);
  color: #fff; font-size: .82rem; font-weight: 700;
  letter-spacing: .06em; text-transform: uppercase;
  padding: 6px 16px; border-radius: 50px; margin-bottom: 20px;
  border: 1px solid rgba(255,255,255,.15);
}
.bk-hero__title { font-family: 'Fraunces', serif; font-size: clamp(2rem, 5vw, 3rem); font-weight: 700; color: #fff; line-height: 1.15; margin-bottom: 14px; }
.bk-hero__title em { font-style: italic; color: var(--coral); }
.bk-hero__sub { font-size: 1rem; color: rgba(255,255,255,.72); line-height: 1.65; max-width: 460px; }
.bk-hero__blob { position: absolute; right: -80px; top: -80px; width: 420px; height: 420px; border-radius: 50%; background: radial-gradient(circle, rgba(255,90,95,.18) 0%, transparent 70%); pointer-events: none; }

.bk-tabs-wrap { position: sticky; top: 72px; z-index: 40; background: var(--white); border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between; padding: 0 var(--section-px, 6%); box-shadow: 0 2px 12px rgba(45,49,66,.05); flex-wrap: wrap; gap: 4px; }
.bk-tabs { display: flex; align-items: center; gap: 4px; }
.bk-tab { display: flex; align-items: center; gap: 8px; padding: 18px 20px 16px; background: none; border: none; border-bottom: 3px solid transparent; font-family: 'DM Sans', sans-serif; font-size: .9rem; font-weight: 600; color: var(--gray-600); cursor: pointer; transition: color var(--transition), border-color var(--transition); white-space: nowrap; }
.bk-tab:hover { color: var(--indigo); }
.bk-tab.active { color: var(--indigo); border-bottom-color: var(--coral); }
.bk-tab__icon { font-size: 1rem; }
.bk-tab__count { background: var(--gray-100); color: var(--gray-600); font-size: .72rem; font-weight: 700; padding: 2px 8px; border-radius: 50px; transition: background var(--transition), color var(--transition); }
.bk-tab__count.has-items { background: var(--coral-lt); color: var(--coral); }

.bk-status-pills { display: flex; gap: 6px; padding: 10px 0; flex-wrap: wrap; }
.status-pill { padding: 5px 14px; border-radius: 50px; border: 1px solid var(--gray-200); background: var(--white); font-size: .8rem; font-weight: 600; color: var(--gray-600); cursor: pointer; transition: all var(--transition); }
.status-pill:hover { border-color: var(--indigo); color: var(--indigo); }
.status-pill.active { background: var(--indigo); border-color: var(--indigo); color: #fff; }

.bk-content { padding: 36px var(--section-px, 6%) 80px; min-height: 360px; }

.bk-search-wrap { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 28px; flex-wrap: wrap; }
.bk-search { position: relative; flex: 1; min-width: 200px; max-width: 360px; }
.bk-search__icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--gray-400); }
.bk-search__input { width: 100%; padding: 10px 12px 10px 36px; border: 1.5px solid var(--gray-200); border-radius: 50px; font-family: 'DM Sans', sans-serif; font-size: .88rem; color: var(--indigo); background: var(--white); outline: none; transition: border-color var(--transition); box-sizing: border-box; }
.bk-search__input:focus { border-color: var(--coral); }
.bk-results-count { font-size: .82rem; color: var(--gray-400); white-space: nowrap; }

.bk-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px 24px; text-align: center; }
.bk-empty__illustration { font-size: 4rem; margin-bottom: 20px; animation: float 3s ease-in-out infinite; }
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.bk-empty__title { font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700; color: var(--indigo); margin-bottom: 10px; }
.bk-empty__sub { font-size: .95rem; color: var(--gray-600); line-height: 1.7; max-width: 360px; margin-bottom: 28px; }

.bk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }

.bk-card { background: var(--white); border-radius: var(--radius, 14px); box-shadow: var(--shadow); overflow: hidden; display: flex; flex-direction: column; transition: box-shadow var(--transition), transform var(--transition); cursor: pointer; }
.bk-card:hover { box-shadow: var(--shadow-lg, 0 8px 32px rgba(0,0,0,.1)); transform: translateY(-3px); }
.bk-card--cancelled { opacity: .62; }

.bk-card__thumb { position: relative; height: 140px; background: linear-gradient(135deg, var(--sand, #f5ede0) 0%, var(--gray-100) 100%); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.bk-card__img { width: 100%; height: 100%; object-fit: cover; }
.bk-card__thumb-bg { font-size: 3rem; }
.bk-card__type-tag { position: absolute; top: 12px; left: 12px; background: rgba(45,49,66,.7); backdrop-filter: blur(6px); color: #fff; font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; padding: 3px 10px; border-radius: 50px; }
.status-badge { position: absolute; top: 12px; right: 12px; font-size: .68rem; font-weight: 700; text-transform: capitalize; padding: 3px 10px; border-radius: 50px; }
.status--confirmed { background: rgba(39,174,96,.15); color: #1a7a45; }
.status--pending   { background: rgba(243,156,18,.15); color: #a0690d; }
.status--cancelled { background: rgba(231,76,60,.15);  color: #c0392b; }
.status--completed { background: rgba(52,152,219,.15); color: #1a6fa0; }

.bk-card__body { padding: 18px 20px 20px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
.bk-card__title { font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 700; color: var(--indigo); margin: 0; line-height: 1.3; }
.bk-card__meta { display: flex; flex-wrap: wrap; gap: 10px; }
.meta-item { font-size: .8rem; color: var(--gray-600); display: flex; align-items: center; gap: 4px; }

.bk-card__footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 12px; border-top: 1px solid var(--gray-100); }
.bk-card__price { display: flex; align-items: baseline; gap: 4px; }
.price-amount { font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; color: var(--coral); }
.price-label { font-size: .72rem; color: var(--gray-400); text-transform: uppercase; }
.bk-card__actions { display: flex; gap: 8px; }

.btn--sm { padding: 7px 16px !important; font-size: .8rem !important; }
.btn-teal { background: var(--teal, #2ec4b6); color: #fff; border: 1.5px solid var(--teal, #2ec4b6); padding: 10px 22px; border-radius: 50px; font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: .9rem; cursor: pointer; transition: all var(--transition); }
.btn-teal:hover { opacity: .85; }

.fade-enter-active, .fade-leave-active { transition: opacity .22s ease, transform .22s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; transform: translateY(8px); }

@media (max-width: 768px) {
  .bk-tabs-wrap { padding: 0 4%; gap: 6px; }
  .bk-tab { padding: 16px 12px 14px; font-size: .82rem; }
  .bk-tab__icon { display: none; }
  .bk-content { padding: 24px 4% 60px; }
  .bk-grid { grid-template-columns: 1fr; gap: 16px; }
  .bk-status-pills { padding: 8px 0; }
}
</style>