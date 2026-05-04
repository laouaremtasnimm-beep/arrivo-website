<template>
  <div class="collabs-panel">

    <!-- ── Header ── -->
    <div class="panel-header">
      <div class="panel-header__left">
        <h2 class="panel-title">Collaborations</h2>
        <p class="panel-sub">Proposals you've sent and received</p>
      </div>
      <div class="panel-header__tabs">
        <button
          v-for="tab in tabs" :key="tab.key"
          class="tab-btn"
          :class="{ active: activeTab === tab.key }"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
          <span class="tab-count" v-if="countFor(tab.key)">{{ countFor(tab.key) }}</span>
        </button>
      </div>
    </div>

    <!-- ── Loading ── -->
    <div v-if="loading" class="state-box">
      <span class="big-spinner"></span>
      <p>Loading collaborations…</p>
    </div>

    <!-- ── Empty ── -->
    <div v-else-if="filtered.length === 0" class="state-box state-box--empty">
      <div class="empty-icon">🤝</div>
      <p class="empty-title">No {{ activeTab }} collaborations yet</p>
      <p class="empty-sub" v-if="activeTab === 'pending'">
        Browse services and click "Propose Collaboration" to get started.
      </p>
    </div>

    <!-- ── List ── -->
    <div v-else class="collab-list">
      <TransitionGroup name="collab-item">
        <CollabCard
          v-for="collab in filtered"
          :key="collab.collabID || collab.id"
          :collab="collab"
          @accept="$emit('accept', $event)"
          @decline="$emit('decline', $event)"
          @counter="$emit('counter', $event)"
        />
      </TransitionGroup>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import CollabCard from '@/components/dashboard/CollabRequestCard.vue'

const props = defineProps({
  userId:         { type: [Number, String], default: '' },
  collaborations: { type: Array,            default: () => [] },
  loading:        { type: Boolean,          default: false }
})

defineEmits(['accept', 'decline', 'counter'])

const activeTab = ref('pending')
const tabs = [
  { key: 'pending',   label: 'Pending'   },
  { key: 'countered', label: 'Countered' },
  { key: 'accepted',  label: 'Accepted'  },
  { key: 'declined',  label: 'Declined'  },
]

// ─── Derived ──────────────────────────────────────────────────────────────────
const filtered = computed(() =>
  props.collaborations.filter(c => c.status === activeTab.value)
)

function countFor(key) {
  return props.collaborations.filter(c => c.status === key).length
}

</script>

<style scoped>
.collabs-panel { display: flex; flex-direction: column; gap: 20px; }

/* ── Header ─────────────────────────────────────────────────────────────────── */
.panel-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  flex-wrap: wrap; gap: 12px;
}
.panel-title {
  font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 700;
  color: var(--indigo); margin: 0;
}
.panel-sub { font-size: .82rem; color: var(--gray-600); margin: 2px 0 0; }

.panel-header__tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.tab-btn {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 16px; border-radius: 50px; font-size: .82rem; font-weight: 600;
  cursor: pointer; border: 1.5px solid var(--gray-200);
  color: var(--gray-600); background: #fff;
  transition: all .18s ease;
}
.tab-btn:hover { border-color: var(--teal); color: var(--teal); }
.tab-btn.active {
  background: var(--teal); border-color: var(--teal); color: #fff;
}
.tab-count {
  background: rgba(255,255,255,.25); color: inherit;
  font-size: .72rem; font-weight: 800; padding: 1px 7px; border-radius: 20px;
}
.tab-btn:not(.active) .tab-count {
  background: var(--gray-100); color: var(--gray-600);
}

/* ── States ─────────────────────────────────────────────────────────────────── */
.state-box {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 10px; padding: 60px 20px; color: var(--gray-500); text-align: center;
}
.big-spinner {
  width: 32px; height: 32px; border: 3px solid var(--gray-200);
  border-top-color: var(--teal); border-radius: 50%;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.empty-icon  { font-size: 2.4rem; }
.empty-title { font-size: 1rem; font-weight: 600; color: var(--indigo); margin: 0; }
.empty-sub   { font-size: .84rem; margin: 0; max-width: 280px; }

/* ── List ───────────────────────────────────────────────────────────────────── */
.collab-list { display: flex; flex-direction: column; gap: 12px; }

/* TransitionGroup */
.collab-item-enter-active, .collab-item-leave-active { transition: all .25s ease; }
.collab-item-enter-from, .collab-item-leave-to { opacity: 0; transform: translateY(8px); }
</style>