<template>
  <div class="auth-page">

    <!-- Background Layer (Full Screen) -->
    <AuthLeftPanel class="auth-bg" />

    <!-- Overlay Content Layer -->
    <div class="auth-overlay">
      <!-- Global Logo -->
      <RouterLink to="/" class="auth-page-logo">Voya<span>go</span></RouterLink>

      <div class="auth-container">
        
        <!-- Left Branding & Social Proof -->
        <div class="auth-branding-area">
          <div class="auth-branding">
            <h1 class="branding-title">Discover the World. <span>Experience the Extraordinary.</span></h1>
            <p class="branding-sub">Your journey to the most beautiful destinations begins here.</p>
          </div>

          <!-- Social Proof at the bottom left -->
          <div class="auth-social-proof">
            <div class="avatar-stack">
              <img v-for="i in 4" :key="i" :src="`https://i.pravatar.cc/100?u=${i+10}`" alt="User" />
              <div class="avatar-plus">+12k</div>
            </div>
            <p class="proof-text">Join over <strong>12,000+</strong> happy travellers exploring the world with us.</p>
          </div>
        </div>

        <!-- Right Floating Card -->
        <div class="auth-card-wrap">
          <div class="auth-floating-card">
            <AuthTabs v-model="mode" />

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
const mode = ref(route.query.mode === 'register' ? 'register' : 'login')
</script>

<style scoped>
.auth-page {
  position: relative;
  min-height: 100vh;
  width: 100%;
  overflow-x: hidden;
  background: var(--indigo);
}

.auth-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
}

.auth-page-logo {
  position: absolute;
  top: 40px;
  left: 5%;
  z-index: 10;
  font-family: 'Fraunces', serif;
  font-size: 2.2rem;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  transition: transform 0.3s ease;
}
.auth-page-logo span { color: var(--coral); }
.auth-page-logo:hover { transform: scale(1.05); }

.auth-overlay {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  align-items: flex-start; /* Fixed top alignment */
  justify-content: center;
  padding: 40px 5%; 
}

.auth-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  width: 100%;
  max-width: 1280px;
  align-items: flex-start; /* Keep items from jumping */
}

.auth-branding-area {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-self: flex-start;
  padding-right: 40px;
  margin-top: 140px; /* Sits in the upper-middle area */
}

.auth-branding {
  color: #fff;
}

.branding-title {
  font-family: 'Fraunces', serif;
  font-size: 3.5rem;
  font-weight: 700;
  line-height: 1.1;
  margin-bottom: 24px;
}
.branding-title span { display: block; color: var(--coral); }
.branding-sub {
  font-size: 1.1rem;
  opacity: 0.9;
  line-height: 1.6;
  max-width: 480px;
}

/* Social Proof */
.auth-social-proof {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 60px; /* Fallback if min-height isn't enough */
}

.avatar-stack {
  display: flex;
  align-items: center;
}

.avatar-stack img, .avatar-plus {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 3px solid rgba(255,255,255,0.1);
  margin-left: -12px;
  object-fit: cover;
  transition: transform 0.3s ease;
}
.avatar-stack img:first-child { margin-left: 0; }
.avatar-stack img:hover { transform: translateY(-5px); z-index: 10; }

.avatar-plus {
  background: var(--coral);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
  border: 3px solid rgba(255,255,255,0.2);
}

.proof-text {
  color: #fff;
  font-size: 0.95rem;
  line-height: 1.4;
  opacity: 0.85;
  max-width: 240px;
}
.proof-text strong { color: var(--coral); }

.auth-card-wrap {
  display: flex;
  justify-content: flex-end;
}

.auth-floating-card {
  background: var(--white);
  padding: 32px 40px;
  border-radius: 28px;
  width: 100%;
  max-width: 560px;
  box-shadow: 0 40px 100px rgba(0,0,0,0.25);
}

/* Form transition */
.form-fade-enter-active,
.form-fade-leave-active { transition: opacity .25s ease, transform .25s ease; }
.form-fade-enter-from,
.form-fade-leave-to     { opacity: 0; transform: translateY(10px); }

/* ── Responsive ───────────────────────────────── */
@media (max-width: 1100px) {
  .branding-title { font-size: 2.8rem; }
}

@media (max-width: 950px) {
  .auth-container { grid-template-columns: 1fr; gap: 40px; }
  .auth-branding { display: none; }
  .auth-card-wrap { justify-content: center; }
  .auth-overlay { padding: 40px 20px; }
}
</style>