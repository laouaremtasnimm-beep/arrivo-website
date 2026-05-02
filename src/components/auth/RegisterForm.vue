<template>
  <form class="auth-form" @submit.prevent="submit" novalidate>

    <div class="form-header">
      <h1 class="form-title">Create your account</h1>
      <p class="form-sub">Join thousands of travellers discovering the world through Voyago.</p>
    </div>

    <!-- Role selector -->
    <AccountTypeSelector v-model="form.role" />

    <SocialLoginButtons @social-login="handleSocialLogin" label="or register with email" />

    <div class="form-fields">
      <div class="form-row">
        <FormInput
          v-model="form.firstName"
          label="First name"
          icon="👤"
          placeholder="Sarah"
          :error="errors.firstName"
        />
        <FormInput
          v-model="form.lastName"
          label="Last name"
          icon="👤"
          placeholder="Connor"
          :error="errors.lastName"
        />
      </div>

      <FormInput
        v-model="form.email"
        label="Email address"
        icon="✉️"
        type="email"
        placeholder="hello@example.com"
        :error="errors.email"
      />

      <div class="password-group">
        <FormInput
          v-model="form.password"
          label="Password"
          icon="🔒"
          type="password"
          placeholder="Min. 8 characters"
          :error="errors.password"
        />
        <PasswordStrengthBar :password="form.password" />
      </div>
    </div>

    <div class="form-options">
      <label class="checkbox-row" :class="{ 'checkbox-row--error': errors.terms }">
        <input type="checkbox" v-model="form.terms" class="checkbox" />
        <span class="checkbox-label">
          I agree to the <a href="#">Terms</a> & <a href="#">Privacy Policy</a>
        </span>
      </label>
      <p class="field-error" v-if="errors.terms">{{ errors.terms }}</p>
    </div>

    <button type="submit" class="btn btn-coral submit-btn" :disabled="loading">
      <div v-if="loading" class="spinner" />
      <span v-else>Create Account</span>
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
import SocialLoginButtons from './SocialLoginButtons.vue'

const props = defineProps({
  defaultRole: {
    type: String,
    default: 'tourist',
    validator: v => ['tourist', 'agency', 'provider'].includes(v),
  },
})

defineEmits(['switch-mode'])

const router = useRouter()
const { login } = useAuth() 

const loading = ref(false)
const form    = ref({
  role: props.defaultRole,
  firstName: '', lastName: '',
  email: '', password: '', terms: false,
})
const errors = ref({})

async function handleSocialLogin(provider) {
  console.log('Social login clicked:', provider)
}

async function submit() {
  errors.value = {}
  let valid = true

  if (!form.value.firstName) { errors.value.firstName = 'Required.'; valid = false }
  if (!form.value.lastName)  { errors.value.lastName  = 'Required.'; valid = false }
  if (!form.value.email.includes('@')) { errors.value.email = 'Enter a valid email.'; valid = false }
  if (form.value.password.length < 8)  { errors.value.password = 'Min. 8 characters required.'; valid = false }
  if (!form.value.terms) { errors.value.terms = 'You must accept the terms.'; valid = false }

  if (!valid) return

  loading.value = true
  
  try {
    const response = await fetch('/arrivo-website/backend/api/v1/register.php', {
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
      login(data.user)
      if (data.user.role === 'agency' || data.user.role === 'provider') {
        router.push('/dashboard')
      } else {
        router.push('/')
      }
    } else {
      errors.value.email = data.error || 'Registration failed'
    }
  } catch (error) {
    console.error('Registration error:', error)
    errors.value.email = 'An error occurred'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.form-header { margin-bottom: 12px; text-align: left; }
.form-title  { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; color: var(--indigo); margin-bottom: 2px; }
.form-sub    { font-size: 0.82rem; color: var(--gray-500); line-height: 1.3; }

.form-fields { display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

.password-group { display: flex; flex-direction: column; gap: 6px; }

.form-options { margin-bottom: 20px; }
.checkbox-row {
  display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
}
.checkbox-row--error .checkbox-label { color: #e11d48; }
.checkbox       { width: 14px; height: 14px; border-radius: 4px; accent-color: var(--coral); cursor: pointer; }
.checkbox-label { font-size: 0.8rem; color: var(--gray-500); line-height: 1.2; font-weight: 500; }
.checkbox-label a { color: var(--coral); font-weight: 700; text-decoration: none; }

.field-error { font-size: 0.68rem; color: #e11d48; margin-top: 2px; font-weight: 600; }

.submit-btn { 
  width: 100%; padding: 11px; font-size: 0.9rem; font-weight: 700; margin-bottom: 12px;
}
.submit-btn:hover { transform: translateY(-1px); }

.spinner {
  width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff; animation: spin 0.8s linear infinite; margin: 0 auto;
}
@keyframes spin { to { transform: rotate(360deg); } }

.switch-hint   { text-align: center; font-size: 0.82rem; color: var(--gray-500); }
.switch-hint a { color: var(--coral); font-weight: 700; cursor: pointer; text-decoration: none; margin-left: 4px; }

@media (max-width: 480px) {
  .form-row { grid-template-columns: 1fr; }
  .form-title { font-size: 1.4rem; }
}
</style>