<template>
  <div class="guides-page">
    <NavBar />

    <!-- ── Masthead ──────────────────────────────────────────────────────── -->
    <header class="masthead">
      <div class="masthead__rule" />
      <div class="masthead__inner">
        <div class="masthead__left">
          <p class="masthead__overline">Voyago Editorial</p>
          <h1 class="masthead__title">Travel<br><em>Guides</em></h1>
          <p class="masthead__sub">
            Curated stories, local insights, and practical wisdom from travellers who've been there.
          </p>
          <div class="masthead__stats">
            <div class="masthead__stat">
              <span class="masthead__stat-num">{{ guides.length }}</span>
              <span class="masthead__stat-label">Guides</span>
            </div>
            <div class="masthead__stat-div" />
            <div class="masthead__stat">
              <span class="masthead__stat-num">{{ regions.length - 1 }}</span>
              <span class="masthead__stat-label">Regions</span>
            </div>
            <div class="masthead__stat-div" />
            <div class="masthead__stat">
              <span class="masthead__stat-num">{{ categories.length - 1 }}</span>
              <span class="masthead__stat-label">Topics</span>
            </div>
          </div>
        </div>
        <div class="masthead__right">
          <!-- Featured guide -->
          <div class="featured-guide" @click="openGuide(featuredGuide)">
            <div class="featured-guide__img-wrap">
              <img :src="featuredGuide.img" :alt="featuredGuide.title" class="featured-guide__img" />
              <div class="featured-guide__overlay" />
              <span class="featured-guide__badge">✦ Editor's Pick</span>
            </div>
            <div class="featured-guide__body">
              <div class="featured-guide__meta">
                <span class="guide-tag">{{ featuredGuide.category }}</span>
                <span class="guide-region">{{ featuredGuide.region }}</span>
                <span class="guide-read">{{ featuredGuide.readTime }} min read</span>
              </div>
              <h2 class="featured-guide__title">{{ featuredGuide.title }}</h2>
              <p class="featured-guide__excerpt">{{ featuredGuide.excerpt }}</p>
              <div class="featured-guide__author">
                <img :src="featuredGuide.authorAvatar" class="author-avatar" />
                <span class="author-name">{{ featuredGuide.author }}</span>
                <span class="author-date">· {{ featuredGuide.date }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="masthead__rule" />
    </header>

    <!-- ── Filters ────────────────────────────────────────────────────────── -->
    <div class="guides-filters">
      <div class="filter-section">
        <span class="filter-section__label">Topic</span>
        <div class="filter-pills">
          <button
            v-for="c in categories" :key="c"
            class="filter-pill"
            :class="{ active: activeCategory === c }"
            @click="activeCategory = c; page = 1"
          >{{ c }}</button>
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
          placeholder="Search guides…"
          @input="page = 1"
        />
      </div>
    </div>

    <!-- ── Results bar ────────────────────────────────────────────────────── -->
    <div class="guides-body">
      <div class="results-bar">
        <p class="results-count">
          <strong>{{ filteredGuides.length }}</strong>
          guide{{ filteredGuides.length !== 1 ? 's' : '' }}
          <template v-if="activeCategory !== 'All'"> in <em>{{ activeCategory }}</em></template>
          <template v-if="activeRegion !== 'All Regions'"> · <em>{{ activeRegion }}</em></template>
        </p>
        <div class="results-sort">
          <label class="sort-lbl">Sort</label>
          <select class="sort-sel" v-model="sortBy">
            <option value="newest">Newest</option>
            <option value="popular">Most popular</option>
            <option value="read_time">Quickest read</option>
          </select>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="pagedGuides.length === 0" class="guides-empty">
        <div class="guides-empty__icon">📭</div>
        <h3 class="guides-empty__title">No guides found</h3>
        <p class="guides-empty__sub">Try a different topic, region, or search term.</p>
        <button class="btn btn-coral" @click="resetFilters">Clear filters</button>
      </div>

      <!-- ── Guide grid (editorial layout) ────────────────────────────────── -->
      <div v-else class="guides-grid">
        <template v-for="(guide, i) in pagedGuides" :key="guide.id">

          <!-- Wide card every 5th (0-indexed: 0, 5, 10…) -->
          <div
            v-if="i % 5 === 0"
            class="guide-card guide-card--wide"
            :style="{ '--i': i }"
            @click="openGuide(guide)"
          >
            <div class="guide-card__img-wrap">
              <img :src="guide.img" :alt="guide.title" class="guide-card__img" />
              <div class="guide-card__img-overlay" />
            </div>
            <div class="guide-card__body">
              <div class="guide-card__meta">
                <span class="guide-tag">{{ guide.category }}</span>
                <span class="guide-region">{{ guide.region }}</span>
              </div>
              <h3 class="guide-card__title">{{ guide.title }}</h3>
              <p class="guide-card__excerpt">{{ guide.excerpt }}</p>
              <div class="guide-card__footer">
                <div class="guide-author">
                  <img :src="guide.authorAvatar" class="author-avatar author-avatar--sm" />
                  <span>{{ guide.author }}</span>
                </div>
                <div class="guide-card__chips">
                  <span class="chip chip--time">{{ guide.readTime }} min</span>
                  <span class="chip chip--views">{{ guide.views.toLocaleString() }} reads</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Standard card -->
          <div
            v-else
            class="guide-card"
            :style="{ '--i': i }"
            @click="openGuide(guide)"
          >
            <div class="guide-card__img-wrap">
              <img :src="guide.img" :alt="guide.title" class="guide-card__img" />
              <div class="guide-card__img-overlay" />
              <span class="guide-card__read-badge">{{ guide.readTime }} min read</span>
            </div>
            <div class="guide-card__body">
              <div class="guide-card__meta">
                <span class="guide-tag">{{ guide.category }}</span>
                <span class="guide-region">{{ guide.region }}</span>
              </div>
              <h3 class="guide-card__title">{{ guide.title }}</h3>
              <p class="guide-card__excerpt guide-card__excerpt--short">{{ guide.excerpt }}</p>
              <div class="guide-card__footer">
                <div class="guide-author">
                  <img :src="guide.authorAvatar" class="author-avatar author-avatar--sm" />
                  <span>{{ guide.author }}</span>
                </div>
                <span class="chip chip--views">{{ guide.views.toLocaleString() }}</span>
              </div>
            </div>
          </div>

        </template>
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

    <!-- ── Newsletter band ───────────────────────────────────────────────── -->
    <div class="guides-newsletter">
      <div class="guides-newsletter__inner">
        <div class="guides-newsletter__text">
          <h3 class="guides-newsletter__title">Never miss a guide</h3>
          <p class="guides-newsletter__sub">New stories every week. No spam, unsubscribe anytime.</p>
        </div>
        <div class="guides-newsletter__form">
          <input
            class="newsletter-input"
            v-model="newsletterEmail"
            type="email"
            placeholder="your@email.com"
          />
          <button class="btn btn-coral newsletter-btn" @click="handleSubscribe">
            Subscribe →
          </button>
        </div>
        <p v-if="subscribed" class="newsletter-thanks">✓ You're in! Welcome to the Voyago editorial.</p>
      </div>
    </div>

    <!-- ── Guide detail modal ────────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="activeGuide" class="guide-modal-backdrop" @click.self="activeGuide = null">
          <div class="guide-modal">
            <button class="guide-modal__close" @click="activeGuide = null">✕</button>

            <div class="guide-modal__hero">
              <img :src="activeGuide.img" :alt="activeGuide.title" class="guide-modal__img" />
              <div class="guide-modal__hero-overlay" />
              <div class="guide-modal__hero-body">
                <span class="guide-tag guide-tag--light">{{ activeGuide.category }}</span>
                <h2 class="guide-modal__title">{{ activeGuide.title }}</h2>
                <div class="guide-modal__byline">
                  <img :src="activeGuide.authorAvatar" class="author-avatar" />
                  <span>{{ activeGuide.author }}</span>
                  <span>·</span>
                  <span>{{ activeGuide.date }}</span>
                  <span>·</span>
                  <span>{{ activeGuide.readTime }} min read</span>
                </div>
              </div>
            </div>

            <div class="guide-modal__content">
              <p class="guide-modal__lead">{{ activeGuide.excerpt }}</p>

              <div v-for="(section, si) in activeGuide.sections" :key="si" class="guide-section">
                <h3 class="guide-section__title">{{ section.heading }}</h3>
                <p class="guide-section__body">{{ section.body }}</p>
              </div>

              <!-- Tips box -->
              <div class="guide-tips" v-if="activeGuide.tips?.length">
                <h4 class="guide-tips__heading">✦ Quick tips</h4>
                <ul class="guide-tips__list">
                  <li v-for="(tip, ti) in activeGuide.tips" :key="ti">{{ tip }}</li>
                </ul>
              </div>

              <!-- CTA -->
              <div class="guide-modal__cta">
                <RouterLink
                  :to="{ path: '/search', query: { q: activeGuide.region } }"
                  class="btn btn-coral"
                  @click="activeGuide = null"
                >
                  Find packages in {{ activeGuide.region }} →
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

    <SiteFooter />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import NavBar     from '@/components/home/NavBar.vue'
import SiteFooter from '@/components/home/SiteFooter.vue'

// ── Filters ───────────────────────────────────────────────────────────────
const categories    = ['All', 'Culture', 'Adventure', 'Food & Drink', 'Beach', 'City Break', 'Nature', 'Wellness', 'Budget Travel']
const regions       = ['All Regions', 'Europe', 'Asia', 'Africa', 'Americas', 'Middle East', 'Oceania']
const activeCategory = ref('All')
const activeRegion   = ref('All Regions')
const searchQuery    = ref('')
const sortBy         = ref('newest')
const page           = ref(1)
const perPage        = 9

// ── Guide data ────────────────────────────────────────────────────────────
const guides = ref([
  {
    id: 1,
    title: 'Kyoto in Bloom: A First-Timer\'s Guide to Cherry Blossom Season',
    excerpt: 'Every spring, Kyoto transforms into a canvas of pale pink and white. Here\'s how to see it without the crowds — and which spots the locals actually love.',
    category: 'Culture', region: 'Asia', readTime: 8, views: 24300, date: 'Mar 2025',
    author: 'Yuki Tanaka', authorAvatar: 'https://i.pravatar.cc/40?img=47',
    img: 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=800&q=80',
    sections: [
      { heading: 'When to go', body: 'Cherry blossom season (sakura) typically runs from late March to mid-April depending on the year. The peak — known as mankai — lasts only about a week, so timing is everything. Check the Japan Meteorological Corporation\'s annual forecast, released each January.' },
      { heading: 'The hidden spots', body: 'While Maruyama Park and the Philosopher\'s Path are stunning, the crowds can be overwhelming. Instead, try Daigo-ji Temple in the south, or the canal-side path through Fushimi — both offer equal beauty with a fraction of the visitors. Hirano Shrine is a true local secret, with over 60 varieties of cherry tree.' },
      { heading: 'Practical advice', body: 'Book accommodation at least 3 months ahead — prices triple during sakura season. Get a Suica card for trains, and avoid peak hours (8–10am, 5–7pm) on the subway. Many of the best spots are free; save your budget for a kaiseki dinner under the blossoms.' },
    ],
    tips: ['Visit Fushimi Inari at 6am before tour buses arrive', 'Rent a bicycle — Kyoto is flat and perfectly bike-sized', 'Pre-book the Arashiyama bamboo grove entry for sunrise access', 'Pack layers — spring mornings are cold'],
    featured: true,
  },
  {
    id: 2,
    title: 'Morocco Beyond Marrakech: The Atlas Mountains Await',
    excerpt: 'Most tourists never leave Marrakech\'s medina. Those who do discover a country of staggering contrasts — Berber villages, ancient kasbahs, and star-filled skies.',
    category: 'Adventure', region: 'Africa', readTime: 10, views: 18700, date: 'Feb 2025',
    author: 'Amelia Rhodes', authorAvatar: 'https://i.pravatar.cc/40?img=5',
    img: 'https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=800&q=80',
    sections: [
      { heading: 'Getting to the mountains', body: 'The High Atlas is just 90 minutes from Marrakech by grand taxi. The village of Imlil is the standard gateway — a 45-minute trail above it sits the 4,167m peak of Jebel Toubkal, North Africa\'s highest summit. You don\'t need to be a serious climber, just reasonably fit.' },
      { heading: 'Staying in a kasbah', body: 'Kasbah du Toubkal is a former Berber chieftain\'s residence converted into a mountain lodge. The views are jaw-dropping and the hammam after a day\'s trek is worth every dirham. Booking 2–3 months in advance is recommended for spring and autumn.' },
    ],
    tips: ['Hire a local Berber guide — it supports communities and they know every trail', 'Bring cash; ATMs don\'t exist above Asni', 'The Sahara is a long drive — consider a domestic flight to Ouarzazate'],
  },
  {
    id: 3,
    title: 'Eating Your Way Through Naples: The Pizza Pilgrim\'s Handbook',
    excerpt: 'Neapolitan pizza is a UNESCO-protected art form. We spent a week finding the best pizzaioli in the city — here\'s the definitive guide.',
    category: 'Food & Drink', region: 'Europe', readTime: 6, views: 31200, date: 'Jan 2025',
    author: 'Marco Ferretti', authorAvatar: 'https://i.pravatar.cc/40?img=12',
    img: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&q=80',
    sections: [
      { heading: 'The Holy Trinity', body: 'Three pizzerias define the Naples experience: L\'Antica Pizzeria da Michele (established 1870, only marinara and margherita), Sorbillo on Via dei Tribunali, and the newer but brilliant Francesco e Salvatore Salvo in San Giorgio a Cremano. All have queues. All are worth it.' },
      { heading: 'Beyond pizza', body: 'Naples is also the home of sfogliatella, the ribbed pastry filled with ricotta and citrus peel, best eaten hot from Attanasio near the central station. For fried food, Via dei Tribunali is lined with friggitorie selling cuoppo — paper cones of mixed fried delights for €3.' },
    ],
    tips: ['Lunch service (12–2pm) has shorter queues than dinner', 'A genuine Neapolitan margherita costs €5–8 — if it\'s more, you\'re in a tourist trap', 'Take the Circumvesuviana train to Pompeii; combine history with a day trip'],
  },
  {
    id: 4,
    title: 'The Greek Islands Nobody Talks About',
    excerpt: 'Santorini is beautiful. It\'s also overrun. Here are five islands where the tavernas are still run by the same family and the beaches are empty by noon.',
    category: 'Beach', region: 'Europe', readTime: 7, views: 15800, date: 'Apr 2025',
    author: 'Elena Vassilaki', authorAvatar: 'https://i.pravatar.cc/40?img=9',
    img: 'https://images.unsplash.com/photo-1533105079780-92b9be482077?w=800&q=80',
    sections: [
      { heading: 'Folegandros', body: 'Fewer than 800 permanent residents, no airport, and a chora (main village) perched on a cliff 300m above the sea. Folegandros is what Santorini was 40 years ago. One ferry a day keeps it gloriously isolated. The Panagia church above the village is reached by a 20-minute walk and offers 360-degree Aegean views.' },
      { heading: 'Ikaria', body: 'One of the world\'s five Blue Zones — places where people regularly live past 100. The islanders attribute their longevity to red wine, dancing, and sleeping late. Ferries run irregularly (the Ikarians say the ferry schedule is merely a suggestion), but the thermal baths at Therma and the wild beaches of Nas make it essential.' },
    ],
    tips: ['Travel May or September — perfect weather, half the tourists', 'Learn ten words of Greek — it unlocks extraordinary hospitality', 'Never pre-book restaurants in smaller islands; just walk in'],
  },
  {
    id: 5,
    title: 'Tokyo for the Architecture Obsessive',
    excerpt: 'From Tadao Ando\'s meditative concrete to Kengo Kuma\'s textured facades, Tokyo is an open-air architecture museum. A curated walking tour across three neighbourhoods.',
    category: 'City Break', region: 'Asia', readTime: 9, views: 12400, date: 'Mar 2025',
    author: 'Yuki Tanaka', authorAvatar: 'https://i.pravatar.cc/40?img=47',
    img: 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800&q=80',
    sections: [
      { heading: 'Omotesando', body: 'The "Champs-Élysées of Tokyo" is lined with flagship stores by world-class architects. Toyo Ito\'s TOD\'S building wraps the structure in concrete tree forms. Herzog & de Meuron\'s Prada boutique is a glass diamond. Tadao Ando\'s Omotesando Hills is a subterranean spiral that deserves an hour of careful exploration.' },
    ],
    tips: ['The ICC in Shinjuku shows exhibitions on digital architecture', 'Nakagin Capsule Tower (now demolished) is documented at the Architecture Museum in Edo-Tokyo'],
  },
  {
    id: 6,
    title: 'Patagonia on a Budget: The W Trek Without Breaking the Bank',
    excerpt: 'Torres del Paine is one of Earth\'s great landscapes. The full W Trek can be done for under $500 all-in — if you know what to book and when.',
    category: 'Adventure', region: 'Americas', readTime: 12, views: 9800, date: 'Nov 2024',
    author: 'Carlos Mendez', authorAvatar: 'https://i.pravatar.cc/40?img=15',
    img: 'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=800&q=80',
    sections: [
      { heading: 'The booking window', body: 'CONAF (Chile\'s national parks authority) releases W Trek camping permits in mid-July for the following season (Nov–Mar). Set an alarm. The free campsites at Italiano and Británico fill in hours; paid sites at Paine Grande and Torres are easier to get.' },
      { heading: 'Day by day', body: 'Classic east-to-west: Day 1 Torre Base (8hr), Day 2 rest or Valle del Francés (4hr loop), Day 3 Mirador Británico (6hr), Day 4 Grey Glacier (5hr), Day 5 catamaran to Pudeto and bus out. Pack for all four seasons in one day — Patagonia is famously capricious.' },
    ],
    tips: ['The park entrance fee is $35 USD — buy online, it\'s often sold out at the gate', 'A gas canister costs $10 in Puerto Natales; check stove compatibility before you buy', 'Hitchhiking from Puerto Natales is common and safe'],
  },
  {
    id: 7,
    title: 'Bali\'s Spiritual Interior: Ubud, Temples and Rice Terraces',
    excerpt: 'Beyond the beach clubs of Seminyak lies a Bali of ancient ritual, hand-woven textiles and volcanic peaks. Here\'s how to find it.',
    category: 'Wellness', region: 'Asia', readTime: 7, views: 20100, date: 'Jan 2025',
    author: 'Amelia Rhodes', authorAvatar: 'https://i.pravatar.cc/40?img=5',
    img: 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800&q=80',
    sections: [
      { heading: 'The temple circuit', body: 'Pura Besakih (the Mother Temple), Pura Luhur Batukaru and Pura Tirta Empul — where locals bathe in sacred spring water — form the essential trinity. Dress modestly, bring a sarong (available for rent at every temple), and visit early morning before tour groups arrive.' },
    ],
    tips: ['Hire a local driver-guide rather than joining tours — flexibility is worth the small premium', 'Attend a Kecak fire dance at Uluwatu at sunset', 'The rice terraces of Tegallalang are beautiful; Jatiluwih (UNESCO-listed) are better and less crowded'],
  },
  {
    id: 8,
    title: 'Crossing the Sahara: Three Days on the Ancient Trans-Saharan Route',
    excerpt: 'From the salt pans of Timbuktu to the dunes of Merzouga, the Sahara is not one desert but a thousand. A journey across its edge reveals something untranslatable.',
    category: 'Adventure', region: 'Africa', readTime: 11, views: 7600, date: 'Oct 2024',
    author: 'Carlos Mendez', authorAvatar: 'https://i.pravatar.cc/40?img=15',
    img: 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=800&q=80',
    sections: [
      { heading: 'Night in the dunes', body: 'A bivouac camp among the Erg Chebbi dunes near Merzouga is the most cinematic experience Morocco offers. Sleeping outside — really outside, on a dune with no tent, watching the Milky Way rotate overhead — resets something fundamental. Temperature drops 20°C after sunset; bring a proper sleeping bag.' },
    ],
    tips: ['Book a reputable guide through a licensed Moroccan agency', 'The light at golden hour on orange dunes is unrepeatable — don\'t waste it on your phone'],
  },
  {
    id: 9,
    title: 'The Norwegian Fjords: What Nobody Prepares You For',
    excerpt: 'Geirangerfjord is on every "bucket list". What the listicles don\'t tell you: the weather, the costs, the ferry schedules, and the secret viewpoints that blow the famous ones away.',
    category: 'Nature', region: 'Europe', readTime: 8, views: 16300, date: 'May 2025',
    author: 'Erik Lindqvist', authorAvatar: 'https://i.pravatar.cc/40?img=33',
    img: 'https://images.unsplash.com/photo-1519659528534-7fd733a832a0?w=800&q=80',
    sections: [
      { heading: 'The real costs', body: 'Norway is expensive — budget $120–150/day minimum outside your accommodation. A beer costs $12. A simple lunch $25. Plan accordingly. The good news: most of what makes Norway spectacular is free — the fjords, the light, the hiking trails.' },
    ],
    tips: ['The Nærøyfjord is narrower and arguably more dramatic than Geirangerfjord', 'Take the Flåm Railway — it\'s touristy and worth every krone', 'Wild camping is legal in Norway; use it'],
  },
  {
    id: 10,
    title: 'Street Food Bangkok: A Neighbourhood-by-Neighbourhood Map',
    excerpt: 'Bangkok\'s street food scene survived the pandemic, the gentrification, and the Michelin inspectors. Here\'s where to eat and what to order in six distinct neighbourhoods.',
    category: 'Food & Drink', region: 'Asia', readTime: 9, views: 27800, date: 'Apr 2025',
    author: 'Priya Sharma', authorAvatar: 'https://i.pravatar.cc/40?img=44',
    img: 'https://images.unsplash.com/photo-1562802378-063ec186a863?w=800&q=80',
    sections: [
      { heading: 'Yaowarat (Chinatown)', body: 'The heartbeat of Bangkok street food. At dusk, T&K Seafood sets up enormous grills on the pavement outside Wat Traimit. Order the grilled river prawn, the oyster omelette, and the crab fried rice. Arrive before 7pm or queue for 45 minutes.' },
      { heading: 'On Nut and Phra Khanong', body: 'These eastern BTS stops are where Bangkok\'s middle class actually eats. Jay Fai — the Michelin-starred street cook with the iconic welding goggles — is here, but so are dozens of cheaper, excellent alternatives. Kuay Teow Reua (boat noodles) at any plastic-stool joint on Sukhumvit Soi 38 rivals anything at twice the price.' },
    ],
    tips: ['Eat where Thais eat — if the menu has photos and English, recalibrate', 'The best mango sticky rice is from supermarket stalls inside Tops or Big C, not tourist spots', 'BTS Skytrain is air-conditioned and costs $0.50; taxi apps (Grab) are cheaper than metered cabs after midnight'],
  },
  {
    id: 11,
    title: 'Slow Travel in Portugal\'s Alentejo: Cork Forests and Whitewashed Villages',
    excerpt: 'While the Algarve fills with summer crowds, the vast plains of Alentejo offer a different Portugal — unhurried, ancient, and still largely undiscovered.',
    category: 'Culture', region: 'Europe', readTime: 7, views: 8900, date: 'Mar 2025',
    author: 'Elena Vassilaki', authorAvatar: 'https://i.pravatar.cc/40?img=9',
    img: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
    sections: [
      { heading: 'Évora and beyond', body: 'Évora\'s Roman temple sits incongruously in the middle of a residential square. The Chapel of Bones (Igreja de São Francisco) — built from the skulls and femurs of 5,000 monks — is macabre and beautiful. Outside the city, the megalithic Cromeleque dos Almendres predates Stonehenge by a thousand years.' },
    ],
    tips: ['Alentejo wine is Portugal\'s best-kept secret — ask for local Trincadeira or Aragonez', 'Rent a car; the region is vast and public transport sparse', 'Stay in a historic pousada rather than a chain hotel'],
  },
  {
    id: 12,
    title: 'Diving the Coral Triangle: Raja Ampat for the First-Time Diver',
    excerpt: 'Raja Ampat contains more marine species than anywhere else on Earth. You don\'t need to be an advanced diver to experience it — here\'s how to plan your first trip.',
    category: 'Nature', region: 'Asia', readTime: 10, views: 11200, date: 'Feb 2025',
    author: 'Priya Sharma', authorAvatar: 'https://i.pravatar.cc/40?img=44',
    img: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&q=80',
    sections: [
      { heading: 'Getting there', body: 'Fly to Sorong via Jakarta or Makassar. From Sorong, a speedboat to Waisai (the main town on Waigeo island) takes 2 hours. Most dive resorts arrange transfers. The Raja Ampat entry fee is $100 and supports local conservation — pay it, it\'s worth it.' },
    ],
    tips: ['October–April is best for visibility (20–30m)', 'You need at least an Open Water certification; arrange it before you arrive', 'The manta ray cleaning station at Manta Sandy is non-negotiable'],
  },
])

// ── Computed ──────────────────────────────────────────────────────────────
const featuredGuide = computed(() => guides.value.find(g => g.featured) ?? guides.value[0])

const filteredGuides = computed(() => {
  let list = guides.value.filter(g => !g.featured || activeCategory.value !== 'All' || activeRegion.value !== 'All Regions' || searchQuery.value)

  if (activeCategory.value !== 'All')
    list = list.filter(g => g.category === activeCategory.value)
  if (activeRegion.value !== 'All Regions')
    list = list.filter(g => g.region === activeRegion.value)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(g =>
      g.title.toLowerCase().includes(q) ||
      g.excerpt.toLowerCase().includes(q) ||
      g.region.toLowerCase().includes(q) ||
      g.author.toLowerCase().includes(q)
    )
  }

  if (sortBy.value === 'popular')   list = [...list].sort((a, b) => b.views - a.views)
  if (sortBy.value === 'read_time') list = [...list].sort((a, b) => a.readTime - b.readTime)
  if (sortBy.value === 'newest')    list = [...list].sort((a, b) => b.id - a.id)

  return list
})

