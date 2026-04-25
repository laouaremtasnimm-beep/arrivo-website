<template>
  <div class="agencies-page">
    <NavBar />

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <section class="hero">
      <div class="hero__bg">
        <img
          src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1600&q=80"
          alt="Travel agency background"
          class="hero__bg-img"
        />
        <div class="hero__bg-overlay" />
      </div>
      <div class="hero__inner">
        <div class="hero__badge">
          <span class="badge-dot" />
          Partner Programme
        </div>
        <h1 class="hero__title">
          Grow your agency<br />
          <em>exponentially</em>
        </h1>
        <p class="hero__sub">
          Join 2,400+ travel agencies listing their packages on Voyago — the marketplace
          where serious travellers come to book. More visibility, less admin, more revenue.
        </p>
        <div class="hero__actions">
          <button class="btn btn-coral btn-lg" @click="openApply">
            Start listing for free →
          </button>
          <button class="btn btn-ghost btn-lg" @click="scrollTo('how-it-works')">
            See how it works
          </button>
        </div>
        <div class="hero__stats">
          <div v-for="s in heroStats" :key="s.label" class="hero__stat">
            <span class="hero__stat-num">{{ s.num }}</span>
            <span class="hero__stat-label">{{ s.label }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Trust logos ────────────────────────────────────────────────────── -->
    <div class="trust-bar">
      <p class="trust-bar__label">Trusted by agencies in 60+ countries</p>
      <div class="trust-bar__logos">
        <span v-for="name in partnerNames" :key="name" class="trust-logo">{{ name }}</span>
      </div>
    </div>

    <!-- ── Benefits ───────────────────────────────────────────────────────── -->
    <section class="benefits" id="benefits">
      <div class="section-header">
        <p class="section-overline">Why Voyago</p>
        <h2 class="section-title">Everything your agency needs<br /><em>in one place</em></h2>
        <p class="section-sub">
          We built the tools that travel agencies actually asked for — not what looked good in a pitch deck.
        </p>
      </div>

      <div class="benefits-grid">
        <div v-for="(b, i) in benefits" :key="i" class="benefit-card" :style="{ '--i': i }">
          <div class="benefit-card__icon" :style="{ background: b.iconBg }">
            {{ b.icon }}
          </div>
          <h3 class="benefit-card__title">{{ b.title }}</h3>
          <p class="benefit-card__body">{{ b.body }}</p>
        </div>
      </div>
    </section>

    <!-- ── How it works ───────────────────────────────────────────────────── -->
    <section class="how-it-works" id="how-it-works">
      <div class="hiw__inner">
        <div class="hiw__left">
          <p class="section-overline">The process</p>
          <h2 class="section-title">Live in under<br /><em>24 hours</em></h2>
          <p class="section-sub">
            From sign-up to your first booking — we've stripped away every unnecessary step.
          </p>
          <button class="btn btn-coral" style="margin-top:28px" @click="openApply">
            Get started now →
          </button>
        </div>
        <div class="hiw__right">
          <div v-for="(step, i) in steps" :key="i" class="step" :class="{ 'step--active': activeStep === i }" @mouseenter="activeStep = i">
            <div class="step__num">{{ String(i + 1).padStart(2, '0') }}</div>
            <div class="step__body">
              <h4 class="step__title">{{ step.title }}</h4>
              <p class="step__desc" v-show="activeStep === i">{{ step.desc }}</p>
            </div>
            <div class="step__line" v-if="i < steps.length - 1" />
          </div>
        </div>
      </div>
    </section>

    <!-- ── Pricing ────────────────────────────────────────────────────────── -->
    <section class="pricing" id="pricing">
      <div class="section-header">
        <p class="section-overline">Pricing</p>
        <h2 class="section-title">Plans for every<br /><em>agency size</em></h2>
        <p class="section-sub">Start free, scale when you're ready. No hidden fees, no lock-in.</p>
      </div>

      <div class="pricing-grid pricing-grid--4">
        <div
          v-for="(plan, i) in plans"
          :key="i"
          class="pricing-card"
          :class="{ 'pricing-card--featured': plan.featured }"
        >
          <div v-if="plan.featured" class="pricing-card__badge">Most popular</div>
          <div class="pricing-card__icon">{{ plan.icon }}</div>
          <h3 class="pricing-card__name">{{ plan.name }}</h3>
          <div class="pricing-card__rate">
            <span class="rate-num">{{ plan.rate }}</span>
            <span class="rate-label">{{ plan.rateLabel }}</span>
          </div>
          <p class="pricing-card__desc">{{ plan.desc }}</p>
          <ul class="pricing-card__features">
            <li v-for="f in plan.features" :key="f">
              <span class="check">✓</span> {{ f }}
            </li>
          </ul>
          <button
            class="btn btn-full"
            :class="plan.featured ? 'btn-coral' : 'btn-outline'"
            @click="plan.isContact ? openContactModal() : openApply()"
          >
            {{ plan.cta }}
          </button>
        </div>
      </div>

      <p class="pricing-note">
        Monthly plans can be cancelled anytime. Commission is charged only on completed bookings.
        <a href="#" @click.prevent="openContactModal">Questions? Contact us →</a>
      </p>
    </section>

    <!-- ── Dashboard preview ──────────────────────────────────────────────── -->
    <section class="dashboard-preview">
      <div class="dp__inner">
        <div class="dp__text">
          <p class="section-overline">Agency Dashboard</p>
          <h2 class="section-title">Your entire operation,<br /><em>one screen</em></h2>
          <ul class="dp__features">
            <li v-for="f in dashboardFeatures" :key="f.title" class="dp__feature">
              <span class="dp__feature-icon">{{ f.icon }}</span>
              <div>
                <strong>{{ f.title }}</strong>
                <p>{{ f.desc }}</p>
              </div>
            </li>
          </ul>
          <button class="btn btn-coral" style="margin-top:28px" @click="openApply">
            Get early access →
          </button>
        </div>
        <div class="dp__mock">
          <div class="mock-card">
            <div class="mock-topbar">
              <div class="mock-dots">
                <span /><span /><span />
              </div>
              <span class="mock-url">voyago.com/dashboard</span>
            </div>
            <div class="mock-body">
              <div class="mock-sidebar">
                <div v-for="item in mockNav" :key="item" class="mock-nav-item" :class="{ active: item === 'Overview' }">
                  {{ item }}
                </div>
              </div>
              <div class="mock-main">
                <div class="mock-stats">
                  <div v-for="s in mockStats" :key="s.label" class="mock-stat">
                    <span class="mock-stat__num" :style="{ color: s.color }">{{ s.num }}</span>
                    <span class="mock-stat__label">{{ s.label }}</span>
                  </div>
                </div>
                <div class="mock-chart">
                  <div class="mock-chart__bars">
                    <div v-for="(h, i) in chartBars" :key="i" class="mock-bar" :style="{ height: h + '%' }" />
                  </div>
                  <span class="mock-chart__label">Bookings this month</span>
                </div>
                <div class="mock-bookings">
                  <div v-for="b in mockBookings" :key="b.name" class="mock-booking">
                    <div class="mock-booking__avatar">{{ b.name[0] }}</div>
                    <div class="mock-booking__info">
                      <strong>{{ b.name }}</strong>
                      <span>{{ b.pkg }}</span>
                    </div>
                    <span class="mock-booking__status" :class="'status--' + b.status">{{ b.status }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Testimonials ───────────────────────────────────────────────────── -->
    <section class="testimonials">
      <div class="section-header section-header--light">
        <p class="section-overline">Agency stories</p>
        <h2 class="section-title" style="color:#fff">What our partners say</h2>
      </div>
      <div class="testimonials-grid">
        <div v-for="(t, i) in testimonials" :key="i" class="testimonial-card">
          <div class="testimonial-card__stars">{{ '★'.repeat(t.rating) }}</div>
          <p class="testimonial-card__text">"{{ t.text }}"</p>
          <div class="testimonial-card__author">
            <img :src="t.avatar" class="t-avatar" :alt="t.name" />
            <div>
              <strong>{{ t.name }}</strong>
              <span>{{ t.role }} · {{ t.company }}</span>
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
          <p class="section-sub">Still not answered? Our partner team replies within 2 hours.</p>
          <button class="btn btn-outline" style="margin-top:24px" @click="openContactModal">
            Ask us anything →
          </button>
        </div>
        <div class="faq__right">
          <div v-for="(q, i) in faqs" :key="i" class="faq-item" :class="{ open: openFaq === i }">
            <button class="faq-item__q" @click="openFaq = openFaq === i ? null : i">
              <span>{{ q.q }}</span>
              <span class="faq-item__chevron">{{ openFaq === i ? '−' : '+' }}</span>
            </button>
            <div class="faq-item__a" v-show="openFaq === i">{{ q.a }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Final CTA ──────────────────────────────────────────────────────── -->
    <section class="final-cta">
      <div class="final-cta__inner">
        <h2 class="final-cta__title">Ready to reach more travellers?</h2>
        <p class="final-cta__sub">
          Free to join. First booking in under 48 hours. Cancel anytime.
        </p>
        <div class="final-cta__actions">
          <button class="btn btn-coral btn-lg" @click="openApply">
            Create your agency profile →
          </button>
          <RouterLink to="/partners/providers" class="btn btn-ghost-light btn-lg">
            I'm a service provider instead
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- ── Apply modal ────────────────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="applyOpen" class="modal-backdrop" @click.self="applyOpen = false">
          <div class="apply-modal apply-modal--register">
            <button class="modal-close" @click="applyOpen = false">✕</button>
            <div class="apply-modal__header">
              <div class="apply-modal__icon">🏢</div>
              <h3 class="apply-modal__title">Join as a Travel Agency</h3>
              <p class="apply-modal__sub">Create your account — role is pre-selected as Agency.</p>
            </div>
            <!-- Reuse the existing RegisterForm; it handles validation, role selection,
                 and redirects to /dashboard on success. The agency role is pre-set. -->
            <RegisterForm default-role="agency" @switch-mode="applyOpen = false; $router.push('/auth?mode=login')" />
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── Contact modal ─────────────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="contactOpen" class="modal-backdrop" @click.self="contactOpen = false">
          <div class="apply-modal apply-modal--sm">
            <button class="modal-close" @click="contactOpen = false">✕</button>
            <div class="apply-modal__header">
              <div class="apply-modal__icon">💬</div>
              <h3 class="apply-modal__title">Contact our partner team</h3>
              <p class="apply-modal__sub">We reply within 2 business hours.</p>
            </div>
            <div class="apply-form">
              <div class="form-group">
                <label>Your email</label>
                <input v-model="contactForm.email" type="email" placeholder="you@agency.com" />
              </div>
              <div class="form-group">
                <label>Message</label>
                <textarea v-model="contactForm.message" rows="4" placeholder="Ask us anything about the partner programme…" />
              </div>
              <button class="btn btn-coral btn-full form-submit" @click="submitContact">
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import NavBar        from '@/components/home/NavBar.vue'
import SiteFooter    from '@/components/home/SiteFooter.vue'
import RegisterForm  from '@/components/auth/RegisterForm.vue'

const router = useRouter()

// ── Hero stats ─────────────────────────────────────────────────────────────
const heroStats = [
  { num: '2,400+', label: 'Partner agencies' },
  { num: '190K',   label: 'Bookings made' },
  { num: '60+',    label: 'Countries' },
  { num: '4.8★',   label: 'Avg. agency rating' },
]

// ── Trust logos ────────────────────────────────────────────────────────────
const partnerNames = ['Meridian Travel', 'Atlas Expeditions', 'SunRoute', 'OceanVoyage', 'PeakTours', 'HorizonCo', 'WanderlustPro', 'NovaJourneys']

// ── Benefits ───────────────────────────────────────────────────────────────
const benefits = [
  {
    icon: '📦',
    iconBg: 'rgba(255,90,95,.1)',
    title: 'List unlimited packages',
    body: 'Upload as many packages as you offer — day trips, multi-week itineraries, private tours. No listing limits, ever.',
  },
  {
    icon: '🎯',
    iconBg: 'rgba(46,196,182,.1)',
    title: 'Reach qualified travellers',
    body: 'Our audience actively searches for curated experiences. Higher intent than social ads. Lower acquisition cost than Google.',
  },
  {
    icon: '📊',
    iconBg: 'rgba(45,49,66,.08)',
    title: 'Real-time booking dashboard',
    body: 'Manage reservations, confirm bookings, send itineraries, and track revenue — all from one clean interface.',
  },
  {
    icon: '💬',
    iconBg: 'rgba(255,90,95,.1)',
    title: 'Built-in messaging',
    body: 'Communicate directly with travellers inside the platform. No more lost emails or missed WhatsApp threads.',
  },
  {
    icon: '⭐',
    iconBg: 'rgba(46,196,182,.1)',
    title: 'Verified reviews',
    body: 'Only travellers who actually booked through Voyago can leave reviews. Authentic social proof that converts.',
  },
  {
    icon: '💳',
    iconBg: 'rgba(45,49,66,.08)',
    title: 'Fast, secure payouts',
    body: 'Funds released within 3 days of trip completion. Bank transfer, PayPal, or Wise — your choice.',
  },
]

// ── How it works steps ─────────────────────────────────────────────────────
const activeStep = ref(0)
const steps = [
  {
    title: 'Create your agency profile',
    desc: 'Fill out your agency details, upload your logo, add your specialisations and regions. Takes about 10 minutes — your profile is your store front.',
  },
  {
    title: 'List your first package',
    desc: 'Use our guided editor: description, itinerary, inclusions, photos, pricing, availability. A preview shows exactly how travellers will see it.',
  },
  {
    title: 'Get verified',
    desc: 'Our partner team reviews your submission within 24 hours and may ask for a brief call or a licence document. Most agencies are live same-day.',
  },
  {
    title: 'Start receiving bookings',
    desc: 'Once live, your packages appear in search results, category pages, and our editorial picks. You\'ll receive booking requests with full traveller details.',
  },
  {
    title: 'Get paid',
    desc: 'Confirm bookings, deliver the experience, and receive your payout automatically — minus our small platform commission.',
  },
]

// ── Pricing plans ──────────────────────────────────────────────────────────
const plans = [
  {
    icon: '🌱',
    name: 'Free',
    price: null,
    rate: 'Free',
    rateLabel: 'forever',
    desc: 'Get started with no commitment. List a few packages and see how Voyago works.',
    features: [
      'Up to 3 active packages',
      'Standard search placement',
      'Booking management dashboard',
      'Basic support',
    ],
    cta: 'Get started',
    featured: false,
    isContact: false,
  },
  {
    icon: '🚀',
    name: 'Growth',
    price: '$9',
    rate: '$9',
    rateLabel: '/mo + 6% per booking',
    desc: 'For growing agencies ready to scale visibility and bookings across regions.',
    features: [
      'Unlimited packages',
      'Boosted search visibility',
      'Analytics & revenue reports',
      'Priority partner support',
      'Custom agency landing page',
      'Promotional deal placement',
      'Early access to new features',
    ],
    cta: 'Join Growth',
    featured: true,
    isContact: false,
  },
  {
    icon: '💼',
    name: 'Business',
    price: '$29',
    rate: '$29',
    rateLabel: '/mo + 5% per booking',
    desc: 'For established agencies managing teams, analytics, and integrations.',
    features: [
      'Everything in Growth',
      'Team seats (5 members)',
      'Advanced analytics dashboard',
      'API access for integrations',
      'Group & MICE packages',
    ],
    cta: 'Start Business',
    featured: false,
    isContact: false,
  },
  {
    icon: '🏆',
    name: 'Enterprise',
    price: null,
    rate: 'Custom',
    rateLabel: 'volume rates',
    desc: 'Tailored for high-volume agencies with white-label and dedicated support needs.',
    features: [
      'Everything in Business',
      'Dedicated account manager',
      'White-label options',
      'Custom SLA guarantee',
      'Onboarding & training',
    ],
    cta: 'Contact us',
    featured: false,
    isContact: true,
  },
]

// ── Dashboard preview ──────────────────────────────────────────────────────
const mockNav = ['Overview', 'Packages', 'Bookings', 'Messages', 'Reviews', 'Payouts']
const mockStats = [
  { num: '€12,480', label: 'Revenue', color: '#FF5A5F' },
  { num: '38',      label: 'Bookings', color: '#2EC4B6' },
  { num: '4.9★',   label: 'Rating', color: '#2D3142' },
]
const chartBars = [40, 55, 45, 70, 60, 85, 72, 90, 65, 80, 95, 78]
const mockBookings = [
  { name: 'Sarah L.',   pkg: 'Bali 7-day tour',     status: 'confirmed' },
  { name: 'Marco R.',   pkg: 'Santorini escape',     status: 'pending' },
  { name: 'Yuki T.',    pkg: 'Morocco adventure',    status: 'confirmed' },
]

const dashboardFeatures = [
  { icon: '📅', title: 'Booking calendar',    desc: 'See all upcoming trips in a clean timeline view.' },
  { icon: '📈', title: 'Revenue analytics',   desc: 'Monthly earnings, conversion rates, top packages.' },
  { icon: '✉️', title: 'Guest messaging',     desc: 'All pre-trip communication in one thread.' },
  { icon: '🔔', title: 'Smart notifications', desc: 'Instant alerts for new bookings and reviews.' },
]

// ── Testimonials ───────────────────────────────────────────────────────────
const testimonials = [
  {
    rating: 5,
    text: "We listed our first 3 packages on a Friday afternoon. By Sunday we had 4 confirmed bookings. The quality of travellers coming through Voyago is completely different from any other platform we've tried.",
    name: 'Laila Mansouri',
    role: 'Founder',
    company: 'Atlas Expeditions, Morocco',
    avatar: 'https://i.pravatar.cc/48?img=47',
    metric: '+340%',
    metricLabel: 'Revenue YoY',
  },
  {
    rating: 5,
    text: 'The dashboard alone saved us 15 hours a week in admin. Seeing all bookings, payments, and messages in one place was the thing we\'d been looking for since we started the agency.',
    name: 'James Owusu',
    role: 'Operations Director',
    company: 'SunRoute, Ghana',
    avatar: 'https://i.pravatar.cc/48?img=15',
    metric: '15 hrs',
    metricLabel: 'Saved per week',
  },
  {
    rating: 5,
    text: "The reviews system is the best we've seen. Because every review comes from a verified booking, our score actually reflects what we deliver — and travellers trust that. It's changed how guests perceive us.",
    name: 'Priya Chandra',
    role: 'CEO',
    company: 'Meridian Travel, India',
    avatar: 'https://i.pravatar.cc/48?img=44',
    metric: '4.9★',
    metricLabel: 'Avg. rating',
  },
]

// ── FAQ ────────────────────────────────────────────────────────────────────
const openFaq = ref(null)
const faqs = [
  {
    q: 'Is there a monthly fee to list on Voyago?',
    a: "No. Listing is completely free. Voyago takes a commission only when a booking is confirmed and completed. Starter agencies pay 8%, Growth plan agencies pay 6%. Enterprise rates are negotiated based on volume.",
  },
  {
    q: 'How long does approval take?',
    a: 'Most agencies are reviewed and live within 24 hours of submitting their application. During peak periods it may take up to 48 hours. Our partner team will contact you if we need any additional documents.',
  },
  {
    q: 'Can I manage multiple agencies under one account?',
    a: 'Yes. Our Growth and Enterprise plans support multi-property accounts. You can manage separate profiles, packages, and payouts for each brand under a single login with role-based team access.',
  },
  {
    q: 'What happens if a traveller cancels?',
    a: "Cancellation and refund policies are set by you — Voyago enforces whatever policy you define on your packages. We provide a standard set of templates (Flexible, Moderate, Strict) or you can write your own. Commission is only charged on completed bookings.",
  },
  {
    q: 'Do you support group bookings and corporate travel?',
    a: 'Yes. Enterprise plan agencies can list group packages and MICE (Meetings, Incentives, Conferences, Events) products. A dedicated account manager helps you configure quoting and deposit workflows for larger groups.',
  },
  {
    q: 'Can I integrate Voyago with my own booking system?',
    a: 'Enterprise plan includes full API access for two-way sync with your existing reservations system, CRM, or PMS. Full API documentation and a sandbox environment are provided at sign-up.',
  },
]

// ── Apply modal ────────────────────────────────────────────────────────────
const applyOpen = ref(false)
function openApply() { applyOpen.value = true }

// ── Contact modal ──────────────────────────────────────────────────────────
const contactOpen  = ref(false)
const contactForm  = ref({ email: '', message: '' })

function openContactModal() { contactOpen.value = true }
function submitContact() { contactOpen.value = false }

// ── Scroll helper ──────────────────────────────────────────────────────────
function scrollTo(id) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<style scoped>
/* ── Base ──────────────────────────────────────────────────────────────────── */
.agencies-page {
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
  font-size: .96rem; color: var(--gray-600);
  line-height: 1.7; max-width: 520px;
}
.section-header { text-align: center; margin-bottom: 56px; }
.section-header .section-sub { margin: 0 auto; }

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.hero {
  position: relative; min-height: 640px;
  display: flex; align-items: center;
  overflow: hidden;
}
.hero__bg { position: absolute; inset: 0; }
.hero__bg-img { width: 100%; height: 100%; object-fit: cover; }
.hero__bg-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(105deg, rgba(45,49,66,.9) 45%, rgba(45,49,66,.5));
}
.hero__inner {
  position: relative; z-index: 2;
  padding: 80px 5%; max-width: 720px;
}
.hero__badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
  color: #fff; font-size: .75rem; font-weight: 700; letter-spacing: .1em;
  text-transform: uppercase; padding: 6px 14px; border-radius: 50px;
  margin-bottom: 24px; backdrop-filter: blur(8px);
}
.badge-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--teal); box-shadow: 0 0 0 3px rgba(46,196,182,.3);
  animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
  0%, 100% { box-shadow: 0 0 0 3px rgba(46,196,182,.3); }
  50%       { box-shadow: 0 0 0 7px rgba(46,196,182,.1); }
}
.hero__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2.8rem, 6vw, 5rem);
  font-weight: 700; line-height: .95;
  color: #fff; margin-bottom: 22px;
}
.hero__title em { color: var(--coral); font-style: italic; }
.hero__sub {
  font-size: 1.05rem; color: rgba(255,255,255,.8);
  line-height: 1.7; margin-bottom: 36px; max-width: 540px;
}
.hero__actions { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 52px; }
.btn-lg    { padding: 15px 32px !important; font-size: .96rem !important; }
.btn-ghost { background: rgba(255,255,255,.12); border: 1.5px solid rgba(255,255,255,.3); color: #fff; border-radius: 50px; padding: 12px 24px; font-family: 'DM Sans', sans-serif; font-weight: 700; cursor: pointer; transition: all var(--transition); }
.btn-ghost:hover { background: rgba(255,255,255,.22); }

.hero__stats {
  display: flex; gap: 40px; flex-wrap: wrap;
  padding-top: 36px;
  border-top: 1px solid rgba(255,255,255,.18);
}
.hero__stat { display: flex; flex-direction: column; gap: 3px; }
.hero__stat-num {
  font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 700; color: #fff;
}
.hero__stat-label {
  font-size: .72rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: rgba(255,255,255,.55);
}

/* ── Trust bar ─────────────────────────────────────────────────────────────── */
.trust-bar {
  background: var(--gray-50); border-bottom: 1px solid var(--gray-200);
  padding: 18px 5%; display: flex; align-items: center; gap: 28px; flex-wrap: wrap;
}
.trust-bar__label {
  font-size: .73rem; font-weight: 700; letter-spacing: .1em;
  text-transform: uppercase; color: var(--gray-400); white-space: nowrap;
}
.trust-bar__logos { display: flex; gap: 20px; flex-wrap: wrap; align-items: center; }
.trust-logo {
  font-size: .82rem; font-weight: 700; color: var(--gray-400);
  padding: 4px 14px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: #fff;
  white-space: nowrap;
}

/* ── Benefits ──────────────────────────────────────────────────────────────── */
.benefits { padding: 88px 5%; }
.benefits-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px;
}
.benefit-card {
  padding: 32px; border-radius: var(--radius);
  border: 1.5px solid var(--gray-200); background: #fff;
  transition: box-shadow var(--transition), transform var(--transition), border-color var(--transition);
  animation: card-in .4s ease both;
  animation-delay: calc(var(--i) * 60ms);
}
.benefit-card:hover {
  box-shadow: var(--shadow-md); transform: translateY(-4px);
  border-color: transparent;
}
.benefit-card__icon {
  width: 52px; height: 52px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; margin-bottom: 20px;
}
.benefit-card__title {
  font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 10px;
}
.benefit-card__body { font-size: .88rem; color: var(--gray-600); line-height: 1.7; }

