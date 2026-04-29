<template>
  <form class="auth-form" @submit.prevent="submit" novalidate>

    <div class="form-header">
      <h1 class="form-title">Create your account</h1>
      <p class="form-sub">Join thousands of travellers discovering the world through Voyago.</p>
    </div>

    <!-- Role selector is shown but agency/provider are pre-selected and highlighted
         when arriving from the partner pages -->
    <AccountTypeSelector v-model="form.role" />

    <div class="form-row">
      <FormInput
        v-model="form.firstName"
        label="First name"
        icon="👤"
        placeholder="Sarah"
        autocomplete="given-name"
        :error="errors.firstName"
      />
      <FormInput
        v-model="form.lastName"
        label="Last name"
        icon="👤"
        placeholder="Connor"
        autocomplete="family-name"
        :error="errors.lastName"
      />
    </div>

    <FormInput
      v-model="form.email"
      label="Email address"
      icon="✉️"
      type="email"
      placeholder="hello@example.com"
      autocomplete="email"
      :error="errors.email"
    />

    <FormInput
      v-model="form.password"
      label="Password"
      icon="🔒"
      type="password"
      placeholder="Min. 8 characters"
      autocomplete="new-password"
      :error="errors.password"
    />
    <PasswordStrengthBar :password="form.password" />

    <label class="checkbox-row" :class="{ 'checkbox-row--error': errors.terms }">
      <input type="checkbox" v-model="form.terms" class="checkbox" />
      <span class="checkbox-label">
        I agree to the
        <a href="#">Terms of Service</a> and
        <a href="#">Privacy Policy</a>
      </span>
    </label>
    <p class="field-error" v-if="errors.terms">{{ errors.terms }}</p>

    <button type="submit" class="btn btn-coral submit-btn" :disabled="loading">
      <span class="spinner" v-if="loading" />
      <span v-else>Create account</span>
    </button>

    <p class="switch-hint">
      Already have an account?
      <a @click.prevent="$emit('switch-mode', 'login')">Sign in →</a>
    </p>

  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import FormInput           from './FormInput.vue'
import AccountTypeSelector from './AccountTypeSelector.vue'
import PasswordStrengthBar from './PasswordStrengthBar.vue'

const props = defineProps({
  // When arriving from a partner page, pre-select the role.
  // AccountTypeSelector still renders so the user can change it if they want.
  defaultRole: {
    type: String,
    default: 'tourist',
    validator: v => ['tourist', 'agency', 'provider'].includes(v),
  },
})

defineEmits(['switch-mode'])

const router = useRouter()
const { switchRole } = useAuth()

const loading = ref(false)
const form    = ref({
  role: props.defaultRole,   // ← seeded from prop
  firstName: '', lastName: '',
  email: '', password: '', terms: false,
})
const errors = ref({})

async function submit() {
  errors.value = {}
  let valid = true

  if (!form.value.firstName)           { errors.value.firstName = 'Required.';                   valid = false }
  if (!form.value.lastName)            { errors.value.lastName  = 'Required.';                   valid = false }
  if (!form.value.email.includes('@')) { errors.value.email     = 'Enter a valid email.';         valid = false }
  if (form.value.password.length < 8)  { errors.value.password  = 'Min. 8 characters required.'; valid = false }
  if (!form.value.terms)               { errors.value.terms     = 'You must accept the terms.';   valid = false }

  if (!valid) return

  loading.value = true
  
  try {
    const response = await fetch('http://localhost/arrivo-website/backend/api/v1/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        firstName: form.value.firstName,
        lastName: form.value.lastName,
        email: form.value.email,
        password: form.value.password,
        role: form.value.role
      })
    })

    const data = await response.json()

    if (response.ok) {
      // Assuming switchRole sets some local state, let's keep it or set the user
      switchRole(form.value.role)
      
      // Auto-login or store the basic user object
      if (data.user) {
        localStorage.setItem('user', JSON.stringify(data.user))
      }

      if (form.value.role === 'agency' || form.value.role === 'provider') {
        router.push('/dashboard')
      } else {
        router.push('/')
      }
    } else {
      errors.value.email = data.error || 'Registration failed'
    }
  } catch (error) {
    console.error('Registration error:', error)
    errors.value.email = 'An error occurred during registration'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.form-header { margin-bottom: 28px; }
.form-title  { font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
.form-sub    { font-size: .92rem; color: var(--gray-600); line-height: 1.6; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.checkbox-row {
  display: flex; align-items: flex-start; gap: 10px;
  margin-top: 16px; margin-bottom: 10px; cursor: pointer;
}
.checkbox-row--error .checkbox-label { color: #e74c3c; }
.checkbox       { width: 18px; height: 18px; border-radius: 5px; accent-color: var(--coral); cursor: pointer; flex-shrink: 0; margin-top: 1px; }
.checkbox-label { font-size: .88rem; color: var(--gray-600); line-height: 1.5; }
.checkbox-label a { color: var(--coral); font-weight: 600; text-decoration: none; }
.checkbox-label a:hover { text-decoration: underline; }

.field-error { font-size: .78rem; color: #e74c3c; margin-bottom: 14px; }

.submit-btn { width: 100%; padding: 15px; font-size: 1rem; margin-top: 8px; margin-bottom: 20px; }
.submit-btn:disabled { opacity: .7; cursor: not-allowed; transform: none !important; }

.spinner {
  display: inline-block; width: 18px; height: 18px; border-radius: 50%;
  border: 2.5px solid rgba(255,255,255,.4); border-top-color: #fff;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.switch-hint   { text-align: center; font-size: .88rem; color: var(--gray-400); }
.switch-hint a { color: var(--coral); font-weight: 600; cursor: pointer; }
.switch-hint a:hover { text-decoration: underline; }

@media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }
</style>