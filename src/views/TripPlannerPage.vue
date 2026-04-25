<template>
  <div class="planner-page">
    <NavBar />

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <header class="planner-hero">
      <div class="planner-hero__left">
        <div class="planner-hero__eyebrow">🗺️ Trip Planner</div>
        <h1 class="planner-hero__title">
          Build your<br><em>perfect trip</em>
        </h1>
        <p class="planner-hero__sub">
          Plan day by day, track your budget, and export a ready-to-go itinerary.
        </p>
      </div>
      <div class="planner-hero__right">
        <div class="planner-meta-card">
          <div class="meta-row">
            <label class="meta-label">Trip name</label>
            <input class="meta-input meta-input--title" v-model="trip.name" placeholder="My Dream Trip" />
          </div>
          <div class="meta-row">
            <label class="meta-label">Destination</label>
            <input class="meta-input" v-model="trip.destination" placeholder="e.g. Kyoto, Japan" />
          </div>
          <div class="meta-two-col">
            <div class="meta-row">
              <label class="meta-label">From</label>
              <input class="meta-input" type="date" v-model="trip.startDate" />
            </div>
            <div class="meta-row">
              <label class="meta-label">To</label>
              <input class="meta-input" type="date" v-model="trip.endDate" />
            </div>
          </div>
          <div class="meta-row">
            <label class="meta-label">Travelers</label>
            <div class="traveler-counter">
              <button class="counter-btn" @click="trip.travelers = Math.max(1, trip.travelers - 1)">−</button>
              <span class="counter-val">{{ trip.travelers }}</span>
              <button class="counter-btn" @click="trip.travelers++">+</button>
              <span class="counter-unit">{{ trip.travelers === 1 ? 'person' : 'people' }}</span>
            </div>
          </div>
          <div class="meta-row">
            <label class="meta-label">Total budget</label>
            <div class="budget-input-wrap">
              <span class="budget-currency">$</span>
              <input class="meta-input meta-input--budget" type="number" min="0" v-model.number="trip.budget" placeholder="0" />
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- ── Main ──────────────────────────────────────────────────────────── -->
    <div class="planner-body">

      <!-- Left: itinerary -->
      <div class="planner-itinerary">

        <div class="itinerary-toolbar">
          <h2 class="itinerary-heading">Itinerary</h2>
          <div class="itinerary-toolbar__actions">
            <button class="btn-ghost-sm" @click="toggleCollapseAll">
              {{ allCollapsed ? '↕ Expand all' : '↕ Collapse all' }}
            </button>
            <button class="btn btn-coral btn-sm" @click="addDay">+ Add day</button>
          </div>
        </div>

        <!-- FIX 1: TransitionGroup wraps only the day-block divs, nothing else -->
        <TransitionGroup name="day-list" tag="div" class="days-list">
          <div
            v-for="(day, di) in trip.days"
            :key="day.id"
            class="day-block"
          >
            <!-- Day header -->
            <div class="day-header" @click="toggleDay(day.id)">
              <div class="day-header__left">
                <span class="day-num">Day {{ di + 1 }}</span>
                <span class="day-date" v-if="dayDate(di)">{{ dayDate(di) }}</span>
                <input
                  class="day-title-input"
                  v-model="day.title"
                  placeholder="e.g. Arrival & Explore"
                  @click.stop
                />
              </div>
              <div class="day-header__right">
                <span class="day-cost">${{ dayTotal(day).toLocaleString() }}</span>
                <button class="day-move-btn" :disabled="di === 0" @click.stop="moveDay(di, -1)" title="Move up">↑</button>
                <button class="day-move-btn" :disabled="di === trip.days.length - 1" @click.stop="moveDay(di, 1)" title="Move down">↓</button>
                <button class="day-delete-btn" @click.stop="deleteDay(day.id)" title="Remove day">✕</button>
                <span class="day-chevron" :class="{ open: openDays.includes(day.id) }">›</span>
              </div>
            </div>

            <!-- Day body -->
            <Transition name="day-expand">
              <div v-if="openDays.includes(day.id)" class="day-body">

                <!-- FIX 2: TransitionGroup for activities — each child must have :key, no v-if siblings -->
                <TransitionGroup name="activity-list" tag="div" class="activities-list">
                  <div
                    v-for="act in day.activities"
                    :key="act.id"
                    class="activity-row"
                  >
                    <div class="activity-time">
                      <input class="time-input" type="time" v-model="act.time" />
                    </div>

                    <div class="activity-icon-wrap">
                      <select class="activity-icon-select" v-model="act.type">
                        <option v-for="t in activityTypes" :key="t.value" :value="t.value">{{ t.icon }}</option>
                      </select>
                      <span class="activity-icon-display">{{ activityIcon(act.type) }}</span>
                    </div>

                    <div class="activity-main">
                      <input class="activity-title-input" v-model="act.title" placeholder="Activity name" />
                      <input class="activity-note-input" v-model="act.note" placeholder="Add a note…" />
                    </div>

                    <div class="activity-cost-wrap">
                      <span class="cost-prefix">$</span>
                      <input class="activity-cost-input" type="number" min="0" v-model.number="act.cost" placeholder="0" />
                    </div>

                    <button class="activity-del" @click="deleteActivity(day.id, act.id)" title="Remove">✕</button>
                  </div>
                </TransitionGroup>

                <button class="add-activity-btn" @click="addActivity(day.id)">
                  <span class="add-activity-btn__plus">+</span> Add activity
                </button>

                <div class="day-note-row">
                  <textarea class="day-note" v-model="day.note" placeholder="Day notes, tips, reminders…" rows="2" />
                </div>

              </div>
            </Transition>
          </div>
        </TransitionGroup>

        <button class="add-day-btn" @click="addDay">
          <span>+</span> Add another day
        </button>

      </div>

      <!-- Right: summary -->
      <aside class="planner-summary">

        <!-- Budget -->
        <div class="summary-card">
          <div class="summary-card__header">
            <h3 class="summary-card__title">💰 Budget</h3>
            <span class="budget-status" :class="budgetStatus">{{ budgetStatusLabel }}</span>
          </div>
          <div class="budget-bar-wrap">
            <div class="budget-bar">
              <div
                class="budget-bar__fill"
                :class="{ 'budget-bar__fill--over': totalSpent > trip.budget }"
                :style="{ width: budgetPct + '%' }"
              />
            </div>
            <div class="budget-bar-labels">
              <span class="budget-spent">${{ totalSpent.toLocaleString() }} spent</span>
              <span class="budget-remaining" :class="{ negative: totalSpent > trip.budget }">
                {{ totalSpent > trip.budget
                  ? `$${(totalSpent - trip.budget).toLocaleString()} over`
                  : `$${(trip.budget - totalSpent).toLocaleString()} left` }}
              </span>
            </div>
          </div>
          <!-- FIX 3: v-if and v-for on same element → wrap in template -->
          <div class="budget-breakdown">
            <template v-for="t in activityTypes" :key="t.value">
              <div v-if="costByType(t.value) > 0" class="budget-row">
                <span class="budget-row__icon">{{ t.icon }}</span>
                <span class="budget-row__label">{{ t.label }}</span>
                <span class="budget-row__val">${{ costByType(t.value).toLocaleString() }}</span>
              </div>
            </template>
          </div>
        </div>

        <!-- Stats -->
        <div class="summary-card">
          <h3 class="summary-card__title">📊 Overview</h3>
          <div class="stats-grid">
            <div class="stat-cell">
              <span class="stat-val">{{ trip.days.length }}</span>
              <span class="stat-label">Days</span>
            </div>
            <div class="stat-cell">
              <span class="stat-val">{{ totalActivities }}</span>
              <span class="stat-label">Activities</span>
            </div>
            <div class="stat-cell">
              <span class="stat-val">{{ trip.travelers }}</span>
              <span class="stat-label">Travelers</span>
            </div>
            <div class="stat-cell">
              <span class="stat-val">${{ perPersonCost.toLocaleString() }}</span>
              <span class="stat-label">Per person</span>
            </div>
          </div>
        </div>

        <!-- Activity type breakdown -->
        <div class="summary-card">
          <h3 class="summary-card__title">🏷️ Activity types</h3>
          <div class="type-bars">
            <!-- FIX 4: same v-if + v-for fix here too -->
            <template v-for="t in activityTypes" :key="t.value">
              <div v-if="countByType(t.value) > 0" class="type-bar-row">
                <span class="type-bar-label">{{ t.icon }} {{ t.label }}</span>
                <div class="type-bar-track">
                  <div
                    class="type-bar-fill"
                    :style="{ width: (countByType(t.value) / totalActivities * 100) + '%', background: t.color }"
                  />
                </div>
                <span class="type-bar-count">{{ countByType(t.value) }}</span>
              </div>
            </template>
          </div>
        </div>

        <!-- Actions -->
        <div class="summary-actions">
          <button class="btn btn-coral summary-btn" @click="printPlan">🖨️ Print / Export</button>
          <button class="btn btn-outline summary-btn" @click="resetPlan">↺ Start over</button>
        </div>

        <RouterLink :to="{ path: '/search', query: { q: trip.destination || '' } }" class="summary-search-cta">
          <span class="summary-search-cta__icon">✈️</span>
          <span>
            <strong>Find packages for {{ trip.destination || 'your destination' }}</strong>
            <span class="summary-search-cta__sub">Browse matching tours & services →</span>
          </span>
        </RouterLink>

      </aside>
    </div>

    <!-- Print view -->
    <div class="print-view">
      <h1>{{ trip.name || 'My Trip' }}</h1>
      <p class="print-meta">{{ trip.destination }} · {{ trip.startDate }} – {{ trip.endDate }} · {{ trip.travelers }} traveler{{ trip.travelers !== 1 ? 's' : '' }}</p>
      <div v-for="(day, di) in trip.days" :key="day.id" class="print-day">
        <h2>Day {{ di + 1 }}: {{ day.title }}</h2>
        <table class="print-table">
          <tr v-for="act in day.activities" :key="act.id">
            <td class="print-time">{{ act.time || '—' }}</td>
            <td class="print-icon">{{ activityIcon(act.type) }}</td>
            <td class="print-title">{{ act.title || 'Untitled' }}</td>
            <td class="print-note">{{ act.note }}</td>
            <td class="print-cost">${{ act.cost || 0 }}</td>
          </tr>
        </table>
        <p class="print-day-note" v-if="day.note">📝 {{ day.note }}</p>
        <p class="print-day-total">Day total: ${{ dayTotal(day).toLocaleString() }}</p>
      </div>
      <p class="print-total">Total: ${{ totalSpent.toLocaleString() }} · Per person: ${{ perPersonCost.toLocaleString() }}</p>
    </div>

    <SiteFooter />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import NavBar     from '@/components/home/NavBar.vue'