const totalPages  = computed(() => Math.max(1, Math.ceil(filteredGuides.value.length / perPage)))
const pagedGuides = computed(() => {
  const s = (page.value - 1) * perPage
  return filteredGuides.value.slice(s, s + perPage)
})

function resetFilters() {
  activeCategory.value = 'All'
  activeRegion.value   = 'All Regions'
  searchQuery.value    = ''
  page.value           = 1
}

// ── Guide modal ───────────────────────────────────────────────────────────
const activeGuide = ref(null)
function openGuide(guide) { activeGuide.value = guide }

// ── Newsletter ────────────────────────────────────────────────────────────
const newsletterEmail = ref('')
const subscribed      = ref(false)
function handleSubscribe() {
  if (!newsletterEmail.value.includes('@')) return
  subscribed.value = true
  newsletterEmail.value = ''
}
</script>

<style scoped>
/* ── Page ────────────────────────────────────────────────────────────────── */
.guides-page {
  min-height: 100vh;
  background: #faf8f3;
  padding-top: 68px;
  font-family: 'DM Sans', sans-serif;
}

/* ── Masthead ────────────────────────────────────────────────────────────── */
.masthead {
  padding: 0 5%;
  background: #faf8f3;
}
.masthead__rule {
  height: 2px;
  background: var(--indigo);
  margin: 0;
}
.masthead__inner {
  display: grid;
  grid-template-columns: 1fr 1.4fr;
  gap: 64px;
  align-items: center;
  padding: 52px 0 48px;
}

