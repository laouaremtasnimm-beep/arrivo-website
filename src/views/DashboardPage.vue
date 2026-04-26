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
        @open-mobile-sidebar="mobileSidebarOpen = true"
        @quick-action="handleQuickAction"
        @navigate-section="setSection"
      />

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

          <!-- OFFERS — reads from useOffers composable, no :offers prop needed -->
         <OffersPanel
  v-else-if="activeSection === 'offers'"
  key="offers"
  :role="user.role"
  @add="openOfferForm(null)"
  @edit="openOfferForm($event)"
/>

          <!-- COLLABORATIONS -->
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

    <!-- OfferFormModal calls saveOffer from the composable on @save -->
    <OfferFormModal
      v-model="offerFormOpen"
      :offer="editingOffer"
      @save="saveOffer"
    />

    <CollabFormModal
      v-model="collabFormOpen"
      @send="handleSendCollab"
    />

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useOffers } from '@/composables/useOffers'   // ← single source of truth

import DashboardSidebar    from '@/components/dashboard/DashboardSidebar.vue'
import DashboardHeader     from '@/components/dashboard/DashboardHeader.vue'
import OverviewSection     from '@/components/dashboard/OverviewSection.vue'
import BookingsTable       from '@/components/dashboard/BookingsTable.vue'
import PackagesTable       from '@/components/dashboard/PackagesTable.vue'
import ServicesTable       from '@/components/dashboard/ServicesTable.vue'
import MessagesPanel       from '@/components/dashboard/MessagesPanel.vue'
import ReviewsPanel        from '@/components/dashboard/ReviewsPanel.vue'
import OffersPanel         from '@/components/dashboard/OffersPanel.vue'
import OfferFormModal      from '@/components/dashboard/OfferFormModal.vue'
import PackageFormModal    from '@/components/dashboard/PackageFormModal.vue'
import ServiceFormModal    from '@/components/dashboard/ServiceFormModal.vue'
import CollaborationsPanel from '@/components/dashboard/CollaborationsPanel.vue'
import CollabFormModal     from '@/components/dashboard/CollabFormModal.vue'

const router = useRouter()
const { user, isAgency, isProvider, logout } = useAuth()

// ── Offers — composable is the ONLY source of truth ──────────────────────
// At the top of <script setup> in DashboardMain.vue
const { saveOffer, deleteOffer, addOffer } = useOffers()
// Add this — either from a composable or directly mutate the local `services` ref

// ── Layout ────────────────────────────────────────────────────────────────
const sidebarCollapsed  = ref(false)
const mobileSidebarOpen = ref(false)
const activeSection     = ref('overview')

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

function handleCounterCollab({ original, counter }) {
  // Mark the original incoming request as countered
  const idx = collaborations.value.findIndex(c => c.collabID === original.collabID)
  if (idx !== -1) collaborations.value[idx].status = 'countered'

  // Add the counter as a new outgoing request
  collaborations.value.unshift({
    ...counter,
    collabID:  Date.now(),
    direction: 'outgoing',
    status:    'pending',
    sentDate:  new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }),
    initiator: { name: user.value?.name || 'You', role: user.value?.role },
    partner:   original.partner,
    isCounter: true,
  })

  // Simulate: after 1.5s the partner receives it as a new incoming
  setTimeout(() => {
    collaborations.value.push({
      ...counter,
      collabID:  Date.now() + 0.5,
      direction: 'incoming',
      status:    'pending',
      sentDate:  'Just now',
      initiator: { name: original.partner?.name, role: original.partner?.role },
      partner:   { id: 'self', name: user.value?.name || 'You', role: user.value?.role, color: '#FF5A5F' },
      isCounter: true,
    })
  }, 1500)
}

