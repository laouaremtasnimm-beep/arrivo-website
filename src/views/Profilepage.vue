<template>
  <div class="profile-page">

    <NavBar />   <!-- ← added -->


    <div class="profile-container">

      <!-- Left Sidebar -->
      <aside class="profile-sidebar">
        <div class="avatar-block">
          <div class="avatar-wrap">
            <img v-if="avatarPreview" :src="avatarPreview" class="avatar-img" alt="Avatar"/>
            <div v-else class="avatar-initials">{{ initials }}</div>
            <label class="avatar-upload-btn" title="Change photo">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
              </svg>
              <input type="file" accept="image/*" @change="handleAvatarChange" hidden />
            </label>
          </div>
          <h2 class="sidebar-name">{{ user.name }}</h2>
          <span class="role-badge" :class="`role-${user.role}`">{{ roleLabel }}</span>
          <p class="sidebar-email">{{ user.email }}</p>
        </div>

        <!-- Sidebar Nav -->
        <nav class="sidebar-nav">
          <button
            v-for="tab in visibleTabs"
            :key="tab.key"
            class="sidebar-nav-btn"
            :class="{ active: activeTab === tab.key }"
            @click="activeTab = tab.key"
          >
            <span class="nav-icon" v-html="tab.icon"></span>
            {{ tab.label }}
          </button>
        </nav>

        <!-- Quick Stats -->
        <div class="sidebar-stats">
          <div class="stat-item" v-for="s in sidebarStats" :key="s.label">
            <span class="stat-value">{{ s.value }}</span>
            <span class="stat-label">{{ s.label }}</span>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="profile-main">

        <!-- ── TAB: Personal Info ── -->
        <section v-show="activeTab === 'personal'" class="tab-section">
          <div class="section-header">
            <div>
              <h3 class="section-title">Personal Information</h3>
              <p class="section-sub">Manage your name, bio, and contact details</p>
            </div>
            <button v-if="!editingPersonal" class="btn-outline" @click="startEdit('personal')">Edit Profile</button>
            <div v-else class="btn-group">
              <button class="btn-ghost" @click="cancelEdit('personal')">Cancel</button>
              <button class="btn-primary" @click="saveEdit('personal')" :disabled="saving">
                <span v-if="saving" class="spinner"></span>
                {{ saving ? 'Saving…' : 'Save Changes' }}
              </button>
            </div>
          </div>

          <div class="info-grid">
            <div class="form-field">
              <label>Full Name</label>
              <input v-if="editingPersonal" v-model="draft.name" class="field-input" placeholder="Your full name" />
              <p v-else class="field-value">{{ user.name }}</p>
            </div>
            <div class="form-field">
              <label>Email Address</label>
              <input v-if="editingPersonal" v-model="draft.email" class="field-input" type="email" placeholder="email@example.com" />
              <p v-else class="field-value">{{ user.email }}</p>
            </div>
            <div class="form-field">
              <label>Phone</label>
              <input v-if="editingPersonal" v-model="draft.phone" class="field-input" placeholder="+1 (555) 000-0000" />
              <p v-else class="field-value">{{ user.phone || '—' }}</p>
            </div>
            <div class="form-field">
              <label>Location</label>
              <input v-if="editingPersonal" v-model="draft.location" class="field-input" placeholder="City, Country" />
              <p v-else class="field-value">{{ user.location || '—' }}</p>
            </div>
            <div class="form-field full-width">
              <label>Bio</label>
              <textarea v-if="editingPersonal" v-model="draft.bio" class="field-input field-textarea" rows="3" placeholder="Tell us a little about yourself…"></textarea>
              <p v-else class="field-value">{{ user.bio || 'No bio yet.' }}</p>
            </div>
          </div>

          <div v-if="savedMessage === 'personal'" class="save-toast">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Profile updated successfully!
          </div>
        </section>

        <!-- ── TAB: Company (agency/provider only) ── -->
        <section v-show="activeTab === 'company'" class="tab-section">
          <div class="section-header">
            <div>
              <h3 class="section-title">{{ user.role === 'agency' ? 'Agency' : 'Provider' }} Profile</h3>
              <p class="section-sub">Public-facing business details shown on your listings</p>
            </div>
            <button v-if="!editingCompany" class="btn-outline" @click="startEdit('company')">Edit</button>
            <div v-else class="btn-group">
              <button class="btn-ghost" @click="cancelEdit('company')">Cancel</button>
              <button class="btn-primary" @click="saveEdit('company')" :disabled="saving">
                <span v-if="saving" class="spinner"></span>
                {{ saving ? 'Saving…' : 'Save Changes' }}
              </button>
            </div>
          </div>

          <div class="info-grid">
            <div class="form-field">
              <label>{{ user.role === 'agency' ? 'Agency' : 'Business' }} Name</label>
              <input v-if="editingCompany" v-model="draft.company" class="field-input" placeholder="Company name" />
              <p v-else class="field-value">{{ user.company || '—' }}</p>
            </div>
            <div class="form-field">
              <label>Website</label>
              <input v-if="editingCompany" v-model="draft.website" class="field-input" placeholder="https://yoursite.com" />
              <p v-else class="field-value">
                <a v-if="user.website" :href="user.website" target="_blank" class="link-teal">{{ user.website }}</a>
                <span v-else>—</span>
              </p>
            </div>
            <div class="form-field">
              <label>License / Reg. No.</label>
              <input v-if="editingCompany" v-model="draft.license" class="field-input" placeholder="e.g. TA-00123" />
              <p v-else class="field-value">{{ user.license || '—' }}</p>
            </div>
            <div class="form-field">
              <label>Years in Operation</label>
              <input v-if="editingCompany" v-model.number="draft.years" class="field-input" type="number" min="0" />
              <p v-else class="field-value">{{ user.years ? user.years + ' years' : '—' }}</p>
            </div>
            <div class="form-field full-width">
              <label>Business Description</label>
              <textarea v-if="editingCompany" v-model="draft.bio" class="field-input field-textarea" rows="4" placeholder="Describe your services…"></textarea>
              <p v-else class="field-value">{{ user.bio || 'No description yet.' }}</p>
            </div>
          </div>

          <div class="verify-banner">
            <div class="verify-icon" :class="user.verified ? 'verified' : 'unverified'">
              <svg v-if="user.verified" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
              <p class="verify-title">{{ user.verified ? 'Verified Business' : 'Not Yet Verified' }}</p>
              <p class="verify-sub">{{ user.verified ? 'Your business identity has been confirmed by Voyago.' : 'Submit your documents to get the verified badge.' }}</p>
            </div>
            <button v-if="!user.verified" class="btn-outline" @click="requestVerification">Request Verification</button>
            <span v-else class="verified-tag">✓ Verified</span>
          </div>

          <div v-if="savedMessage === 'company'" class="save-toast">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Business profile updated!
          </div>
        </section>

        <!-- ── TAB: Preferences ── -->
        <section v-show="activeTab === 'preferences'" class="tab-section">
          <div class="section-header">
            <div>
              <h3 class="section-title">Preferences</h3>
              <p class="section-sub">Customize your Voyago experience</p>
            </div>
          </div>

          <div class="prefs-list">
            <div class="pref-row" v-for="pref in preferences" :key="pref.key">
              <div class="pref-info">
                <span class="pref-label">{{ pref.label }}</span>
                <span class="pref-desc">{{ pref.desc }}</span>
              </div>
              <button
                class="toggle-btn"
                :class="{ on: userPrefs[pref.key] }"
                @click="userPrefs[pref.key] = !userPrefs[pref.key]"
                :aria-pressed="userPrefs[pref.key]"
              >
                <span class="toggle-knob"></span>
              </button>
            </div>
          </div>

          <div class="info-grid" style="margin-top: 28px;">
            <div class="form-field">
              <label>Currency</label>
              <select v-model="userPrefs.currency" class="field-input field-select">
                <option value="USD">USD — US Dollar</option>
                <option value="EUR">EUR — Euro</option>
                <option value="GBP">GBP — British Pound</option>
                <option value="DZD">DZD — Algerian Dinar</option>
                <option value="MAD">MAD — Moroccan Dirham</option>
              </select>
            </div>
            <div class="form-field">
              <label>Language</label>
              <select v-model="userPrefs.language" class="field-input field-select">
                <option value="en">English</option>
                <option value="fr">Français</option>
                <option value="ar">العربية</option>
                <option value="es">Español</option>
              </select>
            </div>
          </div>

          <button class="btn-primary" style="margin-top: 24px;" @click="savePreferences">Save Preferences</button>
          <div v-if="savedMessage === 'prefs'" class="save-toast">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Preferences saved!
          </div>
        </section>

        <!-- ── TAB: Security ── -->
        <section v-show="activeTab === 'security'" class="tab-section">
          <div class="section-header">
            <div>
              <h3 class="section-title">Security</h3>
              <p class="section-sub">Manage your password and account security</p>
            </div>
          </div>

          <div class="info-grid">
            <div class="form-field">
              <label>Current Password</label>
              <div class="input-pw-wrap">
                <input :type="showPw.current ? 'text' : 'password'" v-model="pwForm.current" class="field-input" placeholder="Enter current password" />
                <button class="pw-toggle" @click="showPw.current = !showPw.current">
                  <svg v-if="!showPw.current" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
            </div>
            <div class="form-field">
              <label>New Password</label>
              <div class="input-pw-wrap">
                <input :type="showPw.newPw ? 'text' : 'password'" v-model="pwForm.newPw" class="field-input" placeholder="New password" @input="calcStrength" />
                <button class="pw-toggle" @click="showPw.newPw = !showPw.newPw">
                  <svg v-if="!showPw.newPw" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
              <div v-if="pwForm.newPw" class="strength-wrap">
                <div class="strength-bar">
                  <div class="strength-fill" :style="{ width: pwStrength.pct + '%', background: pwStrength.color }"></div>
                </div>
                <span class="strength-label" :style="{ color: pwStrength.color }">{{ pwStrength.label }}</span>
              </div>
            </div>
            <div class="form-field">
              <label>Confirm New Password</label>
              <div class="input-pw-wrap">
                <input :type="showPw.confirm ? 'text' : 'password'" v-model="pwForm.confirm" class="field-input" placeholder="Confirm new password" />
                <button class="pw-toggle" @click="showPw.confirm = !showPw.confirm">
                  <svg v-if="!showPw.confirm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
              <p v-if="pwForm.confirm && pwForm.newPw !== pwForm.confirm" class="field-error">Passwords do not match</p>
            </div>
          </div>

          <button class="btn-primary" style="margin-top: 8px;" @click="changePassword" :disabled="!canChangePassword">
            Update Password
          </button>
          <div v-if="savedMessage === 'password'" class="save-toast">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Password updated successfully!
          </div>
          <div v-if="pwError" class="save-toast error-toast">{{ pwError }}</div>

          <div class="divider"></div>
          <h4 class="subsection-title">Two-Factor Authentication</h4>
          <div class="twofa-row">
            <div>
              <p class="pref-label">Authenticator App</p>
              <p class="pref-desc">Add an extra layer of security to your account</p>
            </div>
            <button class="btn-outline" @click="toggle2FA">{{ user.twoFA ? 'Disable 2FA' : 'Enable 2FA' }}</button>
          </div>

          <div class="divider"></div>
          <h4 class="subsection-title">Active Sessions</h4>
          <div class="sessions-list">
            <div class="session-row" v-for="s in sessions" :key="s.id">
              <div class="session-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
              </div>
              <div class="session-info">
                <span class="session-device">{{ s.device }}</span>
                <span class="session-detail">{{ s.location }} · {{ s.time }}</span>
              </div>
              <span v-if="s.current" class="current-tag">Current</span>
              <button v-else class="btn-ghost btn-sm" @click="revokeSession(s.id)">Revoke</button>
            </div>
          </div>
        </section>

        <!-- ── TAB: Notifications ── -->
        <section v-show="activeTab === 'notifications'" class="tab-section">
          <div class="section-header">
            <div>
              <h3 class="section-title">Notifications</h3>
              <p class="section-sub">Choose what you want to be notified about</p>
            </div>
          </div>

          <div class="notif-groups">
            <div class="notif-group" v-for="group in notifGroups" :key="group.key">
              <h4 class="notif-group-title">{{ group.title }}</h4>
              <div class="prefs-list">
                <div class="pref-row" v-for="item in group.items" :key="item.key">
                  <div class="pref-info">
                    <span class="pref-label">{{ item.label }}</span>
                  </div>
                  <div class="notif-channels">
                    <label class="channel-check" v-for="ch in ['Email','Push','SMS']" :key="ch">
                      <input type="checkbox" v-model="notifSettings[item.key][ch.toLowerCase()]" />
                      <span>{{ ch }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <button class="btn-primary" style="margin-top: 24px;" @click="saveNotifs">Save Notification Settings</button>
          <div v-if="savedMessage === 'notifs'" class="save-toast">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Notification settings saved!
          </div>
        </section>

        <!-- ── TAB: Danger Zone ── -->
        <section v-show="activeTab === 'danger'" class="tab-section">
          <div class="section-header">
            <div>
              <h3 class="section-title">Danger Zone</h3>
              <p class="section-sub">Irreversible actions — proceed with caution</p>
            </div>
          </div>

          <div class="danger-card">
            <div>
              <p class="danger-title">Export My Data</p>
              <p class="danger-desc">Download a copy of all your personal data stored on Voyago.</p>
            </div>
            <button class="btn-outline" @click="exportData">Export Data</button>
          </div>

          <div class="danger-card">
            <div>
              <p class="danger-title">Deactivate Account</p>
              <p class="danger-desc">Temporarily disable your account. You can reactivate it at any time.</p>
            </div>
            <button class="btn-outline danger-btn" @click="deactivateAccount">Deactivate</button>
          </div>

          <div class="danger-card danger-card-red">
            <div>
              <p class="danger-title">Delete Account</p>
              <p class="danger-desc">Permanently delete your account and all associated data. This cannot be undone.</p>
            </div>
            <button class="btn-danger" @click="confirmDelete = true">Delete Account</button>
          </div>

          <Teleport to="body">
            <div v-if="confirmDelete" class="modal-overlay" @click.self="confirmDelete = false">
              <div class="confirm-modal">
                <div class="confirm-icon">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </div>
                <h4>Delete Account?</h4>
                <p>This action is permanent and cannot be undone. All your data, listings, and booking history will be erased.</p>
                <p class="confirm-type-label">Type <strong>DELETE</strong> to confirm:</p>
                <input v-model="deleteConfirmText" class="field-input" placeholder="DELETE" />
                <div class="btn-group">
                  <button class="btn-ghost" @click="confirmDelete = false">Cancel</button>
                  <button class="btn-danger" :disabled="deleteConfirmText !== 'DELETE'" @click="deleteAccount">Yes, Delete</button>
                </div>
              </div>
            </div>
          </Teleport>
        </section>

      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth.js'

import NavBar        from '@/components/home/NavBar.vue'

const router = useRouter()
const { user: authUser, logout } = useAuth()

const user = reactive({
  ...authUser.value,
  phone: '+1 (555) 234-5678',
  location: 'Algiers, Algeria',
  bio: authUser.value?.role === 'tourist'
    ? 'Adventure seeker and travel enthusiast exploring the world one destination at a time.'
    : authUser.value?.role === 'agency'
    ? 'We craft unforgettable travel experiences across North Africa and the Mediterranean.'
    : 'Premium transport and service provider with 8 years of experience.',
  website: authUser.value?.role !== 'tourist' ? 'https://voyago.dz' : null,
  license: authUser.value?.role !== 'tourist' ? 'TA-007823' : null,
  years: authUser.value?.role !== 'tourist' ? 8 : null,
  verified: authUser.value?.role === 'agency',
  twoFA: false,
  joinDate: 'March 2023',
})

onMounted(async () => {
  const localUserStr = localStorage.getItem('user')
  if (localUserStr) {
    try {
      const localUser = JSON.parse(localUserStr)
      if (localUser && localUser.id) {
        const response = await fetch(`http://localhost/arrivo-website/backend/api/v1/profile.php?user_id=${localUser.id}`)
        const data = await response.json()
        if (data.profile) {
          user.name = `${data.profile.first_name} ${data.profile.last_name}`
          user.email = data.profile.email
          user.role = data.profile.role
          user.company = data.profile.company_name || ''
          user.verified = data.profile.is_verified == 1
        }
      }
    } catch (e) {
      console.error('Error fetching profile:', e)
    }
  }
})

const avatarPreview = ref(null)
function handleAvatarChange(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (ev) => { avatarPreview.value = ev.target.result }
  reader.readAsDataURL(file)
}

const initials = computed(() => {
  if (!user.name) return '?'
  return user.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

const roleLabel = computed(() => ({
  tourist: 'Traveler',
  agency: 'Travel Agency',
  provider: 'Service Provider',
}[user.role] || 'User'))

const allTabs = [
  { key: 'personal',      label: 'Personal Info',    icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       roles: ['tourist','agency','provider'] },
  { key: 'company',       label: 'Business Profile', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 roles: ['agency','provider'] },
  { key: 'preferences',   label: 'Preferences',      icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>', roles: ['tourist','agency','provider'] },
  { key: 'security',      label: 'Security',         icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 roles: ['tourist','agency','provider'] },
  { key: 'notifications', label: 'Notifications',    icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        roles: ['tourist','agency','provider'] },
  { key: 'danger',        label: 'Danger Zone',      icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           roles: ['tourist','agency','provider'] },
]

const visibleTabs = computed(() => allTabs.filter(t => t.roles.includes(user.role)))
const activeTab   = ref('personal')

const sidebarStats = computed(() => {
  if (user.role === 'agency')   return [{ value: '24', label: 'Packages' }, { value: '4.8★', label: 'Rating' }, { value: '312', label: 'Bookings' }]
  if (user.role === 'provider') return [{ value: '9',  label: 'Services' }, { value: '4.7★', label: 'Rating' }, { value: '187', label: 'Jobs Done' }]
  return [{ value: '7', label: 'Trips' }, { value: '3', label: 'Wishlist' }, { value: '12', label: 'Reviews' }]
})

const editingPersonal = ref(false)
const editingCompany  = ref(false)
const saving          = ref(false)
const savedMessage    = ref(null)
const draft           = reactive({})

function startEdit(section) {
  Object.assign(draft, { ...user })
  if (section === 'personal') editingPersonal.value = true
  else editingCompany.value = true
}
function cancelEdit(section) {
  if (section === 'personal') editingPersonal.value = false
  else editingCompany.value = false
}
function saveEdit(section) {
  saving.value = true
  
  const localUserStr = localStorage.getItem('user')
  let userId = null
  if (localUserStr) {
    const localUser = JSON.parse(localUserStr)
    userId = localUser.id
  }

  if (!userId) {
    saving.value = false
    return
  }

  const nameParts = (draft.name || '').split(' ')
  const firstName = nameParts[0] || ''
  const lastName = nameParts.slice(1).join(' ') || ''

  fetch('http://localhost/arrivo-website/backend/api/v1/profile.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      user_id: userId,
      first_name: firstName,
      last_name: lastName,
      company_name: draft.company || ''
    })
  })
  .then(res => res.json())
  .then(() => {
    Object.assign(user, { ...draft })
    saving.value = false
    if (section === 'personal') editingPersonal.value = false
    else editingCompany.value = false
    savedMessage.value = section
    setTimeout(() => savedMessage.value = null, 3000)
  })
  .catch(err => {
    console.error('Error saving profile:', err)
    saving.value = false
  })
}

const userPrefs = reactive({ emailMarketing: true, showProfile: true, activityStatus: false, dataSaving: false, currency: 'USD', language: 'en' })
const preferences = [
  { key: 'emailMarketing', label: 'Marketing Emails',  desc: 'Receive deals, offers, and travel inspiration' },
  { key: 'showProfile',    label: 'Public Profile',    desc: 'Allow others to view your profile and reviews' },
  { key: 'activityStatus', label: 'Activity Status',   desc: 'Show when you were last active' },
  { key: 'dataSaving',     label: 'Data Saving Mode',  desc: 'Load lower-resolution images to save bandwidth' },
]
function savePreferences() { savedMessage.value = 'prefs'; setTimeout(() => savedMessage.value = null, 3000) }

const pwForm     = reactive({ current: '', newPw: '', confirm: '' })
const showPw     = reactive({ current: false, newPw: false, confirm: false })
const pwError    = ref(null)
const pwStrength = reactive({ pct: 0, label: '', color: '' })

function calcStrength() {
  const p = pwForm.newPw; let score = 0
  if (p.length >= 8) score++
  if (/[A-Z]/.test(p)) score++
  if (/[0-9]/.test(p)) score++
  if (/[^A-Za-z0-9]/.test(p)) score++
  const levels = [{ pct: 25, label: 'Weak', color: '#FF5A5F' }, { pct: 50, label: 'Fair', color: '#f4a261' }, { pct: 75, label: 'Good', color: '#2EC4B6' }, { pct: 100, label: 'Strong', color: '#06d6a0' }]
  Object.assign(pwStrength, levels[score - 1] || { pct: 10, label: 'Too short', color: '#FF5A5F' })
}
const canChangePassword = computed(() => pwForm.current && pwForm.newPw && pwForm.confirm && pwForm.newPw === pwForm.confirm)
function changePassword() {
  pwError.value = null
  if (pwForm.current === 'wrongpassword') { pwError.value = 'Current password is incorrect.'; return }
  savedMessage.value = 'password'
  Object.assign(pwForm, { current: '', newPw: '', confirm: '' })
  setTimeout(() => savedMessage.value = null, 3000)
}
function toggle2FA() { user.twoFA = !user.twoFA }

const sessions = ref([
  { id: 1, device: 'Chrome on Windows', location: 'Algiers, DZ', time: 'Active now', current: true },
  { id: 2, device: 'Safari on iPhone',  location: 'Algiers, DZ', time: '2 hours ago', current: false },
  { id: 3, device: 'Firefox on macOS',  location: 'Paris, FR',   time: '5 days ago',  current: false },
])
function revokeSession(id) { sessions.value = sessions.value.filter(s => s.id !== id) }

const notifGroups = [
  { key: 'bookings', title: 'Bookings & Trips', items: [{ key: 'booking_confirm', label: 'Booking Confirmation' }, { key: 'booking_reminder', label: 'Trip Reminders' }, { key: 'booking_cancel', label: 'Cancellation Alerts' }] },
  { key: 'messages', title: 'Messages',         items: [{ key: 'msg_new', label: 'New Messages' }, { key: 'msg_reply', label: 'Message Replies' }] },
  { key: 'promo',    title: 'Promotions',       items: [{ key: 'promo_deals', label: 'Special Deals' }, { key: 'promo_news', label: 'Voyago News & Updates' }] },
]
const notifSettings = reactive(Object.fromEntries(notifGroups.flatMap(g => g.items).map(i => [i.key, { email: true, push: true, sms: false }])))
function saveNotifs() { savedMessage.value = 'notifs'; setTimeout(() => savedMessage.value = null, 3000) }

const confirmDelete     = ref(false)
const deleteConfirmText = ref('')
function exportData() {
  const data = JSON.stringify({ user, prefs: userPrefs }, null, 2)
  const blob = new Blob([data], { type: 'application/json' })
  const url  = URL.createObjectURL(blob)
  const a    = document.createElement('a')
  a.href = url; a.download = 'voyago-data.json'; a.click()
  URL.revokeObjectURL(url)
}
function deactivateAccount() { alert('Account deactivated. (Mock — no backend)') }
function deleteAccount() { confirmDelete.value = false; logout(); router.push('/') }
function requestVerification() { alert('Verification request submitted! Our team will contact you within 2–3 business days. (Mock)') }
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  background: var(--gray-50, #F9F9F9);
  padding: 48px var(--section-px, 5%);
  padding-top: calc(68px + 48px);   /* navbar height + original top padding */
  font-family: 'DM Sans', sans-serif;
}

.profile-container {
  max-width: 1100px; margin: 0 auto;
  display: grid; grid-template-columns: 280px 1fr;
  gap: 28px; align-items: start;
}

/* ── Sidebar ── */
.profile-sidebar {
  background: #fff; border-radius: var(--radius, 16px);
  box-shadow: var(--shadow, 0 4px 24px rgba(45,49,66,.07));
  padding: 32px 24px; position: sticky; top: 100px;
}
.avatar-block { text-align: center; padding-bottom: 24px; border-bottom: 1px solid var(--gray-200); margin-bottom: 20px; }
.avatar-wrap  { position: relative; display: inline-block; margin-bottom: 14px; }
.avatar-img, .avatar-initials { width: 88px; height: 88px; border-radius: 50%; display: block; }
.avatar-img      { object-fit: cover; }
.avatar-initials { background: var(--teal-lt); color: var(--teal); font-size: 28px; font-weight: 700; display: flex; align-items: center; justify-content: center; font-family: 'Fraunces', serif; }
.avatar-upload-btn { position: absolute; bottom: 0; right: 0; width: 28px; height: 28px; background: var(--coral); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background var(--transition); }
.avatar-upload-btn:hover { background: var(--coral-dk); }
.sidebar-name  { font-family: 'Fraunces', serif; font-size: 20px; color: var(--indigo); margin: 0 0 6px; }
.role-badge    { display: inline-block; padding: 3px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; letter-spacing: .02em; margin-bottom: 6px; }
.role-tourist  { background: var(--teal-lt); color: var(--teal); }
.role-agency   { background: var(--coral-lt); color: var(--coral); }
.role-provider { background: rgba(45,49,66,.08); color: var(--indigo); }
.sidebar-email { font-size: 13px; color: var(--gray-600); margin: 0; }
.sidebar-nav   { display: flex; flex-direction: column; gap: 4px; }
.sidebar-nav-btn { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: none; background: transparent; border-radius: var(--radius-sm); cursor: pointer; font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--gray-600); text-align: left; transition: all var(--transition); width: 100%; }
.sidebar-nav-btn:hover  { background: var(--gray-100); color: var(--indigo); }
.sidebar-nav-btn.active { background: var(--teal-lt); color: var(--teal); font-weight: 600; }
.nav-icon { display: flex; align-items: center; opacity: .75; }
.sidebar-nav-btn.active .nav-icon { opacity: 1; }
.sidebar-stats { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--gray-200); }
.stat-item  { text-align: center; }
.stat-value { display: block; font-size: 18px; font-weight: 700; color: var(--indigo); font-family: 'Fraunces', serif; }
.stat-label { font-size: 11px; color: var(--gray-600); }

