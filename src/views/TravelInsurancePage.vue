<template>
  <div class="insurance-page">
    <NavBar />

    <!-- ── Masthead ──────────────────────────────────────────────────────── -->
    <header class="masthead">
      <div class="masthead__rule" />
      <div class="masthead__inner">
        <div class="masthead__left">
          <p class="masthead__overline">Voyago Travel Tools</p>
          <h1 class="masthead__title">Travel<br><em>Insurance</em></h1>
          <p class="masthead__sub">
            Understand your coverage options, compare plans, and get an instant estimate — before you buy a single policy.
          </p>
          <div class="masthead__stats">
            <div class="masthead__stat">
              <span class="masthead__stat-num">8</span>
              <span class="masthead__stat-label">Plan Types</span>
            </div>
            <div class="masthead__stat-div" />
            <div class="masthead__stat">
              <span class="masthead__stat-num">$0</span>
              <span class="masthead__stat-label">To Compare</span>
            </div>
            <div class="masthead__stat-div" />
            <div class="masthead__stat">
              <span class="masthead__stat-num">2min</span>
              <span class="masthead__stat-label">Quick Quote</span>
            </div>
          </div>
          <div class="masthead__trust">
            <span class="trust-badge">🛡️ Independent advice — we don't sell policies</span>
          </div>
        </div>
        <div class="masthead__right">
          <!-- Quick quote estimator -->
          <div class="quote-card">
            <div class="quote-card__header">
              <span class="quote-card__icon">⚡</span>
              <div>
                <h3 class="quote-card__title">Instant Cost Estimator</h3>
                <p class="quote-card__sub">Get a ballpark figure in seconds</p>
              </div>
            </div>

            <div class="quote-form">
              <div class="quote-row">
                <div class="quote-field">
                  <label class="quote-label">Destination type</label>
                  <div class="quote-pills">
                    <button
                      v-for="d in destTypes" :key="d.key"
                      class="quote-pill" :class="{ active: quote.destType === d.key }"
                      @click="quote.destType = d.key"
                    >{{ d.label }}</button>
                  </div>
                </div>
              </div>
              <div class="quote-row quote-row--2">
                <div class="quote-field">
                  <label class="quote-label">Trip duration</label>
                  <select class="quote-select" v-model="quote.duration">
                    <option value="7">Up to 7 days</option>
                    <option value="14">8–14 days</option>
                    <option value="21">15–21 days</option>
                    <option value="30">22–30 days</option>
                    <option value="60">1–2 months</option>
                    <option value="90">3+ months</option>
                  </select>
                </div>
                <div class="quote-field">
                  <label class="quote-label">Travellers</label>
                  <div class="counter">
                    <button class="counter-btn" @click="quote.travellers = Math.max(1, quote.travellers - 1)">−</button>
                    <span class="counter-val">{{ quote.travellers }}</span>
                    <button class="counter-btn" @click="quote.travellers = Math.min(8, quote.travellers + 1)">+</button>
                  </div>
                </div>
              </div>
              <div class="quote-row">
                <div class="quote-field">
                  <label class="quote-label">Coverage level</label>
                  <div class="quote-pills">
                    <button
                      v-for="c in coverageLevels" :key="c.key"
                      class="quote-pill" :class="{ active: quote.coverage === c.key }"
                      @click="quote.coverage = c.key"
                    >{{ c.label }}</button>
                  </div>
                </div>
              </div>
              <div class="quote-row">
                <div class="quote-field">
                  <label class="quote-label">Add-ons</label>
                  <div class="quote-pills quote-pills--wrap">
                    <button
                      v-for="a in addons" :key="a.key"
                      class="quote-pill quote-pill--addon"
                      :class="{ active: quote.addons.includes(a.key) }"
                      @click="toggleAddon(a.key)"
                    >{{ a.label }}</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="quote-result">
              <div class="quote-result__range">
                <span class="quote-result__label">Estimated cost</span>
                <div class="quote-result__price">
                  <span class="quote-price-lo">${{ estimatedLow }}</span>
                  <span class="quote-price-sep">–</span>
                  <span class="quote-price-hi">${{ estimatedHigh }}</span>
                  <span class="quote-price-unit">per person</span>
                </div>
              </div>
              <p class="quote-result__note">Estimates based on market averages. Actual prices vary by insurer, age, and trip cost.</p>
              <button class="btn btn-coral quote-cta" @click="scrollToPlans">Compare plans →</button>
            </div>
          </div>
        </div>
      </div>
      <div class="masthead__rule" />
    </header>

    <!-- ── Why you need it ─────────────────────────────────────────────────── -->
    <div class="why-strip">
      <div class="why-strip__inner">
        <div class="why-item" v-for="w in whyItems" :key="w.id">
          <span class="why-item__icon">{{ w.icon }}</span>
          <div>
            <p class="why-item__title">{{ w.title }}</p>
            <p class="why-item__sub">{{ w.sub }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Plan comparison ────────────────────────────────────────────────── -->
    <div class="plans-section" ref="plansRef">
      <div class="plans-section__inner">
        <div class="plans-header">
          <p class="plans-overline">Find Your Plan</p>
          <h2 class="plans-title">Compare Coverage Types</h2>
          <p class="plans-sub">Not all travel insurance is created equal. Here's what each level actually covers.</p>
        </div>

        <!-- Filter tabs -->
        <div class="plan-tabs">
          <button
            v-for="t in planTabs" :key="t"
            class="plan-tab"
            :class="{ active: activePlanTab === t }"
            @click="activePlanTab = t"
          >{{ t }}</button>
        </div>

        <div class="plans-grid">
          <div
            v-for="plan in filteredPlans" :key="plan.id"
            class="plan-card"
            :class="{ 'plan-card--featured': plan.featured }"
          >
            <div class="plan-card__featured-label" v-if="plan.featured">Most Popular</div>
            <div class="plan-card__header" :style="{ background: plan.headerBg }">
              <span class="plan-card__icon">{{ plan.icon }}</span>
              <div>
                <h3 class="plan-card__name">{{ plan.name }}</h3>
                <p class="plan-card__tagline">{{ plan.tagline }}</p>
              </div>
            </div>
            <div class="plan-card__body">
              <div class="plan-card__price-row">
                <span class="plan-card__price">{{ plan.typicalCost }}</span>
                <span class="plan-card__price-note">{{ plan.costNote }}</span>
              </div>
              <p class="plan-card__desc">{{ plan.desc }}</p>
              <ul class="plan-card__covers">
                <li v-for="c in plan.covers" :key="c.label" :class="{ 'covered': c.covered, 'not-covered': !c.covered, 'partial': c.partial }">
                  <span class="cover-icon">{{ c.covered ? (c.partial ? '~' : '✓') : '✕' }}</span>
                  {{ c.label }}
                </li>
              </ul>
              <div class="plan-card__best">
                <span class="plan-card__best-label">Best for:</span>
                <span class="plan-card__best-val">{{ plan.bestFor }}</span>
              </div>
              <button class="plan-card__cta" :class="plan.featured ? 'btn btn-coral' : 'btn btn-outline'" @click="openPlanDetail(plan)">
                Learn more →
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Coverage explainer ─────────────────────────────────────────────── -->
    <div class="coverage-section">
      <div class="coverage-section__inner">
        <div class="coverage-header">
          <p class="coverage-overline">What's Covered</p>
          <h2 class="coverage-title">Understanding Your Policy</h2>
          <p class="coverage-sub">Click any coverage type to see what it includes — and what it doesn't.</p>
        </div>
        <div class="coverage-grid">
          <div
            v-for="cov in coverageItems" :key="cov.id"
            class="cov-card"
            :class="{ 'cov-card--open': activeCoverage === cov.id }"
            @click="activeCoverage = activeCoverage === cov.id ? null : cov.id"
          >
            <div class="cov-card__header">
              <div class="cov-card__icon-wrap" :style="{ background: cov.iconBg }">
                <span class="cov-card__icon">{{ cov.icon }}</span>
              </div>
              <div class="cov-card__title-group">
                <h4 class="cov-card__title">{{ cov.title }}</h4>
                <p class="cov-card__sub">{{ cov.summary }}</p>
              </div>
              <span class="cov-card__toggle">{{ activeCoverage === cov.id ? '−' : '+' }}</span>
            </div>
            <Transition name="expand">
              <div v-if="activeCoverage === cov.id" class="cov-card__detail">
                <div class="cov-detail__cols">
                  <div class="cov-detail__col cov-detail__col--yes">
                    <h5 class="cov-detail__heading cov-detail__heading--yes">✓ Typically covered</h5>
                    <ul>
                      <li v-for="item in cov.included" :key="item">{{ item }}</li>
                    </ul>
                  </div>
                  <div class="cov-detail__col cov-detail__col--no">
                    <h5 class="cov-detail__heading cov-detail__heading--no">✕ Common exclusions</h5>
                    <ul>
                      <li v-for="item in cov.excluded" :key="item">{{ item }}</li>
                    </ul>
                  </div>
                </div>
                <div class="cov-detail__tip" v-if="cov.tip">
                  <span>💡</span> {{ cov.tip }}
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Claims guide ──────────────────────────────────────────────────── -->
    <div class="claims-section">
      <div class="claims-section__inner">
        <div class="claims-left">
          <p class="claims-overline">When Things Go Wrong</p>
          <h2 class="claims-title">How to Make a Claim</h2>
          <p class="claims-sub">Filing a travel insurance claim doesn't have to be stressful. Follow these steps and keep good records from day one.</p>
          <div class="claims-steps">
            <div class="claims-step" v-for="(step, i) in claimSteps" :key="i">
              <div class="claims-step__num">{{ i + 1 }}</div>
              <div class="claims-step__content">
                <h4 class="claims-step__title">{{ step.title }}</h4>
                <p class="claims-step__body">{{ step.body }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="claims-right">
          <div class="claims-doc-card">
            <h4 class="claims-doc-card__title">📁 Documents to Keep</h4>
            <p class="claims-doc-card__sub">Save digital copies of everything from day one.</p>
            <ul class="claims-doc-list">
              <li v-for="doc in claimDocs" :key="doc" class="claims-doc-item">
                <span class="claims-doc-icon">📄</span>{{ doc }}
              </li>
            </ul>
          </div>
          <div class="claims-warning-card">
            <h4 class="claims-warning-card__title">⚠️ Common reasons claims are denied</h4>
            <ul class="claims-warning-list">
              <li v-for="w in claimWarnings" :key="w">{{ w }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- ── FAQ ────────────────────────────────────────────────────────────── -->
    <div class="faq-section">
      <div class="faq-section__inner">
        <div class="faq-header">
          <p class="faq-overline">Questions & Answers</p>
          <h2 class="faq-title">Frequently Asked</h2>
        </div>
        <div class="faq-list">
          <div
            v-for="(faq, i) in faqs" :key="i"
            class="faq-item"
            :class="{ 'faq-item--open': activeFaq === i }"
            @click="activeFaq = activeFaq === i ? null : i"
          >
            <div class="faq-item__q">
              <span>{{ faq.q }}</span>
              <span class="faq-toggle">{{ activeFaq === i ? '−' : '+' }}</span>
            </div>
            <Transition name="expand">
              <p v-if="activeFaq === i" class="faq-item__a">{{ faq.a }}</p>
            </Transition>
          </div>
        </div>
      </div>
    </div>

    <!-- ── CTA band ───────────────────────────────────────────────────────── -->
    <div class="cta-band">
      <div class="cta-band__inner">
        <div class="cta-band__text">
          <h3 class="cta-band__title">Ready to explore the world?</h3>
          <p class="cta-band__sub">Browse destinations and packages — your adventure is one click away.</p>
        </div>
        <div class="cta-band__actions">
          <RouterLink to="/destinations" class="btn btn-coral">Explore destinations →</RouterLink>
          <RouterLink to="/packages" class="btn btn-outline-light">View packages</RouterLink>
        </div>
      </div>
    </div>

    <!-- ── Plan detail modal ──────────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="activePlan" class="modal-backdrop" @click.self="activePlan = null">
          <div class="plan-modal">
            <button class="plan-modal__close" @click="activePlan = null">✕</button>
            <div class="plan-modal__hero" :style="{ background: activePlan.headerBg }">
              <span class="plan-modal__icon">{{ activePlan.icon }}</span>
              <div class="plan-modal__hero-body">
                <h2 class="plan-modal__title">{{ activePlan.name }}</h2>
                <p class="plan-modal__sub">{{ activePlan.tagline }}</p>
              </div>
            </div>
            <div class="plan-modal__content">
              <p class="plan-modal__lead">{{ activePlan.fullDesc }}</p>

              <div class="plan-modal__sections">
                <div class="plan-modal__section" v-for="s in activePlan.sections" :key="s.heading">
                  <h4 class="plan-modal__section-title">{{ s.heading }}</h4>
                  <p class="plan-modal__section-body">{{ s.body }}</p>
                </div>
              </div>

              <!-- Limits table -->
              <div class="plan-modal__limits" v-if="activePlan.limits?.length">
                <h4 class="plan-modal__limits-title">💰 Typical Coverage Limits</h4>
                <div class="limits-table">
                  <div class="limits-row" v-for="l in activePlan.limits" :key="l.item">
                    <span class="limits-item">{{ l.item }}</span>
                    <span class="limits-amount">{{ l.amount }}</span>
                  </div>
                </div>
              </div>

              <div class="plan-modal__cta">
                <RouterLink to="/search" class="btn btn-coral" @click="activePlan = null">Find a trip that needs this cover →</RouterLink>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <SiteFooter />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import NavBar     from '@/components/home/NavBar.vue'
import SiteFooter from '@/components/home/SiteFooter.vue'

const plansRef = ref(null)
function scrollToPlans() {
  plansRef.value?.scrollIntoView({ behavior: 'smooth' })
}

// ── Quote estimator ──────────────────────────────────────────────────────────
const destTypes = [
  { key: 'europe',    label: '🇪🇺 Europe'    },
  { key: 'worldwide', label: '🌍 Worldwide'  },
  { key: 'usa',       label: '🇺🇸 USA/Canada' },
  { key: 'asia',      label: '🌏 Asia'       },
]
const coverageLevels = [
  { key: 'basic',      label: 'Basic'      },
  { key: 'standard',   label: 'Standard'   },
  { key: 'premium',    label: 'Premium'    },
]
const addons = [
  { key: 'adventure',  label: '🧗 Adventure sports' },
  { key: 'gadgets',    label: '💻 Gadgets'           },
  { key: 'cancel',     label: '🔄 Cancel for any reason' },
  { key: 'cruise',     label: '🚢 Cruise'            },
]

const quote = ref({
  destType:   'europe',
  duration:   '14',
  travellers: 1,
  coverage:   'standard',
  addons:     [],
})

function toggleAddon(key) {
  const idx = quote.value.addons.indexOf(key)
  if (idx === -1) quote.value.addons.push(key)
  else quote.value.addons.splice(idx, 1)
}

// Base rates ($ per person per day, market estimates)
const baseRates = {
  europe:    { basic: 1.2,  standard: 2.0,  premium: 3.5  },
  worldwide: { basic: 1.8,  standard: 3.0,  premium: 5.0  },
  usa:       { basic: 3.5,  standard: 5.5,  premium: 8.5  },
  asia:      { basic: 1.0,  standard: 1.8,  premium: 3.0  },
}
const addonRates = { adventure: 0.6, gadgets: 0.4, cancel: 1.2, cruise: 0.5 }

const estimatedLow = computed(() => {
  const days   = Number(quote.value.duration)
  const base   = baseRates[quote.value.destType][quote.value.coverage]
  const extras = quote.value.addons.reduce((s, k) => s + (addonRates[k] || 0), 0)
  return Math.round((base + extras) * days * 0.85)
})
const estimatedHigh = computed(() => {
  const days   = Number(quote.value.duration)
  const base   = baseRates[quote.value.destType][quote.value.coverage]
  const extras = quote.value.addons.reduce((s, k) => s + (addonRates[k] || 0), 0)
  return Math.round((base + extras) * days * 1.25)
})

// ── Why items ────────────────────────────────────────────────────────────────
const whyItems = [
  { id: 1, icon: '🏥', title: 'Medical emergencies abroad cost thousands', sub: 'A hospital stay in the US can exceed $10,000/night. One evacuation flight from Asia averages $25,000–$80,000.' },
  { id: 2, icon: '✈️', title: 'Cancellations are more common than you think', sub: '1 in 6 trips faces some disruption. Cancellation cover protects your full trip cost — not just the flight.' },
  { id: 3, icon: '🧳', title: 'Baggage loss happens on 26 million bags/year', sub: 'Airlines are only liable for ~$1,700 on domestic and ~$1,700 on international flights — often far less than what you pack.' },
  { id: 4, icon: '⚖️', title: 'Liability can ruin you financially', sub: 'Accidentally injuring someone or damaging property abroad can result in lawsuits. Personal liability cover protects your assets.' },
]

// ── Plan comparison ──────────────────────────────────────────────────────────
const planTabs     = ['All', 'Short Trip', 'Long Stay', 'Adventure', 'Business']
const activePlanTab = ref('All')

const plans = ref([
  {
    id: 1, name: 'Basic Cover', tagline: 'Medical essentials only', icon: '🩹',
    category: 'Short Trip',
    headerBg: 'linear-gradient(135deg, #f0f4ff, #e8f0fe)',
    typicalCost: '$15–$35', costNote: '/ week / person',
    featured: false,
    desc: 'Entry-level protection covering emergency medical expenses and repatriation. No trip cancellation cover.',
    bestFor: 'Budget travellers visiting low-risk destinations',
    covers: [
      { label: 'Emergency medical', covered: true },
      { label: 'Repatriation', covered: true },
      { label: 'Trip cancellation', covered: false },
      { label: 'Baggage & belongings', covered: false },
      { label: 'Travel delay', covered: false },
      { label: 'Adventure sports', covered: false },
    ],
    fullDesc: 'Basic cover is the minimum viable travel insurance. It\'s designed for healthy, young travellers taking short trips to countries with good healthcare, who want medical protection without paying for extras they don\'t need.',
    sections: [
      { heading: 'Who it\'s right for', body: 'Best suited for EU citizens travelling within the EU (where EHIC/GHIC cards offer some baseline cover), young travellers with few bookings to protect, and last-minute trips where cancellation cover doesn\'t apply anyway.' },
      { heading: 'What it doesn\'t cover', body: 'Basic policies almost never cover trip cancellation or interruption, lost or stolen baggage, travel delays, or any adventure or hazardous activities. If your trip involves pre-paid non-refundable bookings, you almost certainly need at least Standard cover.' },
    ],
    limits: [
      { item: 'Emergency medical', amount: 'Up to $500,000' },
      { item: 'Repatriation', amount: 'Up to $250,000' },
      { item: 'Dental emergency', amount: 'Up to $500' },
      { item: 'Personal liability', amount: 'Up to $1,000,000' },
    ],
  },
  {
    id: 2, name: 'Standard Cover', tagline: 'The smart traveller\'s choice', icon: '🛡️',
    category: 'Short Trip',
    headerBg: 'linear-gradient(135deg, #fff4f0, #ffe8e0)',
    typicalCost: '$40–$90', costNote: '/ week / person',
    featured: true,
    desc: 'Comprehensive protection including medical, cancellation, baggage, and delays. Covers most standard holiday scenarios.',
    bestFor: 'Most holiday travellers with pre-booked trips',
    covers: [
      { label: 'Emergency medical', covered: true },
      { label: 'Repatriation', covered: true },
      { label: 'Trip cancellation', covered: true },
      { label: 'Baggage & belongings', covered: true },
      { label: 'Travel delay', covered: true },
      { label: 'Adventure sports', covered: false },
    ],
    fullDesc: 'Standard cover is what most travellers actually need. It combines all the essential protections into one policy — medical, cancellation, baggage, and delay cover — at a price that makes sense for a typical 1–2 week holiday.',
    sections: [
      { heading: 'Trip cancellation and interruption', body: 'Standard cover will reimburse your pre-paid, non-refundable trip costs if you have to cancel or cut short due to illness, injury, bereavement, or other covered reasons. Always read the \'covered reasons\' list carefully — \'fear of travel\' is rarely covered on standard policies.' },
      { heading: 'Baggage and personal effects', body: 'Standard policies typically cover up to $1,500–$3,000 for lost, stolen or damaged baggage. High-value items (laptops, cameras, jewellery) usually have per-item limits of $300–$500. If you travel with expensive gear, consider a gadget add-on or specialist cover.' },
    ],
    limits: [
      { item: 'Emergency medical', amount: 'Up to $5,000,000' },
      { item: 'Trip cancellation', amount: 'Up to $5,000' },
      { item: 'Baggage & effects', amount: 'Up to $2,500' },
      { item: 'Travel delay', amount: '$200 after 6 hours' },
      { item: 'Personal liability', amount: 'Up to $2,000,000' },
      { item: 'Legal expenses', amount: 'Up to $25,000' },
    ],
  },
  {
    id: 3, name: 'Premium Cover', tagline: 'Complete peace of mind', icon: '⭐',
    category: 'Short Trip',
    headerBg: 'linear-gradient(135deg, #f5f0ff, #ede0ff)',
    typicalCost: '$90–$200', costNote: '/ week / person',
    featured: false,
    desc: 'Top-tier protection with the highest limits, cancel-for-any-reason option, concierge services, and zero-excess claims.',
    bestFor: 'Luxury travellers, frequent flyers, high-value trips',
    covers: [
      { label: 'Emergency medical', covered: true },
      { label: 'Repatriation', covered: true },
      { label: 'Trip cancellation', covered: true },
      { label: 'Baggage & belongings', covered: true },
      { label: 'Travel delay', covered: true },
      { label: 'Adventure sports', covered: true, partial: true },
    ],
    fullDesc: 'Premium cover is for travellers who want the highest limits and the broadest protection. Zero excess, concierge assistance 24/7, and cancel-for-any-reason options make this the only policy that truly has you covered in any scenario.',
    sections: [
      { heading: 'Cancel for any reason (CFAR)', body: 'Unlike standard policies, premium plans often include a CFAR add-on that reimburses 50–75% of your trip cost regardless of the reason for cancellation. This must usually be purchased within 14–21 days of your initial trip deposit.' },
      { heading: 'Zero excess', body: 'Premium policies typically have zero excess (deductible) on claims, meaning you don\'t pay the first portion out of pocket. On a medical claim in the US or Canada, this can save you thousands.' },
    ],
    limits: [
      { item: 'Emergency medical', amount: 'Unlimited' },
      { item: 'Trip cancellation', amount: 'Up to $15,000' },
      { item: 'Baggage & effects', amount: 'Up to $7,500' },
      { item: 'Single item limit', amount: 'Up to $1,500' },
      { item: 'Travel delay', amount: '$500 after 4 hours' },
      { item: 'Personal liability', amount: 'Up to $5,000,000' },
    ],
  },
  {
    id: 4, name: 'Backpacker / Long Stay', tagline: 'For the long-term traveller', icon: '🎒',
    category: 'Long Stay',
    headerBg: 'linear-gradient(135deg, #f0fff8, #e0fff2)',
    typicalCost: '$60–$150', costNote: '/ month / person',
    featured: false,
    desc: 'Designed for trips of 60 days to 18 months. Covers all standard risks plus working holiday extensions and multi-country travel.',
    bestFor: 'Gap year travellers, digital nomads, long-term explorers',
    covers: [
      { label: 'Emergency medical', covered: true },
      { label: 'Repatriation', covered: true },
      { label: 'Trip cancellation', covered: true, partial: true },
      { label: 'Baggage & belongings', covered: true },
      { label: 'Travel delay', covered: true },
      { label: 'Working holiday extension', covered: true },
    ],
    fullDesc: 'Standard holiday policies typically cap out at 30–45 days. Backpacker or long-stay insurance is designed for the traveller who is gone for months at a time, often across multiple countries and continents.',
    sections: [
      { heading: 'Working while abroad', body: 'Many long-stay policies allow you to work in paid employment or volunteer work abroad without voiding your cover. Always declare this — working on a standard tourist policy is a common exclusion and will invalidate your medical claims.' },
      { heading: 'Extending your policy', body: 'Most long-stay insurers allow mid-trip extensions via a phone call or app. This is critical for the traveller whose 3 months becomes 6. Some policies auto-extend if you haven\'t returned by the end date.' },
    ],
    limits: [
      { item: 'Emergency medical', amount: 'Up to $5,000,000' },
      { item: 'Trip cancellation', amount: 'Up to $3,000' },
      { item: 'Baggage & effects', amount: 'Up to $2,000' },
      { item: 'Personal liability', amount: 'Up to $2,000,000' },
      { item: 'Maximum trip duration', amount: '12–18 months' },
    ],
  },
  {
    id: 5, name: 'Adventure Sports Cover', tagline: 'For the thrillseeker', icon: '🧗',
    category: 'Adventure',
    headerBg: 'linear-gradient(135deg, #fff8e0, #fff0c0)',
    typicalCost: '$25–$60', costNote: '/ week add-on',
    featured: false,
    desc: 'Extension add-on (or standalone) covering hazardous activities: skiing, scuba diving, surfing, trekking above 4,000m, and more.',
    bestFor: 'Skiers, surfers, hikers, divers, adrenaline travellers',
    covers: [
      { label: 'Emergency medical (sport injury)', covered: true },
      { label: 'Ski equipment', covered: true },
      { label: 'Piste closure', covered: true },
      { label: 'Search & rescue', covered: true },
      { label: 'High altitude trekking', covered: true },
      { label: 'Motorised sports', covered: false },
    ],
    fullDesc: 'Standard travel insurance excludes most hazardous activities by default. Adventure sports cover adds a specialist layer of protection for the activities most likely to cause medical emergencies — and the most expensive to deal with when they do.',
    sections: [
      { heading: 'Skiing and winter sports', body: 'A ski-specific add-on covers piste closure (cash per day if the resort can\'t open), ski equipment hire if yours is lost or damaged, and crucially, helicopter rescue from the mountain — which can cost €5,000–€20,000 without cover.' },
      { heading: 'Scuba and water sports', body: 'Standard medical cover may refuse claims related to scuba injuries (decompression sickness treatment in a hyperbaric chamber can cost $40,000+). An adventure add-on specifically includes these. Check the maximum diving depth covered — usually 30–40m.' },
    ],
    limits: [
      { item: 'Mountain rescue / helicopter', amount: 'Up to $25,000' },
      { item: 'Ski equipment', amount: 'Up to $1,000' },
      { item: 'Piste closure', amount: '$50/day up to $500' },
      { item: 'Emergency medical (sports)', amount: 'Up to $5,000,000' },
    ],
  },
  {
    id: 6, name: 'Annual Multi-Trip', tagline: 'For the frequent traveller', icon: '🌍',
    category: 'Business',
    headerBg: 'linear-gradient(135deg, #f0f8ff, #e0f0ff)',
    typicalCost: '$150–$400', costNote: '/ year / person',
    featured: false,
    desc: 'Covers unlimited trips in a 12-month period (each trip usually capped at 30–45 days). Much cheaper than buying per-trip for 4+ trips/year.',
    bestFor: 'Business travellers, frequent holiday makers, anyone taking 4+ trips/year',
    covers: [
      { label: 'Emergency medical', covered: true },
      { label: 'Trip cancellation', covered: true },
      { label: 'Baggage & belongings', covered: true },
      { label: 'Travel delay', covered: true },
      { label: 'Business equipment', covered: true, partial: true },
      { label: 'Adventure sports', covered: false },
    ],
    fullDesc: 'If you travel more than 3–4 times per year, an annual multi-trip policy almost always works out cheaper than buying per-trip insurance each time. One policy, one premium, unlimited trips for 12 months.',
    sections: [
      { heading: 'Per-trip day limits', body: 'Annual policies cap each individual trip at 30 or 45 days. If you\'re taking a longer trip, you\'ll need to upgrade or purchase a separate long-stay policy for that trip. Check the maximum trip duration carefully before purchasing.' },
      { heading: 'Business travel provisions', body: 'Business-grade annual policies add cover for laptops, tablets and other business equipment (up to $1,500–$3,000), business liability, and missed meeting compensation due to delays. Always declare if your trip has a business purpose.' },
    ],
    limits: [
      { item: 'Emergency medical', amount: 'Up to $5,000,000' },
      { item: 'Trip cancellation', amount: 'Up to $5,000 per trip' },
      { item: 'Baggage', amount: 'Up to $2,500' },
      { item: 'Max trip length', amount: '30 or 45 days' },
      { item: 'Business equipment', amount: 'Up to $2,000' },
    ],
  },
])

const filteredPlans = computed(() => {
  if (activePlanTab.value === 'All') return plans.value
  return plans.value.filter(p => p.category === activePlanTab.value)
})

const activePlan = ref(null)
function openPlanDetail(plan) { activePlan.value = plan }

// ── Coverage explainer ────────────────────────────────────────────────────────
const activeCoverage = ref(null)
const coverageItems = [
  {
    id: 1, icon: '🏥', iconBg: 'rgba(255,90,95,.1)',
    title: 'Emergency Medical & Hospitalisation',
    summary: 'The most important coverage — especially outside your home country.',
    included: [
      'Emergency hospital treatment', 'Ambulance costs', 'Prescription medicines',
      'Emergency dental treatment', 'In-patient and out-patient care',
      'Medical repatriation to home country',
    ],
    excluded: [
      'Pre-existing medical conditions (unless declared)', 'Non-emergency treatment',
      'Cosmetic procedures', 'Treatment you chose to delay until travelling',
      'Medical tourism (travelling specifically to receive treatment)',
    ],
    tip: 'Always declare pre-existing conditions — even if it costs more. An undeclared condition that causes a claim will void your entire policy.',
  },
  {
    id: 2, icon: '✈️', iconBg: 'rgba(46,196,182,.1)',
    title: 'Trip Cancellation & Interruption',
    summary: 'Reimburses non-refundable costs if your trip is cancelled or cut short.',
    included: [
      'Cancellation due to illness or injury (you or a close relative)', 'Death of a close family member',
      'Redundancy / job loss (usually 12 months+ employment)', 'Jury service or court subpoena',
      'Natural disaster making your home uninhabitable', 'Travel warnings issued after booking',
    ],
    excluded: [
      'Cancellation due to fear of travel', 'Pre-existing conditions not declared',
      'Changing your mind (unless CFAR add-on purchased)', 'Airline strikes known before purchase',
      'Cancellations covered by the airline or tour operator under their own policy',
    ],
    tip: 'Cancel For Any Reason (CFAR) policies cost 40–50% more but are the only way to be protected against travel advisories and pandemics. Must be bought within 14–21 days of first trip payment.',
  },
  {
    id: 3, icon: '🧳', iconBg: 'rgba(99,102,241,.1)',
    title: 'Baggage, Luggage & Personal Effects',
    summary: 'Covers lost, stolen, or damaged belongings while travelling.',
    included: [
      'Lost or stolen checked baggage', 'Theft of personal items from your person or accommodation',
      'Damage to luggage', 'Emergency clothing purchases if bags delayed 12+ hours',
      'Passport replacement costs',
    ],
    excluded: [
      'Items left unattended in a public place', 'Valuables left in checked luggage',
      'Sports equipment in use', 'Cash above a small limit (usually $200–$300)',
      'Items not reported to police within 24 hours of discovery',
    ],
    tip: 'Always get a police report within 24 hours of any theft — without it, the insurer will almost certainly deny the claim.',
  },
  {
    id: 4, icon: '⏰', iconBg: 'rgba(245,158,11,.1)',
    title: 'Travel Delay & Missed Departure',
    summary: 'Compensates for extra costs when delays disrupt your journey.',
    included: [
      'Meals and accommodation during delays of 6+ hours', 'Alternative transport if you miss a connection',
      'Compensation after delays of 4+ hours (premium plans)', 'Costs if you miss departure due to accident or breakdown',
      'Compensation for delayed return home',
    ],
    excluded: [
      'Delays under the minimum threshold (usually 6–12 hours on basic plans)',
      'Delays caused by strikes known before purchase', 'Missed departures due to traffic (unless documented accident)',
      'Compensation if the airline already paid under EU/UK261',
    ],
    tip: 'Under EU Regulation 261/2004, airlines operating from EU airports owe you €250–€600 for delays over 3 hours. Always claim from the airline first — your insurer will ask about this.',
  },
  {
    id: 5, icon: '⚖️', iconBg: 'rgba(45,49,66,.08)',
    title: 'Personal Liability',
    summary: 'Protects you if you accidentally injure someone or damage property.',
    included: [
      'Accidental injury to a third party', 'Accidental damage to property',
      'Legal defence costs', 'Court-awarded damages (up to policy limit)',
      'Incidents while abroad on holiday',
    ],
    excluded: [
      'Liability arising from your profession or business', 'Damage to property you\'ve rented',
      'Incidents involving motor vehicles', 'Deliberate or criminal acts',
      'Liability already covered by home insurance or other policies',
    ],
    tip: 'If you rent a car abroad, you need the Collision Damage Waiver from the rental company — personal liability in travel insurance does not cover motor vehicle incidents.',
  },
  {
    id: 6, icon: '🚑', iconBg: 'rgba(46,196,182,.08)',
    title: 'Emergency Evacuation & Repatriation',
    summary: 'Covers the cost of getting you home in a medical emergency.',
    included: [
      'Medical evacuation by air ambulance', 'Repatriation of remains in case of death',
      'Accompanying family member travel costs', 'Non-medical evacuation from natural disaster or civil unrest',
      'Return of dependant children if you are hospitalised',
    ],
    excluded: [
      'Evacuation not authorised by the insurer\'s medical team in advance',
      'Return home for comfort rather than medical necessity',
      'Evacuation from countries under \'Do Not Travel\' advisory at time of purchase',
    ],
    tip: 'In a medical emergency, call your insurer\'s emergency line BEFORE organising an evacuation. Unauthorised evacuations may not be reimbursed — the insurer wants to coordinate and control costs.',
  },
]

// ── Claims guide ─────────────────────────────────────────────────────────────
const claimSteps = [
  { title: 'Contact your insurer immediately', body: 'Most policies require you to notify the insurer within 24–72 hours of an incident, especially for medical emergencies or theft. Have your policy number ready.' },
  { title: 'Get official documentation', body: 'For medical claims: get itemised bills. For theft: get a police report within 24 hours. For delays: get a written statement from the airline or carrier.' },
  { title: 'Keep all receipts and records', body: 'Document every out-of-pocket expense with a receipt. Take photos of damaged items, your hospital surroundings, and any relevant evidence.' },
  { title: 'Submit your claim online', body: 'Most modern insurers have an app or online portal. Upload all supporting documents in one go. Incomplete submissions are the #1 cause of delays.' },
  { title: 'Follow up regularly', body: 'Claims can take 5–28 business days. If you haven\'t heard back within the stated timeframe, follow up. If a claim is denied, you have the right to appeal.' },
]
const claimDocs = [
  'Your insurance policy document and emergency number',
  'Itemised medical bills and prescriptions',
  'Police report (for theft, within 24 hours)',
  'Airline written confirmation of delay or cancellation',
  'Property Irregularity Report (PIR) from airline for baggage',
  'Receipts for all emergency purchases',
  'Photos of damaged items, injuries, or incident location',
  'Doctor\'s note confirming inability to travel (for cancellation)',
]
const claimWarnings = [
  'No police report filed within 24 hours of theft',
  'Pre-existing condition not declared at time of purchase',
  'Incident occurred under the influence of alcohol',
  'Activity was not covered under your plan (e.g. unlisted sport)',
  'Claim made for items left unattended in a public place',
  'You didn\'t call the emergency line before self-organising evacuation',
  'Travelling against an official government advice warning',
]

// ── FAQ ───────────────────────────────────────────────────────────────────────
const activeFaq = ref(null)
const faqs = [
  { q: 'Is travel insurance mandatory?', a: 'Not always, but it\'s required for a Schengen visa (minimum €30,000 medical cover) and often expected by cruise lines. The Maldives and a handful of other countries also require proof of insurance on arrival. Even where it\'s not mandatory, the financial risk of travelling without it is enormous — a single medical evacuation can cost more than your entire trip.' },
  { q: 'Can I get travel insurance after I\'ve already left home?', a: 'Some insurers allow you to purchase coverage after departure, but options are limited and more expensive. Trip cancellation cover is almost never available once you\'ve left — it only covers future events, and your trip has already started. Medical-only policies are sometimes available mid-trip.' },
  { q: 'What\'s the difference between excess and premium?', a: 'The premium is what you pay to buy the policy. The excess (or deductible) is the amount you pay out-of-pocket when you make a claim — for example, a €100 excess means the insurer pays everything above €100. Higher excess = lower premium, and vice versa. Zero-excess policies (available on premium plans) cost more upfront but mean you keep more if you claim.' },
  { q: 'Does travel insurance cover my phone?', a: 'Standard baggage cover usually has a single-item limit of $300–$500, which is less than most smartphones are worth. Some policies have a gadget add-on that increases the limit and specifically covers phones, laptops, cameras and tablets (typically up to $1,000–$2,000 per item). Check whether theft from a vehicle or unattended property is covered.' },
  { q: 'I have a European Health Insurance Card (EHIC). Do I still need insurance?', a: 'Yes. The EHIC (or GHIC in the UK post-Brexit) only covers emergency state healthcare in EU countries — it doesn\'t cover repatriation, trip cancellation, baggage loss, delays, or private hospital care. It\'s a useful supplement, not a replacement for insurance.' },
  { q: 'Can I buy insurance for someone else (as a gift)?', a: 'Yes — most insurers allow you to purchase a policy for a named traveller other than yourself. You\'ll need their full name, date of birth, and passport details. Annual multi-trip policies are a popular and thoughtful gift for frequent travellers.' },
]
</script>

<style scoped>
/* ── Page ──────────────────────────────────────────────────────────────────── */
.insurance-page {
  min-height: 100vh;
  background: #faf8f3;
  padding-top: 68px;
  font-family: 'DM Sans', sans-serif;
}

/* ── Masthead ──────────────────────────────────────────────────────────────── */
.masthead { padding: 0 5%; background: #faf8f3; }
.masthead__rule { height: 2px; background: var(--indigo); margin: 0; }
.masthead__inner {
  display: grid; grid-template-columns: 1fr 1.5fr;
  gap: 56px; align-items: start; padding: 52px 0 48px;
}
.masthead__overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--coral); margin-bottom: 16px;
}
.masthead__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(3rem, 6vw, 5.5rem);
  font-weight: 700; line-height: .92;
  color: var(--indigo); margin-bottom: 20px;
}
.masthead__title em { color: var(--coral); font-style: italic; }
.masthead__sub {
  font-size: 1rem; color: #6b6655; line-height: 1.7;
  max-width: 360px; margin-bottom: 32px;
}
.masthead__stats { display: flex; align-items: center; gap: 24px; margin-bottom: 20px; }
.masthead__stat { display: flex; flex-direction: column; }
.masthead__stat-num {
  font-family: 'Fraunces', serif; font-size: 2.4rem; font-weight: 700;
  color: var(--indigo); line-height: 1;
}
.masthead__stat-label {
  font-size: .72rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: #a09880; margin-top: 3px;
}
.masthead__stat-div { width: 1px; height: 48px; background: var(--gray-200); }
.trust-badge {
  display: inline-block; font-size: .78rem; font-weight: 600; color: #6b6655;
  background: rgba(46,196,182,.1); border: 1px solid rgba(46,196,182,.25);
  padding: 6px 14px; border-radius: 50px;
}

/* ── Quote card ───────────────────────────────────────────────────────────── */
.quote-card {
  background: var(--white); border-radius: 20px;
  box-shadow: 0 20px 60px rgba(45,49,66,.14);
  padding: 28px;
}
.quote-card__header {
  display: flex; align-items: center; gap: 14px; margin-bottom: 22px;
}
.quote-card__icon { font-size: 1.6rem; }
.quote-card__title {
  font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700;
  color: var(--indigo); margin: 0;
}
.quote-card__sub { font-size: .8rem; color: #a09880; margin: 2px 0 0; }

.quote-form { display: flex; flex-direction: column; gap: 16px; margin-bottom: 20px; }
.quote-row { display: flex; flex-direction: column; gap: 8px; }
.quote-row--2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.quote-field { display: flex; flex-direction: column; gap: 6px; }
.quote-label {
  font-size: .7rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .08em; color: #a09880;
}
.quote-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.quote-pills--wrap { flex-wrap: wrap; }
.quote-pill {
  padding: 5px 12px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .78rem; font-weight: 600;
  color: #6b6655; cursor: pointer; transition: all var(--transition); white-space: nowrap;
}
.quote-pill:hover { border-color: var(--coral); color: var(--coral); }
.quote-pill.active { background: var(--coral); border-color: var(--coral); color: #fff; }
.quote-pill--addon.active { background: var(--indigo); border-color: var(--indigo); color: #fff; }

.quote-select {
  width: 100%; padding: 9px 12px; border-radius: 10px;
  border: 1.5px solid var(--gray-200); background: var(--gray-50);
  font-family: 'DM Sans', sans-serif; font-size: .86rem; color: var(--indigo);
  outline: none; cursor: pointer; transition: border-color var(--transition);
}
.quote-select:focus { border-color: var(--coral); }

.counter {
  display: flex; align-items: center; gap: 0;
  border: 1.5px solid var(--gray-200); border-radius: 10px;
  overflow: hidden; width: fit-content;
}
.counter-btn {
  width: 36px; height: 36px; border: none; background: var(--gray-50);
  font-size: 1.1rem; font-weight: 700; color: var(--indigo);
  cursor: pointer; transition: background var(--transition);
  display: flex; align-items: center; justify-content: center;
}
.counter-btn:hover { background: var(--coral-lt); color: var(--coral); }
.counter-val {
  min-width: 36px; text-align: center; font-size: .9rem; font-weight: 700;
  color: var(--indigo);
}

/* Quote result */
.quote-result {
  background: var(--gray-50); border-radius: 12px; padding: 18px 20px;
  border: 1px solid var(--gray-200);
}
.quote-result__range { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.quote-result__label { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #a09880; }
.quote-result__price { display: flex; align-items: baseline; gap: 4px; }
.quote-price-lo  { font-family: 'Fraunces', serif; font-size: 1.8rem; font-weight: 700; color: var(--coral); }
.quote-price-sep { font-size: 1.1rem; color: #a09880; }
.quote-price-hi  { font-family: 'Fraunces', serif; font-size: 1.8rem; font-weight: 700; color: var(--indigo); }
.quote-price-unit { font-size: .75rem; color: #a09880; margin-left: 4px; }
.quote-result__note { font-size: .75rem; color: #a09880; line-height: 1.5; margin-bottom: 14px; }
.quote-cta { width: 100%; border-radius: 10px; padding: 12px; font-size: .88rem; }

/* ── Why strip ────────────────────────────────────────────────────────────── */
.why-strip { background: var(--white); border-top: 1px solid var(--gray-200); border-bottom: 1px solid var(--gray-200); padding: 32px 5%; }
.why-strip__inner { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; }
.why-item { display: flex; align-items: flex-start; gap: 12px; }
.why-item__icon { font-size: 1.5rem; flex-shrink: 0; margin-top: 2px; }
.why-item__title { font-size: .88rem; font-weight: 700; color: var(--indigo); margin-bottom: 4px; line-height: 1.3; }
.why-item__sub { font-size: .78rem; color: #6b6655; line-height: 1.5; }

/* ── Plans section ────────────────────────────────────────────────────────── */
.plans-section { padding: 72px 5%; background: #faf8f3; }
.plans-section__inner { max-width: 1200px; margin: 0 auto; }
.plans-header { text-align: center; margin-bottom: 32px; }
.plans-overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--coral); margin-bottom: 12px;
}
.plans-title {
  font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 3vw, 2.6rem);
  font-weight: 700; color: var(--indigo); margin-bottom: 10px;
}
.plans-sub { font-size: .92rem; color: #6b6655; max-width: 500px; margin: 0 auto; }

.plan-tabs {
  display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-bottom: 36px;
}
.plan-tab {
  padding: 8px 18px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .82rem; font-weight: 600;
  color: #6b6655; cursor: pointer; transition: all var(--transition);
}
.plan-tab:hover { border-color: var(--coral); color: var(--coral); }
.plan-tab.active { background: var(--coral); border-color: var(--coral); color: #fff; }

.plans-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
}
.plan-card {
  background: var(--white); border-radius: 18px; overflow: hidden;
  box-shadow: var(--shadow); transition: transform .28s ease, box-shadow .28s ease;
  display: flex; flex-direction: column; position: relative;
  animation: card-in .4s ease both;
}
.plan-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.plan-card--featured {
  border: 2px solid var(--coral);
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
}
.plan-card--featured:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); }
.plan-card__featured-label {
  position: absolute; top: -1px; left: 50%; transform: translateX(-50%);
  background: var(--coral); color: #fff;
  font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
  padding: 4px 14px; border-radius: 0 0 10px 10px;
}
.plan-card__header {
  display: flex; align-items: center; gap: 14px;
  padding: 24px 22px 20px;
}
.plan-card__icon { font-size: 1.8rem; flex-shrink: 0; }
.plan-card__name {
  font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700;
  color: var(--indigo); margin: 0 0 2px;
}
.plan-card__tagline { font-size: .8rem; color: #6b6655; margin: 0; }
.plan-card__body { padding: 0 22px 22px; flex: 1; display: flex; flex-direction: column; }
.plan-card__price-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 12px; }
.plan-card__price {
  font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--coral);
}
.plan-card__price-note { font-size: .76rem; color: #a09880; }
.plan-card__desc { font-size: .84rem; color: #6b6655; line-height: 1.6; margin-bottom: 14px; }
.plan-card__covers {
  list-style: none; padding: 0; margin: 0 0 14px;
  display: flex; flex-direction: column; gap: 6px;
  border-top: 1px solid var(--gray-100); padding-top: 14px;
}
.plan-card__covers li {
  display: flex; align-items: center; gap: 8px;
  font-size: .83rem; color: #6b6655;
}
.covered     { color: var(--indigo) !important; }
.not-covered { color: #a09880; text-decoration: line-through; }
.partial     { color: var(--indigo) !important; opacity: .75; }
.cover-icon {
  font-size: .7rem; font-weight: 700; min-width: 14px; text-align: center;
}
.covered .cover-icon     { color: var(--teal); }
.not-covered .cover-icon { color: #ccc; }
.partial .cover-icon     { color: #f59e0b; }

.plan-card__best {
  margin-bottom: 16px; font-size: .8rem; background: var(--gray-50);
  border-radius: 8px; padding: 8px 12px; margin-top: auto;
}
.plan-card__best-label { font-weight: 700; color: #a09880; }
.plan-card__best-val   { color: var(--indigo); margin-left: 4px; }
.plan-card__cta { border-radius: 10px; padding: 11px; font-size: .86rem; text-align: center; }

@keyframes card-in {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Coverage section ─────────────────────────────────────────────────────── */
.coverage-section { background: var(--sand); padding: 72px 5%; }
.coverage-section__inner { max-width: 900px; margin: 0 auto; }
.coverage-header { text-align: center; margin-bottom: 40px; }
.coverage-overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--coral); margin-bottom: 12px;
}
.coverage-title {
  font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 3vw, 2.6rem);
  font-weight: 700; color: var(--indigo); margin-bottom: 10px;
}
.coverage-sub { font-size: .9rem; color: #6b6655; }

.coverage-grid { display: flex; flex-direction: column; gap: 10px; }
.cov-card {
  background: var(--white); border-radius: 14px;
  box-shadow: var(--shadow); overflow: hidden; cursor: pointer;
  transition: box-shadow .22s ease;
}
.cov-card:hover { box-shadow: var(--shadow-md); }
.cov-card--open { box-shadow: var(--shadow-md); }
.cov-card__header {
  display: flex; align-items: center; gap: 14px; padding: 18px 20px;
}
.cov-card__icon-wrap {
  width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
}
.cov-card__title-group { flex: 1; }
.cov-card__title {
  font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700;
  color: var(--indigo); margin: 0 0 2px;
}
.cov-card__sub { font-size: .8rem; color: #6b6655; margin: 0; }
.cov-card__toggle {
  font-size: 1.3rem; font-weight: 300; color: var(--coral); flex-shrink: 0;
  width: 24px; text-align: center;
}

.cov-card__detail {
  padding: 0 20px 20px; border-top: 1px solid var(--gray-100);
}
.cov-detail__cols { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding-top: 16px; }
.cov-detail__col ul { list-style: none; padding: 0; margin: 8px 0 0; display: flex; flex-direction: column; gap: 5px; }
.cov-detail__col ul li { font-size: .83rem; line-height: 1.5; padding-left: 14px; position: relative; }
.cov-detail__col--yes ul li { color: var(--indigo); }
.cov-detail__col--yes ul li::before { content: '✓'; position: absolute; left: 0; color: var(--teal); font-size: .7rem; top: 2px; }
.cov-detail__col--no  ul li { color: #6b6655; }
.cov-detail__col--no  ul li::before { content: '✕'; position: absolute; left: 0; color: var(--coral); font-size: .7rem; top: 2px; }
.cov-detail__heading { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; margin: 0; }
.cov-detail__heading--yes { color: var(--teal-dk); }
.cov-detail__heading--no  { color: var(--coral); }
.cov-detail__tip {
  display: flex; gap: 10px; align-items: flex-start;
  background: #f0ede4; border-left: 4px solid var(--coral);
  border-radius: 0 8px 8px 0; padding: 10px 14px; margin-top: 14px;
  font-size: .84rem; color: var(--indigo); line-height: 1.6;
}

.expand-enter-active, .expand-leave-active { transition: all .25s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; padding-bottom: 0; }
.expand-enter-to, .expand-leave-from { max-height: 600px; }

/* ── Claims section ───────────────────────────────────────────────────────── */
.claims-section { background: var(--indigo); padding: 72px 5%; }
.claims-section__inner {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 64px; align-items: start; max-width: 1100px; margin: 0 auto;
}
.claims-overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--coral); margin-bottom: 12px;
}
.claims-title {
  font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 2.5vw, 2.4rem);
  font-weight: 700; color: #fff; margin-bottom: 12px;
}
.claims-sub { font-size: .9rem; color: rgba(255,255,255,.65); line-height: 1.7; margin-bottom: 32px; }

.claims-steps { display: flex; flex-direction: column; gap: 20px; }
.claims-step { display: flex; gap: 16px; }
.claims-step__num {
  width: 32px; height: 32px; border-radius: 50%; background: var(--coral);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: .85rem; color: #fff; flex-shrink: 0; margin-top: 2px;
}
.claims-step__title { font-size: .92rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
.claims-step__body  { font-size: .84rem; color: rgba(255,255,255,.65); line-height: 1.6; }

.claims-doc-card, .claims-warning-card {
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
  border-radius: 14px; padding: 20px 22px; margin-bottom: 16px;
}
.claims-doc-card__title, .claims-warning-card__title {
  font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700;
  color: #fff; margin-bottom: 6px;
}
.claims-doc-card__sub { font-size: .8rem; color: rgba(255,255,255,.5); margin-bottom: 14px; }
.claims-doc-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 7px; }
.claims-doc-item { display: flex; align-items: center; gap: 8px; font-size: .83rem; color: rgba(255,255,255,.8); }
.claims-doc-icon { font-size: .9rem; flex-shrink: 0; }
.claims-warning-list { list-style: none; padding: 0; margin: 8px 0 0; display: flex; flex-direction: column; gap: 6px; }
.claims-warning-list li {
  font-size: .82rem; color: rgba(255,255,255,.7); padding-left: 16px; position: relative; line-height: 1.5;
}
.claims-warning-list li::before { content: '⚠'; position: absolute; left: 0; font-size: .7rem; }

/* ── FAQ ──────────────────────────────────────────────────────────────────── */
.faq-section { padding: 72px 5%; background: #faf8f3; }
.faq-section__inner { max-width: 760px; margin: 0 auto; }
.faq-header { text-align: center; margin-bottom: 40px; }
.faq-overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--coral); margin-bottom: 12px;
}
.faq-title {
  font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 3vw, 2.6rem);
  font-weight: 700; color: var(--indigo);
}
.faq-list { display: flex; flex-direction: column; gap: 0; }
.faq-item {
  border-bottom: 1px solid var(--gray-200);
  cursor: pointer; padding: 18px 0;
}
.faq-item:first-child { border-top: 1px solid var(--gray-200); }
.faq-item__q {
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  font-size: .95rem; font-weight: 700; color: var(--indigo); line-height: 1.4;
}
.faq-toggle { font-size: 1.3rem; font-weight: 300; color: var(--coral); flex-shrink: 0; }
.faq-item__a {
  font-size: .88rem; color: #4a4538; line-height: 1.8;
  padding-top: 12px; padding-right: 24px;
}

/* ── CTA band ─────────────────────────────────────────────────────────────── */
.cta-band { background: var(--sand); padding: 60px 5%; }
.cta-band__inner {
  display: flex; align-items: center; justify-content: space-between;
  gap: 32px; flex-wrap: wrap; max-width: 1100px; margin: 0 auto;
}
.cta-band__title {
  font-family: 'Fraunces', serif; font-size: 1.8rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 6px;
}
.cta-band__sub { font-size: .9rem; color: #6b6655; }
.cta-band__actions { display: flex; gap: 12px; flex-wrap: wrap; }

/* ── Plan modal ──────────────────────────────────────────────────────────── */
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(22,20,15,.7);
  z-index: 300; display: flex; align-items: center; justify-content: center;
  padding: 20px; overflow-y: auto;
}
.modal-fade-enter-active, .modal-fade-leave-active { transition: all .25s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; transform: scale(.97); }

.plan-modal {
  background: #faf8f3; border-radius: 20px; width: 100%; max-width: 640px;
  max-height: 90vh; overflow-y: auto; position: relative;
  box-shadow: 0 32px 80px rgba(0,0,0,.3); scrollbar-width: thin;
}
.plan-modal__close {
  position: fixed; top: 28px; right: 28px; z-index: 10;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(22,20,15,.5); border: none; color: #fff;
  font-size: .9rem; cursor: pointer; display: flex; align-items: center;
  justify-content: center; backdrop-filter: blur(4px);
  transition: background var(--transition);
}
.plan-modal__close:hover { background: var(--coral); }

.plan-modal__hero {
  padding: 36px 32px 28px;
  display: flex; align-items: flex-start; gap: 16px;
  border-radius: 20px 20px 0 0;
}
.plan-modal__icon { font-size: 2.5rem; flex-shrink: 0; }
.plan-modal__title {
  font-family: 'Fraunces', serif; font-size: 1.9rem; font-weight: 700;
  color: var(--indigo); margin: 0 0 4px;
}
.plan-modal__sub { font-size: .88rem; color: #6b6655; margin: 0; }

.plan-modal__content { padding: 28px 32px 32px; }
.plan-modal__lead {
  font-size: .98rem; color: var(--indigo); line-height: 1.75; font-style: italic;
  margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--gray-200);
}
.plan-modal__sections { display: flex; flex-direction: column; gap: 18px; margin-bottom: 24px; }
.plan-modal__section-title {
  font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 6px;
}
.plan-modal__section-body { font-size: .88rem; color: #4a4538; line-height: 1.75; }

.plan-modal__limits { margin-bottom: 24px; }
.plan-modal__limits-title {
  font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
  color: var(--indigo); margin-bottom: 12px;
}
.limits-table { display: flex; flex-direction: column; gap: 0; border: 1px solid var(--gray-200); border-radius: 12px; overflow: hidden; }
.limits-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 16px; border-bottom: 1px solid var(--gray-100);
  font-size: .86rem;
}
.limits-row:last-child { border-bottom: none; }
.limits-item  { color: #6b6655; }
.limits-amount { font-weight: 700; color: var(--indigo); }
.limits-row:nth-child(even) { background: var(--gray-50); }

.plan-modal__cta {
  padding-top: 20px; border-top: 1px solid var(--gray-200);
}

/* btn-outline-light for dark bg */
.btn-outline-light {
  display: inline-flex; align-items: center; justify-content: center;
  padding: 12px 22px; border-radius: 50px;
  border: 2px solid rgba(255,255,255,.4); background: transparent;
  color: #fff; font-family: 'DM Sans', sans-serif; font-size: .9rem;
  font-weight: 700; cursor: pointer; text-decoration: none;
  transition: all var(--transition);
}
.btn-outline-light:hover { border-color: #fff; background: rgba(255,255,255,.1); }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
  .why-strip__inner { grid-template-columns: repeat(2, 1fr); }
  .plans-grid       { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 1024px) {
  .masthead__inner  { grid-template-columns: 1fr; gap: 36px; }
  .claims-section__inner { grid-template-columns: 1fr; gap: 36px; }
}
@media (max-width: 768px) {
  .masthead__title  { font-size: 3rem; }
  .quote-row--2     { grid-template-columns: 1fr; }
  .why-strip__inner { grid-template-columns: 1fr; gap: 20px; }
  .plans-grid       { grid-template-columns: 1fr; }
  .cov-detail__cols { grid-template-columns: 1fr; }
  .cta-band__inner  { flex-direction: column; align-items: flex-start; }
  .plan-modal__hero { flex-direction: column; }
  .plan-modal__content { padding: 20px; }
}
</style>