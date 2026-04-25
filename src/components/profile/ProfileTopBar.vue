<!-- PageTopBar.vue -->
<template>
  <header class="page-topbar">
    <RouterLink to="/" class="logo">Voya<span>go</span></RouterLink>
    <span class="page-title">{{ title }}</span>
    <button class="back-btn" @click="router.back()">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
      Back
    </button>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
defineProps({ title: String })
const router = useRouter()
</script>

<style scoped>
.page-topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 var(--section-px, 5%);
  height: 52px;
  background: #fff;
  border-bottom: 1px solid var(--gray-200);
  position: sticky; top: 0; z-index: 50;
}
.logo {
  font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700;
  color: var(--indigo); letter-spacing: -.3px; text-decoration: none;
}
.logo span { color: var(--coral); }
.page-title { font-size: 13px; font-weight: 500; color: var(--gray-600); }
.back-btn {
  display: flex; align-items: center; gap: 5px;
  background: none; border: none; cursor: pointer;
  font-size: 13px; font-family: 'DM Sans', sans-serif;
  color: var(--gray-600); padding: 0;
  transition: color var(--transition);
}
.back-btn:hover { color: var(--indigo); }
</style>