.masthead__overline {
  font-size: .7rem; font-weight: 700; letter-spacing: .18em;
  text-transform: uppercase; color: var(--coral);
  margin-bottom: 16px;
}
.masthead__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(3rem, 6vw, 5.5rem);
  font-weight: 700; line-height: .92;
  color: var(--indigo); margin-bottom: 20px;
}
.masthead__title em {
  color: var(--coral); font-style: italic;
}
.masthead__sub {
  font-size: 1rem; color: #6b6655; line-height: 1.7;
  max-width: 360px; margin-bottom: 32px;
}
.masthead__stats {
  display: flex; align-items: center; gap: 24px;
}
.masthead__stat { display: flex; flex-direction: column; }
.masthead__stat-num {
  font-family: 'Fraunces', serif; font-size: 2.4rem; font-weight: 700;
  color: var(--indigo); line-height: 1;
}
.masthead__stat-label {
  font-size: .72rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: #a09880; margin-top: 3px;
}
.masthead__stat-div {
  width: 1px; height: 48px; background: var(--gray-200);
}

/* ── Featured guide ──────────────────────────────────────────────────────── */
.featured-guide {
  cursor: pointer;
  border-radius: 20px; overflow: hidden;
  box-shadow: 0 20px 60px rgba(45,49,66,.14);
  transition: transform .3s ease, box-shadow .3s ease;
  background: var(--white);
}
.featured-guide:hover {
  transform: translateY(-4px);
  box-shadow: 0 28px 72px rgba(45,49,66,.2);
}
.featured-guide__img-wrap {
  position: relative; height: 280px; overflow: hidden;
}
.featured-guide__img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform .5s ease;
}
.featured-guide:hover .featured-guide__img { transform: scale(1.04); }
.featured-guide__overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, transparent 40%, rgba(22,20,15,.65));
}
.featured-guide__badge {
  position: absolute; top: 16px; left: 16px;
  background: var(--coral); color: #fff;
  font-size: .7rem; font-weight: 700; letter-spacing: .06em;
  padding: 5px 12px; border-radius: 50px;
  text-transform: uppercase;
}
.featured-guide__body { padding: 22px 26px 24px; }
.featured-guide__meta {
  display: flex; align-items: center; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;
}
.featured-guide__title {
  font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700;
  color: var(--indigo); line-height: 1.25; margin-bottom: 10px;
}
.featured-guide__excerpt {
  font-size: .88rem; color: #6b6655; line-height: 1.65; margin-bottom: 16px;
}
.featured-guide__author {
  display: flex; align-items: center; gap: 8px; font-size: .82rem;
  color: #a09880;
}

