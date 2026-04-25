<template>
  <div class="affiliates-page">
    <NavBar />

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <section class="hero">
      <div class="hero__bg">
        <div class="hero__bg-grid" />
        <div class="hero__bg-orb hero__bg-orb--1" />
        <div class="hero__bg-orb hero__bg-orb--2" />
        <div class="hero__bg-orb hero__bg-orb--3" />
      </div>
      <div class="hero__inner">
          <div class="hero__badge">
            <span class="badge-dot" />
            Affiliate Programme
          </div>
          <h1 class="hero__title">
            Your audience loves<br />
            to travel. <em>Get paid</em><br />
            for that.
          </h1>
          <p class="hero__sub">
            Join Voyago's affiliate programme and earn up to 7% commission on every
            booking your audience makes — travel packages, local experiences, transfers, and more.
          </p>
          <div class="hero__actions">
            <button class="btn btn-coral btn-lg" @click="scrollTo('apply')">
              Apply for free →
            </button>
            <button class="btn btn-ghost btn-lg" @click="scrollTo('how-it-works')">
              See how it works
            </button>
          </div>
          <div class="hero__proof">
            <div v-for="s in heroStats" :key="s.label" class="hero__stat">
              <span class="hero__stat-num">{{ s.num }}</span>
              <span class="hero__stat-label">{{ s.label }}</span>
            </div>
          </div>
      </div>
    </section>

    <!-- ── Who it's for ────────────────────────────────────────────────────── -->
    <section class="who-for">
      <div class="section-header">
        <p class="section-overline">Who it's for</p>
        <h2 class="section-title">Built for creators who<br /><em>actually travel</em></h2>
        <p class="section-sub">
          If you have an audience that takes or plans trips, you can monetise it with Voyago.
        </p>
      </div>
      <div class="who-grid">
        <div
          v-for="(w, i) in whoFor"
          :key="i"
          class="who-card"
          :style="{ '--i': i }"
        >
          <div class="who-card__img-wrap">
            <img :src="w.img" :alt="w.title" class="who-card__img" />
            <div class="who-card__overlay" />
            <div class="who-card__tag">{{ w.tag }}</div>
          </div>
          <div class="who-card__body">
            <span class="who-card__icon">{{ w.icon }}</span>
            <h3 class="who-card__title">{{ w.title }}</h3>
            <p class="who-card__desc">{{ w.desc }}</p>
            <div class="who-card__earn">
              <span class="who-card__earn-label">Avg. monthly earn</span>
              <span class="who-card__earn-val">{{ w.earn }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Commission structure ────────────────────────────────────────────── -->
    <section class="commission" id="commission">
      <div class="commission__inner">
        <div class="commission__left">
          <p class="section-overline">Commission rates</p>
          <h2 class="section-title">The more you refer,<br /><em>the more you keep</em></h2>
          <p class="section-sub">
            Our tiered structure rewards consistent performance. Hit your monthly booking target
            and your rate upgrades automatically — no applications needed.
          </p>
          <div class="commission__tiers">
            <div
              v-for="(tier, i) in tiers"
              :key="i"
              class="tier"
              :class="{ 'tier--active': activeTier === i }"
              @mouseenter="activeTier = i"
            >
              <div class="tier__left">
                <div class="tier__icon" :style="{ background: tier.iconBg }">{{ tier.icon }}</div>
                <div>
                  <h4 class="tier__name">{{ tier.name }}</h4>
                  <p class="tier__req">{{ tier.req }}</p>
                </div>
              </div>
              <div class="tier__right">
                <span class="tier__rate">{{ tier.rate }}</span>
                <span class="tier__rate-label">commission</span>
              </div>
            </div>
          </div>
          <p class="commission__note">
            Rates apply to net booking value after taxes.
            Commission released 14 days after trip completion.
          </p>
        </div>

        <div class="commission__right">
          <!-- Interactive calculator -->
          <div class="calc-card">
            <p class="calc-card__title">💰 Earnings calculator</p>
            <div class="calc-row">
              <label class="calc-label">Monthly referred bookings</label>
              <span class="calc-current">{{ calcBookings }}</span>
            </div>
            <input
              type="range"
              v-model.number="calcBookings"
              min="1" max="100" step="1"
              class="calc-slider"
            />
            <div class="calc-ticks"><span>1</span><span>100</span></div>

            <div class="calc-row" style="margin-top:20px">
              <label class="calc-label">Average booking value</label>
              <span class="calc-current">${{ calcAvgValue }}</span>
            </div>
            <input
              type="range"
              v-model.number="calcAvgValue"
              min="100" max="3000" step="50"
              class="calc-slider"
            />
            <div class="calc-ticks"><span>$100</span><span>$3,000</span></div>

            <div class="calc-result">
              <div class="calc-result__row">
                <span>Your tier</span>
                <strong :style="{ color: calcTierColor }">{{ calcTierName }}</strong>
              </div>
              <div class="calc-result__row">
                <span>Commission rate</span>
                <strong>{{ calcRate }}%</strong>
              </div>
              <div class="calc-result__row calc-result__row--total">
                <span>Monthly earnings</span>
                <strong class="calc-earnings">${{ calcEarnings.toLocaleString() }}</strong>
              </div>
            </div>
            <button class="btn btn-coral btn-full calc-cta" @click="scrollTo('apply')">
              Start earning at {{ calcRate }}% →
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- ── How it works ────────────────────────────────────────────────────── -->
    <section class="how-it-works" id="how-it-works">
      <div class="hiw__inner">
        <div class="hiw__left">
          <p class="section-overline">The process</p>
          <h2 class="section-title">Your first commission<br /><em>in 48 hours</em></h2>
          <p class="section-sub">
            Apply once, get a link the same day, and start earning every time your audience books.
          </p>
          <button class="btn btn-coral" style="margin-top:28px" @click="scrollTo('apply')">
            Apply now →
          </button>
        </div>
        <div class="hiw__right">
          <div
            v-for="(step, i) in howSteps"
            :key="i"
            class="step"
            :class="{ 'step--active': activeHiw === i }"
            @mouseenter="activeHiw = i"
          >
            <div class="step__num">{{ String(i + 1).padStart(2, '0') }}</div>
            <div class="step__body">
              <h4 class="step__title">{{ step.title }}</h4>
              <p class="step__desc" v-show="activeHiw === i">{{ step.desc }}</p>
              <div class="step__tags" v-show="activeHiw === i">
                <span v-for="d in step.details" :key="d" class="step__tag">{{ d }}</span>
              </div>
            </div>
            <div class="step__icon">{{ step.icon }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Tools & resources ───────────────────────────────────────────────── -->
    <section class="tools">
      <div class="section-header">
        <p class="section-overline">Your toolkit</p>
        <h2 class="section-title">Everything you need to<br /><em>promote & convert</em></h2>
        <p class="section-sub">
          We build the tools. You drive the traffic. Together we grow.
        </p>
      </div>
      <div class="tools-grid">
        <div v-for="(tool, i) in tools" :key="i" class="tool-card">
          <div class="tool-card__icon">{{ tool.icon }}</div>
          <h3 class="tool-card__title">{{ tool.title }}</h3>
          <p class="tool-card__desc">{{ tool.desc }}</p>
          <div v-if="tool.preview" class="tool-card__preview">
            <code class="tool-link-preview">{{ tool.preview }}</code>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Affiliate dashboard preview ────────────────────────────────────── -->
    <section class="dashboard-preview">
      <div class="dp__inner">
        <div class="dp__text">
          <p class="section-overline">Affiliate dashboard</p>
          <h2 class="section-title">Track every click,<br /><em>every dollar</em></h2>
          <p class="section-sub">
            Your real-time dashboard shows clicks, conversions, earnings, payout history,
            and your top-performing links — so you know exactly what to write about next.
          </p>
          <ul class="dp__features">
            <li v-for="f in dashFeatures" :key="f.title" class="dp__feature">
              <span class="dp__feature-icon">{{ f.icon }}</span>
              <div>
                <strong>{{ f.title }}</strong>
                <p>{{ f.desc }}</p>
              </div>
            </li>
          </ul>
          <button class="btn btn-coral" style="margin-top:28px" @click="scrollTo('apply')">
            Get dashboard access →
          </button>
        </div>

        <div class="dp__mock">
          <div class="mock-card">
            <div class="mock-topbar">
              <div class="mock-dots"><span /><span /><span /></div>
              <span class="mock-url">voyago.com/affiliate/dashboard</span>
            </div>
            <div class="mock-body">
              <div class="mock-sidebar">
                <div v-for="item in mockNav" :key="item" class="mock-nav-item" :class="{ active: item === 'Overview' }">
                  {{ item }}
                </div>
              </div>
              <div class="mock-main">
                <div class="mock-kpis">
                  <div v-for="k in mockKpis" :key="k.label" class="mock-kpi">
                    <span class="mock-kpi__val" :style="{ color: k.color }">{{ k.val }}</span>
                    <span class="mock-kpi__label">{{ k.label }}</span>
                  </div>
                </div>
                <!-- Top links table -->
                <div class="mock-table">
                  <div class="mock-table__head">
                    <span>Top performing links</span>
                    <span>Clicks</span>
                    <span>Conv.</span>
                    <span>Earned</span>
                  </div>
                  <div v-for="row in mockLinks" :key="row.name" class="mock-table__row">
                    <span class="mock-link-name">{{ row.name }}</span>
                    <span>{{ row.clicks }}</span>
                    <span class="mock-conv">{{ row.conv }}</span>
                    <span class="mock-earned">{{ row.earned }}</span>
                  </div>
                </div>
                <!-- Payout -->
                <div class="mock-payout">
                  <span class="mock-payout__label">Pending payout</span>
                  <span class="mock-payout__val">$1,284.50</span>
                  <span class="mock-payout__status">Processing →</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Testimonials ────────────────────────────────────────────────────── -->
    <section class="testimonials">
      <div class="section-header section-header--light">
        <p class="section-overline" style="color: rgba(255,255,255,.5)">Affiliate stories</p>
        <h2 class="section-title" style="color:#fff">From creators who<br /><em style="color:var(--coral)">already earn with us</em></h2>
      </div>
      <div class="testimonials-grid">
        <div v-for="(t, i) in testimonials" :key="i" class="testimonial-card">
          <div class="testimonial-card__stars">{{ '★'.repeat(t.rating) }}</div>
          <p class="testimonial-card__text">"{{ t.text }}"</p>
          <div class="testimonial-card__author">
            <img :src="t.avatar" class="t-avatar" :alt="t.name" />
            <div>
              <strong>{{ t.name }}</strong>
              <span>{{ t.role }}</span>
            </div>
            <div class="testimonial-card__metric">
              <span class="metric-num">{{ t.metric }}</span>
              <span class="metric-label">{{ t.metricLabel }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── FAQ ────────────────────────────────────────────────────────────── -->
    <section class="faq">
      <div class="faq__inner">
        <div class="faq__left">
          <p class="section-overline">FAQ</p>
          <h2 class="section-title">Questions we<br /><em>hear often</em></h2>
          <p class="section-sub">Still unsure? Our affiliate team replies within 4 hours.</p>
          <button class="btn btn-outline" style="margin-top:24px" @click="contactOpen = true">
            Ask us anything →
          </button>
        </div>
        <div class="faq__right">
          <div
            v-for="(q, i) in faqs"
            :key="i"
            class="faq-item"
            :class="{ open: openFaq === i }"
          >
            <button class="faq-item__q" @click="openFaq = openFaq === i ? null : i">
              <span>{{ q.q }}</span>
              <span class="faq-item__chevron">{{ openFaq === i ? '−' : '+' }}</span>
            </button>
            <div class="faq-item__a" v-show="openFaq === i">{{ q.a }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Apply section ───────────────────────────────────────────────────── -->
    <section class="apply-section" id="apply">
      <div class="apply-section__inner">
        <div class="apply-section__left">
          <p class="section-overline">Apply now</p>
          <h2 class="section-title">Ready to turn your<br /><em>traffic into income?</em></h2>
          <p class="section-sub">
            Free to join. Get approved in 24 hours. Your first tracking link is live the same day.
          </p>
          <div class="apply-checklist">
            <div v-for="item in applyChecklist" :key="item" class="apply-check">
              <span class="apply-check__icon">✓</span>
              <span>{{ item }}</span>
            </div>
          </div>
        </div>

        <div class="apply-section__right">
          <div v-if="appSubmitted" class="apply-success">
            <div class="apply-success__icon">🎉</div>
            <h3>Application received!</h3>
            <p>We'll review your details and email you at <strong>{{ appForm.email }}</strong> within 24 hours with your tracking link and dashboard access.</p>
            <button class="btn btn-outline" @click="appSubmitted = false; resetAppForm()">
              Submit another
            </button>
          </div>

          <form v-else class="apply-form" @submit.prevent="submitApp">
            <div class="form-row">
              <div class="form-group">
                <label>Full name <span class="req">*</span></label>
                <input v-model="appForm.name" placeholder="Your name" />
                <p class="form-error" v-if="appErrors.name">{{ appErrors.name }}</p>
              </div>
              <div class="form-group">
                <label>Email address <span class="req">*</span></label>
                <input v-model="appForm.email" type="email" placeholder="you@example.com" />
                <p class="form-error" v-if="appErrors.email">{{ appErrors.email }}</p>
              </div>
            </div>

            <div class="form-group">
              <label>Your website / channel / profile <span class="req">*</span></label>
              <input v-model="appForm.website" type="url" placeholder="https://yourblog.com or @yourhandle" />
              <p class="form-error" v-if="appErrors.website">{{ appErrors.website }}</p>
            </div>

            <div class="form-group">
              <label>What type of creator are you? <span class="req">*</span></label>
              <div class="chip-group">
                <button
                  v-for="type in creatorTypes"
                  :key="type"
                  type="button"
                  class="chip"
                  :class="{ active: appForm.creatorType === type }"
                  @click="appForm.creatorType = type"
                >{{ type }}</button>
              </div>
              <p class="form-error" v-if="appErrors.creatorType">{{ appErrors.creatorType }}</p>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Audience size</label>
                <select v-model="appForm.audienceSize">
                  <option value="" disabled>Select…</option>
                  <option value="<1k">Under 1,000</option>
                  <option value="1k-10k">1,000 – 10,000</option>
                  <option value="10k-50k">10,000 – 50,000</option>
                  <option value="50k-200k">50,000 – 200,000</option>
                  <option value="200k+">200,000+</option>
                </select>
              </div>
              <div class="form-group">
                <label>Primary audience region</label>
                <select v-model="appForm.region">
                  <option value="" disabled>Select…</option>
                  <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Tell us about your audience & travel niche</label>
              <textarea v-model="appForm.description" rows="3" placeholder="Who follows you, where they travel, what kind of content you create…" />
            </div>

            <div class="form-group">
              <label class="checkbox-item">
                <input type="checkbox" v-model="appForm.agreeTerms" />
                <span>I agree to the <a href="/affiliate-terms" class="form-link" target="_blank">Affiliate Terms & Conditions</a></span>
              </label>
              <p class="form-error" v-if="appErrors.agreeTerms">{{ appErrors.agreeTerms }}</p>
            </div>

            <button type="submit" class="btn btn-coral btn-full form-submit" :disabled="appSubmitting">
              <span v-if="appSubmitting" class="submit-spinner" />
              <span v-else>Submit application →</span>
            </button>
            <p class="form-note">Free to join · No exclusivity · Cancel anytime</p>
          </form>
        </div>
      </div>
    </section>

    <!-- ── Final CTA ──────────────────────────────────────────────────────── -->
    <section class="final-cta">
      <div class="final-cta__inner">
        <h2 class="final-cta__title">Your readers will book anyway.<br />Make sure you earn when they do.</h2>
        <p class="final-cta__sub">Join 900+ affiliates already earning with Voyago.</p>
        <div class="final-cta__actions">
          <button class="btn btn-coral btn-lg" @click="scrollTo('apply')">
            Apply in 2 minutes →
          </button>
          <RouterLink to="/partners/agencies" class="btn btn-ghost-light btn-lg">
            I'm a travel agency instead
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- ── Contact modal ─────────────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="contactOpen" class="modal-backdrop" @click.self="contactOpen = false">
          <div class="contact-modal">
            <button class="modal-close" @click="contactOpen = false">✕</button>
            <div class="apply-modal__header">
              <div class="apply-modal__icon">💬</div>
              <h3 class="apply-modal__title">Contact the affiliate team</h3>
              <p class="apply-modal__sub">We reply within 4 business hours.</p>
            </div>
            <div class="apply-form">
              <div class="form-group">
                <label>Your email</label>
                <input v-model="contactForm.email" type="email" placeholder="you@example.com" />
              </div>
              <div class="form-group">
                <label>Message</label>
                <textarea v-model="contactForm.message" rows="4" placeholder="Ask us anything about the affiliate programme…" />
              </div>
              <button class="btn btn-coral btn-full form-submit" @click="contactOpen = false">
                Send message →
              </button>
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

function scrollTo(id) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
}

// ── Hero stats ─────────────────────────────────────────────────────────────
const heroStats = [
  { num: '900+',  label: 'Active affiliates' },
  { num: 'Up to 7%', label: 'Commission rate' },
  { num: '$2.4M', label: 'Paid to affiliates' },
  { num: '30-day', label: 'Cookie window' },
]

// ── Who it's for ───────────────────────────────────────────────────────────
const whoFor = [
  {
    icon: '✍️',
    title: 'Travel Bloggers',
    tag: 'Content',
    desc: 'Embed deep-links into your destination guides and roundups. Every click is tracked for 30 days.',
    earn: '$400–$2,000',
    img: 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=600&q=80',
  },
  {
    icon: '📱',
    title: 'Social Media Creators',
    tag: 'Social',
    desc: 'Add your link to bio, stories, and Reels. Our landing pages are optimised for mobile conversions.',
    earn: '$200–$1,500',
    img: 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?w=600&q=80',
  },
  {
    icon: '🎙️',
    title: 'Podcast Hosts',
    tag: 'Audio',
    desc: 'Share your vanity URL on-air. Listeners who visit your link are cookied for a full 30 days.',
    earn: '$300–$1,800',
    img: 'https://images.unsplash.com/photo-1478737270239-2f02b77fc618?w=600&q=80',
  },
  {
    icon: '📧',
    title: 'Newsletter Writers',
    tag: 'Email',
    desc: 'Embed curated destination or package links. Email audiences convert at 3× the industry average.',
    earn: '$500–$3,000',
    img: 'https://images.unsplash.com/photo-1528460033278-a6ba57020470?w=600&q=80',
  },
  {
    icon: '🌐',
    title: 'Deal & Comparison Sites',
    tag: 'SEO',
    desc: 'Add Voyago to your comparison tables. We have over 12,000 bookable offers across 80+ destinations.',
    earn: '$1,000–$8,000',
    img: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=80',
  },
  {
    icon: '🎬',
    title: 'YouTubers & Video Creators',
    tag: 'Video',
    desc: 'Drop your affiliate link in descriptions and pinned comments. Viewers plan trips — get paid when they book.',
    earn: '$600–$4,000',
    img: 'https://images.unsplash.com/photo-1536240478700-b869ad10e128?w=600&q=80',
  },
]

// ── Commission tiers ───────────────────────────────────────────────────────
const activeTier = ref(1)
const tiers = [
  {
    icon: '🌱', iconBg: 'rgba(46,196,182,.1)',
    name: 'Starter', req: '0–9 bookings / month',
    rate: '4%',
  },
  {
    icon: '🚀', iconBg: 'rgba(255,90,95,.1)',
    name: 'Growth', req: '10–29 bookings / month',
    rate: '5%',
  },
  {
    icon: '⚡', iconBg: 'rgba(255,165,0,.1)',
    name: 'Pro', req: '30–59 bookings / month',
    rate: '6%',
  },
  {
    icon: '🏆', iconBg: 'rgba(45,49,66,.08)',
    name: 'Elite', req: '60+ bookings / month',
    rate: '7%',
  },
]

// ── Calculator ─────────────────────────────────────────────────────────────
const calcBookings  = ref(20)
const calcAvgValue  = ref(400)

const calcTier = computed(() => {
  if (calcBookings.value >= 60) return { name: 'Elite',   rate: 7, color: '#2D3142' }
  if (calcBookings.value >= 30) return { name: 'Pro',     rate: 6, color: '#FF8C00' }
  if (calcBookings.value >= 10) return { name: 'Growth',  rate: 5, color: '#FF5A5F' }
  return                               { name: 'Starter', rate: 4, color: '#2EC4B6' }
})
const calcTierName  = computed(() => calcTier.value.name)
const calcTierColor = computed(() => calcTier.value.color)
const calcRate      = computed(() => calcTier.value.rate)
const calcEarnings  = computed(() =>
  Math.round(calcBookings.value * calcAvgValue.value * calcRate.value / 100)
)

// ── How it works ───────────────────────────────────────────────────────────
const activeHiw = ref(0)
const howSteps = [
  {
    icon: '📝',
    title: 'Apply & get approved',
    desc: 'Fill in a short form — no minimum traffic requirements. We approve most applications within 24 hours.',
    details: ['Free to join', 'No exclusivity', 'No minimum audience'],
  },
  {
    icon: '🔗',
    title: 'Get your tracking links',
    desc: 'Access your dashboard and generate deep links to any page on Voyago — specific destinations, packages, or services.',
    details: ['Deep-link any page', '30-day cookie', 'Custom vanity URLs'],
  },
  {
    icon: '📣',
    title: 'Promote to your audience',
    desc: 'Share links in your content, bio, email, or ads. Use our banners, widgets, and curated deal feeds.',
    details: ['Banners & widgets', 'Deal feeds via API', 'Co-branded landing pages'],
  },
  {
    icon: '💸',
    title: 'Get paid every month',
    desc: 'Commissions are tracked in real-time. Minimum $50 payout threshold. Choose bank transfer, PayPal, or Wise.',
    details: ['Paid on the 1st', '$50 min. threshold', 'Bank / PayPal / Wise'],
  },
]

// ── Tools ──────────────────────────────────────────────────────────────────
const tools = [
  {
    icon: '🔗',
    title: 'Deep-link generator',
    desc: 'Turn any Voyago URL into a tracked affiliate link in one click. Works for any page — destinations, packages, services.',
    preview: 'voyago.com/ref/yourcode/packages/101',
  },
  {
    icon: '🖼️',
    title: 'Banner library',
    desc: 'Responsive banners in 12 standard ad sizes, available in light and dark variants. Updated with seasonal offers automatically.',
    wide: false,
  },
  {
    icon: '📊',
    title: 'Real-time dashboard',
    desc: 'Track clicks, conversions, earnings, and your top-performing links. Export reports as CSV at any time.',
    wide: false,
  },
  {
    icon: '📡',
    title: 'Deal feed API',
    desc: 'Pull live Voyago deals directly into your website or app via our REST API. Filter by destination, category, price range, and more. Perfect for comparison sites and deal aggregators.',
    preview: 'GET /api/affiliate/deals?dest=santorini&min_commission=5',
  },
  {
    icon: '🎨',
    title: 'Co-branded landing pages',
    desc: 'Elite affiliates get a custom Voyago landing page with their logo, brand colours, and featured deals — fully hosted by us.',
    wide: false,
  },
  {
    icon: '📬',
    title: 'Weekly deal digest',
    desc: 'Get a curated email every Monday with the week\'s top deals, new destinations, and seasonal packages ready to promote.',
    wide: false,
  },
]

// ── Dashboard preview data ─────────────────────────────────────────────────
const mockNav   = ['Overview', 'Links', 'Earnings', 'Payouts', 'Reports']
const mockKpis  = [
  { val: '4,820', label: 'Clicks',      color: '#2D3142' },
  { val: '186',   label: 'Conversions', color: '#FF5A5F' },
  { val: '$1,284',label: 'Earned',      color: '#2EC4B6' },
]
const mockLinks = [
  { name: 'Santorini 7-day guide',   clicks: '1,240', conv: '4.8%', earned: '$420' },
  { name: 'Morocco adventure pkg',   clicks: '890',   conv: '3.9%', earned: '$310' },
  { name: 'Bali transfers',          clicks: '670',   conv: '6.1%', earned: '$290' },
]
const dashFeatures = [
  { icon: '📈', title: 'Real-time click tracking', desc: 'See every click the moment it happens, with referrer source and device.' },
  { icon: '🔗', title: 'Link manager',             desc: 'Create, label, and organise all your affiliate links in one place.' },
  { icon: '💳', title: 'Payout history',           desc: 'Full record of every payment, with tax-ready annual summaries.' },
  { icon: '📊', title: 'Conversion reports',       desc: 'Understand which content converts best and optimise accordingly.' },
]

// ── Testimonials ───────────────────────────────────────────────────────────
const testimonials = [
  {
    rating: 5,
    text: "I wrote one long-form guide to Bali and embedded my Voyago links throughout. That single post now generates around $600 a month on autopilot. The 30-day cookie means readers who come back days later still count.",
    name: 'Amara Diallo',
    role: 'Travel blogger · 85K monthly readers',
    avatar: 'https://i.pravatar.cc/48?img=44',
    metric: '$600',
    metricLabel: 'From one post / mo',
  },
  {
    rating: 5,
    text: "I was sceptical because most affiliate programmes pay peanuts on travel. Voyago's 5% on a $1,200 package is $60 per booking — my newsletter converts at about 3%, so the maths works out extremely well.",
    name: 'Tom Hendricks',
    role: 'Newsletter · 22K subscribers',
    avatar: 'https://i.pravatar.cc/48?img=15',
    metric: '$2,100',
    metricLabel: 'Best single month',
  },
  {
    rating: 5,
    text: "The deal feed API is genuinely impressive. I pull Voyago's best packages into my comparison tables automatically — no manual updates, always current, and the commission on package bookings is the highest I've seen.",
    name: 'Priya Sharma',
    role: 'Deal site owner · 200K monthly visits',
    avatar: 'https://i.pravatar.cc/48?img=47',
    metric: '7%',
    metricLabel: 'Commission (Elite)',
  },
]

// ── FAQ ────────────────────────────────────────────────────────────────────
const openFaq = ref(null)
const faqs = [
  {
    q: 'Is there a minimum traffic or audience requirement to apply?',
    a: "No. We don't have a minimum follower or traffic threshold. What we look at is relevance — a small, highly engaged travel audience will outperform a large general one. If you create travel content and your audience trusts your recommendations, you're a good fit.",
  },
  {
    q: 'How long does the affiliate cookie last?',
    a: '30 days. If someone clicks your link and books any time within 30 days — even if they close the tab and come back later — you receive the commission on that booking. The cookie is set per device.',
  },
  {
    q: 'What exactly do I earn commission on?',
    a: "You earn on the net booking value (total price minus taxes and fees) of any booking made through your link. This includes travel packages, local experiences, transfers, and any other bookable service on Voyago. There's no category exclusion.",
  },
  {
    q: 'When and how do I get paid?',
    a: 'Payouts are processed on the 1st of each month for commissions earned in the previous month, provided your balance exceeds the $50 minimum threshold. Payment methods: bank transfer (SWIFT/SEPA), PayPal, and Wise. Annual tax summaries are provided in January.',
  },
  {
    q: 'Can I use paid ads to drive traffic to my affiliate links?',
    a: 'You can use paid social (Instagram, TikTok, Facebook, Pinterest, YouTube) to drive traffic to your own content that contains affiliate links. Direct bidding on Voyago brand keywords in Google Ads or other search engines is not permitted. If you run a large paid traffic operation, contact us for a custom arrangement.',
  },
  {
    q: 'Do you offer exclusive or negotiated rates for high-volume affiliates?',
    a: 'Yes. Elite tier affiliates (60+ bookings/month) can discuss custom commission arrangements, co-branded landing pages, and exclusive seasonal deals. Contact our affiliate team to start that conversation once you reach Elite status.',
  },
]

// ── Apply form ─────────────────────────────────────────────────────────────
const creatorTypes = ['Travel blogger', 'Social media creator', 'YouTuber / video', 'Podcast host', 'Newsletter writer', 'Deal / comparison site', 'Travel agent', 'Other']
const regions = ['Global / Worldwide', 'North America', 'Europe', 'Asia-Pacific', 'Middle East & Africa', 'Latin America']

function blankApp() {
  return { name: '', email: '', website: '', creatorType: '', audienceSize: '', region: '', description: '', agreeTerms: false }
}
const appForm       = ref(blankApp())
const appErrors     = ref({})
const appSubmitting = ref(false)
const appSubmitted  = ref(false)

function validateApp() {
  const e = {}
  if (!appForm.value.name.trim())    e.name        = 'Name is required.'
  if (!appForm.value.email.includes('@')) e.email  = 'Valid email is required.'
  if (!appForm.value.website.trim()) e.website     = 'Website or profile URL is required.'
  if (!appForm.value.creatorType)    e.creatorType = 'Please select your creator type.'
  if (!appForm.value.agreeTerms)     e.agreeTerms  = 'You must agree to the affiliate terms.'
  appErrors.value = e
  return Object.keys(e).length === 0
}
function resetAppForm() { appForm.value = blankApp(); appErrors.value = {} }
async function submitApp() {
  if (!validateApp()) return
  appSubmitting.value = true
  await new Promise(r => setTimeout(r, 1200))
  appSubmitting.value = false
  appSubmitted.value  = true
}

// ── Apply checklist ────────────────────────────────────────────────────────
const applyChecklist = [
  'Free to join — no setup fees ever',
  'No exclusivity — promote other programmes too',
  'Approved in 24 hours or less',
  'Tracking link live the same day',
  'Commission on every category',
  'Monthly payouts, no surprises',
]

// ── Contact modal ──────────────────────────────────────────────────────────
const contactOpen = ref(false)
const contactForm = ref({ email: '', message: '' })
</script>

<style scoped>
/* ── Base ──────────────────────────────────────────────────────────────────── */
.affiliates-page {
  min-height: 100vh;
  padding-top: 72px;
  font-family: 'DM Sans', sans-serif;
  background: #fff;
}

/* ── Shared section header ─────────────────────────────────────────────────── */
.section-overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--coral); margin-bottom: 14px;
}
.section-title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.1;
  color: var(--indigo); margin-bottom: 16px;
}
.section-title em { color: var(--coral); font-style: italic; }
.section-sub {
  font-size: .96rem; color: var(--gray-600); line-height: 1.7; max-width: 520px;
}
.section-header { text-align: center; margin-bottom: 56px; }
.section-header .section-sub { margin: 0 auto; }
.section-header--light .section-overline { color: rgba(255,255,255,.5); }

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.hero {
  background: var(--indigo);
  position: relative; overflow: hidden;
  padding: 80px 5% 88px;
}
.hero__bg { position: absolute; inset: 0; pointer-events: none; }
.hero__bg-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
  background-size: 60px 60px;
}
.hero__bg-orb {
  position: absolute; border-radius: 50%; filter: blur(80px);
}
.hero__bg-orb--1 { width: 500px; height: 500px; background: rgba(255,90,95,.2);  top: -180px; right: -60px; }
.hero__bg-orb--2 { width: 360px; height: 360px; background: rgba(46,196,182,.12); bottom: -100px; left: 5%; }
.hero__bg-orb--3 { width: 240px; height: 240px; background: rgba(255,90,95,.08);  top: 40%; left: 38%; }