/* ── Main ── */
.profile-main { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow); min-height: 560px; overflow: hidden; }
.tab-section   { padding: 36px 40px; }
.section-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; gap: 16px; }
.section-title  { font-family: 'Fraunces', serif; font-size: 22px; color: var(--indigo); margin: 0 0 4px; }
.section-sub    { font-size: 14px; color: var(--gray-600); margin: 0; }
.subsection-title { font-size: 16px; font-weight: 600; color: var(--indigo); margin: 0 0 16px; }

.info-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-field { display: flex; flex-direction: column; gap: 6px; }
.full-width { grid-column: 1 / -1; }
.form-field label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--gray-600); }
.field-value  { font-size: 15px; color: var(--indigo); margin: 0; min-height: 22px; }
.field-input  { padding: 10px 14px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); font-size: 14px; font-family: 'DM Sans', sans-serif; color: var(--indigo); outline: none; transition: border-color var(--transition); background: #fff; width: 100%; box-sizing: border-box; }
.field-input:focus { border-color: var(--teal); }
.field-textarea { resize: vertical; min-height: 90px; }
.field-select   { cursor: pointer; }
.field-error    { font-size: 12px; color: var(--coral); margin: 2px 0 0; }

.btn-primary  { padding: 10px 22px; background: var(--coral); color: #fff; border: none; border-radius: 50px; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background var(--transition); display: inline-flex; align-items: center; gap: 8px; }
.btn-primary:hover    { background: var(--coral-dk); }
.btn-primary:disabled { opacity: .55; cursor: not-allowed; }
.btn-outline  { padding: 9px 20px; background: transparent; color: var(--indigo); border: 1.5px solid var(--gray-200); border-radius: 50px; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all var(--transition); }
.btn-outline:hover { border-color: var(--indigo); background: var(--gray-50); }
.btn-ghost    { padding: 9px 18px; background: transparent; color: var(--gray-600); border: none; border-radius: 50px; font-size: 14px; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: color var(--transition); }
.btn-ghost:hover { color: var(--indigo); }
.btn-sm       { padding: 6px 14px; font-size: 13px; }
.btn-danger   { padding: 10px 22px; background: #FF5A5F; color: #fff; border: none; border-radius: 50px; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background var(--transition); }
.btn-danger:hover    { background: #e04045; }
.btn-danger:disabled { opacity: .45; cursor: not-allowed; }
.btn-group    { display: flex; gap: 8px; align-items: center; }
.spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

.save-toast  { display: inline-flex; align-items: center; gap: 8px; margin-top: 16px; padding: 10px 16px; background: rgba(46,196,182,.12); color: var(--teal-dk); border-radius: var(--radius-sm); font-size: 14px; font-weight: 600; animation: fadeIn .3s ease; }
.error-toast { background: rgba(255,90,95,.12); color: var(--coral); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }

.prefs-list { display: flex; flex-direction: column; gap: 0; }
.pref-row   { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid var(--gray-100); }
.pref-row:last-child { border-bottom: none; }
.pref-info  { display: flex; flex-direction: column; gap: 3px; }
.pref-label { font-size: 14px; font-weight: 600; color: var(--indigo); }
.pref-desc  { font-size: 13px; color: var(--gray-600); }
.toggle-btn { width: 44px; height: 24px; background: var(--gray-200); border: none; border-radius: 12px; position: relative; cursor: pointer; transition: background .25s; flex-shrink: 0; }
.toggle-btn.on { background: var(--teal); }
.toggle-knob   { position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; background: #fff; border-radius: 50%; transition: transform .25s; box-shadow: 0 1px 4px rgba(0,0,0,.18); }
.toggle-btn.on .toggle-knob { transform: translateX(20px); }

.input-pw-wrap { position: relative; }
.input-pw-wrap .field-input { padding-right: 42px; }
.pw-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400); padding: 4px; display: flex; align-items: center; }
.pw-toggle:hover { color: var(--indigo); }
.strength-wrap { display: flex; align-items: center; gap: 10px; margin-top: 6px; }
.strength-bar  { flex: 1; height: 4px; background: var(--gray-200); border-radius: 4px; overflow: hidden; }
.strength-fill { height: 100%; border-radius: 4px; transition: width .35s, background .35s; }
.strength-label { font-size: 12px; font-weight: 600; white-space: nowrap; }

.verify-banner { display: flex; align-items: center; gap: 16px; padding: 16px 20px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); margin-top: 24px; }
.verify-icon.verified   { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(46,196,182,.12); color: var(--teal); }
.verify-icon.unverified { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(255,90,95,.10); color: var(--coral); }
.verify-title { font-size: 14px; font-weight: 700; color: var(--indigo); margin: 0 0 2px; }
.verify-sub   { font-size: 13px; color: var(--gray-600); margin: 0; }
.verified-tag { font-size: 13px; font-weight: 700; color: var(--teal); white-space: nowrap; }

.twofa-row { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; gap: 16px; }
.sessions-list { display: flex; flex-direction: column; gap: 8px; }
.session-row   { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: var(--gray-50); border-radius: var(--radius-sm); }
.session-icon  { color: var(--gray-600); display: flex; }
.session-info  { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.session-device { font-size: 14px; font-weight: 600; color: var(--indigo); }
.session-detail { font-size: 12px; color: var(--gray-600); }
.current-tag    { font-size: 12px; font-weight: 700; color: var(--teal); background: var(--teal-lt); padding: 3px 10px; border-radius: 50px; }

.divider { height: 1px; background: var(--gray-200); margin: 28px 0; }

.notif-groups      { display: flex; flex-direction: column; gap: 28px; }
.notif-group-title { font-size: 14px; font-weight: 700; color: var(--indigo); margin: 0 0 8px; text-transform: uppercase; letter-spacing: .06em; }
.notif-channels    { display: flex; gap: 16px; }
.channel-check     { display: flex; align-items: center; gap: 5px; font-size: 13px; color: var(--gray-600); cursor: pointer; }
.channel-check input { accent-color: var(--teal); cursor: pointer; }

.danger-card       { display: flex; align-items: center; justify-content: space-between; padding: 20px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); margin-bottom: 12px; gap: 16px; }
.danger-card-red   { border-color: rgba(255,90,95,.3); background: rgba(255,90,95,.03); }
.danger-title      { font-size: 14px; font-weight: 700; color: var(--indigo); margin: 0 0 4px; }
.danger-desc       { font-size: 13px; color: var(--gray-600); margin: 0; }
.danger-btn        { border-color: rgba(255,90,95,.5) !important; color: var(--coral) !important; }
.danger-btn:hover  { background: rgba(255,90,95,.06) !important; }

.modal-overlay { position: fixed; inset: 0; z-index: 9999; background: rgba(45,49,66,.55); display: flex; align-items: center; justify-content: center; padding: 24px; }
.confirm-modal { background: #fff; border-radius: var(--radius); padding: 36px 32px; width: 100%; max-width: 420px; box-shadow: var(--shadow-lg); text-align: center; }
.confirm-icon  { width: 64px; height: 64px; background: rgba(255,90,95,.1); color: var(--coral); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
.confirm-modal h4 { font-family: 'Fraunces', serif; font-size: 22px; color: var(--indigo); margin: 0 0 10px; }
.confirm-modal p  { font-size: 14px; color: var(--gray-600); margin: 0 0 16px; line-height: 1.6; }
.confirm-type-label  { font-size: 13px !important; font-weight: 600; color: var(--indigo) !important; }
.confirm-modal .btn-group { justify-content: center; margin-top: 20px; }
.confirm-modal .field-input { text-align: center; letter-spacing: .1em; font-weight: 700; }
.link-teal       { color: var(--teal); text-decoration: none; }
.link-teal:hover { text-decoration: underline; }

@media (max-width: 840px) {
  .profile-container  { grid-template-columns: 1fr; }
  .profile-sidebar    { position: static; }
  .tab-section        { padding: 28px 24px; }
  .info-grid          { grid-template-columns: 1fr; }
  .sidebar-nav        { flex-direction: row; flex-wrap: wrap; }
  .sidebar-nav-btn    { flex: 1 0 auto; justify-content: center; }
  .section-header     { flex-direction: column; }
  .notif-channels     { flex-direction: column; gap: 8px; }
}
</style>