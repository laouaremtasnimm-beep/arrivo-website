<template>
  <div class="dashboard">

    <!-- ── Desktop Sidebar ───────────────────── -->
    <DashboardSidebar
      :user="user"
      :active-section="activeSection"
      :collapsed="sidebarCollapsed"
      :unread-messages="unreadMessages"
      :pending-collabs="pendingCollabs"
      @toggle="sidebarCollapsed = !sidebarCollapsed"
      @navigate="setSection"
      @logout="handleLogout"
    />

    <!-- ── Mobile backdrop + sidebar ─────────── -->
    <Transition name="backdrop-fade">
      <div class="mobile-backdrop" v-if="mobileSidebarOpen" @click="mobileSidebarOpen = false" />
    </Transition>
    <div class="mobile-sidebar" :class="{ open: mobileSidebarOpen }">
      <DashboardSidebar
        :user="user"
        :active-section="activeSection"
        :collapsed="false"
        :unread-messages="unreadMessages"
        :pending-collabs="pendingCollabs"
        @toggle="mobileSidebarOpen = false"
        @navigate="setSection($event); mobileSidebarOpen = false"
        @logout="handleLogout"
      />
    </div>

    <!-- ── Main ──────────────────────────────── -->
    <div class="dashboard__main">

      <DashboardHeader
        :user="user"
        :notification-count="notificationCount"
        :message-count="unreadMessages"
        @open-mobile-sidebar="mobileSidebarOpen = true"
        @quick-action="handleQuickAction"
        @navigate-section="setSection"
      />

      <div class="dashboard__section-bar">
        <div class="dashboard__section-title">{{ sectionTitle }}</div>
        <div class="dashboard__section-meta">{{ sectionMeta }}</div>
      </div>

      <!-- Global loading/error banner -->
      <div v-if="loadError" class="dashboard__error-banner">
        ⚠️ {{ loadError }}
      </div>

      <div class="dashboard__content">
        <Transition name="section-fade" mode="out-in">
<OverviewSection
  v-if="activeSection === 'overview'"
  key="overview"
  :role="user.role"
  :bookings="bookings"
  :messages="messages"
  :items="isAgency ? packages : services"
  :current-user-id="user.userID ?? user.id"
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

          <div v-else-if="activeSection === 'packages' && isAgency" key="packages">
            <PackagesTable
              :packages="packages"
              @add="openPackageForm(null)"
              @edit="openPackageForm($event)"
              @delete="handleDeletePackage"
            />
          </div>

          <div v-else-if="activeSection === 'services' && isProvider" key="services">
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
  :current-user-id="user.userID"
  :auto-open-conversation="pendingOpenConversation"
  @open="handleOpenMessage"
  @compose="handleCompose"
  @delete="handlePermanentDeleteMessage"
  @conversation-opened="pendingOpenConversation = null"
/>

          <DashboardReviews
            v-else-if="activeSection === 'reviews'"
            key="reviews"
            :role="user.role"
            :user-id="user.userID"
          />

          <OffersPanel
            v-else-if="activeSection === 'offers'"
            key="offers"
            :role="user.role"
            :user-id="user.userID ?? user.id"
            @add="openOfferForm(null)"
            @edit="openOfferForm($event)"
          />

          <CollaborationsPanel
            v-else-if="activeSection === 'collaborations'"
            key="collaborations"
            :collaborations="collaborations"
            @open-form="collabFormOpen = true"
            @accept="handleAcceptCollab"
            @decline="handleDeclineCollab"
            @counter="handleCounterCollab"
            @end="handleEndCollab"
          />

        </Transition>
      </div>
    </div>

    <!-- ── Modals ─────────────────────────────── -->
    <PackageFormModal
      v-model="packageFormOpen"
      :package="editingPackage"
      @save="handleSavePackage"
    />

    <ServiceFormModal
      v-model="serviceFormOpen"
      :service="editingService"
      @save="handleSaveService"
    />

    <OfferFormModal
      v-if="isAgency"
      v-model="offerFormOpen"
      :offer="editingOffer"
      :agency-id="user.userID ?? user.id"
      @save="handleSaveOffer"
    />

    <ServiceOfferFormModal
      v-if="isProvider"
      v-model="offerFormOpen"
      :offer="editingOffer"
      :provider-id="user.userID ?? user.id"
      @save="handleSaveOffer"
    />

  <CollabFormModal
  v-model="collabFormOpen"
  :locked-asset="collabLockedPackage"
  :initiator-type="user?.role"
  :own-id="user?.userID ?? user?.id"
  @created="handleCollabCreated"
