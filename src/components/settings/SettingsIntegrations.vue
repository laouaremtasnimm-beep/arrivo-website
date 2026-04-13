<template>
  <SettingsSection
    title="Connected Apps"
    subtitle="Manage integrations, connected accounts and API access"
  >

    <!-- OAuth integrations -->
    <div class="block-label">Social & Calendar Integrations</div>
    <div class="integrations-list">
      <div v-for="app in integrations" :key="app.key" class="integration-row">
        <div class="app-icon" :style="{ background: app.bg }">
          <span v-html="app.icon" />
        </div>
        <div class="app-info">
          <p class="app-name">{{ app.name }}</p>
          <p class="app-desc">{{ app.desc }}</p>
          <p v-if="app.connected && app.connectedAs" class="app-connected-as">
            Connected as <strong>{{ app.connectedAs }}</strong>
          </p>
        </div>
        <div class="app-actions">
          <span v-if="app.connected" class="connected-tag">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            Connected
          </span>
          <button
            class="btn-connect"
            :class="{ connected: app.connected }"
            @click="toggleIntegration(app)"
          >
            {{ app.connected ? 'Disconnect' : 'Connect' }}
          </button>
        </div>
      </div>
    </div>

    <div class="divider" />

    <!-- Webhooks (agency/provider) -->
    <div class="block-label">
      Webhooks
      <span class="beta-badge">Beta</span>
    </div>
    <p class="section-desc">Receive real-time HTTP notifications when events happen in your account.</p>

    <div class="webhooks-list">
      <div v-for="wh in webhooks" :key="wh.id" class="webhook-row">
        <div class="webhook-info">
          <code class="webhook-url">{{ wh.url }}</code>
          <div class="webhook-events">
            <span v-for="ev in wh.events" :key="ev" class="event-tag">{{ ev }}</span>
          </div>
        </div>
        <span class="wh-status" :class="wh.active ? 'active' : 'inactive'">
          {{ wh.active ? 'Active' : 'Paused' }}
        </span>
        <button class="btn-icon" @click="toggleWebhook(wh.id)">
          <svg v-if="wh.active" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
          <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        </button>
        <button class="btn-icon danger" @click="removeWebhook(wh.id)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
        </button>
      </div>
    </div>

    <button class="btn-add" @click="showAddWebhook = !showAddWebhook">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Webhook
    </button>

    <Transition name="expand">
      <div v-if="showAddWebhook" class="add-webhook-form">
        <div class="form-field">
          <label class="field-label">Endpoint URL</label>
          <input v-model="newWebhook.url" class="field-input" placeholder="https://yourapp.com/webhooks/voyago" />
        </div>
        <div class="form-field">
          <label class="field-label">Events to listen for</label>
          <div class="event-checkboxes">
            <label v-for="ev in webhookEventOptions" :key="ev" class="event-check">
              <input type="checkbox" :value="ev" v-model="newWebhook.events" />
              <span>{{ ev }}</span>
            </label>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn-ghost" @click="showAddWebhook = false">Cancel</button>
          <button class="btn-primary" @click="addWebhook">Add Webhook</button>
        </div>
      </div>
    </Transition>

    <div class="divider" />

    <!-- API Keys -->
    <div class="block-label">API Keys</div>
    <p class="section-desc">Use API keys to integrate Voyago data into your own apps and tools.</p>

    <div class="api-keys-list">
      <div v-for="key in apiKeys" :key="key.id" class="api-key-row">
        <div class="key-info">
          <span class="key-name">{{ key.name }}</span>
          <code class="key-value">{{ key.visible ? key.value : maskKey(key.value) }}</code>
          <span class="key-date">Created {{ key.created }}</span>
        </div>
        <div class="key-actions">
          <button class="btn-icon" @click="key.visible = !key.visible" :title="key.visible ? 'Hide' : 'Reveal'">
            <svg v-if="!key.visible" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
          <button class="btn-icon" @click="copyKey(key.value)" title="Copy">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          </button>
          <button class="btn-icon danger" @click="revokeKey(key.id)" title="Revoke">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
          </button>
        </div>
      </div>
    </div>

    <button class="btn-add" style="margin-top: 12px" @click="generateKey">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Generate New Key
    </button>

  </SettingsSection>
</template>

<script setup>
import { reactive, ref } from 'vue'
import SettingsSection from './SettingsSection.vue'

const emit = defineEmits(['saved'])

