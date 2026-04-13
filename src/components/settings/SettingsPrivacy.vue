<template>
  <SettingsSection
    title="Privacy & Data"
    subtitle="Control how your data is used and how others can reach you"
  >

    <!-- ── Recommendations & Data ──────────────────── -->
    <div class="block-label">Recommendations & Data</div>

    <SettingsRow
      label="Personalised Recommendations"
      desc="Allow Voyago to use your browsing and booking activity to suggest relevant destinations and packages"
    >
      <ToggleSwitch v-model="privacy.personalization" />
    </SettingsRow>

    <SettingsRow
      label="Analytics & Usage Data"
      desc="Share anonymous usage data to help us improve the platform"
    >
      <ToggleSwitch v-model="privacy.analytics" />
    </SettingsRow>

    <SettingsRow
      label="Third-Party Data Sharing"
      desc="Allow sharing anonymised data with trusted travel partners for relevant offers"
    >
      <ToggleSwitch v-model="privacy.thirdParty" />
    </SettingsRow>

    <div class="divider" />

    <!-- ── Discoverability (agencies + providers only) ── -->
    <template v-if="role === 'agency' || role === 'provider'">
      <div class="block-label">Discoverability</div>

      <SettingsRow
        label="Appear in Search Results"
        desc="Allow your listings to appear in Voyago search and category pages"
      >
        <ToggleSwitch v-model="privacy.searchIndexing" />
      </SettingsRow>

      <SettingsRow
        label="Show Reviews Publicly"
        desc="Display guest reviews on your listing and profile pages"
      >
        <ToggleSwitch v-model="privacy.publicReviews" />
      </SettingsRow>

      <div class="divider" />
    </template>

    <!-- ── Communications ──────────────────────────── -->
    <div class="block-label">Communications</div>

    <SettingsRow
      label="Direct Messages"
      desc="Allow other Voyago users to contact you directly"
    >
      <ToggleSwitch v-model="privacy.allowDMs" />
    </SettingsRow>

    <SettingsRow
      label="Promotional Emails"
      desc="Receive curated offers, destination highlights and travel inspiration"
    >
      <ToggleSwitch v-model="privacy.promoEmails" />
    </SettingsRow>

    <SettingsRow
      label="Booking & Activity Notifications"
      desc="Receive email updates for bookings, confirmations and account activity"
    >
      <ToggleSwitch v-model="privacy.activityEmails" />
    </SettingsRow>

    <div class="divider" />

    <!-- ── Your Data ────────────────────────────────── -->
    <div class="block-label">Your Data</div>

    <SettingsRow
      label="Download My Data"
      desc="Request a copy of all your Voyago data — bookings, reviews and account info"
    >
      <button class="btn-secondary" @click="requestDataExport">
        Request export
      </button>
    </SettingsRow>

    <SettingsRow
      label="Delete My Account"
      desc="Permanently remove your account and all associated data. This cannot be undone."
    >
      <button class="btn-danger" @click="showDeleteConfirm = true">
        Delete account
      </button>
    </SettingsRow>

    <!-- Delete confirmation inline -->
    <Transition name="slide-fade">
      <div class="delete-confirm" v-if="showDeleteConfirm">
        <p class="delete-confirm__text">
          Are you sure? This will permanently delete your account, bookings and reviews.
        </p>
        <div class="delete-confirm__actions">
          <button class="btn-secondary" @click="showDeleteConfirm = false">Cancel</button>
          <button class="btn-danger" @click="handleDeleteAccount">Yes, delete my account</button>
        </div>
      </div>
    </Transition>

    <div class="divider" />

    <div class="action-row">
      <button class="btn-primary" @click="save">Save Privacy Settings</button>
    </div>

  </SettingsSection>
</template>

<script setup>
import { reactive, ref } from 'vue'
import SettingsSection from './SettingsSection.vue'
import SettingsRow     from './SettingsRow.vue'
import ToggleSwitch    from './ToggleSwitch.vue'

const props = defineProps({
  role: { type: String, default: 'tourist' }, // 'tourist' | 'agency' | 'provider'
})

const emit = defineEmits(['saved', 'delete-account'])

const privacy = reactive({
  personalization: true,
  analytics:       true,
  thirdParty:      false,
  searchIndexing:  true,   // agency/provider only
  publicReviews:   true,   // agency/provider only
  allowDMs:        true,
  promoEmails:     true,
  activityEmails:  true,
})

const showDeleteConfirm = ref(false)

function requestDataExport() {
  emit('saved', 'Data export requested — you\'ll receive an email shortly.')
}

function handleDeleteAccount() {
  showDeleteConfirm.value = false
  emit('delete-account')
}

function save() {
  emit('saved', 'Privacy settings saved!')
}
</script>

<style scoped>
.block-label {
  font-size: 12px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--gray-600, #666); margin-bottom: 14px;
}
.divider { height: 1px; background: var(--gray-100, #F2F2F2); margin: 24px 0; }
.action-row { display: flex; justify-content: flex-end; margin-top: 8px; }

/* Buttons */
.btn-primary {
  padding: 10px 22px; background: var(--coral); color: #fff;
  border: none; border-radius: 50px; font-size: 14px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; cursor: pointer;
  transition: background var(--transition);
}
.btn-primary:hover { background: var(--coral-dk, #e04045); }

.btn-secondary {
  padding: 8px 18px; background: none;
  border: 1.5px solid var(--gray-200); border-radius: 50px;
  font-size: 13px; font-weight: 600; color: var(--gray-600);
  font-family: 'DM Sans', sans-serif; cursor: pointer;
  transition: all var(--transition);
}
.btn-secondary:hover { border-color: var(--indigo); color: var(--indigo); }

.btn-danger {
  padding: 8px 18px; background: none;
  border: 1.5px solid rgba(231,76,60,.35); border-radius: 50px;
  font-size: 13px; font-weight: 600; color: #e74c3c;
  font-family: 'DM Sans', sans-serif; cursor: pointer;
  transition: all var(--transition);
}
.btn-danger:hover { background: rgba(231,76,60,.07); border-color: #e74c3c; }

/* Delete confirmation */
.delete-confirm {
  margin-top: 12px; padding: 16px 20px;
  background: rgba(231,76,60,.05);
  border: 1.5px solid rgba(231,76,60,.2);
  border-radius: var(--radius-sm, 8px);
}
.delete-confirm__text {
  font-size: 13px; color: var(--indigo); line-height: 1.55; margin: 0 0 14px;
}
.delete-confirm__actions { display: flex; gap: 10px; justify-content: flex-end; }

/* Transition */
.slide-fade-enter-active, .slide-fade-leave-active { transition: opacity .2s ease, transform .2s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; transform: translateY(-6px); }
</style>