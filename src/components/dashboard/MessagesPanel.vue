<template>
  <div class="dash-card" id="messages">
    <div class="dash-card__header">
      <div>
        <div class="dash-card__overline">Inbox</div>
        <h2 class="dash-card__title">Messages
          <span class="unread-badge" v-if="unreadCount">{{ unreadCount }}</span>
        </h2>
      </div>
      <button class="btn btn-outline dash-card__btn" @click="$emit('compose')">+ Compose</button>
    </div>

    <div class="messages-list">
      <div
        class="message-row"
        :class="{ unread: !msg.read }"
        v-for="msg in messages"
        :key="msg.messageID"
        @click="$emit('open', msg)"
      >
        <div class="msg-avatar">{{ msg.from[0] }}</div>
        <div class="msg-body">
          <div class="msg-top">
            <span class="msg-from">{{ msg.from }}</span>
            <span class="msg-date">{{ msg.date }}</span>
          </div>
          <div class="msg-title">{{ msg.title }}</div>
          <div class="msg-preview">{{ msg.content }}</div>
        </div>
        <div class="msg-unread-dot" v-if="!msg.read" />
      </div>
    </div>

    <div class="dash-card__footer">
      <span class="dash-card__count">{{ messages.length }} messages</span>
      <a href="#" class="see-all">View all →</a>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  messages: { type: Array, default: () => [] },
})
defineEmits(['open', 'compose'])

const unreadCount = computed(() => props.messages.filter(m => !m.read).length)
</script>

<style scoped>
.dash-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 28px; overflow: hidden; }
.dash-card__header { display: flex; align-items: flex-start; justify-content: space-between; padding: 24px 24px 0; gap: 16px; flex-wrap: wrap; }
.dash-card__overline { font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--teal); margin-bottom: 4px; }
.dash-card__title { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
.dash-card__btn { padding: 8px 18px; font-size: .84rem; }
.dash-card__footer { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-top: 1px solid var(--gray-100); }
.dash-card__count { font-size: .82rem; color: var(--gray-400); }

.unread-badge {
  background: var(--coral); color: #fff;
  font-size: .68rem; font-weight: 700; padding: 2px 8px;
  border-radius: 50px; font-family: 'DM Sans', sans-serif;
}

.messages-list { padding: 16px 0; }

.message-row {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 14px 24px; cursor: pointer; position: relative;
  transition: background var(--transition);
}
.message-row:hover { background: var(--gray-50); }
.message-row.unread { background: rgba(46,196,182,.04); }

.msg-avatar {
  width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--teal), var(--indigo));
  display: flex; align-items: center; justify-content: center;
  font-family: 'Fraunces', serif; font-size: .95rem; font-weight: 700; color: #fff;
}

.msg-body  { flex: 1; min-width: 0; }
.msg-top   { display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px; }
.msg-from  { font-weight: 600; font-size: .88rem; color: var(--indigo); }
.msg-date  { font-size: .75rem; color: var(--gray-400); flex-shrink: 0; }
.msg-title { font-size: .88rem; font-weight: 600; color: var(--indigo); margin-bottom: 3px; }
.message-row.unread .msg-title { color: var(--coral); }
.msg-preview { font-size: .8rem; color: var(--gray-400); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.msg-unread-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--teal); flex-shrink: 0; margin-top: 6px;
}
</style>
