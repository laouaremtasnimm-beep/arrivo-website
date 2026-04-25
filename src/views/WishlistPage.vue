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
              v-for="item in currentItems" :key="item.id"
              :item="item" :saved="true"
              @select="goToDetail"
              @toggle-wishlist="handleToggle"
            />
          </template>
          <template v-else-if="activeTab === 'packages'">
            <PackageCard
              v-for="item in currentItems" :key="item.id"
              :item="item" :saved="true"
              @select="goToDetail" @book="openBooking"
              @toggle-wishlist="handleToggle"
            />
          </template>
          <template v-else>
            <ServiceCard
              v-for="item in currentItems" :key="item.id"
              :item="item" :saved="true"
              @select="goToDetail" @book="openBooking"
              @toggle-wishlist="handleToggle"
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
    <SiteFooter />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { destinations, packages, services } from '@/data/content.js'
import { useWishlist } from '@/composables/useWishlist.js'

import NavBar          from '@/components/home/NavBar.vue'
import SiteFooter      from '@/components/home/SiteFooter.vue'
import DestinationCard from '@/components/shared/DestinationCard.vue'
import PackageCard     from '@/components/shared/PackageCard.vue'
import ServiceCard     from '@/components/shared/ServiceCard.vue'
import BookingModal    from '@/components/home/BookingModal.vue'

const router = useRouter()

// ── Shared wishlist state ──────────────────────────────────────────────────
const { destIds, pkgIds, svcIds, toggleWishlist, clearCategory } = useWishlist()

const savedDestItems = computed(() => destinations.filter(d => destIds.value.includes(d.id)))
const savedPkgItems  = computed(() => packages.filter(p => pkgIds.value.includes(p.id)))
const savedSvcItems  = computed(() => services.filter(s => svcIds.value.includes(s.id)))

// ── Tabs ───────────────────────────────────────────────────────────────────
const activeTab = ref('destinations')

const tabs = computed(() => [
  { key: 'destinations', label: 'Destinations', icon: '📍', count: savedDestItems.value.length },
  { key: 'packages',     label: 'Packages',     icon: '🧳', count: savedPkgItems.value.length  },
  { key: 'services',     label: 'Services',     icon: '✨', count: savedSvcItems.value.length  },
])

const currentItems = computed(() => {
  if (activeTab.value === 'destinations') return savedDestItems.value
  if (activeTab.value === 'packages')     return savedPkgItems.value
  return savedSvcItems.value
})

const activeTabLabel = computed(() => tabs.value.find(t => t.key === activeTab.value)?.label || '')

const emptyStates = {
  destinations: { icon: '🗺️', title: 'No saved destinations yet', sub: 'Browse destinations and tap the heart to save your favourites here.', link: '/destinations', cta: 'Explore destinations' },
  packages:     { icon: '🧳', title: 'No saved packages yet',     sub: 'Find travel packages that inspire you and save them for later.',        link: '/packages',     cta: 'Browse packages'      },
  services:     { icon: '✨', title: 'No saved services yet',     sub: 'Discover guides, drivers, chefs and more — save the ones you love.',    link: '/services',     cta: 'Discover services'    },
}
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

function handleToggle(id) {
  toggleWishlist(id)
  showToast('Removed from wishlist')
}

function clearTab() {
  confirmClear.value = false
  clearCategory(activeTab.value)
  showToast(`All ${activeTabLabel.value.toLowerCase()} cleared`)
}

// ── Navigation ─────────────────────────────────────────────────────────────
function goToDetail(item) {
  if (activeTab.value === 'destinations') router.push(`/destinations/${item.id}`)
  else if (activeTab.value === 'packages') router.push(`/packages/${item.id}`)
  else router.push(`/services/${item.id}`)
}

// ── Booking ────────────────────────────────────────────────────────────────
const bookingOpen  = ref(false)
const selectedItem = ref(null)
function openBooking(item) { selectedItem.value = item; bookingOpen.value = true }
function handleBookingSubmit(payload) { console.log('Booking:', payload) }
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