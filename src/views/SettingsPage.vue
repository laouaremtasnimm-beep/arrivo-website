<template>
  <div class="settings-page">
    <div class="settings-container">

      <!-- ── Sidebar ── -->
      <aside class="settings-sidebar">
        <div class="sidebar-head">
          <h1 class="sidebar-title">Settings</h1>
          <p class="sidebar-sub">Manage your account</p>
        </div>

        <nav class="settings-nav">
          <div
            v-for="group in navGroups"
            :key="group.label"
            class="nav-group"
          >
            <span class="nav-group-label">{{ group.label }}</span>
            <button
              v-for="item in group.items.filter(i => !i.roles || i.roles.includes(user?.role))"
              :key="item.key"
              class="settings-nav-btn"
              :class="{ active: activeSection === item.key }"
              @click="activeSection = item.key"
            >
              <span class="nav-icon" v-html="item.icon"></span>
              <span>{{ item.label }}</span>
              <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
            </button>
          </div>
        </nav>
      </aside>

      <!-- ── Content ── -->
      <main class="settings-main">
        <Transition name="fade-slide" mode="out-in">
          <component
            :is="currentComponent"
            :key="activeSection"
            :user="user"
            :role="user?.role"
            @saved="onSaved"
          />
        </Transition>
      </main>

    </div>

    <!-- Global save toast -->
    <Transition name="toast">
      <div v-if="toastMsg" class="global-toast">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        {{ toastMsg }}
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, shallowRef, watch } from 'vue'
import { useAuth } from '@/composables/useAuth.js'

import SettingsAppearance   from '@/components/settings/SettingsAppearance.vue'
import SettingsPrivacy      from '@/components/settings/SettingsPrivacy.vue'
import SettingsBilling      from '@/components/settings/SettingsBilling.vue'
import SettingsIntegrations from '@/components/settings/SettingsIntegrations.vue'
import SettingsBooking      from '@/components/settings/SettingsBooking.vue'
import SettingsListings     from '@/components/settings/SettingsListings.vue'

const { user } = useAuth()

const activeSection = ref('appearance')
const toastMsg = ref(null)

const navGroups = computed(() => [
  {
    label: 'General',
    items: [
      {
        key: 'appearance',
        label: 'Appearance',
        icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M20.188 10.934l-1.428-.857A8.007 8.007 0 0 0 19 9.286v-1.6A1.68 1.68 0 0 0 17.32 6h-.36a1.68 1.68 0 0 0-1.68 1.686v.16a8 8 0 0 0-.76-.44l-.857-1.428A1.68 1.68 0 0 0 12.2 5.2h-.4a1.68 1.68 0 0 0-1.463.778L9.48 7.406a8 8 0 0 0-.76.44v-.16A1.68 1.68 0 0 0 7.04 6h-.36A1.68 1.68 0 0 0 5 7.686v1.6a8.007 8.007 0 0 0 .24.791l-1.428.857A1.68 1.68 0 0 0 3 12.4v.2a1.68 1.68 0 0 0 .812 1.446l1.428.857c.074.27.162.535.264.791v.16A1.68 1.68 0 0 0 7.184 17.4h.36a1.68 1.68 0 0 0 1.68-1.686v-.16c.243.156.496.299.757.428l.857 1.428A1.68 1.68 0 0 0 12.3 18.2h.4a1.68 1.68 0 0 0 1.463-.778l.857-1.428c.261-.13.514-.272.757-.428v.16A1.68 1.68 0 0 0 17.457 17.4h.36a1.68 1.68 0 0 0 1.68-1.686v-.16c.102-.256.19-.52.264-.791l1.428-.857A1.68 1.68 0 0 0 22 12.6v-.2a1.68 1.68 0 0 0-.812-1.466z"/></svg>',
      },
      {
        key: 'privacy',
        label: 'Privacy & Visibility',
        icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
      },
    ],
  },
  {
    label: 'Payments',
    items: [
      {
        key: 'billing',
        label: 'Billing & Payments',
        icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
      },
    ],
  },
  {
    label: 'Bookings',
    items: [
      {
        key: 'booking',
        label: 'Booking Preferences',
        icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        roles: ['tourist'],
      },
      {
        key: 'listings',
        label: 'Listing Settings',
        icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
        roles: ['agency', 'provider'],
      },
    ],
  },
  {
    label: 'Integrations',
    items: [
      {
        key: 'integrations',
        label: 'Connected Apps',
        icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
        badge: '3',
      },
    ],
  },
])

