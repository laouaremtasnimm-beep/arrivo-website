<template>
  <div class="collab-panel">

    <!-- Topbar -->
    <div class="collab-topbar">
      <div class="collab-tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          class="collab-tab"
          :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
          <span v-if="tab.count" class="tab-count">{{ tab.count }}</span>
        </button>
      </div>
      <button class="btn-new" @click="$emit('open-form')">+ New Collaboration</button>
    </div>

    <!-- INCOMING REQUESTS -->
    <div v-if="activeTab === 'requests'">
      <div v-if="incomingRequests.length" class="card-list">
        <CollabRequestCard
          v-for="req in incomingRequests"
          :key="req.collabID"
          :collab="req"
          @accept="$emit('accept', req)"
          @decline="$emit('decline', req)"
          @counter="payload => $emit('counter', payload)"
        />
      </div>
      <div v-else class="empty-state">
        <div class="empty-icon">📬</div>
        <p class="empty-title">No incoming requests</p>
        <p class="empty-sub">When a partner sends you a collaboration proposal, it will appear here.</p>
      </div>
    </div>

    <!-- SENT REQUESTS -->
    <div v-else-if="activeTab === 'sent'">
      <div v-if="sentRequests.length" class="card-list">
        <CollabRequestCard
          v-for="req in sentRequests"
          :key="req.collabID"
          :collab="req"
          @accept="() => {}"
          @decline="() => {}"
          @counter="() => {}"
        />
      </div>
      <div v-else class="empty-state">
        <div class="empty-icon">📤</div>
        <p class="empty-title">No sent requests</p>
        <p class="empty-sub">Use "New Collaboration" to invite a partner.</p>
      </div>
    </div>

    <!-- ACTIVE COLLABORATIONS -->
    <div v-else-if="activeTab === 'active'">
      <div v-if="activeCollabs.length" class="offer-grid">
        <CollabOfferCard
          v-for="c in activeCollabs"
          :key="c.collabID"
          :collab="c"
          @end="$emit('end', c)"
        />
      </div>
      <div v-else class="empty-state">
        <div class="empty-icon">🤝</div>
        <p class="empty-title">No active collaborations yet</p>
        <p class="empty-sub">Once a partner accepts your request, the joint offer will appear here.</p>
        <button class="btn-new btn-new--center" @click="$emit('open-form')">
          Propose your first collaboration
        </button>
      </div>
    </div>

    <!-- ARCHIVE -->
    <div v-else-if="activeTab === 'archive'">
      <div v-if="archivedCollabs.length" class="card-list">
        <CollabRequestCard
          v-for="req in archivedCollabs"
          :key="req.collabID"
          :collab="req"
          @accept="() => {}"
          @decline="() => {}"
          @counter="() => {}"
        />
      </div>
      <div v-else class="empty-state">
        <div class="empty-icon">🗂️</div>
        <p class="empty-title">No archived collaborations</p>
        <p class="empty-sub">Ended and declined collaborations are stored here.</p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import CollabRequestCard from '@/components/dashboard/CollabRequestCard.vue'
import CollabOfferCard   from '@/components/dashboard/CollabOfferCard.vue'

const props = defineProps({
  collaborations: { type: Array, default: () => [] },
})
defineEmits(['open-form', 'accept', 'decline', 'counter', 'end'])

const activeTab = ref('requests')

const incomingRequests = computed(() =>
  props.collaborations.filter(c => c.direction === 'incoming' && c.status === 'pending')
)
const sentRequests = computed(() =>
  props.collaborations.filter(c => c.direction === 'outgoing')
)
const activeCollabs = computed(() =>
  props.collaborations.filter(c => c.status === 'accepted')
)
const archivedCollabs = computed(() =>
  props.collaborations.filter(c => c.status === 'declined' || c.status === 'ended' || c.status === 'countered')
)

const tabs = computed(() => [
  { id: 'requests', label: 'Incoming', count: incomingRequests.value.length || null },
  { id: 'sent',     label: 'Sent',     count: null },
  { id: 'active',   label: 'Active',   count: activeCollabs.value.length || null },
  { id: 'archive',  label: 'Archive',  count: null },
])
</script>

<style scoped>
.collab-panel { display: flex; flex-direction: column; gap: 20px; }

.collab-topbar {
  display: flex; align-items: center; justify-content: space-between;
  gap: 12px; flex-wrap: wrap;
}
.collab-tabs { display: flex; gap: 4px; background: var(--gray-100); border-radius: 50px; padding: 4px; }
.collab-tab {
  padding: 7px 18px; border-radius: 50px; border: none; background: transparent;
  font-size: .84rem; font-weight: 600; color: var(--gray-600);
  cursor: pointer; transition: all var(--transition);
  display: flex; align-items: center; gap: 6px; font-family: 'DM Sans', sans-serif;
}
.collab-tab.active { background: #fff; color: var(--indigo); box-shadow: 0 2px 8px rgba(45,49,66,.1); }
.tab-count {
  background: var(--coral); color: #fff; font-size: .68rem;
  min-width: 18px; height: 18px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; padding: 0 4px;
}
.btn-new {
  background: var(--indigo); color: #fff; border: none;
  padding: 9px 22px; border-radius: 50px; font-size: .85rem;
  font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif;
  transition: background var(--transition); white-space: nowrap;
}
.btn-new:hover { background: #3d4460; }
.btn-new--center { margin-top: 12px; }

.card-list  { display: flex; flex-direction: column; gap: 16px; }
.offer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }

.empty-state {
  text-align: center; padding: 60px 20px;
  display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.empty-icon  { font-size: 2.5rem; }
.empty-title { font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: var(--indigo); margin: 0; }
.empty-sub   { font-size: .85rem; color: var(--gray-600); max-width: 320px; line-height: 1.55; margin: 0; }
</style>