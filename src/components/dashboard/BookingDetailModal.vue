<template>
  <Teleport to="body">
    <Transition name="fade">
      <div class="modal-backdrop" v-if="modelValue" @click.self="close">
        <div class="modal">
          <button class="modal__close" @click="close">✕</button>

          <div class="modal__header">
            <div class="modal__icon-wrap">
              <span class="modal__icon">{{ typeIcon }}</span>
            </div>
            <div>
              <h2 class="modal__title">Booking Details</h2>
              <p class="modal__sub">Reservation #{{ booking.reservationID || booking.id }}</p>
            </div>
          </div>

          <div class="modal__body">
            <!-- Item Info -->
            <div class="detail-section">
              <h3 class="section-title">Booked Item</h3>
              <div class="item-preview">
                <div class="item-img" v-if="booking.img_url || booking.img">
                  <img :src="booking.img_url || booking.img" :alt="booking.itemName" />
                </div>
                <div class="item-info">
                  <div class="item-name">{{ booking.itemName }}</div>
                  <div class="item-type">{{ booking.booking_type }}</div>
                </div>
              </div>
            </div>

            <!-- Guest Info -->
            <div class="detail-grid">
              <div class="detail-section">
                <h3 class="section-title">Guest Information</h3>
                <div class="info-group">
                  <label>Name</label>
                  <div>{{ booking.guestName || (booking.guest_first ? `${booking.guest_first} ${booking.guest_last}` : 'Guest') }}</div>
                </div>
                <div class="info-group" v-if="booking.guest_email">
                  <label>Email</label>
                  <div>{{ booking.guest_email }}</div>
                </div>
              </div>

              <div class="detail-section">
                <h3 class="section-title">Stay Details</h3>
                <div class="info-group">
                  <label>Date / Check-in</label>
                  <div>{{ formatDate(booking.date || booking.check_in) }}</div>
                </div>
                <div class="info-group">
                  <label>Travelers</label>
                  <div>{{ booking.guests || 1 }} person(s)</div>
                </div>
              </div>
            </div>

            <!-- Payment -->
            <div class="detail-section">
              <div class="payment-card">
                <div class="payment-row">
                  <span>Total Amount</span>
                  <span class="payment-price">${{ Number(booking.totalPrice || booking.total_price || 0).toLocaleString() }}</span>
                </div>
                <div class="payment-row status-row">
                  <span>Status</span>
                  <span class="status-pill" :class="`status--${booking.status}`">{{ booking.status }}</span>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div class="detail-section" v-if="booking.notes">
              <h3 class="section-title">Special Requests / Notes</h3>
              <div class="notes-box">
                {{ booking.notes }}
              </div>
            </div>
          </div>

          <div class="modal__footer">
            <button class="btn btn-outline" @click="close">Close</button>
            <button 
              v-if="booking.status === 'pending'"
              class="btn btn-teal" 
              @click="$emit('confirm', booking); close()"
            >
              Confirm Booking
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  booking:    { type: Object,  default: () => ({}) }
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const close = () => emit('update:modelValue', false)

const typeIcon = computed(() => {
  const type = props.booking.booking_type
  if (type === 'package')     return '✈️'
  if (type === 'service')     return '🎯'
  if (type === 'destination') return '📍'
  if (type === 'offer')       return '🏷️'
  return '📋'
})

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px); z-index: 2000;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal {
  background: var(--white); border-radius: 24px;
  width: 100%; max-width: 540px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex; flex-direction: column; max-height: 90vh;
  position: relative; animation: slideUp 0.3s ease-out;
}
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.modal__close {
  position: absolute; top: 20px; right: 20px;
  width: 32px; height: 32px; border: none; border-radius: 50%;
  background: var(--gray-50); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.9rem; color: var(--gray-500); transition: all 0.2s;
}
.modal__close:hover { background: var(--gray-100); color: var(--indigo); }

.modal__header { padding: 32px 32px 24px; display: flex; gap: 20px; align-items: center; border-bottom: 1px solid var(--gray-100); }
.modal__icon-wrap {
  width: 56px; height: 56px; border-radius: 16px;
  background: var(--sand); display: flex; align-items: center; justify-content: center;
  font-size: 1.8rem; flex-shrink: 0;
}
.modal__title { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; color: var(--indigo); margin-bottom: 4px; }
.modal__sub { font-size: 0.88rem; color: var(--gray-400); font-weight: 500; }

.modal__body { padding: 24px 32px; overflow-y: auto; flex: 1; }

.detail-section { margin-bottom: 24px; }
.section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-400); margin-bottom: 12px; }

.item-preview { display: flex; gap: 16px; align-items: center; background: var(--gray-50); padding: 12px; border-radius: 16px; }
.item-img { width: 60px; height: 60px; border-radius: 12px; overflow: hidden; flex-shrink: 0; }
.item-img img { width: 100%; height: 100%; object-fit: cover; }
.item-name { font-weight: 700; color: var(--indigo); font-size: 1rem; }
.item-type { font-size: 0.8rem; color: var(--teal); font-weight: 600; text-transform: capitalize; }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.info-group { margin-bottom: 12px; }
.info-group label { display: block; font-size: 0.75rem; color: var(--gray-400); margin-bottom: 2px; }
.info-group div { font-weight: 600; color: var(--indigo); font-size: 0.95rem; }

.payment-card { background: var(--indigo); border-radius: 16px; padding: 20px; color: #fff; }
.payment-row { display: flex; justify-content: space-between; align-items: center; }
.payment-price { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: var(--coral); }
.status-row { margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1); }

.status-pill { padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
.status--confirmed { background: rgba(39, 174, 96, 0.2); color: #4ade80; }
.status--pending { background: rgba(243, 156, 18, 0.2); color: #fbbf24; }
.status--cancelled { background: rgba(231, 76, 60, 0.2); color: #f87171; }

.notes-box { background: #fffcf0; border: 1px solid #f9ebbe; border-radius: 12px; padding: 16px; font-size: 0.9rem; color: #856404; line-height: 1.5; font-style: italic; }

.modal__footer { padding: 20px 32px; border-top: 1px solid var(--gray-100); display: flex; justify-content: flex-end; gap: 12px; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.btn { padding: 10px 24px; border-radius: 50px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; }
.btn-outline { background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-600); }
.btn-outline:hover { background: var(--gray-50); border-color: var(--gray-300); }
.btn-teal { background: var(--teal); border: none; color: #fff; }
.btn-teal:hover { opacity: 0.9; transform: translateY(-1px); }
</style>
