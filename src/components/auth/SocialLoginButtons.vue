<template>
  <div class="social-btns">
    <button type="button" class="social-btn" @click="$emit('social-login', 'google')">
            Continue with Google
    </button>
    <button type="button" class="social-btn social-btn--fb" @click="$emit('social-login', 'facebook')">
      <span class="social-btn__fb-icon"></span>
      Continue with Facebook
    </button>
  </div>

  <div class="divider">
    <span>{{ label }}</span>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, default: 'or sign in with email' },
})
defineEmits(['social-login'])
</script>

<style scoped>
.social-btns {
  display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px;
}

.social-btn {
  display: flex; align-items: center; justify-content: center; gap: 9px;
  padding: 11px 16px; border-radius: 12px; border: 1.5px solid var(--gray-200);
  background: var(--white); font-family: 'DM Sans', sans-serif;
  font-size: .85rem; font-weight: 600; color: var(--indigo);
  cursor: pointer; transition: all var(--transition);
}
.social-btn:hover { background: var(--gray-50); border-color: var(--gray-400); }

.social-btn--fb { background: #1877F2; color: #fff; border-color: #1877F2; }
.social-btn--fb:hover { background: #1464d8; border-color: #1464d8; }
.social-btn__fb-icon { font-size: 1.1rem; }

/* Divider */
.divider {
  display: flex; align-items: center; gap: 14px;
  margin-bottom: 24px; color: var(--gray-400); font-size: .82rem;
}
.divider::before,
.divider::after {
  content: ''; flex: 1; height: 1px; background: var(--gray-200);
}

@media (max-width: 480px) {
  .social-btns { grid-template-columns: 1fr; }
}
</style>
