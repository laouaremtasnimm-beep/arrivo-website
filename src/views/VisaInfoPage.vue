<template>
  <div class="visa-page">
    <NavBar />

    <!-- ── Masthead ──────────────────────────────────────────────────────── -->
    <header class="masthead">
      <div class="masthead__rule" />
      <div class="masthead__inner">
        <div class="masthead__left">
          <p class="masthead__overline">Voyago Travel Tools</p>
          <h1 class="masthead__title">Visa<br><em>Information</em></h1>
          <p class="masthead__sub">
            Up-to-date visa requirements, entry rules, and processing guides for travellers worldwide — all in one place.
          </p>
          <div class="masthead__stats">
            <div class="masthead__stat">
              <span class="masthead__stat-num">195</span>
              <span class="masthead__stat-label">Countries</span>
            </div>
            <div class="masthead__stat-div" />
            <div class="masthead__stat">
              <span class="masthead__stat-num">12</span>
              <span class="masthead__stat-label">Visa Types</span>
            </div>
            <div class="masthead__stat-div" />
            <div class="masthead__stat">
              <span class="masthead__stat-num">24h</span>
              <span class="masthead__stat-label">Updated</span>
            </div>
          </div>
        </div>
        <div class="masthead__right">
          <!-- Quick checker card -->
          <div class="checker-card">
            <div class="checker-card__header">
              <span class="checker-card__icon">🔍</span>
              <div>
                <h3 class="checker-card__title">Visa Requirement Checker</h3>
                <p class="checker-card__sub">Find out what you need in seconds</p>
              </div>
            </div>
            <div class="checker-form">
              <div class="checker-field">
                <label class="checker-label">Your passport</label>
                <div class="checker-select-wrap">
                  <select class="checker-select" v-model="fromCountry">
                    <option value="">Select your country…</option>
                    <option v-for="c in passportCountries" :key="c.code" :value="c.code">{{ c.flag }} {{ c.name }}</option>
                  </select>
                </div>
              </div>
              <div class="checker-arrow">→</div>
              <div class="checker-field">
                <label class="checker-label">Destination</label>
                <div class="checker-select-wrap">
                  <select class="checker-select" v-model="toCountry">
                    <option value="">Select destination…</option>
                    <option v-for="c in destinationCountries" :key="c.code" :value="c.code">{{ c.flag }} {{ c.name }}</option>
                  </select>
                </div>
              </div>
            </div>
            <button class="btn btn-coral checker-btn" @click="runCheck" :disabled="!fromCountry || !toCountry">
              Check Requirements →
            </button>
            <!-- Result -->
            <Transition name="result-fade">
              <div v-if="checkResult" class="checker-result" :class="`checker-result--${checkResult.status}`">
                <div class="checker-result__badge">{{ checkResult.statusLabel }}</div>
                <p class="checker-result__desc">{{ checkResult.desc }}</p>
                <div class="checker-result__facts">
                  <div class="checker-fact" v-for="f in checkResult.facts" :key="f.label">
                    <span class="checker-fact__label">{{ f.label }}</span>
                    <span class="checker-fact__val">{{ f.value }}</span>
                  </div>
                </div>
                <button class="checker-result__cta" @click="scrollToGuides">View full guide →</button>
              </div>
            </Transition>
          </div>
        </div>
      </div>
      <div class="masthead__rule" />
    </header>

    <!-- ── Visa type pills ────────────────────────────────────────────────── -->
    <div class="visa-filters">
      <div class="filter-section">
        <span class="filter-section__label">Visa Type</span>
        <div class="filter-pills">
          <button
            v-for="t in visaTypes" :key="t"
            class="filter-pill"
            :class="{ active: activeType === t }"
            @click="activeType = t; page = 1"
          >{{ t }}</button>
        </div>
      </div>
      <div class="filter-section">
        <span class="filter-section__label">Region</span>
        <div class="filter-pills">
          <button
            v-for="r in regions" :key="r"
            class="filter-pill filter-pill--region"
            :class="{ active: activeRegion === r }"
            @click="activeRegion = r; page = 1"
          >{{ r }}</button>
        </div>
      </div>
      <div class="filter-search">
        <span class="search-icon-sm">🔍</span>
        <input
          class="filter-search__input"
          v-model="searchQuery"
          placeholder="Search country or visa type…"
          @input="page = 1"
        />
      </div>
    </div>

    <!-- ── Body ───────────────────────────────────────────────────────────── -->
    <div class="visa-body" ref="guidesRef">

      <!-- Status legend -->
      <div class="status-legend">
        <div class="legend-item" v-for="s in statusTypes" :key="s.key">
          <span class="legend-dot" :class="`legend-dot--${s.key}`"></span>
          <span class="legend-label">{{ s.label }}</span>
        </div>
      </div>

      <!-- Results bar -->
      <div class="results-bar">
        <p class="results-count">
          <strong>{{ filteredGuides.length }}</strong>
          guide{{ filteredGuides.length !== 1 ? 's' : '' }}
          <template v-if="activeType !== 'All'"> · <em>{{ activeType }}</em></template>
          <template v-if="activeRegion !== 'All Regions'"> · <em>{{ activeRegion }}</em></template>
        </p>
        <div class="results-sort">
          <label class="sort-lbl">Sort</label>
          <select class="sort-sel" v-model="sortBy">
            <option value="alpha">A → Z</option>
            <option value="processing">Fastest processing</option>
            <option value="cost">Lowest cost</option>
          </select>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="pagedGuides.length === 0" class="guides-empty">
        <div class="guides-empty__icon">📭</div>
        <h3 class="guides-empty__title">No results found</h3>
        <p class="guides-empty__sub">Try a different type, region, or country name.</p>
        <button class="btn btn-coral" @click="resetFilters">Clear filters</button>
      </div>

      <!-- ── Country visa guide grid ──────────────────────────────────────── -->
      <div v-else class="visa-grid">
        <div
          v-for="(guide, i) in pagedGuides"
          :key="guide.id"
          class="visa-card"
          :style="{ '--i': i }"
          @click="openGuide(guide)"
        >
          <!-- Flag header -->
          <div class="visa-card__hero" :style="{ background: guide.headerBg }">
            <span class="visa-card__flag">{{ guide.flag }}</span>
            <div class="visa-card__status-badge" :class="`status-badge--${guide.status}`">
              {{ guide.statusLabel }}
            </div>
          </div>

          <div class="visa-card__body">
            <div class="visa-card__meta">
              <span class="guide-tag">{{ guide.visaType }}</span>
              <span class="guide-region">{{ guide.region }}</span>
            </div>
            <h3 class="visa-card__country">{{ guide.country }}</h3>
            <p class="visa-card__desc">{{ guide.desc }}</p>

            <div class="visa-card__facts">
              <div class="visa-fact">
                <span class="visa-fact__icon">⏱</span>
                <div>
                  <span class="visa-fact__label">Processing</span>
                  <span class="visa-fact__val">{{ guide.processing }}</span>
                </div>
              </div>
              <div class="visa-fact">
                <span class="visa-fact__icon">💳</span>
                <div>
                  <span class="visa-fact__label">Fee</span>
                  <span class="visa-fact__val">{{ guide.fee }}</span>
                </div>
              </div>
              <div class="visa-fact">
                <span class="visa-fact__icon">📅</span>
                <div>
                  <span class="visa-fact__label">Stay</span>
                  <span class="visa-fact__val">{{ guide.maxStay }}</span>
                </div>
              </div>
            </div>

            <div class="visa-card__footer">
              <span class="visa-card__updated">Updated {{ guide.updated }}</span>
              <button class="visa-card__cta">View guide →</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="guides-pagination" v-if="totalPages > 1">
        <button class="page-btn" :disabled="page === 1" @click="page--">← Prev</button>
        <div class="page-numbers">
          <button
            v-for="p in totalPages" :key="p"
            class="page-num"
            :class="{ active: p === page }"
            @click="page = p"
          >{{ p }}</button>
        </div>
        <button class="page-btn" :disabled="page === totalPages" @click="page++">Next →</button>
      </div>

    </div>

    <!-- ── Tips section ───────────────────────────────────────────────────── -->
    <div class="tips-section">
      <div class="tips-section__inner">
        <div class="tips-header">
          <p class="tips-overline">Before You Apply</p>
          <h2 class="tips-title">Essential Visa Tips</h2>
        </div>
        <div class="tips-grid">
          <div class="tip-card" v-for="tip in essentialTips" :key="tip.id">
            <div class="tip-card__icon-wrap">
              <span class="tip-card__icon">{{ tip.icon }}</span>
            </div>
            <h4 class="tip-card__title">{{ tip.title }}</h4>
            <p class="tip-card__body">{{ tip.body }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Document checklist promo ───────────────────────────────────────── -->
    <div class="checklist-promo">
      <div class="checklist-promo__inner">
        <div class="checklist-promo__text">
          <span class="checklist-promo__badge">Free Tool</span>
          <h3 class="checklist-promo__title">Build Your Visa Document Checklist</h3>
          <p class="checklist-promo__sub">Answer three questions and get a personalised list of every document you'll need — formatted for printing or email.</p>
          <button class="btn btn-coral" @click="openChecklist">Generate my checklist →</button>
        </div>
        <div class="checklist-promo__visual">
          <div class="doc-preview">
            <div class="doc-preview__item" v-for="d in docPreview" :key="d" :class="{ 'doc-preview__item--checked': d.checked }">
              <span class="doc-check">{{ d.checked ? '✓' : '○' }}</span>
              <span>{{ d.label }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Visa guide detail modal ─────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="activeGuide" class="guide-modal-backdrop" @click.self="activeGuide = null">
          <div class="guide-modal">
            <button class="guide-modal__close" @click="activeGuide = null">✕</button>

            <!-- Hero -->
            <div class="guide-modal__hero" :style="{ background: activeGuide.headerBg }">
              <span class="guide-modal__flag">{{ activeGuide.flag }}</span>
              <div class="guide-modal__hero-body">
                <div class="guide-modal__status-badge" :class="`status-badge--${activeGuide.status}`">
                  {{ activeGuide.statusLabel }}
                </div>
                <h2 class="guide-modal__title">{{ activeGuide.country }}</h2>
                <p class="guide-modal__subtitle">{{ activeGuide.visaType }} · {{ activeGuide.region }}</p>
              </div>
            </div>

            <div class="guide-modal__content">

              <!-- Key facts strip -->
              <div class="modal-facts-strip">
                <div class="modal-fact" v-for="f in activeGuide.modalFacts" :key="f.label">
                  <span class="modal-fact__icon">{{ f.icon }}</span>
                  <span class="modal-fact__label">{{ f.label }}</span>
                  <span class="modal-fact__val">{{ f.value }}</span>
                </div>
              </div>

              <!-- Sections -->
              <p class="guide-modal__lead">{{ activeGuide.desc }}</p>

              <div v-for="(section, si) in activeGuide.sections" :key="si" class="guide-section">
                <h3 class="guide-section__title">{{ section.heading }}</h3>
                <p class="guide-section__body">{{ section.body }}</p>
              </div>

              <!-- Required documents -->
              <div class="modal-docs" v-if="activeGuide.documents?.length">
                <h4 class="modal-docs__heading">📋 Required Documents</h4>
                <ul class="modal-docs__list">
                  <li v-for="(doc, di) in activeGuide.documents" :key="di">
                    <span class="doc-required" :class="{ 'doc-required--optional': doc.optional }">
                      {{ doc.optional ? 'Optional' : 'Required' }}
                    </span>
                    {{ doc.name }}
                  </li>
                </ul>
              </div>

              <!-- Tips box -->
              <div class="guide-tips" v-if="activeGuide.tips?.length">
                <h4 class="guide-tips__heading">✦ Insider tips</h4>
                <ul class="guide-tips__list">
                  <li v-for="(tip, ti) in activeGuide.tips" :key="ti">{{ tip }}</li>
                </ul>
              </div>

              <!-- CTA -->
              <div class="guide-modal__cta">
                <RouterLink
                  :to="{ path: '/search', query: { q: activeGuide.country } }"
                  class="btn btn-coral"
                  @click="activeGuide = null"
                >
                  Find packages in {{ activeGuide.country }} →
                </RouterLink>
                <RouterLink to="/planner" class="btn btn-outline" @click="activeGuide = null">
                  Plan this trip
                </RouterLink>
              </div>

            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── Checklist modal ────────────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="checklistOpen" class="guide-modal-backdrop" @click.self="checklistOpen = false">
          <div class="guide-modal checklist-modal">
            <button class="guide-modal__close" @click="checklistOpen = false">✕</button>
            <div class="checklist-modal__header">
              <h2 class="checklist-modal__title">📋 Build Your Checklist</h2>
              <p class="checklist-modal__sub">We'll generate a personalised document list for your application.</p>
            </div>
            <div class="checklist-form" v-if="!checklistGenerated">
              <div class="checklist-field">
                <label class="checklist-label">What's your nationality?</label>
                <select class="checker-select" v-model="cl.nationality">
                  <option value="">Choose…</option>
                  <option v-for="c in passportCountries" :key="c.code" :value="c.name">{{ c.flag }} {{ c.name }}</option>
                </select>
              </div>
              <div class="checklist-field">
                <label class="checklist-label">Where are you going?</label>
                <select class="checker-select" v-model="cl.destination">
                  <option value="">Choose…</option>
                  <option v-for="c in destinationCountries" :key="c.code" :value="c.name">{{ c.flag }} {{ c.name }}</option>
                </select>
              </div>
              <div class="checklist-field">
                <label class="checklist-label">Purpose of visit</label>
                <div class="purpose-pills">
                  <button
                    v-for="p in purposes"
                    :key="p"
                    class="filter-pill"
                    :class="{ active: cl.purpose === p }"
                    @click="cl.purpose = p"
                  >{{ p }}</button>
                </div>
              </div>
              <button class="btn btn-coral" style="width:100%; margin-top:8px;" @click="generateChecklist" :disabled="!cl.nationality || !cl.destination || !cl.purpose">
                Generate checklist →
              </button>
            </div>
            <div class="checklist-result" v-else>
              <div class="checklist-result__header">
                <h3 class="checklist-result__title">Your checklist for {{ cl.destination }}</h3>
                <p class="checklist-result__sub">{{ cl.purpose }} visa · {{ cl.nationality }} passport</p>
              </div>
              <ul class="checklist-items">
                <li v-for="(item, i) in generatedChecklist" :key="i" class="checklist-item">
                  <label class="checklist-item__label">
                    <input type="checkbox" v-model="item.done" class="checklist-checkbox" />
                    <span :class="{ 'checklist-done': item.done }">{{ item.name }}</span>
                    <span v-if="item.note" class="checklist-note">{{ item.note }}</span>
                  </label>
                </li>
              </ul>
              <div class="checklist-actions">
                <button class="btn btn-outline" @click="checklistGenerated = false; cl = { nationality: '', destination: '', purpose: '' }">Start over</button>
                <button class="btn btn-coral" @click="printChecklist">🖨 Print checklist</button>
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

// ── Filter state ────────────────────────────────────────────────────────────
const visaTypes   = ['All', 'Visa-Free', 'Visa on Arrival', 'e-Visa', 'Sticker Visa', 'ETA Required']
const regions     = ['All Regions', 'Europe', 'Asia', 'Africa', 'Americas', 'Middle East', 'Oceania']
const activeType   = ref('All')
const activeRegion = ref('All Regions')
const searchQuery  = ref('')
const sortBy       = ref('alpha')
const page         = ref(1)
const perPage      = 12
const guidesRef    = ref(null)

// ── Status types legend ─────────────────────────────────────────────────────
const statusTypes = [
  { key: 'free',     label: 'Visa-Free'         },
  { key: 'arrival',  label: 'Visa on Arrival'   },
  { key: 'evisa',    label: 'e-Visa Available'  },
  { key: 'required', label: 'Visa Required'     },
]

// ── Passport checker ────────────────────────────────────────────────────────
const passportCountries = [
  { code: 'DZ', name: 'Algeria',        flag: '🇩🇿' },
  { code: 'US', name: 'United States',  flag: '🇺🇸' },
  { code: 'GB', name: 'United Kingdom', flag: '🇬🇧' },
  { code: 'FR', name: 'France',         flag: '🇫🇷' },
  { code: 'DE', name: 'Germany',        flag: '🇩🇪' },
  { code: 'JP', name: 'Japan',          flag: '🇯🇵' },
  { code: 'CN', name: 'China',          flag: '🇨🇳' },
  { code: 'IN', name: 'India',          flag: '🇮🇳' },
  { code: 'BR', name: 'Brazil',         flag: '🇧🇷' },
  { code: 'AU', name: 'Australia',      flag: '🇦🇺' },
  { code: 'CA', name: 'Canada',         flag: '🇨🇦' },
  { code: 'MA', name: 'Morocco',        flag: '🇲🇦' },
  { code: 'TN', name: 'Tunisia',        flag: '🇹🇳' },
  { code: 'ZA', name: 'South Africa',   flag: '🇿🇦' },
  { code: 'TR', name: 'Turkey',         flag: '🇹🇷' },
]
const destinationCountries = [
  { code: 'JP', name: 'Japan',          flag: '🇯🇵' },
  { code: 'TH', name: 'Thailand',       flag: '🇹🇭' },
  { code: 'ID', name: 'Indonesia',      flag: '🇮🇩' },
  { code: 'TR', name: 'Turkey',         flag: '🇹🇷' },
  { code: 'MA', name: 'Morocco',        flag: '🇲🇦' },
  { code: 'GR', name: 'Greece',         flag: '🇬🇷' },
  { code: 'PT', name: 'Portugal',       flag: '🇵🇹' },
  { code: 'US', name: 'United States',  flag: '🇺🇸' },
  { code: 'AE', name: 'UAE',            flag: '🇦🇪' },
  { code: 'EG', name: 'Egypt',          flag: '🇪🇬' },
  { code: 'TN', name: 'Tunisia',        flag: '🇹🇳' },
  { code: 'MV', name: 'Maldives',       flag: '🇲🇻' },
  { code: 'KE', name: 'Kenya',          flag: '🇰🇪' },
  { code: 'SG', name: 'Singapore',      flag: '🇸🇬' },
  { code: 'VN', name: 'Vietnam',        flag: '🇻🇳' },
]

const fromCountry  = ref('')
const toCountry    = ref('')
const checkResult  = ref(null)

// Mock checker logic
const checkDatabase = {
  'DZ-JP': { status: 'required', statusLabel: 'Visa Required', desc: 'Algerian passport holders must obtain a tourist visa before arrival. Apply at the Japanese Embassy in Algiers at least 4–6 weeks before travel.', facts: [{ label: 'Processing time', value: '5–10 business days' }, { label: 'Fee', value: '¥3,000 (~$20)' }, { label: 'Max stay', value: '15–90 days' }] },
  'DZ-TH': { status: 'free',     statusLabel: 'Visa-Free',     desc: 'Algerian citizens can enter Thailand visa-free for tourism stays of up to 30 days. No prior application needed.', facts: [{ label: 'Allowed stay', value: '30 days' }, { label: 'Fee', value: 'Free' }, { label: 'Extensions', value: 'Possible in-country' }] },
  'DZ-TR': { status: 'free',     statusLabel: 'Visa-Free',     desc: 'Algeria and Turkey have a bilateral agreement. Algerian citizens can visit Turkey without a visa for up to 90 days in any 180-day period.', facts: [{ label: 'Allowed stay', value: '90 days / 180 days' }, { label: 'Fee', value: 'Free' }, { label: 'Entry type', value: 'Multiple' }] },
  'DZ-AE': { status: 'evisa',    statusLabel: 'e-Visa',        desc: 'Algerian passport holders can apply for a UAE e-Visa online before travel. The process takes 3–5 business days and costs approximately $90 USD.', facts: [{ label: 'Processing', value: '3–5 business days' }, { label: 'Fee', value: '~$90 USD' }, { label: 'Stay', value: '30 days, extendable' }] },
  'DZ-GR': { status: 'required', statusLabel: 'Schengen Visa', desc: 'Algeria is not in the Schengen Area. A Schengen short-stay visa (Type C) is required to visit Greece. Apply at the Greek Embassy in Algiers.', facts: [{ label: 'Processing', value: '10–15 business days' }, { label: 'Fee', value: '€80' }, { label: 'Stay', value: '90 days / 180 days' }] },
  'US-JP': { status: 'free',     statusLabel: 'Visa-Free',     desc: 'US citizens can enter Japan visa-free under the Temporary Visitor status for up to 90 days.', facts: [{ label: 'Stay', value: '90 days' }, { label: 'Fee', value: 'Free' }, { label: 'Purpose', value: 'Tourism / Business' }] },
  'GB-TH': { status: 'free',     statusLabel: 'Visa-Free',     desc: 'UK passport holders can enter Thailand visa-free for up to 60 days on the tourist exemption.', facts: [{ label: 'Stay', value: '60 days' }, { label: 'Fee', value: 'Free' }, { label: 'Extendable', value: 'Yes, +30 days' }] },
  'IN-TH': { status: 'free',     statusLabel: 'Visa-Free',     desc: 'Indian passport holders can visit Thailand visa-free for up to 30 days under the mutual exemption agreement.', facts: [{ label: 'Stay', value: '30 days' }, { label: 'Fee', value: 'Free' }, { label: 'Extensions', value: 'Possible in-country' }] },
  'IN-AE': { status: 'arrival',  statusLabel: 'Visa on Arrival', desc: 'Indian citizens can obtain a Visa on Arrival at UAE airports, valid for 14 days. An e-Visa is also available for more flexibility.', facts: [{ label: 'Stay', value: '14 days' }, { label: 'Fee', value: '~$75 USD' }, { label: 'Where', value: 'UAE airports' }] },
}

function runCheck() {
  const key = `${fromCountry.value}-${toCountry.value}`
  if (checkDatabase[key]) {
    checkResult.value = checkDatabase[key]
  } else {
    // Generic fallback
    checkResult.value = {
      status: 'required',
      statusLabel: 'Check Embassy',
      // Fixed using backticks (template literals)
      desc: `We don't have specific data for this combination yet. We recommend checking the official embassy website or contacting the destination country's consulate directly.`,
      facts: [
        { label: 'Best source', value: 'Official embassy' }, 
        { label: 'Lead time', value: 'Allow 4+ weeks' }
      ]
    }
  }
}

function scrollToGuides() {
  guidesRef.value?.scrollIntoView({ behavior: 'smooth' })
}

// ── Visa country guides data ────────────────────────────────────────────────
const guides = ref([
  {
    id: 1, country: 'Japan', flag: '🇯🇵', region: 'Asia', visaType: 'Visa-Free',
    status: 'free', statusLabel: 'Visa-Free',
    headerBg: 'linear-gradient(135deg, #bc002d18, #fff0f3)',
    processing: 'No visa', fee: 'Free', maxStay: '90 days', updated: 'Apr 2025',
    desc: 'Most Western passport holders can enter Japan visa-free for up to 90 days. The country has visa exemption agreements with over 70 nations.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: 'No application' },
      { icon: '💳', label: 'Fee', value: 'Free' },
      { icon: '📅', label: 'Max stay', value: '90 days' },
      { icon: '🔁', label: 'Extensions', value: 'Not permitted' },
      { icon: '✈️', label: 'Entry', value: 'Multiple' },
    ],
    sections: [
      { heading: 'Who qualifies for visa-free entry', body: 'Citizens of the US, UK, EU countries, Canada, Australia, New Zealand, and many others can enter without a visa. Algerian, Moroccan and many African passport holders still require a visa — check the Japanese Embassy website for the full list.' },
      { heading: 'Important conditions', body: 'You must have a return or onward ticket and sufficient funds. Immigration officers may ask to see your hotel bookings. Do not work or engage in any paid activity on a visa-exempt entry — this is illegal and can result in deportation and a ban.' },
      { heading: 'Visa-on-demand for others', body: 'If your country is not on the exemption list, a tourist visa (Type C) can be obtained from the Japanese Embassy in your country. It typically takes 5–10 business days and costs around ¥3,000.' },
    ],
    documents: [
      { name: 'Valid passport (6+ months)', optional: false },
      { name: 'Return or onward ticket', optional: false },
      { name: 'Proof of accommodation', optional: false },
      { name: 'Proof of sufficient funds', optional: true },
    ],
    tips: ['Cherry blossom season (late March–April) is peak: expect extra immigration queues', 'IC card (Suica/Pasmo) can be loaded at the airport on arrival', 'Japan is extremely safe; a lost wallet is usually returned'],
  },
  {
    id: 2, country: 'Thailand', flag: '🇹🇭', region: 'Asia', visaType: 'Visa-Free',
    status: 'free', statusLabel: 'Visa-Free',
    headerBg: 'linear-gradient(135deg, #a6000018, #fff8f0)',
    processing: 'No visa', fee: 'Free', maxStay: '60 days', updated: 'Apr 2025',
    desc: 'Thailand offers one of Southeast Asia\'s most generous visa-free policies. Most passport holders get 30–60 days on arrival depending on nationality.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: 'Instant on arrival' },
      { icon: '💳', label: 'Fee', value: 'Free' },
      { icon: '📅', label: 'Max stay', value: '30–60 days' },
      { icon: '🔁', label: 'Extensions', value: '+30 days possible' },
      { icon: '✈️', label: 'Entry', value: 'Multiple' },
    ],
    sections: [
      { heading: 'Recent policy update (2024)', body: 'Thailand extended its visa-free period from 30 to 60 days for citizens of many countries including the UK, EU, and the US. India was also added to the mutual exemption list in late 2023. Always confirm the current limit for your passport.' },
      { heading: 'Extending your stay', body: 'A single 30-day extension can be obtained at any immigration office in Thailand for ฿1,900 (~$53). For longer stays, look into the Tourist Visa (TR) obtained before arrival (60 days + 30 extension = 90 days) or the new Long-Term Resident visa.' },
    ],
    documents: [
      { name: 'Valid passport (6+ months)', optional: false },
      { name: 'Onward/return ticket', optional: false },
      { name: 'Immigration arrival card (TM.6)', optional: false },
    ],
    tips: ['Don\'t overstay — Thai immigration is strict and bans are common', 'Carry 20,000 THB cash or equivalent; immigration sometimes checks', 'Chiang Mai immigration is less busy than Bangkok for extensions'],
  },
  {
    id: 3, country: 'UAE', flag: '🇦🇪', region: 'Middle East', visaType: 'Visa on Arrival',
    status: 'arrival', statusLabel: 'Visa on Arrival',
    headerBg: 'linear-gradient(135deg, #00732f18, #f0fff4)',
    processing: 'On arrival', fee: '$35–$90', maxStay: '30–90 days', updated: 'Mar 2025',
    desc: 'The UAE offers visa on arrival to citizens of over 50 countries at Dubai, Abu Dhabi and Sharjah airports. E-visa is also available in advance.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: 'Instant on arrival' },
      { icon: '💳', label: 'Fee', value: '$35–$90 USD' },
      { icon: '📅', label: 'Max stay', value: '30 or 90 days' },
      { icon: '🔁', label: 'Extensions', value: 'Online, 30 days' },
      { icon: '✈️', label: 'Entry', value: 'Single or multiple' },
    ],
    sections: [
      { heading: 'Countries eligible for visa on arrival', body: 'Citizens of the US, UK, EU, Canada, Australia, Japan, South Korea, and several others receive a free 90-day visa on arrival. India and many other nations receive a 30-day VoA for a fee. Algerian and many African passport holders require an advance e-visa or embassy visa.' },
      { heading: 'e-Visa alternative', body: 'The ICP Smart Services platform (icp.gov.ae) allows travellers who aren\'t eligible for VoA to apply for a tourist e-Visa in advance. Processing takes 3–5 business days. Most people are approved. Cost is approximately AED 350 ($95 USD).' },
    ],
    documents: [
      { name: 'Valid passport (6+ months)', optional: false },
      { name: 'Return ticket', optional: false },
      { name: 'Hotel booking or host invitation', optional: false },
      { name: 'Travel insurance (recommended)', optional: true },
    ],
    tips: ['Ramadan requires modest dress and no public eating/drinking during daylight', 'Dubai airport has a free 48-hour transit visa if you\'re connecting', 'Alcohol is legal in Dubai and Abu Dhabi but not in Sharjah'],
  },
  {
    id: 4, country: 'Indonesia (Bali)', flag: '🇮🇩', region: 'Asia', visaType: 'Visa on Arrival',
    status: 'arrival', statusLabel: 'Visa on Arrival',
    headerBg: 'linear-gradient(135deg, #ce110018, #fff5f0)',
    processing: 'On arrival', fee: '$35 USD', maxStay: '30 days', updated: 'Feb 2025',
    desc: 'Indonesia introduced a Social Cultural Visa on Arrival for citizens of 95+ countries. Available at major airports including Ngurah Rai (Bali) and Soekarno-Hatta (Jakarta).',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: '10–30 min on arrival' },
      { icon: '💳', label: 'Fee', value: 'IDR 500,000 (~$35)' },
      { icon: '📅', label: 'Max stay', value: '30 days' },
      { icon: '🔁', label: 'Extensions', value: '+30 days in-country' },
      { icon: '✈️', label: 'Entry', value: 'Single' },
    ],
    sections: [
      { heading: 'The Bali tourist levy', body: 'Since February 2024, all foreign tourists entering Bali must pay a one-time levy of IDR 150,000 (~$10 USD) via the Love Bali platform. This is separate from the VoA fee. You can pay online before arrival or at the airport.' },
      { heading: 'Extending your stay', body: 'A single 30-day extension can be arranged at an immigration office in Bali for approximately $35 USD. Many agents in tourist areas also assist with extensions for a small additional fee.' },
    ],
    documents: [
      { name: 'Valid passport (6+ months)', optional: false },
      { name: 'Return or onward ticket', optional: false },
      { name: 'Proof of sufficient funds ($1,000 or equivalent)', optional: false },
      { name: 'Bali tourism levy payment receipt', optional: false },
    ],
    tips: ['Pay the Bali levy online before you arrive — saves queue time', 'Arrival queues at peak hours can be 45+ min; fast-track services exist', 'Dress conservatively when visiting temples — sarongs are provided'],
  },
  {
    id: 5, country: 'Turkey', flag: '🇹🇷', region: 'Middle East', visaType: 'e-Visa',
    status: 'evisa', statusLabel: 'e-Visa Available',
    headerBg: 'linear-gradient(135deg, #e3001918, #fff5f5)',
    processing: '24–72 hours', fee: '$50 USD', maxStay: '90 days', updated: 'Apr 2025',
    desc: 'Turkey has one of the world\'s most popular e-visa systems. Most nationalities can apply online in minutes and receive approval within hours.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: '24–72 hours' },
      { icon: '💳', label: 'Fee', value: '$50 USD' },
      { icon: '📅', label: 'Max stay', value: '90 days / 180 days' },
      { icon: '🔁', label: 'Extensions', value: 'Via e-Visa renewal' },
      { icon: '✈️', label: 'Entry', value: 'Multiple' },
    ],
    sections: [
      { heading: 'Applying for the Turkish e-Visa', body: 'Apply at evisa.gov.tr — the official portal. You\'ll need a valid passport, email address, and a credit/debit card. The process takes about 5 minutes. Most applications are approved instantly or within 24–72 hours. Print or download the e-visa to your phone.' },
      { heading: 'Algerian and North African passports', body: 'Algerian, Moroccan and Tunisian citizens benefit from a bilateral visa exemption agreement with Turkey and do NOT need an e-Visa. They can enter Turkey visa-free for up to 90 days. This is one of the few Schengen-adjacent countries with a blanket exemption for North Africans.' },
    ],
    documents: [
      { name: 'Valid passport (minimum 60 days beyond stay)', optional: false },
      { name: 'Email address for delivery', optional: false },
      { name: 'Credit or debit card for fee payment', optional: false },
    ],
    tips: ['Always apply through evisa.gov.tr — avoid third-party sites that charge extra', 'The e-Visa works at all land, sea and air borders', 'Istanbul has a spectacular airport — allow 2+ hours for connections'],
  },
  {
    id: 6, country: 'Morocco', flag: '🇲🇦', region: 'Africa', visaType: 'Visa-Free',
    status: 'free', statusLabel: 'Visa-Free',
    headerBg: 'linear-gradient(135deg, #c1272d18, #fff8f0)',
    processing: 'No visa', fee: 'Free', maxStay: '90 days', updated: 'Mar 2025',
    desc: 'Morocco is visa-free for citizens of over 65 countries including the entire EU, US, UK, Canada, Japan and most of the Arab world and Africa.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: 'Instant on arrival' },
      { icon: '💳', label: 'Fee', value: 'Free' },
      { icon: '📅', label: 'Max stay', value: '90 days' },
      { icon: '🔁', label: 'Extensions', value: 'Possible at police HQ' },
      { icon: '✈️', label: 'Entry', value: 'Multiple' },
    ],
    sections: [
      { heading: 'Entering Morocco', body: 'On arrival, present your passport and fill in an arrival card. You may be asked about your accommodation and purpose of visit — have hotel bookings ready. There is no fee. Most queues move quickly at Marrakech, Casablanca and Fes airports.' },
      { heading: 'For nationalities requiring a visa', body: 'Some passport holders require a visa, which must be obtained from the Moroccan Embassy in your country. The process typically takes 2–4 weeks. Required documents include a bank statement, hotel bookings, return ticket, and two passport photos.' },
    ],
    documents: [
      { name: 'Valid passport (3+ months)', optional: false },
      { name: 'Arrival form (provided on plane or at border)', optional: false },
      { name: 'Proof of accommodation', optional: true },
    ],
    tips: ['Dirham (MAD) cannot be taken out of Morocco — spend or exchange at the airport', 'Haggling is expected in the souks; first price is usually 3x the real price', 'The Marrakech to Merzouga drive is 8+ hours — consider overnight buses'],
  },
  {
    id: 7, country: 'Greece', flag: '🇬🇷', region: 'Europe', visaType: 'Sticker Visa',
    status: 'required', statusLabel: 'Schengen Visa',
    headerBg: 'linear-gradient(135deg, #003f8718, #f0f5ff)',
    processing: '10–15 days', fee: '€80', maxStay: '90 days', updated: 'Mar 2025',
    desc: 'Greece is part of the Schengen Area. Non-EU travellers from countries without a Schengen exemption must apply for a Type C short-stay visa before travel.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: '10–15 business days' },
      { icon: '💳', label: 'Fee', value: '€80 adults / €40 children' },
      { icon: '📅', label: 'Max stay', value: '90 days / 180 days' },
      { icon: '🔁', label: 'Extensions', value: 'Not available' },
      { icon: '✈️', label: 'Entry', value: 'Single or double' },
    ],
    sections: [
      { heading: 'The Schengen Area explained', body: 'A Greek Schengen visa lets you travel freely within the 27-country Schengen zone for up to 90 days in any 180-day period. You do not need separate visas for France, Spain, Italy etc. as long as Greece is your main destination.' },
      { heading: 'The application process', body: 'Apply at the Greek Embassy or Consulate General in your country, or via an authorised visa application centre (VFS Global). The earliest you can apply is 6 months before travel; apply at least 4–6 weeks in advance. Book an appointment as slots fill quickly.' },
    ],
    documents: [
      { name: 'Visa application form (signed)', optional: false },
      { name: 'Valid passport + old passports', optional: false },
      { name: 'Biometric passport photos (2x)', optional: false },
      { name: '3-month bank statement', optional: false },
      { name: 'Proof of accommodation (all nights)', optional: false },
      { name: 'Return flight tickets', optional: false },
      { name: 'Travel insurance (€30,000 cover)', optional: false },
      { name: 'Cover letter', optional: true },
    ],
    tips: ['Apply as early as possible — Greece is one of the most popular Schengen destinations', 'Travel insurance is mandatory and must cover the entire Schengen stay', 'A first-time applicant must appear in person; fingerprints required'],
  },
  {
    id: 8, country: 'Vietnam', flag: '🇻🇳', region: 'Asia', visaType: 'e-Visa',
    status: 'evisa', statusLabel: 'e-Visa Available',
    headerBg: 'linear-gradient(135deg, #da251d18, #fff5f5)',
    processing: '3 business days', fee: '$25 USD', maxStay: '90 days', updated: 'Feb 2025',
    desc: 'Vietnam launched an expanded e-Visa programme in 2023, offering 90-day multi-entry visas to citizens of all countries. The process is simple and entirely online.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: '3 business days' },
      { icon: '💳', label: 'Fee', value: '$25 USD' },
      { icon: '📅', label: 'Max stay', value: '90 days' },
      { icon: '🔁', label: 'Extensions', value: 'Apply for new e-Visa' },
      { icon: '✈️', label: 'Entry', value: 'Multiple' },
    ],
    sections: [
      { heading: 'Applying online', body: 'Go to evisa.xuatnhapcanh.gov.vn — the official portal. You\'ll need a passport-style photo, a scan of your passport data page, a credit card, and your planned itinerary. Approval usually comes within 3 business days. Download and print the approved e-Visa.' },
    ],
    documents: [
      { name: 'Valid passport (6+ months)', optional: false },
      { name: 'Passport-sized photo (digital)', optional: false },
      { name: 'Passport scan (data page)', optional: false },
      { name: 'Intended arrival and departure dates', optional: false },
    ],
    tips: ['Only use the official government site — many scam portals charge $80+ for the same visa', 'Print a hard copy of your e-Visa; some airlines ask to see it at check-in', 'Hoi An and Ha Long Bay have entry fees separate from visa'],
  },
  {
    id: 9, country: 'Portugal', flag: '🇵🇹', region: 'Europe', visaType: 'Sticker Visa',
    status: 'required', statusLabel: 'Schengen Visa',
    headerBg: 'linear-gradient(135deg, #00600018, #f0fff5)',
    processing: '10–15 days', fee: '€80', maxStay: '90 days', updated: 'Mar 2025',
    desc: 'Portugal is a Schengen member. Non-exempt nationalities must apply for a Schengen visa at the Portuguese Embassy or via the VFS Global visa centre.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: '10–15 business days' },
      { icon: '💳', label: 'Fee', value: '€80' },
      { icon: '📅', label: 'Max stay', value: '90 days / 180 days' },
      { icon: '🔁', label: 'Extensions', value: 'Not available' },
      { icon: '✈️', label: 'Entry', value: 'Single, double or multiple' },
    ],
    sections: [
      { heading: 'Why Portugal as lead country', body: 'If Portugal is your main destination or first entry point into Schengen, you apply to the Portuguese Embassy. If you\'re travelling through multiple Schengen countries, apply to the embassy of the country where you\'ll spend the most nights.' },
    ],
    documents: [
      { name: 'Schengen application form', optional: false },
      { name: 'Valid passport + copies', optional: false },
      { name: 'Biometric photos (2x)', optional: false },
      { name: 'Bank statement (3 months)', optional: false },
      { name: 'Hotel bookings', optional: false },
      { name: 'Return flight ticket', optional: false },
      { name: 'Travel insurance (€30,000 cover, whole Schengen period)', optional: false },
    ],
    tips: ['Lisbon and Porto are best in spring (March–May) and autumn (September–October)', 'Portugal is the most affordable Western European Schengen country', 'The D7 Passive Income visa is available for remote workers staying longer'],
  },
  {
    id: 10, country: 'Kenya', flag: '🇰🇪', region: 'Africa', visaType: 'e-Visa',
    status: 'evisa', statusLabel: 'e-Visa Available',
    headerBg: 'linear-gradient(135deg, #00600018, #fff5f0)',
    processing: '3–5 days', fee: '$50 USD', maxStay: '90 days', updated: 'Jan 2025',
    desc: 'Kenya replaced its visa on arrival system with a fully online e-Visa system in 2023. All nationalities must apply online before travel to Kenya.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: '3–5 business days' },
      { icon: '💳', label: 'Fee', value: '$50 USD' },
      { icon: '📅', label: 'Max stay', value: '90 days' },
      { icon: '🔁', label: 'Extensions', value: 'Possible in Kenya' },
      { icon: '✈️', label: 'Entry', value: 'Single' },
    ],
    sections: [
      { heading: 'Applying for the Kenya e-Visa', body: 'Visit evisa.go.ke — the only official portal. You\'ll need a colour passport photo, a passport scan, proof of accommodation and onward travel, and a card for the $50 payment. Processing takes 3–5 business days.' },
    ],
    documents: [
      { name: 'Valid passport (6+ months)', optional: false },
      { name: 'Passport-sized photo (digital, colour)', optional: false },
      { name: 'Proof of accommodation', optional: false },
      { name: 'Return or onward ticket', optional: false },
      { name: 'Yellow fever certificate (if coming from endemic countries)', optional: true },
    ],
    tips: ['Yellow fever vaccine required if entering from some African countries', 'Nairobi airport connects directly to the Maasai Mara — budget a 5–6 hour drive', 'Peak safari season (July–October) books out months ahead'],
  },
  {
    id: 11, country: 'Maldives', flag: '🇲🇻', region: 'Asia', visaType: 'Visa-Free',
    status: 'free', statusLabel: 'Visa-Free',
    headerBg: 'linear-gradient(135deg, #00737b18, #f0fbff)',
    processing: 'No visa', fee: 'Free', maxStay: '30 days', updated: 'Apr 2025',
    desc: 'The Maldives grants a free 30-day visa on arrival to all nationalities. No advance application required — just arrive with a confirmed resort booking and return ticket.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: 'Instant on arrival' },
      { icon: '💳', label: 'Fee', value: 'Free' },
      { icon: '📅', label: 'Max stay', value: '30 days' },
      { icon: '🔁', label: 'Extensions', value: '+60 days (total 90)' },
      { icon: '✈️', label: 'Entry', value: 'Multiple' },
    ],
    sections: [
      { heading: 'What to bring on arrival', body: 'You must have a confirmed accommodation booking (typically a resort, guesthouse or liveaboard boat), a return ticket, and proof of sufficient funds ($100/day is the unofficial threshold). Departure tax is included in most flight tickets.' },
    ],
    documents: [
      { name: 'Valid passport (6+ months)', optional: false },
      { name: 'Confirmed accommodation booking', optional: false },
      { name: 'Return or onward ticket', optional: false },
      { name: 'Proof of sufficient funds', optional: false },
    ],
    tips: ['Budget islands (local guesthouses) in Maafushi are 80% cheaper than resort atolls', 'Transfer speedboats between islands can be expensive — book in advance', 'The Maldives is an all-Muslim country; public islands have alcohol restrictions'],
  },
  {
    id: 12, country: 'Singapore', flag: '🇸🇬', region: 'Asia', visaType: 'Visa-Free',
    status: 'free', statusLabel: 'Visa-Free',
    headerBg: 'linear-gradient(135deg, #ef000018, #fff5f8)',
    processing: 'No visa', fee: 'Free', maxStay: '30–90 days', updated: 'Apr 2025',
    desc: 'Singapore offers visa-free entry to citizens of over 160 countries. It\'s one of the most open entry policies in the world, reflecting its status as a global hub.',
    modalFacts: [
      { icon: '⏱', label: 'Processing', value: 'Instant on arrival' },
      { icon: '💳', label: 'Fee', value: 'Free' },
      { icon: '📅', label: 'Max stay', value: '30 or 90 days' },
      { icon: '🔁', label: 'Extensions', value: 'Possible at ICA' },
      { icon: '✈️', label: 'Entry', value: 'Multiple' },
    ],
    sections: [
      { heading: 'Entry conditions', body: 'Singapore has strict entry conditions despite no visa requirement. You must have a return or onward ticket, sufficient funds, and accommodation confirmed. Immigration officers can and do ask for proof. Singapore has zero tolerance for drug offences — do not carry any controlled substances.' },
    ],
    documents: [
      { name: 'Valid passport (6+ months)', optional: false },
      { name: 'Return or onward ticket', optional: false },
      { name: 'Proof of accommodation', optional: false },
    ],
    tips: ['Changi Airport is consistently world-ranked #1 — it\'s a destination itself', 'Singapore is extremely expensive — budget at least $150/day', 'Fines for littering, jaywalking and chewing gum are real and enforced'],
  },
])

