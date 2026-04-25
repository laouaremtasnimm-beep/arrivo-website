<template>
  <div class="providers-page">
    <NavBar />

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <section class="hero">
      <div class="hero__bg">
        <img
          src="https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?w=1600&q=80"
          alt="Service provider background"
          class="hero__bg-img"
        />
        <div class="hero__bg-overlay" />
      </div>
      <div class="hero__inner">
        <div class="hero__badge">
          <span class="badge-dot" />
          Service Provider Programme
        </div>
        <h1 class="hero__title">
          Turn your expertise<br />
          into <em>bookings</em>
        </h1>
        <p class="hero__sub">
          Whether you lead hikes, drive transfers, cook private dinners, or teach surf lessons —
          Voyago connects you directly with travellers who are ready to book right now.
        </p>
        <div class="hero__actions">
          <button class="btn btn-teal btn-lg" @click="openApply">
            List your service free →
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

    <!-- ── Trust bar ─────────────────────────────────────────────────────── -->
    <div class="trust-bar">
      <p class="trust-bar__label">Providers in 80+ destinations worldwide</p>
      <div class="trust-bar__logos">
        <span v-for="name in providerNames" :key="name" class="trust-logo">{{ name }}</span>
      </div>
    </div>

    <!-- ── Service categories showcase ───────────────────────────────────── -->
    <section class="categories-section">
      <div class="section-header">
        <p class="section-overline">What you can list</p>
        <h2 class="section-title">Every kind of local<br /><em>expertise</em></h2>
        <p class="section-sub">
          Voyago hosts services across every travel niche. If travellers want it, you can offer it.
        </p>
      </div>
      <div class="categories-grid">
        <div
          v-for="(cat, i) in serviceCategories"
          :key="i"
          class="cat-card"
          :style="{ '--i': i }"
        >
          <div class="cat-card__img-wrap">
            <img :src="cat.img" :alt="cat.title" class="cat-card__img" />
            <div class="cat-card__overlay" />
          </div>
          <div class="cat-card__body">
            <span class="cat-card__icon">{{ cat.icon }}</span>
            <h3 class="cat-card__title">{{ cat.title }}</h3>
            <p class="cat-card__examples">{{ cat.examples }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Benefits ──────────────────────────────────────────────────────── -->
    <section class="benefits" id="benefits">
      <div class="section-header">
        <p class="section-overline">Why Voyago</p>
        <h2 class="section-title">Built for the people<br /><em>who deliver the magic</em></h2>
        <p class="section-sub">
          We handle discovery, payments, and reviews — so you focus entirely on the experience.
        </p>
      </div>
      <div class="benefits-grid">
        <div v-for="(b, i) in benefits" :key="i" class="benefit-card" :style="{ '--i': i }">
          <div class="benefit-card__icon" :style="{ background: b.iconBg }">{{ b.icon }}</div>
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
          <h2 class="section-title">Your first booking<br /><em>in 48 hours</em></h2>
          <p class="section-sub">
            Create your profile, list a service, and start appearing to travellers searching in your area.
          </p>
          <button class="btn btn-teal" style="margin-top:28px" @click="openApply">
            Start now →
          </button>
        </div>
        <div class="hiw__right">
          <div
            v-for="(step, i) in steps"
            :key="i"
            class="step"
            :class="{ 'step--active': activeStep === i }"
            @mouseenter="activeStep = i"
          >
            <div class="step__num">{{ String(i + 1).padStart(2, '0') }}</div>
            <div class="step__body">
              <h4 class="step__title">{{ step.title }}</h4>
              <p class="step__desc" v-show="activeStep === i">{{ step.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Pricing ────────────────────────────────────────────────────────── -->
    <section class="pricing" id="pricing">
      <div class="section-header">
        <p class="section-overline">Pricing</p>
        <h2 class="section-title">Plans for every<br /><em>provider size</em></h2>
        <p class="section-sub">Start free. Upgrade when your bookings grow.</p>
      </div>
      <div class="pricing-grid">
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
            :class="plan.featured ? 'btn-teal' : 'btn-outline'"
            @click="plan.isContact ? openContactModal() : openApply()"
          >
            {{ plan.cta }}
          </button>
        </div>
      </div>
      <p class="pricing-note">
        Commission applies only on completed bookings. Monthly plans cancel anytime.
        <a href="#" @click.prevent="openContactModal">Questions? Contact us →</a>
      </p>
    </section>

    <!-- ── Dashboard preview ─────────────────────────────────────────────── -->
    <section class="dashboard-preview">
      <div class="dp__inner">
        <div class="dp__text">
          <p class="section-overline">Provider Dashboard</p>
          <h2 class="section-title">Manage everything<br /><em>on the go</em></h2>
          <ul class="dp__features">
            <li v-for="f in dashboardFeatures" :key="f.title" class="dp__feature">
              <span class="dp__feature-icon">{{ f.icon }}</span>
              <div>
                <strong>{{ f.title }}</strong>
                <p>{{ f.desc }}</p>
              </div>
            </li>
          </ul>
          <button class="btn btn-teal" style="margin-top:28px" @click="openApply">
            Get early access →
          </button>
        </div>
        <div class="dp__mock">
          <div class="mock-card">
            <div class="mock-topbar">
              <div class="mock-dots"><span /><span /><span /></div>
              <span class="mock-url">voyago.com/dashboard</span>
            </div>
            <div class="mock-body">
              <div class="mock-sidebar">
                <div
                  v-for="item in mockNav"
                  :key="item"
                  class="mock-nav-item"
                  :class="{ active: item === 'Overview' }"
                >{{ item }}</div>
              </div>
              <div class="mock-main">
                <div class="mock-stats">
                  <div v-for="s in mockStats" :key="s.label" class="mock-stat">
                    <span class="mock-stat__num" :style="{ color: s.color }">{{ s.num }}</span>
                    <span class="mock-stat__label">{{ s.label }}</span>
                  </div>
                </div>
                <div class="mock-availability">
                  <span class="mock-avail__label">This week's availability</span>
                  <div class="mock-avail__slots">
                    <div
                      v-for="slot in availSlots"
                      :key="slot.day"
                      class="mock-slot"
                      :class="'mock-slot--' + slot.status"
                    >
                      <span class="mock-slot__day">{{ slot.day }}</span>
                      <span class="mock-slot__status">{{ slot.status }}</span>
                    </div>
                  </div>
                </div>
                <div class="mock-bookings">
                  <div v-for="b in mockBookings" :key="b.name" class="mock-booking">
                    <div class="mock-booking__avatar">{{ b.name[0] }}</div>
                    <div class="mock-booking__info">
                      <strong>{{ b.name }}</strong>
                      <span>{{ b.service }}</span>
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

    <!-- ── Earnings calculator ────────────────────────────────────────────── -->
    <section class="calculator">
      <div class="calc__inner">
        <div class="section-header" style="margin-bottom:40px">
          <p class="section-overline">Earnings potential</p>
          <h2 class="section-title">See what you could<br /><em>earn on Voyago</em></h2>
          <p class="section-sub">Drag the sliders and watch your projected monthly income update live.</p>
        </div>
        <div class="calc__body">
          <div class="calc__controls">
            <div class="calc__control">
              <div class="calc__control-header">
                <label>Price per booking</label>
                <span class="calc__val">${{ pricePerBooking }}</span>
              </div>
              <input
                type="range" v-model.number="pricePerBooking"
                min="20" max="500" step="10" class="calc__slider"
              />
              <div class="calc__ticks"><span>$20</span><span>$500</span></div>
            </div>
            <div class="calc__control">
              <div class="calc__control-header">
                <label>Bookings per month</label>
                <span class="calc__val">{{ bookingsPerMonth }}</span>
              </div>
              <input
                type="range" v-model.number="bookingsPerMonth"
                min="1" max="60" step="1" class="calc__slider"
              />
              <div class="calc__ticks"><span>1</span><span>60</span></div>
            </div>
            <div class="calc__control">
              <div class="calc__control-header">
                <label>Your plan commission</label>
                <span class="calc__val">{{ commissionRate }}%</span>
              </div>
              <div class="calc__plan-pills">
                <button
                  v-for="opt in commissionOptions"
                  :key="opt.label"
                  class="calc__pill"
                  :class="{ active: commissionRate === opt.rate }"
                  @click="commissionRate = opt.rate"
                >{{ opt.label }}</button>
              </div>
            </div>
          </div>
          <div class="calc__result">
            <div class="calc__result-ring">
              <svg viewBox="0 0 120 120" class="calc__ring-svg">
                <circle cx="60" cy="60" r="50" class="calc__ring-track" />
                <circle
                  cx="60" cy="60" r="50"
                  class="calc__ring-fill"
                  :stroke-dasharray="ringDash"
                  stroke-dashoffset="0"
                />
              </svg>
              <div class="calc__result-inner">
                <span class="calc__result-label">You earn</span>
                <span class="calc__result-num">${{ yourEarnings.toLocaleString() }}</span>
                <span class="calc__result-period">/ month</span>
              </div>
            </div>
            <div class="calc__result-breakdown">
              <div class="calc__breakdown-row">
                <span>Gross revenue</span>
                <strong>${{ grossRevenue.toLocaleString() }}</strong>
              </div>
              <div class="calc__breakdown-row calc__breakdown-row--commission">
                <span>Platform fee ({{ commissionRate }}%)</span>
                <strong>−${{ commission.toLocaleString() }}</strong>
              </div>
              <div class="calc__breakdown-row calc__breakdown-row--total">
                <span>Your take-home</span>
                <strong>${{ yourEarnings.toLocaleString() }}</strong>
              </div>
            </div>
            <button class="btn btn-teal calc__cta" @click="openApply">
              Start earning →
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Testimonials ───────────────────────────────────────────────────── -->
    <section class="testimonials">
      <div class="section-header section-header--light">
        <p class="section-overline">Provider stories</p>
        <h2 class="section-title" style="color:#fff">Real providers, real results</h2>
      </div>
      <div class="testimonials-grid">
        <div v-for="(t, i) in testimonials" :key="i" class="testimonial-card">
          <div class="testimonial-card__stars">{{ '★'.repeat(t.rating) }}</div>
          <p class="testimonial-card__text">"{{ t.text }}"</p>
          <div class="testimonial-card__author">
            <img :src="t.avatar" class="t-avatar" :alt="t.name" />
            <div>
              <strong>{{ t.name }}</strong>
              <span>{{ t.role }} · {{ t.location }}</span>
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
          <p class="section-sub">Still unsure? Our partner team replies within 2 hours.</p>
          <button class="btn btn-outline" style="margin-top:24px" @click="openContactModal">
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

    <!-- ── Final CTA ──────────────────────────────────────────────────────── -->
    <section class="final-cta">
      <div class="final-cta__inner">
        <h2 class="final-cta__title">Your next booking is waiting.</h2>
        <p class="final-cta__sub">
          Free to join. List any service. Start earning in under 48 hours.
        </p>
        <div class="final-cta__actions">
          <button class="btn btn-white btn-lg" @click="openApply">
            Create your provider profile →
          </button>
          <RouterLink to="/partners/agencies" class="btn btn-ghost-light btn-lg">
            I'm a travel agency instead
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
              <div class="apply-modal__icon">🤝</div>
              <h3 class="apply-modal__title">Join as a Service Provider</h3>
              <p class="apply-modal__sub">Create your account — role is pre-selected as Provider.</p>
            </div>
            <RegisterForm
              default-role="provider"
              @switch-mode="applyOpen = false; $router.push('/auth?mode=login')"
            />
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
                <input v-model="contactForm.email" type="email" placeholder="you@example.com" />
              </div>
              <div class="form-group">
                <label>Message</label>
                <textarea v-model="contactForm.message" rows="4" placeholder="Ask us anything about listing your service…" />
              </div>
              <button class="btn btn-teal btn-full form-submit" @click="contactOpen = false">
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
import { useRouter } from 'vue-router'
import NavBar        from '@/components/home/NavBar.vue'
import SiteFooter    from '@/components/home/SiteFooter.vue'
import RegisterForm  from '@/components/auth/RegisterForm.vue'

const router = useRouter()

// ── Hero stats ─────────────────────────────────────────────────────────────
const heroStats = [
  { num: '5,800+', label: 'Active providers' },
  { num: '320K',   label: 'Services booked' },
  { num: '80+',    label: 'Destinations' },
  { num: '4.9★',  label: 'Avg. service rating' },
]

// ── Trust logos ────────────────────────────────────────────────────────────
const providerNames = ['Bali Surf School', 'Desert Nomad', 'Roma Table', 'Alpine Guides', 'IslandHops', 'CaféCulture', 'NileExpeditions', 'TokyoDrives']

// ── Service categories ─────────────────────────────────────────────────────
const serviceCategories = [
  {
    icon: '🥾',
    title: 'Tours & Guides',
    examples: 'City walks, jungle treks, historical tours, night tours',
    img: 'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=600&q=80',
  },
  {
    icon: '🚐',
    title: 'Transfers & Transport',
    examples: 'Airport pickups, private drivers, boat charters',
    img: 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=600&q=80',
  },
  {
    icon: '🍽️',
    title: 'Food Experiences',
    examples: 'Private chefs, cooking classes, food tours, wine tastings',
    img: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&q=80',
  },
  {
    icon: '🏄',
    title: 'Sports & Adventure',
    examples: 'Surf lessons, diving, paragliding, horseback riding',
    img: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&q=80',
  },
  {
    icon: '🧘',
    title: 'Wellness & Retreats',
    examples: 'Yoga sessions, spa treatments, meditation, sound healing',
    img: 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=600&q=80',
  },
  {
    icon: '📸',
    title: 'Creative & Media',
    examples: 'Photography tours, portrait sessions, filming locations',
    img: 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?w=600&q=80',
  },
]

// ── Benefits ───────────────────────────────────────────────────────────────
const benefits = [
  {
    icon: '🌍',
    iconBg: 'rgba(46,196,182,.1)',
    title: 'Global traveller audience',
    body: 'Get discovered by thousands of travellers actively searching for local experiences — not passive social scrollers.',
  },
  {
    icon: '📅',
    iconBg: 'rgba(255,90,95,.1)',
    title: 'Availability management',
    body: 'Set your own schedule, block dates, and define capacity per slot. You stay in full control of when and how many you accept.',
  },
  {
    icon: '💳',
    iconBg: 'rgba(45,49,66,.08)',
    title: 'Secure, fast payouts',
    body: 'Payment collected upfront from the traveller. Your earnings are released within 3 days of service completion.',
  },
  {
    icon: '⭐',
    iconBg: 'rgba(46,196,182,.1)',
    title: 'Verified-only reviews',
    body: 'Only travellers who actually completed your service can review you. Authentic ratings that reflect your real quality.',
  },
  {
    icon: '📱',
    iconBg: 'rgba(255,90,95,.1)',
    title: 'Mobile-first dashboard',
    body: 'Confirm bookings, message guests, and track earnings from your phone — no laptop required.',
  },
  {
    icon: '🛡️',
    iconBg: 'rgba(45,49,66,.08)',
    title: 'Provider protection',
    body: 'Cancellation policies you define, dispute resolution support, and a dedicated partner team behind you.',
  },
]

// ── How it works ───────────────────────────────────────────────────────────
const activeStep = ref(0)
const steps = [
  {
    title: 'Create your provider profile',
    desc: 'Add your name, photo, bio, location, and the type of services you offer. Your profile is your face to travellers — make it count.',
  },
  {
    title: 'List your first service',
    desc: 'Use our service editor: title, description, duration, price, photos, availability slots, and what\'s included. Preview it before it goes live.',
  },
  {
    title: 'Get verified',
    desc: 'Our team reviews your listing within 24 hours. For certain categories (diving, trekking) we may request a licence or safety certificate.',
  },
  {
    title: 'Receive booking requests',
    desc: 'Travellers find your listing and request to book. You get a notification with all their details — confirm or decline from your dashboard.',
  },
  {
    title: 'Deliver & get paid',
    desc: 'Run a great experience. Payment is released to you automatically within 3 days of the service date.',
  },
]

// ── Pricing plans ──────────────────────────────────────────────────────────
const plans = [
  {
    icon: '🌱',
    name: 'Free',
    rate: 'Free',
    rateLabel: 'forever',
    desc: 'Perfect for testing the platform with one or two services.',
    features: [
      'Up to 2 active services',
      'Standard search placement',
      'Booking & availability calendar',
      'Basic support',
    ],
    cta: 'Get started',
    featured: false,
    isContact: false,
  },
  {
    icon: '⚡',
    name: 'Pro',
    rate: '$9',
    rateLabel: '/mo + 8% per booking',
    desc: 'For active providers who want more visibility and unlimited listings.',
    features: [
      'Unlimited services',
      'Boosted search placement',
      'Priority support',
      'Exclusive deal slots',
      'Early access to new features',
      'Analytics & earnings reports',
    ],
    cta: 'Join Pro',
    featured: true,
    isContact: false,
  },
  {
    icon: '💼',
    name: 'Business',
    rate: '$29',
    rateLabel: '/mo + 6% per booking',
    desc: 'For operators managing multiple services, staff, and high booking volume.',
    features: [
      'Everything in Pro',
      'Team seats (5 members)',
      'Multi-service scheduling',
      'Advanced analytics',
      'API access',
    ],
    cta: 'Start Business',
    featured: false,
    isContact: false,
  },
  {
    icon: '🏆',
    name: 'Enterprise',
    rate: 'Custom',
    rateLabel: 'volume rates',
    desc: 'For large operators, schools, or multi-location businesses.',
    features: [
      'Everything in Business',
      'Dedicated account manager',
      'White-label options',
      'Custom integrations',
      'SLA guarantee',
    ],
    cta: 'Contact us',
    featured: false,
    isContact: true,
  },
]

// ── Dashboard preview ──────────────────────────────────────────────────────
const mockNav    = ['Overview', 'Services', 'Bookings', 'Availability', 'Reviews', 'Payouts']
const mockStats  = [
  { num: '€4,320', label: 'Earnings',   color: '#2EC4B6' },
  { num: '54',     label: 'Bookings',   color: '#FF5A5F' },
  { num: '4.9★',  label: 'Rating',     color: '#2D3142' },
]
const availSlots = [
  { day: 'Mon', status: 'open' },
  { day: 'Tue', status: 'full' },
  { day: 'Wed', status: 'open' },
  { day: 'Thu', status: 'open' },
  { day: 'Fri', status: 'full' },
  { day: 'Sat', status: 'full' },
  { day: 'Sun', status: 'open' },
]
const mockBookings = [
  { name: 'Elena V.',  service: 'Private cooking class',  status: 'confirmed' },
  { name: 'Ahmed K.',  service: 'Desert sunset tour',     status: 'pending'   },
  { name: 'Sophie M.', service: 'Airport transfer (VIP)', status: 'confirmed' },
]
const dashboardFeatures = [
  { icon: '📅', title: 'Availability calendar',  desc: 'Set open slots, block dates, cap group sizes.' },
  { icon: '💬', title: 'Guest messaging',         desc: 'Pre-trip comms and special requests in one thread.' },
  { icon: '💸', title: 'Earnings tracker',        desc: 'Real-time income, payout history, monthly reports.' },
  { icon: '🔔', title: 'Instant notifications',   desc: 'New bookings and reviews pushed to your phone.' },
]

// ── Earnings calculator ────────────────────────────────────────────────────
const pricePerBooking  = ref(80)
const bookingsPerMonth = ref(12)
const commissionRate   = ref(8)
const commissionOptions = [
  { label: 'Free (10%)', rate: 10 },
  { label: 'Pro (8%)',   rate: 8  },
  { label: 'Biz (6%)',   rate: 6  },
]
const grossRevenue  = computed(() => pricePerBooking.value * bookingsPerMonth.value)
const commission    = computed(() => Math.round(grossRevenue.value * commissionRate.value / 100))
const yourEarnings  = computed(() => grossRevenue.value - commission.value)
// SVG ring: circumference ≈ 314; fill proportional to keep %
const ringDash = computed(() => {
  const pct  = Math.min(yourEarnings.value / (grossRevenue.value || 1), 1)
  const circ = 2 * Math.PI * 50
  return `${(pct * circ).toFixed(1)} ${circ.toFixed(1)}`
})

// ── Testimonials ───────────────────────────────────────────────────────────
const testimonials = [
  {
    rating: 5,
    text: 'I was doing 4 tours a week through word-of-mouth. Three months after listing on Voyago, I\'m fully booked six weeks out. The travellers who book are prepared, respectful, and they actually leave reviews.',
    name: 'Omar Benali',
    role: 'Desert guide',
    location: 'Merzouga, Morocco',
    avatar: 'https://i.pravatar.cc/48?img=12',
    metric: '3×',
    metricLabel: 'Bookings increase',
  },
  {
    rating: 5,
    text: 'The availability calendar alone was worth joining. Before, I was managing WhatsApp, email, and a paper diary. Now everything is in one place and guests can book without even contacting me first.',
    name: 'Ayu Wulandari',
    role: 'Yoga instructor',
    location: 'Ubud, Bali',
    avatar: 'https://i.pravatar.cc/48?img=44',
    metric: '15 hrs',
    metricLabel: 'Admin saved/wk',
  },
  {
    rating: 5,
    text: 'I was nervous about the commission but Voyago brings a completely different calibre of traveller. Higher budgets, better behaviour, and they actually show up. The earnings more than cover the fee.',
    name: 'Marco Ferretti',
    role: 'Private chef',
    location: 'Amalfi, Italy',
    avatar: 'https://i.pravatar.cc/48?img=15',
    metric: '+€1,800',
    metricLabel: 'Extra/month',
  },
]

// ── FAQ ────────────────────────────────────────────────────────────────────
const openFaq = ref(null)
const faqs = [
  {
    q: 'What kinds of services can I list?',
    a: 'Almost anything a traveller would want: guided tours, transfers, cooking classes, surf lessons, photography sessions, yoga, wine tastings, and much more. If it\'s a legitimate, legal experience you provide in person, you can list it.',
  },
  {
    q: 'Do I need any certifications or licences?',
    a: 'For many categories (cooking, photography, general tours) no certification is required. For safety-critical services — diving, trekking, horse riding, water sports — we may ask for proof of certification during the verification process.',
  },
  {
    q: 'Can I set my own prices and cancellation policy?',
    a: 'Yes, completely. You set the price, the duration, what\'s included, and your cancellation policy. We provide standard policy templates (Flexible, Moderate, Strict) or you can write a custom one.',
  },
  {
    q: 'When and how do I get paid?',
    a: 'Travellers pay in full when booking. Funds are held securely and released to you within 3 days of the service date. Payout options include bank transfer, PayPal, and Wise. Minimum payout threshold is $20.',
  },
  {
    q: 'What happens if a traveller doesn\'t show up?',
    a: 'Your cancellation policy protects you. If a traveller doesn\'t appear for a confirmed booking, you receive the payout according to your policy terms. No-shows under a Strict policy are paid in full.',
  },
  {
    q: 'Can I manage multiple services and staff?',
    a: 'Yes. Business and Enterprise plan providers can list unlimited services, assign team members to specific bookings, and manage separate availability calendars per staff member or vehicle.',
  },
]

// ── Modals ─────────────────────────────────────────────────────────────────
const applyOpen   = ref(false)
const contactOpen = ref(false)
const contactForm = ref({ email: '', message: '' })

function openApply()        { applyOpen.value   = true }
function openContactModal() { contactOpen.value = true }

// ── Scroll helper ──────────────────────────────────────────────────────────
function scrollTo(id) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<style scoped>
/* ── Base ──────────────────────────────────────────────────────────────────── */
.providers-page {
  min-height: 100vh;
  padding-top: 72px;
  font-family: 'DM Sans', sans-serif;
  background: #fff;
}

/* ── Shared section header ─────────────────────────────────────────────────── */
.section-overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--teal); margin-bottom: 14px;
}
.section-title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.1;
  color: var(--indigo); margin-bottom: 16px;
}
.section-title em { color: var(--teal); font-style: italic; }
.section-sub {
  font-size: .96rem; color: var(--gray-600); line-height: 1.7; max-width: 520px;
}
.section-header { text-align: center; margin-bottom: 56px; }
.section-header .section-sub { margin: 0 auto; }

