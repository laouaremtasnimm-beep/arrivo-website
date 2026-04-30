<template>
  <Teleport to="body">
    <Transition name="offer-modal-fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal" role="dialog">

          <!-- Close -->
          <button class="modal__close" @click="close">✕</button>

          <!-- Hero banner -->
          <div class="offer-hero">
            <div class="offer-hero__discount">{{ offer?.discount }}% OFF</div>
            <div class="offer-hero__right">
              <div class="offer-hero__title">{{ offer?.title }}</div>
              <div class="offer-hero__dates" v-if="offer?.startDate">
                📅 {{ offer.startDate }} → {{ offer.endDate }}
              </div>
            </div>
          </div>

          <div class="modal__body">

            <!-- Description a-->
            <p class="offer-desc">{{ offer?.description }}</p>

            <!-- Trust strip -->
            <div class="trust-strip">
              <span class="trust-item"><span>✅</span> Verified deal</span>
              <span class="trust-item"><span>🔒</span> Secure booking</span>
              <span class="trust-item"><span>↩️</span> Free cancellation</span>
            </div>

            <!-- Matched packages -->
            <template v-if="matchedPackages.length">
              <div class="section-label">✈️ Packages included in this offer</div>
              <div class="cards-grid">
                <div
                  class="mini-card"
                  v-for="pkg in matchedPackages"
                  :key="pkg.id"
                  @click="selectPackage(pkg)"
                >
                  <div class="mini-card__img-wrap">
                    <img :src="pkg.img" :alt="pkg.title" class="mini-card__img" />
                    <div class="mini-card__discount-pill">-{{ offer?.discount }}%</div>
                  </div>
                  <div class="mini-card__body">
                    <div class="mini-card__type">{{ pkg.type }}</div>
                    <div class="mini-card__title">{{ pkg.title }}</div>
                    <div class="mini-card__meta">
                      <span>{{ pkg.duration }} days</span>
                      <span class="mini-card__dot">·</span>
                      <span class="star">★</span> {{ pkg.rating }}
                    </div>
                    <div class="mini-card__prices">
                      <span class="mini-card__original">${{ pkg.price?.toLocaleString() }}</span>
                      <span class="mini-card__discounted">${{ discounted(pkg.price) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Matched services -->
            <template v-if="matchedServices.length">
              <div class="section-label">🛎️ Services in this offer</div>
              <div class="services-list">
                <div
                  class="svc-row"
                  v-for="svc in matchedServices"
                  :key="svc.id"
                  @click="selectService(svc)"
                >
                  <div class="svc-row__icon">{{ svc.icon }}</div>
                  <div class="svc-row__body">
                    <div class="svc-row__title">{{ svc.title }}</div>
                    <div class="svc-row__provider">{{ svc.provider }}</div>
                  </div>
                  <div class="svc-row__prices">
                    <span class="svc-row__original">${{ svc.price }}</span>
                    <span class="svc-row__discounted">${{ discounted(svc.price) }}</span>
                    <span class="svc-row__unit">/ {{ svc.unit }}</span>
                  </div>
                </div>
              </div>
            </template>

            <!-- Fallback if nothing matched -->
            <div class="offer-fallback" v-if="!matchedPackages.length && !matchedServices.length">
              <p>This is a limited-time promotional offer. Browse our packages and services to find your perfect match.</p>
              <div class="fallback-actions">
                <button class="btn-browse" @click="browsePackages">Browse packages</button>
                <button class="btn-browse btn-browse--outline" @click="browseServices">Browse services</button>
              </div>
            </div>

          </div>

          <!-- Footer CTA -->
          <div class="modal__footer">
            <div class="footer-note">🔥 Limited time — offer ends {{ offer?.endDate }}</div>
            <button 
              class="btn-book" 
              :class="{'btn-outline-danger': alreadyBooked}"
              @click="alreadyBooked ? handleCancel() : bookDeal()"
            >
              {{ alreadyBooked ? 'Cancel Booking' : 'Book this deal' }}
            </button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Reuse existing BookingModal for the actual booking step -->
  <BookingModal
    v-model="bookingOpen"
    :pkg="bookingTarget"
    @submit="handleBookingSubmit"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { packages, services } from '@/data/content.js'
import BookingModal from '@/components/home/BookingModal.vue'

import { useAuth } from '@/composables/useAuth'
import { useBookings } from '@/composables/useBookings'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  offer:      { type: Object,  default: null  },
})
const emit = defineEmits(['update:modelValue'])

const router       = useRouter()
const { user, isLoggedIn } = useAuth()
const { createBooking, isBooked, getBookingId, cancelBooking } = useBookings()
const bookingOpen  = ref(false)
const bookingTarget = ref(null)

const alreadyBooked = computed(() => {
  return props.offer ? isBooked('offer', props.offer.offerID || props.offer.id) : false
})