/* ── Shared guide meta chips ─────────────────────────────────────────────── */
.guide-tag {
  display: inline-block; font-size: .68rem; font-weight: 700;
  letter-spacing: .06em; text-transform: uppercase;
  padding: 3px 10px; border-radius: 50px;
  background: var(--coral-lt); color: var(--coral);
}
.guide-tag--light { background: rgba(255,255,255,.2); color: #fff; }
.guide-region {
  font-size: .76rem; font-weight: 600; color: #a09880;
}
.guide-read { font-size: .76rem; color: #a09880; }

.author-avatar {
  width: 28px; height: 28px; border-radius: 50%; object-fit: cover; flex-shrink: 0;
}
.author-avatar--sm { width: 22px; height: 22px; }
.author-name { font-weight: 600; color: var(--indigo); font-size: .82rem; }
.author-date { color: #a09880; font-size: .8rem; }

/* ── Filters ─────────────────────────────────────────────────────────────── */
.guides-filters {
  background: var(--white);
  border-top: 1px solid var(--gray-200);
  border-bottom: 1px solid var(--gray-200);
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
  color: #6b6655; cursor: pointer; transition: all var(--transition);
  white-space: nowrap;
}
.filter-pill:hover { border-color: var(--coral); color: var(--coral); }
.filter-pill.active { background: var(--coral); border-color: var(--coral); color: #fff; }
.filter-pill--region.active { background: var(--indigo); border-color: var(--indigo); }

.filter-search {
  display: flex; align-items: center; gap: 8px; margin-left: auto;
  background: var(--gray-50); border: 1.5px solid var(--gray-200);
  border-radius: 50px; padding: 7px 16px;
  transition: border-color var(--transition);
}
.filter-search:focus-within { border-color: var(--coral); }
.search-icon-sm { font-size: .9rem; }
.filter-search__input {
  border: none; outline: none; background: transparent;
  font-family: 'DM Sans', sans-serif; font-size: .84rem; color: var(--indigo);
  width: 180px;
}
.filter-search__input::placeholder { color: #a09880; }

/* ── Body ────────────────────────────────────────────────────────────────── */
.guides-body { padding: 36px 5% 64px; }

.results-bar {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
}
.results-count { font-size: .88rem; color: #a09880; }
.results-count strong { color: var(--indigo); font-size: 1rem; }
.results-count em { color: var(--coral); font-style: normal; font-weight: 600; }

.sort-lbl  { font-size: .78rem; font-weight: 700; color: #a09880; }
.sort-sel {
  border: 1.5px solid var(--gray-200); border-radius: 8px; padding: 6px 10px;
  font-family: 'DM Sans', sans-serif; font-size: .82rem; color: var(--indigo);
  background: var(--white); outline: none; cursor: pointer; margin-left: 6px;
  transition: border-color var(--transition);
}
.sort-sel:focus { border-color: var(--coral); }

/* ── Guide grid ──────────────────────────────────────────────────────────── */
.guides-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 28px;
}

/* Wide card spans full row */
.guide-card--wide {
  grid-column: 1 / -1;
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  border-radius: 20px;
  overflow: hidden;
  background: var(--white);
  box-shadow: var(--shadow);
  cursor: pointer;
  transition: transform .28s ease, box-shadow .28s ease;
  animation: card-in .4s ease both;
  animation-delay: calc(var(--i) * 40ms);
}
.guide-card--wide:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}
.guide-card--wide .guide-card__img-wrap { height: 100%; min-height: 320px; }
.guide-card--wide .guide-card__body { padding: 36px 40px; display: flex; flex-direction: column; justify-content: center; }
.guide-card--wide .guide-card__title { font-size: 1.6rem; margin-bottom: 14px; }
.guide-card--wide .guide-card__excerpt { font-size: .92rem; -webkit-line-clamp: 4; }

/* Standard card */
.guide-card {
  border-radius: 16px; overflow: hidden;
  background: var(--white); box-shadow: var(--shadow);
  cursor: pointer; display: flex; flex-direction: column;
  transition: transform .28s ease, box-shadow .28s ease;
  animation: card-in .4s ease both;
  animation-delay: calc(var(--i) * 40ms);
}
.guide-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }

@keyframes card-in {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

.guide-card__img-wrap {
  position: relative; height: 200px; overflow: hidden; flex-shrink: 0;
}
.guide-card__img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform .5s ease;
}
.guide-card:hover .guide-card__img { transform: scale(1.06); }
.guide-card__img-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, transparent 50%, rgba(22,20,15,.4));
}
.guide-card__read-badge {
  position: absolute; bottom: 12px; right: 12px;
  background: rgba(22,20,15,.6); color: #fff;
  font-size: .7rem; font-weight: 700; padding: 3px 9px; border-radius: 50px;
  backdrop-filter: blur(4px);
}

.guide-card__body {
  padding: 18px 20px 20px; flex: 1; display: flex; flex-direction: column; gap: 8px;
}
.guide-card__meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.guide-card__title {
  font-family: 'Fraunces', serif; font-size: 1.08rem; font-weight: 700;
  color: var(--indigo); line-height: 1.25; margin: 0;
}
.guide-card__excerpt {
  font-size: .84rem; color: #6b6655; line-height: 1.6;
  display: -webkit-box; -webkit-box-orient: vertical;
  -webkit-line-clamp: 3; overflow: hidden; flex: 1;
}
.guide-card__excerpt--short { -webkit-line-clamp: 2; }

.guide-card__footer {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: auto; padding-top: 10px; border-top: 1px solid var(--gray-100);
}
.guide-author {
  display: flex; align-items: center; gap: 6px;
  font-size: .78rem; color: #6b6655; font-weight: 500;
}

.chip {
  font-size: .7rem; font-weight: 700; padding: 3px 9px;
  border-radius: 50px; white-space: nowrap;
}
.chip--time  { background: var(--teal-lt); color: var(--teal-dk); }
.chip--views { background: var(--gray-100); color: #6b6655; }

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
.page-btn:hover:not(:disabled) { border-color: var(--coral); color: var(--coral); }
.page-btn:disabled { opacity: .35; cursor: not-allowed; }
.page-numbers { display: flex; gap: 6px; }
.page-num {
  width: 36px; height: 36px; border-radius: 50%;
  border: 1.5px solid var(--gray-200); background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .84rem; font-weight: 700;
  color: var(--gray-600); cursor: pointer; transition: all var(--transition);
  display: flex; align-items: center; justify-content: center;
}
.page-num:hover  { border-color: var(--coral); color: var(--coral); }
.page-num.active { background: var(--coral); border-color: var(--coral); color: #fff; }

/* ── Newsletter band ─────────────────────────────────────────────────────── */
.guides-newsletter {
  background: var(--indigo);
  padding: 52px 5%;
}
.guides-newsletter__inner {
  max-width: 700px; margin: 0 auto; text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 20px;
}
.guides-newsletter__title {
  font-family: 'Fraunces', serif; font-size: 1.8rem; font-weight: 700; color: #fff;
}
.guides-newsletter__sub { font-size: .9rem; color: rgba(255,255,255,.6); }
.guides-newsletter__form {
  display: flex; gap: 10px; width: 100%; max-width: 440px;
}
.newsletter-input {
  flex: 1; border: 1.5px solid rgba(255,255,255,.2); border-radius: 50px;
  background: rgba(255,255,255,.08); padding: 12px 20px;
  font-family: 'DM Sans', sans-serif; font-size: .9rem; color: #fff; outline: none;
  transition: border-color var(--transition);
}
.newsletter-input::placeholder { color: rgba(255,255,255,.4); }
.newsletter-input:focus { border-color: var(--coral); }
.newsletter-btn { padding: 12px 26px; border-radius: 50px; font-size: .88rem; }
.newsletter-thanks { font-size: .84rem; color: var(--teal); font-weight: 600; }

/* ── Empty state ─────────────────────────────────────────────────────────── */
.guides-empty { text-align: center; padding: 80px 20px; }
.guides-empty__icon  { font-size: 3rem; margin-bottom: 14px; }
.guides-empty__title { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; margin-bottom: 10px; }
.guides-empty__sub   { font-size: .9rem; color: #a09880; margin-bottom: 24px; }

/* ── Modal ───────────────────────────────────────────────────────────────── */
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
  box-shadow: 0 32px 80px rgba(0,0,0,.3);
  scrollbar-width: thin;
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

.guide-modal__hero { position: relative; height: 340px; overflow: hidden; border-radius: 20px 20px 0 0; }
.guide-modal__img  { width: 100%; height: 100%; object-fit: cover; }
.guide-modal__hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, transparent 20%, rgba(22,20,15,.8));
}
.guide-modal__hero-body {
  position: absolute; bottom: 0; left: 0; right: 0; padding: 28px 32px;
  display: flex; flex-direction: column; gap: 10px;
}
.guide-modal__title {
  font-family: 'Fraunces', serif; font-size: 1.7rem; font-weight: 700;
  color: #fff; line-height: 1.15;
}
.guide-modal__byline {
  display: flex; align-items: center; gap: 8px;
  font-size: .8rem; color: rgba(255,255,255,.7);
}
.guide-modal__byline img { width: 24px; height: 24px; border-radius: 50%; }

.guide-modal__content { padding: 32px; }
.guide-modal__lead {
  font-size: 1.05rem; color: var(--indigo); line-height: 1.75;
  font-style: italic; margin-bottom: 28px;
  padding-bottom: 28px; border-bottom: 1px solid var(--gray-200);
}

.guide-section { margin-bottom: 24px; }
.guide-section__title {
  font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 10px;
}
.guide-section__body {
  font-size: .92rem; color: #4a4538; line-height: 1.8;
}

.guide-tips {
  background: #f0ede4; border-radius: 12px; padding: 20px 24px;
  border-left: 4px solid var(--coral); margin-bottom: 28px;
}
.guide-tips__heading {
  font-size: .78rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--coral); margin-bottom: 12px;
}
.guide-tips__list {
  list-style: none; padding: 0; margin: 0;
  display: flex; flex-direction: column; gap: 8px;
}
.guide-tips__list li {
  font-size: .88rem; color: var(--indigo); padding-left: 16px; position: relative;
  line-height: 1.5;
}
.guide-tips__list li::before {
  content: '→'; position: absolute; left: 0; color: var(--coral); font-weight: 700;
}

.guide-modal__cta {
  display: flex; gap: 12px; padding-top: 24px;
  border-top: 1px solid var(--gray-200); flex-wrap: wrap;
}

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1024px) {
  .masthead__inner { grid-template-columns: 1fr; gap: 36px; }
  .guides-grid     { grid-template-columns: repeat(2, 1fr); }
  .guide-card--wide { grid-template-columns: 1fr; }
  .guide-card--wide .guide-card__img-wrap { min-height: 220px; }
}
@media (max-width: 768px) {
  .masthead__inner  { padding: 36px 0 32px; }
  .masthead__title  { font-size: 3rem; }
  .guides-filters   { flex-direction: column; align-items: flex-start; gap: 14px; }
  .filter-search    { margin-left: 0; width: 100%; }
  .filter-search__input { width: 100%; }
  .guides-grid      { grid-template-columns: 1fr; }
  .guide-card--wide { grid-column: auto; }
  .guides-newsletter__form { flex-direction: column; }
  .newsletter-btn   { width: 100%; }
  .guide-modal__hero { height: 240px; }
  .guide-modal__content { padding: 20px; }
}
</style>