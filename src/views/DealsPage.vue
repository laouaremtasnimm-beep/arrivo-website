<template>
  <div class="deals-page">
    <NavBar />

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <section class="deals-hero">
      <div class="deals-hero__bg" aria-hidden="true">
        <div class="hero-blob hero-blob--1" />
        <div class="hero-blob hero-blob--2" />
      </div>
      <div class="deals-hero__content">
        <div class="deals-hero__eyebrow">
          <span class="flame-icon">🔥</span> Limited Time
        </div>
        <h1 class="deals-hero__title">
          Hot <em>Deals</em> &amp;<br>Special Offers
        </h1>
        <p class="deals-hero__sub">
          Exclusive discounts from our vetted partner agencies — updated regularly.
        </p>
        <!-- Live count chip -->
        <div class="deals-hero__live">
          <span class="live-dot" />
          <span><strong>{{ activeOffers.length }}</strong> offer{{ activeOffers.length !== 1 ? 's' : '' }} live right now</span>
        </div>
      </div>
    </section>

    <!-- ── Filters + Grid ─────────────────────────────────────────────────── -->
    <section class="deals-body">

      <!-- Filter bar -->
      <div class="deals-filters">

        <div class="filter-chips">
          <button
            v-for="f in typeFilters"
            :key="f.value"
            class="filter-chip"
            :class="{ active: activeFilter === f.value }"
            @click="activeFilter = f.value"
          >
            {{ f.icon }} {{ f.label }}
          </button>
        </div>

        <div class="filter-sort">
          <label class="sort-label">Sort by</label>
          <select class="sort-select" v-model="sortBy">
            <option value="default">Featured</option>
            <option value="discount_desc">Highest discount</option>
            <option value="discount_asc">Lowest discount</option>
            <option value="newest">Newest first</option>
          </select>
        </div>

      </div>

      <!-- Results count -->
      <p class="deals-count" v-if="!loading">
        Showing <strong>{{ filteredOffers.length }}</strong> deal{{ filteredOffers.length !== 1 ? 's' : '' }}
        <span v-if="activeFilter !== 'all'"> in <em>{{ activeFilterLabel }}</em></span>
      </p>

      <!-- Loading skeletons -->
      <div v-if="loading" class="deals-grid">
        <div v-for="i in 6" :key="i" class="deal-skeleton">
          <div class="skeleton-badge" />
          <div class="skeleton-line skeleton-line--title" />
          <div class="skeleton-line" />
          <div class="skeleton-line skeleton-line--short" />
          <div class="skeleton-btn" />
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="filteredOffers.length === 0" class="deals-empty">
        <div class="deals-empty__icon">🏷️</div>
        <h3 class="deals-empty__title">No deals in this category</h3>
        <p class="deals-empty__sub">Try a different filter or check back soon for new offers.</p>
        <button class="btn btn-coral" @click="activeFilter = 'all'">Show all deals</button>
      </div>

      <!-- Deal cards grid -->
      <div v-else class="deals-grid">
        <div
          v-for="(offer, i) in filteredOffers"
          :key="offer.offerID"
          class="deal-card"
          :class="{ 'deal-card--collab': offer.source === 'collab', 'deal-card--featured': i === 0 && activeFilter === 'all' }"
          :style="{ '--i': i }"
          
        >
          <!-- Featured ribbon -->
          <div v-if="i === 0 && activeFilter === 'all'" class="deal-ribbon">⭐ Featured</div>

          <!-- Collab badge -->
          <div v-if="offer.source === 'collab'" class="deal-collab">🤝 Joint Offer</div>

          <!-- Discount badge -->
          <div class="deal-discount">
            <span class="deal-discount__num">{{ offer.discount }}</span>
            <span class="deal-discount__pct">% OFF</span>
          </div>

          <!-- Card body -->
          <div class="deal-body">
            <div class="deal-meta">
              <span v-if="offer.type" class="deal-type">{{ offer.type }}</span>
              <span class="deal-dates">📅 {{ offer.startDate }} – {{ offer.endDate }}</span>
            </div>

            <h3 class="deal-title">{{ offer.title }}</h3>
            <p class="deal-desc">{{ offer.description }}</p>

            <div v-if="offer.source === 'collab' && offer.partnerName" class="deal-partner">
              <span class="deal-partner-dot" :style="{ background: offer.partnerColor || '#2EC4B6' }" />
              with {{ offer.partnerName }}
            </div>
          </div>

          <!-- Card footer -->
          <div class="deal-footer">
            <button 
              class="btn deal-cta" 
              :class="isBooked('offer', offer.offerID) ? 'btn-outline-danger' : 'btn-teal'"
              @click.stop="handleSelect(offer)"
            >
              {{ isBooked('offer', offer.offerID) ? 'Cancel booking' : 'Grab deal →' }}
            </button>
            <button
              class="deal-save"
              :class="{ saved: isSaved('offer', offer.offerID) }"
              @click.stop="toggle('offer', offer.offerID)"
              :title="isSaved('offer', offer.offerID) ? 'Remove from wishlist' : 'Save deal'"
            >
              {{ isSaved('offer', offer.offerID) ? '♥' : '♡' }}
            </button>
          </div>
        </div>
      </div>

    </section>

    <SiteFooter />

   <OfferDetailModal
  v-model="offerModalOpen"
  :offer="selectedOffer"