.hero__inner {
  position: relative; z-index: 2;
  padding: 80px 5%; max-width: 760px;
}

.hero__badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
  color: #fff; font-size: .73rem; font-weight: 700; letter-spacing: .1em;
  text-transform: uppercase; padding: 6px 14px; border-radius: 50px;
  margin-bottom: 24px; backdrop-filter: blur(8px);
}
.badge-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--coral); box-shadow: 0 0 0 3px rgba(255,90,95,.3);
  animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
  0%,100% { box-shadow: 0 0 0 3px rgba(255,90,95,.3); }
  50%      { box-shadow: 0 0 0 7px rgba(255,90,95,.1); }
}

.hero__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2.6rem, 5.5vw, 4.5rem);
  font-weight: 700; line-height: .96; color: #fff; margin-bottom: 22px;
}
.hero__title em { color: var(--coral); font-style: italic; }
.hero__sub {
  font-size: 1.02rem; color: rgba(255,255,255,.7);
  line-height: 1.7; margin-bottom: 36px; max-width: 500px;
}
.hero__actions { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 44px; }
.btn-lg { padding: 15px 32px !important; font-size: .96rem !important; }
.btn-ghost {
  background: rgba(255,255,255,.1); border: 1.5px solid rgba(255,255,255,.25);
  color: #fff; border-radius: 50px; padding: 12px 24px;
  font-family: 'DM Sans', sans-serif; font-weight: 700; cursor: pointer;
  transition: all var(--transition);
}
.btn-ghost:hover { background: rgba(255,255,255,.2); }

