<template>
  <SettingsSection
    title="Privacy & Visibility"
    subtitle="Control who can see your information and how your data is used"
  >

    <!-- Profile visibility -->
    <div class="block-label">Profile Visibility</div>
    <div class="radio-group">
      <label
        v-for="opt in visibilityOptions"
        :key="opt.key"
        class="radio-card"
        :class="{ active: privacy.visibility === opt.key }"
      >
        <input type="radio" :value="opt.key" v-model="privacy.visibility" hidden />
        <div class="radio-dot" />
        <div>
          <p class="radio-label">{{ opt.label }}</p>
          <p class="radio-desc">{{ opt.desc }}</p>
        </div>
      </label>
    </div>

    <div class="divider" />

    <!-- Activity & data -->
    <div class="block-label">Activity & Data</div>

    <SettingsRow label="Show Online Status" desc="Let others see when you're active on Voyago">
      <ToggleSwitch v-model="privacy.onlineStatus" />
    </SettingsRow>

    <SettingsRow label="Show Reviews Publicly" desc="Display your reviews on destination and listing pages">
      <ToggleSwitch v-model="privacy.publicReviews" />
    </SettingsRow>

    <SettingsRow label="Personalized Recommendations" desc="Allow Voyago to use your activity for tailored suggestions">
      <ToggleSwitch v-model="privacy.personalization" />
    </SettingsRow>

    <SettingsRow label="Analytics & Usage Data" desc="Help us improve Voyago by sharing anonymous usage data">
      <ToggleSwitch v-model="privacy.analytics" />
    </SettingsRow>

    <SettingsRow label="Third-Party Data Sharing" desc="Allow sharing anonymized data with trusted travel partners">
      <ToggleSwitch v-model="privacy.thirdParty" />
    </SettingsRow>

    <div class="divider" />

    <!-- Search indexing -->
    <div class="block-label">Discoverability</div>

    <SettingsRow label="Appear in Search Results" desc="Allow your profile or listings to appear in Voyago search">
      <ToggleSwitch v-model="privacy.searchIndexing" />
    </SettingsRow>

    <SettingsRow label="Allow Direct Messages" desc="Let other users send you messages directly">
      <div class="select-wrap">
        <select v-model="privacy.dmPermission" class="field-select">
          <option value="everyone">Everyone</option>
          <option value="verified">Verified users only</option>
          <option value="none">No one</option>
        </select>
      </div>
    </SettingsRow>

    <div class="divider" />

    <!-- Blocked users -->
    <div class="block-label">Blocked Users</div>
    <div v-if="blockedUsers.length === 0" class="empty-state">
      No blocked users.
    </div>
    <div v-else class="blocked-list">
      <div v-for="u in blockedUsers" :key="u.id" class="blocked-row">
        <div class="blocked-avatar">{{ u.name[0] }}</div>
        <span class="blocked-name">{{ u.name }}</span>
        <button class="btn-unblock" @click="unblock(u.id)">Unblock</button>
      </div>
    </div>

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

const emit = defineEmits(['saved'])

const visibilityOptions = [
  { key: 'public',   label: 'Public',   desc: 'Anyone can view your profile' },
  { key: 'members',  label: 'Members',  desc: 'Only logged-in Voyago users' },
  { key: 'private',  label: 'Private',  desc: 'Only you can see your profile' },
]

const privacy = reactive({
  visibility:     'public',
  onlineStatus:   true,
  publicReviews:  true,
  personalization: true,
  analytics:       true,
  thirdParty:      false,
  searchIndexing:  true,
  dmPermission:    'everyone',
})

const blockedUsers = ref([
  { id: 1, name: 'John Doe' },
  { id: 2, name: 'Spam Agency' },
])

function unblock(id) {
  blockedUsers.value = blockedUsers.value.filter(u => u.id !== id)
}

function save() { emit('saved', 'Privacy settings saved!') }
</script>

<style scoped>
.block-label {
  font-size: 12px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--gray-600, #666); margin-bottom: 14px;
}
.divider { height: 1px; background: var(--gray-100, #F2F2F2); margin: 24px 0; }
.action-row { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }

/* Radio cards */
.radio-group { display: flex; flex-direction: column; gap: 10px; margin-bottom: 4px; }
.radio-card {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 14px 16px; border: 1.5px solid var(--gray-200, #E8E8E8);
  border-radius: var(--radius-sm, 8px); cursor: pointer;
  transition: border-color var(--transition, .22s);
}
.radio-card:hover  { border-color: var(--gray-400); }
.radio-card.active { border-color: var(--teal, #2EC4B6); background: var(--teal-lt, rgba(46,196,182,.06)); }

.radio-dot {
  width: 18px; height: 18px; border-radius: 50%; flex-shrink: 0; margin-top: 2px;
  border: 2px solid var(--gray-400);
  transition: border-color .2s, box-shadow .2s;
}
.radio-card.active .radio-dot {
  border-color: var(--teal);
  box-shadow: inset 0 0 0 4px var(--teal);
}
.radio-label { font-size: 14px; font-weight: 700; color: var(--indigo); margin: 0 0 2px; }
.radio-desc  { font-size: 13px; color: var(--gray-600); margin: 0; }

/* Select */
.select-wrap { position: relative; }
.field-select {
  padding: 8px 32px 8px 12px; border: 1.5px solid var(--gray-200);
  border-radius: var(--radius-sm); font-size: 13px; font-weight: 600;
  color: var(--indigo); background: #fff; cursor: pointer;
  font-family: 'DM Sans', sans-serif; outline: none;
  transition: border-color var(--transition);
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23666' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 10px center;
}
.field-select:focus { border-color: var(--teal); }

/* Blocked */
.empty-state { font-size: 14px; color: var(--gray-400); padding: 8px 0; }
.blocked-list { display: flex; flex-direction: column; gap: 8px; }
.blocked-row {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 14px; background: var(--gray-50);
  border-radius: var(--radius-sm);
}
.blocked-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--coral-lt, rgba(255,90,95,.1)); color: var(--coral);
  font-size: 13px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.blocked-name { flex: 1; font-size: 14px; font-weight: 600; color: var(--indigo); }
.btn-unblock {
  padding: 5px 14px; background: none; border: 1.5px solid var(--gray-200);
  border-radius: 50px; font-size: 13px; font-weight: 600; color: var(--gray-600);
  cursor: pointer; font-family: 'DM Sans', sans-serif; transition: all var(--transition);
}
.btn-unblock:hover { border-color: var(--teal); color: var(--teal); }

.btn-primary {
  padding: 10px 22px; background: var(--coral); color: #fff;
  border: none; border-radius: 50px; font-size: 14px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background var(--transition);
}
.btn-primary:hover { background: var(--coral-dk, #e04045); }
</style>