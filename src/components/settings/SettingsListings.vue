<template>
  <SettingsSection
    title="Listing Settings"
    subtitle="Control how your packages and services appear to travellers"
  >

    <!-- Availability -->
    <div class="block-label">Default Availability</div>
    <SettingsRow label="Accept New Bookings" desc="Accept all new booking requests">
      <ToggleSwitch v-model="listings.acceptBookings" />
    </SettingsRow>
    <SettingsRow label="Instant Booking" desc="Allow guests to book without manual approval">
      <ToggleSwitch v-model="listings.instantBook" />
    </SettingsRow>
    <SettingsRow label="Auto-confirm Requests" desc="Automatically confirm all incoming requests">
      <ToggleSwitch v-model="listings.autoConfirm" />
    </SettingsRow>

    <div class="divider" />

    <!-- Response settings -->
    <div class="block-label">Response Settings</div>
    <SettingsRow label="Response Time Target" desc="Aim to respond to enquiries within this window">
      <select v-model="listings.responseTime" class="field-select">
        <option value="1h">Within 1 hour</option>
        <option value="3h">Within 3 hours</option>
        <option value="12h">Within 12 hours</option>
        <option value="24h">Within 24 hours</option>
      </select>
    </SettingsRow>
    <SettingsRow label="Auto-reply Message" desc="Send an automatic reply while you review the request">
      <ToggleSwitch v-model="listings.autoReply" />
    </SettingsRow>

    <Transition name="expand">
      <div v-if="listings.autoReply" class="auto-reply-box">
        <label class="field-label">Auto-reply text</label>
        <textarea v-model="listings.autoReplyText" class="field-textarea" rows="3"
          placeholder="e.g. Thanks for reaching out! I'll get back to you within 3 hours…" />
      </div>
    </Transition>

    <div class="divider" />

    <!-- Cancellation policy -->
    <div class="block-label">Cancellation Policy</div>
    <div class="policy-grid">
      <label
        v-for="p in policies"
        :key="p.key"
        class="policy-card"
        :class="{ active: listings.cancellationPolicy === p.key }"
      >
        <input type="radio" :value="p.key" v-model="listings.cancellationPolicy" hidden />
        <div class="policy-dot" />
        <div>
          <p class="policy-name">{{ p.name }}</p>
          <p class="policy-desc">{{ p.desc }}</p>
        </div>
      </label>
    </div>

    <div class="divider" />

    <!-- Min / Max booking -->
    <div class="block-label">Booking Limits</div>
    <div class="limits-grid">
      <div class="limit-field">
        <label class="field-label">Min. booking lead time</label>
        <select v-model="listings.minLead" class="field-select">
          <option value="0">Same day</option>
          <option value="1">1 day</option>
          <option value="3">3 days</option>
          <option value="7">1 week</option>
          <option value="14">2 weeks</option>
        </select>
      </div>
      <div class="limit-field">
        <label class="field-label">Max. advance booking</label>
        <select v-model="listings.maxAdvance" class="field-select">
          <option value="30">1 month</option>
          <option value="90">3 months</option>
          <option value="180">6 months</option>
          <option value="365">1 year</option>
        </select>
      </div>
      <div class="limit-field">
        <label class="field-label">Min. party size</label>
        <input type="number" v-model.number="listings.minParty" class="field-input" min="1" max="50" />
      </div>
      <div class="limit-field">
        <label class="field-label">Max. party size</label>
        <input type="number" v-model.number="listings.maxParty" class="field-input" min="1" max="200" />
      </div>
    </div>

    <div class="divider" />

    <!-- Pricing -->
    <div class="block-label">Pricing Display</div>
    <SettingsRow label="Show Prices Including Taxes" desc="Display all-inclusive prices on your listing cards">
      <ToggleSwitch v-model="listings.pricesIncludeTax" />
    </SettingsRow>
    <SettingsRow label="Show Early-Bird Discount Badge" desc="Highlight discounted prices for early bookings">
      <ToggleSwitch v-model="listings.earlyBirdBadge" />
    </SettingsRow>

    <div class="divider" />
    <div class="action-row">
      <button class="btn-primary" @click="save">Save Listing Settings</button>
    </div>

  </SettingsSection>
</template>