.hero__proof { display: flex; gap: 36px; flex-wrap: wrap; padding-top: 28px; border-top: 1px solid rgba(255,255,255,.12); }
.hero__stat { display: flex; flex-direction: column; gap: 3px; }
.hero__stat-num   { font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700; color: #fff; }
.hero__stat-label { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.45); }

/* ── How it works ──────────────────────────────────────────────────────────── */
.how-it-works { background: var(--sand); padding: 88px 5%; }
.hiw__inner {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 80px; align-items: start;
}
.hiw__right { display: flex; flex-direction: column; gap: 0; }

.step {
  position: relative; display: flex; gap: 20px; align-items: flex-start;
  padding: 20px 24px; border-radius: var(--radius-sm);
  cursor: pointer; transition: background var(--transition), box-shadow var(--transition);
}
.step:hover, .step--active { background: #fff; box-shadow: var(--shadow); }
.step__num {
  font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 700;
  color: var(--gray-200); line-height: 1; flex-shrink: 0;
  transition: color var(--transition);
}
.step--active .step__num { color: var(--coral); }
.step__body { flex: 1; }
.step__title { font-weight: 700; color: var(--indigo); font-size: .96rem; margin-bottom: 6px; }
.step__desc  { font-size: .86rem; color: var(--gray-600); line-height: 1.65; margin-bottom: 10px; }
.step__tags  { display: flex; flex-wrap: wrap; gap: 6px; }
.step__tag {
  font-size: .72rem; font-weight: 700; color: var(--teal);
  background: rgba(46,196,182,.1); padding: 3px 10px; border-radius: 50px;
}
.step__icon {
  font-size: 1.4rem; flex-shrink: 0; opacity: .35;
  transition: opacity var(--transition);
}
.step--active .step__icon, .step:hover .step__icon { opacity: 1; }

/* ── Tools ─────────────────────────────────────────────────────────────────── */
.tools { padding: 88px 5%; background: #fff; }
.tools-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
.tool-card {
  padding: 28px; border-radius: var(--radius); border: 1.5px solid var(--gray-200);
  transition: box-shadow var(--transition), transform var(--transition), border-color var(--transition);
  display: flex; flex-direction: column; gap: 0;
}
.tool-card:hover { box-shadow: var(--shadow-md); transform: translateY(-3px); border-color: transparent; }
.tool-card__icon  { font-size: 1.6rem; margin-bottom: 14px; }
.tool-card__title { font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 700; color: var(--indigo); margin-bottom: 8px; }
.tool-card__desc  { font-size: .86rem; color: var(--gray-600); line-height: 1.65; flex: 1; }
.tool-card__preview { margin-top: 14px; }
.tool-link-preview {
  font-family: monospace; font-size: .73rem; color: var(--teal);
  background: var(--gray-50); border: 1px solid var(--gray-200);
  padding: 7px 10px; border-radius: 6px; display: block; word-break: break-all;
}
.who-for { padding: 88px 5%; background: var(--gray-50); }
.who-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
}
.who-card {
  border-radius: var(--radius); overflow: hidden; background: #fff;
  box-shadow: var(--shadow); transition: transform var(--transition), box-shadow var(--transition);
  animation: card-in .4s ease both;
  animation-delay: calc(var(--i) * 55ms);
}
.who-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }
.who-card__img-wrap { position: relative; height: 150px; overflow: hidden; }
.who-card__img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
.who-card:hover .who-card__img { transform: scale(1.06); }
.who-card__overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, transparent 40%, rgba(45,49,66,.55));
}
.who-card__tag {
  position: absolute; bottom: 10px; left: 12px;
  background: rgba(255,255,255,.9); color: var(--indigo);
  font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
  padding: 3px 9px; border-radius: 50px;
}
.who-card__body { padding: 18px 20px 22px; }
.who-card__icon { font-size: 1.3rem; display: block; margin-bottom: 8px; }
.who-card__title { font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 700; color: var(--indigo); margin-bottom: 6px; }
.who-card__desc  { font-size: .82rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 14px; }
.who-card__earn  {
  display: flex; justify-content: space-between; align-items: center;
  background: var(--gray-50); border-radius: 8px; padding: 8px 12px;
}
.who-card__earn-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .07em; color: var(--gray-400); font-weight: 700; }
.who-card__earn-val   { font-family: 'Fraunces', serif; font-size: .98rem; font-weight: 700; color: var(--coral); }