// ── Match packages to this offer by keyword in title ────────────────────
const matchedPackages = computed(() => {
  if (!props.offer) return []
  const keywords = offerKeywords(props.offer.title)
  if (!keywords.length) return packages.slice(0, 3)
  return packages.filter(p =>
    keywords.some(kw =>
      p.title.toLowerCase().includes(kw) ||
      p.type.toLowerCase().includes(kw)  ||
      (p.agency || '').toLowerCase().includes(kw)
    )
  ).slice(0, 3)
})

// ── Match services similarly ──────────────────────────────────────────────
const matchedServices = computed(() => {
  if (!props.offer) return []
  const keywords = offerKeywords(props.offer.title)
  if (!keywords.length) return []
  return services.filter(s =>
    keywords.some(kw =>
      s.title.toLowerCase().includes(kw) ||
      s.type.toLowerCase().includes(kw)
    )
  ).slice(0, 3)
})

// Extract meaningful keywords from offer title
function offerKeywords(title) {
  if (!title) return []
  const stop = new Set(['the','a','an','and','or','in','of','for','to','on','at','&'])
  return title.toLowerCase()
    .replace(/[^a-z\s]/g, '')
    .split(/\s+/)
    .filter(w => w.length > 2 && !stop.has(w))
}

function discounted(price) {
  if (!price || !props.offer?.discount) return price
  return Math.round(price * (1 - props.offer.discount / 100)).toLocaleString()
}

function selectPackage(pkg) {
  close()
  router.push(`/packages/${pkg.id}`)
}

function selectService(svc) {
  close()
  router.push(`/services/${svc.id}`)
}

function browsePackages() { close(); router.push('/packages') }
function browseServices()  { close(); router.push('/services') }

function bookDeal() {
  // Book with the first matched package, or a synthetic offer object
  bookingTarget.value = matchedPackages.value[0] ?? {
    title:    props.offer?.title,
    img:      null,
    duration: null,
    agency:   'Voyago Partner',
    price:    null,
  }
  bookingOpen.value = true
}

async function handleBookingSubmit(payload) {
  if (!isLoggedIn.value) { alert('Please log in to book.'); return }

  const basePrice = bookingTarget.value.price || 0
  const discount = props.offer?.discount || 0
  const finalPrice = Math.round(basePrice * (1 - discount / 100))

  const result = await createBooking({
    user_id:  user.value?.userID ?? user.value?.id,
    type:     'offer',
    item_id:  props.offer?.offerID || props.offer?.id,
    title:    props.offer?.title,
    price:    finalPrice,
    check_in: payload.checkin,
    guests:   parseInt(payload.guests) || 1,
    notes:    payload.notes,
  })

  if (result.ok) {
    bookingOpen.value = false
    close()
    alert('Offer booked successfully!')
    router.push('/bookings')
  } else {
    alert('Failed to book offer: ' + result.error)
  }
}

async function handleCancel() {
  if (!confirm('Are you sure you want to cancel this booking?')) return
  const id = getBookingId('offer', props.offer?.offerID || props.offer?.id)
  if (!id) return
  const res = await cancelBooking(id)
  if (res.ok) {
    alert('Booking cancelled successfully.')
    close()
  } else {
    alert('Failed to cancel: ' + res.error)
  }
}