/>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useOffers } from '@/composables/useOffers'
import { useWishlist } from '@/composables/useWishlist'
import { useBookings } from '@/composables/useBookings'
import NavBar    from '@/components/home/NavBar.vue'
import SiteFooter from '@/components/home/SiteFooter.vue'
import OfferDetailModal from '@/components/home/OfferDetailModal.vue'

const { toggle, isSaved } = useWishlist()

const selectedOffer = ref(null)
const offerModalOpen = ref(false)

const router = useRouter()
const { activeOffers } = useOffers()
const { isBooked, getBookingId, cancelBooking } = useBookings()

// ── Fake load so the page doesn't pop in ──────────────────────────────────
const loading = ref(true)
onMounted(() => setTimeout(() => { loading.value = false }, 400))

// ── Filters ───────────────────────────────────────────────────────────────
const typeFilters = [
  { value: 'all',       label: 'All deals',  icon: '✨' },
  { value: 'Adventure', label: 'Adventure',  icon: '🧗' },
  { value: 'Beach',     label: 'Beach',      icon: '🏖️' },
  { value: 'Cultural',  label: 'Cultural',   icon: '🏛️' },
  { value: 'Family',    label: 'Family',     icon: '👨‍👩‍👧' },
  { value: 'Wellness',  label: 'Wellness',   icon: '🧘' },
  { value: 'Bundle',    label: 'Bundles',    icon: '📦' },
]

const activeFilter = ref('all')
const sortBy       = ref('default')

const activeFilterLabel = computed(
  () => typeFilters.find(f => f.value === activeFilter.value)?.label ?? ''
)

const filteredOffers = computed(() => {
  let list = [...activeOffers.value]

  if (activeFilter.value !== 'all')
    list = list.filter(o => o.type === activeFilter.value)

  if (sortBy.value === 'discount_desc') list.sort((a, b) => b.discount - a.discount)
  if (sortBy.value === 'discount_asc')  list.sort((a, b) => a.discount - b.discount)
  if (sortBy.value === 'newest')        list.sort((a, b) => b.offerID - a.offerID)

  return list
})

// ── Actions ───────────────────────────────────────────────────────────────
async function handleSelect(offer) {
  if (isBooked('offer', offer.offerID)) {
    if (!confirm('Are you sure you want to cancel this booking?')) return
    const bid = getBookingId('offer', offer.offerID)
    if (bid) {
      const res = await cancelBooking(bid)
      if (res.ok) alert('Booking cancelled successfully.')
      else alert('Failed to cancel: ' + res.error)
    }
    return
  }
  selectedOffer.value = offer
  offerModalOpen.value = true
}

function toggleSave(id) {
  const idx = saved.value.indexOf(id)
  idx === -1 ? saved.value.push(id) : saved.value.splice(idx, 1)
}
</script>

<style scoped>
.deals-page { min-height: 100vh; background: var(--gray-50); padding-top: 68px; }

/* ── Hero ────────────────────────────────────────────────────────────────── */
.deals-hero {
  position: relative; overflow: hidden;
  background: var(--indigo);
  padding: 72px 5% 64px;
  display: flex; align-items: center;
}