import SiteFooter from '@/components/home/SiteFooter.vue'

// ── Activity types ────────────────────────────────────────────────────────
const activityTypes = [
  { value: 'transport', label: 'Transport',    icon: '🚌', color: '#6366f1' },
  { value: 'hotel',     label: 'Hotel',        icon: '🏨', color: '#0ea5e9' },
  { value: 'food',      label: 'Food & Drink', icon: '🍽️', color: '#f59e0b' },
  { value: 'sight',     label: 'Sightseeing',  icon: '🏛️', color: '#10b981' },
  { value: 'activity',  label: 'Activity',     icon: '🎯', color: '#FF5A5F' },
  { value: 'shopping',  label: 'Shopping',     icon: '🛍️', color: '#ec4899' },
  { value: 'nature',    label: 'Nature',       icon: '🌿', color: '#22c55e' },
  { value: 'other',     label: 'Other',        icon: '📌', color: '#94a3b8' },
]

function activityIcon(type) {
  return activityTypes.find(t => t.value === type)?.icon ?? '📌'
}

// ── ID helpers ────────────────────────────────────────────────────────────
let _dayId = 10
let _actId = 100
const uid = () => ++_dayId
const aid = () => ++_actId

function makeActivity(overrides = {}) {
  return { id: aid(), type: 'sight', time: '', title: '', note: '', cost: 0, ...overrides }
}
function makeDay(overrides = {}) {
  return { id: uid(), title: '', note: '', activities: [makeActivity()], ...overrides }
}