/>
    <BookingDetailModal
      v-model="bookingDetailOpen"
      :booking="activeBooking"
      @confirm="handleConfirmBooking"
    />

    <OfferDetailModal
      v-model="offerDetailOpen"
      :offer="selectedOffer"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useOffers } from '@/composables/useOffers'
import { useWishlist } from '@/composables/useWishlist'
import { useNotifications } from '@/composables/useNotifications'
import { useMessages } from '@/composables/useMessages'
import { useCollabActions } from '@/composables/useCollabActions'

import DashboardSidebar    from '@/components/dashboard/DashboardSidebar.vue'
import DashboardHeader     from '@/components/dashboard/DashboardHeader.vue'
import OverviewSection     from '@/components/dashboard/OverviewSection.vue'
import BookingsTable       from '@/components/dashboard/BookingsTable.vue'
import PackagesTable       from '@/components/dashboard/PackagesTable.vue'
import ServicesTable       from '@/components/dashboard/ServicesTable.vue'
import MessagesPanel       from '@/components/dashboard/MessagesPanel.vue'
import DashboardReviews    from '@/components/dashboard/DashboardReviews.vue'
import OffersPanel         from '@/components/dashboard/OffersPanel.vue'
import OfferFormModal      from '@/components/dashboard/OfferFormModal.vue'
import ServiceOfferFormModal from '@/components/dashboard/ServiceOfferFormModal.vue'
import PackageFormModal    from '@/components/dashboard/PackageFormModal.vue'
import ServiceFormModal    from '@/components/dashboard/Serviceformmodal.vue'
import CollaborationsPanel from '@/components/dashboard/CollaborationsPanel.vue'
import CollabFormModal     from '@/components/dashboard/CollabFormModal.vue'
import BookingDetailModal  from '@/components/dashboard/BookingDetailModal.vue'
import OfferDetailModal    from '@/components/home/OfferDetailModal.vue'

// ─────────────────────────────────────────────────────────────────────────
// BASE URL — same pattern that fixed login/register
// ─────────────────────────────────────────────────────────────────────────
const API = '/arrivo-website/backend/api/v1'

const router = useRouter()
const { user, isAgency, isProvider, logout } = useAuth()
const { addOffer, saveOffer, deleteOffer, saveOfferToDB, deleteOfferFromDB, allOffers } = useOffers()
const { toggle: toggleWishlist } = useWishlist()
const { startPolling: startNotifPolling, stopPolling: stopNotifPolling, unreadCount: getUnreadCount } = useNotifications()
const { fetchMessages, getUnreadCount: getMsgUnreadCount, messages: dbMessages, startPolling, stopPolling } = useMessages()

// ── Layout ────────────────────────────────────────────────────────────────
const sidebarCollapsed  = ref(false)
const mobileSidebarOpen = ref(false)
const activeSection     = ref('overview')
const loadError         = ref(null)

const sectionMap = {
  overview:       { title: 'Overview',        meta: 'Your dashboard at a glance'              },
  bookings:       { title: 'Bookings',         meta: 'Manage and track all reservations'       },
  packages:       { title: 'Travel Packages',  meta: 'Create and manage your packages'         },
  services:       { title: 'My Services',      meta: 'Create and manage your service listings' },
  messages:       { title: 'Messages',         meta: 'Communicate with your customers'         },
  reviews:        { title: 'Reviews',          meta: 'See what customers are saying'           },
  offers:         { title: 'Special Offers',   meta: 'Run promotions and discount campaigns'   },
  collaborations: { title: 'Collaborations',   meta: 'Co-create joint offers with partners'    },
}
const sectionTitle = computed(() => sectionMap[activeSection.value]?.title || '')
const sectionMeta  = computed(() => sectionMap[activeSection.value]?.meta  || '')