<script setup>
import { reactive } from 'vue'
import SettingsSection from './SettingsSection.vue'
import SettingsRow     from './SettingsRow.vue'
import ToggleSwitch    from './ToggleSwitch.vue'

const emit = defineEmits(['saved'])

const policies = [
  { key: 'flexible',  name: 'Flexible',  desc: 'Full refund up to 1 day before departure' },
  { key: 'moderate',  name: 'Moderate',  desc: 'Full refund up to 5 days before, 50% after' },
  { key: 'strict',    name: 'Strict',    desc: '50% refund up to 7 days before, no refund after' },
  { key: 'nonrefund', name: 'Non-refundable', desc: 'No refunds after booking confirmation' },
]

const listings = reactive({
  acceptBookings: true,
  instantBook: false,
  autoConfirm: false,
  responseTime: '3h',
  autoReply: true,
  autoReplyText: 'Thanks for reaching out! I\'ll review your request and get back to you within 3 hours.',
  cancellationPolicy: 'moderate',
  minLead: '1',
  maxAdvance: '180',
  minParty: 1,
  maxParty: 20,
  pricesIncludeTax: true,
  earlyBirdBadge: true,
})

function save() { emit('saved', 'Listing settings saved!') }
</script>

<style scoped>
.block-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--gray-600, #666); margin-bottom: 14px; }
.divider { height: 1px; background: var(--gray-100, #F2F2F2); margin: 24px 0; }
.action-row { display: flex; justify-content: flex-end; margin-top: 8px; }

.field-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--gray-600); margin-bottom: 6px; display: block; }

.field-select {
  padding: 8px 32px 8px 12px; border: 1.5px solid var(--gray-200);
  border-radius: var(--radius-sm, 8px); font-size: 13px; font-weight: 600;
  color: var(--indigo); background: #fff; cursor: pointer;
  font-family: 'DM Sans', sans-serif; outline: none; transition: border-color var(--transition);
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23666' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 10px center;
}
.field-select:focus { border-color: var(--teal); }

.field-input {
  padding: 9px 13px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  font-size: 14px; font-family: 'DM Sans', sans-serif; color: var(--indigo);
  outline: none; transition: border-color var(--transition); background: #fff;
  width: 100%; box-sizing: border-box;
}
.field-input:focus { border-color: var(--teal); }

.field-textarea {
  width: 100%; box-sizing: border-box; padding: 10px 13px;
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  font-size: 13px; font-family: 'DM Sans', sans-serif; color: var(--indigo);
  resize: vertical; outline: none; transition: border-color var(--transition);
}
.field-textarea:focus { border-color: var(--teal); }

.auto-reply-box { background: var(--gray-50); border-radius: var(--radius-sm); padding: 16px; margin-top: 4px; }
.expand-enter-active, .expand-leave-active { transition: opacity .2s, transform .2s; }
.expand-enter-from, .expand-leave-to { opacity: 0; transform: translateY(-6px); }

/* Policies */
.policy-grid { display: flex; flex-direction: column; gap: 10px; }
.policy-card {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 14px 16px; border: 1.5px solid var(--gray-200);
  border-radius: var(--radius-sm); cursor: pointer; transition: border-color .22s;
}
.policy-card:hover  { border-color: var(--gray-400); }
.policy-card.active { border-color: var(--teal); background: var(--teal-lt, rgba(46,196,182,.05)); }
.policy-dot {
  width: 18px; height: 18px; border-radius: 50%; flex-shrink: 0; margin-top: 2px;
  border: 2px solid var(--gray-400); transition: all .2s;
}
.policy-card.active .policy-dot { border-color: var(--teal); box-shadow: inset 0 0 0 4px var(--teal); }
.policy-name { font-size: 14px; font-weight: 700; color: var(--indigo); margin: 0 0 3px; }
.policy-desc { font-size: 13px; color: var(--gray-600); margin: 0; }

/* Limits */
.limits-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.limit-field { display: flex; flex-direction: column; gap: 6px; }

.btn-primary {
  padding: 10px 22px; background: var(--coral); color: #fff;
  border: none; border-radius: 50px; font-size: 14px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background var(--transition);
}
.btn-primary:hover { background: var(--coral-dk, #e04045); }

@media (max-width: 600px) { .limits-grid { grid-template-columns: 1fr; } }
</style>