/* ── How it works ──────────────────────────────────────────────────────────── */
.how-it-works {
  background: var(--sand); padding: 88px 5%;
}
.hiw__inner {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 80px; align-items: start;
}
.hiw__right { display: flex; flex-direction: column; gap: 0; }

.step {
  position: relative; display: flex; gap: 20px;
  padding: 20px 24px; border-radius: var(--radius-sm);
  cursor: pointer; transition: background var(--transition);
}
.step:hover, .step--active { background: #fff; box-shadow: var(--shadow); }
.step__num {
  font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 700;
  color: var(--gray-200); line-height: 1; flex-shrink: 0;
  transition: color var(--transition);
}
.step--active .step__num { color: var(--coral); }
.step__body { flex: 1; }
.step__title {
  font-weight: 700; color: var(--indigo); font-size: .96rem; margin-bottom: 6px;
}
.step__desc { font-size: .86rem; color: var(--gray-600); line-height: 1.65; }
.step__line {
  display: none; /* lines handled by spacing */
}

/* ── Pricing ───────────────────────────────────────────────────────────────── */
.pricing { padding: 88px 5%; background: #fff; }
.pricing-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
  align-items: start;
}
.pricing-grid--4 { grid-template-columns: repeat(4, 1fr); }
.pricing-card {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius);
  padding: 36px 32px; background: #fff; position: relative;
  transition: box-shadow var(--transition);
}
.pricing-card:hover { box-shadow: var(--shadow-md); }
.pricing-card--featured {
  border-color: var(--coral); background: var(--indigo);
  transform: scale(1.03); box-shadow: var(--shadow-lg);
}
.pricing-card__badge {
  position: absolute; top: -13px; left: 50%; transform: translateX(-50%);
  background: var(--coral); color: #fff; font-size: .68rem; font-weight: 700;
  letter-spacing: .08em; text-transform: uppercase; padding: 4px 14px;
  border-radius: 50px; white-space: nowrap;
}
.pricing-card__icon { font-size: 1.8rem; margin-bottom: 16px; }
.pricing-card__name {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 14px;
}
.pricing-card--featured .pricing-card__name { color: #fff; }
.pricing-card__rate {
  display: flex; align-items: baseline; gap: 8px; margin-bottom: 14px;
}
.rate-num {
  font-family: 'Fraunces', serif; font-size: 3rem; font-weight: 700;
  color: var(--coral); line-height: 1;
}
.rate-label {
  font-size: .8rem; color: var(--gray-600);
}
.pricing-card--featured .rate-label { color: rgba(255,255,255,.6); }
.pricing-card__desc {
  font-size: .84rem; color: var(--gray-600); line-height: 1.6; margin-bottom: 22px;
}
.pricing-card--featured .pricing-card__desc { color: rgba(255,255,255,.65); }
.pricing-card__features {
  list-style: none; padding: 0; margin: 0 0 28px;
  display: flex; flex-direction: column; gap: 10px;
}
.pricing-card__features li {
  font-size: .86rem; color: var(--indigo); display: flex; align-items: flex-start; gap: 8px;
}
.pricing-card--featured .pricing-card__features li { color: rgba(255,255,255,.85); }
.check { color: var(--teal); font-weight: 700; flex-shrink: 0; }
.btn-full { width: 100%; }
.pricing-note {
  text-align: center; margin-top: 36px; font-size: .84rem; color: var(--gray-600);
}
.pricing-note a { color: var(--coral); font-weight: 600; text-decoration: none; }
.pricing-note a:hover { text-decoration: underline; }

/* ── Dashboard preview ─────────────────────────────────────────────────────── */
.dashboard-preview { padding: 88px 5%; background: var(--gray-50); }
.dp__inner {
  display: grid; grid-template-columns: 1fr 1.2fr; gap: 64px; align-items: center;
}
.dp__features { list-style: none; padding: 0; margin: 28px 0 0; display: flex; flex-direction: column; gap: 20px; }
.dp__feature { display: flex; gap: 16px; }
.dp__feature-icon {
  width: 40px; height: 40px; border-radius: 10px;
  background: var(--white); border: 1.5px solid var(--gray-200);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; flex-shrink: 0; box-shadow: var(--shadow);
}
.dp__feature strong { display: block; font-size: .9rem; color: var(--indigo); margin-bottom: 3px; }
.dp__feature p { font-size: .82rem; color: var(--gray-600); margin: 0; }

/* ── Mock browser ──────────────────────────────────────────────────────────── */
.mock-card {
  border-radius: 16px; overflow: hidden;
  box-shadow: var(--shadow-lg); background: #fff;
  border: 1px solid var(--gray-200);
}
.mock-topbar {
  background: var(--gray-50); border-bottom: 1px solid var(--gray-200);
  padding: 10px 16px; display: flex; align-items: center; gap: 12px;
}
.mock-dots { display: flex; gap: 5px; }
.mock-dots span {
  width: 10px; height: 10px; border-radius: 50%; background: var(--gray-200);
}
.mock-dots span:first-child { background: #ff5f57; }
.mock-dots span:nth-child(2) { background: #febc2e; }
.mock-dots span:last-child  { background: #28c840; }
.mock-url {
  font-size: .72rem; color: var(--gray-400); font-family: monospace;
  background: var(--white); border: 1px solid var(--gray-200);
  padding: 3px 10px; border-radius: 6px;
}
.mock-body { display: flex; }
.mock-sidebar {
  width: 120px; background: var(--indigo); padding: 16px 0; flex-shrink: 0;
}
.mock-nav-item {
  padding: 9px 16px; font-size: .72rem; font-weight: 600;
  color: rgba(255,255,255,.5); cursor: default;
}
.mock-nav-item.active { color: #fff; background: rgba(255,255,255,.1); border-left: 2px solid var(--coral); }
.mock-main { flex: 1; padding: 16px; display: flex; flex-direction: column; gap: 14px; }
.mock-stats { display: flex; gap: 10px; }
.mock-stat {
  flex: 1; background: var(--gray-50); border-radius: 8px;
  padding: 10px 12px; display: flex; flex-direction: column; gap: 2px;
}
.mock-stat__num { font-size: .9rem; font-weight: 700; font-family: 'Fraunces', serif; }
.mock-stat__label { font-size: .62rem; color: var(--gray-400); font-weight: 600; text-transform: uppercase; }
.mock-chart {
  background: var(--gray-50); border-radius: 8px; padding: 12px;
}
.mock-chart__bars {
  display: flex; align-items: flex-end; gap: 3px; height: 60px; margin-bottom: 6px;
}
.mock-bar {
  flex: 1; border-radius: 3px 3px 0 0; background: var(--coral);
  opacity: .7; min-height: 10%;
}
.mock-bar:nth-child(even) { background: var(--teal); }
.mock-chart__label { font-size: .65rem; color: var(--gray-400); font-weight: 600; }
.mock-bookings { display: flex; flex-direction: column; gap: 6px; }
.mock-booking {
  display: flex; align-items: center; gap: 8px;
  background: var(--gray-50); border-radius: 6px; padding: 7px 10px;
}
.mock-booking__avatar {
  width: 26px; height: 26px; border-radius: 50%;
  background: var(--indigo); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: .7rem; font-weight: 700; flex-shrink: 0;
}
.mock-booking__info { flex: 1; }
.mock-booking__info strong { display: block; font-size: .72rem; color: var(--indigo); }
.mock-booking__info span  { font-size: .65rem; color: var(--gray-400); }
.mock-booking__status {
  font-size: .65rem; font-weight: 700; padding: 2px 8px;
  border-radius: 50px; text-transform: capitalize;
}
.status--confirmed { background: rgba(46,196,182,.15); color: var(--teal-dk); }
.status--pending   { background: rgba(255,90,95,.1); color: var(--coral); }

/* ── Testimonials ──────────────────────────────────────────────────────────── */
.testimonials {
  background: var(--indigo); padding: 88px 5%;
}
.testimonials .section-title { color: #fff; }
.testimonials-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
}
.testimonial-card {
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
  border-radius: var(--radius); padding: 32px;
  display: flex; flex-direction: column; gap: 16px;
  transition: background var(--transition);
}
.testimonial-card:hover { background: rgba(255,255,255,.12); }
.testimonial-card__stars { color: #ffd700; font-size: .88rem; letter-spacing: 2px; }
.testimonial-card__text {
  font-size: .9rem; color: rgba(255,255,255,.8); line-height: 1.75; flex: 1;
  font-style: italic;
}
.testimonial-card__author {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  padding-top: 16px; border-top: 1px solid rgba(255,255,255,.1);
}
.t-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.testimonial-card__author strong { display: block; font-size: .86rem; color: #fff; }
.testimonial-card__author span { font-size: .78rem; color: rgba(255,255,255,.5); }
.testimonial-card__metric {
  margin-left: auto; text-align: right;
}
.metric-num {
  display: block; font-family: 'Fraunces', serif; font-size: 1.3rem;
  font-weight: 700; color: var(--coral);
}
.metric-label { font-size: .68rem; color: rgba(255,255,255,.45); font-weight: 600; text-transform: uppercase; }

/* ── FAQ ───────────────────────────────────────────────────────────────────── */
.faq { padding: 88px 5%; }
.faq__inner {
  display: grid; grid-template-columns: 1fr 1.6fr;
  gap: 64px; align-items: start;
}
.faq__right { display: flex; flex-direction: column; gap: 2px; }
.faq-item {
  border-bottom: 1px solid var(--gray-200);
}
.faq-item__q {
  width: 100%; display: flex; justify-content: space-between; align-items: center;
  background: none; border: none; padding: 18px 0;
  font-family: 'DM Sans', sans-serif; font-size: .94rem; font-weight: 700;
  color: var(--indigo); cursor: pointer; text-align: left; gap: 16px;
  transition: color var(--transition);
}
.faq-item__q:hover { color: var(--coral); }
.faq-item.open .faq-item__q { color: var(--coral); }
.faq-item__chevron {
  font-size: 1.3rem; font-weight: 400; flex-shrink: 0;
  color: var(--coral);
}
.faq-item__a {
  font-size: .88rem; color: var(--gray-600); line-height: 1.75;
  padding-bottom: 18px;
}

/* ── Final CTA ─────────────────────────────────────────────────────────────── */
.final-cta {
  background: linear-gradient(135deg, var(--coral) 0%, #e04045 100%);
  padding: 88px 5%; text-align: center;
}
.final-cta__inner { max-width: 640px; margin: 0 auto; }
.final-cta__title {
  font-family: 'Fraunces', serif; font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 700; color: #fff; margin-bottom: 16px;
}
.final-cta__sub {
  font-size: 1rem; color: rgba(255,255,255,.85);
  line-height: 1.7; margin-bottom: 36px;
}
.final-cta__actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.btn-ghost-light {
  background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.4);
  color: #fff; border-radius: 50px; padding: 12px 24px;
  font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: .9rem;
  text-decoration: none; display: inline-flex; align-items: center;
  transition: background var(--transition); cursor: pointer;
}
.btn-ghost-light:hover { background: rgba(255,255,255,.25); }

/* ── Modals ────────────────────────────────────────────────────────────────── */
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(22,20,15,.7);
  z-index: 300; display: flex; align-items: center; justify-content: center;
  padding: 20px; overflow-y: auto;
}
.modal-fade-enter-active, .modal-fade-leave-active { transition: all .25s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; transform: scale(.97); }

.apply-modal {
  background: #fff; border-radius: 20px;
  width: 100%; max-width: 640px;
  max-height: 90vh; overflow-y: auto;
  position: relative; padding: 36px;
  box-shadow: 0 32px 80px rgba(0,0,0,.25);
}
.apply-modal--sm       { max-width: 480px; }
.apply-modal--register { max-width: 560px; }
.modal-close {
  position: absolute; top: 18px; right: 18px;
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--gray-100); border: none; color: var(--gray-600);
  font-size: .9rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background var(--transition);
}
.modal-close:hover { background: var(--coral); color: #fff; }
.apply-modal__header { text-align: center; margin-bottom: 28px; }
.apply-modal__icon { font-size: 2.4rem; margin-bottom: 10px; }
.apply-modal__title {
  font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 6px;
}
.apply-modal__sub { font-size: .88rem; color: var(--gray-600); }

/* Form */
.apply-form { display: flex; flex-direction: column; gap: 18px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 7px; }
.form-group label { font-size: .78rem; font-weight: 700; color: var(--indigo); }
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
.radio-group, .checkbox-group {
  display: flex; gap: 8px; flex-wrap: wrap;
}
.radio-opt, .checkbox-opt {
  display: flex; align-items: center; gap: 6px;
  font-size: .82rem; color: var(--gray-600); cursor: pointer;
  background: var(--gray-50); border: 1.5px solid var(--gray-200);
  padding: 5px 12px; border-radius: 50px;
  transition: all var(--transition);
}
.radio-opt:has(input:checked),
.checkbox-opt:has(input:checked) {
  background: var(--coral-lt); border-color: var(--coral); color: var(--coral);
}
.radio-opt input, .checkbox-opt input { display: none; }
.form-error { font-size: .82rem; color: var(--coral); font-weight: 600; }
.form-submit { padding: 14px !important; font-size: .94rem !important; }
.form-legal { font-size: .75rem; color: var(--gray-400); text-align: center; }
.form-legal a { color: var(--coral); text-decoration: none; }

/* Success */
.apply-success {
  text-align: center; padding: 20px 0;
  display: flex; flex-direction: column; align-items: center; gap: 14px;
}
.apply-success__icon { font-size: 3.5rem; }
.apply-success h3 {
  font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700;
  color: var(--indigo);
}
.apply-success p { font-size: .9rem; color: var(--gray-600); line-height: 1.65; max-width: 380px; }

/* ── Animations ────────────────────────────────────────────────────────────── */
@keyframes card-in {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1024px) {
  .benefits-grid,
  .testimonials-grid { grid-template-columns: repeat(2, 1fr); }
  .pricing-grid--4   { grid-template-columns: repeat(2, 1fr); }
  .pricing-grid:not(.pricing-grid--4) { grid-template-columns: repeat(2, 1fr); }
  .hiw__inner,
  .dp__inner,
  .faq__inner { grid-template-columns: 1fr; gap: 40px; }
  .pricing-card--featured { transform: none; }
}
@media (max-width: 768px) {
  .hero__inner { padding: 60px 5%; }
  .benefits-grid,
  .pricing-grid,
  .pricing-grid--4,
  .testimonials-grid { grid-template-columns: 1fr; }
  .trust-bar { flex-direction: column; align-items: flex-start; gap: 12px; }
  .form-row { grid-template-columns: 1fr; }
  .hero__stats { gap: 24px; }
  .hero__actions { flex-direction: column; }
  .hero__actions .btn-lg, .hero__actions .btn-ghost { width: 100%; text-align: center; }
}
</style>