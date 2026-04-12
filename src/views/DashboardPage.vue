<template>
  <div class="dashboard">

    <!-- ── Sidebar ────────────────────────────── -->
    <DashboardSidebar
      :user="user"
      :active-section="activeSection"
      :collapsed="sidebarCollapsed"
      :unread-messages="unreadMessages"
      @toggle="sidebarCollapsed = !sidebarCollapsed"
      @navigate="activeSection = $event"
      @logout="handleLogout"
    />

    <!-- Mobile sidebar overlay -->
    <Transition name="backdrop-fade">
      <div class="mobile-backdrop" v-if="mobileSidebarOpen" @click="mobileSidebarOpen = false" />
    </Transition>

    <!-- Mobile sidebar (full) -->
    <div class="mobile-sidebar" :class="{ open: mobileSidebarOpen }">
      <DashboardSidebar
        :user="user"
        :active-section="activeSection"
        :collapsed="false"
        :unread-messages="unreadMessages"
        @toggle="mobileSidebarOpen = false"
        @navigate="activeSection = $event; mobileSidebarOpen = false"
        @logout="handleLogout"
      />
    </div>

    <!-- ── Main area ──────────────────────────── -->
    <div class="dashboard__main">

      <DashboardHeader
        :user="user"
        :notification-count="notificationCount"
        @open-mobile-sidebar="mobileSidebarOpen = true"
        @quick-action="handleQuickAction"
      />

      <div class="dashboard__content">

        <!-- ── Stats ─────────────────────────── -->
        <StatsGrid :role="user.role" />

        <!-- ── Bookings (all roles) ───────────── -->
        <BookingsTable
          :bookings="bookings"
          :role="user.role"
          @confirm="handleConfirmBooking"
          @cancel="handleCancelBooking"
          @view="handleViewBooking"
        />

        <!-- ── Travel Packages (agency only) ─── -->
        <PackagesTable
          v-if="isAgency"
          :packages="packages"
          @add="handleAddPackage"
          @edit="handleEditPackage"
          @delete="handleDeletePackage"
        />

        <!-- ── Services (provider only) ──────── -->
        <ServicesTable
          v-if="isProvider"
          :services="services"
          @add="handleAddService"
          @edit="handleEditService"
          @delete="handleDeleteService"
          @toggle-availability="handleToggleAvailability"
        />

        <!-- ── Two-column: Messages + Reviews ── -->
        <div class="dashboard__two-col">
          <MessagesPanel
            :messages="messages"
            @open="handleOpenMessage"
            @compose="handleCompose"
          />
          <ReviewsPanel
            :reviews="reviews"
            @reply="handleReplyReview"
            @delete="handleDeleteReview"
          />
        </div>

        <!-- ── Special Offers (agency only) ──── -->
        <OffersPanel
          v-if="isAgency"
          :offers="specialOffers"
          @add="handleAddOffer"
          @edit="handleEditOffer"
          @delete="handleDeleteOffer"
        />

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

// ── Components ─────────────────────────────────────────────────────────────
import DashboardSidebar from '@/components/dashboard/DashboardSidebar.vue'
import DashboardHeader  from '@/components/dashboard/DashboardHeader.vue'
import StatsGrid        from '@/components/dashboard/StatsGrid.vue'
import BookingsTable    from '@/components/dashboard/BookingsTable.vue'
import PackagesTable    from '@/components/dashboard/PackagesTable.vue'
import ServicesTable    from '@/components/dashboard/ServicesTable.vue'
import MessagesPanel    from '@/components/dashboard/MessagesPanel.vue'
import ReviewsPanel     from '@/components/dashboard/ReviewsPanel.vue'
import OffersPanel      from '@/components/dashboard/OffersPanel.vue'

const router = useRouter()
const { user, isAgency, isProvider, logout } = useAuth()

// ── Layout state ───────────────────────────────────────────────────────────
const sidebarCollapsed  = ref(false)
const mobileSidebarOpen = ref(false)
const activeSection     = ref('overview')

// ── Computed counts ────────────────────────────────────────────────────────
const unreadMessages    = computed(() => messages.value.filter(m => !m.read).length)
const notificationCount = computed(() => unreadMessages.value)

// ── Auth actions ───────────────────────────────────────────────────────────
function handleLogout() {
  logout()
  router.push('/')
}

// ── Quick action ───────────────────────────────────────────────────────────
function handleQuickAction() {
  if (isAgency.value)    handleAddPackage()
  if (isProvider.value)  handleAddService()
}