// ── Trip state ────────────────────────────────────────────────────────────
const trip = ref({
  name: '', destination: '', startDate: '', endDate: '',
  travelers: 2, budget: 3000,
  days: [
    makeDay({ title: 'Arrival',   activities: [makeActivity({ type: 'transport', title: 'Airport transfer', cost: 45 }), makeActivity({ type: 'hotel', title: 'Check in', cost: 180 })] }),
    makeDay({ title: 'Explore',   activities: [makeActivity({ type: 'food', title: 'Breakfast', cost: 20 }), makeActivity({ type: 'sight', title: 'Old Town tour', cost: 35 }), makeActivity({ type: 'food', title: 'Dinner', cost: 60 })] }),
    makeDay({ title: 'Day trip',  activities: [makeActivity({ type: 'transport', title: 'Rental car', cost: 80 }), makeActivity({ type: 'nature', title: 'Coastal hike', cost: 0 })] }),
    makeDay({ title: 'Departure', activities: [makeActivity({ type: 'food', title: 'Last breakfast', cost: 25 }), makeActivity({ type: 'transport', title: 'Return transfer', cost: 45 })] }),
  ],
})

// ── Open / collapse ───────────────────────────────────────────────────────
const openDays    = ref([trip.value.days[0].id])
const allCollapsed = ref(false)