const integrations = reactive([
  {
    key: 'google',
    name: 'Google',
    desc: 'Sync bookings with Google Calendar and sign in with Google',
    bg: '#fff',
    icon: '<svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>',
    connected: true,
    connectedAs: 'user@gmail.com',
  },
  {
    key: 'facebook',
    name: 'Facebook',
    desc: 'Share travel plans and sign in with Facebook',
    bg: '#1877F2',
    icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
    connected: false,
    connectedAs: null,
  },
  {
    key: 'whatsapp',
    name: 'WhatsApp',
    desc: 'Get booking notifications and chat with providers via WhatsApp',
    bg: '#25D366',
    icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>',
    connected: true,
    connectedAs: '+213 555 000 000',
  },
  {
    key: 'tripadvisor',
    name: 'TripAdvisor',
    desc: 'Import reviews and sync your listing ratings',
    bg: '#34E0A1',
    icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="white"><circle cx="6.5" cy="12" r="3.5"/><circle cx="17.5" cy="12" r="3.5"/><path d="M1 8h4M19 8h4M12 5v2"/></svg>',
    connected: false,
    connectedAs: null,
  },
])

function toggleIntegration(app) {
  app.connected = !app.connected
  if (!app.connected) app.connectedAs = null
  else app.connectedAs = 'mock@example.com'
  emit('saved', app.connected ? `${app.name} connected!` : `${app.name} disconnected`)
}

// Webhooks
const webhooks = ref([
  { id: 1, url: 'https://myapp.com/hooks/booking', events: ['booking.created', 'booking.cancelled'], active: true },
  { id: 2, url: 'https://myapp.com/hooks/review',  events: ['review.created'], active: false },
])

const showAddWebhook = ref(false)
const newWebhook = reactive({ url: '', events: [] })

const webhookEventOptions = ['booking.created', 'booking.cancelled', 'booking.confirmed', 'review.created', 'message.received', 'payment.received']

function toggleWebhook(id) {
  const wh = webhooks.value.find(w => w.id === id)
  if (wh) wh.active = !wh.active
}

function removeWebhook(id) {
  webhooks.value = webhooks.value.filter(w => w.id !== id)
}

function addWebhook() {
  if (!newWebhook.url) return
  webhooks.value.push({ id: Date.now(), url: newWebhook.url, events: [...newWebhook.events], active: true })
  Object.assign(newWebhook, { url: '', events: [] })
  showAddWebhook.value = false
  emit('saved', 'Webhook added!')
}

// API Keys
const apiKeys = ref([
  { id: 1, name: 'Production Key', value: 'voy_live_sk_xK93mP2nLqR7bT5uY8wZ', created: 'Oct 12, 2024', visible: false },
  { id: 2, name: 'Test Key',       value: 'voy_test_sk_aA1bB2cC3dD4eE5fF6gG', created: 'Nov 3, 2024',  visible: false },
])

function maskKey(val) { return val.slice(0, 12) + '••••••••••••••••' }

function copyKey(val) {
  navigator.clipboard.writeText(val).catch(() => {})
  emit('saved', 'API key copied to clipboard!')
}

function revokeKey(id) {
  apiKeys.value = apiKeys.value.filter(k => k.id !== id)
  emit('saved', 'Key revoked.')
}

function generateKey() {
  const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
  const rand = Array.from({ length: 24 }, () => chars[Math.floor(Math.random() * chars.length)]).join('')
  apiKeys.value.push({
    id: Date.now(), name: 'New Key',
    value: `voy_live_sk_${rand}`,
    created: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
    visible: true,
  })
  emit('saved', 'New API key generated!')
}
</script>

