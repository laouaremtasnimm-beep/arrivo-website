import { ref, computed } from 'vue'

export function useAuth() {
  // Read fresh from sessionStorage on every call — no shared singleton
  function getStored() {
    try {
      const s = sessionStorage.getItem('user')
      if (s) return JSON.parse(s)
      const l = localStorage.getItem('user')
      if (l) {
        // First time this tab loads — seed sessionStorage from localStorage
        sessionStorage.setItem('user', l)
        return JSON.parse(l)
      }
    } catch { /* ignore parse errors */ }
    return null
  }

  // Local ref scoped to THIS composable call / component instance
  const _user = ref(getStored())

  const user = computed(() => _user.value)
  const isLoggedIn = computed(() => !!_user.value)
  const isAgency = computed(() => _user.value?.role === 'agency')
  const isProvider = computed(() => _user.value?.role === 'provider')
  const canAccessDashboard = computed(() => isAgency.value || isProvider.value)

  function login(userData) {
    const u = {
      userID: userData.id,
      id: userData.id,
      name: `${userData.first_name} ${userData.last_name}`,
      email: userData.email,
      role: userData.role,
      company: userData.company_name ?? userData.company ?? null,
      avatar: userData.avatar_url ?? userData.avatar ?? null,
    }
    _user.value = u
    sessionStorage.setItem('user', JSON.stringify(u))
    localStorage.setItem('user', JSON.stringify(u))
  }

  function switchRole(role) {
    if (_user.value) {
      _user.value = { ..._user.value, role }
      sessionStorage.setItem('user', JSON.stringify(_user.value))
      localStorage.setItem('user', JSON.stringify(_user.value))
    }
  }

  function logout() {
    _user.value = null
    sessionStorage.removeItem('user')
    sessionStorage.removeItem('token')
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  return {
    user, isLoggedIn, isAgency, isProvider, canAccessDashboard,
    login, switchRole, logout,
  }
}