function setSection(s) {
  activeSection.value = s
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// ── Counts ────────────────────────────────────────────────────────────────
const unreadMessages    = computed(() => messages.value.filter(m => !m.read).length)
const pendingCollabs    = computed(() => collaborations.value.filter(c => c.direction === 'incoming' && c.status === 'pending').length)
const notificationCount = computed(() => unreadMessages.value + pendingCollabs.value)

// ── Auth ──────────────────────────────────────────────────────────────────
function handleLogout() { logout(); router.push('/') }
function handleQuickAction() {
  if (isAgency.value)   { openPackageForm(null); setSection('packages') }
  if (isProvider.value) { openServiceForm(null); setSection('services') }
}

// ── Booking handlers ──────────────────────────────────────────────────────
function handleConfirmBooking(b) {
  const idx = bookings.value.findIndex(x => x.reservationID === b.reservationID)
  if (idx !== -1) bookings.value[idx].status = 'confirmed'
}
function handleCancelBooking(b) {
  const idx = bookings.value.findIndex(x => x.reservationID === b.reservationID)
  if (idx !== -1) bookings.value[idx].status = 'cancelled'
}
function handleViewBooking(b) { console.log('View booking:', b) }

// ── Package handlers ──────────────────────────────────────────────────────
const packageFormOpen = ref(false)
const editingPackage  = ref(null)

function openPackageForm(pkg) {
  editingPackage.value  = pkg ?? null
  packageFormOpen.value = true
}
function handleSavePackage(payload) {
  const idx = packages.value.findIndex(p => p.packageID === payload.packageID)
  if (idx !== -1) packages.value[idx] = payload
  else packages.value.unshift(payload)
}
function handleDeletePackage(pkg) {
  if (!confirm(`Delete "${pkg.title}"? This cannot be undone.`)) return
  packages.value = packages.value.filter(p => p.packageID !== pkg.packageID)
}

// ── Service handlers ──────────────────────────────────────────────────────
const serviceFormOpen = ref(false)
const editingService  = ref(null)

function openServiceForm(svc) {
  editingService.value  = svc ?? null
  serviceFormOpen.value = true
}
function handleSaveService(payload) {
  const idx = services.value.findIndex(s => s.serviceID === payload.serviceID)
  if (idx !== -1) services.value[idx] = payload
  else services.value.unshift(payload)
}
function handleDeleteService(svc) {
  if (!confirm(`Delete "${svc.title}"? This cannot be undone.`)) return
  services.value = services.value.filter(s => s.serviceID !== svc.serviceID)
}
function handleToggleAvailability(svc) {
  const idx = services.value.findIndex(s => s.serviceID === svc.serviceID)
  if (idx !== -1) services.value[idx].availability = !services.value[idx].availability
}

// ── Message handlers ──────────────────────────────────────────────────────
function handleOpenMessage(msg) {
  const idx = messages.value.findIndex(m => m.messageID === msg.messageID)
  if (idx !== -1) messages.value[idx].read = true
}
function handleCompose() { console.log('Compose — wire to a compose modal later') }

// ── Review handlers ───────────────────────────────────────────────────────
function handleReplyReview(r)  { console.log('Reply:', r) }
function handleDeleteReview(r) {
  if (!confirm('Delete this review?')) return
  reviews.value = reviews.value.filter(x => x.reviewID !== r.reviewID)
}

// ── Offer handlers — delegate to composable ───────────────────────────────
const offerFormOpen = ref(false)
const editingOffer  = ref(null)

function openOfferForm(offer) {
  if (offer?.source === 'collab') return   // collab offers are not editable
  editingOffer.value  = offer ?? null
  offerFormOpen.value = true
}
// @save on OfferFormModal goes straight to saveOffer (wired in template above)

// ── Collaboration handlers ────────────────────────────────────────────────
const collabFormOpen = ref(false)

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

function handleAcceptCollab(collab) {
  const idx = collaborations.value.findIndex(c => c.collabID === collab.collabID)
  if (idx !== -1) collaborations.value[idx].status = 'accepted'

  const outIdx = collaborations.value.findIndex(
    c => c.direction === 'outgoing' && c.title === collab.title && c.status === 'pending'
  )
  if (outIdx !== -1) collaborations.value[outIdx].status = 'accepted'

  // Create a joint offer via the composable
  
    addOffer({
      offerID:      Date.now(),
      collabID:     collab.collabID,
      source:       'collab',
      title:        collab.title,
      discount:     collab.discount,
      type:         collab.type || 'Bundle',
      startDate:    collab.startDate || '',
      endDate:      collab.endDate   || '',
      description:  collab.description,
      partnerName:  collab.initiator?.name || collab.partner?.name,
      partnerColor: collab.partner?.color  || '#2EC4B6',
      active:       true,
    })
  }

function handleDeclineCollab(collab) {
  const idx = collaborations.value.findIndex(c => c.collabID === collab.collabID)
  if (idx !== -1) collaborations.value[idx].status = 'declined'
}

function handleEndCollab(collab) {
  if (!confirm(`End the "${collab.title}" collaboration?`)) return
  collaborations.value = collaborations.value.map(c =>
    c.title === collab.title ? { ...c, status: 'ended' } : c
  )
  // Remove the associated joint offer via composable
  deleteOffer(
    collab.collabID
      ? undefined   // handled by filter below
      : collab.offerID
  )
  // deleteOffer by collabID (collab offers use collabID, not offerID directly)
  const { allOffers } = useOffers()
  const match = allOffers.value.find(o => o.source === 'collab' && o.collabID === collab.collabID)
  if (match) deleteOffer(match.offerID)
}

// ─────────────────────────────────────────────────────────────────────────
// LOCAL DATA  (offers removed — now live in useOffers composable)
// ─────────────────────────────────────────────────────────────────────────
const bookings = ref([
  { reservationID: 1041, guestName: 'Amelia Rhodes', itemName: 'Swiss Alps Winter Retreat', date: 'Jun 12, 2025', totalPrice: 4980, status: 'confirmed' },
  { reservationID: 1040, guestName: 'Carlos Mendez', itemName: 'Greek Island Odyssey',      date: 'Jun 10, 2025', totalPrice: 3100, status: 'pending'   },
  { reservationID: 1039, guestName: 'Yuki Tanaka',   itemName: 'Bali Spiritual Journey',    date: 'Jun 8, 2025',  totalPrice: 1650, status: 'confirmed' },
  { reservationID: 1038, guestName: 'Lena Müller',   itemName: 'Amalfi Coast Drive',        date: 'Jun 6, 2025',  totalPrice: 3780, status: 'confirmed' },
  { reservationID: 1037, guestName: "James O'Brien", itemName: 'Sahara Desert Adventure',   date: 'Jun 3, 2025',  totalPrice: 980,  status: 'cancelled' },
])

const packages = ref([
  { packageID: 1, title: 'Swiss Alps Winter Retreat', destination: 'Switzerland', img: 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=200&q=70', type: 'Adventure', duration: 8,  price: 2490, rating: 4.9, bookings: 48, spots: 4 },
  { packageID: 2, title: 'Bali Spiritual Journey',    destination: 'Indonesia',   img: 'https://images.unsplash.com/photo-1604999565976-8913ad2ddb7c?w=200&q=70', type: 'Cultural',  duration: 10, price: 1650, rating: 4.8, bookings: 36, spots: 8 },
  { packageID: 3, title: 'Greek Island Odyssey',      destination: 'Greece',      img: 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=200&q=70', type: 'Beach',     duration: 14, price: 3100, rating: 4.9, bookings: 22, spots: 2 },
  { packageID: 4, title: 'Amalfi Coast Drive',        destination: 'Italy',       img: 'https://images.unsplash.com/photo-1533606688076-b6683a5f59f1?w=200&q=70', type: 'Beach',     duration: 7,  price: 1890, rating: 4.9, bookings: 31, spots: 5 },
])

const services = ref([
  { serviceID: 101, icon: '🚐', iconBg: 'svc-icon-coral', title: 'Private Airport Transfer', provider: 'Alpine Escapes', type: 'Transport',   price: 45,  unit: 'trip',    rating: 4.9, reviews: 540, bookings: 124, availability: true  },
  { serviceID: 102, icon: '🧗', iconBg: 'svc-icon-teal',  title: 'Mountain Guide',           provider: 'Alpine Escapes', type: 'Adventure',   price: 120, unit: 'day',     rating: 4.8, reviews: 312, bookings: 67,  availability: true  },
  { serviceID: 103, icon: '🍽️', iconBg: 'svc-icon-sand',  title: 'Private Chef Experience',  provider: 'Alpine Escapes', type: 'Food',        price: 180, unit: 'evening', rating: 5.0, reviews: 178, bookings: 45,  availability: false },
  { serviceID: 104, icon: '📸', iconBg: 'svc-icon-teal',  title: 'Travel Photography',       provider: 'Alpine Escapes', type: 'Photography', price: 150, unit: 'day',     rating: 4.7, reviews: 98,  bookings: 38,  availability: true  },
])

const messages = ref([
  { messageID: 1, from: 'Amelia Rhodes', title: 'Question about Alpine package', content: 'Hi, I had a question about the ski equipment rental included…',  date: 'Today, 10:24', read: false },
  { messageID: 2, from: 'Carlos Mendez', title: 'Booking confirmation request',  content: 'Could you please confirm my reservation for June 10th?',         date: 'Today, 09:12', read: false },
  { messageID: 3, from: 'Yuki Tanaka',   title: 'Special dietary requirements',  content: 'I wanted to let you know that I have a vegetarian diet…',         date: 'Yesterday',    read: true  },
  { messageID: 4, from: 'Lena Müller',   title: 'Airport pickup details',        content: 'Can you send me the driver contact details for my arrival?',      date: 'Jun 10',       read: true  },
])

const reviews = ref([
  { reviewID: 1, touristName: 'Amelia Rhodes', itemName: 'Swiss Alps Winter Retreat', rating: 5, comment: 'Absolutely magical experience. The team was professional and attentive throughout.',    date: 'Jun 9, 2025' },
  { reviewID: 2, touristName: 'Yuki Tanaka',   itemName: 'Bali Spiritual Journey',    rating: 5, comment: 'Life-changing trip. The spiritual experiences were authentic.',                         date: 'Jun 5, 2025' },
  { reviewID: 3, touristName: 'Lena Müller',   itemName: 'Amalfi Coast Drive',        rating: 4, comment: 'Beautiful scenery and great organisation. A few minor hiccups but wonderful overall.', date: 'Jun 3, 2025' },
])

const collaborations = ref([
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