// ── Computed ─────────────────────────────────────────────────────────────────
const filteredGuides = computed(() => {
  let list = [...guides.value]
  if (activeType.value !== 'All')         list = list.filter(g => g.visaType === activeType.value)
  if (activeRegion.value !== 'All Regions') list = list.filter(g => g.region === activeRegion.value)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(g =>
      g.country.toLowerCase().includes(q) ||
      g.visaType.toLowerCase().includes(q) ||
      g.region.toLowerCase().includes(q) ||
      g.desc.toLowerCase().includes(q)
    )
  }
  if (sortBy.value === 'alpha')      list = [...list].sort((a, b) => a.country.localeCompare(b.country))
  if (sortBy.value === 'processing') list = [...list].sort((a, b) => {
    const order = { 'free': 0, 'arrival': 1, 'evisa': 2, 'required': 3 }
    return order[a.status] - order[b.status]
  })
  if (sortBy.value === 'cost') list = [...list].sort((a, b) => {
    const parse = s => s === 'Free' ? 0 : parseFloat(s.replace(/[^0-9.]/g, '')) || 999
    return parse(a.fee) - parse(b.fee)
  })
  return list
})

const totalPages  = computed(() => Math.max(1, Math.ceil(filteredGuides.value.length / perPage)))
const pagedGuides = computed(() => {
  const s = (page.value - 1) * perPage
  return filteredGuides.value.slice(s, s + perPage)
})

