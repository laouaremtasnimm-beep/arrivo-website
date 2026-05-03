<template>
  <section class="section" id="packages">
    <div class="section-header">
      <div>
        <div class="section-overline">Curated Experiences</div>
        <h2 class="section-title">Travel <em>packages</em></h2>
      </div>
      <RouterLink to="/packages" class="see-all">
        See all packages →
      </RouterLink>
    </div>

    <!-- Filter tabs -->
    <div class="pkg-tabs">
      <button
        class="pkg-tab"
        :class="{ active: activeTab === t }"
        v-for="t in tabs"
        :key="t"
        @click="activeTab = t"
      >
        {{ t }}
      </button>
    </div>

    <!-- Packages grid -->
    <div class="pkg-grid">
      <article
        class="pkg-card"
        v-for="pkg in filtered"
        :key="pkg.id"
        @click="$emit('select', pkg)"
        style="cursor: pointer"
      >
        <div class="pkg-img">
          <img :src="pkg.img || 'https://i.pinimg.com/1200x/4a/40/9b/4a409b63671d654294bd457c1d1ae220.jpg'" :alt="pkg.title" loading="lazy" />
          <span class="pkg-tag">{{ pkg.type }}</span>
        </div>

        <div class="pkg-body">
          <div class="pkg-agency">{{ pkg.agency }}</div>
          <h3 class="pkg-title">{{ pkg.title }}</h3>

          <div class="pkg-meta">
            <span class="pkg-meta-item">📅 {{ pkg.duration }} days</span>
            <span class="pkg-meta-item">⭐ {{ pkg.rating }}</span>
            <span
              class="pkg-meta-item pkg-spots"
              :class="{ scarce: pkg.spots <= 3 }"
            >
              {{ pkg.spots <= 3 ? '🔥' : '👥' }} {{ pkg.spots }} spots left
            </span>
          </div>

          <p class="pkg-desc">{{ pkg.desc }}</p>

          <div class="pkg-footer">
            <div>
              <div class="pkg-price-label">per person</div>
              <div class="pkg-price">
                ${{ pkg.price.toLocaleString() }}
              </div>
            </div>

            <!-- IMPORTANT: .stop prevents card click -->
            <button
              class="btn card-cta"
              :class="isOwner(pkg) ? 'btn-manage' : (isBooked('package', pkg.id) ? 'btn-outline-danger' : 'btn-coral')"
              @click.stop="isOwner(pkg) ? router.push('/dashboard') : $emit(isBooked('package', pkg.id) ? 'cancel' : 'book', pkg)"
            >
              {{ isOwner(pkg) ? 'Manage package' : (isBooked('package', pkg.id) ? 'Cancel Booking' : 'Book now') }}
            </button>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useBookings } from '@/composables/useBookings'
import { useAuth } from '@/composables/useAuth'
import { useRouter } from 'vue-router'

const props = defineProps({
  packages: { type: Array, default: () => [] },
})

const { isBooked } = useBookings()
const { user } = useAuth()
const router = useRouter()

function isOwner(pkg) {
  if (!user.value || !pkg) return false
  const uid = String(user.value.userID || user.value.id)
  const oid = String(pkg.agency_id || pkg.userId || pkg.owner_id || pkg.item_owner_id || '')
  return oid !== '' && oid === uid
}

defineEmits(['book', 'cancel', 'select'])

const tabs = ['All', 'Adventure', 'Beach', 'Cultural', 'Family']
const activeTab = ref('All')

const filtered = computed(() =>
  activeTab.value === 'All'
    ? props.packages
    : props.packages.filter(p => p.type === activeTab.value)
)
</script>

<style scoped>
/* Tabs */
.pkg-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 40px;
  flex-wrap: wrap;
}
.pkg-tab {
  padding: 9px 22px;
  border-radius: 50px;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  border: 1.5px solid var(--gray-200);
  background: var(--white);
  color: var(--gray-600);
  transition: all var(--transition);
}
.pkg-tab.active {
  background: var(--coral);
  color: #fff;
  border-color: var(--coral);
}
.pkg-tab:hover:not(.active) {
  border-color: var(--indigo);
  color: var(--indigo);
}

/* Grid */
.pkg-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 28px;
}

/* Card */
.pkg-card {
  background: var(--white);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
}
.pkg-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}

.pkg-img {
  height: 220px;
  position: relative;
  overflow: hidden;
}
.pkg-img img {
  height: 100%;
  transition: transform 0.5s ease;
}
.pkg-card:hover .pkg-img img {
  transform: scale(1.05);
}

.pkg-tag {
  position: absolute;
  top: 14px;
  right: 14px;
  background: var(--teal);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  padding: 5px 12px;
  border-radius: 50px;
}

.pkg-body {
  padding: 24px;
}
.pkg-agency {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--teal);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}
.pkg-title {
  font-family: 'Fraunces', serif;
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 12px;
}

.pkg-meta {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 14px;
}
.pkg-meta-item {
  font-size: 0.82rem;
  color: var(--gray-600);
}
.pkg-spots.scarce {
  color: var(--coral);
  font-weight: 600;
}

.pkg-desc {
  font-size: 0.85rem;
  color: var(--gray-600);
  line-height: 1.65;
}

.pkg-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 18px;
  margin-top: 16px;
  border-top: 1px solid var(--gray-100);
}

.pkg-price-label {
  font-size: 0.75rem;
  color: var(--gray-400);
}
.pkg-price {
  font-family: 'Fraunces', serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--coral);
}


</style>