/* ── Teal button variant ───────────────────────────────────────────────────── */
.btn-teal {
  background: var(--teal); color: #fff; border: none; border-radius: 50px;
  padding: 12px 28px; font-family: 'DM Sans', sans-serif; font-weight: 700;
  font-size: .9rem; cursor: pointer; transition: background var(--transition);
}
.btn-teal:hover { background: var(--teal-dk); }

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.hero {
  position: relative; min-height: 640px;
  display: flex; align-items: center; overflow: hidden;
}
.hero__bg { position: absolute; inset: 0; }
.hero__bg-img { width: 100%; height: 100%; object-fit: cover; object-position: center 30%; }
.hero__bg-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(105deg, rgba(45,49,66,.92) 42%, rgba(46,196,182,.25));
}
.hero__inner {
  position: relative; z-index: 2; padding: 80px 5%; max-width: 720px;
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
  font-weight: 700; line-height: .95; color: #fff; margin-bottom: 22px;
}
.hero__title em { color: var(--teal); font-style: italic; }
.hero__sub {
  font-size: 1.05rem; color: rgba(255,255,255,.8);
  line-height: 1.7; margin-bottom: 36px; max-width: 540px;
}
.hero__actions { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 52px; }
.btn-lg    { padding: 15px 32px !important; font-size: .96rem !important; }
.btn-ghost {
  background: rgba(255,255,255,.12); border: 1.5px solid rgba(255,255,255,.3);
  color: #fff; border-radius: 50px; padding: 12px 24px;
  font-family: 'DM Sans', sans-serif; font-weight: 700; cursor: pointer;
  transition: all var(--transition);
}
.btn-ghost:hover { background: rgba(255,255,255,.22); }
.hero__stats {
  display: flex; gap: 40px; flex-wrap: wrap;
  padding-top: 36px; border-top: 1px solid rgba(255,255,255,.18);
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
.trust-bar__logos { display: flex; gap: 20px; flex-wrap: wrap; }
.trust-logo {
  font-size: .82rem; font-weight: 700; color: var(--gray-400);
  padding: 4px 14px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: #fff; white-space: nowrap;
}

/* ── Service categories ────────────────────────────────────────────────────── */
.categories-section { padding: 88px 5%; background: var(--gray-50); }
.categories-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
.cat-card {
  border-radius: var(--radius); overflow: hidden; cursor: default;
  position: relative; background: #fff; box-shadow: var(--shadow);
  transition: transform var(--transition), box-shadow var(--transition);
  animation: card-in .4s ease both;
  animation-delay: calc(var(--i) * 55ms);
}
.cat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }
.cat-card__img-wrap { position: relative; height: 160px; overflow: hidden; }
.cat-card__img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform .5s ease;
}
.cat-card:hover .cat-card__img { transform: scale(1.06); }
.cat-card__overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, transparent 30%, rgba(45,49,66,.5));
}
.cat-card__body {
  padding: 18px 20px 22px;
  display: flex; flex-direction: column; gap: 6px;
}
.cat-card__icon { font-size: 1.4rem; }
.cat-card__title {
  font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 700;
  color: var(--indigo);
}
.cat-card__examples {
  font-size: .8rem; color: var(--gray-600); line-height: 1.5;
}

