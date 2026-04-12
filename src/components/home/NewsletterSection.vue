<template>
  <div class="newsletter">
    <h2 class="newsletter__title">Get travel inspiration in your inbox</h2>
    <p class="newsletter__sub">Join 30,000+ subscribers. No spam, unsubscribe any time.</p>

    <form class="newsletter__form" @submit.prevent="submit">
      <input
        class="newsletter__input"
        v-model="email"
        type="email"
        placeholder="Your email address"
        required
      />
      <button type="submit" class="btn btn-coral newsletter__btn">Subscribe</button>
    </form>

    <p class="newsletter__confirm" v-if="submitted">
      🎉 You're in! Check your inbox.
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const email     = ref('')
const submitted = ref(false)

function submit() {
  if (!email.value) return
  submitted.value = true
  email.value = ''
  setTimeout(() => { submitted.value = false }, 5000)
}
</script>

<style scoped>
.newsletter {
  background: linear-gradient(135deg, var(--sand) 0%, rgba(244,235,208,.4) 100%);
  padding: 80px 5%;
  text-align: center;
}

.newsletter__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(1.8rem, 3vw, 2.4rem); font-weight: 700;
  color: var(--indigo); margin-bottom: 12px;
}
.newsletter__sub { color: var(--gray-600); margin-bottom: 36px; font-size: 1rem; }

.newsletter__form {
  display: flex; max-width: 500px; margin: 0 auto;
  background: var(--white); border-radius: 50px; overflow: hidden;
  box-shadow: var(--shadow-md); border: 1px solid var(--gray-200);
}
.newsletter__input {
  flex: 1; border: none; outline: none;
  font-family: 'DM Sans', sans-serif; font-size: .95rem;
  padding: 16px 24px; background: transparent; color: var(--indigo);
  min-width: 0;
}
.newsletter__input::placeholder { color: var(--gray-400); }
.newsletter__btn { border-radius: 50px; margin: 5px; padding: 12px 28px; }

.newsletter__confirm {
  margin-top: 20px; font-size: .9rem; font-weight: 600;
  color: var(--teal);
}

@media (max-width: 480px) {
  .newsletter__form { flex-direction: column; border-radius: 16px; }
  .newsletter__input { padding: 16px 20px; }
  .newsletter__btn { border-radius: 12px; margin: 0 8px 8px; padding: 14px; }
}
</style>
