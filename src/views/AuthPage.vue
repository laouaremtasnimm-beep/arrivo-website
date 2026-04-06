<template>
  <div class="auth-page">

    <!-- ── Left panel – branding & visual ─────── -->
    <div class="auth-left">
      <div class="auth-left__inner">

        <!-- Logo -->
        <RouterLink to="/" class="logo">Voya<span>go</span></RouterLink>

        <!-- Main visual card -->
        <div class="auth-card">
          <img
            src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900&q=80"
            alt="Travel destination"
            class="auth-card__img"
          />
          <div class="auth-card__overlay">
            <div class="auth-card__tag">
              <span class="tag-dot" />
              50,000+ happy travellers
            </div>
            <h2 class="auth-card__title">Your next adventure starts with one click</h2>
          </div>
        </div>

        <!-- Floating stats -->
        <div class="auth-float auth-float--top">
          <span class="auth-float__icon">🌍</span>
          <div>
            <div class="auth-float__val">1,200+</div>
            <div class="auth-float__label">Destinations</div>
          </div>
        </div>
        <div class="auth-float auth-float--bottom">
          <span class="auth-float__icon">⭐</span>
          <div>
            <div class="auth-float__val">4.9 / 5</div>
            <div class="auth-float__label">Average rating</div>
          </div>
        </div>

      </div>
    </div>

    <!-- ── Right panel – form ──────────────────── -->
    <div class="auth-right">
      <div class="auth-form-wrap">

        <!-- Mobile logo (only visible on small screens) -->
        <RouterLink to="/" class="logo logo--mobile">Voya<span>go</span></RouterLink>

        <!-- Tab switcher -->
        <div class="auth-tabs">
          <button
            class="auth-tab"
            :class="{ active: mode === 'login' }"
            @click="mode = 'login'"
          >Sign in</button>
          <button
            class="auth-tab"
            :class="{ active: mode === 'register' }"
            @click="mode = 'register'"
          >Create account</button>
        </div>

        <!-- ── LOGIN FORM ─────────────────────── -->
        <Transition name="form-fade" mode="out-in">
          <form v-if="mode === 'login'" key="login" class="auth-form" @submit.prevent="submitLogin" novalidate>

            <div class="form-header">
              <h1 class="form-title">Welcome back</h1>
              <p class="form-sub">Sign in to manage your bookings and explore new adventures.</p>
            </div>

            <!-- Social login -->
            <div class="social-btns">
              <button type="button" class="social-btn-large">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20" height="20" />
                Continue with Google
              </button>
              <button type="button" class="social-btn-large social-btn-large--fb">
                <span style="font-size:1.1rem">📘</span>
                Continue with Facebook
              </button>
            </div>

            <div class="divider"><span>or sign in with email</span></div>

            <!-- Email -->
            <div class="form-group" :class="{ error: errors.email }">
              <label class="form-label">Email address</label>
              <div class="input-wrap">
                <span class="input-icon">✉️</span>
                <input
                  class="form-input"
                  v-model="loginForm.email"
                  type="email"
                  placeholder="hello@example.com"
                  autocomplete="email"
                  @blur="validateLoginEmail"
                />
              </div>
              <p class="field-error" v-if="errors.email">{{ errors.email }}</p>
            </div>

            <!-- Password -->
            <div class="form-group" :class="{ error: errors.password }">
              <div class="label-row">
                <label class="form-label">Password</label>
                <a href="#" class="forgot-link">Forgot password?</a>
              </div>
              <div class="input-wrap">
                <span class="input-icon">🔒</span>
                <input
                  class="form-input"
                  v-model="loginForm.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  autocomplete="current-password"
                />
                <button type="button" class="input-toggle" @click="showPassword = !showPassword">
                  {{ showPassword ? '🙈' : '👁️' }}
                </button>
              </div>
              <p class="field-error" v-if="errors.password">{{ errors.password }}</p>
            </div>

            <!-- Remember me -->
            <label class="checkbox-row">
              <input type="checkbox" v-model="loginForm.remember" class="checkbox" />
              <span class="checkbox-label">Keep me signed in</span>
            </label>

            <button type="submit" class="btn btn-coral submit-btn" :disabled="loading">
              <span v-if="loading" class="spinner" />
              <span v-else>Sign in</span>
            </button>

            <p class="switch-hint">
              Don't have an account?
              <a @click.prevent="mode = 'register'">Join free →</a>
            </p>

          </form>

          <!-- ── REGISTER FORM ───────────────────── -->
          <form v-else key="register" class="auth-form" @submit.prevent="submitRegister" novalidate>

            <div class="form-header">
              <h1 class="form-title">Create your account</h1>
              <p class="form-sub">Join thousands of travellers discovering the world through Voyago.</p>
            </div>

            <!-- Account type selector -->
            <div class="account-types">
              <button
                type="button"
                class="account-type"
                :class="{ active: registerForm.role === 'tourist' }"
                @click="registerForm.role = 'tourist'"
              >
                <span class="account-type__icon">🧳</span>
                <span class="account-type__label">Tourist</span>
              </button>
              <button
                type="button"
                class="account-type"
                :class="{ active: registerForm.role === 'agency' }"
                @click="registerForm.role = 'agency'"
              >
                <span class="account-type__icon">🏢</span>
                <span class="account-type__label">Agency</span>
              </button>
              <button
                type="button"
                class="account-type"
                :class="{ active: registerForm.role === 'provider' }"
                @click="registerForm.role = 'provider'"
              >
                <span class="account-type__icon">🛎️</span>
                <span class="account-type__label">Provider</span>
              </button>
            </div>

            <!-- Name row -->
            <div class="form-row">
              <div class="form-group" :class="{ error: regErrors.firstName }">
                <label class="form-label">First name</label>
                <div class="input-wrap">
                  <span class="input-icon">👤</span>
                  <input
                    class="form-input"
                    v-model="registerForm.firstName"
                    placeholder="Sarah"
                    autocomplete="given-name"
                  />
                </div>
                <p class="field-error" v-if="regErrors.firstName">{{ regErrors.firstName }}</p>
              </div>
              <div class="form-group" :class="{ error: regErrors.lastName }">
                <label class="form-label">Last name</label>
                <div class="input-wrap">
                  <span class="input-icon">👤</span>
                  <input
                    class="form-input"
                    v-model="registerForm.lastName"
                    placeholder="Connor"
                    autocomplete="family-name"
                  />
                </div>
                <p class="field-error" v-if="regErrors.lastName">{{ regErrors.lastName }}</p>
              </div>
            </div>

            <!-- Email -->
            <div class="form-group" :class="{ error: regErrors.email }">
              <label class="form-label">Email address</label>
              <div class="input-wrap">
                <span class="input-icon">✉️</span>
                <input
                  class="form-input"
                  v-model="registerForm.email"
                  type="email"
                  placeholder="hello@example.com"
                  autocomplete="email"
                />
              </div>
              <p class="field-error" v-if="regErrors.email">{{ regErrors.email }}</p>
            </div>

            <!-- Password -->
            <div class="form-group" :class="{ error: regErrors.password }">
              <label class="form-label">Password</label>
              <div class="input-wrap">
                <span class="input-icon">🔒</span>
                <input
                  class="form-input"
                  v-model="registerForm.password"
                  :type="showRegPassword ? 'text' : 'password'"
                  placeholder="Min. 8 characters"
                  autocomplete="new-password"
                  @input="updatePasswordStrength"
                />
                <button type="button" class="input-toggle" @click="showRegPassword = !showRegPassword">
                  {{ showRegPassword ? '🙈' : '👁️' }}
                </button>
              </div>
              <p class="field-error" v-if="regErrors.password">{{ regErrors.password }}</p>

              <!-- Password strength bar -->
              <div class="strength-bar" v-if="registerForm.password">
                <div class="strength-bar__track">
                  <div
                    class="strength-bar__fill"
                    :style="{ width: strengthWidth }"
                    :class="strengthClass"
                  />
                </div>
                <span class="strength-bar__label" :class="strengthClass">{{ strengthLabel }}</span>
              </div>
            </div>

            <!-- Terms -->
            <label class="checkbox-row" :class="{ error: regErrors.terms }">
              <input type="checkbox" v-model="registerForm.terms" class="checkbox" />
              <span class="checkbox-label">
                I agree to the
                <a href="#">Terms of Service</a> and
                <a href="#">Privacy Policy</a>
              </span>
            </label>
            <p class="field-error" style="margin-top:-8px;margin-bottom:12px" v-if="regErrors.terms">
              {{ regErrors.terms }}
            </p>

            <button type="submit" class="btn btn-coral submit-btn" :disabled="loading">
              <span v-if="loading" class="spinner" />
              <span v-else>Create account</span>
            </button>

            <p class="switch-hint">
              Already have an account?
              <a @click.prevent="mode = 'login'">Sign in →</a>
            </p>

          </form>
        </Transition>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ── Mode toggle ────────────────────────────────────────────────────────────