function toggleCollapseAll() {
  allCollapsed.value = !allCollapsed.value
  if (allCollapsed.value) {
    openDays.value = []
  } else {
    openDays.value = trip.value.days.map(d => d.id)
  }
}

function toggleDay(id) {
  const idx = openDays.value.indexOf(id)
  idx === -1 ? openDays.value.push(id) : openDays.value.splice(idx, 1)
}

// ── Day operations ────────────────────────────────────────────────────────
function dayDate(di) {
  if (!trip.value.startDate) return ''
  const d = new Date(trip.value.startDate)
  d.setDate(d.getDate() + di)
  return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })
}

function addDay() {
  const day = makeDay()
  trip.value.days.push(day)
  openDays.value.push(day.id)
}

function deleteDay(id) {
  if (trip.value.days.length === 1) return
  trip.value.days = trip.value.days.filter(d => d.id !== id)
  openDays.value  = openDays.value.filter(i => i !== id)
}

function moveDay(idx, dir) {
  const newIdx = idx + dir
  if (newIdx < 0 || newIdx >= trip.value.days.length) return
  const copy = [...trip.value.days]
  ;[copy[idx], copy[newIdx]] = [copy[newIdx], copy[idx]]
  trip.value.days = copy
}

function dayTotal(day) {
  return day.activities.reduce((s, a) => s + (Number(a.cost) || 0), 0)
}

// ── Activity operations ───────────────────────────────────────────────────
function addActivity(dayId) {
  const day = trip.value.days.find(d => d.id === dayId)
  if (day) day.activities.push(makeActivity())
}

function deleteActivity(dayId, actId) {
  const day = trip.value.days.find(d => d.id === dayId)
  if (day) day.activities = day.activities.filter(a => a.id !== actId)
}

// ── Computed ──────────────────────────────────────────────────────────────
const totalSpent = computed(() =>
  trip.value.days.reduce((s, d) => s + dayTotal(d), 0)
)
const totalActivities = computed(() =>
  trip.value.days.reduce((s, d) => s + d.activities.length, 0)
)
const perPersonCost = computed(() =>
  trip.value.travelers > 0
    ? Math.round(totalSpent.value / trip.value.travelers)
    : totalSpent.value
)
const budgetPct = computed(() =>
  trip.value.budget > 0
    ? Math.min(100, Math.round(totalSpent.value / trip.value.budget * 100))
    : 0
)
const budgetStatus = computed(() => {
  if (!trip.value.budget) return 'status--none'
  const pct = totalSpent.value / trip.value.budget
  if (pct >= 1)    return 'status--over'
  if (pct >= 0.85) return 'status--warn'
  return 'status--ok'
})
const budgetStatusLabel = computed(() => ({
  'status--over': 'Over budget',
  'status--warn': 'Almost full',
  'status--ok':   'On track',
  'status--none': 'No budget set',
}[budgetStatus.value]))

