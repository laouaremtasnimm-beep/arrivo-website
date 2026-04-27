import { ref, computed } from 'vue'

// Start as guest
const _user = ref(null)

export function useAuth() {

  const user = computed(() => _user.value)
  const isLoggedIn = computed(() => !!_user.value)
  const isAgency = computed(() => _user.value?.role === 'agency')
  const isProvider = computed(() => _user.value?.role === 'provider')
  const canAccessDashboard = computed(() => isAgency.value || isProvider.value)

  function login(userData) {
    _user.value = {
      userID: userData.id,
      name: `${userData.first_name} ${userData.last_name}`,
      email: userData.email,
      role: userData.role,
      company: userData.company ?? null,
      avatar: userData.avatar ?? null,
    }
  }

  function switchRole(role) {
    _user.value = {
      userID: Date.now(),
      name: 'New User',
      email: 'new@example.com',
      role: role,
      company: null,
      avatar: null
    }
  }

  function logout() {
    _user.value = null
  }

  return {
    user,
    isLoggedIn,
    isAgency,
    isProvider,
    canAccessDashboard,
    login,
    switchRole,
    logout
  }
}