<style scoped>
.block-label {
  font-size: 12px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--gray-600, #666);
  margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
}
.beta-badge {
  font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 50px;
  background: rgba(124,58,237,.12); color: #7C3AED; letter-spacing: .05em;
}
.section-desc { font-size: 13px; color: var(--gray-600); margin: -8px 0 16px; line-height: 1.6; }
.divider { height: 1px; background: var(--gray-100, #F2F2F2); margin: 24px 0; }

/* Integrations */
.integrations-list { display: flex; flex-direction: column; gap: 10px; }
.integration-row {
  display: flex; align-items: center; gap: 16px;
  padding: 14px 16px; border: 1.5px solid var(--gray-200);
  border-radius: var(--radius-sm, 8px); transition: border-color .22s;
}
.integration-row:hover { border-color: var(--gray-400); }

.app-icon {
  width: 42px; height: 42px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; border: 1px solid var(--gray-200);
}
.app-info { flex: 1; }
.app-name  { font-size: 14px; font-weight: 700; color: var(--indigo); margin: 0 0 2px; }
.app-desc  { font-size: 12px; color: var(--gray-600); margin: 0; }
.app-connected-as { font-size: 12px; color: var(--teal, #2EC4B6); margin: 3px 0 0; }

.app-actions { display: flex; align-items: center; gap: 10px; }
.connected-tag {
  display: flex; align-items: center; gap: 5px;
  font-size: 12px; font-weight: 700; color: var(--teal);
  background: var(--teal-lt, rgba(46,196,182,.1));
  padding: 4px 10px; border-radius: 50px;
}
.btn-connect {
  padding: 7px 16px; border-radius: 50px; font-size: 13px; font-weight: 600;
  border: 1.5px solid var(--gray-200); background: none; cursor: pointer;
  color: var(--indigo); font-family: 'DM Sans', sans-serif;
  transition: all var(--transition, .22s);
}
.btn-connect:hover:not(.connected) { border-color: var(--teal); color: var(--teal); }
.btn-connect.connected { border-color: rgba(255,90,95,.3); color: var(--coral, #FF5A5F); }
.btn-connect.connected:hover { background: rgba(255,90,95,.06); }

/* Webhooks */
.webhooks-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
.webhook-row {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; background: var(--gray-50); border-radius: var(--radius-sm);
}
.webhook-info { flex: 1; display: flex; flex-direction: column; gap: 5px; }
.webhook-url  { font-size: 12px; font-family: monospace; color: var(--indigo); word-break: break-all; }
.webhook-events { display: flex; flex-wrap: wrap; gap: 4px; }
.event-tag { font-size: 11px; background: var(--teal-lt); color: var(--teal); padding: 2px 8px; border-radius: 4px; font-weight: 600; }
.wh-status { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 50px; white-space: nowrap; }
.wh-status.active   { background: rgba(46,196,182,.12); color: var(--teal-dk, #26b0a4); }
.wh-status.inactive { background: var(--gray-200); color: var(--gray-600); }

/* Webhook form */
.add-webhook-form {
  background: var(--gray-50); border-radius: var(--radius-sm);
  padding: 18px; margin-top: 8px;
  display: flex; flex-direction: column; gap: 14px;
}
.form-field  { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--gray-600); }
.field-input {
  padding: 9px 13px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  font-size: 13px; font-family: 'DM Sans', sans-serif; color: var(--indigo);
  outline: none; transition: border-color var(--transition); background: #fff;
  width: 100%; box-sizing: border-box;
}
.field-input:focus { border-color: var(--teal); }
.event-checkboxes { display: flex; flex-wrap: wrap; gap: 10px; }
.event-check { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--gray-600); cursor: pointer; }
.event-check input { accent-color: var(--teal); }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; }

/* API keys */
.api-keys-list { display: flex; flex-direction: column; gap: 10px; }
.api-key-row {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
}
.key-info  { flex: 1; display: flex; flex-direction: column; gap: 3px; }
.key-name  { font-size: 13px; font-weight: 700; color: var(--indigo); }
.key-value { font-size: 12px; font-family: monospace; color: var(--gray-600); word-break: break-all; }
.key-date  { font-size: 11px; color: var(--gray-400); }
.key-actions { display: flex; gap: 4px; }

/* Expand */
.expand-enter-active, .expand-leave-active { transition: opacity .2s, transform .2s; }
.expand-enter-from, .expand-leave-to { opacity: 0; transform: translateY(-6px); }

/* Shared buttons */
.btn-add {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 16px; background: none;
  border: 1.5px solid var(--teal); color: var(--teal);
  border-radius: 50px; font-size: 13px; font-weight: 700;
  font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all var(--transition);
}
.btn-add:hover { background: var(--teal-lt); }
.btn-primary {
  padding: 9px 18px; background: var(--coral); color: #fff;
  border: none; border-radius: 50px; font-size: 13px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background var(--transition);
}
.btn-primary:hover { background: var(--coral-dk, #e04045); }
.btn-ghost {
  padding: 9px 14px; background: transparent; color: var(--gray-600);
  border: none; border-radius: 50px; font-size: 13px;
  font-family: 'DM Sans', sans-serif; cursor: pointer;
}
.btn-icon {
  width: 30px; height: 30px; border-radius: 6px; border: none;
  background: none; cursor: pointer; color: var(--gray-600);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.btn-icon:hover { background: var(--gray-100); color: var(--indigo); }
.btn-icon.danger:hover { background: rgba(255,90,95,.1); color: var(--coral); }
</style>