.deals-hero__bg { position: absolute; inset: 0; pointer-events: none; }
.hero-blob {
  position: absolute; border-radius: 50%;
  filter: blur(80px); opacity: .18;
}
.hero-blob--1 {
  width: 500px; height: 500px;
  background: var(--coral);
  top: -120px; right: -80px;
}
.hero-blob--2 {
  width: 380px; height: 380px;
  background: var(--teal);
  bottom: -120px; left: 5%;
}

.deals-hero__content { position: relative; max-width: 600px; }

.deals-hero__eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: .78rem; font-weight: 700; letter-spacing: .1em;
  text-transform: uppercase; color: var(--coral);
  background: rgba(255,90,95,.12); padding: 6px 14px; border-radius: 50px;
  margin-bottom: 20px;
}
.flame-icon { font-size: 1rem; animation: flicker 1.8s ease-in-out infinite; }
@keyframes flicker {
  0%,100% { transform: scale(1) rotate(-3deg); }
  50%      { transform: scale(1.15) rotate(3deg); }
}

.deals-hero__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2.2rem, 5vw, 3.6rem);
  font-weight: 700; line-height: 1.08;
  color: #fff; margin-bottom: 16px;
}
.deals-hero__title em { color: var(--coral); font-style: italic; }

.deals-hero__sub {
  font-size: 1rem; color: rgba(255,255,255,.65);
  line-height: 1.6; max-width: 460px; margin-bottom: 24px;
}

