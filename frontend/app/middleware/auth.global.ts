export default defineNuxtRouteMiddleware(async (to) => {
  const { token, user, fetchUser } = useAuth();

  if (!token.value && to.path !== '/login') {
    return navigateTo('/login');
  }

  if (token.value && !user.value) {
    await fetchUser();
  }

  if (token.value && to.path === '/login') {
    return navigateTo('/');
  }
});