function close() { emit('update:modelValue', false) }
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(45,49,66,.50);
  z-index: 200; display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.modal {
  background: var(--white); border-radius: 24px;
  width: 100%; max-width: 640px;
  box-shadow: 0 24px 80px rgba(45,49,66,.22);
  max-height: 90vh; display: flex; flex-direction: column;
  position: relative; overflow: hidden;
}
.modal__close {
  position: absolute; top: 16px; right: 16px; z-index: 2;
  width: 34px; height: 34px; border-radius: 50%; border: none;
  background: rgba(255,255,255,.9); cursor: pointer; font-size: .9rem;
  color: var(--gray-600); display: flex; align-items: center; justify-content: center;
  transition: background var(--transition); box-shadow: 0 2px 8px rgba(0,0,0,.12);
}
.modal__close:hover { background: #fff; }

/* Hero banner */
.offer-hero {
  background: linear-gradient(135deg, var(--indigo) 0%, #3d4460 100%);
  padding: 32px 28px; display: flex; align-items: center; gap: 20px;
  flex-shrink: 0;
}
.offer-hero__discount {
  font-family: 'Fraunces', serif; font-size: 3rem; font-weight: 700;
  color: var(--coral); line-height: 1; flex-shrink: 0;
}
.offer-hero__title {
  font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: #fff;
  margin-bottom: 6px;
}
.offer-hero__dates { font-size: .82rem; color: rgba(255,255,255,.6); }

/* Body */
.modal__body {
  flex: 1; overflow-y: auto; padding: 24px 28px;
  display: flex; flex-direction: column; gap: 20px;
  scrollbar-width: thin; scrollbar-color: var(--gray-200) transparent;
}
.offer-desc { font-size: .92rem; color: var(--gray-600); line-height: 1.65; margin: 0; }

/* Trust strip */
.trust-strip {
  display: flex; gap: 16px; flex-wrap: wrap;
  padding: 12px 16px; background: var(--gray-50);
  border-radius: var(--radius-sm);
}
.trust-item {
  display: flex; align-items: center; gap: 6px;
  font-size: .8rem; color: var(--gray-600);
}

/* Section labels */
.section-label {
  font-size: .78rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--gray-600);
}

/* Package mini cards */
.cards-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 12px;
}
.btn-book {
  padding: 14px 28px; font-family: 'Fraunces', serif; font-size: 1.1rem;
  font-weight: 700; background: var(--coral); color: #fff; border: none;
  border-radius: 14px; cursor: pointer; transition: all var(--transition);
}
.btn-book:hover { background: #ff4a50; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(255,90,95,.3); }

.btn-book.btn-outline-danger {
  background: transparent;
  border: 1.5px solid var(--coral);
  color: var(--coral);
}
.btn-book.btn-outline-danger:hover {
  background: rgba(255, 90, 95, 0.1);
}
.mini-card {
  background: var(--white); border-radius: var(--radius-sm);
  border: 1.5px solid var(--gray-200); overflow: hidden;
  cursor: pointer; transition: all var(--transition);
}
.mini-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--teal); }
.mini-card__img-wrap { position: relative; height: 100px; overflow: hidden; }
.mini-card__img { width: 100%; height: 100%; object-fit: cover; }
.mini-card__discount-pill {
  position: absolute; top: 8px; right: 8px;
  background: var(--coral); color: #fff;
  font-size: .68rem; font-weight: 800; padding: 3px 8px; border-radius: 50px;
}
.mini-card__body { padding: 10px 12px; }
.mini-card__type {
  font-size: .68rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .05em; color: var(--teal-dk); margin-bottom: 3px;
}
.mini-card__title { font-size: .82rem; font-weight: 700; color: var(--indigo); line-height: 1.3; margin-bottom: 4px; }
.mini-card__meta  { font-size: .72rem; color: var(--gray-400); display: flex; align-items: center; gap: 4px; margin-bottom: 6px; }
.mini-card__dot   { color: var(--gray-200); }
.star             { color: #FFB400; }
.mini-card__prices { display: flex; align-items: baseline; gap: 6px; }
.mini-card__original   { font-size: .76rem; color: var(--gray-400); text-decoration: line-through; }
.mini-card__discounted { font-size: .9rem; font-weight: 700; color: var(--coral); }

/* Services list */
.services-list { display: flex; flex-direction: column; gap: 8px; }
.svc-row {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; background: var(--gray-50); border-radius: var(--radius-sm);
  border: 1.5px solid var(--gray-200); cursor: pointer;
  transition: all var(--transition);
}
.svc-row:hover { border-color: var(--teal); background: var(--teal-lt); }
.svc-row__icon { font-size: 1.3rem; flex-shrink: 0; }
.svc-row__body { flex: 1; min-width: 0; }
.svc-row__title    { font-size: .86rem; font-weight: 700; color: var(--indigo); }
.svc-row__provider { font-size: .74rem; color: var(--gray-400); }
.svc-row__prices   { display: flex; align-items: baseline; gap: 4px; flex-shrink: 0; }
.svc-row__original    { font-size: .76rem; color: var(--gray-400); text-decoration: line-through; }
.svc-row__discounted  { font-size: .92rem; font-weight: 700; color: var(--coral); }
.svc-row__unit        { font-size: .72rem; color: var(--gray-400); }

/* Fallback */
.offer-fallback { text-align: center; padding: 12px 0; }
.offer-fallback p { font-size: .88rem; color: var(--gray-600); margin-bottom: 16px; }
.fallback-actions { display: flex; gap: 10px; justify-content: center; }
.btn-browse {
  background: var(--indigo); color: #fff; border: none;
  padding: 9px 22px; border-radius: 50px; font-size: .84rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition);
}
.btn-browse:hover { background: #3d4460; }
.btn-browse--outline {
  background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-600);
}
.btn-browse--outline:hover { border-color: var(--indigo); color: var(--indigo); background: transparent; }

/* Footer */
.modal__footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 28px; border-top: 1px solid var(--gray-100);
  flex-shrink: 0; gap: 12px; flex-wrap: wrap;
}
.footer-note { font-size: .78rem; color: var(--gray-400); }
.btn-book {
  background: var(--coral); color: #fff; border: none;
  padding: 12px 28px; border-radius: 50px; font-size: .92rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition); white-space: nowrap;
}
.btn-book:hover { background: var(--coral-dk); }

/* Transition */
.offer-modal-fade-enter-active, .offer-modal-fade-leave-active {
  transition: opacity .22s ease, transform .22s ease;
}
.offer-modal-fade-enter-from, .offer-modal-fade-leave-to {
  opacity: 0; transform: scale(.96) translateY(8px);
}

@media (max-width: 520px) {
  .modal { border-radius: 20px 20px 0 0; position: fixed; bottom: 0; left: 0; right: 0; max-height: 88vh; }
  .modal-backdrop { align-items: flex-end; padding: 0; }
  .offer-hero { padding: 24px 20px; }
  .offer-hero__discount { font-size: 2.2rem; }
  .modal__body { padding: 20px; }
  .modal__footer { padding: 14px 20px; }
}
</style>