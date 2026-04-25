import { reactive, watch } from 'vue'

// ── Defaults ──────────────────────────────────────────────────────────────
const DEFAULTS = {
  theme:           'light',
  accent:          '#FF5A5F',
  fontSize:        15,
  density:         'default',
  animations:      true,
  collapseSidebar: false,
}

const STORAGE_KEY = 'voyago_appearance'

// ── Load persisted settings (or fall back to defaults) ────────────────────
function loadSaved() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? { ...DEFAULTS, ...JSON.parse(raw) } : { ...DEFAULTS }
  } catch {
    return { ...DEFAULTS }
  }
}

// ── Module-level singleton state ──────────────────────────────────────────
const state = reactive(loadSaved())

// ── Apply a single setting to the DOM immediately ─────────────────────────
function applyTheme(theme) {
  const root = document.documentElement
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  const isDark = theme === 'dark' || (theme === 'system' && prefersDark)

  root.setAttribute('data-theme', isDark ? 'dark' : 'light')

  if (isDark) {
    root.style.setProperty('--white',    '#1e2030')
    root.style.setProperty('--gray-50',  '#252736')
    root.style.setProperty('--gray-100', '#2d2f42')
    root.style.setProperty('--gray-200', '#3a3d52')
    root.style.setProperty('--gray-400', '#7b7e96')
    root.style.setProperty('--gray-600', '#a0a3b8')
    root.style.setProperty('--indigo',   '#e8eaf6')
    root.style.setProperty('--sand',     '#2a2d3e')
  } else {
    root.style.setProperty('--white',    '#FFFFFF')
    root.style.setProperty('--gray-50',  '#F9F9F9')
    root.style.setProperty('--gray-100', '#F2F2F2')
    root.style.setProperty('--gray-200', '#E8E8E8')
    root.style.setProperty('--gray-400', '#AAAAAA')
    root.style.setProperty('--gray-600', '#666666')
    root.style.setProperty('--indigo',   '#2D3142')
    root.style.setProperty('--sand',     '#F4EBD0')
  }
}

function applyAccent(color) {
  const root = document.documentElement
  root.style.setProperty('--coral', color)

  // Derive a slightly darker hover variant
  root.style.setProperty('--coral-dk', darken(color, 12))

  // Derive the light rgba variant
  const { r, g, b } = hexToRgb(color)
  root.style.setProperty('--coral-lt', `rgba(${r},${g},${b},.10)`)
}

function applyFontSize(size) {
  document.documentElement.style.setProperty('--base-font-size', `${size}px`)
  document.documentElement.style.fontSize = `${size}px`
}

function applyDensity(density) {
  const map = {
    compact:  { py: '56px', px: '4%' },
    default:  { py: '88px', px: '5%' },
    spacious: { py: '112px', px: '6%' },
  }
  const vals = map[density] || map.default
  document.documentElement.style.setProperty('--section-py', vals.py)
  document.documentElement.style.setProperty('--section-px', vals.px)
}

function applyAnimations(enabled) {
  document.documentElement.style.setProperty(
    '--transition',
    enabled ? '.22s cubic-bezier(.4,0,.2,1)' : '0s'
  )
}

// ── Apply all settings at once ────────────────────────────────────────────
function applyAll() {
  applyTheme(state.theme)
  applyAccent(state.accent)
  applyFontSize(state.fontSize)
  applyDensity(state.density)
  applyAnimations(state.animations)
}

// ── Watch each field and apply immediately ────────────────────────────────
watch(() => state.theme,      applyTheme)
watch(() => state.accent,     applyAccent)
watch(() => state.fontSize,   applyFontSize)
watch(() => state.density,    applyDensity)
watch(() => state.animations, applyAnimations)

// ── Apply on first load ───────────────────────────────────────────────────
applyAll()

// ── Listen for system dark mode changes when theme === 'system' ───────────
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
  if (state.theme === 'system') applyTheme('system')
})

// ── Public composable ─────────────────────────────────────────────────────
export function useAppearance() {

  function save() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...state }))
    } catch { /* quota exceeded or private mode */ }
  }

  function reset() {
    Object.assign(state, DEFAULTS)
    applyAll()
  }

  function increaseFontSize() { if (state.fontSize < 20) state.fontSize++ }
  function decreaseFontSize() { if (state.fontSize > 12) state.fontSize-- }

  return {
    state,
    save,
    reset,
    increaseFontSize,
    decreaseFontSize,
  }
}

// ── Colour helpers ────────────────────────────────────────────────────────
function hexToRgb(hex) {
  const clean = hex.replace('#', '')
  const full  = clean.length === 3
    ? clean.split('').map(c => c + c).join('')
    : clean
  return {
    r: parseInt(full.slice(0, 2), 16),
    g: parseInt(full.slice(2, 4), 16),
    b: parseInt(full.slice(4, 6), 16),
  }
}

function darken(hex, amount) {
  const { r, g, b } = hexToRgb(hex)
  const clamp = v => Math.max(0, Math.min(255, v))
  const toHex = v => clamp(v).toString(16).padStart(2, '0')
  return `#${toHex(r - amount)}${toHex(g - amount)}${toHex(b - amount)}`
}