function setSection(s) {
  activeSection.value = s
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// ── Counts ────────────────────────────────────────────────────────────────
const unreadMessages    = computed(() => getMsgUnreadCount(user.value?.userID ?? user.value?.id).value)
const pendingCollabs    = computed(() => collaborations.value.filter(c => c.direction === 'incoming' && c.status === 'pending').length)
const notificationCount = computed(() => getUnreadCount(user.value?.role, user.value?.userID ?? user.value?.id, 'notification').value)

// ── Auth ──────────────────────────────────────────────────────────────────
function handleLogout() { logout(); router.push('/') }
function handleQuickAction() {
  if (isAgency.value)   { openPackageForm(null); setSection('packages') }
  if (isProvider.value) { openServiceForm(null); setSection('services') }
}

// ─────────────────────────────────────────────────────────────────────────
// DATA REFS  (start empty — filled by onMounted fetches)
// ─────────────────────────────────────────────────────────────────────────
const bookings       = ref([])
const packages       = ref([])
const services       = ref([])
const messages       = dbMessages
const collaborations = ref([
  // Collaborations are not yet stored in the DB, so we keep the demo seed here.
  // Replace with a real fetch when you add a collaborations table.
  {
    collabID:    9001,
    direction:   'incoming',
    status:      'pending',
    initiator:   { name: 'Wanderlust Travels', role: 'agency' },
    partner:     { id: 'p1', name: 'Alpine Escapes', role: 'Service Provider', color: '#2EC4B6' },
    title:       'Alps Fly & Drive Bundle',
    discount:    20,
    type:        'Bundle',
    startDate:   '2025-07-01',
    endDate:     '2025-07-31',
    description: 'We propose a joint summer bundle combining your Mountain Guide service with our Swiss Alps Winter Retreat package at a 20% combined discount.',
    sentDate:    'Jun 11, 2025',
  },
])

// ─────────────────────────────────────────────────────────────────────────
// FETCH HELPERS  — all use the full /arrivo-website/... path
// ─────────────────────────────────────────────────────────────────────────
// ── Add this normalizer near the other fetch helpers ──────────────────────
function normalizeBooking(b, role) {
  // Resolve item name depending on booking type and role
  let itemName = b.item_title ?? ''
  if (b.booking_type === 'package')     itemName = b.package_title  ?? b.item_title ?? ''
  if (b.booking_type === 'service')     itemName = b.service_title  ?? b.item_title ?? ''
  if (b.booking_type === 'destination') itemName = b.destination_name ?? b.item_title ?? ''
  if (b.booking_type === 'offer')       itemName = b.offer_title    ?? b.item_title ?? ''

  // Tourist sees their own bookings — no guest_first in response
  const guestName = (b.guest_first && b.guest_last)
    ? `${b.guest_first} ${b.guest_last}`
    : (user.value?.first_name && user.value?.last_name)
      ? `${user.value.first_name} ${user.value.last_name}`
      : 'Guest'

  return {
    ...b,
    reservationID: b.id,
    guestName,
    itemName,
    date:       b.check_in ?? '',
    totalPrice: parseFloat(b.total_price) || 0,
    status:     b.status ?? 'confirmed',
  }
}

// ── Replace fetchBookings entirely ───────────────────────────────────────
async function fetchBookings() {
  try {
    const uid = user.value?.userID          // ← was user.value?.id (wrong key)
    if (!uid) return

    let param = `user_id=${uid}`
    if (isAgency.value)   param = `agency_id=${uid}`
    if (isProvider.value) param = `provider_id=${uid}`

    const res  = await fetch(`${API}/bookings.php?${param}`)
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Failed to load bookings')

    bookings.value = (data.bookings ?? []).map(b => normalizeBooking(b, user.value?.role))
  } catch (e) {
    loadError.value = e.message
  }
}

async function fetchPackages() {
  if (!isAgency.value) return
  try {
    const res  = await fetch(`${API}/packages.php?agency_id=${user.value.userID}`)
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Failed to load packages')
    packages.value = data.packages ?? []
  } catch (e) {
    loadError.value = e.message
  }
}

async function fetchServices() {
  if (!isProvider.value) return
  try {
    const res = await fetch(`${API}/services.php?user_id=${user.value.userID}`)
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Failed to load services')
    services.value = data.services ?? []
  } catch (e) {
    loadError.value = e.message
  }
}


onMounted(async () => {
  // Wait one tick for useAuth to hydrate from storage
  await nextTick()
  if (!user.value) return
  const uid = user.value.userID || user.value.id
  startNotifPolling(uid)
  await fetchBookings()
  await fetchPackages()
  await fetchServices()
  await fetchMessages(uid)
  await fetchCollaborations()

  startPolling(uid)
})

onUnmounted(() => {
  stopNotifPolling()
  stopPolling()
})

// ─────────────────────────────────────────────────────────────────────────
// BOOKING HANDLERS
// ─────────────────────────────────────────────────────────────────────────
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
  } catch (e) {
    loadError.value = e.message
  }
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
  } catch (e) {
    loadError.value = e.message
  }
}

const bookingDetailOpen = ref(false)
const activeBooking     = ref({})
const offerDetailOpen   = ref(false)
const selectedOffer     = ref(null)

