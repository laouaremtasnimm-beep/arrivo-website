v<template>
  <SettingsSection
    title="Billing & Payments"
    subtitle="Manage your payment methods, subscription plan and invoices"
  >

    <!-- Current plan -->
    <div class="block-label">Current Plan</div>
    <div class="plan-grid">
      <div
        v-for="p in plans"
        :key="p.key"
        class="plan-card"
        :class="{ active: billing.plan === p.key, popular: p.popular }"
        @click="billing.plan = p.key"
      >
        <span v-if="p.popular" class="popular-badge">Most Popular</span>
        <p class="plan-name">{{ p.name }}</p>
        <p class="plan-price">{{ p.price }}<span v-if="p.price !== 'Free'">/mo</span></p>
        <ul class="plan-features">
          <li v-for="f in p.features" :key="f">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            {{ f }}
          </li>
        </ul>
        <div class="plan-selector">
          <div class="plan-radio" />
        </div>
      </div>
    </div>

    <div class="divider" />

    <!-- Payment methods -->
    <div class="billing-header-row">
      <div class="block-label" style="margin:0">Payment Methods</div>
      <button class="btn-add" @click="showAddCard = !showAddCard">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Card
      </button>
    </div>

    <!-- Add card form -->
    <Transition name="expand">
      <div v-if="showAddCard" class="add-card-form">
        <div class="form-grid">
          <div class="form-field full-width">
            <label>Card Number</label>
            <input v-model="newCard.number" class="field-input" placeholder="1234 5678 9012 3456" maxlength="19" @input="formatCardNumber" />
          </div>
          <div class="form-field">
            <label>Expiry</label>
            <input v-model="newCard.expiry" class="field-input" placeholder="MM / YY" maxlength="7" />
          </div>
          <div class="form-field">
            <label>CVC</label>
            <input v-model="newCard.cvc" class="field-input" placeholder="123" maxlength="3" type="password" />
          </div>
          <div class="form-field full-width">
            <label>Name on Card</label>
            <input v-model="newCard.name" class="field-input" placeholder="Full name" />
          </div>
        </div>
        <div class="form-actions">
          <button class="btn-ghost" @click="showAddCard = false">Cancel</button>
          <button class="btn-primary" @click="addCard">Add Card</button>
        </div>
      </div>
    </Transition>

    <div class="cards-list">
      <div v-if="paymentMethods.length === 0" class="empty-state">No payment methods added.</div>
      <div v-for="card in paymentMethods" :key="card.id" class="card-row">
        <div class="card-brand" :class="`brand-${card.brand}`">
          {{ card.brand.toUpperCase() }}
        </div>
        <div class="card-info">
          <span class="card-number">•••• •••• •••• {{ card.last4 }}</span>
          <span class="card-expiry">Expires {{ card.expiry }}</span>
        </div>
        <span v-if="card.default" class="default-tag">Default</span>
        <button v-else class="btn-ghost-sm" @click="setDefault(card.id)">Set default</button>
        <button class="btn-icon-danger" @click="removeCard(card.id)" title="Remove card">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
        </button>
      </div>
    </div>

    <div class="divider" />

    <!-- Invoices -->
    <div class="block-label">Recent Invoices</div>
    <div class="invoices-list">
      <div v-for="inv in invoices" :key="inv.id" class="invoice-row">
        <div class="invoice-info">
          <span class="invoice-id">{{ inv.id }}</span>
          <span class="invoice-date">{{ inv.date }}</span>
        </div>
        <span class="invoice-amount">{{ inv.amount }}</span>
        <span class="invoice-status" :class="`status-${inv.status}`">{{ inv.status }}</span>
        <button class="btn-ghost-sm" @click="downloadInvoice(inv.id)">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          PDF
        </button>
      </div>
    </div>

    <div class="divider" />

    <!-- Billing settings -->
    <SettingsRow label="Auto-renew Subscription" desc="Automatically renew your plan each billing cycle">
      <ToggleSwitch v-model="billing.autoRenew" />
    </SettingsRow>
    <SettingsRow label="Email Receipts" desc="Receive a receipt by email for every payment">
      <ToggleSwitch v-model="billing.emailReceipts" />
    </SettingsRow>

  </SettingsSection>
</template>

<script setup>
import { reactive, ref } from 'vue'
import SettingsSection from './SettingsSection.vue'
import SettingsRow     from './SettingsRow.vue'
import ToggleSwitch    from './ToggleSwitch.vue'

