<template>
  <div class="dash-card" id="packages">
    <div class="dash-card__header">
      <div>
        <div class="dash-card__overline">Agency</div>
        <h2 class="dash-card__title">Travel Packages</h2>
      </div>
      <button class="btn btn-coral dash-card__add" @click="$emit('add')">+ Add Package</button>
    </div>

    <div class="table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Package</th>
            <th>Type</th>
            <th>Duration</th>
            <th>Price</th>
            <th>Rating</th>
            <th>Bookings</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="pkg in packages" :key="pkg.packageID">
            <td>
              <div class="pkg-cell">
                <div class="pkg-img">
                  <img :src="pkg.img" :alt="pkg.title" />
                </div>
                <div>
                  <div class="pkg-title">{{ pkg.title }}</div>
                  <div class="pkg-sub">{{ pkg.destination }}</div>
                </div>
              </div>
            </td>
            <td><span class="type-badge">{{ pkg.type }}</span></td>
            <td class="pkg-duration">{{ pkg.duration }} days</td>
            <td class="pkg-price">${{ pkg.price.toLocaleString() }}</td>
            <td>
              <div class="pkg-rating"><span class="star">★</span> {{ pkg.rating }}</div>
            </td>
            <td class="pkg-bookings">{{ pkg.bookings }}</td>
            <td>
              <div class="table-actions">
                <button class="tbl-btn tbl-btn--edit" @click="$emit('edit', pkg)" title="Edit">✏️</button>
                <button class="tbl-btn tbl-btn--delete" @click="$emit('delete', pkg)" title="Delete">🗑️</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="dash-card__footer">
      <span class="dash-card__count">{{ packages.length }} packages</span>
      <a href="#" class="see-all">Manage all →</a>
    </div>
  </div>
</template>

<script setup>
defineProps({
  packages: { type: Array, default: () => [] },
})
defineEmits(['add', 'edit', 'delete'])
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

.pkg-cell { display: flex; align-items: center; gap: 12px; }
.pkg-img { width: 48px; height: 38px; border-radius: 8px; overflow: hidden; flex-shrink: 0; }
.pkg-img img { width: 100%; height: 100%; object-fit: cover; }
.pkg-title { font-weight: 600; color: var(--indigo); font-size: .88rem; white-space: nowrap; }
.pkg-sub   { font-size: .76rem; color: var(--gray-400); margin-top: 2px; }

.type-badge { background: var(--teal-lt); color: var(--teal-dk); font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 50px; white-space: nowrap; }
.pkg-duration { color: var(--gray-600); }
.pkg-price    { font-family: 'Fraunces', serif; font-weight: 700; color: var(--coral); }
.pkg-rating   { display: flex; align-items: center; gap: 4px; font-weight: 600; font-size: .86rem; }
.pkg-bookings { font-weight: 600; color: var(--indigo); }

.table-actions { display: flex; gap: 6px; }
.tbl-btn { width: 30px; height: 30px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .85rem; transition: all var(--transition); }
.tbl-btn--edit   { background: rgba(46,196,182,.1); color: var(--teal-dk); }
.tbl-btn--edit:hover   { background: var(--teal); color: #fff; }
.tbl-btn--delete { background: rgba(231,76,60,.1); color: #e74c3c; }
.tbl-btn--delete:hover { background: #e74c3c; color: #fff; }
</style>
