<template>
  <div
    class="notif-item"
    :class="[`notif-item--${notification.type}`, { 'notif-item--unread': !notification.read }]"
    @click="handleClick"
  >
    <!-- Unread dot -->
    <div class="notif-item__dot" v-if="!notification.read" />

    <!-- Icon -->
    <div class="notif-item__icon" :class="`icon-type--${notification.type}`">
      {{ notification.icon }}
    </div>

    <!-- Content -->
    <div class="notif-item__body">
      <div class="notif-item__title">{{ notification.title }}</div>
      <div class="notif-item__text">{{ notification.body }}</div>
      <div class="notif-item__time">{{ notification.time }}</div>
    </div>

  </div>
</template>

<script setup>
const props = defineProps({
  notification: { type: Object, required: true },
})
const emit = defineEmits(['click', 'delete'])

function handleClick() {
  emit('click', props.notification)
}
</script>

<style scoped>
.notif-item {
  display: flex; align-items: flex-start; gap: 12px;
  padding: 12px 16px; cursor: pointer;
  transition: background var(--transition);
  position: relative; border-bottom: 1px solid var(--gray-100);
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--gray-50); }
.notif-item--unread { background: rgba(255,90,95,.03); }
.notif-item--unread:hover { background: rgba(255,90,95,.06); }

/* Unread indicator dot */
.notif-item__dot {
  position: absolute; left: 6px; top: 50%;
  transform: translateY(-50%);
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--coral); flex-shrink: 0;
}

/* Icon bubble */
.notif-item__icon {
  width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: .95rem;
}
.icon-type--booking { background: rgba(46,196,182,.12); }
.icon-type--collab  { background: rgba(255,90,95,.10);  }
.icon-type--review  { background: rgba(255,193,7,.13);  }
.icon-type--offer   { background: rgba(46,196,182,.10); }
.icon-type--system  { background: var(--gray-100);      }

/* Text */
.notif-item__body   { flex: 1; min-width: 0; }
.notif-item__title  {
  font-size: .84rem; font-weight: 700; color: var(--indigo);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.notif-item__text   {
  font-size: .78rem; color: var(--gray-600); margin-top: 2px;
  line-height: 1.45; display: -webkit-box;
  -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.notif-item__time   { font-size: .72rem; color: var(--gray-400); margin-top: 4px; }

/* Delete button */
.notif-item__del {
  width: 22px; height: 22px; border-radius: 50%; border: none;
  background: transparent; color: var(--gray-400);
  font-size: .68rem; cursor: pointer; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  opacity: 0; transition: all var(--transition);
}
.notif-item:hover .notif-item__del { opacity: 1; }
.notif-item__del:hover { background: var(--gray-200); color: var(--indigo); }
</style>