function costByType(type) {
  return trip.value.days.reduce((s, d) =>
    s + d.activities
      .filter(a => a.type === type)
      .reduce((ss, a) => ss + (Number(a.cost) || 0), 0), 0)
}
function countByType(type) {
  return trip.value.days.reduce((s, d) =>
    s + d.activities.filter(a => a.type === type).length, 0)
}

// ── Actions ───────────────────────────────────────────────────────────────
function printPlan() { window.print() }

function resetPlan() {
  if (!confirm('Start over? This will clear your current plan.')) return
  trip.value = {
    name: '', destination: '', startDate: '', endDate: '',
    travelers: 2, budget: 3000,
    days: [makeDay()],
  }
  openDays.value = [trip.value.days[0].id]
}
</script>

<style scoped>
.planner-page { min-height: 100vh; background: var(--sand); padding-top: 68px; }

/* ── Hero ────────────────────────────────────────────────────────────────── */
.planner-hero {
  display: grid; grid-template-columns: 1fr 1fr; gap: 48px;
  align-items: center; padding: 52px 5% 60px;
  background: var(--indigo); position: relative; overflow: hidden;
}
.planner-hero::after {
  content: ''; position: absolute; bottom: -1px; left: 0; right: 0;
  height: 40px; background: var(--sand);
  clip-path: ellipse(55% 100% at 50% 100%);
}

.planner-hero__eyebrow {
  font-size: .78rem; font-weight: 700; letter-spacing: .1em;
  text-transform: uppercase; color: var(--coral); margin-bottom: 14px;
}
.planner-hero__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 700; color: #fff; line-height: 1.1; margin-bottom: 14px;
}
.planner-hero__title em { color: var(--coral); font-style: italic; }
.planner-hero__sub { font-size: .95rem; color: rgba(255,255,255,.6); line-height: 1.65; }

.planner-meta-card {
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
  border-radius: var(--radius); padding: 24px 28px;
  display: flex; flex-direction: column; gap: 16px;
  backdrop-filter: blur(10px);
}
.meta-row   { display: flex; flex-direction: column; gap: 5px; }
.meta-label {
  font-size: .72rem; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: rgba(255,255,255,.5);
}
.meta-input {
  background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
  border-radius: var(--radius-sm); padding: 9px 13px;
  font-family: 'DM Sans', sans-serif; font-size: .92rem; color: #fff;
  outline: none; transition: border-color var(--transition); width: 100%;
}
.meta-input::placeholder { color: rgba(255,255,255,.35); }
.meta-input:focus { border-color: var(--coral); }
.meta-input--title { font-weight: 600; font-size: 1rem; }
.meta-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.traveler-counter { display: flex; align-items: center; gap: 10px; }
.counter-btn {
  width: 30px; height: 30px; border-radius: 50%;
  border: 1px solid rgba(255,255,255,.25); background: rgba(255,255,255,.1);
  color: #fff; font-size: 1.1rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.counter-btn:hover { background: var(--coral); border-color: var(--coral); }
.counter-val  { font-size: 1.1rem; font-weight: 700; color: #fff; min-width: 20px; text-align: center; }
.counter-unit { font-size: .84rem; color: rgba(255,255,255,.5); }

.budget-input-wrap { position: relative; }
.budget-currency {
  position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
  font-size: .9rem; font-weight: 600; color: rgba(255,255,255,.4);
}
.meta-input--budget { padding-left: 26px; }

/* ── Body ────────────────────────────────────────────────────────────────── */
.planner-body {
  display: grid; grid-template-columns: 1fr 340px; gap: 28px;
  padding: 36px 5% 64px; align-items: flex-start;
}

/* ── Itinerary ───────────────────────────────────────────────────────────── */
.planner-itinerary { display: flex; flex-direction: column; }

.itinerary-toolbar {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 20px;
}
.itinerary-heading {
  font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--indigo);
}
.itinerary-toolbar__actions { display: flex; gap: 10px; align-items: center; }

.btn-ghost-sm {
  background: none; border: 1.5px solid var(--gray-200); border-radius: 8px; padding: 7px 13px;
  font-family: 'DM Sans', sans-serif; font-size: .8rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
}
.btn-ghost-sm:hover { border-color: var(--indigo); color: var(--indigo); }
.btn-sm { padding: 8px 18px !important; font-size: .84rem !important; }

.day-list-enter-active, .day-list-leave-active { transition: all .28s ease; }
.day-list-enter-from  { opacity: 0; transform: translateY(12px); }
.day-list-leave-to    { opacity: 0; transform: translateX(-10px); }

.days-list { display: flex; flex-direction: column; }

.day-block {
  background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow);
  margin-bottom: 16px; overflow: hidden; border-left: 4px solid var(--coral);
  transition: box-shadow var(--transition);
}
.day-block:hover { box-shadow: var(--shadow-md); }