function resetFilters() {
  activeType.value   = 'All'
  activeRegion.value = 'All Regions'
  searchQuery.value  = ''
  page.value         = 1
}

// ── Guide modal ───────────────────────────────────────────────────────────────
const activeGuide = ref(null)
function openGuide(guide) { activeGuide.value = guide }

// ── Essential tips data ───────────────────────────────────────────────────────
const essentialTips = [
  { id: 1, icon: '📆', title: 'Apply early', body: 'Embassy processing times can exceed 4 weeks during peak season. Apply at least 6–8 weeks before your travel date for sticker visas.' },
  { id: 2, icon: '🛡️', title: 'Travel insurance is mandatory', body: 'Schengen visas require a minimum €30,000 medical cover for the entire trip. Many other countries also require proof of insurance.' },
  { id: 3, icon: '💰', title: 'Prove sufficient funds', body: 'Most countries expect approximately $50–100 USD per day of your stay. Bank statements (3 months) are the most commonly accepted proof.' },
  { id: 4, icon: '✈️', title: 'Book refundable flights', body: 'Some embassies require confirmed return tickets before issuing a visa. Book flexible fare tickets to avoid losses if your visa is denied.' },
  { id: 5, icon: '📸', title: 'Biometric photo specs', body: 'Requirements differ by country. Schengen visas need 35×45mm photos against a white background with neutral expression and no glasses.' },
  { id: 6, icon: '🔁', title: 'Check validity vs. duration', body: 'A visa valid for 6 months does not mean you can stay 6 months — that\'s the window in which to travel. The permitted stay is separate (often 30–90 days).' },
]