.deals-hero__live {
  display: inline-flex; align-items: center; gap: 10px;
  background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14);
  padding: 8px 16px; border-radius: 50px;
  font-size: .84rem; color: rgba(255,255,255,.8);
}
.deals-hero__live strong { color: #fff; }
.live-dot {
  width: 8px; height: 8px; border-radius: 50%; background: #27ae60; flex-shrink: 0;
  box-shadow: 0 0 0 3px rgba(39,174,96,.3);
  animation: live-pulse 2s ease-in-out infinite;
}
@keyframes live-pulse {
  0%,100% { box-shadow: 0 0 0 3px rgba(39,174,96,.3); }
  50%      { box-shadow: 0 0 0 7px rgba(39,174,96,.08); }
}

/* ── Body ────────────────────────────────────────────────────────────────── */
.deals-body { padding: 40px 5% 64px; }

/* ── Filter bar ──────────────────────────────────────────────────────────── */
.deals-filters {
  display: flex; align-items: center; justify-content: space-between;
  gap: 16px; flex-wrap: wrap; margin-bottom: 20px;
}

.filter-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.filter-chip {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer;
  transition: all var(--transition);
}
.filter-chip:hover  { border-color: var(--coral); color: var(--coral); }
.filter-chip.active { background: var(--coral); border-color: var(--coral); color: #fff; }

.filter-sort { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.sort-label  { font-size: .82rem; font-weight: 600; color: var(--gray-400); }
.sort-select {
  border: 1.5px solid var(--gray-200); border-radius: 10px;
  padding: 8px 12px; font-family: 'DM Sans', sans-serif;
  font-size: .84rem; color: var(--indigo); background: var(--white);
  outline: none; cursor: pointer; transition: border-color var(--transition);
}
.sort-select:focus { border-color: var(--coral); }

.deals-count {
  font-size: .84rem; color: var(--gray-400); margin-bottom: 24px;
}
.deals-count strong { color: var(--indigo); }
.deals-count em     { color: var(--coral); font-style: normal; font-weight: 600; }

/* ── Grid ────────────────────────────────────────────────────────────────── */
.deals-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

/* ── Deal card ───────────────────────────────────────────────────────────── */
.deal-card {
  background: var(--white);
  border-radius: var(--radius);
  border-left: 4px solid var(--teal);
  box-shadow: var(--shadow);
  display: flex; flex-direction: column;
  cursor: pointer; position: relative; overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
  animation: card-in .35s ease both;
  animation-delay: calc(var(--i) * 50ms);
}
@keyframes card-in {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}
.deal-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.deal-card--collab   { border-left-color: var(--coral); }
.deal-card--featured { border-left-color: #f59e0b; }

/* Ribbon */
.deal-ribbon {
  position: absolute; top: 0; right: 0;
  background: #f59e0b; color: #fff;
  font-size: .7rem; font-weight: 700; letter-spacing: .04em;
  padding: 5px 14px 5px 10px;
  border-bottom-left-radius: 10px;
}

/* Collab badge */
.deal-collab {
  position: absolute; top: 12px; left: 12px;
  display: inline-flex; align-items: center; gap: 5px;
  font-size: .68rem; font-weight: 700; letter-spacing: .04em;
  text-transform: uppercase; color: var(--coral);
  background: var(--coral-lt); padding: 3px 10px; border-radius: 50px;
}

/* Discount badge */
.deal-discount {
  padding: 22px 22px 0;
  display: flex; align-items: baseline; gap: 3px;
  margin-top: 4px;
}
.deal-discount__num {
  font-family: 'Fraunces', serif;
  font-size: 3rem; font-weight: 700; line-height: 1;
  color: var(--coral);
}
.deal-discount__pct {
  font-size: .9rem; font-weight: 700; color: var(--coral); opacity: .7;
}

/* Body */
.deal-body { padding: 12px 22px 0; flex: 1; display: flex; flex-direction: column; gap: 8px; }

.deal-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.deal-type {
  font-size: .72rem; font-weight: 700; padding: 2px 9px;
  border-radius: 50px; background: var(--teal-lt); color: var(--teal-dk);
}
.deal-dates { font-size: .76rem; color: var(--gray-400); }

.deal-title {
  font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700;
  color: var(--indigo); line-height: 1.2; margin: 0;
}
.deal-desc {
  font-size: .84rem; color: var(--gray-600); line-height: 1.55;
  margin: 0; flex: 1;
}

.deal-partner {
  display: flex; align-items: center; gap: 7px;
  font-size: .76rem; color: var(--gray-600); margin-top: 4px;
}
.deal-partner-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* Footer */
.deal-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 22px 20px; margin-top: 8px;
  border-top: 1px solid var(--gray-100);
}
.deal-cta { padding: 9px 20px; font-size: .84rem; border-radius: 10px; }
.deal-save {
  width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid var(--gray-200);
  background: var(--white); font-size: 1.1rem; cursor: pointer; line-height: 1;
  display: flex; align-items: center; justify-content: center;
  color: var(--gray-400); transition: all var(--transition);
}
.deal-save:hover  { border-color: var(--coral); color: var(--coral); }
.deal-save.saved  { background: var(--coral-lt); border-color: var(--coral); color: var(--coral); }

/* ── Skeleton ────────────────────────────────────────────────────────────── */
.deal-skeleton {
  background: var(--white); border-radius: var(--radius); padding: 22px;
  border-left: 4px solid var(--gray-200); box-shadow: var(--shadow);
  display: flex; flex-direction: column; gap: 12px;
}
.skeleton-badge {
  width: 70px; height: 48px; border-radius: 8px;
  background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
  background-size: 200% 100%; animation: shimmer 1.3s infinite;
}
.skeleton-line {
  height: 13px; border-radius: 6px;
  background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
  background-size: 200% 100%; animation: shimmer 1.3s infinite;
}
.skeleton-line--title { height: 18px; width: 80%; }
.skeleton-line--short { width: 50%; }
.skeleton-btn {
  height: 36px; width: 110px; border-radius: 50px; margin-top: 6px;
  background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
  background-size: 200% 100%; animation: shimmer 1.3s infinite;
}
@keyframes shimmer { to { background-position: -200% 0; } }

/* ── Empty state ─────────────────────────────────────────────────────────── */
.deals-empty { text-align: center; padding: 80px 20px; }
.deals-empty__icon  { font-size: 3.5rem; margin-bottom: 16px; }
.deals-empty__title { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; margin-bottom: 10px; }
.deals-empty__sub   { font-size: .92rem; color: var(--gray-400); margin-bottom: 28px; }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .deals-hero   { padding: 52px 4% 48px; }
  .deals-body   { padding: 28px 4% 48px; }
  .deals-filters { flex-direction: column; align-items: flex-start; }
  .deals-grid   { grid-template-columns: 1fr; }
}

.btn-outline-danger {
  background: transparent;
  border: 1.5px solid var(--coral);
  color: var(--coral);
}
.btn-outline-danger:hover {
  background: var(--coral-lt);
}
</style>