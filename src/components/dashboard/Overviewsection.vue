<template>
  <div class="overview">

    <!-- Stats -->
    <StatsGrid :role="role" :bookings="bookings" :items="items" />

    <!-- Two-column: recent bookings + recent messages -->
    <div class="overview__cols">

      <!-- Recent bookings snapshot -->
      <div class="dash-card">
        <div class="dash-card__header">
          <div>
            <div class="dash-card__overline">Latest</div>
            <h3 class="dash-card__title">Recent Bookings</h3>
          </div>
          <button class="dash-card__link" @click="$emit('navigate', 'bookings')">View all →</button>
        </div>
        <div class="overview-list">
          <div class="overview-booking" v-for="b in recentBookings" :key="b.reservationID">
            <div class="overview-booking__avatar">{{ b.guestName[0] }}</div>
            <div class="overview-booking__info">
              <div class="overview-booking__name">{{ b.guestName }}</div>
              <div class="overview-booking__item">{{ b.itemName }}</div>
            </div>
            <div class="overview-booking__right">
              <span class="status-badge" :class="`status--${b.status}`">{{ b.status }}</span>
              <div class="overview-booking__amount">${{ b.totalPrice.toLocaleString() }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent messages snapshot -->
      <div class="dash-card">
        <div class="dash-card__header">
          <div>
            <div class="dash-card__overline">Inbox</div>
            <h3 class="dash-card__title">
              Messages
              <span class="unread-badge" v-if="unreadCount">{{ unreadCount }}</span>
            </h3>
          </div>
          <button class="dash-card__link" @click="$emit('navigate', 'messages')">View all →</button>
        </div>
        <div class="overview-list">
          <div
            class="overview-msg"
            :class="{ unread: !msg.read }"
            v-for="msg in recentMessages"
            :key="msg.messageID"
            @click="$emit('open-message', msg)"
          >
            <div class="overview-msg__avatar">{{ msg.from[0] }}</div>
            <div class="overview-msg__body">
              <div class="overview-msg__from">{{ msg.from }}</div>
              <div class="overview-msg__preview">{{ msg.title }}</div>
            </div>
            <div class="overview-msg__meta">
              <div class="overview-msg__date">{{ msg.date }}</div>
              <div class="overview-msg__dot" v-if="!msg.read" />
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Quick links to sections -->
    <div class="overview__quick">
      <div class="overview__quick-label">Quick actions</div>
      <div class="overview__quick-grid">
        <button class="quick-btn" @click="$emit('navigate', 'bookings')">
          <span class="quick-btn__icon">📋</span>
          <span>Manage Bookings</span>
        </button>
        <button class="quick-btn" v-if="isAgency" @click="$emit('navigate', 'packages')">
          <span class="quick-btn__icon">✈️</span>
          <span>My Packages</span>
        </button>
        <button class="quick-btn" v-if="isProvider" @click="$emit('navigate', 'services')">
          <span class="quick-btn__icon">🛎️</span>
          <span>My Services</span>
        </button>
        <button class="quick-btn" @click="$emit('navigate', 'messages')">
          <span class="quick-btn__icon">💬</span>
          <span>Messages</span>
        </button>
        <button class="quick-btn" @click="$emit('navigate', 'reviews')">
          <span class="quick-btn__icon">⭐</span>
          <span>Reviews</span>
        </button>
        <button class="quick-btn" v-if="isAgency" @click="$emit('navigate', 'offers')">
          <span class="quick-btn__icon">🏷️</span>
          <span>Special Offers</span>
        </button>
        <button class="quick-btn" v-if="isAgency || isProvider" @click="$emit('navigate', 'collaborations')">
          <span class="quick-btn__icon">🤝</span>
          <span>Collaborations</span>
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import StatsGrid from './StatsGrid.vue'

const props = defineProps({
  role:     { type: String, required: true },
  bookings: { type: Array,  default: () => [] },
  messages: { type: Array,  default: () => [] },
  items:    { type: Array,  default: () => [] },
})

defineEmits(['navigate', 'open-message'])

const isAgency   = computed(() => props.role === 'agency')
const isProvider = computed(() => props.role === 'provider')

const recentBookings = computed(() => props.bookings.slice(0, 4))
const recentMessages = computed(() => props.messages.slice(0, 4))
const unreadCount    = computed(() => props.messages.filter(m => !m.read).length)
</script>

<style scoped>
.overview { }

/* Two-col layout */
.overview__cols {
  display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;
}

/* Shared card */
.dash-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
}
.dash-card__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 20px 20px 0; gap: 12px;
}
.dash-card__overline { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--teal); margin-bottom: 3px; }
.dash-card__title {
  font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700;
  display: flex; align-items: center; gap: 8px;
}
.dash-card__link {
  font-size: .82rem; font-weight: 600; color: var(--coral);
  background: none; border: none; cursor: pointer; white-space: nowrap;
  transition: opacity var(--transition); padding: 0;
}
.dash-card__link:hover { opacity: .7; }