const mode = ref('login')   // 'login' | 'register'

// ── UI state ───────────────────────────────────────────────────────────────
const loading         = ref(false)
const showPassword    = ref(false)
const showRegPassword = ref(false)

// ── Login form ─────────────────────────────────────────────────────────────
const loginForm = ref({ email: '', password: '', remember: false })
const errors    = ref({ email: '', password: '' })

function validateLoginEmail() {
  errors.value.email = loginForm.value.email.includes('@') ? '' : 'Please enter a valid email.'
}

async function submitLogin() {
  errors.value = { email: '', password: '' }
  if (!loginForm.value.email.includes('@'))  { errors.value.email    = 'Please enter a valid email.'; return }
  if (loginForm.value.password.length < 6)   { errors.value.password = 'Password is too short.';     return }

  loading.value = true
  await new Promise(r => setTimeout(r, 1200))   // simulate API call
  loading.value = false

  // TODO: call your auth API here, then:
  router.push('/')
}

// ── Register form ──────────────────────────────────────────────────────────
const registerForm = ref({
  role: 'tourist',
  firstName: '', lastName: '',
  email: '', password: '',
  terms: false,
})
const regErrors = ref({})

// Password strength
const passwordStrength = ref(0)

function updatePasswordStrength() {
  const p = registerForm.value.password
  let score = 0
  if (p.length >= 8)           score++
  if (/[A-Z]/.test(p))         score++
  if (/[0-9]/.test(p))         score++
  if (/[^A-Za-z0-9]/.test(p))  score++
  passwordStrength.value = score
}

