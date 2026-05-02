<template>
  <form class="auth-form" @submit.prevent="submit" novalidate>

    <div class="form-header">
      <h1 class="form-title">Welcome back</h1>
      <p class="form-sub">Sign in to manage your bookings and explore new adventures.</p>
    </div>

    <SocialLoginButtons @social-login="handleSocialLogin" />

    <div class="form-fields">
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
        placeholder="••••••••"
        autocomplete="current-password"
        :error="errors.password"
      >
        <template #label-action>
          <a href="#" class="forgot-link">Forgot password?</a>
        </template>
      </FormInput>
    </div>

    <div class="form-options">
      <label class="checkbox-row">
        <input type="checkbox" v-model="form.remember" class="checkbox" />
        <span class="checkbox-label">Keep me signed in</span>
      </label>
    </div>

    <div v-if="generalError" class="general-error">{{ generalError }}</div>

    <button type="submit" class="btn btn-coral submit-btn" :disabled="loading">
      <div v-if="loading" class="spinner" />
      <span v-else>Sign in</span>
    </button>

    <p class="switch-hint">
      Don't have an account?
      <a @click.prevent="$emit('switch-mode', 'register')">Register →</a>
    </p>

  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import FormInput          from './FormInput.vue'
import SocialLoginButtons from './SocialLoginButtons.vue'

defineEmits(['switch-mode'])

const router = useRouter()
const route  = useRoute()
const { login } = useAuth()

const loading = ref(false)
const form    = ref({ email: '', password: '', remember: false })
const errors  = ref({ email: '', password: '' })
const generalError = ref('')

async function handleSocialLogin(provider) {
  console.log('Social login clicked:', provider)
}

async function submit() {
  errors.value = { email: '', password: '' }
  generalError.value = ''
  
  if (!form.value.email.includes('@')) { errors.value.email = 'Please enter a valid email.'; return }
  if (form.value.password.length < 6)  { errors.value.password = 'Password is too short.'; return }

  loading.value = true
  
  try {
    const res = await fetch('/arrivo-website/backend/api/v1/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: form.value.email, password: form.value.password })
    })

    const data = await res.json()

    if (!res.ok) {
      generalError.value = data.error || 'Invalid credentials'
      return
    }

    login(data.user)

    const redirectTo = route.query.redirect || (data.user.role === 'tourist' ? '/' : '/dashboard')
    router.push(redirectTo)
  } catch (e) {
    generalError.value = 'Network error. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.form-header { margin-bottom: 18px; text-align: left; }
.form-title  { font-family: 'Fraunces', serif; font-size: 1.7rem; font-weight: 700; color: var(--indigo); margin-bottom: 4px; }
.form-sub    { font-size: 0.88rem; color: var(--gray-500); line-height: 1.4; }

.form-fields { display: flex; flex-direction: column; gap: 12px; margin-bottom: 18px; }

.form-options {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 24px;
}
.checkbox-row { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.checkbox     { width: 16px; height: 16px; border-radius: 4px; accent-color: var(--coral); cursor: pointer; }
.checkbox-label { font-size: 0.85rem; color: var(--gray-500); font-weight: 500; }
.forgot-link  { font-size: 0.85rem; color: var(--coral); font-weight: 700; text-decoration: none; }
.forgot-link:hover { text-decoration: underline; }

.general-error {
  display: flex; align-items: center; gap: 8px;
  color: #dc2626; background: #fff1f2; padding: 12px; border-radius: 10px;
  margin-bottom: 16px; font-size: 0.88rem; border: 1px solid #fecdd3;
}

.submit-btn { 
  width: 100%; padding: 13px; font-size: 0.95rem; font-weight: 700; margin-bottom: 20px;
  box-shadow: 0 6px 12px rgba(255, 90, 95, 0.12);
}
.submit-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 16px rgba(255, 90, 95, 0.15); }
.submit-btn:disabled { opacity: 0.7; transform: none !important; }

.spinner {
  width: 18px; height: 18px; border-radius: 50%; border: 2.5px solid rgba(255,255,255,0.3);
  border-top-color: #fff; animation: spin 0.8s linear infinite; margin: 0 auto;
}
@keyframes spin { to { transform: rotate(360deg); } }

.switch-hint   { text-align: center; font-size: 0.88rem; color: var(--gray-500); }
.switch-hint a { color: var(--coral); font-weight: 700; cursor: pointer; text-decoration: none; margin-left: 4px; }
.switch-hint a:hover { text-decoration: underline; }

@media (max-width: 480px) {
  .form-title { font-size: 1.5rem; }
}
</style>