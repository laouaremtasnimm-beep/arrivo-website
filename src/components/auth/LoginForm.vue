<template>
  <form class="auth-form" @submit.prevent="submit" novalidate>

    <div class="form-header">
      <h1 class="form-title">Welcome back</h1>
      <p class="form-sub">Sign in to manage your bookings and explore new adventures.</p>
    </div>

    <SocialLoginButtons @social-login="handleSocialLogin" />

    <FormInput
      v-model="form.email"
      label="Email address"
      icon="✉️"
      type="email"
      placeholder="hello@example.com"
      autocomplete="email"
      :error="errors.email"
      @blur="validateEmail"
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

    <label class="checkbox-row">
      <input type="checkbox" v-model="form.remember" class="checkbox" />
      <span class="checkbox-label">Keep me signed in</span>
    </label>

    <button type="submit" class="btn btn-coral submit-btn" :disabled="loading">
      <span class="spinner" v-if="loading" />
      <span v-else>Sign in</span>
    </button>

    <p class="switch-hint">
      Don't have an account?
      <a @click.prevent="$emit('switch-mode', 'register')">Join free →</a>
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
const { user } = useAuth()

const loading = ref(false)
const form    = ref({ email: '', password: '', remember: false })
const errors  = ref({ email: '', password: '' })

function validateEmail() {
  errors.value.email = form.value.email.includes('@') ? '' : 'Please enter a valid email.'
}

function handleSocialLogin(provider) {
  console.log('Social login:', provider)
  // TODO: wire to your OAuth flow
}

async function submit() {
  errors.value = { email: '', password: '' }
  if (!form.value.email.includes('@')) { errors.value.email    = 'Please enter a valid email.'; return }
  if (form.value.password.length < 6)  { errors.value.password = 'Password is too short.';     return }

  loading.value = true
  await new Promise(r => setTimeout(r, 1200)) // simulate API call

  // TODO: replace with real API — set user in useAuth() after response
  loading.value = false

  // If there's a ?redirect= param (e.g. came from dashboard guard), go there
  const redirectTo = route.query.redirect || null

  if (redirectTo) {
    router.push(redirectTo)
  } else if (user.value?.role === 'agency' || user.value?.role === 'provider') {
    // Agencies and providers go to their dashboard
    router.push('/dashboard')
  } else {
    // Tourists go home
    router.push('/')
  }
}
</script>

<style scoped>
.form-header { margin-bottom: 28px; }
.form-title  { font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
.form-sub    { font-size: .92rem; color: var(--gray-600); line-height: 1.6; }

.forgot-link { font-size: .82rem; color: var(--coral); font-weight: 600; text-decoration: none; }
.forgot-link:hover { text-decoration: underline; }

.checkbox-row   { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; cursor: pointer; }
.checkbox       { width: 18px; height: 18px; border-radius: 5px; accent-color: var(--coral); cursor: pointer; flex-shrink: 0; }
.checkbox-label { font-size: .88rem; color: var(--gray-600); }

.submit-btn { width: 100%; padding: 15px; font-size: 1rem; margin-top: 4px; margin-bottom: 20px; }
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
</style>