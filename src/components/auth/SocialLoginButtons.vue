<template>
  <div class="social-btns">
    <button type="button" class="social-btn social-btn--google" @click="$emit('social-login', 'google')">
      <img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" alt="Google" class="social-icon" />
      <span>Google</span>
    </button>
    
    <button type="button" class="social-btn social-btn--fb" @click="$emit('social-login', 'facebook')">
      <svg class="social-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
      </svg>
      <span>Facebook</span>
    </button>
  </div>

  <div class="form-divider" v-if="label">
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
  display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 24px;
}

.social-btn {
  display: flex; align-items: center; justify-content: center; gap: 12px;
  padding: 12px 16px; border-radius: 14px; border: 1px solid var(--gray-200);
  background: var(--white); font-family: 'DM Sans', sans-serif;
  font-size: 0.9rem; font-weight: 600; color: var(--indigo);
  cursor: pointer; transition: all 0.25s ease;
}
.social-btn:hover { 
  background: var(--gray-50); border-color: var(--gray-300); 
  transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.social-btn:active { transform: translateY(0); }

.social-icon { width: 18px; height: 18px; object-fit: contain; }

.social-btn--fb { background: #1877F2; color: #fff; border-color: #1877F2; }
.social-btn--fb:hover { background: #166fe5; border-color: #166fe5; box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3); }

/* Divider matched to the form style */
.form-divider {
  display: flex; align-items: center; text-align: center; margin: 24px 0;
  color: var(--gray-300); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;
}
.form-divider::before, .form-divider::after {
  content: ''; flex: 1; border-bottom: 1px solid var(--gray-100);
}
.form-divider span { padding: 0 16px; }

@media (max-width: 480px) {
  .social-btns { grid-template-columns: 1fr; }
}
</style>