const strengthWidth = computed(() => {
  const map = [0, 25, 50, 75, 100]
  return map[passwordStrength.value] + '%'
})
const strengthLabel = computed(() => {
  return ['', 'Weak', 'Fair', 'Good', 'Strong'][passwordStrength.value]
})
const strengthClass = computed(() => {
  return ['', 'weak', 'fair', 'good', 'strong'][passwordStrength.value]
})

async function submitRegister() {
  regErrors.value = {}
  let valid = true

  if (!registerForm.value.firstName) { regErrors.value.firstName = 'Required.';                        valid = false }
  if (!registerForm.value.lastName)  { regErrors.value.lastName  = 'Required.';                        valid = false }
  if (!registerForm.value.email.includes('@')) { regErrors.value.email = 'Enter a valid email.';        valid = false }
  if (registerForm.value.password.length < 8) { regErrors.value.password = 'Min. 8 characters.';       valid = false }
  if (!registerForm.value.terms)               { regErrors.value.terms   = 'You must accept the terms.'; valid = false }

  if (!valid) return

  loading.value = true
  await new Promise(r => setTimeout(r, 1400))   // simulate API call
  loading.value = false

  // TODO: call your auth API here, then:
  router.push('/')
}
</script>

<style scoped>
/* ── Layout ───────────────────────────────────── */
.auth-page {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
}

/* ── Left panel ───────────────────────────────── */
.auth-left {
  background: var(--indigo);
  position: relative; overflow: hidden;
  display: flex; align-items: stretch;
}
.auth-left::before {
  content: '';
  position: absolute; top: -100px; right: -100px;
  width: 400px; height: 400px; border-radius: 50%;
  background: rgba(46,196,182,.12); pointer-events: none;
}
.auth-left::after {
  content: '';
  position: absolute; bottom: -80px; left: -80px;
  width: 300px; height: 300px; border-radius: 50%;
  background: rgba(255,90,95,.10); pointer-events: none;
}

.auth-left__inner {
  position: relative; z-index: 1;
  padding: 48px 52px;
  display: flex; flex-direction: column;
  width: 100%;
}

/* Logo */
.logo {
  font-family: 'Fraunces', serif;
  font-size: 1.6rem; font-weight: 700;
  color: #fff; letter-spacing: -.5px;
  text-decoration: none;
  flex-shrink: 0;
}
.logo span { color: var(--coral); }
.logo--mobile { display: none; color: var(--indigo); margin-bottom: 32px; }
.logo--mobile span { color: var(--coral); }

