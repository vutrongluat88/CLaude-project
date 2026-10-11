/**
 * $fetch instance wired for Sanctum SPA (cookie) auth:
 * sends the session cookie and mirrors the XSRF-TOKEN cookie into the header
 * that Laravel's CSRF middleware expects.
 */
export function useApi() {
  const { public: { apiBase } } = useRuntimeConfig()

  return $fetch.create({
    baseURL: apiBase,
    credentials: 'include',
    headers: { Accept: 'application/json' },
    onRequest({ options }) {
      const token = useCookie('XSRF-TOKEN').value

      if (token) {
        options.headers.set('X-XSRF-TOKEN', token)
      }
    },
  })
}

/** Origin of the Laravel app, i.e. the API base without its `/api` suffix. */
export function useApiOrigin() {
  const { public: { apiBase } } = useRuntimeConfig()

  return apiBase.replace(/\/api\/?$/, '')
}

/** Laravel sets the XSRF-TOKEN cookie here; required before any mutating request. */
export async function fetchCsrfCookie() {
  await $fetch(`${useApiOrigin()}/sanctum/csrf-cookie`, { credentials: 'include' })
}

/** Pull `{ field: [message] }` out of a 422 response. */
export function validationErrors(error: unknown): Record<string, string[]> {
  return (error as { data?: { errors?: Record<string, string[]> } })?.data?.errors ?? {}
}

/** First human-readable message from any API error. */
export function apiErrorMessage(error: unknown, fallback = 'Something went wrong.'): string {
  const data = (error as { data?: { message?: string; errors?: Record<string, string[]> } })?.data

  return Object.values(data?.errors ?? {})[0]?.[0] ?? data?.message ?? fallback
}