// ── Booking handlers ───────────────────────────────────────────────────────
function handleConfirmBooking(b) {
  const idx = bookings.value.findIndex(x => x.reservationID === b.reservationID)
  if (idx !== -1) bookings.value[idx].status = 'confirmed'
}
function handleCancelBooking(b) {
  const idx = bookings.value.findIndex(x => x.reservationID === b.reservationID)
  if (idx !== -1) bookings.value[idx].status = 'cancelled'
}
function handleViewBooking(b)  { console.log('View booking:', b) }

// ── Package handlers ───────────────────────────────────────────────────────
function handleAddPackage()    { console.log('Add package')     /* open modal */ }
function handleEditPackage(p)  { console.log('Edit package:', p) }
function handleDeletePackage(p){ packages.value = packages.value.filter(x => x.packageID !== p.packageID) }

// ── Service handlers ───────────────────────────────────────────────────────
function handleAddService()    { console.log('Add service')      /* open modal */ }
function handleEditService(s)  { console.log('Edit service:', s) }
function handleDeleteService(s){ services.value = services.value.filter(x => x.serviceID !== s.serviceID) }
function handleToggleAvailability(s) {
  const idx = services.value.findIndex(x => x.serviceID === s.serviceID)
  if (idx !== -1) services.value[idx].availability = !services.value[idx].availability
}

// ── Message handlers ───────────────────────────────────────────────────────
function handleOpenMessage(m) {
  const idx = messages.value.findIndex(x => x.messageID === m.messageID)
  if (idx !== -1) messages.value[idx].read = true
  console.log('Open message:', m)
}
function handleCompose() { console.log('Compose message') }

// ── Review handlers ────────────────────────────────────────────────────────
function handleReplyReview(r)  { console.log('Reply to review:', r) }
function handleDeleteReview(r) { reviews.value = reviews.value.filter(x => x.reviewID !== r.reviewID) }

// ── Offer handlers ─────────────────────────────────────────────────────────
function handleAddOffer()      { console.log('Add offer')       /* open modal */ }
function handleEditOffer(o)    { console.log('Edit offer:', o) }
function handleDeleteOffer(o)  { specialOffers.value = specialOffers.value.filter(x => x.offerID !== o.offerID) }

// ─────────────────────────────────────────────────────────────────────────
// DATA — replace with Pinia store + API calls in production
// ─────────────────────────────────────────────────────────────────────────
const bookings = ref([
  { reservationID: 1041, guestName: 'Amelia Rhodes',   itemName: 'Swiss Alps Winter Retreat', date: 'Jun 12, 2025', totalPrice: 4980,  status: 'confirmed' },
  { reservationID: 1040, guestName: 'Carlos Mendez',   itemName: 'Greek Island Odyssey',       date: 'Jun 10, 2025', totalPrice: 3100,  status: 'pending'   },
  { reservationID: 1039, guestName: 'Yuki Tanaka',     itemName: 'Bali Spiritual Journey',     date: 'Jun 8, 2025',  totalPrice: 1650,  status: 'confirmed' },
  { reservationID: 1038, guestName: 'Lena Müller',     itemName: 'Amalfi Coast Drive',         date: 'Jun 6, 2025',  totalPrice: 3780,  status: 'confirmed' },
  { reservationID: 1037, guestName: 'James O\'Brien',  itemName: 'Sahara Desert Adventure',    date: 'Jun 3, 2025',  totalPrice: 980,   status: 'cancelled' },
])

