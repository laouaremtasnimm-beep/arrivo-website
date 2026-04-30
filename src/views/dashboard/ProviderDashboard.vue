<template>
  <div class="dashboard-layout">

    <DashboardSidebar
      :user="user"
      :active-section="activeSection"
      :collapsed="sidebarCollapsed"
      :unread-messages="unreadMessages"
      @toggle="sidebarCollapsed = !sidebarCollapsed"
      @navigate="setSection"
      @logout="handleLogout"
    />

    <div class="dashboard-content">

      <DashboardHeader
        :user="user"
        :notification-count="unreadMessages"
        @open-mobile-sidebar="mobileSidebarOpen = true"
        @quick-action="openServiceForm(null)"
        @navigate-section="setSection"
      />

      <div v-if="loadError" class="dashboard__error-banner">
        ⚠️ {{ loadError }}
      </div>

      <div class="dashboard__section-bar">
        <div class="dashboard__section-title">{{ sectionTitle }}</div>
        <div class="dashboard__section-meta">{{ sectionMeta }}</div>
      </div>

      <div class="dashboard__content">
        <Transition name="section-fade" mode="out-in">

          <OverviewSection
            v-if="activeSection === 'overview'"
            key="overview"
            :role="user.role"
            :bookings="bookings"
            :messages="messages"
            @navigate="setSection"
            @open-message="handleOpenMessage"
          />

          <BookingsTable
            v-else-if="activeSection === 'bookings'"
            key="bookings"
            :bookings="bookings"
            :role="user.role"
            @confirm="handleConfirmBooking"
            @cancel="handleCancelBooking"
            @view="handleViewBooking"
          />

          <!-- Provider-specific: services, not packages -->
          <div v-else-if="activeSection === 'services'" key="services">
            <ServicesTable
              :services="services"
              @add="openServiceForm(null)"
              @edit="openServiceForm($event)"
              @delete="handleDeleteService"
              @toggle-availability="handleToggleAvailability"
            />
          </div>

          <MessagesPanel
            v-else-if="activeSection === 'messages'"
            key="messages"
            :messages="messages"
            :current-user-id="user.userID ? parseInt(user.userID) : null"
            @open="handleOpenMessage"
            @compose="handleCompose"
          />

          <ReviewsPanel
            v-else-if="activeSection === 'reviews'"
            key="reviews"
            :reviews="reviews"
            @reply="handleReplyReview"
            @delete="handleDeleteReview"
          />

          <!-- Provider-specific: offers panel (no collaborations tab) -->
          <OffersPanel
            v-else-if="activeSection === 'offers'"
            key="offers"
            :role="user.role"
            :user-id="user.userID"
            @add="openOfferForm(null)"
            @edit="openOfferForm($event)"
          />

        </Transition>
      </div>
    </div>

    <!-- Modals — providers manage services and offers, not packages -->
    <ServiceFormModal
      v-model="serviceFormOpen"
      :service="editingService"
      @save="handleSaveService"
    />

    <OfferFormModal
      v-model="offerFormOpen"
      :offer="editingOffer"
      @save="handleSaveOffer"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useOffers } from '@/composables/useOffers'

import DashboardSidebar from '@/components/dashboard/DashboardSidebar.vue'
import DashboardHeader  from '@/components/dashboard/DashboardHeader.vue'
import OverviewSection  from '@/components/dashboard/OverviewSection.vue'
import BookingsTable    from '@/components/dashboard/BookingsTable.vue'
import ServicesTable    from '@/components/dashboard/ServicesTable.vue'
import MessagesPanel    from '@/components/dashboard/MessagesPanel.vue'
import ReviewsPanel     from '@/components/dashboard/ReviewsPanel.vue'
import OffersPanel      from '@/components/dashboard/OffersPanel.vue'
import OfferFormModal   from '@/components/dashboard/OfferFormModal.vue'
import ServiceFormModal from '@/components/dashboard/ServiceFormModal.vue'

const API = '/arrivo-website/backend/api/v1'

const router = useRouter()
const { user, logout } = useAuth()
const { saveOffer, saveOfferToDB } = useOffers()

