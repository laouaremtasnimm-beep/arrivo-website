<template>
  <SettingsSection
    title="Appearance"
    subtitle="Customize how Voyago looks and feels for you — changes apply instantly"
  >

    <!-- Theme -->
    <div class="block-label">Theme</div>
    <div class="theme-grid">
      <button
        v-for="t in themes"
        :key="t.key"
        class="theme-card"
        :class="{ active: state.theme === t.key }"
        @click="state.theme = t.key"
      >
        <div class="theme-preview" :class="`preview-${t.key}`">
          <div class="preview-bar" />
          <div class="preview-body">
            <div class="preview-line long" />
            <div class="preview-line short" />
          </div>
        </div>
        <span class="theme-label">{{ t.label }}</span>
        <span v-if="state.theme === t.key" class="theme-check">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </span>
      </button>
    </div>

    <div class="divider" />

    <!-- Accent colour -->
    <div class="block-label">Accent Color</div>
    <div class="accent-row">
      <button
        v-for="c in accentColors"
        :key="c.value"
        class="accent-dot"
        :style="{ background: c.value }"
        :class="{ active: state.accent === c.value }"
        :title="c.label"
        @click="state.accent = c.value"
      >
        <svg v-if="state.accent === c.value" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </button>
    </div>
    <p class="setting-hint">Affects buttons, prices, active states and highlights across the site.</p>

    <div class="divider" />

    <!-- Font size -->
    <SettingsRow label="Font Size" desc="Adjust the base text size — applies site-wide">
      <div class="font-size-control">
        <button class="fs-btn" @click="decreaseFontSize" :disabled="state.fontSize <= 12">A−</button>
        <span class="fs-value">{{ state.fontSize }}px</span>
        <button class="fs-btn" @click="increaseFontSize" :disabled="state.fontSize >= 20">A+</button>
      </div>
    </SettingsRow>

    <!-- Density -->
    <SettingsRow label="Layout Density" desc="Controls section padding and spacing throughout the site">
      <div class="density-tabs">
        <button
          v-for="d in densities"
          :key="d.key"
          class="density-tab"
          :class="{ active: state.density === d.key }"
          @click="state.density = d.key"
        >{{ d.label }}</button>
      </div>
    </SettingsRow>

    <!-- Animations -->
    <SettingsRow label="Animations & Transitions" desc="Disable for a reduced-motion experience">
      <ToggleSwitch v-model="state.animations" />
    </SettingsRow>

    <!-- Sidebar collapsed -->
    <SettingsRow label="Collapsed Sidebar by Default" desc="Start with the sidebar minimized on the dashboard">
      <ToggleSwitch v-model="state.collapseSidebar" />
    </SettingsRow>

    <div class="divider" />

    <!-- Live indicator -->
    <div class="live-notice">
      <span class="live-dot" /> Changes are applied live — save to keep them after refresh.
    </div>

    <div class="action-row">
      <button class="btn-ghost" @click="handleReset">Reset to defaults</button>
      <button class="btn-primary" @click="handleSave">Save Appearance</button>
    </div>

  </SettingsSection>
</template>

<script setup>
import SettingsSection from './SettingsSection.vue'
import SettingsRow     from './SettingsRow.vue'
import ToggleSwitch    from './ToggleSwitch.vue'
import { useAppearance } from '@/composables/useAppearance'

const emit = defineEmits(['saved'])

const { state, save, reset, increaseFontSize, decreaseFontSize } = useAppearance()

const themes = [
  { key: 'light',  label: 'Light'  },
  { key: 'dark',   label: 'Dark'   },
  { key: 'system', label: 'System' },
]

const accentColors = [
  { label: 'Coral',   value: '#FF5A5F' },
  { label: 'Teal',    value: '#2EC4B6' },
  { label: 'Indigo',  value: '#2D3142' },
  { label: 'Violet',  value: '#7C3AED' },
  { label: 'Amber',   value: '#F59E0B' },
  { label: 'Rose',    value: '#F43F5E' },
]

const densities = [
  { key: 'compact',  label: 'Compact'  },
  { key: 'default',  label: 'Default'  },
  { key: 'spacious', label: 'Spacious' },
]

function handleSave() {
  save()
  emit('saved', 'Appearance settings saved!')
}

function handleReset() {
  reset()
  emit('saved', 'Appearance reset to defaults.')
}
</script>

<style scoped>
.block-label {
  font-size: 12px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--gray-600); margin-bottom: 14px;
}
.setting-hint { font-size: .76rem; color: var(--gray-400); margin-top: 8px; }