/* ── Commission ────────────────────────────────────────────────────────────── */
.commission { padding: 88px 5%; background: #fff; }
.commission__inner {
  display: grid; grid-template-columns: 1fr 1fr; gap: 72px; align-items: start;
}
.commission__tiers { display: flex; flex-direction: column; gap: 10px; margin-top: 28px; }
.tier {
  display: flex; justify-content: space-between; align-items: center;
  padding: 16px 20px; border-radius: var(--radius-sm); border: 1.5px solid var(--gray-200);
  cursor: default; transition: all var(--transition);
}
.tier:hover, .tier--active {
  border-color: var(--coral); background: var(--coral-lt);
  box-shadow: var(--shadow);
}
.tier__left { display: flex; align-items: center; gap: 14px; }
.tier__icon {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}
.tier__name { font-weight: 700; color: var(--indigo); font-size: .92rem; margin-bottom: 2px; }
.tier__req  { font-size: .76rem; color: var(--gray-600); }
.tier__right { display: flex; flex-direction: column; align-items: flex-end; }
.tier__rate  { font-family: 'Fraunces', serif; font-size: 1.8rem; font-weight: 700; color: var(--coral); line-height: 1; }
.tier__rate-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .07em; color: var(--gray-400); font-weight: 700; }
.commission__note { font-size: .78rem; color: var(--gray-400); margin-top: 18px; line-height: 1.6; }