const emit = defineEmits(['saved'])

const plans = [
  {
    key: 'free', name: 'Free', price: 'Free',
    features: ['Browse destinations', 'Up to 3 bookings/mo', 'Basic support'],
  },
  {
    key: 'pro', name: 'Explorer Pro', price: '$9',
    popular: true,
    features: ['Unlimited bookings', 'Priority support', 'Exclusive deals', 'Early access'],
  },
  {
    key: 'business', name: 'Business', price: '$29',
    features: ['Everything in Pro', 'Team seats (5)', 'Analytics dashboard', 'API access'],
  },
]

const billing = reactive({ plan: 'pro', autoRenew: true, emailReceipts: true })

const showAddCard = ref(false)
const newCard = reactive({ number: '', expiry: '', cvc: '', name: '' })

const paymentMethods = ref([
  { id: 1, brand: 'visa',       last4: '4242', expiry: '08/26', default: true },
  { id: 2, brand: 'mastercard', last4: '1234', expiry: '11/25', default: false },
])

function formatCardNumber(e) {
  let v = e.target.value.replace(/\D/g, '').slice(0, 16)
  newCard.number = v.replace(/(.{4})/g, '$1 ').trim()
}

function addCard() {
  if (!newCard.number || !newCard.expiry || !newCard.cvc || !newCard.name) return
  paymentMethods.value.push({
    id: Date.now(),
    brand: 'visa',
    last4: newCard.number.replace(/\s/g, '').slice(-4),
    expiry: newCard.expiry,
    default: false,
  })
  Object.assign(newCard, { number: '', expiry: '', cvc: '', name: '' })
  showAddCard.value = false
  emit('saved', 'Card added!')
}

function setDefault(id) {
  paymentMethods.value.forEach(c => c.default = c.id === id)
}

function removeCard(id) {
  paymentMethods.value = paymentMethods.value.filter(c => c.id !== id)
}

const invoices = ref([
  { id: 'INV-2024-011', date: 'Nov 1, 2024', amount: '$9.00', status: 'paid' },
  { id: 'INV-2024-010', date: 'Oct 1, 2024', amount: '$9.00', status: 'paid' },
  { id: 'INV-2024-009', date: 'Sep 1, 2024', amount: '$9.00', status: 'paid' },
])

function downloadInvoice(id) {
  alert(`Downloading ${id}… (mock — no backend)`)
}
</script>

<style scoped>
.block-label {
  font-size: 12px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--gray-600, #666); margin-bottom: 14px;
}
.billing-header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.divider { height: 1px; background: var(--gray-100, #F2F2F2); margin: 24px 0; }
.empty-state { font-size: 14px; color: var(--gray-400); padding: 8px 0; }

/* Plans */
.plan-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 4px; }
.plan-card {
  border: 2px solid var(--gray-200); border-radius: var(--radius-sm); padding: 18px 16px;
  cursor: pointer; position: relative; transition: border-color .22s, box-shadow .22s;
}
.plan-card:hover  { border-color: var(--gray-400); }
.plan-card.active { border-color: var(--teal); box-shadow: 0 0 0 4px var(--teal-lt, rgba(46,196,182,.1)); }
.plan-card.popular.active { border-color: var(--coral); box-shadow: 0 0 0 4px rgba(255,90,95,.08); }

.popular-badge {
  position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
  background: var(--coral); color: #fff; font-size: 10px; font-weight: 700;
  padding: 2px 10px; border-radius: 50px; white-space: nowrap;
}
.plan-name  { font-size: 13px; font-weight: 700; color: var(--indigo); margin: 0 0 4px; }
.plan-price { font-size: 22px; font-weight: 700; color: var(--indigo); margin: 0 0 10px; font-family: 'Fraunces', serif; }
.plan-price span { font-size: 13px; font-weight: 400; color: var(--gray-600); }
.plan-features { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px; }
.plan-features li { display: flex; align-items: center; gap: 7px; font-size: 12px; color: var(--gray-600); }
.plan-features svg { color: var(--teal); flex-shrink: 0; }
.plan-selector { margin-top: 12px; display: flex; justify-content: flex-end; }
.plan-radio {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid var(--gray-400); transition: all .2s;
}
.plan-card.active .plan-radio { border-color: var(--teal); box-shadow: inset 0 0 0 4px var(--teal); }
.plan-card.popular.active .plan-radio { border-color: var(--coral); box-shadow: inset 0 0 0 4px var(--coral); }

/* Add card form */
.add-card-form {
  background: var(--gray-50); border-radius: var(--radius-sm);
  padding: 20px; margin-bottom: 16px;
}
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
.form-field { display: flex; flex-direction: column; gap: 6px; }
.full-width  { grid-column: 1 / -1; }
.form-field label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--gray-600); }
.field-input {
  padding: 9px 13px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  font-size: 14px; font-family: 'DM Sans', sans-serif; color: var(--indigo);
  outline: none; transition: border-color var(--transition); background: #fff; width: 100%; box-sizing: border-box;
}
.field-input:focus { border-color: var(--teal); }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; }

