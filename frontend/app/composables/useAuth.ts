import type { User } from '~/types'

/**
 * Session-backed auth state. `user` is shared app-wide via useState so the layout,
 * the middleware and the pages all read the same value.
 */
export function useAuth() {
  const api = useApi()
  const user = useState<User | null>('auth:user', () => null)
  const ready = useState<boolean>('auth:ready', () => false)

  async function login(credentials: { email: string, password: string, remember?: boolean }) {
    await fetchCsrfCookie()

    const { data } = await api<{ data: User }>('/login', {
      method: 'POST',
      body: credentials,
    })

    user.value = data
    ready.value = true
  }

  async function logout() {
    try {
      await api('/logout', { method: 'POST' })
    }
    finally {
      user.value = null
      await navigateTo('/login')
    }
  }

  /** Resolve the current session once per page load; a 401 just means "guest". */
  async function restore() {
    if (ready.value) {
      return user.value
    }

    try {
      const { data } = await api<{ data: User }>('/user')
      user.value = data
    }
    catch {
      user.value = null
    }
    finally {
      ready.value = true
    }

    return user.value
  }

  return {
    user,
    ready,
    isAuthenticated: computed(() => user.value !== null),
    login,
    logout,
    restore,
  }
}
