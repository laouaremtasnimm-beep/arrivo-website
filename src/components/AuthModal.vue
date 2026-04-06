<template>
  <Teleport to="body">
    <Transition name="fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal" role="dialog" :aria-label="isLogin ? 'Sign in' : 'Create account'">

          <button class="modal__close" @click="close" aria-label="Close">✕</button>

          <h2 class="modal__title">Welcome to Voyago</h2>
          <p class="modal__sub">{{ isLogin ? 'Sign in to your account' : 'Create your free account' }}</p>

          <!-- Tabs -->
          <div class="modal__tabs">
            <button class="modal__tab" :class="{ active: isLogin }"    @click="activeTab = 'login'">Sign in</button>
            <button class="modal__tab" :class="{ active: !isLogin }"   @click="activeTab = 'register'">Register</button>
          </div>

          <!-- Register extras -->
          <div class="form-row" v-if="!isLogin">
            <div class="form-group">
              <label class="form-label">First name</label>
              <input class="form-input" v-model="form.firstName" placeholder="Sarah" />
            </div>
            <div class="form-group">
              <label class="form-label">Last name</label>
              <input class="form-input" v-model="form.lastName" placeholder="Connor" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-input" v-model="form.email" type="email" placeholder="hello@example.com" />
          </div>

          <div class="form-group">
            <label class="form-label">Password</label>
            <input class="form-input" v-model="form.password" type="password" placeholder="••••••••" />
          </div>

          <button class="btn btn-coral modal__submit" @click="submit">
            {{ isLogin ? 'Sign in' : 'Create account' }}
          </button>

          <p class="modal__footer" v-if="isLogin">
            Don't have an account?
            <a @click="activeTab = 'register'">Join free</a>
          </p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: null }, // 'login' | 'register' | null
})
const emit = defineEmits(['update:modelValue', 'submit'])

const activeTab = ref(props.modelValue)
watch(() => props.modelValue, v => { if (v) activeTab.value = v })

const isLogin = computed(() => activeTab.value === 'login')

const form = ref({ firstName: '', lastName: '', email: '', password: '' })

function close() { emit('update:modelValue', null) }

function submit() {
  emit('submit', { type: activeTab.value, ...form.value })
  close()
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.40); z-index: 200;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}

.modal {
  background: var(--white); border-radius: 24px;
  width: 100%; max-width: 520px; padding: 44px;
  position: relative; box-shadow: 0 24px 80px rgba(0,0,0,.20);
}

.modal__close {
  position: absolute; top: 20px; right: 20px;
  width: 36px; height: 36px; border: none; border-radius: 50%;
  background: var(--gray-100); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; color: var(--gray-600);
  transition: background var(--transition);
}
.modal__close:hover { background: var(--gray-200); }

.modal__title { font-family: 'Fraunces', serif; font-size: 1.8rem; font-weight: 700; margin-bottom: 6px; }
.modal__sub   { font-size: .9rem; color: var(--gray-400); margin-bottom: 28px; }

/* Tabs */
.modal__tabs {
  display: flex; background: var(--gray-100); border-radius: 12px;
  padding: 4px; margin-bottom: 28px;
}
.modal__tab {
  flex: 1; padding: 10px; text-align: center; font-weight: 600; font-size: .9rem;
  border-radius: 10px; border: none; background: none; cursor: pointer;
  color: var(--gray-400); transition: all var(--transition);
  font-family: 'DM Sans', sans-serif;
}
.modal__tab.active { background: var(--white); color: var(--indigo); box-shadow: var(--shadow); }

/* Form */
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 18px; }
.form-label { font-size: .85rem; font-weight: 600; color: var(--indigo); margin-bottom: 8px; display: block; }
.form-input {
  width: 100%; padding: 13px 16px; border: 1.5px solid var(--gray-200);
  border-radius: 12px; font-family: 'DM Sans', sans-serif; font-size: .95rem; color: var(--indigo);
  outline: none; transition: border-color var(--transition); background: var(--white);
}
.form-input:focus { border-color: var(--coral); }

.modal__submit { width: 100%; padding: 15px; font-size: 1rem; margin-top: 6px; }

.modal__footer { margin-top: 22px; text-align: center; font-size: .85rem; color: var(--gray-400); }
.modal__footer a { color: var(--coral); font-weight: 600; cursor: pointer; }

/* Transition */
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease, transform .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: scale(.97); }

@media (max-width: 480px) {
  .modal { padding: 32px 24px; }
  .form-row { grid-template-columns: 1fr; }
}
</style>
