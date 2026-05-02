<template>
  <div class="dest-things" v-if="thingsToDo?.length">
    <h3 class="dest-things__title">Top things to do</h3>
    <div class="dest-things-slider">
      <div class="dest-thing-card" v-for="(t, idx) in thingsToDo" :key="t.title">
        <div class="dest-thing-card__img">
          <img :src="t.img || `https://picsum.photos/id/${(idx + 1) * 12}/300/200`" :alt="t.title" loading="lazy" />
          <div class="dest-thing-card__icon">{{ t.icon }}</div>
        </div>
        <div class="dest-thing-card__body">
          <div class="dest-thing-card__title">{{ t.title }}</div>
          <div class="dest-thing-card__desc">{{ t.desc }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  thingsToDo: { type: Array, default: () => [] }
})
</script>

<style scoped>
.dest-things { margin: 60px 0; }
.dest-things__title {
  font-family: 'Fraunces', serif; font-size: 1.6rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 24px;
}

.dest-things-slider {
  display: flex; overflow-x: auto; scroll-snap-type: x mandatory; gap: 20px;
  padding-bottom: 20px; scrollbar-width: none;
}
.dest-things-slider::-webkit-scrollbar { display: none; }

.dest-thing-card {
  flex: 0 0 300px; scroll-snap-align: start;
  background: var(--white); border-radius: 16px; border: 1px solid var(--gray-200);
  overflow: hidden; display: flex; flex-direction: column;
  box-shadow: var(--shadow-sm); transition: transform 0.3s ease;
}
.dest-thing-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }

.dest-thing-card__img { height: 160px; position: relative; }
.dest-thing-card__img img { width: 100%; height: 100%; object-fit: cover; }
.dest-thing-card__icon {
  position: absolute; bottom: -18px; right: 20px;
  width: 42px; height: 42px; border-radius: 50%; background: var(--white);
  box-shadow: var(--shadow); display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; z-index: 2; border: 2px solid var(--white);
}
.dest-thing-card__body { padding: 24px 20px 20px; flex: 1; display: flex; flex-direction: column; }
.dest-thing-card__title { font-weight: 700; font-size: 1.05rem; color: var(--indigo); margin-bottom: 8px; }
.dest-thing-card__desc  { font-size: .88rem; color: var(--gray-500); line-height: 1.6; flex: 1; }

@media (max-width: 640px) {
  .dest-thing-card { flex: 0 0 260px; }
}
</style>