function handleViewBooking(b) {
  const paths = {
    package:     '/packages',
    service:     '/services',
    destination: '/destinations',
  }
  const type = b.booking_type
  const id   = b.package_id ?? b.service_id ?? b.destination_id

  if (id && type && paths[type]) {
    router.push(`${paths[type]}/${id}`)
    return
  }

  // If it's an offer, show the joint offer detail modal
  if (type === 'offer') {
    selectedOffer.value = {
      id: b.item_id || b.offer_id,
      title: b.itemName || b.offer_title,
      description: b.offer_description || b.description || b.notes || '',
      discount: b.discount || 0,
      startDate: b.offer_start || b.start_date || '',
      endDate: b.offer_end || b.end_date || '',
      owner_id: b.owner_id || b.agency_id || b.provider_id || b.item_owner_id,
    }
    offerDetailOpen.value = true
    return
  }

  activeBooking.value = {
    ...b,
    itemName:   b.itemName   ?? b.offer_title ?? b.package_title ?? b.service_title ?? b.item_title ?? '—',
    guestName:  b.guestName  ?? (b.guest_first ? `${b.guest_first} ${b.guest_last}` : 'Guest'),
    totalPrice: Number(b.totalPrice ?? b.total_price ?? 0),
    date:       b.date ?? b.check_in ?? null,
  }
  bookingDetailOpen.value = true
}

// ─────────────────────────────────────────────────────────────────────────
// PACKAGE HANDLERS
// ─────────────────────────────────────────────────────────────────────────
const packageFormOpen = ref(false)
const editingPackage  = ref(null)

function openPackageForm(pkg) {
  editingPackage.value  = pkg ?? null
  packageFormOpen.value = true
}

async function handleSavePackage(payload) {
  try {
    const isNew = !payload.id
    const res = await fetch(`${API}/packages.php`, {
      method: isNew ? 'POST' : 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(isNew ? { ...payload, agency_id: user.value.userID } : payload),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Save failed')

    if (isNew) {
      packages.value.unshift({ ...payload, id: data.package_id })
      toggleWishlist('package', data.package_id) // Add to wishlist
    } else {
      const idx = packages.value.findIndex(p => p.id === payload.id)
      if (idx !== -1) packages.value[idx] = payload
    }
  } catch (e) {
    loadError.value = e.message
  }
}

async function handleDeletePackage(pkg) {
  if (!confirm(`Delete "${pkg.title}"? This cannot be undone.`)) return
  try {
    const res = await fetch(`${API}/packages.php`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: pkg.id }),
    })
    if (!res.ok) throw new Error('Delete failed')
    
    // Remove from local packages list
    packages.value = packages.value.filter(p => p.id !== pkg.id)
    
    // Cascade cleanup: remove any offers linked to this package in the UI
    // Note: The backend handles DB cleanup, but we must sync the local useOffers state.
    allOffers.value.forEach(o => {
      // Offers can have multiple packages (offer_packages table), but for simplicity
      // and matching the user's request "an offer cant be empty it must be deleted",
      // we check if this package was part of it.
      const isLinked = (o.package_id === pkg.id) || 
                       (o.packages && o.packages.some(p => p.id === pkg.id))
      if (isLinked) deleteOffer(o.offerID)
    })
  } catch (e) {
    loadError.value = e.message
  }
}

// ─────────────────────────────────────────────────────────────────────────
// SERVICE HANDLERS
// ─────────────────────────────────────────────────────────────────────────
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
      toggleWishlist('service', data.service_id) // Add to wishlist
    } else {
      const idx = services.value.findIndex(s => s.id === payload.id)
      if (idx !== -1) services.value[idx] = payload
    }
  } catch (e) {
    loadError.value = e.message
  }
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
    
    // Remove from local services list
    services.value = services.value.filter(s => s.id !== svc.id)

    // Cascade cleanup: remove any offers linked to this service in the UI
    allOffers.value.forEach(o => {
      if (o.service_id === svc.id) deleteOffer(o.offerID)
    })
  } catch (e) {
    loadError.value = e.message
  }
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
  } catch (e) {
    loadError.value = e.message
  }
}

// ─────────────────────────────────────────────────────────────────────────
// MESSAGE HANDLERS
// ─────────────────────────────────────────────────────────────────────────
async function handleOpenMessage(msg) {
  // Navigate to messages section first
  setSection('messages')

  // Give the section time to mount, then open the thread
  await nextTick()

  // Store which conversation to auto-open
  pendingOpenConversation.value = msg
}

