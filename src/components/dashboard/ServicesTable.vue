<template>
  <div class="dash-card" id="services">
    <div class="dash-card__header">
      <div>
        <div class="dash-card__overline">Provider</div>
        <h2 class="dash-card__title">My Services</h2>
      </div>
      <button class="btn btn-coral dash-card__add" @click="$emit('add')">+ Add Service</button>
    </div>

    <div class="table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Service</th>
            <th>Type</th>
            <th>Price / day</th>
            <th>Rating</th>
            <th>Bookings</th>
            <th>Availability</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="svc in services" :key="svc.id">
            <td>
              <div class="svc-cell">
                <div class="svc-icon">{{ svc.icon }}</div>
                <div>
                  <div class="svc-title">{{ svc.title }}</div>
                  <div class="svc-sub">{{ svc.provider }}</div>
                </div>
              </div>
            </td>
            <td><span class="type-badge">{{ svc.type }}</span></td>
            <td class="svc-price">${{ svc.price }}</td>
            <td>
              <div class="svc-rating"><span class="star">★</span> {{ svc.rating ? Number(svc.rating).toFixed(1) : '0.0' }}</div>
            </td>
             <td class="svc-bookings">{{ svc.booking_count || 0 }}</td>
  <td>
    <button
      class="avail-toggle"
      :class="{ available: Number(svc.is_available) === 1 }"   <!-- is_available not availability -->
      @click="$emit('toggle-availability', svc)"
    >
      {{ Number(svc.is_available) === 1 ? '✓ Available' : '✕ Unavailable' }}
    </button>
  </td>
            <td>
              <div class="table-actions">
                <button class="tbl-btn tbl-btn--edit"   @click="$emit('edit', svc)"   title="Edit">✏️</button>
                <button class="tbl-btn tbl-btn--delete" @click="$emit('delete', svc)" title="Delete">🗑️</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="dash-card__footer">
      <span class="dash-card__count">{{ services.length }} services</span>
      <a href="#" class="see-all">Manage all →</a>
    </div>
  </div>
</template>

<script setup>
defineProps({
  services: { type: Array, default: () => [] },
})
defineEmits(['add', 'edit', 'delete', 'toggle-availability'])
</script>

<style scoped>
.dash-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 28px; overflow: hidden; }
.dash-card__header { display: flex; align-items: flex-start; justify-content: space-between; padding: 24px 24px 0; gap: 16px; flex-wrap: wrap; }
.dash-card__overline { font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--teal); margin-bottom: 4px; }
.dash-card__title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; }
.dash-card__add { padding: 9px 20px; font-size: .86rem; }
.dash-card__footer { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-top: 1px solid var(--gray-100); }
.dash-card__count { font-size: .82rem; color: var(--gray-400); }

.table-wrap { overflow-x: auto; margin-top: 20px; }
.dash-table { width: 100%; border-collapse: collapse; font-size: .86rem; }
.dash-table thead tr { border-bottom: 2px solid var(--gray-100); }
.dash-table th { padding: 10px 20px; text-align: left; font-size: .72rem; font-weight: 700; color: var(--gray-400); text-transform: uppercase; letter-spacing: .06em; white-space: nowrap; }
.dash-table td { padding: 14px 20px; border-bottom: 1px solid var(--gray-100); vertical-align: middle; }
.dash-table tbody tr:last-child td { border-bottom: none; }
.dash-table tbody tr:hover td { background: var(--gray-50); }

.svc-cell  { display: flex; align-items: center; gap: 12px; }
.svc-icon  { width: 38px; height: 38px; border-radius: 10px; background: var(--sand); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.svc-title { font-weight: 600; color: var(--indigo); font-size: .88rem; }
.svc-sub   { font-size: .76rem; color: var(--gray-400); margin-top: 2px; }

.type-badge  { background: var(--teal-lt); color: var(--teal-dk); font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 50px; white-space: nowrap; }
.svc-price   { font-family: 'Fraunces', serif; font-weight: 700; color: var(--coral); }
.svc-rating  { display: flex; align-items: center; gap: 4px; font-weight: 600; font-size: .86rem; }
.svc-bookings{ font-weight: 600; color: var(--indigo); }

.avail-toggle {
  padding: 5px 12px; border-radius: 50px; border: none; cursor: pointer;
  font-size: .75rem; font-weight: 700; transition: all var(--transition);
  background: rgba(231,76,60,.10); color: #e74c3c;
}
.avail-toggle.available { background: rgba(39,174,96,.10); color: #27ae60; }

.table-actions { display: flex; gap: 6px; }
.tbl-btn { width: 30px; height: 30px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .85rem; transition: all var(--transition); }
.tbl-btn--edit   { background: rgba(46,196,182,.1); color: var(--teal-dk); }
.tbl-btn--edit:hover   { background: var(--teal); color: #fff; }
.tbl-btn--delete { background: rgba(231,76,60,.1); color: #e74c3c; }
.tbl-btn--delete:hover { background: #e74c3c; color: #fff; }
</style>
