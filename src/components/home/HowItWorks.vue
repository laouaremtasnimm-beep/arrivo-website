<template>
  <section class="section section-sand">
    <div class="section-header section-header--center">
      <div class="section-overline">Simple Process</div>
      <h2 class="section-title">How <em>Voyago</em> works</h2>
      <p class="section-sub" style="max-width:480px;margin-top:10px;">
        From browsing to booking in minutes — for tourists, agencies, and service providers alike.
      </p>
    </div>

    <div class="steps-grid">
      <div class="step-connector" aria-hidden="true" />

      <article class="step" v-for="step in steps" :key="step.num">
        <div class="step__num">{{ step.num }}</div>
        <div class="step__icon">{{ step.icon }}</div>
        <h3 class="step__title">{{ step.title }}</h3>
        <p class="step__desc">{{ step.desc }}</p>
      </article>
    </div>
  </section>
</template>

<script setup>
const steps = [
  {
    num: '01', icon: '🔍',
    title: 'Browse & Discover',
    desc:  'Explore thousands of destinations, packages and services tailored to your interests and budget.',
  },
  {
    num: '02', icon: '📋',
    title: 'Choose & Book',
    desc:  'Select your perfect package, fill in your details and confirm your reservation in minutes.',
  },
  {
    num: '03', icon: '✈️',
    title: 'Travel & Enjoy',
    desc:  'Receive your booking confirmation, connect with your provider and set off on your adventure.',
  },
]
</script>

<style scoped>
.steps-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px;
  position: relative;
}

/* Horizontal connector line */
.step-connector {
  position: absolute; top: 30px; left: 16%; right: 16%;
  height: 2px;
  background: linear-gradient(90deg, var(--teal), var(--coral));
  opacity: .30; pointer-events: none;
}

.step { text-align: center; position: relative; z-index: 1; }

.step__num {
  width: 60px; height: 60px; border-radius: 50%;
  background: var(--white); border: 2px solid var(--teal);
  display: flex; align-items: center; justify-content: center;
  font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700; color: var(--teal);
  margin: 0 auto 22px;
  box-shadow: var(--shadow);
}

.step__icon  { font-size: 1.8rem; margin-bottom: 14px; }
.step__title { font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 600; margin-bottom: 10px; }
.step__desc  { font-size: .88rem; color: var(--gray-600); line-height: 1.65; max-width: 220px; margin: 0 auto; }

@media (max-width: 768px) {
  .steps-grid { grid-template-columns: 1fr; gap: 36px; }
  .step-connector { display: none; }
  .step { text-align: left; display: flex; flex-direction: column; align-items: flex-start; }
  .step__num { margin: 0 0 18px; }
  .step__desc { max-width: none; }
}
</style>