/* ── Benefits ──────────────────────────────────────────────────────────────── */
.benefits { padding: 88px 5%; background: #fff; }
.benefits-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
.benefit-card {
  padding: 32px; border-radius: var(--radius);
  border: 1.5px solid var(--gray-200); background: #fff;
  transition: box-shadow var(--transition), transform var(--transition), border-color var(--transition);
  animation: card-in .4s ease both;
  animation-delay: calc(var(--i) * 60ms);
}
.benefit-card:hover {
  box-shadow: var(--shadow-md); transform: translateY(-4px); border-color: transparent;
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
.how-it-works { background: #f0faf9; padding: 88px 5%; }
.hiw__inner { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: start; }
.hiw__right { display: flex; flex-direction: column; gap: 0; }
.step {
  display: flex; gap: 20px; padding: 20px 24px;
  border-radius: var(--radius-sm); cursor: pointer;
  transition: background var(--transition), box-shadow var(--transition);
}
.step:hover, .step--active { background: #fff; box-shadow: var(--shadow); }
.step__num {
  font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 700;
  color: var(--gray-200); line-height: 1; flex-shrink: 0;
  transition: color var(--transition);
}
.step--active .step__num { color: var(--teal); }
.step__body { flex: 1; }
.step__title { font-weight: 700; color: var(--indigo); font-size: .96rem; margin-bottom: 6px; }
.step__desc  { font-size: .86rem; color: var(--gray-600); line-height: 1.65; }

/* ── Pricing ───────────────────────────────────────────────────────────────── */
.pricing { padding: 88px 5%; background: #fff; }
.pricing-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 24px; align-items: start;
}
.pricing-card {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius);
  padding: 36px 28px; background: #fff; position: relative;
  transition: box-shadow var(--transition);
}
.pricing-card:hover { box-shadow: var(--shadow-md); }
.pricing-card--featured {
  border-color: var(--teal); background: var(--indigo);
  box-shadow: var(--shadow-lg);
}
.pricing-card__badge {
  position: absolute; top: -13px; left: 50%; transform: translateX(-50%);
  background: var(--teal); color: #fff; font-size: .68rem; font-weight: 700;
  letter-spacing: .08em; text-transform: uppercase;
  padding: 4px 14px; border-radius: 50px; white-space: nowrap;
}
.pricing-card__icon { font-size: 1.8rem; margin-bottom: 16px; }
.pricing-card__name {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 14px;
}
.pricing-card--featured .pricing-card__name { color: #fff; }
.pricing-card__rate { display: flex; align-items: baseline; gap: 8px; margin-bottom: 14px; }
.rate-num {
  font-family: 'Fraunces', serif; font-size: 3rem; font-weight: 700;
  color: var(--teal); line-height: 1;
}
.rate-label { font-size: .8rem; color: var(--gray-600); }
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
.pricing-note a { color: var(--teal); font-weight: 600; text-decoration: none; }
.pricing-note a:hover { text-decoration: underline; }

/* ── Dashboard preview ─────────────────────────────────────────────────────── */
.dashboard-preview { padding: 88px 5%; background: var(--gray-50); }
.dp__inner {
  display: grid; grid-template-columns: 1fr 1.2fr;
  gap: 64px; align-items: center;
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

/* Mock browser */
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
.mock-dots span:last-child   { background: #28c840; }
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
.mock-nav-item.active {
  color: #fff; background: rgba(255,255,255,.1);
  border-left: 2px solid var(--teal);
}
.mock-main { flex: 1; padding: 14px; display: flex; flex-direction: column; gap: 12px; }
.mock-stats { display: flex; gap: 8px; }
.mock-stat {
  flex: 1; background: var(--gray-50); border-radius: 8px;
  padding: 9px 10px; display: flex; flex-direction: column; gap: 2px;
}
.mock-stat__num   { font-size: .88rem; font-weight: 700; font-family: 'Fraunces', serif; }
.mock-stat__label { font-size: .6rem; color: var(--gray-400); font-weight: 600; text-transform: uppercase; }

/* Availability slots */
.mock-availability { background: var(--gray-50); border-radius: 8px; padding: 10px 12px; }
.mock-avail__label { font-size: .62rem; color: var(--gray-400); font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 8px; }
.mock-avail__slots { display: flex; gap: 5px; }
.mock-slot {
  flex: 1; border-radius: 6px; padding: 6px 4px;
  display: flex; flex-direction: column; align-items: center; gap: 3px;
}
.mock-slot--open { background: rgba(46,196,182,.15); }
.mock-slot--full { background: rgba(255,90,95,.1); }
.mock-slot__day  { font-size: .58rem; font-weight: 700; color: var(--indigo); }
.mock-slot__status {
  font-size: .52rem; font-weight: 700; text-transform: uppercase;
}
.mock-slot--open .mock-slot__status { color: var(--teal-dk); }
.mock-slot--full .mock-slot__status { color: var(--coral); }

.mock-bookings { display: flex; flex-direction: column; gap: 5px; }
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
.mock-booking__info span   { font-size: .65rem; color: var(--gray-400); }
.mock-booking__status {
  font-size: .65rem; font-weight: 700; padding: 2px 8px;
  border-radius: 50px; text-transform: capitalize;
}
.status--confirmed { background: rgba(46,196,182,.15); color: var(--teal-dk); }
.status--pending   { background: rgba(255,90,95,.1);   color: var(--coral); }

/* ── Earnings calculator ───────────────────────────────────────────────────── */
.calculator { padding: 88px 5%; background: #fff; }
.calc__inner { max-width: 1000px; margin: 0 auto; }
.calc__body {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 56px; align-items: center;
}
.calc__controls { display: flex; flex-direction: column; gap: 28px; }
.calc__control  { display: flex; flex-direction: column; gap: 10px; }
.calc__control-header {
  display: flex; justify-content: space-between; align-items: center;
}
.calc__control-header label {
  font-size: .84rem; font-weight: 700; color: var(--indigo);
}
.calc__val {
  font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: var(--teal);
}
.calc__slider {
  -webkit-appearance: none; appearance: none;
  width: 100%; height: 4px; border-radius: 2px;
  background: var(--gray-200); outline: none; cursor: pointer;
}
.calc__slider::-webkit-slider-thumb {
  -webkit-appearance: none; width: 20px; height: 20px; border-radius: 50%;
  background: var(--teal); cursor: pointer;
  box-shadow: 0 0 0 4px rgba(46,196,182,.2);
  transition: box-shadow var(--transition);
}
.calc__slider::-webkit-slider-thumb:hover { box-shadow: 0 0 0 6px rgba(46,196,182,.25); }
.calc__ticks {
  display: flex; justify-content: space-between;
  font-size: .7rem; color: var(--gray-400);
}
.calc__plan-pills { display: flex; gap: 8px; }
.calc__pill {
  padding: 6px 16px; border-radius: 50px; font-family: 'DM Sans', sans-serif;
  font-size: .82rem; font-weight: 700; cursor: pointer;
  border: 1.5px solid var(--gray-200); background: #fff; color: var(--gray-600);
  transition: all var(--transition);
}
.calc__pill.active { background: var(--teal); border-color: var(--teal); color: #fff; }

/* Result ring */
.calc__result {
  display: flex; flex-direction: column; align-items: center; gap: 24px;
}
.calc__result-ring {
  position: relative; width: 200px; height: 200px;
}
.calc__ring-svg {
  width: 100%; height: 100%;
  transform: rotate(-90deg);
}
.calc__ring-track {
  fill: none; stroke: var(--gray-100); stroke-width: 10;
}
.calc__ring-fill {
  fill: none; stroke: var(--teal); stroke-width: 10;
  stroke-linecap: round;
  transition: stroke-dasharray .4s ease;
}
.calc__result-inner {
  position: absolute; inset: 0;
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;
}
.calc__result-label  { font-size: .72rem; font-weight: 700; color: var(--gray-400); text-transform: uppercase; letter-spacing: .08em; }
.calc__result-num    { font-family: 'Fraunces', serif; font-size: 2.2rem; font-weight: 700; color: var(--indigo); line-height: 1; }
.calc__result-period { font-size: .78rem; color: var(--gray-600); }

.calc__result-breakdown {
  width: 100%; background: var(--gray-50); border-radius: var(--radius-sm);
  padding: 16px 20px; display: flex; flex-direction: column; gap: 10px;
}
.calc__breakdown-row {
  display: flex; justify-content: space-between; align-items: center;
  font-size: .86rem; color: var(--gray-600);
}
.calc__breakdown-row strong { font-size: .9rem; color: var(--indigo); }
.calc__breakdown-row--commission strong { color: var(--coral); }
.calc__breakdown-row--total {
  padding-top: 10px; border-top: 1px solid var(--gray-200);
  font-weight: 700; color: var(--indigo);
}
.calc__breakdown-row--total strong { font-size: 1rem; color: var(--teal); }
.calc__cta { width: 100%; padding: 14px !important; font-size: .94rem !important; }

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
  font-size: .9rem; color: rgba(255,255,255,.8);
  line-height: 1.75; flex: 1; font-style: italic;
}
.testimonial-card__author {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  padding-top: 16px; border-top: 1px solid rgba(255,255,255,.1);
}
.t-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.testimonial-card__author strong { display: block; font-size: .86rem; color: #fff; }
.testimonial-card__author span   { font-size: .78rem; color: rgba(255,255,255,.5); }
.testimonial-card__metric { margin-left: auto; text-align: right; }
.metric-num {
  display: block; font-family: 'Fraunces', serif; font-size: 1.3rem;
  font-weight: 700; color: var(--teal);
}
.metric-label { font-size: .68rem; color: rgba(255,255,255,.45); font-weight: 600; text-transform: uppercase; }

/* ── FAQ ───────────────────────────────────────────────────────────────────── */
.faq { padding: 88px 5%; }
.faq__inner {
  display: grid; grid-template-columns: 1fr 1.6fr;
  gap: 64px; align-items: start;
}
.faq__right { display: flex; flex-direction: column; gap: 0; }
.faq-item { border-bottom: 1px solid var(--gray-200); }
.faq-item__q {
  width: 100%; display: flex; justify-content: space-between; align-items: center;
  background: none; border: none; padding: 18px 0;
  font-family: 'DM Sans', sans-serif; font-size: .94rem; font-weight: 700;
  color: var(--indigo); cursor: pointer; text-align: left; gap: 16px;
  transition: color var(--transition);
}
.faq-item__q:hover { color: var(--teal); }
.faq-item.open .faq-item__q { color: var(--teal); }
.faq-item__chevron { font-size: 1.3rem; font-weight: 400; flex-shrink: 0; color: var(--teal); }
.faq-item__a { font-size: .88rem; color: var(--gray-600); line-height: 1.75; padding-bottom: 18px; }

/* ── Final CTA ─────────────────────────────────────────────────────────────── */
.final-cta {
  background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dk) 100%);
  padding: 88px 5%; text-align: center;
}
.final-cta__inner { max-width: 640px; margin: 0 auto; }
.final-cta__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 700; color: #fff; margin-bottom: 16px;
}
.final-cta__sub {
  font-size: 1rem; color: rgba(255,255,255,.85); line-height: 1.7; margin-bottom: 36px;
}
.final-cta__actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.btn-white {
  background: #fff; color: var(--teal); border: none; border-radius: 50px;
  padding: 12px 28px; font-family: 'DM Sans', sans-serif; font-weight: 700;
  font-size: .9rem; cursor: pointer; transition: all var(--transition);
}
.btn-white:hover { background: var(--gray-50); }
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
  background: #fff; border-radius: 20px; width: 100%; max-width: 640px;
  max-height: 90vh; overflow-y: auto; position: relative; padding: 36px;
  box-shadow: 0 32px 80px rgba(0,0,0,.25);
}
.apply-modal--sm       { max-width: 480px; }
.apply-modal--register { max-width: 560px; }
.modal-close {
  position: absolute; top: 18px; right: 18px;
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--gray-100); border: none; color: var(--gray-600);
  font-size: .9rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: background var(--transition);
}
.modal-close:hover { background: var(--teal); color: #fff; }
.apply-modal__header { text-align: center; margin-bottom: 28px; }
.apply-modal__icon   { font-size: 2.4rem; margin-bottom: 10px; }
.apply-modal__title  {
  font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 6px;
}
.apply-modal__sub { font-size: .88rem; color: var(--gray-600); }

/* Contact form */
.apply-form { display: flex; flex-direction: column; gap: 18px; }
.form-group { display: flex; flex-direction: column; gap: 7px; }
.form-group label { font-size: .78rem; font-weight: 700; color: var(--indigo); }
.form-group input,
.form-group textarea {
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
  padding: 10px 14px; font-family: 'DM Sans', sans-serif;
  font-size: .9rem; color: var(--indigo); outline: none; background: #fff;
  transition: border-color var(--transition);
}
.form-group input:focus,
.form-group textarea:focus { border-color: var(--teal); }
.form-group textarea { resize: vertical; }

/* ── Animations ────────────────────────────────────────────────────────────── */
@keyframes card-in {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
  .pricing-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 1024px) {
  .categories-grid,
  .benefits-grid,
  .testimonials-grid { grid-template-columns: repeat(2, 1fr); }
  .hiw__inner,
  .dp__inner,
  .faq__inner,
  .calc__body { grid-template-columns: 1fr; gap: 40px; }
  .calc__result { max-width: 360px; margin: 0 auto; }
}
@media (max-width: 768px) {
  .hero__inner { padding: 60px 5%; }
  .categories-grid,
  .benefits-grid,
  .pricing-grid,
  .testimonials-grid { grid-template-columns: 1fr; }
  .trust-bar { flex-direction: column; align-items: flex-start; gap: 12px; }
  .hero__stats { gap: 24px; }
  .hero__actions { flex-direction: column; }
  .hero__actions .btn-lg,
  .hero__actions .btn-ghost { width: 100%; text-align: center; }
  .final-cta__actions { flex-direction: column; align-items: center; }
}
</style>