/* Visual card */
.auth-card {
  margin-top: 40px;
  border-radius: 22px; overflow: hidden;
  flex: 1; position: relative;
  min-height: 320px;
  box-shadow: var(--shadow-lg);
}
.auth-card__img { width: 100%; height: 100%; object-fit: cover; }

.auth-card__overlay {
  position: absolute; inset: 0;
  background: linear-gradient(180deg, transparent 35%, rgba(20,22,38,.82) 100%);
  display: flex; flex-direction: column; justify-content: flex-end;
  padding: 32px;
}
.auth-card__tag {
  display: inline-flex; align-items: center; gap: 7px;
  background: rgba(255,255,255,.15); backdrop-filter: blur(8px);
  color: #fff; font-size: .78rem; font-weight: 600;
  letter-spacing: .05em; text-transform: uppercase;
  padding: 6px 14px; border-radius: 50px; margin-bottom: 14px;
  width: fit-content;
}
.tag-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--teal); flex-shrink: 0;
}
.auth-card__title {
  font-family: 'Fraunces', serif;
  font-size: 1.5rem; font-weight: 700;
  color: #fff; line-height: 1.25;
  max-width: 320px;
}

/* Floating stat badges */
.auth-float {
  position: absolute;
  background: var(--white); border-radius: 14px;
  padding: 14px 18px; box-shadow: var(--shadow-md);
  display: flex; align-items: center; gap: 12px;
  z-index: 2;
}
.auth-float--top    { top: 140px; right: 28px; }
.auth-float--bottom { bottom: 130px; left: 28px; }
.auth-float__icon   { font-size: 1.4rem; }
.auth-float__val    { font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700; color: var(--indigo); }
.auth-float__label  { font-size: .75rem; color: var(--gray-400); margin-top: 2px; }

/* ── Right panel ──────────────────────────────── */
.auth-right {
  background: var(--white);
  display: flex; align-items: center; justify-content: center;
  padding: 48px 5%;
  overflow-y: auto;
}

.auth-form-wrap {
  width: 100%; max-width: 460px;
}

/* ── Tabs ─────────────────────────────────────── */
.auth-tabs {
  display: flex; background: var(--gray-100);
  border-radius: 14px; padding: 5px; margin-bottom: 36px;
}
.auth-tab {
  flex: 1; padding: 11px; text-align: center;
  font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: .92rem;
  border-radius: 11px; border: none; background: none; cursor: pointer;
  color: var(--gray-400); transition: all var(--transition);
}
.auth-tab.active {
  background: var(--white); color: var(--indigo);
  box-shadow: var(--shadow);
}

