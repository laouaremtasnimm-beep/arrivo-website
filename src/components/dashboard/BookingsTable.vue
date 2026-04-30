<template>
  <div class="dash-card" id="bookings">
    <div class="dash-card__header">
      <div>
        <div class="dash-card__overline">Reservations</div>
        <h2 class="dash-card__title">Recent Bookings</h2>
      </div>
      <div class="dash-card__actions">
        <select class="dash-select" v-model="statusFilter">
          <option value="all">All statuses</option>
          <option value="confirmed">Confirmed</option>
          <option value="pending">Pending</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>

    <div class="table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>Guest</th>
            <th>{{ role === 'agency' ? 'Package' : 'Service' }}</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in filtered" :key="b.reservationID">
            <td class="booking-id">#{{ b.reservationID }}</td>
            <td>
              <div class="guest-cell">
                <div class="guest-avatar">{{ b.guestName[0] }}</div>
                <span>{{ b.guestName }}</span>
              </div>
            </td>
            <td class="item-name">{{ b.itemName }}</td>
            <td class="booking-date">{{ b.date }}</td>
            <td class="booking-amount">${{ b.totalPrice.toLocaleString() }}</td>
            <td><span class="status-badge" :class="`status--${b.status}`">{{ b.status }}</span></td>
            <td>
              <div class="table-actions">
                <button class="tbl-btn tbl-btn--confirm" v-if="b.status === 'pending'" @click="$emit('confirm', b)" title="Confirm">✓</button>
                <button class="tbl-btn tbl-btn--cancel"  v-if="b.status !== 'cancelled'" @click="$emit('cancel', b)" title="Cancel">✕</button>
                <button class="tbl-btn tbl-btn--view" @click="$emit('view', b)" title="View details">👁</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="dash-card__footer">
      <span class="dash-card__count">{{ filtered.length }} bookings</span>
      <a href="#" class="see-all">View all →</a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  bookings: { type: Array, default: () => [] },
  role:     { type: String, required: true },
})
defineEmits(['confirm', 'cancel', 'view'])

const statusFilter = ref('all')

// In the computed filtered — add a guard so missing fields don't crash the table
const filtered = computed(() => {
  const list = statusFilter.value === 'all'
    ? props.bookings
    : props.bookings.filter(b => b.status === statusFilter.value)
  // Ensure every row has the fields the template accesses
  return list.map(b => ({
    reservationID: b.reservationID ?? b.id ?? '—',
    guestName:     b.guestName     ?? 'Guest',
    itemName:      b.itemName      ?? b.item_title ?? '—',
    date:          b.date          ?? b.check_in   ?? '—',
    totalPrice:    b.totalPrice    ?? 0,
    status:        b.status        ?? 'confirmed',
    ...b,
  }))
})
</script>

<style scoped>
.dash-card {
  background: var(--white); border-radius: var(--radius);
  box-shadow: var(--shadow); margin-bottom: 28px; overflow: hidden;
}
.dash-card__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  padding: 24px 24px 0; gap: 16px; flex-wrap: wrap;
}
.dash-card__overline {
  font-size: .72rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--teal); margin-bottom: 4px;
}
.dash-card__title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; }
.dash-card__actions { display: flex; gap: 10px; align-items: center; }

.dash-select {
  padding: 8px 12px; border: 1.5px solid var(--gray-200); border-radius: 10px;
  font-family: 'DM Sans', sans-serif; font-size: .84rem; color: var(--indigo);
  background: var(--white); outline: none; cursor: pointer;
}
.dash-select:focus { border-color: var(--coral); }

.table-wrap { overflow-x: auto; margin-top: 20px; }

.dash-table { width: 100%; border-collapse: collapse; font-size: .86rem; }
.dash-table thead tr { border-bottom: 2px solid var(--gray-100); }
.dash-table th {
  padding: 10px 20px; text-align: left; font-size: .72rem;
  font-weight: 700; color: var(--gray-400); text-transform: uppercase;
  letter-spacing: .06em; white-space: nowrap;
}
.dash-table td { padding: 14px 20px; border-bottom: 1px solid var(--gray-100); vertical-align: middle; }
.dash-table tbody tr:last-child td { border-bottom: none; }
.dash-table tbody tr:hover td { background: var(--gray-50); }

.booking-id     { font-family: 'Fraunces', serif; font-weight: 700; color: var(--indigo); }
.booking-date   { color: var(--gray-600); }
.booking-amount { font-family: 'Fraunces', serif; font-weight: 700; color: var(--coral); }
.item-name      { color: var(--indigo); font-weight: 500; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.guest-cell { display: flex; align-items: center; gap: 10px; }
.guest-avatar {
  width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
  background: var(--sand); color: var(--indigo);
  font-family: 'Fraunces', serif; font-size: .85rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

/* Status badges */
.status-badge {
  display: inline-flex; align-items: center;
  padding: 4px 12px; border-radius: 50px;
  font-size: .72rem; font-weight: 700; text-transform: capitalize;
}
.status--confirmed { background: rgba(39,174,96,.12);  color: #27ae60; }
.status--pending   { background: rgba(243,156,18,.12); color: #d68910; }
.status--cancelled { background: rgba(231,76,60,.12);  color: #e74c3c; }

/* Action buttons */
.table-actions { display: flex; gap: 6px; }
.tbl-btn {
  width: 30px; height: 30px; border-radius: 8px; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center; font-size: .85rem;
  transition: all var(--transition);
}
.tbl-btn--confirm { background: rgba(39,174,96,.1);  color: #27ae60; }
.tbl-btn--confirm:hover { background: #27ae60; color: #fff; }
.tbl-btn--cancel  { background: rgba(231,76,60,.1);  color: #e74c3c; }
.tbl-btn--cancel:hover  { background: #e74c3c; color: #fff; }
.tbl-btn--view    { background: var(--gray-100); color: var(--gray-600); }
.tbl-btn--view:hover    { background: var(--indigo); color: #fff; }

.dash-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 24px; border-top: 1px solid var(--gray-100);
}
.dash-card__count { font-size: .82rem; color: var(--gray-400); }
</style>