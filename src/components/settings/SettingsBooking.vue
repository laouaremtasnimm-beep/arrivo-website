<template>
  <SettingsSection
    title="Booking Preferences"
    subtitle="Set your default preferences to speed up future bookings"
  >

    <!-- Travel style -->
    <div class="block-label">Travel Style</div>
    <div class="tag-grid">
      <button
        v-for="style in travelStyles"
        :key="style"
        class="tag-btn"
        :class="{ active: booking.travelStyles.includes(style) }"
        @click="toggleStyle(style)"
      >{{ style }}</button>
    </div>

    <div class="divider" />

    <!-- Default guests -->
    <div class="block-label">Default Guests</div>
    <div class="guests-row">
      <div v-for="g in guestTypes" :key="g.key" class="guest-counter">
        <div class="guest-info">
          <span class="guest-label">{{ g.label }}</span>
          <span class="guest-sub">{{ g.sub }}</span>
        </div>
        <div class="counter">
          <button class="counter-btn" @click="decrement(g.key)" :disabled="booking.guests[g.key] <= g.min">−</button>
          <span class="counter-val">{{ booking.guests[g.key] }}</span>
          <button class="counter-btn" @click="increment(g.key)" :disabled="booking.guests[g.key] >= 12">+</button>
        </div>
      </div>
    </div>

    <div class="divider" />

    <!-- Budget range -->
    <SettingsRow label="Default Budget Range" desc="Used to pre-filter search results">
      <div class="budget-wrap">
        <span class="budget-label">${{ booking.budgetMin }}</span>
        <span class="budget-sep">—</span>
        <span class="budget-label">${{ booking.budgetMax }}</span>
      </div>
    </SettingsRow>
    <div class="range-row">
      <span class="range-hint">$0</span>
      <input type="range" v-model.number="booking.budgetMin" min="0" max="2000" step="50" class="range-input" />
      <input type="range" v-model.number="booking.budgetMax" min="0" max="10000" step="100" class="range-input" />
      <span class="range-hint">$10k+</span>
    </div>

    <div class="divider" />

    <!-- Toggles -->
    <SettingsRow label="Instant Book Only" desc="Only show listings that can be booked immediately without approval">
      <ToggleSwitch v-model="booking.instantBook" />
    </SettingsRow>

    <SettingsRow label="Flexible Dates" desc="Show options ±3 days around your selected dates">
      <ToggleSwitch v-model="booking.flexDates" />
    </SettingsRow>

    <SettingsRow label="Free Cancellation Only" desc="Filter for listings with free cancellation policy">
      <ToggleSwitch v-model="booking.freeCancellation" />
    </SettingsRow>

    <SettingsRow label="Accessibility Requirements" desc="Only show listings with accessibility features">
      <ToggleSwitch v-model="booking.accessibility" />
    </SettingsRow>

    <div class="divider" />

    <!-- Preferred currency & language already in profile, but a reminder row -->
    <SettingsRow label="Preferred Contact Method" desc="How agencies should reach you about bookings">
      <select v-model="booking.contactMethod" class="field-select">
        <option value="email">Email</option>
        <option value="phone">Phone</option>
        <option value="chat">In-app chat</option>
        <option value="whatsapp">WhatsApp</option>
      </select>
    </SettingsRow>

    <div class="divider" />
    <div class="action-row">
      <button class="btn-primary" @click="save">Save Preferences</button>
    </div>

  </SettingsSection>
</template>

<script setup>
import { reactive } from 'vue'
import SettingsSection from './SettingsSection.vue'
import SettingsRow     from './SettingsRow.vue'
import ToggleSwitch    from './ToggleSwitch.vue'

const emit = defineEmits(['saved'])

const travelStyles = ['Adventure', 'Beach', 'Cultural', 'Luxury', 'Budget', 'Family', 'Solo', 'Romantic', 'Eco', 'Food & Wine', 'Historical', 'Wildlife']