async function handlePermanentDeleteMessage(msg) {
  try {
    // If it's a demo message, just filter locally
    if (DEMO_MSG_IDS.has(msg.messageID)) {
      messages.value = messages.value.filter(m => m.messageID !== msg.messageID)
      return
    }

    const res = await fetch(`${API}/messages.php`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: msg.id }),
    })
    if (!res.ok) throw new Error('Delete failed')
    
    // Remove from local state
    messages.value = messages.value.filter(m => m.id !== msg.id)
  } catch (e) {
    loadError.value = e.message
  }
}

function handleCompose() { console.log('Compose — wire to a compose modal later') }

// ─────────────────────────────────────────────────────────────────────────
// REVIEW HANDLERS
// ─────────────────────────────────────────────────────────────────────────
function handleReplyReview(r)  { console.log('Reply:', r) }

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
  } catch (e) {
    loadError.value = e.message
  }
}

// ─────────────────────────────────────────────────────────────────────────
// OFFER HANDLERS  (composable stays the source of truth — no DB yet)
// ─────────────────────────────────────────────────────────────────────────
const offerFormOpen = ref(false)
const editingOffer  = ref(null)
const pendingOpenConversation = ref(null)

function openOfferForm(offer) {
  if (offer?.source === 'collab') return
  editingOffer.value  = offer ?? null
  offerFormOpen.value = true
}



async function handleSaveOffer(payload) {
  // If provider is creating a new service from scratch within the offer flow
  if (isProvider.value && payload.newService) {
    try {
      const svcRes = await fetch(`${API}/services.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...payload.newService, provider_id: user.value.userID })
      })
      const svcData = await svcRes.json()
      if (svcRes.ok) {
        payload.serviceId = svcData.service_id
        await fetchServices() // Refresh the main services list
      }
    } catch (e) {
      console.error('Failed to create service for offer:', e)
    }
  }

  // Use the ID from the payload which the modal correctly identifies
  const offerId = payload.offerID;
  
  await saveOfferToDB({ 
    ...payload, 
    offerID: offerId,
    owner_id: user.value?.userID || user.value?.id 
  })
  
  offerFormOpen.value = false
  editingOffer.value  = null // Clear state after saving
}
// ─────────────────────────────────────────────────────────────────────────
// COLLABORATION HANDLERS  (in-memory only until a DB table is added)
// ─────────────────────────────────────────────────────────────────────────
const collabFormOpen = ref(false)
const {
  fetchCollaborations,
  handleAcceptCollab,
  handleDeclineCollab,
  handleCounterCollab,
  handleEndCollab,
  handleWithdrawCollab,
} = useCollabActions({ collaborations, user, addOffer, deleteOffer, loadError })

const collabLockedPackage = ref(null)
function handleCollabCreated() {
  collabLockedPackage.value = null
  fetchCollaborations()
}

function handleSendCollab(payload) {
  collaborations.value.unshift({ ...payload, direction: 'outgoing', status: 'pending' })
  setTimeout(() => {
    collaborations.value.push({
      ...payload,
      collabID:  payload.collabID + 0.5,
      direction: 'incoming',
      status:    'pending',
      initiator: { name: payload.partner.name, role: payload.partner.role },
      partner:   { id: 'self', name: user.value?.name || 'You', role: user.value?.role, color: '#FF5A5F' },
    })
  }, 1500)
}

</script>

<style scoped>
.dashboard {
  display: flex; min-height: 100vh; background: var(--gray-50);
}
.dashboard__main {
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
  margin: 12px 32px 0;
  padding: 10px 16px;
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 8px;
  font-size: .875rem;
  color: #856404;
}

.section-fade-enter-active, .section-fade-leave-active { transition: opacity .18s ease, transform .18s ease; }
.section-fade-enter-from, .section-fade-leave-to { opacity: 0; transform: translateY(8px); }

.mobile-sidebar {
  display: none; position: fixed; top: 0; left: -280px; bottom: 0;
  width: 260px; z-index: 80; transition: left .3s ease;
}
.mobile-sidebar.open { left: 0; }
.mobile-backdrop {
  display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 75;
}
.backdrop-fade-enter-active, .backdrop-fade-leave-active { transition: opacity .25s ease; }
.backdrop-fade-enter-from, .backdrop-fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .dashboard            { display: block; }
  .dashboard__main      { display: flex; flex-direction: column; }
  .mobile-sidebar       { display: block; }
  .mobile-backdrop      { display: block; }
  .dashboard__section-bar { padding: 16px 16px 0; }
  .dashboard__content   { padding: 16px; }
}
</style>