/* Calculator */
.calc-card {
  background: var(--gray-50); border-radius: var(--radius);
  border: 1.5px solid var(--gray-200); padding: 32px;
  position: sticky; top: 100px;
}
.calc-card__title { font-size: .94rem; font-weight: 700; color: var(--indigo); margin-bottom: 22px; }
.calc-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.calc-label { font-size: .82rem; font-weight: 700; color: var(--indigo); }
.calc-current { font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: var(--coral); }
.calc-slider {
  -webkit-appearance: none; appearance: none;
  width: 100%; height: 4px; border-radius: 2px;
  background: var(--gray-200); outline: none; cursor: pointer;
}
.calc-slider::-webkit-slider-thumb {
  -webkit-appearance: none; width: 20px; height: 20px; border-radius: 50%;
  background: var(--coral); cursor: pointer;
  box-shadow: 0 0 0 4px rgba(255,90,95,.2); transition: box-shadow var(--transition);
}
.calc-slider::-webkit-slider-thumb:hover { box-shadow: 0 0 0 6px rgba(255,90,95,.25); }
.calc-ticks { display: flex; justify-content: space-between; font-size: .7rem; color: var(--gray-400); margin-top: 4px; }

.calc-result {
  background: #fff; border-radius: var(--radius-sm); border: 1.5px solid var(--gray-200);
  padding: 16px 18px; margin: 20px 0 16px; display: flex; flex-direction: column; gap: 10px;
}
.calc-result__row {
  display: flex; justify-content: space-between; align-items: center;
  font-size: .86rem; color: var(--gray-600);
}
.calc-result__row strong { color: var(--indigo); }
.calc-result__row--total {
  padding-top: 10px; border-top: 1px solid var(--gray-200); font-weight: 700;
}
.calc-earnings { font-family: 'Fraunces', serif; font-size: 1.4rem; color: var(--teal) !important; }
.calc-cta { padding: 13px !important; font-size: .9rem !important; }
.btn-full { width: 100%; }

