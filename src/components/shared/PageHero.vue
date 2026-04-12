<template>
  <div class="page-hero" :style="heroStyle">
    <div class="page-hero__bg" aria-hidden="true" />

    <div class="page-hero__content">
      <div class="page-hero__tag">
        <span class="page-hero__tag-icon">{{ icon }}</span>
        {{ tagline }}
      </div>

      <h1 class="page-hero__title">{{ title }}</h1>
      <p class="page-hero__sub">{{ subtitle }}</p>

      <!-- Inline search -->
      <div class="page-hero__search">
        <span class="page-hero__search-icon">🔍</span>
        <input
          class="page-hero__search-input"
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          @keyup.enter="$emit('search')"
          :placeholder="searchPlaceholder"
        />
        <button class="btn btn-coral page-hero__search-btn" @click="$emit('search')">
          Search
        </button>
      </div>

      <!-- Stat pills -->
      <div class="page-hero__stats">
        <div class="page-hero__stat" v-for="s in stats" :key="s.label">
          <span class="page-hero__stat-icon">{{ s.icon }}</span>
          <strong>{{ s.value }}</strong> {{ s.label }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title:            { type: String, required: true },
  subtitle:         { type: String, default: ''    },
  tagline:          { type: String, default: ''    },
  icon:             { type: String, default: '🌐'  },
  searchPlaceholder:{ type: String, default: 'Search…' },
  stats:            { type: Array,  default: () => [] },
  modelValue:       { type: String, default: ''    },
  // 'teal' | 'coral' | 'sand'
  color:            { type: String, default: 'teal' },
})

defineEmits(['update:modelValue', 'search'])

const heroStyle = computed(() => {
  const gradients = {
    teal:  'linear-gradient(135deg, rgba(46,196,182,.12) 0%, rgba(244,235,208,.3) 100%)',
    coral: 'linear-gradient(135deg, rgba(255,90,95,.08) 0%, rgba(244,235,208,.35) 100%)',
    sand:  'linear-gradient(135deg, rgba(244,235,208,.6) 0%, rgba(255,255,255,.2) 100%)',
  }
  return { background: gradients[props.color] || gradients.teal }
})
</script>

<style scoped>
.page-hero {
  padding: 96px 5% 56px;
  position: relative; overflow: hidden;
}

.page-hero__bg {
  position: absolute; inset: 0; z-index: 0;
  background-image:
    radial-gradient(ellipse 50% 60% at 90% 20%, rgba(46,196,182,.07) 0%, transparent 70%),
    radial-gradient(ellipse 40% 50% at 5%  80%, rgba(255,90,95,.05) 0%, transparent 60%);
  pointer-events: none;
}

.page-hero__content {
  position: relative; z-index: 1;
  max-width: 680px;
}

.page-hero__tag {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--white); border: 1px solid var(--gray-200);
  font-size: .78rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
  color: var(--indigo); padding: 6px 16px; border-radius: 50px;
  margin-bottom: 20px; box-shadow: var(--shadow);
}
.page-hero__tag-icon { font-size: 1rem; }

.page-hero__title {
  font-size: clamp(2.2rem, 4vw, 3.4rem);
  font-weight: 700; color: var(--indigo); margin-bottom: 16px;
  line-height: 1.1;
}

.page-hero__sub {
  font-size: 1.05rem; color: var(--gray-600);
  line-height: 1.7; margin-bottom: 36px; max-width: 520px;
}

/* Search bar */
.page-hero__search {
  display: flex; align-items: center;
  background: var(--white); border: 1.5px solid var(--gray-200);
  border-radius: 16px; padding: 6px 6px 6px 20px;
  box-shadow: var(--shadow-md); max-width: 540px; margin-bottom: 32px;
}
.page-hero__search-icon  { font-size: 1rem; margin-right: 10px; flex-shrink: 0; color: var(--gray-400); }
.page-hero__search-input {
  flex: 1; border: none; outline: none; min-width: 0;
  font-family: 'DM Sans', sans-serif; font-size: .95rem; color: var(--indigo);
  background: transparent;
}
.page-hero__search-input::placeholder { color: var(--gray-400); }
.page-hero__search-btn { padding: 10px 22px; font-size: .9rem; border-radius: 12px; }

/* Stats */
.page-hero__stats {
  display: flex; gap: 28px; flex-wrap: wrap;
}
.page-hero__stat {
  display: flex; align-items: center; gap: 6px;
  font-size: .88rem; color: var(--gray-600);
}
.page-hero__stat-icon { font-size: 1rem; }
.page-hero__stat strong { color: var(--indigo); font-weight: 700; }

@media (max-width: 640px) {
  .page-hero { padding: 88px 4% 40px; }
  .page-hero__stats { gap: 16px; }
}
</style>