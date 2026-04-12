<template>
  <div class="dest-highlights">

    <!-- Quick facts strip -->
    <div class="dest-facts">
      <div class="dest-fact" v-for="f in facts" :key="f.label">
        <span class="dest-fact__icon">{{ f.icon }}</span>
        <div>
          <div class="dest-fact__label">{{ f.label }}</div>
          <div class="dest-fact__val">{{ f.value }}</div>
        </div>
      </div>
    </div>

    <!-- About -->
    <div class="dest-section">
      <h3 class="dest-section__title">About {{ name }}</h3>
      <p class="dest-section__text">{{ longDesc }}</p>
    </div>

    <!-- Highlights tags -->
    <div class="dest-section">
      <h3 class="dest-section__title">Highlights</h3>
      <div class="dest-tags">
        <span class="dest-tag" v-for="h in highlights" :key="h">{{ h }}</span>
      </div>
    </div>

    <!-- Things to do -->
    <div class="dest-section">
      <h3 class="dest-section__title">Top things to do</h3>
      <div class="dest-things">
        <div class="dest-thing" v-for="t in thingsToDo" :key="t.title">
          <div class="dest-thing__icon">{{ t.icon }}</div>
          <div>
            <div class="dest-thing__title">{{ t.title }}</div>
            <div class="dest-thing__desc">{{ t.desc }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Best time -->
    <div class="dest-section dest-best-time">
      <div class="dest-best-time__icon">🗓️</div>
      <div>
        <div class="dest-best-time__label">Best time to visit</div>
        <div class="dest-best-time__value">{{ bestTime }}</div>
      </div>
    </div>

  </div>
</template>

<script setup>
defineProps({
  name:        { type: String, required: true },
  longDesc:    { type: String, default: ''    },
  highlights:  { type: Array,  default: () => [] },
  thingsToDo:  { type: Array,  default: () => [] },
  facts:       { type: Array,  default: () => [] },
  bestTime:    { type: String, default: ''    },
})
</script>

<style scoped>
/* Quick facts */
.dest-facts {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px; margin-bottom: 36px;
}
.dest-fact {
  background: var(--gray-50); border-radius: 12px; padding: 16px;
  display: flex; align-items: center; gap: 12px;
}
.dest-fact__icon  { font-size: 1.3rem; flex-shrink: 0; }
.dest-fact__label { font-size: .72rem; color: var(--gray-400); font-weight: 500; margin-bottom: 3px; }
.dest-fact__val   { font-size: .9rem; font-weight: 600; color: var(--indigo); }

/* Sections */
.dest-section       { margin-bottom: 36px; }
.dest-section__title{
  font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700;
  color: var(--indigo); margin-bottom: 16px;
}
.dest-section__text { font-size: .95rem; color: var(--gray-600); line-height: 1.75; }

/* Tags */
.dest-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.dest-tag  {
  background: rgba(46,196,182,.10); color: var(--teal-dk);
  font-size: .82rem; font-weight: 700; padding: 6px 14px; border-radius: 50px;
}

/* Things to do */
.dest-things { display: flex; flex-direction: column; gap: 16px; }
.dest-thing  { display: flex; align-items: flex-start; gap: 16px; }
.dest-thing__icon {
  width: 44px; height: 44px; border-radius: 12px; background: var(--sand);
  display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;
}
.dest-thing__title { font-weight: 700; font-size: .92rem; color: var(--indigo); margin-bottom: 4px; }
.dest-thing__desc  { font-size: .84rem; color: var(--gray-600); line-height: 1.55; }

/* Best time */
.dest-best-time {
  display: flex; align-items: center; gap: 16px;
  background: var(--sand); border-radius: 14px; padding: 18px 20px;
}
.dest-best-time__icon  { font-size: 1.6rem; }
.dest-best-time__label { font-size: .78rem; color: var(--gray-600); font-weight: 500; }
.dest-best-time__value { font-size: 1rem; font-weight: 700; color: var(--indigo); margin-top: 3px; }
</style>