/* Overview list */
.overview-list { padding: 12px 0 8px; }

/* Booking rows */
.overview-booking {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 20px; transition: background var(--transition);
}
.overview-booking:hover { background: var(--gray-50); }
.overview-booking__avatar {
  width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
  background: var(--sand); color: var(--indigo);
  font-family: 'Fraunces', serif; font-size: .88rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.overview-booking__info { flex: 1; min-width: 0; }
.overview-booking__name { font-size: .86rem; font-weight: 600; color: var(--indigo); }
.overview-booking__item { font-size: .76rem; color: var(--gray-400); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.overview-booking__right { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
.overview-booking__amount { font-family: 'Fraunces', serif; font-size: .9rem; font-weight: 700; color: var(--coral); }

.status-badge {
  display: inline-flex; align-items: center;
  padding: 2px 8px; border-radius: 50px;
  font-size: .68rem; font-weight: 700; text-transform: capitalize;
}
.status--confirmed { background: rgba(39,174,96,.12);  color: #27ae60; }
.status--pending   { background: rgba(243,156,18,.12); color: #d68910; }
.status--cancelled { background: rgba(231,76,60,.12);  color: #e74c3c; }

/* Message rows */
.overview-msg {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 20px; cursor: pointer; transition: background var(--transition);
}
.overview-msg:hover { background: var(--gray-50); }
.overview-msg.unread { background: rgba(46,196,182,.04); }
.overview-msg__avatar {
  width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--teal), var(--indigo));
  color: #fff; font-family: 'Fraunces', serif; font-size: .88rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.overview-msg__body  { flex: 1; min-width: 0; }
.overview-msg__from  { font-size: .86rem; font-weight: 600; color: var(--indigo); }
.overview-msg__preview { font-size: .76rem; color: var(--gray-400); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.overview-msg__meta  { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
.overview-msg__date  { font-size: .72rem; color: var(--gray-400); }
.overview-msg__dot   { width: 8px; height: 8px; border-radius: 50%; background: var(--teal); }

.unread-badge {
  background: var(--coral); color: #fff;
  font-size: .66rem; font-weight: 700; padding: 1px 7px;
  border-radius: 50px; font-family: 'DM Sans', sans-serif;
}

/* Quick actions */
.overview__quick { }
.overview__quick-label {
  font-size: .78rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .08em; color: var(--gray-400); margin-bottom: 14px;
}
.overview__quick-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px;
}
.quick-btn {
  display: flex; align-items: center; gap: 10px;
  padding: 14px 18px; border-radius: 12px; border: 1.5px solid var(--gray-200);
  background: var(--white); font-family: 'DM Sans', sans-serif;
  font-size: .88rem; font-weight: 600; color: var(--indigo);
  cursor: pointer; transition: all var(--transition); text-align: left;
  box-shadow: var(--shadow);
}
.quick-btn:hover { border-color: var(--coral); color: var(--coral); transform: translateY(-2px); box-shadow: var(--shadow-md); }
.quick-btn__icon { font-size: 1.2rem; flex-shrink: 0; }

@media (max-width: 900px) {
  .overview__cols { grid-template-columns: 1fr; }
}
</style>