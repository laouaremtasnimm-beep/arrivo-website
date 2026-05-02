<template>
  <div class="stats-grid">
    <div class="stat-card" v-for="stat in stats" :key="stat.label">
      <div class="stat-card__icon" :style="{ background: stat.iconBg }">{{ stat.icon }}</div>
      <div class="stat-card__body">
        <div class="stat-card__label">{{ stat.label }}</div>
        <div class="stat-card__value">{{ stat.value }}</div>
        <div class="stat-card__trend" v-if="stat.trend !== 0" :class="stat.trend > 0 ? 'up' : 'down'">
          {{ stat.trend > 0 ? '↑' : '↓' }} {{ Math.abs(stat.trend) }}% vs last month
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  role:     { type: String, required: true }, // 'agency' | 'provider'
  bookings: { type: Array,  default: () => [] },
  items:    { type: Array,  default: () => [] },
})

const stats = computed(() => {
  const isAgency = props.role === 'agency'
  
  // 1. Total Bookings
  const totalBookings = props.bookings.length
  
  // 2. Revenue (Confirmed bookings only — handle both DB snake_case and normalized camelCase)
  const revenue = props.bookings
    .filter(b => b.status === 'confirmed')
    .reduce((sum, b) => sum + (parseFloat(b.total_price ?? b.totalPrice) || 0), 0)
    
  // 3. Active Items (Packages or Services)
 const activeItemsCount = Array.isArray(props.items) ? props.items.length : 0
  
  // 4. Avg Rating
 const ratings = props.items
  .map(i => parseFloat(i.rating))
  .filter(r => !isNaN(r))

const avgRating = ratings.length
  ? (ratings.reduce((a, b) => a + b, 0) / ratings.length).toFixed(1)
  : '0.0'
  const common = [
    { icon: '📋', iconBg: 'rgba(255,90,95,.10)',   label: 'Total Bookings',  value: totalBookings.toLocaleString(), trend: 0 },
    { icon: '💰', iconBg: 'rgba(46,196,182,.10)',  label: 'Revenue',         value: '$' + revenue.toLocaleString(), trend: 0 },
    { 
      icon: isAgency ? '✈️' : '🛎️', 
      iconBg: 'rgba(45,49,66,.07)',    
      label: isAgency ? 'Active Packages' : 'Active Services', 
      value: activeItemsCount.toLocaleString(),      
      trend: 0 
    },
    { icon: '⭐', iconBg: 'rgba(255,180,0,.10)',   label: 'Avg Rating',      value: avgRating, trend: 0 },
  ]
  
  return common
})
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: var(--white); border-radius: var(--radius);
  padding: 22px; box-shadow: var(--shadow);
  display: flex; align-items: flex-start; gap: 16px;
  transition: transform var(--transition), box-shadow var(--transition);
}
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

.stat-card__icon {
  width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem;
}

.stat-card__label { font-size: .8rem; color: var(--gray-400); font-weight: 500; margin-bottom: 4px; }
.stat-card__value {
  font-family: 'Fraunces', serif;
  font-size: 1.6rem; font-weight: 700; color: var(--indigo); line-height: 1;
}
.stat-card__trend { font-size: .75rem; font-weight: 600; margin-top: 6px; }
.stat-card__trend.up   { color: #27ae60; }
.stat-card__trend.down { color: #e74c3c; }

@media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px)  { .stats-grid { grid-template-columns: 1fr; } }
</style>
