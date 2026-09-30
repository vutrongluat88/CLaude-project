/**
 * Guard every route: unauthenticated visitors get the login page, and an
 * authenticated one never sees it again.
 */
export default defineNuxtRouteMiddleware(async (to) => {
  const { restore, isAuthenticated } = useAuth()

  await restore()

  if (!isAuthenticated.value && to.path !== '/login') {
    return navigateTo({ path: '/login', query: to.fullPath !== '/' ? { redirect: to.fullPath } : {} })
  }

  if (isAuthenticated.value && to.path === '/login') {
    return navigateTo('/')
  }
})
