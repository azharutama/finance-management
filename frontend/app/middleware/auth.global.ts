export default defineNuxtRouteMiddleware((to) => {
  if (to.path === '/login') {
    return navigateTo('/');
  }
});