const sectionMap = {
  appearance:   SettingsAppearance,
  privacy:      SettingsPrivacy,
  billing:      SettingsBilling,
  booking:      SettingsBooking,
  listings:     SettingsListings,
  integrations: SettingsIntegrations,
}

const currentComponent = computed(() => sectionMap[activeSection.value])

function onSaved(msg) {
  toastMsg.value = msg || 'Settings saved!'
  setTimeout(() => toastMsg.value = null, 3000)
}
</script>

<style scoped>
.settings-page {
  min-height: 100vh;
  background: var(--gray-50, #F9F9F9);
  padding: 48px var(--section-px, 5%);
  font-family: 'DM Sans', sans-serif;
}

.settings-container {
  max-width: 1080px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 256px 1fr;
  gap: 28px;
  align-items: start;
}

/* ── Sidebar ── */
.settings-sidebar {
  background: #fff;
  border-radius: var(--radius, 16px);
  box-shadow: var(--shadow, 0 4px 24px rgba(45,49,66,.07));
  padding: 28px 20px;
  position: sticky;
  top: 100px;
}

.sidebar-head { padding: 0 8px 20px; border-bottom: 1px solid var(--gray-200, #E8E8E8); margin-bottom: 16px; }
.sidebar-title { font-family: 'Fraunces', serif; font-size: 22px; color: var(--indigo, #2D3142); margin: 0 0 4px; }
.sidebar-sub   { font-size: 13px; color: var(--gray-600, #666); margin: 0; }

.settings-nav  { display: flex; flex-direction: column; gap: 20px; }
.nav-group     { display: flex; flex-direction: column; gap: 2px; }
.nav-group-label {
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .08em; color: var(--gray-400, #AAA);
  padding: 0 12px; margin-bottom: 4px;
}

.settings-nav-btn {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px;
  border: none; background: transparent;
  border-radius: var(--radius-sm, 8px);
  cursor: pointer; font-family: 'DM Sans', sans-serif;
  font-size: 14px; color: var(--gray-600, #666);
  text-align: left; width: 100%;
  transition: all var(--transition, .22s);
  position: relative;
}
.settings-nav-btn:hover  { background: var(--gray-100, #F2F2F2); color: var(--indigo); }
.settings-nav-btn.active { background: var(--teal-lt, rgba(46,196,182,.10)); color: var(--teal, #2EC4B6); font-weight: 600; }
.nav-icon { display: flex; align-items: center; flex-shrink: 0; opacity: .7; }
.settings-nav-btn.active .nav-icon { opacity: 1; }
.nav-badge {
  margin-left: auto; font-size: 11px; font-weight: 700;
  background: var(--coral, #FF5A5F); color: #fff;
  padding: 1px 7px; border-radius: 50px;
}

/* ── Main ── */
.settings-main {
  background: #fff;
  border-radius: var(--radius, 16px);
  box-shadow: var(--shadow, 0 4px 24px rgba(45,49,66,.07));
  min-height: 500px;
  overflow: hidden;
}

/* ── Transitions ── */
.fade-slide-enter-active,
.fade-slide-leave-active { transition: opacity .18s ease, transform .18s ease; }
.fade-slide-enter-from   { opacity: 0; transform: translateX(12px); }
.fade-slide-leave-to     { opacity: 0; transform: translateX(-12px); }

/* ── Toast ── */
.global-toast {
  position: fixed; bottom: 32px; right: 32px; z-index: 9999;
  display: flex; align-items: center; gap: 10px;
  padding: 14px 20px;
  background: var(--indigo, #2D3142); color: #fff;
  border-radius: var(--radius-sm, 8px);
  font-size: 14px; font-weight: 600;
  box-shadow: var(--shadow-md);
}
.toast-enter-active, .toast-leave-active { transition: opacity .25s, transform .25s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(12px); }

@media (max-width: 820px) {
  .settings-container { grid-template-columns: 1fr; }
  .settings-sidebar { position: static; }
  .settings-nav { flex-direction: row; flex-wrap: wrap; gap: 8px; }
  .nav-group { flex-direction: row; flex-wrap: wrap; align-items: center; gap: 4px; }
  .nav-group-label { display: none; }
  .settings-nav-btn { width: auto; flex: 0 0 auto; }
}
</style>