<template>
  <div class="svc-faq detail-card">
    <h3 class="svc-faq__title">Good to Know</h3>
    
    <div class="svc-faq__list">
      <div 
        class="svc-faq__item" 
        v-for="(item, idx) in faqItems" 
        :key="idx"
        :class="{ open: openIndex === idx }"
      >
        <button class="svc-faq__header" @click="toggle(idx)">
          <span class="svc-faq__q-icon">?</span>
          <span class="svc-faq__question">{{ item.q }}</span>
          <span class="svc-faq__chevron">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </span>
        </button>

        <div class="svc-faq__answer-wrapper">
          <div class="svc-faq__answer">
            {{ item.a }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  faqs: { type: Array, default: () => [] }
})

const defaultFaqs = [
  { q: 'What is the cancellation policy?', a: 'You can cancel for a full refund up to 24 hours before the service starts. Late cancellations may be subject to a fee.' },
  { q: 'Is there a deposit required?', a: 'For some high-value rentals or bookings, a small security deposit may be required at the time of service.' },
  { q: 'What should I bring?', a: 'Specific requirements depend on the service, but we always recommend bringing a digital or printed copy of your booking confirmation.' },
  { q: 'Can I change my booking time?', a: 'Rescheduling depends on provider availability. Please contact the provider directly via the message button to request a change.' }
]

const faqItems = props.faqs?.length ? props.faqs : defaultFaqs
const openIndex = ref(null)

function toggle(idx) {
  openIndex.value = openIndex.value === idx ? null : idx
}
</script>

<style scoped>
.svc-faq { margin-top: 32px; }
.svc-faq__title {
  font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 24px;
}

.svc-faq__list { display: flex; flex-direction: column; gap: 12px; }

.svc-faq__item {
  border: 1px solid var(--gray-100); border-radius: 14px; overflow: hidden;
  transition: all var(--transition); background: var(--gray-50);
}
.svc-faq__item.open { border-color: var(--coral); background: var(--white); box-shadow: var(--shadow-sm); }

.svc-faq__header {
  width: 100%; display: flex; align-items: center; gap: 14px;
  padding: 14px 20px; background: transparent; border: none; cursor: pointer;
  text-align: left; transition: all var(--transition);
}
.svc-faq__item.open .svc-faq__header { padding-bottom: 6px; }

.svc-faq__q-icon {
  width: 24px; height: 24px; border-radius: 50%; background: var(--indigo);
  color: #fff; display: flex; align-items: center; justify-content: center;
  font-size: .75rem; font-weight: 700; flex-shrink: 0;
}
.svc-faq__item.open .svc-faq__q-icon { background: var(--coral); }

.svc-faq__question {
  flex: 1; font-weight: 600; font-size: .92rem; color: var(--indigo);
}

.svc-faq__chevron {
  font-size: .7rem; color: var(--gray-400); flex-shrink: 0;
  transition: transform 0.3s ease;
}
.svc-faq__item.open .svc-faq__chevron { transform: rotate(180deg); color: var(--coral); }

/* Premium height animation */
.svc-faq__answer-wrapper {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.45s cubic-bezier(0.4, 0, 0.2, 1), 
              opacity 0.4s ease, 
              visibility 0.4s;
  overflow: hidden;
  visibility: hidden;
  opacity: 0;
}
.svc-faq__item.open .svc-faq__answer-wrapper {
  grid-template-rows: 1fr;
  visibility: visible;
  opacity: 1;
}

.svc-faq__answer {
  min-height: 0;
  padding: 0 20px 20px 58px;
  font-size: .9rem; color: var(--gray-600); line-height: 1.6;
  margin-top: -4px;
}

</style>