// ── Checklist tool ────────────────────────────────────────────────────────────
const checklistOpen      = ref(false)
const checklistGenerated = ref(false)
const purposes = ['Tourism', 'Business', 'Family Visit', 'Study', 'Transit']
const cl = ref({ nationality: '', destination: '', purpose: '' })
const generatedChecklist = ref([])

const baseChecklist = [
  { name: 'Valid passport (minimum 6 months validity)', note: 'Check exact requirement for your destination', done: false },
  { name: 'Completed visa application form', note: null, done: false },
  { name: 'Passport-sized photos (2x)', note: '35×45mm, white background, last 6 months', done: false },
  { name: 'Return or onward flight ticket', note: 'Booked and confirmed', done: false },
  { name: 'Proof of accommodation', note: 'Hotel bookings or host letter', done: false },
  { name: 'Bank statement (last 3 months)', note: 'Must show sufficient funds', done: false },
  { name: 'Travel insurance certificate', note: 'Minimum €30,000 / $50,000 cover', done: false },
]
const businessExtras = [
  { name: 'Invitation letter from host company', note: 'On official letterhead', done: false },
  { name: 'Employment letter / proof of employment', note: null, done: false },
  { name: 'Business registration documents', note: 'For self-employed applicants', done: false },
]
const studyExtras = [
  { name: 'University acceptance letter', note: 'Original or certified copy', done: false },
  { name: 'Proof of tuition payment', note: null, done: false },
  { name: 'Student accommodation letter', note: null, done: false },
]