.day-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px; cursor: pointer; user-select: none; gap: 12px;
}
.day-header__left  { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
.day-header__right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

.day-num {
  font-family: 'Fraunces', serif; font-size: .95rem; font-weight: 700;
  color: var(--coral); white-space: nowrap; flex-shrink: 0;
}
.day-date { font-size: .75rem; color: var(--gray-400); white-space: nowrap; flex-shrink: 0; }
.day-title-input {
  border: none; outline: none; background: transparent;
  font-family: 'DM Sans', sans-serif; font-size: .92rem; font-weight: 600;
  color: var(--indigo); min-width: 0; flex: 1;
}
.day-title-input::placeholder { color: var(--gray-400); font-weight: 400; }
.day-cost {
  font-size: .82rem; font-weight: 700; color: var(--indigo);
  background: var(--gray-100); padding: 3px 10px; border-radius: 50px;
}
.day-move-btn {
  width: 26px; height: 26px; border-radius: 7px;
  border: 1px solid var(--gray-200); background: none;
  font-size: .9rem; cursor: pointer; color: var(--gray-400);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.day-move-btn:hover:not(:disabled) { background: var(--gray-100); color: var(--indigo); }
.day-move-btn:disabled { opacity: .3; cursor: not-allowed; }
.day-delete-btn {
  width: 26px; height: 26px; border-radius: 7px;
  border: 1px solid var(--gray-200); background: none;
  font-size: .78rem; cursor: pointer; color: var(--gray-400);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.day-delete-btn:hover { background: #fee2e2; border-color: #ef4444; color: #ef4444; }
.day-chevron {
  font-size: 1.2rem; color: var(--gray-400);
  transition: transform .22s ease; display: inline-block;
}
.day-chevron.open { transform: rotate(90deg); }

.day-expand-enter-active, .day-expand-leave-active { transition: all .24s ease; }
.day-expand-enter-from, .day-expand-leave-to { opacity: 0; transform: translateY(-6px); }

.day-body { padding: 0 20px 16px; border-top: 1px solid var(--gray-100); }

.activity-list-enter-active, .activity-list-leave-active { transition: all .2s ease; }
.activity-list-enter-from { opacity: 0; transform: translateX(-8px); }
.activity-list-leave-to   { opacity: 0; transform: translateX(8px); }

.activities-list { display: flex; flex-direction: column; margin-top: 12px; }

.activity-row {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 0; border-bottom: 1px solid var(--gray-100);
}
.activity-row:last-child { border-bottom: none; }

.time-input {
  border: 1px solid var(--gray-200); border-radius: 7px; padding: 5px 8px;
  font-family: 'DM Sans', sans-serif; font-size: .78rem; color: var(--indigo);
  outline: none; width: 82px; flex-shrink: 0; transition: border-color var(--transition);
}
.time-input:focus { border-color: var(--coral); }

.activity-icon-wrap { position: relative; flex-shrink: 0; width: 32px; height: 32px; }
.activity-icon-select {
  opacity: 0; position: absolute; inset: 0; cursor: pointer; width: 100%; height: 100%;
}
.activity-icon-display {
  width: 32px; height: 32px; border-radius: 8px; background: var(--gray-100);
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; cursor: pointer; pointer-events: none;
  transition: background var(--transition);
}
.activity-icon-wrap:hover .activity-icon-display { background: var(--gray-200); }

.activity-main { flex: 1; display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.activity-title-input {
  border: none; outline: none; background: transparent;
  font-family: 'DM Sans', sans-serif; font-size: .88rem; font-weight: 600;
  color: var(--indigo); width: 100%;
}
.activity-title-input::placeholder { color: var(--gray-400); font-weight: 400; }
.activity-note-input {
  border: none; outline: none; background: transparent;
  font-family: 'DM Sans', sans-serif; font-size: .76rem; color: var(--gray-400); width: 100%;
}
.activity-note-input::placeholder { color: var(--gray-200); }

.activity-cost-wrap {
  display: flex; align-items: center; gap: 3px;
  background: var(--gray-50); border: 1px solid var(--gray-200);
  border-radius: 8px; padding: 5px 8px; flex-shrink: 0;
}
.cost-prefix { font-size: .78rem; color: var(--gray-400); font-weight: 600; }
.activity-cost-input {
  width: 52px; border: none; outline: none; background: transparent;
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 600;
  color: var(--indigo); text-align: right;
}

.activity-del {
  width: 24px; height: 24px; border-radius: 6px; border: none; background: none;
  font-size: .72rem; cursor: pointer; color: var(--gray-400); flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.activity-del:hover { background: #fee2e2; color: #ef4444; }

.add-activity-btn {
  display: inline-flex; align-items: center; gap: 7px;
  margin-top: 10px; padding: 7px 14px; border-radius: 8px;
  border: 1.5px dashed var(--gray-200); background: none; cursor: pointer;
  font-family: 'DM Sans', sans-serif; font-size: .82rem; font-weight: 600;
  color: var(--gray-400); transition: all var(--transition);
}
.add-activity-btn:hover { border-color: var(--coral); color: var(--coral); background: var(--coral-lt); }
.add-activity-btn__plus { font-size: 1rem; line-height: 1; }

.day-note-row { margin-top: 10px; }
.day-note {
  width: 100%; border: 1px solid var(--gray-200); border-radius: 8px;
  padding: 9px 12px; font-family: 'DM Sans', sans-serif; font-size: .82rem;
  color: var(--gray-600); resize: none; outline: none; background: var(--gray-50);
  transition: border-color var(--transition);
}
.day-note:focus { border-color: var(--coral); background: var(--white); }

.add-day-btn {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  width: 100%; padding: 14px; border: 2px dashed var(--gray-200);
  border-radius: var(--radius); background: none; cursor: pointer;
  font-family: 'DM Sans', sans-serif; font-size: .9rem; font-weight: 600;
  color: var(--gray-400); transition: all var(--transition); margin-top: 8px;
}
.add-day-btn span { font-size: 1.3rem; line-height: 1; }
.add-day-btn:hover { border-color: var(--coral); color: var(--coral); background: var(--coral-lt); }

/* ── Summary ─────────────────────────────────────────────────────────────── */
.planner-summary { position: sticky; top: 84px; display: flex; flex-direction: column; gap: 16px; }

.summary-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); padding: 20px 22px; }
.summary-card__header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.summary-card__title { font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700; color: var(--indigo); margin: 0 0 14px; }
.summary-card__header .summary-card__title { margin: 0; }

.budget-status { font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 50px; }
.status--ok   { background: rgba(39,174,96,.1);  color: #27ae60; }
.status--warn { background: rgba(245,158,11,.1); color: #d97706; }
.status--over { background: rgba(239,68,68,.1);  color: #ef4444; }
.status--none { background: var(--gray-100);     color: var(--gray-400); }

.budget-bar-wrap { margin-bottom: 12px; }
.budget-bar { height: 8px; border-radius: 4px; background: var(--gray-100); overflow: hidden; margin-bottom: 6px; }
.budget-bar__fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg,#27ae60,#2ec4b6); transition: width .4s ease; }
.budget-bar__fill--over { background: linear-gradient(90deg,#f59e0b,#ef4444); }
.budget-bar-labels { display: flex; justify-content: space-between; font-size: .76rem; }
.budget-spent     { color: var(--gray-600); font-weight: 600; }
.budget-remaining { color: #27ae60; font-weight: 700; }
.budget-remaining.negative { color: #ef4444; }

.budget-breakdown { display: flex; flex-direction: column; gap: 7px; border-top: 1px solid var(--gray-100); padding-top: 12px; }
.budget-row { display: flex; align-items: center; gap: 8px; font-size: .8rem; }
.budget-row__icon  { width: 20px; text-align: center; }
.budget-row__label { flex: 1; color: var(--gray-600); }
.budget-row__val   { font-weight: 700; color: var(--indigo); }

.stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.stat-cell { background: var(--gray-50); border-radius: var(--radius-sm); padding: 12px 14px; display: flex; flex-direction: column; gap: 3px; }
.stat-val   { font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700; color: var(--coral); line-height: 1; }
.stat-label { font-size: .72rem; color: var(--gray-400); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }

.type-bars { display: flex; flex-direction: column; gap: 9px; }
.type-bar-row { display: flex; align-items: center; gap: 8px; }
.type-bar-label { font-size: .78rem; color: var(--gray-600); min-width: 110px; }
.type-bar-track { flex: 1; height: 6px; background: var(--gray-100); border-radius: 3px; overflow: hidden; }
.type-bar-fill  { height: 100%; border-radius: 3px; transition: width .4s ease; }
.type-bar-count { font-size: .76rem; font-weight: 700; color: var(--indigo); min-width: 16px; text-align: right; }

.summary-actions { display: flex; flex-direction: column; gap: 10px; }
.summary-btn { width: 100%; justify-content: center; padding: 11px; }

.summary-search-cta {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px;
  border-radius: var(--radius); background: var(--indigo); text-decoration: none;
  transition: opacity var(--transition);
}
.summary-search-cta:hover { opacity: .88; }
.summary-search-cta__icon { font-size: 1.4rem; flex-shrink: 0; }
.summary-search-cta strong { display: block; font-size: .88rem; color: #fff; font-weight: 700; line-height: 1.2; }
.summary-search-cta__sub   { font-size: .76rem; color: var(--coral); font-weight: 600; }

/* ── Print ───────────────────────────────────────────────────────────────── */
.print-view { display: none; }
@media print {
  .planner-page > *:not(.print-view) { display: none !important; }
  .print-view { display: block; padding: 20px; font-family: 'DM Sans', sans-serif; color: #000; }
  .print-view h1 { font-size: 1.8rem; margin-bottom: 4px; }
  .print-meta    { color: #666; margin-bottom: 20px; font-size: .9rem; }
  .print-day     { margin-bottom: 24px; page-break-inside: avoid; }
  .print-day h2  { font-size: 1.1rem; border-bottom: 2px solid #000; padding-bottom: 4px; margin-bottom: 8px; }
  .print-table   { width: 100%; border-collapse: collapse; font-size: .85rem; }
  .print-table td { padding: 4px 8px; border-bottom: 1px solid #eee; vertical-align: top; }
  .print-time  { color: #888; width: 60px; white-space: nowrap; }
  .print-icon  { width: 24px; }
  .print-title { font-weight: 600; }
  .print-note  { color: #666; font-size: .8rem; }
  .print-cost  { text-align: right; font-weight: 600; width: 60px; }
  .print-day-note  { margin-top: 6px; font-size: .82rem; color: #555; }
  .print-day-total { font-weight: 700; text-align: right; margin-top: 6px; }
  .print-total { font-size: 1.1rem; font-weight: 700; border-top: 2px solid #000; padding-top: 10px; margin-top: 20px; }
}

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1024px) {
  .planner-hero { grid-template-columns: 1fr; gap: 28px; }
  .planner-body { grid-template-columns: 1fr; }
  .planner-summary { position: static; }
}
@media (max-width: 640px) {
  .planner-hero { padding: 40px 4% 52px; }
  .planner-body { padding: 24px 4% 40px; }
  .meta-two-col { grid-template-columns: 1fr; }
  .activity-row { flex-wrap: wrap; }
  .activity-main { order: 4; width: 100%; }
}
</style>