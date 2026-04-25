<template>
  <div class="reply-box">
    <Transition name="reply-expand">
      <div v-if="open" class="reply-box__inner">
        <div class="reply-box__avatar">You</div>
        <div class="reply-box__right">
          <textarea
            v-model="text"
            class="reply-box__input"
            :placeholder="`Reply to ${reviewerName}…`"
            rows="3"
            ref="inputRef"
          />
          <div class="reply-box__actions">
            <button class="reply-btn-cancel" @click="cancel">Cancel</button>
            <button class="reply-btn-send" @click="submit" :disabled="!text.trim()">
              Post Reply
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'

const props = defineProps({
  open:         { type: Boolean, default: false },
  reviewerName: { type: String,  default: 'Guest' },
})
const emit = defineEmits(['submit', 'cancel'])

const text     = ref('')
const inputRef = ref(null)

watch(() => props.open, v => {
  if (v) {
    text.value = ''
    nextTick(() => inputRef.value?.focus())
  }
})

function submit() {
  if (!text.value.trim()) return
  emit('submit', text.value.trim())
  text.value = ''
}

function cancel() {
  text.value = ''
  emit('cancel')
}
</script>

<style scoped>
.reply-box { overflow: hidden; }
.reply-box__inner {
  display: flex; gap: 10px; padding: 12px 0 4px;
  border-top: 1px solid var(--gray-100); margin-top: 8px;
}
.reply-box__avatar {
  width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--coral), #ff8a8d);
  display: flex; align-items: center; justify-content: center;
  font-size: .6rem; font-weight: 700; color: #fff; margin-top: 2px;
}
.reply-box__right  { flex: 1; display: flex; flex-direction: column; gap: 8px; }
.reply-box__input  {
  width: 100%; border: 1.5px solid var(--gray-200); border-radius: 10px;
  padding: 9px 12px; font-size: .84rem; font-family: 'DM Sans', sans-serif;
  color: var(--indigo); resize: none; outline: none; box-sizing: border-box;
  transition: border-color var(--transition);
}
.reply-box__input:focus { border-color: var(--teal); }
.reply-box__actions { display: flex; gap: 8px; justify-content: flex-end; }
.reply-btn-cancel {
  background: none; border: 1.5px solid var(--gray-200); border-radius: 50px;
  padding: 5px 14px; font-size: .78rem; font-weight: 600; color: var(--gray-600);
  cursor: pointer; font-family: 'DM Sans', sans-serif; transition: all var(--transition);
}
.reply-btn-cancel:hover { border-color: var(--gray-400); color: var(--indigo); }
.reply-btn-send {
  background: var(--teal); color: #fff; border: none;
  padding: 5px 16px; border-radius: 50px; font-size: .78rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition);
}
.reply-btn-send:hover:not(:disabled) { background: var(--teal-dk); }
.reply-btn-send:disabled { opacity: .4; cursor: not-allowed; }

.reply-expand-enter-active, .reply-expand-leave-active { transition: all .2s ease; }
.reply-expand-enter-from, .reply-expand-leave-to { opacity: 0; transform: translateY(-6px); }
</style>