// ── Layout ────────────────────────────────────────────────────────────────
const sidebarCollapsed  = ref(false)
const mobileSidebarOpen = ref(false)
const activeSection     = ref('overview')
const loadError         = ref(null)

// Note: no 'packages' or 'collaborations' sections for providers
const sectionMap = {
  overview: { title: 'Overview',      meta: 'Your provider dashboard at a glance'     },
  bookings: { title: 'Bookings',       meta: 'Manage and track all reservations'       },
  services: { title: 'My Services',    meta: 'Create and manage your service listings' },
  messages: { title: 'Messages',       meta: 'Communicate with your customers'         },
  reviews:  { title: 'Reviews',        meta: 'See what customers are saying'           },
  offers:   { title: 'Special Offers', meta: 'Run promotions and discount campaigns'   },
}
const sectionTitle = computed(() => sectionMap[activeSection.value]?.title || '')
const sectionMeta  = computed(() => sectionMap[activeSection.value]?.meta  || '')

function setSection(s) {
  activeSection.value = s
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const unreadMessages = computed(() => messages.value.filter(m => !m.is_read).length)

function handleLogout() { logout(); router.push('/') }

// ── Data refs ─────────────────────────────────────────────────────────────
const bookings = ref([])
const services = ref([])
const messages = ref([])
const reviews  = ref([])

// ── Fetches — all use provider_id ─────────────────────────────────────────
async function fetchBookings() {
  try {
    const res  = await fetch(`${API}/bookings.php?provider_id=${user.value.userID}`)
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Failed to load bookings')
    bookings.value = data.bookings ?? []
  } catch (e) { loadError.value = e.message }
}

async function fetchServices() {
  try {
    const res  = await fetch(`${API}/services.php?provider_id=${user.value.userID}`)
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Failed to load services')
    services.value = data.services ?? []
  } catch (e) { loadError.value = e.message }
}

// Normalize raw DB row → shape expected by MessagesPanel / MessageThread
function normalizeMessage(m) {
  return {
    messageID: m.id,
    id:        m.id,
    sender_id: parseInt(m.sender_id) || null,
    from:      (`${m.sender_first ?? ''} ${m.sender_last ?? ''}`).trim() || 'Unknown',
    title:     m.subject  ?? '(no subject)',
    content:   m.content  ?? '',
    date:      m.created_at ?? '',
    read:      !!parseInt(m.is_read),
    sent:      false,
    replies:   [],
  }
}

async function fetchMessages() {
  try {
    const res  = await fetch(`${API}/messages.php?user_id=${user.value.userID}`)
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Failed to load messages')
    messages.value = (data.messages ?? []).map(normalizeMessage)
  } catch (e) { loadError.value = e.message }
}

// Reviews are per-service — fetch for each service the provider owns
async function fetchReviews() {
  try {
    const all = []
    for (const svc of services.value) {
      const res  = await fetch(`${API}/reviews.php?item_type=service&item_id=${svc.id}`)
      const data = await res.json()
      if (res.ok) all.push(...(data.reviews ?? []))
    }
    reviews.value = all.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  } catch (e) { loadError.value = e.message }
}

onMounted(async () => {
  await fetchBookings()
  await fetchServices()
  await fetchMessages()
  await fetchReviews()  // must run after fetchServices
})

// ── Booking handlers ──────────────────────────────────────────────────────
async function handleConfirmBooking(b) {
  try {
    const res = await fetch(`${API}/bookings.php`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: b.id, status: 'confirmed' }),
    })
    if (!res.ok) throw new Error('Update failed')
    const idx = bookings.value.findIndex(x => x.id === b.id)
    if (idx !== -1) bookings.value[idx].status = 'confirmed'
  } catch (e) { loadError.value = e.message }
}

async function handleCancelBooking(b) {
  try {
    const res = await fetch(`${API}/bookings.php`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: b.id, status: 'cancelled' }),
    })
    if (!res.ok) throw new Error('Update failed')
    const idx = bookings.value.findIndex(x => x.id === b.id)
    if (idx !== -1) bookings.value[idx].status = 'cancelled'
  } catch (e) { loadError.value = e.message }
}

function handleViewBooking(b) { console.log('View booking:', b) }

// ── Service handlers ──────────────────────────────────────────────────────
const serviceFormOpen = ref(false)
const editingService  = ref(null)