/* Theme cards */
.theme-grid { display: flex; gap: 14px; flex-wrap: wrap; }
.theme-card {
  position: relative; cursor: pointer;
  border: 2px solid var(--gray-200); border-radius: var(--radius-sm);
  overflow: hidden; width: 100px; background: none;
  transition: border-color var(--transition);
}
.theme-card.active { border-color: var(--teal); }
.theme-card:hover:not(.active) { border-color: var(--gray-400); }

.theme-preview { height: 64px; }
.preview-light  { background: #fff; }
.preview-dark   { background: #1e2030; }
.preview-system { background: linear-gradient(135deg, #fff 50%, #1e2030 50%); }
.preview-bar    { height: 12px; background: rgba(0,0,0,.08); }
.preview-dark .preview-bar { background: rgba(255,255,255,.1); }
.preview-body   { padding: 8px 10px; display: flex; flex-direction: column; gap: 5px; }
.preview-line   { height: 4px; border-radius: 2px; background: rgba(0,0,0,.12); }
.preview-dark .preview-line { background: rgba(255,255,255,.15); }
.preview-line.long  { width: 70%; }
.preview-line.short { width: 45%; }
.theme-label {
  display: block; text-align: center; font-size: 12px; font-weight: 600;
  color: var(--indigo); padding: 6px 0;
}
.theme-check {
  position: absolute; top: 6px; right: 6px;
  width: 18px; height: 18px; border-radius: 50%;
  background: var(--teal); color: #fff;
  display: flex; align-items: center; justify-content: center;
}

/* Accent dots */
.accent-row { display: flex; gap: 12px; flex-wrap: wrap; }
.accent-dot {
  width: 32px; height: 32px; border-radius: 50%;
  border: 3px solid transparent; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: transform .18s, box-shadow .18s;
}
.accent-dot:hover  { transform: scale(1.15); }
.accent-dot.active { border-color: var(--indigo); box-shadow: 0 0 0 2px var(--white); }

/* Font size */
.font-size-control { display: flex; align-items: center; gap: 10px; }
.fs-btn {
  width: 32px; height: 32px; border-radius: 50%;
  border: 1.5px solid var(--gray-200); background: none;
  cursor: pointer; font-weight: 700; font-size: 12px;
  color: var(--indigo); transition: all var(--transition);
  display: flex; align-items: center; justify-content: center;
}
.fs-btn:hover:not(:disabled) { border-color: var(--teal); color: var(--teal); }
.fs-btn:disabled { opacity: .35; cursor: not-allowed; }
.fs-value {
  font-size: 14px; font-weight: 700; color: var(--indigo);
  min-width: 36px; text-align: center;
}

/* Density */
.density-tabs {
  display: flex; border: 1.5px solid var(--gray-200);
  border-radius: 8px; overflow: hidden;
}
.density-tab {
  padding: 7px 14px; font-size: 13px; font-weight: 600;
  border: none; background: none; cursor: pointer;
  color: var(--gray-600); font-family: 'DM Sans', sans-serif;
  transition: all var(--transition);
}
.density-tab + .density-tab { border-left: 1.5px solid var(--gray-200); }
.density-tab.active { background: var(--teal-lt); color: var(--teal); }
.density-tab:hover:not(.active) { background: var(--gray-50); }

/* Live notice */
.live-notice {
  display: flex; align-items: center; gap: 8px;
  font-size: .78rem; color: var(--gray-400); margin-bottom: 4px;
}
.live-dot {
  width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
  background: #27ae60;
  box-shadow: 0 0 0 0 rgba(39,174,96,.4);
  animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
  0%   { box-shadow: 0 0 0 0 rgba(39,174,96,.4); }
  70%  { box-shadow: 0 0 0 6px rgba(39,174,96,0); }
  100% { box-shadow: 0 0 0 0 rgba(39,174,96,0); }
}

.divider    { height: 1px; background: var(--gray-100); margin: 24px 0; }
.action-row { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }

.btn-primary {
  padding: 10px 22px; background: var(--coral); color: #fff;
  border: none; border-radius: 50px; font-size: 14px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; cursor: pointer;
  transition: background var(--transition);
}
.btn-primary:hover { background: var(--coral-dk); }
.btn-ghost {
  padding: 10px 18px; background: transparent; color: var(--gray-600);
  border: none; border-radius: 50px; font-size: 14px;
  font-family: 'DM Sans', sans-serif; cursor: pointer;
  transition: color var(--transition);
}
.btn-ghost:hover { color: var(--indigo); }
</style>