/* Expand transition */
.expand-enter-active, .expand-leave-active { transition: opacity .2s, transform .2s; }
.expand-enter-from, .expand-leave-to { opacity: 0; transform: translateY(-8px); }

/* Cards */
.cards-list { display: flex; flex-direction: column; gap: 10px; }
.card-row {
  display: flex; align-items: center; gap: 14px;
  padding: 12px 16px; border: 1.5px solid var(--gray-200);
  border-radius: var(--radius-sm);
}
.card-brand {
  font-size: 11px; font-weight: 800; letter-spacing: .05em;
  padding: 4px 8px; border-radius: 4px; min-width: 52px; text-align: center;
}
.brand-visa       { background: #1a1f71; color: #fff; }
.brand-mastercard { background: #eb001b; color: #fff; }
.brand-amex       { background: #007bc1; color: #fff; }
.card-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.card-number { font-size: 14px; font-weight: 600; color: var(--indigo); }
.card-expiry { font-size: 12px; color: var(--gray-600); }
.default-tag { font-size: 12px; font-weight: 700; color: var(--teal); background: var(--teal-lt); padding: 3px 10px; border-radius: 50px; }

/* Invoices */
.invoices-list { display: flex; flex-direction: column; gap: 8px; }
.invoice-row {
  display: flex; align-items: center; gap: 14px;
  padding: 11px 16px; background: var(--gray-50); border-radius: var(--radius-sm);
}
.invoice-info   { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.invoice-id     { font-size: 13px; font-weight: 700; color: var(--indigo); }
.invoice-date   { font-size: 12px; color: var(--gray-600); }
.invoice-amount { font-size: 14px; font-weight: 700; color: var(--indigo); min-width: 54px; text-align: right; }
.invoice-status { font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 50px; }
.status-paid    { background: rgba(46,196,182,.12); color: var(--teal-dk, #26b0a4); }
.status-pending { background: rgba(245,158,11,.12); color: #d97706; }
.status-failed  { background: rgba(255,90,95,.12); color: var(--coral); }

/* Buttons */
.btn-add {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 16px; background: none;
  border: 1.5px solid var(--teal); color: var(--teal);
  border-radius: 50px; font-size: 13px; font-weight: 700;
  font-family: 'DM Sans', sans-serif; cursor: pointer;
  transition: all var(--transition);
}
.btn-add:hover { background: var(--teal-lt); }
.btn-primary {
  padding: 9px 20px; background: var(--coral); color: #fff;
  border: none; border-radius: 50px; font-size: 14px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background var(--transition);
}
.btn-primary:hover { background: var(--coral-dk, #e04045); }
.btn-ghost {
  padding: 9px 16px; background: transparent; color: var(--gray-600);
  border: none; border-radius: 50px; font-size: 14px;
  font-family: 'DM Sans', sans-serif; cursor: pointer; transition: color var(--transition);
}
.btn-ghost:hover { color: var(--indigo); }
.btn-ghost-sm {
  padding: 5px 12px; background: none; border: 1.5px solid var(--gray-200);
  border-radius: 50px; font-size: 12px; font-weight: 600; color: var(--gray-600);
  cursor: pointer; font-family: 'DM Sans', sans-serif;
  display: flex; align-items: center; gap: 5px; transition: all var(--transition);
}
.btn-ghost-sm:hover { border-color: var(--teal); color: var(--teal); }
.btn-icon-danger {
  width: 30px; height: 30px; border-radius: 50%; border: none;
  background: none; cursor: pointer; color: var(--gray-400);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.btn-icon-danger:hover { background: rgba(255,90,95,.1); color: var(--coral); }

@media (max-width: 600px) {
  .plan-grid { grid-template-columns: 1fr; }
}
</style>