const guestTypes = [
  { key: 'adults',   label: 'Adults',   sub: '13+',         min: 1 },
  { key: 'children', label: 'Children', sub: '2–12 years',  min: 0 },
  { key: 'infants',  label: 'Infants',  sub: 'Under 2',     min: 0 },
]

const booking = reactive({
  travelStyles: ['Adventure', 'Cultural'],
  guests: { adults: 2, children: 0, infants: 0 },
  budgetMin: 200,
  budgetMax: 3000,
  instantBook: false,
  flexDates: true,
  freeCancellation: true,
  accessibility: false,
  contactMethod: 'email',
})

function toggleStyle(style) {
  const idx = booking.travelStyles.indexOf(style)
  if (idx >= 0) booking.travelStyles.splice(idx, 1)
  else booking.travelStyles.push(style)
}

function increment(key) { if (booking.guests[key] < 12) booking.guests[key]++ }
function decrement(key) {
  const min = key === 'adults' ? 1 : 0
  if (booking.guests[key] > min) booking.guests[key]--
}

function save() { emit('saved', 'Booking preferences saved!') }
</script>

<style scoped>
.block-label {
  font-size: 12px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--gray-600, #666); margin-bottom: 14px;
}
.divider { height: 1px; background: var(--gray-100, #F2F2F2); margin: 24px 0; }
.action-row { display: flex; justify-content: flex-end; margin-top: 8px; }

/* Tags */
.tag-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 4px; }
.tag-btn {
  padding: 7px 16px; border-radius: 50px; font-size: 13px; font-weight: 600;
  border: 1.5px solid var(--gray-200); background: none; cursor: pointer;
  color: var(--gray-600); font-family: 'DM Sans', sans-serif;
  transition: all var(--transition, .22s);
}
.tag-btn:hover  { border-color: var(--teal); color: var(--teal); }
.tag-btn.active { border-color: var(--teal); background: var(--teal-lt, rgba(46,196,182,.1)); color: var(--teal); }

/* Guest counters */
.guests-row { display: flex; flex-direction: column; gap: 0; margin-bottom: 4px; }
.guest-counter {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 0; border-bottom: 1px solid var(--gray-100);
}
.guest-counter:last-child { border-bottom: none; }
.guest-info { display: flex; flex-direction: column; gap: 2px; }
.guest-label { font-size: 14px; font-weight: 600; color: var(--indigo); }
.guest-sub   { font-size: 12px; color: var(--gray-600); }

.counter { display: flex; align-items: center; gap: 16px; }
.counter-btn {
  width: 32px; height: 32px; border-radius: 50%;
  border: 1.5px solid var(--gray-200); background: none;
  font-size: 18px; line-height: 1; cursor: pointer; color: var(--indigo);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition); font-weight: 300;
}
.counter-btn:hover:not(:disabled) { border-color: var(--teal); color: var(--teal); }
.counter-btn:disabled { opacity: .3; cursor: not-allowed; }
.counter-val { font-size: 16px; font-weight: 700; color: var(--indigo); min-width: 20px; text-align: center; }

/* Budget */
.budget-wrap { display: flex; align-items: center; gap: 8px; }
.budget-label { font-size: 14px; font-weight: 700; color: var(--indigo); }
.budget-sep   { color: var(--gray-400); }
.range-row { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
.range-input { flex: 1; accent-color: var(--teal); }
.range-hint   { font-size: 12px; color: var(--gray-400); white-space: nowrap; }

/* Select */
.field-select {
  padding: 8px 32px 8px 12px; border: 1.5px solid var(--gray-200);
  border-radius: var(--radius-sm, 8px); font-size: 13px; font-weight: 600;
  color: var(--indigo); background: #fff; cursor: pointer;
  font-family: 'DM Sans', sans-serif; outline: none;
  transition: border-color var(--transition);
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23666' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 10px center;
}
.field-select:focus { border-color: var(--teal); }

.btn-primary {
  padding: 10px 22px; background: var(--coral); color: #fff;
  border: none; border-radius: 50px; font-size: 14px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background var(--transition);
}
.btn-primary:hover { background: var(--coral-dk, #e04045); }
</style>