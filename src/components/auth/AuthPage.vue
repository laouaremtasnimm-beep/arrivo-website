<template>
  <div class="auth-page">

    <!-- Left branding panel -->
    <AuthLeftPanel />

    <!-- Right form panel -->
    <div class="auth-right">
      <div class="auth-form-wrap">

        <!-- Mobile logo (hidden on desktop — logo lives in AuthLeftPanel) -->
        <RouterLink to="/" class="logo logo--mobile">Voya<span>go</span></RouterLink>

        <!-- Login / Register tab switcher -->
        <AuthTabs v-model="mode" />

        <!-- Animated form swap -->
        <Transition name="form-fade" mode="out-in">
          <LoginForm
            v-if="mode === 'login'"
            key="login"
            @switch-mode="mode = $event"
          />
          <RegisterForm
            v-else
            key="register"
            @switch-mode="mode = $event"
          />
        </Transition>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

import AuthLeftPanel from '@/components/auth/AuthLeftPanel.vue'
import AuthTabs      from '@/components/auth/AuthTabs.vue'
import LoginForm     from '@/components/auth/LoginForm.vue'
import RegisterForm  from '@/components/auth/RegisterForm.vue'

const route = useRoute()

// Allow deep-linking: /auth?mode=register
const mode = ref(route.query.mode === 'register' ? 'register' : 'login')
</script>

<style scoped>
/* ── Two-column layout ────────────────────────── */
.auth-page {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
}

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

/* Mobile logo — only shown when left panel is hidden */
.logo--mobile {
  display: none;
  font-family: 'Fraunces', serif;
  font-size: 1.6rem; font-weight: 700;
  color: var(--indigo); letter-spacing: -.5px;
  text-decoration: none; margin-bottom: 32px;
}
.logo--mobile span { color: var(--coral); }

/* Form transition */
.form-fade-enter-active,
.form-fade-leave-active { transition: opacity .18s ease, transform .18s ease; }
.form-fade-enter-from,
.form-fade-leave-to     { opacity: 0; transform: translateX(12px); }

/* ── Responsive ───────────────────────────────── */
@media (max-width: 900px) {
  .auth-page   { grid-template-columns: 1fr; }
  .auth-right  { padding: 40px 6%; align-items: flex-start; }
  .logo--mobile{ display: block; }
  .auth-form-wrap { max-width: 100%; }
}
</style>