function generateChecklist() {
  let list = baseChecklist.map(i => ({ ...i }))
  if (cl.value.purpose === 'Business') list = [...list, ...businessExtras.map(i => ({ ...i }))]
  if (cl.value.purpose === 'Study')    list = [...list, ...studyExtras.map(i => ({ ...i }))]
  generatedChecklist.value = list
  checklistGenerated.value = true
}
function openChecklist() { checklistOpen.value = true }
function printChecklist() { window.print() }

// ── Doc preview animation ─────────────────────────────────────────────────────
const docPreview = [
  { label: 'Valid passport', checked: true },
  { label: 'Return flight ticket', checked: true },
  { label: 'Travel insurance', checked: true },
  { label: 'Bank statement', checked: false },
  { label: 'Hotel bookings', checked: false },
]
</script>

<style scoped>
/* ── Page ──────────────────────────────────────────────────────────────────── */
.visa-page {
  min-height: 100vh;
  background: #faf8f3;
  padding-top: 68px;
  font-family: 'DM Sans', sans-serif;
}

/* ── Masthead ──────────────────────────────────────────────────────────────── */
.masthead { padding: 0 5%; background: #faf8f3; }
.masthead__rule { height: 2px; background: var(--indigo); margin: 0; }
.masthead__inner {
  display: grid; grid-template-columns: 1fr 1.4fr;
  gap: 64px; align-items: center; padding: 52px 0 48px;
}
.masthead__overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--teal); margin-bottom: 16px;
}
.masthead__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(3rem, 6vw, 5.5rem);
  font-weight: 700; line-height: .92;
  color: var(--indigo); margin-bottom: 20px;
}
.masthead__title em { color: var(--teal); font-style: italic; }
.masthead__sub {
  font-size: 1rem; color: #6b6655; line-height: 1.7;
  max-width: 360px; margin-bottom: 32px;
}
.masthead__stats { display: flex; align-items: center; gap: 24px; }
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