/* ── Form shared ──────────────────────────────── */
.form-header { margin-bottom: 28px; }
.form-title  { font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
.form-sub    { font-size: .92rem; color: var(--gray-600); line-height: 1.6; }

/* Social buttons */
.social-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
.social-btn-large {
  display: flex; align-items: center; justify-content: center; gap: 9px;
  padding: 11px 16px; border-radius: 12px; border: 1.5px solid var(--gray-200);
  background: var(--white); font-family: 'DM Sans', sans-serif;
  font-size: .85rem; font-weight: 600; color: var(--indigo);
  cursor: pointer; transition: all var(--transition);
}
.social-btn-large:hover { background: var(--gray-50); border-color: var(--gray-400); }
.social-btn-large--fb { background: #1877F2; color: #fff; border-color: #1877F2; }
.social-btn-large--fb:hover { background: #1464d8; border-color: #1464d8; }

/* Divider */
.divider {
  display: flex; align-items: center; gap: 14px;
  margin-bottom: 24px; color: var(--gray-400); font-size: .82rem;
}
.divider::before, .divider::after {
  content: ''; flex: 1; height: 1px; background: var(--gray-200);
}

/* Form groups */
.form-group  { margin-bottom: 18px; }
.form-row    { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-label  { font-size: .85rem; font-weight: 600; color: var(--indigo); margin-bottom: 8px; display: block; }
.label-row   { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.forgot-link { font-size: .82rem; color: var(--coral); font-weight: 600; text-decoration: none; }
.forgot-link:hover { text-decoration: underline; }

/* Input wrapper */
.input-wrap { position: relative; }
.input-icon {
  position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
  font-size: 1rem; pointer-events: none;
}
.form-input {
  width: 100%; padding: 13px 16px 13px 42px;
  border: 1.5px solid var(--gray-200); border-radius: 12px;
  font-family: 'DM Sans', sans-serif; font-size: .95rem; color: var(--indigo);
  outline: none; transition: border-color var(--transition), box-shadow var(--transition);
  background: var(--white);
}
.form-input:focus {
  border-color: var(--coral);
  box-shadow: 0 0 0 3px rgba(255,90,95,.10);
}
.form-group.error .form-input { border-color: #e74c3c; }
.form-group.error .form-input:focus { box-shadow: 0 0 0 3px rgba(231,76,60,.10); }

.input-toggle {
  position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; font-size: 1rem; padding: 0; line-height: 1;
}

/* Error text */
.field-error { font-size: .78rem; color: #e74c3c; margin-top: 5px; }

/* Checkbox */
.checkbox-row {
  display: flex; align-items: flex-start; gap: 10px;
  margin-bottom: 22px; cursor: pointer;
}
.checkbox-row.error .checkbox-label { color: #e74c3c; }
.checkbox {
  width: 18px; height: 18px; border-radius: 5px;
  accent-color: var(--coral); cursor: pointer; flex-shrink: 0; margin-top: 1px;
}
.checkbox-label { font-size: .88rem; color: var(--gray-600); line-height: 1.5; }
.checkbox-label a { color: var(--coral); font-weight: 600; text-decoration: none; }
.checkbox-label a:hover { text-decoration: underline; }

/* Account type selector */
.account-types {
  display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-bottom: 22px;
}
.account-type {
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  padding: 14px 10px; border-radius: 12px;
  border: 1.5px solid var(--gray-200);
  background: var(--white); cursor: pointer;
  transition: all var(--transition);
}
.account-type:hover { border-color: var(--coral); background: var(--coral-lt); }
.account-type.active {
  border-color: var(--coral); background: var(--coral-lt);
  box-shadow: 0 0 0 3px rgba(255,90,95,.10);
}
.account-type__icon  { font-size: 1.5rem; }
.account-type__label { font-size: .82rem; font-weight: 600; color: var(--indigo); }

/* Password strength */
.strength-bar { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
.strength-bar__track {
  flex: 1; height: 4px; background: var(--gray-200); border-radius: 2px; overflow: hidden;
}
.strength-bar__fill {
  height: 100%; border-radius: 2px; transition: width .4s ease, background .4s ease;
}
.strength-bar__fill.weak   { background: #e74c3c; }
.strength-bar__fill.fair   { background: #f39c12; }
.strength-bar__fill.good   { background: #2EC4B6; }
.strength-bar__fill.strong { background: #27ae60; }
.strength-bar__label { font-size: .78rem; font-weight: 600; min-width: 40px; }
.strength-bar__label.weak   { color: #e74c3c; }
.strength-bar__label.fair   { color: #f39c12; }
.strength-bar__label.good   { color: #2EC4B6; }
.strength-bar__label.strong { color: #27ae60; }

/* Submit */
.submit-btn {
  width: 100%; padding: 15px; font-size: 1rem;
  margin-top: 4px; margin-bottom: 20px;
  position: relative;
}
.submit-btn:disabled { opacity: .7; cursor: not-allowed; transform: none !important; }

/* Spinner */
.spinner {
  display: inline-block;
  width: 18px; height: 18px; border-radius: 50%;
  border: 2.5px solid rgba(255,255,255,.4);
  border-top-color: #fff;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Switch hint */
.switch-hint { text-align: center; font-size: .88rem; color: var(--gray-400); }
.switch-hint a { color: var(--coral); font-weight: 600; cursor: pointer; }
.switch-hint a:hover { text-decoration: underline; }

/* ── Form transition ──────────────────────────── */
.form-fade-enter-active, .form-fade-leave-active {
  transition: opacity .18s ease, transform .18s ease;
}
.form-fade-enter-from, .form-fade-leave-to {
  opacity: 0; transform: translateX(12px);
}

/* ── Responsive ───────────────────────────────── */
@media (max-width: 900px) {
  .auth-page { grid-template-columns: 1fr; }
  .auth-left  { display: none; }
  .auth-right { padding: 40px 6%; align-items: flex-start; }
  .logo--mobile { display: block; }
  .auth-form-wrap { max-width: 100%; }
}
@media (max-width: 480px) {
  .social-btns   { grid-template-columns: 1fr; }
  .form-row      { grid-template-columns: 1fr; }
  .account-types { grid-template-columns: repeat(3,1fr); }
  .form-title    { font-size: 1.7rem; }
}
</style>