const packages = ref([
  { packageID: 1, title: 'Swiss Alps Winter Retreat', destination: 'Switzerland', img: 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=200&q=70', type: 'Adventure', duration: 8,  price: 2490, rating: 4.9, bookings: 48 },
  { packageID: 2, title: 'Bali Spiritual Journey',    destination: 'Indonesia',   img: 'https://images.unsplash.com/photo-1604999565976-8913ad2ddb7c?w=200&q=70', type: 'Cultural',  duration: 10, price: 1650, rating: 4.8, bookings: 36 },
  { packageID: 3, title: 'Greek Island Odyssey',      destination: 'Greece',      img: 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=200&q=70', type: 'Beach',     duration: 14, price: 3100, rating: 4.9, bookings: 22 },
  { packageID: 4, title: 'Amalfi Coast Drive',        destination: 'Italy',       img: 'https://images.unsplash.com/photo-1533606688076-b6683a5f59f1?w=200&q=70', type: 'Beach',     duration: 7,  price: 1890, rating: 4.9, bookings: 31 },
])

const services = ref([
  { serviceID: 101, icon: '🚐', title: 'Private Airport Transfer', provider: 'Alpine Escapes',   type: 'City Break', price: 45,  rating: 4.9, bookings: 124, availability: true  },
  { serviceID: 102, icon: '🧗', title: 'Mountain Guide',           provider: 'Alpine Escapes',   type: 'Adventure',  price: 120, rating: 4.8, bookings: 67,  availability: true  },
  { serviceID: 103, icon: '🍽️', title: 'Private Chef Experience',  provider: 'Alpine Escapes',   type: 'Wellness',   price: 180, rating: 5.0, bookings: 45,  availability: false },
  { serviceID: 104, icon: '📸', title: 'Travel Photography',       provider: 'Alpine Escapes',   type: 'City Break', price: 150, rating: 4.7, bookings: 38,  availability: true  },
])

const messages = ref([
  { messageID: 1, from: 'Amelia Rhodes',  title: 'Question about Alpine package', content: 'Hi, I had a question about the ski equipment rental included in the package…', date: 'Today, 10:24', read: false },
  { messageID: 2, from: 'Carlos Mendez',  title: 'Booking confirmation request',  content: 'Could you please confirm my reservation for June 10th? I have not received…', date: 'Today, 09:12', read: false },
  { messageID: 3, from: 'Yuki Tanaka',    title: 'Special dietary requirements',   content: 'I wanted to let you know that I have a vegetarian diet and would like to know…', date: 'Yesterday',   read: true  },
  { messageID: 4, from: 'Lena Müller',    title: 'Airport pickup details',         content: 'Can you send me the driver contact details and pickup time for my arrival?',     date: 'Jun 10',      read: true  },
])

const reviews = ref([
  { reviewID: 1, touristName: 'Amelia Rhodes', itemName: 'Swiss Alps Winter Retreat', rating: 5, comment: 'Absolutely magical experience. The team was professional and attentive throughout. Would book again without hesitation.', date: 'Jun 9, 2025' },
  { reviewID: 2, touristName: 'Yuki Tanaka',   itemName: 'Bali Spiritual Journey',    rating: 5, comment: 'Life-changing trip. The spiritual experiences were authentic and the local guides were incredibly knowledgeable.', date: 'Jun 5, 2025' },
  { reviewID: 3, touristName: 'Lena Müller',   itemName: 'Amalfi Coast Drive',        rating: 4, comment: 'Beautiful scenery and great organisation. A few minor hiccups with transport timing but overall a wonderful trip.', date: 'Jun 3, 2025' },
])

const specialOffers = ref([
  { offerID: 1, discount: 25, title: 'Early Bird Summer',  startDate: 'Jun 1',  endDate: 'Jun 30', description: 'Book any summer package before June 30 and get 25% off.' },
  { offerID: 2, discount: 15, title: 'Returning Traveller', startDate: 'Jul 1',  endDate: 'Jul 31', description: 'Exclusive discount for customers who have booked with us before.' },
  { offerID: 3, discount: 30, title: 'Last Minute Alps',   startDate: 'Jun 15', endDate: 'Jun 20', description: 'Limited spots available for the Swiss Alps Retreat at a special rate.' },
])
</script>

<style scoped>
.dashboard {
  display: flex; min-height: 100vh;
  background: var(--gray-50);
}

/* Main area */
.dashboard__main {
  flex: 1; display: flex; flex-direction: column;
  min-width: 0; overflow-x: hidden;
}

.dashboard__content {
  padding: 32px; flex: 1;
}

/* Two-column section for messages + reviews */
.dashboard__two-col {
  display: grid; grid-template-columns: 1fr 1fr; gap: 28px;
}

/* Mobile sidebar */
.mobile-sidebar {
  display: none;
  position: fixed; top: 0; left: -280px; bottom: 0;
  width: 260px; z-index: 80;
  transition: left .3s ease;
}
.mobile-sidebar.open { left: 0; }

.mobile-backdrop {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,.45); z-index: 75;
}

.backdrop-fade-enter-active, .backdrop-fade-leave-active { transition: opacity .25s ease; }
.backdrop-fade-enter-from, .backdrop-fade-leave-to { opacity: 0; }

@media (max-width: 1024px) {
  .dashboard__two-col { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  /* Hide desktop sidebar, show mobile one */
  .dashboard > .dashboard__main ~ * { display: none; }
  .dashboard { display: block; }
  .dashboard__main { display: flex; flex-direction: column; }
  .mobile-sidebar   { display: block; }
  .mobile-backdrop  { display: block; }
  .dashboard__content { padding: 16px; }
}
</style>