/* ── Checker card ─────────────────────────────────────────────────────────── */
.checker-card {
  background: var(--white); border-radius: 20px;
  box-shadow: 0 20px 60px rgba(45,49,66,.14);
  padding: 28px 28px 24px;
}
.checker-card__header {
  display: flex; align-items: center; gap: 14px; margin-bottom: 22px;
}
.checker-card__icon { font-size: 1.6rem; }
.checker-card__title {
  font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700;
  color: var(--indigo); margin: 0;
}
.checker-card__sub { font-size: .8rem; color: #a09880; margin: 2px 0 0; }

.checker-form {
  display: grid; grid-template-columns: 1fr auto 1fr;
  align-items: end; gap: 12px; margin-bottom: 16px;
}
.checker-field { display: flex; flex-direction: column; gap: 6px; }
.checker-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #a09880; }
.checker-select-wrap { position: relative; }
.checker-select {
  width: 100%; padding: 10px 14px; border-radius: 10px;
  border: 1.5px solid var(--gray-200); background: var(--gray-50);
  font-family: 'DM Sans', sans-serif; font-size: .86rem; color: var(--indigo);
  outline: none; cursor: pointer; transition: border-color var(--transition);
  appearance: none;
}
.checker-select:focus { border-color: var(--teal); }
.checker-arrow {
  font-size: 1.3rem; color: var(--teal); font-weight: 700;
  padding-bottom: 8px; align-self: end;
}
.checker-btn { width: 100%; border-radius: 10px; padding: 13px; font-size: .9rem; }
.checker-btn:disabled { opacity: .45; cursor: not-allowed; }

/* Checker result */
.checker-result {
  margin-top: 16px; border-radius: 12px; padding: 16px 18px;
  border-left: 4px solid;
}
.checker-result--free     { background: rgba(46,196,182,.08); border-color: var(--teal); }
.checker-result--arrival  { background: rgba(255,167,0,.08);  border-color: #f59e0b; }
.checker-result--evisa    { background: rgba(99,102,241,.08); border-color: #6366f1; }
.checker-result--required { background: rgba(255,90,95,.08);  border-color: var(--coral); }

.checker-result__badge {
  display: inline-block; font-size: .68rem; font-weight: 700;
  letter-spacing: .06em; text-transform: uppercase;
  padding: 3px 10px; border-radius: 50px; margin-bottom: 8px;
  background: rgba(255,255,255,.6); color: var(--indigo);
}
.checker-result__desc { font-size: .84rem; color: var(--indigo); line-height: 1.6; margin-bottom: 12px; }
.checker-result__facts { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 12px; }
.checker-fact { display: flex; flex-direction: column; }
.checker-fact__label { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: #a09880; font-weight: 700; }
.checker-fact__val { font-size: .84rem; font-weight: 700; color: var(--indigo); }
.checker-result__cta {
  font-size: .8rem; font-weight: 700; color: var(--coral); background: none;
  border: none; cursor: pointer; padding: 0; text-decoration: underline;
  text-underline-offset: 3px;
}

.result-fade-enter-active, .result-fade-leave-active { transition: all .3s ease; }
.result-fade-enter-from, .result-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ── Filters ──────────────────────────────────────────────────────────────── */
.visa-filters {
  background: var(--white);
  border-top: 1px solid var(--gray-200); border-bottom: 1px solid var(--gray-200);
  padding: 18px 5%;
  display: flex; align-items: center; gap: 28px; flex-wrap: wrap;
}
.filter-section { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.filter-section__label {
  font-size: .7rem; font-weight: 700; letter-spacing: .1em;
  text-transform: uppercase; color: #a09880; white-space: nowrap;
}
.filter-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.filter-pill {
  padding: 6px 14px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .8rem; font-weight: 600;
  color: #6b6655; cursor: pointer; transition: all var(--transition); white-space: nowrap;
}
.filter-pill:hover { border-color: var(--teal); color: var(--teal); }
.filter-pill.active { background: var(--teal); border-color: var(--teal); color: #fff; }
.filter-pill--region.active { background: var(--indigo); border-color: var(--indigo); }
.filter-search {
  display: flex; align-items: center; gap: 8px; margin-left: auto;
  background: var(--gray-50); border: 1.5px solid var(--gray-200);
  border-radius: 50px; padding: 7px 16px; transition: border-color var(--transition);
}
.filter-search:focus-within { border-color: var(--teal); }
.search-icon-sm { font-size: .9rem; }
.filter-search__input {
  border: none; outline: none; background: transparent;
  font-family: 'DM Sans', sans-serif; font-size: .84rem; color: var(--indigo); width: 180px;
}
.filter-search__input::placeholder { color: #a09880; }

/* ── Body ─────────────────────────────────────────────────────────────────── */
.visa-body { padding: 36px 5% 64px; }

/* Status legend */
.status-legend {
  display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;
}
.legend-item { display: flex; align-items: center; gap: 6px; }
.legend-dot {
  width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.legend-dot--free     { background: var(--teal); }
.legend-dot--arrival  { background: #f59e0b; }
.legend-dot--evisa    { background: #6366f1; }
.legend-dot--required { background: var(--coral); }
.legend-label { font-size: .78rem; font-weight: 600; color: #6b6655; }

/* Results bar */
.results-bar {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
}
.results-count { font-size: .88rem; color: #a09880; }
.results-count strong { color: var(--indigo); font-size: 1rem; }
.results-count em { color: var(--teal); font-style: normal; font-weight: 600; }
.sort-lbl  { font-size: .78rem; font-weight: 700; color: #a09880; }
.sort-sel {
  border: 1.5px solid var(--gray-200); border-radius: 8px; padding: 6px 10px;
  font-family: 'DM Sans', sans-serif; font-size: .82rem; color: var(--indigo);
  background: var(--white); outline: none; cursor: pointer; margin-left: 6px;
  transition: border-color var(--transition);
}
.sort-sel:focus { border-color: var(--teal); }

/* ── Visa grid ────────────────────────────────────────────────────────────── */
.visa-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
}
.visa-card {
  border-radius: 16px; overflow: hidden;
  background: var(--white); box-shadow: var(--shadow);
  cursor: pointer; display: flex; flex-direction: column;
  transition: transform .28s ease, box-shadow .28s ease;
  animation: card-in .4s ease both;
  animation-delay: calc(var(--i) * 40ms);
}
.visa-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }

@keyframes card-in {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

.visa-card__hero {
  height: 100px; display: flex; align-items: center; justify-content: center;
  position: relative; flex-shrink: 0;
}
.visa-card__flag { font-size: 3.5rem; filter: drop-shadow(0 2px 8px rgba(0,0,0,.15)); }
.visa-card__status-badge {
  position: absolute; top: 10px; right: 12px;
  font-size: .65rem; font-weight: 700; letter-spacing: .06em;
  text-transform: uppercase; padding: 3px 9px; border-radius: 50px;
}
.status-badge--free     { background: rgba(46,196,182,.15); color: var(--teal-dk); }
.status-badge--arrival  { background: rgba(245,158,11,.15); color: #92400e; }
.status-badge--evisa    { background: rgba(99,102,241,.12); color: #3730a3; }
.status-badge--required { background: rgba(255,90,95,.12);  color: var(--coral); }

.visa-card__body {
  padding: 16px 18px 18px; flex: 1; display: flex; flex-direction: column; gap: 8px;
}
.visa-card__meta { display: flex; align-items: center; gap: 8px; }
.visa-card__country {
  font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700;
  color: var(--indigo); margin: 0; line-height: 1.2;
}
.visa-card__desc {
  font-size: .82rem; color: #6b6655; line-height: 1.6;
  display: -webkit-box; -webkit-box-orient: vertical;
  -webkit-line-clamp: 2; overflow: hidden; flex: 1;
}

.visa-card__facts {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 8px; padding: 10px 0; border-top: 1px solid var(--gray-100); border-bottom: 1px solid var(--gray-100);
}
.visa-fact {
  display: flex; align-items: flex-start; gap: 5px;
}
.visa-fact__icon { font-size: .9rem; margin-top: 1px; flex-shrink: 0; }
.visa-fact__label {
  display: block; font-size: .65rem; text-transform: uppercase; letter-spacing: .06em;
  color: #a09880; font-weight: 700;
}
.visa-fact__val { display: block; font-size: .78rem; font-weight: 700; color: var(--indigo); }

.visa-card__footer {
  display: flex; align-items: center; justify-content: space-between; margin-top: 4px;
}
.visa-card__updated { font-size: .72rem; color: #a09880; }
.visa-card__cta {
  font-size: .78rem; font-weight: 700; color: var(--coral);
  background: none; border: none; cursor: pointer; padding: 0;
}

/* Guide tag */
.guide-tag {
  display: inline-block; font-size: .68rem; font-weight: 700;
  letter-spacing: .06em; text-transform: uppercase;
  padding: 3px 10px; border-radius: 50px;
  background: var(--teal-lt); color: var(--teal-dk);
}
.guide-region { font-size: .76rem; font-weight: 600; color: #a09880; }

/* ── Pagination ──────────────────────────────────────────────────────────── */
.guides-pagination {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  margin-top: 48px;
}
.page-btn {
  padding: 9px 18px; border-radius: 50px;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 600;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
}
.page-btn:hover:not(:disabled) { border-color: var(--teal); color: var(--teal); }
.page-btn:disabled { opacity: .35; cursor: not-allowed; }
.page-numbers { display: flex; gap: 6px; }
.page-num {
  width: 36px; height: 36px; border-radius: 50%;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 700;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
  display: flex; align-items: center; justify-content: center;
}
.page-num:hover  { border-color: var(--teal); color: var(--teal); }
.page-num.active { background: var(--teal); border-color: var(--teal); color: #fff; }

/* ── Empty state ─────────────────────────────────────────────────────────── */
.guides-empty { text-align: center; padding: 80px 20px; }
.guides-empty__icon  { font-size: 3rem; margin-bottom: 14px; }
.guides-empty__title { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; margin-bottom: 10px; }
.guides-empty__sub   { font-size: .9rem; color: #a09880; margin-bottom: 24px; }

/* ── Essential tips ───────────────────────────────────────────────────────── */
.tips-section {
  background: var(--sand); padding: 72px 5%;
}
.tips-section__inner { max-width: 1100px; margin: 0 auto; }
.tips-header { text-align: center; margin-bottom: 48px; }
.tips-overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--teal); margin-bottom: 12px;
}
.tips-title {
  font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 3vw, 2.6rem);
  font-weight: 700; color: var(--indigo);
}
.tips-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
}
.tip-card {
  background: var(--white); border-radius: 16px; padding: 24px 22px;
  box-shadow: var(--shadow); transition: transform var(--transition), box-shadow var(--transition);
}
.tip-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
.tip-card__icon-wrap {
  width: 48px; height: 48px; border-radius: 12px;
  background: var(--teal-lt); display: flex; align-items: center; justify-content: center;
  margin-bottom: 14px; font-size: 1.4rem;
}
.tip-card__title {
  font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 8px;
}
.tip-card__body { font-size: .84rem; color: #6b6655; line-height: 1.65; }

/* ── Checklist promo ──────────────────────────────────────────────────────── */
.checklist-promo {
  background: var(--indigo); padding: 72px 5%;
}
.checklist-promo__inner {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 64px; align-items: center; max-width: 1100px; margin: 0 auto;
}
.checklist-promo__badge {
  display: inline-block; background: var(--teal); color: #fff;
  font-size: .68rem; font-weight: 700; letter-spacing: .1em;
  text-transform: uppercase; padding: 4px 12px; border-radius: 50px;
  margin-bottom: 16px;
}
.checklist-promo__title {
  font-family: 'Fraunces', serif; font-size: clamp(1.6rem, 2.5vw, 2.2rem);
  font-weight: 700; color: #fff; margin-bottom: 14px; line-height: 1.2;
}
.checklist-promo__sub { font-size: .9rem; color: rgba(255,255,255,.65); line-height: 1.7; margin-bottom: 24px; }

.doc-preview {
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
  border-radius: 16px; padding: 20px 22px;
  display: flex; flex-direction: column; gap: 10px;
}
.doc-preview__item {
  display: flex; align-items: center; gap: 10px;
  font-size: .88rem; color: rgba(255,255,255,.55);
  transition: color .3s;
}
.doc-preview__item--checked { color: rgba(255,255,255,.9); }
.doc-check { font-size: 1rem; width: 18px; text-align: center; }
.doc-preview__item--checked .doc-check { color: var(--teal); }

/* ── Modal ────────────────────────────────────────────────────────────────── */
.guide-modal-backdrop {
  position: fixed; inset: 0; background: rgba(22,20,15,.7);
  z-index: 300; display: flex; align-items: center; justify-content: center;
  padding: 20px; overflow-y: auto;
}
.modal-fade-enter-active, .modal-fade-leave-active { transition: all .25s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; transform: scale(.97); }

.guide-modal {
  background: #faf8f3; border-radius: 20px; width: 100%; max-width: 720px;
  max-height: 90vh; overflow-y: auto; position: relative;
  box-shadow: 0 32px 80px rgba(0,0,0,.3); scrollbar-width: thin;
}
.guide-modal__close {
  position: fixed; top: 28px; right: 28px; z-index: 10;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(22,20,15,.5); border: none; color: #fff;
  font-size: .9rem; cursor: pointer; display: flex; align-items: center;
  justify-content: center; backdrop-filter: blur(4px);
  transition: background var(--transition);
}
.guide-modal__close:hover { background: var(--coral); }

/* Modal hero */
.guide-modal__hero {
  height: 200px; display: flex; align-items: flex-end;
  position: relative; border-radius: 20px 20px 0 0; overflow: hidden;
}
.guide-modal__flag {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%, -60%);
  font-size: 5rem; filter: drop-shadow(0 4px 16px rgba(0,0,0,.15));
}
.guide-modal__hero-body {
  position: relative; padding: 24px 32px; width: 100%;
  background: linear-gradient(to top, rgba(22,20,15,.5) 0%, transparent 100%);
}
.guide-modal__status-badge {
  display: inline-block; font-size: .68rem; font-weight: 700;
  letter-spacing: .06em; text-transform: uppercase;
  padding: 3px 10px; border-radius: 50px; margin-bottom: 8px;
  background: rgba(255,255,255,.2); color: #fff; backdrop-filter: blur(4px);
}
.guide-modal__title {
  font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 700;
  color: #fff; margin: 0 0 4px; line-height: 1.1;
}
.guide-modal__subtitle { font-size: .84rem; color: rgba(255,255,255,.75); margin: 0; }

.guide-modal__content { padding: 32px; }

/* Modal facts strip */
.modal-facts-strip {
  display: flex; gap: 0; margin-bottom: 28px;
  background: var(--white); border-radius: 12px; overflow: hidden;
  border: 1px solid var(--gray-200);
}
.modal-fact {
  flex: 1; padding: 14px 16px; display: flex; flex-direction: column; gap: 4px;
  border-right: 1px solid var(--gray-200);
}
.modal-fact:last-child { border-right: none; }
.modal-fact__icon { font-size: 1rem; }
.modal-fact__label { font-size: .65rem; text-transform: uppercase; letter-spacing: .06em; color: #a09880; font-weight: 700; }
.modal-fact__val { font-size: .82rem; font-weight: 700; color: var(--indigo); }

.guide-modal__lead {
  font-size: 1rem; color: var(--indigo); line-height: 1.75;
  font-style: italic; margin-bottom: 28px;
  padding-bottom: 28px; border-bottom: 1px solid var(--gray-200);
}

.guide-section { margin-bottom: 22px; }
.guide-section__title {
  font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 8px;
}
.guide-section__body { font-size: .9rem; color: #4a4538; line-height: 1.8; }

/* Documents list */
.modal-docs {
  background: #f0ede4; border-radius: 12px; padding: 18px 22px;
  margin-bottom: 20px;
}
.modal-docs__heading {
  font-size: .78rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--indigo); margin-bottom: 12px;
}
.modal-docs__list {
  list-style: none; padding: 0; margin: 0;
  display: flex; flex-direction: column; gap: 8px;
}
.modal-docs__list li {
  font-size: .87rem; color: var(--indigo); display: flex; align-items: center; gap: 8px;
}
.doc-required {
  font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
  padding: 2px 7px; border-radius: 50px; flex-shrink: 0;
  background: rgba(255,90,95,.12); color: var(--coral);
}
.doc-required--optional {
  background: var(--teal-lt); color: var(--teal-dk);
}

/* Tips box */
.guide-tips {
  background: #f0ede4; border-radius: 12px; padding: 20px 24px;
  border-left: 4px solid var(--teal); margin-bottom: 28px;
}
.guide-tips__heading {
  font-size: .78rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--teal-dk); margin-bottom: 12px;
}
.guide-tips__list {
  list-style: none; padding: 0; margin: 0;
  display: flex; flex-direction: column; gap: 8px;
}
.guide-tips__list li {
  font-size: .88rem; color: var(--indigo); padding-left: 16px; position: relative; line-height: 1.5;
}
.guide-tips__list li::before { content: '→'; position: absolute; left: 0; color: var(--teal); font-weight: 700; }

.guide-modal__cta {
  display: flex; gap: 12px; padding-top: 24px;
  border-top: 1px solid var(--gray-200); flex-wrap: wrap;
}

/* ── Checklist modal ──────────────────────────────────────────────────────── */
.checklist-modal { max-width: 560px; }
.checklist-modal__header { padding: 32px 32px 0; margin-bottom: 24px; }
.checklist-modal__title {
  font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 8px;
}
.checklist-modal__sub { font-size: .88rem; color: #6b6655; }

.checklist-form { padding: 0 32px 32px; display: flex; flex-direction: column; gap: 18px; }
.checklist-field { display: flex; flex-direction: column; gap: 8px; }
.checklist-label { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #a09880; }
.purpose-pills { display: flex; gap: 8px; flex-wrap: wrap; }

.checklist-result { padding: 0 32px 32px; }
.checklist-result__header { margin-bottom: 20px; }
.checklist-result__title {
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 4px;
}
.checklist-result__sub { font-size: .82rem; color: #a09880; }

.checklist-items { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.checklist-item { padding: 10px 14px; background: var(--white); border-radius: 10px; border: 1px solid var(--gray-200); }
.checklist-item__label { display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: .88rem; color: var(--indigo); line-height: 1.5; }
.checklist-checkbox { margin-top: 2px; accent-color: var(--teal); flex-shrink: 0; }
.checklist-done { text-decoration: line-through; color: #a09880; }
.checklist-note { display: block; font-size: .75rem; color: #a09880; margin-top: 2px; }

.checklist-actions {
  display: flex; gap: 10px; margin-top: 20px;
}
.checklist-actions .btn { flex: 1; text-align: center; }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1024px) {
  .masthead__inner { grid-template-columns: 1fr; gap: 36px; }
  .visa-grid        { grid-template-columns: repeat(2, 1fr); }
  .tips-grid        { grid-template-columns: repeat(2, 1fr); }
  .checklist-promo__inner { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .masthead__inner { padding: 36px 0 32px; }
  .masthead__title { font-size: 3rem; }
  .checker-form    { grid-template-columns: 1fr; }
  .checker-arrow   { display: none; }
  .visa-filters    { flex-direction: column; align-items: flex-start; gap: 14px; }
  .filter-search   { margin-left: 0; width: 100%; }
  .filter-search__input { width: 100%; }
  .visa-grid       { grid-template-columns: 1fr; }
  .tips-grid       { grid-template-columns: 1fr; }
  .modal-facts-strip { flex-wrap: wrap; }
  .modal-fact      { min-width: 45%; border-bottom: 1px solid var(--gray-200); }
  .guide-modal__content { padding: 20px; }
  .checklist-modal__header { padding: 20px 20px 0; }
  .checklist-form  { padding: 0 20px 20px; }
  .checklist-result { padding: 0 20px 20px; }
}
</style>