/* ── Dashboard preview ─────────────────────────────────────────────────────── */
.dashboard-preview { padding: 88px 5%; background: var(--gray-50); }
.dp__inner { display: grid; grid-template-columns: 1fr 1.3fr; gap: 64px; align-items: center; }
.dp__features { list-style: none; padding: 0; margin: 28px 0 0; display: flex; flex-direction: column; gap: 18px; }
.dp__feature { display: flex; gap: 14px; }
.dp__feature-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: #fff; border: 1.5px solid var(--gray-200);
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0; box-shadow: var(--shadow);
}
.dp__feature strong { display: block; font-size: .88rem; color: var(--indigo); margin-bottom: 3px; }
.dp__feature p { font-size: .8rem; color: var(--gray-600); margin: 0; }

/* Mock browser */
.mock-card {
  border-radius: 16px; overflow: hidden;
  box-shadow: var(--shadow-lg); background: #fff; border: 1px solid var(--gray-200);
}
.mock-topbar {
  background: var(--gray-50); border-bottom: 1px solid var(--gray-200);
  padding: 10px 16px; display: flex; align-items: center; gap: 12px;
}
.mock-dots { display: flex; gap: 5px; }
.mock-dots span { width: 10px; height: 10px; border-radius: 50%; background: var(--gray-200); }
.mock-dots span:first-child  { background: #ff5f57; }
.mock-dots span:nth-child(2) { background: #febc2e; }
.mock-dots span:last-child   { background: #28c840; }
.mock-url { font-size: .7rem; color: var(--gray-400); font-family: monospace; background: #fff; border: 1px solid var(--gray-200); padding: 3px 10px; border-radius: 6px; }
.mock-body { display: flex; }
.mock-sidebar { width: 110px; background: var(--indigo); padding: 14px 0; flex-shrink: 0; }
.mock-nav-item { padding: 8px 14px; font-size: .7rem; font-weight: 600; color: rgba(255,255,255,.45); }
.mock-nav-item.active { color: #fff; background: rgba(255,255,255,.1); border-left: 2px solid var(--coral); }
.mock-main { flex: 1; padding: 14px; display: flex; flex-direction: column; gap: 12px; }

.mock-kpis { display: flex; gap: 8px; }
.mock-kpi { flex: 1; background: var(--gray-50); border-radius: 8px; padding: 10px; }
.mock-kpi__val   { display: block; font-family: 'Fraunces', serif; font-size: .95rem; font-weight: 700; }
.mock-kpi__label { font-size: .6rem; color: var(--gray-400); font-weight: 700; text-transform: uppercase; }

.mock-table { background: var(--gray-50); border-radius: 8px; overflow: hidden; }
.mock-table__head,
.mock-table__row {
  display: grid; grid-template-columns: 1fr auto auto auto;
  gap: 8px; align-items: center; padding: 7px 10px; font-size: .68rem;
}
.mock-table__head { background: var(--indigo); color: rgba(255,255,255,.6); font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
.mock-table__row  { border-bottom: 1px solid var(--gray-200); color: var(--indigo); font-weight: 500; }
.mock-table__row:last-child { border-bottom: none; }
.mock-link-name { font-weight: 600; font-size: .68rem; color: var(--indigo); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mock-conv   { color: var(--teal); font-weight: 700; text-align: right; }
.mock-earned { color: var(--coral); font-weight: 700; text-align: right; }

.mock-payout {
  display: flex; align-items: center; gap: 10px;
  background: rgba(46,196,182,.08); border-radius: 8px; padding: 9px 12px;
  border: 1px solid rgba(46,196,182,.2);
}
.mock-payout__label { font-size: .68rem; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .06em; }
.mock-payout__val   { font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700; color: var(--indigo); flex: 1; }
.mock-payout__status { font-size: .72rem; font-weight: 700; color: var(--teal); white-space: nowrap; }

/* ── Testimonials ──────────────────────────────────────────────────────────── */
.testimonials { background: var(--indigo); padding: 88px 5%; }
.testimonials-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.testimonial-card {
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
  border-radius: var(--radius); padding: 32px;
  display: flex; flex-direction: column; gap: 16px;
  transition: background var(--transition);
}
.testimonial-card:hover { background: rgba(255,255,255,.12); }
.testimonial-card__stars { color: #ffd700; font-size: .88rem; letter-spacing: 2px; }
.testimonial-card__text {
  font-size: .9rem; color: rgba(255,255,255,.78); line-height: 1.75; flex: 1; font-style: italic;
}
.testimonial-card__author {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  padding-top: 16px; border-top: 1px solid rgba(255,255,255,.1);
}
.t-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.testimonial-card__author strong { display: block; font-size: .86rem; color: #fff; }
.testimonial-card__author span   { font-size: .78rem; color: rgba(255,255,255,.45); }
.testimonial-card__metric { margin-left: auto; text-align: right; }
.metric-num   { display: block; font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700; color: var(--coral); }
.metric-label { font-size: .68rem; color: rgba(255,255,255,.4); font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }

/* ── FAQ ───────────────────────────────────────────────────────────────────── */
.faq { padding: 88px 5%; }
.faq__inner { display: grid; grid-template-columns: 1fr 1.6fr; gap: 64px; align-items: start; }
.faq__right { display: flex; flex-direction: column; }
.faq-item { border-bottom: 1px solid var(--gray-200); }
.faq-item__q {
  width: 100%; display: flex; justify-content: space-between; align-items: center;
  background: none; border: none; padding: 18px 0;
  font-family: 'DM Sans', sans-serif; font-size: .94rem; font-weight: 700;
  color: var(--indigo); cursor: pointer; text-align: left; gap: 16px;
  transition: color var(--transition);
}
.faq-item__q:hover { color: var(--coral); }
.faq-item.open .faq-item__q { color: var(--coral); }
.faq-item__chevron { font-size: 1.3rem; font-weight: 400; flex-shrink: 0; color: var(--coral); }
.faq-item__a { font-size: .88rem; color: var(--gray-600); line-height: 1.75; padding-bottom: 18px; }

/* ── Apply section ─────────────────────────────────────────────────────────── */
.apply-section { padding: 88px 5%; background: var(--gray-50); }
.apply-section__inner {
  display: grid; grid-template-columns: 1fr 1.4fr; gap: 72px; align-items: start;
}
.apply-checklist { display: flex; flex-direction: column; gap: 10px; margin-top: 24px; }
.apply-check { display: flex; align-items: center; gap: 10px; font-size: .9rem; color: var(--gray-600); }
.apply-check__icon {
  width: 22px; height: 22px; border-radius: 50%;
  background: rgba(46,196,182,.15); color: var(--teal);
  display: flex; align-items: center; justify-content: center;
  font-size: .7rem; font-weight: 700; flex-shrink: 0;
}

/* Apply success */
.apply-success {
  text-align: center; background: #fff; border-radius: var(--radius);
  padding: 48px 32px; box-shadow: var(--shadow);
  display: flex; flex-direction: column; align-items: center; gap: 14px;
}
.apply-success__icon { font-size: 3rem; }
.apply-success h3 { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; color: var(--indigo); }
.apply-success p  { font-size: .9rem; color: var(--gray-600); line-height: 1.65; max-width: 360px; }

/* Apply form */
.apply-form {
  background: #fff; border-radius: var(--radius); padding: 36px;
  box-shadow: var(--shadow); display: flex; flex-direction: column; gap: 18px;
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: .8rem; font-weight: 700; color: var(--indigo); }
.form-group input,
.form-group select,
.form-group textarea {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 10px 14px; font-family: 'DM Sans', sans-serif;
  font-size: .9rem; color: var(--indigo); outline: none; background: #fff;
  transition: border-color var(--transition);
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: var(--coral); }
.form-group textarea { resize: vertical; }
.form-error { font-size: .76rem; color: var(--coral); margin: 0; }
.req { color: var(--coral); }
.form-link { color: var(--coral); font-weight: 600; text-decoration: none; }
.checkbox-item { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: .86rem; color: var(--gray-600); }

.chip-group { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.chip {
  padding: 6px 14px; border-radius: 50px; border: 1.5px solid var(--gray-200);
  background: #fff; font-family: 'DM Sans', sans-serif; font-size: .8rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
}
.chip:hover { border-color: var(--coral); color: var(--coral); }
.chip.active { background: var(--coral); border-color: var(--coral); color: #fff; }

.form-submit {
  padding: 14px !important; font-size: .94rem !important;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.form-submit:disabled { opacity: .7; cursor: not-allowed; }
.submit-spinner {
  width: 18px; height: 18px; border-radius: 50%;
  border: 2.5px solid rgba(255,255,255,.4); border-top-color: #fff;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.form-note { font-size: .76rem; color: var(--gray-400); text-align: center; margin: 0; }

/* ── Final CTA ─────────────────────────────────────────────────────────────── */
.final-cta {
  background: linear-gradient(135deg, var(--indigo) 0%, #1e2035 100%);
  padding: 88px 5%; text-align: center;
}
.final-cta__inner { max-width: 660px; margin: 0 auto; }
.final-cta__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(1.8rem, 3.5vw, 2.8rem);
  font-weight: 700; color: #fff; margin-bottom: 16px; line-height: 1.15;
}
.final-cta__sub { font-size: 1rem; color: rgba(255,255,255,.6); margin-bottom: 36px; }
.final-cta__actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.btn-ghost-light {
  background: rgba(255,255,255,.1); border: 1.5px solid rgba(255,255,255,.25);
  color: #fff; border-radius: 50px; padding: 12px 24px;
  font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: .9rem;
  text-decoration: none; display: inline-flex; align-items: center;
  transition: background var(--transition); cursor: pointer;
}
.btn-ghost-light:hover { background: rgba(255,255,255,.2); }

/* ── Contact modal ─────────────────────────────────────────────────────────── */
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(22,20,15,.7);
  z-index: 300; display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.modal-fade-enter-active, .modal-fade-leave-active { transition: all .25s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; transform: scale(.97); }

.contact-modal {
  background: #fff; border-radius: 20px; width: 100%; max-width: 480px;
  position: relative; padding: 36px; box-shadow: 0 32px 80px rgba(0,0,0,.25);
}
.modal-close {
  position: absolute; top: 18px; right: 18px;
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--gray-100); border: none; color: var(--gray-600);
  font-size: .9rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: background var(--transition);
}
.modal-close:hover { background: var(--coral); color: #fff; }
.apply-modal__header { text-align: center; margin-bottom: 24px; }
.apply-modal__icon   { font-size: 2rem; margin-bottom: 8px; }
.apply-modal__title  { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--indigo); margin-bottom: 4px; }
.apply-modal__sub    { font-size: .86rem; color: var(--gray-600); }

/* ── Animations ────────────────────────────────────────────────────────────── */
@keyframes card-in {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
  .tools-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 1024px) {
  .hero__inner           { max-width: 100%; }
  .commission__inner,
  .hiw__inner,
  .dp__inner,
  .faq__inner,
  .apply-section__inner  { grid-template-columns: 1fr; gap: 40px; }
  .who-grid              { grid-template-columns: repeat(2, 1fr); }
  .testimonials-grid     { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .who-grid,
  .tools-grid,
  .testimonials-grid { grid-template-columns: 1fr; }
  .form-row          { grid-template-columns: 1fr; }
  .hero__actions     { flex-direction: column; }
  .hero__actions .btn-lg, .hero__actions .btn-ghost { width: 100%; text-align: center; }
  .apply-form        { padding: 24px 18px; }
}
</style>