import { ref, computed } from 'vue'

// Rehydrate from localStorage on startup
const stored = localStorage.getItem('user')
const _user = ref(stored ? JSON.parse(stored) : null)

export function useAuth() {

  const user = computed(() => _user.value)
  const isLoggedIn = computed(() => !!_user.value)
  const isAgency = computed(() => _user.value?.role === 'agency')
  const isProvider = computed(() => _user.value?.role === 'provider')
  const canAccessDashboard = computed(() => isAgency.value || isProvider.value)

  function login(userData) {
    const u = {
      userID: userData.id,
      name: `${userData.first_name} ${userData.last_name}`,
      email: userData.email,
      role: userData.role,
      company: userData.company ?? null,
      avatar: userData.avatar ?? null,
    }
    _user.value = u
    localStorage.setItem('user', JSON.stringify(u))  // ← persist
  }

  function switchRole(role) {
    if (_user.value) {
      _user.value = { ..._user.value, role }
      localStorage.setItem('user', JSON.stringify(_user.value))
    }
  }

  function logout() {
    _user.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    sessionStorage.removeItem('token')
  }

  return {
    user, isLoggedIn, isAgency, isProvider, canAccessDashboard,
    login, switchRole, logout
  }
}