function openServiceForm(svc) {
  editingService.value  = svc ?? null
  serviceFormOpen.value = true
}

async function handleSaveService(payload) {
  try {
    const isNew = !payload.id
    const res = await fetch(`${API}/services.php`, {
      method: isNew ? 'POST' : 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(isNew ? { ...payload, provider_id: user.value.userID } : payload),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Save failed')
    if (isNew) {
      services.value.unshift({ ...payload, id: data.service_id })
    } else {
      const idx = services.value.findIndex(s => s.id === payload.id)
      if (idx !== -1) services.value[idx] = payload
    }
  } catch (e) { loadError.value = e.message }
}

async function handleDeleteService(svc) {
  if (!confirm(`Delete "${svc.title}"? This cannot be undone.`)) return
  try {
    const res = await fetch(`${API}/services.php`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: svc.id }),
    })
    if (!res.ok) throw new Error('Delete failed')
    services.value = services.value.filter(s => s.id !== svc.id)
  } catch (e) { loadError.value = e.message }
}

async function handleToggleAvailability(svc) {
  try {
    const res = await fetch(`${API}/services.php`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: svc.id }),
    })
    if (!res.ok) throw new Error('Toggle failed')
    const idx = services.value.findIndex(s => s.id === svc.id)
    if (idx !== -1) services.value[idx].is_available = services.value[idx].is_available ? 0 : 1
  } catch (e) { loadError.value = e.message }
}

// ── Message handlers ──────────────────────────────────────────────────────
async function handleOpenMessage(msg) {
  try {
    const res = await fetch(`${API}/messages.php`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: msg.id }),
    })
    if (!res.ok) throw new Error('Mark-read failed')
    const idx = messages.value.findIndex(m => m.id === msg.id)
    if (idx !== -1) messages.value[idx].is_read = 1
  } catch (e) { loadError.value = e.message }
}

function handleCompose() { console.log('Compose — wire to a compose modal later') }

// ── Review handlers ───────────────────────────────────────────────────────
function handleReplyReview(r) { console.log('Reply:', r) }

async function handleDeleteReview(r) {
  if (!confirm('Delete this review?')) return
  try {
    const res = await fetch(`${API}/reviews.php`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: r.id }),
    })
    if (!res.ok) throw new Error('Delete failed')
    reviews.value = reviews.value.filter(x => x.id !== r.id)
  } catch (e) { loadError.value = e.message }
}

// ── Offer handlers ────────────────────────────────────────────────────────
const offerFormOpen = ref(false)
const editingOffer  = ref(null)

function openOfferForm(offer) {
  if (offer?.source === 'collab') return
  editingOffer.value  = offer ?? null
  offerFormOpen.value = true
}

// Persist to DB and update the in-memory store
async function handleSaveOffer(payload) {
  await saveOfferToDB({ ...payload, owner_id: user.value?.userID })
}
</script>

<style scoped>
.dashboard-layout {
  display: flex; min-height: 100vh; background: var(--gray-50);
}
.dashboard-content {
  flex: 1; display: flex; flex-direction: column; min-width: 0; overflow-x: hidden;
}
.dashboard__section-bar {
  display: flex; align-items: baseline; gap: 12px;
  padding: 20px 32px 0; flex-wrap: wrap;
}
.dashboard__section-title {
  font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; color: var(--indigo);
}
.dashboard__section-meta { font-size: .84rem; color: var(--gray-400); }
.dashboard__content { padding: 20px 32px 40px; flex: 1; }

.dashboard__error-banner {
  margin: 12px 32px 0; padding: 10px 16px;
  background: #fff3cd; border: 1px solid #ffc107;
  border-radius: 8px; font-size: .875rem; color: #856404;
}

.section-fade-enter-active, .section-fade-leave-active { transition: opacity .18s ease, transform .18s ease; }
.section-fade-enter-from,   .section-fade-leave-to     { opacity: 0; transform: translateY(8px); }

@media (max-width: 768px) {
  .dashboard-layout  { display: block; }
  .dashboard-content { display: flex; flex-direction: column; }
  .dashboard__section-bar { padding: 16px 16px 0; }
  .dashboard__content { padding: 16px; }
}
</style>